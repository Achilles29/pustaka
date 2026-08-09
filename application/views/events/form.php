<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$is_edit = ! empty($event);
$field = function ($key, $default = '') use ($event) {
	return $event[$key] ?? $default;
};
$date_value = function ($key) use ($field) {
	$value = $field($key);
	return $value ? date('Y-m-d', strtotime($value)) : '';
};
$time_value = function ($key, $default = '') use ($field) {
	$value = $field($key);
	return $value ? date('H:i', strtotime($value)) : $default;
};
$library_payload = [];
foreach ($libraries as $library) {
	$library_payload[] = [
		'id' => (int) $library['id'],
		'name' => $library['name'],
		'address' => $library['address'] ?? '',
		'latitude' => $library['latitude'] ?? null,
		'longitude' => $library['longitude'] ?? null,
	];
}
$library_json = json_encode($library_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$event_lat = $field('latitude') ?: -6.7071;
$event_lng = $field('longitude') ?: 111.3502;
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Event Literasi</div>
				<h1 class="page-title"><?= $is_edit ? 'Edit Event' : 'Tambah Event'; ?></h1>
			</div>
			<div class="col-auto ms-auto">
				<a href="<?= $is_edit ? base_url('events/detail/' . (int) $event['id']) : base_url('events'); ?>" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i>Kembali</a>
			</div>
		</div>
	</div>
</div>

<div class="page-body">
	<div class="container-xl">
		<?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?= html_escape($this->session->flashdata('error')); ?></div><?php endif; ?>

		<?= form_open_multipart($action); ?>
			<div class="row row-cards">
				<div class="col-lg-8">
					<div class="card admin-card">
						<div class="card-header"><h2 class="card-title">Identitas Event</h2></div>
						<div class="card-body">
							<div class="mb-3">
								<label class="form-label">Judul Event</label>
								<input type="text" class="form-control form-control-lg" name="title" value="<?= html_escape($field('title')); ?>" required placeholder="Contoh: Kelas Literasi Digital untuk Remaja">
							</div>
							<div class="row">
								<div class="col-md-6 mb-3">
									<label class="form-label">Kategori</label>
									<div class="input-group">
										<select class="form-select" name="event_category_id">
											<option value="">Belum dipilih</option>
											<?php foreach ($categories as $category): ?>
												<option value="<?= (int) $category['id']; ?>" <?= (int) $field('event_category_id') === (int) $category['id'] ? 'selected' : ''; ?>><?= html_escape($category['name']); ?></option>
											<?php endforeach; ?>
										</select>
										<button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-event-category"><i class="ti ti-plus"></i></button>
									</div>
								</div>
								<div class="col-md-6 mb-3">
									<label class="form-label">Perpustakaan Penyelenggara</label>
									<select class="form-select" id="event-library-id" name="library_id">
										<option value="">Pustaka Digital Rembang / eksternal</option>
										<?php foreach ($libraries as $library): ?>
											<option value="<?= (int) $library['id']; ?>" <?= (int) $field('library_id') === (int) $library['id'] ? 'selected' : ''; ?>><?= html_escape($library['name']); ?></option>
										<?php endforeach; ?>
									</select>
									<div class="form-hint">Jika perpustakaan punya titik GIS, koordinat event akan mengikuti titik tersebut.</div>
								</div>
							</div>
							<div class="mb-3">
								<label class="form-label">Ringkasan</label>
								<input type="text" class="form-control" name="summary" value="<?= html_escape($field('summary')); ?>" maxlength="255" placeholder="Kalimat singkat untuk kartu publik">
							</div>
							<div class="mb-3">
								<label class="form-label">Deskripsi</label>
								<textarea class="form-control" name="description" rows="6" placeholder="Tuliskan detail kegiatan, manfaat, alur acara, dan ketentuan peserta."><?= html_escape($field('description')); ?></textarea>
							</div>
							<div class="row">
								<div class="col-md-4 mb-3">
									<label class="form-label">Jenis / Tema</label>
									<input type="text" class="form-control" name="event_type" value="<?= html_escape($field('event_type')); ?>" placeholder="Workshop, lomba, webinar">
								</div>
								<div class="col-md-4 mb-3">
									<label class="form-label">Penyelenggara</label>
									<input type="text" class="form-control" name="organizer_name" value="<?= html_escape($field('organizer_name')); ?>" placeholder="Dinarpusar / mitra">
								</div>
								<div class="col-md-4 mb-3">
									<label class="form-label">Narasumber</label>
									<input type="text" class="form-control" name="speaker_name" value="<?= html_escape($field('speaker_name')); ?>" placeholder="Opsional">
								</div>
							</div>
							<div class="mb-3">
								<label class="form-label">Target Peserta</label>
								<input type="text" class="form-control" name="target_audience" value="<?= html_escape($field('target_audience')); ?>" placeholder="Pelajar SMP/SMA, guru, umum, komunitas">
							</div>
							<div class="mb-3">
								<label class="form-label">Poster</label>
								<input type="file" class="form-control" name="poster" accept=".jpg,.jpeg,.png,.webp">
								<?php if ($field('poster_path')): ?>
									<div class="form-hint">Poster saat ini: <a href="<?= base_url($field('poster_path')); ?>" target="_blank" rel="noopener">lihat poster</a></div>
								<?php endif; ?>
							</div>
						</div>
					</div>

					<div class="card admin-card mt-3">
						<div class="card-header"><h2 class="card-title">Jadwal dan Lokasi</h2></div>
						<div class="card-body">
							<div class="row">
								<div class="col-md-6 mb-3">
									<label class="form-label">Tanggal Mulai</label>
									<input type="date" class="form-control" name="starts_date" value="<?= html_escape($date_value('starts_at')); ?>">
								</div>
								<div class="col-md-6 mb-3">
									<label class="form-label">Jam Mulai</label>
									<input type="time" class="form-control" name="starts_time" value="<?= html_escape($time_value('starts_at', '08:00')); ?>">
								</div>
								<div class="col-md-6 mb-3">
									<label class="form-label">Tanggal Selesai</label>
									<input type="date" class="form-control" name="ends_date" value="<?= html_escape($date_value('ends_at')); ?>">
								</div>
								<div class="col-md-6 mb-3">
									<label class="form-label">Jam Selesai</label>
									<input type="time" class="form-control" name="ends_time" value="<?= html_escape($time_value('ends_at', '10:00')); ?>">
								</div>
							</div>
							<div class="row">
								<div class="col-md-4 mb-3">
									<label class="form-label">Mode Lokasi</label>
									<select class="form-select" name="venue_type">
										<option value="onsite" <?= $field('venue_type', 'onsite') === 'onsite' ? 'selected' : ''; ?>>Tatap muka</option>
										<option value="online" <?= $field('venue_type') === 'online' ? 'selected' : ''; ?>>Online</option>
										<option value="hybrid" <?= $field('venue_type') === 'hybrid' ? 'selected' : ''; ?>>Hybrid</option>
									</select>
								</div>
								<div class="col-md-8 mb-3">
									<label class="form-label">Nama Lokasi</label>
									<input type="text" class="form-control" name="location_name" value="<?= html_escape($field('location_name')); ?>" placeholder="Aula Perpustakaan Daerah / Zoom / Namua">
								</div>
							</div>
							<div class="mb-3">
								<label class="form-label">Link Online</label>
								<input type="url" class="form-control" name="online_url" value="<?= html_escape($field('online_url')); ?>" placeholder="https://...">
							</div>
							<div class="mb-3">
								<label class="form-label">Pin Lokasi Event</label>
								<div id="event-picker-map" class="leaflet-map leaflet-map-form event-location-map"></div>
								<div class="form-hint" id="event-location-hint">Pilih perpustakaan penyelenggara atau geser pin untuk menentukan lokasi event.</div>
							</div>
							<div class="row">
								<div class="col-md-6 mb-3">
									<label class="form-label">Latitude</label>
									<input type="text" class="form-control" id="event-latitude" name="latitude" value="<?= html_escape($event_lat); ?>" placeholder="-6.7071000">
								</div>
								<div class="col-md-6 mb-3">
									<label class="form-label">Longitude</label>
									<input type="text" class="form-control" id="event-longitude" name="longitude" value="<?= html_escape($event_lng); ?>" placeholder="111.3502000">
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="col-lg-4">
					<div class="card admin-card">
						<div class="card-header"><h2 class="card-title">Aturan Pendaftaran</h2></div>
						<div class="card-body">
							<div class="mb-3">
								<label class="form-label">Status Publikasi</label>
								<select class="form-select" name="status">
									<option value="draft" <?= $field('status', 'draft') === 'draft' ? 'selected' : ''; ?>>Draft</option>
									<option value="published" <?= $field('status') === 'published' ? 'selected' : ''; ?>>Tayang</option>
									<option value="closed" <?= $field('status') === 'closed' ? 'selected' : ''; ?>>Selesai</option>
									<option value="cancelled" <?= $field('status') === 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
								</select>
							</div>
							<div class="mb-3">
								<label class="form-label">Mode Pendaftaran</label>
								<select class="form-select" name="registration_mode">
									<option value="open" <?= $field('registration_mode', 'open') === 'open' ? 'selected' : ''; ?>>Publik</option>
									<option value="member_only" <?= $field('registration_mode') === 'member_only' ? 'selected' : ''; ?>>Khusus member</option>
									<option value="invite" <?= $field('registration_mode') === 'invite' ? 'selected' : ''; ?>>Undangan</option>
									<option value="none" <?= $field('registration_mode') === 'none' ? 'selected' : ''; ?>>Tanpa pendaftaran</option>
								</select>
							</div>
							<div class="mb-3">
								<label class="form-label">Verifikasi</label>
								<select class="form-select" name="approval_mode">
									<option value="auto" <?= $field('approval_mode', 'auto') === 'auto' ? 'selected' : ''; ?>>Otomatis diterima</option>
									<option value="manual" <?= $field('approval_mode') === 'manual' ? 'selected' : ''; ?>>Admin verifikasi dulu</option>
								</select>
							</div>
							<div class="mb-3">
								<label class="form-label">Kuota Peserta</label>
								<input type="number" min="0" class="form-control" name="quota" value="<?= html_escape($field('quota')); ?>" placeholder="Kosong = tanpa batas">
							</div>
							<div class="mb-3">
								<label class="form-label">Pendaftaran Dibuka</label>
								<div class="row g-2">
									<div class="col-7"><input type="date" class="form-control" name="registration_opens_date" value="<?= html_escape($date_value('registration_opens_at')); ?>"></div>
									<div class="col-5"><input type="time" class="form-control" name="registration_opens_time" value="<?= html_escape($time_value('registration_opens_at', '00:00')); ?>"></div>
								</div>
							</div>
							<div class="mb-3">
								<label class="form-label">Pendaftaran Ditutup</label>
								<div class="row g-2">
									<div class="col-7"><input type="date" class="form-control" name="registration_closes_date" value="<?= html_escape($date_value('registration_closes_at')); ?>"></div>
									<div class="col-5"><input type="time" class="form-control" name="registration_closes_time" value="<?= html_escape($time_value('registration_closes_at', '23:59')); ?>"></div>
								</div>
							</div>
							<label class="form-check">
								<input class="form-check-input" type="checkbox" name="attendance_enabled" value="1" <?= (int) $field('attendance_enabled', 1) === 1 ? 'checked' : ''; ?>>
								<span class="form-check-label">Aktifkan QR attendance</span>
							</label>
							<label class="form-check mt-2">
								<input class="form-check-input" type="checkbox" name="certificate_enabled" value="1" <?= (int) $field('certificate_enabled') === 1 ? 'checked' : ''; ?>>
								<span class="form-check-label">Siapkan sertifikat digital</span>
							</label>
							<div class="mt-4">
								<button class="btn btn-primary w-100"><i class="ti ti-device-floppy me-1"></i>Simpan Event</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?= form_close(); ?>
	</div>
</div>

<div class="modal fade" id="modal-event-category" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<?= form_open('events/categories/store'); ?>
				<input type="hidden" name="return_to" value="<?= html_escape(uri_string() ?: 'events/create'); ?>">
				<div class="modal-header">
					<h5 class="modal-title">Tambah Kategori Event</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
				</div>
				<div class="modal-body">
					<div class="mb-3">
						<label class="form-label">Nama Kategori</label>
						<input type="text" class="form-control" name="name" required placeholder="Contoh: Literasi Keuangan">
					</div>
					<div class="mb-3">
						<label class="form-label">Deskripsi</label>
						<input type="text" class="form-control" name="description" placeholder="Opsional">
					</div>
					<div class="row">
						<div class="col-md-6 mb-3">
							<label class="form-label">Warna</label>
							<input type="color" class="form-control form-control-color" name="color" value="#005baa">
						</div>
						<div class="col-md-6 mb-3">
							<label class="form-label">Urutan</label>
							<input type="number" class="form-control" name="sort_order" value="100" min="0">
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
					<button class="btn btn-primary"><i class="ti ti-plus me-1"></i>Tambah</button>
				</div>
			<?= form_close(); ?>
		</div>
	</div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
	var libraries = <?= $library_json ?: '[]'; ?>;
	var libraryMap = {};
	libraries.forEach(function (library) {
		libraryMap[String(library.id)] = library;
	});

	var latInput = document.getElementById('event-latitude');
	var lngInput = document.getElementById('event-longitude');
	var librarySelect = document.getElementById('event-library-id');
	var locationInput = document.querySelector('input[name="location_name"]');
	var hint = document.getElementById('event-location-hint');
	var mapEl = document.getElementById('event-picker-map');
	if (!latInput || !lngInput || !librarySelect || !mapEl || typeof L === 'undefined') {
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

	L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		maxZoom: 19,
		attribution: '&copy; OpenStreetMap'
	}).addTo(map);

	var writePosition = function (position, zoom) {
		latInput.value = position.lat.toFixed(7);
		lngInput.value = position.lng.toFixed(7);
		marker.setLatLng(position);
		map.setView(position, zoom || map.getZoom());
	};

	var applyLibraryPosition = function () {
		var library = libraryMap[String(librarySelect.value || '')];
		if (!library) {
			if (hint) {
				hint.textContent = 'Pilih perpustakaan penyelenggara atau geser pin untuk menentukan lokasi event.';
			}
			return;
		}

		if (locationInput && locationInput.value.trim() === '') {
			locationInput.value = library.name;
		}

		var libraryLat = parseFloat(library.latitude);
		var libraryLng = parseFloat(library.longitude);
		if (Number.isFinite(libraryLat) && Number.isFinite(libraryLng)) {
			writePosition({ lat: libraryLat, lng: libraryLng }, 15);
			if (hint) {
				hint.textContent = 'Koordinat mengikuti titik GIS perpustakaan penyelenggara. Geser pin jika event berada di ruang/lokasi berbeda.';
			}
			return;
		}

		if (hint) {
			hint.textContent = 'Perpustakaan ini belum punya titik GIS. Geser pin pada peta untuk menentukan koordinat event.';
		}
	};

	map.on('click', function (event) {
		writePosition(event.latlng);
	});
	marker.on('dragend', function (event) {
		writePosition(event.target.getLatLng());
	});
	librarySelect.addEventListener('change', applyLibraryPosition);
	applyLibraryPosition();
	setTimeout(function () { map.invalidateSize(); }, 250);
});
</script>
