const fs=require('fs');
const puppeteer=require('/tmp/finance2-studio-work/node_modules/puppeteer-core');
(async()=>{
 const dir=process.argv[2];if(!/^\/tmp\/pustaka_network_test_[a-zA-Z0-9_]+$/.test(dir))throw Error('Private test directory required');
 const fixture=JSON.parse(fs.readFileSync(dir+'/fixtures.json'));const service=JSON.parse(fs.readFileSync(dir+'/services.json'));
 const browser=await puppeteer.launch({executablePath:'/usr/bin/google-chrome',headless:true,args:['--no-sandbox']});
 try{
  const page=await browser.newPage();const errors=[];page.on('pageerror',e=>errors.push(e.message));
  await page.goto('http://127.0.0.1:8797/login');await page.type('[name=identifier]','test-school-a');await page.type('[name=password]',fixture.changed_password);await Promise.all([page.waitForNavigation({waitUntil:'networkidle2'}),page.click('button[type=submit]')]);
  for(const width of [1440,390]){
   await page.setViewport({width,height:960});
   for(const path of ['stock','stock/'+service.stock,'labels','cards','registrations','reservations','kiosk']){
    await page.goto('http://127.0.0.1:8797/library-services/'+path,{waitUntil:'networkidle2'});
    const state=await page.evaluate(()=>({width:innerWidth,body:document.body.scrollWidth,active:[...document.querySelectorAll('.admin-menu-item.active:not(.has-children) > a')].map(a=>a.textContent.trim()),menus:document.querySelectorAll('.admin-menu-item:not(.has-children) > a').length}));
    if(state.body>state.width||state.active.length!==1||state.menus<17)throw Error(path+' invalid layout '+JSON.stringify(state));
    if(path==='stock/'+service.stock||path==='registrations')await page.screenshot({path:dir+'/service-'+path.replace('/','-')+'-'+width+'.png',fullPage:true});
   }
  }
  for(const path of ['labels','cards']){
   await page.goto('http://127.0.0.1:8797/library-services/'+path+'?print=1',{waitUntil:'networkidle2'});
   const rendered=await page.$$eval('[data-qr]',els=>els.length>0&&els.every(el=>el.querySelector('img,canvas')));if(!rendered)throw Error('QR rendering failed '+path);
   await page.screenshot({path:dir+'/service-print-'+path+'.png',fullPage:true});
  }
  await page.goto('http://127.0.0.1:8797/logout');await page.setViewport({width:390,height:844});await page.goto('http://127.0.0.1:8797/jejaring/daftar/4',{waitUntil:'networkidle2'});
  if(await page.evaluate(()=>document.body.scrollWidth>innerWidth))throw Error('Public registration overflow');await page.screenshot({path:dir+'/service-public-registration.png',fullPage:true});
  if(errors.length)throw Error(JSON.stringify(errors));console.log('PASS 14 responsive service pages, required sidebar menus, active menu, QR labels/cards, public form; no JS errors.');
 }finally{await browser.close();}
})().catch(e=>{console.error(e.message);process.exit(1);});
