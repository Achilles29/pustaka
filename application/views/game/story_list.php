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
        .st-hero { background:linear-gradient(135deg,#0891b2 0%,#0ea5e9 100%); color:#fff; padding:52px 0 90px; text-align:center; }
        .st-hero h1 { font-size:2.4rem; font-weight:900; }
        .st-hero p { opacity:.9; }
        .st-wrap { max-width:1040px; margin:-56px auto 0; padding:0 16px 60px; }
        .st-card { background:#fff; border-radius:20px; padding:24px; box-shadow:0 4px 20px rgba(0,0,0,.07); transition:transform .15s,box-shadow .15s; display:flex; flex-direction:column; height:100%; text-decoration:none; color:inherit; }
        .st-card:hover { transform:translateY(-4px); box-shadow:0 12px 32px rgba(0,0,0,.12); }
        .st-icon { width:60px; height:60px; border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:1.7rem; margin-bottom:14px; }
        .back-btn { position:fixed; top:16px; left:16px; z-index:10; }
        .st-meta { display:flex; gap:10px; font-size:.8rem; color:#64748b; }
    </style>
</head>
<body>
    <a href="<?= base_url('belajar'); ?>" class="btn btn-sm btn-white back-btn"><i class="ti ti-arrow-left me-1"></i>Arena Belajar</a>

    <div class="st-hero">
        <div class="container">
            <div style="font-size:3rem;margin-bottom:8px">📖</div>
            <h1>Story Quiz</h1>
            <p>Baca ceritanya, jawab pertanyaannya, uji pemahamanmu!</p>
            <?php if (!$user): ?>
            <a href="<?= base_url('login'); ?>" class="btn btn-white mt-2"><i class="ti ti-login me-1"></i>Login untuk simpan nilai & poin</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="st-wrap">
        <div class="row g-3">
            <?php foreach ($passages as $p):
                $score = $user && isset($best[$p['id']]) ? (float)$best[$p['id']] : null;
            ?>
            <div class="col-12 col-sm-6 col-lg-4">
                <a href="<?= base_url('belajar/cerita/'.$p['code']); ?>" class="st-card">
                    <div class="st-icon" style="background:<?= html_escape($p['color']); ?>1a;color:<?= html_escape($p['color']); ?>">
                        <i class="ti <?= html_escape($p['icon']); ?>"></i>
                    </div>
                    <h4 class="fw-bold mb-1"><?= html_escape($p['title']); ?></h4>
                    <p class="text-secondary mb-3" style="font-size:.88rem;flex:1"><?= html_escape($p['summary'] ?? ''); ?></p>
                    <div class="st-meta mb-2">
                        <span><i class="ti ti-clock me-1"></i><?= (int)$p['estimated_minutes']; ?> mnt</span>
                        <span><i class="ti ti-help-circle me-1"></i><?= (int)$p['question_count']; ?> soal</span>
                    </div>
                    <?php if ($score !== null): ?>
                    <span class="badge <?= $score >= 100 ? 'bg-success text-white' : 'bg-cyan-lt text-cyan'; ?>">
                        <i class="ti ti-<?= $score >= 100 ? 'star-filled' : 'target-arrow'; ?> me-1"></i>Nilai terbaik: <?= (int)$score; ?>%
                    </span>
                    <?php else: ?>
                    <span class="badge bg-cyan-lt text-cyan"><i class="ti ti-player-play me-1"></i>Mulai baca</span>
                    <?php endif; ?>
                </a>
            </div>
            <?php endforeach; ?>
            <?php if (empty($passages)): ?>
            <div class="col-12 text-center text-secondary py-5"><i class="ti ti-book-off fs-1 d-block mb-2"></i>Belum ada bacaan tersedia.</div>
            <?php endif; ?>
        </div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js"></script>
</body>
</html>
