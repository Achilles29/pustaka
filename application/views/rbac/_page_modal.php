<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$is_edit = ! empty($edit_page);
$action = $is_edit ? base_url('rbac/pages/update/' . (int) $edit_page['id']) : base_url('rbac/pages/store');
$value = function ($key, $default = '') use ($edit_page) {
	return $edit_page[$key] ?? $default;
};
?>
<div class="modal fade" id="page-modal" tabindex="-1" aria-hidden="true"<?= $is_edit ? ' data-pustaka-open-modal="1"' : ''; ?>>
	<div class="modal-dialog">
		<div class="modal-content">
			<?= form_open($action); ?>
				<div class="modal-header">
					<h2 class="modal-title"><?= $is_edit ? 'Edit Halaman' : 'Tambah Halaman'; ?></h2>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="mb-3">
						<label class="form-label">Page Code</label>
						<input type="text" class="form-control" name="code" value="<?= html_escape($value('code')); ?>" placeholder="contoh: catalog.index" required>
					</div>
					<div class="mb-3">
						<label class="form-label">Module</label>
						<input type="text" class="form-control" name="module" value="<?= html_escape($value('module')); ?>" placeholder="contoh: catalog" required>
					</div>
					<div class="mb-3">
						<label class="form-label">Judul</label>
						<input type="text" class="form-control" name="title" value="<?= html_escape($value('title')); ?>" required>
					</div>
					<div class="mb-3">
						<label class="form-label">Route</label>
						<input type="text" class="form-control" name="route" value="<?= html_escape($value('route')); ?>" placeholder="contoh: catalog" required>
					</div>
					<div class="mb-3">
						<label class="form-label">Deskripsi</label>
						<textarea class="form-control" name="description" rows="3"><?= html_escape($value('description')); ?></textarea>
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
