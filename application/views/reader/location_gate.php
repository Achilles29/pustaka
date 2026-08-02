<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tabler_css = 'https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css';
$tabler_icons_css = 'https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/tabler-icons.min.css';
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
	<link rel="stylesheet" href="<?= base_url('assets/css/pustaka-polish.css?v=20260802j'); ?>">
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
			<a href="<?= base_url('user/dashboard'); ?>" class="btn btn-primary btn-sm"><i class="ti ti-id me-1"></i>Dashboard</a>
		</div>
	</header>
	<nav class="member-bottom-nav" aria-label="Navigasi pemustaka">
		<a href="<?= base_url(); ?>"><i class="ti ti-home"></i><span>Beranda</span></a>
		<a href="<?= base_url('katalog'); ?>"><i class="ti ti-search"></i><span>Katalog</span></a>
		<a href="<?= base_url('user/dashboard'); ?>"><i class="ti ti-id"></i><span>Dashboard</span></a>
		<a href="<?= base_url('user/reading-checkin'); ?>"><i class="ti ti-map-pin-check"></i><span>Pojok</span></a>
	</nav>
	<main class="reader-location-gate">
		<div class="container-tight">
			<div class="registration-pending-card">
				<div class="pending-icon"><i class="ti ti-current-location"></i></div>
				<div>
					<div class="section-kicker">Reader lokasi</div>
					<h1>Validasi lokasi baca</h1>
					<p>Jika Anda berada di Pojok Baca atau perpustakaan, kuota token tidak berkurang. Jika akses dari luar lokasi, kuota akan berkurang satu unit.</p>
				</div>
				<div class="pending-account-grid">
					<div>
						<span>Buku</span>
						<strong><?= html_escape($asset['title'] ?: 'Aset Digital'); ?></strong>
					</div>
					<div>
						<span>Kebijakan</span>
						<strong>Kunci GPS + Token</strong>
					</div>
				</div>
				<div class="pending-actions">
					<?= form_open(current_url(), ['id' => 'reader-location-form', 'class' => 'd-inline-flex']); ?>
						<input type="hidden" name="lat" id="reader-latitude">
						<input type="hidden" name="lng" id="reader-longitude">
						<button type="button" class="btn btn-primary" id="reader-location-button"><i class="ti ti-current-location me-1"></i>Ambil GPS</button>
					<?= form_close(); ?>
					<?= form_open(current_url(), ['class' => 'd-inline-flex']); ?>
						<input type="hidden" name="external" value="1">
						<button type="submit" class="btn btn-outline-primary">Akses dari Luar Lokasi</button>
					<?= form_close(); ?>
				</div>
				<div class="reading-gps-state" id="reader-location-state">GPS belum dibaca.</div>
			</div>
		</div>
	</main>
	<script>
	document.addEventListener('DOMContentLoaded', function () {
		var button = document.getElementById('reader-location-button');
		var form = document.getElementById('reader-location-form');
		var lat = document.getElementById('reader-latitude');
		var lng = document.getElementById('reader-longitude');
		var state = document.getElementById('reader-location-state');
		if (!button || !navigator.geolocation) {
			if (state) state.textContent = 'Browser tidak mendukung GPS. Gunakan akses dari luar lokasi.';
			return;
		}
		button.addEventListener('click', function () {
			button.disabled = true;
			state.textContent = 'Membaca GPS...';
			navigator.geolocation.getCurrentPosition(function (position) {
				lat.value = position.coords.latitude.toFixed(7);
				lng.value = position.coords.longitude.toFixed(7);
				form.submit();
			}, function () {
				button.disabled = false;
				state.textContent = 'GPS gagal dibaca. Izinkan akses lokasi atau lanjut sebagai akses luar lokasi.';
			}, { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 });
		});
	});
	</script>
</body>
</html>
