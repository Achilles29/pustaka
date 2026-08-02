<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tabler_css = 'https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css';
$tabler_icons_css = 'https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/tabler-icons.min.css';
$tabler_js = 'https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js';
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
<body class="d-flex flex-column auth-page-v2">
	<div class="page page-center auth-page auth-page-v2">
		<div class="container py-4">
			<div class="auth-shell-v2">
				<section class="auth-brand-panel">
					<div class="auth-logo-row">
						<span class="brand-logo-shell brand-logo-shell-light">
							<img class="brand-logo" src="<?= base_url('img/logo-small.jpeg'); ?>" alt="Logo Kabupaten Rembang">
						</span>
						<span class="brand-logo-shell brand-logo-shell-light">
							<img class="brand-logo" src="<?= base_url('img/perpusnas.png'); ?>" alt="Logo Perpusnas">
						</span>
					</div>
					<p class="eyebrow text-white">Pustaka Digital Rembang</p>
					<h1>Masuk ke layanan perpustakaan terpadu.</h1>
					<p>Akses admin panel, dashboard pemustaka, kartu digital, katalog online, dan layanan baca digital dalam satu akun.</p>
					<div class="auth-benefits">
						<span><i class="ti ti-shield-lock"></i>Akun berbasis role dan hak akses</span>
						<span><i class="ti ti-id-badge-2"></i>Member memakai NIK sebagai username</span>
						<span><i class="ti ti-books"></i>Reader dan katalog digital terintegrasi</span>
					</div>
				</section>
				<section class="auth-card-panel">
					<a class="d-inline-flex align-items-center gap-2 text-decoration-none mb-4" href="<?= base_url(); ?>">
						<span class="brand-logo-shell">
							<img class="brand-logo" src="<?= base_url('img/logo-small.jpeg'); ?>" alt="Logo Kabupaten Rembang">
						</span>
						<span class="fw-bold text-dark">Pustaka Digital Rembang</span>
					</a>
					<h2>Masuk Akun</h2>
					<p class="text-secondary mb-4">Gunakan username, NIK, email, atau akun admin yang sudah terdaftar.</p>

					<?php if (! empty($error_msg)): ?>
						<div class="alert alert-danger" role="alert"><?= $error_msg; ?></div>
					<?php endif; ?>

					<?= form_open('auth/do_login', ['autocomplete' => 'off']); ?>
						<div class="mb-3">
							<label class="form-label" for="identifier">Username atau email</label>
							<input type="text" class="form-control" id="identifier" name="identifier" value="<?= set_value('identifier'); ?>" required autofocus>
						</div>
						<div class="mb-3">
							<label class="form-label" for="password">Password</label>
							<input type="password" class="form-control" id="password" name="password" required>
						</div>
						<div class="form-footer">
							<button type="submit" class="btn btn-primary btn-lg w-100"><i class="ti ti-login me-1"></i>Masuk</button>
						</div>
					<?= form_close(); ?>
					<div class="auth-secondary-link">
						Belum punya akun member? <a href="<?= base_url('membership/register'); ?>">Daftar online sekarang</a>
					</div>
					<div class="text-secondary small mt-3">
						Default awal admin: <code>superadmin</code> / <code>admin123</code>
					</div>
				</section>
			</div>
		</div>
	</div>
	<script src="<?= $tabler_js; ?>"></script>
</body>
</html>
