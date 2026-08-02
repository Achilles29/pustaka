<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$status_labels = [
	'pending' => 'Menunggu',
	'approved' => 'Disetujui',
	'rejected' => 'Ditolak',
	'cancelled' => 'Dibatalkan',
];
$query_base = $_GET;
unset($query_base['page']);
$page_url = function ($page) use ($query_base) {
	return base_url('members/renewals?' . http_build_query(array_merge($query_base, ['page' => $page])));
};
$pending_count = 0;
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
				<div class="page-pretitle">Membership Digital</div>
				<h1 class="page-title">Perpanjangan Membership</h1>
			</div>
		</div>
	</div>
</div>

<div class="page-body">
	<div class="container-xl">
		<?php if ($this->session->flashdata('success')): ?>
			<div class="alert alert-success"><?= html_escape($this->session->flashdata('success')); ?></div>
		<?php endif; ?>

		<div class="service-command-center">
			<div>
				<div class="section-kicker">Antrean membership</div>
				<h2>Pengajuan perpanjangan</h2>
				<p>Setujui pengajuan untuk otomatis memperpanjang masa berlaku member sesuai durasi yang diminta.</p>
			</div>
			<span class="service-chip"><i class="ti ti-clock"></i><?= number_format((int) $pending_count, 0, ',', '.'); ?> menunggu di halaman ini</span>
		</div>

		<div class="card admin-card data-workspace">
			<div class="card-body workspace-filter">
				<?= form_open('members/renewals', ['method' => 'get', 'class' => 'row g-2 align-items-end service-filter-form']); ?>
					<div class="col-md-5">
						<label class="form-label">Cari</label>
						<input type="text" class="form-control" name="q" value="<?= html_escape($filters['q'] ?? ''); ?>" placeholder="Kode, nama, nomor anggota, username">
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
					<div class="col-md-2">
						<label class="form-label">Baris</label>
						<select class="form-select" name="per_page">
							<?php foreach ([10, 25, 50, 100] as $limit): ?>
								<option value="<?= $limit; ?>" <?= (int) ($filters['per_page'] ?? 25) === $limit ? 'selected' : ''; ?>><?= $limit; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-2">
						<button class="btn btn-primary w-100"><i class="ti ti-filter me-1"></i>Filter</button>
					</div>
				<?= form_close(); ?>
			</div>

			<div class="table-responsive">
				<table class="table table-vcenter card-table">
					<thead><tr><th>Pengajuan</th><th>Member</th><th>Masa Berlaku</th><th>Status</th><th>Aksi</th></tr></thead>
					<tbody>
						<?php if (empty($requests)): ?><tr><td colspan="5" class="text-center text-secondary py-4">Belum ada pengajuan perpanjangan.</td></tr><?php endif; ?>
						<?php foreach ($requests as $request): ?>
							<tr>
								<td data-label="Pengajuan">
									<div class="fw-semibold"><?= html_escape($request['request_code']); ?></div>
									<div class="text-secondary small"><?= html_escape($request['created_at']); ?> - <?= number_format((int) $request['requested_months'], 0, ',', '.'); ?> bulan</div>
								</td>
								<td data-label="Member">
									<div class="fw-semibold"><?= html_escape($request['full_name']); ?></div>
									<div class="text-secondary small"><code><?= html_escape($request['member_no'] ?: '-'); ?></code></div>
								</td>
								<td data-label="Masa Berlaku">
									<div><?= html_escape($request['current_expired_at'] ?: ($request['expired_at'] ?: '-')); ?></div>
									<div class="text-secondary small">Status member: <?= html_escape($request['member_status'] ?: '-'); ?></div>
									<?php if (! empty($request['reason'])): ?>
										<div class="queue-note mt-2"><?= html_escape($request['reason']); ?></div>
									<?php endif; ?>
								</td>
								<td data-label="Status"><span class="badge bg-blue-lt"><?= html_escape($status_labels[$request['status']] ?? $request['status']); ?></span></td>
								<td data-label="Aksi">
									<?= form_open('members/renewals/update/' . (int) $request['id'], ['class' => 'queue-action-form']); ?>
										<select class="form-select form-select-sm" name="status" aria-label="Ubah status perpanjangan">
											<option value="approved" <?= $request['status'] === 'approved' ? 'selected' : ''; ?>>Setujui</option>
											<option value="rejected" <?= $request['status'] === 'rejected' ? 'selected' : ''; ?>>Tolak</option>
											<option value="cancelled" <?= $request['status'] === 'cancelled' ? 'selected' : ''; ?>>Batalkan</option>
										</select>
										<input type="text" class="form-control form-control-sm" name="admin_note" placeholder="Catatan petugas">
										<button class="btn btn-primary btn-sm w-100"><i class="ti ti-check me-1"></i>Simpan Keputusan</button>
									<?= form_close(); ?>
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
