<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= html_escape($title); ?> — Pustaka Digital Rembang</title>
    <link rel="icon" href="<?= base_url('img/favicon.ico'); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/tabler-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/pustaka.css'); ?>">
    <style>
        body { font-family:'Plus Jakarta Sans',sans-serif; background:#f4f6fb; }
        .bt-hero { background:linear-gradient(135deg,#e11d48 0%,#9333ea 100%); color:#fff; padding:52px 0 90px; text-align:center; }
        .bt-hero h1 { font-size:2.4rem; font-weight:900; }
        .bt-wrap { max-width:720px; margin:-56px auto 0; padding:0 16px 60px; }
        .bt-card { background:#fff; border-radius:20px; padding:28px; box-shadow:0 4px 20px rgba(0,0,0,.07); height:100%; }
        .bt-ic { width:64px; height:64px; border-radius:18px; display:flex; align-items:center; justify-content:center; font-size:1.8rem; margin-bottom:14px; }
        .back-btn { position:fixed; top:16px; left:16px; z-index:10; }
        .code-input { text-transform:uppercase; letter-spacing:4px; font-weight:800; font-size:1.4rem; text-align:center; }
    </style>
</head>
<body>
    <a href="<?= base_url('belajar'); ?>" class="btn btn-sm btn-white back-btn"><i class="ti ti-arrow-left me-1"></i>Arena Belajar</a>

    <div class="bt-hero">
        <div class="container">
            <div style="font-size:3rem;margin-bottom:8px">⚔️</div>
            <h1>Mode Battle</h1>
            <p style="opacity:.9">Adu cepat menjawab soal melawan temanmu!</p>
        </div>
    </div>

    <div class="bt-wrap">
        <?php if ($e = $this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><i class="ti ti-alert-circle me-1"></i><?= html_escape($e); ?></div>
        <?php endif; ?>

        <?php if (! $user): ?>
        <div class="bt-card text-center">
            <div class="bt-ic mx-auto" style="background:#fee2e2;color:#e11d48"><i class="ti ti-login"></i></div>
            <h3 class="fw-bold">Login dulu, yuk!</h3>
            <p class="text-secondary">Mode Battle butuh akun untuk mengenali kamu dan lawanmu.</p>
            <a href="<?= base_url('login'); ?>" class="btn btn-danger"><i class="ti ti-login me-1"></i>Login</a>
        </div>
        <?php elseif (! $pool_ready): ?>
        <div class="bt-card text-center">
            <div class="bt-ic mx-auto" style="background:#fef9c3;color:#ca8a04"><i class="ti ti-alert-triangle"></i></div>
            <h3 class="fw-bold">Belum siap</h3>
            <p class="text-secondary">Pool soal battle belum cukup. Minta admin menambah minimal 3 soal aktif.</p>
        </div>
        <?php else: ?>
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <div class="bt-card">
                    <div class="bt-ic" style="background:#fce7f3;color:#9333ea"><i class="ti ti-plus"></i></div>
                    <h3 class="fw-bold mb-1">Buat Room</h3>
                    <p class="text-secondary" style="font-size:.9rem">Buat room baru dan bagikan kodenya ke temanmu.</p>
                    <?= form_open('belajar/battle/create'); ?>
                    <label class="form-label">Jumlah soal</label>
                    <select name="question_count" class="form-select mb-3">
                        <option value="5" selected>5 soal</option>
                        <option value="7">7 soal</option>
                        <option value="10">10 soal</option>
                    </select>
                    <button type="submit" class="btn btn-primary w-100"><i class="ti ti-swords me-1"></i>Buat &amp; Tunggu Lawan</button>
                    <?= form_close(); ?>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="bt-card">
                    <div class="bt-ic" style="background:#dbeafe;color:#2563eb"><i class="ti ti-login-2"></i></div>
                    <h3 class="fw-bold mb-1">Gabung Room</h3>
                    <p class="text-secondary" style="font-size:.9rem">Punya kode dari temanmu? Masukkan di sini.</p>
                    <?= form_open('belajar/battle/join'); ?>
                    <label class="form-label">Kode Room</label>
                    <input type="text" name="code" class="form-control code-input mb-3" maxlength="12" placeholder="ABCDE" required>
                    <button type="submit" class="btn btn-outline-primary w-100"><i class="ti ti-arrow-right me-1"></i>Gabung Battle</button>
                    <?= form_close(); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js"></script>
</body>
</html>
