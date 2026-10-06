<?php
require __DIR__.'/library_network_bootstrap.php';
require __DIR__.'/iplm_task5_support.php';
require APPPATH.'models/Library_model.php';
$dir=$argv[1]??'';
network_check(preg_match('#^/tmp/pustaka_network_test_[a-zA-Z0-9_]+$#D',$dir)&&is_file($dir.'/connection.php'),'Private fixture required');
$params=require $dir.'/connection.php'; network_check(strpos($params['database'],'pustaka_network_test_')===0,'Test DB required');
$db=DB($params); $db->db_debug=false; $checks=0;
function ok5($condition,$label){global $checks;network_check($condition,'FAIL '.$label);$checks++;echo 'PASS '.$label.PHP_EOL;}
function run5($dir){$p=proc_open(['php',__DIR__.'/setup_iplm_task5.php','--apply','--test='.$dir],[0=>['file','/dev/null','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);$exit=proc_close($p);return [$exit,$out,$err];}
$rows=task5_school_rows();ok5(count($rows)===1339,'Read all 1,339 schools without 500-row truncation');
ok5(count(array_unique(array_column($rows,'NPSN')))===1339,'Source NPSN unique');
$plan=task5_plan($db);$statuses=array_count_values(array_column($plan,'status'));
ok5(($statuses['PERBARUI']??0)===42,'42 deterministic updates identified');
ok5(($statuses['TAHAN_KONFLIK_IDENTITAS']??0)===1,'Conflicting NPSN held');
ok5(($statuses['TAHAN_WILAYAH']??0)===1,'Conflicting district/village held');
ok5(($statuses['TAHAN_REFERENSI']??0)===1,'SLB versus SDLB held');
ok5(($statuses['BELUM_TERDAFTAR']??0)===871,'Unregistered schools are report-only');
$model=new Library_model();$model->db=$db;
foreach($db->get('library_types')->result_array()as$type){$filter=['type_id'=>$type['id']];ok5(count($model->get_map_libraries(null,false,$filter))===$model->count_libraries($filter),'Type filter applies to complete map: '.$type['code']);}
foreach($db->get('library_subtypes')->result_array()as$sub){$filter=['type_id'=>$sub['library_type_id'],'subtype_id'=>$sub['id']];ok5(count($model->get_map_libraries(null,false,$filter))===$model->count_libraries($filter),'Subtype map matches table count: '.$sub['code']);}
ok5($model->get_map_libraries(0,false,['type_id'=>2])===[],'Invalid scope never becomes global');
$library=$db->where('id',4)->get('libraries')->row_array();
ok5(count($model->get_map_libraries(4,false,['type_id'=>$library['library_type_id']]))===1,'Scoped map retains matching library');
ok5($model->get_map_libraries(4,false,['type_id'=>999999])===[],'Type filter cannot escape scope');
$db->insert('iplm_periods',['year'=>2026,'title'=>'IPLM 2026 — data tahun 2025','start_date'=>'2025-01-01','end_date'=>'2025-12-31','dates_confirmed'=>1,'population'=>403,'population_note'=>'Fixture snapshot','state'=>'draft','version'=>2,'population_mode'=>'auto','population_registry_count'=>403]);$pid=$db->insert_id();
$sub=['library_id'=>4,'period_id'=>$pid,'schema_json'=>'[]','values_json'=>'{"library_users":"123"}','evidence_json'=>'{"folder":"https://example.org/proof"}','baseline_json'=>'{}','resolutions_json'=>'{}','status'=>'verified','created_by'=>9000000,'updated_by'=>9000000];network_check($db->insert('iplm_submissions',$sub),'Fixture submission failed');$sid=$db->insert_id();
$before=$db->order_by('id')->get('libraries')->result_array();
[$exit,$out,$err]=run5($dir);ok5($exit!==0&&strpos($err,'Submitted/verified')!==false,'Verified submissions prevent date rewrite');
ok5($before===$db->order_by('id')->get('libraries')->result_array(),'Failed period correction rolls back all library changes');
ok5($db->where('source_system','sekolah_task5')->count_all_results('library_source_records')===0,'Failed transaction leaves no archives');
$db->where('id',$sid)->update('iplm_submissions',['status'=>'draft']);
$users=$db->order_by('id')->get('auth_user')->result_array();
[$exit,$out,$err]=run5($dir);ok5($exit===0,'Reconciliation applies to isolated DB: '.$err);
file_put_contents($dir.'/task5-apply-output.json',$out);
$after=$db->order_by('id')->get('libraries')->result_array();$diff=0;
foreach($before as$i=>$l){if($l!==$after[$i])$diff++;foreach(['id','address','village_id','latitude','longitude','manager_name','phone','is_verified','source_id']as$key)ok5($l[$key]===$after[$i][$key],'Protected '.$key.' library '.$l['id']);}
ok5($diff===42,'Exactly 42 existing masters changed, no inserts/deletes');
ok5($users===$db->order_by('id')->get('auth_user')->result_array(),'Accounts/passwords preserved');
$now=$db->where('id',$sid)->get('iplm_submissions')->row_array();
foreach(['schema_json','values_json','evidence_json','baseline_json']as$key)ok5($now[$key]===$sub[$key],'Old IPLM '.$key.' preserved');
ok5($now['status']==='revision'&&strpos($now['review_note'],'2026')!==false,'Existing form requires review for revised year');
$period=$db->where('id',$pid)->get('iplm_periods')->row_array();
ok5($period['start_date']==='2026-01-01'&&$period['end_date']==='2026-12-31'&&$period['population']==='403'&&$period['state']==='draft','Year corrected without changing population/status');
ok5($db->where('source_system','sekolah_task5')->count_all_results('library_source_records')===1339,'All source rows archived with decisions');
ok5($db->where('id',1624)->get('libraries')->row()->code==='20315667','Conflicting identity not overwritten');
$archiveCount=$db->count_all('library_source_records');$historyCount=$db->count_all('iplm_history');
[$exit,$out,$err]=run5($dir);ok5($exit===0&&strpos($out,'Already applied')!==false,'Second apply is no-op');
ok5($archiveCount===$db->count_all('library_source_records')&&$historyCount===$db->count_all('iplm_history')&&$after===$db->order_by('id')->get('libraries')->result_array(),'Rerun preserves data/audit history');
$zip=new ZipArchive();ok5($zip->open($dir.'/PERSANDINGAN_SEKOLAH_TASK5.xlsx')===true,'Comparison is valid XLSX');$xml=simplexml_load_string($zip->getFromName('xl/worksheets/sheet1.xml'));ok5(count($xml->sheetData->row)>1339,'Comparison includes every source plus database-only rows');ok5(strpos($zip->getFromName('xl/worksheets/sheet1.xml'),'<f>')===false,'No formula interpretation in exported names');$zip->close();
file_put_contents($dir.'/task5-model-results.json',json_encode(['checks'=>$checks,'statuses'=>$statuses]));echo $checks.' checks passed.'.PHP_EOL;
