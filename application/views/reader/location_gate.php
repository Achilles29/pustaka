<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tabler_css = 'https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css';
$tabler_icons_css = 'https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/tabler-icons.min.css';
$error_msg = trim((string) ($error_msg ?? ''));
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
	<link rel="stylesheet" href="<?= base_url('assets/css/pustaka-polish.css?v=20260810b'); ?>">
</head>
<body class="user-page">
	<header class="user-topbar user-topbar-app">
		<a class="public-brand" href="<?= base_url('user/dashboard'); ?>">
			<span class="brand-logo-shell"><img class="brand-logo" src="<?= base_url('img/logo-small.jpeg'); ?>" alt="Logo Kabupaten Rembang"></span>
			<span class="public-brand-text">Pustaka Digital Rembang</span>
		</a>
		<div class="btn-list">
			<a href="<?= base_url(); ?>" class="btn btn-outline-primary btn-sm"><i class="ti ti-home me-1"></i>Beranda</a>
			<a href="<?= base_url('katalog'); ?>" class="btn btn-outline-primary btn-sm"><i class="ti ti-search me-1"></i>Katalog</a>
			<a href="<?= base_url('agenda'); ?>" class="btn btn-outline-primary btn-sm"><i class="ti ti-calendar-event me-1"></i>Agenda</a>
			<a href="<?= base_url('user/dashboard'); ?>" class="btn btn-primary btn-sm"><i class="ti ti-id me-1"></i>Dashboard</a>
			<a href="<?= base_url('user/account'); ?>" class="btn btn-outline-primary btn-sm"><i class="ti ti-user-cog me-1"></i>Akun</a>
			<a href="<?= base_url('logout'); ?>" class="btn btn-outline-primary btn-sm"><i class="ti ti-logout me-1"></i>Logout</a>
		</div>
	</header>
	<nav class="member-bottom-nav" aria-label="Navigasi pemustaka">
		<a href="<?= base_url(); ?>"><i class="ti ti-home"></i><span>Beranda</span></a>
		<a href="<?= base_url('katalog'); ?>"><i class="ti ti-search"></i><span>Katalog</span></a>
		<a href="<?= base_url('agenda'); ?>"><i class="ti ti-calendar-event"></i><span>Agenda</span></a>
		<a href="<?= base_url('user/dashboard'); ?>"><i class="ti ti-id"></i><span>Dashboard</span></a>
		<a href="<?= base_url('user/account'); ?>"><i class="ti ti-user-cog"></i><span>Akun</span></a>
	</nav>
	<main class="reader-location-gate reader-location-gate-v2">
		<div class="container-tight">
			<div class="reader-gps-card">
				<div class="reader-gps-icon"><i class="ti ti-current-location"></i></div>
				<div class="section-kicker">Validasi Reader</div>
				<h1>Nyalakan GPS untuk mulai baca</h1>
				<p>Sistem akan mendeteksi posisi Anda otomatis. Jika berada di Pojok Baca atau perpustakaan terdaftar, buku langsung terbuka dan token tidak berkurang. Jika berada di luar zona, akses memakai token baca luar lokasi.</p>

				<?php if ($error_msg !== ''): ?>
					<div class="alert alert-danger reader-gps-alert">
						<i class="ti ti-alert-circle"></i>
						<span><?= html_escape($error_msg); ?></span>
					</div>
				<?php endif; ?>

				<div class="reader-gps-book">
					<div>
						<span>Buku</span>
						<strong><?= html_escape($asset['title'] ?: 'Aset Digital'); ?></strong>
					</div>
					<div>
						<span>Kebijakan</span>
						<strong>GPS zona baca + token luar zona</strong>
					</div>
				</div>

				<div class="reader-gps-flow">
					<div><i class="ti ti-map-pin-check"></i><span>Di zona layanan</span><strong>Token aman</strong></div>
					<div><i class="ti ti-route"></i><span>Di luar zona</span><strong>Token -1</strong></div>
					<div><i class="ti ti-shield-lock"></i><span>PDF aman</span><strong>Tanpa file publik</strong></div>
				</div>

				<div class="reader-gps-actions">
					<?= form_open(current_url(), ['id' => 'reader-location-form']); ?>
						<input type="hidden" name="lat" id="reader-latitude">
						<input type="hidden" name="lng" id="reader-longitude">
						<button type="button" class="btn btn-primary btn-lg w-100" id="reader-location-button">
							<i class="ti ti-current-location me-1"></i>Nyalakan GPS dan Mulai Baca
						</button>
					<?= form_close(); ?>
					<?= form_open(current_url(), ['id' => 'reader-external-form', 'class' => 'reader-external-form']); ?>
						<input type="hidden" name="external" value="1">
						<button type="submit" class="btn btn-outline-primary w-100">
							<i class="ti ti-world me-1"></i>Lanjut sebagai akses luar zona
						</button>
					<?= form_close(); ?>
					<a href="<?= base_url('user/dashboard'); ?>" class="btn btn-outline-secondary w-100">
						<i class="ti ti-arrow-left me-1"></i>Kembali ke Dashboard
					</a>
				</div>

				<div class="reading-gps-state" id="reader-location-state">GPS belum diminta.</div>
			</div>
		</div>
	</main>
	<script>
	document.addEventListener('DOMContentLoaded', function () {
		var button = document.getElementById('reader-location-button');
		var form = document.getElementById('reader-location-form');
		var externalForm = document.getElementById('reader-external-form');
		var lat = document.getElementById('reader-latitude');
		var lng = document.getElementById('reader-longitude');
		var state = document.getElementById('reader-location-state');
		function showExternalFallback() {
			if (externalForm) {
				externalForm.classList.add('is-visible');
			}
		}
		if (!button || !navigator.geolocation) {
			if (state) state.textContent = 'Browser tidak mendukung GPS. Gunakan akses luar zona jika token masih tersedia.';
			showExternalFallback();
			return;
		}
		button.addEventListener('click', function () {
			button.disabled = true;
			button.innerHTML = '<i class="ti ti-loader-2 me-1"></i>Membaca GPS...';
			if (state) state.textContent = 'Membaca koordinat presisi. Izinkan akses lokasi saat browser meminta izin.';
			navigator.geolocation.getCurrentPosition(function (position) {
				lat.value = position.coords.latitude.toFixed(7);
				lng.value = position.coords.longitude.toFixed(7);
				if (state) state.textContent = 'Lokasi terbaca. Sistem sedang mengecek zona baca...';
				form.submit();
			}, function (error) {
				button.disabled = false;
				button.innerHTML = '<i class="ti ti-current-location me-1"></i>Coba Baca GPS Lagi';
				if (state) {
					state.textContent = error && error.code === 1
						? 'Izin GPS ditolak. Aktifkan izin lokasi, atau lanjut sebagai akses luar zona jika ingin memakai token.'
						: 'GPS belum berhasil dibaca. Coba lagi di area terbuka, atau lanjut sebagai akses luar zona.';
				}
				showExternalFallback();
			}, { enableHighAccuracy: true, timeout: 14000, maximumAge: 0 });
		});
	});
	</script>
</body>
</html>
