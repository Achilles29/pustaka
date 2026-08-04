<?php defined('BASEPATH') OR exit('No direct script access allowed');
/** Shared fields for add/edit reward. Set $edit=true for the edit modal (ids differ via name selectors). */
?>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label required">Kode</label>
        <input type="text" class="form-control font-monospace" name="code" placeholder="read_30min" required>
        <small class="text-secondary">Unik, huruf kecil. Contoh: <code>read_30min</code></small>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label required">Nama Hadiah</label>
        <input type="text" class="form-control" name="name" placeholder="Baca Digital 30 Menit" required>
    </div>
</div>
<div class="mb-3">
    <label class="form-label">Deskripsi</label>
    <textarea class="form-control" name="description" rows="2" placeholder="Deskripsi singkat hadiah untuk member..."></textarea>
</div>
<div class="row">
    <div class="col-md-8 mb-3">
        <label class="form-label">Ikon (Tabler)</label>
        <div class="input-group">
            <span class="input-group-text"><i class="rw_icon_preview ti ti-gift"></i></span>
            <input type="text" class="form-control" name="icon" value="ti-gift"
                   oninput="this.closest('.input-group').querySelector('.rw_icon_preview').className='rw_icon_preview ti '+this.value">
        </div>
        <small class="text-secondary">Nama ikon Tabler, mis. <code>ti-clock</code>, <code>ti-book</code></small>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Warna</label>
        <input type="color" class="form-control form-control-color w-100" name="color" value="#0ea5e9">
    </div>
</div>

<hr class="my-2">
<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label required">Biaya Poin</label>
        <div class="input-group">
            <input type="number" class="form-control" name="cost_points" value="100" min="1" required>
            <span class="input-group-text">poin</span>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label required">Kuota Token</label>
        <input type="number" class="form-control" name="quota_amount" value="30" min="1" required>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label required">Satuan Kuota</label>
        <select class="form-select" name="quota_unit">
            <option value="minutes">Menit</option>
            <option value="pages">Halaman</option>
            <option value="books">Buku</option>
        </select>
    </div>
</div>
<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Masa Berlaku Token</label>
        <div class="input-group">
            <input type="number" class="form-control" name="token_validity_days" value="30" min="0">
            <span class="input-group-text">hari</span>
        </div>
        <small class="text-secondary">0 = tanpa kadaluarsa</small>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Stok</label>
        <input type="number" class="form-control" name="stock" placeholder="Kosong = tak terbatas" min="0">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Batas per User</label>
        <input type="number" class="form-control" name="per_user_limit" value="0" min="0">
        <small class="text-secondary">0 = tak terbatas</small>
    </div>
</div>
<div class="row align-items-center">
    <div class="col-md-4 mb-3">
        <label class="form-label">Urutan Tampil</label>
        <input type="number" class="form-control" name="sort_order" value="100" min="0">
    </div>
    <div class="col-md-8">
        <label class="form-check mt-3">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
            <span class="form-check-label">Aktif (tampil untuk member)</span>
        </label>
    </div>
</div>
