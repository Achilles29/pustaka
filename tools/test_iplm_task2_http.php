<?php
require __DIR__.'/library_network_bootstrap.php';
$dir=$argv[1]??'';network_check(preg_match('#^/tmp/pustaka_network_test_[a-zA-Z0-9_]+$#D',$dir)&&is_file($dir.'/connection.php'),'Isolated fixture required.');$params=require $dir.'/connection.php';network_check(strpos($params['database'],'pustaka_network_test_')===0,'Test DB only.');$db=DB($params);$db->db_debug=false;$fixture=json_decode(file_get_contents($dir.'/fixtures.json'),true);$checks=0;
function task2_http($path,$data=null,$who='local'){global$dir;$c=curl_init('http://127.0.0.1:8797'.$path);curl_setopt_array($c,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIEJAR=>$dir.'/task2-cookie-'.$who,CURLOPT_COOKIEFILE=>$dir.'/task2-cookie-'.$who,CURLOPT_TIMEOUT=>30]);if($data!==null)curl_setopt_array($c,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query($data)]);$body=curl_exec($c);network_check($body!==false,'HTTP request failed.');$status=curl_getinfo($c,CURLINFO_RESPONSE_CODE);curl_close($c);return [$status,$body];}
function task2_http_ok($ok,$label){global$checks;network_check($ok,'FAIL '.$label);$checks++;echo 'PASS '.$label,PHP_EOL;}
function task2_http_token($body){network_check(preg_match('/name="iplm_csrf" value="([a-f0-9]+)"/',$body,$m),'Missing token');return $m[1];}
task2_http('/auth/do_login',['identifier'=>'test-central','password'=>$fixture['password']],'central');
[$status,$body]=task2_http('/iplm/settings',null,'central');$token=task2_http_token($body);$p=$db->where('year',2026)->get('iplm_periods')->row_array();
task2_http_ok($status===200&&strpos($body,'Periode & populasi')!==false&&strpos($body,'403 perpustakaan')!==false,'Settings shows tabs and registry population');
$post=['iplm_csrf'=>$token,'entity'=>'period','id'=>$p['id'],'version'=>$p['version'],'year'=>2026,'title'=>'Retain invalid input fixture','start_date'=>'2025-12-31','end_date'=>'2025-01-01','state'=>'open','dates_confirmed'=>1];
[$status,$body]=task2_http('/iplm/settings',$post,'central');task2_http_ok($status===200&&strpos($body,'Urutan tanggal tidak valid')!==false&&strpos($body,'Retain invalid input fixture')!==false,'Settings validation preserves entered values');task2_http_ok($db->where('id',$p['id'])->get('iplm_periods')->row_array()===$p,'Invalid settings POST leaves period unchanged');
$post['iplm_csrf']=task2_http_token($body);$post['start_date']='2025-01-01';$post['end_date']='2025-12-31';$post['population']='999999';$post['population_note']='Browser forged';
[$status]=task2_http('/iplm/settings',$post,'central');task2_http_ok(in_array($status,[302,303],true)&&(int)$db->where('id',$p['id'])->get('iplm_periods')->row()->population===403,'Settings POST recalculates population server-side');
$tk=$db->select('l.id')->from('libraries l')->join('library_subtypes s','s.id=l.library_subtype_id')->where('s.code','tk')->get()->row()->id;
$original=$db->where('id',9000000)->get('auth_user')->row_array();$before=(int)$db->count_all('iplm_submissions');
try{
    $db->where('id',9000000)->update('auth_user',['library_id'=>$tk]);
    task2_http('/auth/do_login',['identifier'=>'test-school-a','password'=>$fixture['changed_password']]);
    [$status,$body]=task2_http('/iplm');task2_http_ok($status===200&&strpos($body,'di luar cakupan')!==false&&strpos($body,'Buat / lanjutkan isian')===false,'TK local dashboard explains exclusion and hides create form');
    [$status,$centralBody]=task2_http('/iplm',null,'central');
    task2_http('/iplm/create',['iplm_csrf'=>task2_http_token($centralBody),'library_id'=>$tk,'period_id'=>$p['id']],'central');
    task2_http_ok((int)$db->count_all('iplm_submissions')===$before,'Forged central creation of TK blocked without inserting data');
    [$status,$body]=task2_http('/iplm',null,'central');task2_http_ok(strpos($body,'TK/SKB tidak diikutkan')!==false,'Rejected TK create reports an understandable reason');
}finally{$db->where('id',9000000)->update('auth_user',['library_id'=>$original['library_id']]);}
echo $checks," task2 HTTP checks passed.\n";
