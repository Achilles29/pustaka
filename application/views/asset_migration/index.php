<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$asset_labels = [
	'all' => 'Semua aset',
	'cover' => 'Cover buku',
	'member_photo' => 'Foto member',
	'digital_file' => 'File digital',
];
$mode_labels = [
	'copy_missing' => 'Salin yang belum ada',
	'refresh_existing' => 'Salin ulang / refresh',
	'dry_run' => 'Dry run / simulasi',
];
$run_status_labels = [
	'queued' => 'Antrean',
	'running' => 'Berjalan',
	'success' => 'Berhasil',
	'failed' => 'Gagal',
];
$item_status_labels = [
	'copied' => 'Tersalin',
	'skipped' => 'Dilewati',
	'missing' => 'Hilang',
	'failed' => 'Gagal',
	'pending' => 'Menunggu',
];
$status_cards = [
	[
		'title' => 'Cover Buku',
		'icon' => 'ti ti-book-2',
		'data' => $summary['covers'],
		'source' => $summary['source_files']['covers'],
		'target' => $summary['target_files']['covers'],
	],
	[
		'title' => 'Foto Member',
		'icon' => 'ti ti-user-square-rounded',
		'data' => $summary['member_photos'],
		'source' => $summary['source_files']['member_photos'],
		'target' => $summary['target_files']['member_photos'],
	],
	[
		'title' => 'File Digital',
		'icon' => 'ti ti-file-type-pdf',
		'data' => $summary['digital_files'],
		'source' => $summary['source_files']['digital_files'],
		'target' => $summary['target_files']['digital_files'],
	],
];
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Full Migrasi</div>
				<h1 class="page-title">Migrasi Aset INLISLite</h1>
			</div>
			<div class="col-auto ms-auto">
				<a href="<?= base_url('catalog/sync'); ?>" class="btn btn-outline-secondary">
					<i class="ti ti-database-import me-1"></i>Sinkron Data
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
		<?php if (! $summary['source_root_exists']): ?>
			<div class="alert alert-danger">
				Folder sumber INLISLite tidak ditemukan: <code><?= html_escape($summary['source_root']); ?></code>
			</div>
		<?php endif; ?>

		<div class="asset-status-grid mb-3">
			<?php foreach ($status_cards as $card): ?>
				<?php $pending = (int) ($card['data']['pending'] ?? 0) + (int) ($card['data']['missing'] ?? 0) + (int) ($card['data']['failed'] ?? 0); ?>
				<div class="card admin-card asset-status-card">
					<div class="card-body">
						<div class="asset-status-head">
							<span class="asset-status-icon"><i class="<?= html_escape($card['icon']); ?>"></i></span>
							<div>
								<h2 class="card-title mb-1"><?= html_escape($card['title']); ?></h2>
								<div class="text-secondary small">Sumber <?= number_format((int) $card['source'], 0, ',', '.'); ?> file, lokal <?= number_format((int) $card['target'], 0, ',', '.'); ?> file</div>
							</div>
						</div>
						<div class="asset-status-main">
							<div>
								<div class="asset-status-number"><?= number_format((int) ($card['data']['copied'] ?? 0), 0, ',', '.'); ?></div>
								<div class="text-secondary small">Tercatat tersalin</div>
							</div>
							<span class="badge <?= $pending > 0 ? 'bg-yellow-lt' : 'bg-green-lt'; ?>">
								<?= number_format($pending, 0, ',', '.'); ?> perlu cek
							</span>
						</div>
						<div class="asset-status-mini">
							<span>Referensi: <?= number_format((int) ($card['data']['referenced'] ?? 0), 0, ',', '.'); ?></span>
							<span>Hilang: <?= number_format((int) ($card['data']['missing'] ?? 0), 0, ',', '.'); ?></span>
							<span>Gagal: <?= number_format((int) ($card['data']['failed'] ?? 0), 0, ',', '.'); ?></span>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="row row-cards">
			<div class="col-lg-4">
				<?php if (! empty($can_run_migration)): ?>
					<div class="card admin-card sync-action-card">
						<div class="card-body">
							<div class="sync-action-icon"><i class="ti ti-file-import"></i></div>
							<h2 class="card-title mb-2">Jalankan Batch Migrasi</h2>
							<p class="text-secondary">Proses bisa diulang. File yang sudah cocok ukurannya akan dilewati dan tetap dicatat aman.</p>
							<?= form_open('assets-migration/run', ['class' => 'row g-2 align-items-end']); ?>
								<div class="col-12">
									<label class="form-label">Jenis aset</label>
									<select class="form-select" name="asset_type">
										<?php foreach ($asset_labels as $value => $label): ?>
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
										<option value="250">250 file</option>
										<option value="500" selected>500 file</option>
										<option value="1000">1.000 file</option>
										<option value="2000">2.000 file</option>
										<option value="5000">5.000 file</option>
									</select>
								</div>
								<div class="col-5">
									<button type="submit" class="btn btn-primary w-100" onclick="return confirm('Jalankan migrasi aset sekarang?')">
										<i class="ti ti-player-play me-1"></i>Jalankan
									</button>
								</div>
							<?= form_close(); ?>
						</div>
					</div>
				<?php endif; ?>

				<div class="card admin-card mt-3">
					<div class="card-header"><h2 class="card-title">Lokasi Storage</h2></div>
					<div class="card-body">
						<div class="datagrid">
							<div class="datagrid-item">
								<div class="datagrid-title">Sumber</div>
								<div class="datagrid-content"><code><?= html_escape($summary['source_root']); ?></code></div>
							</div>
							<div class="datagrid-item">
								<div class="datagrid-title">Target</div>
								<div class="datagrid-content"><code><?= html_escape($summary['target_root']); ?></code></div>
							</div>
							<div class="datagrid-item">
								<div class="datagrid-title">Portable</div>
								<div class="datagrid-content">View aplikasi membaca file lokal lebih dulu.</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-lg-8">
				<div class="card admin-card">
					<div class="card-header">
						<h2 class="card-title">Riwayat Migrasi</h2>
					</div>
					<div class="table-responsive">
						<table class="table table-vcenter card-table">
							<thead>
								<tr>
									<th>Waktu</th>
									<th>Jenis</th>
									<th>Mode</th>
									<th>Status</th>
									<th>Ringkasan</th>
								</tr>
							</thead>
							<tbody>
								<?php if (empty($summary['runs'])): ?>
									<tr><td colspan="5" class="text-center text-secondary py-4">Belum ada proses migrasi aset.</td></tr>
								<?php endif; ?>
								<?php foreach ($summary['runs'] as $run): ?>
									<tr>
										<td><?= html_escape($run['created_at']); ?></td>
										<td><?= html_escape($asset_labels[$run['asset_type']] ?? $run['asset_type']); ?></td>
										<td><?= html_escape($mode_labels[$run['mode']] ?? $run['mode']); ?></td>
										<td><span class="badge <?= $run['status'] === 'success' ? 'bg-green-lt' : 'bg-secondary-lt'; ?>"><?= html_escape($run_status_labels[$run['status']] ?? ucfirst($run['status'])); ?></span></td>
										<td>
											<?= number_format((int) $run['total_source'], 0, ',', '.'); ?> proses,
											<?= number_format((int) $run['total_copied'], 0, ',', '.'); ?> salin,
											<?= number_format((int) $run['total_skipped'], 0, ',', '.'); ?> lewat,
											<?= number_format((int) $run['total_missing'], 0, ',', '.'); ?> hilang,
											<?= number_format((int) $run['total_failed'], 0, ',', '.'); ?> gagal
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>

				<div class="card admin-card mt-3">
					<div class="card-header">
						<h2 class="card-title">Audit Item Perlu Dicek</h2>
					</div>
					<div class="table-responsive">
						<table class="table table-vcenter card-table">
							<thead>
								<tr>
									<th>Aset</th>
									<th>Entitas</th>
									<th>Status</th>
									<th>Path Sumber</th>
								</tr>
							</thead>
							<tbody>
								<?php if (empty($summary['recent_issues'])): ?>
									<tr><td colspan="4" class="text-center text-secondary py-4">Tidak ada item hilang/gagal terbaru.</td></tr>
								<?php endif; ?>
								<?php foreach ($summary['recent_issues'] as $item): ?>
									<tr>
										<td><?= html_escape($asset_labels[$item['asset_type']] ?? $item['asset_type']); ?></td>
										<td>
											<div><?= html_escape($item['entity_type'] ?: '-'); ?></div>
											<div class="text-secondary small">ID sumber: <?= html_escape($item['source_id'] ?: '-'); ?></div>
										</td>
										<td>
											<span class="badge <?= $item['status'] === 'missing' ? 'bg-yellow-lt' : 'bg-red-lt'; ?>"><?= html_escape($item_status_labels[$item['status']] ?? ucfirst($item['status'])); ?></span>
											<?php if (! empty($item['error_message'])): ?><div class="text-secondary small"><?= html_escape($item['error_message']); ?></div><?php endif; ?>
										</td>
										<td><code><?= html_escape($item['source_path'] ?: '-'); ?></code></td>
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
