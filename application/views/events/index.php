<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$metrics = [
	['key' => 'events', 'label' => 'Agenda', 'icon' => 'ti ti-calendar-event'],
	['key' => 'published', 'label' => 'Publik', 'icon' => 'ti ti-speakerphone'],
	['key' => 'registrations', 'label' => 'Pendaftar', 'icon' => 'ti ti-users-group'],
	['key' => 'attended', 'label' => 'Hadir', 'icon' => 'ti ti-user-check'],
];
$status_labels = [
	'draft' => 'Draft',
	'published' => 'Terbit',
	'closed' => 'Selesai',
	'cancelled' => 'Dibatalkan',
];
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Layanan Digital</div>
				<h1 class="page-title">Event Literasi</h1>
			</div>
		</div>
	</div>
</div>

<div class="page-body">
	<div class="container-xl">
		<div class="service-command-center service-command-event">
			<div>
				<div class="section-kicker">Agenda jejaring perpustakaan</div>
				<h2>Event, pendaftaran, QR attendance, dan laporan kegiatan.</h2>
				<p>Modul ini disiapkan untuk publikasi kegiatan Perpusda, sekolah, desa, komunitas, dan mitra swasta yang terintegrasi.</p>
			</div>
			<span class="service-chip"><i class="ti ti-qrcode"></i> Attendance ready</span>
		</div>

		<div class="metric-ribbon service-metric-ribbon">
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
					<h2 class="card-title">Agenda Literasi</h2>
					<div class="text-secondary small">Daftar kegiatan dan jumlah peserta yang sudah masuk ke database.</div>
				</div>
			</div>
			<div class="table-responsive">
				<table class="table table-vcenter card-table">
					<thead><tr><th>Event</th><th>Waktu</th><th>Lokasi</th><th>Peserta</th><th>Status</th></tr></thead>
					<tbody>
						<?php if (empty($events)): ?><tr><td colspan="5" class="text-center text-secondary py-4">Belum ada event literasi. Data akan diinput admin saat jadwal siap.</td></tr><?php endif; ?>
						<?php foreach ($events as $event): ?>
							<tr>
								<td data-label="Event"><div class="fw-semibold"><?= html_escape($event['title']); ?></div><div class="text-secondary small"><?= html_escape($event['event_type'] ?: '-'); ?></div></td>
								<td data-label="Waktu"><div><?= html_escape($event['starts_at'] ?: '-'); ?></div><div class="text-secondary small">Sampai <?= html_escape($event['ends_at'] ?: '-'); ?></div></td>
								<td data-label="Lokasi"><div><?= html_escape($event['location_name'] ?: ($event['library_name'] ?: '-')); ?></div><div class="text-secondary small"><code><?= html_escape($event['latitude'] ?: '-'); ?>, <?= html_escape($event['longitude'] ?: '-'); ?></code></div></td>
								<td data-label="Peserta"><?= number_format((int) $event['registration_count'], 0, ',', '.'); ?> / <?= $event['quota'] ? number_format((int) $event['quota'], 0, ',', '.') : 'tanpa batas'; ?></td>
								<td data-label="Status"><span class="badge bg-blue-lt"><?= html_escape($status_labels[$event['status']] ?? $event['status']); ?></span></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
