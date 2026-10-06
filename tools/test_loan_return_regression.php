<?php
/** Real MariaDB + all eight exchange triggers, never the production database. */
require __DIR__.'/library_network_bootstrap.php';
require APPPATH.'models/Loan_model.php';
require APPPATH.'models/Library_exchange_model.php';
$dir=$argv[1]??'';
network_check(preg_match('#^/tmp/pustaka_network_test_[a-zA-Z0-9_]+$#D',$dir)&&is_file($dir.'/connection.php'),'Private isolated directory required.');
$params=require $dir.'/connection.php';
network_check(strpos($params['database'],'pustaka_network_test_')===0,'Isolated database required.');
$db=DB($params);$db->db_debug=false;
$model=new Loan_model();$model->db=$db;$exchange=new Library_exchange_model();$exchange->db=$db;
if(($argv[2]??'')==='--return-worker'){
    while(microtime(true)<(float)$argv[3])usleep(1000);
    try{$model->return_loan((int)$argv[4],9000004);echo 'accepted';}
    catch(RuntimeException $e){echo strpos($e->getMessage(),'sudah dikembalikan')!==false?'already-returned':'unexpected-error';}
    exit;
}
$checks=0;$tag=bin2hex(random_bytes(5));
function return_ok($ok,$message){global $checks;network_check($ok,'FAIL '.$message);$checks++;echo 'PASS '.$message,PHP_EOL;}
function return_reject($fn,$message){$failed=false;try{$fn();}catch(RuntimeException $e){$failed=true;}return_ok($failed,$message);}
function return_insert($table,$data){global $db;network_check($db->insert($table,$data),'Fixture insert failed: '.$table);return (int)$db->insert_id();}
function return_row($table,$id){global $db;return network_query($db,'SELECT * FROM '.$table.' WHERE id=?',[$id])->row_array();}
$book=return_insert('books',['title'=>'Return regression '.$tag,'status'=>'published']);
$member=return_insert('members',['full_name'=>'Return regression fixture','member_no'=>'RT-'.$tag]);
function return_item($status='loaned',$loanable=1){global $book,$tag;static $n=0;return return_insert('book_items',['book_id'=>$book,'library_id'=>2,'barcode'=>'RT-'.$tag.'-'.++$n,'status'=>$status,'status_label'=>$status==='available'?'Tersedia':'Dipinjam','is_loanable'=>$loanable,'is_public'=>1]);}
function return_fixture($item,$source='pustaka'){
    global $member,$tag;static $n=0;$ref='RTR-'.$tag.'-'.++$n;
    $transaction=return_insert('loan_transactions',['source_system'=>$source,'source_id'=>$ref,'member_id'=>$member,'collection_count'=>1,'loan_count'=>1,'return_count'=>0]);
    $loan=return_insert('loan_transaction_items',['source_system'=>$source,'source_id'=>$ref.'-1','source_loan_id'=>$ref,'loan_transaction_id'=>$transaction,'book_item_id'=>$item,'member_id'=>$member,'loan_date'=>date('Y-m-d H:i:s'),'due_date'=>date('Y-m-d H:i:s',strtotime('-2 days')),'loan_status'=>'Loan']);
    return [$loan,$transaction];
}
return_ok($exchange->ready(),'All eight production-equivalent hold guards present');
$orphan=return_item();
// The historical SQL must actually fail; checking only unchanged holds hid this bug.
$old=$db->query("UPDATE book_items bi SET bi.status='available' WHERE bi.id=? AND NOT EXISTS(SELECT 1 FROM inter_library_loans x WHERE x.source='legacy' AND x.item_id=bi.id AND x.active_slot=1)",[$orphan]);
return_ok($old===false&&(int)$db->error()['code']===1442,'Original SQL reproduces MariaDB 1442');
$item=return_item();[$loan,$transaction]=return_fixture($item);
$result=$model->return_loan($loan,9000004);
return_ok(return_row('loan_transaction_items',$loan)['loan_status']==='Return'&&!$result['is_legacy']&&$result['late_days']===2,'Local return persists actual return and lateness');
$state=return_row('book_items',$item);
return_ok($state['status']==='available'&&$state['status_label']==='Tersedia'&&$result['availability']['available']===1,'Local return atomically restores status and label');
return_ok((int)return_row('loan_transactions',$transaction)['return_count']===1,'Local transaction header updated');
return_ok(return_row('book_items',$orphan)['status']==='loaned','Returning one item does not reconcile unrelated items');
$before=return_row('loan_transaction_items',$loan);return_reject(function()use($model,$loan){$model->return_loan($loan,9000004);},'Duplicate return rejected');
return_ok(return_row('loan_transaction_items',$loan)===$before,'Duplicate return leaves original timestamp intact');
return_reject(function()use($model){$model->return_loan(2147483647,9000004);},'Missing loan rejected');
$legacyItem=return_item();[$legacyLoan]=return_fixture($legacyItem,'inlislite');
$legacyBefore=return_row('loan_transaction_items',$legacyLoan);$legacy=$model->return_loan($legacyLoan,9000004,'Legacy test');$legacyAfter=return_row('loan_transaction_items',$legacyLoan);
return_ok($legacy['is_legacy']&&$legacyAfter['local_return_at']!==null&&$legacyAfter['actual_return_at']===null&&$legacyAfter['loan_status']===$legacyBefore['loan_status'],'Legacy return uses local override, preserving source history');
return_ok(return_row('book_items',$legacyItem)['status']==='available','Legacy override releases item');
$shared=return_item();[$first]=return_fixture($shared);[$second]=return_fixture($shared,'inlislite');
$model->return_loan($first,9000004);return_ok(return_row('book_items',$shared)['status']==='loaned','Another active loan keeps the same item loaned');
$model->return_loan($second,9000004);return_ok(return_row('book_items',$shared)['status']==='available','Last active loan releases the shared item');
$disabled=return_item('loaned',0);[$disabledLoan]=return_fixture($disabled);$model->return_loan($disabledLoan,9000004);$state=return_row('book_items',$disabled);
return_ok($state['status']==='available'&&(int)$state['is_loanable']===0,'Return still works after loan permission disabled without re-enabling it');
$damaged=return_item('damaged');[$damagedLoan]=return_fixture($damaged);$model->return_loan($damagedLoan,9000004);
return_ok(return_row('book_items',$damaged)['status']==='damaged','Return does not overwrite damaged inventory');
$held=return_item('available');$hold=$exchange->request(4,2,'legacy',$held,date('Y-m-d',strtotime('+7 days')),'Return test hold',9000000);
$exchange->act(2,$hold,'approve','Approved','',9000004,false);
// Simulate imported historical overlap; institutional hold must win.
[$overlap]=return_fixture($held,'inlislite');$model->return_loan($overlap,9000004);
$state=return_row('book_items',$held);return_ok($state['status']==='loaned'&&$state['status_label']==='Dipinjam antarlembaga','Member return cannot release institutional hold');
$sync=$model->reconcile_item_availability();
return_ok($db->error()['code']===0&&min($sync)>=0&&return_row('book_items',$orphan)['status']==='available','Global reconcile succeeds with changed ordinary rows and institutional hold');
return_ok(return_row('book_items',$held)['status_label']==='Dipinjam antarlembaga','Global reconcile preserves institutional label');
return_ok($model->reconcile_item_availability()===['loaned'=>0,'available'=>0],'Repeated reconciliation is idempotent');
// Inject a real database failure in isolated DB, after detail/header writes.
$failureItem=return_item();[$failureLoan,$failureHeader]=return_fixture($failureItem);
$beforeLoan=return_row('loan_transaction_items',$failureLoan);$beforeHeader=return_row('loan_transactions',$failureHeader);$beforeItem=return_row('book_items',$failureItem);
network_query($db,"CREATE TRIGGER return_regression_fail BEFORE UPDATE ON book_items FOR EACH ROW BEGIN IF OLD.id=$failureItem THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Isolated regression failure'; END IF; END");
try{
    $db->db_debug=true;
    return_reject(function()use($model,$failureLoan){$model->return_loan($failureLoan,9000004);},'Database failure is catchable, not a raw 500 exit');
    return_ok($db->db_debug===true,'Database debug configuration restored after failure');$db->db_debug=false;
    return_ok(return_row('loan_transaction_items',$failureLoan)===$beforeLoan&&return_row('loan_transactions',$failureHeader)===$beforeHeader&&return_row('book_items',$failureItem)===$beforeItem,'Failure rolls back detail, header and inventory together');
    return_ok(!$db->trans_active(),'No transaction remains open after failure');
}finally{$db->db_debug=false;network_query($db,'DROP TRIGGER return_regression_fail');}
$model->return_loan($failureLoan,9000004);return_ok(return_row('book_items',$failureItem)['status']==='available','Retry after rollback succeeds exactly once');
// Second sync query fails: first query must also roll back.
$needsLoan=return_item('available');return_fixture($needsLoan);$needsAvailable=return_item();
network_query($db,"CREATE TRIGGER return_regression_fail BEFORE UPDATE ON book_items FOR EACH ROW BEGIN IF OLD.id=$needsAvailable THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Isolated reconciliation failure'; END IF; END");
try{
    return_reject(function()use($model){$model->reconcile_item_availability();},'Failed global reconciliation reports failure');
    return_ok(return_row('book_items',$needsLoan)['status']==='available'&&return_row('book_items',$needsAvailable)['status']==='loaned','Global reconciliation rolls back both update phases');
}finally{network_query($db,'DROP TRIGGER return_regression_fail');}
$model->reconcile_item_availability();
return_ok(return_row('book_items',$needsLoan)['status']==='loaned'&&return_row('book_items',$needsAvailable)['status']==='available','Global reconciliation retries successfully');
$exchange->act(4,$hold,'cancel','Fixture complete','',9000000,true);
return_ok(return_row('book_items',$held)['status']==='available','Institutional cancellation still releases its own hold');
// Two real connections return one loan simultaneously.
$concurrentItem=return_item();[$concurrentLoan]=return_fixture($concurrentItem);
$jobs=[];$start=microtime(true)+.4;
for($i=0;$i<2;$i++){
    $process=proc_open([PHP_BINARY,__FILE__,$dir,'--return-worker',(string)$start,(string)$concurrentLoan],[0=>['file','/dev/null','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);
    network_check(is_resource($process),'Cannot start return worker.');$jobs[]=[$process,$pipes];
}
$outputs=[];
foreach($jobs as [$process,$pipes]){
    $outputs[]=stream_get_contents($pipes[1]);$errors=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
    network_check(proc_close($process)===0&&$errors==='','Return worker failed.');
}
sort($outputs);
return_ok($outputs===['accepted','already-returned'],'Concurrent duplicate returns: one success, one already-returned (no database error)');
return_ok(return_row('book_items',$concurrentItem)['status']==='available'&&return_row('loan_transaction_items',$concurrentLoan)['actual_return_at']!==null,'Concurrent return leaves consistent item and loan');
echo $checks," return/reconciliation regression checks passed.\n";
