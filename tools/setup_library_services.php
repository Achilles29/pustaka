<?php
require __DIR__.'/library_network_bootstrap.php';
$dir=null;foreach($argv as $arg)if(strpos($arg,'--test=')===0)$dir=substr($arg,7);
if($dir!==null){network_check(preg_match('#^/tmp/pustaka_network_test_[a-zA-Z0-9_]+$#D',$dir)&&is_file($dir.'/connection.php'),'Private test runtime required.');$params=require $dir.'/connection.php';network_check(strpos($params['database'],'pustaka_network_test_')===0,'Test database required.');$db=DB($params);}else $db=DB('default');
$db->db_debug=false;
if(!in_array('--apply',$argv,true)){echo "DRY RUN: additive inventory/local-service tables and menus, no account or legacy data rewrite.\n";exit;}
$baseline=[];foreach(['libraries','auth_user','books','book_items','members','loan_transactions','loan_transaction_items','member_visits','network_books','network_items','network_members','network_loans','network_visits'] as $table)$baseline[$table]=(int)$db->count_all($table);
if($dir===null){$backup=network_backup($db,'sql/2026-10-04c_library_services.sql');echo 'Backup: '.$backup.'.sql.gz',PHP_EOL;}
// DDL commits implicitly in MariaDB; statements are individually idempotent.
$sql=file_get_contents(FCPATH.'sql/2026-10-04c_library_services.sql');
foreach(preg_split('/;\s*(?:\r?\n|$)/',$sql)as $statement)if(trim($statement)!=='')network_query($db,$statement);
$after=[];foreach($baseline as $table=>$count)$after[$table]=(int)$db->count_all($table);
if($dir===null)file_put_contents($backup.'.services-verification.json',json_encode(['installed_at'=>date(DATE_ATOM),'baseline'=>$baseline,'after'=>$after,'counts_unchanged'=>$baseline===$after],JSON_PRETTY_PRINT));
echo "Library service schema and menus installed.\n";
echo $baseline===$after?"Operational/account row counts unchanged.\n":"Row counts changed during deployment; inspect concurrent activity before concluding migration affected them.\n";
