<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tabler_css = 'https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css';
$tabler_icons_css = 'https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/tabler-icons.min.css';
$auth_user = (array) $this->session->userdata('auth_user');
$role_codes = array_map(function ($role) { return $role['code'] ?? ''; }, (array) $this->session->userdata('user_roles'));
$is_logged_in = ! empty($auth_user['id']);
$dashboard_url = (in_array('SUPERADMIN', $role_codes, true) || in_array('ADMIN', $role_codes, true)) ? base_url('admin') : base_url('user/dashboard');
$status_labels = [
	'pending' => 'Menunggu verifikasi admin',
	'registered' => 'Terdaftar',
	'approved' => 'Disetujui',
	'rejected' => 'Ditolak',
	'attended' => 'Sudah hadir',
	'cancelled' => 'Dibatalkan',
];
$qr_payload = base_url('events/checkin/' . rawurlencode($registration['attendance_token']));
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
			<?php if ($is_logged_in): ?><a href="<?= $dashboard_url; ?>">Dashboard</a><a href="<?= base_url('logout'); ?>" class="btn btn-primary btn-sm">Logout</a><?php else: ?><a href="<?= base_url('login'); ?>" class="btn btn-primary btn-sm">Masuk</a><?php endif; ?>
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
				<div class="event-ticket-shell">
					<div class="event-ticket-card">
						<div class="event-ticket-head">
							<img src="<?= base_url('img/logo-small.jpeg'); ?>" alt="Logo Kabupaten Rembang">
							<div>
								<div class="section-kicker">Tiket Event Literasi</div>
								<h1><?= html_escape($registration['event_title']); ?></h1>
							</div>
						</div>
						<div class="event-ticket-grid">
							<div>
								<div class="ticket-label">Kode Pendaftaran</div>
								<div class="ticket-value"><?= html_escape($registration['registration_code']); ?></div>
							</div>
							<div>
								<div class="ticket-label">Status</div>
								<div class="ticket-value"><?= html_escape($status_labels[$registration['status']] ?? $registration['status']); ?></div>
							</div>
							<div>
								<div class="ticket-label">Peserta</div>
								<div class="ticket-value"><?= html_escape($registration['participant_name']); ?></div>
							</div>
							<div>
								<div class="ticket-label">Jumlah</div>
								<div class="ticket-value"><?= number_format((int) $registration['participant_count'], 0, ',', '.'); ?> orang</div>
							</div>
							<div>
								<div class="ticket-label">Jadwal</div>
								<div class="ticket-value"><?= html_escape($registration['starts_at'] ? date('d M Y H:i', strtotime($registration['starts_at'])) : '-'); ?></div>
							</div>
							<div>
								<div class="ticket-label">Lokasi</div>
								<div class="ticket-value"><?= html_escape($registration['location_name'] ?: ($registration['library_name'] ?: '-')); ?></div>
							</div>
						</div>
						<?php if (! empty($registration['answers'])): ?>
							<div class="ticket-answer-list">
								<?php foreach ($registration['answers'] as $answer): ?>
									<div><span><?= html_escape($answer['field_label']); ?></span><strong><?= html_escape($answer['value_text'] ?: '-'); ?></strong></div>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
					<div class="event-ticket-qr-card">
						<div id="event-ticket-qr" class="event-ticket-qr"></div>
						<div class="text-secondary small mt-3">Tunjukkan QR ini saat check-in. Kode attendance tersimpan dalam tiket digital.</div>
						<a class="btn btn-outline-primary mt-3 w-100" href="<?= base_url('agenda'); ?>"><i class="ti ti-calendar-event me-1"></i>Lihat Agenda Lain</a>
					</div>
				</div>
			</div>
		</section>
	</main>
	<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
	<script>
	document.addEventListener('DOMContentLoaded', function () {
		var target = document.getElementById('event-ticket-qr');
		if (target && window.QRCode) {
			new QRCode(target, {
				text: <?= json_encode($qr_payload); ?>,
				width: 220,
				height: 220,
				colorDark: '#061b49',
				colorLight: '#ffffff',
				correctLevel: QRCode.CorrectLevel.M
			});
		}
	});
	</script>
</body>
</html>
