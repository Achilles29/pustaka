// Runs only against the isolated loopback test service.
const fs=require('fs');
const puppeteer=require('/tmp/finance2-studio-work/node_modules/puppeteer-core');
(async()=>{
  const dir=process.argv[2];if(!/^\/tmp\/pustaka_network_test_[a-zA-Z0-9_]+$/.test(dir))throw Error('Private test directory required');
  const fixture=JSON.parse(fs.readFileSync(dir+'/fixtures.json'));
  const browser=await puppeteer.launch({executablePath:'/usr/bin/google-chrome',headless:true,args:['--no-sandbox']});
  try{
    const page=await browser.newPage();const errors=[];page.on('pageerror',e=>errors.push(e.message));
    await page.setViewport({width:1440,height:1000});await page.goto('http://127.0.0.1:8797/login');
    await page.type('[name=identifier]','test-school-a');await page.type('[name=password]',fixture.changed_password);
    await Promise.all([page.waitForNavigation({waitUntil:'networkidle2'}),page.click('button[type=submit]')]);
    if(!page.url().includes('library-workspace'))throw Error('Login failed');
    await page.screenshot({path:dir+'/workspace-desktop.png',fullPage:true});
    const sidebar=await page.$$eval('.admin-menu a',links=>links.map(a=>({text:a.textContent.trim(),href:a.getAttribute('href')})));
    if(sidebar.length<11||!sidebar.some(x=>x.text==='Katalog Buku')||await page.$('.network-workspace-tabs'))throw Error('School sidebar migration incomplete');
    await page.goto('http://127.0.0.1:8797/library-workspace/edit/books',{waitUntil:'networkidle2'});
    const active=await page.$$eval('.admin-menu-item.active:not(.has-children) > a',links=>links.map(a=>a.textContent.trim()));
    if(active.length!==1||active[0]!=='Katalog Buku')throw Error('Wrong active sidebar menu');
    await page.goto('http://127.0.0.1:8797/library-workspace/records/books',{waitUntil:'networkidle2'});
    await page.setViewport({width:390,height:844});await page.screenshot({path:dir+'/workspace-mobile.png',fullPage:true});
    const dimensions=await page.evaluate(()=>({viewport:innerWidth,body:document.body.scrollWidth}));
    if(dimensions.body>dimensions.viewport)throw Error('Mobile page overflow');
    await page.click('[data-bs-target="#sidebar-menu"]');await page.waitForFunction(()=>document.getElementById('sidebar-menu').classList.contains('show'));
    await page.screenshot({path:dir+'/workspace-mobile-sidebar.png',fullPage:true});
    await page.goto('http://127.0.0.1:8797/logout');await page.goto('http://127.0.0.1:8797/login');
    await page.type('[name=identifier]','test-central');await page.type('[name=password]',fixture.password);
    await Promise.all([page.waitForNavigation({waitUntil:'networkidle2'}),page.click('button[type=submit]')]);
    await page.setViewport({width:1440,height:1000});await page.goto('http://127.0.0.1:8797/reports/network',{waitUntil:'networkidle2'});
    await page.screenshot({path:dir+'/network-report-desktop.png',fullPage:true});
    await page.setViewport({width:390,height:844});await page.screenshot({path:dir+'/network-report-mobile.png',fullPage:true});
    const reportDimensions=await page.evaluate(()=>({viewport:innerWidth,body:document.body.scrollWidth}));
    if(reportDimensions.body>reportDimensions.viewport)throw Error('Mobile report overflow');
    if(errors.length)throw Error(JSON.stringify(errors));
    console.log(JSON.stringify({javascriptErrors:errors,sidebar,activeMenu:active,mobileDimensions:dimensions,reportDimensions,screenshots:dir},null,2));
  }finally{await browser.close();}
})().catch(e=>{console.error(e.message);process.exit(1);});
