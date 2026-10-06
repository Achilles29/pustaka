<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php if (!empty($contact_links)): ?>
<section class="pustaka-contacts" aria-label="Kontak perpustakaan">
 <div class="pustaka-contact-heading"><span>TERHUBUNG LEBIH DEKAT</span><h2>Sapa kami, dengan cara Anda.</h2></div>
 <div class="pustaka-contact-grid">
 <?php foreach ($contact_links as $contact): ?>
 <a class="pustaka-contact-link" href="<?=html_escape($contact['url']);?>" target="_blank" rel="noopener noreferrer" aria-label="<?=html_escape($contact['name'].' '.$contact['handle'].' (buka tab baru)');?>">
  <i class="ti ti-<?=html_escape($contact['icon']);?>" aria-hidden="true"></i><div><strong><?=html_escape($contact['name']);?></strong><span><?=html_escape($contact['handle']);?></span><small><?=html_escape($contact['note']);?></small></div><span class="contact-arrow" aria-hidden="true">↗</span>
 </a>
 <?php endforeach; ?>
 </div>
</section>
<?php endif; ?>
