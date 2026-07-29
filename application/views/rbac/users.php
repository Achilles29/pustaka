<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">RBAC Foundation</div>
				<h1 class="page-title">User</h1>
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
						<h2 class="card-title">Tambah User</h2>
					</div>
					<div class="card-body">
						<?= form_open('rbac/users/store'); ?>
							<div class="mb-3">
								<label class="form-label">Username</label>
								<input type="text" class="form-control" name="username" required>
							</div>
							<div class="mb-3">
								<label class="form-label">Nama lengkap</label>
								<input type="text" class="form-control" name="full_name" required>
							</div>
							<div class="mb-3">
								<label class="form-label">Email</label>
								<input type="email" class="form-control" name="email">
							</div>
							<div class="mb-3">
								<label class="form-label">Password awal</label>
								<input type="password" class="form-control" name="password" minlength="6" required>
							</div>
							<div class="mb-3">
								<label class="form-label">Role</label>
								<div class="stacked-checks">
									<?php foreach ($roles as $role): ?>
										<label class="form-check">
											<input class="form-check-input" type="checkbox" name="role_ids[]" value="<?= (int) $role['id']; ?>">
											<span class="form-check-label"><?= html_escape($role['code'] . ' - ' . $role['name']); ?></span>
										</label>
									<?php endforeach; ?>
								</div>
							</div>
							<button type="submit" class="btn btn-primary w-100">
								<i class="ti ti-user-plus me-1"></i>Buat User
							</button>
						<?= form_close(); ?>
					</div>
				</div>
			</div>

			<div class="col-lg-8">
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
									<th>Login terakhir</th>
									<th class="w-1">Aksi</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($users as $user): ?>
									<?php
									$user_role_ids = array_map(function ($role) {
										return (int) $role['id'];
									}, $user['roles']);
									?>
									<tr>
										<td>
											<div class="d-flex align-items-center gap-2">
												<span class="avatar avatar-sm"><?= html_escape(strtoupper(substr($user['full_name'], 0, 2))); ?></span>
												<div>
													<div class="fw-semibold"><?= html_escape($user['full_name']); ?></div>
													<div class="text-secondary small"><?= html_escape($user['username']); ?><?= $user['email'] ? ' - ' . html_escape($user['email']) : ''; ?></div>
												</div>
											</div>
										</td>
										<td><span class="badge <?= $user['status'] === 'active' ? 'bg-green-lt' : 'bg-red-lt'; ?>"><?= html_escape($user['status']); ?></span></td>
										<td>
											<?= form_open('rbac/users/roles/' . (int) $user['id']); ?>
												<div class="role-check-grid">
													<?php foreach ($roles as $role): ?>
														<label class="form-check">
															<input class="form-check-input" type="checkbox" name="role_ids[]" value="<?= (int) $role['id']; ?>" <?= in_array((int) $role['id'], $user_role_ids, true) ? 'checked' : ''; ?>>
															<span class="form-check-label"><?= html_escape($role['code']); ?></span>
														</label>
													<?php endforeach; ?>
												</div>
												<button type="submit" class="btn btn-sm btn-outline-primary mt-2">Simpan Role</button>
											<?= form_close(); ?>
										</td>
										<td><?= html_escape($user['last_login_at'] ?: '-'); ?></td>
										<td>
											<?= form_open('rbac/users/toggle/' . (int) $user['id']); ?>
												<input type="hidden" name="status" value="<?= html_escape($user['status']); ?>">
												<button type="submit" class="btn btn-sm btn-outline-secondary"><?= $user['status'] === 'active' ? 'Nonaktifkan' : 'Aktifkan'; ?></button>
											<?= form_close(); ?>
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
