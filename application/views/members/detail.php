<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$status_labels = [
	'active' => 'Aktif',
	'inactive' => 'Nonaktif',
	'blocked' => 'Diblokir',
	'expired' => 'Kedaluwarsa',
	'unknown' => 'Belum Dipetakan',
];
$user_status_labels = [
	'active' => 'Aktif',
	'inactive' => 'Nonaktif',
	'suspended' => 'Ditangguhkan',
];
$card_status = $member['card_status'] ?? 'active';
$member_label = function ($label_key, $raw_key) use ($member) {
	return $member[$label_key] ?: ($member[$raw_key] ?: '-');
};
$url_path = function ($path) {
	$segments = explode('/', str_replace('\\', '/', trim((string) $path, '/')));
	return implode('/', array_map('rawurlencode', $segments));
};
if (! empty($member['photo_local_path'])) {
	$photo_url = base_url($url_path($member['photo_local_path']));
} elseif (! empty($member['photo_source_path']) && strpos($member['photo_source_path'], 'assets/') === 0) {
	$photo_url = base_url($url_path($member['photo_source_path']));
} elseif (! empty($member['photo_source_path'])) {
	$photo_url = base_url($url_path('assets/uploads/inlislite/source_mirror/' . $member['photo_source_path']));
} else {
	$photo_url = '';
}
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Membership</div>
				<h1 class="page-title">Detail Member</h1>
			</div>
			<div class="col-auto ms-auto">
				<div class="btn-list">
					<a href="<?= base_url('members'); ?>" class="btn btn-outline-secondary">
						<i class="ti ti-arrow-left me-1"></i>Membership
					</a>
					<a href="<?= base_url('members/edit/' . (int) $member['id']); ?>" class="btn btn-primary">
						<i class="ti ti-edit me-1"></i>Edit
					</a>
					<a href="<?= base_url('members/delete/' . (int) $member['id']); ?>" class="btn btn-outline-danger" onclick="return confirm('Nonaktifkan member ini dari data aktif?')">
						<i class="ti ti-trash me-1"></i>Hapus
					</a>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="page-body">
	<div class="container-xl">
		<?php if ($this->session->flashdata('success')): ?>
			<div class="alert alert-success"><?= html_escape($this->session->flashdata('success')); ?></div>
		<?php endif; ?>
		<?php if ($this->session->flashdata('error')): ?>
			<div class="alert alert-danger"><?= html_escape($this->session->flashdata('error')); ?></div>
		<?php endif; ?>

		<div class="row row-cards">
			<div class="col-lg-4">
				<div class="card admin-card">
					<div class="card-body text-center">
						<div class="member-photo-detail mx-auto mb-3">
							<?php if ($photo_url): ?>
								<img src="<?= html_escape($photo_url); ?>" alt="Foto <?= html_escape($member['full_name']); ?>">
							<?php else: ?>
								<i class="ti ti-user"></i>
							<?php endif; ?>
						</div>
						<h2 class="h3 mb-1"><?= html_escape($member['full_name']); ?></h2>
						<div class="text-secondary"><code><?= html_escape($member['member_no'] ?: '-'); ?></code></div>
						<div class="mt-3">
							<span class="badge bg-blue-lt"><?= html_escape($status_labels[$member['status']] ?? ucfirst($member['status'])); ?></span>
							<span class="badge <?= ($member['user_status'] ?? '') === 'active' ? 'bg-green-lt' : 'bg-secondary-lt'; ?>"><?= html_escape($user_status_labels[$member['user_status'] ?? ''] ?? 'Belum Ada Akun'); ?></span>
						</div>
					</div>
				</div>

				<div class="card admin-card mt-3 member-card-control">
					<div class="card-header">
						<h2 class="card-title">Kartu Digital</h2>
					</div>
					<div class="card-body">
						<div class="digital-card-state <?= $card_status === 'blocked' ? 'is-danger' : 'is-ok'; ?>">
							<i class="ti <?= $card_status === 'blocked' ? 'ti-lock' : 'ti-shield-check'; ?>"></i>
							<div>
								<span>Status sekarang</span>
								<strong><?= $card_status === 'blocked' ? 'Kartu Diblokir' : 'Kartu Aktif'; ?></strong>
							</div>
						</div>
						<?php if ($card_status === 'blocked'): ?>
							<div class="text-secondary small mt-2">Alasan: <?= html_escape($member['card_block_reason'] ?: 'Belum ada catatan.'); ?></div>
							<div class="text-secondary small">Waktu blokir: <?= html_escape($member['card_blocked_at'] ?: '-'); ?></div>
						<?php endif; ?>

						<?= form_open('members/card/update/' . (int) $member['id'], ['class' => 'mt-3']); ?>
							<input type="hidden" name="card_status" value="<?= $card_status === 'blocked' ? 'active' : 'blocked'; ?>">
							<?php if ($card_status !== 'blocked'): ?>
								<label class="form-label">Alasan operasional</label>
								<textarea class="form-control mb-2" name="card_block_reason" rows="3" placeholder="Contoh: kartu dilaporkan disalahgunakan, data identitas perlu verifikasi ulang." required></textarea>
								<button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Blokir kartu digital member ini?')">
									<i class="ti ti-lock me-1"></i>Blokir Kartu Digital
								</button>
							<?php else: ?>
								<label class="form-label">Catatan aktivasi</label>
								<textarea class="form-control mb-2" name="card_block_reason" rows="2" placeholder="Opsional, alasan kartu diaktifkan kembali."></textarea>
								<button type="submit" class="btn btn-primary w-100">
									<i class="ti ti-shield-check me-1"></i>Aktifkan Kartu Digital
								</button>
							<?php endif; ?>
						<?= form_close(); ?>
					</div>
				</div>

				<div class="card admin-card mt-3">
					<div class="card-header"><h2 class="card-title">Akun Login</h2></div>
					<div class="card-body">
						<div class="datagrid">
							<div class="datagrid-item"><div class="datagrid-title">Username Login</div><div class="datagrid-content"><?= html_escape($member['username'] ?: '-'); ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">Aturan Username</div><div class="datagrid-content">NIK utama, fallback nomor anggota.</div></div>
							<div class="datagrid-item"><div class="datagrid-title">Email Akun</div><div class="datagrid-content"><?= html_escape($member['user_email'] ?: '-'); ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">Password Awal</div><div class="datagrid-content"><code><?= html_escape($default_password); ?></code></div></div>
							<div class="datagrid-item"><div class="datagrid-title">Wajib Ganti</div><div class="datagrid-content"><?= (int) ($member['force_password_change'] ?? 0) === 1 ? 'Ya' : 'Tidak'; ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">Login Terakhir</div><div class="datagrid-content"><?= html_escape($member['last_login_at'] ?: '-'); ?></div></div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-lg-8">
				<div class="card admin-card mb-3">
					<div class="card-header"><h2 class="card-title">Profil</h2></div>
					<div class="card-body">
						<div class="datagrid">
							<div class="datagrid-item"><div class="datagrid-title">ID Sumber</div><div class="datagrid-content"><?= html_escape($member['source_id'] ?: '-'); ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">Jenis Identitas</div><div class="datagrid-content"><?= html_escape($member_label('identity_type_label', 'identity_type')); ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">No Identitas</div><div class="datagrid-content"><?= html_escape($member['identity_number'] ?: '-'); ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">Jenis Kelamin</div><div class="datagrid-content"><?= html_escape($member_label('gender_label', 'gender')); ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">Tempat Lahir</div><div class="datagrid-content"><?= html_escape($member['birth_place'] ?: '-'); ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">Tanggal Lahir</div><div class="datagrid-content"><?= html_escape($member['birth_date'] ?: '-'); ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">Telepon</div><div class="datagrid-content"><?= html_escape($member['phone'] ?: '-'); ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">Email Profil</div><div class="datagrid-content"><?= html_escape($member['email'] ?: '-'); ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">Jenis Member</div><div class="datagrid-content"><?= html_escape($member_label('member_type_label', 'member_type')); ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">Status INLISLite</div><div class="datagrid-content"><?= html_escape($member['member_status_label'] ?: '-'); ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">Pendidikan</div><div class="datagrid-content"><?= html_escape($member_label('education_label', 'education')); ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">Pekerjaan</div><div class="datagrid-content"><?= html_escape($member_label('occupation_label', 'occupation')); ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">Tanggal Daftar</div><div class="datagrid-content"><?= html_escape($member['registered_at'] ?: '-'); ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">Masa Berlaku</div><div class="datagrid-content"><?= html_escape($member['expired_at'] ?: '-'); ?></div></div>
						</div>
					</div>
				</div>

				<div class="card admin-card">
					<div class="card-header"><h2 class="card-title">Alamat</h2></div>
					<div class="card-body">
						<p><?= nl2br(html_escape($member['address'] ?: '-')); ?></p>
						<div class="text-secondary"><?= html_escape(trim(($member['village'] ?: '-') . ', ' . ($member['district'] ?: '-'), ', ')); ?></div>
					</div>
				</div>

				<div class="card admin-card mt-3 data-workspace">
					<div class="card-header workspace-header">
						<div>
							<h2 class="card-title">Aktivitas INLISLite</h2>
							<div class="text-secondary small">Kunjungan, hak pinjam, dan histori peminjaman yang sudah disinkronkan.</div>
						</div>
						<ul class="nav nav-tabs card-header-tabs workspace-tabs" role="tablist">
							<li class="nav-item"><a href="#member-visits" class="nav-link active" data-bs-toggle="tab"><i class="ti ti-door-enter me-1"></i>Kunjungan</a></li>
							<li class="nav-item"><a href="#member-loans" class="nav-link" data-bs-toggle="tab"><i class="ti ti-books me-1"></i>Pinjam</a></li>
							<li class="nav-item"><a href="#member-access" class="nav-link" data-bs-toggle="tab"><i class="ti ti-list-check me-1"></i>Hak Akses</a></li>
						</ul>
					</div>
					<div class="tab-content">
						<div class="tab-pane active show" id="member-visits">
							<div class="table-responsive">
								<table class="table table-vcenter card-table">
									<thead><tr><th>Tanggal</th><th>Lokasi</th><th>Tujuan</th><th>Keterangan</th></tr></thead>
									<tbody>
										<?php if (empty($visits)): ?><tr><td colspan="4" class="text-center text-secondary py-4">Belum ada data kunjungan tersinkron.</td></tr><?php endif; ?>
										<?php foreach ($visits as $visit): ?>
											<tr>
												<td><?= html_escape($visit['visited_at'] ?: '-'); ?></td>
												<td>
													<div><?= html_escape($visit['location_label'] ?: ($visit['location_id'] ?: '-')); ?></div>
													<div class="text-secondary small"><?= html_escape($visit['location_loan_label'] ?: ($visit['location_loan_id'] ?: '-')); ?></div>
												</td>
												<td><?= html_escape($visit['purpose_label'] ?: ($visit['purpose_id'] ?: '-')); ?></td>
												<td><?= html_escape($visit['information'] ?: ($visit['description'] ?: '-')); ?></td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						</div>
						<div class="tab-pane" id="member-loans">
							<div class="table-responsive">
								<table class="table table-vcenter card-table">
									<thead><tr><th>Tanggal</th><th>Buku</th><th>Barcode</th><th>Status</th><th>Kembali</th></tr></thead>
									<tbody>
										<?php if (empty($loans)): ?><tr><td colspan="5" class="text-center text-secondary py-4">Belum ada histori pinjam tersinkron.</td></tr><?php endif; ?>
										<?php foreach ($loans as $loan): ?>
											<tr>
												<td><?= html_escape($loan['loan_date'] ?: '-'); ?></td>
												<td><?= html_escape($loan['title'] ?: ('Koleksi #' . $loan['source_collection_id'])); ?></td>
												<td><code><?= html_escape($loan['barcode'] ?: '-'); ?></code></td>
												<td><span class="badge bg-blue-lt"><?= html_escape($loan['loan_status'] ?: '-'); ?></span></td>
												<td><?= html_escape($loan['actual_return_at'] ?: '-'); ?></td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						</div>
						<div class="tab-pane" id="member-access">
							<div class="table-responsive">
								<table class="table table-vcenter card-table">
									<thead><tr><th>Tipe</th><th>Aturan</th><th>Tabel Sumber</th></tr></thead>
									<tbody>
										<?php if (empty($access_rules)): ?><tr><td colspan="3" class="text-center text-secondary py-4">Belum ada hak pinjam tersinkron.</td></tr><?php endif; ?>
										<?php foreach ($access_rules as $rule): ?>
											<tr>
												<td><span class="badge bg-blue-lt"><?= html_escape($rule['rule_type']); ?></span></td>
												<td>
													<div><?= html_escape($rule['rule_label'] ?: '-'); ?></div>
													<div class="text-secondary small">ID sumber: <?= html_escape($rule['source_rule_id'] ?: '-'); ?></div>
												</td>
												<td><code><?= html_escape($rule['source_table']); ?></code></td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
