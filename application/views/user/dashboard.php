<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tabler_css = 'https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css';
$tabler_icons_css = 'https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/tabler-icons.min.css';
$status_labels = [
	'active' => 'Aktif',
	'inactive' => 'Nonaktif',
	'blocked' => 'Diblokir',
	'expired' => 'Kedaluwarsa',
	'unknown' => 'Belum Dipetakan',
];
$request_status_labels = [
	'pending' => 'Menunggu',
	'approved' => 'Disetujui',
	'rejected' => 'Ditolak',
	'fulfilled' => 'Selesai',
	'cancelled' => 'Dibatalkan',
];
$url_path = function ($path) {
	$segments = explode('/', str_replace('\\', '/', trim((string) $path, '/')));
	return implode('/', array_map('rawurlencode', $segments));
};
if (! empty($member['photo_local_path'])) {
	$photo_url = base_url($url_path($member['photo_local_path']));
} elseif (! empty($member['photo_source_path']) && strpos($member['photo_source_path'], 'assets/') === 0) {
	$photo_url = base_url($url_path($member['photo_source_path']));
} elseif (! empty($member['photo_source_path'])) {
	$photo_url = base_url($url_path('assets/uploads/inlislite/source_mirror/' . $member['photo_source_path']));
} else {
	$photo_url = '';
}
$member_name = $member['full_name'] ?? ($current_user['full_name'] ?? $current_user['username']);
$card_status = $member['card_status'] ?? 'active';
$member_status = $member ? ($status_labels[$member['status']] ?? ucfirst($member['status'])) : 'Belum Terhubung';
$card_status_label = $card_status === 'blocked' ? 'Kartu Diblokir' : $member_status;
$token_short = $verify_url ? strtoupper(substr(basename((string) $verify_url), 0, 8)) : '-';
$latest_renewal = ! empty($renewal_requests) ? $renewal_requests[0] : null;
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
	<link rel="stylesheet" href="<?= base_url('assets/css/pustaka-polish.css?v=20260802j'); ?>">
