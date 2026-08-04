<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= html_escape($title); ?></title>
    <link rel="icon" href="<?= base_url('img/favicon.ico'); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box}
        body{font-family:'Plus Jakarta Sans',sans-serif;background:#1e1b4b;min-height:100vh;margin:0;color:#fff}
        .game-topbar{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;background:rgba(255,255,255,.08);backdrop-filter:blur(8px);border-bottom:1px solid rgba(255,255,255,.1)}
        .game-topbar .back{color:rgba(255,255,255,.7);text-decoration:none;display:flex;align-items:center;gap:6px;font-size:.9rem}
        .game-title{font-size:1rem;font-weight:700;opacity:.9}
        .stats-bar{display:flex;gap:20px;padding:12px 20px;background:rgba(255,255,255,.05);border-bottom:1px solid rgba(255,255,255,.08)}
        .stat{display:flex;flex-direction:column;align-items:center}
        .stat-val{font-size:1.3rem;font-weight:800;line-height:1}
        .stat-label{font-size:.68rem;opacity:.6;text-transform:uppercase;letter-spacing:.05em;margin-top:2px}
        .board-wrap{display:flex;justify-content:center;align-items:flex-start;padding:24px 16px;min-height:calc(100vh - 120px)}
        .board{display:grid;gap:10px;width:100%;max-width:700px}
        .card{perspective:800px;cursor:pointer;aspect-ratio:3/2}
        .card.matched{pointer-events:none}
        .card-inner{position:relative;width:100%;height:100%;transition:transform .45s cubic-bezier(.4,0,.2,1);transform-style:preserve-3d}
        .card.flipped .card-inner,.card.matched .card-inner{transform:rotateY(180deg)}
        .card-face{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;border-radius:14px;padding:10px;text-align:center;backface-visibility:hidden;-webkit-backface-visibility:hidden}
        .card-back{background:linear-gradient(135deg,#4338ca,#7c3aed);border:2px solid rgba(255,255,255,.15)}
        .card-back::after{content:'?';font-size:2.5rem;font-weight:900;opacity:.35}
        .card-front{background:#fff;color:#1e1b4b;transform:rotateY(180deg);font-size:.85rem;font-weight:600;line-height:1.3;border:2px solid transparent}
        .card.matched .card-front{background:linear-gradient(135deg,#bbf7d0,#86efac);border-color:#22c55e}
        .card-front.is-term{background:linear-gradient(135deg,#ede9fe,#ddd6fe);color:#4c1d95}
        .card-front.is-def{background:linear-gradient(135deg,#fce7f3,#fbcfe8);color:#831843}
        .card.matched .card-front.is-term,.card.matched .card-front.is-def{background:linear-gradient(135deg,#bbf7d0,#86efac);color:#14532d}

        /* ── Overlays ── */
        .overlay{position:fixed;inset:0;background:rgba(0,0,0,.7);display:flex;align-items:center;justify-content:center;z-index:100;backdrop-filter:blur(6px)}
        .overlay.hidden{display:none}
        .overlay-card{background:#fff;color:#1e1b4b;border-radius:24px;padding:40px 32px;max-width:380px;width:90%;text-align:center;box-shadow:0 24px 60px rgba(0,0,0,.3)}
        .overlay-card h2{font-size:2rem;font-weight:900;margin:0 0 8px}
        .overlay-card p{color:#6b7280;margin:0 0 24px}
        .btn-play{background:linear-gradient(135deg,#4338ca,#7c3aed);color:#fff;border:none;border-radius:12px;padding:14px 32px;font-size:1rem;font-weight:700;cursor:pointer;width:100%;margin-bottom:10px;font-family:inherit}
        .btn-play:hover{opacity:.9}
        .btn-back{background:#f3f4f6;color:#374151;border:none;border-radius:12px;padding:12px 32px;font-size:.95rem;font-weight:600;cursor:pointer;width:100%;font-family:inherit}
        .btn-back:hover{background:#e5e7eb}
        .stars{font-size:2.5rem;margin-bottom:12px}

        /* badge toast */
        .badge-toast{position:fixed;bottom:24px;right:24px;background:#fff;color:#1e1b4b;border-radius:16px;padding:14px 20px;display:flex;gap:12px;align-items:center;box-shadow:0 8px 32px rgba(0,0,0,.2);transform:translateY(100px);opacity:0;transition:all .4s cubic-bezier(.4,0,.2,1);z-index:200}
        .badge-toast.show{transform:translateY(0);opacity:1}
        .badge-toast-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.4rem}
        .badge-toast-text small{display:block;color:#6b7280;font-size:.75rem;margin-bottom:2px}
        .badge-toast-text strong{font-size:.95rem}
    </style>
</head>
<body>

<div class="game-topbar">
    <a href="<?= base_url('belajar/pilih/' . $game_type['code']); ?>" class="back">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
        Pilih Lain
    </a>
    <div class="game-title"><?= html_escape($set['name']); ?></div>
    <div></div>
</div>
<div class="stats-bar">
    <div class="stat"><div class="stat-val" id="movesCount">0</div><div class="stat-label">Langkah</div></div>
    <div class="stat"><div class="stat-val" id="matchCount">0</div><div class="stat-label">Pasangan</div></div>
    <div class="stat flex-fill text-center"><div class="stat-val" id="timerDisplay">0:00</div><div class="stat-label">Waktu</div></div>
    <div class="stat"><div class="stat-val" id="scoreDisplay">0</div><div class="stat-label">Skor</div></div>
</div>

<div class="board-wrap">
    <div class="board" id="board"></div>
</div>

<!-- Start Overlay -->
<div class="overlay" id="overlayStart">
    <div class="overlay-card">
        <div style="font-size:3.5rem;margin-bottom:8px">🃏</div>
        <h2>Memory Match</h2>
        <p><strong><?= html_escape($set['name']); ?></strong><br>
        Cocokkan <?= count($content); ?> pasangan kartu.<br>Balik dua kartu — jika cocok, poin bertambah!</p>
        <button class="btn-play" id="btnStart">Mulai!</button>
        <button class="btn-back" onclick="history.back()">← Pilih Set Lain</button>
    </div>
</div>

<!-- Win Overlay -->
<div class="overlay hidden" id="overlayWin">
    <div class="overlay-card">
        <div class="stars" id="winStars">⭐⭐⭐</div>
        <h2>Hebat! 🎉</h2>
        <p id="winMsg">Kamu berhasil menyelesaikan semua pasangan!</p>
        <div style="background:#f9fafb;border-radius:12px;padding:16px;margin-bottom:20px">
            <div style="display:flex;justify-content:space-between;margin-bottom:8px">
                <span style="color:#6b7280">Skor</span><strong id="finalScore">0</strong>
            </div>
            <div style="display:flex;justify-content:space-between;margin-bottom:8px">
                <span style="color:#6b7280">Langkah</span><strong id="finalMoves">0</strong>
            </div>
            <div style="display:flex;justify-content:space-between">
                <span style="color:#6b7280">Waktu</span><strong id="finalTime">0:00</strong>
            </div>
        </div>
        <button class="btn-play" id="btnPlayAgain">Main Lagi!</button>
        <button class="btn-back" onclick="location.href='<?= base_url('belajar/pilih/' . $game_type['code']); ?>'">Pilih Set Lain</button>
    </div>
</div>

<div class="badge-toast" id="badgeToast">
    <div class="badge-toast-icon" id="toastIcon"></div>
    <div class="badge-toast-text">
        <small>Lencana baru diraih! 🎖️</small>
        <strong id="toastName"></strong>
    </div>
</div>

<script>
const FINISH_URL = '<?= base_url('belajar/finish'); ?>';
const PAIRS = <?= json_encode(array_map(function($item){
    return ['id'=>$item['id'],'term'=>$item['term'],'definition'=>$item['definition']];
}, $content)); ?>;
const GAME_CODE  = '<?= $game_type['code']; ?>';
const SET_ID     = <?= (int)$set['id']; ?>;

let cards = [], flipped = [], matched = 0, moves = 0, score = 0;
let timerSec = 0, timerInterval = null, started = false;
let isProcessing = false;

// Build card deck: for each pair, create a TERM card and a DEF card
function buildDeck() {
    const deck = [];
    PAIRS.forEach((pair, idx) => {
        deck.push({ pairId: idx, type: 'term', text: pair.term });
        deck.push({ pairId: idx, type: 'def',  text: pair.definition });
    });
    // Fisher-Yates shuffle
    for (let i = deck.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [deck[i], deck[j]] = [deck[j], deck[i]];
    }
    return deck;
}

function calcCols(n) {
    if (n <= 8)  return 4;
    if (n <= 12) return 4;
    return 6;
}

function renderBoard() {
    const deck  = buildDeck();
    const total = deck.length;
    const cols  = calcCols(total);
    const board = document.getElementById('board');
    board.style.gridTemplateColumns = `repeat(${cols}, 1fr)`;
    board.innerHTML = '';
    cards = [];
    flipped = [];
    matched = 0;
    moves   = 0;
    score   = 0;
    updateStats();

    deck.forEach((data, i) => {
        const card = document.createElement('div');
        card.className = 'card';
        card.dataset.index  = i;
        card.dataset.pairId = data.pairId;
        card.dataset.type   = data.type;
        card.innerHTML = `
            <div class="card-inner">
                <div class="card-face card-back"></div>
                <div class="card-face card-front is-${data.type}">${escHtml(data.text)}</div>
            </div>`;
        card.addEventListener('click', onCardClick);
        board.appendChild(card);
        cards.push(card);
    });
}

function onCardClick(e) {
    const card = e.currentTarget;
    if (!started || isProcessing) return;
    if (card.classList.contains('flipped') || card.classList.contains('matched')) return;
    if (flipped.length >= 2) return;

    card.classList.add('flipped');
    flipped.push(card);

    if (flipped.length === 2) {
        moves++;
        isProcessing = true;
        const [a, b] = flipped;
        const sameType = a.dataset.type === b.dataset.type;
        const samePair = a.dataset.pairId === b.dataset.pairId;

        if (samePair && !sameType) {
            // Match!
            setTimeout(() => {
                a.classList.add('matched');
                b.classList.add('matched');
                matched++;
                score += Math.max(10, 100 - moves * 2);
                flipped = [];
                isProcessing = false;
                updateStats();
                if (matched === PAIRS.length) onWin();
            }, 500);
        } else {
            setTimeout(() => {
                a.classList.remove('flipped');
                b.classList.remove('flipped');
                flipped = [];
                isProcessing = false;
                updateStats();
            }, 900);
        }
    } else {
        updateStats();
    }
}

function updateStats() {
    document.getElementById('movesCount').textContent = moves;
    document.getElementById('matchCount').textContent = matched + '/' + PAIRS.length;
    document.getElementById('scoreDisplay').textContent = score;
}

function startTimer() {
    timerInterval = setInterval(() => {
        timerSec++;
        const m = Math.floor(timerSec / 60);
        const s = timerSec % 60;
        document.getElementById('timerDisplay').textContent = m + ':' + String(s).padStart(2, '0');
    }, 1000);
}

function stopTimer() {
    clearInterval(timerInterval);
}

function fmtTime(sec) {
    return Math.floor(sec/60) + ':' + String(sec%60).padStart(2,'0');
}

function onWin() {
    stopTimer();
    const stars = moves <= PAIRS.length ? '⭐⭐⭐' : (moves <= PAIRS.length * 2 ? '⭐⭐' : '⭐');
    document.getElementById('winStars').textContent  = stars;
    document.getElementById('finalScore').textContent = score;
    document.getElementById('finalMoves').textContent = moves;
    document.getElementById('finalTime').textContent  = fmtTime(timerSec);
    document.getElementById('winMsg').textContent     = 'Selesai dalam ' + moves + ' langkah! ' + (moves <= PAIRS.length ? 'Luar biasa efisien!' : 'Bagus!');
    document.getElementById('overlayWin').classList.remove('hidden');

    // Report score to server
    fetch(FINISH_URL, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: new URLSearchParams({
            game_code:        GAME_CODE,
            set_id:           SET_ID,
            score:            score,
            max_score:        PAIRS.length * 100,
            duration_seconds: timerSec,
        })
    }).then(r => r.json()).then(d => {
        if (d.ok && d.points_earned > 0) {
            document.getElementById('winMsg').textContent += ` (+${d.points_earned} poin!)`;
        }
        if (d.new_badges && d.new_badges.length > 0) {
            showBadgeToast(d.new_badges[0]);
        }
    }).catch(() => {});
}

function showBadgeToast(badge) {
    const el = document.getElementById('badgeToast');
    document.getElementById('toastIcon').innerHTML = `<i class="${badge.icon}" style="color:${badge.color}"></i>`;
    document.getElementById('toastIcon').style.background = badge.color + '1a';
    document.getElementById('toastName').textContent = badge.name;
    el.classList.add('show');
    setTimeout(() => el.classList.remove('show'), 4000);
}

function escHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Init
renderBoard();

document.getElementById('btnStart').addEventListener('click', () => {
    document.getElementById('overlayStart').classList.add('hidden');
    started = true;
    timerSec = 0;
    startTimer();
});

document.getElementById('btnPlayAgain').addEventListener('click', () => {
    document.getElementById('overlayWin').classList.add('hidden');
    stopTimer();
    timerSec = 0;
    started = true;
    renderBoard();
    startTimer();
});
</script>
</body>
</html>
