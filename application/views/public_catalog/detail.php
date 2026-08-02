<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tabler_css = 'https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css';
$tabler_icons_css = 'https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/tabler-icons.min.css';
$status_labels = [
	'available' => 'Tersedia',
	'loaned' => 'Dipinjam',
	'missing' => 'Hilang',
	'damaged' => 'Rusak',
	'unknown' => 'Belum Dipetakan',
];
$status_badges = [
	'available' => 'bg-green-lt',
	'loaned' => 'bg-yellow-lt',
	'missing' => 'bg-red-lt',
	'damaged' => 'bg-orange-lt',
	'unknown' => 'bg-secondary-lt',
];
$policy_labels = [
	'online_only' => 'Online saja',
	'download_allowed' => 'Boleh download',
	'location_only' => 'Kunci GPS + token',
	'member_only' => 'Member saja',
	'internal' => 'Internal',
];
$url_path = function ($path) {
	$segments = explode('/', str_replace('\\', '/', trim((string) $path, '/')));
	return implode('/', array_map('rawurlencode', $segments));
};
if (! empty($book['cover_local_path'])) {
	$cover_url = base_url($url_path($book['cover_local_path']));
} elseif (! empty($book['cover_source_path'])) {
	$cover_url = base_url($url_path('assets/uploads/inlislite/source_mirror/' . $book['cover_source_path']));
} else {
	$cover_url = '';
}
$auth_user = (array) $this->session->userdata('auth_user');
$role_codes = array_map(function ($role) {
	return $role['code'] ?? '';
}, (array) $this->session->userdata('user_roles'));
$is_logged_in = ! empty($auth_user['id']);
$dashboard_url = (in_array('SUPERADMIN', $role_codes, true) || in_array('ADMIN', $role_codes, true)) ? base_url('admin') : base_url('user/dashboard');
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
<body class="public-page public-catalog-page">
	<header class="public-nav">
		<a class="public-brand" href="<?= base_url(); ?>">
			<span class="brand-logo-shell"><img class="brand-logo" src="<?= base_url('img/logo-small.jpeg'); ?>" alt="Logo Kabupaten Rembang"></span>
			<span class="public-brand-text">Pustaka Digital Rembang</span>
		</a>
		<div class="public-agency-strip" aria-label="Logo instansi">
			<img src="<?= base_url('img/perpusnas.png'); ?>" alt="Logo Perpusnas">
		</div>
		<nav class="public-links">
			<a href="<?= base_url(); ?>">Beranda</a>
			<a href="<?= base_url('katalog'); ?>">Katalog</a>
			<?php if ($is_logged_in): ?>
				<a href="<?= $dashboard_url; ?>">Dashboard</a>
				<a href="<?= base_url('logout'); ?>" class="btn btn-primary btn-sm">Logout</a>
			<?php else: ?>
				<a href="<?= base_url('membership/register'); ?>">Daftar Member</a>
				<a href="<?= base_url('login'); ?>" class="btn btn-primary btn-sm">Masuk</a>
			<?php endif; ?>
		</nav>
	</header>
	<nav class="public-mobile-nav" aria-label="Navigasi publik">
		<a href="<?= base_url(); ?>"><i class="ti ti-home"></i><span>Beranda</span></a>
		<a href="<?= base_url('katalog'); ?>" class="active"><i class="ti ti-search"></i><span>Katalog</span></a>
		<?php if ($is_logged_in): ?>
			<a href="<?= $dashboard_url; ?>"><i class="ti ti-id"></i><span>Dashboard</span></a>
			<a href="<?= base_url('logout'); ?>"><i class="ti ti-logout"></i><span>Logout</span></a>
		<?php else: ?>
			<a href="<?= base_url('membership/register'); ?>"><i class="ti ti-user-plus"></i><span>Daftar</span></a>
			<a href="<?= base_url('login'); ?>"><i class="ti ti-login"></i><span>Masuk</span></a>
		<?php endif; ?>
	</nav>

	<main class="public-catalog-main">
		<?php if ($this->session->flashdata('success')): ?>
			<div class="container-xl pt-3"><div class="alert alert-success"><?= html_escape($this->session->flashdata('success')); ?></div></div>
		<?php endif; ?>
		<?php if ($this->session->flashdata('error')): ?>
			<div class="container-xl pt-3"><div class="alert alert-danger"><?= html_escape($this->session->flashdata('error')); ?></div></div>
		<?php endif; ?>

		<section class="public-book-detail-hero">
			<div class="container-xl">
				<a href="<?= base_url('katalog'); ?>" class="btn btn-outline-primary mb-3"><i class="ti ti-arrow-left me-1"></i>Kembali</a>
				<div class="public-book-detail-layout">
					<div class="public-book-detail-cover">
						<?php if ($cover_url): ?>
							<img src="<?= html_escape($cover_url); ?>" alt="Cover <?= html_escape($book['title']); ?>">
						<?php else: ?>
							<i class="ti ti-book"></i>
						<?php endif; ?>
					</div>
					<div>
						<div class="section-kicker">Detail katalog</div>
						<h1><?= html_escape($book['title']); ?></h1>
						<p class="lead"><?= html_escape($book['statement_responsibility'] ?: 'Penulis belum tercatat'); ?></p>
						<div class="public-detail-badges">
							<span class="badge bg-blue-lt"><?= number_format((int) $book['public_item_count'], 0, ',', '.'); ?> eksemplar publik</span>
							<span class="badge <?= (int) $book['available_count'] > 0 ? 'bg-green-lt' : 'bg-secondary-lt'; ?>"><?= number_format((int) $book['available_count'], 0, ',', '.'); ?> tersedia</span>
						</div>
						<a href="#request-buku" class="btn btn-primary mt-3">
							<i class="ti ti-book-upload me-1"></i><?= (int) $book['available_count'] > 0 ? 'Reservasi Buku' : 'Request Buku'; ?>
						</a>
						<div class="public-detail-grid">
							<div><span>ISBN</span><strong><?= html_escape($book['isbn'] ?: '-'); ?></strong></div>
							<div><span>Penerbit</span><strong><?= html_escape($book['publisher'] ?: '-'); ?></strong></div>
							<div><span>Tahun</span><strong><?= html_escape($book['publish_year'] ?: '-'); ?></strong></div>
							<div><span>No Panggil</span><strong><?= html_escape($book['call_number'] ?: '-'); ?></strong></div>
							<div><span>Klasifikasi</span><strong><?= html_escape($book['classification'] ?: '-'); ?></strong></div>
							<div><span>Bahasa</span><strong><?= html_escape($book['language'] ?: '-'); ?></strong></div>
						</div>
						<?php if (! empty($digital_assets)): ?>
							<div class="public-digital-read-panel">
								<div>
									<div class="section-kicker">Buku digital</div>
									<h2>Baca online melalui reader aplikasi.</h2>
									<p>File tidak dibuka dari folder publik. Akses diarahkan melalui sesi login, policy aset, token, dan audit reader.</p>
								</div>
								<div class="public-digital-actions">
									<?php foreach ($digital_assets as $asset): ?>
										<a href="<?= base_url('reader/read/' . (int) $asset['id']); ?>" class="btn <?= $asset['access_policy'] === 'download_allowed' ? 'btn-primary' : 'btn-outline-primary'; ?>">
											<i class="ti ti-book-reader me-1"></i>Baca Online
											<span><?= html_escape($policy_labels[$asset['access_policy']] ?? $asset['access_policy']); ?></span>
										</a>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</section>

		<section class="public-section">
			<div class="container-xl">
				<div class="row g-4">
					<div class="col-lg-4">
						<h2 class="public-detail-section-title">Subjek dan catatan</h2>
						<div class="public-chip-list">
							<?php foreach ($subjects as $subject): ?><span><?= html_escape($subject['subject']); ?></span><?php endforeach; ?>
							<?php if (empty($subjects)): ?><span>Subjek belum tercatat</span><?php endif; ?>
						</div>
						<p class="text-secondary mt-3"><?= nl2br(html_escape($book['abstract'] ?: 'Ringkasan belum tersedia.')); ?></p>
					</div>
					<div class="col-lg-8">
						<h2 class="public-detail-section-title">Lokasi eksemplar</h2>
						<div class="table-responsive public-table-shell">
							<table class="table table-vcenter">
								<thead><tr><th>Barcode</th><th>Lokasi</th><th>Kategori</th><th>Status</th></tr></thead>
								<tbody>
									<?php if (empty($items)): ?><tr><td colspan="4" class="text-center text-secondary py-4">Eksemplar publik belum tersedia.</td></tr><?php endif; ?>
									<?php foreach ($items as $item): ?>
										<tr>
											<td data-label="Barcode"><code><?= html_escape($item['barcode'] ?: '-'); ?></code></td>
											<td data-label="Lokasi">
												<div><?= html_escape($item['location_room_name'] ?: ($item['location_name'] ?: '-')); ?></div>
												<div class="text-secondary small"><?= html_escape($item['location_library_name'] ?: '-'); ?></div>
											</td>
											<td data-label="Kategori">
												<div><?= html_escape($item['category_name'] ?: '-'); ?></div>
												<div class="text-secondary small"><?= html_escape($item['rule_name'] ?: '-'); ?></div>
											</td>
											<td data-label="Status"><span class="badge <?= html_escape($status_badges[$item['status']] ?? 'bg-secondary-lt'); ?>"><?= html_escape($status_labels[$item['status']] ?? ucfirst($item['status'])); ?></span></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</section>

		<section class="public-section pt-0" id="request-buku">
			<div class="container-xl">
				<div class="public-request-panel">
					<div>
						<div class="section-kicker"><?= (int) $book['available_count'] > 0 ? 'Reservasi koleksi' : 'Request koleksi'; ?></div>
						<h2><?= (int) $book['available_count'] > 0 ? 'Minta petugas menyiapkan buku ini.' : 'Ajukan permintaan buku ini.'; ?></h2>
						<p class="text-secondary">Permintaan akan masuk ke panel admin untuk diproses. Jika eksemplar tersedia, sistem menandainya sebagai reservasi awal.</p>
					</div>
					<?= form_open('katalog/request/' . (int) $book['id'], ['class' => 'public-request-form']); ?>
						<div class="row g-2">
							<div class="col-md-6">
								<label class="form-label">Nama</label>
								<input type="text" class="form-control" name="requester_name" value="<?= html_escape($current_member['full_name'] ?? ''); ?>" required>
							</div>
							<div class="col-md-6">
								<label class="form-label">No HP</label>
								<input type="text" class="form-control" name="requester_phone" value="<?= html_escape($current_member['phone'] ?? ''); ?>">
							</div>
							<div class="col-md-12">
								<label class="form-label">Email</label>
								<input type="email" class="form-control" name="requester_email" value="<?= html_escape($current_member['email'] ?? ''); ?>">
							</div>
							<div class="col-md-12">
								<label class="form-label">Catatan</label>
								<textarea class="form-control" name="message" rows="3" placeholder="Opsional, misalnya lokasi pengambilan atau kebutuhan khusus."></textarea>
							</div>
							<div class="col-md-12">
								<button type="submit" class="btn btn-primary w-100">
									<i class="ti ti-send me-1"></i>Kirim <?= (int) $book['available_count'] > 0 ? 'Reservasi' : 'Request'; ?>
								</button>
							</div>
						</div>
					<?= form_close(); ?>
				</div>
			</div>
		</section>
	</main>
</body>
</html>
