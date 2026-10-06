<?php defined('BASEPATH') OR exit('No direct script access allowed');
$footer_hours = (array) $this->config->item('service_hours', 'library_public');
$footer_contacts = isset($contact_links) ? (array) $contact_links : [];
?>
<footer class="pustaka-site-footer" aria-label="Informasi dan layanan Pustaka Digital Rembang">
 <div class="pustaka-footer-inner">
  <section class="pustaka-footer-invite" aria-labelledby="footer-invite-title">
   <div class="pustaka-footer-invite-copy"><span class="pustaka-footer-eyebrow">SELALU ADA CERITA BERIKUTNYA</span><h2 id="footer-invite-title">Satu halaman lagi.<br><em>Satu dunia baru.</em></h2><p>Temukan bacaan yang menginspirasi, dan ruang untuk terus bertumbuh.</p></div>
   <div class="pustaka-footer-invite-actions"><a class="pustaka-footer-button" href="<?=base_url('katalog');?>"><i class="ti ti-book" aria-hidden="true"></i>Temukan bacaan Anda<i class="ti ti-arrow-up-right" aria-hidden="true"></i></a><a class="pustaka-footer-secondary" href="<?=html_escape($is_logged_in ? $dashboard_url : base_url('membership/register'));?>"><?=$is_logged_in?'Kembali ke dashboard':'Belum jadi anggota? Daftar di sini';?><span aria-hidden="true">↗</span></a></div>
   <div class="pustaka-footer-books" aria-hidden="true"><span>BACA</span><span>BELAJAR</span><span>BERTUMBUH</span></div>
  </section>

  <div class="pustaka-footer-main">
   <div class="pustaka-footer-about">
    <a class="pustaka-footer-brand" href="<?=base_url();?>"><span class="pustaka-footer-logo"><img src="<?=base_url('img/logo-small.jpeg');?>" alt="Lambang Kabupaten Rembang" width="42" height="48" loading="lazy"></span><span>Pustaka Digital<strong>Rembang</strong></span></a>
    <p>Merawat ingatan, membuka pengetahuan. Menghubungkan masyarakat Rembang dengan bacaan, warisan lokal, dan kesempatan belajar.</p>
    <a class="pustaka-footer-map-link" href="#public-map"><i class="ti ti-map-pin" aria-hidden="true"></i>Temukan titik layanan<span aria-hidden="true">↗</span></a>
    <span class="pustaka-footer-tagline">DARI REMBANG, UNTUK PENGETAHUAN.</span>
   </div>

   <nav class="pustaka-footer-column" aria-labelledby="footer-explore-title"><h3 id="footer-explore-title">Jelajahi pustaka</h3><ul>
    <li><a href="<?=base_url('profil-perpustakaan');?>">Profil perpustakaan</a></li><li><a href="<?=base_url('katalog');?>">Katalog koleksi</a></li><li><a href="<?=base_url('buku-pelajaran');?>">Buku pelajaran</a></li><li><a href="<?=base_url('naskah-kuno');?>">Naskah kuno</a></li><li><a href="<?=base_url('agenda');?>">Agenda literasi</a></li>
   </ul></nav>

   <nav class="pustaka-footer-column" aria-labelledby="footer-services-title"><h3 id="footer-services-title">Layanan pemustaka</h3><ul>
    <li><a href="<?=base_url('membership/register');?>">Daftar keanggotaan</a></li><li><a href="<?=base_url('membership/registration-status');?>">Cek status pendaftaran</a></li><li><a href="<?=base_url('donasi-digital');?>">Donasikan karya</a></li><li><a href="<?=base_url('suara-pemustaka');?>">Saran & suara pemustaka</a></li><li><a href="<?=base_url('belajar');?>">Arena belajar</a></li>
   </ul></nav>

   <section class="pustaka-footer-column pustaka-footer-hours" aria-labelledby="footer-hours-title"><h3 id="footer-hours-title">Waktu untuk berkunjung</h3><dl>
    <?php foreach ($footer_hours as $slot): ?><div><dt><?=html_escape($slot['label']);?></dt><dd><?=html_escape(str_replace(':','.',$slot['open']).' – '.str_replace(':','.',$slot['close']));?></dd></div><?php endforeach; ?>
   </dl><p><i class="ti ti-clock" aria-hidden="true"></i>Waktu Indonesia Barat (WIB)</p><small>Jadwal reguler. Hari libur khusus mengikuti pengumuman perpustakaan.</small><a class="pustaka-footer-hours-link" href="<?=base_url('agenda');?>">Lihat agenda kunjungan <span aria-hidden="true">↗</span></a></section>
  </div>

  <?php if ($footer_contacts): ?>
  <section class="pustaka-footer-connect" aria-label="Kontak dan media sosial perpustakaan"><div><span class="pustaka-footer-eyebrow">TETAP TERHUBUNG</span><h3>Ada cerita. Ada kabar. Ada kami.</h3></div><div class="pustaka-footer-socials">
   <?php foreach ($footer_contacts as $contact): ?><a href="<?=html_escape($contact['url']);?>" target="_blank" rel="noopener noreferrer" aria-label="<?=html_escape($contact['name'].' '.$contact['handle'].' (buka tab baru)');?>"><i class="ti ti-<?=html_escape($contact['icon']);?>" aria-hidden="true"></i><span><strong><?=html_escape($contact['name']);?></strong><small><?=html_escape($contact['handle']);?></small></span><span class="pustaka-footer-external" aria-hidden="true">↗</span></a><?php endforeach; ?>
  </div></section>
  <?php endif; ?>

  <nav class="pustaka-footer-resources" aria-label="Tautan pengetahuan dan pemerintahan"><span>Terhubung dengan</span><div>
   <a href="https://rembangkab.go.id/" target="_blank" rel="noopener noreferrer"><i class="ti ti-building-community" aria-hidden="true"></i>Pemkab Rembang<span aria-hidden="true">↗</span></a><a href="https://data.rembangkab.go.id/" target="_blank" rel="noopener noreferrer"><i class="ti ti-chart-bar" aria-hidden="true"></i>Data Rembang<span aria-hidden="true">↗</span></a><a href="https://onesearch.id/" target="_blank" rel="noopener noreferrer"><i class="ti ti-world-search" aria-hidden="true"></i>Indonesia OneSearch<span aria-hidden="true">↗</span></a>
  </div></nav>

  <div class="pustaka-footer-bottom"><p>© <?=date('Y');?> Pustaka Digital Rembang.<span>Baca. Belajar. Bertumbuh.</span></p><a href="#page-top" class="pustaka-footer-top">Kembali ke atas<i class="ti ti-arrow-up" aria-hidden="true"></i></a></div>
 </div>
</footer>
