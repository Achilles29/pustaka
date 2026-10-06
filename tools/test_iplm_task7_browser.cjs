const fs=require('fs'),puppeteer=require('/tmp/finance2-studio-work/node_modules/puppeteer-core');
(async()=>{
 const dir=process.argv[2];if(!/^\/tmp\/pustaka_network_test_[a-zA-Z0-9_]+$/.test(dir))throw Error('Private fixture required');const f=JSON.parse(fs.readFileSync(dir+'/fixtures.json')),base='http://127.0.0.1:8797';
 const browser=await puppeteer.launch({executablePath:'/usr/bin/google-chrome',headless:true,args:['--no-sandbox']});let checks=0;const errors=[];const ok=(v,m)=>{if(!v)throw Error(m);checks++;console.log('PASS '+m);};
 async function login(page,user){await page.goto(base+'/login');await page.type('[name=identifier]',user);await page.type('[name=password]',f.password);await Promise.all([page.waitForNavigation(),page.click('button[type=submit]')]);}
 try{
  const page=await browser.newPage();page.on('pageerror',e=>errors.push(e.message));await login(page,'test-central');
  for(const width of [1440,390]){
   await page.setViewport({width,height:960});await page.goto(base+'/libraries?q=20316062',{waitUntil:'networkidle2'});
   ok(await page.evaluate(()=>document.body.scrollWidth<=innerWidth),'Directory responsive '+width);
   await page.waitForSelector('[data-library-focus]');await page.click('[data-library-focus]');await page.waitForSelector('.library-marker-selected',{timeout:15000});
   ok(await page.$eval('.library-marker-selected',e=>e.classList.contains('leaflet-marker-icon')),'Coordinate click highlights actual marker '+width);
   ok((await page.$$('.leaflet-popup')).length===1,'Coordinate opens map popup '+width);
   await page.click('[data-library-profile]');await page.waitForSelector('dialog[open]');
   ok(await page.$eval('#library-profile-dialog',e=>e.getBoundingClientRect().width<=innerWidth),'Profile modal responsive '+width);
   ok((await page.$$('#library-profile-survey dt')).length>=40,'Admin sees full supplemental fields '+width);
   await page.screenshot({path:dir+'/task7-profile-'+width+'.png'});await page.keyboard.press('Escape');ok(!(await page.$('dialog[open]')),'Escape closes profile '+width);
  }
  await page.goto(base+'/libraries?q=20316062');
  const missing=(await page.goto(base+'/library-workspace/survey?library_id=4')).status();ok(missing===200,'Central can view scoped survey');
  const context=await browser.createBrowserContext();const local=await context.newPage();local.on('pageerror',e=>errors.push(e.message));await login(local,'test-school-a');
  for(const width of [1440,390]){
   await local.setViewport({width,height:960});await local.goto(base+'/library-workspace');ok(!!(await local.$('.network-profile-flare')),'Dashboard flare visible '+width);
   await local.goto(base+'/library-workspace/guide');ok((await local.content()).includes('1 Januari–31 Desember 2026'),'Guide uses 2026 data '+width);ok(await local.evaluate(()=>document.body.scrollWidth<=innerWidth),'Guide responsive '+width);
   await local.goto(base+'/library-workspace/profile');ok(await local.$eval('[name=manager_name]',e=>e.required)&&await local.$eval('[name=phone]',e=>e.required),'PIC and phone required in form '+width);
   await local.goto(base+'/library-workspace/survey');ok((await local.$$('input[name=survey_version]')).length===1,'Versioned local survey visible '+width);ok(await local.evaluate(()=>document.body.scrollWidth<=innerWidth),'Survey responsive '+width);await local.screenshot({path:dir+'/task7-survey-'+width+'.png'});
  }
  await local.$eval('[name=vision]',e=>e.value='Browser Task7 vision <script>window.task7xss=1</script>');await local.$eval('[name=reference_year]',e=>e.value='2026');await Promise.all([local.waitForNavigation(),local.click('form.card button')]);ok((await local.content()).includes('Pendataan tambahan disimpan.'),'Local survey saves via CSRF form');
  ok(await local.evaluate(()=>!window.task7xss),'Stored markup is escaped');
  ok((await local.goto(base+'/library-workspace/survey?library_id=6')).status()===403,'Cross-library survey access rejected');
  await local.goto(base+'/library-workspace/survey');
  const csrf=await local.evaluate(async()=>{const r=await fetch('/library-workspace/survey',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'network_csrf=forged&survey_version=1&vision=forged'});return r.status;});ok(csrf===403,'Forged CSRF rejected');
  ok((await local.goto(base+'/libraries')).status()===403,'Local operator cannot open central directory');
  await page.goto(base+'/libraries?q='+encodeURIComponent('SD NEGERI 1 KARANGASEM'));await page.click('[data-library-profile]');
  // Scoped test-school-a is library 4; inspect directly through admin survey page.
  await page.goto(base+'/library-workspace/survey?library_id=4');ok(await page.$eval('[name=vision]',e=>e.value.includes('Browser Task7 vision')),'Central reads latest local input');
  const metadata=JSON.parse(fs.readFileSync(dir+'/task7-fixture-metadata.json'));
  await page.goto(base+'/libraries?q='+metadata.code);await page.click('[data-library-profile="4"]');
  ok(await page.$eval('#library-profile-survey',e=>e.textContent.includes('Browser Task7 vision <script>')),'Admin profile modal includes latest local survey, escaped');
  ok(await page.evaluate(()=>!window.task7xss),'Admin modal prevents stored script execution');
  ok(errors.length===0,'No JavaScript runtime errors');fs.writeFileSync(dir+'/task7-browser-results.json',JSON.stringify({checks,errors}));console.log(checks+' browser checks passed');
 }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exit(1);});
