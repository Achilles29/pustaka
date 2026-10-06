<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="container-xl mt-3"><details class="card" id="contact-settings" open>
 <summary class="card-header" style="cursor:pointer"><h2 class="card-title"><i class="ti ti-address-book me-2"></i>Kontak & media sosial perpustakaan</h2></summary>
 <div class="card-body"><p class="text-secondary">Tampil sebagai tautan kontak di formulir Suara Pemustaka. Kosongkan kolom untuk menyembunyikan kanal tersebut.</p>
 <?=form_open('patron-feedback/contacts');?>
 <input type="hidden" name="contacts_token" value="<?=html_escape($contacts_token);?>">
 <fieldset <?=$can_edit_contacts?'':'disabled';?>>
 <div class="row g-3">
 <?php foreach (['whatsapp'=>['WhatsApp','085165805518',20], 'instagram'=>['Instagram','dinarpusrembang',30], 'tiktok'=>['TikTok','perpustakaan.umum.rbg',24]] as $key=>$field): ?>
 <div class="col-md-4"><label class="form-label" for="contact-<?=$key;?>"><?=$field[0];?></label><input id="contact-<?=$key;?>" class="form-control" type="<?=$key==='whatsapp'?'tel':'text';?>" name="<?=$key;?>" maxlength="<?=$field[2];?>" placeholder="<?=html_escape($field[1]);?>" value="<?=html_escape($contacts[$key]);?>"><div class="form-hint"><?=$key==='whatsapp'?'Nomor Indonesia, awalan 08 atau 628.':'Nama pengguna saja, tanpa URL.';?></div></div>
 <?php endforeach; ?>
 </div>
 <?php if ($can_edit_contacts): ?><button type="submit" class="btn btn-primary mt-3"><i class="ti ti-device-floppy me-1"></i>Simpan kontak publik</button><?php endif; ?>
 </fieldset><?=form_close();?>
 <?php if (!$can_edit_contacts): ?><p class="small text-secondary mt-3 mb-0">Izin edit Suara Pemustaka diperlukan untuk mengubah kontak.</p><?php endif; ?>
 </div>
</details></div>
