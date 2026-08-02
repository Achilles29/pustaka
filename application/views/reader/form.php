<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$is_edit = ! empty($asset);
$field = function ($key, $default = '') use ($asset) {
	return $asset[$key] ?? $default;
};
$policy_labels = [
	'online_only' => 'Online aman - render halaman, PDF utuh dikunci',
	'download_allowed' => 'Bebas download - hanya untuk domain publik/lisensi bebas',
	'location_only' => 'Pojok Baca / token - akses luar lokasi mengurangi kuota',
	'member_only' => 'Member saja - butuh login, PDF utuh dikunci',
	'internal' => 'Internal petugas - tidak tampil untuk publik/member',
];
$rights_labels = [
	'public_domain' => 'Domain publik',
	'licensed' => 'Lisensi resmi',
	'owned' => 'Milik perpustakaan/pemda',
	'permission_letter' => 'Ada surat izin penerbit/pemegang hak',
	'internal_use' => 'Internal operasional',
	'unknown' => 'Belum jelas / jangan aktifkan publik',
];
$status_labels = [
	'draft' => 'Draft',
	'active' => 'Aktif',
	'archived' => 'Arsip',
];
$selected_book_id = (int) ($is_edit ? $field('book_id') : ($selected_book_id ?? 0));
$selected_exists = false;
foreach ($books ?? [] as $book) {
	if ((int) $book['id'] === $selected_book_id) {
		$selected_exists = true;
		break;
	}
}
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Reader PDF Aman</div>
				<h1 class="page-title"><?= $is_edit ? 'Edit Ebook' : 'Tambah Ebook'; ?></h1>
			</div>
			<div class="col-auto ms-auto">
				<a href="<?= base_url('reader/assets'); ?>" class="btn btn-outline-secondary">
					<i class="ti ti-arrow-left me-1"></i>Kembali
				</a>
			</div>
		</div>
	</div>
</div>

