<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= html_escape($title); ?></title>
    <link rel="icon" href="<?= base_url('img/favicon.ico'); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/tabler-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family:'Plus Jakarta Sans',sans-serif; background:#f4f6fb; }
        .choose-header { padding:32px 0 24px; background:#fff; border-bottom:1px solid #e9ecef; }
        .set-card { background:#fff; border-radius:16px; padding:20px; box-shadow:0 2px 8px rgba(0,0,0,.06); cursor:pointer; transition:all .15s; text-decoration:none; color:inherit; display:block; border:2px solid transparent; }
        .set-card:hover { border-color:var(--game-color,#6366f1); transform:translateY(-2px); box-shadow:0 8px 24px rgba(0,0,0,.1); }
        .diff-badge { display:inline-block; padding:3px 10px; border-radius:999px; font-size:.78rem; font-weight:600; }
    </style>
</head>
<body>
<?php
$diff_colors = ['easy'=>'#d1fae5,#065f46','medium'=>'#fef3c7,#92400e','hard'=>'#fee2e2,#991b1b'];
$diff_labels = ['easy'=>'Mudah','medium'=>'Sedang','hard'=>'Sulit'];
?>

<div class="choose-header">
    <div class="container-xl">
        <div class="d-flex align-items-center gap-3">
            <a href="<?= base_url('belajar'); ?>" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left"></i>
            </a>
            <div class="avatar avatar-lg" style="background:<?= html_escape($game_type['color']); ?>1a;color:<?= html_escape($game_type['color']); ?>">
                <i class="<?= html_escape($game_type['icon']); ?> fs-2"></i>
            </div>
            <div>
                <h1 style="font-size:1.5rem;font-weight:800;margin:0"><?= html_escape($game_type['name']); ?></h1>
                <p class="text-secondary mb-0"><?= html_escape($game_type['description']); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="container-xl py-4">
    <?php if (empty($categories)): ?>
    <div class="empty py-5 text-center">
        <i class="ti ti-stack-2 fs-1 text-secondary d-block mb-3"></i>
        <h3>Belum ada konten game</h3>
        <p class="text-secondary">Admin belum menambahkan konten untuk game ini. Coba lagi nanti!</p>
        <a href="<?= base_url('belajar'); ?>" class="btn btn-primary">Kembali ke Arena</a>
    </div>
    <?php endif; ?>

    <?php foreach ($categories as $cat): ?>
    <?php
    $sets_in_cat = array_filter(
        $categories, // placeholder — in real code pass $sets per category from controller
        fn() => true
    );
    ?>
    <div class="mb-4">
        <h3 class="fw-bold mb-3">
            <?= html_escape($cat['name']); ?>
            <?php if ($cat['grade_label'] || $cat['subject_name']): ?>
            <span class="badge bg-blue-lt text-blue ms-2 fw-normal" style="font-size:.8rem">
                <?= html_escape(trim(($cat['grade_label'] ?? '') . ' · ' . ($cat['subject_name'] ?? ''), ' · ')); ?>
            </span>
            <?php endif; ?>
        </h3>
        <?php if (!empty($cat['sets'])): ?>
        <div class="row g-3">
            <?php foreach ($cat['sets'] as $set): ?>
            <?php [$dc_bg, $dc_fg] = explode(',', $diff_colors[$set['difficulty']] ?? '#e5e7eb,#374151'); ?>
            <div class="col-12 col-sm-6 col-md-4">
                <a href="<?= base_url('belajar/play/' . $game_type['code'] . '/' . $set['id']); ?>"
                   class="set-card" style="--game-color:<?= html_escape($game_type['color']); ?>">
                    <h4 class="fw-bold mb-1"><?= html_escape($set['name']); ?></h4>
                    <div class="text-secondary small mb-2"><i class="ti ti-list me-1"></i><?= (int)$set['item_count']; ?> item</div>
                    <span class="diff-badge" style="background:<?= $dc_bg; ?>;color:<?= $dc_fg; ?>">
                        <?= $diff_labels[$set['difficulty']] ?? $set['difficulty']; ?>
                    </span>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="text-secondary">Belum ada set konten untuk kategori ini.</p>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js"></script>
</body>
</html>
