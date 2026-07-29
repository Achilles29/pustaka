<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$query_base = $_GET;
unset($query_base['page']);
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Keamanan Sistem</div>
				<h1 class="page-title">Audit Log</h1>
			</div>
		</div>
	</div>
</div>

<div class="page-body">
	<div class="container-xl">
		<div class="card admin-card mb-3">
			<div class="card-body">
				<?= form_open('audit', ['method' => 'get', 'class' => 'row g-2 align-items-end']); ?>
					<div class="col-md-4">
						<label class="form-label">Cari</label>
						<input type="text" class="form-control" name="q" value="<?= html_escape($filters['q'] ?? ''); ?>" placeholder="Event, entity, user">
					</div>
					<div class="col-md-3">
						<label class="form-label">Event</label>
						<select name="event_code" class="form-select">
							<option value="">Semua event</option>
							<?php foreach ($event_options as $option): ?>
								<option value="<?= html_escape($option['event_code']); ?>" <?= ($filters['event_code'] ?? '') === $option['event_code'] ? 'selected' : ''; ?>>
									<?= html_escape($option['event_code']); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-3">
						<label class="form-label">Entity</label>
						<select name="entity_type" class="form-select">
							<option value="">Semua entity</option>
							<?php foreach ($entity_options as $option): ?>
								<option value="<?= html_escape($option['entity_type']); ?>" <?= ($filters['entity_type'] ?? '') === $option['entity_type'] ? 'selected' : ''; ?>>
									<?= html_escape($option['entity_type']); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-2">
						<label class="form-label">Baris</label>
						<select name="per_page" class="form-select">
							<?php foreach ([10, 25, 50, 100] as $option): ?>
								<option value="<?= $option; ?>" <?= (int) ($per_page ?? 25) === $option ? 'selected' : ''; ?>><?= $option; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-2">
						<button type="submit" class="btn btn-outline-primary w-100">Filter</button>
					</div>
				<?= form_close(); ?>
			</div>
		</div>

		<div class="card admin-card">
			<div class="card-header">
				<h2 class="card-title">Aktivitas Terbaru</h2>
				<div class="card-actions text-secondary">
					<?= number_format((int) $total_logs, 0, ',', '.'); ?> log
				</div>
			</div>
			<div class="table-responsive">
				<table class="table table-vcenter card-table">
					<thead>
						<tr>
							<th>Waktu</th>
							<th>Event</th>
							<th>Entity</th>
							<th>User</th>
							<th>IP</th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($logs)): ?>
							<tr>
								<td colspan="5" class="text-center text-secondary py-4">Belum ada audit log sesuai filter.</td>
							</tr>
						<?php endif; ?>
						<?php foreach ($logs as $log): ?>
							<tr>
								<td class="text-secondary"><?= html_escape($log['created_at']); ?></td>
								<td>
									<div class="fw-semibold"><?= html_escape($log['event_code']); ?></div>
									<div class="text-secondary small"><?= html_escape($log['user_agent'] ?: '-'); ?></div>
								</td>
								<td>
									<span class="badge bg-blue-lt"><?= html_escape($log['entity_type'] ?: '-'); ?></span>
									<div class="text-secondary small mt-1"><?= html_escape($log['entity_id'] ?: '-'); ?></div>
								</td>
								<td><?= html_escape($log['full_name'] ?: $log['username'] ?: 'system'); ?></td>
								<td><?= html_escape($log['ip_address'] ?: '-'); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php if ((int) $total_pages > 1): ?>
				<div class="card-footer d-flex align-items-center">
					<p class="m-0 text-secondary">
						Halaman <?= number_format((int) $page, 0, ',', '.'); ?> dari <?= number_format((int) $total_pages, 0, ',', '.'); ?>
					</p>
					<ul class="pagination m-0 ms-auto">
						<?php
						$prev_query = array_merge($query_base, ['page' => max(1, (int) $page - 1)]);
						$next_query = array_merge($query_base, ['page' => min((int) $total_pages, (int) $page + 1)]);
						?>
						<li class="page-item <?= (int) $page <= 1 ? 'disabled' : ''; ?>">
							<a class="page-link" href="<?= base_url('audit?' . http_build_query($prev_query)); ?>">Sebelumnya</a>
						</li>
						<?php
						$start = max(1, (int) $page - 2);
						$end = min((int) $total_pages, (int) $page + 2);
						for ($i = $start; $i <= $end; $i++):
							$page_query = array_merge($query_base, ['page' => $i]);
						?>
							<li class="page-item <?= $i === (int) $page ? 'active' : ''; ?>">
								<a class="page-link" href="<?= base_url('audit?' . http_build_query($page_query)); ?>"><?= $i; ?></a>
							</li>
						<?php endfor; ?>
						<li class="page-item <?= (int) $page >= (int) $total_pages ? 'disabled' : ''; ?>">
							<a class="page-link" href="<?= base_url('audit?' . http_build_query($next_query)); ?>">Berikutnya</a>
						</li>
					</ul>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
