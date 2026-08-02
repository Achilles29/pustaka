<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tabler_css = 'https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css';
$tabler_icons_css = 'https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/tabler-icons.min.css';
$status_labels = [
	'active' => 'Aktif',
	'inactive' => 'Nonaktif',
	'blocked' => 'Diblokir',
	'expired' => 'Kedaluwarsa',
	'unknown' => 'Belum Dipetakan',
];
$url_path = function ($path) {
	$segments = explode('/', str_replace('\\', '/', trim((string) $path, '/')));
	return implode('/', array_map('rawurlencode', $segments));
};
$photo_url = '';
if ($is_valid && ! empty($member['photo_local_path'])) {
	$photo_url = base_url($url_path($member['photo_local_path']));
} elseif ($is_valid && ! empty($member['photo_source_path']) && strpos($member['photo_source_path'], 'assets/') === 0) {
	$photo_url = base_url($url_path($member['photo_source_path']));
} elseif ($is_valid && ! empty($member['photo_source_path'])) {
	$photo_url = base_url($url_path('assets/uploads/inlislite/source_mirror/' . $member['photo_source_path']));
}
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
<body class="public-page">
	<header class="public-nav">
		<a class="public-brand" href="<?= base_url(); ?>">
			<span class="brand-logo-shell"><img class="brand-logo" src="<?= base_url('img/logo-small.jpeg'); ?>" alt="Logo Kabupaten Rembang"></span>
			<span class="public-brand-text">Pustaka Digital Rembang</span>
		</a>
		<nav class="public-links">
			<a href="<?= base_url('katalog'); ?>">Katalog</a>
			<a href="<?= base_url('login'); ?>" class="btn btn-primary btn-sm">Masuk</a>
		</nav>
	</header>

	<main class="membership-verify-main">
		<div class="verify-container">
			<div class="verify-panel <?= $is_valid ? 'is-valid' : 'is-invalid'; ?>">
				<?php if ($is_valid): ?>
					<div class="verify-status-line">
						<span class="verify-icon"><i class="ti ti-shield-check"></i></span>
						<div>
							<div class="section-kicker">Kartu terverifikasi</div>
							<h1>Anggota resmi Pustaka Digital Rembang</h1>
						</div>
					</div>
					<div class="verify-digital-card">
						<div class="member-card-watermark">PDR</div>
						<div class="member-card-heading">
							<div class="member-card-brand">
								<span class="member-card-logo"><img src="<?= base_url('img/logo-small.jpeg'); ?>" alt="Logo Kabupaten Rembang"></span>
								<div>
									<div class="member-card-label">Pustaka Digital Rembang</div>
									<strong>Kartu Anggota</strong>
								</div>
							</div>
							<span class="member-card-chip"><?= html_escape($status_labels[$member['status']] ?? ucfirst($member['status'])); ?></span>
						</div>
						<div class="member-card-identity">
							<div class="member-avatar">
								<?php if ($photo_url): ?>
									<img src="<?= html_escape($photo_url); ?>" alt="Foto <?= html_escape($member['full_name']); ?>">
								<?php else: ?>
									<i class="ti ti-user"></i>
								<?php endif; ?>
							</div>
							<div>
								<div class="member-card-name"><?= html_escape($member['full_name']); ?></div>
								<div class="member-card-number"><?= html_escape($member['member_no'] ?: '-'); ?></div>
								<div class="member-card-subline"><?= html_escape($member['member_type_label'] ?: ($member['member_type'] ?: 'Pemustaka')); ?></div>
							</div>
						</div>
						<div class="verify-grid">
							<div><span>Berlaku Sampai</span><strong><?= html_escape($member['expired_at'] ?: '-'); ?></strong></div>
							<div><span>Token</span><strong><?= html_escape(strtoupper(substr((string) $token, 0, 8))); ?></strong></div>
						</div>
					</div>
				<?php else: ?>
					<div class="verify-icon">
						<i class="ti ti-shield-x"></i>
					</div>
					<div class="section-kicker">Kartu tidak valid</div>
					<h1>Verifikasi gagal</h1>
					<p class="text-secondary">Token kartu tidak cocok atau data anggota sudah tidak tersedia.</p>
				<?php endif; ?>
			</div>
		</div>
	</main>
</body>
</html>
