<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$target_labels = [
	'book' => 'Buku',
	'category' => 'Kategori',
];
$highlight_labels = [
	'featured' => 'Pilihan Pustakawan',
	'new_arrival' => 'Baru Datang',
	'digital' => 'Buku Digital',
	'local' => 'Rembang/Lokal',
	'recommendation' => 'Rekomendasi',
];
$status_labels = [
	'active' => 'Aktif',
	'inactive' => 'Nonaktif',
];
$filters = $filters ?? [];
$pagination = $pagination ?? ['page' => 1, 'total_pages' => 1, 'total_rows' => 0, 'per_page' => 25, 'offset' => 0];
$query_base = $_GET;
unset($query_base['page'], $query_base['edit_id']);
$page_url = function ($page) use ($query_base) {
	return base_url('catalog/highlights?' . http_build_query(array_merge($query_base, ['page' => $page])));
};
$edit_highlight = isset($edit_highlight) ? $edit_highlight : null;
$is_edit = ! empty($edit_highlight);
$form_highlight = $edit_highlight ?: [
	'target_type' => 'book',
	'book_id' => '',
	'content_category_id' => '',
	'highlight_type' => 'featured',
	'label' => '',
	'title_override' => '',
	'summary' => '',
	'sort_order' => 100,
	'is_active' => 1,
	'starts_at' => '',
	'ends_at' => '',
];
$value = function ($key, $default = '') use ($form_highlight) {
	return $form_highlight[$key] ?? $default;
};
$datetime_value = function ($key) use ($value) {
	$raw = $value($key);
	return $raw ? date('Y-m-d\TH:i', strtotime($raw)) : '';
};
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Koleksi & Katalog</div>
				<h1 class="page-title">Highlight Katalog</h1>
			</div>
			<div class="col-auto ms-auto">
				<div class="btn-list">
					<a href="<?= base_url('catalog'); ?>" class="btn btn-outline-primary">
						<i class="ti ti-books me-1"></i>Katalog
					</a>
					<?php if (! empty($can_create_highlight)): ?>
						<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#highlight-modal">
							<i class="ti ti-plus me-1"></i>Tambah Highlight
						</button>
					<?php endif; ?>
				</div>
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

		<div class="card admin-card data-workspace">
			<div class="card-header workspace-header">
				<div>
					<h2 class="card-title">Daftar Highlight</h2>
					<div class="text-secondary small">Atur buku dan kategori yang muncul sebagai rekomendasi di dashboard pemustaka.</div>
				</div>
			</div>
			<div class="card-body workspace-filter">
				<?= form_open('catalog/highlights', ['method' => 'get', 'class' => 'row g-2 align-items-end']); ?>
					<div class="col-lg-3 col-md-6">
						<label class="form-label">Cari</label>
						<input type="text" class="form-control" name="q" value="<?= html_escape($filters['q'] ?? ''); ?>" placeholder="Judul, kategori, label">
					</div>
					<div class="col-lg-2 col-md-6">
						<label class="form-label">Target</label>
						<select class="form-select" name="target_type">
							<option value="">Semua</option>
							<?php foreach ($target_labels as $key => $label): ?>
								<option value="<?= $key; ?>" <?= ($filters['target_type'] ?? '') === $key ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-lg-2 col-md-6">
						<label class="form-label">Jenis</label>
						<select class="form-select" name="highlight_type">
							<option value="">Semua</option>
							<?php foreach ($highlight_labels as $key => $label): ?>
								<option value="<?= $key; ?>" <?= ($filters['highlight_type'] ?? '') === $key ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-lg-2 col-md-6">
						<label class="form-label">Status</label>
						<select class="form-select" name="status">
							<option value="">Semua</option>
							<?php foreach ($status_labels as $key => $label): ?>
								<option value="<?= $key; ?>" <?= ($filters['status'] ?? '') === $key ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-lg-1 col-md-4">
						<label class="form-label">Baris</label>
						<select class="form-select" name="per_page">
							<?php foreach ([10, 25, 50, 100] as $limit): ?>
								<option value="<?= $limit; ?>" <?= (int) ($filters['per_page'] ?? 25) === $limit ? 'selected' : ''; ?>><?= $limit; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-lg-2 col-md-8">
						<div class="btn-list flex-nowrap">
							<button type="submit" class="btn btn-outline-primary w-100"><i class="ti ti-filter me-1"></i>Filter</button>
							<a href="<?= base_url('catalog/highlights'); ?>" class="btn btn-outline-secondary" title="Reset filter" aria-label="Reset filter"><i class="ti ti-refresh"></i></a>
						</div>
					</div>
				<?= form_close(); ?>
			</div>

			<div class="table-responsive admin-table-scroll">
				<table class="table table-vcenter card-table table-admin-list">
					<thead>
						<tr>
							<th>Highlight</th>
							<th>Target</th>
							<th>Jenis</th>
							<th>Periode</th>
							<th>Urutan</th>
							<th>Status</th>
							<th class="w-1">Aksi</th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($highlights)): ?>
							<tr><td colspan="7" class="text-center text-secondary py-4">Belum ada highlight katalog sesuai filter.</td></tr>
						<?php endif; ?>
						<?php foreach ($highlights as $highlight): ?>
							<?php
								$target_name = $highlight['target_type'] === 'category'
									? ($highlight['target_category_name'] ?: 'Kategori #' . $highlight['content_category_id'])
									: ($highlight['book_title'] ?: 'Buku #' . $highlight['book_id']);
								$target_meta = $highlight['target_type'] === 'category'
									? 'Kategori katalog'
									: trim(($highlight['statement_responsibility'] ?: '-') . ' ' . ($highlight['publish_year'] ?: ''));
							?>
							<tr>
								<td>
									<div class="fw-semibold"><?= html_escape($highlight['title_override'] ?: $target_name); ?></div>
									<div class="text-secondary small"><?= html_escape($highlight['label'] ?: ($highlight_labels[$highlight['highlight_type']] ?? 'Highlight')); ?></div>
									<?php if (! empty($highlight['summary'])): ?>
										<div class="text-secondary small text-truncate" style="max-width: 26rem;"><?= html_escape($highlight['summary']); ?></div>
									<?php endif; ?>
								</td>
								<td>
									<span class="badge bg-blue-lt"><?= html_escape($target_labels[$highlight['target_type']] ?? $highlight['target_type']); ?></span>
									<div class="small mt-1"><?= html_escape($target_name); ?></div>
									<div class="text-secondary small"><?= html_escape($target_meta); ?></div>
								</td>
								<td><span class="badge bg-cyan-lt"><?= html_escape($highlight_labels[$highlight['highlight_type']] ?? $highlight['highlight_type']); ?></span></td>
								<td>
									<div class="small">Mulai: <?= html_escape($highlight['starts_at'] ?: 'Sekarang'); ?></div>
									<div class="small text-secondary">Selesai: <?= html_escape($highlight['ends_at'] ?: 'Tanpa batas'); ?></div>
								</td>
								<td><span class="badge bg-secondary-lt"><?= (int) $highlight['sort_order']; ?></span></td>
								<td><span class="badge <?= (int) $highlight['is_active'] === 1 ? 'bg-green-lt' : 'bg-secondary-lt'; ?>"><?= (int) $highlight['is_active'] === 1 ? 'Aktif' : 'Nonaktif'; ?></span></td>
								<td>
									<div class="btn-list flex-nowrap">
										<?php if (! empty($can_edit_highlight)): ?>
											<a href="<?= base_url('catalog/highlights?edit_id=' . (int) $highlight['id']); ?>" class="btn btn-sm btn-action btn-action-primary">
												<i class="ti ti-edit"></i><span>Edit</span>
											</a>
										<?php endif; ?>
										<?php if (! empty($can_delete_highlight)): ?>
											<?= form_open('catalog/highlights/delete/' . (int) $highlight['id'], ['class' => 'd-inline', 'onsubmit' => "return confirm('Hapus highlight ini?');"]); ?>
												<button type="submit" class="btn btn-sm btn-outline-danger">
													<i class="ti ti-trash"></i><span>Hapus</span>
												</button>
											<?= form_close(); ?>
										<?php endif; ?>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<div class="card-footer responsive-footer">
				<p class="m-0 text-secondary">Menampilkan <?= number_format($pagination['total_rows'] > 0 ? $pagination['offset'] + 1 : 0, 0, ',', '.'); ?>-<?= number_format(min($pagination['offset'] + $pagination['per_page'], $pagination['total_rows']), 0, ',', '.'); ?> dari <?= number_format($pagination['total_rows'], 0, ',', '.'); ?> data</p>
				<ul class="pagination m-0">
					<li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : ''; ?>"><a class="page-link" href="<?= $page_url(max(1, $pagination['page'] - 1)); ?>">Prev</a></li>
					<?php for ($i = max(1, $pagination['page'] - 2); $i <= min($pagination['total_pages'], $pagination['page'] + 2); $i++): ?>
						<li class="page-item <?= $i === (int) $pagination['page'] ? 'active' : ''; ?>"><a class="page-link" href="<?= $page_url($i); ?>"><?= $i; ?></a></li>
					<?php endfor; ?>
					<li class="page-item <?= $pagination['page'] >= $pagination['total_pages'] ? 'disabled' : ''; ?>"><a class="page-link" href="<?= $page_url(min($pagination['total_pages'], $pagination['page'] + 1)); ?>">Next</a></li>
				</ul>
			</div>
		</div>
	</div>
