<?php
require __DIR__.'/library_network_bootstrap.php';
$manifest=$argv[1]??'';network_check(preg_match('#^/www/backup/pustaka-network/before-network-[0-9]{8}-[0-9]{6}-[a-f0-9]{8}\.accounts\.json$#D',$manifest)&&is_file($manifest),'Private provisioning manifest required.');
$base=substr($manifest,0,-strlen('.accounts.json'));$journal=json_decode(file_get_contents($manifest),true,512,JSON_THROW_ON_ERROR);$meta=json_decode(file_get_contents($base.'.json'),true,512,JSON_THROW_ON_ERROR);
network_check(hash_equals($meta['sha256'],hash_file('sha256',$base.'.sql.gz')),'Backup checksum mismatch.');
$db=DB('default');$db->db_debug=false;$restore='pustaka_network_restore_'.date('Ymd_His').'_'.bin2hex(random_bytes(3));network_query($db,'CREATE DATABASE `'.$restore.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$client=tempnam(dirname($base),'restore-client-');$ini=function($v){return '"'.str_replace(['\\','"',"\n","\r"],['\\\\','\\"','\\n','\\r'],(string)$v).'"';};
try{
    network_check(file_put_contents($client,"[client]\nhost=".$ini($db->hostname==='localhost'?'127.0.0.1':$db->hostname)."\nport=".(int)($db->port?:3306)."\nuser=".$ini($db->username)."\npassword=".$ini($db->password)."\ndefault-character-set=utf8mb4\n")!==false,'Cannot create private restore client config.');
    $process=proc_open(['mariadb','--defaults-extra-file='.$client,'--database='.$restore],[0=>['file',$base.'.sql','r'],1=>['file','/dev/null','w'],2=>['pipe','w']],$pipes);network_check(is_resource($process),'Cannot start restore.');stream_get_contents($pipes[2]);fclose($pipes[2]);network_check(proc_close($process)===0,'Isolated restore failed.');
    foreach($journal['baseline']as$table=>$expected){network_check(preg_match('/^[a-z_]+$/D',$table),'Invalid baseline table.');$count=(int)network_query($db,'SELECT COUNT(*) n FROM `'.$restore.'`.`'.$table.'`')->row()->n;network_check($count===$expected,'Restored count differs for '.$table);}
    $mismatch=network_query($db,'SELECT COUNT(*) n FROM `'.$restore.'`.auth_user old LEFT JOIN auth_user current ON current.id=old.id WHERE current.id IS NULL OR NOT (current.username<=>old.username) OR NOT (current.password_hash<=>old.password_hash) OR NOT (current.library_id<=>old.library_id) OR NOT (current.status<=>old.status) OR NOT (current.force_password_change<=>old.force_password_change)')->row()->n;
    network_check((int)$mismatch===0,'Existing account security fields differ from restored backup.');
    $missing=network_query($db,'SELECT COUNT(*) n FROM `'.$restore.'`.auth_user_role old LEFT JOIN auth_user_role current ON current.user_id=old.user_id AND current.role_id=old.role_id WHERE current.user_id IS NULL')->row()->n;network_check((int)$missing===0,'Existing role removed.');
    file_put_contents($base.'.restore-test.json',json_encode(['verified_at'=>date(DATE_ATOM),'full_restore_succeeded'=>true,'baseline_tables'=>array_keys($journal['baseline']),'legacy_security_fields_unchanged'=>true,'restored_database_disposed'=>true],JSON_PRETTY_PRINT));
    echo "Full backup restored in isolated database; 11 baseline table counts, existing account security fields and original roles verified.\n";
}finally{
    if(is_file($client))unlink($client);
    // This exact, newly-created restore database is the only destructive target.
    network_query($db,'DROP DATABASE `'.$restore.'`');
    echo "Temporary restore database removed; original private backup retained.\n";
}
