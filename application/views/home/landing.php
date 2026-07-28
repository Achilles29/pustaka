<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tabler_css = 'https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css';
$map_json = json_encode($map_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= html_escape($title); ?></title>
	<link rel="stylesheet" href="<?= $tabler_css; ?>">
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
	<link rel="stylesheet" href="<?= base_url('assets/css/pustaka.css'); ?>">
</head>
<body class="public-page">
	<header class="public-nav">
		<a class="public-brand" href="<?= base_url(); ?>">
			<span class="brand-mark">PR</span>
			<span>Pustaka Digital Rembang</span>
		</a>
		<nav class="public-links">
			<a href="#katalog">Katalog</a>
			<a href="#jejaring">Jejaring</a>
			<a href="<?= base_url('login'); ?>" class="btn btn-primary btn-sm">Masuk</a>
		</nav>
	</header>

	<main>
		<section class="public-hero">
			<div id="public-map" class="public-map"></div>
			<div class="public-hero-copy">
				<p class="eyebrow">Perpustakaan digital terpadu Kabupaten Rembang</p>
				<h1>Akses katalog, anggota, event, dan pojok baca digital dalam satu jaringan.</h1>
				<p class="lead">Portal publik untuk menemukan koleksi dan titik layanan perpustakaan. Admin mengelola data dari panel khusus, pemustaka masuk ke dashboard layanan pribadi.</p>
				<div class="btn-list">
					<a href="<?= base_url('login'); ?>" class="btn btn-primary">Masuk Aplikasi</a>
					<a href="#jejaring" class="btn btn-outline-light">Lihat Jejaring</a>
				</div>
			</div>
		</section>

		<section class="public-section" id="katalog">
			<div class="container-xl">
				<div class="row g-4 align-items-end">
					<div class="col-lg-6">
						<div class="section-kicker">Data acuan INLISLite</div>
						<h2>Katalog lama tetap menjadi sumber, aplikasi baru memakai database bersih.</h2>
					</div>
					<div class="col-lg-6">
						<div class="public-stat-grid">
							<div class="public-stat">
								<div class="h1"><?= number_format($source_counts['catalogs'], 0, ',', '.'); ?></div>
								<div>Katalog</div>
							</div>
							<div class="public-stat">
								<div class="h1"><?= number_format($source_counts['collections'], 0, ',', '.'); ?></div>
								<div>Eksemplar</div>
							</div>
							<div class="public-stat">
								<div class="h1"><?= number_format($source_counts['members'], 0, ',', '.'); ?></div>
								<div>Anggota</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>

		<section class="public-section public-section-muted" id="jejaring">
			<div class="container-xl">
				<div class="row g-4">
					<div class="col-md-4">
						<h3>GIS Perpustakaan</h3>
						<p>Seluruh perpustakaan terdaftar memiliki profil, koordinat, radius layanan, dan galeri foto.</p>
					</div>
					<div class="col-md-4">
						<h3>Membership Digital</h3>
						<p>Pemustaka akan memiliki dashboard pribadi, kartu digital, kuota akses, dan riwayat layanan.</p>
					</div>
					<div class="col-md-4">
						<h3>Pojok Baca Digital</h3>
						<p>Akses koleksi dapat dikunci pada lokasi mitra tertentu dengan token, GPS, dan absensi ulang.</p>
					</div>
				</div>
			</div>
		</section>
	</main>

	<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
	<script>
	(function () {
		var points = <?= $map_json ?: '[]'; ?>;
		var map = L.map('public-map', {
			zoomControl: false,
			attributionControl: false,
			scrollWheelZoom: false,
			dragging: false,
			doubleClickZoom: false
		}).setView([-6.7071, 111.3502], 11);

		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18 }).addTo(map);

		points.forEach(function (point) {
			L.circleMarker([point.lat, point.lng], {
				radius: 8,
				color: '#ffffff',
				weight: 2,
				fillColor: point.color || '#0b6b86',
				fillOpacity: 1
			}).addTo(map);
		});
	})();
	</script>
</body>
</html>
