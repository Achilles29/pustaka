<?php defined('BASEPATH') OR exit('No direct script access allowed');
$csrf_name = $this->security->get_csrf_token_name();
$csrf_hash = $this->security->get_csrf_hash();
$q_json = json_encode($questions, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT);
$my_progress = $role === 'host' ? (int) $room['host_progress'] : (int) $room['guest_progress'];
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= html_escape($title); ?> — Mode Battle</title>
    <link rel="icon" href="<?= base_url('img/favicon.ico'); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/tabler-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family:'Plus Jakarta Sans',sans-serif; background:linear-gradient(135deg,#fdf2f8,#f5f3ff); min-height:100vh; margin:0; }
        .wrap { max-width:620px; margin:0 auto; padding:18px 16px 50px; }
        .scoreboard { display:flex; gap:10px; margin-bottom:18px; }
        .player { flex:1; background:#fff; border-radius:16px; padding:12px 14px; box-shadow:0 3px 14px rgba(0,0,0,.06); }
        .player.me { outline:2px solid #9333ea; }
        .player .nm { font-weight:700; font-size:.9rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .player .sc { font-size:1.6rem; font-weight:900; }
        .pbar { height:6px; border-radius:3px; background:#eee; overflow:hidden; margin-top:6px; }
        .pbar > div { height:100%; transition:width .3s; }
        .me .pbar > div { background:#9333ea; }
        .opp .pbar > div { background:#e11d48; }
        .vs { align-self:center; font-weight:900; color:#9333ea; }

        .panel { background:#fff; border-radius:20px; padding:26px; box-shadow:0 6px 24px rgba(0,0,0,.08); text-align:center; }
        .qtext { font-size:1.3rem; font-weight:800; color:#1e1b4b; margin:8px 0 18px; }
        .opt { display:flex; align-items:center; gap:12px; border:2px solid #e2e8f0; border-radius:14px; padding:14px 16px; margin-top:10px; cursor:pointer; transition:.15s; text-align:left; }
        .opt:hover { border-color:#9333ea; }
        .opt.correct { border-color:#16a34a; background:#f0fdf4; }
        .opt.wrong { border-color:#dc2626; background:#fef2f2; }
        .opt-l { width:28px; height:28px; border-radius:50%; background:#f1f5f9; display:inline-flex; align-items:center; justify-content:center; font-weight:800; flex:none; }
        .code-share { font-family:monospace; font-size:2rem; font-weight:900; letter-spacing:6px; color:#9333ea; background:#faf5ff; border:2px dashed #d8b4fe; border-radius:16px; padding:14px; }
        .spin { width:48px; height:48px; border:5px solid #eee; border-top-color:#9333ea; border-radius:50%; animation:sp 1s linear infinite; margin:16px auto; }
        @keyframes sp { to { transform:rotate(360deg); } }
    </style>
</head>
<body>
<div class="wrap">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="<?= base_url('belajar/battle'); ?>" class="btn btn-sm btn-white"><i class="ti ti-arrow-left me-1"></i>Keluar</a>
        <span class="badge bg-purple text-white">Room <?= html_escape($room['code']); ?></span>
    </div>

    <!-- Scoreboard -->
    <div class="scoreboard">
        <div class="player me">
            <div class="nm"><i class="ti ti-user me-1"></i><span id="me-name">Kamu</span></div>
            <div class="sc" id="me-score">0</div>
            <div class="pbar"><div id="me-bar" style="width:0%"></div></div>
        </div>
        <div class="vs">VS</div>
        <div class="player opp">
            <div class="nm"><i class="ti ti-user me-1"></i><span id="opp-name">Lawan</span></div>
            <div class="sc" id="opp-score">0</div>
            <div class="pbar"><div id="opp-bar" style="width:0%"></div></div>
        </div>
    </div>

    <!-- Waiting for opponent to join (host) -->
    <div class="panel" id="panel-waiting" style="display:none">
        <h3 class="fw-bold">Menunggu lawan…</h3>
        <p class="text-secondary">Bagikan kode ini ke temanmu:</p>
        <div class="code-share mb-2"><?= html_escape($room['code']); ?></div>
        <div class="spin"></div>
        <p class="text-secondary small mb-0">Battle mulai otomatis saat lawan bergabung.</p>
    </div>

    <!-- Question panel -->
    <div class="panel" id="panel-question" style="display:none">
        <div class="text-secondary small">Soal <span id="q-num">1</span> dari <?= count($questions); ?></div>
        <div class="qtext" id="q-text">—</div>
        <div id="q-options"></div>
    </div>

    <!-- Waiting for opponent to finish -->
    <div class="panel" id="panel-done-wait" style="display:none">
        <div style="font-size:2.6rem">⏳</div>
        <h3 class="fw-bold">Kamu selesai!</h3>
        <p class="text-secondary">Menunggu lawan menyelesaikan soal…</p>
        <div class="spin"></div>
    </div>

    <!-- Result -->
    <div class="panel" id="panel-result" style="display:none">
        <div style="font-size:3.4rem" id="res-emoji">🏆</div>
        <h2 class="fw-bold" id="res-title">Hasil</h2>
        <p class="text-secondary" id="res-score"></p>
        <div class="alert alert-success" id="res-points" style="display:none"></div>
        <div class="d-flex gap-2 mt-2">
            <a href="<?= base_url('belajar/battle'); ?>" class="btn btn-outline-primary flex-fill">Main Lagi</a>
            <a href="<?= base_url('belajar'); ?>" class="btn btn-primary flex-fill">Arena Belajar</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js"></script>
<script>
(function () {
    'use strict';
    var CODE = '<?= html_escape($room['code']); ?>';
    var ROLE = '<?= $role; ?>';                 // 'host' | 'guest'
    var OPP  = ROLE === 'host' ? 'guest' : 'host';
    var QS   = <?= $q_json; ?>;
    var QCOUNT = <?= count($questions); ?>;
    var CSRF_NAME = '<?= $csrf_name; ?>', csrfHash = '<?= $csrf_hash; ?>';

    var myProgress = <?= $my_progress; ?>;
    var myFinished = myProgress >= QCOUNT;
    var answering  = false;
    var resultShown = false;
    var letters = ['A', 'B', 'C', 'D'];

    var panels = {
        waiting:  document.getElementById('panel-waiting'),
        question: document.getElementById('panel-question'),
        donewait: document.getElementById('panel-done-wait'),
        result:   document.getElementById('panel-result')
    };
    function show(name) { for (var k in panels) panels[k].style.display = (k === name ? 'block' : 'none'); }

    function post(url, data) {
        var body = new URLSearchParams();
        Object.keys(data).forEach(function (k) { body.append(k, data[k]); });
        body.append(CSRF_NAME, csrfHash);
        return fetch(url, { method:'POST', headers:{ 'X-Requested-With':'XMLHttpRequest','Content-Type':'application/x-www-form-urlencoded' }, body:body.toString() })
            .then(function (r) { return r.json(); });
    }

    function renderQuestion() {
        if (myProgress >= QCOUNT) { myFinished = true; show('donewait'); return; }
        var q = QS[myProgress];
        document.getElementById('q-num').textContent = (myProgress + 1);
        document.getElementById('q-text').textContent = q.question;
        var box = document.getElementById('q-options');
        box.innerHTML = '';
        q.options.forEach(function (opt, idx) {
            var el = document.createElement('button');
            el.type = 'button'; el.className = 'opt';
            el.innerHTML = '<span class="opt-l">' + letters[idx] + '</span><span>' + escapeHtml(opt) + '</span>';
            el.addEventListener('click', function () { answer(idx, el); });
            box.appendChild(el);
        });
        show('question');
    }

    function answer(idx, el) {
        if (answering) return;
        answering = true;
        document.querySelectorAll('#q-options .opt').forEach(function (o) { o.style.pointerEvents = 'none'; });

        post('<?= base_url('belajar/battle/answer'); ?>', { code: CODE, index: myProgress, selected: idx })
        .then(function (j) {
            if (!j.ok) { answering = false; document.querySelectorAll('#q-options .opt').forEach(function (o){o.style.pointerEvents='';}); return; }
            if (j.ignored) { myProgress = j.progress; answering = false; renderQuestion(); return; }
            // feedback
            var opts = document.querySelectorAll('#q-options .opt');
            if (j.correct !== null && opts[j.correct]) opts[j.correct].classList.add('correct');
            if (!j.is_correct && opts[idx]) opts[idx].classList.add('wrong');
            // update my score/bar immediately (optimistic)
            fetchState();
            setTimeout(function () {
                myProgress = j.progress;
                answering = false;
                if (j.finished) { myFinished = true; show('donewait'); }
                else renderQuestion();
            }, 850);
        })
        .catch(function () { answering = false; document.querySelectorAll('#q-options .opt').forEach(function (o){o.style.pointerEvents='';}); });
    }

    function applyState(s) {
        document.getElementById('me-name').textContent  = (ROLE === 'host' ? s.host_name : s.guest_name) || 'Kamu';
        document.getElementById('opp-name').textContent = (OPP === 'host' ? s.host_name : s.guest_name) || 'Menunggu…';
        var meScore = ROLE === 'host' ? s.host_score : s.guest_score;
        var oppScore = OPP === 'host' ? s.host_score : s.guest_score;
        var meProg = ROLE === 'host' ? s.host_progress : s.guest_progress;
        var oppProg = OPP === 'host' ? s.host_progress : s.guest_progress;
        document.getElementById('me-score').textContent = meScore;
        document.getElementById('opp-score').textContent = oppScore;
        document.getElementById('me-bar').style.width  = (meProg / QCOUNT * 100) + '%';
        document.getElementById('opp-bar').style.width = (oppProg / QCOUNT * 100) + '%';

        if (s.status === 'finished' && !resultShown) { showResult(s); return; }
        if (s.status === 'waiting') { show('waiting'); return; }
        if (s.status === 'playing' && !resultShown) {
            if (myFinished) show('donewait');
            else if (panels.question.style.display === 'none' && panels.donewait.style.display === 'none') renderQuestion();
        }
    }

    function showResult(s) {
        resultShown = true;
        var meScore = ROLE === 'host' ? s.host_score : s.guest_score;
        var oppScore = OPP === 'host' ? s.host_score : s.guest_score;
        var iWon = s.winner_user_id !== null && ((ROLE === 'host' && s.winner_user_id === <?= (int)$room['host_user_id']; ?>) || (ROLE === 'guest' && s.winner_user_id === <?= (int)$user['id']; ?>));
        var draw = s.winner_user_id === null;
        document.getElementById('res-emoji').textContent = draw ? '🤝' : (iWon ? '🏆' : '💪');
        document.getElementById('res-title').textContent = draw ? 'Seri!' : (iWon ? 'Kamu Menang!' : 'Kamu Kalah');
        document.getElementById('res-score').textContent = 'Skor akhir: ' + meScore + ' — ' + oppScore;
        var rp = document.getElementById('res-points');
        rp.innerHTML = '<i class="ti ti-coins me-1"></i>' + (iWon ? '+20 poin (menang) & ' : '') + '+5 poin (ikut battle) sudah ditambahkan.';
        rp.style.display = 'block';
        show('result');
    }

    function fetchState() {
        return fetch('<?= base_url('belajar/battle/state/'); ?>' + CODE, { headers:{ 'X-Requested-With':'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (j) { if (j.ok) applyState(j.state); })
            .catch(function () {});
    }

    function escapeHtml(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

    // init
    if (myFinished) show('donewait');
    fetchState();
    setInterval(fetchState, 1500);
})();
</script>
</body>
</html>
