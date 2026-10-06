<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$status_labels = [
	'pending' => 'Menunggu',
	'approved' => 'Disiapkan',
	'rejected' => 'Ditolak',
	'fulfilled' => 'Selesai',
	'cancelled' => 'Dibatalkan',
	'active' => 'Dipinjam',
	'returned' => 'Dikembalikan',
	'completed_legacy' => 'Selesai (riwayat lama)',
];
$type_labels = [
	'reservation' => 'Reservasi',
	'request' => 'Request',
];
$query_base = $_GET;
unset($query_base['page']);
$page_url = function ($page) use ($query_base) {
	return base_url('catalog/requests?' . http_build_query(array_merge($query_base, ['page' => $page])));
};
$pending_count = 0;
$default_loan_days = max(1, min(60, (int) ($loan_settings['default_loan_days'] ?? 7)));
$default_due_date = date('Y-m-d', strtotime('+' . $default_loan_days . ' days'));
foreach ($requests as $row) {
	if (($row['status'] ?? '') === 'pending') {
		$pending_count++;
	}
}
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Layanan Digital</div>
				<h1 class="page-title">Request Buku</h1>
			</div>
			<div class="col-auto ms-auto">
				<?php if (! empty($can_create_loan)): ?><button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#manual-loan-modal"><i class="ti ti-scan me-1"></i>Catat Pinjam Manual</button><?php endif; ?>
				<a href="<?= base_url('catalog/loans'); ?>" class="btn btn-outline-primary"><i class="ti ti-book-2 me-1"></i>Transaksi Pinjam</a>
				<a href="<?= base_url('catalog'); ?>" class="btn btn-outline-primary"><i class="ti ti-books me-1"></i>Katalog Admin</a>
			</div>
		</div>
	</div>
</div>
<?php if (! empty($can_create_loan)): ?>
<div class="modal modal-blur fade" id="manual-loan-modal" tabindex="-1">
	<div class="modal-dialog"><div class="modal-content">
		<?= form_open('catalog/loans/issue'); ?>
		<div class="modal-header"><h5 class="modal-title">Catat Peminjaman Manual</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
		<div class="modal-body">
			<div class="alert alert-info py-2 small"><i class="ti ti-scan me-1"></i>Gunakan scanner barcode untuk eksemplar. Member dapat dikenali dari nomor anggota, NIK, atau nomor HP.</div>
			<label class="form-label required">Member</label><input class="form-control mb-3" name="member_lookup" required placeholder="Nomor anggota, NIK, atau nomor HP" autofocus>
			<label class="form-label required">Barcode / No. Induk Eksemplar</label><input class="form-control mb-3" name="item_lookup" required placeholder="Pindai barcode atau ketik nomor induk">
			<label class="form-label">Jatuh Tempo</label><input type="date" class="form-control" name="due_date" min="<?= date('Y-m-d'); ?>">
		</div>
		<div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary"><i class="ti ti-book-download me-1"></i>Catat Peminjaman</button></div>
		<?= form_close(); ?>
	</div></div>
</div>
<?php endif; ?>

