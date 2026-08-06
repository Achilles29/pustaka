<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label required">Kode</label>
        <input type="text" class="form-control font-monospace" name="code" placeholder="semut_belalang" required>
    </div>
    <div class="col-md-8 mb-3">
        <label class="form-label required">Judul Bacaan</label>
        <input type="text" class="form-control" name="title" placeholder="Semut dan Belalang" required>
    </div>
</div>
<div class="mb-3">
    <label class="form-label required">Isi Bacaan</label>
    <textarea class="form-control" name="body" rows="7" placeholder="Tulis bacaan di sini. Pisahkan paragraf dengan baris baru (Enter)." required></textarea>
    <small class="text-secondary">Pisahkan paragraf dengan baris kosong / Enter.</small>
</div>
<div class="mb-3">
    <label class="form-label">Ringkasan (untuk kartu)</label>
    <input type="text" class="form-control" name="summary" maxlength="300" placeholder="Deskripsi singkat yang tampil di daftar bacaan">
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Mata Pelajaran</label>
        <select class="form-select" name="subject_id">
            <option value="">— Umum —</option>
            <?php foreach ($subjects as $s): ?><option value="<?= $s['id']; ?>"><?= html_escape($s['name']); ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Jenjang</label>
        <select class="form-select" name="grade_level_id">
            <option value="">— Semua —</option>
            <?php foreach ($grades as $g): ?><option value="<?= $g['id']; ?>"><?= html_escape($g['name']); ?></option><?php endforeach; ?>
        </select>
    </div>
</div>
<div class="row align-items-end">
    <div class="col-md-4 mb-3">
        <label class="form-label">Ikon (Tabler)</label>
        <div class="input-group">
            <span class="input-group-text"><i class="st_icon_preview ti ti-book"></i></span>
            <input type="text" class="form-control" name="icon" value="ti-book"
                   oninput="this.closest('.input-group').querySelector('.st_icon_preview').className='st_icon_preview ti '+this.value">
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <label class="form-label">Warna</label>
        <input type="color" class="form-control form-control-color w-100" name="color" value="#0891b2">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Estimasi (menit)</label>
        <input type="number" class="form-control" name="estimated_minutes" value="3" min="1">
    </div>
    <div class="col-md-2 mb-3">
        <label class="form-label">Urutan</label>
        <input type="number" class="form-control" name="sort_order" value="100" min="0">
    </div>
    <div class="col-md-1 mb-3">
        <label class="form-check mt-2">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
            <span class="form-check-label">Aktif</span>
        </label>
    </div>
</div>
