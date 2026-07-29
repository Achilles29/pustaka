<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$is_edit = ! empty($edit_role);
$action = $is_edit ? base_url('rbac/roles/update/' . (int) $edit_role['id']) : base_url('rbac/roles/store');
$value = function ($key, $default = '') use ($edit_role) {
	return $edit_role[$key] ?? $default;
};
?>
<div class="modal fade" id="role-modal" tabindex="-1" aria-hidden="true"<?= $is_edit ? ' data-pustaka-open-modal="1"' : ''; ?>>
	<div class="modal-dialog">
		<div class="modal-content">
			<?= form_open($action); ?>
				<div class="modal-header">
					<h2 class="modal-title"><?= $is_edit ? 'Edit Tipe User' : 'Tambah Tipe User'; ?></h2>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-5 mb-3">
							<label class="form-label">Kode Role</label>
							<input type="text" class="form-control" name="code" value="<?= html_escape($value('code')); ?>" placeholder="ADMIN_DESA" required>
						</div>
						<div class="col-md-7 mb-3">
							<label class="form-label">Nama Tipe User</label>
							<input type="text" class="form-control" name="name" value="<?= html_escape($value('name')); ?>" placeholder="Admin Desa" required>
						</div>
					</div>
					<div class="mb-3">
						<label class="form-label">Deskripsi</label>
						<input type="text" class="form-control" name="description" value="<?= html_escape($value('description')); ?>">
					</div>
					<div class="row">
						<div class="col-md-6 mb-3">
							<label class="form-label">Scope</label>
							<select class="form-select" name="scope_type">
								<?php foreach (['global' => 'Global', 'library' => 'Perpustakaan/Unit', 'self' => 'Diri sendiri'] as $key => $label): ?>
									<option value="<?= $key; ?>" <?= $value('scope_type', 'self') === $key ? 'selected' : ''; ?>><?= $label; ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-md-6 mb-3">
							<label class="form-label">Level</label>
							<input type="number" class="form-control" name="level" value="<?= html_escape($value('level', 100)); ?>" min="1" required>
						</div>
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
