<?php defined('BASEPATH') OR exit('No direct script access allowed');
$csrf_name = $this->security->get_csrf_token_name();
$csrf_hash = $this->security->get_csrf_hash();
$paras = array_filter(array_map('trim', preg_split('/\n\s*\n/', $passage['body'])));
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= html_escape($title); ?> — Story Quiz</title>
    <link rel="icon" href="<?= base_url('img/favicon.ico'); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/tabler-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family:'Plus Jakarta Sans',sans-serif; background:#f4f6fb; margin:0; }
        .rd-top { background:linear-gradient(135deg,#0891b2,#0ea5e9); color:#fff; padding:22px 16px; }
        .rd-top .wrap { max-width:760px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; gap:10px; }
        .rd-wrap { max-width:760px; margin:0 auto; padding:22px 16px 60px; }
        .reading { background:#fff; border-radius:20px; padding:28px; box-shadow:0 4px 20px rgba(0,0,0,.06); font-size:1.08rem; line-height:1.85; color:#1f2937; }
        .reading p { margin:0 0 1rem; }
        .qcard { background:#fff; border-radius:16px; padding:20px; box-shadow:0 4px 20px rgba(0,0,0,.06); margin-top:16px; }
        .opt { display:flex; align-items:center; gap:12px; border:2px solid #e2e8f0; border-radius:12px; padding:12px 14px; margin-top:8px; cursor:pointer; transition:all .15s; }
        .opt:hover { border-color:#0ea5e9; }
        .opt input { accent-color:#0891b2; width:18px; height:18px; }
        .opt.sel { border-color:#0891b2; background:#ecfeff; }
        .opt.correct { border-color:#16a34a; background:#f0fdf4; }
        .opt.wrong { border-color:#dc2626; background:#fef2f2; }
        .opt-letter { width:26px; height:26px; border-radius:50%; background:#f1f5f9; display:inline-flex; align-items:center; justify-content:center; font-weight:700; font-size:.8rem; flex:none; }
        .explain { margin-top:10px; font-size:.85rem; color:#475569; background:#f8fafc; border-radius:10px; padding:10px 12px; display:none; }
        .result-hero { background:#fff; border-radius:20px; padding:32px; text-align:center; box-shadow:0 4px 20px rgba(0,0,0,.08); margin-bottom:16px; display:none; }
        .toast-points { position:fixed; top:16px; left:50%; transform:translateX(-50%); background:#16a34a; color:#fff; padding:12px 22px; border-radius:999px; font-weight:700; box-shadow:0 8px 24px rgba(22,163,74,.35); z-index:100; opacity:0; transition:.3s; }
        .toast-points.show { opacity:1; transform:translateX(-50%) translateY(6px); }
    </style>
</head>
<body>
    <div class="rd-top">
        <div class="wrap">
            <a href="<?= base_url('belajar/cerita'); ?>" class="btn btn-sm btn-white"><i class="ti ti-arrow-left me-1"></i>Bacaan</a>
            <div class="fw-bold text-truncate"><?= html_escape($passage['title']); ?></div>
            <span class="badge bg-white text-cyan" id="timer">00:00</span>
        </div>
    </div>

    <div class="rd-wrap">
        <!-- Result banner (after submit) -->
        <div class="result-hero" id="result">
            <div style="font-size:3rem" id="result-emoji">🎯</div>
            <h2 class="fw-bold mt-1" id="result-title">Hasil</h2>
            <div class="display-4 fw-bold" id="result-score">0%</div>
            <p class="text-secondary" id="result-detail"></p>
            <div class="alert alert-success d-inline-block" id="result-points" style="display:none"></div>
            <div class="d-flex gap-2 mt-2">
                <a href="<?= base_url('belajar/cerita'); ?>" class="btn btn-outline-primary flex-fill">Bacaan Lain</a>
                <button class="btn btn-primary flex-fill" onclick="location.reload()"><i class="ti ti-refresh me-1"></i>Ulangi</button>
            </div>
        </div>

        <!-- Reading passage -->
        <div class="reading" id="reading">
            <?php foreach ($paras as $p): ?><p><?= nl2br(html_escape($p)); ?></p><?php endforeach; ?>
        </div>

        <!-- Questions -->
        <form id="quiz-form">
            <h3 class="fw-bold mt-4 mb-1">Pertanyaan</h3>
            <p class="text-secondary">Jawab semua pertanyaan berdasarkan bacaan di atas.</p>

            <?php foreach ($questions as $i => $q):
                $opts = ['A' => $q['option_a'], 'B' => $q['option_b'], 'C' => $q['option_c'], 'D' => $q['option_d']];
            ?>
            <div class="qcard" data-qid="<?= (int)$q['id']; ?>">
                <div class="fw-medium mb-2"><?= $i + 1; ?>. <?= html_escape($q['question']); ?></div>
                <?php $oi = 0; foreach ($opts as $letter => $text): if ($text === null || $text === '') { $oi++; continue; } ?>
                <label class="opt" data-idx="<?= $oi; ?>">
                    <input type="radio" name="q<?= (int)$q['id']; ?>" value="<?= $oi; ?>">
                    <span class="opt-letter"><?= $letter; ?></span>
                    <span><?= html_escape($text); ?></span>
                </label>
                <?php $oi++; endforeach; ?>
                <div class="explain"></div>
            </div>
            <?php endforeach; ?>

            <button type="submit" class="btn btn-primary btn-lg w-100 mt-3" id="btn-submit">
                <i class="ti ti-checks me-1"></i>Kumpulkan Jawaban
            </button>
        </form>
    </div>

    <div class="toast-points" id="toast"></div>

<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js"></script>
<script>
(function () {
    'use strict';
    var PASSAGE_ID = <?= (int)$passage['id']; ?>;
    var TOTAL = <?= count($questions); ?>;
    var CSRF_NAME = '<?= $csrf_name; ?>', csrfHash = '<?= $csrf_hash; ?>';
    var started = Date.now(), submitted = false;

    // Timer
    var timerEl = document.getElementById('timer');
    setInterval(function () {
        if (submitted) return;
        var s = Math.floor((Date.now() - started) / 1000);
        var m = String(Math.floor(s / 60)).padStart(2, '0');
        timerEl.textContent = m + ':' + String(s % 60).padStart(2, '0');
    }, 1000);

    // Option selection styling
    document.querySelectorAll('.opt').forEach(function (opt) {
        opt.addEventListener('click', function () {
            if (submitted) return;
            var card = opt.closest('.qcard');
            card.querySelectorAll('.opt').forEach(function (o) { o.classList.remove('sel'); });
            opt.classList.add('sel');
            opt.querySelector('input').checked = true;
        });
    });

    document.getElementById('quiz-form').addEventListener('submit', function (e) {
        e.preventDefault();
        if (submitted) return;

        var answers = {}, answered = 0;
        document.querySelectorAll('.qcard').forEach(function (card) {
            var qid = card.dataset.qid;
            var checked = card.querySelector('input:checked');
            if (checked) { answers['answers[' + qid + ']'] = checked.value; answered++; }
        });

        if (answered < TOTAL) {
            if (!confirm('Masih ada ' + (TOTAL - answered) + ' soal belum dijawab. Kumpulkan sekarang?')) return;
        }

        var btn = document.getElementById('btn-submit');
        btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menilai...';

        var body = new URLSearchParams();
        body.append('passage_id', PASSAGE_ID);
        body.append('duration_seconds', Math.floor((Date.now() - started) / 1000));
        Object.keys(answers).forEach(function (k) { body.append(k, answers[k]); });
        body.append(CSRF_NAME, csrfHash);

        fetch('<?= base_url('belajar/cerita/submit'); ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        })
        .then(function (r) { return r.json(); })
        .then(function (j) {
            if (!j.ok) { alert(j.message || 'Gagal menilai.'); btn.disabled = false; btn.innerHTML = '<i class="ti ti-checks me-1"></i>Kumpulkan Jawaban'; return; }
            submitted = true;
            showResults(j);
        })
        .catch(function () { alert('Kesalahan jaringan.'); btn.disabled = false; btn.innerHTML = '<i class="ti ti-checks me-1"></i>Kumpulkan Jawaban'; });
    });

    function showResults(j) {
        // Reveal per-question correctness
        j.details.forEach(function (d) {
            var card = document.querySelector('.qcard[data-qid="' + d.id + '"]');
            if (!card) return;
            card.querySelectorAll('.opt').forEach(function (opt) {
                var idx = parseInt(opt.dataset.idx, 10);
                opt.querySelector('input').disabled = true;
                if (idx === d.correct_option) opt.classList.add('correct');
                else if (idx === d.selected && !d.is_correct) opt.classList.add('wrong');
            });
            if (d.explanation) {
                var ex = card.querySelector('.explain');
                ex.innerHTML = '<i class="ti ti-info-circle me-1"></i>' + d.explanation;
                ex.style.display = 'block';
            }
        });

        document.getElementById('btn-submit').style.display = 'none';

        var pct = Math.round(j.percent);
        var res = document.getElementById('result');
        document.getElementById('result-score').textContent = pct + '%';
        document.getElementById('result-detail').textContent = 'Benar ' + j.correct + ' dari ' + j.total + ' soal.';
        var emoji = pct >= 100 ? '🏆' : (pct >= 60 ? '🎉' : '📚');
        document.getElementById('result-emoji').textContent = emoji;
        document.getElementById('result-title').textContent = pct >= 100 ? 'Sempurna!' : (pct >= 60 ? 'Bagus!' : 'Terus Berlatih!');
        document.getElementById('result-score').className = 'display-4 fw-bold ' + (pct >= 60 ? 'text-success' : 'text-orange');

        if (j.logged_in && j.points_earned > 0) {
            var rp = document.getElementById('result-points');
            rp.innerHTML = '<i class="ti ti-coins me-1"></i>+' + j.points_earned + ' poin';
            rp.style.display = 'inline-block';
            showToast('+' + j.points_earned + ' poin! 🎉');
        }
        if (j.new_badges && j.new_badges.length) {
            j.new_badges.forEach(function (b, i) { setTimeout(function () { showToast('🏅 Lencana: ' + b.name); }, 1300 * (i + 1)); });
        }

        res.style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function showToast(msg) {
        var t = document.getElementById('toast');
        t.textContent = msg; t.classList.add('show');
        setTimeout(function () { t.classList.remove('show'); }, 2600);
    }
})();
</script>
</body>
</html>
