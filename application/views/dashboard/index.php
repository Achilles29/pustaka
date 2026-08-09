<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$queue = $stats['queue'] ?? ['total' => 0, 'items' => []];
$activity = $stats['activity'] ?? [];
$libraries = $stats['libraries'] ?? [];
$service = $stats['service'] ?? [];
$sync_health = $stats['sync_health'] ?? [];

$metric_cards = [
	[
		'label' => 'Katalog Buku',
		'value' => (int) ($service['books']['value'] ?? 0),
		'caption' => 'Judul dalam database Pustaka',
		'icon' => 'ti ti-books',
		'accent' => 'blue',
		'url' => 'catalog',
	],
	[
		'label' => 'Member Aktif',
		'value' => (int) ($service['members']['value'] ?? 0),
		'caption' => 'Profil pemustaka dan akun digital',
		'icon' => 'ti ti-id-badge-2',
		'accent' => 'green',
		'url' => 'members',
	],
	[
		'label' => 'Perpustakaan GIS',
		'value' => (int) ($libraries['active'] ?? 0),
		'caption' => 'Titik layanan yang aktif',
		'icon' => 'ti ti-map-2',
		'accent' => 'cyan',
		'url' => 'libraries',
	],
	[
		'label' => 'Akses Digital',
		'value' => (int) ($activity['digital_assets'] ?? 0),
		'caption' => 'Aset baca online aktif',
		'icon' => 'ti ti-file-type-pdf',
		'accent' => 'indigo',
		'url' => 'reader/assets',
	],
];

$activity_cards = [
	['label' => 'Kunjungan Hari Ini', 'value' => (int) ($activity['today_visits'] ?? 0), 'icon' => 'ti ti-door-enter'],
	['label' => 'Token Aktif', 'value' => (int) ($activity['active_tokens'] ?? 0), 'icon' => 'ti ti-ticket'],
	['label' => 'Sesi Baca Aktif', 'value' => (int) ($activity['active_reading_sessions'] ?? 0), 'icon' => 'ti ti-device-tablet'],
	['label' => 'Event Tayang', 'value' => (int) ($activity['published_events'] ?? 0), 'icon' => 'ti ti-calendar-event'],
];

$quick_actions = [
	['label' => 'Monitor Buku Tamu', 'url' => 'guestbook/monitor', 'icon' => 'ti ti-qrcode'],
	['label' => 'Laporan Kunjungan', 'url' => 'reports/visits', 'icon' => 'ti ti-chart-line'],
	['label' => 'Kelola Event', 'url' => 'events', 'icon' => 'ti ti-calendar-plus'],
	['label' => 'Pojok Baca', 'url' => 'reading-points', 'icon' => 'ti ti-map-pin-check'],
	['label' => 'Monitoring Token', 'url' => 'reading-points/tokens', 'icon' => 'ti ti-shield-check'],
	['label' => 'Pengaturan Sidebar', 'url' => 'rbac/sidebar', 'icon' => 'ti ti-layout-sidebar'],
];

