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
        .lt-hero { background:linear-gradient(135deg,#0369a1 0%,#0ea5e9 100%); color:#fff; padding:52px 0 90px; text-align:center; }
        .lt-hero h1 { font-size:2.4rem; font-weight:900; }
        .lt-wrap { max-width:900px; margin:-56px auto 0; padding:0 16px 60px; }
        .lt-card { background:#fff; border-radius:18px; padding:22px; box-shadow:0 4px 20px rgba(0,0,0,.07); display:flex; flex-direction:column; height:100%; }
        .lt-meta { display:flex; gap:14px; flex-wrap:wrap; font-size:.82rem; color:#64748b; margin:8px 0 14px; }
        .back-btn { position:fixed; top:16px; left:16px; z-index:10; }
    </style>
</head>
<body>
    <a href="<?= base_url('belajar'); ?>" class="btn btn-sm btn-white back-btn"><i class="ti ti-arrow-left me-1"></i>Arena Belajar</a>

    <div class="lt-hero">
        <div class="container">
            <div style="font-size:3rem;margin-bottom:8px">📝</div>
            <h1>Latihan Soal</h1>
            <p style="opacity:.9">Pilih sesi latihan, kerjakan soalnya, dan raih poin!</p>
            <?php if (! $user): ?>
            <a href="<?= base_url('login'); ?>" class="btn btn-white mt-2"><i class="ti ti-login me-1"></i>Login untuk mulai latihan</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="lt-wrap">
        <div class="row g-3">
            <?php $now = date('Y-m-d H:i:s'); foreach ($sessions as $s):
                $not_open_yet = ! empty($s['start_time']) && $now < $s['start_time'];
            ?>
            <div class="col-12 col-md-6">
                <div class="lt-card">
                    <h4 class="fw-bold mb-1"><?= html_escape($s['title']); ?></h4>
                    <div class="lt-meta">
                        <?php if (! empty($s['subject_name'])): ?><span><i class="ti ti-book me-1"></i><?= html_escape($s['subject_name']); ?></span><?php endif; ?>
                        <?php if (! empty($s['grade_name'])): ?><span><i class="ti ti-stairs me-1"></i><?= html_escape($s['grade_name']); ?></span><?php endif; ?>
                        <span><i class="ti ti-list-numbers me-1"></i><?= (int)$s['question_count']; ?> soal</span>
                        <?php if ((int)$s['time_limit_minutes'] > 0): ?><span><i class="ti ti-clock me-1"></i><?= (int)$s['time_limit_minutes']; ?> mnt</span><?php endif; ?>
                    </div>
                    <?php if (! empty($s['start_time']) || ! empty($s['end_time'])): ?>
                    <div class="mb-2" style="font-size:.8rem;color:#0369a1">
                        <i class="ti ti-calendar-clock me-1"></i>
                        <?php if (! empty($s['start_time'])): ?>Buka <?= date('d M H:i', strtotime($s['start_time'])); ?><?php endif; ?>
                        <?php if (! empty($s['end_time'])): ?> s/d <?= date('d M H:i', strtotime($s['end_time'])); ?><?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <div class="mt-auto">
                        <?php if (! $user): ?>
                        <a href="<?= base_url('login'); ?>" class="btn btn-outline-primary w-100">Login untuk mengerjakan</a>
                        <?php elseif ($not_open_yet): ?>
                        <button class="btn btn-light w-100" disabled><i class="ti ti-lock me-1"></i>Belum dibuka</button>
                        <?php else: ?>
                        <a href="<?= base_url('quiz/practice/'.$s['code']); ?>" class="btn btn-primary w-100"><i class="ti ti-player-play me-1"></i>Mulai Latihan</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($sessions)): ?>
            <div class="col-12 text-center text-secondary py-5" style="background:#fff;border-radius:18px">
                <i class="ti ti-clipboard-off fs-1 d-block mb-2"></i>
                Belum ada sesi latihan yang dibuka. Cek lagi nanti, ya!
            </div>
            <?php endif; ?>
        </div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js"></script>
</body>
</html>
