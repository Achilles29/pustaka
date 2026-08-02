<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tabler_css = 'https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css';
$tabler_icons_css = 'https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/tabler-icons.min.css';
$old = (array) $this->session->flashdata('registration_old');
$field = function ($key, $default = '') use ($old) {
	return $old[$key] ?? $default;
};
$form_options = $form_options ?? [
	'genders' => ['Laki-laki', 'Perempuan'],
	'member_types' => ['Umum'],
	'educations' => [],
	'occupations' => [],
];
$options_for = function ($group, $current = '') use ($form_options) {
	$options = $form_options[$group] ?? [];
	if ($current !== '' && ! in_array($current, $options, true)) {
		array_unshift($options, $current);
	}
	return array_values(array_unique($options));
};
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
			<a href="<?= base_url(); ?>">Beranda</a>
			<a href="<?= base_url('katalog'); ?>">Katalog</a>
			<a href="<?= base_url('login'); ?>" class="btn btn-primary btn-sm">Masuk</a>
		</nav>
	</header>

	<main class="public-register-main">
		<div class="container-xl">
			<div class="register-shell register-shell-v2">
				<div class="register-intro register-intro-v2">
					<div class="hero-logo-row mb-3">
						<span class="hero-logo-card">
							<img src="<?= base_url('img/logo-small.jpeg'); ?>" alt="Logo Kabupaten Rembang">
						</span>
						<span class="hero-logo-card hero-logo-card-wide">
							<img src="<?= base_url('img/perpusnas.png'); ?>" alt="Logo Perpusnas">
						</span>
					</div>
					<div class="section-kicker">Pendaftaran online</div>
					<h1>Daftar member tanpa datang dulu.</h1>
					<p>Isi data sesuai identitas, unggah berkas, lalu admin akan memverifikasi sebelum akun anggota aktif.</p>
					<div class="register-rule-list">
						<div><i class="ti ti-id"></i><span>Foto, KTP, dan KK wajib diunggah.</span></div>
						<div><i class="ti ti-map-pin"></i><span>NIK luar Rembang wajib melampirkan surat domisili, sekolah, pondok, atau keterangan lain yang berlaku.</span></div>
						<div><i class="ti ti-shield-check"></i><span>Akun aktif setelah data diverifikasi admin.</span></div>
					</div>
					<div class="auth-secondary-link mt-3">
						Sudah punya akun? <a href="<?= base_url('login'); ?>">Masuk ke dashboard</a>
					</div>
				</div>
				<div class="register-form-panel register-form-panel-v2">
					<div class="register-form-head">
						<div class="section-kicker">Data calon anggota</div>
						<h2>Identitas, kontak, alamat, dan berkas verifikasi.</h2>
						<p>Username akun akan memakai NIK. Password awal ditampilkan setelah pendaftaran terkirim.</p>
					</div>
					<?php if ($this->session->flashdata('registration_success')): ?>
						<div class="alert alert-success"><?= html_escape($this->session->flashdata('registration_success')); ?></div>
					<?php endif; ?>
					<?php if ($this->session->flashdata('registration_error')): ?>
						<div class="alert alert-danger"><?= html_escape($this->session->flashdata('registration_error')); ?></div>
					<?php endif; ?>

					<?= form_open_multipart('membership/register/submit', ['class' => 'public-member-register-form']); ?>
						<div class="row">
							<div class="col-md-8 mb-3">
								<label class="form-label">Nama Lengkap</label>
								<input type="text" class="form-control" name="full_name" value="<?= html_escape($field('full_name')); ?>" required>
							</div>
							<div class="col-md-4 mb-3">
								<label class="form-label">NIK</label>
								<input type="text" class="form-control" name="identity_number" value="<?= html_escape($field('identity_number')); ?>" required inputmode="numeric">
								<div class="form-hint">NIK Rembang diawali <code>3317</code>.</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-4 mb-3">
								<label class="form-label">Jenis Kelamin</label>
								<select class="form-select" name="gender">
									<option value="">Pilih</option>
									<?php foreach ($options_for('genders', $field('gender')) as $option): ?>
										<option value="<?= html_escape($option); ?>" <?= $field('gender') === $option ? 'selected' : ''; ?>><?= html_escape($option); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-md-4 mb-3">
								<label class="form-label">Tempat Lahir</label>
								<input type="text" class="form-control" name="birth_place" value="<?= html_escape($field('birth_place')); ?>">
							</div>
							<div class="col-md-4 mb-3">
								<label class="form-label">Tanggal Lahir</label>
								<input type="date" class="form-control" name="birth_date" value="<?= html_escape($field('birth_date')); ?>">
							</div>
						</div>
						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="form-label">No HP</label>
								<input type="text" class="form-control" name="phone" value="<?= html_escape($field('phone')); ?>">
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Email</label>
								<input type="email" class="form-control" name="email" value="<?= html_escape($field('email')); ?>">
							</div>
						</div>
						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="form-label">Kecamatan</label>
								<input type="text" class="form-control" name="district" value="<?= html_escape($field('district')); ?>">
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Desa / Kelurahan</label>
								<input type="text" class="form-control" name="village" value="<?= html_escape($field('village')); ?>">
							</div>
						</div>
						<div class="mb-3">
							<label class="form-label">Alamat Lengkap</label>
							<textarea class="form-control" name="address" rows="3"><?= html_escape($field('address')); ?></textarea>
						</div>
						<div class="row">
							<div class="col-md-4 mb-3">
								<label class="form-label">Tipe Member</label>
								<select class="form-select" name="member_type">
									<?php foreach ($options_for('member_types', $field('member_type', 'Umum')) as $option): ?>
										<option value="<?= html_escape($option); ?>" <?= $field('member_type', 'Umum') === $option ? 'selected' : ''; ?>><?= html_escape($option); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-md-4 mb-3">
								<label class="form-label">Pendidikan</label>
								<select class="form-select" name="education">
									<option value="">Pilih</option>
									<?php foreach ($options_for('educations', $field('education')) as $option): ?>
										<option value="<?= html_escape($option); ?>" <?= $field('education') === $option ? 'selected' : ''; ?>><?= html_escape($option); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-md-4 mb-3">
								<label class="form-label">Pekerjaan</label>
								<select class="form-select" name="occupation">
									<option value="">Pilih</option>
									<?php foreach ($options_for('occupations', $field('occupation')) as $option): ?>
										<option value="<?= html_escape($option); ?>" <?= $field('occupation') === $option ? 'selected' : ''; ?>><?= html_escape($option); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
						<div class="row">
							<div class="col-md-4 mb-3">
								<label class="form-label">Foto Diri</label>
								<input type="file" class="form-control" name="photo_file" accept=".jpg,.jpeg,.png" required>
							</div>
							<div class="col-md-4 mb-3">
								<label class="form-label">KTP</label>
								<input type="file" class="form-control" name="ktp_file" accept=".jpg,.jpeg,.png,.pdf" required>
							</div>
							<div class="col-md-4 mb-3">
								<label class="form-label">Kartu Keluarga</label>
								<input type="file" class="form-control" name="kk_file" accept=".jpg,.jpeg,.png,.pdf" required>
							</div>
						</div>
						<div class="mb-3">
							<label class="form-label">Surat Keterangan Luar Rembang</label>
							<input type="file" class="form-control" name="support_letter_file" accept=".jpg,.jpeg,.png,.pdf">
							<div class="form-hint">Wajib untuk NIK yang tidak diawali <code>3317</code>.</div>
						</div>
						<div class="mb-3">
							<label class="form-label">Catatan Domisili / Instansi</label>
							<input type="text" class="form-control" name="residency_note" value="<?= html_escape($field('residency_note')); ?>" placeholder="Contoh: Domisili Desa X, santri Pondok Y, siswa Sekolah Z">
						</div>
						<div class="register-submit-bar">
							<button type="submit" class="btn btn-primary btn-lg w-100">
								<i class="ti ti-send me-1"></i>Kirim Pendaftaran
							</button>
							<div class="text-secondary small text-center mt-2">Setelah terkirim, halaman pending akan menampilkan username dan password awal.</div>
						</div>
					<?= form_close(); ?>
				</div>
			</div>
		</div>
	</main>
</body>
</html>
