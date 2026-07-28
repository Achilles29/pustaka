<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$title = isset($title) ? $title : 'Pustaka Digital Rembang';
$content = isset($content) ? $content : '';
$menu_items = isset($menu_items) ? $menu_items : array();
$current_user = isset($current_user) ? $current_user : array();
$user_roles = isset($user_roles) ? $user_roles : array();
$tabler_css = 'https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css';
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
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="Dashboard awal Pustaka Digital Rembang">
	<title><?= html_escape($title); ?></title>
	<link rel="stylesheet" href="<?= $tabler_css; ?>">
	<link rel="stylesheet" href="<?= base_url('assets/css/pustaka.css'); ?>">
</head>
<body class="admin-body">
	<div class="page">
		<aside class="navbar navbar-vertical navbar-expand-lg navbar-dark admin-sidebar">
			<div class="container-fluid">
				<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				<a class="navbar-brand fw-semibold" href="<?= base_url(); ?>" aria-label="Pustaka Digital Rembang">
					<span class="brand-mark">PR</span>
					<span>Pustaka Digital Rembang</span>
				</a>

				<div class="collapse navbar-collapse" id="sidebar-menu">
					<ul class="navbar-nav pt-lg-3">
						<?php foreach ($menu_items as $item): ?>
							<?php $has_children = ! empty($item['children']); ?>
							<?php $is_active = $is_active_menu($item); ?>
							<li class="nav-item<?= $has_children ? ' dropdown' : ''; ?><?= $is_active ? ' active' : ''; ?>">
								<a class="nav-link<?= $has_children ? ' dropdown-toggle' : ''; ?>" href="<?= $has_children || empty($item['url']) ? '#' : base_url($item['url']); ?>"<?= $has_children ? ' data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="' . ($is_active ? 'true' : 'false') . '"' : ''; ?>>
									<span class="nav-link-icon d-md-none d-lg-inline-block"><?= html_escape(strtoupper(substr($item['title'], 0, 1))); ?></span>
									<span class="nav-link-title"><?= html_escape($item['title']); ?></span>
								</a>
								<?php if ($has_children): ?>
									<div class="dropdown-menu<?= $is_active ? ' show' : ''; ?>">
										<?php foreach ($item['children'] as $child): ?>
											<?php $child_active = $is_active_menu($child); ?>
											<a class="dropdown-item<?= $child_active ? ' active' : ''; ?>" href="<?= empty($child['url']) ? '#' : base_url($child['url']); ?>">
												<?= html_escape($child['title']); ?>
											</a>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
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
</body>
</html>
