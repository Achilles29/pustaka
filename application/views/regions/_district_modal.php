<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$is_edit = ! empty($edit_district);
$action = $is_edit ? base_url('regions/districts/update/' . (int) $edit_district['id']) : base_url('regions/districts/store');
$value = function ($key, $default = '') use ($edit_district) {
	return $edit_district[$key] ?? $default;
};
?>
<div class="modal fade" id="district-modal" tabindex="-1" aria-hidden="true"<?= $is_edit ? ' data-pustaka-open-modal="1"' : ''; ?>>
	<div class="modal-dialog">
		<div class="modal-content">
			<?= form_open($action); ?>
				<div class="modal-header">
					<h2 class="modal-title"><?= $is_edit ? 'Edit Kecamatan' : 'Tambah Kecamatan'; ?></h2>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-4 mb-3">
							<label class="form-label">Provinsi</label>
							<input class="form-control" value="33" readonly>
						</div>
						<div class="col-md-4 mb-3">
							<label class="form-label">Kab/Kota</label>
							<input class="form-control" value="17" readonly>
						</div>
						<div class="col-md-4 mb-3">
							<label class="form-label">Kode</label>
							<input type="text" class="form-control" name="code" value="<?= html_escape($value('code')); ?>" maxlength="2" required>
						</div>
					</div>
					<div class="mb-3">
						<label class="form-label">Nama Kecamatan</label>
						<input type="text" class="form-control" name="name" value="<?= html_escape($value('name')); ?>" required>
					</div>
					<div class="mb-3">
						<label class="form-label">Kode Lengkap</label>
						<input class="form-control" value="<?= html_escape($value('full_code', '33.17.xx')); ?>" readonly>
					</div>
					<label class="form-check">
						<input class="form-check-input" type="checkbox" name="is_active" value="1" <?= (int) $value('is_active', 1) === 1 ? 'checked' : ''; ?>>
						<span class="form-check-label">Aktif</span>
					</label>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
					<button type="submit" class="btn btn-primary">Simpan</button>
				</div>
			<?= form_close(); ?>
		</div>
	</div>
</div>
