<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tabler_css = 'https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css';
$tabler_js = 'https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js';
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= html_escape($title); ?></title>
	<link rel="stylesheet" href="<?= $tabler_css; ?>">
	<link rel="stylesheet" href="<?= base_url('assets/css/pustaka.css'); ?>">
</head>
<body class="d-flex flex-column">
	<div class="page page-center auth-page">
		<div class="container container-tight py-4">
			<div class="text-center mb-4">
				<a class="navbar-brand navbar-brand-autodark justify-content-center" href="<?= base_url(); ?>">
					<span class="brand-mark">PR</span>
					<span class="ms-2 fw-bold">Pustaka Digital Rembang</span>
				</a>
			</div>
			<div class="card card-md">
				<div class="card-body">
					<h1 class="h2 text-center mb-2">Masuk Admin</h1>
					<p class="text-secondary text-center mb-4">Gunakan akun yang sudah terdaftar di database aplikasi.</p>

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
							<button type="submit" class="btn btn-primary w-100">Masuk</button>
						</div>
					<?= form_close(); ?>
				</div>
			</div>
			<div class="text-center text-secondary mt-3">
				Default awal: <code>superadmin</code> / <code>admin123</code>
			</div>
		</div>
	</div>
	<script src="<?= $tabler_js; ?>"></script>
</body>
</html>
