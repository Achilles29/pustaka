<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$metrics = [
	['key' => 'member_visits', 'label' => 'Kunjungan', 'icon' => 'ti ti-door-enter'],
	['key' => 'access_rules', 'label' => 'Hak Layanan', 'icon' => 'ti ti-list-check'],
	['key' => 'loan_transactions', 'label' => 'Peminjaman', 'icon' => 'ti ti-receipt-2'],
	['key' => 'loan_items', 'label' => 'Item Koleksi', 'icon' => 'ti ti-books'],
	['key' => 'sync_runs', 'label' => 'Run', 'icon' => 'ti ti-refresh'],
];
$domain_labels = [
	'all' => 'Semua layanan',
	'visits' => 'Kunjungan tamu',
	'access_rules' => 'Hak layanan',
	'loans' => 'Histori pinjam',
];
$mode_labels = [
	'import_new' => 'Import data baru',
	'refresh_existing' => 'Update data lama',
	'dry_run' => 'Dry run / simulasi',
];
$status_labels = [
	'queued' => 'Antrean',
	'running' => 'Berjalan',
	'success' => 'Berhasil',
	'failed' => 'Gagal',
];
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Layanan Harian</div>
				<h1 class="page-title">Sinkronisasi Aktivitas INLISLite</h1>
			</div>
			<div class="col-auto ms-auto">
				<a href="<?= base_url('transactions'); ?>" class="btn btn-outline-primary">
					<i class="ti ti-table me-1"></i>Aktivitas Layanan
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

		<div class="row row-cards">
			<div class="col-lg-4">
				<?php if (! empty($can_run_sync)): ?>
					<div class="card admin-card sync-action-card mb-3">
						<div class="card-body">
							<div class="sync-action-icon"><i class="ti ti-arrows-transfer-down"></i></div>
							<h2 class="card-title mb-2">Tarik Aktivitas Layanan</h2>
							<p class="text-secondary">Sinkronisasi ini menarik buku tamu, hak layanan, peminjaman, dan detail item dari INLISLite ke schema `pustaka`.</p>
							<?= form_open('transactions/sync/run', ['class' => 'row g-2 align-items-end']); ?>
								<div class="col-12">
									<label class="form-label">Data</label>
									<select class="form-select" name="domain">
										<?php foreach ($domain_labels as $value => $label): ?>
											<option value="<?= $value; ?>"><?= html_escape($label); ?></option>
										<?php endforeach; ?>
									</select>
								</div>
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
										<option value="1000">1.000</option>
										<option value="5000" selected>5.000</option>
										<option value="10000">10.000</option>
									</select>
								</div>
								<div class="col-5">
									<button class="btn btn-primary w-100" type="submit" onclick="return confirm('Jalankan sinkronisasi layanan harian sekarang?')">
										<i class="ti ti-player-play me-1"></i>Jalankan
									</button>
								</div>
							<?= form_close(); ?>
						</div>
					</div>
				<?php endif; ?>

				<div class="card admin-card">
					<div class="card-header"><h2 class="card-title">Sumber INLISLite</h2></div>
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
					<div class="card-header"><h2 class="card-title">Riwayat Sinkronisasi</h2></div>
					<div class="table-responsive">
						<table class="table table-vcenter card-table">
							<thead>
								<tr>
									<th>Waktu</th>
									<th>Data</th>
									<th>Mode</th>
									<th>Status</th>
									<th>Ringkasan</th>
								</tr>
							</thead>
							<tbody>
								<?php if (empty($sync_runs)): ?>
									<tr><td colspan="5" class="text-center text-secondary py-4">Belum ada sinkronisasi transaksi.</td></tr>
								<?php endif; ?>
								<?php foreach ($sync_runs as $run): ?>
									<tr>
										<td data-label="Waktu"><?= html_escape($run['created_at']); ?></td>
										<td data-label="Data"><?= html_escape($domain_labels[$run['source_table']] ?? $run['source_table']); ?></td>
										<td data-label="Mode"><?= html_escape($mode_labels[$run['mode']] ?? $run['mode']); ?></td>
										<td data-label="Status"><span class="badge <?= $run['status'] === 'success' ? 'bg-green-lt' : 'bg-secondary-lt'; ?>"><?= html_escape($status_labels[$run['status']] ?? ucfirst($run['status'])); ?></span></td>
										<td data-label="Ringkasan">
											<?= number_format((int) $run['total_source'], 0, ',', '.'); ?> proses,
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
