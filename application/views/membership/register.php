<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tabler_css = 'https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css';
$tabler_icons_css = 'https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/tabler-icons.min.css';
$old = (array) $this->session->flashdata('registration_old');
$field = function ($key, $default = '') use ($old) {
	return $old[$key] ?? $default;
};
$form_options = $form_options ?? [
	'genders' => ['Laki-laki', 'Perempuan'],
	'member_types' => ['Umum'],
	'educations' => [],
	'occupations' => [],
];
$options_for = function ($group, $current = '') use ($form_options) {
	$options = $form_options[$group] ?? [];
	if ($current !== '' && ! in_array($current, $options, true)) {
		array_unshift($options, $current);
	}
	return array_values(array_unique($options));
};
$districts = $districts ?? [];
$villages = $villages ?? [];
$provinces = $provinces ?? [];
$village_json = json_encode($villages, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= html_escape($title); ?></title>
	<link rel="icon" href="<?= base_url('img/favicon.ico'); ?>" type="image/x-icon">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Fraunces:opsz,wght@9..144,650;9..144,750;9..144,850&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="<?= $tabler_css; ?>">
	<link rel="stylesheet" href="<?= $tabler_icons_css; ?>">
	<link rel="stylesheet" href="<?= base_url('assets/css/pustaka.css'); ?>">
	<link rel="stylesheet" href="<?= base_url('assets/css/pustaka-polish.css?v=20260810p'); ?>">
</head>
<body class="public-page public-register-page">
	<header class="public-nav">
		<a class="public-brand" href="<?= base_url(); ?>">
			<span class="brand-logo-shell"><img class="brand-logo" src="<?= base_url('img/logo-small.jpeg'); ?>" alt="Logo Kabupaten Rembang"></span>
			<span class="public-brand-text">Pustaka Digital Rembang</span>
		</a>
		<nav class="public-links">
			<a href="<?= base_url(); ?>">Beranda</a>
			<a href="<?= base_url('katalog'); ?>">Katalog</a>
			<a href="<?= base_url('agenda'); ?>">Agenda</a>
			<a href="<?= base_url('membership/registration-status'); ?>" class="btn btn-outline-primary btn-sm">Cek Status</a>
			<a href="<?= base_url('login'); ?>" class="btn btn-primary btn-sm">Masuk</a>
		</nav>
	</header>

	<main class="public-register-main">
		<div class="container-xl">
			<div class="register-shell register-shell-v2">
				<div class="register-intro register-intro-v2">
					<div class="hero-logo-row mb-3">
						<span class="hero-logo-card">
							<img src="<?= base_url('img/logo-small.jpeg'); ?>" alt="Logo Kabupaten Rembang">
						</span>
						<span class="hero-logo-card hero-logo-card-wide">
							<img src="<?= base_url('img/perpusnas.png'); ?>" alt="Logo Perpusnas">
						</span>
					</div>
					<div class="section-kicker">Pendaftaran online</div>
					<h1>Daftar member tanpa datang dulu.</h1>
					<p>Isi data sesuai identitas, unggah berkas, lalu admin akan memverifikasi sebelum akun anggota aktif.</p>
					<div class="register-rule-list">
						<div><i class="ti ti-id"></i><span>Unggah foto dan pilih salah satu: KTP atau Kartu Keluarga.</span></div>
						<div><i class="ti ti-map-pin"></i><span>Bila dokumen identitas dari luar Rembang, surat keterangan dan catatan domisili dapat diisi bila diperlukan.</span></div>
						<div><i class="ti ti-file-check"></i><span>Target ukuran setiap berkas maksimal 2 MB; berkas lebih besar akan dicoba dikompresi otomatis.</span></div>
						<div><i class="ti ti-shield-check"></i><span>Akun aktif setelah data diverifikasi admin.</span></div>
					</div>
					<div class="auth-secondary-link mt-3">
						Sudah punya akun? <a href="<?= base_url('login'); ?>">Masuk ke dashboard</a>
					</div>
				</div>
				<div class="register-form-panel register-form-panel-v2">
					<div class="register-form-head">
						<div class="section-kicker">Data calon anggota</div>
						<h2>Identitas, kontak, alamat, dan berkas verifikasi.</h2>
						<p>Username akun akan memakai NIK. Password awal dapat dilihat setelah pendaftaran diverifikasi.</p>
					</div>
					<?php if ($this->session->flashdata('registration_success')): ?>
						<div class="alert alert-success"><?= html_escape($this->session->flashdata('registration_success')); ?></div>
					<?php endif; ?>
					<?php if ($this->session->flashdata('registration_error')): ?>
						<div class="alert alert-danger" role="alert"><strong>Pendaftaran belum terkirim.</strong><br><?= html_escape($this->session->flashdata('registration_error')); ?><div class="small mt-1">Periksa kembali berkas yang disebutkan, lalu pilih ulang berkas tersebut sebelum mengirim formulir.</div></div>
					<?php endif; ?>

					<?= form_open_multipart('membership/register/submit', ['class' => 'public-member-register-form']); ?>
						<div class="row">
							<div class="col-md-8 mb-3">
								<label class="form-label">Nama Lengkap</label>
								<input type="text" class="form-control" name="full_name" value="<?= html_escape($field('full_name')); ?>" required>
							</div>
							<div class="col-md-4 mb-3">
								<label class="form-label">NIK (sesuai KTP/KK)</label>
								<input type="text" class="form-control" name="identity_number" value="<?= html_escape($field('identity_number')); ?>" required inputmode="numeric" pattern="[0-9]{16}" maxlength="16" title="NIK harus terdiri dari 16 digit angka.">
								<div class="form-hint">NIK harus tepat 16 digit dan hanya dapat digunakan untuk satu member. Domisili ditentukan dari alamat KTP/KK, bukan awalan NIK.</div>
							</div>
						</div>
						<fieldset class="mb-3" aria-describedby="domicile-help">
							<legend class="form-label mb-2">Apakah Anda warga Rembang?</legend>
							<div class="identity-choice-group identity-choice-group-compact">
								<label class="identity-choice"><input type="radio" name="identity_domicile" value="rembang" <?= $field('identity_domicile', 'rembang') === 'rembang' ? 'checked' : ''; ?> required><span><strong>Ya, warga Rembang</strong><small>Alamat pada KTP/KK berada di Kabupaten Rembang.</small></span></label>
								<label class="identity-choice"><input type="radio" name="identity_domicile" value="outside_rembang" <?= $field('identity_domicile') === 'outside_rembang' ? 'checked' : ''; ?>><span><strong>Bukan warga Rembang</strong><small>Alamat pada KTP/KK berada di luar Kabupaten Rembang.</small></span></label>
							</div>
							<div id="domicile-help" class="form-hint">Pilihan ini berdasarkan alamat pada identitas, bukan awalan NIK.</div>
						</fieldset>
						<div class="row">
							<div class="col-md-4 mb-3">
								<label class="form-label">Jenis Kelamin</label>
								<select class="form-select" name="gender">
									<option value="">Pilih</option>
									<?php foreach ($options_for('genders', $field('gender')) as $option): ?>
										<option value="<?= html_escape($option); ?>" <?= $field('gender') === $option ? 'selected' : ''; ?>><?= html_escape($option); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-md-4 mb-3">
								<label class="form-label">Tempat Lahir</label>
								<input type="text" class="form-control" name="birth_place" value="<?= html_escape($field('birth_place')); ?>">
							</div>
							<div class="col-md-4 mb-3">
								<label class="form-label">Tanggal Lahir</label>
								<input type="date" class="form-control" name="birth_date" value="<?= html_escape($field('birth_date')); ?>">
							</div>
						</div>
						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="form-label">No HP</label>
								<input type="text" class="form-control" name="phone" value="<?= html_escape($field('phone')); ?>">
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Email</label>
								<input type="email" class="form-control" name="email" value="<?= html_escape($field('email')); ?>">
							</div>
						</div>
						<div class="resident-location-fields">
							<div class="row">
								<div class="col-md-6 mb-3">
									<label class="form-label" for="district_id">Kecamatan</label>
									<select id="district_id" class="form-select" name="district_id" data-current="<?= (int) $field('district_id'); ?>">
										<option value="">Pilih kecamatan</option>
										<?php foreach ($districts as $district): ?>
											<option value="<?= (int) $district['id']; ?>" <?= (int) $field('district_id') === (int) $district['id'] ? 'selected' : ''; ?>><?= html_escape($district['name']); ?></option>
										<?php endforeach; ?>
									</select>
								</div>
								<div class="col-md-6 mb-3">
									<label class="form-label" for="village_id">Desa / Kelurahan</label>
									<select id="village_id" class="form-select" name="village_id" data-current="<?= (int) $field('village_id'); ?>">
										<option value="">Pilih desa / kelurahan</option>
									</select>
								</div>
							</div>
							<div class="mb-3">
								<label class="form-label" for="address">Alamat Lengkap</label>
								<textarea id="address" class="form-control" name="address" rows="3" placeholder="Jalan, RT/RW, nomor rumah"><?= html_escape($field('address')); ?></textarea>
							</div>
						</div>
						<div class="outside-location-fields" hidden>
							<?= $this->load->view('membership/_national_address_fields', ['prefix' => 'identity', 'title' => 'Alamat Sesuai KTP / KK', 'field' => $field, 'provinces' => $provinces], true); ?>
							<?= $this->load->view('membership/_national_address_fields', ['prefix' => 'domicile', 'title' => 'Alamat Domisili Saat Ini', 'field' => $field, 'provinces' => $provinces], true); ?>
						</div>
						<div class="row">
							<div class="col-md-4 mb-3">
								<label class="form-label">Tipe Member</label>
								<select class="form-select" name="member_type">
									<?php foreach ($options_for('member_types', $field('member_type', 'Umum')) as $option): ?>
										<option value="<?= html_escape($option); ?>" <?= $field('member_type', 'Umum') === $option ? 'selected' : ''; ?>><?= html_escape($option); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-md-4 mb-3">
								<label class="form-label">Pendidikan</label>
								<select class="form-select" name="education">
									<option value="">Pilih</option>
									<?php foreach ($options_for('educations', $field('education')) as $option): ?>
										<option value="<?= html_escape($option); ?>" <?= $field('education') === $option ? 'selected' : ''; ?>><?= html_escape($option); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-md-4 mb-3">
								<label class="form-label">Pekerjaan</label>
								<select class="form-select" name="occupation">
									<option value="">Pilih</option>
									<?php foreach ($options_for('occupations', $field('occupation')) as $option): ?>
										<option value="<?= html_escape($option); ?>" <?= $field('occupation') === $option ? 'selected' : ''; ?>><?= html_escape($option); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
						<div class="registration-files">
							<div class="mb-3">
								<label class="form-label">Foto Diri</label>
								<input type="file" class="form-control" name="photo_file" accept=".jpg,.jpeg,.png" required data-upload-file>
								<div class="form-hint">JPG atau PNG. Target maksimal 2 MB; gambar yang lebih besar akan dicoba dikompresi otomatis.</div>
							</div>
							<fieldset class="mb-3" aria-describedby="identity-document-help">
								<legend class="form-label mb-2">Dokumen Identitas Utama</legend>
								<div class="identity-choice-group">
									<label class="identity-choice"><input type="radio" name="identity_document_type" value="ktp" <?= $field('identity_document_type', 'ktp') === 'ktp' ? 'checked' : ''; ?> required><span><strong>KTP</strong><small>Untuk pendaftar yang sudah memiliki KTP.</small></span></label>
									<label class="identity-choice"><input type="radio" name="identity_document_type" value="kk" <?= $field('identity_document_type') === 'kk' ? 'checked' : ''; ?>><span><strong>Kartu Keluarga</strong><small>Untuk anak atau pendaftar yang belum memiliki KTP.</small></span></label>
								</div>
								<div class="identity-file mt-2" data-identity-file="ktp">
									<label class="form-label" for="ktp_file">File KTP</label>
									<input id="ktp_file" type="file" class="form-control" name="ktp_file" accept=".jpg,.jpeg,.png,.pdf" data-upload-file>
								</div>
								<div class="identity-file mt-2" data-identity-file="kk">
									<label class="form-label" for="kk_file">File Kartu Keluarga</label>
									<input id="kk_file" type="file" class="form-control" name="kk_file" accept=".jpg,.jpeg,.png,.pdf" data-upload-file>
								</div>
								<div id="identity-document-help" class="form-hint">Pilih dan unggah salah satu saja. JPG, PNG, atau PDF. Target maksimal 2 MB; berkas lebih besar akan dicoba dikompresi otomatis.</div>
							</fieldset>
						</div>
						<div class="outside-rembang-fields" hidden>
							<div class="mb-3">
								<label class="form-label">Surat Keterangan Luar Rembang <span class="text-secondary">(opsional)</span></label>
								<input type="file" class="form-control" name="support_letter_file" accept=".jpg,.jpeg,.png,.pdf" data-upload-file disabled>
								<div class="form-hint">Boleh dilampirkan bila ada surat domisili, sekolah, pondok, atau instansi. Target maksimal 2 MB; berkas lebih besar akan dicoba dikompresi otomatis.</div>
							</div>
							<div class="mb-3">
								<label class="form-label">Catatan Domisili / Instansi <span class="text-secondary">(opsional)</span></label>
								<input type="text" class="form-control" name="residency_note" value="<?= html_escape($field('residency_note')); ?>" placeholder="Contoh: Domisili Desa X, santri Pondok Y, siswa Sekolah Z" disabled>
							</div>
						</div>
						<div id="upload-compression-notice" class="alert alert-info py-2 small" role="status" hidden>
							<i class="ti ti-file-zip me-1"></i>Ada berkas di atas 2 MB. Server akan mencoba mengompresinya setelah formulir dikirim. Proses dapat membutuhkan waktu lebih lama; jangan menutup halaman.
						</div>
						<div class="register-submit-bar">
							<button type="submit" class="btn btn-primary btn-lg w-100" data-submit-registration>
								<i class="ti ti-send me-1"></i>Kirim Pendaftaran
							</button>
							<div class="text-secondary small text-center mt-2">Setelah terkirim, halaman pending akan menampilkan username dan password awal.</div>
						</div>
					<?= form_close(); ?>
				</div>
			</div>
		</div>
	</main>
	<script>
	(function () {
		var form = document.querySelector('.public-member-register-form');
		if (!form) return;
		var maxBytes = 2 * 1024 * 1024;
		var villageMap = <?= $village_json ?: '{}'; ?>;
		var districtSelect = document.getElementById('district_id');
		var villageSelect = document.getElementById('village_id');
		var currentVillage = parseInt(villageSelect.dataset.current || '0', 10);
		var renderVillages = function () {
			var districtId = parseInt(districtSelect.value || '0', 10);
			var villages = villageMap[districtId] || [];
			villageSelect.innerHTML = '<option value="">Pilih desa / kelurahan</option>';
			villages.forEach(function (village) {
				var option = document.createElement('option');
				option.value = village.id;
				option.textContent = village.name;
				option.selected = parseInt(village.id, 10) === currentVillage;
				villageSelect.appendChild(option);
			});
		};
		var regionBaseUrl = <?= json_encode(base_url('membership/regions/'), JSON_UNESCAPED_SLASHES); ?>;
		var setOptions = function (select, rows, label, selectedId) {
			select.innerHTML = '<option value="">Pilih ' + label + '</option>';
			(rows || []).forEach(function (row) {
				var option = document.createElement('option'); option.value = row.id; option.textContent = row.name; option.selected = parseInt(row.id, 10) === selectedId; select.appendChild(option);
			});
		};
		var fetchRegions = function (path) { return fetch(regionBaseUrl + path, { credentials: 'same-origin' }).then(function (response) { return response.ok ? response.json() : { data: [] }; }).then(function (json) { return json.data || []; }); };
		var setupNationalAddress = function (card) {
			var province = card.querySelector('.national-province'), regency = card.querySelector('.national-regency'), district = card.querySelector('.national-district'), village = card.querySelector('.national-village');
			var selectedRegency = parseInt(regency.dataset.current || '0', 10), selectedDistrict = parseInt(district.dataset.current || '0', 10), selectedVillage = parseInt(village.dataset.current || '0', 10);
			var loadRegencies = function (keep) { setOptions(regency, [], 'kabupaten / kota', keep ? selectedRegency : 0); setOptions(district, [], 'kecamatan', 0); setOptions(village, [], 'desa / kelurahan', 0); if (!province.value) return Promise.resolve(); return fetchRegions('regencies/' + province.value).then(function (rows) { setOptions(regency, rows, 'kabupaten / kota', keep ? selectedRegency : 0); if (keep && regency.value) return loadDistricts(true); }); };
			var loadDistricts = function (keep) { setOptions(district, [], 'kecamatan', keep ? selectedDistrict : 0); setOptions(village, [], 'desa / kelurahan', 0); if (!regency.value) return Promise.resolve(); return fetchRegions('districts/' + regency.value).then(function (rows) { setOptions(district, rows, 'kecamatan', keep ? selectedDistrict : 0); if (keep && district.value) return loadVillages(true); }); };
			var loadVillages = function (keep) { setOptions(village, [], 'desa / kelurahan', keep ? selectedVillage : 0); if (!district.value) return Promise.resolve(); return fetchRegions('villages/' + district.value).then(function (rows) { setOptions(village, rows, 'desa / kelurahan', keep ? selectedVillage : 0); }); };
			province.addEventListener('change', function () { selectedRegency = selectedDistrict = selectedVillage = 0; loadRegencies(false); }); regency.addEventListener('change', function () { selectedDistrict = selectedVillage = 0; loadDistricts(false); }); district.addEventListener('change', function () { selectedVillage = 0; loadVillages(false); });
			if (province.value) loadRegencies(true);
		};
		var selectedDocument = function () {
			var selected = form.querySelector('input[name="identity_document_type"]:checked');
			return selected ? selected.value : '';
		};
		var updateDocument = function () {
			var type = selectedDocument();
			form.querySelectorAll('[data-identity-file]').forEach(function (group) {
				var active = group.getAttribute('data-identity-file') === type;
				group.hidden = !active;
				var input = group.querySelector('input');
				input.disabled = !active;
				input.required = active;
			});
			if (typeof updateCompressionNotice === 'function') updateCompressionNotice();
		};
		var updateDomicile = function () {
			var selected = form.querySelector('input[name="identity_domicile"]:checked');
			var outside = selected && selected.value === 'outside_rembang';
			var supportGroup = form.querySelector('.outside-rembang-fields');
			var residentLocationGroup = form.querySelector('.resident-location-fields');
			var outsideLocationGroup = form.querySelector('.outside-location-fields');
			supportGroup.hidden = !outside;
			supportGroup.querySelectorAll('input').forEach(function (input) { input.disabled = !outside; });
			residentLocationGroup.hidden = outside;
			residentLocationGroup.querySelectorAll('select').forEach(function (input) { input.disabled = outside; input.required = !outside; });
			residentLocationGroup.querySelectorAll('textarea').forEach(function (input) { input.disabled = outside; input.required = false; });
			outsideLocationGroup.hidden = !outside;
			outsideLocationGroup.querySelectorAll('select').forEach(function (input) { input.disabled = !outside; input.required = outside; });
			outsideLocationGroup.querySelectorAll('textarea').forEach(function (input) { input.disabled = !outside; input.required = false; });
			if (typeof updateCompressionNotice === 'function') updateCompressionNotice();
		};
		form.querySelectorAll('input[name="identity_document_type"]').forEach(function (input) { input.addEventListener('change', updateDocument); });
		form.querySelectorAll('input[name="identity_domicile"]').forEach(function (input) { input.addEventListener('change', updateDomicile); });
		districtSelect.addEventListener('change', function () { currentVillage = 0; renderVillages(); });
		form.querySelectorAll('[data-address-prefix]').forEach(setupNationalAddress);
		var compressionNotice = document.getElementById('upload-compression-notice');
		var submitButton = form.querySelector('[data-submit-registration]');
		var hasOversizedUpload = function () {
			return Array.prototype.some.call(form.querySelectorAll('[data-upload-file]'), function (input) {
				return !input.disabled && input.files[0] && input.files[0].size > maxBytes;
			});
		};
		var updateCompressionNotice = function () {
			compressionNotice.hidden = !hasOversizedUpload();
		};
		form.querySelectorAll('[data-upload-file]').forEach(function (input) {
			input.addEventListener('change', updateCompressionNotice);
		});
		form.addEventListener('submit', function () {
			if (hasOversizedUpload() && submitButton) {
				submitButton.disabled = true;
				submitButton.innerHTML = '<i class="ti ti-loader-2 me-1"></i>Mengirim dan mengompresi berkas...';
			}
		});
		renderVillages();
		updateDocument();
		updateDomicile();
		updateCompressionNotice();
	}());
	</script>
</body>
</html>
