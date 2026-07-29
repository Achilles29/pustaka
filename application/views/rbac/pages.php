<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">RBAC Foundation</div>
				<h1 class="page-title">Registry Halaman</h1>
			</div>
			<div class="col-auto ms-auto">
				<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#page-modal">
					<i class="ti ti-plus me-1"></i>Tambah Halaman
				</button>
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

		<div class="row row-cards mb-3">
			<div class="col-sm-6 col-lg-3">
				<div class="card stat-card"><div class="card-body"><div class="subheader">Registry</div><div class="h1 mb-0"><?= number_format(count($pages), 0, ',', '.'); ?></div></div></div>
			</div>
			<div class="col-sm-6 col-lg-3">
				<div class="card stat-card"><div class="card-body"><div class="subheader">Module</div><div class="h1 mb-0"><?= number_format(count(array_unique(array_column($pages, 'module'))), 0, ',', '.'); ?></div></div></div>
			</div>
		</div>

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
								<td><span class="badge <?= (int) $page['is_active'] === 1 ? 'bg-green-lt' : 'bg-red-lt'; ?>"><?= (int) $page['is_active'] === 1 ? 'Aktif' : 'Nonaktif'; ?></span></td>
								<td>
									<div class="btn-list flex-nowrap">
										<a class="btn btn-sm btn-action btn-action-primary" title="Edit Halaman" href="<?= base_url('rbac/pages?edit_id=' . (int) $page['id']); ?>"><i class="ti ti-edit"></i><span>Edit</span></a>
										<?= form_open('rbac/pages/toggle/' . (int) $page['id'], ['class' => 'd-inline']); ?>
											<button class="btn btn-sm btn-action btn-action-muted" title="<?= (int) $page['is_active'] === 1 ? 'Nonaktifkan Halaman' : 'Aktifkan Halaman'; ?>" type="submit"><i class="ti <?= (int) $page['is_active'] === 1 ? 'ti-toggle-right' : 'ti-toggle-left'; ?>"></i><span><?= (int) $page['is_active'] === 1 ? 'Nonaktifkan' : 'Aktifkan'; ?></span></button>
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

<?php $this->load->view('rbac/_page_modal', ['edit_page' => $edit_page]); ?>
