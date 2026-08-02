<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tabler_css = 'https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css';
$tabler_icons_css = 'https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/tabler-icons.min.css';
$url_path = function ($path) {
	$segments = explode('/', str_replace('\\', '/', trim((string) $path, '/')));
	return implode('/', array_map('rawurlencode', $segments));
};
$cover_url = function ($book) use ($url_path) {
	if (! empty($book['cover_local_path'])) {
		return base_url($url_path($book['cover_local_path']));
	}
	if (! empty($book['cover_source_path'])) {
		return base_url($url_path('assets/uploads/inlislite/source_mirror/' . $book['cover_source_path']));
	}
	return '';
};
$query_base = $_GET;
unset($query_base['page']);
$page_url = function ($page) use ($query_base) {
	return base_url('katalog?' . http_build_query(array_merge($query_base, ['page' => $page])));
};
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
		<section class="catalog-search-band">
			<div class="container-xl">
				<div class="row g-4 align-items-end">
					<div class="col-lg-7">
						<div class="section-kicker">Katalog publik</div>
						<h1>Temukan koleksi jejaring perpustakaan Rembang.</h1>
					</div>
					<div class="col-lg-5">
						<div class="catalog-count-pill">
							<i class="ti ti-books"></i>
							<span><?= number_format((int) $pagination['total_rows'], 0, ',', '.'); ?> judul sesuai filter</span>
						</div>
					</div>
				</div>

				<?= form_open('katalog', ['method' => 'get', 'class' => 'public-catalog-filter public-catalog-filter-advanced']); ?>
					<div class="row g-2 align-items-end">
						<div class="col-lg-5">
							<label class="form-label">Cari</label>
							<input type="text" class="form-control form-control-lg" name="q" value="<?= html_escape($filters['q'] ?? ''); ?>" placeholder="Judul, penulis, ISBN, barcode">
						</div>
						<div class="col-md-6 col-lg-3">
							<label class="form-label">Kategori Isi</label>
							<select class="form-select form-select-lg" name="content_category_id">
								<option value="">Semua</option>
								<?php foreach ($filter_options['content_categories'] as $category): ?>
									<option value="<?= (int) $category['id']; ?>" <?= (int) ($filters['content_category_id'] ?? 0) === (int) $category['id'] ? 'selected' : ''; ?>><?= html_escape($category['name']); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-md-6 col-lg-3">
							<label class="form-label">Klasifikasi</label>
							<select class="form-select form-select-lg" name="content_classification_id">
								<option value="">Semua</option>
								<?php foreach ($filter_options['content_classifications'] as $classification): ?>
									<option value="<?= (int) $classification['id']; ?>" <?= (int) ($filters['content_classification_id'] ?? 0) === (int) $classification['id'] ? 'selected' : ''; ?>><?= html_escape($classification['name']); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-md-6 col-lg-2">
							<label class="form-label">Kategori INLISLite</label>
							<select class="form-select form-select-lg" name="category">
								<option value="">Semua</option>
								<?php foreach ($filter_options['categories'] as $category): ?>
									<option value="<?= html_escape($category['name']); ?>" <?= ($filters['category'] ?? '') === $category['name'] ? 'selected' : ''; ?>><?= html_escape($category['name']); ?> (<?= number_format((int) $category['total'], 0, ',', '.'); ?>)</option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-md-6 col-lg-2">
							<label class="form-label">Media</label>
							<select class="form-select form-select-lg" name="media">
								<option value="">Semua</option>
								<?php foreach ($filter_options['medias'] as $media): ?>
									<option value="<?= html_escape($media['name']); ?>" <?= ($filters['media'] ?? '') === $media['name'] ? 'selected' : ''; ?>><?= html_escape($media['name']); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-md-6 col-lg-2">
							<label class="form-label">Aturan</label>
							<select class="form-select form-select-lg" name="rule">
								<option value="">Semua</option>
								<?php foreach ($filter_options['rules'] as $rule): ?>
									<option value="<?= html_escape($rule['name']); ?>" <?= ($filters['rule'] ?? '') === $rule['name'] ? 'selected' : ''; ?>><?= html_escape($rule['name']); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-md-6 col-lg-3">
							<label class="form-label">Lokasi Perpustakaan</label>
							<select class="form-select form-select-lg" name="location_library">
								<option value="">Semua</option>
								<?php foreach ($filter_options['locations'] as $location): ?>
									<option value="<?= html_escape($location['name']); ?>" <?= ($filters['location_library'] ?? '') === $location['name'] ? 'selected' : ''; ?>><?= html_escape($location['name']); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-md-6 col-lg-2">
							<label class="form-label">Tahun</label>
							<select class="form-select form-select-lg" name="publish_year">
								<option value="">Semua</option>
								<?php foreach ($filter_options['years'] as $year): ?>
									<option value="<?= html_escape($year['publish_year']); ?>" <?= ($filters['publish_year'] ?? '') === $year['publish_year'] ? 'selected' : ''; ?>><?= html_escape($year['publish_year']); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-md-6 col-lg-2">
							<label class="form-label">Ketersediaan</label>
							<select class="form-select form-select-lg" name="availability">
								<option value="">Semua</option>
								<option value="with_items" <?= ($filters['availability'] ?? '') === 'with_items' ? 'selected' : ''; ?>>Ada eksemplar</option>
								<option value="available" <?= ($filters['availability'] ?? '') === 'available' ? 'selected' : ''; ?>>Tersedia</option>
							</select>
						</div>
						<div class="col-md-6 col-lg-2">
							<label class="form-label">Baris</label>
							<select class="form-select form-select-lg" name="per_page">
								<?php foreach ([12, 24, 48] as $limit): ?>
									<option value="<?= $limit; ?>" <?= (int) ($filters['per_page'] ?? 12) === $limit ? 'selected' : ''; ?>><?= $limit; ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-md-6 col-lg-3">
							<div class="public-filter-actions">
								<button type="submit" class="btn btn-primary btn-lg"><i class="ti ti-search me-1"></i>Cari</button>
								<a href="<?= base_url('katalog'); ?>" class="btn btn-outline-primary btn-lg"><i class="ti ti-refresh me-1"></i>Reset</a>
							</div>
						</div>
					</div>
				<?= form_close(); ?>
			</div>
		</section>

		<section class="public-section">
			<div class="container-xl">
				<div class="public-book-grid">
					<?php if (empty($books)): ?>
						<div class="empty public-empty">
							<div class="empty-icon"><i class="ti ti-books-off"></i></div>
							<p class="empty-title">Koleksi tidak ditemukan</p>
							<p class="empty-subtitle text-secondary">Coba kata kunci lain atau longgarkan filter.</p>
						</div>
					<?php endif; ?>
					<?php foreach ($books as $book): ?>
						<?php $image = $cover_url($book); ?>
						<article class="public-book-card">
							<a class="public-book-cover" href="<?= base_url('katalog/detail/' . (int) $book['id']); ?>">
								<?php if ($image): ?>
									<img src="<?= html_escape($image); ?>" alt="Cover <?= html_escape($book['title']); ?>" loading="lazy">
								<?php else: ?>
									<i class="ti ti-book"></i>
								<?php endif; ?>
							</a>
							<div class="public-book-body">
								<h2><a href="<?= base_url('katalog/detail/' . (int) $book['id']); ?>"><?= html_escape($book['title']); ?></a></h2>
								<p><?= html_escape($book['statement_responsibility'] ?: 'Penulis belum tercatat'); ?></p>
								<div class="public-book-meta">
									<span><?= html_escape(trim(($book['publisher'] ?: '-') . ' ' . ($book['publish_year'] ?: ''))); ?></span>
									<span><?= number_format((int) $book['public_item_count'], 0, ',', '.'); ?> eksemplar</span>
								</div>
								<div class="public-book-status">
									<?php if (! empty($book['content_category_name']) || ! empty($book['content_classification_name'])): ?>
										<span class="badge bg-blue-lt"><?= html_escape($book['content_category_name'] ?: $book['content_classification_name']); ?></span>
									<?php endif; ?>
									<span class="badge <?= (int) $book['available_count'] > 0 ? 'bg-green-lt' : 'bg-secondary-lt'; ?>">
										<?= (int) $book['available_count'] > 0 ? number_format((int) $book['available_count'], 0, ',', '.') . ' tersedia' : 'Cek ketersediaan'; ?>
									</span>
									<?php if ((int) ($book['digital_asset_count'] ?? 0) > 0): ?>
										<span class="badge bg-cyan-lt"><i class="ti ti-file-type-pdf me-1"></i>Buku Digital</span>
									<?php endif; ?>
								</div>
								<div class="public-book-actions">
									<a href="<?= base_url('katalog/detail/' . (int) $book['id']); ?>" class="btn btn-outline-primary btn-sm">
										<i class="ti ti-eye me-1"></i>Detail
									</a>
									<?php if ((int) ($book['first_digital_asset_id'] ?? 0) > 0): ?>
										<a href="<?= base_url('reader/read/' . (int) $book['first_digital_asset_id']); ?>" class="btn btn-primary btn-sm">
											<i class="ti ti-book-reader me-1"></i>Baca Online
										</a>
									<?php endif; ?>
								</div>
							</div>
						</article>
					<?php endforeach; ?>
				</div>

				<div class="public-pagination">
					<p class="m-0 text-secondary">Menampilkan <?= number_format($pagination['total_rows'] > 0 ? $pagination['offset'] + 1 : 0, 0, ',', '.'); ?>-<?= number_format(min($pagination['offset'] + $pagination['per_page'], $pagination['total_rows']), 0, ',', '.'); ?> dari <?= number_format($pagination['total_rows'], 0, ',', '.'); ?> data</p>
					<ul class="pagination m-0">
						<li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : ''; ?>"><a class="page-link" href="<?= $page_url(max(1, $pagination['page'] - 1)); ?>">Prev</a></li>
						<?php for ($i = max(1, $pagination['page'] - 2); $i <= min($pagination['total_pages'], $pagination['page'] + 2); $i++): ?>
							<li class="page-item <?= $i === (int) $pagination['page'] ? 'active' : ''; ?>"><a class="page-link" href="<?= $page_url($i); ?>"><?= $i; ?></a></li>
						<?php endfor; ?>
						<li class="page-item <?= $pagination['page'] >= $pagination['total_pages'] ? 'disabled' : ''; ?>"><a class="page-link" href="<?= $page_url(min($pagination['total_pages'], $pagination['page'] + 1)); ?>">Next</a></li>
					</ul>
				</div>
			</div>
		</section>
	</main>
</body>
</html>
