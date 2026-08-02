<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$title = isset($title) ? $title : 'Pustaka Digital Rembang';
$content = isset($content) ? $content : '';
$menu_items = isset($menu_items) ? $menu_items : array();
$current_user = isset($current_user) ? $current_user : array();
$user_roles = isset($user_roles) ? $user_roles : array();
$admin_inbox = isset($admin_inbox) ? $admin_inbox : ['total' => 0, 'items' => []];
$tabler_css = 'https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css';
$tabler_icons_css = 'https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/tabler-icons.min.css';
$tabler_js = 'https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js';
$role_labels = array_map(function ($role) {
	return $role['code'];
}, $user_roles);
$current_path = trim(uri_string(), '/');
$is_active_menu = function ($item) use (&$is_active_menu, $current_path) {
	$url = trim((string) ($item['url'] ?? ''), '/');
	if ($url !== '' && ($current_path === $url || strpos($current_path . '/', $url . '/') === 0)) {
		return true;
	}
	foreach (($item['children'] ?? []) as $child) {
		if ($is_active_menu($child)) {
			return true;
		}
	}
	return false;
};
$render_sidebar_items = function ($items, $depth = 0) use (&$render_sidebar_items, $is_active_menu) {
	foreach ($items as $item) {
		$has_children = ! empty($item['children']);
		$is_active = $is_active_menu($item);
		$icon = trim((string) ($item['icon'] ?? ''));
		$icon = $icon !== '' ? $icon : 'ti ti-circle';
		$url = empty($item['url']) ? '#' : base_url($item['url']);
		$item_classes = 'admin-menu-item' . ($has_children ? ' has-children' : '') . ($is_active ? ' active' : '');
		$link_classes = 'admin-menu-link' . ($has_children ? ' admin-menu-toggle' : '');
		?>
		<li class="<?= $item_classes; ?>">
			<a class="<?= $link_classes; ?>" href="<?= $has_children ? '#' : $url; ?>"<?= $has_children ? ' data-bs-toggle="collapse" data-bs-target="#menu-' . (int) $item['id'] . '" role="button" aria-expanded="' . ($is_active ? 'true' : 'false') . '"' : ''; ?>>
				<span class="admin-menu-icon"><i class="<?= html_escape($icon); ?>"></i></span>
				<span class="admin-menu-title"><?= html_escape($item['title']); ?></span>
				<?php if ($has_children): ?>
					<span class="admin-menu-caret"><i class="ti ti-chevron-right"></i></span>
				<?php endif; ?>
			</a>
			<?php if ($has_children): ?>
				<div class="collapse admin-submenu<?= $is_active ? ' show' : ''; ?>" id="menu-<?= (int) $item['id']; ?>">
					<ul class="admin-submenu-list">
						<?php $render_sidebar_items($item['children'], $depth + 1); ?>
					</ul>
				</div>
			<?php endif; ?>
		</li>
		<?php
	}
};
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="Dashboard awal Pustaka Digital Rembang">
	<title><?= html_escape($title); ?></title>
	<link rel="icon" href="<?= base_url('img/favicon.ico'); ?>" type="image/x-icon">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Fraunces:opsz,wght@9..144,650;9..144,750;9..144,850&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="<?= $tabler_css; ?>">
	<link rel="stylesheet" href="<?= $tabler_icons_css; ?>">
	<link rel="stylesheet" href="<?= base_url('assets/css/pustaka.css'); ?>">
	<link rel="stylesheet" href="<?= base_url('assets/css/pustaka-polish.css?v=20260802j'); ?>">
