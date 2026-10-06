const fs=require('fs'),puppeteer=require('/tmp/finance2-studio-work/node_modules/puppeteer-core');
(async()=>{
 const dir=process.argv[2];if(!/^\/tmp\/pustaka_network_test_[a-zA-Z0-9_]+$/.test(dir))throw Error('Private fixture required');
 const fixture=JSON.parse(fs.readFileSync(dir+'/fixtures.json')),base='http://127.0.0.1:8797';
 const browser=await puppeteer.launch({executablePath:'/usr/bin/google-chrome',headless:true,args:['--no-sandbox']});let checks=0;const errors=[];
 function ok(v,m){if(!v)throw Error(m);checks++;console.log('PASS '+m);}
 try{const page=await browser.newPage();page.on('pageerror',e=>errors.push(e.message));await page.goto(base+'/login');await page.type('[name=identifier]','test-central');await page.type('[name=password]',fixture.password);await Promise.all([page.waitForNavigation(),page.click('button[type=submit]')]);
 for(const width of [1440,390]){await page.setViewport({width,height:1000});await page.goto(base+'/libraries?per_page=10',{waitUntil:'networkidle2'});
 ok(await page.evaluate(()=>document.body.scrollWidth<=innerWidth),'No page overflow '+width);
 ok(await page.$eval('.library-legend-grid',el=>{const a=el.children[0].getBoundingClientRect(),b=el.children[1].getBoundingClientRect();return b.left>a.left&&Math.abs(a.top-b.top)<2;}),'Two side-by-side legend columns '+width);
 ok(await page.$$eval('.library-legend-filter',els=>els.length===7),'Six types plus reset '+width);
 ok(await page.$$eval('.library-legend-subfilter',els=>els.length===20),'All 19 subtypes plus reset '+width);
 ok(await page.$eval('[name=type_id]',el=>!Array.from(el.options).some(o=>['Perpustakaan Daerah','Perpustakaan Desa','Komunitas Literasi'].includes(o.textContent.trim()))),'No retired dropdown enums '+width);
 const original=await page.$eval('#libraries-map-data',el=>JSON.parse(el.textContent).length);
 const subtype=await page.$$eval('.library-legend-subfilter',els=>els.find(e=>e.textContent.includes('Desa/Kelurahan')).href);
 await page.goto(subtype,{waitUntil:'networkidle2'});
 ok(await page.$eval('#libraries-map-data',el=>{const ps=JSON.parse(el.textContent);return ps.length>0&&ps.every(p=>p.type_code==='umum'&&p.subtype.includes('Desa/Kelurahan'));}),'Subtype legend filters actual map payload '+width);
 ok(await page.$$eval('.library-legend-subfilter',els=>els.length===5),'Only selected parent subtypes appear '+width);
 ok(await page.$eval('.library-legend-subfilter.active',el=>el.textContent.includes('Desa/Kelurahan')),'Selected subtype highlighted '+width);
 await page.$eval('.library-legend-grid',el=>el.scrollIntoView({block:'center'}));await page.screenshot({path:dir+'/taxonomy-legend-'+width+'.png'});
 const reset=await page.$eval('.library-legend-subfilter',el=>el.href);await page.goto(reset);
 ok(await page.$eval('[name=type_id]',el=>el.value!=='')&&await page.$eval('[name=subtype_id]',el=>el.value===''),'Subtype reset keeps parent '+width);
 const all=await page.$eval('.library-legend-filter',el=>el.href);await page.goto(all);ok(await page.$eval('#libraries-map-data',el=>JSON.parse(el.textContent).length)===original,'Reset restores full map '+width);
 }
 await page.goto(base+'/libraries/create');ok(await page.$eval('[name=library_type_id]',el=>!Array.from(el.options).some(o=>['Perpustakaan Daerah','Perpustakaan Desa','Komunitas Literasi'].includes(o.textContent.trim()))),'Create form shares clean enum');
 await page.goto(base+'/library-types');ok(!(await page.content()).includes('value="perpusda"'),'Master management has no removed entry');
 ok(errors.length===0,'No JS errors');fs.writeFileSync(dir+'/taxonomy-browser-results.json',JSON.stringify({checks,errors}));console.log(checks+' browser checks passed.');
 }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exit(1);});
