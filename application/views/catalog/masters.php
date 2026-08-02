<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$active_tab = $active_tab ?? 'categories';
$status_badge = function ($row) {
	return ! empty($row['is_active'])
		? '<span class="badge bg-green-lt">Aktif</span>'
		: '<span class="badge bg-secondary-lt">Nonaktif</span>';
};
$category_modal = function ($row = null) {
	$is_edit = ! empty($row);
	return [
		'id' => $is_edit ? 'category-modal-' . (int) $row['id'] : 'category-modal-new',
		'title' => $is_edit ? 'Edit Kategori' : 'Tambah Kategori',
		'action' => $is_edit ? base_url('catalog/masters/categories/update/' . (int) $row['id']) : base_url('catalog/masters/categories/store'),
		'row' => $row ?: ['code' => '', 'name' => '', 'description' => '', 'sort_order' => 0, 'is_active' => 1],
	];
};
$classification_modal = function ($row = null) {
	$is_edit = ! empty($row);
	return [
		'id' => $is_edit ? 'classification-modal-' . (int) $row['id'] : 'classification-modal-new',
		'title' => $is_edit ? 'Edit Klasifikasi' : 'Tambah Klasifikasi',
		'action' => $is_edit ? base_url('catalog/masters/classifications/update/' . (int) $row['id']) : base_url('catalog/masters/classifications/store'),
		'row' => $row ?: ['code' => '', 'name' => '', 'description' => '', 'sort_order' => 0, 'is_active' => 1],
	];
};
$render_modal = function ($config, $code_hint) {
	$row = $config['row'];
	?>
	<div class="modal modal-blur fade" id="<?= html_escape($config['id']); ?>" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<?= form_open($config['action'], ['class' => 'modal-content']); ?>
				<div class="modal-header">
					<h5 class="modal-title"><?= html_escape($config['title']); ?></h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-4 mb-3">
							<label class="form-label">Kode</label>
							<input type="text" class="form-control" name="code" value="<?= html_escape($row['code'] ?? ''); ?>" placeholder="<?= html_escape($code_hint); ?>">
						</div>
						<div class="col-md-8 mb-3">
							<label class="form-label">Nama</label>
							<input type="text" class="form-control" name="name" value="<?= html_escape($row['name'] ?? ''); ?>" required>
						</div>
					</div>
					<div class="mb-3">
						<label class="form-label">Deskripsi</label>
						<textarea class="form-control" name="description" rows="3"><?= html_escape($row['description'] ?? ''); ?></textarea>
					</div>
					<div class="row align-items-end">
						<div class="col-md-6 mb-3">
							<label class="form-label">Urutan</label>
							<input type="number" class="form-control" name="sort_order" value="<?= (int) ($row['sort_order'] ?? 0); ?>">
						</div>
						<div class="col-md-6 mb-3">
							<label class="form-check form-switch">
								<input class="form-check-input" type="checkbox" name="is_active" value="1" <?= ! empty($row['is_active']) ? 'checked' : ''; ?>>
								<span class="form-check-label">Aktif untuk filter</span>
							</label>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
					<button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Simpan</button>
				</div>
			<?= form_close(); ?>
		</div>
	</div>
	<?php
};
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Katalog</div>
				<h1 class="page-title">Master Buku</h1>
			</div>
			<div class="col-auto ms-auto">
				<a href="<?= base_url('catalog'); ?>" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i>Katalog</a>
			</div>
		</div>
	</div>
</div>

