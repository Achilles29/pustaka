<?php
require __DIR__.'/library_network_bootstrap.php';
require APPPATH.'models/Library_network_model.php';require APPPATH.'libraries/Catalog_xlsx.php';
$db=DB('default');$db->db_debug=false;$model=new Library_network_model();$model->db=$db;
try{
    $schools=network_query($db,"SELECT l.id,l.code,l.name,l.district FROM libraries l JOIN library_types t ON t.id=l.library_type_id WHERE l.source_system='school_xlsx' AND t.code='sekolah' AND l.status='active' AND l.is_verified=1 ORDER BY l.id")->result_array();
    network_check(count($schools)===410,'Expected exactly 410 verified imported schools. Review targets before provisioning.');
    $actor=network_query($db,"SELECT u.id FROM auth_user u JOIN auth_user_role ur ON ur.user_id=u.id JOIN auth_role r ON r.id=ur.role_id WHERE r.code='SUPERADMIN' AND u.status='active' ORDER BY u.id LIMIT 1")->row_array();network_check($actor,'Central audit actor not found.');
    $existing=network_query($db,'SELECT id,username,library_id,source_system FROM auth_user')->result_array();$used=[];$already=[];
    foreach($existing as$row){$used[strtolower($row['username'])]=true;if($row['source_system']===Library_network_model::SOURCE)$already[(int)$row['library_id']][]=$row;}
    $slug=function($name){return trim(preg_replace('/[^a-z0-9]+/','-',strtolower(iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$name))),'-');};$plans=[];$skip=0;
    foreach($schools as$school){
        if(isset($already[(int)$school['id']])){$skip++;continue;}
        $base=substr($slug($school['name']),0,65);$username=$base;
        if(isset($used[$username]))$username=substr($base,0,55).'-'.substr($slug($school['district']),0,24);
        if(isset($used[$username]))$username=substr($base,0,65).'-'.$slug($school['code']);
        network_check(!isset($used[$username])&&strlen($username)>=5&&strlen($username)<=80,'Username collision requires review.');$used[$username]=true;$school['username']=$username;$plans[]=$school;
    }
    echo json_encode(['targets'=>count($schools),'new_accounts'=>count($plans),'already_provisioned_libraries'=>$skip,'role'=>Library_network_model::ROLE,'forced_password_change'=>true],JSON_PRETTY_PRINT),"\n";
    if(!in_array('--apply',$argv,true)){echo "DRY RUN: no database changes.\n";exit;}
    network_check(count($plans)===410&&$skip===0,'Initial activation already ran or is partial; no existing account will be reset.');
    $lock=fopen('/www/backup/pustaka-network-setup.lock','c');network_check($lock&&flock($lock,LOCK_EX|LOCK_NB),'Another provisioning run is active.');
    $backup=network_backup($db);echo 'Verified full backup: '.$backup.'.sql.gz',"\n";
    $legacy=network_query($db,'SELECT id,username,password_hash,library_id,source_system,status,force_password_change FROM auth_user ORDER BY id')->result_array();
    $legacyRoles=network_query($db,'SELECT * FROM auth_user_role ORDER BY user_id,role_id')->result_array();
    $baseline=[];foreach(['libraries','library_photos','members','books','book_items','loan_transactions','loan_transaction_items','loan_renewal_logs','member_visits','member_visit_demographics','member_visit_simulations']as$table)if($db->table_exists($table))$baseline[$table]=(int)$db->count_all($table);
    network_migrate($db);$password='Rembang!'.random_int(1000,9999);$records=[];
    network_check($db->trans_begin(),'Cannot begin provisioning transaction.');
    try{
        foreach($plans as$school){
            $id=$model->create_admin((int)$school['id'],['username'=>$school['username'],'full_name'=>$school['name'],'password'=>$password],(int)$actor['id']);
            $stored=network_query($db,'SELECT password_hash,force_password_change,library_id FROM auth_user WHERE id=?',[$id])->row_array();network_check(password_verify($password,$stored['password_hash'])&&(int)$stored['force_password_change']===1&&(int)$stored['library_id']===(int)$school['id'],'Provisioned account verification failed.');
            $records[]=['library_id'=>(int)$school['id'],'npsn'=>$school['code'],'name'=>$school['name'],'district'=>$school['district'],'username'=>$school['username'],'user_id'=>$id];
        }
        $ids=array_column($legacy,'id');$after=[];$rolesAfter=[];
        foreach(array_chunk($ids,500)as$chunk){
            $after=array_merge($after,$db->select('id,username,password_hash,library_id,source_system,status,force_password_change')->where_in('id',$chunk)->order_by('id')->get('auth_user')->result_array());
            $rolesAfter=array_merge($rolesAfter,$db->where_in('user_id',$chunk)->order_by('user_id')->order_by('role_id')->get('auth_user_role')->result_array());
        }
        network_check($after===$legacy,'Existing account security fields changed during activation.');
        network_check($rolesAfter===$legacyRoles,'Existing role assignments changed.');
        $credentials=$backup.'.accounts.xlsx';$writer=new Catalog_xlsx();$temp=$writer->build('Akun Perpustakaan',['Library ID','NPSN','Sekolah','Kecamatan','Username','Password awal','Peran','Wajib ganti password'],array_map(function($r)use($password){return [$r['library_id'],$r['npsn'],$r['name'],$r['district'],$r['username'],$password,'Admin Perpustakaan','Ya'];},$records));
        network_check(rename($temp,$credentials),'Cannot preserve private credential workbook.');chmod($credentials,0600);
        network_check(file_put_contents($backup.'.accounts.json',json_encode(['created_at'=>date(DATE_ATOM),'backup'=>$backup.'.sql.gz','baseline'=>$baseline,'accounts'=>$records],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE))!==false,'Cannot write provisioning manifest.');
        network_check($db->trans_status()&&$db->trans_commit(),'Provisioning commit failed.');
    }catch(Throwable$e){$db->trans_rollback();throw $e;}
    $afterCounts=[];foreach($baseline as$table=>$count)$afterCounts[$table]=(int)$db->count_all($table);
    echo json_encode(['activated'=>count($records),'credentials_private_file'=>$credentials,'manifest'=>$backup.'.accounts.json','legacy_counts_before'=>$baseline,'legacy_counts_after'=>$afterCounts,'existing_accounts_and_roles_unchanged'=>true],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),"\n";
    flock($lock,LOCK_UN);fclose($lock);
}catch(Throwable$e){fwrite(STDERR,$e->getMessage()."\n");exit(1);}
