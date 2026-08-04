<?php defined('BASEPATH') OR exit('No direct script access allowed');
$unit_labels = ['minutes' => 'menit', 'pages' => 'halaman', 'books' => 'buku'];
$csrf_name = $this->security->get_csrf_token_name();
$csrf_hash = $this->security->get_csrf_hash();
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
    <link rel="stylesheet" href="<?= base_url('assets/css/pustaka-polish.css?v=20260802j'); ?>">
    <style>
        body { font-family:'Plus Jakarta Sans',sans-serif; background:#f4f6fb; }
        .rw-hero { background:linear-gradient(135deg,#0ea5e9 0%,#6366f1 100%); color:#fff; padding:52px 0 90px; text-align:center; position:relative; overflow:hidden; }
        .rw-hero h1 { font-size:2.4rem; font-weight:900; position:relative; z-index:1; }
        .rw-hero p  { font-size:1.05rem; opacity:.9; position:relative; z-index:1; }
        .points-chip { display:inline-flex; align-items:center; gap:8px; background:rgba(255,255,255,.18); backdrop-filter:blur(8px); border:1px solid rgba(255,255,255,.3); border-radius:999px; padding:10px 22px; font-weight:800; font-size:1.35rem; margin-top:14px; position:relative; z-index:1; }
        .rw-wrap { max-width:1040px; margin:-56px auto 0; padding:0 16px 60px; position:relative; z-index:2; }
        .rw-card { background:#fff; border-radius:20px; padding:24px; box-shadow:0 4px 20px rgba(0,0,0,.07); transition:transform .15s,box-shadow .15s; display:flex; flex-direction:column; height:100%; }
        .rw-card:hover { transform:translateY(-4px); box-shadow:0 12px 32px rgba(0,0,0,.12); }
        .rw-icon { width:64px; height:64px; border-radius:18px; display:flex; align-items:center; justify-content:center; font-size:1.8rem; margin-bottom:14px; }
        .rw-cost { display:inline-flex; align-items:center; gap:6px; padding:5px 12px; border-radius:999px; font-weight:700; font-size:.9rem; background:#fff7ed; color:#c2410c; }
        .rw-quota { display:inline-flex; align-items:center; gap:6px; padding:5px 12px; border-radius:999px; font-weight:600; font-size:.85rem; background:#eff6ff; color:#1d4ed8; }
        .back-btn { position:fixed; top:16px; left:16px; z-index:10; }
        .rw-disabled { opacity:.55; }
        .token-box { font-family:monospace; font-size:1.15rem; font-weight:700; letter-spacing:1px; background:#f1f5f9; border:2px dashed #94a3b8; border-radius:12px; padding:14px; text-align:center; color:#0f172a; }
    </style>
</head>
<body>
    <a href="<?= base_url('belajar'); ?>" class="btn btn-sm btn-white back-btn">
        <i class="ti ti-arrow-left me-1"></i>Arena Belajar
    </a>

    <div class="rw-hero">
        <div class="container">
            <div style="font-size:3rem;margin-bottom:8px">🎁</div>
            <h1>Tukar Poin</h1>
            <p>Kumpulkan poin belajar, tukar jadi token baca digital!</p>
            <?php if ($user): ?>
                <div class="points-chip"><i class="ti ti-coins"></i> <span id="points-balance"><?= number_format((int)$total_points); ?></span> poin</div>
            <?php else: ?>
                <div><a href="<?= base_url('login'); ?>" class="btn btn-white mt-3"><i class="ti ti-login me-1"></i>Login untuk menukar poin</a></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="rw-wrap">
        <?php if ($user && ! $has_member): ?>
        <div class="alert alert-warning">
            <i class="ti ti-alert-triangle me-1"></i>
            Akunmu belum tertaut ke data anggota perpustakaan, jadi token belum bisa diterbitkan. Hubungi petugas untuk menautkan akun.
        </div>
        <?php endif; ?>

        <div class="row g-3">
            <?php foreach ($catalog as $r):
                $affordable = $user && (int)$total_points >= (int)$r['cost_points'];
                $out_of_stock = ($r['stock'] !== null && (int)$r['stock'] <= 0);
                $can_redeem = $user && $has_member && $affordable && ! $out_of_stock;
            ?>
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="rw-card <?= (!$affordable && $user) || $out_of_stock ? 'rw-disabled' : ''; ?>">
                    <div class="rw-icon" style="background:<?= html_escape($r['color']); ?>1a;color:<?= html_escape($r['color']); ?>">
                        <i class="ti <?= html_escape($r['icon']); ?>"></i>
                    </div>
                    <h4 class="fw-bold mb-1"><?= html_escape($r['name']); ?></h4>
                    <p class="text-secondary mb-3" style="font-size:.88rem;flex:1"><?= html_escape($r['description'] ?? ''); ?></p>
                    <div class="d-flex gap-2 flex-wrap mb-3">
                        <span class="rw-cost"><i class="ti ti-coins"></i> <?= number_format((int)$r['cost_points']); ?> poin</span>
                        <span class="rw-quota"><i class="ti ti-book"></i> <?= (int)$r['quota_amount']; ?> <?= $unit_labels[$r['quota_unit']] ?? $r['quota_unit']; ?></span>
                    </div>
                    <?php if ($out_of_stock): ?>
                        <button class="btn btn-secondary w-100" disabled><i class="ti ti-ban me-1"></i>Stok Habis</button>
                    <?php elseif (! $user): ?>
                        <a href="<?= base_url('login'); ?>" class="btn btn-outline-primary w-100">Login untuk menukar</a>
                    <?php elseif (! $affordable): ?>
                        <button class="btn btn-light w-100" disabled>Poin belum cukup</button>
                    <?php else: ?>
                        <button class="btn btn-primary w-100 btn-redeem" <?= $can_redeem ? '' : 'disabled'; ?>
                            data-id="<?= (int)$r['id']; ?>"
                            data-name="<?= html_escape($r['name']); ?>"
                            data-cost="<?= (int)$r['cost_points']; ?>">
                            <i class="ti ti-arrows-exchange me-1"></i>Tukar Sekarang
                        </button>
                    <?php endif; ?>
                    <?php if ((int)$r['token_validity_days'] > 0): ?>
                    <div class="text-center text-secondary mt-2" style="font-size:.75rem">Token berlaku <?= (int)$r['token_validity_days']; ?> hari</div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($catalog)): ?>
            <div class="col-12 text-center text-secondary py-5"><i class="ti ti-gift-off fs-1 d-block mb-2"></i>Belum ada hadiah tersedia.</div>
            <?php endif; ?>
        </div>

        <?php if ($user && ! empty($redemptions)): ?>
        <h3 class="fw-bold mt-5 mb-3">Riwayat Penukaranmu</h3>
        <div class="card">
            <div class="list-group list-group-flush">
                <?php foreach ($redemptions as $rd): ?>
                <div class="list-group-item d-flex align-items-center gap-3">
                    <span class="avatar bg-blue-lt text-blue"><i class="ti ti-ticket"></i></span>
                    <div class="flex-fill">
                        <div class="fw-medium"><?= html_escape($rd['reward_name']); ?></div>
                        <div class="text-secondary small">
                            <?= date('d M Y H:i', strtotime($rd['created_at'])); ?> ·
                            Token: <code><?= html_escape($rd['token_code']); ?></code>
                        </div>
                    </div>
                    <span class="badge bg-orange-lt text-orange">−<?= number_format((int)$rd['cost_points']); ?> poin</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Success Modal -->
    <div class="modal modal-blur fade" id="successModal" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-status bg-success"></div>
                <div class="modal-body text-center py-4">
                    <div style="font-size:3rem">🎉</div>
                    <h3 class="mt-2">Penukaran Berhasil!</h3>
                    <p class="text-secondary mb-3" id="success-msg"></p>
                    <div class="mb-2 text-secondary small">Kode Token Baca:</div>
                    <div class="token-box mb-3" id="success-token">—</div>
                    <div class="text-secondary small mb-3" id="success-expire"></div>
                    <button class="btn btn-success w-100" data-bs-dismiss="modal">Selesai</button>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js"></script>
<script>
(function () {
    'use strict';
    var CSRF_NAME = '<?= $csrf_name; ?>';
    var csrfHash  = '<?= $csrf_hash; ?>';
    var successModal = new bootstrap.Modal(document.getElementById('successModal'));

    document.querySelectorAll('.btn-redeem').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id   = btn.dataset.id;
            var name = btn.dataset.name;
            if (!confirm('Tukar ' + btn.dataset.cost + ' poin dengan "' + name + '"?')) return;

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memproses...';

            var body = new URLSearchParams();
            body.append('catalog_id', id);
            body.append(CSRF_NAME, csrfHash);

            fetch('<?= base_url('belajar/tukar/redeem'); ?>', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString()
            })
            .then(function (r) { return r.json().then(function (j) { return { status: r.status, body: j }; }); })
            .then(function (res) {
                var j = res.body;
                // Refresh CSRF if provided by server rotation
                if (j.csrf_hash) csrfHash = j.csrf_hash;

                if (j.ok) {
                    document.getElementById('success-msg').textContent = j.reward_name + ' (' + j.quota_amount + ' ' + unitLabel(j.quota_unit) + ')';
                    document.getElementById('success-token').textContent = j.token;
                    document.getElementById('success-expire').textContent = j.expires_at ? ('Berlaku sampai ' + formatDate(j.expires_at)) : 'Token tanpa batas waktu';
                    var bal = document.getElementById('points-balance');
                    if (bal) bal.textContent = new Intl.NumberFormat('id-ID').format(j.remaining_points);
                    successModal.show();
                    // Reload after modal close to refresh affordability states
                    document.getElementById('successModal').addEventListener('hidden.bs.modal', function () { location.reload(); }, { once: true });
                } else {
                    alert(j.message || 'Penukaran gagal.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ti ti-arrows-exchange me-1"></i>Tukar Sekarang';
                }
            })
            .catch(function () {
                alert('Terjadi kesalahan jaringan. Coba lagi.');
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-arrows-exchange me-1"></i>Tukar Sekarang';
            });
        });
    });

    function unitLabel(u) { return { minutes: 'menit', pages: 'halaman', books: 'buku' }[u] || u; }
    function formatDate(s) {
        var d = new Date(s.replace(' ', 'T'));
        if (isNaN(d)) return s;
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    }
})();
</script>
</body>
</html>
