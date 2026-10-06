SET NAMES utf8mb4;

-- Second pass against lanternbrook-v1.png. Open the visible northern stairs,
-- reconnect the plaza below the fountain, and keep the east/south cobbled
-- roads usable while the house, water, fountain, and roof cells remain solid.
UPDATE learn_english_rpg_world_maps
SET layout_json = '["########...#######","#####.......######","#####.......######","#####.......######","##.........#....##","#.......##.......#","#####....#.....###","##.............###","#####.......######","##..#.......######","#................#","##################"]'
WHERE code = 'season2_village';
