<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tabler_css = 'https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css';
$tabler_icons_css = 'https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/tabler-icons.min.css';
$dashboard_url = $is_admin ? base_url('admin') : base_url('user/dashboard');
$steps = function (array $items) {
	foreach ($items as $item): ?>
		<li><span><?= (int) $item[0]; ?></span><div><?= $item[1]; ?></div></li>
	<?php endforeach;
};
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
	<link rel="stylesheet" href="<?= base_url('assets/css/pustaka-polish.css?v=20260812g'); ?>">
</head>
<body class="public-page guide-page">
	<header class="public-nav">
		<a class="public-brand" href="<?= base_url(); ?>">
			<span class="brand-logo-shell"><img class="brand-logo" src="<?= base_url('img/logo-small.jpeg'); ?>" alt="Logo Kabupaten Rembang"></span>
			<span class="public-brand-text">Pustaka Digital Rembang</span>
		</a>
		<nav class="public-links">
			<a href="<?= base_url(); ?>">Beranda</a>
			<a href="<?= base_url('katalog'); ?>">Katalog</a>
			<a href="<?= base_url('agenda'); ?>">Agenda</a>
			<a href="#daftar-isi" class="active">Panduan</a>
			<?php if ($is_logged_in): ?>
				<a href="<?= $dashboard_url; ?>" class="btn btn-primary btn-sm"><i class="ti ti-layout-dashboard me-1"></i>Dashboard</a>
			<?php else: ?>
				<a href="<?= base_url('login'); ?>" class="btn btn-primary btn-sm"><i class="ti ti-login me-1"></i>Masuk</a>
			<?php endif; ?>
		</nav>
	</header>
	<nav class="public-mobile-nav" aria-label="Navigasi publik">
		<a href="<?= base_url(); ?>"><i class="ti ti-home"></i><span>Beranda</span></a>
		<a href="<?= base_url('katalog'); ?>"><i class="ti ti-search"></i><span>Katalog</span></a>
		<a href="<?= base_url('panduan'); ?>" class="active"><i class="ti ti-book-2"></i><span>Panduan</span></a>
		<a href="<?= $is_logged_in ? $dashboard_url : base_url('login'); ?>"><i class="ti <?= $is_logged_in ? 'ti-layout-dashboard' : 'ti-login'; ?>"></i><span><?= $is_logged_in ? 'Dashboard' : 'Masuk'; ?></span></a>
	</nav>

	<main class="guide-main">
		<section class="guide-hero">
			<div class="container-xl guide-hero-inner">
				<div>
					<div class="section-kicker">Pusat bantuan</div>
					<h1>Panduan Pustaka Digital Rembang</h1>
					<p>Satu tempat untuk memahami cara mencari koleksi, menjadi anggota, membaca buku digital, meminjam buku fisik, mengikuti Arena Belajar, hingga mengelola layanan sebagai petugas.</p>
					<div class="guide-hero-actions">
						<a href="#untuk-member" class="btn btn-light"><i class="ti ti-id-badge-2 me-1"></i>Panduan Member</a>
						<a href="#untuk-petugas" class="btn btn-outline-light"><i class="ti ti-settings me-1"></i>Panduan Petugas</a>
					</div>
				</div>
				<div class="guide-hero-symbol" aria-hidden="true"><i class="ti ti-books"></i><span>?</span></div>
			</div>
		</section>

		<section class="guide-shell container-xl" id="daftar-isi">
			<aside class="guide-toc">
				<strong>Daftar isi</strong>
				<a href="#mulai"><i class="ti ti-rocket"></i>Mulai menggunakan</a>
				<a href="#untuk-member"><i class="ti ti-id-badge-2"></i>Member &amp; akun</a>
				<a href="#katalog-reader"><i class="ti ti-book-open"></i>Katalog &amp; reader</a>
				<a href="#peminjaman"><i class="ti ti-books"></i>Peminjaman buku</a>
				<a href="#pojok-baca"><i class="ti ti-map-pin-check"></i>Pojok Baca &amp; token</a>
				<a href="#belajar"><i class="ti ti-school"></i>Arena Belajar</a>
				<a href="#untuk-petugas"><i class="ti ti-tool"></i>Petugas &amp; admin</a>
				<a href="#troubleshooting"><i class="ti ti-lifebuoy"></i>Bantuan cepat</a>
			</aside>

			<div class="guide-content">
				<section class="guide-section" id="mulai">
					<div class="guide-section-heading"><span class="guide-section-icon"><i class="ti ti-rocket"></i></span><div><div class="section-kicker">01 · Dasar</div><h2>Mulai menggunakan aplikasi</h2></div></div>
					<p>Pustaka Digital Rembang menyatukan katalog perpustakaan, koleksi digital, layanan keanggotaan, jejaring GIS, kegiatan literasi, peminjaman buku fisik, dan Arena Belajar.</p>
					<div class="guide-callout"><i class="ti ti-info-circle"></i><div><strong>Dua area layanan.</strong><span>Halaman publik dapat dibuka siapa saja. Fitur pribadi membutuhkan akun member aktif; fitur operasional hanya dapat diakses admin sesuai role.</span></div></div>
					<div class="guide-grid three">
						<article><i class="ti ti-search"></i><h3>1. Jelajahi</h3><p>Buka Katalog untuk mencari judul, penulis, ISBN, kategori, klasifikasi, media, lokasi, atau tahun.</p></article>
						<article><i class="ti ti-user-plus"></i><h3>2. Daftar</h3><p>Daftar sebagai member jika ingin membaca aset terlindungi, meminta pinjam, dan memakai layanan pribadi.</p></article>
						<article><i class="ti ti-layout-dashboard"></i><h3>3. Kelola</h3><p>Masuk ke Dashboard untuk kartu anggota, status pinjam, token luar zona, dan aktivitas belajar.</p></article>
					</div>
				</section>

				<section class="guide-section" id="untuk-member">
					<div class="guide-section-heading"><span class="guide-section-icon"><i class="ti ti-id-badge-2"></i></span><div><div class="section-kicker">02 · Member</div><h2>Pendaftaran, akun, dan kartu anggota</h2></div></div>
					<div class="guide-split">
						<div><h3>Daftar menjadi member</h3><ol class="guide-steps"><?php $steps([
							[1, 'Buka <a href="' . base_url('membership/register') . '">Daftar Member</a>, lalu pilih warga Rembang atau luar Rembang.'],
							[2, 'Isi identitas sesuai KTP atau KK. KTP dan KK dipilih salah satu; ukuran berkas maksimal 2 MB.'],
							[3, 'Warga Rembang memilih kecamatan serta desa/kelurahan dari master wilayah. Pendaftar luar Rembang mengisi alamat KTP dan domisili.'],
							[4, 'Kirim pendaftaran. Sistem menolak NIK yang sudah terdaftar; permohonan kemudian menunggu verifikasi petugas.'],
						]); ?></ol></div>
						<div><h3>Setelah mendaftar</h3><ol class="guide-steps"><?php $steps([
							[1, 'Gunakan <a href="' . base_url('membership/registration-status') . '">Cek Status Pendaftaran</a> dengan NIK, nomor HP, atau token pendaftaran.'],
							[2, 'Saat disetujui, gunakan username dan password yang diinformasikan petugas/kanal layanan resmi.'],
							[3, 'Masuk melalui halaman Login. Gunakan ikon mata untuk memeriksa password sebelum dikirim.'],
							[4, 'Buka Dashboard lalu pilih Kartu Anggota untuk melihat, mencetak, atau memverifikasi kartu digital.'],
						]); ?></ol></div>
					</div>
				</section>

				<section class="guide-section" id="katalog-reader">
					<div class="guide-section-heading"><span class="guide-section-icon"><i class="ti ti-book-open"></i></span><div><div class="section-kicker">03 · Koleksi</div><h2>Mencari buku dan membaca digital</h2></div></div>
					<div class="guide-grid two">
						<article><h3><i class="ti ti-search"></i>Mencari di katalog</h3><ul><li>Masukkan judul, penulis, ISBN, atau barcode pada kolom Cari.</li><li>Persempit hasil dengan kategori isi, klasifikasi, sumber, media, lokasi perpustakaan, tahun, dan ketersediaan.</li><li>Jenis koleksi menjelaskan bentuk bahan: buku, ebook, CD, atau jenis lain. Semua tetap masuk satu pintu katalog.</li><li>Buka Detail untuk melihat metadata, ketersediaan eksemplar, dan opsi Baca Online atau Request Buku.</li></ul></article>
						<article><h3><i class="ti ti-file-text"></i>Membaca ebook/PDF</h3><ul><li>Login sebagai member aktif lalu klik Baca Online pada koleksi digital.</li><li>Reader aman merender halaman; file tidak terbuka dari folder publik.</li><li>Kebijakan aset menentukan apakah PDF hanya dirender atau unduhan diizinkan. Pengaturan ini terpisah dari token lokasi.</li><li>Jika GPS tersedia, reader otomatis menentukan apakah Anda berada di zona bebas atau luar zona.</li></ul></article>
					</div>
					<div class="guide-callout warning"><i class="ti ti-shield-lock"></i><div><strong>Catatan hak cipta.</strong><span>Jangan membagikan akun atau berupaya menyalin koleksi yang kebijakannya hanya Baca Online. Aktivitas reader dicatat untuk keamanan layanan.</span></div></div>
				</section>

				<section class="guide-section" id="peminjaman">
					<div class="guide-section-heading"><span class="guide-section-icon"><i class="ti ti-books"></i></span><div><div class="section-kicker">04 · Sirkulasi</div><h2>Request dan peminjaman buku fisik</h2></div></div>
					<div class="guide-split">
						<div><h3>Untuk member</h3><ol class="guide-steps"><?php $steps([[1, 'Buka Detail katalog dan pastikan eksemplar masih tersedia.'], [2, 'Klik Request Buku lalu kirim permintaan. Buku yang sedang dipinjam atau telah direservasi tidak dapat diminta lagi.'], [3, 'Pantau status pada kartu Request Buku dan Riwayat Pinjam di Dashboard.'], [4, 'Ambil buku sesuai instruksi petugas dan kembalikan sebelum jatuh tempo.']]); ?></ol></div>
						<div><h3>Arti status</h3><div class="guide-status-list"><div><b class="badge bg-yellow-lt">Menunggu</b><span>Permintaan belum diperiksa.</span></div><div><b class="badge bg-blue-lt">Disiapkan</b><span>Petugas menyiapkan eksemplar.</span></div><div><b class="badge bg-green-lt">Dipinjam</b><span>Transaksi aktif dan memiliki jatuh tempo.</span></div><div><b class="badge bg-secondary-lt">Dikembalikan</b><span>Transaksi selesai dan buku tersedia kembali.</span></div></div></div>
					</div>
					<p class="guide-note">Lama pinjam, batas perpanjangan, dan jatuh tempo ditentukan pada Pengaturan Peminjaman oleh petugas. Petugas dapat mengubah tanggal jatuh tempo saat menyetujui permintaan bila ada alasan layanan yang sah.</p>
				</section>

				<section class="guide-section" id="pojok-baca">
					<div class="guide-section-heading"><span class="guide-section-icon"><i class="ti ti-map-pin-check"></i></span><div><div class="section-kicker">05 · Akses digital</div><h2>Pojok Baca, GIS, dan token luar zona</h2></div></div>
					<div class="guide-policy-table"><div class="guide-policy-head"><span>Lokasi membaca</span><span>Akses</span><span>Token</span></div><div><span>Di Pojok Baca Digital aktif</span><span>Bebas baca online</span><b class="free">Tidak diperlukan</b></div><div><span>Di perpustakaan terdaftar GIS</span><span>Bebas baca online</span><b class="free">Tidak diperlukan</b></div><div><span>Di luar zona</span><span>Baca online bila token tersedia</span><b class="charged">1 token per sesi buku</b></div></div>
					<div class="guide-grid three compact"><article><i class="ti ti-current-location"></i><h3>Check-in GPS</h3><p>Buka Pojok Baca, aktifkan GPS, lalu check-in untuk memverifikasi lokasi gratis. Check-in tidak menerbitkan token.</p></article><article><i class="ti ti-ticket"></i><h3>Token luar zona</h3><p>Ajukan dari halaman Pojok Baca bila tidak dapat datang. Petugas memeriksa permohonan sebelum menerbitkan token.</p></article><article><i class="ti ti-school"></i><h3>Jalur token lain</h3><p>Token juga dapat diperoleh melalui check-in Buku Tamu Perpustakaan Daerah dan hadiah Arena Belajar.</p></article></div>
				</section>

				<section class="guide-section" id="belajar">
					<div class="guide-section-heading"><span class="guide-section-icon"><i class="ti ti-school"></i></span><div><div class="section-kicker">06 · Literasi interaktif</div><h2>Arena Belajar dan latihan soal</h2></div></div>
					<div class="guide-grid two"><article><h3><i class="ti ti-target-arrow"></i>Untuk peserta</h3><ol class="guide-steps"><?php $steps([[1, 'Buka <a href="' . base_url('belajar') . '">Arena Belajar</a> lalu pilih Latihan.'], [2, 'Gunakan filter jenjang, kelas, mapel, kesulitan, dan status untuk menemukan latihan yang sesuai.'], [3, 'Kerjakan sesi. Urutan soal dan pilihan dapat diacak sesuai pengaturan sesi.'], [4, 'Nilai dapat tampil langsung dan pembahasan tersedia jika diizinkan penyusun sesi.']]); ?></ol></article><article><h3><i class="ti ti-award"></i>Poin &amp; penghargaan</h3><ul><li>Aktivitas belajar dapat menghasilkan poin dan lencana.</li><li>Poin tertentu dapat ditukar dengan hadiah yang aktif, termasuk token baca jika tersedia.</li><li>Riwayat aktivitas, poin, dan hasil latihan dapat dipantau dari Dashboard.</li><li>Gunakan latihan sesuai jenjang; rekomendasi didasarkan pada data usia member bila tersedia.</li></ul></article></div>
				</section>

				<section class="guide-section" id="untuk-petugas">
					<div class="guide-section-heading"><span class="guide-section-icon"><i class="ti ti-tool"></i></span><div><div class="section-kicker">07 · Operasional</div><h2>Panduan singkat petugas dan admin</h2></div></div>
					<p>Menu admin mengikuti role dan hak akses. Jika suatu menu tidak terlihat, minta Superadmin memeriksa role, scope perpustakaan, dan izin halaman Anda.</p>
					<div class="guide-admin-grid">
						<article><i class="ti ti-books"></i><h3>Katalog &amp; aset</h3><p>Kelola bibliografi pada Katalog. Isi jenis koleksi, kategori isi, klasifikasi, dan eksemplar. Ebook/PDF adalah aset yang ditautkan ke katalog, bukan katalog terpisah.</p><small>Menu: Katalog, Master Katalog, Reader PDF Aman.</small></article>
						<article><i class="ti ti-users"></i><h3>Member &amp; layanan</h3><p>Verifikasi pendaftaran, pembaruan membership, data member, Buku Tamu, request buku, transaksi pinjam, pengembalian, dan jatuh tempo.</p><small>Menu: Membership, Layanan Harian, Request Buku, Peminjaman.</small></article>
						<article><i class="ti ti-map-2"></i><h3>Jejaring &amp; Pojok Baca</h3><p>Kelola perpustakaan, titik GIS, radius layanan, Pojok Baca, dan permohonan token. Pastikan koordinat serta status aktif benar sebelum dipublikasikan.</p><small>Menu: Perpustakaan, GIS, Pojok Baca, Token.</small></article>
						<article><i class="ti ti-school"></i><h3>Arena Belajar</h3><p>Atur jenjang/mapel, buat atau impor Bank Soal, lalu susun Sesi Latihan. Gunakan sumber semua bank, pilihan tertentu, atau soal khusus sesi sesuai kebutuhan.</p><small>Menu: Konfigurasi Belajar, Bank Soal, Sesi Latihan.</small></article>
						<article><i class="ti ti-shield-lock"></i><h3>Hak akses</h3><p>Kelola role, akun admin, scope perpustakaan, dan status admin pada RBAC. Jangan memberikan hak lebih dari kebutuhan kerja.</p><small>Menu: Pengaturan Akses / RBAC.</small></article>
						<article><i class="ti ti-chart-dots-3"></i><h3>Monitoring &amp; audit</h3><p>Periksa kotak masuk, riwayat kunjungan, akses reader, transaksi, dan audit aksi penting. Gunakan data ini untuk tindak lanjut layanan.</p><small>Menu: Kotak Masuk, Laporan, Monitor Akses, Audit.</small></article>
					</div>
					<div class="guide-callout"><i class="ti ti-alert-triangle"></i><div><strong>Sinkronisasi adalah modul terbatas.</strong><span>Jalankan sinkronisasi hanya oleh petugas yang ditunjuk dan sesuai SOP. Perubahan hak akses Admin reguler tidak mencakup modul sinkronisasi.</span></div></div>
				</section>

				<section class="guide-section" id="troubleshooting">
					<div class="guide-section-heading"><span class="guide-section-icon"><i class="ti ti-lifebuoy"></i></span><div><div class="section-kicker">08 · Bantuan</div><h2>Masalah umum dan solusi cepat</h2></div></div>
					<div class="guide-faq">
						<details open><summary>Saya tidak bisa masuk.</summary><p>Periksa username/NIK/email dan password. Gunakan ikon mata untuk memeriksa penulisan. Jika pendaftaran masih menunggu, cek status pendaftaran terlebih dahulu; bila akun aktif tetapi password lupa, hubungi petugas layanan.</p></details>
						<details><summary>Buku digital tidak dapat dibuka.</summary><p>Pastikan status member aktif, izinkan GPS jika berada di lokasi layanan, dan cek token bila Anda berada di luar zona. Aset Internal atau PDF dengan policy khusus memang tidak dapat dibuka untuk semua member.</p></details>
						<details><summary>Token tidak berkurang di Pojok Baca, apakah normal?</summary><p>Normal. Pojok Baca dan perpustakaan GIS adalah zona bebas: buku terbuka tanpa token dan tanpa kuota sesi.</p></details>
						<details><summary>Request buku belum menjadi pinjaman.</summary><p>Request harus diperiksa petugas. Status Dipinjam baru muncul setelah eksemplar diserahkan dan transaksi peminjaman diterbitkan.</p></details>
						<details><summary>Menu admin saya tidak lengkap.</summary><p>Akses halaman ditentukan oleh role, status akun, dan scope perpustakaan. Hubungi Superadmin untuk penyesuaian, bukan memakai akun orang lain.</p></details>
					</div>
				</section>
			</div>
		</section>
	</main>
	<footer class="guide-footer"><div class="container-xl"><span>© <?= date('Y'); ?> Pustaka Digital Rembang</span><a href="#daftar-isi">Kembali ke daftar isi <i class="ti ti-arrow-up"></i></a></div></footer>
</body>
</html>
