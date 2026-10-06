<?php
declare(strict_types=1);
$dbPassword=getenv('PUSTAKA_DB_PASSWORD');if($dbPassword===false||$dbPassword==='')die("Set PUSTAKA_DB_PASSWORD terlebih dahulu.\n");
$pdo=new PDO('mysql:host=127.0.0.1;dbname=pustaka;charset=utf8mb4','root',$dbPassword,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$rows=$pdo->query("SELECT s.id,s.episode_id,s.choices_json,s.vocabulary_meaning FROM learn_english_rpg_scenes s JOIN learn_english_rpg_episodes e ON e.id=s.episode_id WHERE e.season_id=1 AND s.is_active=1 AND s.challenge_type IN ('choice','listening','sentence') ORDER BY s.episode_id,s.id")->fetchAll(PDO::FETCH_ASSOC);
$pool=[];foreach($rows as $row){$meaning=mb_strtolower(trim((string)$row['vocabulary_meaning']));if($meaning!=='')$pool[$row['episode_id']][$meaning]=trim((string)$row['vocabulary_meaning']);}
$update=$pdo->prepare('UPDATE learn_english_rpg_scenes SET choices_json=? WHERE id=?');$changed=0;
foreach($rows as $row){$choices=json_decode($row['choices_json'],true);if(!is_array($choices))continue;$used=[];foreach($choices as $choice)$used[]=mb_strtolower(trim((string)($choice['text']??'')));$candidates=array_values(array_diff($pool[$row['episode_id']]??[],[(string)$row['vocabulary_meaning']]));$cursor=0;$dirty=false;
    foreach($choices as &$choice){$text=mb_strtolower(trim((string)($choice['text']??'')));if(in_array($text,['berlari cepat','sangat jauh'],true)){while(isset($candidates[$cursor])&&in_array(mb_strtolower($candidates[$cursor]),$used,true))$cursor++;if(isset($candidates[$cursor])){$replacement=$candidates[$cursor++];$used[] = mb_strtolower($replacement);$choice['text']=$replacement;$choice['feedback']='Arti ini tidak sesuai dengan kata kunci pada adegan.';$dirty=true;}}}unset($choice);
    if($dirty){$update->execute([json_encode($choices,JSON_UNESCAPED_UNICODE),$row['id']]);$changed++;}
}
echo "Contextualized distractors in $changed scenes.\n";
