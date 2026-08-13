INSERT INTO learn_english_rpg_episodes(code,title,subtitle,description,is_active) VALUES
('dragon_academy','Dragon Academy','Episode 9 · The First Spell','Berlatih menjadi penjaga naga dengan kosakata sihir, keberanian, dan instruksi.',1),
('pirate_islands','Pirates of Emerald Islands','Episode 10 · The Secret Map','Berlayar mencari pulau rahasia sambil belajar arah, cuaca, dan benda di kapal.',1),
('haunted_museum','Night at the Haunted Museum','Episode 11 · The Moving Statue','Memecahkan misteri museum dengan materi bentuk lampau, benda sejarah, dan perasaan.',1),
('future_city','Heroes of Future City','Episode 12 · The Power Blackout','Menyelamatkan kota masa depan dengan kosakata teknologi, transportasi, dan energi.',1)
ON DUPLICATE KEY UPDATE title=VALUES(title),subtitle=VALUES(subtitle),description=VALUES(description),is_active=1;

DROP PROCEDURE IF EXISTS seed_new_rpg_chapter;
DELIMITER $$
CREATE PROCEDURE seed_new_rpg_chapter(IN p_code VARCHAR(50),IN p_words TEXT,IN p_meanings TEXT,IN p_place VARCHAR(150),IN p_emoji VARCHAR(30))
BEGIN
 DECLARE ep INT; DECLARE n INT DEFAULT 0; DECLARE w VARCHAR(100); DECLARE m VARCHAR(150);
 SELECT id INTO ep FROM learn_english_rpg_episodes WHERE code COLLATE utf8mb4_general_ci=p_code LIMIT 1;
 WHILE n<10 DO SET n=n+1;
  IF NOT EXISTS(SELECT 1 FROM learn_english_rpg_scenes WHERE episode_id=ep AND sort_order=n) THEN
   SET w=SUBSTRING_INDEX(SUBSTRING_INDEX(p_words,',',n),',',-1);SET m=SUBSTRING_INDEX(SUBSTRING_INDEX(p_meanings,',',n),',',-1);
   INSERT INTO learn_english_rpg_scenes(episode_id,sort_order,place,speaker,emoji,dialogue,translation,prompt,choices_json,vocabulary_word,vocabulary_meaning,is_active)
   VALUES(ep,n,CONCAT(p_place,' · Stage ',n),ELT(MOD(n-1,4)+1,'Guild Master','Young Hero','Quest Guardian','Final Boss'),p_emoji,
   CONCAT('Stage ',n,': the guardian challenges you with “',w,'”. Master this word to win the battle.'),CONCAT('Tahap ',n,': penjaga menantangmu dengan kata “',w,'”. Kuasai kata ini untuk memenangkan pertarungan.'),CONCAT('Choose the meaning of “',w,'”.'),
   JSON_ARRAY(JSON_OBJECT('text',m,'correct',1,'feedback',CONCAT('Critical hit! ',w,' means ',m,'.')),JSON_OBJECT('text','beristirahat','correct',0,'feedback','The enemy blocks your attack. Try another meaning.'),JSON_OBJECT('text','sangat kecil','correct',0,'feedback','Not quite. Listen to the word and strike again.')),w,m,1);
  END IF;
 END WHILE;
END$$
DELIMITER ;
CALL seed_new_rpg_chapter('dragon_academy','dragon,spell,shield,flame,brave,wings,magic,protect,enemy,victory','naga,mantra,perisai,api,berani,sayap,sihir,melindungi,musuh,kemenangan','Dragon Academy','🐲');
CALL seed_new_rpg_chapter('pirate_islands','captain,ship,sail,anchor,map,north,storm,island,dig,chest','kapten,kapal,berlayar,jangkar,peta,utara,badai,pulau,menggali,peti','Emerald Sea','🏴‍☠️');
CALL seed_new_rpg_chapter('haunted_museum','museum,statue,portrait,ancient,vanished,heard,afraid,shadow,secret,history','museum,patung,lukisan,kuno,menghilang,mendengar,takut,bayangan,rahasia,sejarah','Midnight Museum','🗿');
CALL seed_new_rpg_chapter('future_city','robot,energy,screen,vehicle,charge,network,signal,repair,power,future','robot,energi,layar,kendaraan,mengisi daya,jaringan,sinyal,memperbaiki,kekuatan,masa depan','Future City','🦾');
DROP PROCEDURE seed_new_rpg_chapter;
