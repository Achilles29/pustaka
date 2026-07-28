<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle"><?= html_escape($module_label ?? 'Modul'); ?></div>
				<h1 class="page-title"><?= html_escape($page_title ?? 'Modul Pustaka'); ?></h1>
			</div>
		</div>
	</div>
</div>

<div class="page-body">
	<div class="container-xl">
		<div class="card">
			<div class="card-body">
				<div class="row align-items-center">
					<div class="col">
						<h2 class="card-title mb-2"><?= html_escape($page_title ?? 'Modul Pustaka'); ?></h2>
						<p class="text-secondary mb-0"><?= html_escape($description ?? 'Modul ini sudah terdaftar di permission dan siap dikembangkan.'); ?></p>
					</div>
					<div class="col-auto">
						<span class="badge bg-blue-lt">Terproteksi RBAC</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
