<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tabler_css = 'https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css';
$tabler_icons_css = 'https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/tabler-icons.min.css';
$map_json = json_encode($map_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$url_path = function ($path) {
	$segments = explode('/', str_replace('\\', '/', trim((string) $path, '/')));
	return implode('/', array_map('rawurlencode', $segments));
};
$cover_url = function ($book) use ($url_path) {
	if (! empty($book['cover_local_path'])) {
		return base_url($url_path($book['cover_local_path']));
	}
	if (! empty($book['cover_source_path'])) {
		return base_url($url_path('assets/uploads/inlislite/source_mirror/' . $book['cover_source_path']));
	}
	return '';
};
$auth_user = (array) $this->session->userdata('auth_user');
$role_codes = array_map(function ($role) {
	return $role['code'] ?? '';
}, (array) $this->session->userdata('user_roles'));
$is_logged_in = ! empty($auth_user['id']);
$dashboard_url = (in_array('SUPERADMIN', $role_codes, true) || in_array('ADMIN', $role_codes, true)) ? base_url('admin') : base_url('user/dashboard');
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
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
	<link rel="stylesheet" href="<?= base_url('assets/css/pustaka.css'); ?>">
	<link rel="stylesheet" href="<?= base_url('assets/css/pustaka-polish.css?v=20260802j'); ?>">
</head>
<body class="public-page">
	<header class="public-nav">
		<a class="public-brand" href="<?= base_url(); ?>">
			<span class="brand-logo-shell">
				<img class="brand-logo" src="<?= base_url('img/logo-small.jpeg'); ?>" alt="Logo Kabupaten Rembang">
			</span>
			<span class="public-brand-text">Pustaka Digital Rembang</span>
		</a>
		<div class="public-agency-strip" aria-label="Logo instansi">
			<img src="<?= base_url('img/perpusnas.png'); ?>" alt="Logo Perpusnas">
		</div>
		<nav class="public-links">
			<a href="<?= base_url(); ?>">Beranda</a>
			<a href="<?= base_url('katalog'); ?>">Katalog</a>
			<a href="#jejaring">Jejaring</a>
			<?php if ($is_logged_in): ?>
				<a href="<?= $dashboard_url; ?>">Dashboard</a>
				<a href="<?= base_url('logout'); ?>" class="btn btn-primary btn-sm">Logout</a>
			<?php else: ?>
				<a href="<?= base_url('membership/register'); ?>">Daftar Member</a>
				<a href="<?= base_url('login'); ?>" class="btn btn-primary btn-sm">Masuk</a>
			<?php endif; ?>
		</nav>
	</header>
	<nav class="public-mobile-nav" aria-label="Navigasi publik">
		<a href="<?= base_url(); ?>" class="active"><i class="ti ti-home"></i><span>Beranda</span></a>
		<a href="<?= base_url('katalog'); ?>"><i class="ti ti-search"></i><span>Katalog</span></a>
		<?php if ($is_logged_in): ?>
			<a href="<?= $dashboard_url; ?>"><i class="ti ti-id"></i><span>Dashboard</span></a>
			<a href="<?= base_url('logout'); ?>"><i class="ti ti-logout"></i><span>Logout</span></a>
		<?php else: ?>
			<a href="<?= base_url('membership/register'); ?>"><i class="ti ti-user-plus"></i><span>Daftar</span></a>
			<a href="<?= base_url('login'); ?>"><i class="ti ti-login"></i><span>Masuk</span></a>
		<?php endif; ?>
	</nav>

	<main>
		<section class="public-hero public-hero-compact">
			<div id="public-map" class="public-map"></div>
			<div class="public-hero-copy">
				<div class="hero-logo-row">
					<span class="hero-logo-card">
						<img src="<?= base_url('img/logo-small.jpeg'); ?>" alt="Logo Kabupaten Rembang">
					</span>
					<span class="hero-logo-card hero-logo-card-wide">
						<img src="<?= base_url('img/perpusnas.png'); ?>" alt="Logo Perpusnas">
					</span>
				</div>
				<p class="eyebrow">Perpustakaan digital terpadu Kabupaten Rembang</p>
				<h1>Pustaka Digital Rembang</h1>
				<p class="lead">Portal koleksi, kartu anggota, peta perpustakaan, pojok baca, dan layanan literasi untuk seluruh jejaring perpustakaan di Kabupaten Rembang.</p>
				<div class="btn-list">
					<a href="<?= base_url('katalog'); ?>" class="btn btn-primary btn-lg"><i class="ti ti-search me-1"></i>Cari Buku</a>
					<?php if ($is_logged_in): ?>
						<a href="<?= $dashboard_url; ?>" class="btn btn-outline-light btn-lg"><i class="ti ti-id me-1"></i>Buka Dashboard</a>
					<?php else: ?>
						<a href="<?= base_url('membership/register'); ?>" class="btn btn-outline-light btn-lg"><i class="ti ti-user-plus me-1"></i>Daftar Member</a>
					<?php endif; ?>
					<a href="#jejaring" class="btn btn-outline-light btn-lg"><i class="ti ti-map-pin me-1"></i>Lihat Jejaring</a>
				</div>
				<div class="public-service-strip" aria-label="Layanan utama">
					<span><i class="ti ti-books"></i>Katalog terpadu</span>
					<span><i class="ti ti-id-badge-2"></i>Kartu digital</span>
					<span><i class="ti ti-map-pin-check"></i>Pojok baca</span>
				</div>
				<div class="public-hero-stats" aria-label="Ringkasan layanan">
					<div><strong><?= number_format($public_catalog_count, 0, ',', '.'); ?></strong><span>Katalog publik</span></div>
					<div><strong><?= number_format($source_counts['collections'], 0, ',', '.'); ?></strong><span>Eksemplar acuan</span></div>
					<div><strong><?= number_format(count($libraries), 0, ',', '.'); ?></strong><span>Titik layanan</span></div>
				</div>
			</div>
			<a href="#katalog" class="public-scroll-hint">Mulai jelajah</a>
		</section>

		<section class="public-section" id="katalog">
			<div class="container-xl">
				<div class="row g-4 align-items-end">
					<div class="col-lg-6">
						<div class="section-kicker">Katalog terpadu</div>
						<h2>Koleksi dari INLISLite sudah hadir sebagai layanan publik yang ringan dan siap dicari.</h2>
						<p class="text-secondary mt-3">Data katalog, eksemplar, cover, dan status OPAC dibaca dari database baru `pustaka`, sehingga portal publik tidak bergantung langsung pada aplikasi INLISLite lama.</p>
						<a href="<?= base_url('katalog'); ?>" class="btn btn-primary mt-3">
							<i class="ti ti-search me-1"></i>Buka Katalog Publik
						</a>
					</div>
					<div class="col-lg-6">
						<div class="public-stat-grid">
							<div class="public-stat">
								<div class="h1"><?= number_format($public_catalog_count, 0, ',', '.'); ?></div>
								<div>Katalog publik</div>
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
				<div class="landing-book-strip">
					<?php foreach ($catalog_preview as $book): ?>
						<?php $image = $cover_url($book); ?>
						<a href="<?= base_url('katalog/detail/' . (int) $book['id']); ?>" class="landing-book">
							<div class="landing-book-cover">
								<?php if ($image): ?>
									<img src="<?= html_escape($image); ?>" alt="Cover <?= html_escape($book['title']); ?>" loading="lazy">
								<?php else: ?>
									<i class="ti ti-book"></i>
								<?php endif; ?>
							</div>
							<div>
								<h3><?= html_escape($book['title']); ?></h3>
								<p><?= html_escape($book['statement_responsibility'] ?: 'Penulis belum tercatat'); ?></p>
							</div>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		</section>

		<section class="public-section public-section-muted" id="jejaring">
			<div class="container-xl">
				<div class="row g-4 align-items-center">
					<div class="col-lg-5">
						<div class="section-kicker">Jejaring Rembang</div>
						<h2>Perpustakaan sekolah, desa, komunitas, dan swasta berada dalam satu peta layanan.</h2>
						<p class="text-secondary mt-3">Setiap titik dapat membawa profil, koordinat, radius layanan, galeri foto, dan kelak kuota akses pojok baca digital berbasis lokasi.</p>
					</div>
					<div class="col-lg-7">
						<div class="public-network-grid">
							<div class="public-network-item">
								<i class="ti ti-map-2"></i>
								<h3>GIS Perpustakaan</h3>
								<p>Profil lokasi, radius layanan, dan galeri foto untuk seluruh perpustakaan terdaftar.</p>
							</div>
							<div class="public-network-item">
								<i class="ti ti-id-badge-2"></i>
								<h3>Membership Digital</h3>
								<p>Kartu anggota digital, QR verifikasi, status masa berlaku, dan riwayat layanan.</p>
							</div>
							<div class="public-network-item">
								<i class="ti ti-device-tablet-search"></i>
								<h3>Pojok Baca Digital</h3>
								<p>Akses koleksi berbasis titik GPS, token, kuota, dan absensi ulang di perpustakaan.</p>
							</div>
							<div class="public-network-item">
								<i class="ti ti-calendar-event"></i>
								<h3>Agenda Literasi</h3>
								<p>Ruang publikasi event perpustakaan daerah dan jejaring mitra literasi.</p>
							</div>
						</div>
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
		}).setView([-6.7750, 111.3900], 11);

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
