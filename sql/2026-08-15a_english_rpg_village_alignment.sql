SET NAMES utf8mb4;

-- Align the logical walk grid with the illustrated Lanternbrook village.
-- The west cottage's outside path is row 7; the old row-5 positions placed
-- Pip and Tessa on the roof, while Ren's old position landed in the river.
UPDATE learn_english_rpg_world_maps
SET layout_json = '["##################","#####.......######","#####.......######","#####.......######","##..#.......#..###","#.......##.......#","#####....#.....###","##.......#.....###","#####.......######","##..#.......#..###","#..........#####.#","##################"]'
WHERE code = 'season2_village';

UPDATE learn_english_rpg_world_npcs n
JOIN learn_english_rpg_world_maps m ON m.id = n.map_id
SET n.x = CASE n.code
    WHEN 'courier_pip' THEN 5
    WHEN 'innkeeper_tessa' THEN 3
    WHEN 'scout_ren' THEN 7
    ELSE n.x END,
    n.y = CASE n.code
    WHEN 'courier_pip' THEN 7
    WHEN 'innkeeper_tessa' THEN 7
    WHEN 'scout_ren' THEN 9
    ELSE n.y END
WHERE m.code = 'season2_village'
  AND n.code IN ('courier_pip','innkeeper_tessa','scout_ren');

-- Move only legacy exact-position states so an existing player is not left
-- standing on the old roof/river tiles after the alignment.
UPDATE learn_english_rpg_world_player_states s
JOIN learn_english_rpg_world_maps m ON m.id = s.map_id
SET s.x = 7, s.y = 8
WHERE m.code = 'season2_village' AND s.x = 5 AND s.y = 10;

UPDATE learn_english_rpg_world_player_states s
JOIN learn_english_rpg_world_maps m ON m.id = s.map_id
SET s.x = 2, s.y = 7
WHERE m.code = 'season2_village' AND s.x = 1 AND s.y = 5;

UPDATE learn_english_rpg_world_player_states s
JOIN learn_english_rpg_world_maps m ON m.id = s.map_id
SET s.x = 4, s.y = 7
WHERE m.code = 'season2_village' AND s.x = 3 AND s.y = 5;
