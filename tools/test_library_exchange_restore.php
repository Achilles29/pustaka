<?php
require __DIR__.'/library_network_bootstrap.php';
$dir=$argv[1]??'';network_check(preg_match('#^/tmp/pustaka_network_test_[a-zA-Z0-9_]+$#D',$dir)&&is_file($dir.'/connection.php'),'Private fixture directory required.');$params=require $dir.'/connection.php';network_check(strpos($params['database'],'pustaka_network_test_')===0,'Fixture database required.');$db=DB($params);$db->db_debug=false;
// Keep this test independent of which contender won the earlier concurrency race.
if(!$db->where('active_slot',1)->count_all_results('inter_library_loans')){
    require APPPATH.'models/Library_network_model.php';require APPPATH.'models/Library_exchange_model.php';
    $n=new Library_network_model();$n->db=$db;$x=new Library_exchange_model();$x->db=$db;
    $book=$n->save(6,'books',0,['title'=>'Restore hold fixture','format'=>'physical','status'=>'published'],9000002);
    $item=$n->save(6,'items',0,['book_id'=>$book,'barcode'=>'RESTORE-'.bin2hex(random_bytes(5)),'status'=>'available'],9000002);
    $loan=$x->request(4,6,'network',$item,date('Y-m-d',strtotime('+5 days')),'Fixture restore check',9000000);$x->act(6,$loan,'approve','Fixture owner approval','',9000002,true);
}
$restore='pustaka_network_restore_'.date('Ymd_His').'_'.bin2hex(random_bytes(3));$client=tempnam($dir,'restore-client-');$dump=$dir.'/exchange-restore.sql';$ini=function($v){return '"'.str_replace(['\\','"',"\n","\r"],['\\\\','\\"','\\n','\\r'],(string)$v).'"';};
$run=function($args,$input,$output){$p=proc_open($args,[0=>['file',$input,'r'],1=>['file',$output,'w'],2=>['pipe','w']],$pipes);network_check(is_resource($p),'Cannot start database utility.');stream_get_contents($pipes[2]);fclose($pipes[2]);network_check(proc_close($p)===0,'Database backup/restore failed.');};
$created=false;
try{
    file_put_contents($client,"[client]\nhost=".$ini($db->hostname==='localhost'?'127.0.0.1':$db->hostname)."\nuser=".$ini($db->username)."\npassword=".$ini($db->password)."\ndefault-character-set=utf8mb4\n");
    $run(['mariadb-dump','--defaults-extra-file='.$client,'--single-transaction','--quick','--hex-blob','--skip-lock-tables',$db->database],'/dev/null',$dump);
    network_query($db,'CREATE DATABASE `'.$restore.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');$created=true;
    $run(['mariadb','--defaults-extra-file='.$client,'--database='.$restore],$dump,'/dev/null');
    foreach(['inter_library_loans','inter_library_events','library_fines','library_fine_events','book_items','network_items'] as $table){$expected=(int)$db->count_all($table);$actual=(int)network_query($db,'SELECT COUNT(*) n FROM `'.$restore.'`.`'.$table.'`')->row()->n;network_check($expected===$actual,'Restored ledger/item count mismatch.');}
    $guardCount=(int)network_query($db,"SELECT COUNT(*) n FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA=? AND LEFT(TRIGGER_NAME,12)='pustaka_ill_'",[$restore])->row()->n;network_check($guardCount===8,'Inventory guards missing in restored dump.');
    $hold=network_query($db,'SELECT * FROM `'.$restore.'`.inter_library_loans WHERE active_slot=1 LIMIT 1')->row_array();network_check($hold,'Run concurrency tests first to retain an active test hold.');$table=$hold['source']==='network'?'network_items':'book_items';
    network_query($db,'UPDATE `'.$restore.'`.`'.$table.'` SET status=? WHERE id=?',['available',$hold['item_id']]);$item=network_query($db,'SELECT status FROM `'.$restore.'`.`'.$table.'` WHERE id=?',[$hold['item_id']])->row_array();network_check($item['status']==='loaned','Restored trigger does not preserve hold.');
    echo "PASS full isolated dump/restore preserves ledger/item counts, eight triggers, and active-hold behavior.\n";
}finally{if(is_file($client))unlink($client);if($created){network_query($db,'DROP DATABASE `'.$restore.'`');echo "Temporary restore database deleted; fixture backup retained for cleanup.\n";}}
