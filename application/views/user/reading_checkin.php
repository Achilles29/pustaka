<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tabler_css = 'https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css';
$tabler_icons_css = 'https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/tabler-icons.min.css';
$unit_labels = ['minutes' => 'menit', 'pages' => 'halaman', 'books' => 'buku'];
$status_labels = ['active' => 'Aktif', 'used' => 'Terpakai', 'expired' => 'Kedaluwarsa', 'revoked' => 'Dicabut'];
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
			<a href="<?= base_url('user/dashboard'); ?>" class="btn btn-outline-primary btn-sm"><i class="ti ti-id me-1"></i>Dashboard</a>
			<a href="<?= base_url('logout'); ?>" class="btn btn-primary btn-sm"><i class="ti ti-logout me-1"></i>Logout</a>
		</div>
	</header>
	<nav class="member-bottom-nav" aria-label="Navigasi pemustaka">
		<a href="<?= base_url(); ?>"><i class="ti ti-home"></i><span>Beranda</span></a>
		<a href="<?= base_url('katalog'); ?>"><i class="ti ti-search"></i><span>Katalog</span></a>
		<a href="<?= base_url('user/dashboard'); ?>"><i class="ti ti-id"></i><span>Dashboard</span></a>
		<a href="<?= base_url('user/reading-checkin'); ?>" class="active"><i class="ti ti-map-pin-check"></i><span>Pojok</span></a>
	</nav>

	<main class="user-dashboard user-dashboard-v2">
		<div class="container-xl">
			<?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?= html_escape($this->session->flashdata('success')); ?></div><?php endif; ?>
			<?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?= html_escape($this->session->flashdata('error')); ?></div><?php endif; ?>

			<section class="reading-checkin-shell">
				<div class="reading-checkin-panel">
					<div class="section-kicker">Pojok Baca Digital</div>
					<h1>Check-in lokasi baca</h1>
					<p>Aktifkan GPS saat berada di radius titik Pojok Baca. Token harian dipakai untuk membuka koleksi yang dikunci lokasi dan kuota.</p>

					<?php if ($active_token): ?>
						<div class="reading-token-card">
							<div>
								<span>Token Aktif</span>
								<strong><?= html_escape($active_token['token']); ?></strong>
							</div>
							<div>
								<span>Titik</span>
								<strong><?= html_escape($active_token['point_name'] ?: '-'); ?></strong>
							</div>
							<div>
								<span>Sisa Kuota</span>
								<strong><?= number_format(max(0, (int) $active_token['quota_total'] - (int) $active_token['quota_used']), 0, ',', '.'); ?> <?= html_escape($unit_labels[$active_token['quota_unit']] ?? $active_token['quota_unit']); ?></strong>
							</div>
							<div>
								<span>Berlaku Sampai</span>
								<strong><?= html_escape($active_token['expires_at'] ?: '-'); ?></strong>
							</div>
						</div>
					<?php endif; ?>

					<?= form_open('user/reading-checkin/store', ['id' => 'reading-checkin-form', 'class' => 'reading-checkin-form']); ?>
						<input type="hidden" name="latitude" id="checkin-latitude">
						<input type="hidden" name="longitude" id="checkin-longitude">
						<button type="button" class="btn btn-primary btn-lg" id="checkin-location-button">
							<i class="ti ti-current-location me-1"></i>Ambil GPS dan Check-in
						</button>
						<div class="reading-gps-state" id="reading-gps-state">GPS belum dibaca.</div>
					<?= form_close(); ?>
				</div>

				<div class="reading-checkin-side">
					<div class="member-panel">
						<div class="member-panel-head">
							<div>
								<div class="section-kicker">Titik Aktif</div>
								<h3>Radius Layanan</h3>
							</div>
							<i class="ti ti-map-pin-star"></i>
						</div>
						<div class="member-mini-list">
							<?php if (empty($points)): ?><div class="member-mini-empty">Belum ada Pojok Baca aktif.</div><?php endif; ?>
							<?php foreach ($points as $point): ?>
								<div class="member-mini-item">
									<div>
										<strong><?= html_escape($point['name']); ?></strong>
										<span><?= html_escape($point['partner_name'] ?: ($point['library_name'] ?: '-')); ?> - radius <?= number_format((int) $point['radius_meters'], 0, ',', '.'); ?> m</span>
									</div>
									<code><?= number_format((int) $point['daily_quota'], 0, ',', '.'); ?> <?= html_escape($unit_labels[$point['quota_unit']] ?? $point['quota_unit']); ?></code>
								</div>
							<?php endforeach; ?>
						</div>
					</div>

					<div class="member-panel">
						<div class="member-panel-head">
							<div>
								<div class="section-kicker">Riwayat</div>
								<h3>Token Terakhir</h3>
							</div>
							<i class="ti ti-ticket"></i>
						</div>
						<div class="member-mini-list">
							<?php if (empty($tokens)): ?><div class="member-mini-empty">Belum ada token baca.</div><?php endif; ?>
							<?php foreach ($tokens as $token): ?>
								<div class="member-mini-item">
									<div>
										<strong><?= html_escape($token['point_name'] ?: '-'); ?></strong>
										<span><?= html_escape($token['issued_at']); ?> - <?= html_escape($status_labels[$token['status']] ?? $token['status']); ?></span>
									</div>
									<code><?= html_escape(substr($token['token'], 0, 8)); ?></code>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</section>
		</div>
	</main>

	<script>
	document.addEventListener('DOMContentLoaded', function () {
		var button = document.getElementById('checkin-location-button');
		var form = document.getElementById('reading-checkin-form');
		var lat = document.getElementById('checkin-latitude');
		var lng = document.getElementById('checkin-longitude');
		var state = document.getElementById('reading-gps-state');
		if (! button || ! form || ! navigator.geolocation) {
			if (state) state.textContent = 'Browser tidak mendukung GPS.';
			return;
		}

		button.addEventListener('click', function () {
			button.disabled = true;
			state.textContent = 'Membaca lokasi GPS...';
			navigator.geolocation.getCurrentPosition(function (position) {
				lat.value = position.coords.latitude.toFixed(7);
				lng.value = position.coords.longitude.toFixed(7);
				state.textContent = 'Lokasi terbaca: ' + lat.value + ', ' + lng.value + '. Mengirim check-in...';
				form.submit();
			}, function () {
				button.disabled = false;
				state.textContent = 'GPS gagal dibaca. Izinkan akses lokasi lalu coba lagi.';
			}, {
				enableHighAccuracy: true,
				timeout: 12000,
				maximumAge: 0
			});
		});
	});
	</script>
</body>
</html>
