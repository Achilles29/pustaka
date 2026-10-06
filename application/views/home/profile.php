<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!doctype html><html lang="id"><head>
 <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
 <title><?=html_escape($title);?> — Pustaka Digital Rembang</title>
 <meta name="description" content="Kenali Pustaka Digital Rembang: ruang membaca, layanan pengetahuan, warisan lokal, dan jam layanan perpustakaan.">
 <link rel="icon" href="<?=base_url('img/favicon.ico');?>">
 <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fraunces:opsz,wght@9..144,650;9..144,750&display=swap" rel="stylesheet">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/tabler-icons.min.css">
 <link rel="stylesheet" href="<?=base_url('assets/css/pustaka-community.css?v=20260929b');?>">
</head><body class="pustaka-profile">
<a class="profile-skip" href="#profile-main">Lewati ke isi</a>
<header class="profile-nav"><a class="profile-brand" href="<?=base_url();?>"><img src="<?=base_url('img/logo-small.jpeg');?>" alt="Logo Kabupaten Rembang"><span>Pustaka Digital<br><strong>Rembang</strong></span></a><nav aria-label="Navigasi utama"><a href="<?=base_url();?>">Beranda</a><a href="<?=base_url('profil-perpustakaan');?>" aria-current="page">Profil</a><a href="<?=base_url('katalog');?>">Katalog</a><a href="<?=base_url('suara-pemustaka');?>">Suara Pemustaka</a></nav><a class="profile-button" href="<?=base_url('membership/register');?>">Menjadi anggota <span aria-hidden="true">↗</span></a></header>
<main id="profile-main">
 <section class="profile-hero"><div class="profile-hero-copy"><p class="pustaka-eyebrow">MENGENAL PERPUSTAKAAN KITA</p><h1>Ruang untuk membaca.<br><em>Ruang untuk tumbuh.</em></h1><p><?=html_escape($profile_intro);?></p><div class="profile-actions"><a class="profile-button" href="#kunjungi">Rencanakan kunjungan <span aria-hidden="true">↓</span></a><a class="profile-text-link" href="<?=base_url('katalog');?>">Jelajahi koleksi ↗</a></div></div>
 <div class="profile-art" aria-hidden="true"><span class="profile-art-stamp">REMBANG<br>RUANG PENGETAHUAN</span><div class="profile-book book-one">CERITA<br><small>dari tanah kita</small></div><div class="profile-book book-two">ILMU<br><small>untuk hari esok</small></div><div class="profile-book book-three">IMAJINASI<br><small>tanpa batas</small></div><div class="profile-art-caption">Satu buku. Banyak kemungkinan.</div></div>
 </section>
 <section class="profile-section"><div class="profile-section-title"><span class="pustaka-eyebrow">DEKAT DENGAN KEHIDUPAN</span><h2>Lebih dari rak dan buku.</h2><p>Temukan jalan Anda menuju pengetahuan, dari koleksi cetak hingga cerita lokal yang berharga.</p></div><div class="profile-services">
 <?php foreach ([['01','books','Baca & jelajahi','Telusuri katalog dan temukan bacaan yang sesuai dengan minat Anda.','katalog','Jelajahi katalog'],['02','feather','Rawat ingatan lokal','Kenali naskah kuno dan warisan pengetahuan yang terhubung dengan Rembang.','naskah-kuno','Kenali naskah'],['03','users','Terhubung & berpartisipasi','Ikuti agenda perpustakaan dan sampaikan ide untuk layanan yang lebih baik.','agenda','Lihat agenda']] as $card): ?>
 <article class="profile-service"><div class="profile-service-top"><i class="ti ti-<?=$card[1];?>" aria-hidden="true"></i><span><?=$card[0];?></span></div><h3><?=$card[2];?></h3><p><?=$card[3];?></p><a href="<?=base_url($card[4]);?>"><?=$card[5];?> ↗</a></article>
 <?php endforeach; ?>
 </div></section>
 <section class="profile-story profile-section"><div><span class="pustaka-eyebrow">PROFIL KELEMBAGAAN</span><h2>Merawat ingatan,<br>membuka pengetahuan.</h2></div><div><p>Pustaka Digital Rembang menjadi pintu masuk untuk menemukan koleksi, menjelajahi jejaring perpustakaan, dan berpartisipasi dalam layanan literasi.</p><div class="profile-template-note"><strong>Sejarah & arah perpustakaan</strong><p><?=html_escape($profile_history);?></p></div></div></section>
 <section id="kunjungi" class="profile-section profile-visit"><div><span class="pustaka-eyebrow">KAMI MENANTIKAN KUNJUNGAN ANDA</span><h2>Luangkan waktu.<br>Temukan hal baru.</h2><p>Datang untuk membaca, mencari referensi, atau menemukan buku favorit berikutnya.</p><a class="profile-text-link" href="<?=base_url('#public-map');?>">Lihat peta perpustakaan ↗</a></div><?php $this->load->view('partials/service_hours'); ?></section>
 <div class="profile-section"><?php $this->load->view('partials/public_contacts'); ?></div>
</main><footer class="profile-footer"><span>© <?=date('Y');?> Pustaka Digital Rembang</span><nav aria-label="Tautan pengetahuan"><a href="https://data.rembangkab.go.id/" target="_blank" rel="noopener noreferrer">Data Rembang ↗</a><a href="https://onesearch.id/" target="_blank" rel="noopener noreferrer">Indonesia OneSearch ↗</a></nav></footer>
</body></html>
