const fs=require('fs'),puppeteer=require('/tmp/finance2-studio-work/node_modules/puppeteer-core');
(async()=>{
 const dir=process.argv[2];if(!/^\/tmp\/pustaka_network_test_[a-zA-Z0-9_]+$/.test(dir))throw Error('Private fixture required');
 const fixture=JSON.parse(fs.readFileSync(dir+'/fixtures.json')),ids=JSON.parse(fs.readFileSync(dir+'/iplm-http.json'));
 const browser=await puppeteer.launch({executablePath:'/usr/bin/google-chrome',headless:true,args:['--no-sandbox']});
 const evidence=[],errors=[];
 try{const page=await browser.newPage();page.on('pageerror',e=>errors.push(e.message));
 for(const who of ['local','central']){
  await page.goto('http://127.0.0.1:8797/logout');await page.goto('http://127.0.0.1:8797/login');await page.type('[name=identifier]',who==='local'?'test-school-a':'test-central');await page.type('[name=password]',who==='local'?fixture.changed_password:fixture.password);await Promise.all([page.waitForNavigation({waitUntil:'networkidle2'}),page.click('button[type=submit]')]);
  for(const width of [1440,390]){await page.setViewport({width,height:960});
   for(const path of who==='local'?['iplm','iplm/form/'+ids.id]:['iplm','iplm/form/'+ids.id,'iplm/settings','iplm/analysis/'+ids.period,'library-types','libraries/create']){
    const res=await page.goto('http://127.0.0.1:8797/'+path,{waitUntil:'networkidle2'});if(res.status()!==200)throw Error('HTTP '+res.status()+' '+path);
    const state=await page.evaluate(()=>({width:innerWidth,body:document.body.scrollWidth,active:[...document.querySelectorAll('.admin-menu-item.active:not(.has-children)>a')].map(x=>x.textContent.trim()),hasIplm:[...document.querySelectorAll('.admin-menu a')].some(x=>x.href.endsWith('/iplm'))}));
    if(state.body>state.width||state.active.length!==1||!state.hasIplm)throw Error(path+' '+JSON.stringify(state));
    if(path.startsWith('iplm/form')){
     const tabs=await page.$$('[data-iplm-tabs] [role=tab]');if(tabs.length!==6)throw Error('Expected six form tabs');
     await page.click('#tab-collection');
     const evidenceLayout=await page.evaluate(()=>{if(document.querySelectorAll('#iplm-form input[type=url]').length!==1||document.querySelector('[name^="e_"]'))throw Error('Expected single global evidence URL');const f=document.querySelector('#f_print_titles').getBoundingClientRect(),e=document.querySelector('#f_print_titles').closest('.iplm-field-row').querySelector('.iplm-evidence').getBoundingClientRect();return {inputX:f.x,evidenceX:e.x,inputY:f.y,evidenceY:e.y,visible:document.querySelectorAll('[data-iplm-panel]:not([hidden])').length};});
     if(evidenceLayout.visible!==1||(width===1440?evidenceLayout.evidenceX<=evidenceLayout.inputX:evidenceLayout.evidenceY<=evidenceLayout.inputY))throw Error('Incorrect evidence layout '+JSON.stringify(evidenceLayout));
     if(who==='local'){await page.$eval('#f_print_titles',e=>e.value='321');await page.click('#tab-staff');await page.click('#tab-collection');if(await page.$eval('#f_print_titles',e=>e.value)!=='321')throw Error('Tab switch lost field value');const count=await page.$eval('#iplm-form',f=>Array.from(new FormData(f).keys()).filter(k=>k.startsWith('f_')).length);if(count!==43)throw Error('Hidden tabs omitted from form submission');}
     await page.screenshot({path:dir+'/iplm-task2-'+who+'-collection-'+width+'.png'});
     await page.click('#tab-identity');
     const selects=await page.evaluate(()=>{const t=document.querySelector('[name=f_library_type_id]'),s=document.querySelector('[name=f_library_subtype_id]');return {type:t.value,sub:s.value,valid:[...s.options].filter(o=>o.value&&!o.disabled).every(o=>o.dataset.type===t.value)};});if(!selects.valid||!selects.sub)throw Error('Dependent form subtype invalid');
     if(who==='local'){await page.select('[name=f_library_type_id]',await page.$eval('[name=f_library_type_id]',s=>[...s.options].find(o=>o.textContent==='Perpustakaan Umum').value));const changed=await page.$eval('[name=f_library_subtype_id]',s=>({value:s.value,visible:[...s.options].filter(o=>o.value&&!o.disabled).length}));if(changed.value!==''||changed.visible!==4)throw Error('Parent change did not reset/filter subtype');}
    }
    if(path==='libraries/create'){const types=await page.$eval('[name=library_type_id]',s=>[...s.options].filter(o=>o.value).slice(0,3).map(o=>o.textContent.trim()));if(types.join('|')!=='Perpustakaan Umum|Perpustakaan Sekolah|Perpustakaan Khusus')throw Error('Wrong type ordering');}
    if(path==='iplm/settings'){await page.click('#tab-fields');if(!await page.$eval('#panel-periods',p=>p.hidden)||await page.$eval('#panel-fields',p=>p.hidden))throw Error('Settings tabs failed');await page.click('#tab-periods');await page.screenshot({path:dir+'/iplm-task2-settings-'+width+'.png'});}
    if(path==='iplm'||path==='iplm/form/'+ids.id||path==='library-types')await page.screenshot({path:dir+'/iplm-'+who+'-'+path.replaceAll('/','-')+'-'+width+'.png',fullPage:path!=='iplm/form/'+ids.id});
    evidence.push({who,path,...state});
   }
  }
 }
 if(errors.length)throw Error(JSON.stringify(errors));fs.writeFileSync(dir+'/iplm-browser.json',JSON.stringify({errors,evidence},null,2));console.log('PASS '+evidence.length+' responsive IPLM/master pages, active scoped sidebar and dependent dropdowns; no JS errors.');
 }finally{await browser.close();}
})().catch(e=>{console.error(e.message);process.exit(1);});
