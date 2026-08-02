<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$text = function ($value, $fallback = '-') {
	$value = trim((string) $value);
	return $value === '' ? $fallback : $value;
};
$chart_json = json_encode($chart_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$export_query = http_build_query($this->input->get());
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Laporan & Analitik</div>
				<h1 class="page-title">Laporan Kunjungan</h1>
			</div>
			<div class="col-auto">
				<div class="btn-list">
					<a href="<?= base_url('reports/visits/print' . ($export_query ? '?' . $export_query : '')); ?>" class="btn btn-outline-primary" target="_blank">
						<i class="ti ti-printer me-1"></i>Cetak / PDF
					</a>
					<a href="<?= base_url('reports/visits/excel' . ($export_query ? '?' . $export_query : '')); ?>" class="btn btn-primary">
						<i class="ti ti-file-spreadsheet me-1"></i>Excel
					</a>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="page-body">
	<div class="container-xl">
		<div class="report-filter-panel">
			<?= form_open('reports/visits', ['method' => 'get', 'class' => 'row g-2 align-items-end']); ?>
				<div class="col-md-2">
					<label class="form-label">Mode</label>
					<select class="form-select" name="mode" id="report-mode">
						<option value="month" <?= $period['mode'] === 'month' ? 'selected' : ''; ?>>Bulanan</option>
						<option value="year" <?= $period['mode'] === 'year' ? 'selected' : ''; ?>>Tahunan</option>
						<option value="day" <?= $period['mode'] === 'day' ? 'selected' : ''; ?>>Harian</option>
						<option value="custom" <?= $period['mode'] === 'custom' ? 'selected' : ''; ?>>Custom</option>
					</select>
				</div>
				<div class="col-md-2 report-field report-year">
					<label class="form-label">Tahun</label>
					<input type="number" class="form-control" name="year" value="<?= (int) $period['year']; ?>" min="2000" max="2100">
				</div>
				<div class="col-md-2 report-field report-month">
					<label class="form-label">Bulan</label>
					<input type="month" class="form-control" name="month" value="<?= html_escape($period['month']); ?>">
				</div>
				<div class="col-md-2 report-field report-day">
					<label class="form-label">Hari</label>
					<input type="date" class="form-control" name="day" value="<?= html_escape($period['day']); ?>">
				</div>
				<div class="col-md-2 report-field report-custom">
					<label class="form-label">Dari</label>
					<input type="date" class="form-control" name="date_from" value="<?= html_escape($period['date_from']); ?>">
				</div>
				<div class="col-md-2 report-field report-custom">
					<label class="form-label">Sampai</label>
					<input type="date" class="form-control" name="date_to" value="<?= html_escape($period['date_to']); ?>">
				</div>
				<div class="col-md-2">
					<button type="submit" class="btn btn-primary w-100"><i class="ti ti-chart-line me-1"></i>Tampilkan</button>
				</div>
			<?= form_close(); ?>
		</div>

		<div class="report-period-strip">
			<div>
				<div class="section-kicker">Periode aktif</div>
				<strong><?= html_escape($period['label']); ?></strong>
				<span><?= html_escape($period['date_from']); ?> sampai <?= html_escape($period['date_to']); ?></span>
			</div>
			<div class="btn-list">
				<a href="<?= base_url('transactions?tab=visits&date_from=' . rawurlencode($period['date_from']) . '&date_to=' . rawurlencode($period['date_to'])); ?>" class="btn btn-outline-primary">
					<i class="ti ti-table me-1"></i>Data Mentah
				</a>
				<a href="<?= base_url('reports/visits/print' . ($export_query ? '?' . $export_query : '')); ?>" class="btn btn-outline-primary" target="_blank">
					<i class="ti ti-file-type-pdf me-1"></i>PDF
				</a>
			</div>
		</div>

		<div class="report-kpi-grid">
			<div class="report-kpi"><span>Total Orang</span><strong><?= number_format((int) $summary['people'], 0, ',', '.'); ?></strong><small>Termasuk rombongan</small></div>
			<div class="report-kpi"><span>Entri Kunjungan</span><strong><?= number_format((int) $summary['entries'], 0, ',', '.'); ?></strong><small>Baris buku tamu</small></div>
			<div class="report-kpi"><span>Member</span><strong><?= number_format((int) $summary['members'], 0, ',', '.'); ?></strong><small>Terhubung akun/member</small></div>
			<div class="report-kpi"><span>Rombongan</span><strong><?= number_format((int) $summary['groups'], 0, ',', '.'); ?></strong><small>Kunjungan kolektif</small></div>
		</div>

		<div class="row g-3">
			<div class="col-xl-8">
				<div class="card admin-card report-card">
					<div class="card-header"><h2 class="card-title">Tren Kunjungan</h2></div>
					<div class="card-body"><canvas id="visit-trend-chart" height="118"></canvas></div>
				</div>
			</div>
			<div class="col-xl-4">
				<div class="card admin-card report-card">
					<div class="card-header"><h2 class="card-title">Komposisi Kanal</h2></div>
					<div class="card-body"><canvas id="visit-channel-chart" height="240"></canvas></div>
				</div>
			</div>
		</div>

		<div class="row g-3 mt-0">
			<div class="col-lg-4">
				<div class="card admin-card report-card">
					<div class="card-header"><h2 class="card-title">Kanal Kunjungan</h2></div>
					<div class="table-responsive">
						<table class="table table-vcenter card-table">
							<thead><tr><th>Kanal</th><th>Orang</th><th>Entri</th></tr></thead>
							<tbody>
								<?php foreach ($channel_breakdown as $row): ?>
									<tr><td><?= html_escape($channel_labels[$row['label']] ?? $row['label']); ?></td><td class="fw-bold"><?= number_format((int) $row['people'], 0, ',', '.'); ?></td><td><?= number_format((int) $row['entries'], 0, ',', '.'); ?></td></tr>
								<?php endforeach; ?>
								<?php if (empty($channel_breakdown)): ?><tr><td colspan="3" class="text-center text-secondary py-4">Belum ada data.</td></tr><?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="col-lg-4">
				<div class="card admin-card report-card">
					<div class="card-header"><h2 class="card-title">Asal Layanan</h2></div>
					<div class="table-responsive">
						<table class="table table-vcenter card-table">
							<thead><tr><th>Asal</th><th>Orang</th><th>Entri</th></tr></thead>
							<tbody>
								<?php foreach ($origin_breakdown as $row): ?>
									<tr><td><?= html_escape($origin_labels[$row['label']] ?? $row['label']); ?></td><td class="fw-bold"><?= number_format((int) $row['people'], 0, ',', '.'); ?></td><td><?= number_format((int) $row['entries'], 0, ',', '.'); ?></td></tr>
								<?php endforeach; ?>
								<?php if (empty($origin_breakdown)): ?><tr><td colspan="3" class="text-center text-secondary py-4">Belum ada data.</td></tr><?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="col-lg-4">
				<div class="card admin-card report-card">
					<div class="card-header"><h2 class="card-title">Metode Check-in</h2></div>
					<div class="table-responsive">
						<table class="table table-vcenter card-table">
							<thead><tr><th>Metode</th><th>Orang</th><th>Entri</th></tr></thead>
							<tbody>
								<?php foreach ($method_breakdown as $row): ?>
									<tr><td><?= html_escape($method_labels[$row['label']] ?? $row['label']); ?></td><td class="fw-bold"><?= number_format((int) $row['people'], 0, ',', '.'); ?></td><td><?= number_format((int) $row['entries'], 0, ',', '.'); ?></td></tr>
								<?php endforeach; ?>
								<?php if (empty($method_breakdown)): ?><tr><td colspan="3" class="text-center text-secondary py-4">Belum ada data.</td></tr><?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>

		<div class="card admin-card data-workspace mt-3">
			<div class="card-header"><h2 class="card-title">Kunjungan Terbaru</h2></div>
			<div class="table-responsive">
				<table class="table table-vcenter card-table">
					<thead><tr><th>Waktu</th><th>Pengunjung</th><th>Kanal</th><th>Lokasi/Tujuan</th><th>Jumlah</th></tr></thead>
					<tbody>
						<?php foreach ($recent_visits as $visit): ?>
							<tr>
								<td data-label="Waktu"><?= html_escape($text($visit['visited_at'])); ?></td>
								<td data-label="Pengunjung">
									<div class="fw-semibold"><?= html_escape($text($visit['member_name'] ?: $visit['visitor_name'])); ?></div>
									<div class="text-secondary small"><?= html_escape($text($visit['member_no'] ?: $visit['source_member_no'] ?: $visit['visitor_no'])); ?></div>
								</td>
								<td data-label="Kanal"><span class="badge bg-blue-lt"><?= html_escape($channel_labels[$visit['visit_channel'] ?? 'unknown'] ?? $text($visit['visit_channel'] ?? 'unknown')); ?></span></td>
								<td data-label="Lokasi/Tujuan">
									<div><?= html_escape($text($visit['location_label'])); ?></div>
									<div class="text-secondary small"><?= html_escape($text($visit['purpose_label'])); ?></div>
								</td>
								<td data-label="Jumlah" class="fw-bold"><?= number_format((int) ($visit['visitor_count'] ?? 1), 0, ',', '.'); ?></td>
							</tr>
						<?php endforeach; ?>
						<?php if (empty($recent_visits)): ?><tr><td colspan="5" class="text-center text-secondary py-4">Belum ada kunjungan pada periode ini.</td></tr><?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.9/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
	var mode = document.getElementById('report-mode');
	function syncFields() {
		var value = mode ? mode.value : 'month';
		document.querySelectorAll('.report-field').forEach(function (field) { field.style.display = 'none'; });
		document.querySelectorAll('.report-' + value).forEach(function (field) { field.style.display = ''; });
	}
	if (mode) {
		mode.addEventListener('change', syncFields);
		syncFields();
	}

	var payload = <?= $chart_json ?: '{}'; ?>;
	var colors = ['#0057a8', '#061a40', '#d6a419', '#0b8f6a', '#6b7a90', '#2f80ed', '#9b6b00'];
	if (window.Chart && document.getElementById('visit-trend-chart')) {
		new Chart(document.getElementById('visit-trend-chart'), {
			type: 'line',
			data: {
				labels: payload.trend.labels,
				datasets: [
					{ label: 'Orang', data: payload.trend.people, borderColor: '#0057a8', backgroundColor: 'rgba(0,87,168,.12)', fill: true, tension: .32 },
					{ label: 'Entri', data: payload.trend.entries, borderColor: '#d6a419', backgroundColor: 'rgba(214,164,25,.14)', fill: false, tension: .32 }
				]
			},
			options: { responsive: true, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
		});
	}
	if (window.Chart && document.getElementById('visit-channel-chart')) {
		new Chart(document.getElementById('visit-channel-chart'), {
			type: 'doughnut',
			data: { labels: payload.channels.labels, datasets: [{ data: payload.channels.people, backgroundColor: colors, borderColor: '#fff', borderWidth: 3 }] },
			options: { responsive: true, plugins: { legend: { position: 'bottom' } }, cutout: '62%' }
		});
	}
});
</script>
