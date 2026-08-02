<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$metrics = [
	['key' => 'member_visits', 'label' => 'Kunjungan', 'icon' => 'ti ti-door-enter'],
	['key' => 'access_rules', 'label' => 'Hak Layanan', 'icon' => 'ti ti-list-check'],
	['key' => 'loan_transactions', 'label' => 'Peminjaman', 'icon' => 'ti ti-receipt-2'],
	['key' => 'loan_items', 'label' => 'Item Koleksi', 'icon' => 'ti ti-books'],
	['key' => 'digital_sessions', 'label' => 'Akses Digital', 'icon' => 'ti ti-device-tablet'],
	['key' => 'external_digital_sessions', 'label' => 'Luar Lokasi', 'icon' => 'ti ti-world'],
];
$tabs = [
	'visits' => ['label' => 'Buku Tamu', 'icon' => 'ti ti-door-enter', 'stat' => 'member_visits'],
	'access' => ['label' => 'Hak Layanan', 'icon' => 'ti ti-list-check', 'stat' => 'access_rules'],
	'loans' => ['label' => 'Peminjaman', 'icon' => 'ti ti-receipt-2', 'stat' => 'loan_transactions'],
	'items' => ['label' => 'Item Koleksi', 'icon' => 'ti ti-books', 'stat' => 'loan_items'],
];
$tab_descriptions = [
	'visits' => 'Pantau aktivitas kunjungan tamu dan anggota dari buku tamu INLISLite.',
	'access' => 'Lihat hak layanan anggota berdasarkan kategori dan lokasi koleksi.',
	'loans' => 'Ringkasan header peminjaman untuk membaca volume layanan harian.',
	'items' => 'Rincian item buku yang dipinjam, status, jatuh tempo, dan barcode.',
];
$rule_type_labels = [
	'category' => 'Kategori Koleksi',
	'location' => 'Lokasi Koleksi',
];
$visit_channel_labels = [
	'inlislite_guestbook' => 'Buku Tamu INLISLite',
	'library_guestbook' => 'Buku Tamu Perpus',
	'member_dashboard' => 'Online Dashboard',
	'digital_access' => 'Baca Digital',
	'reading_point' => 'Pojok Baca',
	'service_monitor' => 'Monitor Pelayanan',
	'qr_checkin' => 'Scan QR Check-in',
];
$visit_origin_labels = [
	'library' => 'Perpustakaan',
	'reading_point' => 'Pojok Baca',
	'digital_external' => 'Online Luar Lokasi',
	'digital_internal' => 'Internal',
	'legacy' => 'Data Lama',
];
$query_base = $_GET;
unset($query_base['page']);
$tab_url = function ($tab) use ($query_base) {
	return base_url('transactions?' . http_build_query(array_merge($query_base, ['tab' => $tab, 'page' => 1])));
};
$page_url = function ($page) use ($query_base, $active_tab) {
	return base_url('transactions?' . http_build_query(array_merge($query_base, ['tab' => $active_tab, 'page' => $page])));
};
$text = function ($value, $fallback = '-') {
	$value = trim((string) $value);
	return $value === '' ? $fallback : $value;
};
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Layanan Harian</div>
				<h1 class="page-title">Pusat Aktivitas Layanan</h1>
			</div>
		</div>
	</div>
</div>

