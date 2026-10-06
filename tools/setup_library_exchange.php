<?php
require __DIR__.'/library_network_bootstrap.php';require __DIR__.'/library_exchange_guards.php';
$dir=null;foreach($argv as $arg)if(strpos($arg,'--test=')===0)$dir=substr($arg,7);
if($dir!==null){network_check(preg_match('#^/tmp/pustaka_network_test_[a-zA-Z0-9_]+$#D',$dir)&&is_file($dir.'/connection.php'),'Private test runtime required.');$params=require $dir.'/connection.php';network_check(strpos($params['database'],'pustaka_network_test_')===0,'Test database required.');$db=DB($params);}else $db=DB('default');$db->db_debug=false;
if(!in_array('--apply',$argv,true)){echo "DRY RUN: four ledger tables, eight inventory hold guards, and central/local menus. No charges/loans created.\n";exit;}
$baseline=[];foreach(['auth_user','libraries','books','book_items','members','loan_transaction_items','network_books','network_items','network_members','network_loans'] as $table)$baseline[$table]=(int)$db->count_all($table);
$states=[];foreach(['book_items','network_items'] as $table)$states[$table]=network_query($db,'SELECT status,COUNT(*) total FROM '.$table.' GROUP BY status ORDER BY status')->result_array();
if($dir===null){$backup=network_backup($db,'sql/2026-10-05a_library_exchange.sql');echo 'Backup: '.$backup.'.sql.gz',PHP_EOL;}
$migrate=function($file)use($db){foreach(preg_split('/;\s*(?:\r?\n|$)/',file_get_contents(FCPATH.$file)) as $sql)if(trim($sql)!=='')network_query($db,$sql);};
$migrate('sql/2026-10-05a_library_exchange.sql');install_library_exchange_guards($db);
$guards=network_query($db,"SELECT COUNT(*) n FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA=DATABASE() AND LEFT(TRIGGER_NAME,12)='pustaka_ill_'")->row()->n;
network_check((int)$guards===8,'All eight inventory guards must be installed before enabling menus.');
$migrate('sql/2026-10-05b_library_exchange_menus.sql');$after=[];foreach($baseline as $table=>$count)$after[$table]=(int)$db->count_all($table);
$statesAfter=[];foreach(['book_items','network_items'] as $table)$statesAfter[$table]=network_query($db,'SELECT status,COUNT(*) total FROM '.$table.' GROUP BY status ORDER BY status')->result_array();
if($dir===null)file_put_contents($backup.'.exchange-verification.json',json_encode(['installed_at'=>date(DATE_ATOM),'baseline'=>$baseline,'after'=>$after,'counts_unchanged'=>$baseline===$after,'inventory_states_before'=>$states,'inventory_states_after'=>$statesAfter,'inventory_states_unchanged'=>$states===$statesAfter,'guards'=>(int)$guards,'guard_code_sha256'=>hash_file('sha256',__DIR__.'/library_exchange_guards.php')],JSON_PRETTY_PRINT));
echo "Exchange/manual-fine tables, eight guards and menus installed.\n";
echo $baseline===$after?"Operational/account counts unchanged.\n":"Review concurrent row count changes.\n";
echo $states===$statesAfter?"Inventory status counts unchanged.\n":"Review concurrent inventory changes.\n";
