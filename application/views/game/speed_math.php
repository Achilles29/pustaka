<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= html_escape($title); ?></title>
    <link rel="icon" href="<?= base_url('img/favicon.ico'); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,700;0,900;1,800&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box}
        body{font-family:'Plus Jakarta Sans',sans-serif;background:radial-gradient(circle at 50% -10%,#2563eb 0,transparent 31rem),radial-gradient(circle at 5% 92%,#4f46e5 0,transparent 24rem),#0b1225;min-height:100vh;margin:0;color:#fff;display:flex;flex-direction:column}
        .game-topbar{display:flex;align-items:center;justify-content:space-between;padding:13px max(20px,calc((100vw - 900px)/2));background:rgba(4,12,31,.63);border-bottom:1px solid rgba(255,255,255,.11);backdrop-filter:blur(14px)}
        .back{color:rgba(255,255,255,.78);text-decoration:none;font-size:.82rem;font-weight:700;display:flex;align-items:center;gap:6px}.back:hover{color:#fff}.game-name{display:flex;align-items:center;gap:7px;font-size:.82rem;font-weight:800;color:#dbeafe}.game-name b{display:grid;place-items:center;width:26px;height:26px;border-radius:8px;background:rgba(96,165,250,.2);font-size:1rem}
        .game-area{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:28px 16px 36px;gap:22px}

        /* Timer bar */
        .timer-bar-wrap{width:100%;max-width:530px;height:10px;background:rgba(255,255,255,.13);border-radius:999px;overflow:hidden;box-shadow:inset 0 1px 2px rgba(0,0,0,.3)}
        .timer-bar{height:100%;border-radius:999px;background:linear-gradient(90deg,#22c55e,#86efac);transition:width .95s linear,background .3s}
        .timer-bar.warn{background:linear-gradient(90deg,#f59e0b,#fbbf24)}
        .timer-bar.danger{background:linear-gradient(90deg,#ef4444,#f87171)}

        /* Score + time display */
        .hud{display:grid;grid-template-columns:repeat(3,minmax(75px,1fr));gap:10px;width:100%;max-width:410px;align-items:center}.hud-item{padding:10px 12px;text-align:center;border:1px solid rgba(255,255,255,.1);border-radius:14px;background:rgba(255,255,255,.06)}
        .hud-val{font-size:1.65rem;font-weight:900;line-height:1}.hud-label{font-size:.63rem;text-transform:uppercase;letter-spacing:.09em;opacity:.58;margin-top:4px}

        /* Question card */
        .q-card{position:relative;overflow:hidden;background:linear-gradient(145deg,rgba(30,58,138,.96),rgba(49,46,129,.96));border:1px solid rgba(191,219,254,.25);border-radius:28px;padding:42px 48px 35px;text-align:center;box-shadow:0 20px 52px rgba(0,0,0,.34);width:100%;max-width:530px}.q-card::before{content:'SELESAIKAN OPERASI';position:absolute;top:15px;left:0;right:0;color:rgba(219,234,254,.66);font-size:.62rem;font-weight:800;letter-spacing:.14em}.q-card::after{content:'';position:absolute;width:190px;height:190px;right:-96px;bottom:-108px;border:1px solid rgba(255,255,255,.12);border-radius:50%}
        .q-equation{position:relative;z-index:1;font-size:clamp(2.6rem,9vw,3.8rem);font-weight:900;letter-spacing:-.045em;margin:12px 0 29px;line-height:1}
        .q-equation .op{color:#93c5fd}
        .answer-input{position:relative;z-index:1;font-size:2.15rem;font-weight:800;text-align:center;background:rgba(255,255,255,.12);border:2px solid rgba(255,255,255,.28);border-radius:15px;padding:11px 20px;color:#fff;width:min(100%,220px);outline:none;font-family:inherit;-moz-appearance:textfield}
        .answer-input::placeholder{color:rgba(255,255,255,.3)}
        .answer-input:focus{border-color:#60a5fa;background:rgba(255,255,255,.18)}
        .answer-input::-webkit-inner-spin-button,.answer-input::-webkit-outer-spin-button{-webkit-appearance:none}

        /* Feedback flash */
        .feedback{font-size:2.35rem;height:44px;display:flex;align-items:center;justify-content:center;font-weight:900;transition:opacity .3s}
        .feedback.hidden{opacity:0}
        .feedback.correct{color:#4ade80}
        .feedback.wrong{color:#f87171}

        /* streak */
        .streak-bar{display:flex;gap:6px;height:8px}
        .streak-dot{width:8px;height:8px;border-radius:50%;background:rgba(255,255,255,.15);transition:background .2s}
        .streak-dot.lit{background:#fbbf24}

        /* Overlays */
        .overlay{position:fixed;inset:0;background:rgba(2,6,23,.79);display:flex;align-items:center;justify-content:center;z-index:100;backdrop-filter:blur(10px)}
        .overlay.hidden{display:none}
        .ov-card{background:linear-gradient(150deg,#fff,#eff6ff);color:#0f172a;border:1px solid rgba(255,255,255,.8);border-radius:24px;padding:34px 30px;max-width:390px;width:calc(100% - 28px);text-align:center;box-shadow:0 28px 70px rgba(0,0,0,.35)}
        .ov-card h2{font-size:2rem;font-weight:900;margin:0 0 8px}
        .ov-card p{color:#64748b;margin:0 0 20px}
        .btn-go{background:linear-gradient(135deg,#2563eb,#7c3aed);color:#fff;border:none;border-radius:12px;padding:14px 32px;font-size:1.05rem;font-weight:800;cursor:pointer;width:100%;margin-bottom:10px;font-family:inherit;box-shadow:0 10px 20px rgba(79,70,229,.22)}
        .btn-go:hover{opacity:.9}
        .btn-back2{background:#f1f5f9;color:#334155;border:none;border-radius:12px;padding:12px 32px;font-size:.95rem;font-weight:600;cursor:pointer;width:100%;font-family:inherit}
        .stats-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px}
        .stat-box{background:#fff;border:1px solid #dbeafe;border-radius:12px;padding:12px;text-align:center}
        .stat-box strong{display:block;font-size:1.5rem;font-weight:900}
        .stat-box span{font-size:.78rem;color:#64748b}
        .game-tip{max-width:530px;color:rgba(219,234,254,.72);font-size:.73rem;text-align:center;line-height:1.55}.game-tip kbd{padding:2px 5px;border:1px solid rgba(255,255,255,.2);border-radius:5px;background:rgba(255,255,255,.08);color:#fff;font-family:inherit;font-size:.68rem}@media(max-width:520px){.game-topbar{padding:11px 14px}.game-topbar>div:last-child{display:none}.game-area{justify-content:flex-start;padding-top:32px;gap:20px}.q-card{padding:40px 22px 30px;border-radius:23px}.hud{max-width:100%;gap:7px}.hud-item{padding:9px 6px}.hud-val{font-size:1.35rem}.answer-input{font-size:1.85rem}.streak-bar{margin-top:2px}}
    </style>
</head>
<body>

<?php
$duration   = (int) ($config['duration'] ?? 60);
$ops_raw    = $config['operators'] ?? ['+', '-'];
$ops        = is_array($ops_raw) ? $ops_raw : explode(',', $ops_raw);
$max_num    = (int) ($config['max_num'] ?? 20);
$level      = (int) ($config['level'] ?? 1);
$op_labels  = ['+'=>'Penjumlahan','-'=>'Pengurangan','×'=>'Perkalian','÷'=>'Pembagian'];
$ops_label  = implode(', ', array_map(fn($o) => $op_labels[$o] ?? $o, $ops));
?>

<div class="game-topbar">
    <a href="<?= base_url('belajar/play/speed_math'); ?>" class="back">← Pilih Level</a>
    <span class="game-name"><b>⚡</b>Level <?= $level; ?> · <?= html_escape($config['name']??'Hitung Cepat'); ?></span>
    <div></div>
</div>

<!-- Start Overlay -->
<div class="overlay" id="overlayStart">
    <div class="ov-card">
        <div style="font-size:3rem;margin-bottom:8px">⚡</div>
        <h2>Level <?= $level; ?>: <?= html_escape($config['name']??'Hitung Cepat'); ?></h2>
        <p>Jawab sebanyak mungkin soal dalam <strong><?= $duration; ?> detik</strong>.<br>
        Operasi: <?= html_escape($ops_label); ?><br>
        Angka maks: <?= $max_num; ?></p>
        <button class="btn-go" id="btnStart">Siap!</button>
        <button class="btn-back2" onclick="location.href='<?= base_url('belajar'); ?>'">Kembali</button>
    </div>
</div>

<!-- End Overlay -->
<div class="overlay hidden" id="overlayEnd">
    <div class="ov-card">
        <div style="font-size:3rem;margin-bottom:8px" id="endEmoji">🎉</div>
        <h2 id="endTitle">Waktu Habis!</h2>
        <div class="stats-grid">
            <div class="stat-box"><strong id="endScore">0</strong><span>Skor</span></div>
            <div class="stat-box"><strong id="endCorrect">0</strong><span>Benar</span></div>
            <div class="stat-box"><strong id="endWrong">0</strong><span>Salah</span></div>
            <div class="stat-box"><strong id="endStreak">0</strong><span>Streak Terbaik</span></div>
        </div>
        <p id="endMsg" style="margin-bottom:20px"></p>
        <?php if(!empty($next_level)): ?><button class="btn-go" onclick="location.href='<?= base_url('belajar/play/speed_math?level='.($level+1)); ?>'">Lanjut Level <?= $level+1; ?> →</button><?php elseif(!empty($next_game)): ?><button class="btn-go" onclick="location.href='<?= base_url($next_game['url']); ?>'">Coba <?= html_escape($next_game['name']); ?> →</button><?php endif; ?>
        <button class="btn-back2" onclick="location.href='<?= base_url('belajar/play/speed_math'); ?>'">Pilih Level Lain</button>
    </div>
</div>

<div class="game-area">
    <div class="timer-bar-wrap"><div class="timer-bar" id="timerBar" style="width:100%"></div></div>

    <div class="hud">
        <div class="hud-item"><div class="hud-val" id="timeLeft"><?= $duration; ?></div><div class="hud-label">Detik</div></div>
        <div class="hud-item"><div class="hud-val" id="scoreHud">0</div><div class="hud-label">Skor</div></div>
        <div class="hud-item"><div class="hud-val" id="streakHud">0🔥</div><div class="hud-label">Streak</div></div>
    </div>

    <div class="q-card">
        <div class="q-equation" id="equation">? <span class="op">+</span> ? <span style="color:#fff">=</span></div>
        <input type="number" class="answer-input" id="answerInput" placeholder="?" autocomplete="off" inputmode="numeric">
    </div>

    <div class="feedback hidden" id="feedback"></div>

    <div class="streak-bar" id="streakBar">
        <?php for ($i = 0; $i < 10; $i++): ?>
        <div class="streak-dot" id="sd<?= $i; ?>"></div>
        <?php endfor; ?>
    </div>
    <div class="game-tip">Ketik jawaban lalu tekan <kbd>Enter</kbd>. Jawaban benar beruntun memberi bonus skor.</div>
</div>

<script>
const DURATION    = <?= $duration; ?>;
const OPERATORS   = <?= json_encode($ops); ?>;
const MAX_NUM     = <?= $max_num; ?>;
const FINISH_URL  = '<?= base_url('belajar/finish'); ?>';
const GAME_CODE   = 'speed_math';
const GAME_LEVEL  = <?= $level; ?>;

let score=0, correct=0, wrong=0, streak=0, bestStreak=0;
let timeLeft=DURATION, timerInt=null, started=false;
let currentAnswer=0, feedbackTimeout=null;

function randInt(min, max) { return Math.floor(Math.random()*(max-min+1))+min; }

function nextQuestion() {
    const op = OPERATORS[Math.floor(Math.random()*OPERATORS.length)];
    let a, b;
    if (op === '÷') {
        b = randInt(1, Math.min(MAX_NUM, 12));
        a = b * randInt(1, Math.max(1, Math.floor(MAX_NUM/b)));
        currentAnswer = a / b;
    } else if (op === '×') {
        a = randInt(1, Math.min(MAX_NUM, 12));
        b = randInt(1, Math.min(MAX_NUM, 12));
        currentAnswer = a * b;
    } else if (op === '-') {
        a = randInt(1, MAX_NUM);
        b = randInt(0, a);
        currentAnswer = a - b;
    } else {
        a = randInt(0, MAX_NUM);
        b = randInt(0, MAX_NUM - a);
        currentAnswer = a + b;
    }
    const opSym = {'+':`<span class="op">+</span>`,'-':`<span class="op">−</span>`,'×':`<span class="op">×</span>`,'÷':`<span class="op">÷</span>`}[op] || op;
    document.getElementById('equation').innerHTML = a + ' ' + opSym + ' ' + b + ' <span style="color:#fff">=</span>';
    document.getElementById('answerInput').value = '';
    document.getElementById('answerInput').focus();
}

function showFeedback(type) {
    clearTimeout(feedbackTimeout);
    const el = document.getElementById('feedback');
    el.className = 'feedback ' + type;
    el.textContent = type === 'correct' ? '+✓' : '✗';
    feedbackTimeout = setTimeout(() => el.classList.add('hidden'), 600);
}

function updateStreakDots() {
    for (let i = 0; i < 10; i++) {
        document.getElementById('sd'+i).className = 'streak-dot' + (i < streak ? ' lit' : '');
    }
}

function startGame() {
    score=0; correct=0; wrong=0; streak=0; bestStreak=0;
    timeLeft = DURATION;
    document.getElementById('timerBar').style.width = '100%';
    document.getElementById('timerBar').className   = 'timer-bar';
    document.getElementById('scoreHud').textContent = '0';
    document.getElementById('streakHud').textContent = '0🔥';
    document.getElementById('timeLeft').textContent  = DURATION;
    updateStreakDots();
    nextQuestion();
    started = true;

    timerInt = setInterval(() => {
        timeLeft--;
        document.getElementById('timeLeft').textContent = timeLeft;
        const pct = (timeLeft / DURATION) * 100;
        const bar = document.getElementById('timerBar');
        bar.style.width = pct + '%';
        bar.className = 'timer-bar' + (pct < 20 ? ' danger' : pct < 40 ? ' warn' : '');
        if (timeLeft <= 0) endGame();
    }, 1000);
}

function endGame() {
    clearInterval(timerInt);
    started = false;
    document.getElementById('overlayEnd').classList.remove('hidden');
    const emoji = correct >= 20 ? '🔥' : correct >= 10 ? '🎉' : correct >= 5 ? '😊' : '💪';
    document.getElementById('endEmoji').textContent   = emoji;
    document.getElementById('endTitle').textContent   = correct >= 15 ? 'Luar Biasa!' : 'Waktu Habis!';
    document.getElementById('endScore').textContent   = score;
    document.getElementById('endCorrect').textContent = correct;
    document.getElementById('endWrong').textContent   = wrong;
    document.getElementById('endStreak').textContent  = bestStreak;
    document.getElementById('endMsg').textContent = `Kamu menjawab ${correct} soal dengan benar dari ${correct+wrong} soal.`;

    // Report
    fetch(FINISH_URL, {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
        body: new URLSearchParams({game_code:GAME_CODE,level:GAME_LEVEL,score:score,max_score:DURATION*5,duration_seconds:DURATION-timeLeft})
    }).then(r=>r.json()).then(d=>{
        if(d.ok && d.points_earned > 0) document.getElementById('endMsg').textContent += ` +${d.points_earned} poin diperoleh!`;
    }).catch(()=>{});
}

document.getElementById('answerInput').addEventListener('keydown', (e) => {
    if (!started) return;
    if (e.key === 'Enter' || e.key === 'Tab') {
        e.preventDefault();
        const val = parseInt(document.getElementById('answerInput').value, 10);
        if (isNaN(val)) return;
        if (val === currentAnswer) {
            streak++;
            bestStreak = Math.max(bestStreak, streak);
            const bonus = Math.min(streak, 5) * 2;
            score += 10 + bonus;
            correct++;
            showFeedback('correct');
        } else {
            streak = 0;
            wrong++;
            score = Math.max(0, score - 3);
            showFeedback('wrong');
        }
        document.getElementById('scoreHud').textContent  = score;
        document.getElementById('streakHud').textContent = streak + '🔥';
        updateStreakDots();
        nextQuestion();
    }
});

document.getElementById('btnStart').addEventListener('click', () => {
    document.getElementById('overlayStart').classList.add('hidden');
    startGame();
});

</script>
</body>
</html>
