<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$status_labels = [
	'active' => 'Aktif',
	'inactive' => 'Nonaktif',
	'blocked' => 'Diblokir',
	'expired' => 'Kedaluwarsa',
	'unknown' => 'Belum Dipetakan',
];
$user_status_labels = [
	'active' => 'Aktif',
	'inactive' => 'Nonaktif',
	'suspended' => 'Ditangguhkan',
];
$inlislite_base = preg_replace('#/pustaka/?$#', '/inlislite3/', base_url());
$photo_url = ! empty($member['photo_path']) ? $inlislite_base . 'uploaded_files/foto_anggota/' . rawurlencode($member['photo_path']) : '';
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Membership</div>
				<h1 class="page-title">Detail Member</h1>
			</div>
			<div class="col-auto ms-auto">
				<a href="<?= base_url('members'); ?>" class="btn btn-outline-secondary">
					<i class="ti ti-arrow-left me-1"></i>Membership
				</a>
			</div>
		</div>
	</div>
</div>

<div class="page-body">
	<div class="container-xl">
		<div class="row row-cards">
			<div class="col-lg-4">
				<div class="card admin-card">
					<div class="card-body text-center">
						<div class="member-photo-detail mx-auto mb-3">
							<?php if ($photo_url): ?>
								<img src="<?= html_escape($photo_url); ?>" alt="Foto <?= html_escape($member['full_name']); ?>">
							<?php else: ?>
								<i class="ti ti-user"></i>
							<?php endif; ?>
						</div>
						<h2 class="h3 mb-1"><?= html_escape($member['full_name']); ?></h2>
						<div class="text-secondary"><code><?= html_escape($member['member_no'] ?: '-'); ?></code></div>
						<div class="mt-3">
							<span class="badge bg-blue-lt"><?= html_escape($status_labels[$member['status']] ?? ucfirst($member['status'])); ?></span>
							<span class="badge <?= ($member['user_status'] ?? '') === 'active' ? 'bg-green-lt' : 'bg-secondary-lt'; ?>"><?= html_escape($user_status_labels[$member['user_status'] ?? ''] ?? 'Belum Ada Akun'); ?></span>
						</div>
					</div>
				</div>

				<div class="card admin-card mt-3">
					<div class="card-header"><h2 class="card-title">Akun Login</h2></div>
					<div class="card-body">
						<div class="datagrid">
							<div class="datagrid-item"><div class="datagrid-title">Username</div><div class="datagrid-content"><?= html_escape($member['username'] ?: '-'); ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">Email Akun</div><div class="datagrid-content"><?= html_escape($member['user_email'] ?: '-'); ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">Password Awal</div><div class="datagrid-content"><code><?= html_escape($default_password); ?></code></div></div>
							<div class="datagrid-item"><div class="datagrid-title">Wajib Ganti</div><div class="datagrid-content"><?= (int) ($member['force_password_change'] ?? 0) === 1 ? 'Ya' : 'Tidak'; ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">Login Terakhir</div><div class="datagrid-content"><?= html_escape($member['last_login_at'] ?: '-'); ?></div></div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-lg-8">
				<div class="card admin-card mb-3">
					<div class="card-header"><h2 class="card-title">Profil</h2></div>
					<div class="card-body">
						<div class="datagrid">
							<div class="datagrid-item"><div class="datagrid-title">ID Sumber</div><div class="datagrid-content"><?= html_escape($member['source_id'] ?: '-'); ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">No Identitas</div><div class="datagrid-content"><?= html_escape($member['identity_number'] ?: '-'); ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">Jenis Kelamin</div><div class="datagrid-content"><?= html_escape($member['gender'] ?: '-'); ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">Tempat Lahir</div><div class="datagrid-content"><?= html_escape($member['birth_place'] ?: '-'); ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">Tanggal Lahir</div><div class="datagrid-content"><?= html_escape($member['birth_date'] ?: '-'); ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">Telepon</div><div class="datagrid-content"><?= html_escape($member['phone'] ?: '-'); ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">Email Profil</div><div class="datagrid-content"><?= html_escape($member['email'] ?: '-'); ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">Jenis Member</div><div class="datagrid-content"><?= html_escape($member['member_type'] ?: '-'); ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">Pendidikan</div><div class="datagrid-content"><?= html_escape($member['education'] ?: '-'); ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">Pekerjaan</div><div class="datagrid-content"><?= html_escape($member['occupation'] ?: '-'); ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">Tanggal Daftar</div><div class="datagrid-content"><?= html_escape($member['registered_at'] ?: '-'); ?></div></div>
							<div class="datagrid-item"><div class="datagrid-title">Masa Berlaku</div><div class="datagrid-content"><?= html_escape($member['expired_at'] ?: '-'); ?></div></div>
						</div>
					</div>
				</div>

				<div class="card admin-card">
					<div class="card-header"><h2 class="card-title">Alamat</h2></div>
					<div class="card-body">
						<p><?= nl2br(html_escape($member['address'] ?: '-')); ?></p>
						<div class="text-secondary"><?= html_escape(trim(($member['village'] ?: '-') . ', ' . ($member['district'] ?: '-'), ', ')); ?></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
