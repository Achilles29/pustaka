<?php
require __DIR__.'/library_network_bootstrap.php';
$dir=null;foreach($argv as$a)if(strpos($a,'--test=')===0)$dir=substr($a,7);
if($dir!==null){network_check(preg_match('#^/tmp/pustaka_network_test_[a-zA-Z0-9_]+$#D',$dir)&&is_file($dir.'/connection.php'),'Isolated directory required.');$params=require $dir.'/connection.php';network_check(strpos($params['database'],'pustaka_network_test_')===0,'Isolated DB required.');$db=DB($params);}else$db=DB('default');$db->db_debug=false;
if(!in_array('--apply',$argv,true)){echo "DRY RUN: manual population mode, preserving existing population and all evidence.\n";exit;}
$snapshot=function()use($db){$out=[];foreach(['libraries','auth_user','iplm_submissions','iplm_fields']as$table)$out[$table]=hash('sha256',json_encode(network_query($db,'SELECT * FROM `'.$table.'` ORDER BY id')->result_array()));$out['period_values']=hash('sha256',json_encode(network_query($db,'SELECT id,population,population_note,state,start_date,end_date,version FROM iplm_periods ORDER BY id')->result_array()));return $out;};$before=$snapshot();
if(!$dir){$backup=network_backup($db,'sql/2026-10-05e_iplm_task3.sql');echo 'Backup: '.$backup.'.sql.gz',PHP_EOL;}
foreach(preg_split('/;\s*(?:\r?\n|$)/',file_get_contents(FCPATH.'sql/2026-10-05e_iplm_task3.sql'))as$sql)if(trim($sql)!=='')network_query($db,$sql);
network_query($db,"UPDATE iplm_periods SET population_registry_count=population WHERE population_mode='auto' AND population_registry_count IS NULL");
network_check($before===$snapshot(),'Concurrent data change detected; review before claiming unchanged data.');
echo "Population mode ready; existing figures and submission links unchanged.\n";
