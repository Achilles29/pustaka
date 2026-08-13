<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tabler_css = 'https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css';
$tabler_icons_css = 'https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/tabler-icons.min.css';
$url_path = function ($path) {
	$segments = explode('/', str_replace('\\', '/', trim((string) $path, '/')));
	return implode('/', array_map('rawurlencode', $segments));
};
$photo_url = '';
if (! empty($member['photo_local_path'])) {
	$photo_url = base_url($url_path($member['photo_local_path']));
} elseif (! empty($member['photo_source_path']) && strpos($member['photo_source_path'], 'assets/') === 0) {
	$photo_url = base_url($url_path($member['photo_source_path']));
} elseif (! empty($member['photo_source_path'])) {
	$photo_url = base_url($url_path('assets/uploads/inlislite/source_mirror/' . $member['photo_source_path']));
}
$is_blocked = ($member['card_status'] ?? '') === 'blocked';
$is_active = ($member['status'] ?? '') === 'active' && ! $is_blocked;
$status_label = $is_blocked ? 'Kartu diblokir' : ($is_active ? 'Anggota aktif' : ucfirst((string) ($member['status'] ?? 'belum aktif')));
$member_name = $member['full_name'] ?? ($current_user['full_name'] ?? $current_user['username']);
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
	<link rel="stylesheet" href="<?= base_url('assets/css/pustaka-polish.css?v=20260812h'); ?>">
</head>
<body class="user-page member-card-page">
	<header class="user-topbar user-topbar-app">
		<a class="public-brand" href="<?= base_url('user/dashboard'); ?>">
			<span class="brand-logo-shell"><img class="brand-logo" src="<?= base_url('img/logo-small.jpeg'); ?>" alt="Logo Kabupaten Rembang"></span>
			<span class="public-brand-text">Pustaka Digital Rembang</span>
		</a>
		<div class="btn-list">
			<a href="<?= base_url('user/dashboard'); ?>" class="btn btn-outline-primary btn-sm"><i class="ti ti-layout-dashboard me-1"></i>Dashboard</a>
			<a href="<?= base_url('user/account'); ?>" class="btn btn-outline-primary btn-sm"><i class="ti ti-user-cog me-1"></i>Akun</a>
			<a href="<?= base_url('logout'); ?>" class="btn btn-primary btn-sm"><i class="ti ti-logout me-1"></i>Logout</a>
		</div>
	</header>
	<nav class="member-bottom-nav" aria-label="Navigasi pemustaka">
		<a href="<?= base_url(); ?>"><i class="ti ti-home"></i><span>Beranda</span></a>
		<a href="<?= base_url('katalog'); ?>"><i class="ti ti-search"></i><span>Katalog</span></a>
		<a href="<?= base_url('user/dashboard'); ?>"><i class="ti ti-layout-dashboard"></i><span>Dashboard</span></a>
		<a href="<?= base_url('user/member-card'); ?>" class="active"><i class="ti ti-id-badge-2"></i><span>Kartu</span></a>
		<a href="<?= base_url('user/account'); ?>"><i class="ti ti-user-cog"></i><span>Akun</span></a>
		<a href="<?= base_url('logout'); ?>"><i class="ti ti-logout"></i><span>Keluar</span></a>
	</nav>

	<main class="member-card-main">
		<div class="container-xl">
			<div class="member-card-page-head">
				<div>
					<div class="section-kicker">Identitas pemustaka</div>
					<h1>Kartu Anggota Digital</h1>
					<p>Simpan kartu ini di perangkat Anda. Petugas dapat memverifikasi keaslian kartu melalui tautan aman.</p>
				</div>
				<div class="btn-list">
					<a class="btn btn-outline-primary" href="<?= html_escape($verify_url); ?>" target="_blank" rel="noopener"><i class="ti ti-shield-check me-1"></i>Verifikasi Kartu</a>
					<button class="btn btn-primary" type="button" onclick="window.print()"><i class="ti ti-printer me-1"></i>Cetak</button>
				</div>
			</div>

			<section class="member-card-display <?= $is_active ? '' : 'is-inactive'; ?>">
				<div class="member-card-orbit member-card-orbit-one"></div>
				<div class="member-card-orbit member-card-orbit-two"></div>
				<div class="member-card-grid"></div>
				<div class="member-card-display-top">
					<div class="member-card-brand">
						<span class="member-card-logo"><img src="<?= base_url('img/logo-small.jpeg'); ?>" alt="Logo Kabupaten Rembang"></span>
						<div><span>PEMERINTAH KABUPATEN REMBANG</span><strong>Pustaka Digital Rembang</strong></div>
					</div>
					<span class="member-card-live"><i class="ti ti-circle-check-filled"></i><?= html_escape($status_label); ?></span>
				</div>
				<div class="member-card-display-body">
					<div class="member-card-photo">
						<?php if ($photo_url): ?><img src="<?= html_escape($photo_url); ?>" alt="Foto <?= html_escape($member_name); ?>"><?php else: ?><i class="ti ti-user"></i><?php endif; ?>
					</div>
					<div class="member-card-person">
						<span>NAMA ANGGOTA</span>
						<h2><?= html_escape($member_name); ?></h2>
						<strong><?= html_escape($member['member_no'] ?: $current_user['username']); ?></strong>
						<p><?= html_escape($member['member_type_label'] ?: ($member['member_type'] ?: 'Pemustaka')); ?></p>
					</div>
				</div>
				<div class="member-card-display-bottom">
					<div><span>BERLAKU SAMPAI</span><strong><?= html_escape($member['expired_at'] ?: '-'); ?></strong></div>
					<div><span>STATUS</span><strong><?= html_escape($status_label); ?></strong></div>
					<div class="member-card-code"><i class="ti ti-scan"></i><span><?= html_escape(strtoupper(substr(basename((string) $verify_url), 0, 8))); ?></span></div>
				</div>
			</section>

			<div class="member-card-notes">
				<i class="ti ti-shield-lock"></i>
				<div><strong>Kartu aman dan pribadi.</strong><span>Nomor identitas kependudukan tidak ditampilkan. Gunakan tombol Verifikasi Kartu saat petugas memerlukan validasi.</span></div>
			</div>
		</div>
	</main>
</body>
</html>
