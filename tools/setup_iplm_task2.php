<?php
require __DIR__.'/library_network_bootstrap.php';require __DIR__.'/iplm_task2_support.php';require APPPATH.'models/Iplm_model.php';
$dir=null;foreach($argv as$a)if(strpos($a,'--test=')===0)$dir=substr($a,7);
if($dir!==null){network_check(preg_match('#^/tmp/pustaka_network_test_[a-zA-Z0-9_]+$#D',$dir)&&is_file($dir.'/connection.php'),'Isolated test directory required.');$params=require $dir.'/connection.php';network_check(strpos($params['database'],'pustaka_network_test_')===0,'Isolated database required.');$db=DB($params);}else $db=DB('default');
$db->db_debug=false;$m=new Iplm_model();$m->db=$db;$evidence=iplm_evidence_map();$population=$m->registry_population();
if(!in_array('--apply',$argv,true)){echo 'DRY RUN: 2026 assesses 2025; population='.$population['total'].'; 28 evidence instructions; minimum D2; visits not unique people.',PHP_EOL;exit;}
if($db->where('event','task2.2026.applied')->count_all_results('iplm_history')){echo "Already applied; subsequent administrator changes preserved.\n";exit;}
if(!$dir){$backup=network_backup($db,'docs/iplm/task2.md');echo 'Backup: '.$backup.'.sql.gz',PHP_EOL;}
$masterBefore=hash('sha256',json_encode(network_query($db,'SELECT * FROM libraries ORDER BY id')->result_array()));
$db->trans_begin();
try{
    $p=network_query($db,'SELECT * FROM iplm_periods WHERE year=2026 FOR UPDATE')->row_array();
    if($p)network_check(!$db->where('period_id',$p['id'])->where('deleted_at IS NULL',null,false)->where_in('status',['submitted','verified'])->count_all_results('iplm_submissions'),'Submitted/verified forms require review before changing the period.');
    $period=['year'=>2026,'title'=>'IPLM 2026 — data tahun 2025','start_date'=>'2025-01-01','end_date'=>'2025-12-31','dates_confirmed'=>1,'population'=>$population['total'],'population_note'=>$population['note'],'state'=>$p?$p['state']:'draft','version'=>$p?(int)$p['version']+1:1];
    network_check($p?$db->where('id',$p['id'])->update('iplm_periods',$period):$db->insert('iplm_periods',$period),'Cannot configure period.');$pid=$p?(int)$p['id']:(int)$db->insert_id();
    $changes=[];
    foreach($m->fields(true)as$f){$data=[];$key=$f['code'];if(isset($evidence[$key]))$data['evidence_hint']=$evidence[$key]['hint'];
        if($key==='staff_qualified')$data['definition']='Jumlah tenaga perpustakaan dengan pendidikan Ilmu Perpustakaan minimal Diploma 2 (D2), sesuai keputusan pengelola pada task2.md. Standar ini dapat diubah admin pada Pengaturan Form; tidak menyatakan D2 sama dengan D3.';
        if($key==='library_users')$data['definition']='Jumlah pemanfaatan/kunjungan perpustakaan secara luring dan/atau daring dalam rentang data periode, bukan terbatas orang unik. Seseorang yang berkunjung beberapa kali dihitung sesuai jumlah kunjungannya. Gunakan rekap nyata yang dapat dibuktikan; data simulasi tidak otomatis menjadi jawaban IPLM.';
        if(!$data)continue;$data['version']=(int)$f['version']+1;network_check($db->where('id',$f['id'])->update('iplm_fields',$data),'Cannot update evidence definition.');$changes[$key]=$data;
    }
    $updated=[];foreach(network_query($db,'SELECT * FROM iplm_submissions WHERE period_id=? AND deleted_at IS NULL FOR UPDATE',[$pid])->result_array()as$r){
        network_check(in_array($r['status'],['draft','revision'],true),'Unexpected locked submission.');
        $schema=json_decode($r['schema_json'],true,512,JSON_THROW_ON_ERROR);foreach($schema as&$f)if(isset($changes[$f['code']]))$f=array_replace($f,$changes[$f['code']]);unset($f);
        $data=['schema_json'=>$m->json($schema),'status'=>'revision','version'=>(int)$r['version']+1,'review_note'=>'Keputusan task2: penilaian 2026 memakai data 1 Januari–31 Desember 2025; kualifikasi minimal D2; pemustaka dihitung sebagai kunjungan. Petunjuk bukti diperbarui. Nilai dan tautan lama dipertahankan; periksa sebelum mengirim.','resolutions_json'=>'{}','verified_at'=>null,'updated_at'=>date('Y-m-d H:i:s')];
        network_check($db->where('id',$r['id'])->update('iplm_submissions',$data),'Cannot refresh draft guidance.');
        network_check($db->insert('iplm_history',['submission_id'=>$r['id'],'actor_id'=>0,'event'=>'task2.guidance.updated','payload_json'=>$m->json(['before'=>$r,'change'=>$data,'source'=>'docs/iplm/task2.md'])]),'Cannot audit draft refresh.');$updated[]=(int)$r['id'];
    }
    network_check($db->insert('iplm_history',['actor_id'=>0,'event'=>'task2.2026.applied','payload_json'=>$m->json(['period_before'=>$p,'period_after'=>$period,'population'=>$population,'fields_changed'=>array_keys($changes),'updated_drafts'=>$updated,'source_sha256'=>hash_file('sha256',FCPATH.'docs/iplm/bukti_dukung.xlsx')])]),'Cannot audit task2.');
    network_check($db->trans_status()&&$db->trans_commit(),'Task2 migration failed.');
}catch(Throwable$e){$db->trans_rollback();throw $e;}
network_check($masterBefore===hash('sha256',json_encode(network_query($db,'SELECT * FROM libraries ORDER BY id')->result_array())),'Master changed; investigate concurrent changes.');
$result=['population'=>$population['total'],'period'=>$pid,'fields_updated'=>count($changes),'drafts_preserved'=>$updated,'libraries_unchanged'=>true];
if(!$dir)file_put_contents($backup.'.iplm-task2.json',json_encode($result,JSON_PRETTY_PRINT));
echo json_encode($result,JSON_PRETTY_PRINT),PHP_EOL;
