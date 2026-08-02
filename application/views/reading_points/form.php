<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$is_edit = ! empty($point);
$field = function ($key, $default = '') use ($point) {
	return $point[$key] ?? $default;
};
$lat = (float) ($field('latitude') ?: -6.7071);
$lng = (float) ($field('longitude') ?: 111.3502);
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Pojok Baca Digital</div>
				<h1 class="page-title"><?= $is_edit ? 'Edit Titik' : 'Tambah Titik'; ?></h1>
			</div>
			<div class="col-auto ms-auto">
				<a href="<?= base_url('reading-points'); ?>" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i>Kembali</a>
			</div>
		</div>
	</div>
</div>

<div class="page-body">
	<div class="container-xl">
		<?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?= html_escape($this->session->flashdata('error')); ?></div><?php endif; ?>
		<?= form_open($action); ?>
			<div class="row row-cards">
				<div class="col-lg-8">
					<div class="card admin-card">
						<div class="card-header"><h2 class="card-title">Identitas Titik</h2></div>
						<div class="card-body">
							<div class="mb-3">
								<label class="form-label">Nama Titik</label>
								<input type="text" class="form-control" name="name" value="<?= html_escape($field('name')); ?>" required>
							</div>
							<div class="row">
								<div class="col-md-6 mb-3">
									<label class="form-label">Perpustakaan Pengampu</label>
									<select class="form-select" name="library_id">
										<option value="">Belum dipilih</option>
										<?php foreach ($libraries as $library): ?>
											<option value="<?= (int) $library['id']; ?>" <?= (int) $field('library_id') === (int) $library['id'] ? 'selected' : ''; ?>><?= html_escape($library['name']); ?></option>
										<?php endforeach; ?>
									</select>
								</div>
								<div class="col-md-6 mb-3">
									<label class="form-label">Nama Mitra</label>
									<input type="text" class="form-control" name="partner_name" value="<?= html_escape($field('partner_name')); ?>" placeholder="Contoh: Namua">
								</div>
							</div>
							<div class="mb-3">
								<label class="form-label">Alamat</label>
								<textarea class="form-control" name="address" rows="3"><?= html_escape($field('address')); ?></textarea>
							</div>
							<div class="mb-3">
								<label class="form-label">Pin Lokasi</label>
								<div id="reading-point-picker-map" class="leaflet-map leaflet-map-form reading-point-map"></div>
								<div class="form-hint">Klik peta atau geser pin untuk menentukan titik GPS Pojok Baca.</div>
							</div>
							<div class="row">
								<div class="col-md-6 mb-3">
									<label class="form-label">Latitude</label>
									<input type="text" class="form-control" id="reading-point-latitude" name="latitude" value="<?= html_escape($lat); ?>" placeholder="-6.7071000">
								</div>
								<div class="col-md-6 mb-3">
									<label class="form-label">Longitude</label>
									<input type="text" class="form-control" id="reading-point-longitude" name="longitude" value="<?= html_escape($lng); ?>" placeholder="111.3502000">
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-4">
					<div class="card admin-card">
						<div class="card-header"><h2 class="card-title">Aturan Akses</h2></div>
						<div class="card-body">
							<div class="mb-3">
								<label class="form-label">Radius GPS</label>
								<div class="input-group">
									<input type="number" class="form-control" name="radius_meters" value="<?= html_escape($field('radius_meters', 100)); ?>" min="10" max="5000">
									<span class="input-group-text">meter</span>
								</div>
							</div>
							<div class="mb-3">
								<label class="form-label">Kuota Harian</label>
								<input type="number" class="form-control" name="daily_quota" value="<?= html_escape($field('daily_quota', 60)); ?>" min="0">
							</div>
							<div class="mb-3">
								<label class="form-label">Satuan Kuota</label>
								<select class="form-select" name="quota_unit">
									<option value="minutes" <?= $field('quota_unit', 'minutes') === 'minutes' ? 'selected' : ''; ?>>Menit</option>
									<option value="pages" <?= $field('quota_unit') === 'pages' ? 'selected' : ''; ?>>Halaman</option>
									<option value="books" <?= $field('quota_unit') === 'books' ? 'selected' : ''; ?>>Buku</option>
								</select>
							</div>
							<div class="mb-3">
								<label class="form-label">Jam Aktif</label>
								<input type="text" class="form-control" name="opening_hours" value="<?= html_escape($field('opening_hours')); ?>" placeholder="Senin-Jumat 08.00-16.00">
							</div>
							<div class="mb-3">
								<label class="form-label">Status</label>
								<select class="form-select" name="status">
									<option value="draft" <?= $field('status', 'draft') === 'draft' ? 'selected' : ''; ?>>Draft</option>
									<option value="active" <?= $field('status') === 'active' ? 'selected' : ''; ?>>Aktif</option>
									<option value="inactive" <?= $field('status') === 'inactive' ? 'selected' : ''; ?>>Nonaktif</option>
								</select>
							</div>
							<button class="btn btn-primary w-100"><i class="ti ti-device-floppy me-1"></i>Simpan Titik</button>
						</div>
					</div>
				</div>
			</div>
		<?= form_close(); ?>
	</div>
</div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
	var latInput = document.getElementById('reading-point-latitude');
	var lngInput = document.getElementById('reading-point-longitude');
	var mapEl = document.getElementById('reading-point-picker-map');
	if (! latInput || ! lngInput || ! mapEl || typeof L === 'undefined') {
		return;
	}

	var readNumber = function (input, fallback) {
		var value = parseFloat(String(input.value || '').replace(',', '.'));
		return Number.isFinite(value) ? value : fallback;
	};
	var lat = readNumber(latInput, -6.7071);
	var lng = readNumber(lngInput, 111.3502);
	var map = L.map(mapEl).setView([lat, lng], 13);
	var marker = L.marker([lat, lng], { draggable: true }).addTo(map);
	var circle = L.circle([lat, lng], {
		color: '#005baa',
		fillColor: '#005baa',
		fillOpacity: .08,
		radius: 120
	}).addTo(map);

	L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		maxZoom: 19,
		attribution: '&copy; OpenStreetMap'
	}).addTo(map);

	var writePosition = function (position) {
		latInput.value = position.lat.toFixed(7);
		lngInput.value = position.lng.toFixed(7);
		marker.setLatLng(position);
		circle.setLatLng(position);
	};

	marker.on('dragend', function (event) {
		writePosition(event.target.getLatLng());
	});
	map.on('click', function (event) {
		writePosition(event.latlng);
	});
});
</script>
