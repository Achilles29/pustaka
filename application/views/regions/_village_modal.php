<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$is_edit = ! empty($edit_village);
$action = $is_edit ? base_url('regions/villages/update/' . (int) $edit_village['id']) : base_url('regions/villages/store');
$value = function ($key, $default = '') use ($edit_village) {
	return $edit_village[$key] ?? $default;
};
?>
<div class="modal fade" id="village-modal" tabindex="-1" aria-hidden="true"<?= $is_edit ? ' data-pustaka-open-modal="1"' : ''; ?>>
	<div class="modal-dialog">
		<div class="modal-content">
			<?= form_open($action); ?>
				<div class="modal-header">
					<h2 class="modal-title"><?= $is_edit ? 'Edit Desa / Kelurahan' : 'Tambah Desa / Kelurahan'; ?></h2>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="mb-3">
						<label class="form-label">Kecamatan</label>
						<select class="form-select" name="district_id" required>
							<option value="">Pilih kecamatan</option>
							<?php foreach ($districts as $district): ?>
								<option value="<?= (int) $district['id']; ?>" <?= (int) $value('district_id', $filters['district_id'] ?? 0) === (int) $district['id'] ? 'selected' : ''; ?>>
									<?= html_escape(($district['full_code'] ?: $district['code']) . ' - ' . $district['name']); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="row">
						<div class="col-md-5 mb-3">
							<label class="form-label">Jenis</label>
							<select class="form-select" name="area_type">
								<option value="desa" <?= $value('area_type', 'desa') === 'desa' ? 'selected' : ''; ?>>Desa</option>
								<option value="kelurahan" <?= $value('area_type') === 'kelurahan' ? 'selected' : ''; ?>>Kelurahan</option>
							</select>
						</div>
						<div class="col-md-7 mb-3">
							<label class="form-label">Kode Wilayah</label>
							<input type="text" class="form-control" name="code" value="<?= html_escape($value('code')); ?>" required>
						</div>
					</div>
					<div class="mb-3">
						<label class="form-label">Nama Desa / Kelurahan</label>
						<input type="text" class="form-control" name="name" value="<?= html_escape($value('name')); ?>" required>
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