<div class="page-body">
	<div class="container-xl">
		<?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?= html_escape($this->session->flashdata('success')); ?></div><?php endif; ?>
		<?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?= html_escape($this->session->flashdata('error')); ?></div><?php endif; ?>

		<div class="card admin-card data-workspace">
			<div class="card-header workspace-header">
				<div>
					<h2 class="card-title">Taksonomi Pencarian</h2>
					<div class="text-secondary small">Kategori isi menjadi payung pencarian; klasifikasi isi/DDC menjadi penyaring subjek lanjutan. Di katalog publik, pilihan klasifikasi disaring sesuai kategori yang dipilih.</div>
				</div>
				<ul class="nav nav-tabs card-header-tabs workspace-tabs" role="tablist">
					<li class="nav-item" role="presentation"><a href="#tab-categories" class="nav-link <?= $active_tab === 'categories' ? 'active' : ''; ?>" data-bs-toggle="tab" role="tab"><i class="ti ti-category me-1"></i>Kategori</a></li>
					<li class="nav-item" role="presentation"><a href="#tab-classifications" class="nav-link <?= $active_tab === 'classifications' ? 'active' : ''; ?>" data-bs-toggle="tab" role="tab"><i class="ti ti-tags me-1"></i>Klasifikasi</a></li>
				</ul>
			</div>

			<div class="tab-content">
				<div class="tab-pane <?= $active_tab === 'categories' ? 'active show' : ''; ?>" id="tab-categories" role="tabpanel">
					<div class="card-body workspace-filter d-flex justify-content-between align-items-center">
						<div>
							<div class="fw-semibold">Kategori Konten</div>
							<div class="text-secondary small">Contoh: fiksi, non-fiksi, karya ilmiah, lokal Rembang.</div>
						</div>
						<?php if ($can_create): ?>
							<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#category-modal-new"><i class="ti ti-plus me-1"></i>Tambah</button>
						<?php endif; ?>
					</div>
					<div class="table-responsive">
						<table class="table table-vcenter card-table">
							<thead><tr><th>Kode</th><th>Nama</th><th>Deskripsi</th><th>Urutan</th><th>Status</th><th class="w-1">Aksi</th></tr></thead>
							<tbody>
								<?php foreach ($categories as $row): ?>
									<tr>
										<td><code><?= html_escape($row['code']); ?></code></td>
										<td class="fw-semibold"><?= html_escape($row['name']); ?></td>
										<td class="text-secondary"><?= html_escape($row['description'] ?: '-'); ?></td>
										<td><?= (int) $row['sort_order']; ?></td>
										<td><?= $status_badge($row); ?></td>
										<td>
											<?php if ($can_edit): ?>
												<button type="button" class="btn btn-sm btn-action btn-action-primary" data-bs-toggle="modal" data-bs-target="#category-modal-<?= (int) $row['id']; ?>"><i class="ti ti-edit"></i><span>Edit</span></button>
											<?php endif; ?>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>

				<div class="tab-pane <?= $active_tab === 'classifications' ? 'active show' : ''; ?>" id="tab-classifications" role="tabpanel">
					<div class="card-body workspace-filter d-flex justify-content-between align-items-center">
						<div>
							<div class="fw-semibold">Klasifikasi Isi</div>
							<div class="text-secondary small">Ringkasan DDC dan kelompok lokal untuk filter yang mudah dipahami.</div>
						</div>
						<?php if ($can_create): ?>
							<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#classification-modal-new"><i class="ti ti-plus me-1"></i>Tambah</button>
						<?php endif; ?>
					</div>
					<div class="table-responsive">
						<table class="table table-vcenter card-table">
							<thead><tr><th>Kode</th><th>Nama</th><th>Deskripsi</th><th>Urutan</th><th>Status</th><th class="w-1">Aksi</th></tr></thead>
							<tbody>
								<?php foreach ($classifications as $row): ?>
									<tr>
										<td><code><?= html_escape($row['code']); ?></code></td>
										<td class="fw-semibold"><?= html_escape($row['name']); ?></td>
										<td class="text-secondary"><?= html_escape($row['description'] ?: '-'); ?></td>
										<td><?= (int) $row['sort_order']; ?></td>
										<td><?= $status_badge($row); ?></td>
										<td>
											<?php if ($can_edit): ?>
												<button type="button" class="btn btn-sm btn-action btn-action-primary" data-bs-toggle="modal" data-bs-target="#classification-modal-<?= (int) $row['id']; ?>"><i class="ti ti-edit"></i><span>Edit</span></button>
											<?php endif; ?>
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

<?php
if ($can_create) {
	$render_modal($category_modal(), 'fiksi');
	$render_modal($classification_modal(), '800');
}
if ($can_edit) {
	foreach ($categories as $row) {
		$render_modal($category_modal($row), 'fiksi');
	}
	foreach ($classifications as $row) {
		$render_modal($classification_modal($row), '800');
	}
}
?>
