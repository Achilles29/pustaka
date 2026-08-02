<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tabler_css = 'https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css';
$tabler_icons_css = 'https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/tabler-icons.min.css';
$policy_labels = [
	'online_only' => 'Online saja',
	'download_allowed' => 'Boleh download',
	'location_only' => 'Kunci GPS',
	'member_only' => 'Member saja',
	'internal' => 'Internal',
];
$origin_labels = [
	'external' => 'Akses luar lokasi',
	'reading_point' => 'Di Pojok Baca',
	'library' => 'Di Perpustakaan',
	'admin' => 'Preview admin',
];
$reader_session = isset($reader_session) ? (array) $reader_session : [];
$session_id = (int) ($reader_session['id'] ?? 0);
$session_token = (string) ($reader_session['secure_token'] ?? '');
$can_stream_pdf = ! empty($can_stream_pdf);
$page_info_url = (string) ($page_info_url ?? '');
$page_url_base = (string) ($page_url_base ?? '');
$page_query = (string) ($page_query ?? '');
$watermark = trim(($member['full_name'] ?? 'Member') . ' | ' . ($member['member_no'] ?? '-') . ' | ' . date('Y-m-d H:i'));
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
<body class="user-page secure-reader-page" oncontextmenu="return false">
	<header class="user-topbar user-topbar-app">
		<a class="public-brand" href="<?= base_url('user/dashboard'); ?>">
			<span class="brand-logo-shell"><img class="brand-logo" src="<?= base_url('img/logo-small.jpeg'); ?>" alt="Logo Kabupaten Rembang"></span>
			<span class="public-brand-text">Pustaka Digital Rembang</span>
		</a>
		<div class="btn-list">
			<a href="<?= base_url(); ?>" class="btn btn-outline-primary btn-sm"><i class="ti ti-home me-1"></i>Beranda</a>
			<a href="<?= base_url('katalog'); ?>" class="btn btn-outline-primary btn-sm"><i class="ti ti-search me-1"></i>Katalog</a>
			<a href="<?= base_url('user/reading-checkin'); ?>" class="btn btn-outline-primary btn-sm"><i class="ti ti-map-pin-check me-1"></i>Pojok Baca</a>
			<a href="<?= base_url('user/dashboard'); ?>" class="btn btn-primary btn-sm"><i class="ti ti-id me-1"></i>Dashboard</a>
		</div>
	</header>
	<nav class="member-bottom-nav" aria-label="Navigasi pemustaka">
		<a href="<?= base_url(); ?>"><i class="ti ti-home"></i><span>Beranda</span></a>
		<a href="<?= base_url('katalog'); ?>"><i class="ti ti-search"></i><span>Katalog</span></a>
		<a href="<?= base_url('user/dashboard'); ?>"><i class="ti ti-id"></i><span>Dashboard</span></a>
		<a href="<?= base_url('user/reading-checkin'); ?>"><i class="ti ti-map-pin-check"></i><span>Pojok</span></a>
	</nav>

	<main class="user-dashboard user-dashboard-v2">
		<div class="container-xl">
			<div class="secure-reader-shell">
				<div class="secure-reader-toolbar">
					<div>
						<div class="section-kicker">Reader aman</div>
						<h2><?= html_escape($asset['title'] ?: 'Aset Digital'); ?></h2>
						<p><?= html_escape($asset['statement_responsibility'] ?: 'Penanggung jawab belum tercatat'); ?></p>
					</div>
					<div class="secure-reader-badges">
						<span class="badge bg-blue-lt"><?= html_escape($policy_labels[$asset['access_policy']] ?? $asset['access_policy']); ?></span>
						<span class="badge <?= ($reader_context['quota_charged'] ?? 0) > 0 ? 'bg-yellow-lt' : 'bg-green-lt'; ?>">
							<?= ($reader_context['quota_charged'] ?? 0) > 0 ? 'Kuota -' . (int) $reader_context['quota_charged'] : 'Kuota tidak berkurang'; ?>
						</span>
						<span class="badge bg-cyan-lt"><?= html_escape($origin_labels[$reader_context['origin'] ?? 'external'] ?? 'Akses'); ?></span>
					</div>
				</div>
				<div class="secure-reader-stage">
					<?php if (! empty($stream_url)): ?>
						<div class="secure-pdfjs-reader" data-stream-url="<?= html_escape($stream_url); ?>" data-session-id="<?= $session_id; ?>" data-session-token="<?= html_escape($session_token); ?>" data-watermark="<?= html_escape($watermark); ?>" data-audit-url="<?= base_url('reader/audit-page'); ?>">
							<div class="secure-pdfjs-controls">
								<button type="button" class="btn btn-outline-primary btn-sm" data-reader-prev><i class="ti ti-chevron-left"></i><span>Sebelumnya</span></button>
								<div class="secure-pdfjs-page-state"><span data-reader-page>1</span>/<span data-reader-pages>...</span></div>
								<button type="button" class="btn btn-outline-primary btn-sm" data-reader-next><span>Berikutnya</span><i class="ti ti-chevron-right"></i></button>
							</div>
							<div class="secure-pdfjs-canvas-shell">
								<canvas data-reader-canvas></canvas>
								<div class="secure-reader-watermark" data-reader-watermark></div>
								<button type="button" class="secure-reader-tap-zone secure-reader-tap-prev" data-reader-tap-prev aria-label="Halaman sebelumnya"><i class="ti ti-chevron-left"></i></button>
								<button type="button" class="secure-reader-tap-zone secure-reader-tap-next" data-reader-tap-next aria-label="Halaman berikutnya"><i class="ti ti-chevron-right"></i></button>
							</div>
							<div class="secure-pdfjs-hint">Reader aman aktif. Tap sisi kanan/kiri halaman atau swipe untuk berpindah halaman.</div>
						</div>
					<?php elseif ($page_info_url !== '' && $page_url_base !== ''): ?>
						<div class="secure-image-reader" data-page-info-url="<?= html_escape($page_info_url); ?>" data-page-url-base="<?= html_escape($page_url_base); ?>" data-page-query="<?= html_escape($page_query); ?>">
							<div class="secure-pdfjs-controls">
								<button type="button" class="btn btn-outline-primary btn-sm" data-page-prev><i class="ti ti-chevron-left"></i><span>Sebelumnya</span></button>
								<div class="secure-pdfjs-page-state"><span data-page-now>1</span>/<span data-page-total>...</span></div>
								<button type="button" class="btn btn-outline-primary btn-sm" data-page-next><span>Berikutnya</span><i class="ti ti-chevron-right"></i></button>
							</div>
							<div class="secure-page-image-shell">
								<div class="secure-page-loader" data-page-loader>Menyiapkan halaman aman...</div>
								<img data-page-image alt="Halaman buku digital" draggable="false">
								<button type="button" class="secure-reader-tap-zone secure-reader-tap-prev" data-page-tap-prev aria-label="Halaman sebelumnya"><i class="ti ti-chevron-left"></i></button>
								<button type="button" class="secure-reader-tap-zone secure-reader-tap-next" data-page-tap-next aria-label="Halaman berikutnya"><i class="ti ti-chevron-right"></i></button>
							</div>
							<div class="secure-pdfjs-hint">Mode aman aktif: tap sisi kanan/kiri halaman atau swipe. Browser hanya menerima gambar halaman ber-watermark.</div>
						</div>
					<?php else: ?>
						<div class="secure-reader-empty">
							<i class="ti ti-file-lock"></i>
							<h3>PDF dikunci dari browser.</h3>
							<p>Sesi baca sudah tercatat, tetapi file PDF utuh tidak dikirim ke perangkat untuk aset non-downloadable. Mode baca halaman aman membutuhkan renderer server-side agar tiap halaman dikirim sebagai gambar ber-watermark.</p>
							<?php if (! empty($reader_context['location_label'])): ?>
								<p>Lokasi: <?= html_escape($reader_context['location_label']); ?></p>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</main>
	<?php if ($page_info_url !== '' && $page_url_base !== ''): ?>
	<script>
		(function () {
			const reader = document.querySelector('.secure-image-reader');
			if (!reader) return;
			const image = reader.querySelector('[data-page-image]');
			const shell = reader.querySelector('.secure-page-image-shell');
			const loader = reader.querySelector('[data-page-loader]');
			const prev = reader.querySelector('[data-page-prev]');
			const next = reader.querySelector('[data-page-next]');
			const tapPrev = reader.querySelector('[data-page-tap-prev]');
			const tapNext = reader.querySelector('[data-page-tap-next]');
			const now = reader.querySelector('[data-page-now]');
			const total = reader.querySelector('[data-page-total]');
			let current = 1;
			let pages = 1;
			let startX = 0;
			let startY = 0;
			let pointerActive = false;

			function setBusy(isBusy) {
				loader.hidden = !isBusy;
				prev.disabled = isBusy || current <= 1;
				next.disabled = isBusy || current >= pages;
			}

			function imageUrl(page) {
				return reader.dataset.pageUrlBase + '/' + page + '?' + reader.dataset.pageQuery + '&_=' + Date.now();
			}

			function turnClass(direction) {
				return direction === 'prev' ? 'is-turning-prev' : 'is-turning-next';
			}

			function animatePage(direction) {
				const className = turnClass(direction);
				shell.classList.remove('is-turning-prev', 'is-turning-next');
				void shell.offsetWidth;
				shell.classList.add(className);
				window.setTimeout(() => shell.classList.remove(className), 360);
			}

			function showPage(page, direction) {
				const target = Math.max(1, Math.min(pages, page));
				if (target === current && image.dataset.loadedPage === String(target)) return;
				current = target;
				now.textContent = current;
				setBusy(true);
				const nextImage = new Image();
				nextImage.onload = function () {
					image.src = nextImage.src;
					image.dataset.loadedPage = String(current);
					image.hidden = false;
					animatePage(direction || 'next');
					setBusy(false);
				};
				nextImage.onerror = function () {
					loader.hidden = false;
					loader.textContent = 'Halaman gagal dirender. Buka ulang sesi baca.';
					prev.disabled = false;
					next.disabled = false;
				};
				nextImage.src = imageUrl(current);
			}

			function goPrev() {
				showPage(current - 1, 'prev');
			}

			function goNext() {
				showPage(current + 1, 'next');
			}

			fetch(reader.dataset.pageInfoUrl, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
				.then((response) => response.ok ? response.json() : Promise.reject())
				.then((data) => {
					pages = Math.max(1, parseInt(data.pages || 1, 10));
					total.textContent = pages;
					showPage(1, 'next');
				})
				.catch(() => {
					loader.textContent = 'Renderer halaman belum siap.';
					prev.disabled = true;
					next.disabled = true;
				});

			prev.addEventListener('click', goPrev);
			next.addEventListener('click', goNext);
			tapPrev.addEventListener('click', goPrev);
			tapNext.addEventListener('click', goNext);
			shell.addEventListener('pointerdown', function (event) {
				pointerActive = true;
				startX = event.clientX;
				startY = event.clientY;
			});
			shell.addEventListener('pointerup', function (event) {
				if (!pointerActive) return;
				pointerActive = false;
				const deltaX = event.clientX - startX;
				const deltaY = event.clientY - startY;
				if (Math.abs(deltaX) < 44 || Math.abs(deltaX) < Math.abs(deltaY) * 1.35) return;
				if (deltaX < 0) goNext();
				if (deltaX > 0) goPrev();
			});
			document.addEventListener('keydown', function (event) {
				const blocked = (event.ctrlKey || event.metaKey) && ['s', 'p', 'u'].includes(event.key.toLowerCase());
				if (blocked || event.key === 'F12') {
					event.preventDefault();
				}
				if (event.key === 'ArrowLeft') goPrev();
				if (event.key === 'ArrowRight') goNext();
			});
		})();
	</script>
	<?php endif; ?>
	<?php if (! empty($stream_url)): ?>
	<script type="module">
		import * as pdfjsLib from 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.10.38/pdf.min.mjs';
		const reader = document.querySelector('.secure-pdfjs-reader');
		if (reader) {
			pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.10.38/pdf.worker.min.mjs';
			const canvas = reader.querySelector('[data-reader-canvas]');
			const shell = reader.querySelector('.secure-pdfjs-canvas-shell');
			const ctx = canvas.getContext('2d');
			const pageNow = reader.querySelector('[data-reader-page]');
			const pageTotal = reader.querySelector('[data-reader-pages]');
			const watermark = reader.querySelector('[data-reader-watermark]');
			const prev = reader.querySelector('[data-reader-prev]');
			const next = reader.querySelector('[data-reader-next]');
			const tapPrev = reader.querySelector('[data-reader-tap-prev]');
			const tapNext = reader.querySelector('[data-reader-tap-next]');
			let pdf = null;
			let currentPage = 1;
			let rendering = false;
			let startX = 0;
			let startY = 0;
			let pointerActive = false;

			function audit(page) {
				const form = new FormData();
				form.append('session', reader.dataset.sessionId || '');
				form.append('token', reader.dataset.sessionToken || '');
				form.append('page', page);
				fetch(reader.dataset.auditUrl, {
					method: 'POST',
					body: form,
					headers: {'X-Requested-With': 'XMLHttpRequest'}
				}).catch(() => {});
			}

			function animateCanvas(direction) {
				const className = direction === 'prev' ? 'is-turning-prev' : 'is-turning-next';
				shell.classList.remove('is-turning-prev', 'is-turning-next');
				void shell.offsetWidth;
				shell.classList.add(className);
				window.setTimeout(() => shell.classList.remove(className), 360);
			}

			async function renderPage(pageNumber, direction) {
				if (!pdf || rendering) return;
				rendering = true;
				const page = await pdf.getPage(pageNumber);
				const containerWidth = Math.max(320, reader.querySelector('.secure-pdfjs-canvas-shell').clientWidth - 24);
				const viewport = page.getViewport({scale: 1});
				const scale = Math.min(1.55, containerWidth / viewport.width);
				const scaled = page.getViewport({scale});
				canvas.width = scaled.width;
				canvas.height = scaled.height;
				await page.render({canvasContext: ctx, viewport: scaled}).promise;
				currentPage = pageNumber;
				pageNow.textContent = currentPage;
				watermark.textContent = reader.dataset.watermark + ' | Hal. ' + currentPage;
				prev.disabled = currentPage <= 1;
				next.disabled = currentPage >= pdf.numPages;
				animateCanvas(direction || 'next');
				audit(currentPage);
				rendering = false;
			}

			function goPrev() {
				renderPage(Math.max(1, currentPage - 1), 'prev');
			}

			function goNext() {
				renderPage(Math.min(pdf ? pdf.numPages : currentPage, currentPage + 1), 'next');
			}

			pdfjsLib.getDocument({url: reader.dataset.streamUrl, withCredentials: true}).promise.then((doc) => {
				pdf = doc;
				pageTotal.textContent = pdf.numPages;
				renderPage(1, 'next');
			}).catch(() => {
				reader.innerHTML = '<div class="secure-reader-empty"><i class="ti ti-alert-triangle"></i><h3>PDF gagal dibuka.</h3><p>Sesi baca mungkin sudah kedaluwarsa. Buka ulang dari katalog atau dashboard.</p></div>';
			});

			prev.addEventListener('click', goPrev);
			next.addEventListener('click', goNext);
			tapPrev.addEventListener('click', goPrev);
			tapNext.addEventListener('click', goNext);
			shell.addEventListener('pointerdown', function (event) {
				pointerActive = true;
				startX = event.clientX;
				startY = event.clientY;
			});
			shell.addEventListener('pointerup', function (event) {
				if (!pointerActive) return;
				pointerActive = false;
				const deltaX = event.clientX - startX;
				const deltaY = event.clientY - startY;
				if (Math.abs(deltaX) < 44 || Math.abs(deltaX) < Math.abs(deltaY) * 1.35) return;
				if (deltaX < 0) goNext();
				if (deltaX > 0) goPrev();
			});
			document.addEventListener('keydown', function (event) {
				const blocked = (event.ctrlKey || event.metaKey) && ['s', 'p', 'u'].includes(event.key.toLowerCase());
				if (blocked || event.key === 'F12') {
					event.preventDefault();
				}
				if (event.key === 'ArrowLeft') goPrev();
				if (event.key === 'ArrowRight') goNext();
			});
		}
	</script>
	<?php endif; ?>
</body>
</html>
