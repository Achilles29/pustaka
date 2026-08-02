<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$metrics = [
	['key' => 'members', 'label' => 'Member Aplikasi', 'icon' => 'ti ti-id-badge-2'],
	['key' => 'linked_users', 'label' => 'Akun Login', 'icon' => 'ti ti-user-check'],
	['key' => 'photo_refs', 'label' => 'Referensi Foto', 'icon' => 'ti ti-photo'],
	['key' => 'sync_runs', 'label' => 'Sinkronisasi', 'icon' => 'ti ti-refresh'],
];
$status_labels = [
	'active' => 'Aktif',
	'inactive' => 'Nonaktif',
	'blocked' => 'Diblokir',
	'expired' => 'Kedaluwarsa',
	'unknown' => 'Belum Dipetakan',
];
$user_status_labels = [
	'active' => 'Aktif',
	'inactive' => 'Nonaktif',
	'suspended' => 'Ditangguhkan',
];
$query_base = $_GET;
unset($query_base['page']);
$page_url = function ($page) use ($query_base) {
	return base_url('members?' . http_build_query(array_merge($query_base, ['page' => $page])));
};
$asset_url = function ($member) {
	$url_path = function ($path) {
		$segments = explode('/', str_replace('\\', '/', trim((string) $path, '/')));
		return implode('/', array_map('rawurlencode', $segments));
	};

	if (! empty($member['photo_local_path'])) {
		return base_url($url_path($member['photo_local_path']));
	}

	if (! empty($member['photo_source_path']) && strpos($member['photo_source_path'], 'assets/') === 0) {
		return base_url($url_path($member['photo_source_path']));
	}

	if (! empty($member['photo_source_path'])) {
		return base_url($url_path('assets/uploads/inlislite/source_mirror/' . $member['photo_source_path']));
	}

	return '';
};
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Fase 3</div>
				<h1 class="page-title">Membership Digital</h1>
			</div>
			<div class="col-auto ms-auto">
				<div class="btn-list">
					<a href="<?= base_url('members/create'); ?>" class="btn btn-outline-primary">
						<i class="ti ti-user-plus me-1"></i>Tambah
					</a>
					<a href="<?= base_url('members/sync'); ?>" class="btn btn-primary">
						<i class="ti ti-refresh me-1"></i>Sinkronisasi Member
					</a>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="page-body">
	<div class="container-xl">
		<div class="metric-ribbon">
			<?php foreach ($metrics as $metric): ?>
				<div class="metric-ribbon-item">
					<span class="metric-icon"><i class="<?= html_escape($metric['icon']); ?>"></i></span>
					<div>
						<div class="metric-value"><?= number_format((int) ($stats[$metric['key']] ?? 0), 0, ',', '.'); ?></div>
						<div class="metric-label"><?= html_escape($metric['label']); ?></div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="card admin-card data-workspace">
			<div class="card-header workspace-header">
				<div>
					<h2 class="card-title">Data Member</h2>
					<div class="text-secondary small">Profil pemustaka hasil migrasi dengan filter, pagination, foto, dan status akun login.</div>
				</div>
				<ul class="nav nav-tabs card-header-tabs workspace-tabs" role="tablist">
					<li class="nav-item" role="presentation"><a href="#tab-member-data" class="nav-link active" data-bs-toggle="tab" role="tab"><i class="ti ti-table me-1"></i>Data</a></li>
					<li class="nav-item" role="presentation"><a href="#tab-member-source" class="nav-link" data-bs-toggle="tab" role="tab"><i class="ti ti-database-search me-1"></i>Sumber</a></li>
					<li class="nav-item" role="presentation"><a href="#tab-member-schema" class="nav-link" data-bs-toggle="tab" role="tab"><i class="ti ti-sitemap me-1"></i>Mapping</a></li>
				</ul>
			</div>

			<div class="tab-content">
				<div class="tab-pane active show" id="tab-member-data" role="tabpanel">
					<div class="card-body workspace-filter">
						<?= form_open('members', ['method' => 'get', 'class' => 'row g-2 align-items-end']); ?>
							<div class="col-md-4">
								<label class="form-label">Cari</label>
								<input type="text" class="form-control" name="q" value="<?= html_escape($filters['q'] ?? ''); ?>" placeholder="Nama, nomor anggota, kontak, wilayah">
							</div>
							<div class="col-md-2">
								<label class="form-label">Membership</label>
								<select class="form-select" name="status">
									<option value="">Semua</option>
									<?php foreach ($status_labels as $value => $label): ?>
										<option value="<?= $value; ?>" <?= ($filters['status'] ?? '') === $value ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-md-2">
								<label class="form-label">Akun</label>
								<select class="form-select" name="user_status">
									<option value="">Semua</option>
									<?php foreach ($user_status_labels as $value => $label): ?>
										<option value="<?= $value; ?>" <?= ($filters['user_status'] ?? '') === $value ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-md-2">
								<label class="form-label">Baris</label>
								<select class="form-select" name="per_page">
									<?php foreach ([10, 25, 50, 100] as $limit): ?>
										<option value="<?= $limit; ?>" <?= (int) ($filters['per_page'] ?? 25) === $limit ? 'selected' : ''; ?>><?= $limit; ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-md-2">
								<button type="submit" class="btn btn-outline-primary w-100">
									<i class="ti ti-filter me-1"></i>Filter
								</button>
							</div>
						<?= form_close(); ?>
					</div>

					<div class="table-responsive">
						<table class="table table-vcenter card-table">
							<thead>
								<tr>
									<th>Member</th>
									<th>Kontak</th>
									<th>Wilayah</th>
									<th>Membership</th>
									<th>Akun</th>
									<th class="w-1">Aksi</th>
								</tr>
							</thead>
							<tbody>
								<?php if (empty($members)): ?>
									<tr><td colspan="6" class="text-center text-secondary py-4">Belum ada member sesuai filter.</td></tr>
								<?php endif; ?>
								<?php foreach ($members as $member): ?>
									<?php $photo_url = $asset_url($member); ?>
									<tr>
										<td>
											<div class="entity-main">
												<div class="member-photo-thumb">
													<?php if ($photo_url): ?>
														<img src="<?= html_escape($photo_url); ?>" alt="Foto <?= html_escape($member['full_name']); ?>" loading="lazy">
													<?php else: ?>
														<i class="ti ti-user"></i>
													<?php endif; ?>
												</div>
												<div>
													<div class="fw-semibold"><?= html_escape($member['full_name']); ?></div>
													<div class="text-secondary small"><code><?= html_escape($member['member_no'] ?: '-'); ?></code></div>
													<div class="small text-blue"><?= html_escape($member['member_type_label'] ?: ($member['member_type'] ?: '-')); ?></div>
												</div>
											</div>
										</td>
										<td>
											<div><?= html_escape($member['phone'] ?: '-'); ?></div>
											<div class="text-secondary small"><?= html_escape($member['email'] ?: '-'); ?></div>
										</td>
										<td><?= html_escape(trim(($member['village'] ?: '-') . ', ' . ($member['district'] ?: '-'), ', ')); ?></td>
										<td><span class="badge bg-blue-lt"><?= html_escape($status_labels[$member['status']] ?? ucfirst($member['status'])); ?></span></td>
										<td>
											<span class="badge <?= ($member['user_status'] ?? '') === 'active' ? 'bg-green-lt' : 'bg-secondary-lt'; ?>"><?= html_escape($user_status_labels[$member['user_status'] ?? ''] ?? 'Belum Ada'); ?></span>
											<div class="text-secondary small"><?= html_escape($member['username'] ?: '-'); ?></div>
										</td>
										<td>
											<div class="btn-list flex-nowrap">
												<a class="btn btn-sm btn-action btn-action-muted" href="<?= base_url('members/detail/' . (int) $member['id']); ?>">
													<i class="ti ti-eye"></i><span>Detail</span>
												</a>
												<a class="btn btn-sm btn-action btn-action-primary" href="<?= base_url('members/edit/' . (int) $member['id']); ?>">
													<i class="ti ti-edit"></i><span>Edit</span>
												</a>
											</div>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>

					<div class="card-footer responsive-footer">
						<p class="m-0 text-secondary">Menampilkan <?= number_format($pagination['total_rows'] > 0 ? $pagination['offset'] + 1 : 0, 0, ',', '.'); ?>-<?= number_format(min($pagination['offset'] + $pagination['per_page'], $pagination['total_rows']), 0, ',', '.'); ?> dari <?= number_format($pagination['total_rows'], 0, ',', '.'); ?> data</p>
						<ul class="pagination m-0">
							<li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : ''; ?>"><a class="page-link" href="<?= $page_url(max(1, $pagination['page'] - 1)); ?>">Prev</a></li>
							<?php for ($i = max(1, $pagination['page'] - 2); $i <= min($pagination['total_pages'], $pagination['page'] + 2); $i++): ?>
								<li class="page-item <?= $i === (int) $pagination['page'] ? 'active' : ''; ?>"><a class="page-link" href="<?= $page_url($i); ?>"><?= $i; ?></a></li>
							<?php endfor; ?>
							<li class="page-item <?= $pagination['page'] >= $pagination['total_pages'] ? 'disabled' : ''; ?>"><a class="page-link" href="<?= $page_url(min($pagination['total_pages'], $pagination['page'] + 1)); ?>">Next</a></li>
						</ul>
					</div>
				</div>

				<div class="tab-pane" id="tab-member-source" role="tabpanel">
					<div class="list-group list-group-flush">
						<?php foreach ($source_stats as $source): ?>
							<div class="list-group-item d-flex align-items-center">
								<div class="flex-fill"><?= html_escape($source['label']); ?></div>
								<span class="badge bg-blue-lt"><?= number_format((int) $source['value'], 0, ',', '.'); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				</div>

				<div class="tab-pane" id="tab-member-schema" role="tabpanel">
					<div class="table-responsive">
						<table class="table table-vcenter card-table">
							<thead>
								<tr><th>Domain</th><th>Tabel</th><th>Fungsi</th></tr>
							</thead>
							<tbody>
								<tr><td class="fw-semibold">Akun</td><td><code>auth_user</code>, <code>auth_user_role</code></td><td>Login pemustaka role <code>USER</code> dan wajib ganti password awal.</td></tr>
								<tr><td class="fw-semibold">Profil</td><td><code>members</code></td><td>Nomor anggota, identitas, alamat, kontak, foto, dan status membership.</td></tr>
								<tr><td class="fw-semibold">Sinkronisasi</td><td><code>member_sync_runs</code></td><td>Riwayat import, jumlah sumber, data baru, update, dan gagal.</td></tr>
								<tr><td class="fw-semibold">Berikutnya</td><td><code>loan_history</code>, <code>member_access_rules</code></td><td>Histori pinjam dan hak akses layanan digital per pemustaka.</td></tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
