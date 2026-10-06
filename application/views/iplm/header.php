<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="<?= base_url('assets/css/iplm.css?v=20261005-2'); ?>">
<script defer src="<?= base_url('assets/iplm-tabs.js?v=20261005-2'); ?>"></script>
<div class="page-header"><div class="container-xl"><div class="row align-items-center g-2"><div class="col"><div class="page-pretitle">Perpustakaan terpadu · IPLM</div><h1 class="page-title"><?= html_escape($title); ?></h1></div><div class="col-auto btn-list"><a class="btn btn-outline-primary" href="<?= base_url('iplm'); ?>">Pendataan</a><?php if($may_settings): ?><a class="btn btn-outline-secondary" href="<?= base_url('iplm/settings'); ?>">Pengaturan Form & Periode</a><?php endif; ?></div></div></div></div>
<div class="page-body"><div class="container-xl">
<?php foreach(['success'=>'success','error'=>'danger']as$key=>$style): if($this->session->flashdata($key)): ?><div class="alert alert-<?= $style; ?>"><?= html_escape($this->session->flashdata($key)); ?></div><?php endif; endforeach; ?>
<?php if(!empty($error)): ?><div class="alert alert-danger" role="alert"><?= html_escape($error); ?></div><?php endif; ?>
