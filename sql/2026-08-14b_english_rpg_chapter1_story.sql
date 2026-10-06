SET NAMES utf8mb4;

-- Chapter 1 story expansion: supporting NPCs, act-based objective metadata,
-- and a longer quest reward. Progress details remain in visited_json so the
-- existing Season 2 tables stay backward compatible.

INSERT INTO learn_english_rpg_world_npcs
    (map_id, code, name, role, avatar, x, y, greeting, dialogue_json, vocabulary_word, vocabulary_meaning, is_active)
SELECT m.id, d.code, d.name, d.role, d.avatar, d.x, d.y, d.greeting, d.dialogue_json, d.word, d.meaning, 1
FROM learn_english_rpg_world_maps m
JOIN (
    SELECT 'courier_pip' code, 'Pip' name, 'Young Story Courier' role, '🧒' avatar, 3 x, 5 y,
        'I was carrying a storybook, but it slipped away near the greenhouse.' greeting,
        '[{"en":"I was carrying a storybook for my mother. Can you help me find it?","id":"I was carrying a storybook for my mother. Can you help me find it?"},{"en":"The book likes quiet places and warm green light.","id":"The book likes quiet places and warm green light."}]' dialogue_json,
        'storybook' word, 'a book with stories' meaning
    UNION ALL SELECT 'innkeeper_tessa', 'Tessa', 'Keeper of the Lantern Inn', '👩‍🍳', 1, 5,
        'Welcome to the inn. I kept a sealed parcel safe for a very long time.',
        '[{"en":"Nova asked me to protect this parcel until someone remembered the old promise.","id":"Nova asked me to protect this parcel until someone remembered the old promise."},{"en":"A promise is a bridge between what we say and what we do.","id":"A promise is a bridge between what we say and what we do."}]',
        'sealed' word, 'closed so nobody can open it' meaning
    UNION ALL SELECT 'smith_bram', 'Bram', 'Lantern Smith', '🧔', 13, 5,
        'Bring me the right materials, and I can repair the broken sigil case.',
        '[{"en":"The case needs brass wire, blue glass, and dry wood.","id":"The case needs brass wire, blue glass, and dry wood."},{"en":"Good work follows a clear order: place, fit, and close.","id":"Good work follows a clear order: place, fit, and close."}]',
        'repair' word, 'to fix something that is broken' meaning
    UNION ALL SELECT 'gate_orin', 'Orin', 'Warden of the Northern Stairs', '🛡️', 10, 1,
        'The northern gate is closed. I will listen when you are ready to ask politely.',
        '[{"en":"The Hollow Echo is near the stairs, so careless words can become dangerous.","id":"The Hollow Echo is near the stairs, so careless words can become dangerous."},{"en":"Ask for permission before you cross the northern gate.","id":"Ask for permission before you cross the northern gate."}]',
        'permission' word, 'the right to do something' meaning
) d ON m.code = 'season2_village'
WHERE NOT EXISTS (SELECT 1 FROM learn_english_rpg_world_npcs n WHERE n.map_id = m.id AND n.code = d.code);

UPDATE learn_english_rpg_world_npcs SET avatar = CASE code
    WHEN 'courier_pip' THEN '🧒'
    WHEN 'innkeeper_tessa' THEN '👩‍🍳'
    WHEN 'smith_bram' THEN '🧔'
    WHEN 'gate_orin' THEN '🛡️'
    ELSE avatar END
WHERE map_id = (SELECT id FROM learn_english_rpg_world_maps WHERE code = 'season2_village' LIMIT 1)
  AND code IN ('courier_pip','innkeeper_tessa','smith_bram','gate_orin');

UPDATE learn_english_rpg_world_quests
SET description = 'Follow six connected acts across Lanternbrook: help Pip, restore Eli''s garden, carry Nova''s promise, read Ren''s trail, repair the sigil case with Bram, and open the northern gate.',
    objective_target = 6,
    objective_codes_json = '["elder_mira","courier_pip","gardener_eli","trader_nova","innkeeper_tessa","scout_ren","gate_orin","smith_bram"]',
    reward_xp = 150
WHERE code = 'missing_story_sigils';
