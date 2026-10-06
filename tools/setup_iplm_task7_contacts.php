<?php
// Fill only previously empty, syntactically valid contact fields on unambiguous task7 matches.
require __DIR__.'/library_network_bootstrap.php';$dir=null;foreach($argv as$a)if(strpos($a,'--test=')===0)$dir=substr($a,7);
if($dir){network_check(preg_match('#^/tmp/pustaka_network_test_[a-zA-Z0-9_]+$#D',$dir)&&is_file($dir.'/connection.php'),'Private fixture required');$p=require $dir.'/connection.php';network_check(strpos($p['database'],'pustaka_network_test_')===0,'Test database required');$db=DB($p);}else$db=DB();$db->db_debug=false;$apply=in_array('--apply',$argv,true);
if($apply){network_check((int)network_query($db,'SELECT GET_LOCK(?,10) acquired',[$db->database.':task7contacts'])->row()->acquired===1,'Contact migration is already running');register_shutdown_function(function()use($db){$db->query('SELECT RELEASE_LOCK(?)',[$db->database.':task7contacts']);});}
if($db->where('event','task7.contacts.applied')->count_all_results('iplm_history')){echo "Already applied; local contact edits preserved.\n";exit;}
network_check($db->where('event','task7.applied')->count_all_results('iplm_history'),'Run task7 reconciliation first');
$backup=null;if($apply&&!$dir)$backup=network_backup($db,'docs/iplm/task7.md');
$rows=$db->where('source_system','pendataan_task7')->where('decision','PADANAN')->get('library_source_records')->result_array();$changes=[];$counts=[];
if($apply)$db->trans_begin();
try{
 foreach($rows as$a){
  // Multiple records for a unit require the user's choice, not first-row precedence for contact details.
  if($db->where('source_system','pendataan_task7')->where('library_id',$a['library_id'])->count_all_results('library_source_records')!==1)continue;
  $l=network_query($db,'SELECT * FROM libraries WHERE id=?'.($apply?' FOR UPDATE':''),[$a['library_id']])->row_array();$s=json_decode($a['source_json'],true)['source'];$values=[];
  foreach(['phone'=>'Telepon','email'=>'Email','website'=>'Website']as$k=>$col){
   if(trim((string)$l[$k])!=='')continue;$v=trim($s[$col]??'');if($v===''||mb_strlen($v)>($k==='phone'?40:180))continue;
   if($k==='phone'){$v=preg_replace('/[\s().-]/','',$v);if(!preg_match('/^(?:\+?62|0)8[0-9]{7,12}$/D',$v))continue;}
   if($k==='email'&&!filter_var($v,FILTER_VALIDATE_EMAIL))continue;
   if($k==='website'&&(!filter_var($v,FILTER_VALIDATE_URL)||strtolower(parse_url($v,PHP_URL_SCHEME)??'')!=='https'||parse_url($v,PHP_URL_USER)||parse_url($v,PHP_URL_PASS)))continue;
   $values[$k]=$v;$counts[$k]=($counts[$k]??0)+1;
  }
  if($values){$changes[]=['library_id'=>$l['id'],'source_id'=>$s['Id'],'before'=>array_intersect_key($l,$values),'after'=>$values];if($apply)network_check($db->where('id',$l['id'])->update('libraries',$values),'Contact enrichment failed');}
 }
 $result=['applied'=>$apply,'libraries'=>count($changes),'fields'=>$counts,'backup'=>$backup?$backup.'.sql.gz':null];
 if($apply){network_check($db->insert('iplm_history',['actor_id'=>0,'event'=>'task7.contacts.applied','payload_json'=>json_encode(['summary'=>$result,'changes'=>$changes],JSON_UNESCAPED_UNICODE)]),'Contact audit failed');network_check($db->trans_status()&&$db->trans_commit(),'Contact commit failed');file_put_contents(($dir?:FCPATH.'docs/iplm').'/kontak-task7.json',json_encode($result,JSON_PRETTY_PRINT));}
}catch(Throwable$e){if($apply)$db->trans_rollback();throw$e;}
echo json_encode($result,JSON_PRETTY_PRINT),"\n";
