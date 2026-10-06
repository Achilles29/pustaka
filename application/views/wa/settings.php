<div class="page-header">
  <div class="container-xl">
    <div class="page-pretitle">WA Center · Infrastruktur Pengiriman</div>
    <h1 class="page-title">Pengaturan & Penautan WhatsApp</h1>
    <div class="text-secondary mt-1">Pantau nomor yang dipakai, aktifkan atau jedakan pengiriman, dan kelola penautan perangkat secara aman.</div>
  </div>
</div>

<div class="page-body"><div class="container-xl">
  <div class="row row-deck row-cards">
    <div class="col-lg-7">
      <div class="card h-100">
        <div class="card-header"><h3 class="card-title"><i class="ti ti-brand-whatsapp text-success me-2"></i>Status koneksi</h3><div id="engine-dot" class="badge bg-secondary-lt">Memeriksa engine…</div></div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-sm-6"><div class="text-secondary small text-uppercase fw-bold">Status WhatsApp</div><div id="wa-status" class="fs-2 fw-bold mt-1">—</div><div id="wa-status-help" class="text-secondary mt-1">Memeriksa koneksi WA Engine.</div></div>
            <div class="col-sm-6"><div class="text-secondary small text-uppercase fw-bold">Nomor tertaut</div><div id="wa-phone" class="fs-2 fw-bold mt-1">Belum ada</div><div class="text-secondary mt-1">Nomor ini menjadi pengirim notifikasi Pustaka.</div></div>
          </div>
          <div class="alert alert-info mt-4 mb-0"><div class="d-flex"><i class="ti ti-shield-lock fs-2 me-2"></i><div><strong>Privasi sesi.</strong><br><span class="text-secondary">QR hanya dapat dibaca oleh admin yang berwenang. Token engine tersimpan di server dan koneksi engine hanya menerima akses lokal.</span></div></div></div>
        </div>
        <?php if(!empty($can_edit)):?><div class="card-footer d-flex flex-wrap gap-2">
          <button type="button" class="btn btn-success" id="btn-enable"><i class="ti ti-player-play me-1"></i>Aktifkan WA</button>
          <button type="button" class="btn btn-outline-warning" id="btn-disable"><i class="ti ti-player-pause me-1"></i>Nonaktifkan sementara</button>
          <button type="button" class="btn btn-outline-danger ms-sm-auto" id="btn-unlink"><i class="ti ti-link-off me-1"></i>Hapus penautan</button>
        </div><?php endif;?>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="card h-100">
        <div class="card-header"><h3 class="card-title"><i class="ti ti-qrcode me-2"></i>QR penautan</h3><button class="btn btn-sm btn-outline-primary" type="button" id="load-qr"><i class="ti ti-refresh me-1"></i>Muat ulang</button></div>
        <div class="card-body text-center d-flex flex-column justify-content-center">
          <div id="wa-qr" class="d-inline-flex justify-content-center mx-auto rounded bg-white p-2"></div>
          <div id="wa-qr-help" class="text-secondary small mt-3">Jika belum tertaut, aktifkan WA lalu scan QR dari WhatsApp → Perangkat tertaut.</div>
        </div>
      </div>
    </div>
  </div>

  <div class="card mt-4">
	<?=form_open('wa/settings')?><input type="hidden" name="form_mode" value="automation"?><div class="card-header"><h3 class="card-title"><i class="ti ti-bell-automation me-2"></i>Aturan notifikasi peminjaman</h3><div class="card-actions text-secondary small">Dijalankan terjadwal setiap 15 menit</div></div><div class="card-body"><div class="row g-3 align-items-center">
		<div class="col-md-5"><label class="form-check form-switch mb-0"><input class="form-check-input" type="checkbox" name="due_reminder_enabled" value="1" <?=!empty($automation['due_reminder_enabled'])?'checked':''?> <?=empty($can_edit)?'disabled':''?>><span class="form-check-label"><strong>Pengingat H-1 jatuh tempo</strong><small class="d-block text-secondary">Mengirim satu pesan pada sehari sebelum batas pengembalian.</small></span></label></div>
		<div class="col-md-5"><label class="form-check form-switch mb-0"><input class="form-check-input" type="checkbox" name="overdue_auto_enabled" value="1" <?=!empty($automation['overdue_auto_enabled'])?'checked':''?> <?=empty($can_edit)?'disabled':''?>><span class="form-check-label"><strong>Keterlambatan otomatis</strong><small class="d-block text-secondary">Pesan dapat dikirim ulang sesuai interval yang Anda tentukan.</small></span></label></div>
		<div class="col-md-2"><label class="form-label">Ulang tiap</label><div class="input-group"><input class="form-control" type="number" min="1" max="30" name="overdue_repeat_days" value="<?=max(1,(int)($automation['overdue_repeat_days']??3))?>" <?=empty($can_edit)?'readonly':''?>><span class="input-group-text">hari</span></div></div>
	</div><div class="form-hint mt-3"><i class="ti ti-info-circle me-1"></i>Pesan keterlambatan juga dapat dikirim manual dari <a href="<?=base_url('catalog/loans?status=overdue')?>">Transaksi Peminjaman → Terlambat</a>. Riwayat tidak dikirim ganda pada jadwal yang sama.</div></div><?php if(!empty($can_edit)):?><div class="card-footer text-end"><button class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Simpan aturan otomatis</button></div><?php endif;?><?=form_close()?>
  </div>

  <div class="card mt-4">
    <div class="card-header"><h3 class="card-title"><i class="ti ti-adjustments-horizontal me-2"></i>Konfigurasi WA Engine</h3><div class="card-actions text-secondary small">Untuk administrator server</div></div>
	<?=form_open('wa/settings')?><input type="hidden" name="form_mode" value="engine"><div class="card-body"><div class="row g-3">
      <div class="col-md-5"><label class="form-label">URL engine internal</label><input class="form-control font-monospace" name="bot_api_url" value="<?=html_escape($session['bot_api_url']??'http://127.0.0.1:3071')?>" <?=empty($can_edit)?'readonly':''?>><div class="form-hint">Umumnya <code>http://127.0.0.1:3071</code>; jangan dibuka ke internet.</div></div>
      <div class="col-md-4"><label class="form-label">Token rahasia engine</label><input class="form-control font-monospace" type="password" name="bot_api_token" value="<?=html_escape($session['bot_api_token']??'')?>" <?=empty($can_edit)?'readonly':''?>><div class="form-hint">Harus sama dengan file <code>wa-engine/.env</code>.</div></div>
      <div class="col-md-3"><label class="form-label">Path Node.js <span class="text-secondary">(opsional)</span></label><input class="form-control font-monospace" name="node_path" value="<?=html_escape($session['node_path']??'')?>" <?=empty($can_edit)?'readonly':''?>><div class="form-hint">Hanya catatan konfigurasi server.</div></div>
    </div></div><?php if(!empty($can_edit)):?><div class="card-footer text-end"><button class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Simpan konfigurasi</button></div><?php endif;?><?=form_close()?>
  </div>
