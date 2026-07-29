<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$is_edit = ! empty($edit_page);
$action = $is_edit ? base_url('rbac/pages/update/' . (int) $edit_page['id']) : base_url('rbac/pages/store');
$value = function ($key, $default = '') use ($edit_page) {
	return $edit_page[$key] ?? $default;
};
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">RBAC Foundation</div>
				<h1 class="page-title">Registry Halaman</h1>
			</div>
		</div>
	</div>
</div>

<div class="page-body">
	<div class="container-xl">
		<?php $this->load->view('rbac/_tabs', ['active_rbac_tab' => $active_rbac_tab]); ?>
		<?php if ($this->session->flashdata('success')): ?>
			<div class="alert alert-success"><?= html_escape($this->session->flashdata('success')); ?></div>
		<?php endif; ?>
		<?php if ($this->session->flashdata('error')): ?>
			<div class="alert alert-danger"><?= html_escape($this->session->flashdata('error')); ?></div>
		<?php endif; ?>

		<div class="row row-cards">
			<div class="col-lg-4">
				<div class="card admin-card">
					<div class="card-header">
						<h2 class="card-title"><?= $is_edit ? 'Edit Halaman' : 'Tambah Halaman'; ?></h2>
					</div>
					<div class="card-body">
						<?= form_open($action); ?>
							<div class="mb-3">
								<label class="form-label">Page Code</label>
								<input type="text" class="form-control" name="code" value="<?= html_escape($value('code')); ?>" placeholder="contoh: catalog.index" required>
							</div>
							<div class="mb-3">
								<label class="form-label">Module</label>
								<input type="text" class="form-control" name="module" value="<?= html_escape($value('module')); ?>" placeholder="contoh: catalog" required>
							</div>
							<div class="mb-3">
								<label class="form-label">Judul</label>
								<input type="text" class="form-control" name="title" value="<?= html_escape($value('title')); ?>" required>
							</div>
							<div class="mb-3">
								<label class="form-label">Route</label>
								<input type="text" class="form-control" name="route" value="<?= html_escape($value('route')); ?>" placeholder="contoh: catalog" required>
							</div>
							<div class="mb-3">
								<label class="form-label">Deskripsi</label>
								<textarea class="form-control" name="description" rows="3"><?= html_escape($value('description')); ?></textarea>
							</div>
							<label class="form-check mb-3">
								<input class="form-check-input" type="checkbox" name="is_active" value="1" <?= (int) $value('is_active', 1) === 1 ? 'checked' : ''; ?>>
								<span class="form-check-label">Aktif</span>
							</label>
							<div class="btn-list">
								<button type="submit" class="btn btn-primary"><?= $is_edit ? 'Simpan Perubahan' : 'Tambah Halaman'; ?></button>
								<?php if ($is_edit): ?>
									<a href="<?= base_url('rbac/pages'); ?>" class="btn btn-outline-secondary">Batal</a>
								<?php endif; ?>
							</div>
						<?= form_close(); ?>
					</div>
				</div>
			</div>

			<div class="col-lg-8">
				<div class="card admin-card">
					<div class="card-header">
						<h2 class="card-title">Daftar Registry</h2>
					</div>
					<div class="table-responsive">
						<table class="table table-vcenter card-table">
							<thead>
								<tr>
									<th>Halaman</th>
									<th>Module</th>
									<th>Route</th>
									<th>Status</th>
									<th class="w-1">Aksi</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($pages as $page): ?>
									<tr>
										<td>
											<div class="fw-semibold"><?= html_escape($page['title']); ?></div>
											<div class="text-secondary small"><code><?= html_escape($page['code']); ?></code></div>
										</td>
										<td><span class="badge bg-blue-lt"><?= html_escape($page['module']); ?></span></td>
										<td><code><?= html_escape($page['route']); ?></code></td>
										<td><span class="badge <?= (int) $page['is_active'] === 1 ? 'bg-green-lt' : 'bg-red-lt'; ?>"><?= (int) $page['is_active'] === 1 ? 'aktif' : 'nonaktif'; ?></span></td>
										<td>
											<div class="btn-list flex-nowrap">
												<a class="btn btn-sm btn-outline-primary" href="<?= base_url('rbac/pages?edit_id=' . (int) $page['id']); ?>">Edit</a>
												<?= form_open('rbac/pages/toggle/' . (int) $page['id'], ['class' => 'd-inline']); ?>
													<button class="btn btn-sm btn-outline-secondary" type="submit"><?= (int) $page['is_active'] === 1 ? 'Nonaktifkan' : 'Aktifkan'; ?></button>
												<?= form_close(); ?>
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
