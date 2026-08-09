<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$status_labels = [
	'draft' => 'Draft',
	'published' => 'Tayang',
	'closed' => 'Selesai',
	'cancelled' => 'Dibatalkan',
];
$registration_status_labels = [
	'pending' => 'Menunggu',
	'registered' => 'Terdaftar',
	'approved' => 'Disetujui',
	'rejected' => 'Ditolak',
	'attended' => 'Hadir',
	'cancelled' => 'Batal',
];
$field_types = [
	'text' => 'Teks pendek',
	'textarea' => 'Teks panjang',
	'select' => 'Dropdown',
	'radio' => 'Pilihan tunggal',
	'checkbox' => 'Pilihan jamak',
	'number' => 'Angka',
	'date' => 'Tanggal',
	'email' => 'Email',
	'phone' => 'Nomor HP',
];
$participant_total = 0;
$pending_total = 0;
$attended_total = 0;
foreach ($registrations as $registration) {
	$participant_total += (int) $registration['participant_count'];
	if ($registration['status'] === 'pending') {
		$pending_total++;
	}
	if ($registration['status'] === 'attended') {
		$attended_total += (int) $registration['participant_count'];
	}
}
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Event Literasi</div>
				<h1 class="page-title"><?= html_escape($event['title']); ?></h1>
			</div>
			<div class="col-auto ms-auto">
				<div class="btn-list">
					<a href="<?= base_url('events'); ?>" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i>Kembali</a>
					<a href="<?= base_url('agenda/detail/' . (int) $event['id']); ?>" class="btn btn-outline-primary" target="_blank" rel="noopener"><i class="ti ti-world me-1"></i>Lihat Publik</a>
					<a href="<?= base_url('events/qr?event_id=' . (int) $event['id']); ?>" class="btn btn-outline-primary"><i class="ti ti-qrcode me-1"></i>QR Event</a>
					<?php if ($can_edit): ?><a href="<?= base_url('events/edit/' . (int) $event['id']); ?>" class="btn btn-primary"><i class="ti ti-edit me-1"></i>Edit Event</a><?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="page-body">
	<div class="container-xl">
		<?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?= html_escape($this->session->flashdata('success')); ?></div><?php endif; ?>
		<?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?= html_escape($this->session->flashdata('error')); ?></div><?php endif; ?>

		<div class="row row-cards">
			<div class="col-lg-8">
				<div class="card admin-card event-detail-card">
					<div class="card-body">
						<div class="d-flex flex-column flex-md-row gap-4">
							<div class="event-poster">
								<?php if (! empty($event['poster_path'])): ?>
									<img src="<?= base_url($event['poster_path']); ?>" alt="Poster <?= html_escape($event['title']); ?>">
								<?php else: ?>
									<div><i class="ti ti-calendar-event"></i><span>Event Literasi</span></div>
								<?php endif; ?>
							</div>
							<div class="flex-fill">
								<div class="d-flex flex-wrap gap-2 mb-2">
									<span class="badge bg-blue-lt"><?= html_escape($status_labels[$event['status']] ?? $event['status']); ?></span>
									<span class="badge bg-azure-lt"><?= html_escape($event['category_name'] ?: 'Tanpa kategori'); ?></span>
									<span class="badge bg-secondary-lt"><?= html_escape($event['venue_type']); ?></span>
								</div>
								<h2 class="mb-2"><?= html_escape($event['title']); ?></h2>
								<p class="text-secondary mb-3"><?= html_escape($event['summary'] ?: 'Belum ada ringkasan singkat.'); ?></p>
								<div class="row g-3">
									<div class="col-md-6"><div class="mini-info"><i class="ti ti-clock"></i><span><?= html_escape($event['starts_at'] ?: 'Jadwal menyusul'); ?></span></div></div>
									<div class="col-md-6"><div class="mini-info"><i class="ti ti-map-pin"></i><span><?= html_escape($event['location_name'] ?: ($event['library_name'] ?: 'Lokasi menyusul')); ?></span></div></div>
									<div class="col-md-6"><div class="mini-info"><i class="ti ti-users"></i><span><?= number_format($participant_total, 0, ',', '.'); ?> / <?= $event['quota'] ? number_format((int) $event['quota'], 0, ',', '.') : 'tanpa batas'; ?> peserta</span></div></div>
									<div class="col-md-6"><div class="mini-info"><i class="ti ti-user-star"></i><span><?= html_escape($event['speaker_name'] ?: 'Narasumber belum diisi'); ?></span></div></div>
								</div>
							</div>
						</div>
						<?php if (! empty($event['description'])): ?>
							<hr>
							<div class="prose-lite"><?= nl2br(html_escape($event['description'])); ?></div>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<div class="col-lg-4">
				<div class="metric-ribbon event-side-metrics">
					<div class="metric-ribbon-item"><span class="metric-icon"><i class="ti ti-users"></i></span><div><div class="metric-value"><?= number_format($participant_total, 0, ',', '.'); ?></div><div class="metric-label">Peserta</div></div></div>
					<div class="metric-ribbon-item"><span class="metric-icon"><i class="ti ti-inbox"></i></span><div><div class="metric-value"><?= number_format($pending_total, 0, ',', '.'); ?></div><div class="metric-label">Antrean</div></div></div>
					<div class="metric-ribbon-item"><span class="metric-icon"><i class="ti ti-user-check"></i></span><div><div class="metric-value"><?= number_format($attended_total, 0, ',', '.'); ?></div><div class="metric-label">Hadir</div></div></div>
				</div>
				<?php if ($can_edit): ?>
					<div class="card admin-card mt-3">
						<div class="card-header"><h2 class="card-title">Status Cepat</h2></div>
						<div class="card-body">
							<?= form_open('events/status/' . (int) $event['id'], ['class' => 'row g-2']); ?>
								<div class="col-12">
									<select class="form-select" name="status">
										<?php foreach ($status_labels as $value => $label): ?>
											<option value="<?= $value; ?>" <?= $event['status'] === $value ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
										<?php endforeach; ?>
									</select>
								</div>
								<div class="col-12">
									<input type="text" class="form-control" name="cancelled_reason" value="<?= html_escape($event['cancelled_reason'] ?? ''); ?>" placeholder="Alasan jika dibatalkan">
								</div>
								<div class="col-12">
									<button class="btn btn-primary w-100"><i class="ti ti-refresh me-1"></i>Update Status</button>
								</div>
							<?= form_close(); ?>
						</div>
					</div>
				<?php endif; ?>
				<div class="card admin-card mt-3">
					<div class="card-header"><h2 class="card-title">QR Attendance</h2></div>
					<div class="card-body">
						<div class="qr-attendance-summary">
							<i class="ti ti-qrcode"></i>
							<div>
								<strong><?= number_format(count($registrations), 0, ',', '.'); ?> tiket siap check-in</strong>
								<p>QR ada di tiket digital peserta. Admin juga bisa membuka QR dari tombol di tabel peserta.</p>
							</div>
						</div>
						<a href="#peserta" class="btn btn-outline-primary w-100 mt-3"><i class="ti ti-users me-1"></i>Lihat QR Peserta</a>
					</div>
				</div>
			</div>
		</div>

		<div class="card admin-card data-workspace mt-3" id="form-pendaftaran">
			<div class="card-header workspace-header">
				<div>
					<h2 class="card-title">Form Pendaftaran</h2>
					<div class="text-secondary small">Field ini tampil di halaman publik khusus event ini. Nama, HP/email, institusi, dan jumlah peserta sudah menjadi field dasar.</div>
				</div>
				<?php if ($can_edit): ?><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-add-field"><i class="ti ti-plus me-1"></i>Tambah Field</button><?php endif; ?>
			</div>
			<div class="table-responsive">
				<table class="table table-vcenter card-table">
					<thead><tr><th>Field</th><th>Tipe</th><th>Opsi</th><th>Status</th><th class="w-1">Aksi</th></tr></thead>
					<tbody>
						<?php if (empty($fields)): ?><tr><td colspan="5" class="text-center text-secondary py-4">Belum ada field tambahan. Form publik tetap memakai field dasar peserta.</td></tr><?php endif; ?>
						<?php foreach ($fields as $field): ?>
							<tr>
								<td>
									<div class="fw-semibold"><?= html_escape($field['field_label']); ?> <?= ! empty($field['is_required']) ? '<span class="text-danger">*</span>' : ''; ?></div>
									<div class="text-secondary small"><code><?= html_escape($field['field_key']); ?></code> · Urutan <?= (int) $field['sort_order']; ?></div>
									<?php if ($field['helper_text']): ?><div class="small"><?= html_escape($field['helper_text']); ?></div><?php endif; ?>
								</td>
								<td><?= html_escape($field_types[$field['field_type']] ?? $field['field_type']); ?></td>
								<td class="text-secondary small"><?= html_escape($field['options_text'] ? str_replace("\n", ', ', $field['options_text']) : '-'); ?></td>
								<td><span class="badge <?= ! empty($field['is_active']) ? 'bg-green-lt' : 'bg-secondary-lt'; ?>"><?= ! empty($field['is_active']) ? 'Aktif' : 'Nonaktif'; ?></span></td>
								<td>
									<div class="btn-list flex-nowrap">
										<?php if ($can_edit): ?>
											<button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-edit-field-<?= (int) $field['id']; ?>"><i class="ti ti-edit me-1"></i>Edit</button>
											<?= form_open('events/fields/toggle/' . (int) $event['id'] . '/' . (int) $field['id'], ['class' => 'd-inline']); ?>
												<button class="btn btn-outline-secondary btn-sm"><?= ! empty($field['is_active']) ? 'Nonaktifkan' : 'Aktifkan'; ?></button>
											<?= form_close(); ?>
										<?php endif; ?>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>

		<div class="card admin-card data-workspace mt-3" id="peserta">
			<div class="card-header workspace-header">
				<div>
					<h2 class="card-title">Peserta</h2>
					<div class="text-secondary small">Antrean, verifikasi, dan check-in peserta event.</div>
				</div>
			</div>
			<div class="card-body workspace-filter">
				<?= form_open('events/detail/' . (int) $event['id'], ['method' => 'get', 'class' => 'row g-2 align-items-end']); ?>
					<div class="col-md-5">
						<label class="form-label">Cari Peserta</label>
						<input type="text" class="form-control" name="q" value="<?= html_escape($registration_filters['q'] ?? ''); ?>" placeholder="Kode, nama, HP, email, institusi">
					</div>
					<div class="col-md-3">
						<label class="form-label">Status</label>
						<select class="form-select" name="registration_status">
							<option value="">Semua</option>
							<?php foreach ($registration_status_labels as $value => $label): ?>
								<option value="<?= $value; ?>" <?= ($registration_filters['status'] ?? '') === $value ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-2"><button class="btn btn-outline-primary w-100"><i class="ti ti-filter me-1"></i>Filter</button></div>
					<div class="col-md-2"><a class="btn btn-outline-secondary w-100" href="<?= base_url('events/detail/' . (int) $event['id'] . '#peserta'); ?>"><i class="ti ti-refresh me-1"></i>Reset</a></div>
				<?= form_close(); ?>
			</div>
			<div class="table-responsive">
				<table class="table table-vcenter card-table">
					<thead><tr><th>Peserta</th><th>Kontak</th><th>Jumlah</th><th>Status</th><th class="w-1">Aksi</th></tr></thead>
					<tbody>
						<?php if (empty($registrations)): ?><tr><td colspan="5" class="text-center text-secondary py-4">Belum ada pendaftar.</td></tr><?php endif; ?>
						<?php foreach ($registrations as $registration): ?>
							<tr>
								<td>
									<div class="fw-semibold"><?= html_escape($registration['participant_name']); ?></div>
									<div class="text-secondary small"><code><?= html_escape($registration['registration_code'] ?: '-'); ?></code> · <?= html_escape($registration['participant_type']); ?></div>
									<?php if ($registration['institution']): ?><div class="small"><?= html_escape($registration['institution']); ?></div><?php endif; ?>
								</td>
								<td><div><?= html_escape($registration['participant_phone'] ?: '-'); ?></div><div class="text-secondary small"><?= html_escape($registration['participant_email'] ?: '-'); ?></div></td>
								<td><?= number_format((int) $registration['participant_count'], 0, ',', '.'); ?> orang</td>
								<td><span class="badge bg-blue-lt"><?= html_escape($registration_status_labels[$registration['status']] ?? $registration['status']); ?></span></td>
								<td>
									<?php if ($can_approve): ?>
										<div class="btn-list flex-nowrap mb-2">
											<?php if (! empty($registration['attendance_token'])): ?>
												<button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-attendance-qr-<?= (int) $registration['id']; ?>"><i class="ti ti-qrcode me-1"></i>QR</button>
											<?php endif; ?>
											<?php if (! empty($registration['ticket_token'])): ?>
												<a class="btn btn-outline-secondary btn-sm" href="<?= base_url('agenda/ticket/' . rawurlencode($registration['ticket_token'])); ?>" target="_blank" rel="noopener"><i class="ti ti-ticket me-1"></i>Tiket</a>
											<?php endif; ?>
										</div>
										<?= form_open('events/registrations/update/' . (int) $event['id'] . '/' . (int) $registration['id'], ['class' => 'event-registration-action']); ?>
											<div class="input-group input-group-sm">
												<select class="form-select" name="status">
													<?php foreach ($registration_status_labels as $value => $label): ?>
														<option value="<?= $value; ?>" <?= $registration['status'] === $value ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
													<?php endforeach; ?>
												</select>
												<button class="btn btn-primary"><i class="ti ti-check"></i></button>
											</div>
										<?= form_close(); ?>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<?php if ($can_edit): ?>
	<?php
	$render_field_modal = function ($modal_id, $action, $field = null) use ($field_types) {
		$field = $field ?: [];
		$get = function ($key, $default = '') use ($field) { return $field[$key] ?? $default; };
		?>
		<div class="modal fade" id="<?= html_escape($modal_id); ?>" tabindex="-1" aria-hidden="true">
			<div class="modal-dialog modal-lg modal-dialog-centered">
				<div class="modal-content">
					<?= form_open($action); ?>
						<div class="modal-header">
							<h5 class="modal-title">Field Formulir</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
						</div>
						<div class="modal-body">
							<div class="row">
								<div class="col-md-7 mb-3">
									<label class="form-label">Label</label>
									<input type="text" class="form-control" name="field_label" value="<?= html_escape($get('field_label')); ?>" required placeholder="Contoh: Asal sekolah / instansi">
								</div>
								<div class="col-md-5 mb-3">
									<label class="form-label">Key</label>
									<input type="text" class="form-control" name="field_key" value="<?= html_escape($get('field_key')); ?>" placeholder="otomatis jika kosong">
								</div>
							</div>
							<div class="row">
								<div class="col-md-4 mb-3">
									<label class="form-label">Tipe</label>
									<select class="form-select" name="field_type">
										<?php foreach ($field_types as $value => $label): ?>
											<option value="<?= $value; ?>" <?= $get('field_type', 'text') === $value ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
										<?php endforeach; ?>
									</select>
								</div>
								<div class="col-md-4 mb-3">
									<label class="form-label">Urutan</label>
									<input type="number" class="form-control" name="sort_order" value="<?= html_escape($get('sort_order', 0)); ?>" min="0">
								</div>
								<div class="col-md-4 mb-3">
									<label class="form-label">Placeholder</label>
									<input type="text" class="form-control" name="placeholder" value="<?= html_escape($get('placeholder')); ?>">
								</div>
							</div>
							<div class="mb-3">
								<label class="form-label">Opsi</label>
								<textarea class="form-control" name="options_text" rows="4" placeholder="Satu opsi per baris, khusus dropdown/radio/checkbox"><?= html_escape($get('options_text')); ?></textarea>
							</div>
							<div class="mb-3">
								<label class="form-label">Bantuan</label>
								<input type="text" class="form-control" name="helper_text" value="<?= html_escape($get('helper_text')); ?>" placeholder="Teks kecil di bawah field">
							</div>
							<div class="row">
								<div class="col-md-6">
									<label class="form-check"><input class="form-check-input" type="checkbox" name="is_required" value="1" <?= (int) $get('is_required') === 1 ? 'checked' : ''; ?>><span class="form-check-label">Wajib diisi</span></label>
								</div>
								<div class="col-md-6">
									<label class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" <?= (int) $get('is_active', 1) === 1 ? 'checked' : ''; ?>><span class="form-check-label">Aktif</span></label>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
							<button class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Simpan</button>
						</div>
					<?= form_close(); ?>
				</div>
			</div>
		</div>
		<?php
	};
	$render_field_modal('modal-add-field', 'events/fields/store/' . (int) $event['id']);
	foreach ($fields as $field) {
		$render_field_modal('modal-edit-field-' . (int) $field['id'], 'events/fields/update/' . (int) $event['id'] . '/' . (int) $field['id'], $field);
	}
	?>
