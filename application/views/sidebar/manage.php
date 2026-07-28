<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$is_edit = ! empty($edit_menu);
$form_action = $is_edit ? base_url('sidebar/manage/update/' . (int) $edit_menu['id']) : base_url('sidebar/manage/store');
$value = function ($key, $default = '') use ($edit_menu) {
	return $edit_menu[$key] ?? $default;
};
$render_tree = function ($items, $depth = 0) use (&$render_tree) {
	foreach ($items as $item) {
		echo '<div class="menu-tree-row" style="padding-left:' . ((int) $depth * 18) . 'px">';
		echo '<span class="badge bg-secondary-lt me-2">' . (int) $item['sort_order'] . '</span>';
		echo '<span class="fw-semibold">' . html_escape($item['title']) . '</span>';
		echo '<span class="text-secondary ms-2">' . html_escape($item['menu_key']) . '</span>';
		if (empty($item['is_active'])) {
			echo '<span class="badge bg-red-lt ms-2">nonaktif</span>';
		}
		echo '</div>';
		if (! empty($item['children'])) {
			$render_tree($item['children'], $depth + 1);
		}
	}
};
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Sistem</div>
				<h1 class="page-title">Manajemen Sidebar</h1>
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
				<div class="card">
					<div class="card-header">
						<h2 class="card-title"><?= $is_edit ? 'Edit Menu' : 'Tambah Menu'; ?></h2>
					</div>
					<div class="card-body">
						<?= form_open($form_action); ?>
							<div class="mb-3">
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
							<div class="mb-3">
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
							<div class="mb-3">
								<label class="form-label">Menu Key</label>
								<input type="text" class="form-control" name="menu_key" value="<?= html_escape($value('menu_key')); ?>" required>
							</div>
							<div class="mb-3">
								<label class="form-label">Judul</label>
								<input type="text" class="form-control" name="title" value="<?= html_escape($value('title')); ?>" required>
							</div>
							<div class="mb-3">
								<label class="form-label">URL</label>
								<input type="text" class="form-control" name="url" value="<?= html_escape($value('url')); ?>" placeholder="contoh: catalog">
							</div>
							<div class="row">
								<div class="col-md-6 mb-3">
									<label class="form-label">Ikon</label>
									<input type="text" class="form-control" name="icon" value="<?= html_escape($value('icon')); ?>" placeholder="ti ti-books">
								</div>
								<div class="col-md-6 mb-3">
									<label class="form-label">Urutan</label>
									<input type="number" class="form-control" name="sort_order" value="<?= html_escape($value('sort_order', 100)); ?>">
								</div>
							</div>
							<label class="form-check">
								<input class="form-check-input" type="checkbox" name="is_visible" value="1" <?= (int) $value('is_visible', 1) === 1 ? 'checked' : ''; ?>>
								<span class="form-check-label">Tampilkan di sidebar</span>
							</label>
							<label class="form-check mb-3">
								<input class="form-check-input" type="checkbox" name="is_active" value="1" <?= (int) $value('is_active', 1) === 1 ? 'checked' : ''; ?>>
								<span class="form-check-label">Aktif</span>
							</label>
							<div class="btn-list">
								<button type="submit" class="btn btn-primary"><?= $is_edit ? 'Simpan Perubahan' : 'Tambah Menu'; ?></button>
								<?php if ($is_edit): ?>
									<a href="<?= base_url('sidebar/manage'); ?>" class="btn btn-outline-secondary">Batal</a>
								<?php endif; ?>
							</div>
						<?= form_close(); ?>
					</div>
				</div>
			</div>

			<div class="col-lg-8">
				<div class="card mb-3">
					<div class="card-header">
						<h2 class="card-title">Preview Struktur</h2>
					</div>
					<div class="card-body">
						<div class="menu-tree">
							<?php $render_tree($menu_tree); ?>
						</div>
					</div>
				</div>

				<div class="card">
					<div class="card-header">
						<h2 class="card-title">Data Menu</h2>
					</div>
					<div class="table-responsive">
						<table class="table table-vcenter card-table">
							<thead>
								<tr>
									<th>Judul</th>
									<th>Key</th>
									<th>Halaman</th>
									<th>Status</th>
									<th class="w-1">Aksi</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($menus as $menu): ?>
									<tr>
										<td><?= html_escape($menu['title']); ?></td>
										<td><code><?= html_escape($menu['menu_key']); ?></code></td>
										<td><?= html_escape($menu['page_code'] ?: '-'); ?></td>
										<td>
											<span class="badge <?= (int) $menu['is_active'] === 1 ? 'bg-green-lt' : 'bg-red-lt'; ?>">
												<?= (int) $menu['is_active'] === 1 ? 'aktif' : 'nonaktif'; ?>
											</span>
										</td>
										<td>
											<div class="btn-list flex-nowrap">
												<a class="btn btn-sm btn-outline-primary" href="<?= base_url('sidebar/manage?edit_id=' . (int) $menu['id']); ?>">Edit</a>
												<?= form_open('sidebar/manage/toggle/' . (int) $menu['id'], ['class' => 'd-inline']); ?>
													<button class="btn btn-sm btn-outline-secondary" type="submit"><?= (int) $menu['is_active'] === 1 ? 'Nonaktifkan' : 'Aktifkan'; ?></button>
												<?= form_close(); ?>
											</div>
										</td>
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
