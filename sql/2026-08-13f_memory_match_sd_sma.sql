-- Memory Match SD1–SMA12. Menyalin pasangan istilah-definisi terkurasi
-- dari katalog Word Scramble untuk latihan recall dengan mekanik berbeda.
INSERT INTO learn_game_categories(game_type_id,grade_level_id,subject_id,name,description,is_active)
SELECT mm.id,wc.grade_level_id,wc.subject_id,CONCAT('Memory — ',g.name),CONCAT('Cocokkan istilah dan pengertian untuk ',g.name,'.'),1
FROM learn_game_types mm JOIN learn_game_types ws ON ws.code='word_scramble'
JOIN learn_game_categories wc ON wc.game_type_id=ws.id AND wc.name LIKE 'Susun Kata — %'
JOIN quiz_grade_levels g ON g.id=wc.grade_level_id
WHERE mm.code='memory_match' AND NOT EXISTS(SELECT 1 FROM learn_game_categories x WHERE x.game_type_id=mm.id AND x.grade_level_id=wc.grade_level_id AND x.name=CONCAT('Memory — ',g.name));

INSERT INTO learn_game_content_sets(category_id,name,difficulty,is_active)
SELECT mc.id,CONCAT('Pasangan ',g.name),IF(mc.grade_level_id<=6,'easy',IF(mc.grade_level_id<=10,'medium','hard')),1
FROM learn_game_categories mc JOIN learn_game_types mm ON mm.id=mc.game_type_id AND mm.code='memory_match'
JOIN quiz_grade_levels g ON g.id=mc.grade_level_id
WHERE mc.name LIKE 'Memory — %' AND NOT EXISTS(SELECT 1 FROM learn_game_content_sets s WHERE s.category_id=mc.id AND s.name=CONCAT('Pasangan ',g.name));

INSERT INTO learn_game_content_items(set_id,term,definition,sort_order)
SELECT ms.id,wi.term,wi.definition,wi.sort_order
FROM learn_game_types ws
JOIN learn_game_categories wc ON wc.game_type_id=ws.id AND wc.name LIKE 'Susun Kata — %'
JOIN learn_game_content_sets wset ON wset.category_id=wc.id AND wset.name LIKE 'Tantangan %'
JOIN learn_game_content_items wi ON wi.set_id=wset.id
JOIN learn_game_types mm ON mm.code='memory_match'
JOIN learn_game_categories mc ON mc.game_type_id=mm.id AND mc.grade_level_id=wc.grade_level_id AND mc.name LIKE 'Memory — %'
JOIN learn_game_content_sets ms ON ms.category_id=mc.id AND ms.name LIKE 'Pasangan %'
WHERE ws.code='word_scramble' AND NOT EXISTS(SELECT 1 FROM learn_game_content_items x WHERE x.set_id=ms.id AND x.term=wi.term);
