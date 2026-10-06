<?php defined('BASEPATH') OR exit('No direct script access allowed');
$hours = (array) $this->config->item('service_hours', 'library_public');
$now = new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta'));
$today = (int) $now->format('N');
$is_open = false;
foreach ($hours as $slot) {
    if (in_array($today, $slot['days'], true)) $is_open = $now->format('H:i') >= $slot['open'] && $now->format('H:i') < $slot['close'];
}
?>
<section class="pustaka-hours" aria-label="Jam layanan perpustakaan">
 <div class="pustaka-hours-heading"><div><span class="pustaka-eyebrow">SINGGAH & TEMUKAN INSPIRASI</span><h2><i class="ti ti-clock" aria-hidden="true"></i> Jam layanan perpustakaan</h2></div><span class="pustaka-hours-badge <?=$is_open?'is-open':'';?>"><?=$is_open?'Dalam jam layanan':'Di luar jam layanan';?></span></div>
 <dl class="pustaka-hours-grid">
 <?php foreach ($hours as $slot): ?><div class="<?=in_array($today,$slot['days'],true)?'is-today':'';?>"><dt><?=html_escape($slot['label']);?><?php if(in_array($today,$slot['days'],true)):?><span>Hari ini</span><?php endif;?></dt><dd><?=html_escape(str_replace(':','.',$slot['open']).' – '.str_replace(':','.',$slot['close']));?><small>WIB</small></dd></div><?php endforeach; ?>
 </dl>
 <p>Jadwal reguler · Hari libur khusus mengikuti pengumuman perpustakaan.</p>
</section>
