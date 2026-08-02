<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$is_edit = ! empty($item);
$modal_id = $is_edit ? 'item-modal-' . (int) $item['id'] : 'item-modal-create';
$action_url = $is_edit
	? 'catalog/items/update/' . (int) $book['id'] . '/' . (int) $item['id']
	: 'catalog/items/store/' . (int) $book['id'];
$field = function ($key, $default = '') use ($item) {
	return $item[$key] ?? $default;
};
$select_options = function ($options, $selected) {
	foreach ($options as $option) {
		$value = (string) $option['source_id'];
		$label = trim(($option['code'] ? $option['code'] . ' - ' : '') . $option['name']);
		echo '<option value="' . html_escape($value) . '"' . ((string) $selected === $value ? ' selected' : '') . '>' . html_escape($label) . '</option>';
	}
};
$status_options = [
	'available' => 'Tersedia',
	'loaned' => 'Dipinjam',
	'missing' => 'Hilang',
	'damaged' => 'Rusak',
	'unknown' => 'Belum Dipetakan',
];
?>
<div class="modal fade" id="<?= html_escape($modal_id); ?>" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-scrollable">
		<div class="modal-content">
			<?= form_open($action_url); ?>
				<div class="modal-header">
					<div>
						<h2 class="modal-title"><?= $is_edit ? 'Edit Eksemplar' : 'Tambah Eksemplar'; ?></h2>
						<div class="text-secondary small"><?= html_escape($book['title']); ?></div>
					</div>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="row g-3">
						<div class="col-md-4">
							<label class="form-label">Barcode</label>
							<input type="text" class="form-control" name="barcode" value="<?= html_escape($field('barcode')); ?>" placeholder="Nomor barcode">
						</div>
						<div class="col-md-4">
							<label class="form-label">No Induk</label>
							<input type="text" class="form-control" name="inventory_number" value="<?= html_escape($field('inventory_number')); ?>" placeholder="Inventaris">
						</div>
						<div class="col-md-4">
							<label class="form-label">Kode Item</label>
							<input type="text" class="form-control" name="item_code" value="<?= html_escape($field('item_code')); ?>" placeholder="Kode internal">
						</div>
						<div class="col-md-4">
							<label class="form-label">No Panggil</label>
							<input type="text" class="form-control" name="call_number" value="<?= html_escape($field('call_number', $book['call_number'] ?? '')); ?>" placeholder="Klasifikasi rak">
						</div>
						<div class="col-md-4">
							<label class="form-label">Status Aplikasi</label>
							<select class="form-select" name="status">
								<?php foreach ($status_options as $value => $label): ?>
									<option value="<?= $value; ?>" <?= $field('status', 'unknown') === $value ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-md-4">
							<label class="form-label">Status INLISLite</label>
							<select class="form-select" name="source_status_id">
								<option value="">Tidak dipilih</option>
								<?php $select_options($reference_options['statuses'] ?? [], $field('source_status_id')); ?>
							</select>
						</div>
						<div class="col-md-6">
							<label class="form-label">Perpustakaan Sumber</label>
							<select class="form-select" name="source_location_library_id">
								<option value="">Tidak dipilih</option>
								<?php $select_options($reference_options['location_libraries'] ?? [], $field('source_location_library_id')); ?>
							</select>
						</div>
						<div class="col-md-6">
							<label class="form-label">Ruang / Lokasi Koleksi</label>
							<select class="form-select" name="source_location_id">
								<option value="">Tidak dipilih</option>
								<?php $select_options($reference_options['locations'] ?? [], $field('source_location_id')); ?>
							</select>
						</div>
						<div class="col-md-4">
							<label class="form-label">Kategori Koleksi</label>
							<select class="form-select" name="source_category_id">
								<option value="">Tidak dipilih</option>
								<?php $select_options($reference_options['categories'] ?? [], $field('source_category_id')); ?>
							</select>
						</div>
						<div class="col-md-4">
							<label class="form-label">Aturan Pinjam</label>
							<select class="form-select" name="source_rule_id">
								<option value="">Tidak dipilih</option>
								<?php $select_options($reference_options['rules'] ?? [], $field('source_rule_id')); ?>
							</select>
						</div>
						<div class="col-md-4">
							<label class="form-label">Media</label>
							<select class="form-select" name="source_media_id">
								<option value="">Tidak dipilih</option>
								<?php $select_options($reference_options['medias'] ?? [], $field('source_media_id')); ?>
							</select>
						</div>
						<div class="col-md-6">
							<label class="form-label">Sumber Pengadaan</label>
							<select class="form-select" name="source_collection_source_id">
								<option value="">Tidak dipilih</option>
								<?php $select_options($reference_options['sources'] ?? [], $field('source_collection_source_id')); ?>
							</select>
						</div>
						<div class="col-md-6 d-flex align-items-end">
							<label class="form-check form-switch mb-2">
								<input class="form-check-input" type="checkbox" name="is_public" value="1" <?= (int) $field('is_public', 1) === 1 ? 'checked' : ''; ?>>
								<span class="form-check-label">Tampil di OPAC / katalog publik</span>
							</label>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
					<button type="submit" class="btn btn-primary">
						<i class="ti ti-device-floppy me-1"></i>Simpan
					</button>
				</div>
			<?= form_close(); ?>
		</div>
	</div>
</div>
