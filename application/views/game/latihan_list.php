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
    <link rel="stylesheet" href="<?= base_url('assets/css/pustaka-polish.css?v=20260811b'); ?>">
</head>
<body class="practice-list-page">
<?php
$now = date('Y-m-d H:i:s');
$difficulty_labels = ['easy' => 'Mudah', 'medium' => 'Sedang', 'hard' => 'Menantang', 'mixed' => 'Campuran'];
$recommended_url = ! empty($recommendation['grade_id']) ? base_url('belajar/latihan?' . http_build_query(['grade_level_id' => $recommendation['grade_id']])) : '';
?>
<header class="practice-list-topbar">
    <div class="container practice-list-topbar-inner">
        <a href="<?= base_url('belajar'); ?>" class="practice-list-back"><i class="ti ti-arrow-left"></i><span>Arena Belajar</span></a>
        <a href="<?= base_url(); ?>" class="practice-list-home"><i class="ti ti-home"></i><span>Beranda</span></a>
    </div>
</header>

<main class="container practice-list-shell">
    <section class="practice-list-hero">
        <div>
            <span class="practice-list-eyebrow"><i class="ti ti-sparkles"></i> Arena pembelajaran</span>
            <h1>Latihan yang pas untukmu</h1>
            <p>Cari sesi berdasarkan jenjang, mata pelajaran, dan tingkat kesulitan. Hasil latihan langsung dapat dilihat setelah selesai.</p>
        </div>
        <div class="practice-list-hero-icon" aria-hidden="true"><i class="ti ti-notebook"></i></div>
    </section>

    <?php if (! empty($recommendation)): ?>
    <section class="practice-recommendation">
        <div class="practice-recommendation-icon"><i class="ti ti-target-arrow"></i></div>
        <div class="practice-recommendation-copy">
            <span>Rekomendasi untuk usia <?= (int) $recommendation['age']; ?> tahun</span>
            <h2>Mulai dari <?= html_escape($recommendation['label']); ?></h2>
            <p><?= html_escape($recommendation['message']); ?></p>
        </div>
        <?php if ($recommended_url !== ''): ?>
        <a class="btn btn-primary practice-recommendation-action" href="<?= $recommended_url; ?>">Lihat latihan yang disarankan <i class="ti ti-arrow-right"></i></a>
        <?php endif; ?>
    </section>
    <?php elseif (! $user): ?>
    <section class="practice-guest-tip">
        <i class="ti ti-user-check"></i>
        <span><strong>Masuk sebagai anggota</strong> agar kami dapat menyarankan jenjang latihan berdasarkan usia.</span>
        <a href="<?= base_url('login'); ?>">Login</a>
    </section>
    <?php endif; ?>

    <section class="practice-filter-card" aria-label="Filter latihan">
        <div class="practice-filter-heading">
            <div>
                <span class="practice-list-eyebrow">Temukan latihan</span>
                <h2>Filter sesi latihan</h2>
            </div>
            <span class="practice-result-count"><?= count($sessions); ?> sesi ditemukan</span>
        </div>
        <form method="get" action="<?= base_url('belajar/latihan'); ?>" class="practice-filter-form">
            <label class="practice-search-field">
                <span>Cari judul atau mata pelajaran</span>
                <div><i class="ti ti-search"></i><input type="search" name="q" value="<?= html_escape($filters['q']); ?>" placeholder="Contoh: Matematika"></div>
            </label>
            <label>
                <span>Jenjang</span>
                <select name="grade_level_id">
                    <option value="">Semua jenjang</option>
                    <?php foreach ($grades as $grade): ?>
                    <option value="<?= (int) $grade['id']; ?>" <?= (int) $filters['grade_level_id'] === (int) $grade['id'] ? 'selected' : ''; ?>><?= html_escape($grade['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                <span>Mata pelajaran</span>
                <select name="subject_id">
                    <option value="">Semua mata pelajaran</option>
                    <?php foreach ($subjects as $subject): ?>
                    <option value="<?= (int) $subject['id']; ?>" <?= (int) $filters['subject_id'] === (int) $subject['id'] ? 'selected' : ''; ?>><?= html_escape($subject['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                <span>Tingkat kesulitan</span>
                <select name="difficulty">
                    <option value="">Semua tingkat</option>
                    <?php foreach ($difficulty_labels as $value => $label): ?>
                    <option value="<?= $value; ?>" <?= $filters['difficulty'] === $value ? 'selected' : ''; ?>><?= $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                <span>Ketersediaan</span>
                <select name="availability">
                    <option value="available" <?= $filters['availability'] === 'available' ? 'selected' : ''; ?>>Sedang tersedia</option>
                    <option value="upcoming" <?= $filters['availability'] === 'upcoming' ? 'selected' : ''; ?>>Segera dibuka</option>
                </select>
            </label>
            <div class="practice-filter-actions">
                <button type="submit" class="btn btn-primary"><i class="ti ti-adjustments-horizontal"></i> Terapkan filter</button>
                <a href="<?= base_url('belajar/latihan'); ?>" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </section>

    <?php if (! empty($recommended_sessions) && empty($filters['q']) && ! $filters['subject_id'] && ! $filters['grade_level_id'] && ! $filters['difficulty'] && $filters['availability'] === 'available'): ?>
    <section class="practice-suggested-section">
        <div class="practice-section-title">
            <div><span class="practice-list-eyebrow">Pilihan awal</span><h2>Sesuai jenjangmu</h2></div>
            <a href="<?= $recommended_url; ?>">Lihat semua <i class="ti ti-arrow-right"></i></a>
        </div>
        <div class="row g-3">
            <?php foreach ($recommended_sessions as $s): ?>
            <div class="col-12 col-md-4"><?php $compact = true; include __DIR__ . '/_practice_card.php'; ?></div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <section class="practice-session-section">
        <div class="practice-section-title">
            <div><span class="practice-list-eyebrow">Daftar latihan</span><h2><?= $filters['availability'] === 'upcoming' ? 'Latihan yang segera dibuka' : 'Latihan yang dapat dikerjakan' ?></h2></div>
        </div>
        <?php if (! empty($sessions)): ?>
        <div class="row g-3">
            <?php foreach ($sessions as $s): ?>
            <div class="col-12 col-md-6 col-xl-4"><?php $compact = false; include __DIR__ . '/_practice_card.php'; ?></div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="practice-empty-state">
            <i class="ti ti-clipboard-off"></i>
            <h3>Belum ada latihan yang sesuai</h3>
            <p>Ubah atau reset filter untuk melihat sesi lainnya. Latihan baru akan muncul saat dibuka oleh pengelola.</p>
            <a href="<?= base_url('belajar/latihan'); ?>" class="btn btn-outline-primary">Reset filter</a>
        </div>
        <?php endif; ?>
    </section>
</main>
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js"></script>
</body>
</html>
