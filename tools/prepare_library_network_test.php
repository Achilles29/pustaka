<?php
require __DIR__.'/library_network_bootstrap.php';
$db=DB('default');$db->db_debug=false;
$name='pustaka_network_test_'.date('Ymd_His').'_'.bin2hex(random_bytes(3));
$directory=sys_get_temp_dir().'/'.$name;network_check(mkdir($directory,0700),'Cannot create test directory.');
network_query($db,'CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$params=['hostname'=>$db->hostname,'username'=>$db->username,'password'=>$db->password,'database'=>$name,'dbdriver'=>'mysqli','db_debug'=>false,'pconnect'=>false,'char_set'=>'utf8mb4','dbcollat'=>'utf8mb4_unicode_ci'];
$test=DB($params);network_query($test,'SET FOREIGN_KEY_CHECKS=0');
foreach($db->list_tables()as$table){
    $create=$db->query('SHOW CREATE TABLE `'.$table.'`')->row_array();
    if(!isset($create['Create Table']))continue;
    network_query($test,$create['Create Table']);
}
foreach(['library_types','library_subtypes','ref_districts','ref_villages','libraries','auth_role','sys_page','sys_menu','auth_role_permission']as$table){
    if(!$db->table_exists($table))continue;
    network_query($test,'INSERT INTO `'.$table.'` SELECT * FROM `'.$db->database.'`.`'.$table.'`');
}
network_query($test,'SET FOREIGN_KEY_CHECKS=1');network_migrate($test);
$password='TestOnly!'.bin2hex(random_bytes(8));
foreach([9000000=>['test-school-a',4,'LIBRARY_ADMIN',1],9000002=>['test-school-b',6,'LIBRARY_ADMIN',0],9000004=>['test-central',null,'SUPERADMIN',0],9000006=>['test-member',null,'USER',0],9000008=>['test-legacy',null,'ADMIN',0]]as$id=>$row){
    network_check($test->insert('auth_user',['id'=>$id,'username'=>$row[0],'full_name'=>'Pengujian '.$row[0],'library_id'=>$row[1],'password_hash'=>password_hash($password,PASSWORD_BCRYPT),'source_system'=>$row[2]==='LIBRARY_ADMIN'?'library_network_v1':'network_test','status'=>'active','force_password_change'=>$row[3]]),'Cannot create fixture account.');
    $role=network_query($test,'SELECT id FROM auth_role WHERE code=?',[$row[2]])->row_array();
    network_check($test->insert('auth_user_role',['user_id'=>$id,'role_id'=>$role['id']]),'Cannot assign fixture role.');
}
file_put_contents($directory.'/connection.php','<?php return '.var_export($params,true).';');
file_put_contents($directory.'/fixtures.json',json_encode(['database'=>$name,'password'=>$password,'directory'=>$directory]));
mkdir($directory.'/application',0700);mkdir($directory.'/application/config',0700);mkdir($directory.'/sessions',0700);mkdir($directory.'/logs',0700);
foreach(glob(APPPATH.'*',GLOB_ONLYDIR)as$path){$part=basename($path);if($part!=='config'&&$part!=='logs')symlink($path,$directory.'/application/'.$part);}
symlink($directory.'/logs',$directory.'/application/logs');
foreach(glob(APPPATH.'config/*.php')as$file)if(basename($file)!=='database.php')copy($file,$directory.'/application/config/'.basename($file));
file_put_contents($directory.'/application/config/database.php',"<?php \$active_group='default'; \$query_builder=true; \$db['default']=".var_export($params,true).';');
file_put_contents($directory.'/application/config/config.php',"\n\$config['sess_save_path']=".var_export($directory.'/sessions',true).";\n\$config['log_path']=".var_export($directory.'/logs/',true).";\n\$config['log_threshold']=1;\n\$config['cookie_secure']=false;\n",FILE_APPEND);
copy(__DIR__.'/library_network_test_router.php',$directory.'/router.php');
echo $directory,"\n";
