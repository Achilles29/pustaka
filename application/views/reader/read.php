<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$policy_labels = [
	'online_only' => 'Online saja',
	'download_allowed' => 'Boleh download',
	'location_only' => 'Kunci GPS',
	'member_only' => 'Member saja',
	'internal' => 'Internal',
];
$page_info_url = (string) ($page_info_url ?? '');
$page_url_base = (string) ($page_url_base ?? '');
?>
<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-2 align-items-center">
			<div class="col">
				<div class="page-pretitle">Reader PDF</div>
				<h1 class="page-title"><?= html_escape($asset['title'] ?: 'Aset Digital'); ?></h1>
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
		<div class="secure-reader-shell" oncontextmenu="return false">
			<div class="secure-reader-toolbar">
				<div>
					<div class="section-kicker">Mode aman</div>
					<h2><?= html_escape($asset['title'] ?: 'Aset Digital'); ?></h2>
					<p><?= html_escape($asset['statement_responsibility'] ?: 'Penanggung jawab belum tercatat'); ?></p>
				</div>
				<div class="secure-reader-badges">
					<span class="badge bg-blue-lt"><?= html_escape($policy_labels[$asset['access_policy']] ?? $asset['access_policy']); ?></span>
					<span class="badge <?= (int) $asset['is_downloadable'] === 1 ? 'bg-green-lt' : 'bg-red-lt'; ?>"><?= (int) $asset['is_downloadable'] === 1 ? 'Download boleh' : 'Download dikunci'; ?></span>
				</div>
			</div>
			<div class="secure-reader-stage">
				<?php if (! empty($stream_url)): ?>
					<iframe class="secure-reader-frame" src="<?= html_escape($stream_url); ?>#toolbar=0&navpanes=0" title="Reader <?= html_escape($asset['title'] ?: 'PDF'); ?>"></iframe>
				<?php elseif ($page_info_url !== '' && $page_url_base !== ''): ?>
					<div class="secure-image-reader" data-page-info-url="<?= html_escape($page_info_url); ?>" data-page-url-base="<?= html_escape($page_url_base); ?>" data-page-query="">
						<div class="secure-pdfjs-controls">
							<button type="button" class="btn btn-outline-primary btn-sm" data-page-prev><i class="ti ti-chevron-left"></i><span>Sebelumnya</span></button>
							<div class="secure-pdfjs-page-state"><span data-page-now>1</span>/<span data-page-total>...</span></div>
							<button type="button" class="btn btn-outline-primary btn-sm" data-page-next><span>Berikutnya</span><i class="ti ti-chevron-right"></i></button>
						</div>
						<div class="secure-page-image-shell">
							<div class="secure-page-loader" data-page-loader>Menyiapkan preview aman...</div>
							<img data-page-image alt="Halaman buku digital" draggable="false">
							<button type="button" class="secure-reader-tap-zone secure-reader-tap-prev" data-page-tap-prev aria-label="Halaman sebelumnya"><i class="ti ti-chevron-left"></i></button>
							<button type="button" class="secure-reader-tap-zone secure-reader-tap-next" data-page-tap-next aria-label="Halaman berikutnya"><i class="ti ti-chevron-right"></i></button>
						</div>
						<div class="secure-pdfjs-hint">Preview admin aman: PDF utuh tidak dikirim ke browser untuk aset non-downloadable.</div>
					</div>
				<?php else: ?>
					<div class="secure-reader-empty">
						<i class="ti ti-file-lock"></i>
						<h3>Renderer halaman PDF siap disambungkan.</h3>
						<p>Aset sudah melewati gerbang login dan sesi baca tercatat. Tahap berikutnya adalah endpoint render per halaman agar file asli tidak berada di URL publik.</p>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

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
			return reader.dataset.pageUrlBase + '/' + page + '?_=' + Date.now();
		}

		function animatePage(direction) {
			const className = direction === 'prev' ? 'is-turning-prev' : 'is-turning-next';
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
				loader.textContent = 'Halaman gagal dirender.';
			};
			nextImage.src = imageUrl(current);
		}

		function goPrev() { showPage(current - 1, 'prev'); }
		function goNext() { showPage(current + 1, 'next'); }

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
			if (event.key === 'ArrowLeft') goPrev();
			if (event.key === 'ArrowRight') goNext();
		});
	})();
</script>
<?php endif; ?>
