<?php
require __DIR__.'/library_network_bootstrap.php';require APPPATH.'models/Library_activation_model.php';require APPPATH.'models/Auth_model.php';
$dir=$argv[1]??'';network_check(preg_match('#^/tmp/pustaka_network_test_[a-zA-Z0-9_]+$#D',$dir)&&is_file($dir.'/connection.php'),'Private runtime required');$params=require $dir.'/connection.php';network_check(strpos($params['database'],'pustaka_network_test_')===0,'Test DB required');$db=DB($params);$db->db_debug=false;
$m=new Library_activation_model();$m->db=$db;$auth=new Auth_model();$auth->db=$db;$checks=0;
$input=['registrant_name'=>'Pengelola Uji Task6','registrant_phone'=>'081234567890','password'=>'Personal!Task6Password','confirmation'=>'Personal!Task6Password','ownership'=>'1'];
if(in_array('--race',$argv,true)){try{$m->register_self(26,$input);echo 'REGISTERED';}catch(Throwable$e){echo 'REJECTED';}exit;}
function t6ok($v,$label){global$checks;network_check($v,'FAIL '.$label);$checks++;echo 'PASS '.$label.PHP_EOL;}
function t6reject($fn,$label){$failed=false;try{$fn();}catch(RuntimeException$e){$failed=true;}t6ok($failed,$label);}
$libraries=$db->order_by('id')->get('libraries')->result_array();$originalUser=$db->where('id',9000000)->get('auth_user')->row_array();
t6reject(fn()=>$m->register_self(2,$input),'County library cannot self-register');
t6reject(fn()=>$m->register_self(6,$input),'Previously used account cannot be claimed');
foreach([['password'=>'short','confirmation'=>'short'],['confirmation'=>'different'],['registrant_phone'=>'not-a-phone'],['ownership'=>'0'],['registrant_name'=>['array']]]as$bad)t6reject(fn()=>$m->register_self(8,array_replace($input,$bad)),'Invalid input rejected before account creation');
t6ok($db->where('library_id',8)->count_all_results('auth_user')===0,'Invalid requests create no account');
$user=$m->register_self(8,$input);t6ok((int)$user['library_id']===8&&(int)$user['force_password_change']===0,'New account active immediately without central issuance');
t6ok(password_verify($input['password'],$user['password_hash']),'Chosen password hashed correctly');
t6ok(array_column($auth->load_roles($user['id']),'code')===['LIBRARY_ADMIN'],'Only local admin role assigned');
t6ok((bool)$auth->attempt_login($user['username'],$input['password']),'New account can log in with chosen password');
t6ok($db->where('library_id',8)->count_all_results('library_self_registrations')===1,'One durable claim record created');
t6reject(fn()=>$m->register_self(8,$input),'Repeat claim rejected');
$bootstrap=$m->register_self(4,$input);t6ok($bootstrap['id']===$originalUser['id']&&$bootstrap['username']===$originalUser['username'],'Existing unused account reused without changing username');
t6ok($bootstrap['password_hash']!==$originalUser['password_hash'],'Legacy shared password replaced');
t6reject(fn()=>$m->issue(4,9000004),'County cannot accidentally reopen claimed unit with activation code');
$issued=$m->issue(10,9000004);$pending=$m->register_self(10,$input);t6ok((bool)$auth->attempt_login($pending['username'],$input['password']),'Old pending code does not block self-registration/login');
t6reject(fn()=>$m->claim(10,$issued['code']),'Previously issued code cannot reclaim self-registered account');
$m->moderate_account(8,$user['id'],'suspend',9000004,'Fixture ownership dispute');t6ok($db->where('id',$user['id'])->get('auth_user')->row()->status==='inactive','County can suspend local account');
t6ok(!$auth->attempt_login($user['username'],$input['password']),'Suspended account cannot log in');t6reject(fn()=>$m->register_self(8,$input),'Suspension cannot be bypassed by public claim');
t6reject(fn()=>$m->moderate_account(8,$user['id'],'reset',9000004,'Fixture reset'),'Inactive account cannot be silently reactivated by reset');
$m->moderate_account(8,$user['id'],'restore',9000004,'Fixture owner verified');t6ok(!$auth->attempt_login($user['username'],$input['password']),'Restore does not revive compromised password');
$reset=$m->moderate_account(8,$user['id'],'reset',9000004,'Fixture verified owner reset');$login=$auth->attempt_login($user['username'],$reset['temporary_password']);t6ok($login&&(int)$login['force_password_change']===1,'Reset creates working temporary password and forces change');
t6reject(fn()=>$m->moderate_account(4,$user['id'],'reset',9000004,'Wrong scope fixture'),'Cross-library account reset rejected');
t6reject(fn()=>$m->moderate_account(8,$user['id'],'reset',9000004,''),'Moderation requires reason');
$mixed=$db->where('code','SUPERADMIN')->get('auth_role')->row()->id;$db->insert('auth_user_role',['user_id'=>$user['id'],'role_id'=>$mixed]);t6reject(fn()=>$m->moderate_account(8,$user['id'],'reset',9000004,'Mixed role fixture'),'Mixed/global role account cannot be reset through local moderation');$db->where(['user_id'=>$user['id'],'role_id'=>$mixed])->delete('auth_user_role');
$m->issue(12,9000004);$db->where('library_id',12)->update('auth_user',['status'=>'inactive']);t6reject(fn()=>$m->register_self(12,$input),'Inactive bootstrap account cannot be activated publicly');
$original14=$db->where('id',14)->get('libraries')->row_array();$db->where('id',14)->update('libraries',['status'=>'inactive']);t6reject(fn()=>$m->register_self(14,$input),'Inactive library rejected');$db->where('id',14)->update('libraries',$original14);
$procs=[];for($i=0;$i<2;$i++){$p=proc_open([PHP_BINARY,__FILE__,$dir,'--race'],[0=>['file','/dev/null','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);$procs[]=[$p,$pipes];}$results=[];
foreach($procs as[$p,$pipes]){$results[]=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);t6ok(proc_close($p)===0&&$err==='','Concurrent worker completed');}sort($results);t6ok($results===['REGISTERED','REJECTED'],'Exactly one simultaneous self-registration succeeds');
t6ok($db->where('library_id',26)->count_all_results('auth_user')===1,'Concurrent claim does not duplicate account');
t6ok($libraries===$db->order_by('id')->get('libraries')->result_array(),'No library identity/status/GPS changed');
$history=json_encode($db->get('iplm_history')->result_array());t6ok(strpos($history,$input['password'])===false&&strpos($history,$reset['temporary_password'])===false,'Passwords absent from durable audit logs');
file_put_contents($dir.'/task6-model-results.json',json_encode(['checks'=>$checks]));echo $checks.' model/security/concurrency checks passed.'.PHP_EOL;
