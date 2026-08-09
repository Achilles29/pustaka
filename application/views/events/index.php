<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$metrics = [
	['key' => 'events', 'label' => 'Agenda', 'icon' => 'ti ti-calendar-event'],
	['key' => 'published', 'label' => 'Tayang', 'icon' => 'ti ti-speakerphone'],
	['key' => 'open', 'label' => 'Dibuka', 'icon' => 'ti ti-door-enter'],
	['key' => 'pending', 'label' => 'Antrean', 'icon' => 'ti ti-inbox'],
	['key' => 'attended', 'label' => 'Hadir', 'icon' => 'ti ti-user-check'],
];
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
unset($query_base['page']);
$page_url = function ($page) use ($query_base) {
	return base_url('events?' . http_build_query(array_merge($query_base, ['page' => $page])));
};
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Jejaring & Agenda</div>
				<h1 class="page-title">Event Literasi</h1>
			</div>
			<div class="col-auto ms-auto">
				<div class="btn-list">
					<a href="<?= base_url('agenda'); ?>" class="btn btn-outline-primary" target="_blank" rel="noopener"><i class="ti ti-world me-1"></i>Publik</a>
					<a href="<?= base_url('events/qr'); ?>" class="btn btn-outline-primary"><i class="ti ti-qrcode me-1"></i>QR Event</a>
					<a href="<?= base_url('events/create'); ?>" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Tambah Event</a>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="page-body">
	<div class="container-xl">
		<?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?= html_escape($this->session->flashdata('success')); ?></div><?php endif; ?>
		<?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?= html_escape($this->session->flashdata('error')); ?></div><?php endif; ?>

		<div class="metric-ribbon">
			<?php foreach ($metrics as $metric): ?>
				<div class="metric-ribbon-item">
					<span class="metric-icon"><i class="<?= html_escape($metric['icon']); ?>"></i></span>
					<div>
						<div class="metric-value"><?= number_format((int) ($stats[$metric['key']] ?? 0), 0, ',', '.'); ?></div>
						<div class="metric-label"><?= html_escape($metric['label']); ?></div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="card admin-card data-workspace">
			<div class="card-header workspace-header">
				<div>
					<h2 class="card-title">Pusat Agenda</h2>
					<div class="text-secondary small">Kelola publikasi kegiatan, formulir pendaftaran, dan antrean peserta.</div>
				</div>
			</div>

			<div class="card-body workspace-filter">
				<?= form_open('events', ['method' => 'get', 'class' => 'row g-2 align-items-end']); ?>
					<div class="col-md-3">
						<label class="form-label">Cari</label>
						<input type="text" class="form-control" name="q" value="<?= html_escape($filters['q'] ?? ''); ?>" placeholder="Judul, lokasi, penyelenggara">
					</div>
					<div class="col-md-2">
						<label class="form-label">Status</label>
						<select class="form-select" name="status">
							<option value="">Semua</option>
							<?php foreach ($status_labels as $value => $label): ?>
								<option value="<?= $value; ?>" <?= ($filters['status'] ?? '') === $value ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-2">
						<label class="form-label">Kategori</label>
						<select class="form-select" name="category_id">
							<option value="">Semua</option>
							<?php foreach ($categories as $category): ?>
								<option value="<?= (int) $category['id']; ?>" <?= (int) ($filters['category_id'] ?? 0) === (int) $category['id'] ? 'selected' : ''; ?>><?= html_escape($category['name']); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-2">
						<label class="form-label">Perpustakaan</label>
						<select class="form-select" name="library_id">
							<option value="">Semua</option>
							<?php foreach ($libraries as $library): ?>
								<option value="<?= (int) $library['id']; ?>" <?= (int) ($filters['library_id'] ?? 0) === (int) $library['id'] ? 'selected' : ''; ?>><?= html_escape($library['name']); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-1">
						<label class="form-label">Baris</label>
						<select class="form-select" name="per_page">
							<?php foreach ([10, 25, 50, 100] as $limit): ?>
								<option value="<?= $limit; ?>" <?= (int) ($filters['per_page'] ?? 25) === $limit ? 'selected' : ''; ?>><?= $limit; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-1">
						<button class="btn btn-outline-primary w-100"><i class="ti ti-filter"></i></button>
					</div>
					<div class="col-md-1">
						<a href="<?= base_url('events'); ?>" class="btn btn-outline-secondary w-100" title="Reset filter"><i class="ti ti-refresh"></i></a>
					</div>
				<?= form_close(); ?>
			</div>

			<div class="table-responsive">
				<table class="table table-vcenter card-table">
					<thead>
						<tr>
							<th>Event</th>
							<th>Jadwal</th>
							<th>Pendaftaran</th>
							<th>Peserta</th>
							<th>Status</th>
							<th class="w-1">Aksi</th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($events)): ?>
							<tr><td colspan="6" class="text-center text-secondary py-4">Belum ada event sesuai filter.</td></tr>
						<?php endif; ?>
						<?php foreach ($events as $event): ?>
							<tr>
								<td data-label="Event">
									<div class="d-flex align-items-center gap-3">
										<span class="event-dot" style="--event-color: <?= html_escape($event['category_color'] ?: '#005baa'); ?>"></span>
										<div>
											<div class="fw-semibold"><?= html_escape($event['title']); ?></div>
											<div class="text-secondary small"><?= html_escape($event['category_name'] ?: ($event['event_type'] ?: '-')); ?></div>
											<div class="small"><?= html_escape($event['library_name'] ?: ($event['organizer_name'] ?: 'Pustaka Digital Rembang')); ?></div>
										</div>
									</div>
								</td>
								<td data-label="Jadwal">
									<div><?= html_escape($event['starts_at'] ?: '-'); ?></div>
									<div class="text-secondary small"><?= html_escape($event['venue_type']); ?> · <?= html_escape($event['location_name'] ?: 'Lokasi menyusul'); ?></div>
								</td>
								<td data-label="Pendaftaran">
									<div><?= html_escape($registration_modes[$event['registration_mode']] ?? $event['registration_mode']); ?></div>
									<div class="text-secondary small"><?= $event['approval_mode'] === 'manual' ? 'Verifikasi manual' : 'Auto approve'; ?></div>
								</td>
								<td data-label="Peserta">
									<strong><?= number_format((int) $event['participant_total'], 0, ',', '.'); ?></strong>
									<span class="text-secondary">/ <?= $event['quota'] ? number_format((int) $event['quota'], 0, ',', '.') : 'tanpa batas'; ?></span>
									<div class="text-secondary small"><?= number_format((int) $event['registration_count'], 0, ',', '.'); ?> pendaftaran</div>
								</td>
								<td data-label="Status"><span class="badge bg-blue-lt"><?= html_escape($status_labels[$event['status']] ?? $event['status']); ?></span></td>
								<td data-label="Aksi">
									<div class="btn-list flex-nowrap">
										<a href="<?= base_url('events/detail/' . (int) $event['id']); ?>" class="btn btn-primary btn-sm"><i class="ti ti-eye me-1"></i>Detail</a>
										<a href="<?= base_url('events/qr?event_id=' . (int) $event['id']); ?>" class="btn btn-outline-primary btn-sm"><i class="ti ti-qrcode me-1"></i>QR</a>
										<a href="<?= base_url('events/edit/' . (int) $event['id']); ?>" class="btn btn-outline-primary btn-sm"><i class="ti ti-edit me-1"></i>Edit</a>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<?php if (($pagination['total_pages'] ?? 1) > 1): ?>
				<div class="card-footer d-flex align-items-center">
					<p class="m-0 text-secondary">Menampilkan <?= number_format((int) $pagination['total_rows'], 0, ',', '.'); ?> data.</p>
					<ul class="pagination m-0 ms-auto">
						<li class="page-item <?= (int) $pagination['page'] <= 1 ? 'disabled' : ''; ?>"><a class="page-link" href="<?= $page_url(max(1, (int) $pagination['page'] - 1)); ?>">Prev</a></li>
						<?php for ($i = max(1, (int) $pagination['page'] - 2); $i <= min((int) $pagination['total_pages'], (int) $pagination['page'] + 2); $i++): ?>
							<li class="page-item <?= $i === (int) $pagination['page'] ? 'active' : ''; ?>"><a class="page-link" href="<?= $page_url($i); ?>"><?= $i; ?></a></li>
						<?php endfor; ?>
						<li class="page-item <?= (int) $pagination['page'] >= (int) $pagination['total_pages'] ? 'disabled' : ''; ?>"><a class="page-link" href="<?= $page_url(min((int) $pagination['total_pages'], (int) $pagination['page'] + 1)); ?>">Next</a></li>
					</ul>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