$status_badge = function ($status) {
	$status = strtolower((string) $status);
	if ($status === 'success') {
		return 'bg-green-lt text-green';
	}
	if ($status === 'failed') {
		return 'bg-red-lt text-red';
	}
	if ($status === 'running' || $status === 'queued') {
		return 'bg-yellow-lt text-yellow';
	}
	return 'bg-secondary-lt text-secondary';
};
$short_text = function ($value, $limit = 92) {
	$value = trim((string) $value);
	if ($value === '') {
		return '-';
	}

	return strlen($value) > $limit ? substr($value, 0, max(1, $limit - 3)) . '...' : $value;
};
?>
<div class="page-header d-print-none dashboard-header-clean">
	<div class="container-xl">
		<div class="row g-3 align-items-center">
			<div class="col">
				<div class="page-pretitle">Admin Panel</div>
				<h1 class="page-title">Dashboard Operasional</h1>
				<div class="text-secondary">Pantau antrean, layanan harian, koleksi, dan kesehatan data dari satu layar.</div>
			</div>
			<div class="col-auto ms-auto d-print-none">
				<div class="btn-list">
					<a href="<?= base_url('guestbook/monitor'); ?>" class="btn btn-outline-primary">
						<i class="ti ti-qrcode me-1"></i>Monitor Tamu
					</a>
					<a href="<?= base_url('reports/visits'); ?>" class="btn btn-primary">
						<i class="ti ti-chart-bar me-1"></i>Laporan
					</a>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="page-body dashboard-page">
	<div class="container-xl">
		<div class="dashboard-command">
			<div class="dashboard-command-main">
				<div class="dashboard-command-kicker">Kotak masuk layanan</div>
				<h2><?= number_format((int) $queue['total'], 0, ',', '.'); ?> antrean perlu ditinjau</h2>
				<p>Permintaan publik dan member yang masuk akan muncul di sini agar admin tidak melewatkan pekerjaan penting.</p>
			</div>
			<div class="dashboard-command-actions">
				<?php foreach (($queue['items'] ?? []) as $item): ?>
					<a href="<?= base_url($item['url']); ?>" class="dashboard-queue-pill <?= (int) $item['value'] > 0 ? 'has-work' : ''; ?>">
						<i class="<?= html_escape($item['icon']); ?>"></i>
						<span><?= html_escape($item['label']); ?></span>
						<strong><?= number_format((int) $item['value'], 0, ',', '.'); ?></strong>
					</a>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="dashboard-metric-grid">
			<?php foreach ($metric_cards as $card): ?>
				<a href="<?= base_url($card['url']); ?>" class="dashboard-metric-card accent-<?= html_escape($card['accent']); ?>">
					<span class="dashboard-metric-icon"><i class="<?= html_escape($card['icon']); ?>"></i></span>
					<span class="dashboard-metric-label"><?= html_escape($card['label']); ?></span>
					<strong><?= number_format((int) $card['value'], 0, ',', '.'); ?></strong>
					<small><?= html_escape($card['caption']); ?></small>
				</a>
			<?php endforeach; ?>
		</div>

		<div class="row g-3 align-items-stretch">
			<div class="col-xl-8">
				<div class="card admin-card dashboard-panel h-100">
					<div class="card-header">
						<div>
							<h2 class="card-title">Aktivitas Hari Ini</h2>
							<div class="text-secondary small">Ringkasan layanan fisik, digital, token, dan event.</div>
						</div>
					</div>
					<div class="card-body">
						<div class="dashboard-activity-grid">
							<?php foreach ($activity_cards as $card): ?>
								<div class="dashboard-activity-card">
									<i class="<?= html_escape($card['icon']); ?>"></i>
									<div>
										<strong><?= number_format((int) $card['value'], 0, ',', '.'); ?></strong>
										<span><?= html_escape($card['label']); ?></span>
									</div>
								</div>
							<?php endforeach; ?>
						</div>

						<div class="dashboard-map-strip">
							<div>
								<div class="section-kicker">Jejaring layanan</div>
								<h3><?= number_format((int) ($libraries['total'] ?? 0), 0, ',', '.'); ?> perpustakaan terdaftar</h3>
								<p><?= number_format((int) ($libraries['verified'] ?? 0), 0, ',', '.'); ?> titik sudah terverifikasi dan <?= number_format((int) ($libraries['photos'] ?? 0), 0, ',', '.'); ?> foto profil tersedia.</p>
							</div>
							<a href="<?= base_url('libraries'); ?>" class="btn btn-outline-primary">
								<i class="ti ti-map-2 me-1"></i>Buka GIS
							</a>
						</div>
					</div>
				</div>
			</div>

			<div class="col-xl-4">
				<div class="card admin-card dashboard-panel h-100">
					<div class="card-header">
						<div>
							<h2 class="card-title">Aksi Cepat</h2>
							<div class="text-secondary small">Jalur pendek ke pekerjaan admin rutin.</div>
						</div>
					</div>
					<div class="dashboard-action-list">
						<?php foreach ($quick_actions as $action): ?>
							<a href="<?= base_url($action['url']); ?>" class="dashboard-action-link">
								<i class="<?= html_escape($action['icon']); ?>"></i>
								<span><?= html_escape($action['label']); ?></span>
								<i class="ti ti-chevron-right"></i>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>

		<div class="row g-3 mt-0">
			<div class="col-lg-7">
				<div class="card admin-card dashboard-panel">
					<div class="card-header">
						<div>
							<h2 class="card-title">Kesehatan Sinkronisasi</h2>
							<div class="text-secondary small">Hanya halaman sinkronisasi yang membaca sumber lama. Halaman operasional memakai database Pustaka.</div>
						</div>
					</div>
					<div class="table-responsive">
						<table class="table table-vcenter card-table">
							<thead>
								<tr><th>Domain</th><th>Status</th><th>Terakhir</th><th>Catatan</th></tr>
							</thead>
							<tbody>
								<?php foreach ($sync_health as $row): ?>
									<tr>
										<td class="fw-semibold"><?= html_escape($row['label']); ?></td>
										<td><span class="badge <?= $status_badge($row['status']); ?>"><?= html_escape($row['status']); ?></span></td>
										<td><?= html_escape($row['finished_at'] ?: '-'); ?></td>
										<td class="text-secondary"><?= html_escape($short_text($row['message'] ?? '-', 92)); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>

			<div class="col-lg-5">
				<div class="card admin-card dashboard-panel">
					<div class="card-header">
						<div>
							<h2 class="card-title">Data Sistem</h2>
							<div class="text-secondary small">Fondasi RBAC dan audit aplikasi.</div>
						</div>
					</div>
					<div class="dashboard-system-grid">
						<?php foreach (($stats['app'] ?? []) as $item): ?>
							<div class="dashboard-system-item">
								<span><?= html_escape($item['label']); ?></span>
								<strong><?= number_format((int) $item['value'], 0, ',', '.'); ?></strong>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
