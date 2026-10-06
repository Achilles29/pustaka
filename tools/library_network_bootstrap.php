<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
if(!defined('FCPATH'))define('FCPATH',dirname(__DIR__).'/');
if(!defined('APPPATH'))define('APPPATH',FCPATH.'application/');
if(!defined('BASEPATH'))define('BASEPATH',FCPATH.'system/');
if(!defined('ENVIRONMENT'))define('ENVIRONMENT','production');
require_once BASEPATH.'core/Common.php';
require_once BASEPATH.'core/Model.php';
require_once BASEPATH.'database/DB.php';
date_default_timezone_set('Asia/Jakarta');umask(0077);
function network_check($ok,$message){if(!$ok)throw new RuntimeException($message);}
function network_query($db,$sql,$args=[]){$result=$db->query($sql,$args?:false);network_check($result!==false,'Database operation failed ('.$db->error()['code'].').');return $result;}
function network_migrate($db){
    $sql=file_get_contents(FCPATH.'sql/2026-10-04a_library_network.sql');
    foreach(preg_split('/;\s*(?:\r?\n|$)/',$sql)as$statement)if(trim($statement)!=='')network_query($db,$statement);
}
function network_backup($db,$migration='sql/2026-10-04a_library_network.sql'){
    $directory='/www/backup/pustaka-network';
    if(!is_dir($directory))network_check(mkdir($directory,0700,true),'Cannot create backup directory.');
    network_check(realpath($directory)===$directory&&!is_link($directory),'Invalid backup path.');chmod($directory,0700);
    $base=$directory.'/before-network-'.date('Ymd-His').'-'.bin2hex(random_bytes(4));
    $ini=function($v){return '"'.str_replace(['\\','"',"\n","\r"],['\\\\','\\"','\\n','\\r'],(string)$v).'"';};
    $config=tempnam($directory,'client-');
    $run=function($args,$output){$p=proc_open($args,[0=>['file','/dev/null','r'],1=>['file',$output,'w'],2=>['pipe','w']],$pipes);network_check(is_resource($p),'Cannot run backup.');stream_get_contents($pipes[2]);fclose($pipes[2]);network_check(proc_close($p)===0,'Backup command failed.');};
    try{
        network_check(file_put_contents($config,"[client]\nhost=".$ini($db->hostname==='localhost'?'127.0.0.1':$db->hostname)."\nport=".(int)($db->port?:3306)."\nuser=".$ini($db->username)."\npassword=".$ini($db->password)."\ndefault-character-set=utf8mb4\n")!==false,'Cannot create private backup configuration.');
        $run(['mariadb-dump','--defaults-extra-file='.$config,'--single-transaction','--quick','--hex-blob','--skip-lock-tables',$db->database],$base.'.sql');
    }finally{if(is_file($config))unlink($config);}
    network_check(filesize($base.'.sql')>100,'Empty backup.');
    $run(['gzip','-c',$base.'.sql'],$base.'.sql.gz');$run(['gzip','-t',$base.'.sql.gz'],'/dev/null');
    file_put_contents($base.'.json',json_encode(['database'=>$db->database,'created_at'=>date(DATE_ATOM),'sha256'=>hash_file('sha256',$base.'.sql.gz'),'migration'=>$migration,'migration_sha256'=>hash_file('sha256',FCPATH.$migration)],JSON_PRETTY_PRINT));
    return $base;
}
