<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$summary = [
	'districts' => count($districts),
	'villages' => (int) $pagination['total_rows'],
	'kelurahan' => 0,
	'desa' => 0,
];
foreach ($villages as $row) {
	if ($row['area_type'] === 'kelurahan') {
		$summary['kelurahan']++;
	} else {
		$summary['desa']++;
	}
}
$active_tab = ! empty($edit_district) ? 'districts' : ($this->input->get('tab', true) === 'districts' ? 'districts' : 'villages');
$page_url = function ($page) use ($filters) {
	$params = $filters;
	$params['tab'] = 'villages';
	$params['page'] = $page;
	return base_url('regions?' . http_build_query(array_filter($params, function ($value) {
		return $value !== '' && $value !== null;
	})));
};
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Data Master</div>
				<h1 class="page-title">Master Wilayah</h1>
			</div>
			<?php if ($can_create): ?>
				<div class="col-auto ms-auto">
					<div class="btn-list">
						<button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#district-modal">
							<i class="ti ti-map-pin-plus me-1"></i>Kecamatan
						</button>
						<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#village-modal">
							<i class="ti ti-home-plus me-1"></i>Desa / Kelurahan
						</button>
					</div>
				</div>
			<?php endif; ?>
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

		<div class="row row-cards mb-3">
			<div class="col-6 col-lg-3">
				<div class="card stat-card stat-card-compact">
					<div class="card-body">
						<div class="subheader">Kecamatan</div>
						<div class="h1 mb-0"><?= number_format($summary['districts'], 0, ',', '.'); ?></div>
					</div>
				</div>
			</div>
			<div class="col-6 col-lg-3">
				<div class="card stat-card stat-card-compact">
					<div class="card-body">
						<div class="subheader">Desa / Kelurahan</div>
						<div class="h1 mb-0"><?= number_format($pagination['total_rows'], 0, ',', '.'); ?></div>
					</div>
				</div>
			</div>
			<div class="col-6 col-lg-3">
				<div class="card stat-card stat-card-compact">
					<div class="card-body">
						<div class="subheader">Provinsi</div>
						<div class="h1 mb-0">33</div>
						<div class="text-secondary">Jawa Tengah</div>
					</div>
				</div>
			</div>
			<div class="col-6 col-lg-3">
				<div class="card stat-card stat-card-compact">
					<div class="card-body">
						<div class="subheader">Kabupaten</div>
						<div class="h1 mb-0">17</div>
						<div class="text-secondary">Rembang</div>
					</div>
				</div>
			</div>
		</div>

		<div class="card admin-card data-workspace">
			<div class="card-header workspace-header">
				<div>
					<h2 class="card-title">Wilayah Administratif Rembang</h2>
					<div class="text-secondary small">Kelola kecamatan dan Desa / Kelurahan dari satu halaman tanpa tabel menumpuk.</div>
				</div>
				<ul class="nav nav-tabs card-header-tabs workspace-tabs" role="tablist">
					<li class="nav-item" role="presentation">
						<a href="#tab-districts" class="nav-link <?= $active_tab === 'districts' ? 'active' : ''; ?>" data-bs-toggle="tab" role="tab">
							<i class="ti ti-map-2 me-1"></i>Kecamatan
						</a>
					</li>
					<li class="nav-item" role="presentation">
						<a href="#tab-villages" class="nav-link <?= $active_tab === 'villages' ? 'active' : ''; ?>" data-bs-toggle="tab" role="tab">
							<i class="ti ti-building-community me-1"></i>Desa / Kelurahan
						</a>
					</li>
				</ul>
			</div>

			<div class="tab-content">
				<div class="tab-pane <?= $active_tab === 'districts' ? 'active show' : ''; ?>" id="tab-districts" role="tabpanel">
					<div class="table-responsive">
						<table class="table table-vcenter card-table">
							<thead>
								<tr>
									<th>Kode</th>
									<th>Kecamatan</th>
									<th>Status</th>
									<?php if ($can_edit): ?><th class="w-1">Aksi</th><?php endif; ?>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($districts as $district): ?>
									<tr>
										<td>
											<div class="fw-semibold"><?= html_escape($district['full_code']); ?></div>
											<div class="text-secondary small">kode <?= html_escape($district['code']); ?></div>
										</td>
										<td><?= html_escape($district['name']); ?></td>
										<td><span class="badge <?= (int) $district['is_active'] === 1 ? 'bg-green-lt' : 'bg-red-lt'; ?>"><?= (int) $district['is_active'] === 1 ? 'Aktif' : 'Nonaktif'; ?></span></td>
										<?php if ($can_edit): ?>
											<td>
												<div class="btn-list flex-nowrap">
													<a class="btn btn-sm btn-action btn-action-primary" title="Edit Kecamatan" href="<?= base_url('regions?tab=districts&edit_district_id=' . (int) $district['id']); ?>">
														<i class="ti ti-edit"></i><span>Edit</span>
													</a>
													<?= form_open('regions/districts/toggle/' . (int) $district['id'], ['class' => 'd-inline']); ?>
														<button class="btn btn-sm btn-action btn-action-muted" title="<?= (int) $district['is_active'] === 1 ? 'Nonaktifkan Kecamatan' : 'Aktifkan Kecamatan'; ?>" type="submit">
															<i class="ti <?= (int) $district['is_active'] === 1 ? 'ti-toggle-right' : 'ti-toggle-left'; ?>"></i><span><?= (int) $district['is_active'] === 1 ? 'Nonaktifkan' : 'Aktifkan'; ?></span>
														</button>
													<?= form_close(); ?>
												</div>
											</td>
										<?php endif; ?>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>

				<div class="tab-pane <?= $active_tab === 'villages' ? 'active show' : ''; ?>" id="tab-villages" role="tabpanel">
					<div class="card-body workspace-filter">
						<?= form_open('regions', ['method' => 'get', 'class' => 'row g-2 align-items-end']); ?>
							<input type="hidden" name="tab" value="villages">
							<div class="col-md-3">
								<label class="form-label">Kecamatan</label>
								<select class="form-select" name="district_id">
									<option value="">Semua kecamatan</option>
									<?php foreach ($active_districts as $district): ?>
										<option value="<?= (int) $district['id']; ?>" <?= (int) ($filters['district_id'] ?? 0) === (int) $district['id'] ? 'selected' : ''; ?>>
											<?= html_escape(($district['full_code'] ?: $district['code']) . ' - ' . $district['name']); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-md-2">
								<label class="form-label">Jenis</label>
								<select class="form-select" name="area_type">
									<option value="">Semua</option>
									<option value="desa" <?= ($filters['area_type'] ?? '') === 'desa' ? 'selected' : ''; ?>>Desa</option>
									<option value="kelurahan" <?= ($filters['area_type'] ?? '') === 'kelurahan' ? 'selected' : ''; ?>>Kelurahan</option>
								</select>
							</div>
							<div class="col-md-3">
								<label class="form-label">Cari</label>
								<input type="text" class="form-control" name="q" value="<?= html_escape($filters['q'] ?? ''); ?>" placeholder="Kode atau nama">
							</div>
							<div class="col-md-2">
								<label class="form-label">Baris</label>
								<select class="form-select" name="per_page">
									<?php foreach ([10, 25, 50, 100] as $limit): ?>
										<option value="<?= $limit; ?>" <?= (int) $filters['per_page'] === $limit ? 'selected' : ''; ?>><?= $limit; ?></option>
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
									<th>Kode</th>
									<th>Nama</th>
									<th>Kecamatan</th>
									<th>Status</th>
									<?php if ($can_edit): ?><th class="w-1">Aksi</th><?php endif; ?>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($villages as $village): ?>
									<tr>
										<td><code><?= html_escape($village['code']); ?></code></td>
										<td>
											<div class="fw-semibold"><?= html_escape($village['name']); ?></div>
											<div class="text-secondary small"><?= html_escape(ucfirst($village['area_type'])); ?></div>
										</td>
										<td><?= html_escape(($village['district_full_code'] ?: '-') . ' - ' . $village['district_name']); ?></td>
										<td><span class="badge <?= (int) $village['is_active'] === 1 ? 'bg-green-lt' : 'bg-red-lt'; ?>"><?= (int) $village['is_active'] === 1 ? 'Aktif' : 'Nonaktif'; ?></span></td>
										<?php if ($can_edit): ?>
											<td>
												<div class="btn-list flex-nowrap">
													<a class="btn btn-sm btn-action btn-action-primary" title="Edit Desa / Kelurahan" href="<?= base_url('regions?tab=villages&edit_village_id=' . (int) $village['id'] . '&district_id=' . (int) $village['district_id']); ?>">
														<i class="ti ti-edit"></i><span>Edit</span>
													</a>
													<?= form_open('regions/villages/toggle/' . (int) $village['id'], ['class' => 'd-inline']); ?>
														<button class="btn btn-sm btn-action btn-action-muted" title="<?= (int) $village['is_active'] === 1 ? 'Nonaktifkan Desa / Kelurahan' : 'Aktifkan Desa / Kelurahan'; ?>" type="submit">
															<i class="ti <?= (int) $village['is_active'] === 1 ? 'ti-toggle-right' : 'ti-toggle-left'; ?>"></i><span><?= (int) $village['is_active'] === 1 ? 'Nonaktifkan' : 'Aktifkan'; ?></span>
														</button>
													<?= form_close(); ?>
												</div>
											</td>
										<?php endif; ?>
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
			</div>
		</div>
	</div>
</div>

<?php $this->load->view('regions/_district_modal', compact('edit_district', 'can_create', 'can_edit')); ?>
<?php $this->load->view('regions/_village_modal', compact('edit_village', 'districts', 'filters', 'can_create', 'can_edit')); ?>
