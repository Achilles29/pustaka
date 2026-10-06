<?php
require __DIR__.'/library_network_bootstrap.php';
$directory=$argv[1]??'';network_check(preg_match('#^/tmp/pustaka_network_test_[a-zA-Z0-9_]+$#D',$directory)&&is_file($directory.'/connection.php'),'Prepared private test directory required.');
$params=require $directory.'/connection.php';network_check(strpos($params['database'],'pustaka_network_test_')===0,'Isolated test database required.');$db=DB($params);$db->db_debug=false;
$fixture=json_decode(file_get_contents($directory.'/fixtures.json'),true);$ids=json_decode(file_get_contents($directory.'/ids.json'),true);$checks=0;
$db->where('id',9000000)->update('auth_user',['password_hash'=>password_hash($fixture['password'],PASSWORD_BCRYPT),'force_password_change'=>1,'status'=>'active','library_id'=>4]);
foreach(['a','public','test-central','test-member','test-legacy']as$who)if(is_file($directory.'/cookies-'.$who))unlink($directory.'/cookies-'.$who);
$port=(int)($argv[2]??8796);network_check($port>=8796&&$port<=8799,'Only local test ports allowed.');$base='http://127.0.0.1:'.$port;
function ok($value,$message){global$checks;network_check($value,'FAIL '.$message);$checks++;echo 'PASS '.$message,"\n";}
function request($path,$data=null,$who='a',$multipart=false){
    global $base,$directory;
    $curl=curl_init($base.$path);curl_setopt_array($curl,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_HEADER=>true,CURLOPT_COOKIEJAR=>$directory.'/cookies-'.$who,CURLOPT_COOKIEFILE=>$directory.'/cookies-'.$who,CURLOPT_TIMEOUT=>30]);
    if($data!==null)curl_setopt_array($curl,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>is_array($data)&&!$multipart?http_build_query($data):$data]);
    $result=curl_exec($curl);network_check($result!==false,'Local HTTP request failed.');$size=curl_getinfo($curl,CURLINFO_HEADER_SIZE);$status=curl_getinfo($curl,CURLINFO_RESPONSE_CODE);curl_close($curl);
    return ['status'=>in_array($status,[302,303,307],true)?302:$status,'raw_status'=>$status,'headers'=>substr($result,0,$size),'body'=>substr($result,$size)];
}
function csrf($path,$who='a'){$r=request($path,null,$who);network_check($r['status']===200,'CSRF form unavailable: '.$path);network_check(preg_match('/name="network_csrf" value="([a-f0-9]+)"/',$r['body'],$m),'CSRF field missing');return $m[1];}
function login($username,$password,$who='a'){return request('/auth/do_login',['identifier'=>$username,'password'=>$password],$who);}
$r=request('/library-workspace');ok($r['status']===302&&strpos($r['headers'],'/login')!==false,'Anonymous operations redirect to login');
$r=login('test-school-a',$fixture['password']);ok($r['status']===302&&strpos($r['headers'],'library-workspace/account')!==false,'Initial login forces personal password');
ok(request('/library-workspace/records/books')['status']===302,'Forced-password account cannot read catalog');
ok(request('/library-workspace/loans',['member_no'=>'001','barcode'=>'0001'])['status']===403,'Forced-password account cannot submit operations');
$token=csrf('/library-workspace/account');$new='ChangedPrivateTest!98765';
$r=request('/library-workspace/account',['network_csrf'=>$token,'current_password'=>$fixture['password'],'password'=>$new,'confirmation'=>$new]);ok($r['status']===302,'Password changed through actual form');
ok((int)$db->where('id',9000000)->get('auth_user')->row()->force_password_change===0,'Forced-password flag cleared after verified update');
$fixture['changed_password']=$new;file_put_contents($directory.'/fixtures.json',json_encode($fixture));
foreach(['','/records/books','/records/items','/records/members','/edit/books','/edit/items','/edit/members','/loans','/visits','/reports','/profile','/settings','/admins','/account','/import/books','/import/members','/import/items']as$path){$r=request('/library-workspace'.$path);ok($r['status']===200&&strpos($r['body'],'PHP Error')===false&&strpos($r['body'],'Severity:')===false,'Render local workspace '.$path);}
foreach(['/members','/catalog','/catalog/export','/reports/visits','/libraries','/users','/user/dashboard','/guestbook','/user/reading-checkin','/library-workspace?library_id=6','/library-workspace/export/members/xlsx?library_id=6']as$path){$r=request($path);ok(in_array($r['status'],[302,403,404],true),'Blocked legacy or foreign endpoint '.$path);}
ok(request('/library-workspace/edit/books/'.$ids['book_b'])['status']===404,'Foreign object ID not readable');
ok(request('/library-workspace/loans',['member_no'=>'001','barcode'=>'0001'])['status']===403,'Missing CSRF rejected');
ok(request('/library-workspace/archive/books/'.$ids['book_a'])['status']===405,'GET cannot archive');
$token=csrf('/library-workspace/edit/books');$r=request('/library-workspace/edit/books',['network_csrf'=>$token,'title'=>'HTTP Book <script>alert(1)</script>','format'=>'physical','status'=>'draft','library_id'=>6]);ok($r['status']===302,'Create catalog through HTTP');
$created=$db->like('title','HTTP Book')->get('network_books')->row_array();ok((int)$created['library_id']===4,'Posted scope ignored, ownership from authenticated account');
$r=request('/library-workspace/records/books');ok(strpos($r['body'],'HTTP Book &lt;script&gt;')!==false&&strpos($r['body'],'<script>alert(1)</script>')===false&&strpos($r['body'],'Fixture B PRIVATE')===false,'Output escaped and foreign books absent');
ok(request('/library-workspace/edit/books',['network_csrf'=>$token,'title'=>'Double submit','format'=>'physical','status'=>'draft'])['status']===403,'Consumed CSRF prevents double submission');
$token=csrf('/library-workspace/edit/books');$r=request('/library-workspace/edit/books',['network_csrf'=>$token,'title'=>['malformed']]);ok($r['status']===200&&strpos($r['body'],'Isian formulir tidak valid')!==false,'Malformed form handled without server error');
$r=request('/library-workspace/export/members/csv');ok($r['status']===200&&strpos($r['body'],'Anggota Uji A')!==false&&strpos($r['body'],'PRIVATE')===false,'CSV export scoped');
$r=request('/library-workspace/export/members/xlsx');$file=$directory.'/http-members.xlsx';file_put_contents($file,$r['body']);$z=new ZipArchive();ok($z->open($file)===true,'Excel download is genuine XLSX');$xml=$z->getFromName('xl/worksheets/sheet1.xml');$z->close();ok(strpos($xml,'Anggota Uji A')!==false&&strpos($xml,'PRIVATE')===false,'Excel export scoped');
// End-to-end operations and multipart Excel preview/confirmation.
$tag=bin2hex(random_bytes(4));$number='HTTP-'.$tag;
$r=request('/library-workspace/edit/members',['network_csrf'=>csrf('/library-workspace/edit/members'),'full_name'=>'HTTP Anggota','member_no'=>$number,'status'=>'active']);ok($r['status']===302,'Create local member through HTTP');
$r=request('/library-workspace/edit/items',['network_csrf'=>csrf('/library-workspace/edit/items'),'book_id'=>$created['id'],'barcode'=>$number,'status'=>'available']);ok($r['status']===302,'Create physical item through HTTP');
$r=request('/library-workspace/loans',['network_csrf'=>csrf('/library-workspace/loans'),'member_no'=>$number,'barcode'=>$number]);$item=$db->where('library_id',4)->where('barcode',$number)->get('network_items')->row_array();$loan=$db->where('library_id',4)->where('item_id',$item['id'])->get('network_loans')->row_array();ok($r['status']===302&&$loan&&$item['status']==='loaned','Issue loan through HTTP persists scoped transaction');
$r=request('/library-workspace/loan-action/'.$loan['id'].'/return',['network_csrf'=>csrf('/library-workspace/loans'),'note'=>'HTTP return test']);ok($r['status']===302&&$db->where('id',$item['id'])->get('network_items')->row()->status==='available','Return through HTTP restores availability');
$r=request('/library-workspace/visits',['network_csrf'=>csrf('/library-workspace/visits'),'member_no'=>$number,'purpose'=>'Layanan digital','channel'=>'offline']);ok($r['status']===302&&$db->where('library_id',4)->where('member_id',$loan['member_id'])->count_all_results('network_visits')===1,'Record digital service offline through HTTP');
require APPPATH.'models/Library_network_model.php';require APPPATH.'libraries/Catalog_xlsx.php';$model=new Library_network_model();$headers=array_keys($model->definitions()['books']['fields']);$input=['title'=>'Excel HTTP '.$tag,'format'=>'physical','status'=>'draft'];$values=[];foreach($headers as$h)$values[]=$input[$h]??'';$writer=new Catalog_xlsx();$xlsx=$writer->build('Data',$headers,[$values]);
try{$r=request('/library-workspace/import/books',['network_csrf'=>csrf('/library-workspace/import/books'),'xlsx'=>new CURLFile($xlsx,'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','books.xlsx')],'a',true);ok($r['status']===200&&strpos($r['body'],'Excel HTTP '.$tag)!==false,'Multipart Excel preview renders');ok($db->where('title',$input['title'])->count_all_results('network_books')===0,'Preview does not insert rows');network_check(preg_match('/name="preview_nonce" value="([a-f0-9]+)"/',$r['body'],$nonce),'Preview nonce missing');network_check(preg_match('/name="network_csrf" value="([a-f0-9]+)"/',$r['body'],$token),'Preview CSRF missing');$r=request('/library-workspace/import/books',['network_csrf'=>$token[1],'preview_nonce'=>$nonce[1],'commit'=>'1']);ok($r['status']===302&&$db->where('library_id',4)->where('title',$input['title'])->count_all_results('network_books')===1,'Confirmed Excel import persists in authenticated library');}finally{unlink($xlsx);}
$r=request('/jejaring/katalog',null,'public');ok($r['status']===200&&strpos($r['body'],'Fixture A')!==false&&strpos($r['body'],'Fixture B PRIVATE')===false&&strpos($r['body'],'HTTP Book')===false,'Public catalog only published titles');
ok(request('/jejaring/katalog/'.$ids['book_b'],null,'public')['status']===404,'Draft detail private');
ok(request('/jejaring/katalog/'.$ids['book_a'],null,'public')['status']===200,'Published detail visible');
foreach(['test-central'=>'admin','test-member'=>'user/dashboard','test-legacy'=>'admin']as$user=>$destination){$r=login($user,$fixture['password'],$user);ok($r['status']===302&&strpos($r['headers'],$destination)!==false,'Existing role login preserved: '.$user);}
$r=request('/library-workspace',null,'test-central');ok($r['status']===200&&strpos($r['body'],'Pilih perpustakaan')!==false,'Central operator can select library');
ok(request('/library-workspace?library_id=6',null,'test-central')['status']===200,'Central operator can manage selected library');
ok(request('/library-workspace',null,'test-member')['status']===403,'Ordinary member cannot enter workspace');
// DB assignment changes must invalidate stale access immediately.
$db->where('id',9000000)->update('auth_user',['status'=>'inactive']);ok(request('/library-workspace')['status']===403,'Disabled account loses active session');$db->where('id',9000000)->update('auth_user',['status'=>'active']);login('test-school-a',$new);
$db->where('id',9000000)->update('auth_user',['library_id'=>null]);ok(request('/library-workspace')['status']===403,'Unassigned account never falls back to global scope');$db->where('id',9000000)->update('auth_user',['library_id'=>4]);login('test-school-a',$new);
$role=$db->where('code','LIBRARY_ADMIN')->get('auth_role')->row()->id;$db->where('user_id',9000000)->delete('auth_user_role');ok(request('/library-workspace')['status']===403,'Revoked role loses active session');$db->insert('auth_user_role',['user_id'=>9000000,'role_id'=>$role]);login('test-school-a',$new);
$db->where('id',4)->update('libraries',['status'=>'inactive']);ok(request('/library-workspace')['status']===403,'Inactive library blocks existing local session');$r=request('/jejaring/katalog',null,'public');ok($r['status']===200&&strpos($r['body'],'Fixture A')===false,'Inactive library publications hidden from public catalog');$db->where('id',4)->update('libraries',['status'=>'active']);login('test-school-a',$new);
$superRole=$db->where('code','SUPERADMIN')->get('auth_role')->row()->id;$db->insert('auth_user_role',['user_id'=>9000000,'role_id'=>$superRole]);ok(request('/admin')['status']===403,'Mixed global and local roles cannot escalate access');$db->where('user_id',9000000)->where('role_id',$superRole)->delete('auth_user_role');login('test-school-a',$new);
$db->where('id',9000000)->update('auth_user',['library_id'=>6]);ok(request('/library-workspace?library_id=4')['status']===403,'Old scope rejected immediately after central reassignment');$db->where('id',9000000)->update('auth_user',['library_id'=>4]);
$db->where('id',9000000)->update('auth_user',['password_hash'=>password_hash($new,PASSWORD_BCRYPT)]);ok(request('/library-workspace')['status']===302,'Password reset revokes old sessions');
echo $checks," HTTP checks passed.\n";
