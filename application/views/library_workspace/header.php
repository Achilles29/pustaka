<?php defined('BASEPATH') OR exit('No direct script access allowed');
?>
<link rel="stylesheet" href="<?= base_url('assets/css/library-workspace.css'); ?>?v=<?= filemtime(FCPATH.'assets/css/library-workspace.css'); ?>">
<div class="page-header"><div class="container-xl"><div class="page-pretitle">Perpustakaan Terpadu Kabupaten Rembang</div><h1 class="page-title"><?= html_escape($library['name']??'Jejaring Perpustakaan'); ?></h1><?php if($library): ?><div class="text-secondary mt-1">Kode <?= html_escape($library['code']); ?> · <?= html_escape($library['village'].', '.$library['district']); ?> · Data operasional perpustakaan ini</div><?php endif; ?></div></div>
<div class="page-body network-workspace"><div class="container-xl">
<?php if($this->session->flashdata('success')): ?><div class="alert alert-success" role="status"><?= html_escape($this->session->flashdata('success')); ?></div><?php endif; ?>
<?php if($this->session->flashdata('error')): ?><div class="alert alert-danger" role="alert"><?= html_escape($this->session->flashdata('error')); ?></div><?php endif; ?>
<?php if($library && ($is_central || empty($library_sidebar_ready))): ?><nav class="network-workspace-tabs mb-3" aria-label="Operasional perpustakaan">
<?php foreach([''=>'Dashboard','records/books'=>'Katalog','records/items'=>'Eksemplar','records/members'=>'Anggota','loans'=>'Peminjaman','visits'=>'Kunjungan','reports'=>'Laporan','profile'=>'Profil','survey'=>'Pendataan tambahan','guide'=>'Panduan','settings'=>'Aturan Pinjam','admins'=>'Admin','account'=>'Password'] as $path=>$label): ?><a href="<?= html_escape($network_url($path)); ?>" class="<?= trim(uri_string(),'/')==='library-workspace'.($path?'/'.$path:'')?'active':''; ?>"><?= $label; ?></a><?php endforeach; ?>
<?php if($is_central): ?><a href="<?= base_url('library-workspace'); ?>">Pilih perpustakaan lain</a><?php endif; ?></nav><?php endif; ?>
