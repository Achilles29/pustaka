<?php
require __DIR__.'/library_network_bootstrap.php';
$dir=null;foreach($argv as$arg)if(strpos($arg,'--test=')===0)$dir=substr($arg,7);
if($dir!==null){network_check(preg_match('#^/tmp/pustaka_network_test_[a-zA-Z0-9_]+$#D',$dir)&&is_file($dir.'/connection.php'),'Private fixture required');$params=require $dir.'/connection.php';network_check(strpos($params['database'],'pustaka_network_test_')===0,'Test DB required');$db=DB($params);}else$db=DB();
$db->db_debug=false;
$codes=['perpusda'=>'kabupaten','desa'=>'desa_kelurahan','komunitas'=>'tbm'];
$rows=$db->where_in('code',array_keys($codes))->get('library_types')->result_array();
if(!in_array('--apply',$argv,true)){echo json_encode(['remove'=>$rows,'replaced_by_subtypes'=>$codes],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).PHP_EOL;exit;}
if(!$rows){echo "Already cleaned; no rows changed.\n";exit;}
$backup=$dir?null:network_backup($db,'tools/cleanup_library_taxonomy.php');
if($backup)echo 'Backup: '.$backup.'.sql.gz'.PHP_EOL;
$db->trans_begin();try{
    $rows=network_query($db,"SELECT * FROM library_types WHERE code IN ('perpusda','desa','komunitas') FOR UPDATE")->result_array();
    $ids=array_column($rows,'id');$old=[];
    foreach(['libraries','library_subtypes','auth_user','iplm_submissions','iplm_fields','iplm_periods']as$table)$old[$table]=$db->order_by('id')->get($table)->result_array();
    foreach($rows as$row){
        network_check(!$db->where('library_type_id',$row['id'])->count_all_results('libraries'),'Type still used by a library; explicit remapping required');
        network_check(!$db->where('library_type_id',$row['id'])->count_all_results('library_subtypes'),'Type still owns subtypes');
        $replacement=$db->select('s.id,t.code type_code')->from('library_subtypes s')->join('library_types t','t.id=s.library_type_id')->where('s.code',$codes[$row['code']])->get()->row_array();
        network_check($replacement&&$replacement['type_code']==='umum','Canonical replacement missing');
    }
    foreach($old['iplm_submissions']as$form)foreach(['values_json','baseline_json']as$key){$value=json_decode($form[$key],true);network_check(!isset($value['library_type_id'])||!in_array((string)$value['library_type_id'],$ids,true),'Saved IPLM identity still references a retired type; review required');}
    foreach($rows as$row)network_check($db->where('id',$row['id'])->where('code',$row['code'])->delete('library_types'),'Cannot remove duplicate reference');
    foreach($old as$table=>$data)network_check($data===$db->order_by('id')->get($table)->result_array(),'Unrelated data changed: '.$table);
    $payload=['removed'=>$rows,'replacement_subtypes'=>$codes,'backup'=>$backup?$backup.'.sql.gz':null,'libraries_preserved'=>count($old['libraries']),'source'=>'user: legenda dua kolom dan hapus jenis yang menduplikasi subjenis'];
    network_check($db->insert('iplm_history',['actor_id'=>0,'event'=>'taxonomy.duplicates.removed','payload_json'=>json_encode($payload,JSON_UNESCAPED_UNICODE)]),'Cannot archive removed references');
    network_check($db->trans_status()&&$db->trans_commit(),'Cleanup transaction failed');
}catch(Throwable$e){$db->trans_rollback();throw$e;}
if($backup)file_put_contents($backup.'.taxonomy.json',json_encode($payload,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
echo json_encode($payload,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).PHP_EOL;
