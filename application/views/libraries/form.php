<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$is_edit = ! empty($library);
$field = function ($key, $default = '') use ($library) {
	return $library[$key] ?? $default;
};
$lat = (float) $field('latitude', -6.7071);
$lng = (float) $field('longitude', 111.3502);
$village_json = json_encode($villages, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Perpustakaan GIS</div>
				<h1 class="page-title"><?= $is_edit ? 'Edit Perpustakaan' : 'Tambah Perpustakaan'; ?></h1>
			</div>
			<div class="col-auto ms-auto">
				<div class="btn-list">
					<?php if ($is_edit && (int) $field('is_verified') !== 1): ?>
						<a href="<?= base_url('libraries/verify/' . (int) $library['id']); ?>" class="btn btn-outline-success">Verifikasi</a>
					<?php endif; ?>
					<a href="<?= base_url('libraries'); ?>" class="btn btn-outline-secondary">Kembali</a>
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

		<?= form_open_multipart($action); ?>
			<div class="row row-cards">
				<div class="col-lg-7">
					<div class="card">
						<div class="card-header">
							<h2 class="card-title">Identitas</h2>
						</div>
						<div class="card-body">
							<div class="row">
								<div class="col-md-4 mb-3">
									<label class="form-label">Kode</label>
									<input type="text" class="form-control" name="code" value="<?= html_escape($field('code')); ?>" required>
								</div>
								<div class="col-md-8 mb-3">
									<label class="form-label">Nama perpustakaan</label>
									<input type="text" class="form-control" name="name" value="<?= html_escape($field('name')); ?>" required>
								</div>
							</div>
							<div class="row">
								<div class="col-md-6 mb-3">
									<label class="form-label">Jenis</label>
									<select name="library_type_id" class="form-select" required>
										<option value="">Pilih jenis</option>
										<?php foreach ($types as $type): ?>
											<option value="<?= (int) $type['id']; ?>" <?= (int) $field('library_type_id') === (int) $type['id'] ? 'selected' : ''; ?>>
												<?= html_escape($type['name']); ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
								<div class="col-md-6 mb-3">
									<label class="form-label">Pengelola/PIC</label>
									<input type="text" class="form-control" name="manager_name" value="<?= html_escape($field('manager_name')); ?>">
								</div>
							</div>
							<div class="mb-3">
								<label class="form-label">Alamat</label>
								<textarea class="form-control" name="address" rows="3"><?= html_escape($field('address')); ?></textarea>
							</div>
							<div class="row">
								<div class="col-md-6 mb-3">
									<label class="form-label">Kecamatan</label>
									<select class="form-select" id="district_id" name="district_id">
										<option value="">Pilih kecamatan</option>
										<?php foreach ($districts as $district): ?>
											<option value="<?= (int) $district['id']; ?>" <?= (int) $field('district_id') === (int) $district['id'] ? 'selected' : ''; ?>>
												<?= html_escape(($district['full_code'] ?: $district['code']) . ' - ' . $district['name']); ?>
											</option>
										<?php endforeach; ?>
									</select>
									<input type="hidden" name="district" value="<?= html_escape($field('district')); ?>">
								</div>
								<div class="col-md-6 mb-3">
									<label class="form-label">Desa / Kelurahan</label>
									<select class="form-select" id="village_id" name="village_id" data-current="<?= (int) $field('village_id'); ?>">
										<option value="">Pilih desa / kelurahan</option>
									</select>
									<input type="hidden" name="village" value="<?= html_escape($field('village')); ?>">
								</div>
							</div>
							<div class="row">
								<div class="col-md-4 mb-3">
									<label class="form-label">Telepon</label>
									<input type="text" class="form-control" name="phone" value="<?= html_escape($field('phone')); ?>">
								</div>
								<div class="col-md-4 mb-3">
									<label class="form-label">Email</label>
									<input type="email" class="form-control" name="email" value="<?= html_escape($field('email')); ?>">
								</div>
								<div class="col-md-4 mb-3">
									<label class="form-label">Website</label>
									<input type="text" class="form-control" name="website" value="<?= html_escape($field('website')); ?>">
								</div>
							</div>
							<div class="row">
								<div class="col-md-6 mb-3">
									<label class="form-label">Jam layanan</label>
									<input type="text" class="form-control" name="opening_hours" value="<?= html_escape($field('opening_hours')); ?>" placeholder="Senin-Jumat 08.00-15.00">
								</div>
								<div class="col-md-3 mb-3">
									<label class="form-label">Radius layanan (m)</label>
									<input type="number" class="form-control" name="service_radius_meters" value="<?= html_escape($field('service_radius_meters', 100)); ?>" min="10">
								</div>
								<div class="col-md-3 mb-3">
									<label class="form-label">Status</label>
									<select name="status" class="form-select">
										<?php foreach (['active' => 'Aktif', 'pending' => 'Pending', 'inactive' => 'Nonaktif'] as $value => $label): ?>
											<option value="<?= $value; ?>" <?= $field('status', 'active') === $value ? 'selected' : ''; ?>><?= $label; ?></option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>
							<label class="form-check mb-3">
								<input class="form-check-input" type="checkbox" name="is_verified" value="1" <?= (int) $field('is_verified') === 1 ? 'checked' : ''; ?>>
								<span class="form-check-label">Data sudah diverifikasi</span>
							</label>
							<div class="mb-3">
								<label class="form-label">Deskripsi</label>
								<textarea class="form-control" name="description" rows="3"><?= html_escape($field('description')); ?></textarea>
							</div>
							<div class="mb-3">
								<label class="form-label">Fasilitas</label>
								<textarea class="form-control" name="facilities" rows="2" placeholder="WiFi, ruang baca anak, komputer publik"><?= html_escape($field('facilities')); ?></textarea>
							</div>
						</div>
					</div>
				</div>

				<div class="col-lg-5">
					<div class="card mb-3">
						<div class="card-header">
							<h2 class="card-title">Koordinat</h2>
						</div>
						<div class="card-body">
							<div id="library-picker-map" class="leaflet-map leaflet-map-form"></div>
							<div class="row mt-3">
								<div class="col-md-6 mb-3">
									<label class="form-label">Latitude</label>
									<input type="text" class="form-control" id="latitude" name="latitude" value="<?= html_escape($lat); ?>" required>
								</div>
								<div class="col-md-6 mb-3">
									<label class="form-label">Longitude</label>
									<input type="text" class="form-control" id="longitude" name="longitude" value="<?= html_escape($lng); ?>" required>
								</div>
							</div>
						</div>
					</div>

					<div class="card mb-3">
						<div class="card-header">
							<h2 class="card-title">Foto</h2>
						</div>
						<div class="card-body">
							<div class="mb-3">
								<label class="form-label">Upload foto</label>
								<input type="file" class="form-control" name="photo" accept=".jpg,.jpeg,.png,.webp">
							</div>
							<div class="mb-3">
								<label class="form-label">Caption foto</label>
								<input type="text" class="form-control" name="photo_caption">
							</div>

							<?php if (! empty($photos)): ?>
								<div class="library-photo-grid">
									<?php foreach ($photos as $photo): ?>
										<div class="library-photo-item">
											<img src="<?= base_url($photo['file_path']); ?>" alt="<?= html_escape($photo['caption'] ?: 'Foto perpustakaan'); ?>">
											<div class="d-flex align-items-center gap-2 mt-2">
												<div class="small text-secondary flex-fill"><?= html_escape($photo['caption'] ?: 'Tanpa caption'); ?></div>
												<?php if ((int) $photo['is_cover'] === 1): ?>
													<span class="badge bg-green-lt">cover</span>
												<?php endif; ?>
											</div>
											<div class="btn-list mt-2">
												<?php if ((int) $photo['is_cover'] !== 1): ?>
													<a class="btn btn-sm btn-outline-primary" href="<?= base_url('libraries/photos/set-cover/' . (int) $photo['id']); ?>">Jadikan Cover</a>
												<?php endif; ?>
												<a class="btn btn-sm btn-outline-danger" href="<?= base_url('libraries/photos/delete/' . (int) $photo['id']); ?>" onclick="return confirm('Hapus foto ini dari galeri?')">Hapus</a>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>
					</div>

					<div class="card">
						<div class="card-body text-end">
							<button type="submit" class="btn btn-primary"><?= $is_edit ? 'Simpan Perubahan' : 'Simpan Perpustakaan'; ?></button>
						</div>
					</div>
				</div>
			</div>
		<?= form_close(); ?>
	</div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
	var latInput = document.getElementById('latitude');
	var lngInput = document.getElementById('longitude');
	var lat = parseFloat(latInput.value || '-6.7071');
	var lng = parseFloat(lngInput.value || '111.3502');
	var map = L.map('library-picker-map').setView([lat, lng], 13);
	var marker = L.marker([lat, lng], { draggable: true }).addTo(map);

	L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		maxZoom: 19,
		attribution: '&copy; OpenStreetMap'
	}).addTo(map);

	function setPosition(position) {
		var nextLat = position.lat.toFixed(7);
		var nextLng = position.lng.toFixed(7);
		latInput.value = nextLat;
		lngInput.value = nextLng;
		marker.setLatLng(position);
	}

	map.on('click', function (event) {
		setPosition(event.latlng);
	});

	marker.on('dragend', function () {
		setPosition(marker.getLatLng());
	});

	var villageMap = <?= $village_json ?: '{}'; ?>;
	var districtSelect = document.getElementById('district_id');
	var villageSelect = document.getElementById('village_id');
	var currentVillage = parseInt(villageSelect.dataset.current || '0', 10);

	function renderVillages() {
		var districtId = parseInt(districtSelect.value || '0', 10);
		var villages = villageMap[districtId] || [];
		villageSelect.innerHTML = '<option value="">Pilih desa / kelurahan</option>';
		villages.forEach(function (village) {
			var option = document.createElement('option');
			option.value = village.id;
			option.textContent = village.name;
			if (parseInt(village.id, 10) === currentVillage) {
				option.selected = true;
			}
			villageSelect.appendChild(option);
		});
	}

	districtSelect.addEventListener('change', function () {
		currentVillage = 0;
		renderVillages();
	});
	renderVillages();
})();
</script>