</head>
<body class="user-page">
	<header class="user-topbar user-topbar-app">
		<a class="public-brand" href="<?= base_url(); ?>">
			<span class="brand-logo-shell">
				<img class="brand-logo" src="<?= base_url('img/logo-small.jpeg'); ?>" alt="Logo Kabupaten Rembang">
			</span>
			<span class="public-brand-text">Pustaka Digital Rembang</span>
		</a>
		<div class="btn-list">
			<a href="<?= base_url(); ?>" class="btn btn-outline-primary btn-sm"><i class="ti ti-home me-1"></i>Beranda</a>
			<a href="<?= base_url('katalog'); ?>" class="btn btn-outline-primary btn-sm"><i class="ti ti-search me-1"></i>Katalog</a>
			<a href="<?= base_url('user/reading-checkin'); ?>" class="btn btn-outline-primary btn-sm"><i class="ti ti-map-pin-check me-1"></i>Pojok Baca</a>
			<a href="<?= base_url('logout'); ?>" class="btn btn-primary btn-sm"><i class="ti ti-logout me-1"></i>Logout</a>
		</div>
	</header>
	<nav class="member-bottom-nav" aria-label="Navigasi pemustaka">
		<a href="<?= base_url(); ?>"><i class="ti ti-home"></i><span>Beranda</span></a>
		<a href="<?= base_url('katalog'); ?>"><i class="ti ti-search"></i><span>Katalog</span></a>
		<a href="<?= base_url('user/dashboard'); ?>" class="active"><i class="ti ti-id"></i><span>Dashboard</span></a>
		<a href="<?= base_url('user/reading-checkin'); ?>"><i class="ti ti-map-pin-check"></i><span>Pojok</span></a>
	</nav>

	<main class="user-dashboard user-dashboard-v2">
		<div class="container-xl">
			<?php if ($this->session->flashdata('success')): ?>
				<div class="alert alert-success"><?= html_escape($this->session->flashdata('success')); ?></div>
			<?php endif; ?>
			<?php if ($this->session->flashdata('error')): ?>
				<div class="alert alert-danger"><?= html_escape($this->session->flashdata('error')); ?></div>
			<?php endif; ?>

			<section class="member-app-shell">
				<div class="member-pass-wrap">
					<div class="member-digital-pass <?= $card_status === 'blocked' ? 'is-blocked' : ''; ?>">
						<div class="member-card-watermark">PDR</div>
						<div class="member-pass-head">
							<div class="member-card-brand">
								<span class="member-card-logo"><img src="<?= base_url('img/logo-small.jpeg'); ?>" alt="Logo Kabupaten Rembang"></span>
								<div>
									<div class="member-card-label">Pustaka Digital Rembang</div>
									<strong>Kartu Anggota Digital</strong>
								</div>
							</div>
							<span class="member-card-chip"><?= html_escape($card_status_label); ?></span>
						</div>
						<div class="member-pass-body">
							<div class="member-avatar member-avatar-large">
								<?php if ($photo_url): ?>
									<img src="<?= html_escape($photo_url); ?>" alt="Foto <?= html_escape($member_name); ?>">
								<?php else: ?>
									<i class="ti ti-user"></i>
								<?php endif; ?>
							</div>
							<div class="member-pass-identity">
								<span>Nama Pemustaka</span>
								<h1><?= html_escape($member_name); ?></h1>
								<div class="member-pass-number"><?= html_escape($member['member_no'] ?? $current_user['username']); ?></div>
								<p><?= html_escape($member ? ($member['member_type_label'] ?: ($member['member_type'] ?: 'Pemustaka')) : 'Akun pemustaka'); ?></p>
							</div>
						</div>
						<?php if ($member): ?>
							<div class="member-pass-footer">
								<div><span>Berlaku sampai</span><strong><?= html_escape($member['expired_at'] ?: '-'); ?></strong></div>
								<div><span>Token</span><strong><?= html_escape($token_short); ?></strong></div>
							</div>
							<div class="member-pass-qr">
								<div id="member-qr" class="member-qr-box" data-qr="<?= html_escape($verify_url); ?>"></div>
								<div>
									<strong>Scan untuk verifikasi</strong>
									<p>QR memvalidasi kartu tanpa membuka NIK di area publik.</p>
								</div>
							</div>
						<?php else: ?>
							<div class="member-card-empty">
								<i class="ti ti-id-off"></i>
								<p>Akun ini belum terhubung ke data member hasil migrasi.</p>
							</div>
						<?php endif; ?>
					</div>
				</div>

				<div class="member-service-panel">
					<div class="section-kicker">Dashboard Pemustaka</div>
					<h2>Halo, <?= html_escape($member_name); ?></h2>
					<p class="text-secondary">Semua layanan pribadi dikumpulkan di sini: kartu digital, katalog, perpanjangan membership, request buku, dan histori aktivitas.</p>
					<div class="member-action-grid">
						<a href="<?= base_url('katalog'); ?>" class="member-action">
							<i class="ti ti-search"></i>
							<span>Cari Buku</span>
						</a>
						<?php if ($verify_url): ?>
							<a href="<?= html_escape($verify_url); ?>" class="member-action">
								<i class="ti ti-shield-check"></i>
								<span>Verifikasi</span>
							</a>
						<?php endif; ?>
						<a href="#membership-renewal" class="member-action">
							<i class="ti ti-id-badge-2"></i>
							<span>Perpanjang</span>
						</a>
						<a href="#book-requests" class="member-action">
							<i class="ti ti-book-upload"></i>
							<span>Request Buku</span>
						</a>
						<a href="<?= base_url('user/reading-checkin'); ?>" class="member-action">
							<i class="ti ti-map-pin-check"></i>
							<span>Pojok Baca</span>
						</a>
					</div>
					<div class="member-status-stack">
						<div class="member-status-card">
							<i class="ti ti-books"></i>
							<div><span>Katalog publik</span><strong><?= number_format($catalog_count, 0, ',', '.'); ?></strong></div>
						</div>
						<div class="member-status-card">
							<i class="ti ti-receipt-2"></i>
							<div><span>Riwayat pinjam</span><strong><?= number_format(count($recent_loans), 0, ',', '.'); ?></strong></div>
						</div>
						<div class="member-status-card">
							<i class="ti ti-door-enter"></i>
							<div><span>Kunjungan</span><strong><?= number_format(count($recent_visits), 0, ',', '.'); ?></strong></div>
						</div>
						<div class="member-status-card">
							<i class="ti ti-ticket"></i>
							<div><span>Token baca</span><strong><?= ! empty($reading_token) ? 'Aktif' : 'Belum'; ?></strong></div>
						</div>
					</div>
					<?php if ($card_status === 'blocked'): ?>
						<div class="member-alert-soft">
							<i class="ti ti-lock"></i>
							<span>Kartu digital sedang diblokir. Alasan: <?= html_escape($member['card_block_reason'] ?: 'Belum ada catatan.'); ?></span>
						</div>
					<?php elseif ($latest_renewal): ?>
						<div class="member-alert-soft">
							<i class="ti ti-clock-check"></i>
							<span>Pengajuan terakhir <?= html_escape($latest_renewal['request_code']); ?>: <?= html_escape($request_status_labels[$latest_renewal['status']] ?? $latest_renewal['status']); ?>.</span>
						</div>
					<?php endif; ?>
				</div>
			</section>

			<section class="member-digital-library">
				<div class="member-section-head">
					<div>
						<div class="section-kicker">Buku Digital</div>
						<h2>Siap dibaca online</h2>
					</div>
					<a href="<?= base_url('katalog?q=Project%20Gutenberg'); ?>" class="btn btn-outline-primary"><i class="ti ti-books me-1"></i>Lihat Katalog Digital</a>
				</div>
				<div class="member-digital-grid">
					<?php if (empty($digital_books)): ?>
						<div class="member-mini-empty">Belum ada buku digital aktif.</div>
					<?php endif; ?>
					<?php foreach ($digital_books as $asset): ?>
						<?php
							if (! empty($asset['cover_local_path'])) {
								$digital_cover = base_url($url_path($asset['cover_local_path']));
							} elseif (! empty($asset['cover_source_path'])) {
								$digital_cover = base_url($url_path('assets/uploads/inlislite/source_mirror/' . $asset['cover_source_path']));
							} else {
								$digital_cover = '';
							}
							$policy_label = [
								'download_allowed' => 'Bebas unduh',
								'location_only' => 'Token/GPS',
								'online_only' => 'Online',
								'member_only' => 'Member',
								'internal' => 'Internal',
							][$asset['access_policy']] ?? $asset['access_policy'];
						?>
						<article class="member-digital-book">
							<a href="<?= base_url('reader/read/' . (int) $asset['id']); ?>" class="member-digital-cover">
								<?php if ($digital_cover): ?>
									<img src="<?= html_escape($digital_cover); ?>" alt="Cover <?= html_escape($asset['title']); ?>" loading="lazy">
								<?php else: ?>
									<i class="ti ti-book"></i>
								<?php endif; ?>
							</a>
							<div>
								<span><?= html_escape($policy_label); ?></span>
								<h3><?= html_escape($asset['title']); ?></h3>
								<p><?= html_escape($asset['statement_responsibility'] ?: 'Penulis belum tercatat'); ?></p>
								<a href="<?= base_url('reader/read/' . (int) $asset['id']); ?>" class="btn btn-primary btn-sm">
									<i class="ti ti-book-reader me-1"></i>Baca Online
								</a>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			</section>

			<section class="member-content-grid">
				<div class="member-panel" id="membership-renewal">
					<div class="member-panel-head">
						<div>
							<div class="section-kicker">Membership</div>
							<h3>Ajukan Perpanjangan</h3>
						</div>
						<i class="ti ti-calendar-plus"></i>
					</div>
					<?php if ($member): ?>
						<?= form_open('membership/renewal/request', ['class' => 'member-renewal-form']); ?>
							<label class="form-label">Durasi</label>
							<select class="form-select mb-2" name="requested_months">
								<option value="12">12 bulan</option>
								<option value="6">6 bulan</option>
								<option value="24">24 bulan</option>
							</select>
							<label class="form-label">Catatan</label>
							<textarea class="form-control mb-3" name="reason" rows="3" placeholder="Opsional, misalnya perubahan data atau kebutuhan layanan."></textarea>
							<button type="submit" class="btn btn-primary w-100"><i class="ti ti-send me-1"></i>Kirim Pengajuan</button>
						<?= form_close(); ?>
					<?php else: ?>
						<p class="text-secondary mb-0">Akun perlu dihubungkan ke data member terlebih dahulu.</p>
					<?php endif; ?>
				</div>

				<div class="member-panel" id="book-requests">
					<div class="member-panel-head">
						<div>
							<div class="section-kicker">Request Buku</div>
							<h3>Permintaan Terakhir</h3>
						</div>
						<i class="ti ti-book-upload"></i>
					</div>
					<div class="member-mini-list">
						<?php if (empty($book_requests)): ?>
							<div class="member-mini-empty">Belum ada request buku. Buka katalog publik untuk mengajukan.</div>
						<?php endif; ?>
						<?php foreach ($book_requests as $request): ?>
							<div class="member-mini-item">
								<div>
									<strong><?= html_escape($request['title'] ?: 'Katalog #' . $request['book_id']); ?></strong>
									<span><?= html_escape($request['request_code']); ?> - <?= html_escape($request_status_labels[$request['status']] ?? $request['status']); ?></span>
								</div>
								<i class="ti ti-chevron-right"></i>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</section>

			<section class="member-content-grid member-content-grid-wide">
				<div class="member-panel">
					<div class="member-panel-head">
						<div>
							<div class="section-kicker">Aktivitas</div>
							<h3>Riwayat Pinjam</h3>
						</div>
						<i class="ti ti-books"></i>
					</div>
					<div class="member-mini-list">
						<?php if (empty($recent_loans)): ?><div class="member-mini-empty">Belum ada riwayat pinjam yang terhubung.</div><?php endif; ?>
						<?php foreach ($recent_loans as $loan): ?>
							<div class="member-mini-item">
								<div>
									<strong><?= html_escape($loan['title'] ?: ('Koleksi #' . $loan['source_collection_id'])); ?></strong>
									<span><?= html_escape($loan['loan_date'] ?: '-'); ?> - <?= html_escape($loan['loan_status'] ?: '-'); ?></span>
								</div>
								<code><?= html_escape($loan['barcode'] ?: '-'); ?></code>
							</div>
						<?php endforeach; ?>
					</div>
				</div>

				<div class="member-panel">
					<div class="member-panel-head">
						<div>
							<div class="section-kicker">Kunjungan</div>
							<h3>Buku Tamu Terakhir</h3>
						</div>
						<i class="ti ti-door-enter"></i>
					</div>
					<div class="member-mini-list">
						<?php if (empty($recent_visits)): ?><div class="member-mini-empty">Belum ada kunjungan yang terhubung.</div><?php endif; ?>
						<?php foreach ($recent_visits as $visit): ?>
							<div class="member-mini-item">
								<div>
									<strong><?= html_escape($visit['visited_at'] ?: '-'); ?></strong>
									<span><?= html_escape(($visit['location_label'] ?? '') ?: (($visit['location_id'] ?? '') ?: '-')); ?></span>
								</div>
								<i class="ti ti-map-pin"></i>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		</div>
	</main>

	<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
	<script>
	(function () {
		var target = document.getElementById('member-qr');
		if (!target || !window.QRCode) {
			return;
		}
		new QRCode(target, {
			text: target.getAttribute('data-qr'),
			width: 128,
			height: 128,
			colorDark: '#0d56a6',
			colorLight: '#ffffff',
			correctLevel: QRCode.CorrectLevel.M
		});
	})();
	</script>
</body>
</html>
