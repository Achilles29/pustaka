(() => {
    'use strict';
    const root = document.querySelector('[data-rpg-world]');
    const data = window.RPG_WORLD || {};
    if (!root || !data.map) return;
    const map = data.map;
    let state = data.state || {};
    let questState = data.quest_state || { status: 'not_started', progress: 0, visited: [] };
    let hero = JSON.parse(window.localStorage.getItem('englishQuestHero') || 'null') || { name: 'Lantern', skin: 'teal' };
    let busy = false;
    let walking = false;
    let pendingDirection = null;
    let selectedAnswer = null;
    let tapTarget = null;
    let tapTimer = null;
    let proximityNpcId = null;
    let proximityHotspotCode = null;
    let speechVoices = [];
    // The sprite sheet is ordered male, female, male, female. Keep this
    // mapping explicit so voice selection follows the character the player
    // actually sees, rather than relying on an NPC name or browser default.
    const npcVoiceGender = {
        elder_mira: 'male', gardener_eli: 'female', trader_nova: 'male', scout_ren: 'female',
        courier_pip: 'male', innkeeper_tessa: 'female', smith_bram: 'male', gate_orin: 'female'
    };
    const $ = id => document.getElementById(id);
    const csrfName = root.dataset.csrfName || '';
    const csrfHash = root.dataset.csrfHash || '';

    function escapeHtml(value) { const el = document.createElement('div'); el.textContent = String(value ?? ''); return el.innerHTML; }
    function refreshSpeechVoices() {
        if (!window.speechSynthesis) return;
        speechVoices = window.speechSynthesis.getVoices().filter(voice => /^en(?:-|_)/i.test(voice.lang || ''));
    }
    function speakerGender(speaker) {
        if (speaker && (speaker.gender === 'male' || speaker.gender === 'female')) return speaker.gender;
        if (speaker && npcVoiceGender[speaker.code]) return npcVoiceGender[speaker.code];
        // Lantern is drawn with the male hero sprite by default. A future
        // hero profile can override this by saving `gender` in localStorage.
        const saved = hero && hero.gender;
        return saved === 'female' ? 'female' : 'male';
    }
    function preferredSpeechVoice(gender) {
        const femaleNames = ['jenny', 'samantha', 'aria', 'ava', 'zira', 'karen', 'susan', 'hazel', 'female', 'woman', 'girl'];
        const maleNames = ['guy', 'daniel', 'alex', 'aaron', 'david', 'mark', 'george', 'male', 'man', 'boy'];
        const targetNames = gender === 'female' ? femaleNames : maleNames;
        const oppositeNames = gender === 'female' ? maleNames : femaleNames;
        const score = voice => {
            const name = String(voice.name || '').toLowerCase(); let points = 0;
            if (name.includes('natural') || name.includes('neural')) points += 100;
            if (targetNames.some(token => name.includes(token))) points += 70;
            if (oppositeNames.some(token => name.includes(token))) points -= 75;
            if (name.includes('google us english')) points += 30;
            if (name.includes('microsoft')) points += 12;
            if (voice.lang && voice.lang.toLowerCase() === 'en-us') points += 8;
            if (voice.default) points += 1;
            return points;
        };
        return speechVoices.slice().sort((a, b) => score(b) - score(a))[0] || null;
    }
    refreshSpeechVoices();
    if (window.speechSynthesis) {
        if (typeof window.speechSynthesis.addEventListener === 'function') window.speechSynthesis.addEventListener('voiceschanged', refreshSpeechVoices);
        else window.speechSynthesis.onvoiceschanged = refreshSpeechVoices;
    }
    function npcAt(npcId) { return (map.npcs || []).find(npc => Number(npc.id) === Number(npcId)); }
    function isNearby(npc) { return Math.abs(Number(npc.x) - Number(state.x)) + Math.abs(Number(npc.y) - Number(state.y)) <= 1; }
    function isPointNearby(point) { return Math.abs(Number(point.x) - Number(state.x)) + Math.abs(Number(point.y) - Number(state.y)) <= 1; }
    function directionClass() { return ['up', 'down', 'left', 'right'].includes(state.direction) ? state.direction : 'down'; }
    function heroSkinClass() { return ['teal', 'ember', 'violet'].includes(hero.skin) ? 'skin-' + hero.skin : 'skin-teal'; }

    function clearTapTarget() {
        tapTarget = null;
        window.clearTimeout(tapTimer);
    }

    function walkableCell(x, y, targetNpcId = null) {
        if (x < 0 || y < 0 || x >= Number(map.width) || y >= Number(map.height)) return false;
        const tile = String((map.layout || [])[y] || '')[x] || '#';
        if (tile === '#' || tile === 'T' || tile === 'B') return false;
        return !(map.npcs || []).some(npc => Number(npc.x) === x && Number(npc.y) === y && Number(npc.id) !== Number(targetNpcId));
    }

    // Follow a short breadth-first route instead of trying to walk in a
    // straight line. This lets a tap on Pip, Tessa, or a story object route
    // around houses, fences, the fountain, and other villagers.
    function nextDirectionToTapTarget() {
        if (!tapTarget) return null;
        const start = { x: Number(state.x), y: Number(state.y) };
        const targetNpcId = tapTarget.npcId ? Number(tapTarget.npcId) : null;
        const target = { x: Number(tapTarget.x), y: Number(tapTarget.y) };
        const goals = targetNpcId
            ? (map.npcs || []).filter(npc => Number(npc.id) === targetNpcId).flatMap(npc => [
                { x: Number(npc.x) - 1, y: Number(npc.y) }, { x: Number(npc.x) + 1, y: Number(npc.y) },
                { x: Number(npc.x), y: Number(npc.y) - 1 }, { x: Number(npc.x), y: Number(npc.y) + 1 },
            ]).filter(point => walkableCell(point.x, point.y, targetNpcId))
            : [target].filter(point => walkableCell(point.x, point.y));
        const goalKeys = new Set(goals.map(point => `${point.x},${point.y}`));
        if (goalKeys.has(`${start.x},${start.y}`)) return null;
        const queue = [start]; const seen = new Set([`${start.x},${start.y}`]); const previous = new Map();
        const directions = [['up', 0, -1], ['down', 0, 1], ['left', -1, 0], ['right', 1, 0]];
        let destination = null;
        while (queue.length) {
            const current = queue.shift();
            for (const [direction, dx, dy] of directions) {
                const next = { x: current.x + dx, y: current.y + dy }; const key = `${next.x},${next.y}`;
                if (seen.has(key) || !walkableCell(next.x, next.y, targetNpcId)) continue;
                seen.add(key); previous.set(key, { key: `${current.x},${current.y}`, direction });
                if (goalKeys.has(key)) { destination = key; break; }
                queue.push(next);
            }
            if (destination) break;
        }
        if (!destination) return null;
        let step = destination;
        while (previous.has(step) && previous.get(step).key !== `${start.x},${start.y}`) step = previous.get(step).key;
        return previous.get(step)?.direction || null;
    }

    function followTapTarget() {
        if (!tapTarget || busy || !$('worldDialogue').hidden) return;
        const targetNpc = tapTarget.npcId ? npcAt(tapTarget.npcId) : null;
        if (targetNpc && isNearby(targetNpc)) {
            clearTapTarget();
            showProximityPrompt(targetNpc);
            return;
        }
        const targetHotspot = tapTarget.hotspotCode ? (map.hotspots || []).find(item => (item.action_code || item.code) === tapTarget.hotspotCode) : null;
        if (targetHotspot && isPointNearby(targetHotspot)) {
            clearTapTarget();
            showHotspotPrompt(targetHotspot);
            return;
        }
        const direction = nextDirectionToTapTarget();
        if (!direction) { clearTapTarget(); return; }
        move(direction, true);
    }

    function actorPercent(value, size) { return `${(Number(value) + .5) / Number(size) * 100}%`; }

    function renderMap(motion = null) {
        const grid = $('worldMap'); grid.style.setProperty('--map-width', map.width); grid.style.setProperty('--map-height', map.height); grid.innerHTML = '';
        (map.layout || []).forEach((row, y) => [...String(row)].forEach((tile, x) => {
            const cell = document.createElement('div'); cell.className = 'world-tile'; cell.style.gridColumn = x + 1; cell.style.gridRow = y + 1; cell.setAttribute('aria-hidden', 'true'); grid.appendChild(cell);
        }));
        (map.npcs || []).forEach((npc, index) => {
            const actor = document.createElement('button'); actor.type = 'button'; actor.className = 'world-actor npc' + (isNearby(npc) ? ' nearby' : ''); actor.style.setProperty('--actor-x', npc.x); actor.style.setProperty('--actor-y', npc.y); actor.style.left = actorPercent(npc.x, map.width); actor.style.top = actorPercent(npc.y, map.height); actor.dataset.npcId = npc.id; actor.title = 'Talk to ' + npc.name;
            actor.innerHTML = `<span class="npc-sprite npc-${(index % 4) + 1}"></span><span class="actor-label">${escapeHtml(npc.name)}</span>`; grid.appendChild(actor);
        });
        (map.hotspots || []).filter(hotspot => questState.status === 'active' && (hotspot.action_code || hotspot.code) === questState.stage).forEach(hotspot => {
            const actionCode = hotspot.action_code || hotspot.code;
            const marker = document.createElement('button'); marker.type = 'button'; marker.className = 'world-hotspot' + (isPointNearby(hotspot) ? ' nearby' : ''); marker.style.setProperty('--actor-x', hotspot.x); marker.style.setProperty('--actor-y', hotspot.y); marker.dataset.hotspotCode = actionCode; marker.dataset.hotspotType = hotspot.type || 'action'; marker.title = hotspot.description || hotspot.name; marker.innerHTML = `<span>${escapeHtml(hotspot.emoji || '✦')}</span><span class="hotspot-label">${escapeHtml(hotspot.name || 'Objective')}</span>`; grid.appendChild(marker);
        });
        if (questState.status === 'claimed') {
            const exit = document.createElement('button'); exit.type = 'button'; exit.className = 'world-exit-arrow'; exit.dataset.nextRoute = 'whispering_grove'; exit.title = 'Continue north to Whispering Grove'; exit.innerHTML = '<span>↑</span><small>Whispering Grove</small>'; grid.appendChild(exit);
        }
        const player = document.createElement('div'); player.className = `world-actor player ${heroSkinClass()}${walking ? ' walking' : ''}`; player.style.setProperty('--actor-x', state.x); player.style.setProperty('--actor-y', state.y); player.style.left = actorPercent(state.x, map.width); player.style.top = actorPercent(state.y, map.height); if (motion && motion.from && motion.to) { player.classList.add('walking-slide'); player.style.setProperty('--from-left', actorPercent(motion.from.x, map.width)); player.style.setProperty('--from-top', actorPercent(motion.from.y, map.height)); player.style.setProperty('--to-left', actorPercent(motion.to.x, map.width)); player.style.setProperty('--to-top', actorPercent(motion.to.y, map.height)); } player.innerHTML = `<span class="hero-sprite facing-${directionClass()}"></span><span class="actor-label">${escapeHtml(hero.name)}</span>`; grid.appendChild(player);
        renderNearby();
        updateProximityPrompt();
    }

    function renderNearby() {
        const list = $('nearbyList'); if (!list) return; const nearby = (map.npcs || []).filter(isNearby);
        list.innerHTML = nearby.length ? nearby.map(npc => `<button type="button" class="nearby-btn" data-talk="${npc.id}"><span class="npc-mini">✦</span><span>Talk to ${escapeHtml(npc.name)}</span></button>`).join('') : '<small class="text-secondary">Explore the village and approach a glowing character.</small>';
    }

    function hideProximityPrompt() {
        proximityNpcId = null;
        proximityHotspotCode = null;
        const prompt = $('worldProximityPrompt');
        if (prompt) prompt.hidden = true;
    }

    function showProximityPrompt(npc) {
        if (!npc || !$('worldDialogue').hidden) return;
        proximityNpcId = Number(npc.id);
        const prompt = $('worldProximityPrompt');
        if (!prompt) return;
        $('proximityName').textContent = npc.name || 'Villager';
        const avatar = $('proximityAvatar');
        const npcIndex = (map.npcs || []).findIndex(item => Number(item.id) === Number(npc.id));
        avatar.className = 'proximity-avatar'; avatar.textContent = '';
        avatar.style.backgroundPosition = ['0 0', '100% 0', '0 100%', '100% 100%'][Math.max(0, npcIndex) % 4];
        $('worldProximityPrompt').querySelector('.proximity-copy small').textContent = 'Nearby · tap to talk';
        $('worldProximityPrompt').querySelector('.proximity-arrow').textContent = '›';
        prompt.hidden = false;
    }

    function showHotspotPrompt(hotspot) {
        if (!hotspot || !$('worldDialogue').hidden) return;
        proximityNpcId = null; proximityHotspotCode = hotspot.action_code || hotspot.code;
        const prompt = $('worldProximityPrompt'); if (!prompt) return;
        $('proximityName').textContent = hotspot.name || 'Objective';
        const avatar = $('proximityAvatar'); avatar.className = 'proximity-avatar proximity-hotspot-avatar'; avatar.textContent = hotspot.emoji || '✦'; avatar.style.backgroundImage = 'none'; avatar.style.backgroundPosition = 'center';
        prompt.querySelector('.proximity-copy small').textContent = 'Objective nearby · tap to inspect';
        prompt.querySelector('.proximity-arrow').textContent = '›'; prompt.hidden = false;
    }

    function updateProximityPrompt() {
        if (!$('worldDialogue').hidden) { hideProximityPrompt(); return; }
        const npc = (map.npcs || []).find(isNearby);
        if (npc) { showProximityPrompt(npc); return; }
        const hotspot = (map.hotspots || []).find(item => (item.action_code || item.code) === questState.stage && isPointNearby(item));
        if (hotspot) showHotspotPrompt(hotspot); else hideProximityPrompt();
    }

    function walkToNpc(npcId) {
        const npc = npcAt(npcId);
        if (!npc) return;
        if (isNearby(npc)) { showProximityPrompt(npc); return; }
        tapTarget = { x: Number(npc.x), y: Number(npc.y), npcId: Number(npc.id) };
        followTapTarget();
    }

    function walkToHotspot(code) {
        const hotspot = (map.hotspots || []).find(item => (item.action_code || item.code) === String(code));
        if (!hotspot) return;
        if (isPointNearby(hotspot)) { showHotspotPrompt(hotspot); return; }
        tapTarget = { x: Number(hotspot.x), y: Number(hotspot.y), hotspotCode: hotspot.action_code || hotspot.code };
        followTapTarget();
    }

    function openProximityConversation() {
        if (proximityHotspotCode !== null) return performAction(proximityHotspotCode);
        if (proximityNpcId === null) return;
        const npc = npcAt(proximityNpcId);
        if (npc && isNearby(npc)) interact(proximityNpcId);
        else if (npc) walkToNpc(proximityNpcId);
    }

    function renderQuest() {
        const quest = data.quest; if (!quest) return;
        const target = Number(questState.total_objectives || 6); const progress = Number(questState.progress || 0); const visited = questState.visited || [];
        const status = { not_started: 'Not started', active: 'In progress', completed: 'Ready to turn in', claimed: 'Completed' }[questState.status] || questState.status;
        $('questStatus').textContent = status; $('questProgressText').textContent = `${progress}/${target} acts`; $('questProgressBar').style.width = `${Math.min(100, progress / target * 100)}%`;
        const objectives = [
            { code: 'arrival', name: 'Darkened Archive' }, { code: 'root', name: 'Root Remembers' }, { code: 'promise', name: 'Promise Market' },
            { code: 'crossing', name: 'Crossing Trail' }, { code: 'repair', name: 'Broken Case' }, { code: 'gate', name: 'Northern Gate' },
        ];
        $('questSteps').innerHTML = objectives.map((item, index) => `<li class="${index < progress || questState.status === 'claimed' ? 'done' : ''}">${escapeHtml(item.name)}</li>`).join('');
        const pendingNpc = questState.question && (map.npcs || []).find(item => item.code === questState.question.npc_code);
        const next = questState.status === 'not_started' ? 'Find Mira and accept the quest.' : questState.status === 'active' ? (pendingNpc ? `Find ${pendingNpc.name} and answer the question.` : (questState.objective?.short || 'Follow the glowing objective.')) : questState.status === 'completed' ? 'Return to Mira to claim your reward.' : 'Chapter 1 is complete. The path to Whispering Grove is now open.';
        $('questNextAction').textContent = next; $('worldXp').textContent = Number(state.world_xp || 0) + ' XP';
        $('chapterEnding').hidden = questState.status !== 'claimed';
    }

    function toast(message) { const node = $('worldToast'); node.textContent = message; node.classList.add('show'); window.clearTimeout(toast.timer); toast.timer = window.setTimeout(() => node.classList.remove('show'), 2600); }

    function speak(text, button, speaker) {
        if (!window.speechSynthesis || !window.SpeechSynthesisUtterance) { toast('Speech playback is not available in this browser.'); return; }
        refreshSpeechVoices();
        window.speechSynthesis.cancel(); const utterance = new SpeechSynthesisUtterance(String(text || '')); const voice = preferredSpeechVoice(speakerGender(speaker));
        if (voice) { utterance.voice = voice; utterance.lang = voice.lang || 'en-US'; } else utterance.lang = 'en-US';
        utterance.rate = voice && /natural|neural/i.test(voice.name || '') ? .9 : .84; utterance.pitch = .98; utterance.volume = 1;
        const old = button ? button.innerHTML : ''; if (button) button.innerHTML = '🔊 Playing…'; utterance.onend = () => { if (button) button.innerHTML = old; }; window.speechSynthesis.speak(utterance);
    }

    function showDialogue(npc, result) {
        hideProximityPrompt();
        const lines = npc.dialogue || []; const ending = result && result.ending; const first = ending ? { en: ending.message } : (result && result.dialogue_line ? { en: result.dialogue_line } : (lines[0] || { en: npc.greeting })); const questionPreview = result && Object.prototype.hasOwnProperty.call(result, 'question') ? result.question : (result && result.quest_state && result.quest_state.question); const second = ending ? { en: ending.next } : (result && result.dialogue_line ? {} : (result && result.correct && result.feedback && !questionPreview ? { en: result.feedback } : (lines[1] || {})));
        $('dialogueAvatar').className = 'dialogue-avatar npc-dialogue-' + Math.min(4, Math.max(1, (map.npcs || []).findIndex(item => Number(item.id) === Number(npc.id)) + 1));
        $('dialogueName').textContent = npc.name; $('dialogueRole').textContent = npc.role || ''; $('dialogueLine').textContent = first.en || npc.greeting; $('dialogueExtra').textContent = second.en || '';
        $('dialogueGlossary').innerHTML = ending ? `<b>${escapeHtml(ending.title)}</b>` : (npc.vocabulary_word ? `New word: <b>${escapeHtml(npc.vocabulary_word)}</b> — ${escapeHtml(npc.vocabulary_meaning || 'a useful English word')}` : '');
        const question = questionPreview;
        const questionBox = $('dialogueQuestion');
        const feedback = $('dialogueFeedback');
        const dialogueHelpText = $('dialogueHelpText');
        if (dialogueHelpText) { dialogueHelpText.hidden = true; dialogueHelpText.textContent = ''; }
        selectedAnswer = null;
        if (questionBox) {
            delete questionBox.dataset.answer;
            questionBox.hidden = !question;
            if (question) {
                questionBox.dataset.npcId = npc.id;
                $('dialoguePrompt').textContent = question.prompt;
                $('dialogueOptions').innerHTML = (question.options || []).map((option, index) => `<label class="dialogue-option" data-dialogue-option="${index}"><input class="dialogue-choice-input" type="radio" name="dialogue_answer" value="${index}"><span>${escapeHtml(option)}</span></label>`).join('');
                const answerButton = $('submitDialogueAnswer');
                answerButton.disabled = false;
                answerButton.onclick = event => { event.stopPropagation(); answerQuestion(); };
                const options = $('dialogueOptions');
                options.onchange = event => {
                    const input = event.target.closest('input[name="dialogue_answer"]');
                    if (!input) return;
                    selectedAnswer = Number(input.value);
                    questionBox.dataset.answer = String(selectedAnswer);
                    options.querySelectorAll('.dialogue-option').forEach(label => label.classList.toggle('selected', label.contains(input)));
                    // Selecting an option is the submission action. A short
                    // delay lets the native radio state paint before the
                    // dialog is refreshed by the response.
                    window.setTimeout(() => answerQuestion(), 70);
                };
            }
        }
        if (feedback) {
            feedback.hidden = !(result && result.feedback);
            feedback.textContent = result && result.feedback ? result.feedback : '';
            feedback.classList.toggle('is-wrong', result && result.correct === false);
            feedback.classList.toggle('is-correct', result && result.correct === true);
        }
        $('worldDialogue').hidden = false; $('listenDialogue').onclick = () => speak([first.en, second.en].filter(Boolean).join(' '), $('listenDialogue'), npc); if (result && result.message && result.message !== first.en) toast(result.message);
    }

    async function post(url, values) {
        const body = new FormData(); Object.entries(values).forEach(([key, value]) => body.append(key, value)); if (csrfName) body.append(csrfName, csrfHash);
        const response = await fetch(url, { method: 'POST', body, headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' } }); const raw = await response.text(); let json;
        try { json = JSON.parse(raw); } catch (error) { throw new Error('The server did not return JSON. Please reload the page.'); }
        if (!response.ok || !json.ok) throw new Error(json.message || 'This action could not be saved.'); return json;
    }

    async function move(direction, fromTap = false) {
        if (!fromTap) clearTapTarget();
        if (busy) { if (!fromTap) pendingDirection = direction; state.direction = direction; renderMap(); return; }
        busy = true;
        state.direction = direction;
        walking = true;
        renderMap();
        let moved = false;
        try {
            const result = await post(root.dataset.moveUrl, { direction });
            const from = { x: state.x, y: state.y };
            state = result.state || state;
            moved = Boolean(result.moved);
            walking = moved;
            renderMap(moved ? { from, to: state } : null);
            if (moved) {
                window.setTimeout(() => { walking = false; renderMap(); }, 360);
            } else {
                walking = false;
                if (fromTap) clearTapTarget();
                toast('That path is blocked. Try another direction.');
            }
        } catch (error) {
            if (fromTap) clearTapTarget();
            toast(error.message);
        }
        busy = false;
        if (pendingDirection) {
            const nextDirection = pendingDirection;
            pendingDirection = null;
            window.setTimeout(() => move(nextDirection), 0);
        } else if (fromTap && moved && tapTarget) {
            tapTimer = window.setTimeout(followTapTarget, 95);
        }
    }

    async function interact(npcId) {
        if (busy) return; busy = true;
        try { const result = await post(root.dataset.interactUrl, { npc_id: npcId }); state = result.state || state; questState = result.quest_state || questState; renderMap(); renderQuest(); showDialogue(result.npc || npcAt(npcId), result); if (result.claimed && result.reward) toast(`Chapter 1 complete! +${result.reward.xp} XP`); }
        catch (error) { toast(error.message); } busy = false;
    }

    async function answerQuestion() {
        const box = $('dialogueQuestion');
        if (!box || busy) return;
        if (selectedAnswer === null && box.dataset.answer !== undefined) selectedAnswer = Number(box.dataset.answer);
        if (selectedAnswer === null) {
            const checked = document.querySelector('#dialogueOptions input[name="dialogue_answer"]:checked');
            if (checked) selectedAnswer = Number(checked.value);
        }
        if (selectedAnswer === null || Number.isNaN(selectedAnswer)) { toast('Choose an answer first.'); return; }
        busy = true;
        try {
            const result = await post(root.dataset.answerUrl, { npc_id: box.dataset.npcId, answer: selectedAnswer });
            state = result.state || state; questState = result.quest_state || questState; renderMap(); renderQuest();
            const currentNpc = npcAt(box.dataset.npcId) || result.npc;
            const nextNpc = result.npc || currentNpc;
            // A correct answer unlocks the next destination, but must never
            // teleport the conversation there. The player has to walk to the
            // next NPC and press Talk first.
            if (!result.claimed) {
                result.question = null;
                const nextQuestion = result.quest_state && result.quest_state.question;
                const nextTarget = nextQuestion && (map.npcs || []).find(item => item.code === nextQuestion.npc_code);
                result.message = nextTarget
                    ? `${result.message || 'Correct!'} Find ${nextTarget.name} and press Talk when you are next to them.`
                    : `${result.message || 'Correct!'} Follow the glowing objective on the map.`;
            }
            showDialogue(currentNpc, result);
            if (result.claimed && result.reward) toast(`Chapter 1 complete! +${result.reward.xp} XP`);
        } catch (error) { toast(error.message); }
        busy = false;
    }

    async function performAction(actionCode) {
        if (busy) return;
        busy = true; hideProximityPrompt();
        try {
            const result = await post(root.dataset.actionUrl, { action_code: actionCode });
            state = result.state || state; questState = result.quest_state || questState; renderMap(); renderQuest();
            if (result.message) toast(result.message);
            if (result.claimed && result.reward) showDialogue(npcAt('elder_mira'), result);
        } catch (error) { toast(error.message); }
        busy = false;
    }

    async function requestHelp(helpType) {
        if (busy) return;
        const buttons = [...document.querySelectorAll(`[data-help="${helpType}"]`)];
        buttons.forEach(button => { button.disabled = true; button.dataset.oldText = button.innerHTML; button.innerHTML = 'Loading…'; });
        try {
            const result = await post(root.dataset.helpUrl, { help_type: helpType });
            state = result.state || state; questState = result.quest_state || questState; renderQuest();
            const text = result.help || result.message || 'Try the objective again.';
            ['questHelpText', 'dialogueHelpText'].forEach(id => { const node = $(id); if (node) { node.textContent = text; node.hidden = false; } });
            toast(result.cost ? `${result.cost} XP used for help.` : 'Help unlocked.');
        } catch (error) { toast(error.message); }
        buttons.forEach(button => { button.disabled = false; button.innerHTML = button.dataset.oldText || button.innerHTML; });
    }

    function applyHero() {
        hero.name = String($('heroName').value || 'Lantern').trim().slice(0, 24) || 'Lantern'; hero.skin = $('heroSkin').value; window.localStorage.setItem('englishQuestHero', JSON.stringify(hero)); $('heroPreviewName').textContent = hero.name; $('heroPreviewSprite').className = `mini-hero ${heroSkinClass()}`; renderMap(); toast('Hero profile updated.');
    }

    function setupMapTapMovement() {
        const grid = $('worldMap');
        if (!grid) return;
        let pointerStart = null;
        grid.addEventListener('pointerdown', event => {
            if (event.pointerType === 'mouse' && event.button !== 0) return;
            pointerStart = { x: event.clientX, y: event.clientY };
        }, { passive: true });
        grid.addEventListener('pointerup', event => {
            if (event.pointerType === 'mouse' && event.button !== 0) return;
            if ($('worldDialogue') && !$('worldDialogue').hidden) return;
            if (event.target.closest('.world-actor,.world-hotspot')) return;
            if (pointerStart && Math.hypot(event.clientX - pointerStart.x, event.clientY - pointerStart.y) > 28) return;
            const rect = grid.getBoundingClientRect();
            if (!rect.width || !rect.height) return;
            const x = Math.max(0, Math.min(Number(map.width) - 1, Math.floor((event.clientX - rect.left) / rect.width * Number(map.width))));
            const y = Math.max(0, Math.min(Number(map.height) - 1, Math.floor((event.clientY - rect.top) / rect.height * Number(map.height))));
            tapTarget = { x, y };
            followTapTarget();
            event.preventDefault();
        }, { passive: false });
    }

    function setupSidebar() {
        const sidebar = $('worldSidebar');
        const toggle = $('toggleWorldSidebar');
        if (!sidebar || !toggle) return;
        const updateToggleLabel = () => {
            const collapsed = sidebar.classList.contains('is-collapsed');
            toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            toggle.innerHTML = collapsed ? '☰ Quest panel' : '× Close panel';
        };
        if (window.innerWidth <= 850) sidebar.classList.add('is-collapsed');
        toggle.addEventListener('click', event => {
            event.preventDefault();
            event.stopPropagation();
            sidebar.classList.toggle('is-collapsed');
            updateToggleLabel();
        });
        updateToggleLabel();
        const tabs = [...sidebar.querySelectorAll('[data-panel-tab]')];
        const panels = [...sidebar.querySelectorAll('[data-panel-content]')];
        tabs.forEach(tab => tab.addEventListener('click', event => {
            event.preventDefault();
            event.stopPropagation();
            const key = tab.dataset.panelTab;
            tabs.forEach(item => { const active = item === tab; item.classList.toggle('is-active', active); item.setAttribute('aria-selected', active ? 'true' : 'false'); });
            panels.forEach(panel => { panel.hidden = panel.dataset.panelContent !== key; });
        }));
    }

    async function toggleFullscreen() {
        try {
            if (!document.fullscreenElement) {
                const target = document.querySelector('.world-shell') || root;
                if (target.requestFullscreen) await target.requestFullscreen();
                else toast('Fullscreen is not supported in this browser.');
            } else if (document.exitFullscreen) {
                await document.exitFullscreen();
            }
        } catch (error) {
            toast('Fullscreen permission was not granted.');
        }
    }

    document.addEventListener('keydown', event => { const directions = { ArrowUp: 'up', w: 'up', W: 'up', ArrowDown: 'down', s: 'down', S: 'down', ArrowLeft: 'left', a: 'left', A: 'left', ArrowRight: 'right', d: 'right', D: 'right' }; if (directions[event.key]) { event.preventDefault(); move(directions[event.key]); } if (event.key === 'Escape') { $('worldDialogue').hidden = true; updateProximityPrompt(); } });
    document.addEventListener('click', event => { const option = event.target.closest('[data-dialogue-option]'); if (option) return; const answerButton = event.target.closest('#submitDialogueAnswer'); if (answerButton) return answerQuestion(); const helpButton = event.target.closest('[data-help]'); if (helpButton) return requestHelp(helpButton.dataset.help); const nextRoute = event.target.closest('[data-next-route]'); if (nextRoute) return toast('The northern road to Whispering Grove is unlocked. Chapter 2 will continue from here.'); if (event.target.closest('[data-proximity-talk]')) return openProximityConversation(); const hotspotButton = event.target.closest('[data-hotspot-code]'); if (hotspotButton) return walkToHotspot(hotspotButton.dataset.hotspotCode); const moveButton = event.target.closest('[data-move]'); if (moveButton) return move(moveButton.dataset.move); const talkButton = event.target.closest('[data-talk]'); if (talkButton) return walkToNpc(talkButton.dataset.talk); const npcButton = event.target.closest('[data-npc-id]'); if (npcButton) return walkToNpc(npcButton.dataset.npcId); if (event.target.closest('[data-close-dialogue]')) { $('worldDialogue').hidden = true; updateProximityPrompt(); } if (event.target.closest('[data-fullscreen]')) toggleFullscreen(); if (event.target.closest('[data-reduced-motion]')) document.body.classList.toggle('reduced-motion'); if (event.target.closest('[data-save-hero]')) applyHero(); });
    $('heroName').value = hero.name; $('heroSkin').value = hero.skin; $('heroPreviewName').textContent = hero.name; $('heroPreviewSprite').className = `mini-hero ${heroSkinClass()}`; setupMapTapMovement(); setupSidebar(); renderMap(); renderQuest();
})();
