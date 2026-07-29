<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$active_tab = ! empty($edit_menu) ? 'data' : 'structure';
$render_sortable = function ($items, $parent_id = 0, $depth = 0) use (&$render_sortable) {
	echo '<ul class="sidebar-sortable" data-parent-id="' . (int) $parent_id . '">';
	foreach ($items as $item) {
		$icon = trim((string) ($item['icon'] ?? '')) ?: 'ti ti-circle';
		echo '<li class="sidebar-sortable-item" draggable="true" data-menu-id="' . (int) $item['id'] . '">';
		echo '<div class="sortable-row">';
		echo '<span class="drag-handle" title="Geser urutan"><i class="ti ti-grip-vertical"></i></span>';
		echo '<span class="menu-tree-icon"><i class="' . html_escape($icon) . '"></i></span>';
		echo '<span class="sortable-title">';
		echo '<span class="fw-semibold">' . html_escape($item['title']) . '</span>';
		echo '<span class="text-secondary small"><code>' . html_escape($item['menu_key']) . '</code> - ' . html_escape($item['url'] ?: 'group') . '</span>';
		echo '</span>';
		echo '<span class="badge bg-secondary-lt ms-auto">' . (int) $item['sort_order'] . '</span>';
		if (empty($item['is_active'])) {
			echo '<span class="badge bg-red-lt ms-2">Nonaktif</span>';
		}
		echo '</div>';
		$render_sortable($item['children'] ?? [], (int) $item['id'], $depth + 1);
		echo '</li>';
	}
	echo '</ul>';
};
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">RBAC Foundation</div>
				<h1 class="page-title">Sidebar</h1>
			</div>
			<div class="col-auto ms-auto">
				<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#menu-modal">
					<i class="ti ti-plus me-1"></i>Tambah Menu
				</button>
			</div>
		</div>
	</div>
</div>

<div class="page-body">
	<div class="container-xl">
		<?php $this->load->view('rbac/_tabs', ['active_rbac_tab' => $active_rbac_tab]); ?>
		<?php if ($this->session->flashdata('success')): ?>
			<div class="alert alert-success"><?= html_escape($this->session->flashdata('success')); ?></div>
		<?php endif; ?>
		<?php if ($this->session->flashdata('error')): ?>
			<div class="alert alert-danger"><?= html_escape($this->session->flashdata('error')); ?></div>
		<?php endif; ?>

		<div class="row row-cards mb-3">
			<div class="col-6 col-lg-3">
				<div class="card stat-card stat-card-compact">
					<div class="card-body">
						<div class="subheader">Total Menu</div>
						<div class="h1 mb-0"><?= number_format(count($menus), 0, ',', '.'); ?></div>
					</div>
				</div>
			</div>
			<div class="col-6 col-lg-3">
				<div class="card stat-card stat-card-compact">
					<div class="card-body">
						<div class="subheader">Menu Aktif</div>
						<div class="h1 mb-0"><?= number_format(count(array_filter($menus, function ($menu) { return (int) $menu['is_active'] === 1; })), 0, ',', '.'); ?></div>
					</div>
				</div>
			</div>
		</div>

		<div class="card admin-card data-workspace">
			<div class="card-header workspace-header">
				<div>
					<h2 class="card-title">Struktur Sidebar</h2>
					<div class="text-secondary small">Geser menu untuk mengatur urutan atau memindahkan menu ke grup lain.</div>
				</div>
				<ul class="nav nav-tabs card-header-tabs workspace-tabs" role="tablist">
					<li class="nav-item" role="presentation">
						<a href="#tab-sidebar-structure" class="nav-link <?= $active_tab === 'structure' ? 'active' : ''; ?>" data-bs-toggle="tab" role="tab">
							<i class="ti ti-list-tree me-1"></i>Struktur
						</a>
					</li>
					<li class="nav-item" role="presentation">
						<a href="#tab-sidebar-data" class="nav-link <?= $active_tab === 'data' ? 'active' : ''; ?>" data-bs-toggle="tab" role="tab">
							<i class="ti ti-table me-1"></i>Data Menu
						</a>
					</li>
				</ul>
			</div>

			<div class="tab-content">
				<div class="tab-pane <?= $active_tab === 'structure' ? 'active show' : ''; ?>" id="tab-sidebar-structure" role="tabpanel">
					<div class="card-body">
						<?= form_open('rbac/sidebar/reorder', ['id' => 'sidebar-order-form']); ?>
							<input type="hidden" name="menu_order" id="menu-order-input">
							<div class="sortable-shell">
								<?php $render_sortable($menu_tree); ?>
							</div>
							<div class="d-flex flex-wrap gap-2 align-items-center mt-3">
								<button type="submit" class="btn btn-primary">
									<i class="ti ti-device-floppy me-1"></i>Simpan Urutan
								</button>
								<span class="text-secondary small">Submenu tetap mengikuti permission dari halaman yang terhubung.</span>
							</div>
						<?= form_close(); ?>
					</div>
				</div>

				<div class="tab-pane <?= $active_tab === 'data' ? 'active show' : ''; ?>" id="tab-sidebar-data" role="tabpanel">
					<div class="table-responsive">
						<table class="table table-vcenter card-table">
							<thead>
								<tr>
									<th>Menu</th>
									<th>Halaman</th>
									<th>Status</th>
									<th class="w-1">Aksi</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($menus as $menu): ?>
									<tr>
										<td>
											<div class="fw-semibold"><i class="<?= html_escape($menu['icon'] ?: 'ti ti-circle'); ?> me-1"></i><?= html_escape($menu['title']); ?></div>
											<div class="text-secondary small"><code><?= html_escape($menu['menu_key']); ?></code> - <?= html_escape($menu['url'] ?: 'group'); ?></div>
										</td>
										<td><?= html_escape($menu['page_code'] ?: '-'); ?></td>
										<td><span class="badge <?= (int) $menu['is_active'] === 1 ? 'bg-green-lt' : 'bg-red-lt'; ?>"><?= (int) $menu['is_active'] === 1 ? 'Aktif' : 'Nonaktif'; ?></span></td>
										<td>
											<div class="btn-list flex-nowrap">
												<a class="btn btn-sm btn-action btn-action-primary" title="Edit Menu" href="<?= base_url('rbac/sidebar?edit_id=' . (int) $menu['id']); ?>">
													<i class="ti ti-edit"></i><span>Edit</span>
												</a>
												<?= form_open('rbac/sidebar/toggle/' . (int) $menu['id'], ['class' => 'd-inline']); ?>
													<button class="btn btn-sm btn-action btn-action-muted" title="<?= (int) $menu['is_active'] === 1 ? 'Nonaktifkan Menu' : 'Aktifkan Menu'; ?>" type="submit">
														<i class="ti <?= (int) $menu['is_active'] === 1 ? 'ti-toggle-right' : 'ti-toggle-left'; ?>"></i><span><?= (int) $menu['is_active'] === 1 ? 'Nonaktifkan' : 'Aktifkan'; ?></span>
													</button>
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

