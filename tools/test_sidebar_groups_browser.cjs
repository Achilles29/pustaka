// Only fake accounts in the isolated loopback fixture; never logs credentials.
const fs = require('fs');
const puppeteer = require('/tmp/finance2-studio-work/node_modules/puppeteer-core');
(async () => {
  const dir = process.argv[2];
  if (!/^\/tmp\/pustaka_network_test_[a-zA-Z0-9_]+$/.test(dir)) throw Error('Private test runtime required');
  const fixture = JSON.parse(fs.readFileSync(dir + '/fixtures.json'));
  const base = 'http://127.0.0.1:8797';
  const browser = await puppeteer.launch({executablePath:'/usr/bin/google-chrome', headless:true, args:['--no-sandbox']});
  const evidence = [];
  try {
    const page = await browser.newPage(), errors = [];
    page.on('pageerror', error => errors.push(error.message));
    for (const role of ['school', 'central']) {
      await page.goto(base + '/logout');
      await page.goto(base + '/login');
      await page.type('[name=identifier]', role === 'school' ? 'test-school-a' : 'test-central');
      await page.type('[name=password]', role === 'school' ? fixture.changed_password : fixture.password);
      await Promise.all([page.waitForNavigation({waitUntil:'networkidle2'}), page.click('button[type=submit]')]);
      const paths = role === 'school' ? [
        ['library-workspace', 'Dashboard'],
        ['library-workspace/edit/books', 'Katalog Buku'],
        ['library-services/cards', 'Kartu Anggota'],
        ['library-services/exchange', 'Pinjam Antarlembaga'],
        ['library-services/kiosk', 'Buku Tamu Mandiri'],
        ['library-workspace/account', 'Ganti Password']
      ] : [['catalog/loans', 'Transaksi Peminjaman'], ['library-services/stock', 'Stok Opname']];
      for (const width of [1440, 390]) {
        await page.setViewport({width, height:960});
        for (const [path, title] of paths) {
          const response = await page.goto(base + '/' + path, {waitUntil:'networkidle2'});
          if (response.status() !== 200) throw Error('Page failed: ' + path);
          const state = await page.evaluate(() => ({
            roots: document.querySelectorAll('.admin-menu > li').length,
            leaves: document.querySelectorAll('.admin-menu-item:not(.has-children) > a').length,
            active: [...document.querySelectorAll('.admin-menu-item.active:not(.has-children) > a')].map(a => a.textContent.trim()),
            expanded: document.querySelectorAll('.admin-submenu.show').length,
            groups: [...document.querySelectorAll('.admin-menu-toggle')].every(a => !!document.getElementById(a.getAttribute('aria-controls'))),
            overflow: document.body.scrollWidth > innerWidth
          }));
          if (state.roots !== (role === 'school' ? 7 : 13) || state.active.length !== 1 || state.active[0] !== title || state.overflow || !state.groups) throw Error(path + ': ' + JSON.stringify(state));
          if (role === 'school' && state.leaves !== 20) throw Error('School leaf missing');
          if (state.expanded !== (path === 'library-workspace' ? 0 : 1)) throw Error('Wrong auto-expanded group: ' + path);
          if (width < 992) {
            await page.click('[data-bs-target="#sidebar-menu"]');
            await page.waitForFunction(() => document.getElementById('sidebar-menu').classList.contains('show'));
          }
          const toggle = path === 'library-workspace' ? '.admin-menu-toggle' : '.admin-menu-item.active.has-children > .admin-menu-toggle';
          const target = await page.$eval(toggle, a => a.getAttribute('data-bs-target'));
          if (path !== 'library-workspace') {
            await page.click(toggle);
            await page.waitForFunction(selector => !document.querySelector(selector).classList.contains('show') && !document.querySelector(selector).classList.contains('collapsing'), {}, target);
            if (await page.$eval(toggle, a => a.getAttribute('aria-expanded')) !== 'false') throw Error('Collapse ARIA not updated');
          }
          await page.click(toggle);
          await page.waitForFunction(selector => document.querySelector(selector).classList.contains('show'), {}, target);
          if (await page.$eval(toggle, a => a.getAttribute('aria-expanded')) !== 'true') throw Error('Expand ARIA not updated');
          if (path === 'library-workspace/edit/books' || path === 'catalog/loans') await page.screenshot({path:dir + '/sidebar-' + role + '-' + width + '.png', fullPage:true});
          evidence.push({role, width, path, ...state});
        }
      }
    }
    if (errors.length) throw Error(JSON.stringify(errors));
    fs.writeFileSync(dir + '/sidebar-browser-verification.json', JSON.stringify({errors, evidence}, null, 2));
    console.log('PASS ' + evidence.length + ' desktop/mobile layouts, group toggles, active leaves, ARIA, route grouping; no JS errors.');
  } finally { await browser.close(); }
})().catch(error => { console.error(error.message); process.exit(1); });