</div></div>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
(() => {
  const urls={status:'<?=base_url('wa/api/status')?>',qr:'<?=base_url('wa/api/qr')?>',control:'<?=base_url('wa/api/control')?>'};
  const statusEl=document.getElementById('wa-status'),helpEl=document.getElementById('wa-status-help'),phoneEl=document.getElementById('wa-phone'),dotEl=document.getElementById('engine-dot'),qrEl=document.getElementById('wa-qr'),qrHelp=document.getElementById('wa-qr-help');
  const labels={CONNECTED:'Terhubung',WAITING_QR:'Menunggu scan QR',DISCONNECTED:'Belum terhubung',DISABLED:'Dinonaktifkan',CONNECTING:'Menghubungkan'};
  function render(d){const state=d.status||'DISCONNECTED';statusEl.textContent=labels[state]||state;phoneEl.textContent=d.phone||'Belum ada';helpEl.textContent=state==='CONNECTED'?'WA siap mengirim pesan dari antrian.':state==='WAITING_QR'?'Buka WhatsApp pada ponsel lalu scan QR di sebelah kanan.':state==='DISABLED'?'Pengiriman dihentikan. Aktifkan kembali saat ingin menggunakan WA.':'Engine aktif, tetapi belum ada sesi WA yang terhubung.';dotEl.textContent=d.ok?'Engine aktif':'Engine tidak merespons';dotEl.className='badge '+(d.ok?'bg-success-lt':'bg-danger-lt');}
  async function loadStatus(){try{const d=await fetch(urls.status,{cache:'no-store'}).then(r=>r.json());render(d);return d;}catch(e){render({ok:false,status:'DISCONNECTED'});return null;}}
  async function loadQr(){try{const d=await fetch(urls.qr,{cache:'no-store'}).then(r=>r.json());render(d);qrEl.innerHTML='';if(d.qr){new QRCode(qrEl,{text:d.qr,width:218,height:218,correctLevel:QRCode.CorrectLevel.M});qrHelp.textContent='Scan QR dari WhatsApp → menu Perangkat tertaut → Tautkan perangkat.';}else if(d.status==='CONNECTED'){qrHelp.textContent='Nomor WhatsApp sudah tertaut. QR tidak diperlukan.';}else if(d.status==='DISABLED'){qrHelp.textContent='WA sedang dinonaktifkan. Klik “Aktifkan WA” untuk membuat QR baru.';}else{qrHelp.textContent='QR sedang disiapkan. Muat ulang beberapa saat lagi.';}}catch(e){qrHelp.textContent='QR tidak dapat dimuat. Periksa koneksi WA Engine.';}}
  async function control(action,question){if(question&&!window.confirm(question))return;const buttons=document.querySelectorAll('#btn-enable,#btn-disable,#btn-unlink');buttons.forEach(b=>b&&(b.disabled=true));try{const d=await fetch(urls.control,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action})}).then(r=>r.json());if(!d.ok)throw new Error(d.message||'Aksi gagal diproses.');await loadStatus();setTimeout(loadQr,600);if(d.message)alert(d.message);}catch(e){alert(e.message||'WA Engine tidak dapat dihubungi.');}finally{buttons.forEach(b=>b&&(b.disabled=false));}}
  document.getElementById('load-qr').addEventListener('click',loadQr);
  const enable=document.getElementById('btn-enable'),disable=document.getElementById('btn-disable'),unlink=document.getElementById('btn-unlink');
  if(enable)enable.addEventListener('click',()=>control('enable','Aktifkan WA Center dan mulai siapkan sesi WhatsApp?'));
  if(disable)disable.addEventListener('click',()=>control('disable','Nonaktifkan pengiriman WA sementara? Penautan nomor tetap disimpan.'));
  if(unlink)unlink.addEventListener('click',()=>control('unlink','Hapus penautan nomor WhatsApp? Anda perlu scan QR lagi sebelum dapat mengirim pesan.'));
  loadStatus().then(loadQr);setInterval(loadStatus,10000);
})();
</script>
