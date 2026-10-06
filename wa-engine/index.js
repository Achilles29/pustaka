'use strict';
const http=require('http'),fs=require('fs'),path=require('path'),mysql=require('mysql2/promise'),pino=require('pino');
const {default:makeWASocket,useMultiFileAuthState,DisconnectReason,fetchLatestBaileysVersion}=require('@whiskeysockets/baileys');
for(const line of (fs.existsSync(path.join(__dirname,'.env'))?fs.readFileSync(path.join(__dirname,'.env'),'utf8').split(/\r?\n/):[])){const i=line.indexOf('=');if(i>0&&!process.env[line.slice(0,i)])process.env[line.slice(0,i)]=line.slice(i+1).trim();}
const port=Number(process.env.WA_PORT||3071),token=process.env.WA_TOKEN||'',pool=mysql.createPool({host:process.env.DB_HOST||'127.0.0.1',user:process.env.DB_USER||'root',password:process.env.DB_PASS||'',database:process.env.DB_NAME||'pustaka'});
let sock,status='DISCONNECTED',phone=null,qr=null,busy=false,enabled=true,restarting=false;
const db=(q,p=[])=>pool.query(q,p);
const set=async(s,p=phone)=>{status=s;phone=p;await db('UPDATE wa_session SET status=?,phone_number=?,qr_data=?,last_ping_at=NOW() WHERE id=1',[s,p,qr]);};
const reply=(r,c,d)=>{r.writeHead(c,{'content-type':'application/json','cache-control':'no-store'});r.end(JSON.stringify(d));};
const readBody=req=>new Promise(resolve=>{let raw='';req.on('data',c=>raw+=c);req.on('end',()=>{try{resolve(JSON.parse(raw||'{}'));}catch(e){resolve({});}});});
async function closeSocket(){try{if(sock&&sock.ws&&typeof sock.ws.close==='function')sock.ws.close();}catch(e){}sock=null;}
async function clearPairing(){await closeSocket();try{fs.rmSync(path.join(__dirname,'auth_info'),{recursive:true,force:true});}catch(e){}qr=null;phone=null;await db('UPDATE wa_session SET is_enabled=0,status=?,phone_number=NULL,qr_data=NULL,last_ping_at=NOW() WHERE id=1',['DISCONNECTED']);status='DISCONNECTED';}
async function control(action){
  if(action==='disable'){enabled=false;qr=null;await closeSocket();await db('UPDATE wa_session SET is_enabled=0,status=?,qr_data=NULL,last_ping_at=NOW() WHERE id=1',['DISABLED']);status='DISABLED';return {ok:true,status,message:'WA dinonaktifkan sementara. Penautan tetap tersimpan.'};}
  if(action==='unlink'){enabled=false;try{if(sock&&typeof sock.logout==='function')await sock.logout();}catch(e){}await clearPairing();return {ok:true,status,message:'Penautan WhatsApp telah dihapus. Scan QR baru untuk menautkan kembali.'};}
  if(action==='enable'){enabled=true;await db('UPDATE wa_session SET is_enabled=1,status=?,qr_data=NULL,last_ping_at=NOW() WHERE id=1',['DISCONNECTED']);qr=null;await closeSocket();setTimeout(()=>start().catch(console.error),250);return {ok:true,status:'CONNECTING',message:'WA diaktifkan. QR akan tersedia dalam beberapa detik.'};}
  return {ok:false,message:'Aksi tidak dikenal.'};
}
http.createServer(async(req,res)=>{const u=new URL(req.url,'http://127.0.0.1');if((u.searchParams.get('token')||'')!==token)return reply(res,403,{ok:false,message:'Forbidden'});if(req.method==='GET'&&u.pathname==='/internal/status')return reply(res,200,{ok:true,status,phone,enabled});if(req.method==='GET'&&u.pathname==='/internal/qr')return reply(res,200,{ok:true,status,phone,enabled,qr:enabled?qr:null});if(req.method==='POST'&&u.pathname==='/internal/control')return reply(res,200,await control((await readBody(req)).action));return reply(res,404,{ok:false,message:'Not found'});}).listen(port,'127.0.0.1');
async function worker(){if(busy||!enabled||status!=='CONNECTED'||!sock)return;busy=true;try{const [rows]=await db("SELECT * FROM wa_outbox WHERE status='PENDING' AND (scheduled_at IS NULL OR scheduled_at<=NOW()) ORDER BY id LIMIT 5");for(const r of rows){try{await sock.sendMessage(String(r.phone_number).replace(/\D/g,'').replace(/^0/,'62')+'@s.whatsapp.net',{text:r.message});await db("UPDATE wa_outbox SET status='SENT',sent_at=NOW() WHERE id=?",[r.id]);}catch(e){await db("UPDATE wa_outbox SET status='FAILED',retry_count=retry_count+1,error_message=? WHERE id=?",[String(e.message).slice(0,500),r.id]);}await new Promise(x=>setTimeout(x,800));}}finally{busy=false}}
async function start(){
  if(!enabled||restarting)return;restarting=true;
  try{const {state,saveCreds}=await useMultiFileAuthState(path.join(__dirname,'auth_info'));const {version}=await fetchLatestBaileysVersion();sock=makeWASocket({version,auth:state,logger:pino({level:'silent'})});sock.ev.on('creds.update',saveCreds);sock.ev.on('connection.update',async u=>{if(u.qr&&enabled){qr=u.qr;await set('WAITING_QR');}if(u.connection==='open'&&enabled){qr=null;await set('CONNECTED',String(sock.user.id).split(':')[0]);}if(u.connection==='close'){qr=null;if(!enabled){await set(status==='DISABLED'?'DISABLED':'DISCONNECTED',phone);return;}await set('DISCONNECTED',phone);if(u.lastDisconnect?.error?.output?.statusCode!==DisconnectReason.loggedOut)setTimeout(()=>start().catch(console.error),5000);}});}
  finally{restarting=false;}
}
async function boot(){const [rows]=await db('SELECT is_enabled,status,phone_number FROM wa_session WHERE id=1');if(rows[0]){enabled=Number(rows[0].is_enabled)===1;phone=rows[0].phone_number||null;}if(!enabled){await set('DISABLED',phone);return;}await start();}
boot().catch(console.error);setInterval(worker,3000);
