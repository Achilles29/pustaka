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
        :root{--game-color:<?= html_escape($game_type['color']); ?>;--game-soft:<?= html_escape($game_type['color']); ?>17}
        body { font-family:'Plus Jakarta Sans',sans-serif; background:linear-gradient(180deg,var(--game-soft) 0,#f8fafc 390px); color:#102a43; min-height:100vh; }
        .choose-header { position:relative; isolation:isolate; overflow:hidden; padding:20px 0 96px; color:#fff; background:linear-gradient(125deg,#172554,var(--game-color)); }
        .choose-header::before,.choose-header::after{content:'';position:absolute;z-index:-1;border:1px solid rgba(255,255,255,.24);border-radius:50%}.choose-header::before{height:370px;width:370px;top:-250px;right:5%}.choose-header::after{height:250px;width:250px;bottom:-200px;left:8%;background:rgba(255,255,255,.06)}
        .choose-nav{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:42px}.choose-nav .btn{color:#fff;background:rgba(255,255,255,.11);border-color:rgba(255,255,255,.35);font-weight:700}.choose-nav .btn:hover{background:#fff;color:var(--game-color)}.choose-brand{font-size:.77rem;font-weight:700;color:rgba(255,255,255,.86)}
        .choose-hero{display:flex;align-items:center;justify-content:center;gap:16px;text-align:left}.game-icon{width:68px;height:68px;flex:none;display:grid;place-items:center;border:1px solid rgba(255,255,255,.35);border-radius:21px;background:rgba(255,255,255,.15);font-size:2rem}.choose-kicker{margin-bottom:4px;color:rgba(255,255,255,.76);font-size:.7rem;font-weight:800;letter-spacing:.11em;text-transform:uppercase}.choose-hero h1{margin:0;font-size:clamp(1.8rem,4.5vw,2.6rem);font-weight:900;letter-spacing:-.045em}.choose-hero p{margin:6px 0 0;color:rgba(255,255,255,.9);font-size:.9rem;line-height:1.55}
        .choose-wrap{position:relative;z-index:1;max-width:1110px;margin:-55px auto 0;padding:0 16px 64px}.choose-hint{display:flex;align-items:center;gap:11px;padding:15px 18px;margin-bottom:18px;border:1px solid #dbeafe;border-radius:18px;background:rgba(255,255,255,.94);box-shadow:0 12px 30px rgba(15,23,42,.07);font-size:.83rem;color:#52606d}.choose-hint i{color:var(--game-color);font-size:1.25rem}.set-section-title{display:flex;align-items:center;gap:10px;margin:25px 0 13px;font-size:1.05rem;color:#1e293b}.set-section-title::before{content:'';width:4px;height:24px;border-radius:99px;background:var(--game-color)}.game-set-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(235px,1fr));gap:14px}
        .set-card { position:relative; overflow:hidden; background:rgba(255,255,255,.98); border-radius:18px; padding:19px 20px; box-shadow:0 7px 20px rgba(15,23,42,.06); cursor:pointer; transition:transform .2s,box-shadow .2s,border-color .2s; text-decoration:none; color:inherit; display:flex; flex-direction:column; min-height:172px; border:1px solid #e2e8f0; }.set-card::after{content:'›';position:absolute;right:17px;bottom:13px;font-size:1.55rem;color:var(--game-color);transition:transform .2s}.set-card:hover { border-color:var(--game-color); transform:translateY(-5px); box-shadow:0 17px 32px rgba(30,41,59,.14); color:inherit; }.set-card:hover::after{transform:translateX(4px)}.set-card h4{max-width:90%;margin-top:8px;font-size:1rem;line-height:1.4;color:#1e293b}.set-category{max-width:90%;overflow:hidden;color:var(--game-color);font-size:.68rem;font-weight:800;letter-spacing:.04em;text-overflow:ellipsis;text-transform:uppercase;white-space:nowrap}.set-card .item-meta{margin-top:auto;color:#64748b;font-size:.78rem}.diff-badge { display:inline-flex; align-items:center; width:max-content; margin-top:10px; padding:4px 10px; border-radius:999px; font-size:.72rem; font-weight:800; }.set-grade{max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.72rem}
        .choose-empty{border:1px dashed #cbd5e1;border-radius:20px;background:#fff;padding:4rem 1.25rem}.choose-empty i{color:var(--game-color)}@media(max-width:575.98px){.choose-header{padding-bottom:82px}.choose-nav{margin-bottom:32px}.choose-brand{font-size:.68rem}.choose-hero{align-items:flex-start;text-align:left;gap:12px}.game-icon{width:55px;height:55px;border-radius:17px;font-size:1.6rem}.choose-hero p{font-size:.82rem}.choose-wrap{margin-top:-42px;padding-bottom:32px}.choose-hint{padding:13px 14px}.set-section-title{margin-top:22px}.game-set-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.set-card{min-height:156px;padding:15px}.set-card h4{font-size:.86rem}.set-category{font-size:.6rem}.set-grade{font-size:.65rem}.set-card .item-meta{font-size:.69rem}.diff-badge{margin-top:8px;padding:3px 8px;font-size:.65rem}}
        .choose-filter{display:grid;grid-template-columns:1fr 1fr auto;gap:10px;background:#fff;border:1px solid #dbeafe;border-radius:18px;padding:16px;margin-bottom:18px}.choose-filter label{font-size:.72rem;font-weight:800;color:#52606d}@media(max-width:575.98px){.choose-filter{grid-template-columns:1fr}.choose-filter .d-flex{justify-content:stretch}.choose-filter .btn{flex:1}}
    </style>
</head>
<body>
<?php
$diff_colors = ['easy'=>'#d1fae5,#065f46','medium'=>'#fef3c7,#92400e','hard'=>'#fee2e2,#991b1b'];
$diff_labels = ['easy'=>'Mudah','medium'=>'Sedang','hard'=>'Sulit'];
?>

<div class="choose-header">
    <div class="container-xl">
        <div class="choose-nav"><a href="<?= base_url('belajar'); ?>" class="btn btn-sm"><i class="ti ti-arrow-left me-1"></i>Arena Belajar</a><span class="choose-brand"><i class="ti ti-sparkles me-1"></i>Pustaka Digital Rembang</span></div>
        <div class="choose-hero">
            <div class="game-icon"><i class="<?= html_escape($game_type['icon']); ?>"></i></div>
            <div>
                <div class="choose-kicker">Pilih tantangan</div>
                <h1><?= html_escape($game_type['name']); ?></h1>
                <p><?= html_escape($game_type['description']); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="choose-wrap">
    <div class="choose-hint"><i class="ti ti-bulb"></i><span>Pilih set yang sesuai jenjang atau topikmu. Tingkat kesulitan dapat kamu gunakan sebagai panduan, bukan batasan.</span></div>
    <?php if(in_array($game_type['code'],['word_scramble','memory_match'],true)):$subjects=[];$grades=[];foreach($all_categories as $fc){if($fc['subject_id'])$subjects[(int)$fc['subject_id']]=$fc['subject_name'];if($fc['grade_level_id'])$grades[(int)$fc['grade_level_id']]=$fc['grade_label'];} ?><form class="choose-filter" method="get"><div><label class="form-label">Mata pelajaran</label><select class="form-select" name="subject_id"><option value="">Semua mapel</option><?php foreach($subjects as $id=>$name): ?><option value="<?= $id; ?>" <?= (int)$filters['subject_id']===$id?'selected':''; ?>><?= html_escape($name); ?></option><?php endforeach; ?></select></div><div><label class="form-label">Jenjang kelas</label><select class="form-select" name="grade_level_id"><option value="">Semua jenjang</option><?php foreach($grades as $id=>$name): ?><option value="<?= $id; ?>" <?= (int)$filters['grade_level_id']===$id?'selected':''; ?>><?= html_escape($name); ?></option><?php endforeach; ?></select></div><div class="d-flex align-items-end gap-2"><button class="btn btn-primary">Tampilkan</button><a class="btn btn-outline-secondary" href="<?= base_url('belajar/pilih/'.$game_type['code']); ?>">↻</a></div></form><?php endif; ?>
    <?php if (empty($categories)): ?>
    <div class="choose-empty text-center">
        <i class="ti ti-stack-2 fs-1 d-block mb-3"></i>
        <h3>Belum ada konten game</h3>
        <p class="text-secondary">Admin belum menambahkan konten untuk game ini. Coba lagi nanti!</p>
        <a href="<?= base_url('belajar'); ?>" class="btn btn-primary">Kembali ke Arena</a>
    </div>
    <?php endif; ?>

    <?php if (!empty($categories)): ?>
    <h3 class="set-section-title">Semua set tersedia</h3>
    <div class="game-set-grid">
        <?php foreach ($categories as $cat): ?>
            <?php foreach (($cat['sets'] ?? []) as $set): ?>
                <?php
                [$dc_bg, $dc_fg] = explode(',', $diff_colors[$set['difficulty']] ?? '#e5e7eb,#374151');
                $scope = trim(($cat['grade_label'] ?? '') . ' · ' . ($cat['subject_name'] ?? ''), ' · ');
                ?>
                <a href="<?= base_url('belajar/play/' . $game_type['code'] . '/' . $set['id']); ?>"
                   class="set-card" style="--game-color:<?= html_escape($game_type['color']); ?>">
                    <span class="set-category"><?= html_escape($cat['name']); ?></span>
                    <h4 class="fw-bold mb-1"><?= html_escape($set['name']); ?></h4>
                    <?php if ($scope !== ''): ?><span class="badge bg-blue-lt text-blue set-grade"><?= html_escape($scope); ?></span><?php endif; ?>
                    <div class="item-meta"><i class="ti ti-list me-1"></i><?= (int)$set['item_count']; ?> <?= $game_type['code'] === 'memory_match' ? 'pasangan' : 'kata'; ?></div>
                    <span class="diff-badge" style="background:<?= $dc_bg; ?>;color:<?= $dc_fg; ?>"><?= $diff_labels[$set['difficulty']] ?? $set['difficulty']; ?></span>
                </a>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js"></script>
</body>
</html>
