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
			<?= form_open('reports/visits', ['method' => 'get', 'class' => 'visit-report-filter', 'data-mode' => $period['mode']]); ?>
				<div class="visit-filter-field">
					<label class="form-label" for="report-mode">Mode</label>
					<select class="form-select" name="mode" id="report-mode">
						<option value="month" <?= $period['mode'] === 'month' ? 'selected' : ''; ?>>Bulanan</option>
						<option value="year" <?= $period['mode'] === 'year' ? 'selected' : ''; ?>>Tahunan</option>
						<option value="day" <?= $period['mode'] === 'day' ? 'selected' : ''; ?>>Harian</option>
						<option value="custom" <?= $period['mode'] === 'custom' ? 'selected' : ''; ?>>Custom</option>
					</select>
				</div>
				<div class="visit-filter-field report-field report-year" <?= $period['mode'] !== 'year' ? 'hidden' : ''; ?>>
					<label class="form-label" for="report-year">Tahun</label>
					<input type="number" class="form-control" id="report-year" name="year" value="<?= (int) $period['year']; ?>" min="2000" max="2100">
				</div>
				<div class="visit-filter-field report-field report-month" <?= $period['mode'] !== 'month' ? 'hidden' : ''; ?>>
					<label class="form-label" for="report-month">Bulan</label>
					<input type="month" class="form-control" id="report-month" name="month" value="<?= html_escape($period['month']); ?>">
				</div>
				<div class="visit-filter-field report-field report-day" <?= $period['mode'] !== 'day' ? 'hidden' : ''; ?>>
					<label class="form-label" for="report-day">Tanggal</label>
					<input type="date" class="form-control" id="report-day" name="day" value="<?= html_escape($period['day']); ?>">
				</div>
				<div class="visit-filter-field report-field report-custom" <?= $period['mode'] !== 'custom' ? 'hidden' : ''; ?>>
					<label class="form-label" for="report-from">Dari tanggal</label>
					<input type="date" class="form-control" id="report-from" name="date_from" value="<?= html_escape($period['date_from']); ?>">
				</div>
				<div class="visit-filter-field report-field report-custom" <?= $period['mode'] !== 'custom' ? 'hidden' : ''; ?>>
					<label class="form-label" for="report-to">Sampai tanggal</label>
					<input type="date" class="form-control" id="report-to" name="date_to" value="<?= html_escape($period['date_to']); ?>">
				</div>
				<div class="visit-filter-field visit-filter-scope">
					<label class="form-label" for="report-scope">Jenis Kunjungan</label>
					<select class="form-select" id="report-scope" name="visit_scope" aria-describedby="report-scope-help">
						<option value="all" <?= $visit_scope === 'all' ? 'selected' : ''; ?>>Semua kunjungan</option>
						<option value="offline" <?= $visit_scope === 'offline' ? 'selected' : ''; ?>>Offline / kunjungan fisik</option>
						<option value="online" <?= $visit_scope === 'online' ? 'selected' : ''; ?>>Online / layanan digital</option>
					</select>
				</div>
				<div class="visit-filter-actions">
					<button type="submit" class="btn btn-primary w-100"><i class="ti ti-chart-line me-1"></i>Tampilkan</button>
				</div>
			<?= form_close(); ?>
			<div class="text-secondary small mt-3" id="report-scope-help">Offline: buku tamu dan check-in fisik. Online: dashboard member dan akses buku digital.</div>
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

		<div class="card admin-card report-card mb-3" id="visit-purpose-report">
			<div class="card-header d-flex flex-wrap gap-2">
				<div class="me-auto"><h2 class="card-title">Kunjungan Berdasarkan Tujuan</h2><div class="text-secondary small">Mengikuti tujuan yang dicatat di buku tamu, periode, dan jenis kunjungan yang dipilih.</div></div>
				<a class="btn btn-outline-primary btn-sm" href="<?=html_escape(base_url('reports/visits?'.http_build_query(array_merge((array)$this->input->get(),['tab'=>'format','row_variable'=>'purpose','column_variable'=>'none','split_variable'=>'none','report_title'=>'Kunjungan Berdasarkan Tujuan']))));?>"><i class="ti ti-table me-1" aria-hidden="true"></i>Format per tujuan</a>
			</div>
			<div class="table-responsive"><table class="table table-vcenter card-table">
				<thead><tr><th>Tujuan kunjungan</th><th class="text-end">Orang</th><th class="text-end">Entri</th><th class="text-end">Persentase orang</th></tr></thead>
				<tbody><?php foreach ($purpose_breakdown as $row): $share=$summary['people']>0?(int)$row['people']/$summary['people']*100:0; ?>
				<tr><td><?=html_escape($row['label']);?></td><td class="text-end fw-bold"><?=number_format((int)$row['people'],0,',','.');?></td><td class="text-end"><?=number_format((int)$row['entries'],0,',','.');?></td><td class="text-end"><?=number_format($share,1,',','.');?>%</td></tr>
				<?php endforeach; ?><?php if (!$purpose_breakdown): ?><tr><td colspan="4" class="text-center text-secondary py-4">Belum ada kunjungan pada periode dan jenis kunjungan ini.</td></tr><?php endif; ?></tbody>
				<?php if ($purpose_breakdown): ?><tfoot><tr><th>Total</th><th class="text-end"><?=number_format((int)$summary['people'],0,',','.');?></th><th class="text-end"><?=number_format((int)$summary['entries'],0,',','.');?></th><th class="text-end"><?=$summary['people']>0?'100,0%':'0,0%';?></th></tr></tfoot><?php endif; ?>
			</table></div>
			<div class="card-footer text-secondary small">Orang menghitung jumlah peserta, termasuk rombongan; entri menghitung baris buku tamu. Tujuan kosong ditampilkan sebagai “Belum diisi”. Tujuan data lama memakai referensi INLIS bila tersedia. Akses dashboard member dan baca buku digital dikelompokkan sebagai “Layanan digital”; tujuan asli tetap tersedia pada detail kunjungan.</div>
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

<style>
.visit-report-filter { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); align-items: end; gap: 1rem; }
.visit-report-filter > div { min-width: 0; }
.visit-report-filter .form-label { margin-bottom: .5rem; }
.visit-report-filter .form-control, .visit-report-filter .form-select, .visit-report-filter .btn { width: 100%; min-width: 0; height: 44px; }
.visit-report-filter .report-field[hidden] { display: none !important; }
.visit-report-filter[data-mode="custom"] .visit-filter-actions { grid-column: 1 / -1; }
@media (min-width: 1200px) {
	.visit-report-filter { grid-template-columns: minmax(120px, 1fr) minmax(150px, 1fr) minmax(230px, 1.4fr) auto; }
	.visit-report-filter[data-mode="custom"] { grid-template-columns: minmax(120px, 1fr) minmax(150px, 1fr) minmax(150px, 1fr) minmax(230px, 1.4fr) auto; }
	.visit-report-filter[data-mode="custom"] .visit-filter-actions { grid-column: auto; }
	.visit-report-filter .btn { min-width: 132px; }
}
@media (max-width: 575.98px) {
	.visit-report-filter { grid-template-columns: minmax(0, 1fr); gap: .875rem; }
}
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.9/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
	var mode = document.getElementById('report-mode');
	function syncFields() {
		var value = mode ? mode.value : 'month';
		mode.form.dataset.mode = value;
		mode.form.querySelectorAll('.report-field').forEach(function (field) { field.hidden = !field.classList.contains('report-' + value); });
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