</div>

<div class="modal modal-blur fade" id="highlight-modal" tabindex="-1" aria-hidden="true"<?= $is_edit ? ' data-pustaka-open-modal="1"' : ''; ?>>
	<div class="modal-dialog modal-lg modal-dialog-centered">
		<div class="modal-content">
			<?= form_open($is_edit ? 'catalog/highlights/update/' . (int) $edit_highlight['id'] : 'catalog/highlights/store', ['id' => 'highlight-form']); ?>
				<div class="modal-header">
					<h5 class="modal-title"><?= $is_edit ? 'Edit Highlight' : 'Tambah Highlight'; ?></h5>
					<a href="<?= base_url('catalog/highlights'); ?>" class="btn-close" aria-label="Tutup"></a>
				</div>
				<div class="modal-body">
					<div class="row g-3">
						<div class="col-md-4">
							<label class="form-label">Target</label>
							<select class="form-select" name="target_type" id="highlight-target-type">
								<?php foreach ($target_labels as $key => $label): ?>
									<option value="<?= $key; ?>" <?= $value('target_type', 'book') === $key ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-md-4">
							<label class="form-label">Jenis Highlight</label>
							<select class="form-select" name="highlight_type">
								<?php foreach ($highlight_labels as $key => $label): ?>
									<option value="<?= $key; ?>" <?= $value('highlight_type', 'featured') === $key ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-md-4">
							<label class="form-label">Urutan</label>
							<input type="number" class="form-control" name="sort_order" min="0" max="9999" value="<?= html_escape($value('sort_order', 100)); ?>">
						</div>
						<div class="col-12 highlight-book-field">
							<label class="form-label">Buku</label>
							<select class="form-select" name="book_id">
								<option value="">Belum dipilih</option>
								<?php foreach ($book_options as $book): ?>
									<?php
										$book_label = $book['title'];
										if (! empty($book['statement_responsibility'])) {
											$book_label .= ' - ' . $book['statement_responsibility'];
										}
										if (! empty($book['publish_year'])) {
											$book_label .= ' (' . $book['publish_year'] . ')';
										}
									?>
									<option value="<?= (int) $book['id']; ?>" <?= (int) $value('book_id') === (int) $book['id'] ? 'selected' : ''; ?>><?= html_escape($book_label); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-12 highlight-category-field">
							<label class="form-label">Kategori Katalog</label>
							<select class="form-select" name="content_category_id">
								<option value="">Belum dipilih</option>
								<?php foreach ($content_categories as $category): ?>
									<option value="<?= (int) $category['id']; ?>" <?= (int) $value('content_category_id') === (int) $category['id'] ? 'selected' : ''; ?>><?= html_escape($category['name']); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-md-6">
							<label class="form-label">Label Pendek</label>
							<input type="text" class="form-control" name="label" maxlength="120" value="<?= html_escape($value('label')); ?>" placeholder="Contoh: Pilihan minggu ini">
						</div>
						<div class="col-md-6">
							<label class="form-label">Judul Tampilan</label>
							<input type="text" class="form-control" name="title_override" maxlength="180" value="<?= html_escape($value('title_override')); ?>" placeholder="Opsional">
						</div>
						<div class="col-12">
							<label class="form-label">Ringkasan</label>
							<textarea class="form-control" name="summary" rows="3" maxlength="255" placeholder="Kalimat pendek untuk dashboard member."><?= html_escape($value('summary')); ?></textarea>
						</div>
						<div class="col-md-6">
							<label class="form-label">Tayang Mulai</label>
							<input type="datetime-local" class="form-control" name="starts_at" value="<?= html_escape($datetime_value('starts_at')); ?>">
						</div>
						<div class="col-md-6">
							<label class="form-label">Tayang Sampai</label>
							<input type="datetime-local" class="form-control" name="ends_at" value="<?= html_escape($datetime_value('ends_at')); ?>">
						</div>
						<div class="col-12">
							<label class="form-check form-switch">
								<input class="form-check-input" type="checkbox" name="is_active" value="1" <?= (int) $value('is_active', 1) === 1 ? 'checked' : ''; ?>>
								<span class="form-check-label">Aktif dan tampil di dashboard member</span>
							</label>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<a href="<?= base_url('catalog/highlights'); ?>" class="btn btn-outline-secondary">Batal</a>
					<button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Simpan</button>
				</div>
			<?= form_close(); ?>
		</div>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
	var targetType = document.getElementById('highlight-target-type');
	var bookField = document.querySelector('.highlight-book-field');
	var categoryField = document.querySelector('.highlight-category-field');
	function refreshTargetFields() {
		if (!targetType || !bookField || !categoryField) {
			return;
		}
		var isCategory = targetType.value === 'category';
		bookField.style.display = isCategory ? 'none' : '';
		categoryField.style.display = isCategory ? '' : 'none';
	}
	if (targetType) {
		targetType.addEventListener('change', refreshTargetFields);
		refreshTargetFields();
	}
	<?php if ($is_edit): ?>
	var modalElement = document.getElementById('highlight-modal');
	if (modalElement && window.bootstrap) {
		window.bootstrap.Modal.getOrCreateInstance(modalElement).show();
	}
	<?php endif; ?>
});
</script>
