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
$textbook_grade_ids = array_map('intval', array_column(($textbook_tags['grades'] ?? []), 'id'));
$textbook_subject_ids = array_map('intval', array_column(($textbook_tags['subjects'] ?? []), 'id'));
$status_labels = [
	'draft' => 'Draft',
	'published' => 'Tayang',
	'hidden' => 'Disembunyikan',
];
$url_path = function ($path) {
	return implode('/', array_map('rawurlencode', explode('/', str_replace('\\', '/', trim((string) $path, '/')))));
};
$cover_url = '';
if (! empty($book['cover_local_path'])) {
	$cover_url = base_url($url_path($book['cover_local_path']));
} elseif (! empty($book['cover_source_path'])) {
	$cover_url = base_url($url_path('assets/uploads/inlislite/source_mirror/' . $book['cover_source_path']));
}
?>
<style>.catalog-cover-upload{display:grid;grid-template-columns:4.5rem minmax(0,1fr);gap:.8rem;align-items:start}.catalog-cover-preview{display:grid;width:4.5rem;aspect-ratio:2/3;place-items:center;overflow:hidden;border:1px dashed #b9d6ea;border-radius:10px;background:#f1f8fd;color:#5c88aa}.catalog-cover-preview img{width:100%;height:100%;object-fit:cover}.catalog-cover-preview i{font-size:1.5rem}.catalog-cover-upload .form-hint{margin-top:.35rem}@media(max-width:575px){.catalog-cover-upload{grid-template-columns:1fr}.catalog-cover-preview{width:5rem}}</style>
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

		<?= form_open_multipart($action); ?>
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
								<label class="form-label">Jenis Koleksi</label>
								<select class="form-select" name="collection_type">
									<option value="">Pilih jenis koleksi</option>
									<?php foreach (($collection_types ?? []) as $type): ?>
										<option value="<?= html_escape($type['name']); ?>" <?= $field('primary_collection_type') === $type['name'] ? 'selected' : ''; ?>><?= html_escape($type['code'] . ' — ' . $type['name']); ?></option>
									<?php endforeach; ?>
								</select>
								<div class="d-flex justify-content-between gap-2 mt-1">
									<div class="form-hint">Disimpan sebagai jenis eksemplar utama pada katalog. Satu judul dapat mempunyai jenis/eksemplar lain setelah tersimpan.</div>
									<a href="<?= base_url('catalog/masters?tab=collection_types'); ?>" class="small fw-semibold text-primary text-nowrap"><i class="ti ti-settings me-1"></i>Kelola jenis</a>
								</div>
							</div>
							<div class="mb-3">
								<label class="form-label">Kategori Isi</label>
								<select class="form-select" name="content_category_id">
									<option value="">Belum dipetakan</option>
									<?php foreach ($content_categories ?? [] as $category): ?>
										<option value="<?= (int) $category['id']; ?>" <?= (int) $field('content_category_id') === (int) $category['id'] ? 'selected' : ''; ?>><?= html_escape($category['code'] . ' — ' . $category['name']); ?></option>
									<?php endforeach; ?>
								</select>
								<div class="form-hint">Dipakai untuk filter publik seperti fiksi, karya ilmiah, lokal Rembang.</div>
							</div>
							<div class="mb-3">
								<label class="form-label">Klasifikasi Isi</label>
								<select class="form-select" name="content_classification_id">
									<option value="">Belum dipetakan</option>
									<?php foreach ($classification_masters ?? [] as $classification): ?>
										<option value="<?= (int) $classification['id']; ?>" <?= (int) $field('content_classification_id') === (int) $classification['id'] ? 'selected' : ''; ?>><?= html_escape($classification['code'] . ' — ' . $classification['name']); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="mb-3 textbook-tag-fields">
								<div class="d-flex align-items-start justify-content-between gap-2 mb-2">
									<div>
										<label class="form-label mb-0">Tag Buku Pelajaran</label>
										<div class="form-hint">Aktifkan jika katalog ini termasuk Buku Pelajaran. Satu judul dapat memiliki lebih dari satu tag.</div>
									</div>
									<span class="badge bg-azure-lt">Opsional</span>
								</div>
								<label class="form-label small">Jenjang / kelas</label>
								<select class="form-select mb-2" name="textbook_grade_ids[]" multiple size="5" aria-label="Pilih jenjang atau kelas buku pelajaran">
									<?php foreach (($textbook_grade_levels ?? []) as $grade): ?>
										<option value="<?= (int) $grade['id']; ?>" <?= in_array((int) $grade['id'], $textbook_grade_ids, true) ? 'selected' : ''; ?>><?= html_escape($grade['name']); ?></option>
									<?php endforeach; ?>
								</select>
								<label class="form-label small">Mata pelajaran</label>
								<select class="form-select" name="textbook_subject_ids[]" multiple size="6" aria-label="Pilih mata pelajaran buku pelajaran">
									<?php foreach (($textbook_subject_options ?? []) as $subject): ?>
										<option value="<?= (int) $subject['id']; ?>" <?= in_array((int) $subject['id'], $textbook_subject_ids, true) ? 'selected' : ''; ?>><?= html_escape($subject['name']); ?></option>
									<?php endforeach; ?>
								</select>
								<div class="form-hint">Gunakan Ctrl/Cmd saat memilih beberapa pilihan. Tag ini menjadi filter pada halaman Buku Pelajaran.</div>
							</div>
							<div class="mb-3">
								<label class="form-label">Cover Buku</label>
								<div class="catalog-cover-upload"><div class="catalog-cover-preview" id="catalog-cover-preview"><?php if ($cover_url): ?><img src="<?= html_escape($cover_url); ?>" alt="Preview cover"><?php else: ?><i class="ti ti-photo"></i><?php endif; ?></div><div><input type="file" class="form-control" name="cover_file" id="catalog-cover-file" accept="image/jpeg,image/png,image/webp"><div class="form-hint">JPG, PNG, atau WebP; maksimal 5 MB. Cover baru menggantikan cover yang aktif saat ini.</div><details class="mt-2"><summary class="small text-secondary">Referensi file cover hasil migrasi</summary><input type="text" class="form-control form-control-sm mt-2" name="cover_path" value="<?= html_escape($field('cover_path')); ?>" placeholder="contoh: cover.jpg"><div class="form-hint">Gunakan hanya untuk referensi cover legacy yang akan diproses oleh modul Migrasi Aset.</div></details></div></div>
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
<script>
(function(){var input=document.getElementById('catalog-cover-file'),preview=document.getElementById('catalog-cover-preview');if(!input||!preview)return;input.addEventListener('change',function(){var file=this.files&&this.files[0];if(!file)return;if(!/^image\/(jpeg|png|webp)$/.test(file.type)){preview.innerHTML='<i class="ti ti-photo-off"></i>';return;}var reader=new FileReader();reader.onload=function(event){preview.innerHTML='<img src="'+event.target.result+'" alt="Preview cover baru">';};reader.readAsDataURL(file);});})();
</script>
