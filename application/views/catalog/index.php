<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$metrics = [
	['key' => 'books', 'label' => 'Judul Buku', 'icon' => 'ti ti-books'],
	['key' => 'items', 'label' => 'Eksemplar', 'icon' => 'ti ti-barcode'],
	['key' => 'digital_assets', 'label' => 'Aset Digital', 'icon' => 'ti ti-file-type-pdf'],
	['key' => 'sync_runs', 'label' => 'Sinkronisasi', 'icon' => 'ti ti-refresh'],
];
$status_labels = [
	'draft' => 'Draft',
	'published' => 'Tayang',
	'hidden' => 'Disembunyikan',
];
$query_base = $_GET;
unset($query_base['page']);
$page_url = function ($page) use ($query_base) {
	return base_url('catalog?' . http_build_query(array_merge($query_base, ['page' => $page])));
};
$asset_url = function ($book) {
	$url_path = function ($path) {
		$segments = explode('/', str_replace('\\', '/', trim((string) $path, '/')));
		return implode('/', array_map('rawurlencode', $segments));
	};

	if (! empty($book['cover_local_path'])) {
		return base_url($url_path($book['cover_local_path']));
	}

	if (! empty($book['cover_source_path'])) {
		return base_url($url_path('assets/uploads/inlislite/source_mirror/' . $book['cover_source_path']));
	}

	return base_url('assets/img/book-cover-default.webp');
};
$filter_options = $filter_options ?? ['sources' => [], 'collection_types' => [], 'categories' => [], 'medias' => [], 'rules' => [], 'locations' => []];
$source_labels = [
	'inlislite_v3' => 'INLISLite',
	'buku_kemendikdasmen_2026' => 'Buku Digital Kemendikdasmen',
	'sample_free_pdf' => 'Sampel PDF Bebas',
	'manual' => 'Input Manual',
];
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Fase 2</div>
				<h1 class="page-title">Katalog Koleksi</h1>
			</div>
			<div class="col-auto ms-auto">
				<div class="btn-list">
					<?php if (!empty($current_user['is_superadmin']) || !empty($user_perms['catalog.index']['can_export'])): ?>
					<a class="btn btn-success" href="<?= html_escape(base_url('catalog/export?'.http_build_query(array_merge($filters,['format'=>'xlsx'])))); ?>" title="Unduh katalog Excel (.xlsx)" aria-label="Unduh katalog Excel (.xlsx)"><i class="ti ti-download me-1" aria-hidden="true"></i>Excel</a>
					<a class="btn btn-primary" href="<?= html_escape(base_url('catalog/export?'.http_build_query($filters))); ?>" title="Unduh katalog CSV" aria-label="Unduh katalog CSV"><i class="ti ti-download me-1" aria-hidden="true"></i>CSV</a><?php endif; ?>
					<a class="btn btn-outline-primary" href="<?= html_escape(base_url('catalog/annual-report?'.http_build_query($filters))); ?>"><i class="ti ti-chart-bar me-1"></i>Perkembangan tahunan</a>
					<a href="<?= base_url('catalog/create'); ?>" class="btn btn-outline-primary">
						<i class="ti ti-plus me-1"></i>Tambah
					</a>
					<a href="<?= base_url('catalog/masters'); ?>" class="btn btn-outline-primary">
						<i class="ti ti-category me-1"></i>Master Katalog
					</a>
					<a href="<?= base_url('catalog/highlights'); ?>" class="btn btn-outline-primary">
						<i class="ti ti-sparkles me-1"></i>Highlight
					</a>
					<a href="<?= base_url('catalog/sync'); ?>" class="btn btn-primary">
						<i class="ti ti-refresh me-1"></i>Sinkronisasi
					</a>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="page-body">
	<div class="container-xl">
		<div class="metric-ribbon">
			<?php foreach ($metrics as $metric): ?>
				<div class="metric-ribbon-item">
					<span class="metric-icon"><i class="<?= html_escape($metric['icon']); ?>"></i></span>
					<div>
						<div class="metric-value"><?= number_format((int) ($stats[$metric['key']] ?? 0), 0, ',', '.'); ?></div>
						<div class="metric-label"><?= html_escape($metric['label']); ?></div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="card admin-card data-workspace">
			<div class="card-header workspace-header">
				<div>
					<h2 class="card-title">Data Katalog</h2>
					<div class="text-secondary small">Bibliografi hasil sinkronisasi dengan filter, pagination, cover, dan ringkasan eksemplar.</div>
				</div>
				<ul class="nav nav-tabs card-header-tabs workspace-tabs" role="tablist">
					<li class="nav-item" role="presentation"><a href="#tab-catalog-data" class="nav-link active" data-bs-toggle="tab" role="tab"><i class="ti ti-table me-1"></i>Data</a></li>
					<li class="nav-item" role="presentation"><a href="#tab-catalog-source" class="nav-link" data-bs-toggle="tab" role="tab"><i class="ti ti-database-search me-1"></i>Data Lokal</a></li>
					<li class="nav-item" role="presentation"><a href="#tab-catalog-schema" class="nav-link" data-bs-toggle="tab" role="tab"><i class="ti ti-sitemap me-1"></i>Mapping</a></li>
				</ul>
			</div>

			<div class="tab-content">
				<div class="tab-pane active show" id="tab-catalog-data" role="tabpanel">
					<div class="card-body workspace-filter">
						<?= form_open('catalog', ['method' => 'get', 'class' => 'row g-2 align-items-end']); ?>
							<div class="col-md-3">
								<label class="form-label">Cari</label>
								<input type="text" class="form-control" name="q" value="<?= html_escape($filters['q'] ?? ''); ?>" placeholder="Judul, penulis, ISBN, barcode">
							</div>
							<div class="col-md-2">
								<label class="form-label">Status</label>
								<select class="form-select" name="status">
									<option value="">Semua</option>
									<?php foreach ($status_labels as $value => $label): ?>
										<option value="<?= $value; ?>" <?= ($filters['status'] ?? '') === $value ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-md-2">
								<label class="form-label">Kategori Isi</label>
								<select class="form-select" name="content_category_id">
									<option value="">Semua</option>
									<?php foreach ($content_categories as $category): ?>
										<option value="<?= (int) $category['id']; ?>" <?= (int) ($filters['content_category_id'] ?? 0) === (int) $category['id'] ? 'selected' : ''; ?>><?= html_escape($category['name']); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-md-2">
								<label class="form-label">Klasifikasi Isi</label>
								<select class="form-select" name="content_classification_id">
									<option value="">Semua</option>
									<?php foreach ($classification_masters as $classification): ?>
										<option value="<?= (int) $classification['id']; ?>" <?= (int) ($filters['content_classification_id'] ?? 0) === (int) $classification['id'] ? 'selected' : ''; ?>><?= html_escape($classification['name']); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-md-3">
								<label class="form-label">Sumber Data</label>
								<select class="form-select" name="source_system">
									<option value="">Semua</option>
									<?php foreach ($filter_options['sources'] as $source): ?>
										<option value="<?= html_escape($source['name']); ?>" <?= ($filters['source_system'] ?? '') === $source['name'] ? 'selected' : ''; ?>><?= html_escape($source_labels[$source['name']] ?? $source['name']); ?> (<?= number_format((int) $source['total'], 0, ',', '.'); ?>)</option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-md-2">
								<label class="form-label">Jenis Koleksi</label>
								<select class="form-select" name="collection_type">
									<option value="">Semua</option>
									<?php foreach (($filter_options['collection_types'] ?? []) as $type): ?>
										<option value="<?= html_escape($type['name']); ?>" <?= ($filters['collection_type'] ?? '') === $type['name'] ? 'selected' : ''; ?>><?= html_escape($type['name']); ?> (<?= number_format((int) $type['total'], 0, ',', '.'); ?>)</option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-md-2">
								<label class="form-label">Kategori Sumber</label>
								<select class="form-select" name="category">
									<option value="">Semua</option>
									<?php foreach ($filter_options['categories'] as $category): ?>
										<option value="<?= html_escape($category['name']); ?>" <?= ($filters['category'] ?? '') === $category['name'] ? 'selected' : ''; ?>><?= html_escape($category['name']); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-md-2">
								<label class="form-label">Media</label>
								<select class="form-select" name="media">
									<option value="">Semua</option>
									<?php foreach ($filter_options['medias'] as $media): ?>
										<option value="<?= html_escape($media['name']); ?>" <?= ($filters['media'] ?? '') === $media['name'] ? 'selected' : ''; ?>><?= html_escape($media['name']); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-md-2">
								<label class="form-label">Aturan</label>
								<select class="form-select" name="rule">
									<option value="">Semua</option>
									<?php foreach ($filter_options['rules'] as $rule): ?>
										<option value="<?= html_escape($rule['name']); ?>" <?= ($filters['rule'] ?? '') === $rule['name'] ? 'selected' : ''; ?>><?= html_escape($rule['name']); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-md-3">
								<label class="form-label">Lokasi Perpustakaan</label>
								<select class="form-select" name="location_library">
									<option value="">Semua</option>
									<?php foreach ($filter_options['locations'] as $location): ?>
										<option value="<?= html_escape($location['name']); ?>" <?= ($filters['location_library'] ?? '') === $location['name'] ? 'selected' : ''; ?>><?= html_escape($location['name']); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-md-2">
								<label class="form-label">Akses</label>
								<select class="form-select" name="availability">
									<option value="">Semua</option>
									<option value="digital" <?= ($filters['availability'] ?? '') === 'digital' ? 'selected' : ''; ?>>Buku digital</option>
									<option value="with_items" <?= ($filters['availability'] ?? '') === 'with_items' ? 'selected' : ''; ?>>Ada eksemplar</option>
									<option value="available" <?= ($filters['availability'] ?? '') === 'available' ? 'selected' : ''; ?>>Tersedia dipinjam</option>
								</select>
							</div>
							<div class="col-md-2">
								<label class="form-label">Tahun</label>
								<input type="text" class="form-control" name="publish_year" value="<?= html_escape($filters['publish_year'] ?? ''); ?>" placeholder="2004">
							</div>
							<div class="col-md-1">
								<label class="form-label">Baris</label>
								<select class="form-select" name="per_page">
									<?php foreach ([10, 25, 50, 100] as $limit): ?>
										<option value="<?= $limit; ?>" <?= (int) ($filters['per_page'] ?? 25) === $limit ? 'selected' : ''; ?>><?= $limit; ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-md-2">
								<button type="submit" class="btn btn-outline-primary w-100">
									<i class="ti ti-filter me-1"></i>Filter
								</button>
							</div>
							<div class="col-md-1">
								<a href="<?= base_url('catalog'); ?>" class="btn btn-outline-secondary w-100" title="Reset filter" aria-label="Reset filter"><i class="ti ti-refresh"></i></a>
							</div>
						<?= form_close(); ?>
					</div>

					<div class="table-responsive">
						<table class="table table-vcenter card-table">
							<thead>
								<tr>
									<th>Buku</th>
									<th>Penerbit</th>
									<th>Klasifikasi</th>
									<th>Eksemplar</th>
									<th>Status</th>
									<th class="w-1">Aksi</th>
								</tr>
							</thead>
							<tbody>
								<?php if (empty($books)): ?>
									<tr>
										<td colspan="6" class="text-center text-secondary py-4">Belum ada data katalog sesuai filter.</td>
									</tr>
								<?php endif; ?>
								<?php foreach ($books as $book): ?>
									<?php $cover_url = $asset_url($book); ?>
									<tr>
										<td>
											<div class="entity-main">
												<div class="cover-thumb">
													<?php if ($cover_url): ?>
														<img src="<?= html_escape($cover_url); ?>" alt="Cover <?= html_escape($book['title']); ?>" loading="lazy">
													<?php else: ?>
														<i class="ti ti-book"></i>
													<?php endif; ?>
												</div>
												<div>
													<div class="fw-semibold"><?= html_escape($book['title']); ?></div>
													<div class="text-secondary small"><?= html_escape($book['statement_responsibility'] ?: '-'); ?></div>
													<div class="small"><code><?= html_escape($book['isbn'] ?: 'ISBN -'); ?></code></div>
												</div>
											</div>
										</td>
										<td><?= html_escape(trim(($book['publisher'] ?: '-') . ' ' . ($book['publish_year'] ?: ''))); ?></td>
										<td>
											<div><?= html_escape($book['classification'] ?: '-'); ?></div>
											<div class="text-secondary small"><?= html_escape($book['content_classification_name'] ?: ($book['call_number'] ?: '-')); ?></div>
									<div class="small text-blue"><?= html_escape($book['content_category_name'] ?: 'Belum dipetakan'); ?></div>
									<?php if (! empty($book['collection_types'])): ?><div class="small text-secondary">Jenis: <?= html_escape($book['collection_types']); ?></div><?php endif; ?>
										</td>
										<td><span class="badge bg-blue-lt"><?= number_format((int) $book['item_count'], 0, ',', '.'); ?></span></td>
										<td><span class="badge bg-blue-lt"><?= html_escape($status_labels[$book['status']] ?? ucfirst($book['status'])); ?></span></td>
										<td>
											<div class="btn-list flex-nowrap">
												<a class="btn btn-sm btn-action btn-action-muted" href="<?= base_url('catalog/detail/' . (int) $book['id']); ?>">
													<i class="ti ti-eye"></i><span>Detail</span>
												</a>
												<a class="btn btn-sm btn-action btn-action-primary" href="<?= base_url('catalog/edit/' . (int) $book['id']); ?>">
													<i class="ti ti-edit"></i><span>Edit</span>
												</a>
											</div>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>

					<div class="card-footer responsive-footer">
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

				<div class="tab-pane" id="tab-catalog-source" role="tabpanel">
					<div class="list-group list-group-flush">
						<?php foreach ($local_stats as $source): ?>
							<div class="list-group-item d-flex align-items-center">
								<div class="flex-fill"><?= html_escape($source['label']); ?></div>
								<span class="badge bg-blue-lt"><?= number_format((int) $source['value'], 0, ',', '.'); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				</div>

				<div class="tab-pane" id="tab-catalog-schema" role="tabpanel">
					<div class="table-responsive">
						<table class="table table-vcenter card-table">
							<thead>
								<tr><th>Domain</th><th>Tabel</th><th>Fungsi</th></tr>
							</thead>
							<tbody>
								<tr><td class="fw-semibold">Bibliografi</td><td><code>books</code></td><td>Judul, penerbit, ISBN, klasifikasi, cover, metadata utama.</td></tr>
								<tr><td class="fw-semibold">Kontributor</td><td><code>book_authors</code>, <code>book_subjects</code></td><td>Penulis, peran, subjek untuk pencarian.</td></tr>
								<tr><td class="fw-semibold">Eksemplar</td><td><code>book_items</code></td><td>Barcode, lokasi, status, dan relasi perpustakaan.</td></tr>
								<tr><td class="fw-semibold">Digital</td><td><code>digital_assets</code></td><td>File PDF, kebijakan akses, dan status aset digital.</td></tr>
								<tr><td class="fw-semibold">Sinkronisasi</td><td><code>catalog_sync_runs</code>, <code>catalog_sync_maps</code></td><td>Riwayat import dan pemetaan ID sumber.</td></tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
