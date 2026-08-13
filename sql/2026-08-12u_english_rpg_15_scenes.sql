INSERT INTO learn_english_rpg_episodes(code,title,subtitle,description,is_active) VALUES
('sky_kingdom','The Kingdom Above the Clouds','Episode 13 · Wings of the Wind','Terbang ke kerajaan awan untuk memulihkan arah angin dan belajar tentang cuaca serta penerbangan.',1),
('jungle_temple','Secrets of the Jungle Temple','Episode 14 · The Emerald Sun','Menembus kuil rimba, membaca petunjuk kuno, dan belajar tentang alam serta penjelajahan.',1),
('winter_village','Winter Village in Danger','Episode 15 · The Frozen Bell','Menolong desa bersalju melalui percakapan tentang musim, pakaian, rumah, dan kebersamaan.',1)
ON DUPLICATE KEY UPDATE title=VALUES(title),subtitle=VALUES(subtitle),description=VALUES(description),is_active=1;

DROP PROCEDURE IF EXISTS expand_rpg_chapter;
DELIMITER $$
CREATE PROCEDURE expand_rpg_chapter(IN p_code VARCHAR(50),IN p_words TEXT,IN p_meanings TEXT,IN p_world VARCHAR(100),IN p_emoji VARCHAR(30))
BEGIN
 DECLARE ep INT; DECLARE n INT DEFAULT 0; DECLARE w VARCHAR(100); DECLARE m VARCHAR(150); DECLARE loc VARCHAR(150); DECLARE npc VARCHAR(100); DECLARE line TEXT; DECLARE indo TEXT;
 SELECT id INTO ep FROM learn_english_rpg_episodes WHERE code COLLATE utf8mb4_general_ci=p_code LIMIT 1;
 WHILE n<15 DO
  SET n=n+1;SET w=SUBSTRING_INDEX(SUBSTRING_INDEX(p_words,',',n),',',-1);SET m=SUBSTRING_INDEX(SUBSTRING_INDEX(p_meanings,',',n),',',-1);
  SET loc=CONCAT(p_world,' · ',ELT(n,'Moonlit Gate','Whispering Path','Travelers Camp','Hidden Crossing','Crystal Hall','Old Watchtower','Underground Passage','Guardian Court','Broken Bridge','Silent Garden','Maze Chamber','Ancient Shrine','Shadow Arena','Final Passage','Heroes Sanctuary'));
  SET npc=ELT(n,'Village Guide','Forest Scout','Traveling Merchant','Young Ranger','Royal Scholar','Tower Keeper','Cave Explorer','Gate Guardian','Bridge Builder','Garden Spirit','Maze Keeper','Temple Sage','Dark Champion','Quest Captain','Royal Elder');
  SET line=ELT(n,
   CONCAT('The gate will open for a traveler who understands “',w,'”. What have you learned?'),
   CONCAT('Fresh tracks disappear near the path. The clue left behind is “',w,'”.'),
   CONCAT('A traveler offers a useful clue: “',w,'”. Choose its meaning before the journey continues.'),
   CONCAT('The safe route is marked with the word “',w,'”. Read it carefully before crossing.'),
   CONCAT('An old book glows and reveals “',w,'”. This word will unlock the crystal hall.'),
   CONCAT('From the tower we hear a warning about “',w,'”. Understanding it may save the village.'),
   CONCAT('A voice echoes through the passage: “',w,'”. The correct meaning reveals a hidden door.'),
   CONCAT('The guardian lowers a shield and asks about “',w,'”. Answer to enter the court.'),
   CONCAT('One plank bears the word “',w,'”. Only the right answer makes the bridge steady.'),
   CONCAT('The garden spirit whispers “',w,'” and waits for a kind and careful answer.'),
   CONCAT('The maze walls move around the symbol for “',w,'”. Choose wisely to find the exit.'),
   CONCAT('At the shrine an ancient inscription contains “',w,'”. Its meaning awakens the next clue.'),
   CONCAT('The dark champion attacks with the word “',w,'”. Break the spell with its true meaning.'),
   CONCAT('The final passage is sealed. The captain says “',w,'” is the last key we need.'),
   CONCAT('The quest is nearly complete. The elder asks you to remember “',w,'” before the celebration.'));
  SET indo=ELT(n,
   CONCAT('Gerbang akan terbuka bagi petualang yang memahami “',w,'”. Apa yang sudah kamu pelajari?'),
   CONCAT('Jejak baru menghilang di dekat jalan. Petunjuk yang tertinggal adalah “',w,'”.'),
   CONCAT('Seorang pengembara memberi petunjuk: “',w,'”. Pilih artinya sebelum perjalanan berlanjut.'),
   CONCAT('Jalur aman ditandai kata “',w,'”. Bacalah dengan teliti sebelum menyeberang.'),
   CONCAT('Sebuah buku tua bersinar dan memperlihatkan “',w,'”. Kata ini akan membuka aula kristal.'),
   CONCAT('Dari menara terdengar peringatan tentang “',w,'”. Memahaminya dapat menyelamatkan desa.'),
   CONCAT('Suara menggema di lorong: “',w,'”. Arti yang benar akan menunjukkan pintu rahasia.'),
   CONCAT('Penjaga menurunkan perisai dan bertanya tentang “',w,'”. Jawablah untuk memasuki istana.'),
   CONCAT('Salah satu papan bertuliskan “',w,'”. Jawaban benar akan membuat jembatan kokoh.'),
   CONCAT('Roh taman membisikkan “',w,'” dan menunggu jawaban yang baik dan teliti.'),
   CONCAT('Dinding labirin bergerak mengelilingi simbol “',w,'”. Pilih dengan bijak untuk keluar.'),
   CONCAT('Di kuil terdapat tulisan kuno dengan kata “',w,'”. Artinya membangunkan petunjuk berikutnya.'),
   CONCAT('Kesatria gelap menyerang dengan kata “',w,'”. Hancurkan mantranya dengan arti yang benar.'),
   CONCAT('Lorong terakhir tersegel. Kapten mengatakan “',w,'” adalah kunci terakhir.'),
   CONCAT('Misi hampir selesai. Tetua memintamu mengingat “',w,'” sebelum perayaan.'));
  IF NOT EXISTS(SELECT 1 FROM learn_english_rpg_scenes WHERE episode_id=ep AND sort_order=n) THEN
   INSERT INTO learn_english_rpg_scenes(episode_id,sort_order,place,speaker,emoji,dialogue,translation,prompt,choices_json,vocabulary_word,vocabulary_meaning,is_active)
   VALUES(ep,n,loc,npc,p_emoji,line,indo,CONCAT('What does “',w,'” mean?'),JSON_ARRAY(JSON_OBJECT('text',m,'correct',1,'feedback',CONCAT('Excellent! ',w,' means ',m,'.')),JSON_OBJECT('text','berlari cepat','correct',0,'feedback','The clue does not match that meaning.'),JSON_OBJECT('text','sangat jauh','correct',0,'feedback','Listen once more and choose another answer.')),w,m,1);
  ELSEIF EXISTS(SELECT 1 FROM learn_english_rpg_scenes WHERE episode_id=ep AND sort_order=n AND (speaker IN('Quest Guardian','Guild Master','Young Hero','Final Boss') OR place LIKE '%Stage%')) THEN
   UPDATE learn_english_rpg_scenes SET place=loc,speaker=npc,emoji=p_emoji,dialogue=line,translation=indo,prompt=CONCAT('What does “',w,'” mean?'),choices_json=JSON_ARRAY(JSON_OBJECT('text',m,'correct',1,'feedback',CONCAT('Excellent! ',w,' means ',m,'.')),JSON_OBJECT('text','berlari cepat','correct',0,'feedback','The clue does not match that meaning.'),JSON_OBJECT('text','sangat jauh','correct',0,'feedback','Listen once more and choose another answer.')),vocabulary_word=w,vocabulary_meaning=m WHERE episode_id=ep AND sort_order=n;
  END IF;
 END WHILE;
