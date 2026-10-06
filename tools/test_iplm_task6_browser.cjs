const fs=require('fs'),puppeteer=require('/tmp/finance2-studio-work/node_modules/puppeteer-core');
(async()=>{
 const dir=process.argv[2];if(!/^\/tmp\/pustaka_network_test_[a-zA-Z0-9_]+$/.test(dir))throw Error('Private fixture required');const fixture=JSON.parse(fs.readFileSync(dir+'/fixtures.json')),base='http://127.0.0.1:8797',fixtureLibrary=Number(process.argv[3]||30);
 const browser=await puppeteer.launch({executablePath:'/usr/bin/google-chrome',headless:true,args:['--no-sandbox']});let checks=0;const errors=[];function ok(v,m){if(!v)throw Error(m);checks++;console.log('PASS '+m);}
 try{
 const page=await browser.newPage();page.on('pageerror',e=>errors.push(e.message));await page.goto(base+'/login');await page.type('[name=identifier]','test-central');await page.type('[name=password]',fixture.password);await Promise.all([page.waitForNavigation(),page.click('button[type=submit]')]);
 for(const width of [1440,390]){
  await page.setViewport({width,height:1000});await page.goto(base+'/libraries',{waitUntil:'networkidle2'});
  ok(await page.$$eval('#library-directory-tabs .library-filter-tab-row',els=>els.length===2),'Two stacked tab rows '+width);
  ok(await page.$eval('#library-directory-tabs',el=>{const body=el.getBoundingClientRect(),table=document.querySelector('.libraries-table-toolbar').getBoundingClientRect(),map=document.getElementById('libraries-map').getBoundingClientRect();return body.top>map.top&&body.bottom<=table.top;}),'Tabs exactly between map and table '+width);
  ok(await page.evaluate(()=>document.body.scrollWidth<=innerWidth),'Responsive directory '+width);
  const school=await page.$$eval('#library-directory-tabs .library-filter-tab-row:first-child a',els=>els.find(e=>e.textContent.trim()==='Perpustakaan Sekolah').href);await page.goto(school,{waitUntil:'networkidle2'});
  ok(await page.$eval('#libraries-map-data',el=>{const ps=JSON.parse(el.textContent);return ps.length>0&&ps.every(p=>p.type_code==='sekolah');}),'Type tab filters map '+width);
  const smp=await page.$$eval('#library-directory-tabs .library-filter-tab-row:nth-child(2) a',els=>els.find(e=>e.textContent.includes('Perpustakaan SMP')).href);await page.goto(smp,{waitUntil:'networkidle2'});
  ok(await page.$eval('#libraries-map-data',el=>{const ps=JSON.parse(el.textContent);return ps.length>0&&ps.every(p=>p.subtype.includes('SMP'));}),'Subtype tab filters map '+width);
  ok(await page.$eval('[name=subtype_id]',el=>el.value===new URL(location.href).searchParams.get('subtype_id')),'Tabs synchronize dropdown '+width);
  ok(await page.$eval('.library-legend-subfilter.active',el=>el.textContent.includes('SMP')),'Tabs synchronize legend '+width);
  await page.$eval('#library-directory-tabs',el=>el.scrollIntoView({block:'center'}));await page.screenshot({path:dir+'/task6-tabs-'+width+'.png'});
  await page.goto(base+'/library-activation',{waitUntil:'networkidle2'});ok((await page.content()).includes('tanpa menunggu kabupaten'),'County sees moderation flow '+width);ok((await page.$$('form[action$="/issue"]')).length===0,'No county issuance requirement in UI '+width);
 }
 const publicContext=await browser.createBrowserContext();const pub=await publicContext.newPage();pub.on('pageerror',e=>errors.push(e.message));await pub.setViewport({width:390,height:960});await pub.goto(base+'/aktivasi-perpustakaan',{waitUntil:'networkidle2'});
 await pub.type('#library-search','SD NEGERI');await pub.waitForSelector('#search-results button');await pub.$eval('#search-results button',e=>e.click());
 ok(await pub.$eval('#ownership-dialog',el=>el.open),'School search opens signup modal');ok((await pub.$$('#activation-code')).length===0,'Signup requires no county code');
 ok(await pub.$eval('#ownership-dialog',el=>el.getBoundingClientRect().width<=innerWidth),'Signup modal responsive');
 await pub.screenshot({path:dir+'/task6-signup-mobile.png'});
 // Deterministic unused fixture library 30, selected through the same public map event.
 const record=await pub.$eval('#libraries-map-data',(el,id)=>JSON.parse(el.textContent).find(p=>Number(p.id)===id),fixtureLibrary);ok(!!record,'Unused fixture exists on public map');await pub.evaluate(p=>document.dispatchEvent(new CustomEvent('library:selected',{detail:p})),record);
 await pub.type('#registrant-name','Pengelola Browser Task6');await pub.type('#registrant-phone','081234567888');await pub.type('#activation-password','Browser!Task6Personal');await pub.type('#activation-confirmation','Browser!Task6Personal');await pub.click('[name=ownership]');
 await Promise.all([pub.waitForNavigation({waitUntil:'networkidle2'}),pub.click('#activation-form button')]);ok(new URL(pub.url()).pathname==='/library-workspace','Public signup immediately opens scoped dashboard');
 ok((await pub.content()).includes('Username Anda:'),'User receives username without county approval');
 await pub.goto(base+'/admin');ok(new URL(pub.url()).pathname==='/library-workspace','County dashboard route redirects local account to its workspace');
 ok((await pub.goto(base+'/libraries')).status()===403,'New local account cannot access county directory');
 ok(errors.length===0,'No JS runtime errors');fs.writeFileSync(dir+'/task6-browser-results.json',JSON.stringify({checks,errors}));console.log(checks+' browser checks passed.');
 }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exit(1);});
