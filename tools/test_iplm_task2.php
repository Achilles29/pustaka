<?php
require __DIR__.'/library_network_bootstrap.php';require __DIR__.'/iplm_task2_support.php';require APPPATH.'models/Iplm_model.php';
$dir=$argv[1]??'';network_check(preg_match('#^/tmp/pustaka_network_test_[a-zA-Z0-9_]+$#D',$dir)&&is_file($dir.'/connection.php'),'Isolated test runtime required.');$params=require $dir.'/connection.php';network_check(strpos($params['database'],'pustaka_network_test_')===0,'Isolated DB required.');$db=DB($params);$db->db_debug=false;$m=new Iplm_model();$m->db=$db;$checks=0;
function task2_ok($ok,$message){global$checks;network_check($ok,'FAIL '.$message);$checks++;echo 'PASS '.$message,PHP_EOL;}
function task2_reject($fn,$message){$failed=false;try{$fn();}catch(Throwable$e){$failed=true;}task2_ok($failed,$message);}
$population=$m->registry_population();task2_ok($population['total']===403&&$population['groups']['sd']['count']===363&&$population['groups']['smp']['count']===39&&$population['groups']['kabupaten']['count']===1,'Registry population 403 with correct subtype breakdown');
$p=$m->periods()[0];$audit=$db->where('event','task2.2026.applied')->get('iplm_history')->row_array();$payload=json_decode($audit['payload_json'],true);
task2_ok($payload['period_after']['start_date']==='2025-01-01'&&$payload['period_after']['end_date']==='2025-12-31'&&$payload['period_after']['dates_confirmed']===1,'Migration confirms assessment 2026 / data 2025');
foreach(['tk','skb']as$sub){$l=$db->select('l.id')->from('libraries l')->join('library_subtypes s','s.id=l.library_subtype_id')->where('s.code',$sub)->get()->row_array();task2_ok(!$m->eligible($l['id'])&&!in_array((int)$l['id'],$population['ids'],true),'Excluded from population: '.$sub);task2_reject(function()use($m,$l,$p){$m->create($l['id'],$p['id'],9000004,'Fixture');},'Cannot create IPLM for '.$sub);}
$fields=array_column($m->fields(),null,'code');$evidence=iplm_evidence_map();task2_ok(count($evidence)===28,'28 evidence indicators mapped');
foreach($evidence as$key=>$spec)task2_ok($fields[$key]['evidence_hint']===$spec['hint'],'Evidence maps to correct indicator: '.$key);
task2_ok(strpos($fields['staff_qualified']['definition'],'minimal Diploma 2 (D2)')!==false,'Minimum staff qualification is D2');
task2_ok(strpos($fields['library_users']['definition'],'bukan terbatas orang unik')!==false,'Users defined as visits/utilizations');
task2_ok(strpos($fields['service_policies']['evidence_hint'],'Excel rincian anggaran')===false,'Misplaced budget text in D57 not used for SOP evidence');
$school=$m->library(4);$schema=$m->fields();$input=[];foreach($schema as$f)$input['f_'.$f['code']]='';$input['f_library_type_id']=$school['library_type_id'];$input['f_library_subtype_id']=$db->where('code','tk')->get('library_subtypes')->row()->id;
task2_reject(function()use($m,$schema,$input){$m->validate($schema,$input);},'Cannot switch form to TK to bypass eligibility');
// POST-supplied population cannot alter the server-derived denominator.
$input=['id'=>$p['id'],'version'=>$p['version'],'year'=>2026,'title'=>'Fixture assessment 2026 / data 2025','start_date'=>'2025-01-01','end_date'=>'2025-12-31','dates_confirmed'=>1,'state'=>'open','population'=>999999,'population_note'=>'Forged'];
$m->save_period($input,9000004);$p=$m->period($p['id']);task2_ok((int)$p['population']===403&&strpos($p['population_note'],'/libraries')!==false,'Population recalculated, ignoring tampered browser count');
$original=$db->where('id',4)->get('libraries')->row_array();$db->where('id',4)->update('libraries',['status'=>'inactive']);
try{task2_ok($m->registry_population()['total']===402&&!$m->eligible(4),'Inactive libraries consistently excluded');task2_ok(!$m->listing($p['id'],4),'Out-of-scope submissions omitted from recap/export');task2_ok((int)$m->period($p['id'])['population']===403,'Saved population is auditable snapshot, not silently mutated');}finally{$db->where('id',4)->update('libraries',['status'=>$original['status']]);}
// Derived artifacts retain source row count, do not confuse numbered schools.
$matched=json_decode(file_get_contents(FCPATH.'docs/iplm/pencocokan-pendataan-task2.json'),true);task2_ok(count($matched)===834,'All source rows retained in mapping');
$bySource=array_column($matched,null,'source_id');task2_ok($bySource['48720']['library_id']===null&&$bySource['48199']['library_id']===null,'Numbered school 2/3 not merged into unnumbered master');
foreach(['PENCOCOKAN_PENDATAAN.xlsx','PENDATAAN_NAMA_DISELARASKAN.xlsx','LIBRARIES_BELUM_COCOK.xlsx','PEMETAAN_KOLOM_PENDATAAN.xlsx','PEMETAAN_BUKTI_DUKUNG.xlsx']as$file){$z=new ZipArchive();task2_ok($z->open(FCPATH.'docs/iplm/'.$file)===true&&$z->locateName('xl/worksheets/sheet1.xml')!==false,'Genuine Excel report: '.$file);$z->close();}
echo $checks," task2 checks passed.\n";