<div class="page-body">
	<div class="container-xl">
		<?php if ($this->session->flashdata('error')): ?>
			<div class="alert alert-danger"><?= html_escape($this->session->flashdata('error')); ?></div>
		<?php endif; ?>

		<?= form_open_multipart($action); ?>
			<div class="row row-cards">
				<div class="col-lg-7">
					<div class="card admin-card">
						<div class="card-header"><h2 class="card-title">File dan Buku</h2></div>
						<div class="card-body">
							<div class="mb-3">
								<label class="form-label">Buku katalog</label>
								<select class="form-select" name="book_id" required>
									<option value="">Pilih buku</option>
									<?php if ($selected_book_id > 0 && ! $selected_exists): ?>
										<option value="<?= $selected_book_id; ?>" selected><?= html_escape(($selected_book['title'] ?? $field('title')) ?: 'Buku #' . $selected_book_id); ?></option>
									<?php endif; ?>
									<?php foreach ($books ?? [] as $book): ?>
										<option value="<?= (int) $book['id']; ?>" <?= $selected_book_id === (int) $book['id'] ? 'selected' : ''; ?>>
											<?= html_escape($book['title']); ?><?= ! empty($book['call_number']) ? ' - ' . html_escape($book['call_number']) : ''; ?>
										</option>
									<?php endforeach; ?>
								</select>
								<div class="form-hint">Jika buku belum ada, tambahkan dulu di modul Katalog Buku.</div>
							</div>
							<div class="mb-3">
								<label class="form-label">File PDF <?= $is_edit ? 'baru (opsional)' : ''; ?></label>
								<input type="file" class="form-control" name="pdf_file" accept="application/pdf,.pdf" <?= $is_edit ? '' : 'required'; ?>>
								<div class="form-hint">File disimpan di <code>storage/ebooks/manual</code>, bukan folder publik.</div>
							</div>
							<?php if ($is_edit): ?>
								<div class="datagrid mb-3">
									<div class="datagrid-item">
										<div class="datagrid-title">File saat ini</div>
										<div class="datagrid-content"><?= html_escape($field('file_original_name') ?: basename((string) $field('file_path'))); ?></div>
									</div>
									<div class="datagrid-item">
										<div class="datagrid-title">Ukuran</div>
										<div class="datagrid-content"><?= number_format((int) $field('file_size'), 0, ',', '.'); ?> byte</div>
									</div>
									<div class="datagrid-item">
										<div class="datagrid-title">Path</div>
										<div class="datagrid-content text-truncate"><?= html_escape($field('file_path')); ?></div>
									</div>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>

				<div class="col-lg-5">
					<div class="card admin-card mb-3">
						<div class="card-header"><h2 class="card-title">Hak Akses Baca</h2></div>
						<div class="card-body">
							<div class="mb-3">
								<label class="form-label">Policy akses</label>
								<select class="form-select" name="access_policy" id="access_policy">
									<?php foreach ($policy_labels as $value => $label): ?>
										<option value="<?= $value; ?>" <?= $field('access_policy', 'online_only') === $value ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="mb-3">
								<label class="form-check form-switch">
									<input class="form-check-input" type="checkbox" name="is_downloadable" value="1" id="is_downloadable" <?= (int) $field('is_downloadable') === 1 ? 'checked' : ''; ?>>
									<span class="form-check-label">Izinkan download PDF utuh</span>
								</label>
								<div class="form-hint">Akan otomatis aktif hanya jika policy `Bebas download`. Untuk policy lain, server tetap menolak stream PDF utuh.</div>
							</div>
							<div class="mb-3">
								<label class="form-label">Status</label>
								<select class="form-select" name="status">
									<?php foreach ($status_labels as $value => $label): ?>
										<option value="<?= $value; ?>" <?= $field('status', 'draft') === $value ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
					</div>

					<div class="card admin-card">
						<div class="card-header"><h2 class="card-title">Hak Publikasi</h2></div>
						<div class="card-body">
							<div class="mb-3">
								<label class="form-label">Dasar hak</label>
								<select class="form-select" name="rights_basis">
									<?php foreach ($rights_labels as $value => $label): ?>
										<option value="<?= $value; ?>" <?= $field('rights_basis', 'unknown') === $value ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="mb-3">
								<label class="form-label">Pemegang hak / penerbit</label>
								<input type="text" class="form-control" name="rights_holder" value="<?= html_escape($field('rights_holder')); ?>">
							</div>
							<div class="mb-3">
								<label class="form-label">URL lisensi / sumber izin</label>
								<input type="url" class="form-control" name="license_url" value="<?= html_escape($field('license_url')); ?>">
							</div>
							<div class="row">
								<div class="col-md-6 mb-3">
									<label class="form-label">Nomor referensi izin</label>
									<input type="text" class="form-control" name="permission_reference" value="<?= html_escape($field('permission_reference')); ?>">
								</div>
								<div class="col-md-3 mb-3">
									<label class="form-label">Mulai</label>
									<input type="date" class="form-control" name="permission_starts_at" value="<?= html_escape($field('permission_starts_at')); ?>">
								</div>
								<div class="col-md-3 mb-3">
									<label class="form-label">Sampai</label>
									<input type="date" class="form-control" name="permission_ends_at" value="<?= html_escape($field('permission_ends_at')); ?>">
								</div>
							</div>
							<div class="mb-3">
								<label class="form-label">Catatan akses</label>
								<textarea class="form-control" name="access_notes" rows="4" placeholder="Catat batasan penerbit, wilayah akses, atau alasan policy."><?= html_escape($field('access_notes')); ?></textarea>
							</div>
						</div>
						<div class="card-footer text-end">
							<button type="submit" class="btn btn-primary">
								<i class="ti ti-device-floppy me-1"></i><?= $is_edit ? 'Simpan Perubahan' : 'Simpan Ebook'; ?>
							</button>
						</div>
					</div>
				</div>
			</div>
		<?= form_close(); ?>
	</div>
</div>

<script>
	(function () {
		const policy = document.getElementById('access_policy');
		const downloadable = document.getElementById('is_downloadable');
		if (!policy || !downloadable) return;
		function syncDownload() {
			if (policy.value === 'download_allowed') {
				downloadable.checked = true;
				downloadable.disabled = false;
				return;
			}
			downloadable.checked = false;
			downloadable.disabled = true;
		}
		policy.addEventListener('change', syncDownload);
		syncDownload();
	})();
</script>
