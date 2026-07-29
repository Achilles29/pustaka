<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$run_status_labels = [
	'queued' => 'Antrean',
	'running' => 'Berjalan',
	'success' => 'Berhasil',
	'failed' => 'Gagal',
];
$mode_labels = [
	'import_new' => 'Import data baru',
	'refresh_existing' => 'Update data lama',
	'dry_run' => 'Dry run / simulasi',
];
$run_mode_label = function ($run) {
	if (($run['sync_type'] ?? '') === 'dry_run') {
		return 'Dry run / simulasi';
	}
	if (strpos((string) ($run['message'] ?? ''), 'update data lama') !== false) {
		return 'Update data lama';
	}
	if (strpos((string) ($run['message'] ?? ''), 'import data baru') !== false) {
		return 'Import data baru';
	}
	return 'Manual';
};
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Fase 2</div>
				<h1 class="page-title">Sinkronisasi Katalog</h1>
			</div>
			<div class="col-auto ms-auto">
				<a href="<?= base_url('catalog'); ?>" class="btn btn-outline-secondary">
					<i class="ti ti-arrow-left me-1"></i>Katalog
				</a>
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

		<div class="row row-cards">
			<div class="col-lg-4">
				<?php if (! empty($can_run_sync)): ?>
					<div class="card admin-card mb-3 sync-action-card">
						<div class="card-body">
							<div class="sync-action-icon"><i class="ti ti-cloud-download"></i></div>
							<h2 class="card-title mb-2">Tarik Katalog INLISLite</h2>
							<p class="text-secondary">Pilih mode agar prosesnya jelas: import hanya data baru, update menyegarkan data lama, dry run hanya simulasi tanpa menulis data.</p>
							<?= form_open('catalog/sync/run', ['class' => 'row g-2 align-items-end']); ?>
								<div class="col-12">
									<label class="form-label">Mode</label>
									<select class="form-select" name="mode">
										<?php foreach ($mode_labels as $value => $label): ?>
											<option value="<?= $value; ?>"><?= html_escape($label); ?></option>
										<?php endforeach; ?>
									</select>
								</div>
								<div class="col-7">
									<label class="form-label">Batch</label>
									<select class="form-select" name="limit">
										<option value="500">500 data</option>
										<option value="1000">1.000 data</option>
										<option value="2000">2.000 data</option>
									</select>
								</div>
								<div class="col-5">
									<button type="submit" class="btn btn-primary w-100" onclick="return confirm('Jalankan sinkronisasi katalog sekarang?')">
										<i class="ti ti-refresh me-1"></i>Jalankan
									</button>
								</div>
							<?= form_close(); ?>
						</div>
					</div>
				<?php endif; ?>

				<div class="card admin-card">
					<div class="card-header">
						<h2 class="card-title">Sumber INLISLite</h2>
					</div>
					<div class="list-group list-group-flush">
						<?php foreach ($source_stats as $source): ?>
							<div class="list-group-item d-flex align-items-center">
								<div class="flex-fill"><?= html_escape($source['label']); ?></div>
								<span class="badge bg-blue-lt"><?= number_format((int) $source['value'], 0, ',', '.'); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

			<div class="col-lg-8">
				<div class="card admin-card">
					<div class="card-header">
						<h2 class="card-title">Riwayat Sinkronisasi</h2>
					</div>
					<div class="table-responsive">
						<table class="table table-vcenter card-table">
							<thead>
								<tr>
									<th>Waktu</th>
									<th>Sumber</th>
									<th>Mode</th>
									<th>Status</th>
									<th>Ringkasan</th>
								</tr>
							</thead>
							<tbody>
								<?php if (empty($sync_runs)): ?>
									<tr>
										<td colspan="5" class="text-center text-secondary py-4">Belum ada proses sinkronisasi.</td>
									</tr>
								<?php endif; ?>
								<?php foreach ($sync_runs as $run): ?>
									<tr>
										<td><?= html_escape($run['created_at']); ?></td>
										<td><?= html_escape($run['source_database'] . ($run['source_table'] ? '.' . $run['source_table'] : '')); ?></td>
										<td><?= html_escape($run_mode_label($run)); ?></td>
										<td><span class="badge bg-secondary-lt"><?= html_escape($run_status_labels[$run['status']] ?? ucfirst($run['status'])); ?></span></td>
										<td>
											<?= number_format((int) $run['total_source'], 0, ',', '.'); ?> sumber,
											<?= number_format((int) $run['total_inserted'], 0, ',', '.'); ?> baru,
											<?= number_format((int) $run['total_updated'], 0, ',', '.'); ?> update,
											<?= number_format((int) $run['total_failed'], 0, ',', '.'); ?> gagal
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
