<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$settings = $settings ?? [];
$value = function ($key, $default) use ($settings) {
	return (int) ($settings[$key] ?? $default);
};
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Layanan Digital</div>
				<h1 class="page-title">Pengaturan Token Baca</h1>
				<div class="text-secondary mt-1">Atur jumlah token yang diterbitkan, masa berlaku, dan pemotongan token luar zona tanpa mengubah kode aplikasi.</div>
			</div>
			<div class="col-auto"><a href="<?= base_url('reading-points/tokens'); ?>" class="btn btn-outline-primary"><i class="ti ti-arrow-left me-1"></i>Monitoring Token</a></div>
		</div>
	</div>
</div>

<div class="page-body">
	<div class="container-xl">
		<?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?= html_escape($this->session->flashdata('success')); ?></div><?php endif; ?>
		<?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?= html_escape($this->session->flashdata('error')); ?></div><?php endif; ?>

		<?= form_open('reading-points/token-settings/update'); ?>
			<div class="row g-3">
				<div class="col-lg-8">
					<div class="card admin-card mb-3">
						<div class="card-header"><h2 class="card-title">Token dari Check-in Buku Tamu</h2></div>
						<div class="card-body">
							<p class="text-secondary small mb-3">Diterbitkan ketika member aktif check-in di Monitor Buku Tamu Perpustakaan Daerah. Check-in GPS Pojok Baca/GIS tetap akses bebas dan tidak menerbitkan token.</p>
							<div class="row g-3">
								<div class="col-md-4"><label class="form-label">Token per check-in</label><div class="input-group"><input class="form-control" type="number" name="library_checkin_quota" min="1" max="1000" value="<?= $value('library_checkin_quota', 5); ?>" required><span class="input-group-text">sesi</span></div><div class="form-hint">Jumlah kuota yang diterbitkan sekali check-in.</div></div>
								<div class="col-md-4"><label class="form-label">Masa berlaku</label><div class="input-group"><input class="form-control" type="number" name="library_checkin_valid_days" min="0" max="365" value="<?= $value('library_checkin_valid_days', 0); ?>" required><span class="input-group-text">hari</span></div><div class="form-hint">Isi 0 agar habis pada pukul 23:59 hari check-in.</div></div>
								<div class="col-md-4"><label class="form-label">Batas check-in/token harian</label><div class="input-group"><input class="form-control" type="number" name="library_checkin_daily_limit" min="1" max="20" value="<?= $value('library_checkin_daily_limit', 1); ?>" required><span class="input-group-text">kali</span></div><div class="form-hint">Mencegah penerbitan berulang pada hari yang sama.</div></div>
							</div>
						</div>
					</div>

					<div class="card admin-card">
						<div class="card-header"><h2 class="card-title">Token Permohonan Luar Zona</h2></div>
						<div class="card-body">
							<div class="row g-3">
								<div class="col-md-6"><label class="form-label">Kuota permohonan default</label><div class="input-group"><input class="form-control" type="number" name="request_default_quota" min="1" max="1000" value="<?= $value('request_default_quota', 3); ?>" required><span class="input-group-text">sesi</span></div><div class="form-hint">Ditampilkan kepada member saat mengajukan token; petugas masih memutuskan persetujuan.</div></div>
								<div class="col-md-6"><label class="form-label">Masa berlaku token permohonan</label><div class="input-group"><input class="form-control" type="number" name="request_valid_days" min="1" max="365" value="<?= $value('request_valid_days', 7); ?>" required><span class="input-group-text">hari</span></div><div class="form-hint">Dihitung dari tanggal persetujuan petugas.</div></div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-4">
					<div class="card admin-card token-settings-side">
						<div class="card-body">
							<div class="section-kicker">Aturan Reader</div>
							<h3>Potongan akses luar zona</h3>
							<p class="text-secondary small">Hanya dipakai saat member membuka buku di luar Pojok Baca dan perpustakaan terdaftar GIS.</p>
							<label class="form-label">Token yang dipotong per sesi buku</label>
							<div class="input-group"><input class="form-control" type="number" name="outside_session_charge" min="1" max="100" value="<?= $value('outside_session_charge', 1); ?>" required><span class="input-group-text">token</span></div>
							<div class="form-hint">Refresh atau membuka ulang judul dalam sesi aktif tidak memotong token lagi.</div>
							<hr>
							<div class="token-settings-rules"><div><i class="ti ti-map-pin-check"></i><span>Pojok Baca/GIS</span><strong>0 token</strong></div><div><i class="ti ti-building-library"></i><span>Buku Tamu pusat</span><strong>Sesuai setting</strong></div><div><i class="ti ti-world"></i><span>Luar zona</span><strong>Sesuai setting</strong></div></div>
						</div>
						<div class="card-footer"><button class="btn btn-primary w-100" type="submit"><i class="ti ti-device-floppy me-1"></i>Simpan Pengaturan Token</button></div>
					</div>
				</div>
			</div>
		<?= form_close(); ?>
	</div>
</div>
