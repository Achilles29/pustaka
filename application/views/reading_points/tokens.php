<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$status_labels = [
	'active' => 'Aktif',
	'used' => 'Kuota Habis',
	'expired' => 'Kedaluwarsa',
	'revoked' => 'Dicabut',
];
$status_badges = [
	'active' => 'bg-green-lt',
	'used' => 'bg-yellow-lt',
	'expired' => 'bg-secondary-lt',
	'revoked' => 'bg-red-lt',
];
$unit_labels = ['minutes' => 'menit', 'pages' => 'halaman', 'books' => 'buku'];
$query_base = $_GET;
unset($query_base['page']);
$page_url = function ($page) use ($query_base) {
	return base_url('reading-points/tokens?' . http_build_query(array_merge($query_base, ['page' => $page])));
};
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Pojok Baca Digital</div>
				<h1 class="page-title">Monitoring Token Baca</h1>
			</div>
			<div class="col-auto ms-auto">
				<a href="<?= base_url('reading-points'); ?>" class="btn btn-outline-primary"><i class="ti ti-map-pin me-1"></i>Titik Baca</a>
			</div>
		</div>
	</div>
</div>

<div class="page-body">
	<div class="container-xl">
		<?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?= html_escape($this->session->flashdata('success')); ?></div><?php endif; ?>
		<?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?= html_escape($this->session->flashdata('error')); ?></div><?php endif; ?>

		<div class="ops-summary-strip">
			<div>
				<div class="section-kicker">Token dan kuota</div>
				<h2>Pantau token aktif, sisa kuota, dan akses member dari luar lokasi.</h2>
				<p>Token bisa dipakai dari mana saja. Kuota hanya berkurang saat akses dari luar Pojok Baca atau luar radius perpustakaan.</p>
			</div>
			<a href="<?= base_url('reader/assets'); ?>" class="btn btn-outline-primary"><i class="ti ti-file-lock me-1"></i>Reader</a>
		</div>

		<div class="card admin-card data-workspace">
			<div class="card-body workspace-filter">
				<?= form_open('reading-points/tokens', ['method' => 'get', 'class' => 'row g-2 align-items-end service-filter-form']); ?>
					<div class="col-md-5">
						<label class="form-label">Cari</label>
						<input type="text" class="form-control" name="q" value="<?= html_escape($filters['q'] ?? ''); ?>" placeholder="Token, nama, nomor anggota, NIK, titik">
					</div>
					<div class="col-md-3">
						<label class="form-label">Status</label>
						<select class="form-select" name="status">
							<option value="">Semua</option>
							<?php foreach ($status_labels as $value => $label): ?>
								<option value="<?= $value; ?>" <?= ($filters['status'] ?? '') === $value ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-2">
						<label class="form-label">Baris</label>
						<select class="form-select" name="per_page">
							<?php foreach ([10, 25, 50, 100] as $limit): ?><option value="<?= $limit; ?>" <?= (int) ($filters['per_page'] ?? 25) === $limit ? 'selected' : ''; ?>><?= $limit; ?></option><?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-2">
						<button class="btn btn-primary w-100"><i class="ti ti-filter me-1"></i>Filter</button>
					</div>
				<?= form_close(); ?>
			</div>

			<div class="table-responsive">
				<table class="table table-vcenter card-table">
					<thead><tr><th>Member</th><th>Token</th><th>Titik</th><th>Kuota</th><th>Masa Berlaku</th><th>Status</th><th>Aksi</th></tr></thead>
					<tbody>
						<?php if (empty($tokens)): ?><tr><td colspan="7" class="text-center text-secondary py-4">Belum ada token sesuai filter.</td></tr><?php endif; ?>
						<?php foreach ($tokens as $token): ?>
							<?php
							$remaining = (int) $token['quota_total'] === 0 ? 'Unlimited' : number_format(max(0, (int) $token['quota_total'] - (int) $token['quota_used']), 0, ',', '.') . ' ' . ($unit_labels[$token['quota_unit']] ?? $token['quota_unit']);
							?>
							<tr>
								<td data-label="Member">
									<div class="fw-semibold"><?= html_escape($token['full_name'] ?: '-'); ?></div>
									<div class="text-secondary small"><?= html_escape($token['member_no'] ?: ($token['identity_number'] ?: '-')); ?></div>
								</td>
								<td data-label="Token">
									<code><?= html_escape($token['token']); ?></code>
									<div class="text-secondary small">Terbit: <?= html_escape($token['issued_at']); ?></div>
								</td>
								<td data-label="Titik"><?= html_escape($token['point_name'] ?: '-'); ?></td>
								<td data-label="Kuota">
									<div class="fw-semibold"><?= html_escape($remaining); ?></div>
									<div class="text-secondary small">Terpakai <?= number_format((int) $token['quota_used'], 0, ',', '.'); ?> / <?= (int) $token['quota_total'] === 0 ? 'Unlimited' : number_format((int) $token['quota_total'], 0, ',', '.'); ?></div>
								</td>
								<td data-label="Masa Berlaku"><?= html_escape($token['expires_at'] ?: '-'); ?></td>
								<td data-label="Status"><span class="badge <?= html_escape($status_badges[$token['status']] ?? 'bg-secondary-lt'); ?>"><?= html_escape($status_labels[$token['status']] ?? $token['status']); ?></span></td>
								<td data-label="Aksi">
									<?php if ($token['status'] === 'active'): ?>
										<?= form_open('reading-points/tokens/revoke/' . (int) $token['id'], ['class' => 'queue-action-form']); ?>
											<input type="hidden" name="reason" value="Dicabut dari monitoring admin">
											<button class="btn btn-sm btn-action btn-action-danger" onclick="return confirm('Cabut token ini?')"><i class="ti ti-ban"></i><span>Cabut</span></button>
										<?= form_close(); ?>
									<?php else: ?>
										<span class="text-secondary small">Tidak ada aksi</span>
									<?php endif; ?>
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
					<?php for ($i = max(1, $pagination['page'] - 2); $i <= min($pagination['total_pages'], $pagination['page'] + 2); $i++): ?><li class="page-item <?= $i === (int) $pagination['page'] ? 'active' : ''; ?>"><a class="page-link" href="<?= $page_url($i); ?>"><?= $i; ?></a></li><?php endfor; ?>
					<li class="page-item <?= $pagination['page'] >= $pagination['total_pages'] ? 'disabled' : ''; ?>"><a class="page-link" href="<?= $page_url(min($pagination['total_pages'], $pagination['page'] + 1)); ?>">Next</a></li>
				</ul>
			</div>
		</div>
	</div>
</div>
