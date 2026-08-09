<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tabler_css = 'https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css';
$tabler_icons_css = 'https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/tabler-icons.min.css';
$auth_user = (array) $this->session->userdata('auth_user');
$role_codes = array_map(function ($role) { return $role['code'] ?? ''; }, (array) $this->session->userdata('user_roles'));
$is_logged_in = ! empty($auth_user['id']);
$dashboard_url = (in_array('SUPERADMIN', $role_codes, true) || in_array('ADMIN', $role_codes, true)) ? base_url('admin') : base_url('user/dashboard');
$old = (array) $this->session->flashdata('event_registration_old');
$old_answers = (array) ($old['answers'] ?? []);
$value = function ($key, $default = '') use ($old, $current_member) {
	if (array_key_exists($key, $old)) {
		return $old[$key];
	}
	if ($current_member) {
		if ($key === 'participant_type') return 'member';
		if ($key === 'participant_name') return $current_member['full_name'] ?? '';
		if ($key === 'participant_phone') return $current_member['phone'] ?? '';
		if ($key === 'participant_email') return $current_member['email'] ?? '';
		if ($key === 'institution') return $current_member['occupation_label'] ?? ($current_member['occupation'] ?? '');
	}
	return $default;
};
$poster = empty($event['poster_path']) ? '' : base_url($event['poster_path']);
$member_answer_defaults = (array) ($member_answer_defaults ?? []);
$render_dynamic_field = function ($field) use ($old_answers, $member_answer_defaults) {
	$name = 'answers[' . $field['field_key'] . ']';
	$current = array_key_exists($field['field_key'], $old_answers)
		? $old_answers[$field['field_key']]
		: ($member_answer_defaults[$field['field_key']] ?? '');
	$required = ! empty($field['is_required']) ? ' required' : '';
	?>
	<div class="mb-3">
		<label class="form-label"><?= html_escape($field['field_label']); ?><?= ! empty($field['is_required']) ? ' <span class="text-danger">*</span>' : ''; ?></label>
		<?php if ($field['field_type'] === 'textarea'): ?>
			<textarea class="form-control" name="<?= html_escape($name); ?>" rows="3" placeholder="<?= html_escape($field['placeholder'] ?? ''); ?>"<?= $required; ?>><?= html_escape($current); ?></textarea>
		<?php elseif ($field['field_type'] === 'select'): ?>
			<select class="form-select" name="<?= html_escape($name); ?>"<?= $required; ?>>
				<option value="">Pilih</option>
				<?php foreach (($field['options'] ?? []) as $option): ?>
					<option value="<?= html_escape($option); ?>" <?= $current === $option ? 'selected' : ''; ?>><?= html_escape($option); ?></option>
				<?php endforeach; ?>
			</select>
		<?php elseif ($field['field_type'] === 'radio'): ?>
			<div class="form-selectgroup">
				<?php foreach (($field['options'] ?? []) as $option): ?>
					<label class="form-selectgroup-item">
						<input type="radio" name="<?= html_escape($name); ?>" value="<?= html_escape($option); ?>" class="form-selectgroup-input" <?= $current === $option ? 'checked' : ''; ?><?= $required; ?>>
						<span class="form-selectgroup-label"><?= html_escape($option); ?></span>
					</label>
				<?php endforeach; ?>
			</div>
		<?php elseif ($field['field_type'] === 'checkbox'): ?>
			<?php $checked_values = is_array($current) ? $current : array_map('trim', explode(',', (string) $current)); ?>
			<div class="form-selectgroup">
				<?php foreach (($field['options'] ?? []) as $option): ?>
					<label class="form-selectgroup-item">
						<input type="checkbox" name="<?= html_escape($name); ?>[]" value="<?= html_escape($option); ?>" class="form-selectgroup-input" <?= in_array($option, $checked_values, true) ? 'checked' : ''; ?>>
						<span class="form-selectgroup-label"><?= html_escape($option); ?></span>
					</label>
				<?php endforeach; ?>
			</div>
		<?php else: ?>
			<?php
			$type = $field['field_type'] === 'phone' ? 'tel' : ($field['field_type'] === 'number' ? 'number' : ($field['field_type'] === 'date' ? 'date' : ($field['field_type'] === 'email' ? 'email' : 'text')));
			?>
			<input type="<?= $type; ?>" class="form-control" name="<?= html_escape($name); ?>" value="<?= html_escape($current); ?>" placeholder="<?= html_escape($field['placeholder'] ?? ''); ?>"<?= $required; ?>>
		<?php endif; ?>
		<?php if (! empty($field['helper_text'])): ?><div class="form-hint"><?= html_escape($field['helper_text']); ?></div><?php endif; ?>
	</div>
	<?php
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
	<link rel="stylesheet" href="<?= base_url('assets/css/pustaka-polish.css?v=20260808b'); ?>">
</head>
<body class="public-page public-agenda-page">
	<header class="public-nav">
		<a class="public-brand" href="<?= base_url(); ?>"><span class="brand-logo-shell"><img class="brand-logo" src="<?= base_url('img/logo-small.jpeg'); ?>" alt="Logo Kabupaten Rembang"></span><span class="public-brand-text">Pustaka Digital Rembang</span></a>
		<div class="public-agency-strip"><img src="<?= base_url('img/perpusnas.png'); ?>" alt="Logo Perpusnas"></div>
		<nav class="public-links">
			<a href="<?= base_url(); ?>">Beranda</a>
			<a href="<?= base_url('katalog'); ?>">Katalog</a>
			<a href="<?= base_url('agenda'); ?>">Agenda</a>
			<?php if ($is_logged_in): ?><a href="<?= $dashboard_url; ?>">Dashboard</a><a href="<?= base_url('logout'); ?>" class="btn btn-primary btn-sm">Logout</a><?php else: ?><a href="<?= base_url('membership/register'); ?>">Daftar Member</a><a href="<?= base_url('login'); ?>" class="btn btn-primary btn-sm">Masuk</a><?php endif; ?>
		</nav>
	</header>
	<nav class="public-mobile-nav">
		<a href="<?= base_url(); ?>"><i class="ti ti-home"></i><span>Beranda</span></a>
		<a href="<?= base_url('katalog'); ?>"><i class="ti ti-search"></i><span>Katalog</span></a>
		<a href="<?= base_url('agenda'); ?>" class="active"><i class="ti ti-calendar-event"></i><span>Agenda</span></a>
		<a href="<?= $is_logged_in ? $dashboard_url : base_url('login'); ?>"><i class="ti ti-id"></i><span><?= $is_logged_in ? 'Dashboard' : 'Masuk'; ?></span></a>
	</nav>

	<main class="public-catalog-main">
		<section class="public-section">
			<div class="container-xl">
				<?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?= html_escape($this->session->flashdata('success')); ?></div><?php endif; ?>
				<?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?= html_escape($this->session->flashdata('error')); ?></div><?php endif; ?>
				<div class="row row-cards">
					<div class="col-lg-7">
						<div class="event-public-detail">
							<div class="event-public-detail-poster" style="<?= $poster ? 'background-image:url(' . html_escape($poster) . ')' : ''; ?>">
								<?php if (! $poster): ?><i class="ti ti-calendar-event"></i><?php endif; ?>
							</div>
							<div class="event-public-detail-body">
								<div class="section-kicker"><?= html_escape($event['category_name'] ?: 'Agenda Literasi'); ?></div>
								<h1><?= html_escape($event['title']); ?></h1>
								<p><?= html_escape($event['summary'] ?: 'Kegiatan literasi dari jejaring perpustakaan Rembang.'); ?></p>
								<div class="event-public-meta event-public-meta-wide">
									<span><i class="ti ti-clock"></i><?= html_escape($event['starts_at'] ? date('d M Y H:i', strtotime($event['starts_at'])) : 'Jadwal menyusul'); ?></span>
									<span><i class="ti ti-map-pin"></i><?= html_escape($event['location_name'] ?: ($event['library_name'] ?: 'Rembang')); ?></span>
									<span><i class="ti ti-users"></i><?= $event['quota'] ? number_format((int) $event['quota'], 0, ',', '.') . ' kuota' : 'Tanpa batas kuota'; ?></span>
								</div>
								<?php if (! empty($event['description'])): ?><div class="prose-lite mt-4"><?= nl2br(html_escape($event['description'])); ?></div><?php endif; ?>
							</div>
						</div>
					</div>
					<div class="col-lg-5" id="daftar-event">
						<div class="card public-registration-card">
							<div class="card-header"><h2 class="card-title">Daftar Event</h2></div>
							<div class="card-body">
								<?php if (! $is_open): ?>
									<div class="empty"><div class="empty-icon"><i class="ti ti-lock"></i></div><p class="empty-title">Pendaftaran belum dibuka atau sudah ditutup.</p></div>
								<?php elseif ($event['registration_mode'] === 'member_only' && ! $current_member): ?>
									<div class="alert alert-info">Event ini khusus member. Silakan login sebagai member untuk mendaftar.</div>
									<a href="<?= base_url('login'); ?>" class="btn btn-primary w-100"><i class="ti ti-login me-1"></i>Masuk Member</a>
								<?php else: ?>
									<?= form_open('agenda/register/' . (int) $event['id']); ?>
										<?php if ($current_member): ?>
											<input type="hidden" name="participant_type" value="member">
											<div class="alert alert-info d-flex gap-2 align-items-start">
												<i class="ti ti-user-check mt-1"></i>
												<div>
													<strong>Data member otomatis dipakai.</strong>
													<div>Periksa kembali data yang sudah terisi. Field tambahan yang belum tersedia di profil tetap perlu dilengkapi manual.</div>
												</div>
											</div>
										<?php endif; ?>
										<div class="row">
											<?php if (! $current_member): ?>
												<div class="col-md-6 mb-3">
													<label class="form-label">Tipe Peserta</label>
													<select class="form-select" name="participant_type">
														<option value="public" <?= $value('participant_type', 'public') === 'public' ? 'selected' : ''; ?>>Perorangan</option>
														<option value="group" <?= $value('participant_type') === 'group' ? 'selected' : ''; ?>>Rombongan</option>
													</select>
												</div>
											<?php endif; ?>
											<div class="<?= $current_member ? 'col-12' : 'col-md-6'; ?> mb-3">
												<label class="form-label">Jumlah Orang</label>
												<input type="number" min="1" max="1000" class="form-control" name="participant_count" value="<?= html_escape($value('participant_count', 1)); ?>" required>
											</div>
										</div>
										<div class="mb-3">
											<label class="form-label">Nama Peserta / Penanggung Jawab</label>
											<input type="text" class="form-control" name="participant_name" value="<?= html_escape($value('participant_name')); ?>" required>
										</div>
										<div class="row">
											<div class="col-md-6 mb-3">
												<label class="form-label">Nomor HP</label>
												<input type="tel" class="form-control" name="participant_phone" value="<?= html_escape($value('participant_phone')); ?>">
											</div>
											<div class="col-md-6 mb-3">
												<label class="form-label">Email</label>
												<input type="email" class="form-control" name="participant_email" value="<?= html_escape($value('participant_email')); ?>">
											</div>
										</div>
										<div class="mb-3">
											<label class="form-label">Instansi / Komunitas</label>
											<input type="text" class="form-control" name="institution" value="<?= html_escape($value('institution')); ?>" placeholder="Opsional">
										</div>
										<?php foreach ($fields as $field): ?>
											<?php $render_dynamic_field($field); ?>
										<?php endforeach; ?>
										<button class="btn btn-primary btn-lg w-100"><i class="ti ti-ticket me-1"></i>Kirim Pendaftaran</button>
									<?= form_close(); ?>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</main>
</body>
</html>
