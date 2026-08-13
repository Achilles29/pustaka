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
        body { font-family:'Plus Jakarta Sans',sans-serif; background:linear-gradient(180deg,#fff1f2 0,#f8fafc 430px); min-height:100vh; color:#1f163a; }
        .bt-hero { position:relative; isolation:isolate; overflow:hidden; background:linear-gradient(125deg,#9f1239 0%,#db2777 48%,#7e22ce 100%); color:#fff; padding:20px 0 104px; text-align:center; }.bt-hero::before,.bt-hero::after{content:'';position:absolute;z-index:-1;border:1px solid rgba(255,255,255,.18);border-radius:50%}.bt-hero::before{width:340px;height:340px;top:-225px;right:6%}.bt-hero::after{width:280px;height:280px;bottom:-220px;left:6%;background:rgba(255,255,255,.06)}
        .bt-nav{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:42px}.bt-nav .btn{color:#fff;background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.35);font-weight:700}.bt-nav .btn:hover{color:#be185d;background:#fff}.bt-kicker{color:#fce7f3;font-size:.72rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase;margin-bottom:8px}.bt-hero h1 { margin:0; font-size:clamp(2rem,5vw,3rem); font-weight:900; letter-spacing:-.045em; }.bt-hero p{margin:10px 0 0;color:#fdf2f8;font-size:.98rem}
        .bt-wrap { max-width:800px; margin:-58px auto 0; padding:0 16px 64px; position:relative; z-index:1; }.bt-card { position:relative; overflow:hidden; background:rgba(255,255,255,.96); border:1px solid #fbcfe8; border-radius:22px; padding:26px; box-shadow:0 10px 28px rgba(159,18,57,.09); height:100%; }.bt-card h3{color:#312e81}.bt-ic { width:58px; height:58px; border-radius:17px; display:flex; align-items:center; justify-content:center; font-size:1.65rem; margin-bottom:16px; }.bt-card .form-label{font-size:.76rem;font-weight:800;letter-spacing:.05em;text-transform:uppercase;color:#64748b}.bt-card .btn{border-radius:12px;font-weight:800;padding:.7rem 1rem}.bt-card .form-select,.bt-card .form-control{min-height:45px;border-color:#dbeafe}.code-input { text-transform:uppercase; letter-spacing:4px; font-weight:800; font-size:1.25rem; text-align:center; }.bt-note{display:flex;gap:9px;align-items:flex-start;padding:14px 16px;margin-bottom:16px;border:1px solid #fbcfe8;border-radius:16px;background:#fff;color:#6b214f;font-size:.82rem;line-height:1.55}.bt-note i{color:#db2777;font-size:1.1rem}@media(max-width:575.98px){.bt-hero{padding-bottom:82px}.bt-nav{margin-bottom:32px}.bt-nav .brand{font-size:.76rem}.bt-wrap{margin-top:-42px;padding-bottom:32px}.bt-card{padding:22px}}
    </style>
</head>
<body>
    <div class="bt-hero">
        <div class="container">
            <div class="bt-nav"><a href="<?= base_url('belajar'); ?>" class="btn btn-sm"><i class="ti ti-arrow-left me-1"></i>Arena Belajar</a><span class="brand"><i class="ti ti-sparkles me-1"></i>Pustaka Digital Rembang</span></div>
            <div class="bt-kicker">Main bersama teman</div>
            <div style="font-size:2.9rem;margin-bottom:8px" aria-hidden="true">⚔️</div>
            <h1>Mode Battle</h1>
            <p style="opacity:.9">Adu cepat menjawab soal melawan temanmu!</p>
        </div>
    </div>

    <div class="bt-wrap">
        <div class="bt-note"><i class="ti ti-info-circle"></i><span>Battle dimainkan bergantian dalam satu room. Bagikan kode room hanya kepada teman yang ingin kamu ajak bermain.</span></div>
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
                    <label class="form-label">Pilih sesi / judul Battle</label>
                    <select name="battle_session_id" class="form-select mb-3" required>
                        <option value="">Pilih sesi…</option><?php foreach($battle_sessions as $bs): ?><option value="<?= (int)$bs['id']; ?>"><?= html_escape($bs['title']); ?> · <?= (int)$bs['question_count']; ?> soal · <?= ceil($bs['time_limit_seconds']/60); ?> menit · <?= $bs['max_players']===null?'tanpa batas':(int)$bs['max_players'].' pemain'; ?></option><?php endforeach; ?>
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
