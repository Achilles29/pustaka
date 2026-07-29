<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$active_rbac_tab = isset($active_rbac_tab) ? $active_rbac_tab : 'roles';
$tabs = [
	'roles' => ['label' => 'Tipe User', 'url' => 'rbac/roles', 'icon' => 'ti ti-users-group'],
	'users' => ['label' => 'User', 'url' => 'rbac/users', 'icon' => 'ti ti-users'],
	'pages' => ['label' => 'Registry Halaman', 'url' => 'rbac/pages', 'icon' => 'ti ti-file-settings'],
	'sidebar' => ['label' => 'Sidebar', 'url' => 'rbac/sidebar', 'icon' => 'ti ti-layout-sidebar'],
];
?>
<div class="rbac-tabs">
	<?php foreach ($tabs as $key => $tab): ?>
		<a class="rbac-tab<?= $active_rbac_tab === $key ? ' active' : ''; ?>" href="<?= base_url($tab['url']); ?>">
			<i class="<?= html_escape($tab['icon']); ?>"></i>
			<span><?= html_escape($tab['label']); ?></span>
		</a>
	<?php endforeach; ?>
</div>
