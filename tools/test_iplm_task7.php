<?php
require __DIR__.'/library_network_bootstrap.php';require __DIR__.'/iplm_task7_support.php';
foreach(['Library_survey_model','Library_network_model','Library_model','Iplm_model']as$m)require APPPATH.'models/'.$m.'.php';
$dir=$argv[1]??'';network_check(preg_match('#^/tmp/pustaka_network_test_[a-zA-Z0-9_]+$#D',$dir)&&is_file($dir.'/connection.php'),'Private fixture required');$params=require $dir.'/connection.php';network_check(strpos($params['database'],'pustaka_network_test_')===0,'Test database required');$db=DB($params);$db->db_debug=false;$checks=0;
function ok7($v,$s){global$checks;network_check($v,'FAIL '.$s);$checks++;echo 'PASS '.$s,"\n";}
function reject7($fn,$s){$bad=false;try{$fn();}catch(Throwable$e){$bad=true;}ok7($bad,$s);}
$s=new Library_survey_model();$s->db=$db;$n=new Library_network_model();$n->db=$db;$i=new Iplm_model();$i->db=$db;$l=new Library_model();$l->db=$db;
$all=$db->get('libraries')->result_array();ok7(count($all)===count(array_unique(array_column($all,'code'))),'No duplicate NPSN/code after reconciliation');
foreach([1624=>'20315657',1598=>'20315667',1632=>'20315824',1586=>'20315837']as$id=>$code)ok7($n->library($id)['code']===$code,'Approved NPSN corrected on existing ID '.$id);
ok7($n->library(1644)['district']==='Sulang'&&$n->library(1644)['village']==='KEMADU'&&$n->library(1644)['address']==='JL. PEMUDA KM 03 REMBANG','YPI district, village and address retained');
ok7($n->library(1586)['name']==='Baitul Ulum','Named library preserved');
ok7($n->library(1638)['code']==='PDT-45869'&&$n->library(1602)['code']==='20315662','Hamong Siswa and Adisucipto retained');
ok7((int)$db->where('code','slb')->get('library_subtypes')->row()->iplm_eligible===0,'SLB does not automatically enter IPLM');
ok7($db->where('source_system','dapodik_task7')->where('is_verified',1)->count_all_results('libraries')===0,'New schools require verification');
ok7($db->where('source_system','dapodik_task7')->where('iplm_population_status !=','pending')->count_all_results('libraries')===0,'New schools await population selection');
ok7((int)$db->where('year',2026)->get('iplm_periods')->row()->population===403,'Historic snapshot 403 preserved');
ok7($i->registry_population()['total']===0,'Registry count is not school count');
$db->where('id',4)->update('libraries',['iplm_population_status'=>'included']);ok7($i->registry_population()['total']===1,'Automatic population uses selected eligible unit');
$db->where('id',1632)->update('libraries',['iplm_population_status'=>'included']);ok7($i->registry_population()['total']===1,'Out-of-authority SLB excluded even if selected');
$db->where_in('id',[4,1632])->update('libraries',['iplm_population_status'=>'pending']);
$initial=$n->library(4);$forms=$db->order_by('id')->get('iplm_submissions')->result_array();
reject7(function()use($n,$initial){$n->save_profile(4,array_replace($initial,['manager_name'=>'','phone'=>'081234567890']),9000000);},'PIC required server-side');
reject7(function()use($n,$initial){$n->save_profile(4,array_replace($initial,['manager_name'=>'Tester','phone'=>'123']),9000000);},'Valid mobile number required server-side');
$n->save_profile(4,array_replace($initial,['manager_name'=>'Petugas Task7','phone'=>'081234567890','code'=>'FORGED','iplm_population_status'=>'included','description'=>'Browser Task7']),9000000);
ok7($n->library(4)['code']===$initial['code']&&$n->library(4)['iplm_population_status']==='pending','Local profile cannot change NPSN or population selection');
reject7(function()use($s){$s->validate(['students'=>-1]);},'Negative survey count rejected');
reject7(function()use($s){$s->validate(['students'=>['bad']]);},'Array payload rejected');
reject7(function()use($s){$s->validate(['npp_date'=>'2026-02-31']);},'Invalid date rejected');
reject7(function()use($s){$s->validate(['uses_it'=>'invalid']);},'Invalid boolean rejected');
reject7(function()use($s){$s->validate(['established_year'=>'2999']);},'Future year rejected');
$old=$s->get(4);$other=$s->get(6);$data=['vision'=>'Visi <script>alert(1)</script> TASK7','students'=>'0','uses_it'=>'yes','library_presence'=>'yes','reference_year'=>'2026'];
$s->save(4,$data,$old['version'],9000000);$now=$s->get(4);
ok7($now['values']['students']==='0'&&$now['values']['members']==='','Explicit zero distinct from missing');
ok7($now['version']===$old['version']+1,'Optimistic version advances');
reject7(function()use($s,$old,$data){$s->save(4,$data,$old['version'],9000000);},'Stale concurrent save rejected');
ok7($s->get(4)===$now,'Rejected save leaves data unchanged');
ok7($s->get(6)===$other,'Other library remains unchanged');
ok7($db->order_by('id')->get('iplm_submissions')->result_array()===$forms,'Supplemental values never change IPLM answers');
$db->where('id',6)->update('libraries',['manager_name'=>null,'phone'=>null]);
reject7(function()use($s){$s->save(6,[],$s->get(6)['version'],9000002);},'Survey save requires profile contact');
ok7(!task7_gps('0','0')&&!task7_gps('-5.4438','114.4211')&&task7_gps('-6.826803','111.387722'),'Invalid/out-of-region source GPS rejected');
$records=$db->count_all('library_source_records');$p=proc_open([PHP_BINARY,__DIR__.'/setup_iplm_task7.php','--apply','--test='.$dir],[0=>['file','/dev/null','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);ok7(proc_close($p)===0&&strpos($out,'Already applied')!==false,'Rerun is idempotent');ok7($records===$db->count_all('library_source_records')&&$s->get(4)===$now,'Rerun preserves local edits and archive count');
$period=$db->where('year',2026)->get('iplm_periods')->row_array();
$periodInput=$period+['dates_confirmed'=>1];$periodInput['state']='open';$periodInput['population_mode']='auto';
reject7(function()use($i,$periodInput){$i->save_period($periodInput,9000004);},'Empty automatic population cannot open period');
$manual=$periodInput;$manual['population_mode']='manual';$manual['population']='25';$manual['population_note']='Penetapan fixture berdasarkan unit berwenang';
$i->save_period($manual,9000004);$saved=$i->period($period['id']);ok7((int)$saved['population']===25&&$saved['population_mode']==='manual','Manual population independent from master count');
$bad=$manual;$bad['version']=$saved['version'];$bad['population_note']='';reject7(function()use($i,$bad){$i->save_period($bad,9000004);},'Manual population requires reason');
$submission=$db->where('library_id',4)->where('deleted_at IS NULL',null,false)->get('iplm_submissions')->row_array();
if($submission){
 $db->where('id',4)->update('libraries',['phone'=>null]);$caught='';
 try{$i->save($submission['id'],4,9000000,['version'=>$submission['version'],'action'=>'submit']);}catch(Throwable$e){$caught=$e->getMessage();}
 ok7(strpos($caught,'contact person')!==false,'IPLM submission requires complete profile contact');
 $db->where('id',4)->update('libraries',['phone'=>'081234567890']);
}
$db->where('id',$period['id'])->update('iplm_periods',$period);
file_put_contents($dir.'/task7-fixture-metadata.json',json_encode(['code'=>$n->library(4)['code'],'checks'=>$checks]));
echo $checks," checks passed.\n";
