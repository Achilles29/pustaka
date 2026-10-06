<?php
require __DIR__.'/library_network_bootstrap.php';require __DIR__.'/iplm_task7_support.php';require APPPATH.'libraries/Catalog_xlsx.php';
$dir=null;foreach($argv as$a)if(strpos($a,'--test=')===0)$dir=substr($a,7);
if($dir){network_check(preg_match('#^/tmp/pustaka_network_test_[a-zA-Z0-9_]+$#D',$dir)&&is_file($dir.'/connection.php'),'Private fixture required');$params=require $dir.'/connection.php';network_check(strpos($params['database'],'pustaka_network_test_')===0,'Test DB required');$db=DB($params);}else $db=DB();
$db->db_debug=false;$apply=in_array('--apply',$argv,true);$folder=$dir?:FCPATH.'docs/iplm';
if($db->where('event','task7.applied')->count_all_results('iplm_history')){echo "Already applied; subsequent edits preserved.\n";exit;}
$plan=task7_plan($db);$portal=is_file(FCPATH.'docs/iplm/verifikasi-portal-task7.json')?json_decode(file_get_contents(FCPATH.'docs/iplm/verifikasi-portal-task7.json'),true):[];
$summary=['applied'=>$apply,'schools'=>count($plan),'before'=>$db->count_all('libraries'),'school_statuses'=>[],'gps_from_portal'=>0,'legacy_statuses'=>[],'surveys_imported'=>0];$backup=null;
if($apply){
 network_check((int)network_query($db,'SELECT GET_LOCK(?,10) acquired',[$db->database.':task7'])->row()->acquired===1,'Another task7 migration is running');
 register_shutdown_function(function()use($db){$db->query('SELECT RELEASE_LOCK(?)',[$db->database.':task7']);});
 if($db->where('event','task7.applied')->count_all_results('iplm_history')){echo "Already applied; subsequent edits preserved.\n";exit;}
 if(!$dir){$backup=network_backup($db,'docs/iplm/task7.md');$summary['backup']=$backup.'.sql.gz';}
 foreach(preg_split('/;\s*(?:\r?\n|$)/',file_get_contents(FCPATH.'sql/2026-10-06b_iplm_task7.sql'))as$sql)if(trim($sql)!=='')network_query($db,$sql);
 if(!$db->field_exists('iplm_population_status','libraries'))network_query($db,"ALTER TABLE libraries ADD iplm_population_status ENUM('pending','included','excluded') NOT NULL DEFAULT 'pending'");
 $db->trans_begin();
 try{
  $before=array_column(network_query($db,'SELECT * FROM libraries ORDER BY id FOR UPDATE')->result_array(),null,'id');$plan=task7_plan($db);$protected=[];
  foreach(['auth_user','auth_user_role','iplm_submissions','iplm_periods']as$t)$protected[$t]=network_query($db,'SELECT * FROM '.$t.' ORDER BY '.($t==='auth_user_role'?'user_id,role_id':'id'))->result_array();
  $types=array_column($db->get('library_types')->result_array(),'id','code');$subs=array_column($db->get('library_subtypes')->result_array(),null,'code');
  foreach($plan as$p){$code=str_replace(' ','_',$p['source']['subtype']);if(!isset($subs[$code])){network_check($db->insert('library_subtypes',['library_type_id'=>$types['sekolah'],'code'=>$code,'name'=>'Perpustakaan '.strtoupper($p['source']['subtype']),'iplm_eligible'=>0,'sort_order'=>80,'description'=>'Master institusi Dapodik; tidak otomatis menjadi populasi IPLM.']),'Subtype failed');$subs[$code]=$db->where('id',$db->insert_id())->get('library_subtypes')->row_array();}}
  $districts=[];foreach($db->where('regency_code','3317')->get('ref_districts')->result_array()as$d)$districts[task7_key($d['name'])]=$d;
  $villages=[];foreach($db->get('ref_villages')->result_array()as$v)$villages[$v['district_id']][task7_key($v['name'])]=$v;
  // Release the explicitly approved erroneous NPSN before assigning it to Muhammadiyah.
  network_check(($before[1624]['code']??'')==='20315667','Yos Sudarso state changed; review required');
  network_check(!$db->where('code','20315657')->where('id !=',1624)->count_all_results('libraries'),'Target NPSN already occupied');
  network_check($db->where('id',1624)->update('libraries',['code'=>'20315657']),'NPSN correction failed');
  foreach($plan as&$p){
   $s=$p['source'];$id=$p['library_id'];$old=$id?$before[$id]:null;$web=$portal[$s['code']]??null;$p['portal_status']=$web['status']??'not_requested';
   if($web&&$web['status']==='ok'){
    $f=$web['fields'];$same=task7_key($f['Nama'])===task7_key($s['institution_name']);
    $same=$same||!!array_intersect(task5_names($f['Nama'],$s['district']),task5_names($s['institution_name'],$s['district']));
    $p['portal_url']=$web['url'];
    if(!$same||strpos(strtoupper($f['Kab.-Kota/Negara (LN)']),'REMBANG')===false){
     if($p['status']==='IMPORT'){$p['status']='TAHAN_PORTAL';$p['note'].=' Identitas/wilayah portal berbeda; sumber tetap diarsipkan.';}
    }else{
     if(task7_gps($web['latitude']??'',$web['longitude']??'')){$s['latitude']=$web['latitude'];$s['longitude']=$web['longitude'];$s['gps_valid']=true;$p['gps_source']='portal';}
     if(!$old){$s['address']=$f['Alamat'];$s['village']=$f['Desa/Kelurahan'];$s['district']=preg_replace('/^KEC\.\s*/i','',$f['Kecamatan/Kota (LN)']);}
    }
   }
   if(!isset($p['gps_source']))$p['gps_source']=$s['gps_valid']?'workbook':'unavailable';
   $p['source_enriched']=$s;
   if(in_array($p['status'],['IMPORT','PADANAN'],true)){
    $d=$districts[task7_key($s['district'])]??null;$v=$d?($villages[$d['id']][task7_key($s['village']??'')]??null):null;
    if(!$d){$p['status']='TAHAN_WILAYAH';$p['note'].=' Kecamatan tidak sesuai referensi.';}
    elseif(!$old){
     network_check(!$db->where('code',$s['code'])->count_all_results('libraries'),'Duplicate code rejected');
     $values=['library_type_id'=>$types['sekolah'],'library_subtype_id'=>$subs[str_replace(' ','_',$s['subtype'])]['id'],'code'=>$s['code'],'name'=>$s['institution_name'],'institution_name'=>$s['institution_name'],'institution_status'=>$s['institution_status'],'address'=>$s['address']??null,'district_id'=>$d['id'],'district'=>$d['name'],'village_id'=>$v['id']??null,'village'=>$v['name']??($s['village']??null),'latitude'=>$s['gps_valid']?$s['latitude']:0,'longitude'=>$s['gps_valid']?$s['longitude']:0,'service_radius_meters'=>50,'status'=>'active','is_verified'=>0,'iplm_population_status'=>'pending','source_system'=>'dapodik_task7','source_id'=>$s['code'],'description'=>'Unit institusi Dapodik. Keberadaan perpustakaan dan kewenangan IPLM belum dikonfirmasi.'];
     network_check($db->insert('libraries',$values),'School insert failed');$id=(int)$db->insert_id();$p['library_id']=$id;$p['changes']=$values;
    }else{
     $values=$p['changes'];$sub=$subs[str_replace(' ','_',$s['subtype'])];if((int)$old['library_subtype_id']!==(int)$sub['id']){$values['library_type_id']=$sub['library_type_id'];$values['library_subtype_id']=$sub['id'];}
     // Existing addresses/villages remain intact; YPI's explicitly retained address is never replaced.
     if(($id===1644||!task7_gps($old['latitude'],$old['longitude']))&&$s['gps_valid']){$values['latitude']=$s['latitude'];$values['longitude']=$s['longitude'];}
     if(!$old['address']&&!empty($s['address']))$values['address']=$s['address'];
     if(!$old['village_id']&&$v&&(int)$old['district_id']===(int)$d['id']){$values['village_id']=$v['id'];$values['village']=$v['name'];}
     if($values)network_check($db->where('id',$id)->update('libraries',$values),'School update failed');$p['changes']=$values;
    }
    if($p['gps_source']==='portal'&&isset($p['changes']['latitude']))$summary['gps_from_portal']++;
   }
   network_check($db->insert('library_source_records',['source_system'=>'dapodik_task7','source_id'=>$s['code'],'library_id'=>$id,'source_row'=>$s['row'],'classification'=>'sekolah/'.$s['subtype'],'decision'=>$p['status'],'note'=>$p['note'],'source_json'=>json_encode(['source'=>$s,'portal'=>$web,'before'=>$old,'changes'=>$p['changes']],JSON_UNESCAPED_UNICODE),'source_sha256'=>hash_file('sha256',FCPATH.'docs/iplm/sumber-task7.json')]),'School archive failed');
  }unset($p);
  // Reconcile pendataan against canonical institutions, never promote its NPSN over Dapodik.
  $master=$db->order_by('id')->get('libraries')->result_array();$byId=array_column($master,null,'id');$names=[];
  foreach($master as$l)foreach([$l['institution_name'],$l['name']]as$n)if($n)foreach(task5_names($n,$l['district']??'')as$key)$names[$key.'|'.task7_key($l['district']??'')][$l['id']]=$l;
  $legacy=[];$used=[];
  foreach($db->where('source_system','pendataan_task4')->order_by('source_row')->get('library_source_records')->result_array()as$a){
   $s=json_decode($a['source_json'],true);$id=$a['library_id']?(int)$a['library_id']:null;$c=[];$note='Padanan terdahulu dipertahankan.';
   if(!$id&&$s['Jenis']==='SEKOLAH'){
    foreach([$s['Lembaga Induk'],$s['Nama']]as$name)foreach(task5_names($name,$s['Kecamatan'])as$key)$c+=$names[$key.'|'.task7_key($s['Kecamatan'])]??[];
    if(!$c && trim($s['Kelurahan'])!==''){
     // A one-letter spelling discrepancy needs the same district, village and school number.
     foreach($master as$candidate){
      if(!preg_match('/^\d{8}$/D',$candidate['code'])||task7_key($candidate['district']??'')!==task7_key($s['Kecamatan'])||task7_key($candidate['village']??'')!==task7_key($s['Kelurahan']))continue;
      foreach(task5_names($s['Lembaga Induk'],$s['Kecamatan'])as$x)foreach(task5_names($candidate['institution_name']?:$candidate['name'],$candidate['district'])as$y){
       preg_match_all('/[0-9]+/',$x,$nx);preg_match_all('/[0-9]+/',$y,$ny);
       if($nx[0]===$ny[0]&&levenshtein($x,$y)<=1)$c[$candidate['id']]=$candidate;
      }
     }
     if(count($c)===1)$note='Ejaan berbeda satu huruf; nomor sekolah, kecamatan dan desa sama, kandidat Dapodik tunggal. Identitas Dapodik dipertahankan.';
    }
    if(count($c)===1){$id=(int)array_key_first($c);if($note==='Padanan terdahulu dipertahankan.')$note='Nama institusi dan kecamatan cocok unik setelah penyandingan Dapodik.';}
   }
   $status=$id?'PADANAN':'TAHAN_VERIFIKASI';
   if($id&&isset($used[$id])){$status='TAHAN_GANDA';$note='Lebih dari satu sumber untuk satu unit; isian tidak ditimpa.';}elseif($id){
    $used[$id]=true;$l=$byId[$id];$values=[];
    foreach(['npp'=>'Npp','opening_hours'=>'Jam Layanan']as$k=>$col)if(!$l[$k]&&($s[$col]??'')!==''&&mb_strlen($s[$col])<=($k==='npp'?80:180))$values[$k]=$s[$col];
    // Keep every preexisting account, identity and named library; only fill nonidentity gaps.
    if($values)network_check($db->where('id',$id)->update('libraries',$values),'Pendataan enrichment failed');
    if(!$db->where('library_id',$id)->count_all_results('library_survey_profiles')){
     network_check($db->insert('library_survey_profiles',['library_id'=>$id,'values_json'=>json_encode(task7_legacy_values($s),JSON_UNESCAPED_UNICODE),'version'=>1]),'Survey import failed');$summary['surveys_imported']++;
    }
   }
   $legacy[]=['source'=>$s,'library_id'=>$id,'status'=>$status,'note'=>$note,'candidates'=>array_keys($c)];
   network_check($db->insert('library_source_records',['source_system'=>'pendataan_task7','source_id'=>$s['Id'],'library_id'=>$id,'source_row'=>$a['source_row'],'classification'=>$a['classification'],'decision'=>$status,'note'=>$note,'source_json'=>json_encode(['source'=>$s,'previous_archive_id'=>$a['id'],'candidates'=>array_keys($c)],JSON_UNESCAPED_UNICODE),'source_sha256'=>$a['source_sha256']]),'Legacy archive failed');
  }
  foreach($protected as$t=>$rows)network_check($rows===network_query($db,'SELECT * FROM '.$t.' ORDER BY '.($t==='auth_user_role'?'user_id,role_id':'id'))->result_array(),'Protected data changed: '.$t);
  foreach($before as$id=>$l){$now=$db->where('id',$id)->get('libraries')->row_array();network_check($now!==null,'Existing ID lost');foreach(['name','source_system','source_id','status','manager_name','phone','email','website','description','facilities','is_verified']as$k)network_check($now[$k]===$l[$k],'Protected master changed '.$id.' '.$k);}
  foreach([1638,1602]as$id){$now=$db->where('id',$id)->get('libraries')->row_array();foreach(['code','institution_name','address','district','village','latitude','longitude']as$k)network_check($now[$k]===$before[$id][$k],'Explicitly retained school changed');}
  network_check($db->insert('iplm_history',['actor_id'=>0,'event'=>'task7.applied','payload_json'=>json_encode(['backup'=>$backup,'hashes'=>task7_sources()['hashes'],'population'=>'Pending selection; period snapshots preserved.'],JSON_UNESCAPED_UNICODE)]),'History failed');
  network_check($db->trans_status()&&$db->trans_commit(),'Task7 commit failed');
 }catch(Throwable$e){$db->trans_rollback();throw$e;}
}
foreach($plan as$p)$summary['school_statuses'][$p['status']]=($summary['school_statuses'][$p['status']]??0)+1;
foreach($legacy??[]as$p)$summary['legacy_statuses'][$p['status']]=($summary['legacy_statuses'][$p['status']]??0)+1;
$summary['after']=$db->count_all('libraries');$summary['portal_available']=count(array_filter($portal,function($r){return $r['status']==='ok';}));
if($apply||in_array('--report',$argv,true)){
 file_put_contents($folder.'/ringkasan-task7.json',json_encode($summary,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));chmod($folder.'/ringkasan-task7.json',0600);
 file_put_contents($folder.'/penyandingan-task7.json',json_encode(['schools'=>$plan,'legacy'=>$legacy??[]],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));chmod($folder.'/penyandingan-task7.json',0600);
 $current=array_column(network_query($db,'SELECT l.*,s.name subtype_name FROM libraries l LEFT JOIN library_subtypes s ON s.id=l.library_subtype_id')->result_array(),null,'id');
 $lines=[];foreach($plan as$p){$s=$p['source_enriched']??$p['source'];$m=$current[$p['library_id']]??[];
  $lines[]=[ $m['id']??'', $m['code']??'', $m['name']??'', $m['institution_name']??'', $m['subtype_name']??'', $m['district']??'', $m['village']??'', $m['address']??'', isset($m['latitude'])?$m['latitude'].', '.$m['longitude']:'',
   $s['row'],$s['code'],$s['institution_name'],$s['subtype'],$s['district'],$s['village']??'',$s['address']??'',$s['gps_valid']?($s['latitude'].', '.$s['longitude']):'Belum valid',$s['source'],
   $p['status'],implode(', ',array_keys($p['changes'])),$p['gps_source']??'',$p['note'],$p['portal_url']??'',$p['portal_status']??'' ];
 }
 $writer=new Catalog_xlsx();$path=$writer->build('Penyandingan sekolah task7',['ID database','NPSN database','Nama perpustakaan database','Institusi database','Subjenis database','Kecamatan database','Desa database','Alamat database','GPS database','Baris sumber','NPSN sumber','Institusi sumber','Jenjang sumber','Kecamatan sumber','Desa sumber','Alamat sumber','GPS sumber valid','Workbook','Keputusan','Kolom diperbarui','Sumber GPS','Catatan','Portal resmi','Status portal'],$lines);task4_style_workbook($path);network_check(copy($path,$folder.'/PERSANDINGAN_SEKOLAH_TASK7.xlsx'),'Report write failed');chmod($folder.'/PERSANDINGAN_SEKOLAH_TASK7.xlsx',0600);unlink($path);

 $lines=[];foreach($legacy??[]as$p){$s=$p['source'];$lines[]=[$p['library_id'],$s['Id'],$s['Nama'],$s['Lembaga Induk'],$s['NPSN'],$s['Kecamatan'],$p['status'],$p['note'],implode(', ',$p['candidates'])];}
 if($lines){$path=$writer->build('Pendataan task7',['ID unit','ID pendataan','Nama perpustakaan','Institusi sumber','NPSN sumber (bukan acuan)','Kecamatan','Keputusan','Catatan','Kandidat'],$lines);copy($path,$folder.'/PERSANDINGAN_PENDATAAN_TASK7.xlsx');chmod($folder.'/PERSANDINGAN_PENDATAAN_TASK7.xlsx',0600);unlink($path);}
}
echo json_encode($summary,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE),"\n";
