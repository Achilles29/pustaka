<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$is_edit = ! empty($library);
$field = function ($key, $default = '') use ($library) {
	return $library[$key] ?? $default;
};
$lat = (float) $field('latitude', -6.7071);
$lng = (float) $field('longitude', 111.3502);
$village_json = json_encode($villages, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$day_options = [
	'Senin',
	'Selasa',
	'Rabu',
	'Kamis',
	'Jumat',
	'Sabtu',
	'Minggu',
];
$opening_hours_raw = (string) $field('opening_hours');
$opening_schedule = [];
foreach ($day_options as $day) {
	$opening_schedule[$day] = [
		'checked' => ! $is_edit && in_array($day, ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'], true),
		'open' => '08:00',
		'close' => '15:00',
	];
}
if ($opening_hours_raw !== '') {
	foreach ($day_options as $day) {
		if (preg_match('/' . preg_quote($day, '/') . '\s+(\d{1,2})[:.](\d{2})\s*-\s*(\d{1,2})[:.](\d{2})/i', $opening_hours_raw, $match)) {
			$opening_schedule[$day] = [
				'checked' => true,
				'open' => str_pad($match[1], 2, '0', STR_PAD_LEFT) . ':' . $match[2],
				'close' => str_pad($match[3], 2, '0', STR_PAD_LEFT) . ':' . $match[4],
			];
		}
	}
}
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
			<input type="hidden" name="libraries_csrf" value="<?= html_escape($this->session->userdata('libraries_csrf')); ?>">
			<div class="row row-cards">
				<div class="col-lg-7">
					<div class="card">
						<div class="card-header">
							<h2 class="card-title">Identitas</h2>
						</div>
						<div class="card-body">
							<div class="row">
								<div class="col-md-4 mb-3">
									<label class="form-label">Kode / NPSN untuk sekolah</label>
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
									<select name="library_type_id" id="library-type" class="form-select" required>
										<option value="">Pilih jenis</option>
										<?php foreach ($types as $type): ?>
											<option value="<?= (int) $type['id']; ?>" <?= (int) $field('library_type_id') === (int) $type['id'] ? 'selected' : ''; ?>>
												<?= html_escape($type['name']); ?>
											</option>
										<?php endforeach; ?>
									</select>
									<label class="form-label mt-2">Subjenis</label>
									<select name="library_subtype_id" id="library-subtype" class="form-select"><option value="">Pilih subjenis</option><?php foreach(($subtypes??[]) as $subtype): ?><option value="<?= (int)$subtype['id']; ?>" data-type="<?= (int)$subtype['library_type_id']; ?>" <?= (int)$field('library_subtype_id')===(int)$subtype['id']?'selected':''; ?>><?= html_escape($subtype['name']); ?></option><?php endforeach; ?></select>
									<label class="form-label mt-2">Nama institusi/sekolah</label><input class="form-control" name="institution_name" maxlength="180" value="<?= html_escape($field('institution_name')); ?>">
									<label class="form-label mt-2">Nomor Pokok Perpustakaan (NPP)</label><input class="form-control" name="npp" maxlength="80" value="<?= html_escape($field('npp')); ?>">
									<label class="form-label mt-2">Status institusi sekolah</label><select class="form-select" name="institution_status"><?php foreach([''=>'Tidak berlaku / belum ditetapkan','negeri'=>'Negeri','swasta'=>'Swasta','belum_diketahui'=>'Perlu verifikasi']as$key=>$label): ?><option value="<?= $key; ?>" <?= $field('institution_status')===$key?'selected':''; ?>><?= $label; ?></option><?php endforeach; ?></select>
								</div>
								<div class="col-md-6 mb-3">
									<label class="form-label">Pengelola/PIC</label>
									<input type="text" class="form-control" name="manager_name" required value="<?= html_escape($field('manager_name')); ?>">
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
									<input type="text" class="form-control" name="phone" required value="<?= html_escape($field('phone')); ?>">
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
							<div class="mb-3">
								<label class="form-label">Jam Layanan</label>
								<input type="hidden" id="opening-hours-value" name="opening_hours" value="<?= html_escape($field('opening_hours')); ?>">
								<div class="library-hours-grid" id="library-hours-grid">
									<?php foreach ($day_options as $day): ?>
										<?php $row = $opening_schedule[$day]; ?>
										<div class="library-hours-row">
											<label class="form-check library-hours-day">
												<input class="form-check-input library-hours-check" type="checkbox" value="<?= html_escape($day); ?>" <?= ! empty($row['checked']) ? 'checked' : ''; ?>>
												<span class="form-check-label"><?= html_escape($day); ?></span>
											</label>
											<div class="library-hours-time">
												<input type="time" class="form-control library-hours-open" value="<?= html_escape($row['open']); ?>" aria-label="Jam buka <?= html_escape($day); ?>">
												<span>-</span>
												<input type="time" class="form-control library-hours-close" value="<?= html_escape($row['close']); ?>" aria-label="Jam tutup <?= html_escape($day); ?>">
											</div>
										</div>
									<?php endforeach; ?>
								</div>
								<div class="form-hint">Checklist hari aktif lalu isi jam buka dan tutup. Ringkasan otomatis disimpan ke data perpustakaan.</div>
								<?php if ($is_edit && $opening_hours_raw !== ''): ?>
									<div class="form-hint">Data saat ini: <?= html_escape($opening_hours_raw); ?></div>
								<?php endif; ?>
							</div>
							<div class="row">
								<div class="col-md-6 mb-3">
									<label class="form-label">Radius layanan (m)</label>
									<input type="number" class="form-control" name="service_radius_meters" value="<?= html_escape($field('service_radius_meters', 100)); ?>" min="10">
								</div>
								<div class="col-md-6 mb-3">
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
		<div class="card card-body mt-3"><label class="form-label" for="iplm-population-status">Seleksi populasi IPLM</label><select class="form-select" id="iplm-population-status" name="iplm_population_status"><?php foreach(['pending'=>'Belum dipilih / perlu verifikasi','included'=>'Dipilih: memiliki perpustakaan dan sesuai kewenangan','excluded'=>'Dikecualikan dari populasi'] as $key=>$label): ?><option value="<?= $key; ?>" <?= ($library['iplm_population_status']??'pending')===$key?'selected':''; ?>><?= $label; ?></option><?php endforeach; ?></select><small class="text-secondary mt-2">Unit baru tidak otomatis dihitung. Mode otomatis periode hanya menghitung unit dipilih yang memenuhi cakupan. Perubahan ini tidak mengubah snapshot periode yang sudah disimpan.</small></div>
<?= form_close(); ?>
	</div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded',function(){
    var type=document.getElementById('library-type'),sub=document.getElementById('library-subtype');
    if(!type||!sub)return;
    function refresh(reset){var count=0;Array.from(sub.options).forEach(function(option){var match=!option.value||option.dataset.type===type.value;option.hidden=!match;option.disabled=!match;if(option.value&&match)count++;});sub.required=count>0;if(reset&&sub.selectedOptions[0]&&sub.selectedOptions[0].disabled)sub.value='';}
    refresh(false);type.addEventListener('change',function(){refresh(true);});
});
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

	var hoursGrid = document.getElementById('library-hours-grid');
	var openingHidden = document.getElementById('opening-hours-value');

	function syncOpeningHours() {
		if (! hoursGrid || ! openingHidden) {
			return;
		}

		var parts = [];
		hoursGrid.querySelectorAll('.library-hours-row').forEach(function (row) {
			var check = row.querySelector('.library-hours-check');
			var open = row.querySelector('.library-hours-open');
			var close = row.querySelector('.library-hours-close');
			if (! check || ! check.checked) {
				return;
			}

			var start = open && open.value ? open.value : '08:00';
			var end = close && close.value ? close.value : '15:00';
			parts.push(check.value + ' ' + start + '-' + end);
		});

		openingHidden.value = parts.join('; ');
	}

	if (hoursGrid && openingHidden) {
		hoursGrid.addEventListener('change', syncOpeningHours);
		hoursGrid.addEventListener('input', syncOpeningHours);
		var form = openingHidden.closest('form');
		if (form) {
			form.addEventListener('submit', syncOpeningHours);
		}
		syncOpeningHours();
	}
})();
</script>