<div class="page-body">
	<div class="container-xl">
		<?php if ($this->session->flashdata('success')): ?>
			<div class="alert alert-success"><?= html_escape($this->session->flashdata('success')); ?></div>
		<?php endif; ?>
		<?php if ($this->session->flashdata('error')): ?>
			<div class="alert alert-danger"><?= html_escape($this->session->flashdata('error')); ?></div>
		<?php endif; ?>

		<div class="service-command-center">
			<div>
				<div class="section-kicker">Reservasi dan permintaan</div>
				<h2>Antrean layanan koleksi</h2>
				<p>Request adalah antrean/reservasi. Setelah disiapkan, klik <strong>Serahkan &amp; Catat Pinjam</strong>; saat itulah transaksi peminjaman resmi dibuat.</p>
			</div>
			<span class="service-chip"><i class="ti ti-bell-ringing"></i><?= number_format((int) $pending_count, 0, ',', '.'); ?> menunggu di halaman ini</span>
		</div>

		<?php if (! empty($can_manage_loan_settings)): ?>
		<div class="card admin-card mb-3"><div class="card-body">
			<?= form_open('catalog/loans/settings', ['class' => 'row g-2 align-items-end']); ?>
			<div class="col-md-4"><label class="form-check form-switch mb-0"><input class="form-check-input" type="checkbox" name="is_loan_enabled" value="1" <?= ! empty($loan_settings['is_loan_enabled']) ? 'checked' : ''; ?>><span class="form-check-label"><strong>Layanan peminjaman fisik dibuka</strong><small class="d-block text-secondary">Jika ditutup, request dan pinjam manual baru ditolak.</small></span></label></div>
			<div class="col-6 col-md-3"><label class="form-label">Lama pinjam standar (hari)</label><input type="number" min="1" max="60" class="form-control" name="default_loan_days" value="<?= (int) ($loan_settings['default_loan_days'] ?? 7); ?>"></div>
			<div class="col-6 col-md-3"><label class="form-label">Maks. buku sedang dipinjam/member</label><input type="number" min="1" max="20" class="form-control" name="max_active_loans" value="<?= (int) ($loan_settings['max_active_loans'] ?? 3); ?>"></div>
			<div class="col-md-2"><button class="btn btn-outline-primary w-100"><i class="ti ti-device-floppy me-1"></i>Simpan Aturan</button></div>
			<?= form_close(); ?>
		</div></div>
		<?php endif; ?>

		<div class="card admin-card data-workspace">
			<div class="card-body workspace-filter">
				<?= form_open('catalog/requests', ['method' => 'get', 'class' => 'row g-2 align-items-end service-filter-form']); ?>
					<div class="col-md-4">
						<label class="form-label">Cari</label>
						<input type="text" class="form-control" name="q" value="<?= html_escape($filters['q'] ?? ''); ?>" placeholder="Kode, nama, buku, barcode">
					</div>
					<div class="col-md-3">
						<label class="form-label">Status</label>
						<select class="form-select" name="status">
							<option value="">Semua status</option>
							<?php foreach ($status_labels as $value => $label): ?>
								<option value="<?= $value; ?>" <?= ($filters['status'] ?? '') === $value ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-3">
						<label class="form-label">Tipe</label>
						<select class="form-select" name="request_type">
							<option value="">Semua tipe</option>
							<?php foreach ($type_labels as $value => $label): ?>
								<option value="<?= $value; ?>" <?= ($filters['request_type'] ?? '') === $value ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-2">
						<label class="form-label">Baris</label>
						<select class="form-select" name="per_page">
							<?php foreach ([10, 25, 50, 100] as $limit): ?>
								<option value="<?= $limit; ?>" <?= (int) ($filters['per_page'] ?? 25) === $limit ? 'selected' : ''; ?>><?= $limit; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-12">
						<button class="btn btn-primary"><i class="ti ti-filter me-1"></i>Terapkan Filter</button>
					</div>
				<?= form_close(); ?>
			</div>

			<div class="table-responsive">
				<table class="table table-vcenter card-table">
					<thead><tr><th>Request</th><th>Pemohon</th><th>Buku</th><th>Status</th><th>Aksi</th></tr></thead>
					<tbody>
						<?php if (empty($requests)): ?><tr><td colspan="5" class="text-center text-secondary py-4">Belum ada request buku.</td></tr><?php endif; ?>
						<?php foreach ($requests as $request): ?>
							<?php $display_status = $request['display_status'] ?? $request['status']; ?>
							<tr>
								<td data-label="Request">
									<div class="fw-semibold"><?= html_escape($request['request_code']); ?></div>
									<div class="text-secondary small"><?= html_escape($type_labels[$request['request_type']] ?? $request['request_type']); ?> - <?= html_escape($request['created_at']); ?></div>
								</td>
								<td data-label="Pemohon">
									<div class="fw-semibold"><?= html_escape($request['requester_name']); ?></div>
									<div class="text-secondary small"><?= html_escape($request['requester_phone'] ?: ($request['requester_email'] ?: '-')); ?></div>
								</td>
								<td data-label="Buku">
									<div><?= html_escape($request['title'] ?: 'Katalog #' . $request['book_id']); ?></div>
									<div class="text-secondary small"><code><?= html_escape($request['barcode'] ?: '-'); ?></code> <?= html_escape($request['call_number'] ?: ''); ?></div>
									<?php if (! empty($request['message'])): ?>
										<div class="queue-note mt-2"><small class="d-block text-secondary mb-1">Pesan pemohon</small><?= html_escape($request['message']); ?></div>
									<?php endif; ?>
									<?php if (! empty($request['admin_note'])): ?>
										<div class="queue-note mt-2"><small class="d-block text-secondary mb-1">Tanggapan petugas</small><?= html_escape($request['admin_note']); ?></div>
									<?php endif; ?>
								</td>
								<td data-label="Status"><span class="badge <?= in_array($display_status, ['active', 'returned'], true) ? 'bg-green-lt text-green' : ($display_status === 'completed_legacy' ? 'bg-secondary-lt' : (($request['status'] ?? '') === 'rejected' ? 'bg-red-lt text-red' : (($request['status'] ?? '') === 'approved' ? 'bg-yellow-lt text-yellow' : 'bg-blue-lt text-blue'))); ?>"><?= html_escape($status_labels[$display_status] ?? $display_status); ?></span><?php if (($request['status'] ?? '') === 'approved'): ?><div class="text-secondary small mt-1">Eksemplar ditahan untuk member</div><?php endif; ?></td>
								<td data-label="Aksi">
									<?php if (($request['status'] ?? '') === 'approved'): ?>
										<?= form_open('catalog/requests/issue/' . (int) $request['id'], ['class' => 'queue-action-form']); ?>
											<label class="form-label form-label-sm mb-1">Jatuh tempo</label>
											<input type="date" class="form-control form-control-sm" name="due_date" min="<?= date('Y-m-d'); ?>" value="<?= $default_due_date; ?>">
											<div class="form-hint">Standar <?= $default_loan_days; ?> hari; dapat diubah untuk transaksi ini.</div>
											<button class="btn btn-success btn-sm w-100" data-confirm="Pastikan buku fisik sudah diserahkan kepada member. Lanjut mencatat peminjaman?" data-confirm-title="Serahkan Buku" data-confirm-ok="Ya, Catat Pinjam"><i class="ti ti-book-download me-1"></i>Serahkan &amp; Catat Pinjam</button>
										<?= form_close(); ?>
										<?= form_open('catalog/requests/update/' . (int) $request['id'], ['class' => 'queue-action-form mt-2']); ?>
											<input type="hidden" name="status" value="cancelled">
											<button class="btn btn-outline-secondary btn-sm w-100"><i class="ti ti-x me-1"></i>Batalkan Reservasi</button>
										<?= form_close(); ?>
									<?php elseif (($request['status'] ?? '') === 'pending'): ?>
										<?= form_open('catalog/requests/update/' . (int) $request['id'], ['class' => 'queue-action-form']); ?>
											<select class="form-select form-select-sm" name="status" aria-label="Keputusan request">
												<option value="approved">Siapkan untuk dipinjam</option>
												<option value="rejected">Tolak</option>
												<option value="cancelled">Batalkan</option>
											</select>
											<input type="text" class="form-control form-control-sm" name="admin_note" placeholder="Tanggapan/alasan untuk pemohon (ditampilkan di dashboard)">
											<button class="btn btn-primary btn-sm w-100"><i class="ti ti-check me-1"></i>Simpan Keputusan</button>
										<?= form_close(); ?>
									<?php elseif (($request['status'] ?? '') === 'fulfilled' && ! empty($request['loan_transaction_item_id'])): ?>
										<a href="<?= base_url('catalog/loans?q=' . rawurlencode($request['request_code'])); ?>" class="btn btn-outline-primary btn-sm w-100"><i class="ti ti-receipt-2 me-1"></i>Lihat Transaksi</a>
									<?php else: ?><span class="text-secondary small">Tidak ada aksi lanjutan</span><?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
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
