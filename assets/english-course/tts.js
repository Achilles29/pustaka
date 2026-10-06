(function(w){
    'use strict';
    var synth=w.speechSynthesis,cache=[];
    function refresh(){cache=synth?synth.getVoices():[];return cache;}
    if(synth){refresh();synth.addEventListener?synth.addEventListener('voiceschanged',refresh):synth.onvoiceschanged=refresh;}
    function waitVoices(){return new Promise(function(resolve){var voices=refresh();if(voices.length)return resolve(voices);var done=false,finish=function(){if(done)return;done=true;resolve(refresh());};if(synth&&synth.addEventListener)synth.addEventListener('voiceschanged',finish,{once:true});setTimeout(finish,1200);});}
    function clean(value){return String(value||'').toLowerCase().replace('_','-');}
    function score(voice,preference){var lang=clean(voice.lang),name=clean(voice.name),s=0;if(!/^en(?:-|$)/.test(lang))return -10000;if(/^ru(?:-|$)/.test(lang)||/russian|русск|milena|yuri/.test(name))return -10000;if(lang==='en-us')s+=500;else if(lang==='en-gb')s+=430;else if(lang==='en-au'||lang==='en-ca')s+=390;else s+=250;if(preference==='gb_female'){if(lang==='en-gb')s+=300;}else if(preference==='us_female'){if(lang==='en-us')s+=300;}if(/google us english|microsoft aria|microsoft jenny|samantha|ava|allison|karen|serena|female/.test(name))s+=180;if(/natural|premium|enhanced|neural/.test(name))s+=100;if(/compact|espeak/.test(name))s-=120;if(voice.default)s+=10;return s;}
    async function speak(text,options){options=options||{};if(!synth)return false;var voices=await waitVoices(),ranked=voices.map(function(v){return {voice:v,score:score(v,options.preference||'us_female')};}).filter(function(x){return x.score>0;}).sort(function(a,b){return b.score-a.score;});if(!ranked.length){w.alert('Suara English (US/UK) belum tersedia di perangkat ini. Aktifkan atau unduh voice English pada pengaturan Text-to-Speech ponsel.');return false;}synth.cancel();var chosen=ranked[0].voice,u=new SpeechSynthesisUtterance(String(text||''));u.voice=chosen;u.lang=chosen.lang||'en-US';u.rate=Math.max(.5,Math.min(1.2,Number(options.rate)||.88));u.pitch=1;u.volume=1;synth.speak(u);return true;}
    w.EnglishCourseTTS={speak:speak,voices:waitVoices};
})(window);
