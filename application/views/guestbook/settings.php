<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$refresh_seconds = max(15, min(600, (int) ($settings['qr_refresh_seconds'] ?? 60)));
$default_library_id = (int) ($settings['default_visit_library_id'] ?? 0);
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Layanan Harian</div>
				<h1 class="page-title">Pengaturan Buku Tamu</h1>
			</div>
			<div class="col-auto">
				<a href="<?= base_url('guestbook/monitor'); ?>" class="btn btn-outline-primary" target="_blank">
					<i class="ti ti-external-link me-1"></i>Buka Monitor
				</a>
			</div>
		</div>
	</div>
</div>

<div class="page-body">
	<div class="container-xl">
		<?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?= html_escape($this->session->flashdata('success')); ?></div><?php endif; ?>
		<?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?= html_escape($this->session->flashdata('error')); ?></div><?php endif; ?>

		<div class="row g-3">
			<div class="col-lg-8">
				<div class="card admin-card">
					<div class="card-header">
						<h2 class="card-title">Monitor QR Dinamis</h2>
					</div>
					<?= form_open('guestbook/settings/update'); ?>
						<div class="card-body">
							<div class="row g-3">
								<div class="col-md-6">
									<label class="form-label">Refresh QR Code</label>
									<div class="input-group">
										<input type="number" class="form-control" name="qr_refresh_seconds" value="<?= $refresh_seconds; ?>" min="15" max="600" step="5" required>
										<span class="input-group-text">detik</span>
									</div>
									<div class="form-hint">Batas aman 15-600 detik. Rekomendasi operasional: 45-90 detik.</div>
								</div>
								<div class="col-md-6">
									<label class="form-label">Perpustakaan Default Monitor</label>
									<select class="form-select" name="default_visit_library_id">
										<option value="0">Tidak dikunci ke perpustakaan tertentu</option>
										<?php foreach ($libraries as $library): ?>
											<option value="<?= (int) $library['id']; ?>" <?= $default_library_id === (int) $library['id'] ? 'selected' : ''; ?>>
												<?= html_escape($library['code'] . ' - ' . $library['name']); ?>
											</option>
										<?php endforeach; ?>
									</select>
									<div class="form-hint">Dipakai saat URL monitor dibuka tanpa parameter `library_id`.</div>
								</div>
							</div>
						</div>
						<div class="card-footer d-flex justify-content-between align-items-center">
							<span class="text-secondary small">QR baru akan mengikuti setting ini saat halaman monitor berikutnya dimuat.</span>
							<button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Simpan Pengaturan</button>
						</div>
					<?= form_close(); ?>
				</div>
			</div>
			<div class="col-lg-4">
				<div class="card admin-card guestbook-setting-preview">
					<div class="card-body">
						<div class="section-kicker">Preview SOP</div>
						<h3>QR aktif <?= number_format($refresh_seconds, 0, ',', '.'); ?> detik</h3>
						<p>Monitor pelayanan akan membuat token QR baru mengikuti durasi ini. Member yang scan setelah token kedaluwarsa diminta scan ulang QR terbaru.</p>
						<div class="guestbook-setting-meter">
							<span style="width: <?= min(100, max(5, ($refresh_seconds / 600) * 100)); ?>%"></span>
						</div>
						<div class="datagrid mt-3">
							<div class="datagrid-item">
								<div class="datagrid-title">Minimum</div>
								<div class="datagrid-content">15 detik</div>
							</div>
							<div class="datagrid-item">
								<div class="datagrid-title">Maksimum</div>
								<div class="datagrid-content">600 detik</div>
							</div>
							<div class="datagrid-item">
								<div class="datagrid-title">Monitor</div>
								<div class="datagrid-content"><code>/guestbook/monitor</code></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
