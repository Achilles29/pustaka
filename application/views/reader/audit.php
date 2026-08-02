<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$event_labels = [
	'' => 'Semua event',
	'session_opened' => 'Sesi dibuka',
	'pdf_stream' => 'Stream PDF',
	'page_rendered' => 'Halaman dirender',
	'rate_limited' => 'Rate limit',
	'blocked' => 'Diblokir',
	'finished' => 'Selesai',
];
$filters = $filters ?? [];
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Reader PDF Aman</div>
				<h1 class="page-title">Audit Reader</h1>
			</div>
			<div class="col-auto ms-auto">
				<a href="<?= base_url('reader/assets'); ?>" class="btn btn-outline-primary">
					<i class="ti ti-arrow-left me-1"></i>Aset Digital
				</a>
			</div>
		</div>
	</div>
</div>

<div class="page-body">
	<div class="container-xl">
		<div class="card admin-card mb-3">
			<div class="card-body">
				<?= form_open('reader/audit', ['method' => 'get', 'class' => 'row g-2 align-items-end']); ?>
					<div class="col-md-4">
						<label class="form-label">Event</label>
						<select class="form-select" name="event_type">
							<?php foreach ($event_labels as $value => $label): ?>
								<option value="<?= html_escape($value); ?>" <?= ($filters['event_type'] ?? '') === $value ? 'selected' : ''; ?>><?= html_escape($label); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-8 btn-list">
						<button class="btn btn-primary" type="submit"><i class="ti ti-filter me-1"></i>Filter</button>
						<a href="<?= base_url('reader/audit'); ?>" class="btn btn-outline-secondary"><i class="ti ti-refresh me-1"></i>Reset</a>
					</div>
				<?= form_close(); ?>
			</div>
		</div>

		<div class="card admin-card data-workspace">
			<div class="card-header workspace-header">
				<div>
					<h2 class="card-title">Log Akses Reader</h2>
					<div class="text-secondary small">Menampilkan maksimal 150 log terbaru sesuai filter.</div>
				</div>
			</div>
			<div class="table-responsive">
				<table class="table table-vcenter card-table">
					<thead>
						<tr>
							<th>Waktu</th>
							<th>Event</th>
							<th>Buku</th>
							<th>Member/Admin</th>
							<th>Hal.</th>
							<th>IP</th>
							<th>Meta</th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($logs)): ?><tr><td colspan="7" class="text-center text-secondary py-4">Belum ada log.</td></tr><?php endif; ?>
						<?php foreach ($logs as $log): ?>
							<?php
								$meta = json_decode((string) ($log['meta_json'] ?? ''), true);
								$actor = $log['full_name'] ?: ($meta['admin_username'] ?? 'Tidak terbaca');
							?>
							<tr>
								<td data-label="Waktu"><?= html_escape($log['created_at']); ?></td>
								<td data-label="Event"><span class="badge <?= $log['event_type'] === 'blocked' ? 'bg-red-lt' : ($log['event_type'] === 'page_rendered' ? 'bg-green-lt' : 'bg-blue-lt'); ?>"><?= html_escape($event_labels[$log['event_type']] ?? $log['event_type']); ?></span></td>
								<td data-label="Buku">
									<div class="fw-semibold"><?= html_escape($log['title'] ?: 'Aset #' . $log['digital_asset_id']); ?></div>
									<div class="text-secondary small">Asset ID <?= (int) $log['digital_asset_id']; ?></div>
								</td>
								<td data-label="Member/Admin">
									<div><?= html_escape($actor); ?></div>
									<div class="text-secondary small"><?= html_escape($log['member_no'] ?: ('Admin #' . (int) ($meta['admin_user_id'] ?? 0))); ?></div>
								</td>
								<td data-label="Hal."><?= ! empty($log['page_number']) ? (int) $log['page_number'] : '-'; ?></td>
								<td data-label="IP"><?= html_escape($log['ip_address'] ?: '-'); ?></td>
								<td data-label="Meta"><code class="small"><?= html_escape($log['meta_json'] ?: '-'); ?></code></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
