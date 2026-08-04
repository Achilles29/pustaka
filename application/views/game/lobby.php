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
    <link rel="stylesheet" href="<?= base_url('assets/css/pustaka-polish.css?v=20260802j'); ?>">
    <style>
        body { font-family:'Plus Jakarta Sans',sans-serif; background:#f4f6fb; }
        .lobby-hero { background:linear-gradient(135deg,#1a56a7 0%,#7c3aed 100%); color:#fff; padding:60px 0 80px; text-align:center; position:relative; overflow:hidden; }
        .lobby-hero::after { content:''; position:absolute; inset:-40px; background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E"); }
        .lobby-hero h1 { font-size:2.8rem; font-weight:900; position:relative; z-index:1; }
        .lobby-hero p  { font-size:1.15rem; opacity:.85; position:relative; z-index:1; }
        .game-cards   { max-width:1000px; margin:0 auto; padding:0 16px; }
        .game-card    { background:#fff; border-radius:20px; padding:28px; box-shadow:0 4px 20px rgba(0,0,0,.07); cursor:pointer; transition:transform .15s,box-shadow .15s; text-decoration:none; color:inherit; display:block; }
        .game-card:hover { transform:translateY(-4px); box-shadow:0 12px 32px rgba(0,0,0,.12); }
        .game-icon    { width:72px; height:72px; border-radius:20px; display:flex; align-items:center; justify-content:center; font-size:2rem; margin-bottom:16px; }
        .game-tag     { display:inline-block; padding:4px 10px; border-radius:999px; font-size:.78rem; font-weight:600; }
        .back-btn     { position:fixed; top:16px; left:16px; z-index:10; }
    </style>
</head>
<body>
    <a href="<?= base_url('user/dashboard'); ?>" class="btn btn-sm btn-white back-btn">
        <i class="ti ti-arrow-left me-1"></i>Dashboard
    </a>

    <div class="lobby-hero">
        <div class="container">
            <div style="font-size:3.5rem;margin-bottom:16px">🎮</div>
            <h1>Arena Belajar</h1>
            <p>Belajar sambil bermain, raih poin dan lencana!</p>
            <?php if (!$user): ?>
            <a href="<?= base_url('login'); ?>" class="btn btn-white mt-3">
                <i class="ti ti-login me-1"></i>Login untuk kumpulkan poin
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="game-cards py-5">
        <!-- Quiz Section -->
        <h3 class="mb-3 fw-bold">Quiz &amp; Latihan</h3>
        <div class="row g-3 mb-5">
            <div class="col-12 col-md-6">
                <a href="<?= base_url('quiz-sessions'); ?>" class="game-card">
                    <div class="game-icon" style="background:#e0f2fe;color:#0369a1">
                        <i class="ti ti-clipboard-list"></i>
                    </div>
                    <h4 class="fw-bold mb-1">Latihan Soal</h4>
                    <p class="text-secondary mb-3">Latihan soal dari bank soal. Kerjakan kapan saja, langsung dapat pembahasan.</p>
                    <span class="game-tag" style="background:#e0f2fe;color:#0369a1"><i class="ti ti-coin me-1"></i>+10 poin / sesi</span>
                </a>
            </div>
            <div class="col-12 col-md-6">
                <a href="<?= base_url('quiz-competitions'); ?>" class="game-card">
                    <div class="game-icon" style="background:#fdf2f8;color:#9333ea">
                        <i class="ti ti-trophy"></i>
                    </div>
                    <h4 class="fw-bold mb-1">Kompetisi</h4>
                    <p class="text-secondary mb-3">Ikuti kompetisi resmi dengan soal yang telah ditentukan. Adu kemampuan!</p>
                    <span class="game-tag" style="background:#fdf2f8;color:#9333ea"><i class="ti ti-trophy me-1"></i>Hadiah poin spesial</span>
                </a>
            </div>
        </div>

        <!-- Tukar Poin banner -->
        <a href="<?= base_url('belajar/tukar'); ?>" class="game-card mb-5 d-flex align-items-center gap-3" style="background:linear-gradient(135deg,#0ea5e9 0%,#6366f1 100%);color:#fff">
            <div class="game-icon mb-0" style="background:rgba(255,255,255,.2);color:#fff"><i class="ti ti-gift"></i></div>
            <div class="flex-fill">
                <h4 class="fw-bold mb-1">Tukar Poin jadi Token Baca</h4>
                <p class="mb-0" style="opacity:.9">Sudah kumpulkan banyak poin? Tukarkan dengan akses baca koleksi digital!</p>
            </div>
            <i class="ti ti-arrow-right fs-1"></i>
        </a>

        <!-- Game Section -->
        <?php if (!empty($game_types)): ?>
        <h3 class="mb-3 fw-bold">Mini Game</h3>
        <div class="row g-3">
            <?php foreach ($game_types as $gt): ?>
            <div class="col-12 col-sm-6 col-md-4">
                <a href="<?= base_url('belajar/pilih/' . $gt['code']); ?>" class="game-card">
                    <div class="game-icon" style="background:<?= html_escape($gt['color']); ?>1a;color:<?= html_escape($gt['color']); ?>">
                        <i class="<?= html_escape($gt['icon']); ?>"></i>
                    </div>
                    <h4 class="fw-bold mb-1"><?= html_escape($gt['name']); ?></h4>
                    <p class="text-secondary mb-3" style="font-size:.9rem"><?= html_escape(mb_substr($gt['description'], 0, 90)); ?></p>
                    <span class="game-tag" style="background:<?= html_escape($gt['color']); ?>1a;color:<?= html_escape($gt['color']); ?>">
                        <i class="ti ti-coin me-1"></i>+5 poin / sesi
                    </span>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js"></script>
</body>
</html>