<?php $this->load->view('rbac/_menu_modal', compact('edit_menu', 'parents', 'pages')); ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
	var dragged = null;

	document.querySelectorAll('.sidebar-sortable-item').forEach(function (item) {
		item.addEventListener('dragstart', function () {
			dragged = item;
			item.classList.add('is-dragging');
		});
		item.addEventListener('dragend', function () {
			item.classList.remove('is-dragging');
			document.querySelectorAll('.sidebar-sortable').forEach(function (list) {
				list.classList.remove('is-drag-over');
			});
			dragged = null;
		});
	});

	document.querySelectorAll('.sidebar-sortable').forEach(function (list) {
		list.addEventListener('dragover', function (event) {
			event.preventDefault();
			list.classList.add('is-drag-over');
			if (!dragged || dragged.parentElement === null) {
				return;
			}
			if (list.closest('[data-menu-id="' + dragged.getAttribute('data-menu-id') + '"]') === dragged) {
				return;
			}

			var after = Array.prototype.find.call(list.children, function (child) {
				if (child === dragged || !child.classList.contains('sidebar-sortable-item')) {
					return false;
				}
				return event.clientY <= child.getBoundingClientRect().top + child.offsetHeight / 2;
			});

			if (after) {
				list.insertBefore(dragged, after);
			} else {
				list.appendChild(dragged);
			}
		});

		list.addEventListener('dragleave', function () {
			list.classList.remove('is-drag-over');
		});
	});

	document.getElementById('sidebar-order-form').addEventListener('submit', function () {
		var payload = [];
		document.querySelectorAll('.sidebar-sortable').forEach(function (list) {
			Array.prototype.forEach.call(list.children, function (item, index) {
				if (!item.classList.contains('sidebar-sortable-item')) {
					return;
				}
				payload.push({
					id: parseInt(item.getAttribute('data-menu-id'), 10),
					parent_id: parseInt(list.getAttribute('data-parent-id'), 10) || 0,
					sort_order: (index + 1) * 10
				});
			});
		});
		document.getElementById('menu-order-input').value = JSON.stringify(payload);
	});
});
</script>
