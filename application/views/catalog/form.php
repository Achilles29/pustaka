<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$is_edit = ! empty($book);
$field = function ($key, $default = '') use ($book) {
	return $book[$key] ?? $default;
};
$author_text = implode('; ', array_map(function ($row) {
	return $row['name'];
}, $authors ?? []));
$subject_text = implode('; ', array_map(function ($row) {
	return $row['subject'];
}, $subjects ?? []));
$status_labels = [
	'draft' => 'Draft',
	'published' => 'Tayang',
	'hidden' => 'Disembunyikan',
];
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Katalog</div>
				<h1 class="page-title"><?= $is_edit ? 'Edit Katalog' : 'Tambah Katalog'; ?></h1>
			</div>
			<div class="col-auto ms-auto">
				<a href="<?= $is_edit ? base_url('catalog/detail/' . (int) $book['id']) : base_url('catalog'); ?>" class="btn btn-outline-secondary">
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

		<?= form_open($action); ?>
			<div class="row row-cards">
				<div class="col-lg-8">
					<div class="card admin-card">
						<div class="card-header"><h2 class="card-title">Bibliografi</h2></div>
						<div class="card-body">
							<div class="mb-3">
								<label class="form-label">Judul</label>
								<input type="text" class="form-control" name="title" value="<?= html_escape($field('title')); ?>" required>
							</div>
							<div class="mb-3">
								<label class="form-label">Subjudul</label>
								<input type="text" class="form-control" name="subtitle" value="<?= html_escape($field('subtitle')); ?>">
							</div>
							<div class="row">
								<div class="col-md-6 mb-3">
									<label class="form-label">Penanggung Jawab</label>
									<input type="text" class="form-control" name="statement_responsibility" value="<?= html_escape($field('statement_responsibility')); ?>">
								</div>
								<div class="col-md-6 mb-3">
									<label class="form-label">Penulis</label>
									<input type="text" class="form-control" name="authors" value="<?= html_escape($author_text ?: $field('statement_responsibility')); ?>" placeholder="Pisahkan dengan titik koma">
								</div>
							</div>
							<div class="row">
								<div class="col-md-4 mb-3">
									<label class="form-label">Edisi</label>
									<input type="text" class="form-control" name="edition" value="<?= html_escape($field('edition')); ?>">
								</div>
								<div class="col-md-4 mb-3">
									<label class="form-label">Tempat Terbit</label>
									<input type="text" class="form-control" name="publish_place" value="<?= html_escape($field('publish_place')); ?>">
								</div>
								<div class="col-md-4 mb-3">
									<label class="form-label">Tahun</label>
									<input type="text" class="form-control" name="publish_year" value="<?= html_escape($field('publish_year')); ?>">
								</div>
							</div>
							<div class="row">
								<div class="col-md-6 mb-3">
									<label class="form-label">Penerbit</label>
									<input type="text" class="form-control" name="publisher" value="<?= html_escape($field('publisher')); ?>">
								</div>
								<div class="col-md-6 mb-3">
									<label class="form-label">ISBN</label>
									<input type="text" class="form-control" name="isbn" value="<?= html_escape($field('isbn')); ?>">
								</div>
							</div>
							<div class="row">
								<div class="col-md-4 mb-3">
									<label class="form-label">Nomor Klasifikasi</label>
									<input type="text" class="form-control" name="classification" value="<?= html_escape($field('classification')); ?>">
								</div>
								<div class="col-md-4 mb-3">
									<label class="form-label">No. Panggil</label>
									<input type="text" class="form-control" name="call_number" value="<?= html_escape($field('call_number')); ?>">
								</div>
								<div class="col-md-4 mb-3">
									<label class="form-label">Bahasa</label>
									<input type="text" class="form-control" name="language" value="<?= html_escape($field('language')); ?>">
								</div>
							</div>
							<div class="mb-3">
								<label class="form-label">Deskripsi Fisik</label>
								<input type="text" class="form-control" name="physical_description" value="<?= html_escape($field('physical_description')); ?>">
							</div>
							<div class="mb-3">
								<label class="form-label">Subjek</label>
								<input type="text" class="form-control" name="subjects" value="<?= html_escape($subject_text); ?>" placeholder="Pisahkan dengan titik koma">
							</div>
							<div class="mb-3">
								<label class="form-label">Abstrak / Catatan</label>
								<textarea class="form-control" name="abstract" rows="5"><?= html_escape($field('abstract')); ?></textarea>
							</div>
						</div>
					</div>
				</div>

				<div class="col-lg-4">
					<div class="card admin-card">
						<div class="card-header"><h2 class="card-title">Status dan Aset</h2></div>
						<div class="card-body">
							<div class="alert alert-info">
								<div class="fw-semibold">Katalog adalah data induk buku.</div>
								<div class="small">Ebook/PDF bersifat opsional dan ditambahkan setelah katalog tersimpan melalui detail buku atau menu Reader PDF Aman.</div>
							</div>
							<div class="mb-3">
								<label class="form-label">Status</label>
								<select class="form-select" name="status">
									<?php foreach ($status_labels as $value => $label): ?>
										<option value="<?= $value; ?>" <?= $field('status', 'draft') === $value ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="mb-3">
								<label class="form-label">Kategori Isi</label>
								<select class="form-select" name="content_category_id">
									<option value="">Belum dipetakan</option>
									<?php foreach ($content_categories ?? [] as $category): ?>
										<option value="<?= (int) $category['id']; ?>" <?= (int) $field('content_category_id') === (int) $category['id'] ? 'selected' : ''; ?>><?= html_escape($category['name']); ?></option>
									<?php endforeach; ?>
								</select>
								<div class="form-hint">Dipakai untuk filter publik seperti fiksi, karya ilmiah, lokal Rembang.</div>
							</div>
							<div class="mb-3">
								<label class="form-label">Klasifikasi Isi</label>
								<select class="form-select" name="content_classification_id">
									<option value="">Belum dipetakan</option>
									<?php foreach ($classification_masters ?? [] as $classification): ?>
										<option value="<?= (int) $classification['id']; ?>" <?= (int) $field('content_classification_id') === (int) $classification['id'] ? 'selected' : ''; ?>><?= html_escape($classification['name']); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="mb-3">
								<label class="form-label">Nama File Cover</label>
								<input type="text" class="form-control" name="cover_path" value="<?= html_escape($field('cover_path')); ?>" placeholder="contoh: cover.jpg">
								<div class="form-hint">Untuk data INLISLite, file fisik tetap diproses dari modul Migrasi Aset.</div>
							</div>
							<?php if ($is_edit): ?>
								<div class="datagrid mb-3">
									<div class="datagrid-item">
										<div class="datagrid-title">Sumber</div>
										<div class="datagrid-content"><?= html_escape($field('source_system') ?: 'manual'); ?></div>
									</div>
									<div class="datagrid-item">
										<div class="datagrid-title">Status Cover</div>
										<div class="datagrid-content"><?= html_escape($field('cover_migration_status') ?: '-'); ?></div>
									</div>
								</div>
							<?php endif; ?>
						</div>
						<div class="card-footer text-end">
							<button type="submit" class="btn btn-primary">
								<i class="ti ti-device-floppy me-1"></i><?= $is_edit ? 'Simpan Perubahan' : 'Simpan Katalog'; ?>
							</button>
						</div>
					</div>
				</div>
			</div>
		<?= form_close(); ?>
	</div>
</div>
