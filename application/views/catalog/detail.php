<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$status_labels = [
	'draft' => 'Draft',
	'published' => 'Tayang',
	'hidden' => 'Disembunyikan',
];
$item_status_labels = [
	'available' => 'Tersedia',
	'loaned' => 'Dipinjam',
	'missing' => 'Hilang',
	'damaged' => 'Rusak',
	'unknown' => 'Belum Dipetakan',
];
$inlislite_base = preg_replace('#/pustaka/?$#', '/inlislite3/', base_url());
$cover_url = ! empty($book['cover_path']) ? $inlislite_base . 'uploaded_files/sampul_koleksi/original/Monograf/' . rawurlencode($book['cover_path']) : '';
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Katalog</div>
				<h1 class="page-title">Detail Buku</h1>
			</div>
			<div class="col-auto ms-auto">
				<a href="<?= base_url('catalog'); ?>" class="btn btn-outline-secondary">
					<i class="ti ti-arrow-left me-1"></i>Katalog
				</a>
			</div>
		</div>
	</div>
</div>

<div class="page-body">
	<div class="container-xl">
		<div class="row row-cards">
			<div class="col-lg-4">
				<div class="card admin-card">
					<div class="card-body text-center">
						<div class="cover-detail mx-auto mb-3">
							<?php if ($cover_url): ?>
								<img src="<?= html_escape($cover_url); ?>" alt="Cover <?= html_escape($book['title']); ?>">
							<?php else: ?>
								<i class="ti ti-book"></i>
							<?php endif; ?>
						</div>
						<h2 class="h3 mb-1"><?= html_escape($book['title']); ?></h2>
						<div class="text-secondary"><?= html_escape($book['statement_responsibility'] ?: '-'); ?></div>
						<div class="mt-3">
							<span class="badge bg-blue-lt"><?= html_escape($status_labels[$book['status']] ?? ucfirst($book['status'])); ?></span>
							<span class="badge bg-secondary-lt"><?= number_format((int) $book['item_count'], 0, ',', '.'); ?> eksemplar</span>
						</div>
					</div>
				</div>
			</div>

			<div class="col-lg-8">
				<div class="card admin-card mb-3">
					<div class="card-header"><h2 class="card-title">Bibliografi</h2></div>
					<div class="card-body">
						<div class="datagrid">
							<div class="datagrid-item"><div class="datagrid-title">ID Sumber</div><div class="datagrid-content"><?= html_escape($book['source_id'] ?: '-'); ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">ISBN</div><div class="datagrid-content"><?= html_escape($book['isbn'] ?: '-'); ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">Penerbit</div><div class="datagrid-content"><?= html_escape($book['publisher'] ?: '-'); ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">Tahun</div><div class="datagrid-content"><?= html_escape($book['publish_year'] ?: '-'); ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">Tempat</div><div class="datagrid-content"><?= html_escape($book['publish_place'] ?: '-'); ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">Edisi</div><div class="datagrid-content"><?= html_escape($book['edition'] ?: '-'); ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">Klasifikasi</div><div class="datagrid-content"><?= html_escape($book['classification'] ?: '-'); ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">No. Panggil</div><div class="datagrid-content"><?= html_escape($book['call_number'] ?: '-'); ?></div></div>
						</div>
					</div>
				</div>

				<div class="row row-cards mb-3">
					<div class="col-md-6">
						<div class="card admin-card h-100">
							<div class="card-header"><h2 class="card-title">Penulis</h2></div>
							<div class="card-body">
								<div class="chip-list">
									<?php foreach ($authors as $author): ?><span class="chip"><?= html_escape($author['name']); ?></span><?php endforeach; ?>
									<?php if (empty($authors)): ?><span class="text-secondary">Belum ada penulis.</span><?php endif; ?>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="card admin-card h-100">
							<div class="card-header"><h2 class="card-title">Subjek</h2></div>
							<div class="card-body">
								<div class="chip-list">
									<?php foreach ($subjects as $subject): ?><span class="chip"><?= html_escape($subject['subject']); ?></span><?php endforeach; ?>
									<?php if (empty($subjects)): ?><span class="text-secondary">Belum ada subjek.</span><?php endif; ?>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="card admin-card">
					<div class="card-header"><h2 class="card-title">Eksemplar</h2></div>
					<div class="table-responsive">
						<table class="table table-vcenter card-table">
							<thead><tr><th>Barcode</th><th>No Induk</th><th>No Panggil</th><th>Lokasi</th><th>Status</th></tr></thead>
							<tbody>
								<?php if (empty($items)): ?><tr><td colspan="5" class="text-center text-secondary py-4">Belum ada eksemplar.</td></tr><?php endif; ?>
								<?php foreach ($items as $item): ?>
									<tr>
										<td><code><?= html_escape($item['barcode'] ?: '-'); ?></code></td>
										<td><?= html_escape($item['inventory_number'] ?: '-'); ?></td>
										<td><?= html_escape($item['call_number'] ?: '-'); ?></td>
										<td><?= html_escape($item['location_name'] ?: '-'); ?></td>
										<td><span class="badge bg-blue-lt"><?= html_escape($item_status_labels[$item['status']] ?? ucfirst($item['status'])); ?></span></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
