<?php defined('BASEPATH') OR exit('No direct script access allowed');
$cards_json = json_encode(array_map(function ($c) {
    return [
        'id'    => (int) $c['id'],
        'front' => $c['front'],
        'back'  => $c['back'],
        'hint'  => $c['hint'],
        'known' => ($c['progress_status'] ?? '') === 'known',
    ];
}, $cards), JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT);
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
    <style>
        body { font-family:'Plus Jakarta Sans',sans-serif; background:linear-gradient(135deg,#f5f3ff 0%,#eef2ff 100%); min-height:100vh; margin:0; }
        .study-top { max-width:640px; margin:0 auto; padding:20px 16px 0; }
        .study-bar { height:8px; border-radius:4px; background:#e0e7ff; overflow:hidden; margin-top:10px; }
        .study-bar > div { height:100%; background:linear-gradient(90deg,#8b5cf6,#6366f1); transition:width .3s; }
        .study-wrap { max-width:640px; margin:0 auto; padding:24px 16px 40px; }

        .flip { perspective:1600px; width:100%; height:360px; cursor:pointer; }
        .flip-inner { position:relative; width:100%; height:100%; transition:transform .55s; transform-style:preserve-3d; }
        .flip.flipped .flip-inner { transform:rotateY(180deg); }
        .flip-face { position:absolute; inset:0; backface-visibility:hidden; -webkit-backface-visibility:hidden;
                     border-radius:24px; box-shadow:0 12px 40px rgba(99,102,241,.18); display:flex; flex-direction:column;
                     align-items:center; justify-content:center; text-align:center; padding:32px; }
        .flip-front { background:#fff; }
        .flip-back { background:linear-gradient(135deg,#8b5cf6,#6366f1); color:#fff; transform:rotateY(180deg); }
        .flip-label { position:absolute; top:16px; left:20px; font-size:.72rem; font-weight:700; letter-spacing:1px; text-transform:uppercase; opacity:.5; }
        .flip-front .term { font-size:2rem; font-weight:800; color:#1e1b4b; }
        .flip-back .def { font-size:1.3rem; font-weight:600; line-height:1.5; }
        .flip-hint { margin-top:16px; font-size:.9rem; color:#8b5cf6; background:#f5f3ff; padding:6px 14px; border-radius:999px; }
        .flip-tip { position:absolute; bottom:16px; width:100%; left:0; text-align:center; font-size:.78rem; opacity:.5; }

        .actions { display:flex; gap:12px; margin-top:22px; }
        .actions .btn { flex:1; padding:14px; font-weight:700; font-size:1rem; border-radius:16px; }
        .known-chip { display:inline-flex; align-items:center; gap:5px; background:#dcfce7; color:#15803d; padding:3px 10px; border-radius:999px; font-size:.75rem; font-weight:700; }

        .done-card { background:#fff; border-radius:24px; padding:40px 28px; text-align:center; box-shadow:0 12px 40px rgba(99,102,241,.15); }
        .toast-points { position:fixed; top:20px; left:50%; transform:translateX(-50%); background:#16a34a; color:#fff; padding:12px 24px; border-radius:999px; font-weight:700; box-shadow:0 8px 24px rgba(22,163,74,.35); z-index:100; opacity:0; transition:opacity .3s,transform .3s; }
        .toast-points.show { opacity:1; transform:translateX(-50%) translateY(6px); }
    </style>
</head>
<body>
    <div class="study-top">
        <div class="d-flex align-items-center justify-content-between">
            <a href="<?= base_url('belajar/flashcard'); ?>" class="btn btn-sm btn-white"><i class="ti ti-arrow-left me-1"></i>Deck</a>
            <div class="fw-bold text-truncate mx-2" style="color:#4c1d95"><?= html_escape($deck['name']); ?></div>
            <span class="badge bg-purple text-white" id="counter">1 / <?= count($cards); ?></span>
        </div>
        <div class="study-bar"><div id="bar" style="width:0%"></div></div>
    </div>

    <div class="study-wrap">
        <!-- Card stage -->
        <div id="stage">
            <div class="flip" id="flip">
                <div class="flip-inner">
                    <div class="flip-face flip-front">
                        <span class="flip-label">Istilah</span>
                        <span class="known-chip" id="known-chip" style="position:absolute;top:14px;right:18px;display:none"><i class="ti ti-circle-check"></i>Hafal</span>
                        <div class="term" id="card-front">—</div>
                        <div class="flip-hint" id="card-hint" style="display:none"></div>
                        <div class="flip-tip"><i class="ti ti-hand-finger me-1"></i>Ketuk kartu untuk membalik</div>
                    </div>
                    <div class="flip-face flip-back">
                        <span class="flip-label">Definisi</span>
                        <div class="def" id="card-back">—</div>
                    </div>
                </div>
            </div>

            <div class="actions">
                <button class="btn btn-outline-secondary" id="btn-again"><i class="ti ti-rotate me-1"></i>Ulangi</button>
                <button class="btn btn-success" id="btn-known"><i class="ti ti-circle-check me-1"></i>Sudah Hafal</button>
            </div>
            <div class="text-center text-secondary mt-3" style="font-size:.8rem">
                <kbd>Spasi</kbd> balik · <kbd>1</kbd> ulangi · <kbd>2</kbd> hafal
            </div>
        </div>

        <!-- Done screen -->
        <div id="done" style="display:none">
            <div class="done-card">
                <div style="font-size:3.5rem">🎉</div>
                <h2 class="fw-bold mt-2">Selesai!</h2>
                <p class="text-secondary mb-3">Kamu sudah menyelesaikan deck <strong><?= html_escape($deck['name']); ?></strong>.</p>
                <div class="d-inline-flex gap-4 mb-3">
                    <div><div class="fs-1 fw-bold text-success" id="sum-known">0</div><div class="text-secondary small">Sudah Hafal</div></div>
                    <div><div class="fs-1 fw-bold text-secondary" id="sum-total"><?= count($cards); ?></div><div class="text-secondary small">Total Kartu</div></div>
                </div>
                <div id="points-line" class="alert alert-success" style="display:none"></div>
                <div class="d-flex gap-2 mt-2">
                    <button class="btn btn-outline-primary flex-fill" id="btn-restart"><i class="ti ti-refresh me-1"></i>Ulangi Deck</button>
                    <a href="<?= base_url('belajar/flashcard'); ?>" class="btn btn-primary flex-fill">Deck Lain</a>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-points" id="toast"></div>

<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js"></script>
<script>
(function () {
    'use strict';
    var CARDS = <?= $cards_json; ?>;
    var LOGGED_IN = <?= $user ? 'true' : 'false'; ?>;
    var DECK_ID = <?= (int)$deck['id']; ?>;
    var CSRF_NAME = '<?= $csrf_name; ?>', csrfHash = '<?= $csrf_hash; ?>';
    var TOTAL = CARDS.length;

    var idx = 0, knownCount = 0;
    var flip = document.getElementById('flip');

    function post(url, data) {
        var body = new URLSearchParams();
        Object.keys(data).forEach(function (k) { body.append(k, data[k]); });
        body.append(CSRF_NAME, csrfHash);
        return fetch(url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        }).then(function (r) { return r.json().catch(function () { return {}; }); });
    }

    function render() {
        var c = CARDS[idx];
        flip.classList.remove('flipped');
        document.getElementById('counter').textContent = (idx + 1) + ' / ' + TOTAL;
        document.getElementById('bar').style.width = (idx / TOTAL * 100) + '%';
        // small delay so text doesn't change mid-flip
        setTimeout(function () {
            document.getElementById('card-front').textContent = c.front;
            document.getElementById('card-back').textContent  = c.back;
            var hint = document.getElementById('card-hint');
            if (c.hint) { hint.textContent = '💡 ' + c.hint; hint.style.display = ''; }
            else { hint.style.display = 'none'; }
            document.getElementById('known-chip').style.display = c.known ? 'inline-flex' : 'none';
        }, flip.classList.contains('flipped') ? 250 : 0);
    }

    function advance(status) {
        var c = CARDS[idx];
        if (status === 'known' && !c._counted) { knownCount++; c._counted = true; }
        if (LOGGED_IN) { post('<?= base_url('belajar/flashcard/progress'); ?>', { card_id: c.id, status: status }); }

        if (idx < TOTAL - 1) { idx++; render(); }
        else { finish(); }
    }

    function finish() {
        document.getElementById('bar').style.width = '100%';
        document.getElementById('stage').style.display = 'none';
        document.getElementById('done').style.display = 'block';
        document.getElementById('sum-known').textContent = knownCount;

        if (LOGGED_IN) {
            post('<?= base_url('belajar/flashcard/finish'); ?>', { deck_id: DECK_ID }).then(function (j) {
                if (j && j.points_earned > 0) {
                    var line = document.getElementById('points-line');
                    line.innerHTML = '<i class="ti ti-coins me-1"></i>Kamu mendapat <strong>+' + j.points_earned + ' poin</strong>!';
                    line.style.display = 'block';
                    showToast('+' + j.points_earned + ' poin! 🎉');
                }
                if (j && j.new_badges && j.new_badges.length) {
                    j.new_badges.forEach(function (b, i) { setTimeout(function () { showToast('🏅 Lencana baru: ' + b.name); }, 1200 * (i + 1)); });
                }
            });
        }
    }

    function showToast(msg) {
        var t = document.getElementById('toast');
        t.textContent = msg; t.classList.add('show');
        setTimeout(function () { t.classList.remove('show'); }, 2600);
    }

    flip.addEventListener('click', function () { flip.classList.toggle('flipped'); });
    document.getElementById('btn-again').addEventListener('click', function () { advance('learning'); });
    document.getElementById('btn-known').addEventListener('click', function () { advance('known'); });
    document.getElementById('btn-restart').addEventListener('click', function () {
        idx = 0; knownCount = 0; CARDS.forEach(function (c) { c._counted = false; });
        document.getElementById('done').style.display = 'none';
        document.getElementById('stage').style.display = 'block';
        render();
    });

    document.addEventListener('keydown', function (e) {
        if (document.getElementById('done').style.display === 'block') return;
        if (e.code === 'Space') { e.preventDefault(); flip.classList.toggle('flipped'); }
        else if (e.key === '1') { advance('learning'); }
        else if (e.key === '2') { advance('known'); }
    });

    render();
})();
</script>
</body>
</html>
