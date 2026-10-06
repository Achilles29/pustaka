<?php
require __DIR__.'/library_network_bootstrap.php';
require APPPATH.'models/Library_type_model.php';
require APPPATH.'models/Library_model.php';
$dir=$argv[1]??'';network_check(preg_match('#^/tmp/pustaka_network_test_[a-zA-Z0-9_]+$#D',$dir)&&is_file($dir.'/connection.php'),'Private fixture required');$params=require $dir.'/connection.php';network_check(strpos($params['database'],'pustaka_network_test_')===0,'Test DB required');$db=DB($params);$db->db_debug=false;$checks=0;
function tax_ok($ok,$message){global$checks;network_check($ok,'FAIL '.$message);$checks++;echo 'PASS '.$message.PHP_EOL;}
function tax_run($dir){$p=proc_open(['php',__DIR__.'/cleanup_library_taxonomy.php','--apply','--test='.$dir],[0=>['file','/dev/null','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);return [proc_close($p),$out,$err];}
$lib=new Library_model();$lib->db=$db;$model=new Library_type_model();$model->db=$db;
$before=$db->order_by('id')->get('libraries')->result_array();$map=$lib->get_map_libraries();
$row=$db->where('id',2)->get('libraries')->row_array();$oldType=$db->where('code','perpusda')->get('library_types')->row()->id;
network_check($db->where('id',2)->update('libraries',['library_type_id'=>$oldType,'library_subtype_id'=>null]),'Fixture assignment');
[$exit,$out,$err]=tax_run($dir);tax_ok($exit!==0&&strpos($err,'still used')!==false,'Refuse deleting a type that becomes used');tax_ok($db->where('code','perpusda')->count_all_results('library_types')===1,'Failed cleanup preserves references');
network_check($db->where('id',2)->update('libraries',$row),'Restore fixture');
[$exit,$out,$err]=tax_run($dir);tax_ok($exit===0,'Cleanup succeeds for unused types: '.$err);file_put_contents($dir.'/taxonomy-applied.json',$out);
tax_ok(count($lib->get_types())===6,'Six non-overlapping type options remain');
tax_ok(count($lib->get_subtypes())===19,'All nineteen subtypes preserved');
tax_ok($before===$db->order_by('id')->get('libraries')->result_array(),'All 832 libraries unchanged');
tax_ok($map===$lib->get_map_libraries(),'Map locations/names/categories unchanged');
foreach(['perpusda','desa','komunitas']as$code)tax_ok(!$db->where('code',$code)->count_all_results('library_types'),'Retired type physically removed: '.$code);
foreach([['perpusda','Daerah'],['desa','Desa'],['komunitas','Komunitas Literasi'],['alias_desa','Perpustakaan Desa'],['alias_tbm','Perpustakaan TBM/Rumah Baca/penamaan lainnya'],['sd','Nama baru']]as$pair){$rejected=false;try{$model->save('type',['code'=>$pair[0],'name'=>$pair[1],'marker_color'=>'#123456','is_active'=>1],0);}catch(RuntimeException$e){$rejected=true;}tax_ok($rejected,'Reject duplicate type enum: '.$pair[0]);}
tax_ok(count($lib->get_types())===6,'Rejected writes leave enum unchanged');
$rejected=false;try{$model->save('subtype',['code'=>'umum','name'=>'Perpustakaan Umum','library_type_id'=>2,'is_active'=>1],0);}catch(RuntimeException$e){$rejected=strpos($e->getMessage(),'menduplikasi')!==false;}tax_ok($rejected,'Reject subtype duplicating an existing parent type');
$school=$db->where('code','sekolah')->get('library_types')->row_array();$model->save('type',$school,0);tax_ok($db->where('code','sekolah')->count_all_results('library_types')===1,'Normal type editing still allowed');
[$exit,$out,$err]=tax_run($dir);tax_ok($exit===0&&strpos($out,'Already cleaned')!==false,'Repeated cleanup is idempotent');
tax_ok($db->where('event','taxonomy.duplicates.removed')->count_all_results('iplm_history')===1,'One recoverable deletion audit');
file_put_contents($dir.'/taxonomy-model-results.json',json_encode(['checks'=>$checks]));echo $checks.' checks passed.'.PHP_EOL;
