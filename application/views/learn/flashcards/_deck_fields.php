<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label required">Kode</label>
        <input type="text" class="form-control font-monospace" name="code" placeholder="ipa_dasar" required>
    </div>
    <div class="col-md-8 mb-3">
        <label class="form-label required">Nama Deck</label>
        <input type="text" class="form-control" name="name" placeholder="IPA Dasar — Istilah Penting" required>
    </div>
</div>
<div class="mb-3">
    <label class="form-label">Deskripsi</label>
    <textarea class="form-control" name="description" rows="2"></textarea>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Mata Pelajaran</label>
        <select class="form-select" name="subject_id">
            <option value="">— Umum —</option>
            <?php foreach ($subjects as $s): ?>
            <option value="<?= $s['id']; ?>"><?= html_escape($s['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Jenjang</label>
        <select class="form-select" name="grade_level_id">
            <option value="">— Semua —</option>
            <?php foreach ($grades as $g): ?>
            <option value="<?= $g['id']; ?>"><?= html_escape($g['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
<div class="row align-items-end">
    <div class="col-md-5 mb-3">
        <label class="form-label">Ikon (Tabler)</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fc_icon_preview ti ti-cards"></i></span>
            <input type="text" class="form-control" name="icon" value="ti-cards"
                   oninput="this.closest('.input-group').querySelector('.fc_icon_preview').className='fc_icon_preview ti '+this.value">
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Warna</label>
        <input type="color" class="form-control form-control-color w-100" name="color" value="#8b5cf6">
    </div>
    <div class="col-md-2 mb-3">
        <label class="form-label">Urutan</label>
        <input type="number" class="form-control" name="sort_order" value="100" min="0">
    </div>
    <div class="col-md-2 mb-3">
        <label class="form-check mt-2">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
            <span class="form-check-label">Aktif</span>
        </label>
    </div>
</div>
