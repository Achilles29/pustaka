<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= html_escape($title); ?></title>
    <link rel="icon" href="<?= base_url('img/favicon.ico'); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/tabler-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family:'Plus Jakarta Sans',sans-serif; background:#f4f6fb; }
        .review-header { background:#1a56a7; color:#fff; padding:16px 24px; margin-bottom:24px; }
        .review-header h1 { font-size:1.3rem; font-weight:700; margin:0; }
        .review-container { max-width:800px; margin:0 auto; padding:0 16px 60px; }
        .q-card { background:#fff; border-radius:14px; padding:24px; margin-bottom:16px; box-shadow:0 2px 8px rgba(0,0,0,.06); border-left:5px solid #dee2e6; }
        .q-card.correct { border-left-color:#2ecc71; }
        .q-card.incorrect { border-left-color:#e74c3c; }
        .q-card.essay-card { border-left-color:#3498db; }
        .q-num { font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#999; margin-bottom:8px; }
        .q-text { font-size:1rem; line-height:1.7; margin-bottom:16px; }
        .opt { padding:8px 14px; border-radius:8px; margin-bottom:6px; font-size:.95rem; }
        .opt.opt-correct { background:#d5f5e3; font-weight:600; }
        .opt.opt-user-wrong { background:#fdecea; text-decoration:line-through; }
        .opt.opt-neutral { background:#f8f9fa; }
        .explanation-box { background:#fff9e6; border:1px solid #f39c12; border-radius:8px; padding:12px 16px; margin-top:14px; }
        .explanation-box h5 { font-size:.85rem; font-weight:700; color:#e67e22; margin:0 0 6px; }
        .essay-answer-box { background:#eaf3fb; border-radius:8px; padding:12px; margin:10px 0; white-space:pre-wrap; }
        .score-chip { display:inline-flex; align-items:center; gap:6px; padding:4px 12px; border-radius:999px; font-weight:600; font-size:.85rem; }
        .score-chip.correct { background:#d5f5e3; color:#1e8449; }
        .score-chip.incorrect { background:#fdecea; color:#922b21; }
        .score-chip.partial { background:#fef9e7; color:#9a7d0a; }
        .score-chip.pending { background:#eaf3fb; color:#1a5276; }
    </style>
</head>
<body>

<div class="review-header">
    <div class="d-flex align-items-center gap-3">
        <a href="<?= base_url('quiz/result/'.$attempt['id']); ?>" class="btn btn-sm btn-outline-light"><i class="ti ti-arrow-left"></i></a>
        <div>
            <h1><?= html_escape($attempt['session_title']); ?> — Pembahasan</h1>
            <div style="font-size:.85rem;opacity:.8"><?= html_escape($attempt['full_name']); ?> · Nilai: <?= number_format((float)$attempt['percentage'],1); ?>%</div>
        </div>
    </div>
</div>

<div class="review-container">
    <?php $letters = ['A','B','C','D','E']; ?>
    <?php $num = 0; ?>
    <?php foreach($answers as $ans): ?>
    <?php $num++; ?>

    <?php if($ans['type'] === 'multiple_choice'): ?>
    <?php $is_correct = (bool)$ans['is_correct']; ?>
    <div class="q-card <?= $is_correct ? 'correct' : 'incorrect'; ?>">
        <div class="q-num">Soal <?= $num; ?></div>
        <div class="q-text"><?= nl2br(html_escape($ans['question_text'])); ?></div>

        <?php foreach($ans['options'] as $opt): ?>
        <?php
            $idx = (int)$opt['option_index'];
            $is_user = (string)$ans['selected_option'] === (string)$idx;
            $is_right = (int)$ans['correct_option_index'] === $idx;
            $cls = 'opt-neutral';
            if($is_right) $cls = 'opt-correct';
            elseif($is_user && !$is_right) $cls = 'opt-user-wrong';
        ?>
        <div class="opt <?= $cls; ?>">
            <?php if($is_right): ?><i class="ti ti-check text-success me-1"></i><?php endif; ?>
            <?php if($is_user && !$is_right): ?><i class="ti ti-x text-danger me-1"></i><?php endif; ?>
            <strong><?= $letters[$idx]; ?>.</strong> <?= html_escape($opt['option_text']); ?>
            <?php if($is_user && !$is_right): ?><span class="text-danger small ms-1">(jawaban kamu)</span><?php endif; ?>
        </div>
        <?php endforeach; ?>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <span class="score-chip <?= $is_correct ? 'correct' : 'incorrect'; ?>">
                <?= $is_correct ? '<i class="ti ti-check me-1"></i>Benar' : '<i class="ti ti-x me-1"></i>Salah'; ?>
                · <?= number_format((float)$ans['score_earned'],1); ?> / <?= $ans['score_weight']; ?> poin
            </span>
            <?php if($ans['explanation']): ?>
            <button class="btn btn-sm btn-outline-warning" type="button" data-bs-toggle="collapse" data-bs-target="#exp-<?= $num; ?>">
                <i class="ti ti-bulb me-1"></i>Pembahasan
            </button>
            <?php endif; ?>
        </div>

        <?php if($ans['explanation']): ?>
        <div class="collapse mt-2" id="exp-<?= $num; ?>">
            <div class="explanation-box">
                <h5><i class="ti ti-bulb me-1"></i>Pembahasan</h5>
                <?= nl2br(html_escape($ans['explanation'])); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php else: /* essay */ ?>
    <?php
        $score_earned = (float)($ans['essay_score'] ?? $ans['score_earned'] ?? 0);
        $max_score = (float)$ans['score_weight'];
        $chip_cls = $ans['is_correct'] === null ? 'pending' : ($score_earned >= $max_score ? 'correct' : ($score_earned > 0 ? 'partial' : 'incorrect'));
    ?>
    <div class="q-card essay-card">
        <div class="q-num">Soal <?= $num; ?> — Essay</div>
        <div class="q-text"><?= nl2br(html_escape($ans['question_text'])); ?></div>

        <div class="text-secondary small mb-1"><i class="ti ti-writing me-1"></i>Jawaban kamu:</div>
        <div class="essay-answer-box"><?= html_escape($ans['essay_answer'] ?? '(tidak dijawab)'); ?></div>

        <?php if($ans['explanation']): ?>
        <div class="explanation-box mt-3">
            <h5><i class="ti ti-key me-1"></i>Kunci Jawaban</h5>
            <?= nl2br(html_escape($ans['explanation'])); ?>
        </div>
        <?php endif; ?>

        <?php if(!empty($ans['essay_feedback'])): ?>
        <div class="alert alert-info mt-2 mb-0 py-2"><i class="ti ti-message me-1"></i><strong>Feedback:</strong> <?= html_escape($ans['essay_feedback']); ?></div>
        <?php endif; ?>

        <div class="mt-3">
            <span class="score-chip <?= $chip_cls; ?>">
                <?php if($ans['is_correct'] === null): ?><i class="ti ti-clock me-1"></i>Menunggu penilaian
                <?php else: ?><i class="ti ti-star me-1"></i><?= number_format($score_earned,1); ?> / <?= $max_score; ?> poin<?php endif; ?>
            </span>
        </div>
    </div>
    <?php endif; ?>
    <?php endforeach; ?>

    <div class="text-center mt-4">
        <a href="<?= base_url('quiz/result/'.$attempt['id']); ?>" class="btn btn-outline-primary me-2"><i class="ti ti-arrow-left me-1"></i>Kembali ke Hasil</a>
        <a href="<?= base_url('user/dashboard'); ?>" class="btn btn-primary"><i class="ti ti-home me-1"></i>Dashboard</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js"></script>
</body>
</html>
