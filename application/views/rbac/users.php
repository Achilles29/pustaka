<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$status_labels = [
	'active' => 'Aktif',
	'inactive' => 'Nonaktif',
	'suspended' => 'Ditangguhkan',
];
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Pengaturan Akses</div>
				<h1 class="page-title">User</h1>
			</div>
			<div class="col-auto ms-auto">
				<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#user-modal">
					<i class="ti ti-user-plus me-1"></i>Tambah User
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
				<span class="metric-icon"><i class="ti ti-users"></i></span>
				<div><div class="metric-value"><?= number_format(count($users), 0, ',', '.'); ?></div><div class="metric-label">Total User</div></div>
			</div>
			<div class="metric-ribbon-item">
				<span class="metric-icon"><i class="ti ti-user-check"></i></span>
				<div><div class="metric-value"><?= number_format(count(array_filter($users, function ($user) { return $user['status'] === 'active'; })), 0, ',', '.'); ?></div><div class="metric-label">Aktif</div></div>
			</div>
			<div class="metric-ribbon-item">
				<span class="metric-icon"><i class="ti ti-key"></i></span>
				<div><div class="metric-value"><?= number_format(count($roles), 0, ',', '.'); ?></div><div class="metric-label">Role Aktif</div></div>
			</div>
		</div>

		<div class="card admin-card">
			<div class="card-header">
				<h2 class="card-title">Daftar User</h2>
			</div>
			<div class="table-responsive">
				<table class="table table-vcenter card-table">
					<thead>
						<tr>
							<th>User</th>
							<th>Status</th>
							<th>Role</th>
							<th>Scope</th>
							<th>Login terakhir</th>
							<th class="w-1">Aksi</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($users as $user): ?>
							<tr>
								<td>
									<div class="user-row-main">
										<span class="avatar avatar-sm bg-blue-lt text-blue"><?= html_escape(strtoupper(substr($user['full_name'], 0, 2))); ?></span>
										<div>
											<div class="fw-semibold"><?= html_escape($user['full_name']); ?></div>
											<div class="text-secondary small"><?= html_escape($user['username']); ?><?= $user['email'] ? ' - ' . html_escape($user['email']) : ''; ?></div>
										</div>
									</div>
								</td>
								<td><span class="badge <?= $user['status'] === 'active' ? 'bg-blue-lt' : 'bg-secondary-lt'; ?>"><?= html_escape($status_labels[$user['status']] ?? ucfirst($user['status'])); ?></span></td>
								<td>
									<div class="chip-list">
										<?php foreach ($user['roles'] as $role): ?>
											<span class="chip"><?= html_escape($role['code']); ?></span>
										<?php endforeach; ?>
									</div>
								</td>
								<td><?= html_escape($user['library_name'] ?: 'Global'); ?></td>
								<td><?= html_escape($user['last_login_at'] ?: '-'); ?></td>
								<td>
									<div class="btn-list flex-nowrap">
										<a class="btn btn-sm btn-action btn-action-primary" href="<?= base_url('rbac/users?edit_id=' . (int) $user['id']); ?>">
											<i class="ti ti-edit"></i><span>Edit</span>
										</a>
										<?= form_open('rbac/users/toggle/' . (int) $user['id'], ['class' => 'd-inline']); ?>
											<input type="hidden" name="status" value="<?= html_escape($user['status']); ?>">
											<button type="submit" class="btn btn-sm btn-action btn-action-muted" title="<?= $user['status'] === 'active' ? 'Nonaktifkan User' : 'Aktifkan User'; ?>">
												<i class="ti <?= $user['status'] === 'active' ? 'ti-toggle-right' : 'ti-toggle-left'; ?>"></i><span><?= $user['status'] === 'active' ? 'Nonaktifkan' : 'Aktifkan'; ?></span>
											</button>
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

<?php $this->load->view('rbac/_user_modal', compact('roles', 'libraries')); ?>
<?php $this->load->view('rbac/_user_scope_modal', compact('roles', 'libraries', 'edit_user')); ?>
