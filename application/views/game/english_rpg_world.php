<?php defined('BASEPATH') OR exit('No direct script access allowed'); $map=$world['map']; $quest=$world['quest']; $state=$world['state']; $quest_state=$world['quest_state']; ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= html_escape($title); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/tabler-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/english-rpg-world.css?v=20260815f'); ?>">
</head>
<body class="world-page" data-rpg-world data-csrf-name="<?= $this->security->get_csrf_token_name(); ?>" data-csrf-hash="<?= $this->security->get_csrf_hash(); ?>" data-move-url="<?= base_url('belajar/english-quest/world/move'); ?>" data-interact-url="<?= base_url('belajar/english-quest/world/interact'); ?>" data-answer-url="<?= base_url('belajar/english-quest/world/answer'); ?>" data-action-url="<?= base_url('belajar/english-quest/world/action'); ?>" data-help-url="<?= base_url('belajar/english-quest/world/help'); ?>">
<main class="world-shell">
    <header class="world-top">
        <a class="world-back" href="<?= base_url('belajar/english-quest'); ?>"><i class="ti ti-arrow-left"></i> <span>Choose Season</span></a>
        <div class="world-brand"><small>ENGLISH QUEST · SEASON 2</small><h1>Lanternbrook Village</h1></div>
        <div class="world-hud"><span class="hud-chip">🌟 <strong id="worldXp"><?= (int)$state['world_xp']; ?> XP</strong></span><button class="hud-chip border-0" type="button" data-fullscreen>⛶ Fullscreen</button><button class="hud-chip border-0" type="button" data-reduced-motion>◌ Reduced motion</button></div>
    </header>
    <section class="world-intro"><div><small class="chapter-kicker">CHAPTER 1 · THE MISSING STORY SIGILS</small><h2><?= html_escape($map['title']); ?></h2><p><?= html_escape($map['description']); ?></p></div><div class="text-end"><small>Keyboard: WASD / arrow keys</small><br><small>Touch: tap anywhere on the map to move</small></div></section>
    <section class="world-layout">
        <button type="button" id="toggleWorldSidebar" class="world-sidebar-toggle" aria-controls="worldSidebar" aria-expanded="true">☰ Quest panel</button>
        <div class="world-card world-stage-card">
            <div class="world-stage-bar"><span>🌿 LANTERNBROOK</span><span class="hint">Move close to a glowing character and press Talk</span></div>
            <div id="worldMap" class="world-map" aria-label="Interactive Lanternbrook Village map"></div>
            <div class="world-controls"><small>Tap the map to walk there. Your position is saved automatically.</small></div>
        </div>
        <aside id="worldSidebar" class="world-side" aria-label="Game information panel">
            <nav class="world-side-dock" role="tablist" aria-label="Game panels">
                <button type="button" class="world-side-tab is-active" data-panel-tab="quest" role="tab" aria-selected="true">📜 <span>Quest</span></button>
                <button type="button" class="world-side-tab" data-panel-tab="hero" role="tab" aria-selected="false">🧭 <span>Hero</span></button>
                <button type="button" class="world-side-tab" data-panel-tab="guide" role="tab" aria-selected="false">🎯 <span>Guide</span></button>
            </nav>
            <div class="world-side-window">
                <div class="world-card world-side-card" data-panel-content="quest" role="tabpanel"><h3>📜 <?= html_escape($quest['title']); ?></h3><p><?= html_escape($quest['description']); ?></p><div class="quest-status"><span>Quest status</span><strong id="questStatus">Not started</strong></div><div class="d-flex justify-content-between small mb-1"><span>Act progress</span><b id="questProgressText">0/6 acts</b></div><div class="quest-progress-bar"><i id="questProgressBar" style="width:<?= min(100,(int)$quest_state['progress']/6*100); ?>%"></i></div><p id="questNextAction" class="mt-3 mb-1 fw-bold text-primary">Find Mira and accept the quest.</p><div class="quest-assist"><div class="quest-assist-title">Need help?</div><div class="quest-assist-buttons"><button type="button" data-help="hint">💡 Hint <small>-5 XP</small></button><button type="button" data-help="translate">🌐 Translate <small>-8 XP</small></button></div><p id="questHelpText" hidden></p></div><ul id="questSteps" class="quest-steps"></ul><div id="chapterEnding" class="chapter-ending" hidden><strong>Chapter 1 Complete</strong><span>Mira restores the Lantern Archive. A new road to Whispering Grove opens beyond the northern stairs.</span><div class="chapter-route"><span class="chapter-route-arrow">↑</span><span><b>Next route: Whispering Grove</b><small>Chapter 2 will continue north from the village.</small></span></div></div></div>
                <div class="world-card world-side-card" data-panel-content="hero" role="tabpanel" hidden><h3>🧭 Shape your hero</h3><div class="hero-customizer"><div class="hero-preview"><span id="heroPreviewSprite" class="mini-hero"></span><strong id="heroPreviewName">Lantern</strong></div><label for="heroName">Hero name</label><input id="heroName" maxlength="24" value="Lantern" autocomplete="off"><label for="heroSkin">Cloak style</label><select id="heroSkin"><option value="teal">Lantern Keeper</option><option value="ember">Ember Scout</option><option value="violet">Moon Scholar</option></select><button type="button" data-save-hero>Save hero</button></div></div>
                <div class="world-card world-side-card" data-panel-content="guide" role="tabpanel" hidden><h3>🎯 Learning goal</h3><p class="mb-0">Listen to each villager, read the English clue, and collect a new word from every conversation.</p><hr><p class="mb-0"><strong>How to play</strong><br>Tap a villager on the map. Your hero will walk over, then tap the conversation prompt to talk.</p><small class="d-block mt-2 text-secondary">🔊 Listen prefers a natural English voice matched to the character’s illustrated voice profile. Your browser’s installed voices determine the final sound.</small></div>
            </div>
        </aside>
    </section>