<div class="page-body">
	<div class="container-xl">
		<?php if (! empty($admin_inbox['total'])): ?>
			<div class="admin-inbox-banner has-pending">
				<i class="ti ti-inbox"></i>
				<div>
					<strong><?= number_format((int) $admin_inbox['total'], 0, ',', '.'); ?> antrean layanan menunggu</strong>
					<span>Cek kotak masuk untuk pendaftaran online, request buku, dan perpanjangan membership sebelum memproses layanan harian.</span>
				</div>
			</div>
		<?php endif; ?>

		<div class="ops-summary-strip">
			<div>
				<div class="section-kicker">Live dari INLISLite</div>
				<h2><?= html_escape($tabs[$active_tab]['label']); ?></h2>
				<p><?= html_escape($tab_descriptions[$active_tab]); ?></p>
			</div>
			<a href="<?= base_url('transactions/sync'); ?>" class="btn btn-primary">
				<i class="ti ti-refresh me-1"></i>Sinkronisasi
			</a>
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
					<h2 class="card-title">Aktivitas Tersinkron</h2>
					<div class="text-secondary small">Gunakan tab untuk berpindah dari buku tamu, hak layanan, peminjaman, sampai rincian item.</div>
				</div>
				<ul class="nav nav-tabs card-header-tabs workspace-tabs" role="tablist">
					<?php foreach ($tabs as $key => $tab): ?>
						<li class="nav-item" role="presentation">
							<a href="<?= $tab_url($key); ?>" class="nav-link <?= $active_tab === $key ? 'active' : ''; ?>">
								<i class="<?= html_escape($tab['icon']); ?> me-1"></i><?= html_escape($tab['label']); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<div class="card-body workspace-filter">
				<?= form_open('transactions', ['method' => 'get', 'class' => 'row g-2 align-items-end service-filter-form']); ?>
					<input type="hidden" name="tab" value="<?= html_escape($active_tab); ?>">
					<div class="col-md-3">
						<label class="form-label">Cari</label>
						<input type="text" class="form-control" name="q" value="<?= html_escape($filters['q'] ?? ''); ?>" placeholder="Nama, nomor, barcode, judul">
					</div>
					<div class="col-md-2">
						<label class="form-label">Dari</label>
						<input type="date" class="form-control" name="date_from" value="<?= html_escape($filters['date_from'] ?? ''); ?>">
					</div>
					<div class="col-md-2">
						<label class="form-label">Sampai</label>
						<input type="date" class="form-control" name="date_to" value="<?= html_escape($filters['date_to'] ?? ''); ?>">
					</div>
					<div class="col-md-2">
						<label class="form-label">Filter Tab</label>
						<?php if ($active_tab === 'visits'): ?>
							<select class="form-select" name="visit_channel">
								<option value="">Semua kanal</option>
								<?php foreach ($visit_channel_labels as $value => $label): ?>
									<option value="<?= $value; ?>" <?= ($filters['visit_channel'] ?? '') === $value ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
								<?php endforeach; ?>
							</select>
						<?php elseif ($active_tab === 'access'): ?>
							<select class="form-select" name="rule_type">
								<option value="">Semua hak</option>
								<?php foreach ($rule_type_labels as $value => $label): ?>
									<option value="<?= $value; ?>" <?= ($filters['rule_type'] ?? '') === $value ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
								<?php endforeach; ?>
							</select>
						<?php elseif ($active_tab === 'items'): ?>
							<input type="text" class="form-control" name="loan_status" value="<?= html_escape($filters['loan_status'] ?? ''); ?>" placeholder="Status pinjam">
						<?php else: ?>
							<input type="text" class="form-control" value="Semua" disabled>
						<?php endif; ?>
					</div>
					<div class="col-md-1">
						<label class="form-label">Baris</label>
						<select class="form-select" name="per_page">
							<?php foreach ([10, 25, 50, 100] as $limit): ?>
								<option value="<?= $limit; ?>" <?= (int) ($filters['per_page'] ?? 25) === $limit ? 'selected' : ''; ?>><?= $limit; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-2">
						<button type="submit" class="btn btn-outline-primary w-100">
							<i class="ti ti-filter me-1"></i>Filter
						</button>
					</div>
				<?= form_close(); ?>
			</div>

			<div class="table-responsive">
				<?php if ($active_tab === 'visits'): ?>
					<table class="table table-vcenter card-table">
						<thead><tr><th>Waktu</th><th>Pengunjung</th><th>Kanal</th><th>Lokasi</th><th>Tujuan</th><th>Keterangan</th></tr></thead>
						<tbody>
							<?php if (empty($visits)): ?><tr><td colspan="6" class="text-center text-secondary py-4">Belum ada data kunjungan sesuai filter.</td></tr><?php endif; ?>
							<?php foreach ($visits as $visit): ?>
								<tr>
									<td data-label="Waktu"><?= html_escape($text($visit['visited_at'])); ?></td>
									<td data-label="Pengunjung">
										<div class="fw-semibold"><?= html_escape($text($visit['member_name'] ?: $visit['visitor_name'])); ?></div>
										<div class="text-secondary small"><code><?= html_escape($text($visit['member_no'] ?: $visit['source_member_no'] ?: $visit['visitor_no'])); ?></code></div>
										<?php if ((int) ($visit['visitor_count'] ?? 1) > 1): ?>
											<div class="badge bg-yellow-lt mt-1"><?= number_format((int) $visit['visitor_count'], 0, ',', '.'); ?> orang</div>
										<?php endif; ?>
									</td>
									<td data-label="Kanal">
										<span class="badge bg-blue-lt"><?= html_escape($visit_channel_labels[$visit['visit_channel'] ?? ''] ?? $text($visit['visit_channel'] ?? 'legacy')); ?></span>
										<div class="text-secondary small mt-1"><?= html_escape($visit_origin_labels[$visit['visit_origin'] ?? ''] ?? $text($visit['visit_origin'] ?? 'legacy')); ?></div>
									</td>
									<td data-label="Lokasi">
										<div><?= html_escape($text($visit['location_label'] ?: $visit['location_id'])); ?></div>
										<div class="text-secondary small"><?= html_escape($text($visit['location_loan_label'] ?: $visit['location_loan_id'])); ?></div>
									</td>
									<td data-label="Tujuan"><?= html_escape($text($visit['purpose_label'] ?: $visit['purpose_id'])); ?></td>
									<td data-label="Keterangan"><?= html_escape($text($visit['information'] ?: $visit['description'] ?: $visit['address'])); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php elseif ($active_tab === 'access'): ?>
					<table class="table table-vcenter card-table">
						<thead><tr><th>Member</th><th>Tipe</th><th>Hak Layanan</th><th>Tabel Sumber</th></tr></thead>
						<tbody>
							<?php if (empty($access_rules)): ?><tr><td colspan="4" class="text-center text-secondary py-4">Belum ada hak pinjam sesuai filter.</td></tr><?php endif; ?>
							<?php foreach ($access_rules as $rule): ?>
								<tr>
									<td data-label="Member">
										<div class="fw-semibold"><?= html_escape($text($rule['member_name'], 'Member sumber #' . $rule['source_member_id'])); ?></div>
										<div class="text-secondary small"><code><?= html_escape($text($rule['member_no'] ?: $rule['source_member_id'])); ?></code></div>
									</td>
									<td data-label="Tipe"><span class="badge bg-blue-lt"><?= html_escape($rule_type_labels[$rule['rule_type']] ?? $rule['rule_type']); ?></span></td>
									<td data-label="Hak Layanan">
										<div><?= html_escape($text($rule['rule_label'])); ?></div>
										<div class="text-secondary small">ID sumber: <?= html_escape($text($rule['source_rule_id'])); ?></div>
									</td>
									<td data-label="Tabel Sumber"><code><?= html_escape($rule['source_table']); ?></code></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php elseif ($active_tab === 'loans'): ?>
					<table class="table table-vcenter card-table">
						<thead><tr><th>ID Layanan</th><th>Member</th><th>Lokasi</th><th>Jumlah</th><th>Waktu Sumber</th></tr></thead>
						<tbody>
							<?php if (empty($loans)): ?><tr><td colspan="5" class="text-center text-secondary py-4">Belum ada transaksi pinjam sesuai filter.</td></tr><?php endif; ?>
							<?php foreach ($loans as $loan): ?>
								<tr>
									<td data-label="ID Layanan"><code><?= html_escape($text($loan['source_id'])); ?></code></td>
									<td data-label="Member">
										<div class="fw-semibold"><?= html_escape($text($loan['member_name'], 'Member sumber #' . $loan['source_member_id'])); ?></div>
										<div class="text-secondary small"><code><?= html_escape($text($loan['member_no'] ?: $loan['source_member_id'])); ?></code></div>
									</td>
									<td data-label="Lokasi"><?= html_escape($text($loan['location_library_name'] ?: $loan['location_library_id'])); ?></td>
									<td data-label="Jumlah">
										<span class="badge bg-blue-lt"><?= number_format((int) $loan['loan_count'], 0, ',', '.'); ?> pinjam</span>
										<span class="badge bg-green-lt"><?= number_format((int) $loan['return_count'], 0, ',', '.'); ?> kembali</span>
										<span class="badge bg-red-lt"><?= number_format((int) $loan['late_count'], 0, ',', '.'); ?> telat</span>
									</td>
									<td data-label="Waktu Sumber"><?= html_escape($text($loan['source_created_at'])); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else: ?>
					<table class="table table-vcenter card-table">
						<thead><tr><th>Tanggal</th><th>Member</th><th>Buku</th><th>Status</th><th>Kembali</th></tr></thead>
						<tbody>
							<?php if (empty($loan_items)): ?><tr><td colspan="5" class="text-center text-secondary py-4">Belum ada detail item pinjam sesuai filter.</td></tr><?php endif; ?>
							<?php foreach ($loan_items as $item): ?>
								<tr>
									<td data-label="Tanggal"><?= html_escape($text($item['loan_date'])); ?></td>
									<td data-label="Member">
										<div class="fw-semibold"><?= html_escape($text($item['member_name'], 'Member sumber #' . $item['source_member_id'])); ?></div>
										<div class="text-secondary small"><code><?= html_escape($text($item['member_no'] ?: $item['source_member_id'])); ?></code></div>
									</td>
									<td data-label="Buku">
										<div><?= html_escape($text($item['title'], 'Koleksi sumber #' . $item['source_collection_id'])); ?></div>
										<div class="text-secondary small"><code><?= html_escape($text($item['barcode'])); ?></code> <?= html_escape($text($item['call_number'], '')); ?></div>
									</td>
									<td data-label="Status">
										<span class="badge bg-blue-lt"><?= html_escape($text($item['loan_status'])); ?></span>
										<div class="text-secondary small">Jatuh tempo: <?= html_escape($text($item['due_date'])); ?></div>
									</td>
									<td data-label="Kembali"><?= html_escape($text($item['actual_return_at'])); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>

			<div class="card-footer responsive-footer">
				<p class="m-0 text-secondary">Menampilkan <?= number_format($pagination['total_rows'] > 0 ? $pagination['offset'] + 1 : 0, 0, ',', '.'); ?>-<?= number_format(min($pagination['offset'] + $pagination['per_page'], $pagination['total_rows']), 0, ',', '.'); ?> dari <?= number_format($pagination['total_rows'], 0, ',', '.'); ?> data</p>
				<ul class="pagination m-0">
					<li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : ''; ?>"><a class="page-link" href="<?= $page_url(max(1, $pagination['page'] - 1)); ?>">Prev</a></li>
					<?php for ($i = max(1, $pagination['page'] - 2); $i <= min($pagination['total_pages'], $pagination['page'] + 2); $i++): ?>
						<li class="page-item <?= $i === (int) $pagination['page'] ? 'active' : ''; ?>"><a class="page-link" href="<?= $page_url($i); ?>"><?= $i; ?></a></li>
					<?php endfor; ?>
					<li class="page-item <?= $pagination['page'] >= $pagination['total_pages'] ? 'disabled' : ''; ?>"><a class="page-link" href="<?= $page_url(min($pagination['total_pages'], $pagination['page'] + 1)); ?>">Next</a></li>
				</ul>
			</div>
		</div>
	</div>
</div>
