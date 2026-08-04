<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= html_escape($attempt['session_title']); ?> — Quiz</title>
    <link rel="icon" href="<?= base_url('img/favicon.ico'); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/tabler-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { font-family:'Plus Jakarta Sans',sans-serif; background:#f4f6fb; user-select:none; -webkit-user-select:none; }
        .exam-header { background:#1a56a7; color:#fff; padding:12px 20px; display:flex; align-items:center; gap:16px; position:sticky; top:0; z-index:1000; box-shadow:0 2px 8px rgba(0,0,0,.3); }
        .exam-title { font-weight:700; font-size:1.1rem; flex:1; }
        .timer-box { background:rgba(0,0,0,.25); border-radius:8px; padding:6px 16px; font-size:1.4rem; font-weight:700; font-variant-numeric:tabular-nums; min-width:80px; text-align:center; }
        .timer-box.warning { background:#e67e22; }
        .timer-box.danger { background:#e74c3c; animation:pulse .5s ease-in-out infinite alternate; }
        @keyframes pulse { from{opacity:1} to{opacity:.7} }
        .exam-body { max-width:860px; margin:24px auto; padding:0 16px 80px; }
        .question-card { background:#fff; border-radius:14px; padding:24px; margin-bottom:20px; box-shadow:0 2px 8px rgba(0,0,0,.07); border:2px solid transparent; transition:border-color .15s; }
        .question-card.answered { border-color:#2ecc71; }
        .question-num { font-size:.8rem; font-weight:700; color:#999; text-transform:uppercase; letter-spacing:.05em; margin-bottom:8px; }
        .question-text { font-size:1.05rem; line-height:1.7; margin-bottom:18px; }
        .option-label { display:flex; align-items:flex-start; gap:12px; padding:10px 14px; border-radius:10px; border:2px solid #e9ecef; margin-bottom:8px; cursor:pointer; transition:all .15s; }
        .option-label:hover { border-color:#4a90d9; background:#f0f6ff; }
        .option-label input[type=radio] { display:none; }
        .option-label input[type=radio]:checked ~ * { color:#1a56a7; font-weight:600; }
        .option-label:has(input:checked) { border-color:#1a56a7; background:#e8f0fe; }
        .option-letter { width:28px; height:28px; border-radius:50%; background:#e9ecef; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.85rem; flex-shrink:0; transition:all .15s; }
        .option-label:has(input:checked) .option-letter { background:#1a56a7; color:#fff; }
        .essay-box { width:100%; min-height:140px; border:2px solid #e9ecef; border-radius:10px; padding:12px; font-family:inherit; font-size:1rem; resize:vertical; transition:border-color .15s; }
        .essay-box:focus { outline:none; border-color:#1a56a7; }
        .nav-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(38px,1fr)); gap:6px; }
        .nav-btn { width:38px; height:38px; border-radius:8px; border:2px solid #dee2e6; background:#fff; font-weight:600; font-size:.85rem; cursor:pointer; transition:all .15s; }
        .nav-btn.answered { background:#2ecc71; color:#fff; border-color:#2ecc71; }
        .nav-btn.current { border-color:#1a56a7; box-shadow:0 0 0 3px rgba(26,86,167,.2); }
        .sidebar-panel { position:fixed; right:20px; top:80px; width:220px; background:#fff; border-radius:14px; box-shadow:0 4px 20px rgba(0,0,0,.12); padding:16px; }
        .sidebar-panel h4 { font-size:.85rem; font-weight:700; color:#666; text-transform:uppercase; margin-bottom:10px; }
        .fraud-banner { position:fixed; bottom:0; left:0; right:0; background:#e74c3c; color:#fff; text-align:center; padding:12px; font-weight:700; transform:translateY(100%); transition:transform .3s; z-index:9999; }
        .fraud-banner.show { transform:translateY(0); }
        .submit-bar { position:fixed; bottom:0; left:0; right:0; background:#fff; border-top:1px solid #dee2e6; padding:12px 20px; display:flex; align-items:center; justify-content:space-between; z-index:100; box-shadow:0 -2px 8px rgba(0,0,0,.08); }
        @media (max-width:768px) { .sidebar-panel { display:none; } .exam-body { padding-bottom:80px; } }
    </style>
</head>
<body>

<div id="fraud-banner" class="fraud-banner"></div>

<!-- Header -->
<div class="exam-header">
    <div class="exam-title"><?= html_escape($attempt['session_title']); ?></div>
    <div class="text-white-50 small d-none d-md-block"><?= html_escape($attempt['participant_name']); ?></div>
    <?php if($time_limit > 0): ?>
    <div class="timer-box" id="timer">--:--</div>
    <?php else: ?>
    <div class="timer-box" id="timer-elapsed">00:00</div>
    <?php endif; ?>
</div>

<!-- Navigation sidebar (desktop) -->
<div class="sidebar-panel d-none d-lg-block">
    <h4>Navigasi Soal</h4>
    <div class="nav-grid" id="nav-grid">
        <?php foreach($questions as $i=>$q): ?>
        <button class="nav-btn <?= !empty($saved_answers[$q['id']]['selected_option']) || !empty($saved_answers[$q['id']]['essay_answer']) ? 'answered' : ''; ?> <?= $i===0?'current':''; ?>" id="nav-<?= $i; ?>" onclick="scrollToQuestion(<?= $i; ?>)"><?= $i+1; ?></button>
        <?php endforeach; ?>
    </div>
    <div class="mt-3 text-secondary" style="font-size:.8rem">
        <span class="me-2">🟢 Dijawab</span><span>⬜ Belum</span>
    </div>
    <hr>
    <div id="answered-count" class="fw-semibold text-center"><?= count(array_filter($saved_answers, fn($a) => $a['selected_option']!==null || !empty($a['essay_answer']))); ?> / <?= count($questions); ?></div>
    <div class="text-secondary text-center small">Soal Dijawab</div>
</div>

<!-- Exam Body -->
<div class="exam-body">
    <?php if($attempt['instructions']): ?>
    <div class="alert alert-info mb-4"><i class="ti ti-info-circle me-1"></i><?= nl2br(html_escape($attempt['instructions']??'')); ?></div>
    <?php endif; ?>

    <?php $letters = ['A','B','C','D','E']; ?>
    <?php foreach($questions as $i=>$q): ?>
    <div class="question-card <?= !empty($saved_answers[$q['id']]['selected_option']!==null && isset($saved_answers[$q['id']])) ? 'answered' : ''; ?>" id="q-card-<?= $i; ?>">
        <div class="question-num">Soal <?= $i+1; ?> dari <?= count($questions); ?></div>
        <div class="question-text"><?= nl2br(html_escape($q['question_text'])); ?></div>

        <?php if($q['type'] === 'multiple_choice'): ?>
        <?php $saved_opt = $saved_answers[$q['id']]['selected_option'] ?? null; ?>
        <div class="options-wrapper" data-qid="<?= $q['id']; ?>" data-qidx="<?= $i; ?>">
            <?php foreach($q['options'] as $j=>$opt):
                $real_idx = $opt['option_index'];
                $display_idx = $opt['display_index'] ?? $j;
            ?>
            <label class="option-label">
                <input type="radio" name="opt_<?= $q['id']; ?>" value="<?= $real_idx; ?>" <?= (string)$saved_opt === (string)$real_idx ? 'checked' : ''; ?> onchange="saveAnswer(<?= $q['id']; ?>, <?= $i; ?>, this.value, 'mc')">
                <span class="option-letter"><?= $letters[$display_idx]; ?></span>
                <span><?= html_escape($opt['option_text']); ?></span>
            </label>
            <?php endforeach; ?>
        </div>

        <?php else: ?>
        <textarea class="essay-box" id="essay-<?= $q['id']; ?>" placeholder="Tuliskan jawaban kamu di sini..." onpaste="logFraud('paste_attempt', <?= $q['id']; ?>)" oncopy="logFraud('copy_attempt', <?= $q['id']; ?>)"><?= html_escape($saved_answers[$q['id']]['essay_answer'] ?? ''); ?></textarea>
        <div class="d-flex justify-content-end mt-2">
            <button class="btn btn-sm btn-outline-primary" onclick="saveEssay(<?= $q['id']; ?>, <?= $i; ?>)"><i class="ti ti-device-floppy me-1"></i>Simpan Jawaban</button>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<!-- Submit Bar -->
<div class="submit-bar">
    <div class="text-secondary small d-none d-md-block"><span id="bottom-count">0</span> / <?= count($questions); ?> soal dijawab</div>
    <button class="btn btn-danger btn-lg fw-bold ms-auto" onclick="confirmSubmit()">
        <i class="ti ti-send me-1"></i>Kumpulkan Jawaban
    </button>
</div>

<!-- Submit Confirm Modal -->
<div class="modal fade" id="modal-submit" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0"><h5 class="modal-title">Kumpulkan Jawaban?</h5></div>
            <div class="modal-body pt-0">
                <p>Kamu telah menjawab <strong id="confirm-count">0</strong> dari <strong><?= count($questions); ?></strong> soal.</p>
                <p class="text-danger small">Setelah dikumpulkan, jawaban tidak bisa diubah lagi.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Periksa Lagi</button>
                <button type="button" class="btn btn-danger fw-bold" onclick="doSubmit()"><i class="ti ti-send me-1"></i>Kumpulkan</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js"></script>
<script>
const TOKEN       = '<?= html_escape($token); ?>';
const BASE        = '<?= base_url(); ?>';
const CSRF_NAME   = '<?= $this->security->get_csrf_token_name(); ?>';
const ATTEMPT_ID  = <?= $attempt['id']; ?>;
const TIME_LIMIT  = <?= $time_limit; ?>;        // seconds; 0 = no limit
let   remaining   = <?= $remaining; ?>;          // -1 = no limit
const DETECT_TAB  = <?= $attempt['fraud_detect_tab_switch'] ? 'true' : 'false'; ?>;
const MAX_TAB     = <?= $attempt['fraud_max_tab_switches']; ?>;
const FRAUD_ACTION= '<?= html_escape($attempt['fraud_action']); ?>';
let   tabSwitches = 0;
let   elapsedSec  = <?= max(0, ($time_limit - $remaining)); ?>;
let   submitInProgress = false;
let   answered    = {};

// ── Anti-fraud: disable right-click & text selection ──────────────────────
document.addEventListener('contextmenu', e => { e.preventDefault(); logFraud('right_click'); });
document.addEventListener('keydown', e => {
    if ((e.ctrlKey||e.metaKey) && ['c','v','u','a','s'].includes(e.key.toLowerCase())) {
        e.preventDefault();
        if (e.key.toLowerCase() === 'c') logFraud('copy_attempt');
        if (e.key.toLowerCase() === 'v') logFraud('paste_attempt');
    }
    // Detect DevTools shortcut
    if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && ['i','j','c'].includes(e.key.toLowerCase()))) {
        e.preventDefault(); logFraud('devtools_open');
    }
});

// ── Anti-fraud: tab/window visibility ─────────────────────────────────────
document.addEventListener('visibilitychange', () => {
    if (document.hidden && DETECT_TAB) {
        tabSwitches++;
        logFraud('tab_switch', null, {count: tabSwitches}).then(res => {
            if (res && res.result && res.result.action === 'disqualified') {
                alert('Anda telah didiskualifikasi karena pindah tab terlalu sering.');
                location.href = BASE + 'quiz/result/' + ATTEMPT_ID;
            } else if (res && res.result && res.result.action !== 'logged' && tabSwitches >= MAX_TAB) {
                showFraudBanner(`⚠️ Peringatan ke-${tabSwitches}: Jangan pindah tab! (Batas: ${MAX_TAB}x)`);
            }
        });
    }
});
window.addEventListener('blur', () => {
    if (DETECT_TAB) logFraud('window_blur');
});

// ── Timer ─────────────────────────────────────────────────────────────────
function updateTimer() {
    if (TIME_LIMIT <= 0) {
        const el = document.getElementById('timer-elapsed');
        if (el) el.textContent = formatTime(elapsedSec);
        elapsedSec++;
        return;
    }
    const el = document.getElementById('timer');
    if (!el) return;
    el.textContent = formatTime(remaining);
    if (remaining <= 0) {
        el.className = 'timer-box danger';
        doSubmit(true);
        return;
    }
    if (remaining <= 60) el.className = 'timer-box danger';
    else if (remaining <= 300) el.className = 'timer-box warning';
    else el.className = 'timer-box';
    remaining--;
    elapsedSec++;
}
function formatTime(s) {
    const m = Math.floor(s/60), sec = s % 60;
    return String(m).padStart(2,'0') + ':' + String(sec).padStart(2,'0');
}
setInterval(updateTimer, 1000);
updateTimer();

// ── Heartbeat every 30s ───────────────────────────────────────────────────
setInterval(async () => {
    try {
        const fd = new FormData();
        fd.append('token', TOKEN);
        fd.append(CSRF_NAME, getCsrf());
        const r = await fetch(BASE + 'quiz/heartbeat', {method:'POST', body:fd});
        const d = await r.json();
        if (d.action === 'submit' || d.action === 'stop') {
            doSubmit(true);
        } else if (d.remaining !== undefined && d.remaining >= 0) {
            remaining = d.remaining;
        }
    } catch(e) {}
}, 30000);

// ── Save Answer ───────────────────────────────────────────────────────────
let saveQueue = {};
function saveAnswer(qid, qidx, value, type, timeSpent = 0) {
    answered[qid] = true;
    updateNavBtn(qidx, true);
    updateAnsweredCount();
    clearTimeout(saveQueue[qid]);
    saveQueue[qid] = setTimeout(() => doSaveAnswer(qid, value, null, timeSpent), 600);
}
function saveEssay(qid, qidx) {
    const val = document.getElementById('essay-' + qid).value;
    if (val.trim()) answered[qid] = true;
    else delete answered[qid];
    updateNavBtn(qidx, !!val.trim());
    updateAnsweredCount();
    doSaveAnswer(qid, null, val, 0);
}
async function doSaveAnswer(qid, selected, essay, timeSpent) {
    const fd = new FormData();
    fd.append('token', TOKEN);
    fd.append('question_id', qid);
    if (selected !== null) fd.append('selected_option', selected);
    if (essay !== null) fd.append('essay_answer', essay);
    fd.append('time_spent', timeSpent);
    fd.append(CSRF_NAME, getCsrf());
    try {
        await fetch(BASE + 'quiz/save_answer', {method:'POST', body:fd});
    } catch(e) {}
}

// ── UI helpers ────────────────────────────────────────────────────────────
function updateNavBtn(idx, isAnswered) {
    const btn = document.getElementById('nav-' + idx);
    if (!btn) return;
    btn.classList.toggle('answered', isAnswered);
}
function updateAnsweredCount() {
    const count = Object.keys(answered).length;
    const el = document.getElementById('answered-count');
    if (el) el.textContent = count + ' / <?= count($questions); ?>';
    const bot = document.getElementById('bottom-count');
    if (bot) bot.textContent = count;
    document.getElementById('confirm-count').textContent = count;
}
function scrollToQuestion(idx) {
    document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('current'));
    document.getElementById('nav-' + idx).classList.add('current');
    document.getElementById('q-card-' + idx).scrollIntoView({behavior:'smooth', block:'start'});
}
function showFraudBanner(msg) {
    const el = document.getElementById('fraud-banner');
    el.textContent = msg;
    el.classList.add('show');
    setTimeout(() => el.classList.remove('show'), 5000);
}

// ── Fraud log ─────────────────────────────────────────────────────────────
async function logFraud(eventType, qid = null, detail = null) {
    const fd = new FormData();
    fd.append('token', TOKEN);
    fd.append('event_type', eventType);
    if (qid) fd.append('question_id', qid);
    if (detail) fd.append('detail', JSON.stringify(detail));
    fd.append(CSRF_NAME, getCsrf());
    try {
        const r = await fetch(BASE + 'quiz/fraud_event', {method:'POST', body:fd});
        return await r.json();
    } catch(e) { return null; }
}

// ── Submit ────────────────────────────────────────────────────────────────
function confirmSubmit() {
    document.getElementById('confirm-count').textContent = Object.keys(answered).length;
    new bootstrap.Modal(document.getElementById('modal-submit')).show();
}
async function doSubmit(auto = false) {
    if (submitInProgress) return;
    submitInProgress = true;
    const fd = new FormData();
    fd.append('token', TOKEN);
    fd.append('time_spent', elapsedSec);
    fd.append(CSRF_NAME, getCsrf());
    try {
        const r = await fetch(BASE + 'quiz/submit', {method:'POST', body:fd});
        const d = await r.json();
        if (d.ok) {
            window.onbeforeunload = null;
            location.href = d.redirect;
        }
    } catch(e) {
        submitInProgress = false;
        if (!auto) alert('Gagal mengumpulkan. Coba lagi.');
    }
}

function getCsrf() {
    const m = document.cookie.match(/ci_csrf_token=([^;]+)/);
    return m ? m[1] : '';
}

// Warn before leaving
window.onbeforeunload = () => 'Jawaban kamu belum dikumpulkan. Yakin ingin meninggalkan halaman?';

// Initialize answered map from saved answers
<?php foreach($saved_answers as $qid => $ans): ?>
<?php if($ans['selected_option'] !== null || !empty($ans['essay_answer'])): ?>
answered[<?= (int)$qid; ?>] = true;
<?php endif; ?>
<?php endforeach; ?>
updateAnsweredCount();
</script>
</body>
</html>
