<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$app_cards = [
	['key' => 'auth_user', 'accent' => 'blue', 'delta' => 'Akun aplikasi baru'],
	['key' => 'auth_role', 'accent' => 'green', 'delta' => 'Role berjenjang'],
	['key' => 'sys_page', 'accent' => 'cyan', 'delta' => 'Registry akses'],
	['key' => 'sys_menu', 'accent' => 'yellow', 'delta' => 'Sidebar dari database'],
	['key' => 'auth_role_permission', 'accent' => 'indigo', 'delta' => 'Matrix permission'],
	['key' => 'audit_log', 'accent' => 'red', 'delta' => 'Jejak aktivitas'],
];

$source_cards = [
	['key' => 'catalogs', 'accent' => 'blue', 'delta' => 'Metadata bibliografi'],
	['key' => 'collections', 'accent' => 'teal', 'delta' => 'Eksemplar fisik'],
	['key' => 'members', 'accent' => 'green', 'delta' => 'Basis membership'],
	['key' => 'location_library', 'accent' => 'yellow', 'delta' => 'Unit awal INLISLite'],
	['key' => 'locations', 'accent' => 'cyan', 'delta' => 'Ruang dan titik layanan'],
	['key' => 'collectionloans', 'accent' => 'indigo', 'delta' => 'Histori sirkulasi'],
];
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Fondasi data</div>
				<h1 class="page-title">Dashboard Pustaka Digital Rembang</h1>
			</div>
			<div class="col-auto ms-auto d-print-none">
				<div class="btn-list">
					<a href="<?= base_url('docs/SCAN_SUMMARY.md'); ?>" class="btn btn-outline-primary">
						Scan Summary
					</a>
					<a href="<?= base_url('docs/PRODUCT_VISION_PLUS.md'); ?>" class="btn btn-primary">
						Vision Plus
					</a>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="page-body">
	<div class="container-xl">
		<div class="row row-deck row-cards">
			<div class="col-12">
				<div class="card">
					<div class="card-header">
						<h2 class="card-title">Fondasi Database Baru: pustaka</h2>
					</div>
				</div>
			</div>

			<?php foreach ($app_cards as $card): ?>
				<?php $item = $stats['app'][$card['key']]; ?>
				<div class="col-sm-6 col-lg-4">
					<div class="card stat-card">
						<div class="card-body">
							<div class="d-flex align-items-center">
								<div class="subheader"><?= html_escape($item['label']); ?></div>
								<div class="ms-auto lh-1">
									<span class="badge bg-<?= $card['accent']; ?>-lt"><?= html_escape($card['delta']); ?></span>
								</div>
							</div>
							<div class="h1 mb-3"><?= number_format($item['value'], 0, ',', '.'); ?></div>
							<div class="progress progress-sm">
								<div class="progress-bar bg-<?= $card['accent']; ?>" style="width: <?= min(100, max(18, $item['value'] % 100)); ?>%" role="progressbar"></div>
							</div>
						</div>
					</div>
				</div>
			<?php endforeach; ?>

			<div class="col-12">
				<div class="card">
					<div class="card-header">
						<h2 class="card-title">Sumber Migrasi: inlislite_v3</h2>
					</div>
				</div>
			</div>

			<?php foreach ($source_cards as $card): ?>
				<?php $item = $stats['source'][$card['key']]; ?>
				<div class="col-sm-6 col-lg-4">
					<div class="card stat-card">
						<div class="card-body">
							<div class="d-flex align-items-center">
								<div class="subheader"><?= html_escape($item['label']); ?></div>
								<div class="ms-auto lh-1">
									<span class="badge bg-<?= $card['accent']; ?>-lt"><?= html_escape($card['delta']); ?></span>
								</div>
							</div>
							<div class="h1 mb-3"><?= number_format($item['value'], 0, ',', '.'); ?></div>
							<div class="progress progress-sm">
								<div class="progress-bar bg-<?= $card['accent']; ?>" style="width: <?= min(100, max(18, $item['value'] % 100)); ?>%" role="progressbar"></div>
							</div>
						</div>
					</div>
				</div>
			<?php endforeach; ?>

			<div class="col-lg-8">
				<div class="card">
					<div class="card-header">
						<h2 class="card-title">Sinkronisasi GIS Perpustakaan Rembang</h2>
					</div>
					<div class="card-body">
						<div class="gis-preview">
							<div class="gis-grid"></div>
							<div class="gis-pin pin-main"></div>
							<div class="gis-pin pin-school"></div>
							<div class="gis-pin pin-village"></div>
							<div class="gis-pin pin-private"></div>
							<div class="gis-panel">
								<div class="fw-semibold">Layer data</div>
								<div class="text-secondary small">Perpusda, sekolah, desa, swasta, mitra, pojok baca</div>
							</div>
						</div>
						<div class="row g-3 mt-3">
							<div class="col-md-4">
								<div class="status-line border-blue">
									<div class="fw-semibold">Titik lokasi</div>
									<div class="text-secondary">Koordinat, radius, dan status aktif.</div>
								</div>
							</div>
							<div class="col-md-4">
								<div class="status-line border-green">
									<div class="fw-semibold">Profil lengkap</div>
									<div class="text-secondary">Data layanan, kontak, fasilitas, dan foto.</div>
								</div>
							</div>
							<div class="col-md-4">
								<div class="status-line border-yellow">
									<div class="fw-semibold">Koleksi terhubung</div>
									<div class="text-secondary">Katalog, eksemplar, event, dan pojok baca.</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-lg-4">
				<div class="card">
					<div class="card-header">
						<h2 class="card-title">Kesiapan Migrasi</h2>
					</div>
					<div class="list-group list-group-flush">
						<div class="list-group-item">
							<div class="row align-items-center">
								<div class="col-auto"><span class="status-dot status-dot-animated bg-green d-block"></span></div>
								<div class="col">Database INLISLite lokal sudah terimport.</div>
							</div>
						</div>
						<div class="list-group-item">
							<div class="row align-items-center">
								<div class="col-auto"><span class="status-dot bg-blue d-block"></span></div>
								<div class="col">Aset cover dan foto anggota sudah diinventaris.</div>
							</div>
						</div>
						<div class="list-group-item">
							<div class="row align-items-center">
								<div class="col-auto"><span class="status-dot bg-yellow d-block"></span></div>
								<div class="col">Pencocokan foto anggota perlu aturan tambahan.</div>
							</div>
						</div>
						<div class="list-group-item">
							<div class="row align-items-center">
								<div class="col-auto"><span class="status-dot bg-cyan d-block"></span></div>
								<div class="col">Tabler sudah dibuild dari folder lokal.</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-12">
				<div class="card">
					<div class="card-header">
						<h2 class="card-title">Prioritas Implementasi Terdekat</h2>
					</div>
					<div class="table-responsive">
						<table class="table table-vcenter card-table">
							<thead>
								<tr>
									<th>Modul</th>
									<th>Fokus</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td class="fw-semibold">Schema Baru</td>
									<td>Auth, role, permission, page registry, sidebar database.</td>
									<td><span class="badge bg-green-lt">Dimulai</span></td>
								</tr>
								<tr>
									<td class="fw-semibold">Importer</td>
									<td>Ambil katalog, eksemplar, anggota, cover, dan foto.</td>
									<td><span class="badge bg-green-lt">Dirancang</span></td>
								</tr>
								<tr>
									<td class="fw-semibold">GIS</td>
									<td>Peta perpustakaan terdaftar se-Kabupaten Rembang.</td>
									<td><span class="badge bg-yellow-lt">Dicatat</span></td>
								</tr>
								<tr>
									<td class="fw-semibold">Reader Aman</td>
									<td>Token halaman, watermark, logging, dan kebijakan download.</td>
									<td><span class="badge bg-cyan-lt">Roadmap</span></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
