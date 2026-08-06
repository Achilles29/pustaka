<?php defined('BASEPATH') OR exit('No direct script access allowed');
$name = $member['full_name'] ?: ($member['member_name'] ?: $member['username']);
$r    = $report;
$back = ! empty($is_admin) ? base_url('learn-reports') : base_url('belajar');
$fmt  = function ($ts) { return $ts ? date('d M Y', strtotime($ts)) : '—'; };
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Raport Belajar — <?= html_escape($name); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/tabler-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { box-sizing:border-box; }
        body { font-family:'Plus Jakarta Sans',sans-serif; background:#eef2f7; color:#1f2937; margin:0; }
        .toolbar { max-width:800px; margin:0 auto; padding:14px 16px; display:flex; gap:10px; justify-content:space-between; align-items:center; }
        .btn { border:0; border-radius:10px; padding:9px 16px; font-weight:700; font-size:.9rem; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:6px; }
        .btn-print { background:#4c1d95; color:#fff; }
        .btn-back { background:#fff; color:#374151; border:1px solid #e5e7eb; }

        .sheet { max-width:800px; margin:0 auto 30px; background:#fff; box-shadow:0 6px 30px rgba(0,0,0,.1); border-radius:12px; overflow:hidden; }
        .sheet-head { background:linear-gradient(135deg,#4c1d95,#7c3aed); color:#fff; padding:28px 32px; display:flex; justify-content:space-between; align-items:center; }
        .sheet-head h1 { font-size:1.5rem; margin:0 0 4px; font-weight:900; }
        .sheet-head .sub { opacity:.85; font-size:.85rem; }
        .sheet-head .logo { text-align:right; font-size:.78rem; opacity:.9; }
        .ident { padding:20px 32px; border-bottom:1px solid #eee; display:flex; flex-wrap:wrap; gap:8px 40px; }
        .ident div span { display:block; font-size:.72rem; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; }
        .ident div strong { font-size:1rem; }

        .kpis { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; padding:22px 32px; }
        .kpi { border:1px solid #eef0f3; border-radius:12px; padding:14px; text-align:center; }
        .kpi .v { font-size:1.7rem; font-weight:900; line-height:1; }
        .kpi .l { font-size:.72rem; color:#6b7280; margin-top:4px; }

        .section { padding:8px 32px 22px; }
        .section h2 { font-size:1rem; font-weight:800; margin:16px 0 10px; display:flex; align-items:center; gap:7px; color:#4c1d95; }
        table.data { width:100%; border-collapse:collapse; font-size:.85rem; }
        table.data th, table.data td { text-align:left; padding:7px 10px; border-bottom:1px solid #f0f0f3; }
        table.data th { background:#faf9fc; font-size:.72rem; text-transform:uppercase; letter-spacing:.4px; color:#6b7280; }
        .badges { display:flex; flex-wrap:wrap; gap:8px; }
        .badge-item { display:flex; align-items:center; gap:7px; border:1px solid #eee; border-radius:999px; padding:5px 12px; font-size:.82rem; font-weight:600; }
        .empty { color:#9ca3af; font-size:.85rem; padding:6px 0; }
        .pill { display:inline-block; padding:2px 8px; border-radius:999px; font-size:.72rem; font-weight:700; }
        .pill-green { background:#dcfce7; color:#15803d; } .pill-blue { background:#dbeafe; color:#1d4ed8; } .pill-orange { background:#ffedd5; color:#c2410c; }
        .foot { padding:16px 32px 26px; font-size:.72rem; color:#9ca3af; text-align:center; border-top:1px solid #f0f0f3; }

        @media print {
            body { background:#fff; }
            .toolbar { display:none; }
            .sheet { box-shadow:none; margin:0; border-radius:0; max-width:100%; }
            .sheet-head { -webkit-print-color-adjust:exact; print-color-adjust:exact; }
            .kpi, .pill, .badge-item, table.data th { -webkit-print-color-adjust:exact; print-color-adjust:exact; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="<?= $back; ?>" class="btn btn-back"><i class="ti ti-arrow-left"></i>Kembali</a>
        <button class="btn btn-print" onclick="window.print()"><i class="ti ti-printer"></i>Cetak / Simpan PDF</button>
    </div>

    <div class="sheet">
        <div class="sheet-head">
            <div>
                <h1>Raport Belajar</h1>
                <div class="sub">Modul Pembelajaran Inklusif</div>
            </div>
            <div class="logo">
                <div style="font-weight:800">Perpustakaan Digital</div>
                <div>Kabupaten Rembang</div>
            </div>
        </div>

        <div class="ident">
            <div><span>Nama</span><strong><?= html_escape($name); ?></strong></div>
            <div><span>No. Anggota</span><strong><?= html_escape($member['member_no'] ?: '—'); ?></strong></div>
            <div><span>Username</span><strong><?= html_escape($member['username'] ?: '—'); ?></strong></div>
            <div><span>Dicetak</span><strong><?= date('d M Y H:i'); ?></strong></div>
        </div>

        <!-- KPI ringkas -->
        <div class="kpis">
            <div class="kpi"><div class="v" style="color:#7c3aed"><?= number_format($r['total_points']); ?></div><div class="l">Total Poin</div></div>
            <div class="kpi"><div class="v" style="color:#f59e0b"><?= count($r['badges']); ?></div><div class="l">Lencana</div></div>
            <div class="kpi"><div class="v" style="color:#0891b2"><?= (int)$r['quiz']['attempts'] + (int)$r['story']['attempts']; ?></div><div class="l">Quiz &amp; Bacaan</div></div>
            <div class="kpi"><div class="v" style="color:#e11d48"><?= (int)$r['games']['played'] + (int)$r['battle']['played']; ?></div><div class="l">Game &amp; Battle</div></div>
        </div>

        <!-- Lencana -->
        <div class="section">
            <h2><i class="ti ti-award"></i>Lencana Diraih</h2>
            <?php if (! empty($r['badges'])): ?>
            <div class="badges">
                <?php foreach ($r['badges'] as $b): ?>
                <span class="badge-item" style="border-color:<?= html_escape($b['color']); ?>66;color:<?= html_escape($b['color']); ?>">
                    <i class="ti <?= html_escape($b['icon']); ?>"></i><?= html_escape($b['name']); ?>
                </span>
                <?php endforeach; ?>
            </div>
            <?php else: ?><div class="empty">Belum ada lencana.</div><?php endif; ?>
        </div>

        <!-- Ringkasan aktivitas -->
        <div class="section">
            <h2><i class="ti ti-chart-bar"></i>Ringkasan Aktivitas</h2>
            <table class="data">
                <tbody>
                    <tr><td><i class="ti ti-clipboard-check me-1"></i>Quiz / Kompetisi</td><td><?= (int)$r['quiz']['attempts']; ?>x dikerjakan · <?= (int)$r['quiz']['passed']; ?> lulus · rata-rata <?= $r['quiz']['avg']; ?>%</td></tr>
                    <tr><td><i class="ti ti-book me-1"></i>Story Quiz</td><td><?= (int)$r['story']['attempts']; ?>x dibaca · <?= (int)$r['story']['perfect']; ?> sempurna · rata-rata <?= $r['story']['avg']; ?>%</td></tr>
                    <tr><td><i class="ti ti-cards me-1"></i>Flashcard</td><td><?= (int)$r['flashcard']['known']; ?> kartu dikuasai di <?= (int)$r['flashcard']['decks']; ?> deck</td></tr>
                    <tr><td><i class="ti ti-device-gamepad me-1"></i>Mini Game</td><td><?= (int)$r['games']['played']; ?>x dimainkan</td></tr>
                    <tr><td><i class="ti ti-swords me-1"></i>Mode Battle</td><td><?= (int)$r['battle']['played']; ?>x main · <?= (int)$r['battle']['won']; ?> menang</td></tr>
                </tbody>
            </table>
        </div>

        <!-- Riwayat quiz -->
        <?php if (! empty($r['quiz']['recent'])): ?>
        <div class="section">
            <h2><i class="ti ti-history"></i>Riwayat Quiz Terakhir</h2>
            <table class="data">
                <thead><tr><th>Judul</th><th>Nilai</th><th>Status</th><th>Tanggal</th></tr></thead>
                <tbody>
                    <?php foreach ($r['quiz']['recent'] as $q): ?>
                    <tr>
                        <td><?= html_escape($q['title'] ?: '—'); ?></td>
                        <td><?= (int)$q['percentage']; ?>%</td>
                        <td><?= (int)$q['is_passed'] === 1 ? '<span class="pill pill-green">Lulus</span>' : '<span class="pill pill-orange">Belum</span>'; ?></td>
                        <td><?= $fmt($q['submitted_at']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Riwayat poin -->
        <div class="section">
            <h2><i class="ti ti-coins"></i>Perolehan Poin Terakhir</h2>
            <?php if (! empty($r['points_log'])): ?>
            <table class="data">
                <thead><tr><th>Aktivitas</th><th>Poin</th><th>Tanggal</th></tr></thead>
                <tbody>
                    <?php foreach ($r['points_log'] as $p): ?>
                    <tr>
                        <td><?= html_escape($p['description'] ?: $p['label']); ?></td>
                        <td><span class="pill <?= (int)$p['points'] >= 0 ? 'pill-blue' : 'pill-orange'; ?>"><?= (int)$p['points'] >= 0 ? '+' : ''; ?><?= (int)$p['points']; ?></span></td>
                        <td><?= $fmt($p['awarded_at']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?><div class="empty">Belum ada aktivitas berpoin.</div><?php endif; ?>
        </div>

        <div class="foot">
            Raport ini dihasilkan otomatis oleh sistem Perpustakaan Digital Kabupaten Rembang · <?= date('d M Y H:i'); ?>
        </div>
    </div>

    <script>
        // Auto-print bila dibuka dengan ?print=1
        if (new URLSearchParams(location.search).get('print') === '1') {
            window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 400); });
        }
    </script>
</body>
</html>