<?php endif; ?>

<?php if ($can_approve): ?>
	<?php foreach ($registrations as $registration): ?>
		<?php if (empty($registration['attendance_token'])) continue; ?>
		<div class="modal fade" id="modal-attendance-qr-<?= (int) $registration['id']; ?>" tabindex="-1" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">QR Check-in Peserta</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
					</div>
					<div class="modal-body text-center">
						<div class="event-admin-qr mx-auto" data-qr="<?= html_escape(base_url('events/checkin/' . rawurlencode($registration['attendance_token']))); ?>"></div>
						<h3 class="mt-3 mb-1"><?= html_escape($registration['participant_name']); ?></h3>
						<div class="text-secondary"><?= html_escape($registration['registration_code'] ?: '-'); ?> - <?= number_format((int) $registration['participant_count'], 0, ',', '.'); ?> orang</div>
						<div class="form-hint mt-3">Scan QR ini menggunakan akun admin yang punya hak verifikasi event. Sistem akan menandai peserta sebagai hadir.</div>
					</div>
					<div class="modal-footer">
						<a class="btn btn-outline-primary" href="<?= base_url('events/checkin/' . rawurlencode($registration['attendance_token'])); ?>"><i class="ti ti-user-check me-1"></i>Check-in Manual</a>
						<button type="button" class="btn btn-primary" data-bs-dismiss="modal">Selesai</button>
					</div>
				</div>
			</div>
		</div>
	<?php endforeach; ?>

	<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
	<script>
	document.addEventListener('DOMContentLoaded', function () {
		if (! window.QRCode) {
			return;
		}
		document.querySelectorAll('.event-admin-qr[data-qr]').forEach(function (target) {
			if (target.dataset.rendered === '1') {
				return;
			}
			target.dataset.rendered = '1';
			new QRCode(target, {
				text: target.getAttribute('data-qr'),
				width: 220,
				height: 220,
				colorDark: '#061b49',
				colorLight: '#ffffff',
				correctLevel: QRCode.CorrectLevel.M
			});
		});
	});
	</script>
<?php endif; ?>
