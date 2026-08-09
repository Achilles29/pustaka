<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tabler_css = 'https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css';
$tabler_icons_css = 'https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/tabler-icons.min.css';
$url_path = function ($path) {
	$segments = explode('/', str_replace('\\', '/', trim((string) $path, '/')));
	return implode('/', array_map('rawurlencode', $segments));
};
if (! empty($member['photo_local_path'])) {
	$photo_url = base_url($url_path($member['photo_local_path']));
} elseif (! empty($member['photo_source_path']) && strpos($member['photo_source_path'], 'assets/') === 0) {
	$photo_url = base_url($url_path($member['photo_source_path']));
} elseif (! empty($member['photo_source_path'])) {
	$photo_url = base_url($url_path('assets/uploads/inlislite/source_mirror/' . $member['photo_source_path']));
} else {
	$photo_url = '';
}
$display_name = $member['full_name'] ?? ($account['full_name'] ?? $current_user['username']);
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
	<link rel="stylesheet" href="<?= base_url('assets/css/pustaka-polish.css?v=20260810a'); ?>">
</head>
<body class="user-page">
	<header class="user-topbar user-topbar-app">
		<a class="public-brand" href="<?= base_url(); ?>">
			<span class="brand-logo-shell">
				<img class="brand-logo" src="<?= base_url('img/logo-small.jpeg'); ?>" alt="Logo Kabupaten Rembang">
			</span>
			<span class="public-brand-text">Pustaka Digital Rembang</span>
		</a>
		<div class="btn-list">
			<a href="<?= base_url(); ?>" class="btn btn-outline-primary btn-sm"><i class="ti ti-home me-1"></i>Beranda</a>
			<a href="<?= base_url('katalog'); ?>" class="btn btn-outline-primary btn-sm"><i class="ti ti-search me-1"></i>Katalog</a>
			<a href="<?= base_url('agenda'); ?>" class="btn btn-outline-primary btn-sm"><i class="ti ti-calendar-event me-1"></i>Agenda</a>
			<a href="<?= base_url('user/dashboard'); ?>" class="btn btn-outline-primary btn-sm"><i class="ti ti-id me-1"></i>Dashboard</a>
			<a href="<?= base_url('user/account'); ?>" class="btn btn-primary btn-sm"><i class="ti ti-user-cog me-1"></i>Akun</a>
			<a href="<?= base_url('logout'); ?>" class="btn btn-outline-primary btn-sm"><i class="ti ti-logout me-1"></i>Logout</a>
		</div>
	</header>

	<nav class="member-bottom-nav" aria-label="Navigasi pemustaka">
		<a href="<?= base_url(); ?>"><i class="ti ti-home"></i><span>Beranda</span></a>
		<a href="<?= base_url('katalog'); ?>"><i class="ti ti-search"></i><span>Katalog</span></a>
		<a href="<?= base_url('agenda'); ?>"><i class="ti ti-calendar-event"></i><span>Agenda</span></a>
		<a href="<?= base_url('user/dashboard'); ?>"><i class="ti ti-id"></i><span>Dashboard</span></a>
		<a href="<?= base_url('user/account'); ?>" class="active"><i class="ti ti-user-cog"></i><span>Akun</span></a>
	</nav>

	<main class="user-dashboard user-dashboard-v2">
		<div class="container-xl">
			<?php if ($this->session->flashdata('success')): ?>
				<div class="alert alert-success"><?= html_escape($this->session->flashdata('success')); ?></div>
			<?php endif; ?>
			<?php if ($this->session->flashdata('error')): ?>
				<div class="alert alert-danger"><?= html_escape($this->session->flashdata('error')); ?></div>
			<?php endif; ?>

			<section class="account-hero-panel member-panel">
				<div class="account-profile-card">
					<div class="member-avatar member-avatar-large">
						<?php if ($photo_url): ?>
							<img src="<?= html_escape($photo_url); ?>" alt="Foto <?= html_escape($display_name); ?>">
						<?php else: ?>
							<i class="ti ti-user"></i>
						<?php endif; ?>
					</div>
					<div>
						<div class="section-kicker">Akun Pemustaka</div>
						<h1><?= html_escape($display_name); ?></h1>
						<p class="text-secondary mb-0">Kelola username dan password login tanpa mengubah data identitas keanggotaan.</p>
					</div>
				</div>
				<div class="account-data-list">
					<div><span>Username</span><strong><?= html_escape($account['username'] ?? '-'); ?></strong></div>
					<div><span>Email</span><strong><?= html_escape($account['email'] ?: '-'); ?></strong></div>
					<div><span>No. Anggota</span><strong><?= html_escape($member['member_no'] ?? '-'); ?></strong></div>
					<div><span>Status Password</span><strong><?= ! empty($account['force_password_change']) ? 'Perlu diganti' : 'Aktif'; ?></strong></div>
				</div>
			</section>

			<section class="account-settings-grid">
				<div class="member-panel">
					<div class="member-panel-head">
						<div>
							<div class="section-kicker">Login</div>
							<h3>Ganti Username</h3>
						</div>
						<i class="ti ti-user-edit"></i>
					</div>
					<p class="text-secondary">Username dipakai untuk masuk aplikasi. NIK/nomor identitas member tidak ikut berubah.</p>
					<?= form_open('user/account/username'); ?>
						<div class="mb-3">
							<label class="form-label">Username baru</label>
							<input type="text" class="form-control" name="username" value="<?= html_escape($account['username'] ?? ''); ?>" minlength="5" maxlength="80" autocomplete="username" required>
							<div class="form-hint">Boleh huruf, angka, titik, underscore, atau strip.</div>
						</div>
						<div class="mb-3">
							<label class="form-label">Password saat ini</label>
							<input type="password" class="form-control" name="current_password" autocomplete="current-password" required>
						</div>
						<button class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Simpan Username</button>
					<?= form_close(); ?>
				</div>

				<div class="member-panel">
					<div class="member-panel-head">
						<div>
							<div class="section-kicker">Keamanan</div>
							<h3>Ganti Password</h3>
						</div>
						<i class="ti ti-lock-cog"></i>
					</div>
					<p class="text-secondary">Gunakan password unik minimal 8 karakter. Setelah berhasil, status wajib ganti password akan dibersihkan.</p>
					<?= form_open('user/account/password'); ?>
						<div class="mb-3">
							<label class="form-label">Password saat ini</label>
							<input type="password" class="form-control" name="current_password" autocomplete="current-password" required>
						</div>
						<div class="mb-3">
							<label class="form-label">Password baru</label>
							<input type="password" class="form-control" name="new_password" minlength="8" maxlength="72" autocomplete="new-password" required>
						</div>
						<div class="mb-3">
							<label class="form-label">Ulangi password baru</label>
							<input type="password" class="form-control" name="confirm_password" minlength="8" maxlength="72" autocomplete="new-password" required>
						</div>
						<button class="btn btn-primary"><i class="ti ti-shield-check me-1"></i>Simpan Password</button>
					<?= form_close(); ?>
				</div>
			</section>
		</div>
	</main>
</body>
</html>
