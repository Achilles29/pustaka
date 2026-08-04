<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= html_escape($title); ?></title>
    <link rel="icon" href="<?= base_url('img/favicon.ico'); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/tabler-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family:'Plus Jakarta Sans',sans-serif; background:#f4f6fb; min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .result-card { max-width:520px; width:100%; margin:20px auto; }
        .score-circle { width:160px; height:160px; border-radius:50%; display:flex; flex-direction:column; align-items:center; justify-content:center; margin:0 auto 20px; font-weight:800; }
        .score-circle.passed { background:linear-gradient(135deg,#27ae60,#2ecc71); color:#fff; }
        .score-circle.failed { background:linear-gradient(135deg,#e74c3c,#c0392b); color:#fff; }
        .score-circle.pending { background:linear-gradient(135deg,#f39c12,#e67e22); color:#fff; }
        .score-percent { font-size:2.8rem; line-height:1; }
        .score-label { font-size:.85rem; opacity:.85; }
        .stat-row { display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid #f0f0f0; }
        .stat-row:last-child { border-bottom:none; }
        .result-hero { text-align:center; padding:32px 24px 20px; }
        .result-emoji { font-size:3rem; }
    </style>
</head>
<body>
<div class="result-card">
    <div class="card shadow-lg" style="border-radius:20px;border:none">
        <?php if(! $hidden): ?>
        <?php
            $pct = (float)($attempt['percentage'] ?? 0);
            $passed = $attempt['is_passed'];
            $statusClass = $passed === null ? 'pending' : ($passed ? 'passed' : 'failed');
            $emoji = $passed === null ? '⏳' : ($passed ? '🎉' : '😊');
            $label = $passed === null ? 'Menunggu Penilaian' : ($passed ? 'Selamat! Kamu Lulus!' : 'Belum Lulus — Semangat!');
        ?>
        <div class="card-body result-hero">
            <div class="result-emoji mb-3"><?= $emoji; ?></div>
            <h1 class="fw-bold"><?= $label; ?></h1>
            <div class="score-circle <?= $statusClass; ?> my-4">
                <div class="score-percent"><?= number_format($pct, 1); ?>%</div>
                <div class="score-label">Nilai Akhir</div>
            </div>
            <p class="text-secondary"><?= html_escape($attempt['full_name']); ?></p>
            <p class="text-secondary small"><?= html_escape($attempt['session_title']); ?></p>
        </div>

        <div class="card-body pt-0">
            <div class="stat-row"><span class="text-secondary">Skor</span><span class="fw-bold"><?= number_format((float)$attempt['total_score'],1); ?> / <?= number_format((float)$attempt['max_possible_score'],1); ?></span></div>
            <div class="stat-row"><span class="text-secondary">Waktu Pengerjaan</span><span class="fw-bold"><?= $attempt['time_spent_seconds'] ? gmdate('i\m s\s',(int)$attempt['time_spent_seconds']) : '—'; ?></span></div>
            <div class="stat-row"><span class="text-secondary">Status</span>
                <span class="badge <?= $statusClass==='passed'?'bg-success':($statusClass==='pending'?'bg-warning':'bg-danger'); ?> fs-6">
                    <?= $passed===null?'Menunggu Penilaian Essay':($passed?'Lulus':'Tidak Lulus'); ?>
                </span>
            </div>
            <?php if($attempt['is_flagged']): ?>
            <div class="stat-row"><span class="text-secondary">Catatan</span><span class="badge bg-danger"><i class="ti ti-alert-triangle me-1"></i>Jawaban Ditandai</span></div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="card-body text-center py-5">
            <i class="ti ti-clock-hour-4 fs-1 text-warning d-block mb-3"></i>
            <h2>Jawaban Sudah Dikumpulkan</h2>
            <p class="text-secondary">Hasil akan diumumkan setelah panitia menyelesaikan penilaian.</p>
        </div>
        <?php endif; ?>

        <div class="card-footer bg-transparent d-flex gap-2 justify-content-center pb-4">
            <?php if(!$hidden && ($attempt['allow_review'] && in_array($attempt['status'],['submitted','timed_out']))): ?>
            <a href="<?= base_url('quiz/review/'.$attempt['id']); ?>" class="btn btn-outline-primary">
                <i class="ti ti-book-2 me-1"></i>Lihat Pembahasan
            </a>
            <?php endif; ?>
            <?php if(!empty($attempt['session_type']) && $attempt['session_type']==='practice'): ?>
            <a href="javascript:history.back()" class="btn btn-outline-secondary">
                <i class="ti ti-refresh me-1"></i>Coba Lagi
            </a>
            <?php endif; ?>
            <a href="<?= base_url('user/dashboard'); ?>" class="btn btn-primary">
                <i class="ti ti-home me-1"></i>Dashboard
            </a>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js"></script>
</body>
</html>
