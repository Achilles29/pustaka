<?php
require __DIR__.'/library_network_bootstrap.php';require __DIR__.'/iplm_task7_support.php';
$source=DB();$name='pustaka_network_test_task7rollback_'.date('Ymd_His');$dir='/tmp/'.$name;network_check(mkdir($dir,0700),'Fixture directory failed');
network_query($source,'CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$params=['hostname'=>$source->hostname,'username'=>$source->username,'password'=>$source->password,'database'=>$name,'dbdriver'=>'mysqli','db_debug'=>false,'char_set'=>'utf8mb4','dbcollat'=>'utf8mb4_unicode_ci'];$db=DB($params);
file_put_contents($dir.'/connection.php','<?php return '.var_export($params,true).';');network_query($db,'SET FOREIGN_KEY_CHECKS=0');
foreach(['libraries','library_types','library_subtypes','ref_districts','ref_villages','library_source_records','auth_user','auth_user_role','iplm_submissions','iplm_periods','iplm_history']as$t){$create=$source->query('SHOW CREATE TABLE '.$t)->row_array();network_query($db,$create['Create Table']);if(!in_array($t,['auth_user','auth_user_role','iplm_submissions','iplm_history'],true))network_query($db,'INSERT INTO '.$t.' SELECT * FROM `'.$source->database.'`.'.$t);}
network_query($db,'SET FOREIGN_KEY_CHECKS=1');
$plans=task7_plan($db);$last=end($plans)['source'];network_check($db->insert('library_source_records',['source_system'=>'dapodik_task7','source_id'=>$last['code'],'source_row'=>$last['row'],'classification'=>'test','decision'=>'TEST_CONFLICT','source_json'=>'{}','source_sha256'=>str_repeat('0',64)]),'Conflict fixture failed');
$before=$db->order_by('id')->get('libraries')->result_array();$archives=$db->count_all('library_source_records');
$p=proc_open([PHP_BINARY,__DIR__.'/setup_iplm_task7.php','--apply','--test='.$dir],[0=>['file','/dev/null','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);$exit=proc_close($p);
network_check($exit!==0&&strpos($err,'School archive failed')!==false,'Expected late archive conflict');
$after=$db->order_by('id')->get('libraries')->result_array();if(!array_key_exists('iplm_population_status',$before[0]))foreach($after as&$row)unset($row['iplm_population_status']);unset($row);network_check($before===$after,'Migration did not roll back master changes');network_check($archives===$db->count_all('library_source_records'),'Migration did not roll back archives');network_check(!$db->where('event','task7.applied')->count_all_results('iplm_history'),'Failed batch marked applied');
file_put_contents($dir.'/rollback-test.json',json_encode(['passed'=>4,'failure'=>'Late unique-source conflict','master_unchanged'=>true,'archives_unchanged'=>true],JSON_PRETTY_PRINT));echo "4 rollback checks passed. Fixture: ".$dir."\n";
