<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$text = function ($value, $fallback = '-') {
	$value = trim((string) $value);
	return $value === '' ? $fallback : $value;
};
$cell = function ($value) {
	return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>
<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<style>
		body { font-family: Arial, sans-serif; }
		table { border-collapse: collapse; width: 100%; }
		th, td { border: 1px solid #999; padding: 6px; vertical-align: top; }
		th { background: #d9eaf7; font-weight: bold; }
		.section { background: #0057a8; color: #fff; font-weight: bold; }
		.number { text-align: right; }
	</style>
</head>
<body>
	<table>
		<tr><td colspan="10" class="section">Laporan Kunjungan Pustaka Digital Rembang</td></tr>
		<tr><td>Periode</td><td colspan="9"><?= $cell($period['label']); ?></td></tr>
		<tr><td>Range</td><td colspan="9"><?= $cell($period['date_from'] . ' s.d. ' . $period['date_to']); ?></td></tr>
		<tr><td>Dibuat</td><td colspan="9"><?= $cell($generated_at); ?></td></tr>
		<tr><td colspan="10"></td></tr>
		<tr><td class="section" colspan="10">Ringkasan</td></tr>
		<tr><th>Total Orang</th><th>Entri Kunjungan</th><th>Member</th><th>Non-Member</th><th>Rombongan</th><th colspan="5"></th></tr>
		<tr>
			<td class="number"><?= (int) $summary['people']; ?></td>
			<td class="number"><?= (int) $summary['entries']; ?></td>
			<td class="number"><?= (int) $summary['members']; ?></td>
			<td class="number"><?= (int) $summary['non_members']; ?></td>
			<td class="number"><?= (int) $summary['groups']; ?></td>
			<td colspan="5"></td>
		</tr>
		<tr><td colspan="10"></td></tr>
		<tr><td class="section" colspan="10">Kunjungan Berdasarkan Tujuan</td></tr>
		<tr><th colspan="6">Tujuan kunjungan</th><th>Orang</th><th>Entri</th><th colspan="2">Persentase orang</th></tr>
		<?php foreach ($purpose_breakdown as $row): $purpose=(string)$row['label']; if(preg_match('/^[\x00-\x20]*[=+@-]/u',$purpose))$purpose="'".$purpose; ?>
		<tr><td colspan="6"><?=$cell($purpose);?></td><td class="number"><?=(int)$row['people'];?></td><td class="number"><?=(int)$row['entries'];?></td><td class="number" colspan="2"><?=number_format($summary['people']>0?(int)$row['people']/$summary['people']*100:0,1,'.','');?>%</td></tr>
		<?php endforeach; ?>
		<?php if (!$purpose_breakdown): ?><tr><td colspan="10">Belum ada kunjungan pada periode dan jenis kunjungan ini.</td></tr><?php endif; ?>
		<tr><th colspan="6">Total</th><th><?=(int)$summary['people'];?></th><th><?=(int)$summary['entries'];?></th><th colspan="2"><?=$summary['people']>0?'100.0%':'0.0%';?></th></tr>
		<tr><td colspan="10">Orang termasuk peserta rombongan; entri adalah baris buku tamu. Tujuan kosong: Belum diisi. Akses dashboard member dan baca buku digital dikelompokkan sebagai “Layanan digital”; detail kunjungan tetap menampilkan tujuan asli.</td></tr>
		<tr><td colspan="10"></td></tr>
		<tr><td class="section" colspan="10">Breakdown Kanal</td></tr>
		<tr><th>Kanal</th><th>Orang</th><th>Entri</th><th colspan="7"></th></tr>
		<?php foreach ($channel_breakdown as $row): ?>
			<tr><td><?= $cell($channel_labels[$row['label']] ?? $row['label']); ?></td><td class="number"><?= (int) $row['people']; ?></td><td class="number"><?= (int) $row['entries']; ?></td><td colspan="7"></td></tr>
		<?php endforeach; ?>
		<tr><td colspan="10"></td></tr>
		<tr><td class="section" colspan="10">Breakdown Asal Layanan</td></tr>
		<tr><th>Asal</th><th>Orang</th><th>Entri</th><th colspan="7"></th></tr>
		<?php foreach ($origin_breakdown as $row): ?>
			<tr><td><?= $cell($origin_labels[$row['label']] ?? $row['label']); ?></td><td class="number"><?= (int) $row['people']; ?></td><td class="number"><?= (int) $row['entries']; ?></td><td colspan="7"></td></tr>
		<?php endforeach; ?>
		<tr><td colspan="10"></td></tr>
		<tr><td class="section" colspan="10">Tren</td></tr>
		<tr><th>Periode</th><th>Orang</th><th>Entri</th><th colspan="7"></th></tr>
		<?php foreach ($trend as $row): ?>
			<tr><td><?= $cell($row['period']); ?></td><td class="number"><?= (int) $row['people']; ?></td><td class="number"><?= (int) $row['entries']; ?></td><td colspan="7"></td></tr>
		<?php endforeach; ?>
		<tr><td colspan="10"></td></tr>
		<tr><td class="section" colspan="10">Detail Kunjungan</td></tr>
		<tr>
			<th>Waktu</th>
			<th>Pengunjung</th>
			<th>No Anggota</th>
			<th>Kanal</th>
			<th>Asal</th>
			<th>Metode</th>
			<th>Lokasi</th>
			<th>Tujuan</th>
			<th>Jumlah</th>
			<th>Keterangan</th>
		</tr>
		<?php foreach ($rows as $visit): ?>
			<tr>
				<td><?= $cell($text($visit['visited_at'])); ?></td>
				<td><?= $cell($text($visit['member_name'] ?: $visit['visitor_name'])); ?></td>
				<td><?= $cell($text($visit['member_no'] ?: $visit['source_member_no'] ?: $visit['visitor_no'])); ?></td>
				<td><?= $cell($channel_labels[$visit['visit_channel'] ?? 'unknown'] ?? $text($visit['visit_channel'] ?? 'unknown')); ?></td>
				<td><?= $cell($origin_labels[$visit['visit_origin'] ?? 'unknown'] ?? $text($visit['visit_origin'] ?? 'unknown')); ?></td>
				<td><?= $cell($method_labels[$visit['checkin_method'] ?? 'unknown'] ?? $text($visit['checkin_method'] ?? 'unknown')); ?></td>
				<td><?= $cell($text($visit['location_label'] ?: $visit['location_id'])); ?></td>
				<td><?= $cell($text($visit['purpose_label'] ?: $visit['purpose_id'])); ?></td>
				<td class="number"><?= (int) ($visit['visitor_count'] ?? 1); ?></td>
				<td><?= $cell($text($visit['information'] ?: $visit['description'] ?: $visit['address'])); ?></td>
			</tr>
		<?php endforeach; ?>
	</table>
</body>
</html>
