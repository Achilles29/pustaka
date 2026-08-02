<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$status_labels = [
	'pending' => 'Menunggu Verifikasi',
	'verified' => 'Terverifikasi',
	'rejected' => 'Ditolak',
	'cancelled' => 'Dibatalkan',
];
$metrics = [
	['key' => 'pending', 'label' => 'Menunggu', 'icon' => 'ti ti-inbox'],
	['key' => 'verified', 'label' => 'Terverifikasi', 'icon' => 'ti ti-user-check'],
	['key' => 'rejected', 'label' => 'Ditolak', 'icon' => 'ti ti-user-x'],
	['key' => 'total', 'label' => 'Total', 'icon' => 'ti ti-users'],
];
$query_base = $_GET;
unset($query_base['page']);
$page_url = function ($page) use ($query_base) {
	return base_url('members/registrations?' . http_build_query(array_merge($query_base, ['page' => $page])));
};
$file_url = function ($path) {
	return $path ? base_url($path) : '';
};
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Kotak Masuk</div>
				<h1 class="page-title">Pendaftaran Online</h1>
			</div>
			<div class="col-auto ms-auto">
				<a href="<?= base_url('membership/register'); ?>" class="btn btn-outline-primary" target="_blank">
					<i class="ti ti-external-link me-1"></i>Form Publik
				</a>
			</div>
		</div>
	</div>
</div>

<div class="page-body">
	<div class="container-xl">
		<?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?= html_escape($this->session->flashdata('success')); ?></div><?php endif; ?>
		<?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?= html_escape($this->session->flashdata('error')); ?></div><?php endif; ?>

		<div class="admin-inbox-banner <?= (int) ($stats['pending'] ?? 0) > 0 ? 'has-pending' : ''; ?>">
			<i class="ti ti-inbox"></i>
			<div>
				<strong><?= number_format((int) ($stats['pending'] ?? 0), 0, ',', '.'); ?> pendaftaran menunggu verifikasi</strong>
				<span>Cek kesesuaian data, foto, KTP, KK, dan surat keterangan untuk pendaftar luar Rembang sebelum akun aktif.</span>
			</div>
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
				<?= form_open('members/registrations', ['method' => 'get', 'class' => 'row g-2 align-items-end service-filter-form']); ?>
					<div class="col-md-5">
						<label class="form-label">Cari</label>
						<input type="text" class="form-control" name="q" value="<?= html_escape($filters['q'] ?? ''); ?>" placeholder="Kode, nama, NIK, HP, email">
					</div>
					<div class="col-md-3">
						<label class="form-label">Status</label>
						<select class="form-select" name="status">
							<option value="">Semua</option>
							<?php foreach ($status_labels as $value => $label): ?>
								<option value="<?= $value; ?>" <?= ($filters['status'] ?? '') === $value ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
							<?php endforeach; ?>
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
					<thead><tr><th>Pendaftar</th><th>Domisili</th><th>Berkas</th><th>Status</th><th>Aksi</th></tr></thead>
					<tbody>
						<?php if (empty($requests)): ?><tr><td colspan="5" class="text-center text-secondary py-4">Belum ada pendaftaran sesuai filter.</td></tr><?php endif; ?>
						<?php foreach ($requests as $request): ?>
							<tr>
								<td data-label="Pendaftar">
									<div class="fw-semibold"><?= html_escape($request['full_name']); ?></div>
									<div class="text-secondary small"><?= html_escape($request['registration_code']); ?> - <code><?= html_escape($request['identity_number']); ?></code></div>
									<div class="small text-blue"><?= html_escape($request['phone'] ?: ($request['email'] ?: '-')); ?></div>
								</td>
								<td data-label="Domisili">
									<div><?= (int) $request['is_rembang_resident'] === 1 ? 'KTP Rembang' : 'KTP Luar Rembang'; ?></div>
									<div class="text-secondary small"><?= html_escape($request['residency_note'] ?: trim(($request['village'] ?: '') . ' ' . ($request['district'] ?: ''))); ?></div>
								</td>
								<td data-label="Berkas">
									<div class="btn-list flex-nowrap registration-files">
										<a class="btn btn-sm btn-action btn-action-muted" href="<?= html_escape($file_url($request['photo_path'])); ?>" target="_blank"><i class="ti ti-photo"></i><span>Foto</span></a>
										<a class="btn btn-sm btn-action btn-action-muted" href="<?= html_escape($file_url($request['ktp_path'])); ?>" target="_blank"><i class="ti ti-id"></i><span>KTP</span></a>
										<a class="btn btn-sm btn-action btn-action-muted" href="<?= html_escape($file_url($request['kk_path'])); ?>" target="_blank"><i class="ti ti-file-text"></i><span>KK</span></a>
										<?php if ($request['support_letter_path']): ?><a class="btn btn-sm btn-action btn-action-muted" href="<?= html_escape($file_url($request['support_letter_path'])); ?>" target="_blank"><i class="ti ti-certificate"></i><span>Surat</span></a><?php endif; ?>
									</div>
								</td>
								<td data-label="Status"><span class="badge bg-blue-lt"><?= html_escape($status_labels[$request['status']] ?? $request['status']); ?></span><?php if ($request['member_no']): ?><div class="text-secondary small"><code><?= html_escape($request['member_no']); ?></code></div><?php endif; ?></td>
								<td data-label="Aksi">
									<?php if ($request['status'] === 'pending'): ?>
										<?= form_open('members/registrations/update/' . (int) $request['id'], ['class' => 'queue-action-form']); ?>
											<select class="form-select form-select-sm" name="status">
												<option value="verified">Verifikasi dan Aktifkan</option>
												<option value="rejected">Tolak</option>
												<option value="cancelled">Batalkan</option>
											</select>
											<input type="text" class="form-control form-control-sm" name="admin_note" placeholder="Catatan verifikasi">
											<button class="btn btn-primary btn-sm w-100"><i class="ti ti-check me-1"></i>Simpan Keputusan</button>
										<?= form_close(); ?>
									<?php else: ?>
										<div class="text-secondary small"><?= html_escape($request['admin_note'] ?: 'Sudah diproses'); ?></div>
									<?php endif; ?>
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
