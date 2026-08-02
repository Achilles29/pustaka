<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$text = function ($value, $fallback = '-') {
	$value = trim((string) $value);
	return $value === '' ? $fallback : $value;
};
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= html_escape($title); ?></title>
	<style>
		@page { margin: 16mm; size: A4; }
		* { box-sizing: border-box; }
		body { color: #061a40; font-family: Arial, sans-serif; font-size: 12px; margin: 0; }
		h1, h2, h3, p { margin: 0; }
		.report-head { align-items: center; border-bottom: 3px solid #0057a8; display: flex; gap: 14px; padding-bottom: 12px; }
		.report-head img { height: 54px; width: 54px; object-fit: contain; }
		.report-head h1 { font-size: 22px; line-height: 1.15; }
		.report-head p { color: #53677e; margin-top: 4px; }
		.meta { display: grid; gap: 6px; grid-template-columns: repeat(3, 1fr); margin: 14px 0; }
		.meta div, .kpi div { border: 1px solid #cbd8e6; border-radius: 8px; padding: 8px; }
		.meta span, .kpi span { color: #53677e; display: block; font-size: 10px; font-weight: 700; text-transform: uppercase; }
		.meta strong, .kpi strong { display: block; font-size: 15px; margin-top: 3px; }
		.kpi { display: grid; gap: 8px; grid-template-columns: repeat(4, 1fr); margin-bottom: 14px; }
		.kpi strong { color: #0057a8; font-size: 22px; }
		.grid { display: grid; gap: 10px; grid-template-columns: repeat(3, 1fr); margin-bottom: 14px; }
		h2 { font-size: 14px; margin: 0 0 7px; }
		table { border-collapse: collapse; width: 100%; }
		th, td { border: 1px solid #d6e1ec; padding: 6px; text-align: left; vertical-align: top; }
		th { background: #eaf3fb; color: #061a40; font-size: 10px; text-transform: uppercase; }
		.section { break-inside: avoid; margin-bottom: 14px; }
		.number { text-align: right; }
		.print-actions { display: flex; gap: 8px; justify-content: flex-end; margin: 12px 0; }
		.print-actions button { background: #0057a8; border: 0; border-radius: 7px; color: #fff; cursor: pointer; font-weight: 700; padding: 8px 12px; }
		@media print {
			.print-actions { display: none; }
			.section { page-break-inside: avoid; }
		}
	</style>
</head>
<body>
	<div class="print-actions">
		<button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
	</div>
	<header class="report-head">
		<img src="<?= base_url('img/logo-small.jpeg'); ?>" alt="Logo Kabupaten Rembang">
		<div>
			<h1>Laporan Kunjungan Pustaka Digital Rembang</h1>
			<p><?= html_escape($period['label']); ?> | <?= html_escape($period['date_from']); ?> sampai <?= html_escape($period['date_to']); ?></p>
		</div>
	</header>

	<section class="meta">
		<div><span>Dibuat</span><strong><?= html_escape($generated_at); ?></strong></div>
		<div><span>Mode</span><strong><?= html_escape(ucfirst($period['mode'])); ?></strong></div>
		<div><span>Sumber</span><strong>member_visits</strong></div>
	</section>

	<section class="kpi">
		<div><span>Total Orang</span><strong><?= number_format((int) $summary['people'], 0, ',', '.'); ?></strong></div>
		<div><span>Entri</span><strong><?= number_format((int) $summary['entries'], 0, ',', '.'); ?></strong></div>
		<div><span>Member</span><strong><?= number_format((int) $summary['members'], 0, ',', '.'); ?></strong></div>
		<div><span>Rombongan</span><strong><?= number_format((int) $summary['groups'], 0, ',', '.'); ?></strong></div>
	</section>

	<section class="grid">
		<div class="section">
			<h2>Kanal</h2>
			<table>
				<thead><tr><th>Kanal</th><th>Orang</th><th>Entri</th></tr></thead>
				<tbody>
					<?php foreach ($channel_breakdown as $row): ?><tr><td><?= html_escape($channel_labels[$row['label']] ?? $row['label']); ?></td><td class="number"><?= number_format((int) $row['people'], 0, ',', '.'); ?></td><td class="number"><?= number_format((int) $row['entries'], 0, ',', '.'); ?></td></tr><?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<div class="section">
			<h2>Asal Layanan</h2>
			<table>
				<thead><tr><th>Asal</th><th>Orang</th><th>Entri</th></tr></thead>
				<tbody>
					<?php foreach ($origin_breakdown as $row): ?><tr><td><?= html_escape($origin_labels[$row['label']] ?? $row['label']); ?></td><td class="number"><?= number_format((int) $row['people'], 0, ',', '.'); ?></td><td class="number"><?= number_format((int) $row['entries'], 0, ',', '.'); ?></td></tr><?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<div class="section">
			<h2>Metode</h2>
			<table>
				<thead><tr><th>Metode</th><th>Orang</th><th>Entri</th></tr></thead>
				<tbody>
					<?php foreach ($method_breakdown as $row): ?><tr><td><?= html_escape($method_labels[$row['label']] ?? $row['label']); ?></td><td class="number"><?= number_format((int) $row['people'], 0, ',', '.'); ?></td><td class="number"><?= number_format((int) $row['entries'], 0, ',', '.'); ?></td></tr><?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</section>

	<section class="section">
		<h2>Tren Kunjungan</h2>
		<table>
			<thead><tr><th>Periode</th><th>Orang</th><th>Entri</th></tr></thead>
			<tbody>
				<?php foreach ($trend as $row): ?><tr><td><?= html_escape($row['period']); ?></td><td class="number"><?= number_format((int) $row['people'], 0, ',', '.'); ?></td><td class="number"><?= number_format((int) $row['entries'], 0, ',', '.'); ?></td></tr><?php endforeach; ?>
			</tbody>
		</table>
	</section>

	<section class="section">
		<h2>Kunjungan Terbaru</h2>
		<table>
			<thead><tr><th>Waktu</th><th>Pengunjung</th><th>Kanal</th><th>Lokasi/Tujuan</th><th>Jumlah</th></tr></thead>
			<tbody>
				<?php foreach ($recent_visits as $visit): ?>
					<tr>
						<td><?= html_escape($text($visit['visited_at'])); ?></td>
						<td><?= html_escape($text($visit['member_name'] ?: $visit['visitor_name'])); ?><br><?= html_escape($text($visit['member_no'] ?: $visit['source_member_no'] ?: $visit['visitor_no'])); ?></td>
						<td><?= html_escape($channel_labels[$visit['visit_channel'] ?? 'unknown'] ?? $text($visit['visit_channel'] ?? 'unknown')); ?></td>
						<td><?= html_escape($text($visit['location_label'])); ?><br><?= html_escape($text($visit['purpose_label'])); ?></td>
						<td class="number"><?= number_format((int) ($visit['visitor_count'] ?? 1), 0, ',', '.'); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</section>
</body>
</html>
