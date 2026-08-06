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
        .fc-hero { background:linear-gradient(135deg,#7c3aed 0%,#8b5cf6 100%); color:#fff; padding:52px 0 90px; text-align:center; }
        .fc-hero h1 { font-size:2.4rem; font-weight:900; }
        .fc-hero p { opacity:.9; }
        .fc-wrap { max-width:1040px; margin:-56px auto 0; padding:0 16px 60px; }
        .fc-deck { background:#fff; border-radius:20px; padding:24px; box-shadow:0 4px 20px rgba(0,0,0,.07); transition:transform .15s,box-shadow .15s; display:flex; flex-direction:column; height:100%; text-decoration:none; color:inherit; }
        .fc-deck:hover { transform:translateY(-4px); box-shadow:0 12px 32px rgba(0,0,0,.12); }
        .fc-deck-icon { width:60px; height:60px; border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:1.7rem; margin-bottom:14px; }
        .back-btn { position:fixed; top:16px; left:16px; z-index:10; }
        .fc-progress { height:6px; border-radius:3px; background:#ede9fe; overflow:hidden; }
        .fc-progress > div { height:100%; background:#8b5cf6; }
    </style>
</head>
<body>
    <a href="<?= base_url('belajar'); ?>" class="btn btn-sm btn-white back-btn"><i class="ti ti-arrow-left me-1"></i>Arena Belajar</a>

    <div class="fc-hero">
        <div class="container">
            <div style="font-size:3rem;margin-bottom:8px">🃏</div>
            <h1>Flashcard</h1>
            <p>Belajar mandiri dengan kartu bolak-balik. Balik, ingat, kuasai!</p>
            <?php if (!$user): ?>
            <a href="<?= base_url('login'); ?>" class="btn btn-white mt-2"><i class="ti ti-login me-1"></i>Login untuk simpan progress & poin</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="fc-wrap">
        <div class="row g-3">
            <?php foreach ($decks as $d):
                $total = (int)$d['card_count'];
                $done  = (int)($known[$d['id']] ?? 0);
                $pct   = $total > 0 ? round($done / $total * 100) : 0;
            ?>
            <div class="col-12 col-sm-6 col-lg-4">
                <a href="<?= base_url('belajar/flashcard/'.$d['code']); ?>" class="fc-deck">
                    <div class="fc-deck-icon" style="background:<?= html_escape($d['color']); ?>1a;color:<?= html_escape($d['color']); ?>">
                        <i class="ti <?= html_escape($d['icon']); ?>"></i>
                    </div>
                    <h4 class="fw-bold mb-1"><?= html_escape($d['name']); ?></h4>
                    <p class="text-secondary mb-3" style="font-size:.88rem;flex:1"><?= html_escape($d['description'] ?? ''); ?></p>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="badge bg-purple-lt text-purple"><?= $total; ?> kartu</span>
                        <?php if ($user): ?><span class="text-secondary small"><?= $done; ?>/<?= $total; ?> hafal</span><?php endif; ?>
                    </div>
                    <?php if ($user): ?>
                    <div class="fc-progress"><div style="width:<?= $pct; ?>%"></div></div>
                    <?php endif; ?>
                </a>
            </div>
            <?php endforeach; ?>
            <?php if (empty($decks)): ?>
            <div class="col-12 text-center text-secondary py-5"><i class="ti ti-cards fs-1 d-block mb-2"></i>Belum ada deck flashcard tersedia.</div>
            <?php endif; ?>
        </div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js"></script>
</body>
</html>
