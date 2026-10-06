<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tabler_css = 'https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css';
$tabler_icons_css = 'https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/tabler-icons.min.css';
$qr_url = ! empty($qr_token['token']) ? base_url('guestbook/checkin/' . rawurlencode($qr_token['token'])) : '';
$refresh_seconds = (int) ($qr_token['refresh_seconds'] ?? 60);
$purpose_options = [
	'Membaca di tempat',
	'Meminjam / mengembalikan buku',
	'Mencari referensi',
	'Mengikuti kegiatan literasi',
	'Kunjungan rombongan',
	'Layanan digital',
];
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= html_escape($title); ?></title>
	<link rel="icon" href="<?= base_url('img/favicon.ico'); ?>" type="image/x-icon">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Fraunces:opsz,wght@9..144,650;9..144,750;9..144,850&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="<?= $tabler_css; ?>">
	<link rel="stylesheet" href="<?= $tabler_icons_css; ?>">
	<link rel="stylesheet" href="<?= base_url('assets/css/pustaka.css'); ?>">
	<link rel="stylesheet" href="<?= base_url('assets/css/pustaka-polish.css?v=20260807d'); ?>">
	<link rel="stylesheet" href="<?= base_url('assets/css/guestbook-demographics.css?v=20260820b'); ?>">
	<style>
		.guestbook-welcome{position:fixed;z-index:1080;top:82px;left:50%;display:flex;align-items:center;gap:14px;width:min(620px,calc(100% - 28px));padding:17px 20px;border:1px solid #86d4ad;border-radius:16px;background:#ecfff5;color:#12633b;box-shadow:0 18px 55px rgba(20,84,54,.24);transform:translateX(-50%);animation:guestbook-welcome-in .3s ease-out}
		.guestbook-welcome>i{flex:none;font-size:2rem}.guestbook-welcome-copy{min-width:0;flex:1}.guestbook-welcome strong{display:block;font-size:1.05rem}.guestbook-welcome span{display:block;margin-top:3px;color:#357257;font-size:.78rem}.guestbook-welcome-close{flex:none;border:0;background:transparent;color:inherit;font-size:1.25rem;cursor:pointer}.guestbook-welcome-play{display:none;margin-top:8px;padding:5px 10px;border:1px solid #5bb98a;border-radius:8px;background:#fff;color:#12633b;font-size:.72rem;font-weight:800;cursor:pointer}.guestbook-welcome-play.is-visible{display:inline-flex;align-items:center;gap:5px}.guestbook-welcome.is-hiding{opacity:0;transform:translate(-50%,-12px);transition:.25s ease}
		@keyframes guestbook-welcome-in{from{opacity:0;transform:translate(-50%,-12px)}to{opacity:1;transform:translate(-50%,0)}}
	</style>
</head>
<body class="guestbook-page">
	<header class="user-topbar user-topbar-app">
		<a class="public-brand" href="<?= base_url(); ?>">
			<span class="brand-logo-shell"><img class="brand-logo" src="<?= base_url('img/logo-small.jpeg'); ?>" alt="Logo Kabupaten Rembang"></span>
			<span class="public-brand-text">Buku Tamu Perpustakaan</span>
		</a>
		<div class="btn-list">
			<a href="<?= base_url(); ?>" class="btn btn-outline-primary btn-sm"><i class="ti ti-home me-1"></i>Beranda</a>
			<a href="<?= base_url('login'); ?>" class="btn btn-primary btn-sm"><i class="ti ti-login me-1"></i>Masuk Member</a>
		</div>
	</header>

	<main class="guestbook-shell">
		<div class="container-xl">
			<?php if (! empty($success)): ?>
				<div class="guestbook-welcome" id="guestbook-welcome" role="status" aria-live="polite">
					<i class="ti ti-confetti"></i>
					<div class="guestbook-welcome-copy">
						<strong>Selamat Datang di Perpustakaan Daerah Kabupaten Rembang</strong>
						<span><?= html_escape($success); ?></span>
						<button class="guestbook-welcome-play" type="button" id="guestbook-welcome-play"><i class="ti ti-volume"></i> Putar suara</button>
					</div>
					<button class="guestbook-welcome-close" type="button" id="guestbook-welcome-close" aria-label="Tutup notifikasi"><i class="ti ti-x"></i></button>
				</div>
				<audio id="guestbook-welcome-audio" preload="auto" playsinline>
					<source src="<?= base_url('assets/uploads/audio/selamat-datang-perpustakaan-rembang.mp3'); ?>" type="audio/mpeg">
				</audio>
			<?php endif; ?>
			<?php if (! empty($error)): ?><div class="alert alert-danger"><?= html_escape($error); ?></div><?php endif; ?>

			<div class="guestbook-grid">
				<section class="guestbook-qr-panel">
					<div class="section-kicker">Check-in Member</div>
					<h1>Scan QR dari HP member</h1>
					<p>QR berubah otomatis setiap <?= number_format($refresh_seconds, 0, ',', '.'); ?> detik. Member yang sudah login akan langsung tercatat sebagai kunjungan fisik.</p>
					<div class="guestbook-qr-box">
						<canvas id="guestbook-qr" width="280" height="280" aria-label="QR check-in"></canvas>
					</div>
					<div class="guestbook-token">
						<span>Kode aktif</span>
						<strong><?= html_escape($qr_token['token'] ?? '-'); ?></strong>
					</div>
					<div class="progress progress-sm mt-3">
						<div class="progress-bar" id="qr-countdown-bar" style="width: 100%"></div>
					</div>
				</section>

				<section class="guestbook-form-panel">
					<div class="guestbook-tabs" role="tablist">
						<button class="active" type="button" data-tab-target="guest-tab"><i class="ti ti-user"></i>Pengunjung</button>
						<button type="button" data-tab-target="member-tab"><i class="ti ti-id"></i>Member</button>
					</div>

					<div class="guestbook-tab-panel active" id="guest-tab">
						<form method="post" action="<?= base_url('guestbook/store-guest'); ?>">
							<input type="hidden" name="library_id" value="<?= (int) $library_id; ?>">
							<div class="row g-3">
								<div class="col-md-7">
									<label class="form-label">Nama Pengunjung / Penanggung Jawab</label>
									<input type="text" class="form-control" name="visitor_name" required>
								</div>
								<div class="col-md-5">
									<label class="form-label">Jumlah Orang</label>
									<input type="number" class="form-control" name="visitor_count" id="guest-visitor-count" value="1" readonly required>
									<div class="form-hint">Dihitung otomatis dari komposisi.</div>
								</div>
								<div class="col-md-6">
									<label class="form-label">Nama Rombongan / Instansi</label>
									<input type="text" class="form-control" name="group_name" placeholder="Opsional">
								</div>
								<div class="col-md-6">
									<label class="form-label">Pemimpin Rombongan</label>
									<input type="text" class="form-control" name="group_leader_name" placeholder="Opsional">
								</div>
								<div class="col-12">
									<div class="d-flex align-items-center justify-content-between mb-2">
										<div><label class="form-label mb-0">Komposisi Pengunjung</label><div class="form-hint">Pisahkan baris bila gender, usia, pendidikan, atau pekerjaannya berbeda.</div></div>
										<button class="btn btn-outline-primary btn-sm" type="button" id="add-demographic-row"><i class="ti ti-plus me-1"></i>Tambah Komposisi</button>
									</div>
									<div id="demographic-rows" class="vstack gap-2">
										<div class="card card-sm demographic-row"><div class="card-body"><div class="row g-2 align-items-end">
											<div class="demographic-field demographic-gender"><label class="form-label">Jenis Kelamin</label><select class="form-select" name="demographic_gender[]" required><option value="male">Laki-laki</option><option value="female">Perempuan</option></select></div>
											<div class="demographic-field demographic-age"><label class="form-label">Kelompok Usia</label><select class="form-select" name="demographic_age_group[]" required><?php foreach(['0–6','7–12','13–15','16–18','19–24','25–44','45–59','60+'] as $age):?><option value="<?=html_escape($age);?>"><?=html_escape($age);?> tahun</option><?php endforeach;?></select></div>
											<div class="demographic-field demographic-education"><label class="form-label">Pendidikan</label><select class="form-select" name="demographic_education[]" required><option value="">Pilih pendidikan</option><?php foreach($education_options as $option):?><option value="<?=html_escape($option['id'].'|'.$option['name']);?>"><?=html_escape($option['name']);?></option><?php endforeach;?></select></div>
											<div class="demographic-field demographic-profession"><label class="form-label">Pekerjaan</label><select class="form-select" name="demographic_profession[]" required><option value="">Pilih pekerjaan</option><?php foreach($profession_options as $option):?><option value="<?=html_escape($option['id'].'|'.$option['name']);?>"><?=html_escape($option['name']);?></option><?php endforeach;?></select></div>
											<div class="demographic-field demographic-total"><label class="form-label">Jumlah</label><div class="input-group"><input type="number" class="form-control demographic-count" name="demographic_count[]" value="1" min="1" max="1000" required><button class="btn btn-outline-danger remove-demographic-row" type="button" title="Hapus"><i class="ti ti-trash"></i></button></div></div>
										</div></div></div>
									</div>
								</div>
								<div class="col-md-6">
									<label class="form-label">Tujuan</label>
									<select class="form-select" name="purpose_label">
										<?php foreach ($purpose_options as $purpose): ?>
											<option value="<?= html_escape($purpose); ?>"><?= html_escape($purpose); ?></option>
										<?php endforeach; ?>
									</select>
								</div>
								<div class="col-md-6">
									<label class="form-label">Asal / Alamat</label>
									<input type="text" class="form-control" name="address" placeholder="Desa, sekolah, instansi">
								</div>
								<div class="col-12">
									<label class="form-label">Catatan</label>
									<textarea class="form-control" name="information" rows="2" placeholder="Opsional"></textarea>
								</div>
							</div>
							<button class="btn btn-primary mt-3" type="submit"><i class="ti ti-check me-1"></i>Catat Kunjungan</button>
						</form>
					</div>

					<div class="guestbook-tab-panel" id="member-tab">
						<form method="post" action="<?= base_url('guestbook/store-member'); ?>">
							<input type="hidden" name="library_id" value="<?= (int) $library_id; ?>">
							<input type="hidden" name="member_id" id="guestbook-member-id">
							<div class="row g-3">
								<div class="col-md-7">
									<label class="form-label">Cari NIK / Nomor Anggota / Nama</label>
					<input type="text" class="form-control" name="identifier" id="guestbook-member-search" required autocomplete="off" placeholder="Ketik minimal 4 karakter">
									<div class="guestbook-search-state" id="guestbook-member-search-state">Pilih member dari hasil pencarian.</div>
								</div>
								<div class="col-md-5">
									<label class="form-label">Jumlah Orang</label>
									<input type="number" class="form-control" name="visitor_count" value="1" min="1" max="1000" required>
								</div>
								<div class="col-12">
									<div class="guestbook-member-results" id="guestbook-member-results" aria-live="polite"></div>
								</div>
								<div class="col-md-6">
									<label class="form-label">Tujuan</label>
									<select class="form-select" name="purpose_label">
										<?php foreach ($purpose_options as $purpose): ?>
											<option value="<?= html_escape($purpose); ?>"><?= html_escape($purpose); ?></option>
										<?php endforeach; ?>
									</select>
								</div>
								<div class="col-md-6">
									<label class="form-label">Catatan</label>
									<input type="text" class="form-control" name="information" placeholder="Opsional">
								</div>
							</div>
							<button class="btn btn-primary mt-3" type="submit" id="guestbook-member-submit"><i class="ti ti-id-badge-2 me-1"></i>Catat Member</button>
						</form>
					</div>
				</section>
			</div>
		</div>
	</main>

	<script src="https://cdn.jsdelivr.net/npm/qrious@4.0.2/dist/qrious.min.js"></script>
	<script>
	document.addEventListener('DOMContentLoaded', function () {
		var welcome = document.getElementById('guestbook-welcome');
		var welcomeClose = document.getElementById('guestbook-welcome-close');
		var welcomePlay = document.getElementById('guestbook-welcome-play');
		var welcomeAudio = document.getElementById('guestbook-welcome-audio');
		function closeWelcome() {
			if (! welcome) return;
			welcome.classList.add('is-hiding');
			window.setTimeout(function () { if (welcome) welcome.remove(); }, 260);
		}
		if (welcomeClose) welcomeClose.addEventListener('click', closeWelcome);
		if (welcome) {
			window.setTimeout(closeWelcome, 6500);
			if (welcomeAudio) {
				function playWelcomeAudio() {
					welcomeAudio.currentTime = 0;
					var playback = welcomeAudio.play();
					if (playback && typeof playback.then === 'function') {
						playback.then(function () {
							if (welcomePlay) welcomePlay.classList.remove('is-visible');
						}).catch(function () {
							if (welcomePlay) welcomePlay.classList.add('is-visible');
						});
					}
				}
				if (welcomePlay) welcomePlay.addEventListener('click', playWelcomeAudio);
				window.setTimeout(playWelcomeAudio, 250);
			}
		}

		var demographicRows = document.getElementById('demographic-rows');
		var visitorCount = document.getElementById('guest-visitor-count');
		var addDemographicRow = document.getElementById('add-demographic-row');
		function updateVisitorCount() { var total=0;if(demographicRows)demographicRows.querySelectorAll('.demographic-count').forEach(function(input){total+=Math.max(0,parseInt(input.value||'0',10));});if(visitorCount)visitorCount.value=Math.max(1,total); }
		if(addDemographicRow&&demographicRows){addDemographicRow.addEventListener('click',function(){var first=demographicRows.querySelector('.demographic-row');if(!first)return;var clone=first.cloneNode(true);clone.querySelectorAll('select').forEach(function(select){select.selectedIndex=0;});var count=clone.querySelector('.demographic-count');if(count)count.value=1;demographicRows.appendChild(clone);updateVisitorCount();});demographicRows.addEventListener('input',updateVisitorCount);demographicRows.addEventListener('click',function(event){var remove=event.target.closest('.remove-demographic-row');if(!remove)return;if(demographicRows.querySelectorAll('.demographic-row').length===1)return;remove.closest('.demographic-row').remove();updateVisitorCount();});updateVisitorCount();}
		var qrValue = <?= json_encode($qr_url, JSON_UNESCAPED_SLASHES); ?>;
		if (qrValue && window.QRious) {
			new QRious({
				element: document.getElementById('guestbook-qr'),
				value: qrValue,
				size: 280,
				level: 'H',
				background: '#ffffff',
				foreground: '#061a40'
			});
		}

		var seconds = <?= max(15, $refresh_seconds); ?>;
		var remaining = seconds;
		var bar = document.getElementById('qr-countdown-bar');
		window.setInterval(function () {
			remaining -= 1;
			if (bar) {
				bar.style.width = Math.max(0, (remaining / seconds) * 100) + '%';
			}
			if (remaining <= 0) {
				window.location.reload();
			}
		}, 1000);

		document.querySelectorAll('[data-tab-target]').forEach(function (button) {
			button.addEventListener('click', function () {
				document.querySelectorAll('[data-tab-target]').forEach(function (item) { item.classList.remove('active'); });
				document.querySelectorAll('.guestbook-tab-panel').forEach(function (panel) { panel.classList.remove('active'); });
				button.classList.add('active');
				document.getElementById(button.getAttribute('data-tab-target')).classList.add('active');
			});
		});

		var memberSearch = document.getElementById('guestbook-member-search');
		var memberId = document.getElementById('guestbook-member-id');
		var memberResults = document.getElementById('guestbook-member-results');
		var memberState = document.getElementById('guestbook-member-search-state');
		var memberSubmit = document.getElementById('guestbook-member-submit');
		var searchTimer = null;
		var searchController = null;
		var searchUrl = <?= json_encode(base_url('guestbook/search-members'), JSON_UNESCAPED_SLASHES); ?>;

		function escapeHtml(value) {
			return String(value || '').replace(/[&<>"']/g, function (char) {
				return ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'})[char];
			});
		}

		function setMemberState(text) {
			if (memberState) memberState.textContent = text;
		}

		function clearMemberSelection() {
			if (memberId) memberId.value = '';
			if (memberResults) {
				memberResults.querySelectorAll('.guestbook-member-option').forEach(function (item) {
					item.classList.remove('is-selected');
				});
			}
		}

		function renderMemberResults(items) {
			if (! memberResults) return;
			if (! items.length) {
				memberResults.innerHTML = '<div class="member-mini-empty">Member tidak ditemukan. Coba nama, NIK, atau nomor anggota lain.</div>';
				setMemberState('Tidak ada hasil.');
				return;
			}

			memberResults.innerHTML = items.map(function (item) {
				var number = item.identity_number || item.member_no || '-';
				var location = item.location ? '<span>' + escapeHtml(item.location) + '</span>' : '';
				return '<button type="button" class="guestbook-member-option" data-member-id="' + item.id + '" data-label="' + escapeHtml(item.full_name) + '" data-identifier="' + escapeHtml(number) + '">' +
					'<strong>' + escapeHtml(item.full_name) + '<span class="guestbook-member-status ' + escapeHtml(item.status) + '">' + escapeHtml(item.status) + '</span></strong>' +
					'<span>No. Anggota: ' + escapeHtml(item.member_no || '-') + '</span>' +
					'<code>' + escapeHtml(number) + '</code>' +
					location +
				'</button>';
			}).join('');
			setMemberState('Pilih salah satu member dari hasil pencarian.');
		}

		function searchMembers(keyword) {
			clearMemberSelection();
			if (! memberResults || keyword.length < 4) {
				if (memberResults) memberResults.innerHTML = '';
				setMemberState('Ketik minimal 4 karakter untuk mencari.');
				return;
			}

			if (searchController) searchController.abort();
			searchController = new AbortController();
			setMemberState('Mencari member...');
			fetch(searchUrl + '?q=' + encodeURIComponent(keyword), {
				headers: {'X-Requested-With': 'XMLHttpRequest'},
				signal: searchController.signal
			})
				.then(function (response) { return response.json(); })
				.then(function (payload) { renderMemberResults(payload.items || []); })
				.catch(function (error) {
					if (error.name !== 'AbortError') {
						setMemberState('Pencarian gagal. Coba ulangi.');
					}
				});
		}

		if (memberSearch) {
			memberSearch.addEventListener('input', function () {
				window.clearTimeout(searchTimer);
				searchTimer = window.setTimeout(function () {
					searchMembers(memberSearch.value.trim());
				}, 260);
			});
		}

		if (memberResults) {
			memberResults.addEventListener('click', function (event) {
				var option = event.target.closest('.guestbook-member-option');
				if (! option) return;
				clearMemberSelection();
				option.classList.add('is-selected');
				memberId.value = option.getAttribute('data-member-id');
				memberSearch.value = option.getAttribute('data-label') + ' - ' + option.getAttribute('data-identifier');
				setMemberState('Member dipilih. Silakan catat kunjungan.');
			});
		}

		if (memberSubmit) {
			memberSubmit.closest('form').addEventListener('submit', function (event) {
				if (! memberId.value && memberSearch.value.trim().length < 4) {
					event.preventDefault();
					setMemberState('Cari dan pilih member terlebih dahulu.');
				}
			});
		}
	});
	</script>
</body>
</html>
