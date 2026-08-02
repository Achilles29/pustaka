<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$is_edit = ! empty($member);
$field = function ($key, $default = '') use ($member) {
	return $member[$key] ?? $default;
};
$member_no_value = $is_edit ? $field('member_no') : ($generated_member_no ?? $field('member_no'));
$form_options = $form_options ?? [
	'identity_types' => ['NIK/KTP'],
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
$status_labels = [
	'active' => 'Aktif',
	'inactive' => 'Nonaktif',
	'blocked' => 'Diblokir',
	'expired' => 'Kedaluwarsa',
	'unknown' => 'Belum Dipetakan',
];
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Membership</div>
				<h1 class="page-title"><?= $is_edit ? 'Edit Member' : 'Tambah Member'; ?></h1>
			</div>
			<div class="col-auto ms-auto">
				<a href="<?= $is_edit ? base_url('members/detail/' . (int) $member['id']) : base_url('members'); ?>" class="btn btn-outline-secondary">
					<i class="ti ti-arrow-left me-1"></i>Kembali
				</a>
			</div>
		</div>
	</div>
</div>

<div class="page-body">
	<div class="container-xl">
		<?php if ($this->session->flashdata('error')): ?>
			<div class="alert alert-danger"><?= html_escape($this->session->flashdata('error')); ?></div>
		<?php endif; ?>

		<?= form_open($action); ?>
			<div class="row row-cards">
				<div class="col-lg-8">
					<div class="card admin-card">
						<div class="card-header"><h2 class="card-title">Profil Member</h2></div>
						<div class="card-body">
							<div class="row">
								<div class="col-md-4 mb-3">
									<label class="form-label">Nomor Anggota</label>
									<input type="text" class="form-control" id="member-no-input" name="member_no" value="<?= html_escape($member_no_value); ?>" readonly>
									<div class="form-hint">Otomatis: <code>PDR-3317-TAHUN-URUTAN</code>.</div>
								</div>
								<div class="col-md-8 mb-3">
									<label class="form-label">Nama Lengkap</label>
									<input type="text" class="form-control" name="full_name" value="<?= html_escape($field('full_name')); ?>" required>
								</div>
							</div>
							<div class="row">
								<div class="col-md-4 mb-3">
									<label class="form-label">Jenis Identitas</label>
									<select class="form-select" name="identity_type">
										<option value="">Pilih</option>
										<?php foreach ($options_for('identity_types', $field('identity_type')) as $option): ?>
											<option value="<?= html_escape($option); ?>" <?= $field('identity_type', 'NIK/KTP') === $option ? 'selected' : ''; ?>><?= html_escape($option); ?></option>
										<?php endforeach; ?>
									</select>
								</div>
								<div class="col-md-8 mb-3">
									<label class="form-label">Nomor Identitas</label>
									<input type="text" class="form-control" id="identity-number-input" name="identity_number" value="<?= html_escape($field('identity_number')); ?>">
								</div>
							</div>
							<div class="row">
								<div class="col-md-4 mb-3">
									<label class="form-label">Gender</label>
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
									<label class="form-label">Telepon</label>
									<input type="text" class="form-control" name="phone" value="<?= html_escape($field('phone')); ?>">
								</div>
								<div class="col-md-6 mb-3">
									<label class="form-label">Email</label>
									<input type="email" class="form-control" name="email" value="<?= html_escape($field('email')); ?>">
								</div>
							</div>
							<div class="row">
								<div class="col-md-6 mb-3">
									<label class="form-label">Kecamatan</label>
									<input type="text" class="form-control" name="district" value="<?= html_escape($field('district')); ?>">
								</div>
								<div class="col-md-6 mb-3">
									<label class="form-label">Desa / Kelurahan</label>
									<input type="text" class="form-control" name="village" value="<?= html_escape($field('village')); ?>">
								</div>
							</div>
							<div class="mb-3">
								<label class="form-label">Alamat</label>
								<textarea class="form-control" name="address" rows="4"><?= html_escape($field('address')); ?></textarea>
							</div>
							<div class="row">
								<div class="col-md-4 mb-3">
									<label class="form-label">Tipe Member</label>
									<select class="form-select" name="member_type">
										<?php foreach ($options_for('member_types', $field('member_type')) as $option): ?>
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
						</div>
					</div>
				</div>

				<div class="col-lg-4">
					<div class="card admin-card">
						<div class="card-header"><h2 class="card-title">Status dan Akun</h2></div>
						<div class="card-body">
							<div class="mb-3">
								<label class="form-label">Status Membership</label>
								<select class="form-select" name="status">
									<?php foreach ($status_labels as $value => $label): ?>
										<option value="<?= $value; ?>" <?= $field('status', 'active') === $value ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="row">
								<div class="col-md-6 mb-3">
									<label class="form-label">Tanggal Daftar</label>
									<input type="datetime-local" class="form-control" name="registered_at" value="<?= html_escape($field('registered_at') ? date('Y-m-d\TH:i', strtotime($field('registered_at'))) : ''); ?>">
								</div>
								<div class="col-md-6 mb-3">
									<label class="form-label">Berlaku Sampai</label>
									<input type="datetime-local" class="form-control" name="expired_at" value="<?= html_escape($field('expired_at') ? date('Y-m-d\TH:i', strtotime($field('expired_at'))) : ''); ?>">
								</div>
							</div>
							<div class="mb-3">
								<label class="form-label">Nama File Foto</label>
								<input type="text" class="form-control" name="photo_path" value="<?= html_escape($field('photo_path')); ?>">
							</div>
							<label class="form-check mb-3">
								<input class="form-check-input" type="checkbox" name="create_account" value="1" <?= $is_edit && ! empty($member['auth_user_id']) ? 'checked' : ''; ?>>
								<span class="form-check-label">Buat / update akun login pemustaka</span>
							</label>
							<div class="mb-3">
								<label class="form-label">Username Login</label>
								<input type="text" class="form-control" id="member-login-username-preview" value="<?= html_escape($field('identity_number') ?: ($field('username') ?: $field('member_no'))); ?>" readonly>
								<div class="form-hint">Otomatis memakai NIK/nomor identitas. Jika kosong, sistem memakai nomor anggota atau ID sumber.</div>
							</div>
							<div class="mb-3">
								<label class="form-label">Password</label>
								<input type="password" class="form-control" name="password" placeholder="<?= $is_edit ? 'Kosongkan jika tidak diganti' : $default_password; ?>">
								<div class="form-hint">Password default member baru: <code><?= html_escape($default_password); ?></code></div>
							</div>
							<?php if ($is_edit): ?>
								<div class="datagrid">
									<div class="datagrid-item">
										<div class="datagrid-title">Sumber</div>
										<div class="datagrid-content"><?= html_escape($field('source_system') ?: 'manual'); ?></div>
									</div>
									<div class="datagrid-item">
										<div class="datagrid-title">Status Foto</div>
										<div class="datagrid-content"><?= html_escape($field('photo_migration_status') ?: '-'); ?></div>
									</div>
								</div>
							<?php endif; ?>
						</div>
						<div class="card-footer text-end">
							<button type="submit" class="btn btn-primary">
								<i class="ti ti-device-floppy me-1"></i><?= $is_edit ? 'Simpan Perubahan' : 'Simpan Member'; ?>
							</button>
						</div>
					</div>
				</div>
			</div>
		<?= form_close(); ?>
	</div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
	var identityInput = document.getElementById('identity-number-input');
	var memberNoInput = document.getElementById('member-no-input');
	var previewInput = document.getElementById('member-login-username-preview');
	if (! identityInput || ! memberNoInput || ! previewInput) {
		return;
	}
	var updatePreview = function () {
		previewInput.value = identityInput.value.trim() || memberNoInput.value.trim() || '';
	};
	identityInput.addEventListener('input', updatePreview);
	memberNoInput.addEventListener('input', updatePreview);
	updatePreview();
});
</script>