END$$
DELIMITER ;

CALL expand_rpg_chapter('lost_library','help,lantern,cross,behind,table,knowledge,welcome,ancient,whisper,return,chapter,author,borrow,quiet,wisdom','membantu,lentera,menyeberang,di belakang,meja,pengetahuan,sama-sama,kuno,berbisik,kembali,bab,penulis,meminjam,tenang,kebijaksanaan','Lost Library','📚');
CALL expand_rpg_chapter('market_mystery','basket,three,shelf,price,coin,cheap,expensive,fresh,customer,change,weigh,receipt,vendor,ripe,trade','keranjang,tiga,rak,harga,koin,murah,mahal,segar,pelanggan,kembalian,menimbang,struk,pedagang,matang,berdagang','Sunny Market','🛒');
CALL expand_rpg_chapter('forest_rescue','footprints,raincoat,left,branch,river,careful,injured,shelter,follow,rescue,meadow,medicine,search,healthy,wildlife','jejak kaki,jas hujan,kiri,ranting,sungai,hati-hati,terluka,tempat berlindung,mengikuti,menyelamatkan,padang rumput,obat,mencari,sehat,satwa liar','Green Forest','🌲');
CALL expand_rpg_chapter('space_station','rocket,button,broken,launch,helmet,float,planet,oxygen,repair,signal,orbit,gravity,crew,control,landing','roket,tombol,rusak,peluncuran,helm,melayang,planet,oksigen,memperbaiki,sinyal,orbit,gravitasi,awak,kendali,pendaratan','Star Station','🪐');
CALL expand_rpg_chapter('school_festival','perform,nervous,together,practice,costume,audience,stage,proud,cheer,celebrate,rehearsal,decorate,invite,music,applause','tampil,gugup,bersama,berlatih,kostum,penonton,panggung,bangga,bersorak,merayakan,geladi,menghias,mengundang,musik,tepuk tangan','School Festival','🎭');
CALL expand_rpg_chapter('ocean_treasure','beneath,gently,treasure,coral,turtle,deep,shallow,protect,clean,pearl,current,diver,whale,reef,surface','di bawah,dengan lembut,harta karun,karang,penyu,dalam,dangkal,melindungi,bersih,mutiara,arus,penyelam,paus,terumbu,permukaan','Coral Kingdom','🐬');
CALL expand_rpg_chapter('clockwork_castle','clock,key,tower,stairs,lever,gear,doorway,hurry,unlock,midnight,machine,minute,mechanic,rotate,alarm','jam,kunci,menara,tangga,tuas,roda gigi,pintu,bergegas,membuka kunci,tengah malam,mesin,menit,montir,berputar,alarm','Clockwork Castle','🏰');
CALL expand_rpg_chapter('mountain_camp','mountain,tent,rope,trail,compass,campfire,storm,safe,summit,descend,backpack,climb,valley,steep,survive','gunung,tenda,tali,jalur,kompas,api unggun,badai,aman,puncak,turun,ransel,mendaki,lembah,curam,bertahan','Eagle Mountain','⛰️');
CALL expand_rpg_chapter('dragon_academy','dragon,spell,shield,flame,brave,wings,magic,protect,enemy,victory,potion,wand,armor,training,courage','naga,mantra,perisai,api,berani,sayap,sihir,melindungi,musuh,kemenangan,ramuan,tongkat sihir,baju zirah,latihan,keberanian','Dragon Academy','🐲');
CALL expand_rpg_chapter('pirate_islands','captain,ship,sail,anchor,map,north,storm,island,dig,chest,deck,harbor,crew,compass,aboard','kapten,kapal,berlayar,jangkar,peta,utara,badai,pulau,menggali,peti,geladak,pelabuhan,awak,kompas,naik kapal','Emerald Islands','🏴‍☠️');
CALL expand_rpg_chapter('haunted_museum','museum,statue,portrait,ancient,vanished,heard,afraid,shadow,secret,history,gallery,artifact,ghost,investigate,restore','museum,patung,lukisan,kuno,menghilang,mendengar,takut,bayangan,rahasia,sejarah,galeri,artefak,hantu,menyelidiki,memulihkan','Haunted Museum','🗿');
CALL expand_rpg_chapter('future_city','robot,energy,screen,vehicle,charge,network,signal,repair,power,future,device,electric,system,engineer,connect','robot,energi,layar,kendaraan,mengisi daya,jaringan,sinyal,memperbaiki,kekuatan,masa depan,perangkat,listrik,sistem,insinyur,menghubungkan','Future City','🦾');
CALL expand_rpg_chapter('sky_kingdom','cloud,wind,feather,fly,storm,rainbow,thunder,lightning,weather,sky,breeze,glide,wing,heaven,airship','awan,angin,bulu,terbang,badai,pelangi,guntur,kilat,cuaca,langit,angin sepoi,meluncur,sayap,surga,kapal udara','Sky Kingdom','☁️');
CALL expand_rpg_chapter('jungle_temple','jungle,vine,temple,torch,stone,monkey,insect,ruins,explore,emerald,waterfall,poison,statue,escape,discover','rimba,tanaman rambat,kuil,obor,batu,monyet,serangga,reruntuhan,menjelajah,zamrud,air terjun,racun,patung,melarikan diri,menemukan','Jungle Temple','🛕');
CALL expand_rpg_chapter('winter_village','winter,snow,coat,gloves,scarf,fireplace,freeze,warm,blanket,village,ice,boots,bell,neighbor,together','musim dingin,salju,mantel,sarung tangan,syal,perapian,membeku,hangat,selimut,desa,es,sepatu bot,lonceng,tetangga,bersama','Winter Village','❄️');
DROP PROCEDURE expand_rpg_chapter;
