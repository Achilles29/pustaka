<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$metrics = [
	['key' => 'assets', 'label' => 'Aset PDF', 'icon' => 'ti ti-file-type-pdf'],
	['key' => 'active_assets', 'label' => 'Aktif', 'icon' => 'ti ti-circle-check'],
	['key' => 'locked_assets', 'label' => 'Download Dikunci', 'icon' => 'ti ti-lock'],
	['key' => 'rights_expiring', 'label' => 'Izin < 60 Hari', 'icon' => 'ti ti-alert-circle'],
];
$policy_labels = [
	'online_only' => 'Online aman',
	'download_allowed' => 'Bebas download',
	'location_only' => 'Pojok Baca / token',
	'member_only' => 'Member saja',
	'internal' => 'Internal petugas',
];
$rights_labels = [
	'public_domain' => 'Domain publik',
	'licensed' => 'Lisensi resmi',
	'owned' => 'Milik perpustakaan',
	'permission_letter' => 'Surat izin',
	'internal_use' => 'Internal',
	'unknown' => 'Belum jelas',
];
$status_labels = [
	'draft' => 'Draft',
	'active' => 'Aktif',
	'archived' => 'Arsip',
];
$event_labels = [
	'session_opened' => 'Sesi dibuka',
	'pdf_stream' => 'Stream PDF',
	'page_rendered' => 'Halaman dirender',
	'rate_limited' => 'Rate limit',
	'blocked' => 'Diblokir',
	'finished' => 'Selesai',
];
$filters = $filters ?? [];
$pagination = $pagination ?? ['page' => 1, 'total_pages' => 1, 'total_rows' => 0, 'per_page' => 25, 'offset' => 0];
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Layanan Digital</div>
				<h1 class="page-title">Manajemen Ebook & Reader Aman</h1>
			</div>
			<div class="col-auto ms-auto btn-list">
				<a href="<?= base_url('assets-migration'); ?>" class="btn btn-outline-primary">
					<i class="ti ti-cloud-upload me-1"></i>Migrasi Aset
				</a>
				<a href="<?= base_url('reader/audit'); ?>" class="btn btn-outline-primary">
					<i class="ti ti-shield-search me-1"></i>Audit Reader
				</a>
				<?php if (! empty($can_create_asset)): ?>
					<a href="<?= base_url('reader/assets/create'); ?>" class="btn btn-primary">
						<i class="ti ti-plus me-1"></i>Tambah Ebook
					</a>
				<?php endif; ?>
			</div>
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

		<div class="metric-ribbon service-metric-ribbon mb-3">
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

		<div class="card admin-card data-workspace mb-3">
			<div class="card-header workspace-header">
				<div>
					<h2 class="card-title">Filter Ebook</h2>
					<div class="text-secondary small">Cari buku, file, policy, atau dasar hak publikasi.</div>
				</div>
			</div>
			<div class="card-body">
				<?= form_open('reader/assets', ['method' => 'get', 'class' => 'row g-2 align-items-end']); ?>
					<div class="col-lg-4 col-md-6">
						<label class="form-label">Cari</label>
						<input type="text" class="form-control" name="q" value="<?= html_escape($filters['q'] ?? ''); ?>" placeholder="Judul, nomor panggil, file, pemegang hak">
					</div>
					<div class="col-lg-2 col-md-6">
						<label class="form-label">Policy</label>
						<select class="form-select" name="access_policy">
							<option value="">Semua</option>
							<?php foreach ($policy_labels as $value => $label): ?>
								<option value="<?= $value; ?>" <?= ($filters['access_policy'] ?? '') === $value ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-lg-2 col-md-6">
						<label class="form-label">Hak</label>
						<select class="form-select" name="rights_basis">
							<option value="">Semua</option>
							<?php foreach ($rights_labels as $value => $label): ?>
								<option value="<?= $value; ?>" <?= ($filters['rights_basis'] ?? '') === $value ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-lg-2 col-md-6">
						<label class="form-label">Status</label>
						<select class="form-select" name="status">
							<option value="">Semua</option>
							<?php foreach ($status_labels as $value => $label): ?>
								<option value="<?= $value; ?>" <?= ($filters['status'] ?? '') === $value ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-lg-2 col-md-6">
						<label class="form-label">Baris</label>
						<select class="form-select" name="per_page">
							<?php foreach ([10, 25, 50, 100] as $size): ?>
								<option value="<?= $size; ?>" <?= (int) ($filters['per_page'] ?? 25) === $size ? 'selected' : ''; ?>><?= $size; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-12 btn-list">
						<button class="btn btn-primary" type="submit"><i class="ti ti-search me-1"></i>Terapkan</button>
						<a href="<?= base_url('reader/assets'); ?>" class="btn btn-outline-secondary"><i class="ti ti-refresh me-1"></i>Reset</a>
					</div>
				<?= form_close(); ?>
			</div>
		</div>

		<div class="row row-cards">
			<div class="col-xl-8">
				<div class="card admin-card data-workspace">
					<div class="card-header workspace-header">
						<div>
							<h2 class="card-title">Aset Digital</h2>
							<div class="text-secondary small"><?= number_format((int) $pagination['total_rows'], 0, ',', '.'); ?> aset terdaftar.</div>
						</div>
					</div>
					<div class="table-responsive">
						<table class="table table-vcenter card-table">
							<thead><tr><th>Buku</th><th>Hak Akses</th><th>Hak Publikasi</th><th>Status</th><th>Aksi</th></tr></thead>
							<tbody>
								<?php if (empty($assets)): ?><tr><td colspan="5" class="text-center text-secondary py-4">Belum ada aset PDF sesuai filter.</td></tr><?php endif; ?>
								<?php foreach ($assets as $asset): ?>
									<?php
										$is_locked = (int) $asset['is_downloadable'] !== 1 || $asset['access_policy'] !== 'download_allowed';
										$rights_end = ! empty($asset['permission_ends_at']) ? strtotime($asset['permission_ends_at']) : null;
										$rights_warning = $rights_end && $rights_end >= strtotime(date('Y-m-d')) && $rights_end <= strtotime('+60 days');
									?>
									<tr>
										<td data-label="Buku">
											<div class="fw-semibold"><?= html_escape($asset['title'] ?: 'Buku #' . $asset['book_id']); ?></div>
											<div class="text-secondary small"><?= html_escape($asset['call_number'] ?: '-'); ?></div>
											<div class="text-secondary small text-truncate" style="max-width: 20rem;"><?= html_escape($asset['file_original_name'] ?: basename((string) $asset['file_path'])); ?></div>
										</td>
										<td data-label="Hak Akses">
											<span class="badge bg-blue-lt"><?= html_escape($policy_labels[$asset['access_policy']] ?? $asset['access_policy']); ?></span>
											<span class="badge <?= $is_locked ? 'bg-red-lt' : 'bg-green-lt'; ?>"><?= $is_locked ? 'PDF utuh dikunci' : 'Download boleh'; ?></span>
										</td>
										<td data-label="Hak Publikasi">
											<div><span class="badge <?= ($asset['rights_basis'] ?? 'unknown') === 'unknown' ? 'bg-yellow-lt' : 'bg-cyan-lt'; ?>"><?= html_escape($rights_labels[$asset['rights_basis'] ?? 'unknown'] ?? ($asset['rights_basis'] ?? '-')); ?></span></div>
											<div class="text-secondary small mt-1"><?= html_escape($asset['rights_holder'] ?: 'Pemegang hak belum dicatat'); ?></div>
											<?php if (! empty($asset['permission_ends_at'])): ?>
												<div class="small <?= $rights_warning ? 'text-warning fw-bold' : 'text-secondary'; ?>">Izin s.d. <?= html_escape($asset['permission_ends_at']); ?></div>
											<?php endif; ?>
										</td>
										<td data-label="Status"><span class="badge bg-secondary-lt"><?= html_escape($status_labels[$asset['status']] ?? $asset['status']); ?></span></td>
										<td data-label="Aksi">
											<div class="btn-list flex-nowrap">
												<?php if ($asset['status'] === 'active'): ?>
													<a href="<?= base_url('reader/read/' . (int) $asset['id']); ?>" class="btn btn-sm btn-action btn-action-primary">
														<i class="ti ti-book-reader"></i><span>Baca</span>
													</a>
												<?php endif; ?>
												<?php if (! empty($can_edit_asset)): ?>
													<a href="<?= base_url('reader/assets/edit/' . (int) $asset['id']); ?>" class="btn btn-sm btn-outline-primary">
														<i class="ti ti-edit"></i><span>Edit</span>
													</a>
													<?= form_open('reader/assets/status/' . (int) $asset['id'], ['class' => 'd-inline']); ?>
														<input type="hidden" name="status" value="<?= $asset['status'] === 'active' ? 'archived' : 'active'; ?>">
														<button type="submit" class="btn btn-sm btn-outline-secondary">
															<i class="ti <?= $asset['status'] === 'active' ? 'ti-archive' : 'ti-circle-check'; ?>"></i>
															<span><?= $asset['status'] === 'active' ? 'Arsip' : 'Aktifkan'; ?></span>
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
					<?php if ((int) $pagination['total_pages'] > 1): ?>
						<div class="card-footer d-flex align-items-center">
							<div class="text-secondary small">Halaman <?= (int) $pagination['page']; ?> dari <?= (int) $pagination['total_pages']; ?></div>
							<div class="ms-auto btn-list">
								<?php
									$query = $filters;
									$query['page'] = max(1, (int) $pagination['page'] - 1);
								?>
								<a class="btn btn-outline-secondary <?= (int) $pagination['page'] <= 1 ? 'disabled' : ''; ?>" href="<?= base_url('reader/assets?' . http_build_query($query)); ?>">Sebelumnya</a>
								<?php $query['page'] = min((int) $pagination['total_pages'], (int) $pagination['page'] + 1); ?>
								<a class="btn btn-outline-secondary <?= (int) $pagination['page'] >= (int) $pagination['total_pages'] ? 'disabled' : ''; ?>" href="<?= base_url('reader/assets?' . http_build_query($query)); ?>">Berikutnya</a>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<div class="col-xl-4">
				<div class="card admin-card">
					<div class="card-header"><h2 class="card-title">Audit Reader Terbaru</h2></div>
					<div class="list-group list-group-flush">
						<?php if (empty($logs)): ?><div class="list-group-item text-secondary">Belum ada audit reader.</div><?php endif; ?>
						<?php foreach ($logs as $log): ?>
							<div class="list-group-item">
								<div class="d-flex align-items-start gap-2">
									<span class="badge <?= $log['event_type'] === 'blocked' ? 'bg-red-lt' : ($log['event_type'] === 'page_rendered' ? 'bg-green-lt' : 'bg-blue-lt'); ?> mt-1"><?= html_escape($event_labels[$log['event_type']] ?? $log['event_type']); ?></span>
									<div class="flex-fill">
										<div class="fw-semibold"><?= html_escape($log['title'] ?: 'Aset #' . $log['digital_asset_id']); ?></div>
										<div class="text-secondary small"><?= html_escape($log['full_name'] ?: 'Member tidak terbaca'); ?><?= ! empty($log['page_number']) ? ' - Hal. ' . (int) $log['page_number'] : ''; ?></div>
										<div class="text-secondary small"><?= html_escape($log['created_at']); ?></div>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
