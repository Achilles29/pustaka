(() => {
    'use strict';
    const root = document.querySelector('.quest');
    const data = window.ENGLISH_QUEST || {};
    const scenes = data.scenes || [];
    let progress = data.progress || {};
    let busy = false;
    const $ = id => document.getElementById(id);

    function speak(text, button) {
        if (!window.speechSynthesis || !window.SpeechSynthesisUtterance) {
            alert('Fitur suara tidak tersedia pada browser ini. Gunakan Chrome, Edge, atau Safari versi terbaru.');
            return;
        }
        if (!text) return;
        window.speechSynthesis.cancel();
        const utterance = new window.SpeechSynthesisUtterance(String(text));
        utterance.lang = 'en-US';
        utterance.rate = 0.78;
        utterance.pitch = 1;
        utterance.volume = 1;
        const voices = window.speechSynthesis.getVoices();
        const englishVoice = voices.find(v => /^en-(US|GB)/i.test(v.lang)) || voices.find(v => /^en/i.test(v.lang));
        if (englishVoice) utterance.voice = englishVoice;
        const old = button ? button.innerHTML : '';
        utterance.onstart = () => { if (button) button.innerHTML = '<i class="ti ti-volume-2"></i> Sedang dibacakan...'; };
        const restore = () => { if (button) button.innerHTML = old; };
        utterance.onend = restore;
        utterance.onerror = event => {
            restore();
            if (event.error !== 'canceled' && event.error !== 'interrupted') alert('Suara gagal diputar. Pastikan volume perangkat aktif.');
        };
        window.speechSynthesis.speak(utterance);
        // Chromium kadang tertahan setelah cancel(); resume memastikan antrean berjalan.
        window.setTimeout(() => window.speechSynthesis.resume(), 80);
    }

    function vocabulary() {
        const list = $('vocabList'), items = progress.vocabulary || {};
        list.innerHTML = Object.keys(items).length
            ? Object.entries(items).map(([a,b]) => `<div class="vocab-row"><b>${escapeHtml(a)}</b><span>${escapeHtml(b)}</span></div>`).join('')
            : '<div class="text-secondary p-2">Kata yang ditemukan akan tersimpan di sini.</div>';
        $('vocabCount').textContent = Object.keys(items).length;
    }

    function render() {
        if (window.speechSynthesis) window.speechSynthesis.cancel();
        const i = Number(progress.current_scene);
        $('heartCount').textContent = progress.hearts;
        $('xpCount').textContent = progress.xp;
        vocabulary();
        if (progress.is_completed || i >= scenes.length) {
            $('gameScene').hidden = true; $('questResult').hidden = false;
            const xp = $('questResult').querySelector('.result-xp b');
            if (xp) xp.textContent = progress.xp + ' XP';
            return;
        }
        const s = scenes[i];
        const code = root.dataset.episodeCode || '';
        root.dataset.biome = /space|future/.test(code)?'space':/ocean|pirate/.test(code)?'ocean':/winter/.test(code)?'winter':/jungle|forest/.test(code)?'forest':/sky/.test(code)?'sky':/dragon|castle/.test(code)?'castle':/museum/.test(code)?'haunted':'village';
        $('gameScene').hidden = false; $('questResult').hidden = true;
        $('placeName').textContent = s.place; $('character').textContent = s.emoji;
        $('character').classList.remove('scene-change'); void $('character').offsetWidth; $('character').classList.add('scene-change');
        $('speaker').textContent = s.challenge_type==='listening'?'🎧 Listening Challenge':s.challenge_type==='sentence'?'🧩 Sentence Forge':s.challenge_type==='story'?'🗨️ Story Decision':s.speaker;
        $('dialogue').textContent = s.challenge_type==='listening'?'Listen carefully. The dialogue is hidden until you use translation help.':s.text;
        $('translation').textContent = s.translation; $('translation').hidden = true;
        $('prompt').textContent = s.prompt; $('progressText').textContent = `CHAPTER ${i+1} / ${scenes.length}`;
        $('progressBar').style.width = `${i/scenes.length*100}%`;
        $('heroHp').style.width = `${Math.max(0,Number(progress.hearts)/3*100)}%`;
        $('enemyHp').style.width = `${Math.max(0,(scenes.length-i)/scenes.length*100)}%`;
        const boss=i%5===4;$('enemyAvatar').textContent = boss?'👹':['🐉','👻','🧌','🦂','🤖'][i%5];
        $('enemyAvatar').classList.toggle('boss',boss);$('enemyName').textContent = boss?'Chapter Boss':['Word Dragon','Grammar Ghost','Riddle Troll','Phrase Scorpion','Quiz Bot'][i%5];
        if(s.challenge_type==='sentence'){$('choices').innerHTML=`<div class="sentence-answer" id="sentenceAnswer"><small>Susun kalimat di sini</small></div><div class="word-bank">${s.sentence_words.map((w,n)=>`<button type="button" class="word-chip" data-word="${n}">${escapeHtml(w)}</button>`).join('')}</div><div class="d-flex gap-2"><button type="button" class="translate" data-sentence-reset><i class="ti ti-refresh"></i> Ulangi</button><button type="button" class="choice flex-fill text-center" data-sentence-submit>Periksa Kalimat</button></div>`;}else{$('choices').innerHTML=s.choices.map((c,n)=>`<div class="d-flex gap-2"><button type="button" class="choice flex-fill" data-choice="${c.answer_index}"><span>${String.fromCharCode(65+n)}.</span> ${escapeHtml(c.text)}</button><button type="button" class="translate" data-speak="${n}" aria-label="Dengarkan pilihan ${String.fromCharCode(65+n)}"><i class="ti ti-volume"></i></button></div>`).join('');if(s.challenge_type==='listening')window.setTimeout(()=>speak(s.audio_text,$('speakDialogue')),350);}
    }

    async function answer(choice) {
        if (busy) return; busy = true;
        document.querySelectorAll('.choice').forEach(b => b.disabled = true);
        const body = new FormData(); body.append('scene', progress.current_scene); body.append('choice', choice); body.append('episode_code', root.dataset.episodeCode);
        if (root.dataset.csrfName) body.append(root.dataset.csrfName, root.dataset.csrfHash);
        try {
            const isStory=scenes[Number(progress.current_scene)].challenge_type==='story';const response = await fetch(isStory?root.dataset.storyUrl:root.dataset.answerUrl, {method:'POST',body,headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}});
            const raw = await response.text(); let json;
            try { json = JSON.parse(raw); } catch (parseError) { throw new Error('Server tidak mengirim respons yang benar. Muat ulang halaman lalu coba kembali.'); }
            if (!response.ok || !json.ok) throw new Error(json.message || 'Jawaban gagal disimpan.');
            const box = document.createElement('div'); box.className = isStory?'story-result':'feedback '+(json.correct?'ok':'no');
            box.textContent = isStory?json.feedback+' Reputation +'+json.reputation_delta:(json.correct?'✓ ':'✕ ')+json.feedback; $('choices').appendChild(box);
            root.classList.remove('attack-success','attack-hit'); void root.offsetWidth; if(!isStory)root.classList.add(json.correct?'attack-success':'attack-hit');
            $('battleFx').textContent=isStory?'✨ PATH CHOSEN':(json.correct?'⚡ -10 HP':'💥 OUCH!');progress = json.progress;
            const delay=json.loot?2400:(json.correct?1100:1500);if(json.loot)showLoot(json.loot);
            window.setTimeout(() => { busy=false; render(); }, delay);
        } catch (error) { busy=false; alert(error.message); render(); }
    }

    async function submitSentence(){if(busy)return;const words=[...document.querySelectorAll('#sentenceAnswer .placed-word')].map(x=>x.textContent);if(!words.length){alert('Pilih kata untuk menyusun kalimat.');return}busy=true;const body=new FormData();body.append('scene',progress.current_scene);body.append('sentence',words.join(' '));body.append('episode_code',root.dataset.episodeCode);try{const response=await fetch(root.dataset.sentenceUrl,{method:'POST',body,headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}});const raw=await response.text();let json;try{json=JSON.parse(raw)}catch(e){throw new Error('Server tidak mengirim respons yang benar.')}if(!response.ok||!json.ok)throw new Error(json.message||'Kalimat gagal diperiksa.');const box=document.createElement('div');box.className='feedback '+(json.correct?'ok':'no');box.textContent=(json.correct?'✓ ':'✕ ')+json.feedback;$('choices').appendChild(box);root.classList.remove('attack-success','attack-hit');void root.offsetWidth;root.classList.add(json.correct?'attack-success':'attack-hit');$('battleFx').textContent=json.correct?'⚡ SENTENCE HIT!':'💥 TRY AGAIN';progress=json.progress;const delay=json.loot?2400:(json.correct?1200:1600);if(json.loot)showLoot(json.loot);window.setTimeout(()=>{busy=false;render()},delay)}catch(error){busy=false;alert(error.message)}}

    $('choices').addEventListener('click', event => {
        const word=event.target.closest('[data-word]');if(word){const target=$('sentenceAnswer');if(target.querySelector('small'))target.innerHTML='';const placed=document.createElement('button');placed.type='button';placed.className='placed-word';placed.textContent=word.textContent;placed.addEventListener('click',()=>{word.disabled=false;placed.remove()});target.appendChild(placed);word.disabled=true;return}
        if(event.target.closest('[data-sentence-reset]')){render();return}if(event.target.closest('[data-sentence-submit]')){submitSentence();return}
        const voice = event.target.closest('[data-speak]');
        if (voice) { speak(scenes[Number(progress.current_scene)].choices[Number(voice.dataset.speak)].text, voice); return; }
        const button = event.target.closest('[data-choice]'); if (button) answer(Number(button.dataset.choice));
    });
    $('speakDialogue').addEventListener('click', event => {const s=scenes[Number(progress.current_scene)];speak(s.challenge_type==='listening'?s.audio_text:s.text,event.currentTarget)});
    $('translateBtn').addEventListener('click', () => { $('translation').hidden = !$('translation').hidden; });
    $('vocabToggle').addEventListener('click', () => { $('vocabList').hidden = !$('vocabList').hidden; });
    function showLoot(item){const layer=document.createElement('div');layer.className='loot-drop';layer.innerHTML=`<div class="loot-card"><small>ITEM DITEMUKAN!</small><span class="loot-icon">${escapeHtml(item.icon)}</span><h2>${escapeHtml(item.name)}</h2><p>Masuk ke tas petualanganmu</p></div>`;document.body.appendChild(layer);window.setTimeout(()=>layer.remove(),2100);}
    function escapeHtml(value) { const div=document.createElement('div'); div.textContent=String(value); return div.innerHTML; }
    render();
})();
