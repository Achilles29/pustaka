<?php
require __DIR__.'/library_network_bootstrap.php';
$manifest=$argv[1]??'';
network_check(preg_match('#^/www/backup/pustaka-network/before-network-[0-9]{8}-[0-9]{6}-[a-f0-9]{8}\.accounts\.json$#D',$manifest)&&is_file($manifest),'Private provisioning manifest required.');
$journal=json_decode(file_get_contents($manifest),true,512,JSON_THROW_ON_ERROR);$base=substr($manifest,0,-strlen('.accounts.json'));
$db=DB('default');$db->db_debug=false;$checks=0;
function verified($value,$message){global$checks;network_check($value,$message);$checks++;echo 'PASS '.$message,"\n";}
$zip=new ZipArchive();network_check($zip->open($base.'.accounts.xlsx')===true,'Credential workbook unavailable.');$xml=simplexml_load_string($zip->getFromName('xl/worksheets/sheet1.xml'));$xml->registerXPathNamespace('s','http://schemas.openxmlformats.org/spreadsheetml/2006/main');$username=(string)$xml->xpath('//s:c[@r="E2"]/s:is/s:t')[0];$password=(string)$xml->xpath('//s:c[@r="F2"]/s:is/s:t')[0];$zip->close();
$accounts=network_query($db,"SELECT u.id,u.username,u.library_id,u.status,u.force_password_change,u.password_hash,GROUP_CONCAT(r.code ORDER BY r.code) roles FROM auth_user u LEFT JOIN auth_user_role ur ON ur.user_id=u.id LEFT JOIN auth_role r ON r.id=ur.role_id WHERE u.source_system='library_network_v1' GROUP BY u.id ORDER BY u.id")->result_array();
verified(count($accounts)===410,'Exactly 410 school accounts');$mapping=array_column($journal['accounts'],null,'user_id');
foreach($accounts as$row){network_check(isset($mapping[$row['id']])&&$row['username']===$mapping[$row['id']]['username']&&(int)$row['library_id']===$mapping[$row['id']]['library_id']&&$row['status']==='active'&&(int)$row['force_password_change']===1&&$row['roles']==='LIBRARY_ADMIN'&&password_verify($password,$row['password_hash']),'Account mapping/role/password verification failed.');}
verified(true,'All accounts match manifest, one role, assigned library, initial password and forced change');
foreach(['network_books','network_items','network_members','network_loans','network_visits','network_settings']as$table)verified((int)$db->count_all($table)===0,'No simulated operational data in '.$table);
foreach($journal['baseline']as$table=>$count)verified((int)$db->count_all($table)===$count,'Legacy row count unchanged: '.$table);
$cookie=$base.'.verification-cookie';
$request=function($path,$data=null)use($cookie){$c=curl_init('https://pustaka.rembangkab.go.id'.$path);curl_setopt_array($c,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_HEADER=>true,CURLOPT_USERAGENT=>'curl/8.5.0',CURLOPT_TIMEOUT=>30,CURLOPT_COOKIEJAR=>$cookie,CURLOPT_COOKIEFILE=>$cookie]);if($data!==null)curl_setopt_array($c,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query($data)]);$text=curl_exec($c);network_check($text!==false,'Production HTTP check failed.');$status=curl_getinfo($c,CURLINFO_RESPONSE_CODE);$length=curl_getinfo($c,CURLINFO_HEADER_SIZE);curl_close($c);return ['status'=>$status,'header'=>substr($text,0,$length),'body'=>substr($text,$length)];};
try{
    foreach(['/login','/','/katalog','/jejaring/katalog']as$path){$r=$request($path);verified($r['status']===200&&strpos($r['body'],'A PHP Error')===false,'Live public route '.$path);}
    $r=$request('/auth/do_login',['identifier'=>$username,'password'=>$password]);verified(in_array($r['status'],[302,303,307],true)&&strpos($r['header'],'library-workspace/account')!==false,'Live school login redirects to forced password change');
    $r=$request('/library-workspace/account');verified($r['status']===200&&strpos($r['body'],'name="network_csrf"')!==false&&strpos($r['body'],'A PHP Error')===false,'Live account form renders without changing initial password');
    $r=$request('/library-workspace/records/books');verified(in_array($r['status'],[302,303,307],true)&&strpos($r['header'],'library-workspace/account')!==false,'Live initial account cannot bypass password change');
    $request('/logout');
}finally{if(is_file($cookie))unlink($cookie);}
file_put_contents($base.'.verification.json',json_encode(['verified_at'=>date(DATE_ATOM),'checks'=>$checks,'accounts'=>410,'initial_password_left_unchanged'=>true,'legacy_counts_unchanged'=>true,'production_test_data_inserted'=>false],JSON_PRETTY_PRINT));
echo $checks," deployment checks passed.\n";
