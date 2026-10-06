<?php
require __DIR__.'/library_network_bootstrap.php';
$dir=null;foreach($argv as$arg)if(strpos($arg,'--test=')===0)$dir=substr($arg,7);
if($dir!==null){network_check(preg_match('#^/tmp/pustaka_network_test_[a-zA-Z0-9_]+$#D',$dir)&&is_file($dir.'/connection.php'),'Private fixture required');$params=require $dir.'/connection.php';network_check(strpos($params['database'],'pustaka_network_test_')===0,'Isolated DB required');$db=DB($params);}else$db=DB();$db->db_debug=false;
if(!in_array('--apply',$argv,true)){echo "DRY RUN: enable self-registration archive, retain accounts/credentials and all library data.\n";exit;}
if($db->where('event','task6.self_registration.enabled')->count_all_results('iplm_history')){echo "Already installed; data preserved.\n";exit;}
$backup=$dir?null:network_backup($db,'sql/2026-10-06a_library_self_registration.sql');if($backup)echo 'Backup: '.$backup.'.sql.gz'.PHP_EOL;
$before=[];foreach(['libraries','auth_user','iplm_submissions','iplm_periods']as$table)$before[$table]=hash('sha256',json_encode($db->order_by('id')->get($table)->result_array()));
foreach(preg_split('/;\s*(?:\r?\n|$)/',file_get_contents(FCPATH.'sql/2026-10-06a_library_self_registration.sql'))as$sql)if(trim($sql)!=='')network_query($db,$sql);
$db->trans_begin();try{
    network_check($db->where('code','library.activation')->update('sys_page',['title'=>'Aktivasi & Moderasi Perpustakaan']),'Page update failed');
    network_check($db->where('menu_key','library.activation')->update('sys_menu',['title'=>'Aktivasi & Moderasi']),'Menu update failed');
    network_check($db->insert('iplm_history',['actor_id'=>0,'event'=>'task6.self_registration.enabled','payload_json'=>json_encode(['source'=>'docs/iplm/task6.md','self_declared_ownership'=>true,'requires_county_approval'=>false,'backup'=>$backup?$backup.'.sql.gz':null])]),'Audit failed');
    network_check($db->trans_status()&&$db->trans_commit(),'Task6 failed');
}catch(Throwable$e){$db->trans_rollback();throw$e;}
$preserved=[];foreach($before as$table=>$hash)$preserved[$table]=$hash===hash('sha256',json_encode($db->order_by('id')->get($table)->result_array()));
$result=['enabled'=>true,'preserved'=>$preserved,'backup'=>$backup?$backup.'.sql.gz':null];if($backup)file_put_contents($backup.'.task6.json',json_encode($result,JSON_PRETTY_PRINT));echo json_encode($result,JSON_PRETTY_PRINT).PHP_EOL;
