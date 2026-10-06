<?php
declare(strict_types=1);
$dbPassword=getenv('PUSTAKA_DB_PASSWORD');if($dbPassword===false||$dbPassword==='')die("Set PUSTAKA_DB_PASSWORD terlebih dahulu.\n");
$pdo=new PDO('mysql:host=127.0.0.1;dbname=pustaka;charset=utf8mb4','root',$dbPassword,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$rows=$pdo->query("SELECT s.id,s.choices_json FROM learn_english_rpg_scenes s JOIN learn_english_rpg_episodes e ON e.id=s.episode_id WHERE e.season_id=1 AND s.is_active=1 AND s.challenge_type IN ('choice','listening','sentence') ORDER BY s.id")->fetchAll(PDO::FETCH_ASSOC);
$update=$pdo->prepare('UPDATE learn_english_rpg_scenes SET choices_json=? WHERE id=?');$count=0;
foreach($rows as $row){$choices=json_decode($row['choices_json'],true);if(!is_array($choices)||count($choices)<2)continue;$shift=((int)$row['id'])%count($choices);if($shift>0)$choices=array_merge(array_slice($choices,$shift),array_slice($choices,0,$shift));$update->execute([json_encode($choices,JSON_UNESCAPED_UNICODE),$row['id']]);$count++;}
echo "Balanced $count scenes.\n";
