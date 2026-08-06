<?php defined('BASEPATH') OR exit('No direct script access allowed');
function _notif_ago($ts) {
    $diff = time() - strtotime($ts);
    if ($diff < 60)    return 'baru saja';
    if ($diff < 3600)  return floor($diff / 60) . ' menit lalu';
    if ($diff < 86400) return floor($diff / 3600) . ' jam lalu';
    if ($diff < 604800) return floor($diff / 86400) . ' hari lalu';
    return date('d M Y', strtotime($ts));
}
?>
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
        .nt-hero { background:linear-gradient(135deg,#3b82f6 0%,#6366f1 100%); color:#fff; padding:44px 0 70px; text-align:center; }
        .nt-hero h1 { font-size:2rem; font-weight:900; }
        .nt-wrap { max-width:640px; margin:-46px auto 0; padding:0 16px 60px; }
        .nt-item { background:#fff; border-radius:16px; padding:16px; box-shadow:0 3px 14px rgba(0,0,0,.06); display:flex; gap:14px; align-items:flex-start; margin-bottom:10px; text-decoration:none; color:inherit; transition:transform .1s; }
        .nt-item:hover { transform:translateY(-2px); }
        .nt-item.unread { border-left:4px solid #3b82f6; background:#f8faff; }
        .nt-ic { width:46px; height:46px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.3rem; flex:none; }
        .nt-time { font-size:.75rem; color:#94a3b8; }
        .back-btn { position:fixed; top:16px; left:16px; z-index:10; }
    </style>
</head>
<body>
    <a href="<?= base_url('belajar'); ?>" class="btn btn-sm btn-white back-btn"><i class="ti ti-arrow-left me-1"></i>Arena Belajar</a>

    <div class="nt-hero">
        <div class="container">
            <div style="font-size:2.6rem;margin-bottom:6px">🔔</div>
            <h1>Notifikasi</h1>
        </div>
    </div>

    <div class="nt-wrap">
        <?php if (empty($items)): ?>
        <div class="text-center text-secondary py-5" style="background:#fff;border-radius:16px">
            <i class="ti ti-bell-off fs-1 d-block mb-2"></i>Belum ada notifikasi.
        </div>
        <?php else: ?>
            <?php foreach ($items as $n):
                $href = ! empty($n['url']) ? base_url($n['url']) : 'javascript:void(0)';
            ?>
            <a href="<?= $href; ?>" class="nt-item <?= (int)$n['is_read'] === 0 ? 'unread' : ''; ?>">
                <span class="nt-ic" style="background:<?= html_escape($n['color']); ?>1a;color:<?= html_escape($n['color']); ?>">
                    <i class="ti <?= html_escape($n['icon']); ?>"></i>
                </span>
                <div class="flex-fill">
                    <div class="fw-semibold"><?= html_escape($n['title']); ?></div>
                    <?php if (! empty($n['message'])): ?><div class="text-secondary" style="font-size:.9rem"><?= html_escape($n['message']); ?></div><?php endif; ?>
                    <div class="nt-time mt-1"><i class="ti ti-clock me-1"></i><?= _notif_ago($n['created_at']); ?></div>
                </div>
                <?php if ((int)$n['is_read'] === 0): ?><span class="badge bg-blue">Baru</span><?php endif; ?>
            </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js"></script>
</body>
</html>
