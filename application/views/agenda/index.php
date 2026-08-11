<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tabler_css = 'https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css';
$tabler_icons_css = 'https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/tabler-icons.min.css';
$auth_user = (array) $this->session->userdata('auth_user');
$role_codes = array_map(function ($role) { return $role['code'] ?? ''; }, (array) $this->session->userdata('user_roles'));
$is_logged_in = ! empty($auth_user['id']);
$dashboard_url = (in_array('SUPERADMIN', $role_codes, true) || in_array('ADMIN', $role_codes, true)) ? base_url('admin') : base_url('user/dashboard');
$query_base = $_GET;
unset($query_base['page']);
$page_url = function ($page) use ($query_base) {
	return base_url('agenda?' . http_build_query(array_merge($query_base, ['page' => $page])));
};
$poster_url = function ($event) {
	return empty($event['poster_path']) ? '' : base_url($event['poster_path']);
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
		<a class="public-brand" href="<?= base_url(); ?>">
			<span class="brand-logo-shell"><img class="brand-logo" src="<?= base_url('img/logo-small.jpeg'); ?>" alt="Logo Kabupaten Rembang"></span>
			<span class="public-brand-text">Pustaka Digital Rembang</span>
		</a>
		<div class="public-agency-strip"><img src="<?= base_url('img/perpusnas.png'); ?>" alt="Logo Perpusnas"></div>
		<nav class="public-links">
			<a href="<?= base_url(); ?>">Beranda</a>
			<a href="<?= base_url('katalog'); ?>">Katalog</a>
			<a href="<?= base_url('agenda'); ?>">Agenda</a>
			<?php if ($is_logged_in): ?>
				<a href="<?= $dashboard_url; ?>">Dashboard</a>
				<a href="<?= base_url('logout'); ?>" class="btn btn-primary btn-sm">Logout</a>
			<?php else: ?>
				<a href="<?= base_url('membership/register'); ?>">Daftar Member</a>
				<a href="<?= base_url('membership/registration-status'); ?>" class="btn btn-outline-primary btn-sm">Cek Status</a>
				<a href="<?= base_url('login'); ?>" class="btn btn-primary btn-sm">Masuk</a>
			<?php endif; ?>
		</nav>
	</header>
	<nav class="public-mobile-nav">
		<a href="<?= base_url(); ?>"><i class="ti ti-home"></i><span>Beranda</span></a>
		<a href="<?= base_url('katalog'); ?>"><i class="ti ti-search"></i><span>Katalog</span></a>
		<a href="<?= base_url('agenda'); ?>" class="active"><i class="ti ti-calendar-event"></i><span>Agenda</span></a>
		<a href="<?= $is_logged_in ? $dashboard_url : base_url('login'); ?>"><i class="ti ti-id"></i><span><?= $is_logged_in ? 'Dashboard' : 'Masuk'; ?></span></a>
	</nav>

	<main class="public-catalog-main">
		<section class="catalog-search-band agenda-search-band">
			<div class="container-xl">
				<div class="row g-4 align-items-end">
					<div class="col-lg-7">
						<div class="section-kicker">Agenda Literasi</div>
						<h1>Kegiatan literasi Rembang dalam satu kalender.</h1>
					</div>
					<div class="col-lg-5">
						<div class="catalog-count-pill"><i class="ti ti-calendar-event"></i><span><?= number_format((int) $pagination['total_rows'], 0, ',', '.'); ?> event sesuai filter</span></div>
					</div>
				</div>

				<?= form_open('agenda', ['method' => 'get', 'class' => 'public-catalog-filter']); ?>
					<div class="row g-2 align-items-end">
						<div class="col-lg-5">
							<label class="form-label">Cari</label>
							<input type="text" class="form-control form-control-lg" name="q" value="<?= html_escape($filters['q'] ?? ''); ?>" placeholder="Judul, kategori, lokasi, penyelenggara">
						</div>
						<div class="col-md-4 col-lg-3">
							<label class="form-label">Kategori</label>
							<select class="form-select form-select-lg" name="category_id">
								<option value="">Semua</option>
								<?php foreach ($categories as $category): ?>
									<option value="<?= (int) $category['id']; ?>" <?= (int) ($filters['category_id'] ?? 0) === (int) $category['id'] ? 'selected' : ''; ?>><?= html_escape($category['name']); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-md-4 col-lg-2">
							<label class="form-label">Waktu</label>
							<select class="form-select form-select-lg" name="time">
								<option value="upcoming" <?= ($filters['time'] ?? '') === 'upcoming' ? 'selected' : ''; ?>>Akan datang</option>
								<option value="all" <?= ($filters['time'] ?? '') === 'all' ? 'selected' : ''; ?>>Semua</option>
								<option value="past" <?= ($filters['time'] ?? '') === 'past' ? 'selected' : ''; ?>>Selesai</option>
							</select>
						</div>
						<div class="col-md-2 col-lg-1"><button class="btn btn-primary btn-lg w-100"><i class="ti ti-search"></i></button></div>
						<div class="col-md-2 col-lg-1"><a href="<?= base_url('agenda'); ?>" class="btn btn-outline-secondary btn-lg w-100"><i class="ti ti-refresh"></i></a></div>
					</div>
				<?= form_close(); ?>
			</div>
		</section>

		<section class="public-section">
			<div class="container-xl">
				<div class="row row-cards">
					<?php if (empty($events)): ?>
						<div class="col-12"><div class="empty"><div class="empty-icon"><i class="ti ti-calendar-off"></i></div><p class="empty-title">Belum ada event sesuai filter.</p></div></div>
					<?php endif; ?>
					<?php foreach ($events as $event): ?>
						<?php $poster = $poster_url($event); ?>
						<div class="col-md-6 col-xl-4">
							<a class="event-public-card" href="<?= base_url('agenda/detail/' . (int) $event['id']); ?>">
								<div class="event-public-poster" style="<?= $poster ? 'background-image:url(' . html_escape($poster) . ')' : ''; ?>">
									<?php if (! $poster): ?><i class="ti ti-calendar-event"></i><?php endif; ?>
									<span class="event-category-pill" style="--event-color: <?= html_escape($event['category_color'] ?: '#005baa'); ?>"><?= html_escape($event['category_name'] ?: 'Agenda'); ?></span>
								</div>
								<div class="event-public-body">
									<div class="event-date-line"><i class="ti ti-clock"></i><?= html_escape($event['starts_at'] ? date('d M Y H:i', strtotime($event['starts_at'])) : 'Jadwal menyusul'); ?></div>
									<h2><?= html_escape($event['title']); ?></h2>
									<p><?= html_escape($event['summary'] ?: 'Detail kegiatan tersedia di halaman event.'); ?></p>
									<div class="event-public-meta">
										<span><i class="ti ti-map-pin"></i><?= html_escape($event['location_name'] ?: ($event['library_name'] ?: 'Rembang')); ?></span>
										<span><i class="ti ti-users"></i><?= number_format((int) $event['participant_total'], 0, ',', '.'); ?>/<?= $event['quota'] ? number_format((int) $event['quota'], 0, ',', '.') : '∞'; ?></span>
									</div>
								</div>
							</a>
						</div>
					<?php endforeach; ?>
				</div>

				<?php if (($pagination['total_pages'] ?? 1) > 1): ?>
					<div class="mt-4 d-flex justify-content-center">
						<ul class="pagination">
							<li class="page-item <?= (int) $pagination['page'] <= 1 ? 'disabled' : ''; ?>"><a class="page-link" href="<?= $page_url(max(1, (int) $pagination['page'] - 1)); ?>">Prev</a></li>
							<?php for ($i = max(1, (int) $pagination['page'] - 2); $i <= min((int) $pagination['total_pages'], (int) $pagination['page'] + 2); $i++): ?>
								<li class="page-item <?= $i === (int) $pagination['page'] ? 'active' : ''; ?>"><a class="page-link" href="<?= $page_url($i); ?>"><?= $i; ?></a></li>
							<?php endfor; ?>
							<li class="page-item <?= (int) $pagination['page'] >= (int) $pagination['total_pages'] ? 'disabled' : ''; ?>"><a class="page-link" href="<?= $page_url(min((int) $pagination['total_pages'], (int) $pagination['page'] + 1)); ?>">Next</a></li>
						</ul>
					</div>
				<?php endif; ?>
			</div>
		</section>
	</main>
</body>
</html>
