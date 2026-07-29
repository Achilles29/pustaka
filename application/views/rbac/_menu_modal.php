<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$is_edit = ! empty($edit_menu);
$form_action = $is_edit ? base_url('rbac/sidebar/update/' . (int) $edit_menu['id']) : base_url('rbac/sidebar/store');
$value = function ($key, $default = '') use ($edit_menu) {
	return $edit_menu[$key] ?? $default;
};
?>
<div class="modal fade" id="menu-modal" tabindex="-1" aria-hidden="true"<?= $is_edit ? ' data-pustaka-open-modal="1"' : ''; ?>>
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<?= form_open($form_action); ?>
				<div class="modal-header">
					<h2 class="modal-title"><?= $is_edit ? 'Edit Menu' : 'Tambah Menu'; ?></h2>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-6 mb-3">
							<label class="form-label">Parent</label>
							<select name="parent_id" class="form-select">
								<option value="">Menu utama</option>
								<?php foreach ($parents as $parent): ?>
									<option value="<?= (int) $parent['id']; ?>" <?= (int) $value('parent_id') === (int) $parent['id'] ? 'selected' : ''; ?>>
										<?= html_escape($parent['title']); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-md-6 mb-3">
							<label class="form-label">Halaman Permission</label>
							<select name="page_id" class="form-select">
								<option value="">Grup menu tanpa halaman</option>
								<?php foreach ($pages as $page): ?>
									<option value="<?= (int) $page['id']; ?>" <?= (int) $value('page_id') === (int) $page['id'] ? 'selected' : ''; ?>>
										<?= html_escape($page['module'] . ' - ' . $page['title']); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6 mb-3">
							<label class="form-label">Menu Key</label>
							<input type="text" class="form-control" name="menu_key" value="<?= html_escape($value('menu_key')); ?>" required>
						</div>
						<div class="col-md-6 mb-3">
							<label class="form-label">Judul</label>
							<input type="text" class="form-control" name="title" value="<?= html_escape($value('title')); ?>" required>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6 mb-3">
							<label class="form-label">URL</label>
							<input type="text" class="form-control" name="url" value="<?= html_escape($value('url')); ?>" placeholder="contoh: catalog">
						</div>
						<div class="col-md-3 mb-3">
							<label class="form-label">Ikon</label>
							<input type="text" class="form-control" name="icon" value="<?= html_escape($value('icon', 'ti ti-circle')); ?>" placeholder="ti ti-books">
						</div>
						<div class="col-md-3 mb-3">
							<label class="form-label">Urutan</label>
							<input type="number" class="form-control" name="sort_order" value="<?= html_escape($value('sort_order', 100)); ?>">
						</div>
					</div>
					<label class="form-check">
						<input class="form-check-input" type="checkbox" name="is_visible" value="1" <?= (int) $value('is_visible', 1) === 1 ? 'checked' : ''; ?>>
						<span class="form-check-label">Tampilkan di sidebar</span>
					</label>
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
