<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$filters = $filters ?? [];
$pagination = $pagination ?? ['page' => 1, 'total_pages' => 1, 'total_rows' => 0, 'per_page' => 25, 'offset' => 0];
$stats = $stats ?? ['total' => 0, 'active' => 0, 'inactive' => 0, 'suspended' => 0];
$base_route = $base_route ?? 'rbac/admins';
$status_labels = [
	'active' => 'Aktif',
	'inactive' => 'Nonaktif',
	'suspended' => 'Ditangguhkan',
];
$source_labels = [
	'' => 'Semua sumber',
	'local' => 'Lokal Pustaka',
	'inlislite_admin' => 'Admin INLISLite',
];
$clean_query = function (array $params) {
	unset($params['account_type']);
	return array_filter($params, function ($value) {
		return $value !== '' && $value !== null && $value !== 0 && $value !== '0';
	});
};
$page_url = function ($page) use ($base_route, $filters, $clean_query) {
	$params = $clean_query($filters);
	$params['page'] = $page;
	return base_url($base_route . '?' . http_build_query($params));
};
$status_tab_url = function ($status) use ($base_route, $filters, $clean_query) {
	$params = $filters;
	$params['status'] = $status;
	unset($params['page']);
	return base_url($base_route . '?' . http_build_query($clean_query($params)));
};
$initials = function ($name) {
	$name = trim((string) $name);
	return strtoupper(substr($name !== '' ? $name : 'AD', 0, 2));
};
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Pengaturan Akses</div>
				<h1 class="page-title">Daftar Admin</h1>
				<div class="text-secondary mt-1">Akun operasional dan admin perpustakaan. Akun member dikelola terpisah di modul Membership.</div>
			</div>
			<div class="col-auto ms-auto">
				<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#user-modal">
					<i class="ti ti-shield-plus me-1"></i>Tambah Admin
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
				<span class="metric-icon"><i class="ti ti-shield-check"></i></span>
				<div><div class="metric-value"><?= number_format((int) $stats['total'], 0, ',', '.'); ?></div><div class="metric-label">Total Admin</div></div>
			</div>
			<div class="metric-ribbon-item">
				<span class="metric-icon"><i class="ti ti-user-check"></i></span>
				<div><div class="metric-value"><?= number_format((int) $stats['active'], 0, ',', '.'); ?></div><div class="metric-label">Aktif</div></div>
			</div>
			<div class="metric-ribbon-item">
				<span class="metric-icon"><i class="ti ti-user-pause"></i></span>
				<div><div class="metric-value"><?= number_format((int) $stats['inactive'] + (int) $stats['suspended'], 0, ',', '.'); ?></div><div class="metric-label">Nonaktif</div></div>
			</div>
			<div class="metric-ribbon-item">
				<span class="metric-icon"><i class="ti ti-key"></i></span>
				<div><div class="metric-value"><?= number_format(count($roles), 0, ',', '.'); ?></div><div class="metric-label">Role Admin</div></div>
			</div>
		</div>

		<div class="status-tabs" role="tablist" aria-label="Filter status admin">
			<a href="<?= $status_tab_url(''); ?>" class="status-tab <?= empty($filters['status']) ? 'active' : ''; ?>"><i class="ti ti-users"></i>Semua <span><?= number_format((int) $stats['total'], 0, ',', '.'); ?></span></a>
			<a href="<?= $status_tab_url('active'); ?>" class="status-tab <?= ($filters['status'] ?? '') === 'active' ? 'active' : ''; ?>"><i class="ti ti-user-check"></i>Aktif <span><?= number_format((int) $stats['active'], 0, ',', '.'); ?></span></a>
			<a href="<?= $status_tab_url('inactive'); ?>" class="status-tab <?= ($filters['status'] ?? '') === 'inactive' ? 'active' : ''; ?>"><i class="ti ti-user-pause"></i>Nonaktif <span><?= number_format((int) $stats['inactive'] + (int) $stats['suspended'], 0, ',', '.'); ?></span></a>
		</div>

		<div class="card admin-card data-workspace">
			<div class="card-header workspace-header">
				<div>
					<h2 class="card-title">Akun Admin</h2>
					<div class="text-secondary small">Default 25 baris, header tabel sticky, dan area data bisa discroll tanpa membuat halaman terlalu panjang.</div>
				</div>
			</div>

			<div class="card-body workspace-filter">
				<?= form_open($base_route, ['method' => 'get', 'class' => 'row g-2 align-items-end service-filter-form admin-user-filter']); ?>
					<div class="col-lg-3 col-md-6">
						<label class="form-label">Cari</label>
						<input type="search" class="form-control" name="q" value="<?= html_escape($filters['q'] ?? ''); ?>" placeholder="Nama, username, email, perpustakaan" autocomplete="off">
					</div>
					<div class="col-lg-1 col-md-6">
						<label class="form-label">Status</label>
						<select class="form-select" name="status">
							<option value="">Semua</option>
							<?php foreach ($status_labels as $value => $label): ?>
								<option value="<?= $value; ?>" <?= ($filters['status'] ?? '') === $value ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-lg-1 col-md-6">
						<label class="form-label">Role</label>
						<select class="form-select" name="role_id">
							<option value="">Semua</option>
							<?php foreach ($roles as $role): ?>
								<option value="<?= (int) $role['id']; ?>" <?= (int) ($filters['role_id'] ?? 0) === (int) $role['id'] ? 'selected' : ''; ?>><?= html_escape($role['code']); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-lg-2 col-md-6">
						<label class="form-label">Perpustakaan</label>
						<select class="form-select" name="library_id">
							<option value="">Semua scope</option>
							<?php foreach ($libraries as $library): ?>
								<option value="<?= (int) $library['id']; ?>" <?= (int) ($filters['library_id'] ?? 0) === (int) $library['id'] ? 'selected' : ''; ?>><?= html_escape($library['name']); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-lg-2 col-md-4">
						<label class="form-label">Sumber</label>
						<select class="form-select" name="source">
							<?php foreach ($source_labels as $value => $label): ?>
								<option value="<?= html_escape($value); ?>" <?= ($filters['source'] ?? '') === $value ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-lg-1 col-md-4">
						<label class="form-label">Baris</label>
						<select class="form-select" name="per_page">
							<?php foreach ([10, 25, 50, 100] as $limit): ?>
								<option value="<?= $limit; ?>" <?= (int) ($filters['per_page'] ?? 25) === $limit ? 'selected' : ''; ?>><?= $limit; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-lg-1 col-md-4">
						<button class="btn btn-primary w-100" title="Terapkan filter"><i class="ti ti-filter"></i></button>
					</div>
					<div class="col-lg-1 col-md-4">
						<a class="btn btn-outline-secondary w-100" href="<?= base_url($base_route); ?>" title="Reset filter"><i class="ti ti-refresh"></i></a>
					</div>
				<?= form_close(); ?>
			</div>

			<div class="table-responsive admin-table-scroll">
				<table class="table table-vcenter card-table table-admin-list">
					<thead>
						<tr>
							<th>Admin</th>
							<th>Username</th>
							<th>Role</th>
							<th>Scope</th>
							<th>Sumber</th>
							<th>Status</th>
							<th>Login terakhir</th>
							<th class="w-1">Aksi</th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($users)): ?>
							<tr><td colspan="8" class="text-center text-secondary py-4">Belum ada admin sesuai filter.</td></tr>
						<?php endif; ?>
						<?php foreach ($users as $user): ?>
							<?php
							$is_active = $user['status'] === 'active';
							$is_inlislite = ($user['source_system'] ?? '') === 'inlislite_v3' && ($user['source_table'] ?? '') === 'users';
							?>
							<tr>
								<td>
									<div class="user-row-main">
										<span class="avatar avatar-sm bg-blue-lt text-blue"><?= html_escape($initials($user['full_name'])); ?></span>
										<div>
											<div class="fw-semibold"><?= html_escape($user['full_name']); ?></div>
											<div class="text-secondary small"><?= $user['email'] ? html_escape($user['email']) : '-'; ?></div>
										</div>
									</div>
								</td>
								<td><code><?= html_escape($user['username']); ?></code></td>
								<td>
									<div class="chip-list">
										<?php foreach ($user['roles'] as $role): ?>
											<span class="chip"><?= html_escape($role['code']); ?></span>
										<?php endforeach; ?>
									</div>
								</td>
								<td>
									<div class="fw-semibold"><?= html_escape($user['library_name'] ?: 'Global'); ?></div>
									<div class="text-secondary small"><?= empty($user['library_name']) ? 'Lintas unit' : 'Scope perpustakaan'; ?></div>
								</td>
								<td>
									<span class="badge <?= $is_inlislite ? 'bg-blue-lt' : 'bg-secondary-lt'; ?>"><?= $is_inlislite ? 'INLISLite' : 'Lokal'; ?></span>
									<?php if (! empty($user['source_role'])): ?>
										<div class="text-secondary small"><?= html_escape($user['source_role']); ?></div>
									<?php endif; ?>
								</td>
								<td><span class="badge <?= $is_active ? 'bg-green-lt' : 'bg-secondary-lt'; ?>"><?= html_escape($status_labels[$user['status']] ?? ucfirst($user['status'])); ?></span></td>
								<td><?= html_escape($user['last_login_at'] ?: '-'); ?></td>
								<td>
									<div class="btn-list flex-nowrap">
										<a class="btn btn-sm btn-action btn-action-primary" href="<?= base_url($base_route . '?' . http_build_query(array_merge($clean_query($filters), ['edit_id' => (int) $user['id']]))); ?>">
											<i class="ti ti-edit"></i><span>Edit</span>
										</a>
										<?= form_open('rbac/admins/toggle/' . (int) $user['id'], ['class' => 'd-inline']); ?>
											<input type="hidden" name="status" value="<?= html_escape($user['status']); ?>">
											<button type="submit" class="btn btn-sm btn-action btn-action-muted" title="<?= $is_active ? 'Nonaktifkan admin ini' : 'Aktifkan admin ini'; ?>">
												<i class="ti <?= $is_active ? 'ti-toggle-right' : 'ti-toggle-left'; ?>"></i><span><?= $is_active ? 'Nonaktifkan' : 'Aktifkan'; ?></span>
											</button>
										<?= form_close(); ?>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<div class="card-footer responsive-footer">
				<p class="m-0 text-secondary">Menampilkan <?= number_format($pagination['total_rows'] > 0 ? $pagination['offset'] + 1 : 0, 0, ',', '.'); ?>-<?= number_format(min($pagination['offset'] + $pagination['per_page'], $pagination['total_rows']), 0, ',', '.'); ?> dari <?= number_format($pagination['total_rows'], 0, ',', '.'); ?> admin</p>
				<ul class="pagination m-0">
					<li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : ''; ?>"><a class="page-link" href="<?= $page_url(max(1, $pagination['page'] - 1)); ?>">Prev</a></li>
					<?php for ($i = max(1, $pagination['page'] - 2); $i <= min($pagination['total_pages'], $pagination['page'] + 2); $i++): ?>
						<li class="page-item <?= $i === (int) $pagination['page'] ? 'active' : ''; ?>"><a class="page-link" href="<?= $page_url($i); ?>"><?= $i; ?></a></li>
					<?php endfor; ?>
					<li class="page-item <?= $pagination['page'] >= $pagination['total_pages'] ? 'disabled' : ''; ?>"><a class="page-link" href="<?= $page_url(min($pagination['total_pages'], $pagination['page'] + 1)); ?>">Next</a></li>
				</ul>
			</div>
		</div>
	</div>
</div>

<?php $this->load->view('rbac/_user_modal', compact('roles', 'libraries')); ?>
<?php $this->load->view('rbac/_user_scope_modal', compact('roles', 'libraries', 'edit_user')); ?>
