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
$item_status_badges = [
	'available' => 'bg-green-lt',
	'loaned' => 'bg-yellow-lt',
	'missing' => 'bg-red-lt',
	'damaged' => 'bg-orange-lt',
	'unknown' => 'bg-secondary-lt',
];
$policy_labels = [
	'online_only' => 'Online aman',
	'download_allowed' => 'Bebas download',
	'location_only' => 'Pojok Baca / token',
	'member_only' => 'Member saja',
	'internal' => 'Internal',
];
$rights_labels = [
	'public_domain' => 'Domain publik',
	'licensed' => 'Lisensi',
	'owned' => 'Milik perpustakaan',
	'permission_letter' => 'Surat izin',
	'internal_use' => 'Internal',
	'unknown' => 'Belum jelas',
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
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Katalog</div>
				<h1 class="page-title">Detail Buku</h1>
			</div>
			<div class="col-auto ms-auto">
				<div class="btn-list">
					<a href="<?= base_url('catalog'); ?>" class="btn btn-outline-secondary">
						<i class="ti ti-arrow-left me-1"></i>Katalog
					</a>
					<a href="<?= base_url('catalog/edit/' . (int) $book['id']); ?>" class="btn btn-primary">
						<i class="ti ti-edit me-1"></i>Edit
					</a>
					<a href="<?= base_url('catalog/delete/' . (int) $book['id']); ?>" class="btn btn-outline-danger" onclick="return confirm('Sembunyikan katalog ini dari data aktif?')">
						<i class="ti ti-trash me-1"></i>Hapus
					</a>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="page-body">
	<div class="container-xl">
		<?php if ($this->session->flashdata('success')): ?>
			<div class="alert alert-success"><?= html_escape($this->session->flashdata('success')); ?></div>
		<?php endif; ?>
		<?php if ($this->session->flashdata('error')): ?>
			<div class="alert alert-danger"><?= html_escape($this->session->flashdata('error')); ?></div>
		<?php endif; ?>

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

				<div class="card admin-card data-workspace mb-3">
					<div class="card-header workspace-header">
						<div>
							<h2 class="card-title">Ebook / Aset Digital</h2>
							<div class="text-secondary small">Opsional. Buku katalog bisa hanya fisik, atau punya satu/lebih aset PDF untuk reader.</div>
						</div>
						<a href="<?= base_url('reader/assets/create?book_id=' . (int) $book['id']); ?>" class="btn btn-primary">
							<i class="ti ti-file-plus me-1"></i>Tambah Ebook
						</a>
					</div>
					<div class="table-responsive">
						<table class="table table-vcenter card-table">
							<thead><tr><th>File</th><th>Policy</th><th>Hak Publikasi</th><th>Status</th><th>Aksi</th></tr></thead>
							<tbody>
								<?php if (empty($digital_assets)): ?><tr><td colspan="5" class="text-center text-secondary py-4">Buku ini belum punya ebook. Ini normal untuk koleksi fisik.</td></tr><?php endif; ?>
								<?php foreach ($digital_assets ?? [] as $asset): ?>
									<?php $is_locked = (int) $asset['is_downloadable'] !== 1 || $asset['access_policy'] !== 'download_allowed'; ?>
									<tr>
										<td data-label="File">
											<div class="fw-semibold"><?= html_escape($asset['file_original_name'] ?: basename((string) $asset['file_path'])); ?></div>
											<div class="text-secondary small"><?= number_format((int) ($asset['file_size'] ?? 0), 0, ',', '.'); ?> byte</div>
										</td>
										<td data-label="Policy">
											<span class="badge bg-blue-lt"><?= html_escape($policy_labels[$asset['access_policy']] ?? $asset['access_policy']); ?></span>
											<span class="badge <?= $is_locked ? 'bg-red-lt' : 'bg-green-lt'; ?>"><?= $is_locked ? 'PDF utuh dikunci' : 'Download boleh'; ?></span>
										</td>
										<td data-label="Hak Publikasi">
											<span class="badge <?= ($asset['rights_basis'] ?? 'unknown') === 'unknown' ? 'bg-yellow-lt' : 'bg-cyan-lt'; ?>"><?= html_escape($rights_labels[$asset['rights_basis'] ?? 'unknown'] ?? ($asset['rights_basis'] ?? '-')); ?></span>
											<div class="text-secondary small"><?= html_escape($asset['rights_holder'] ?: 'Pemegang hak belum dicatat'); ?></div>
										</td>
										<td data-label="Status"><span class="badge bg-secondary-lt"><?= html_escape($asset['status']); ?></span></td>
										<td data-label="Aksi">
											<div class="btn-list flex-nowrap">
												<?php if ($asset['status'] === 'active'): ?>
													<a href="<?= base_url('reader/read/' . (int) $asset['id']); ?>" class="btn btn-sm btn-action btn-action-primary">
														<i class="ti ti-book-reader"></i><span>Baca</span>
													</a>
												<?php endif; ?>
												<a href="<?= base_url('reader/assets/edit/' . (int) $asset['id']); ?>" class="btn btn-sm btn-outline-primary">
													<i class="ti ti-edit"></i><span>Edit</span>
												</a>
											</div>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>

				<div class="card admin-card data-workspace">
					<div class="card-header workspace-header">
						<div>
							<h2 class="card-title">Eksemplar</h2>
							<div class="text-secondary small">Item fisik/digital beserta lokasi, aturan pinjam, dan status OPAC.</div>
						</div>
						<?php if (! empty($can_create_item)): ?>
							<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#item-modal-create">
								<i class="ti ti-plus me-1"></i>Tambah Eksemplar
							</button>
						<?php endif; ?>
					</div>
					<div class="table-responsive">
						<table class="table table-vcenter card-table">
							<thead>
								<tr>
									<th>Identitas</th>
									<th>Lokasi</th>
									<th>Koleksi</th>
									<th>Status</th>
									<th>Publik</th>
									<th class="w-1">Aksi</th>
								</tr>
							</thead>
							<tbody>
								<?php if (empty($items)): ?><tr><td colspan="6" class="text-center text-secondary py-4">Belum ada eksemplar.</td></tr><?php endif; ?>
								<?php foreach ($items as $item): ?>
									<tr>
										<td>
											<div class="fw-semibold"><code><?= html_escape($item['barcode'] ?: '-'); ?></code></div>
											<div class="text-secondary small">No induk: <?= html_escape($item['inventory_number'] ?: ($item['item_code'] ?: '-')); ?></div>
											<div class="text-secondary small">Panggil: <?= html_escape($item['call_number'] ?: '-'); ?></div>
										</td>
										<td>
											<div><?= html_escape($item['location_room_name'] ?: ($item['location_name'] ?: '-')); ?></div>
											<div class="text-secondary small"><?= html_escape($item['location_library_name'] ?: '-'); ?></div>
										</td>
										<td>
											<div><?= html_escape($item['category_name'] ?: ($item['collection_type'] ?: '-')); ?></div>
											<div class="text-secondary small">Aturan: <?= html_escape($item['rule_name'] ?: '-'); ?></div>
											<div class="text-secondary small">Media: <?= html_escape($item['media_name'] ?: '-'); ?></div>
										</td>
										<td>
											<span class="badge <?= html_escape($item_status_badges[$item['status']] ?? 'bg-secondary-lt'); ?>"><?= html_escape($item_status_labels[$item['status']] ?? ucfirst($item['status'])); ?></span>
											<div class="text-secondary small"><?= html_escape($item['status_label'] ?: '-'); ?></div>
										</td>
										<td>
											<span class="badge <?= (int) ($item['is_public'] ?? 0) === 1 ? 'bg-blue-lt' : 'bg-secondary-lt'; ?>">
												<?= (int) ($item['is_public'] ?? 0) === 1 ? 'OPAC' : 'Internal'; ?>
											</span>
										</td>
										<td>
											<div class="btn-list flex-nowrap">
												<?php if (! empty($can_edit_item)): ?>
													<button type="button" class="btn btn-sm btn-action btn-action-primary" data-bs-toggle="modal" data-bs-target="#item-modal-<?= (int) $item['id']; ?>">
														<i class="ti ti-edit"></i><span>Edit</span>
													</button>
												<?php endif; ?>
												<?php if (! empty($can_delete_item)): ?>
													<a class="btn btn-sm btn-action btn-action-danger" href="<?= base_url('catalog/items/delete/' . (int) $book['id'] . '/' . (int) $item['id']); ?>" onclick="return confirm('Nonaktifkan eksemplar ini dari data aktif?')">
														<i class="ti ti-trash"></i><span>Nonaktifkan</span>
													</a>
												<?php endif; ?>
											</div>
										</td>
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

<?php if (! empty($can_create_item)): ?>
	<?php $this->load->view('catalog/_item_modal', ['book' => $book, 'item' => null, 'reference_options' => $reference_options]); ?>
<?php endif; ?>
<?php if (! empty($can_edit_item)): ?>
	<?php foreach ($items as $item): ?>
		<?php $this->load->view('catalog/_item_modal', ['book' => $book, 'item' => $item, 'reference_options' => $reference_options]); ?>
	<?php endforeach; ?>
<?php endif; ?>
