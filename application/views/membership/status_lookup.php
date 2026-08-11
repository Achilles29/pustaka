<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tabler_css = 'https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css';
$tabler_icons_css = 'https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/css/tabler-icons.min.css';
$status_labels = [
	'pending' => ['Menunggu Verifikasi', 'bg-yellow-lt', 'Admin sedang memeriksa kesesuaian data dan berkas. Akun belum dapat digunakan untuk masuk.'],
	'verified' => ['Terverifikasi', 'bg-green-lt', 'Pendaftaran disetujui. Akun member sudah aktif dan dapat digunakan untuk masuk.'],
	'rejected' => ['Perlu Perbaikan', 'bg-red-lt', 'Pendaftaran belum dapat disetujui. Baca catatan admin lalu ajukan kembali dengan NIK yang sama.'],
	'cancelled' => ['Dibatalkan', 'bg-secondary-lt', 'Pendaftaran dibatalkan. Anda dapat mengajukan kembali dengan NIK yang sama bila diperlukan.'],
];
$status = $request ? ($status_labels[$request['status']] ?? $status_labels['pending']) : null;
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
	<link rel="stylesheet" href="<?= base_url('assets/css/pustaka-polish.css?v=20260811a'); ?>">
</head>
<body class="public-page public-register-page">
	<header class="public-nav">
		<a class="public-brand" href="<?= base_url(); ?>">
			<span class="brand-logo-shell"><img class="brand-logo" src="<?= base_url('img/logo-small.jpeg'); ?>" alt="Logo Kabupaten Rembang"></span>
			<span class="public-brand-text">Pustaka Digital Rembang</span>
		</a>
		<nav class="public-links">
			<a href="<?= base_url(); ?>">Beranda</a>
			<a href="<?= base_url('katalog'); ?>">Katalog</a>
			<a href="<?= base_url('agenda'); ?>">Agenda</a>
			<a href="<?= base_url('membership/register'); ?>">Daftar Member</a>
			<a href="<?= base_url('login'); ?>" class="btn btn-primary btn-sm">Masuk</a>
		</nav>
	</header>

	<main class="public-register-main">
		<div class="container-tight">
			<div class="registration-pending-card">
				<div class="pending-icon"><i class="ti ti-search"></i></div>
				<div>
					<div class="section-kicker">Pendaftaran online</div>
					<h1>Cek Status Pendaftaran</h1>
					<p>Gunakan salah satu data yang Anda simpan saat mendaftar. Status akan diperbarui setelah petugas memproses pengajuan.</p>
				</div>

				<?= form_open('membership/registration-status', ['class' => 'mt-3']); ?>
					<div class="row g-2 align-items-end">
						<div class="col-md-4">
							<label class="form-label" for="lookup_type">Cari dengan</label>
							<select class="form-select" id="lookup_type" name="lookup_type">
								<option value="nik" <?= $lookup_type === 'nik' ? 'selected' : ''; ?>>NIK</option>
								<option value="phone" <?= $lookup_type === 'phone' ? 'selected' : ''; ?>>Nomor HP</option>
								<option value="registration_code" <?= $lookup_type === 'registration_code' ? 'selected' : ''; ?>>Kode pendaftaran</option>
								<option value="token" <?= $lookup_type === 'token' ? 'selected' : ''; ?>>Tautan / token status</option>
							</select>
						</div>
						<div class="col-md-8">
							<label class="form-label" for="lookup_value" id="lookup_value_label">NIK</label>
							<div class="input-group">
								<input class="form-control" id="lookup_value" name="lookup_value" value="<?= html_escape($lookup_value); ?>" required inputmode="numeric" autocomplete="off" placeholder="Masukkan 16 digit NIK">
								<button class="btn btn-primary" type="submit"><i class="ti ti-search me-1"></i>Cek Status</button>
							</div>
						</div>
					</div>
					<div class="form-hint mt-2" id="lookup_help">Masukkan NIK yang digunakan saat pendaftaran.</div>
				<?= form_close(); ?>

				<?php if ($error): ?>
					<div class="alert alert-danger mt-3 mb-0" role="alert"><strong>Status belum dapat ditampilkan.</strong><br><?= html_escape($error); ?></div>
				<?php endif; ?>

				<?php if ($request): ?>
					<div class="border-top mt-4 pt-4">
						<div class="pending-status-row mb-3">
							<span class="badge <?= html_escape($status[1]); ?>"><?= html_escape($status[0]); ?></span>
							<code><?= html_escape($request['registration_code']); ?></code>
						</div>
						<h2 class="h3 mb-1"><?= html_escape($request['full_name']); ?></h2>
						<p class="text-secondary mb-3"><?= html_escape($status[2]); ?></p>

						<?php if ($request['status'] === 'verified'): ?>
							<div class="pending-account-grid">
								<div><span>Username</span><strong><?= html_escape($request['identity_number']); ?></strong></div>
								<div><span>Password awal</span><strong><?= html_escape($default_password); ?></strong></div>
							</div>
							<div class="alert alert-info mb-0">Password di atas adalah password awal dan hanya berlaku bila belum pernah diganti. Masuk menggunakan data tersebut, kemudian segera ganti password pada halaman akun.</div>
						<?php else: ?>
							<div class="alert alert-info mb-0">Username Anda nanti memakai NIK. Password awal akan berlaku setelah pendaftaran diverifikasi.</div>
						<?php endif; ?>

						<?php if (! empty($request['admin_note'])): ?>
							<div class="pending-note"><span>Catatan Admin</span><p><?= html_escape($request['admin_note']); ?></p></div>
						<?php endif; ?>

						<div class="pending-actions">
							<?php if ($request['status'] === 'verified'): ?><a href="<?= base_url('login'); ?>" class="btn btn-primary"><i class="ti ti-login me-1"></i>Masuk ke Akun</a><?php endif; ?>
							<?php if (in_array($request['status'], ['rejected', 'cancelled'], true)): ?><a href="<?= base_url('membership/register'); ?>" class="btn btn-outline-primary"><i class="ti ti-refresh me-1"></i>Perbarui Pendaftaran</a><?php endif; ?>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</main>
	<script>
	(function () {
		var type = document.getElementById('lookup_type');
		var input = document.getElementById('lookup_value');
		var label = document.getElementById('lookup_value_label');
		var help = document.getElementById('lookup_help');
		var options = {
			nik: { label: 'NIK', placeholder: 'Masukkan 16 digit NIK', hint: 'Masukkan NIK yang digunakan saat pendaftaran.', inputmode: 'numeric' },
			phone: { label: 'Nomor HP', placeholder: 'Contoh: 0812xxxxxx', hint: 'Masukkan nomor HP yang dicantumkan saat pendaftaran.', inputmode: 'tel' },
			registration_code: { label: 'Kode pendaftaran', placeholder: 'Contoh: REG-20260811-0001', hint: 'Kode ditampilkan setelah formulir pendaftaran terkirim.', inputmode: 'text' },
			token: { label: 'Tautan atau token status', placeholder: 'Tempel tautan status atau token', hint: 'Anda dapat menempelkan tautan status lengkap yang pernah diterima.', inputmode: 'text' }
		};
		var update = function () { var option = options[type.value] || options.nik; label.textContent = option.label; input.placeholder = option.placeholder; input.inputMode = option.inputmode; help.textContent = option.hint; };
		type.addEventListener('change', update); update();
	}());
	</script>
</body>
</html>
