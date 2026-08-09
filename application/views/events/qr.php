<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$status_labels = [
	'draft' => 'Draft',
	'published' => 'Tayang',
	'closed' => 'Selesai',
	'cancelled' => 'Dibatalkan',
];
$registration_modes = [
	'none' => 'Tanpa pendaftaran',
	'open' => 'Publik',
	'member_only' => 'Khusus member',
	'invite' => 'Undangan',
];
$query_base = $_GET;
unset($query_base['event_id']);
$select_url = function ($event_id) use ($query_base) {
	return base_url('events/qr?' . http_build_query(array_merge($query_base, ['event_id' => $event_id])));
};
$event_date = ! empty($selected_event['starts_at']) ? date('d M Y, H:i', strtotime($selected_event['starts_at'])) : 'Jadwal menyusul';
$event_location = $selected_event ? ($selected_event['location_name'] ?: ($selected_event['library_name'] ?: 'Kabupaten Rembang')) : '-';
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Event Literasi</div>
				<h1 class="page-title">QR Event & Pendaftaran</h1>
			</div>
			<div class="col-auto ms-auto">
				<div class="btn-list">
					<a href="<?= base_url('events'); ?>" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i>Event</a>
					<a href="<?= base_url('events/create'); ?>" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Tambah Event</a>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="page-body">
	<div class="container-xl">
		<div class="row row-cards">
			<div class="col-lg-4 d-print-none">
				<div class="card admin-card">
					<div class="card-header"><h2 class="card-title">Pilih Event</h2></div>
					<div class="card-body">
						<?= form_open('events/qr', ['method' => 'get', 'class' => 'row g-2']); ?>
							<div class="col-12">
								<label class="form-label">Cari</label>
								<input type="text" class="form-control" name="q" value="<?= html_escape($filters['q'] ?? ''); ?>" placeholder="Judul, lokasi, penyelenggara">
							</div>
							<div class="col-6">
								<label class="form-label">Status</label>
								<select class="form-select" name="status">
									<option value="">Semua</option>
									<?php foreach ($status_labels as $value => $label): ?>
										<option value="<?= $value; ?>" <?= ($filters['status'] ?? '') === $value ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-6">
								<label class="form-label">Kategori</label>
								<select class="form-select" name="category_id">
									<option value="">Semua</option>
									<?php foreach ($categories as $category): ?>
										<option value="<?= (int) $category['id']; ?>" <?= (int) ($filters['category_id'] ?? 0) === (int) $category['id'] ? 'selected' : ''; ?>><?= html_escape($category['name']); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-12">
								<label class="form-label">Perpustakaan</label>
								<select class="form-select" name="library_id">
									<option value="">Semua</option>
									<?php foreach ($libraries as $library): ?>
										<option value="<?= (int) $library['id']; ?>" <?= (int) ($filters['library_id'] ?? 0) === (int) $library['id'] ? 'selected' : ''; ?>><?= html_escape($library['name']); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-8">
								<button class="btn btn-primary w-100"><i class="ti ti-filter me-1"></i>Terapkan</button>
							</div>
							<div class="col-4">
								<a href="<?= base_url('events/qr'); ?>" class="btn btn-outline-secondary w-100"><i class="ti ti-refresh"></i></a>
							</div>
						<?= form_close(); ?>
					</div>
				</div>

				<div class="card admin-card mt-3">
					<div class="list-group list-group-flush event-qr-event-list">
						<?php if (empty($events)): ?>
							<div class="list-group-item text-secondary">Belum ada event sesuai filter.</div>
						<?php endif; ?>
						<?php foreach ($events as $event): ?>
							<a href="<?= $select_url((int) $event['id']); ?>" class="list-group-item list-group-item-action <?= (int) ($filters['event_id'] ?? 0) === (int) $event['id'] ? 'active' : ''; ?>">
								<div class="d-flex align-items-center gap-2">
									<span class="event-dot" style="--event-color: <?= html_escape($event['category_color'] ?: '#005baa'); ?>"></span>
									<div class="min-width-0">
										<div class="fw-semibold text-truncate"><?= html_escape($event['title']); ?></div>
										<div class="small"><?= html_escape($event['starts_at'] ? date('d M Y H:i', strtotime($event['starts_at'])) : 'Jadwal menyusul'); ?></div>
									</div>
								</div>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

			<div class="col-lg-8">
				<?php if (! $selected_event): ?>
					<div class="empty">
						<div class="empty-icon"><i class="ti ti-qrcode-off"></i></div>
						<p class="empty-title">Pilih event untuk membuat QR.</p>
					</div>
				<?php else: ?>
					<?php if (! $is_public_ready): ?>
						<div class="alert alert-warning d-print-none">
							<i class="ti ti-alert-triangle me-1"></i>
							QR tetap bisa dibuat, tetapi pendaftaran belum siap dibuka. Pastikan status event `Tayang`, mode pendaftaran bukan `Tanpa pendaftaran`, dan periode pendaftaran sedang aktif.
						</div>
					<?php endif; ?>

					<div class="card admin-card event-qr-builder d-print-none">
						<div class="card-body">
							<div class="event-qr-control-head">
								<div>
									<div class="section-kicker">QR Event</div>
									<h2>Link publik siap ditempel di poster, banner, monitor, atau media sosial.</h2>
								</div>
								<span class="badge <?= $is_public_ready ? 'bg-green-lt' : 'bg-yellow-lt'; ?>"><?= $is_public_ready ? 'Pendaftaran aktif' : 'Perlu cek jadwal'; ?></span>
							</div>
							<div class="row g-3 align-items-end">
								<div class="col-md-6">
									<label class="form-label">Link QR</label>
									<input type="text" class="form-control" id="event-qr-url" value="<?= html_escape($public_url); ?>" readonly>
								</div>
								<div class="col-md-6">
									<div class="btn-list">
										<button type="button" class="btn btn-outline-primary" id="copy-event-qr"><i class="ti ti-copy me-1"></i>Copy Link</button>
										<button type="button" class="btn btn-outline-primary" id="download-event-qr"><i class="ti ti-download me-1"></i>Unduh QR</button>
										<button type="button" class="btn btn-primary" onclick="window.print()"><i class="ti ti-printer me-1"></i>Cetak</button>
									</div>
								</div>
							</div>
						</div>
					</div>

					<section class="event-qr-print-card mt-3">
						<div class="event-qr-print-head">
							<img src="<?= base_url('img/logo-small.jpeg'); ?>" alt="Logo Kabupaten Rembang">
							<div>
								<div class="section-kicker">Pustaka Digital Rembang</div>
								<h2>Scan untuk Mendaftar</h2>
							</div>
							<img src="<?= base_url('img/perpusnas.png'); ?>" alt="Logo Perpusnas">
						</div>
						<div class="event-qr-print-body">
							<div>
								<div class="event-qr-type"><?= html_escape($selected_event['category_name'] ?: ($selected_event['event_type'] ?: 'Event Literasi')); ?></div>
								<h1><?= html_escape($selected_event['title']); ?></h1>
								<p><?= html_escape($selected_event['summary'] ?: 'Buka kamera HP, arahkan ke QR, lalu isi formulir pendaftaran.'); ?></p>
								<div class="event-qr-info-grid">
									<div><i class="ti ti-clock"></i><span><?= html_escape($event_date); ?></span></div>
									<div><i class="ti ti-map-pin"></i><span><?= html_escape($event_location); ?></span></div>
									<div><i class="ti ti-users"></i><span><?= ! empty($selected_event['quota']) ? number_format((int) $selected_event['quota'], 0, ',', '.') . ' kuota' : 'Tanpa batas kuota'; ?></span></div>
									<div><i class="ti ti-ticket"></i><span><?= html_escape($registration_modes[$selected_event['registration_mode']] ?? $selected_event['registration_mode']); ?></span></div>
								</div>
							</div>
							<div class="event-qr-box-wrap">
								<div id="event-registration-qr" class="event-registration-qr" data-qr="<?= html_escape($public_url); ?>"></div>
								<div class="event-qr-caption">Scan QR ini untuk membuka formulir pendaftaran.</div>
							</div>
						</div>
						<div class="event-qr-print-footer">
							<span><?= html_escape($public_url); ?></span>
							<strong><?= html_escape($status_labels[$selected_event['status']] ?? $selected_event['status']); ?></strong>
						</div>
					</section>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<?php if ($selected_event): ?>
	<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
	<script>
	document.addEventListener('DOMContentLoaded', function () {
		var qrTarget = document.getElementById('event-registration-qr');
		var urlInput = document.getElementById('event-qr-url');
		if (qrTarget && window.QRCode) {
			new QRCode(qrTarget, {
				text: qrTarget.getAttribute('data-qr'),
				width: 260,
				height: 260,
				colorDark: '#061b49',
				colorLight: '#ffffff',
				correctLevel: QRCode.CorrectLevel.M
			});
		}

		var copyBtn = document.getElementById('copy-event-qr');
		if (copyBtn && urlInput) {
			copyBtn.addEventListener('click', function () {
				urlInput.select();
				urlInput.setSelectionRange(0, 99999);
				if (navigator.clipboard) {
					navigator.clipboard.writeText(urlInput.value);
				} else {
					document.execCommand('copy');
				}
				copyBtn.innerHTML = '<i class="ti ti-check me-1"></i>Tersalin';
				setTimeout(function () {
					copyBtn.innerHTML = '<i class="ti ti-copy me-1"></i>Copy Link';
				}, 1500);
			});
		}

		var downloadBtn = document.getElementById('download-event-qr');
		if (downloadBtn && qrTarget) {
			downloadBtn.addEventListener('click', function () {
				var canvas = qrTarget.querySelector('canvas');
				var image = qrTarget.querySelector('img');
				var dataUrl = canvas ? canvas.toDataURL('image/png') : (image ? image.src : '');
				if (! dataUrl) {
					return;
				}
				var link = document.createElement('a');
				link.href = dataUrl;
				link.download = 'qr-pendaftaran-event-<?= (int) $selected_event['id']; ?>.png';
				document.body.appendChild(link);
				link.click();
				link.remove();
			});
		}
	});
	</script>
<?php endif; ?>
