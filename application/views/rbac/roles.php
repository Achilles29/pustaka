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
				<div class="page-pretitle">RBAC Foundation</div>
				<h1 class="page-title">Role & Permission</h1>
			</div>
			<div class="col-auto ms-auto">
				<?= form_open('rbac/roles', ['method' => 'get']); ?>
					<select class="form-select" name="role_id" onchange="this.form.submit()">
						<?php foreach ($roles as $role): ?>
							<option value="<?= (int) $role['id']; ?>" <?= ! empty($selected_role) && (int) $selected_role['id'] === (int) $role['id'] ? 'selected' : ''; ?>>
								<?= html_escape($role['code'] . ' - ' . $role['name']); ?>
							</option>
						<?php endforeach; ?>
					</select>
				<?= form_close(); ?>
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

		<div class="card admin-card">
			<div class="card-header">
				<div>
					<h2 class="card-title"><?= html_escape($selected_role['name'] ?? 'Role'); ?></h2>
					<div class="text-secondary small"><?= html_escape($selected_role['description'] ?? ''); ?></div>
				</div>
			</div>
			<?php if (! empty($selected_role)): ?>
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
			<?php endif; ?>
		</div>
	</div>
</div>
