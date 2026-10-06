const fs = require('fs');
const puppeteer = require('/tmp/finance2-studio-work/node_modules/puppeteer-core');
(async () => {
    const dir = process.argv[2];
    if (!/^\/tmp\/pustaka_network_test_[a-zA-Z0-9_]+$/.test(dir)) throw Error('Private fixture required');
    const fixture = JSON.parse(fs.readFileSync(dir + '/fixtures.json'));
    const base = 'http://127.0.0.1:8797';
    const browser = await puppeteer.launch({executablePath:'/usr/bin/google-chrome',headless:true,args:['--no-sandbox']});
    let checks = 0; const errors = [];
    function ok(value, label) { if (!value) throw Error(label); checks++; console.log('PASS '+label); }
    try {
        const page = await browser.newPage(); page.on('pageerror', e => errors.push(e.message));
        await page.goto(base+'/libraries'); ok(page.url().includes('/login'), 'Anonymous directory access requires login');
        await page.type('[name=identifier]','test-central'); await page.type('[name=password]',fixture.password);
        await Promise.all([page.waitForNavigation(),page.click('button[type=submit]')]);
        for (const width of [1440,390]) {
            await page.setViewport({width,height:960});
            await page.goto(base+'/libraries?per_page=10',{waitUntil:'networkidle2'});
            ok(await page.$eval('thead', el=>el.textContent.includes('Nama institusi')), 'Institution column '+width);
            ok(await page.evaluate(()=>document.body.scrollWidth<=innerWidth),'Responsive page '+width);
            const original = await page.$eval('#libraries-map-data',el=>JSON.parse(el.textContent));
            ok(original.length>10,'Map not limited to table page '+width);
            ok(await page.$eval('.library-directory-actions',el=>Array.from(el.querySelectorAll('a,button')).every(a=>!a.textContent.trim()&&a.getAttribute('aria-label'))),'Icon-only actions retain accessible names '+width);
            await page.focus('[data-library-profile]');
            ok(await page.$eval('#library-action-tooltip',el=>!el.hidden&&el.textContent==='Detail profil'),'Tooltip on keyboard focus '+width);
            await page.click('[data-library-profile]');
            ok(await page.$eval('#library-profile-dialog',el=>el.open),'Profile modal opens '+width);
            ok(await page.$eval('#library-profile-fields',el=>el.children.length===17),'Full profile fields '+width);
            ok(await page.$eval('#library-profile-dialog',el=>el.getBoundingClientRect().width<=innerWidth),'Responsive modal '+width);
            await page.screenshot({path:dir+'/task5-profile-'+width+'.png'});
            await page.keyboard.press('Escape'); ok(await page.$eval('#library-profile-dialog',el=>!el.open),'Escape closes modal '+width);
            const type = await page.$eval('[name=type_id]',el=>Array.from(el.options).find(o=>o.textContent.trim()==='Perpustakaan Sekolah').value);
            await Promise.all([page.waitForNavigation({waitUntil:'networkidle2'}),page.select('[name=type_id]',type)]);
            let points = await page.$eval('#libraries-map-data',el=>JSON.parse(el.textContent));
            ok(points.length>0 && points.every(p=>p.type_code==='sekolah') && points.length<original.length,'Type change automatically filters map '+width);
            const sub = await page.$eval('[name=subtype_id]',el=>Array.from(el.options).find(o=>o.textContent.includes('Perpustakaan SMP')).value);
            ok(await page.$eval('[name=subtype_id]',el=>Array.from(el.options).every(o=>!o.value||o.disabled||o.dataset.type===document.querySelector('[name=type_id]').value)),'Cascading subtypes '+width);
            await Promise.all([page.waitForNavigation({waitUntil:'networkidle2'}),page.select('[name=subtype_id]',sub)]);
            points = await page.$eval('#libraries-map-data',el=>JSON.parse(el.textContent));
            ok(points.length>0 && points.every(p=>p.subtype.includes('SMP')),'Subtype filters map '+width);
            const link = await page.$$eval('.library-legend-filter',els=>els.find(e=>e.textContent.includes('Perpustakaan Umum')).href);
            await page.goto(link,{waitUntil:'networkidle2'});
            points = await page.$eval('#libraries-map-data',el=>JSON.parse(el.textContent));
            ok(points.length>0 && points.every(p=>p.type_code==='umum'),'Legend filters map '+width);
            ok(await page.$eval('[name=subtype_id]',el=>el.value===''),'Legend resets incompatible subtype '+width);
            await page.screenshot({path:dir+'/task5-directory-'+width+'.png'});
            const reset = await page.$eval('.library-legend-filter',el=>el.href); await page.goto(reset,{waitUntil:'networkidle2'});
            ok((await page.$eval('#libraries-map-data',el=>JSON.parse(el.textContent))).length===original.length,'All-types legend resets map '+width);
        }
        await page.goto(base+'/libraries?type_id=999999&per_page=10');
        ok(await page.$eval('#libraries-map-data',el=>JSON.parse(el.textContent).length===0),'Unknown type gives empty map');
        await page.goto(base+'/libraries?type_id[]=1&subtype_id[]=2&q[]=x');
        ok((await page.$$('#libraries-profile-data')).length===1,'Malformed array query handled without PHP error');
        await page.goto(base+'/libraries');
        const action = await page.$eval('form[action*="/libraries/toggle/"]',el=>el.action);
        const denied = await page.evaluate(async action=>({get:(await fetch(action)).status,post:(await fetch(action,{method:'POST',body:new URLSearchParams({})})).status}),action);
        ok(denied.get===405&&denied.post===403,'Status action rejects GET and missing CSRF');
        await page.goto(base+'/iplm');
        ok((await page.content()).includes('data tahun 2026'),'Admin sees corrected 2026 period');
        await page.goto(base+'/logout'); await page.goto(base+'/login');
        await page.type('[name=identifier]','test-school-b'); await page.type('[name=password]',fixture.password);
        await Promise.all([page.waitForNavigation(),page.click('button[type=submit]')]);
        const local = await page.goto(base+'/libraries'); ok(local.status()===403,'Local account cannot read county directory/profile JSON');
        ok(errors.length===0,'No JavaScript runtime errors');
        fs.writeFileSync(dir+'/task5-browser-results.json',JSON.stringify({checks,errors}));
        console.log(checks+' browser checks passed.');
    } finally { await browser.close(); }
})().catch(e=>{console.error(e);process.exit(1);});
