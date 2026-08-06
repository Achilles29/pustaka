<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="mb-3">
    <label class="form-label required">Sisi Depan (Istilah / Pertanyaan)</label>
    <input type="text" class="form-control" name="front" maxlength="300" required>
</div>
<div class="mb-3">
    <label class="form-label required">Sisi Belakang (Definisi / Jawaban)</label>
    <textarea class="form-control" name="back" rows="3" maxlength="1000" required></textarea>
</div>
<div class="mb-3">
    <label class="form-label">Petunjuk (opsional)</label>
    <input type="text" class="form-control" name="hint" maxlength="300" placeholder="Petunjuk kecil sebelum kartu dibalik">
</div>
<div class="row align-items-end">
    <div class="col-6">
        <label class="form-label">Urutan</label>
        <input type="number" class="form-control" name="sort_order" value="0" min="0">
    </div>
    <div class="col-6">
        <label class="form-check mt-2">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
            <span class="form-check-label">Aktif</span>
        </label>
    </div>
</div>
