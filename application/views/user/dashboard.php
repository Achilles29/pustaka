<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tabler_css = 'https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css';
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= html_escape($title); ?></title>
	<link rel="stylesheet" href="<?= $tabler_css; ?>">
	<link rel="stylesheet" href="<?= base_url('assets/css/pustaka.css'); ?>">
</head>
<body class="user-page">
	<header class="user-topbar">
		<a class="public-brand" href="<?= base_url(); ?>">
			<span class="brand-mark">PR</span>
			<span>Pustaka Digital Rembang</span>
		</a>
		<div class="btn-list">
			<a href="<?= base_url(); ?>" class="btn btn-outline-primary btn-sm">Beranda</a>
			<a href="<?= base_url('logout'); ?>" class="btn btn-primary btn-sm">Logout</a>
		</div>
	</header>

	<main class="user-dashboard">
		<div class="container-xl">
			<div class="row g-4 align-items-center mb-4">
				<div class="col-lg-8">
					<div class="page-pretitle">Dashboard Pemustaka</div>
					<h1>Halo, <?= html_escape($current_user['full_name'] ?? $current_user['username']); ?></h1>
					<p class="text-secondary">Area ini khusus layanan pemustaka: katalog, kartu digital, event, dan akses baca online saat modulnya aktif.</p>
				</div>
				<div class="col-lg-4">
					<div class="member-card">
						<div class="small text-uppercase">Kartu Digital</div>
						<div class="h2 mb-1"><?= html_escape($current_user['username']); ?></div>
						<div><?= html_escape($current_user['email'] ?: 'Email belum diisi'); ?></div>
					</div>
				</div>
			</div>

			<div class="row row-cards">
				<div class="col-md-4">
					<div class="card stat-card">
						<div class="card-body">
							<div class="subheader">Katalog tersedia</div>
							<div class="h1 mb-0"><?= number_format($catalog_count, 0, ',', '.'); ?></div>
						</div>
					</div>
				</div>
				<div class="col-md-4">
					<div class="card stat-card">
						<div class="card-body">
							<div class="subheader">Kuota baca</div>
							<div class="h1 mb-0">0</div>
							<div class="text-secondary">Menunggu modul pojok baca.</div>
						</div>
					</div>
				</div>
				<div class="col-md-4">
					<div class="card stat-card">
						<div class="card-body">
							<div class="subheader">Event</div>
							<div class="h3 mb-0"><?= html_escape($event_label); ?></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</main>
</body>
</html>
