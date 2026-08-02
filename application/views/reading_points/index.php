<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$metrics = [
	['key' => 'points', 'label' => 'Titik Baca', 'icon' => 'ti ti-map-pin-star'],
	['key' => 'active_points', 'label' => 'Aktif', 'icon' => 'ti ti-circle-check'],
	['key' => 'active_tokens', 'label' => 'Token Aktif', 'icon' => 'ti ti-ticket'],
	['key' => 'sessions', 'label' => 'Sesi Baca', 'icon' => 'ti ti-device-tablet'],
];
$status_labels = [
	'draft' => 'Draft',
	'active' => 'Aktif',
	'inactive' => 'Nonaktif',
];
$query_base = $_GET;
unset($query_base['page']);
$page_url = function ($page) use ($query_base) {
	return base_url('reading-points?' . http_build_query(array_merge($query_base, ['page' => $page])));
};
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Layanan Digital</div>
				<h1 class="page-title">Pojok Baca Digital</h1>
			</div>
			<div class="col-auto ms-auto">
				<a href="<?= base_url('reading-points/create'); ?>" class="btn btn-primary">
					<i class="ti ti-map-pin-plus me-1"></i>Tambah Titik
				</a>
			</div>
		</div>
	</div>
</div>

<div class="page-body">
	<div class="container-xl">
		<?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?= html_escape($this->session->flashdata('success')); ?></div><?php endif; ?>
		<?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?= html_escape($this->session->flashdata('error')); ?></div><?php endif; ?>

		<div class="ops-summary-strip">
			<div>
				<div class="section-kicker">Pengaturan akses lokasi</div>
				<h2>Titik GPS, radius, mitra, jam aktif, dan kuota baca.</h2>
				<p>Tambahkan titik layanan seperti Namua, pojok baca desa, sekolah, atau mitra lain. Token/check-in akan memakai radius dan kuota dari data ini.</p>
			</div>
			<a href="<?= base_url('reader/assets'); ?>" class="btn btn-outline-primary"><i class="ti ti-file-lock me-1"></i>Atur PDF</a>
		</div>

		<div class="metric-ribbon service-metric-ribbon">
			<?php foreach ($metrics as $metric): ?>
				<div class="metric-ribbon-item">
					<span class="metric-icon"><i class="<?= html_escape($metric['icon']); ?>"></i></span>
					<div><div class="metric-value"><?= number_format((int) ($stats[$metric['key']] ?? 0), 0, ',', '.'); ?></div><div class="metric-label"><?= html_escape($metric['label']); ?></div></div>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="card admin-card data-workspace">
			<div class="card-body workspace-filter">
				<?= form_open('reading-points', ['method' => 'get', 'class' => 'row g-2 align-items-end service-filter-form']); ?>
					<div class="col-md-5">
						<label class="form-label">Cari</label>
						<input type="text" class="form-control" name="q" value="<?= html_escape($filters['q'] ?? ''); ?>" placeholder="Nama titik, mitra, perpustakaan, alamat">
					</div>
					<div class="col-md-3">
						<label class="form-label">Status</label>
						<select class="form-select" name="status">
							<option value="">Semua</option>
							<?php foreach ($status_labels as $value => $label): ?><option value="<?= $value; ?>" <?= ($filters['status'] ?? '') === $value ? 'selected' : ''; ?>><?= html_escape($label); ?></option><?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-2">
						<label class="form-label">Baris</label>
						<select class="form-select" name="per_page">
							<?php foreach ([10, 25, 50, 100] as $limit): ?><option value="<?= $limit; ?>" <?= (int) ($filters['per_page'] ?? 25) === $limit ? 'selected' : ''; ?>><?= $limit; ?></option><?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-2">
						<button class="btn btn-primary w-100"><i class="ti ti-filter me-1"></i>Filter</button>
					</div>
				<?= form_close(); ?>
			</div>

			<div class="table-responsive">
				<table class="table table-vcenter card-table">
					<thead><tr><th>Titik</th><th>Koordinat</th><th>Kuota</th><th>Status</th><th>Aksi</th></tr></thead>
					<tbody>
						<?php if (empty($points)): ?><tr><td colspan="5" class="text-center text-secondary py-4">Belum ada titik pojok baca sesuai filter.</td></tr><?php endif; ?>
						<?php foreach ($points as $point): ?>
							<tr>
								<td data-label="Titik">
									<div class="fw-semibold"><?= html_escape($point['name']); ?></div>
									<div class="text-secondary small"><?= html_escape($point['partner_name'] ?: ($point['library_name'] ?: '-')); ?></div>
									<div class="small text-blue"><?= html_escape(trim(($point['village'] ?: '') . ' ' . ($point['district'] ?: ''))); ?></div>
								</td>
								<td data-label="Koordinat">
									<div><code><?= html_escape($point['latitude'] ?: '-'); ?>, <?= html_escape($point['longitude'] ?: '-'); ?></code></div>
									<div class="text-secondary small">Radius <?= number_format((int) $point['radius_meters'], 0, ',', '.'); ?> meter</div>
								</td>
								<td data-label="Kuota"><?= number_format((int) $point['daily_quota'], 0, ',', '.'); ?> <?= html_escape($point['quota_unit']); ?> / hari</td>
								<td data-label="Status"><span class="badge bg-blue-lt"><?= html_escape($status_labels[$point['status']] ?? $point['status']); ?></span></td>
								<td data-label="Aksi">
									<a href="<?= base_url('reading-points/edit/' . (int) $point['id']); ?>" class="btn btn-sm btn-action btn-action-primary">
										<i class="ti ti-edit"></i><span>Edit</span>
									</a>
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
					<?php for ($i = max(1, $pagination['page'] - 2); $i <= min($pagination['total_pages'], $pagination['page'] + 2); $i++): ?><li class="page-item <?= $i === (int) $pagination['page'] ? 'active' : ''; ?>"><a class="page-link" href="<?= $page_url($i); ?>"><?= $i; ?></a></li><?php endfor; ?>
					<li class="page-item <?= $pagination['page'] >= $pagination['total_pages'] ? 'disabled' : ''; ?>"><a class="page-link" href="<?= $page_url(min($pagination['total_pages'], $pagination['page'] + 1)); ?>">Next</a></li>
				</ul>
			</div>
		</div>
	</div>
</div>
