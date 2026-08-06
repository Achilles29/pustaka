<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="mb-3">
    <label class="form-label required">Pertanyaan</label>
    <textarea class="form-control" name="question" rows="2" maxlength="500" required></textarea>
</div>
<div class="row">
    <div class="col-md-6 mb-2">
        <label class="form-label required">Opsi A</label>
        <input type="text" class="form-control" name="option_a" maxlength="300" required>
    </div>
    <div class="col-md-6 mb-2">
        <label class="form-label required">Opsi B</label>
        <input type="text" class="form-control" name="option_b" maxlength="300" required>
    </div>
    <div class="col-md-6 mb-2">
        <label class="form-label">Opsi C</label>
        <input type="text" class="form-control" name="option_c" maxlength="300">
    </div>
    <div class="col-md-6 mb-2">
        <label class="form-label">Opsi D</label>
        <input type="text" class="form-control" name="option_d" maxlength="300">
    </div>
</div>
<div class="row mt-1">
    <div class="col-md-5 mb-3">
        <label class="form-label required">Jawaban Benar</label>
        <select class="form-select" name="correct_option">
            <option value="0">A</option>
            <option value="1">B</option>
            <option value="2">C</option>
            <option value="3">D</option>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Urutan</label>
        <input type="number" class="form-control" name="sort_order" value="0" min="0">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-check mt-4">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
            <span class="form-check-label">Aktif</span>
        </label>
    </div>
</div>
<div class="mb-1">
    <label class="form-label">Penjelasan (opsional)</label>
    <textarea class="form-control" name="explanation" rows="2" maxlength="500" placeholder="Penjelasan jawaban yang tampil setelah dijawab"></textarea>
</div>
