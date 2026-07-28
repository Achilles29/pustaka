<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$map_json = json_encode($map_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Direktori Terintegrasi</div>
				<h1 class="page-title">Perpustakaan GIS</h1>
			</div>
			<div class="col-auto ms-auto">
				<a href="<?= base_url('libraries/create'); ?>" class="btn btn-primary">Tambah Perpustakaan</a>
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

		<div class="card mb-3">
			<div class="card-body">
				<?= form_open('libraries', ['method' => 'get', 'class' => 'row g-2 align-items-end']); ?>
					<div class="col-md-4">
						<label class="form-label">Cari</label>
						<input type="text" class="form-control" name="q" value="<?= html_escape($filters['q'] ?? ''); ?>" placeholder="Nama, kode, kecamatan, desa">
					</div>
					<div class="col-md-3">
						<label class="form-label">Jenis</label>
						<select name="type_id" class="form-select">
							<option value="">Semua jenis</option>
							<?php foreach ($types as $type): ?>
								<option value="<?= (int) $type['id']; ?>" <?= (int) ($filters['type_id'] ?? 0) === (int) $type['id'] ? 'selected' : ''; ?>>
									<?= html_escape($type['name']); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-3">
						<label class="form-label">Status</label>
						<select name="status" class="form-select">
							<option value="">Semua status</option>
							<?php foreach (['active' => 'Aktif', 'pending' => 'Pending', 'inactive' => 'Nonaktif'] as $value => $label): ?>
								<option value="<?= $value; ?>" <?= ($filters['status'] ?? '') === $value ? 'selected' : ''; ?>><?= $label; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-2">
						<button type="submit" class="btn btn-outline-primary w-100">Filter</button>
					</div>
				<?= form_close(); ?>
			</div>
		</div>

		<div class="row row-cards">
			<div class="col-lg-7">
				<div class="card">
					<div class="card-header">
						<h2 class="card-title">Peta Lokasi</h2>
					</div>
					<div class="card-body">
						<div id="libraries-map" class="leaflet-map"></div>
					</div>
				</div>
			</div>
			<div class="col-lg-5">
				<div class="row row-cards">
					<div class="col-6">
						<div class="card stat-card">
							<div class="card-body">
								<div class="subheader">Total titik</div>
								<div class="h1 mb-0"><?= number_format(count($libraries), 0, ',', '.'); ?></div>
							</div>
						</div>
					</div>
					<div class="col-6">
						<div class="card stat-card">
							<div class="card-body">
								<div class="subheader">Jenis aktif</div>
								<div class="h1 mb-0"><?= number_format(count($types), 0, ',', '.'); ?></div>
							</div>
						</div>
					</div>
					<div class="col-12">
						<div class="card">
							<div class="card-header">
								<h2 class="card-title">Legenda</h2>
							</div>
							<div class="list-group list-group-flush">
								<?php foreach ($types as $type): ?>
									<div class="list-group-item d-flex align-items-center">
										<span class="legend-dot me-2" style="background: <?= html_escape($type['marker_color']); ?>"></span>
										<span><?= html_escape($type['name']); ?></span>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-12">
				<div class="card">
					<div class="card-header">
						<h2 class="card-title">Data Perpustakaan</h2>
					</div>
					<div class="table-responsive">
						<table class="table table-vcenter card-table">
							<thead>
								<tr>
									<th>Perpustakaan</th>
									<th>Jenis</th>
									<th>Wilayah</th>
									<th>Koordinat</th>
									<th>Status</th>
									<th class="w-1">Aksi</th>
								</tr>
							</thead>
							<tbody>
								<?php if (empty($libraries)): ?>
									<tr>
										<td colspan="6" class="text-center text-secondary py-4">Belum ada data perpustakaan.</td>
									</tr>
								<?php endif; ?>
								<?php foreach ($libraries as $library): ?>
									<tr>
										<td>
											<div class="fw-semibold"><?= html_escape($library['name']); ?></div>
											<div class="text-secondary small"><code><?= html_escape($library['code']); ?></code></div>
										</td>
										<td><?= html_escape($library['type_name']); ?></td>
										<td><?= html_escape(trim(($library['village'] ?: '-') . ', ' . ($library['district'] ?: '-'), ', ')); ?></td>
										<td><?= html_escape($library['latitude'] . ', ' . $library['longitude']); ?></td>
										<td>
											<span class="badge <?= $library['status'] === 'active' ? 'bg-green-lt' : ($library['status'] === 'pending' ? 'bg-yellow-lt' : 'bg-red-lt'); ?>">
												<?= html_escape($library['status']); ?>
											</span>
										</td>
										<td>
											<div class="btn-list flex-nowrap">
												<a class="btn btn-sm btn-outline-primary" href="<?= base_url('libraries/edit/' . (int) $library['id']); ?>">Edit</a>
												<?= form_open('libraries/toggle/' . (int) $library['id'], ['class' => 'd-inline']); ?>
													<button type="submit" class="btn btn-sm btn-outline-secondary"><?= $library['status'] === 'active' ? 'Nonaktifkan' : 'Aktifkan'; ?></button>
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

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
	var points = <?= $map_json ?: '[]'; ?>;
	var map = L.map('libraries-map').setView([-6.7071, 111.3502], 11);
	L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		maxZoom: 19,
		attribution: '&copy; OpenStreetMap'
	}).addTo(map);

	var bounds = [];
	points.forEach(function (point) {
		var marker = L.circleMarker([point.lat, point.lng], {
			radius: 8,
			color: '#ffffff',
			weight: 2,
			fillColor: point.color || '#0b6b86',
			fillOpacity: 1
		}).addTo(map);
		L.circle([point.lat, point.lng], {
			radius: point.radius || 100,
			color: point.color || '#0b6b86',
			weight: 1,
			fillOpacity: 0.06
		}).addTo(map);
		marker.bindPopup('<strong>' + point.name + '</strong><br>' + point.type + '<br><a href="' + point.url + '">Edit data</a>');
		bounds.push([point.lat, point.lng]);
	});

	if (bounds.length > 0) {
		map.fitBounds(bounds, { padding: [24, 24], maxZoom: 14 });
	}
})();
</script>