</head>
<body class="admin-body">
	<div class="page">
		<aside class="navbar navbar-vertical navbar-expand-lg admin-sidebar">
			<div class="container-fluid admin-sidebar-inner">
				<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				<a class="navbar-brand admin-brand" href="<?= base_url('admin'); ?>" aria-label="Pustaka Digital Rembang">
					<span class="brand-logo-shell">
						<img class="brand-logo" src="<?= base_url('img/logo-small.jpeg'); ?>" alt="Logo Kabupaten Rembang">
					</span>
					<span class="admin-brand-text">
						<span>Pustaka Digital</span>
						<small>Rembang</small>
					</span>
				</a>

				<div class="collapse navbar-collapse" id="sidebar-menu">
					<div class="admin-sidebar-label">Menu Utama</div>
					<ul class="admin-menu">
						<?php $render_sidebar_items($menu_items); ?>
					</ul>
				</div>
			</div>
		</aside>

		<div class="page-wrapper">
			<header class="navbar navbar-expand-md d-print-none admin-topbar">
				<div class="container-xl">
					<div>
						<div class="page-pretitle mb-0">Admin Panel</div>
						<div class="fw-semibold"><?= html_escape($title); ?></div>
					</div>
					<div class="navbar-nav flex-row ms-auto">
						<div class="nav-item dropdown me-3 admin-inbox-dropdown">
							<a href="#" class="admin-inbox-trigger <?= (int) ($admin_inbox['total'] ?? 0) > 0 ? 'has-pending' : ''; ?>" data-bs-toggle="dropdown" aria-label="Buka kotak masuk layanan">
								<i class="ti ti-inbox"></i>
								<span>Kotak Masuk</span>
								<strong><?= number_format((int) ($admin_inbox['total'] ?? 0), 0, ',', '.'); ?></strong>
							</a>
							<div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow admin-inbox-menu">
								<div class="dropdown-header">Antrean Layanan</div>
								<?php foreach (($admin_inbox['items'] ?? []) as $item): ?>
									<a href="<?= base_url($item['url']); ?>" class="dropdown-item d-flex align-items-center">
										<span class="flex-fill"><?= html_escape($item['label']); ?></span>
										<span class="badge <?= (int) $item['count'] > 0 ? 'bg-red-lt' : 'bg-secondary-lt'; ?>"><?= number_format((int) $item['count'], 0, ',', '.'); ?></span>
									</a>
								<?php endforeach; ?>
							</div>
						</div>
						<div class="nav-item dropdown">
							<a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Buka menu user">
								<span class="avatar avatar-sm"><?= html_escape(strtoupper(substr($current_user['full_name'] ?? 'PU', 0, 2))); ?></span>
								<div class="d-none d-xl-block ps-2">
									<div><?= html_escape($current_user['full_name'] ?? 'Pustaka'); ?></div>
									<div class="mt-1 small text-secondary"><?= html_escape(implode(', ', $role_labels)); ?></div>
								</div>
							</a>
							<div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
								<a href="<?= base_url('logout'); ?>" class="dropdown-item">Logout</a>
							</div>
						</div>
					</div>
				</div>
			</header>
			<?= $content; ?>
			<footer class="footer footer-transparent d-print-none">
				<div class="container-xl">
					<div class="row text-center align-items-center flex-row-reverse">
						<div class="col-lg-auto ms-lg-auto">
							<ul class="list-inline list-inline-dots mb-0">
								<li class="list-inline-item">CodeIgniter 3.1.13</li>
								<li class="list-inline-item">Tabler 1.4.0</li>
							</ul>
						</div>
						<div class="col-12 col-lg-auto mt-3 mt-lg-0">
							Pustaka Digital Rembang
						</div>
					</div>
				</div>
			</footer>
		</div>
	</div>
	<script src="<?= $tabler_js; ?>"></script>
	<script>
	(function () {
		function showModalFallback(modalElement) {
			if (!modalElement) {
				return;
			}

			modalElement.classList.add('show');
			modalElement.style.display = 'block';
			modalElement.removeAttribute('aria-hidden');
			modalElement.setAttribute('aria-modal', 'true');
			modalElement.setAttribute('role', 'dialog');
			document.body.classList.add('modal-open');

			if (!document.querySelector('.modal-backdrop')) {
				var backdrop = document.createElement('div');
				backdrop.className = 'modal-backdrop fade show';
				backdrop.setAttribute('data-pustaka-modal-backdrop', '1');
				document.body.appendChild(backdrop);
			}
		}

		function hideModalFallback(modalElement) {
			if (!modalElement) {
				return;
			}

			modalElement.classList.remove('show');
			modalElement.style.display = 'none';
			modalElement.setAttribute('aria-hidden', 'true');
			modalElement.removeAttribute('aria-modal');
			modalElement.removeAttribute('role');
			document.body.classList.remove('modal-open');

			document.querySelectorAll('[data-pustaka-modal-backdrop="1"]').forEach(function (backdrop) {
				backdrop.remove();
			});
		}

		function showModal(modalElement) {
			if (!modalElement) {
				return;
			}

			if (window.bootstrap && bootstrap.Modal) {
				bootstrap.Modal.getOrCreateInstance(modalElement).show();
				return;
			}

			showModalFallback(modalElement);
		}

		function openMarkedModals() {
			document.querySelectorAll('[data-pustaka-open-modal="1"]').forEach(function (modalElement) {
				showModal(modalElement);
				modalElement.removeAttribute('data-pustaka-open-modal');
			});
		}

		function syncMobileTables() {
			document.querySelectorAll('.table-responsive table').forEach(function (table) {
				var headers = Array.prototype.map.call(table.querySelectorAll('thead th'), function (th) {
					return th.textContent.trim();
				});
				table.querySelectorAll('tbody tr').forEach(function (row) {
					Array.prototype.forEach.call(row.children, function (cell, index) {
						if (headers[index] && !cell.hasAttribute('data-label')) {
							cell.setAttribute('data-label', headers[index]);
						}
					});
				});
			});
		}

		function bindModalFallback() {
			if (window.bootstrap && bootstrap.Modal) {
				return;
			}

			document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target]').forEach(function (trigger) {
				trigger.addEventListener('click', function (event) {
					event.preventDefault();
					showModalFallback(document.querySelector(trigger.getAttribute('data-bs-target')));
				});
			});

			document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(function (trigger) {
				trigger.addEventListener('click', function (event) {
					event.preventDefault();
					hideModalFallback(trigger.closest('.modal'));
				});
			});

			document.addEventListener('click', function (event) {
				if (event.target && event.target.matches('[data-pustaka-modal-backdrop="1"]')) {
					hideModalFallback(document.querySelector('.modal.show'));
				}
			});

			document.addEventListener('keydown', function (event) {
				if (event.key === 'Escape') {
					hideModalFallback(document.querySelector('.modal.show'));
				}
			});
		}

		function bindTabFallback() {
			if (window.bootstrap && bootstrap.Tab) {
				return;
			}

			document.querySelectorAll('[data-bs-toggle="tab"]').forEach(function (trigger) {
				trigger.addEventListener('click', function (event) {
					var targetSelector = trigger.getAttribute('href') || trigger.getAttribute('data-bs-target');
					var target = targetSelector ? document.querySelector(targetSelector) : null;
					if (!target) {
						return;
					}

					event.preventDefault();
					var tablist = trigger.closest('[role="tablist"]');
					var content = target.closest('.tab-content');

					if (tablist) {
						tablist.querySelectorAll('.nav-link').forEach(function (link) {
							link.classList.remove('active');
							link.setAttribute('aria-selected', 'false');
						});
					}

					if (content) {
						content.querySelectorAll('.tab-pane').forEach(function (pane) {
							pane.classList.remove('active', 'show');
						});
					}

					trigger.classList.add('active');
					trigger.setAttribute('aria-selected', 'true');
					target.classList.add('active', 'show');
				});
			});
		}

		document.addEventListener('DOMContentLoaded', function () {
			bindModalFallback();
			bindTabFallback();
			openMarkedModals();
			syncMobileTables();
		});
	})();
	</script>
</body>
</html>
