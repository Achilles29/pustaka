<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tabler_css = 'https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css';
$tabler_icons_css = 'https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/tabler-icons.min.css';
$status_labels = [
	'pending' => ['Menunggu Verifikasi', 'bg-yellow-lt', 'Admin akan memeriksa kesesuaian data dan berkas sebelum akun aktif.'],
	'verified' => ['Terverifikasi', 'bg-green-lt', 'Akun sudah aktif. Silakan masuk memakai username dan password di bawah.'],
	'rejected' => ['Ditolak', 'bg-red-lt', 'Pendaftaran belum dapat disetujui. Periksa catatan admin.'],
	'cancelled' => ['Dibatalkan', 'bg-secondary-lt', 'Pendaftaran dibatalkan.'],
];
$status = $status_labels[$request['status']] ?? $status_labels['pending'];
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
<body class="public-page public-register-page">
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

	<main class="public-register-main">
		<div class="container-tight">
			<?php if ($this->session->flashdata('registration_success')): ?>
				<div class="alert alert-success"><?= html_escape($this->session->flashdata('registration_success')); ?></div>
			<?php endif; ?>
			<div class="registration-pending-card">
				<div class="pending-icon"><i class="ti ti-user-check"></i></div>
				<div>
					<div class="section-kicker">Pendaftaran online</div>
					<h1>Status Pendaftaran</h1>
					<p><?= html_escape($status[2]); ?></p>
				</div>
				<div class="pending-status-row">
					<span class="badge <?= html_escape($status[1]); ?>"><?= html_escape($status[0]); ?></span>
					<code><?= html_escape($request['registration_code']); ?></code>
				</div>
				<div class="pending-account-grid">
					<div>
						<span>Username</span>
						<strong><?= html_escape($request['identity_number']); ?></strong>
					</div>
					<div>
						<span>Password</span>
						<strong><?= html_escape($default_password); ?></strong>
					</div>
				</div>
				<div class="alert alert-info mb-0">
					Catat username dan password ini. Setelah admin menyetujui pendaftaran, login akan masuk ke dashboard member.
				</div>
				<?php if (! empty($request['admin_note'])): ?>
					<div class="pending-note">
						<span>Catatan Admin</span>
						<p><?= html_escape($request['admin_note']); ?></p>
					</div>
				<?php endif; ?>
				<div class="pending-actions">
					<a href="<?= base_url('login'); ?>" class="btn btn-primary"><i class="ti ti-login me-1"></i>Masuk</a>
					<a href="<?= base_url('membership/register'); ?>" class="btn btn-outline-primary">Daftar Baru</a>
				</div>
			</div>
		</div>
	</main>
</body>
</html>