</main>
<div id="worldToast" class="world-toast" role="status"></div>
<div id="worldProximityPrompt" class="world-proximity-prompt" hidden><button type="button" class="proximity-card" data-proximity-talk><span id="proximityAvatar" class="proximity-avatar"></span><span class="proximity-copy"><strong id="proximityName">Villager</strong><small>Nearby · tap to talk</small></span><span class="proximity-arrow">›</span></button></div>
<div id="worldDialogue" class="world-dialogue" hidden><div class="dialogue-card"><div class="dialogue-head"><span id="dialogueAvatar" class="dialogue-avatar"></span><div><h3 id="dialogueName">Villager</h3><small id="dialogueRole"></small></div><button type="button" class="dialogue-close" data-close-dialogue aria-label="Close">×</button></div><p id="dialogueLine" class="dialogue-line"></p><p id="dialogueExtra" class="dialogue-extra"></p><span id="dialogueGlossary" class="dialogue-glossary"></span><div id="dialogueQuestion" class="dialogue-question" hidden><p id="dialoguePrompt" class="dialogue-prompt"></p><div id="dialogueOptions" class="dialogue-options"></div><div class="dialogue-help"><button type="button" data-help="hint">💡 Hint <small>-5 XP</small></button><button type="button" data-help="translate">🌐 Translate <small>-8 XP</small></button><p id="dialogueHelpText" hidden></p></div><button type="button" id="submitDialogueAnswer" class="dialogue-answer" disabled>Answer</button><p id="dialogueFeedback" class="dialogue-feedback" hidden></p></div><div class="dialogue-actions"><button type="button" id="listenDialogue" class="dialogue-listen">🔊 Listen</button><small>Press Escape to close</small></div></div></div>
<script>window.RPG_WORLD=<?= json_encode(['map'=>$map,'quest'=>$quest,'state'=>$state,'quest_state'=>$quest_state,'ending'=>$world['ending']??null],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>;</script>
<script src="<?= base_url('assets/english-rpg-world.js?v=20260815g'); ?>"></script>
</body>
</html>
