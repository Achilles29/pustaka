<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tabler_css = 'https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css';
$tabler_icons_css = 'https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/tabler-icons.min.css';
$map_json = json_encode($map_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$url_path = function ($path) {
	$segments = explode('/', str_replace('\\', '/', trim((string) $path, '/')));
	return implode('/', array_map('rawurlencode', $segments));
};
$cover_url = function ($book) use ($url_path) {
	if (! empty($book['cover_local_path'])) {
		return base_url($url_path($book['cover_local_path']));
	}
	if (! empty($book['cover_source_path'])) {
		return base_url($url_path('assets/uploads/inlislite/source_mirror/' . $book['cover_source_path']));
	}
	return base_url('assets/img/book-cover-default.webp');
};
$auth_user = (array) $this->session->userdata('auth_user');
$role_codes = array_map(function ($role) {
	return $role['code'] ?? '';
}, (array) $this->session->userdata('user_roles'));
$is_logged_in = ! empty($auth_user['id']);
$dashboard_url = (in_array('SUPERADMIN', $role_codes, true) || in_array('ADMIN', $role_codes, true)) ? base_url('admin') : base_url('user/dashboard');
$featured_book = ! empty($catalog_preview) ? $catalog_preview[0] : null;
$catalog_tiles = array_slice($catalog_preview, 1);
$cover_hues = [211, 262, 18, 151, 334, 42, 191, 292, 356];
$share_title = 'Pustaka Digital Rembang';
$share_tagline = 'Merawat Ingatan, Membuka Pengetahuan.';
$share_description = $share_tagline . ' Satu pintu untuk membaca, belajar, dan terhubung.';
$share_image = base_url('img/logo.png');
$share_url = base_url();
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= html_escape($share_title . ' — ' . $share_tagline); ?></title>
	<meta name="description" content="<?= html_escape($share_description); ?>">
	<meta name="theme-color" content="#005baa">
	<meta property="og:type" content="website">
	<meta property="og:locale" content="id_ID">
	<meta property="og:site_name" content="<?= html_escape($share_title); ?>">
	<meta property="og:title" content="<?= html_escape($share_title); ?>">
	<meta property="og:description" content="<?= html_escape($share_description); ?>">
	<meta property="og:url" content="<?= html_escape($share_url); ?>">
	<meta property="og:image" content="<?= html_escape($share_image); ?>">
	<meta property="og:image:alt" content="Logo Pustaka Digital Rembang">
	<meta name="twitter:card" content="summary">
	<meta name="twitter:title" content="<?= html_escape($share_title); ?>">
	<meta name="twitter:description" content="<?= html_escape($share_description); ?>">
	<meta name="twitter:image" content="<?= html_escape($share_image); ?>">
	<link rel="icon" href="<?= base_url('img/favicon.ico'); ?>" type="image/x-icon">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Fraunces:opsz,wght@9..144,650;9..144,750;9..144,850&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="<?= $tabler_css; ?>">
	<link rel="stylesheet" href="<?= $tabler_icons_css; ?>">
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
	<link rel="stylesheet" href="<?= base_url('assets/css/pustaka.css'); ?>">
	<link rel="stylesheet" href="<?= base_url('assets/css/pustaka-polish.css?v=20260807d'); ?>">
	<style>
		/* A self-contained editorial shelf; overrides legacy landing-card styling. */
		.public-page{background:#f7fafc}.landing-search{max-width:730px;margin:1.7rem 0 0;padding:7px;background:#fff;border:1px solid #ffffff99;border-radius:18px;box-shadow:0 18px 42px #041f4b38}.landing-search .form-control{border:0;box-shadow:none;min-height:48px;background:transparent}.landing-search .btn{border-radius:12px;padding-inline:1.15rem}.landing-tagline{margin:1rem 0 -.1rem;color:#0874ba;font-family:Fraunces,serif;font-size:clamp(1.25rem,1.8vw,1.7rem);font-weight:700;letter-spacing:-.025em}.landing-trust{display:flex;flex-wrap:wrap;gap:10px;margin-top:16px;font-size:.82rem;font-weight:700}.landing-trust span{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:999px;background:#ffffff18;border:1px solid #ffffff30}.landing-catalog-section{padding:4rem 0 4.5rem!important;background:radial-gradient(circle at 92% 4%,#d8f0ff 0,transparent 28rem),linear-gradient(180deg,#fff,#f3f8fd)!important}.landing-catalog-section .container-xl{max-width:1440px!important}.landing-catalog-intro{display:flex;align-items:end;justify-content:space-between;gap:2rem;margin-bottom:1.5rem}.landing-catalog-intro h2{max-width:15ch;margin:.3rem 0 0;font-size:clamp(2rem,3vw,3.25rem)!important;letter-spacing:-.065em}.landing-catalog-intro p{max-width:35rem;margin:0;color:#58728c;font-size:1rem;line-height:1.7}.landing-library-stage{position:relative;display:grid;grid-template-columns:minmax(300px,.9fr) minmax(0,2.1fr);gap:1rem;padding:1rem;border:1px solid #d8e8f4;border-radius:30px;background:#fff;box-shadow:0 24px 60px rgba(11,62,107,.1);overflow:hidden}.landing-library-stage:before{content:'';position:absolute;right:-8rem;bottom:-11rem;width:30rem;height:30rem;border-radius:50%;background:radial-gradient(circle,#b4e3ff 0,transparent 66%);pointer-events:none}.landing-featured-book{position:relative;display:flex!important;flex-direction:column;justify-content:end;min-height:500px;padding:2rem!important;border:0!important;border-radius:20px!important;overflow:hidden;text-decoration:none;background:linear-gradient(150deg,#04254e,#056da7)!important;box-shadow:none!important;color:#fff!important}.landing-featured-book:before{content:'';position:absolute;inset:0;background:linear-gradient(180deg,transparent 20%,#031a3de8 100%);z-index:1}.landing-featured-book img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.58}.landing-featured-book .landing-fallback{position:absolute;inset:0;border-radius:0;opacity:.82}.landing-featured-copy{position:relative;z-index:2}.landing-featured-kicker{display:inline-flex;align-items:center;gap:6px;padding:7px 10px;border-radius:999px;background:#ffffff25;border:1px solid #ffffff3d;font-size:.72rem;font-weight:800}.landing-featured-book h3{margin:1rem 0 .4rem!important;color:#fff!important;font-family:Fraunces,serif;font-size:clamp(1.55rem,2.3vw,2.3rem)!important;line-height:1.08!important}.landing-featured-book p{margin:0!important;color:#e2f3ff!important;font-size:.9rem!important}.landing-featured-open{display:inline-flex;align-items:center;gap:6px;margin-top:1.3rem;font-size:.83rem;font-weight:800}.landing-book-grid{position:relative;z-index:1;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1rem;align-content:start}.landing-book{display:block!important;min-width:0;padding:0!important;border:0!important;background:transparent!important;box-shadow:none!important;text-decoration:none}.landing-book-cover{width:100%!important;flex:0 0 auto!important;height:220px!important;border:0!important;border-radius:15px!important;background:#0d3d64;overflow:hidden;box-shadow:0 12px 22px #123f6722;transition:transform .22s ease,box-shadow .22s ease}.landing-book:hover .landing-book-cover{transform:translateY(-6px) rotate(-1deg);box-shadow:0 19px 30px #123f6733}.landing-book-cover img{width:100%!important;height:100%!important;object-fit:cover}.landing-fallback{position:relative;display:flex;height:100%;align-items:flex-end;padding:14px;background:linear-gradient(155deg,hsl(var(--book-hue) 65% 30%),hsl(calc(var(--book-hue) + 24) 72% 13%));color:#fff;isolation:isolate;overflow:hidden}.landing-fallback:before,.landing-fallback:after{content:'';position:absolute;width:9rem;height:9rem;border-radius:50%;background:#ffffff14;transform:translate(35%,-72%)}.landing-fallback:after{width:13rem;height:13rem;left:-6rem;bottom:-7rem;transform:none}.landing-fallback b{position:relative;z-index:1;display:block;max-width:7ch;font-family:Fraunces,serif;font-size:1.45rem;line-height:.98;word-break:break-word}.landing-book-copy{padding:.75rem .1rem 0}.landing-book h3{display:-webkit-box;overflow:hidden;margin:0!important;color:#0d3155!important;font-size:.89rem!important;font-weight:850!important;line-height:1.38!important;-webkit-box-orient:vertical;-webkit-line-clamp:2}.landing-book p{display:block!important;overflow:hidden;margin:.28rem 0 0!important;color:#71869b!important;font-size:.73rem!important;white-space:nowrap;text-overflow:ellipsis}.landing-book .book-format{display:inline-block;margin-top:.42rem;color:#0877be;font-size:.68rem;font-weight:850}.landing-shelf-top{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin:0 0 1rem}.landing-shelf-top strong{font-size:1.04rem;color:#10395f}.landing-shelf-top span{display:block;margin-top:.17rem;color:#71869b;font-size:.78rem}.landing-guide-grid{display:grid;grid-template-columns:1.18fr .82fr;gap:18px}.landing-journey{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}.landing-journey-card,.landing-agenda-card{border:1px solid #dbe8f3;border-radius:18px;background:#fff;padding:18px;box-shadow:0 10px 26px #123f6710}.landing-journey-card i{display:grid;place-items:center;width:38px;height:38px;border-radius:12px;background:#e9f5ff;color:#006cb7;font-size:1.3rem}.landing-journey-card h3{font-size:1rem;margin:12px 0 5px}.landing-journey-card p{font-size:.83rem;color:#607388;margin:0}.landing-agenda-card{background:linear-gradient(135deg,#07285b,#075e98);color:#fff;border:0}.landing-agenda-card .section-kicker{color:#83d4ff}.landing-agenda-list{display:grid;gap:8px;margin-top:12px}.landing-agenda-item{display:flex;gap:10px;align-items:center;color:#fff;text-decoration:none;padding:9px;border-radius:12px;background:#ffffff12}.landing-agenda-date{width:42px;text-align:center;border-right:1px solid #ffffff44;font-size:.68rem;font-weight:800}.landing-agenda-item strong{display:block;font-size:.82rem;line-height:1.35}.landing-agenda-item small{opacity:.75;font-size:.7rem}.landing-quick-links{display:flex;flex-wrap:wrap;gap:10px;margin-top:18px}.landing-quick-links a{display:inline-flex;align-items:center;gap:7px;padding:9px 12px;border:1px solid #d8e7f2;border-radius:11px;text-decoration:none;color:#124878;background:#fff;font-size:.83rem;font-weight:700}@media(max-width:1100px){.landing-library-stage{grid-template-columns:300px 1fr}.landing-book-grid{grid-template-columns:repeat(3,1fr)}.landing-book-grid .landing-book:nth-child(n+7){display:none!important}}@media(max-width:767px){.landing-search{margin-top:1rem;border-radius:14px}.landing-search .btn{width:100%;margin-top:5px}.landing-catalog-section{padding:2.5rem 0 3rem!important}.landing-catalog-intro{display:block}.landing-catalog-intro p{margin-top:.8rem}.landing-library-stage{grid-template-columns:1fr;padding:.7rem;border-radius:20px}.landing-featured-book{min-height:360px;padding:1.35rem!important;border-radius:14px!important}.landing-book-grid{grid-template-columns:repeat(2,1fr);gap:.8rem}.landing-book-grid .landing-book:nth-child(n+5){display:none!important}.landing-book-cover{height:190px!important}.landing-guide-grid,.landing-journey{grid-template-columns:1fr}.landing-trust{font-size:.72rem}}
		</style>
		<style>
			/* Final composition for the opening screen: one clear message, one primary action. */
			.public-page .public-agency-strip { display: none !important; }
			.public-page .public-nav { min-height: 80px; padding-inline: clamp(1rem, 2.6vw, 3rem); }
			.public-page .public-links { gap: clamp(.7rem, 1.1vw, 1.3rem); }
			.public-page .public-links a:not(.btn) { font-size: .82rem; font-weight: 800; }
			@media (min-width: 992px) {
				.public-page .public-hero-compact {
					display: grid !important;
					grid-template-columns: minmax(430px, .82fr) minmax(600px, 1.18fr) !important;
					align-items: center;
					column-gap: clamp(2rem, 4vw, 5rem) !important;
					min-height: 560px !important;
					padding: 6.75rem clamp(2rem, 4vw, 4.5rem) 2.6rem !important;
				}
				.public-page .public-hero-compact .public-hero-copy { grid-column: 1 !important; grid-row: 1 !important; max-width: 650px !important; }
				.public-page .public-hero-compact .public-map { grid-column: 2 !important; grid-row: 1 !important; height: 332px !important; width: min(100%, 960px) !important; margin: 0 !important; justify-self: end !important; }
				.public-page .public-hero-copy h1 { max-width: 11ch !important; font-size: clamp(3rem, 4.25vw, 4.5rem) !important; line-height: 1.02 !important; letter-spacing: -.055em !important; }
				.public-page .public-hero-compact .lead { max-width: 34rem !important; margin: 1rem 0 0 !important; font-size: 1rem !important; line-height: 1.65 !important; }
				.public-page .landing-search { max-width: 620px; margin-top: 1.25rem; }
				.public-page .public-hero-copy .btn-list { display: flex !important; width: auto !important; gap: .6rem; margin-top: .75rem; }
				.public-page .public-hero-copy .btn-list .btn { width: auto !important; min-height: 44px; padding: .62rem .95rem; font-size: .84rem; }
				.public-page .public-hero-copy .btn-list .btn:nth-child(3) { display: inline-flex !important; }
				.public-page .public-hero-copy .btn-list .btn:nth-child(n+4) { display: none !important; }
				.public-page .landing-trust { margin-top: .75rem; gap: .42rem; font-size: .72rem; }
				.public-page .landing-trust span { padding: .35rem .55rem; }
			}
			@media (max-width: 991.98px) { .public-page .public-agency-strip { display: none !important; } }
		</style>
	</head>
<body class="public-page">
	<header class="public-nav">
		<a class="public-brand" href="<?= base_url(); ?>">
			<span class="brand-logo-shell">
				<img class="brand-logo" src="<?= base_url('img/logo-small.jpeg'); ?>" alt="Logo Kabupaten Rembang">
			</span>
			<span class="public-brand-text">Pustaka Digital Rembang</span>
		</a>
		<div class="public-agency-strip" aria-label="Logo instansi">
			<img src="<?= base_url('img/perpusnas.png'); ?>" alt="Logo Perpusnas">
		</div>
		<nav class="public-links">
			<a href="<?= base_url(); ?>">Beranda</a>
			<a href="<?= base_url('katalog'); ?>">Katalog</a>
			<a href="<?= base_url('naskah-kuno'); ?>">Naskah Kuno</a>
			<a href="<?= base_url('donasi-digital'); ?>">Donasi Digital</a>
			<a href="<?= base_url('suara-pemustaka'); ?>">Suara Pemustaka</a>
			<a href="<?= base_url('agenda'); ?>">Agenda</a>
			<a href="#jejaring">Jejaring</a>
			<?php if ($is_logged_in): ?>
				<a href="<?= $dashboard_url; ?>">Dashboard</a>
				<a href="<?= base_url('logout'); ?>" class="btn btn-primary btn-sm">Logout</a>
			<?php else: ?>
				<a href="<?= base_url('membership/register'); ?>">Daftar Member</a>
				<a href="<?= base_url('membership/registration-status'); ?>" class="btn btn-outline-primary btn-sm">Cek Status</a>
				<a href="<?= base_url('login'); ?>" class="btn btn-primary btn-sm">Masuk</a>
			<?php endif; ?>
		</nav>
	</header>
	<nav class="public-mobile-nav" aria-label="Navigasi publik">
		<a href="<?= base_url(); ?>" class="active"><i class="ti ti-home"></i><span>Beranda</span></a>
		<a href="<?= base_url('katalog'); ?>"><i class="ti ti-search"></i><span>Katalog</span></a>
		<a href="<?= base_url('naskah-kuno'); ?>"><i class="ti ti-feather"></i><span>Naskah</span></a>
		<a href="<?= base_url('agenda'); ?>"><i class="ti ti-calendar-event"></i><span>Agenda</span></a>
		<?php if ($is_logged_in): ?>
			<a href="<?= $dashboard_url; ?>"><i class="ti ti-id"></i><span>Dashboard</span></a>
		<?php else: ?>
			<a href="<?= base_url('login'); ?>"><i class="ti ti-login"></i><span>Masuk</span></a>
		<?php endif; ?>
	</nav>

	<main>
		<section class="public-hero public-hero-compact">
			<div id="public-map" class="public-map"></div>
			<div class="public-hero-copy">
				<div class="hero-logo-row">
					<span class="hero-logo-card">
						<img src="<?= base_url('img/logo-small.jpeg'); ?>" alt="Logo Kabupaten Rembang">
					</span>
					<span class="hero-logo-card hero-logo-card-wide">
						<img src="<?= base_url('img/perpusnas.png'); ?>" alt="Logo Perpusnas">
					</span>
				</div>
				<p class="eyebrow">Pustaka Digital Rembang</p>
				<h1>Pustaka Digital Rembang</h1>
				<p class="landing-tagline">Merawat Ingatan, Membuka Pengetahuan.</p>
				<p class="lead">Satu pintu untuk membaca, belajar, dan terhubung—menghadirkan koleksi, warisan lokal, dan layanan perpustakaan bagi masyarakat Rembang.</p>
				<?= form_open('katalog', ['method' => 'get', 'class' => 'landing-search row g-1 align-items-center', 'role' => 'search']); ?>
					<div class="col-md"><label class="visually-hidden" for="landing-catalog-search">Cari koleksi</label><input id="landing-catalog-search" class="form-control" name="q" placeholder="Cari judul, penulis, ISBN, atau subjek…"></div>
					<div class="col-md-auto"><button class="btn btn-primary w-100" type="submit"><i class="ti ti-search me-1"></i>Telusuri koleksi</button></div>
				<?= form_close(); ?>
				<div class="landing-trust"><span><i class="ti ti-shield-check"></i>Akses layanan resmi</span><span><i class="ti ti-book-2"></i><?= number_format($public_digital_count ?? 0, 0, ',', '.'); ?> koleksi digital</span><span><i class="ti ti-heart-handshake"></i>Terbuka untuk masyarakat</span></div>
				<div class="btn-list">
					<a href="<?= base_url('katalog'); ?>" class="btn btn-primary btn-lg"><i class="ti ti-search me-1"></i>Cari Buku</a>
					<?php if ($is_logged_in): ?>
						<a href="<?= $dashboard_url; ?>" class="btn btn-outline-light btn-lg"><i class="ti ti-id me-1"></i>Buka Dashboard</a>
					<?php else: ?>
						<a href="<?= base_url('membership/register'); ?>" class="btn btn-outline-light btn-lg"><i class="ti ti-user-plus me-1"></i>Daftar Member</a>
					<?php endif; ?>
					<a href="<?= base_url('donasi-digital'); ?>" class="btn btn-outline-light btn-lg"><i class="ti ti-gift me-1"></i>Donasikan Karya</a>
					<a href="#jejaring" class="btn btn-outline-light btn-lg"><i class="ti ti-map-pin me-1"></i>Lihat Jejaring</a>
				</div>
				<div class="public-service-strip" aria-label="Layanan utama">
					<span><i class="ti ti-books"></i>Katalog terpadu</span>
					<span><i class="ti ti-id-badge-2"></i>Kartu digital</span>
					<span><i class="ti ti-map-pin-check"></i>Pojok baca</span>
				</div>
				<div class="public-hero-stats" aria-label="Ringkasan layanan">
					<div><strong><?= number_format($public_catalog_count, 0, ',', '.'); ?></strong><span>Katalog publik</span></div>
					<div><strong><?= number_format($service_counts['collections'], 0, ',', '.'); ?></strong><span>Eksemplar acuan</span></div>
					<div><strong><?= number_format(count($libraries), 0, ',', '.'); ?></strong><span>Titik layanan</span></div>
				</div>
			</div>
			<a href="#katalog" class="public-scroll-hint">Mulai jelajah</a>
		</section>

		<section class="public-section landing-catalog-section" id="katalog">
			<div class="container-xl">
				<div class="landing-catalog-intro">
					<div><div class="section-kicker">Koleksi pilihan</div><h2>Satu rak besar untuk menemukan bacaan berikutnya.</h2></div>
					<div><p>Judul di bawah dipilih acak dari katalog yang bisa diakses publik—bukan daftar yang itu-itu saja. Setiap klik membawa Anda ke detail koleksi dan pilihan aksesnya.</p><a href="<?= base_url('katalog'); ?>" class="btn btn-primary mt-3"><i class="ti ti-search me-1"></i>Telusuri seluruh katalog</a></div>
				</div>
				<div class="landing-library-stage">
					<?php if ($featured_book): ?>
						<?php $featured_has_cover = ! empty($featured_book['cover_local_path']) || ! empty($featured_book['cover_source_path']); $featured_hue = $cover_hues[(int) $featured_book['id'] % count($cover_hues)]; ?>
						<a href="<?= base_url('katalog/detail/' . (int) $featured_book['id']); ?>" class="landing-featured-book">
							<?php if ($featured_has_cover): ?><img src="<?= html_escape($cover_url($featured_book)); ?>" alt="Cover <?= html_escape($featured_book['title']); ?>" loading="lazy"><?php else: ?><span class="landing-fallback" style="--book-hue:<?= (int) $featured_hue; ?>"><b><?= html_escape(mb_substr($featured_book['title'], 0, 18)); ?></b></span><?php endif; ?>
							<div class="landing-featured-copy"><span class="landing-featured-kicker"><i class="ti ti-sparkles"></i>Pilihan acak hari ini</span><h3><?= html_escape($featured_book['title']); ?></h3><p><?= html_escape($featured_book['statement_responsibility'] ?: ($featured_book['content_category_name'] ?: 'Koleksi Pustaka Digital Rembang')); ?></p><span class="landing-featured-open">Lihat koleksi <i class="ti ti-arrow-up-right"></i></span></div>
						</a>
					<?php endif; ?>
					<div>
						<div class="landing-shelf-top"><div><strong>Jelajah tanpa pola</strong><span>Delapan judul lain untuk dijelajahi sekarang.</span></div><a href="<?= base_url('katalog'); ?>" class="btn btn-outline-primary btn-sm">Semua koleksi</a></div>
					<div class="landing-book-grid">
					<?php foreach ($catalog_tiles as $book): ?>
						<?php $has_cover = ! empty($book['cover_local_path']) || ! empty($book['cover_source_path']); $book_hue = $cover_hues[(int) $book['id'] % count($cover_hues)]; ?>
						<a href="<?= base_url('katalog/detail/' . (int) $book['id']); ?>" class="landing-book">
							<div class="landing-book-cover">
								<?php if ($has_cover): ?><img src="<?= html_escape($cover_url($book)); ?>" alt="Cover <?= html_escape($book['title']); ?>" loading="lazy"><?php else: ?><span class="landing-fallback" style="--book-hue:<?= (int) $book_hue; ?>"><b><?= html_escape(mb_substr($book['title'], 0, 15)); ?></b></span><?php endif; ?>
							</div>
							<div class="landing-book-copy">
								<h3><?= html_escape($book['title']); ?></h3>
								<p><?= html_escape($book['statement_responsibility'] ?: 'Penulis belum tercatat'); ?></p>
								<span class="book-format"><?= ! empty($book['digital_asset_count']) ? 'Baca digital' : (! empty($book['available_count']) ? 'Tersedia dipinjam' : 'Lihat detail'); ?></span>
							</div>
						</a>
					<?php endforeach; ?>
					</div>
					</div>
				</div>
			</div>
		</section>

		<section class="public-section public-section-muted" id="layanan">
			<div class="container-xl"><div class="landing-guide-grid"><div><div class="section-kicker">Mulai dari mana?</div><h2>Layanan perpustakaan, dibuat lebih dekat dengan keseharian.</h2><div class="landing-journey mt-3"><a class="landing-journey-card text-decoration-none" href="<?= base_url('membership/register'); ?>"><i class="ti ti-id-badge-2"></i><h3>Jadi member</h3><p>Daftar mandiri, pantau verifikasi, dan simpan kartu digital.</p></a><a class="landing-journey-card text-decoration-none" href="<?= base_url('katalog'); ?>"><i class="ti ti-books"></i><h3>Temukan koleksi</h3><p>Telusuri buku, media digital, kategori, dan klasifikasi.</p></a><a class="landing-journey-card text-decoration-none" href="<?= base_url('user/reading-checkin'); ?>"><i class="ti ti-map-pin-check"></i><h3>Baca di pojok baca</h3><p>Check-in di lokasi layanan untuk akses baca digital yang lebih leluasa.</p></a></div><div class="landing-quick-links"><a href="<?= base_url('buku-pelajaran'); ?>"><i class="ti ti-school"></i>Buku pelajaran</a><a href="<?= base_url('naskah-kuno'); ?>"><i class="ti ti-feather"></i>Naskah kuno</a><a href="<?= base_url('donasi-digital'); ?>"><i class="ti ti-gift"></i>Donasikan karya</a><a href="<?= base_url('suara-pemustaka'); ?>"><i class="ti ti-message-heart"></i>Suara pemustaka</a><a href="<?= base_url('belajar'); ?>"><i class="ti ti-school"></i>Arena belajar</a></div></div><aside class="landing-agenda-card"><div class="section-kicker">Agenda terdekat</div><h3 class="mb-0">Ruang bertemu, belajar, dan berbagi.</h3><div class="landing-agenda-list"><?php if (empty($upcoming_events)): ?><div class="landing-agenda-item"><i class="ti ti-calendar-off"></i><div><strong>Agenda berikutnya sedang disiapkan</strong><small>Ikuti halaman agenda untuk informasi terbaru.</small></div></div><?php endif; ?><?php foreach ($upcoming_events as $event): ?><a class="landing-agenda-item" href="<?= base_url('agenda/detail/' . (int) $event['id']); ?>"><span class="landing-agenda-date"><?= ! empty($event['starts_at']) ? date('d M', strtotime($event['starts_at'])) : 'Segera'; ?></span><div><strong><?= html_escape($event['title']); ?></strong><small><?= html_escape($event['location_name'] ?: ($event['library_name'] ?: 'Rembang')); ?></small></div></a><?php endforeach; ?></div><a href="<?= base_url('agenda'); ?>" class="btn btn-light btn-sm mt-3">Lihat agenda literasi <i class="ti ti-arrow-right ms-1"></i></a></aside></div></div>
		</section>

		<section class="public-section public-section-muted" id="naskah-kuno">
			<div class="container-xl">
				<div class="row g-4 align-items-center">
					<div class="col-lg-5">
						<div class="section-kicker">Pelestarian warisan</div>
						<h2>Naskah kuno: ingatan lokal yang dirawat, diteliti, dan didigitalisasi.</h2>
						<p class="text-secondary mt-3">Setiap rekam menyatukan identitas fisik, asal-usul, kondisi konservasi, riwayat koleksi, dan preview hasil digitalisasi yang dapat diakses sesuai kebijakan.</p>
						<a href="<?= base_url('naskah-kuno'); ?>" class="btn btn-primary mt-2"><i class="ti ti-feather me-1"></i>Jelajahi Naskah Kuno</a>
					</div>
					<div class="col-lg-7">
						<div class="row g-3">
							<?php if (empty($manuscript_preview)): ?>
								<div class="col-12"><div class="public-network-item"><i class="ti ti-feather"></i><h3>Koleksi sedang dipersiapkan</h3><p>Tim pelestarian dapat mulai mendata naskah, citra sampul, dan salinan digital dari panel admin.</p></div></div>
							<?php endif; ?>
							<?php foreach ($manuscript_preview as $manuscript): ?>
								<div class="col-md-4"><a href="<?= base_url('naskah-kuno/detail/' . (int) $manuscript['id']); ?>" class="public-network-item d-block text-decoration-none h-100"><i class="ti ti-feather"></i><h3><?= html_escape($manuscript['title']); ?></h3><p><?= html_escape($manuscript['inventory_number']); ?><br><?= html_escape($manuscript['language'] ?: 'Koleksi naskah'); ?></p></a></div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</div>
		</section>

		<section class="public-section public-section-muted" id="jejaring">
			<div class="container-xl">
				<div class="row g-4 align-items-center">
					<div class="col-lg-5">
						<div class="section-kicker">Jejaring Rembang</div>
						<h2>Perpustakaan sekolah, desa, komunitas, dan swasta berada dalam satu peta layanan.</h2>
						<p class="text-secondary mt-3">Setiap titik dapat membawa profil, koordinat, radius layanan, galeri foto, dan kelak kuota akses pojok baca digital berbasis lokasi.</p>
					</div>
					<div class="col-lg-7">
						<div class="public-network-grid">
							<div class="public-network-item">
								<i class="ti ti-map-2"></i>
								<h3>GIS Perpustakaan</h3>
								<p>Profil lokasi, radius layanan, dan galeri foto untuk seluruh perpustakaan terdaftar.</p>
							</div>
							<div class="public-network-item">
								<i class="ti ti-id-badge-2"></i>
								<h3>Membership Digital</h3>
								<p>Kartu anggota digital, QR verifikasi, status masa berlaku, dan riwayat layanan.</p>
							</div>
							<div class="public-network-item">
								<i class="ti ti-device-tablet-search"></i>
								<h3>Pojok Baca Digital</h3>
								<p>Akses koleksi berbasis titik GPS, token, kuota, dan absensi ulang di perpustakaan.</p>
							</div>
							<div class="public-network-item">
								<i class="ti ti-calendar-event"></i>
								<h3>Agenda Literasi</h3>
								<p>Ruang publikasi event perpustakaan daerah dan jejaring mitra literasi.</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</main>

	<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
	<script>
	(function () {
		var points = <?= $map_json ?: '[]'; ?>;
		var map = L.map('public-map', {
			zoomControl: false,
			attributionControl: false,
			scrollWheelZoom: false,
			dragging: false,
			doubleClickZoom: false
		}).setView([-6.7750, 111.3900], 11);

		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18 }).addTo(map);

		points.forEach(function (point) {
			L.circleMarker([point.lat, point.lng], {
				radius: 8,
				color: '#ffffff',
				weight: 2,
				fillColor: point.color || '#0b6b86',
				fillOpacity: 1
			}).addTo(map);
		});
	})();
	</script>
</body>
</html>
