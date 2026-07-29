<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$actions = [
	'view' => 'Lihat',
	'create' => 'Tambah',
	'edit' => 'Edit',
	'delete' => 'Hapus',
	'export' => 'Export',
	'approve' => 'Approve',
];
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Pengaturan Akses</div>
				<h1 class="page-title">Tipe User</h1>
			</div>
			<div class="col-auto ms-auto">
				<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#role-modal">
					<i class="ti ti-plus me-1"></i>Tambah Tipe User
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

		<div class="metric-ribbon">
			<div class="metric-ribbon-item">
				<span class="metric-icon"><i class="ti ti-users-group"></i></span>
				<div><div class="metric-value"><?= number_format(count($roles), 0, ',', '.'); ?></div><div class="metric-label">Tipe User</div></div>
			</div>
			<div class="metric-ribbon-item">
				<span class="metric-icon"><i class="ti ti-shield-check"></i></span>
				<div><div class="metric-value"><?= number_format(count(array_filter($roles, function ($role) { return (int) $role['is_active'] === 1; })), 0, ',', '.'); ?></div><div class="metric-label">Aktif</div></div>
			</div>
			<div class="metric-ribbon-item">
				<span class="metric-icon"><i class="ti ti-lock-cog"></i></span>
				<div><div class="metric-value"><?= number_format(count(array_filter($roles, function ($role) { return (int) $role['is_system'] === 1; })), 0, ',', '.'); ?></div><div class="metric-label">Sistem</div></div>
			</div>
		</div>

		<div class="card admin-card mb-3">
			<div class="card-header">
				<h2 class="card-title">Daftar Tipe User</h2>
			</div>
			<div class="table-responsive">
				<table class="table table-vcenter card-table">
					<thead>
						<tr>
							<th>Tipe User</th>
							<th>Scope</th>
							<th>Level</th>
							<th>Status</th>
							<th class="w-1">Aksi</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($roles as $role): ?>
							<tr>
								<td>
									<div class="fw-semibold"><?= html_escape($role['name']); ?></div>
									<div class="text-secondary small"><code><?= html_escape($role['code']); ?></code><?= $role['description'] ? ' - ' . html_escape($role['description']) : ''; ?></div>
								</td>
								<td><span class="chip"><?= html_escape($role['scope_type']); ?></span></td>
								<td><?= number_format((int) $role['level'], 0, ',', '.'); ?></td>
								<td>
									<span class="badge <?= (int) $role['is_active'] === 1 ? 'bg-blue-lt' : 'bg-secondary-lt'; ?>"><?= (int) $role['is_active'] === 1 ? 'Aktif' : 'Nonaktif'; ?></span>
									<?php if ((int) $role['is_system'] === 1): ?><span class="badge bg-secondary-lt ms-1">Sistem</span><?php endif; ?>
								</td>
								<td>
									<div class="btn-list flex-nowrap">
										<a class="btn btn-sm btn-action btn-action-primary" href="<?= base_url('rbac/roles?role_id=' . (int) $role['id']); ?>">
											<i class="ti ti-shield-cog"></i><span>Hak Akses</span>
										</a>
										<a class="btn btn-sm btn-action btn-action-muted" href="<?= base_url('rbac/roles?edit_id=' . (int) $role['id']); ?>">
											<i class="ti ti-edit"></i><span>Edit</span>
										</a>
										<?php if ((int) $role['is_system'] !== 1): ?>
											<?= form_open('rbac/roles/toggle/' . (int) $role['id'], ['class' => 'd-inline']); ?>
												<button class="btn btn-sm btn-action btn-action-muted" title="<?= (int) $role['is_active'] === 1 ? 'Nonaktifkan Tipe User' : 'Aktifkan Tipe User'; ?>" type="submit">
													<i class="ti <?= (int) $role['is_active'] === 1 ? 'ti-toggle-right' : 'ti-toggle-left'; ?>"></i><span><?= (int) $role['is_active'] === 1 ? 'Nonaktifkan' : 'Aktifkan'; ?></span>
												</button>
											<?= form_close(); ?>
										<?php endif; ?>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>

		<?php if (! empty($selected_role)): ?>
			<div class="card admin-card data-workspace">
				<div class="card-header workspace-header">
					<div>
						<h2 class="card-title">Hak Akses: <?= html_escape($selected_role['name']); ?></h2>
						<div class="text-secondary small">Pilih izin halaman untuk tipe user ini.</div>
					</div>
					<a href="<?= base_url('rbac/roles'); ?>" class="btn btn-outline-secondary btn-sm">Tutup Matrix</a>
				</div>
				<?= form_open('rbac/roles/save-permissions/' . (int) $selected_role['id']); ?>
					<div class="table-responsive">
						<table class="table table-vcenter card-table permission-table">
							<thead>
								<tr>
									<th>Halaman</th>
									<th>Module</th>
									<?php foreach ($actions as $label): ?>
										<th class="text-center"><?= html_escape($label); ?></th>
									<?php endforeach; ?>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($pages as $page): ?>
									<?php $perm = $permissions[(int) $page['id']] ?? []; ?>
									<tr>
										<td>
											<div class="fw-semibold"><?= html_escape($page['title']); ?></div>
											<div class="text-secondary small"><code><?= html_escape($page['code']); ?></code></div>
										</td>
										<td><span class="badge bg-blue-lt"><?= html_escape($page['module']); ?></span></td>
										<?php foreach ($actions as $action => $label): ?>
											<td class="text-center">
												<label class="form-check form-switch permission-switch">
													<input class="form-check-input" type="checkbox" name="permissions[<?= (int) $page['id']; ?>][<?= $action; ?>]" value="1" <?= ! empty($perm['can_' . $action]) ? 'checked' : ''; ?>>
												</label>
											</td>
										<?php endforeach; ?>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
					<div class="card-footer text-end">
						<button type="submit" class="btn btn-primary">
							<i class="ti ti-device-floppy me-1"></i>Simpan Hak Akses
						</button>
					</div>
				<?= form_close(); ?>
			</div>
		<?php endif; ?>
	</div>
</div>

<?php $this->load->view('rbac/_role_modal', ['edit_role' => $edit_role]); ?>
