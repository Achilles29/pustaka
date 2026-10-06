<?php
// Includes central/local authorization and local-circulation HTTP regression.
require __DIR__.'/test_library_network_http.php';
$baseChecks=$checks;
$tag=bin2hex(random_bytes(5));
function http_return_insert($table,$values){global $db;network_check($db->insert($table,$values),'Fixture insert failed');return (int)$db->insert_id();}
function http_return_row($table,$id){global $db;return network_query($db,'SELECT * FROM '.$table.' WHERE id=?',[$id])->row_array();}
$book=http_return_insert('books',['title'=>'HTTP return '.$tag,'status'=>'published']);
$member=http_return_insert('members',['full_name'=>'HTTP return fixture','member_no'=>'HTTP-RT-'.$tag]);
$loans=[];
foreach(['pustaka','inlislite','failure'] as $mode){
    $item=http_return_insert('book_items',['book_id'=>$book,'library_id'=>2,'barcode'=>'HTTP-RT-'.$tag.'-'.$mode,'status'=>'loaned','status_label'=>'Dipinjam','is_loanable'=>1]);
    $source=$mode==='failure'?'pustaka':$mode;
    $header=http_return_insert('loan_transactions',['source_system'=>$source,'source_id'=>'HTTP-RT-'.$tag.'-'.$mode,'member_id'=>$member,'loan_count'=>1,'collection_count'=>1]);
    $loan=http_return_insert('loan_transaction_items',['source_system'=>$source,'source_id'=>'HTTP-RT-'.$tag.'-'.$mode,'loan_transaction_id'=>$header,'book_item_id'=>$item,'member_id'=>$member,'loan_date'=>date('Y-m-d H:i:s'),'due_date'=>date('Y-m-d H:i:s',strtotime('+7 days')),'loan_status'=>'Loan']);
    $loans[$mode]=compact('item','loan','header');
}
login('test-school-a',$new);
$target=$loans['pustaka'];
ok(request('/catalog/loans/return/'.$target['loan'],['return_note'=>'Forbidden'],'a')['status']===403,'School cannot mutate county circulation');
ok(http_return_row('loan_transaction_items',$target['loan'])['actual_return_at']===null,'Rejected school request leaves county loan unchanged');
foreach(['pustaka','inlislite'] as $mode){
    $target=$loans[$mode];$r=request('/catalog/loans/return/'.$target['loan'],['return_note'=>'HTTP regression'],'test-central');
    ok($r['raw_status']===303,'County return endpoint redirects successfully: '.$mode);
    $r=request('/catalog/loans',null,'test-central');
    ok($r['status']===200&&strpos($r['body'],'berhasil disimpan')!==false&&strpos($r['body'],'Database Error')===false,'County return shows success, no raw SQL error: '.$mode);
    $row=http_return_row('loan_transaction_items',$target['loan']);$item=http_return_row('book_items',$target['item']);
    ok(($mode==='pustaka'?$row['actual_return_at']:$row['local_return_at'])!==null&&$item['status']==='available'&&$item['status_label']==='Tersedia','HTTP return persists transaction and inventory together: '.$mode);
    request('/catalog/loans/return/'.$target['loan'],['return_note'=>'Duplicate'],'test-central');
    $r=request('/catalog/loans',null,'test-central');
    ok($r['status']===200&&strpos($r['body'],'sudah dikembalikan')!==false,'HTTP duplicate handled clearly: '.$mode);
}
$target=$loans['failure'];$failItem=$target['item'];
network_query($db,"CREATE TRIGGER return_http_fail BEFORE UPDATE ON book_items FOR EACH ROW BEGIN IF OLD.id=$failItem THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Isolated HTTP failure'; END IF; END");
try{
    $r=request('/catalog/loans/return/'.$target['loan'],['return_note'=>'Fail atomically'],'test-central');
    ok($r['raw_status']===303,'Failed return redirects instead of database 500');
    $r=request('/catalog/loans',null,'test-central');
    ok($r['status']===200&&strpos($r['body'],'Operasi sirkulasi gagal disimpan')!==false&&strpos($r['body'],'Isolated HTTP failure')===false,'Failure shown safely without leaking SQL');
    ok(http_return_row('loan_transaction_items',$target['loan'])['actual_return_at']===null&&(int)http_return_row('loan_transactions',$target['header'])['return_count']===0&&http_return_row('book_items',$target['item'])['status']==='loaned','HTTP failure rolls back all return changes');
}finally{network_query($db,'DROP TRIGGER return_http_fail');}
$r=request('/catalog/loans/return/'.$target['loan'],['return_note'=>'Retry'],'test-central');
ok($r['raw_status']===303&&http_return_row('book_items',$target['item'])['status']==='available','HTTP retry succeeds after rollback');
$orphan=http_return_insert('book_items',['book_id'=>$book,'library_id'=>2,'barcode'=>'HTTP-SYNC-'.$tag,'status'=>'loaned','status_label'=>'Dipinjam','is_loanable'=>1]);
$r=request('/catalog/loans/reconcile',['confirm'=>'1'],'test-central');
ok($r['raw_status']===303&&http_return_row('book_items',$orphan)['status']==='available','Manual reconcile endpoint succeeds with actual candidate row');
$r=request('/catalog/loans',null,'test-central');
ok($r['status']===200&&strpos($r['body'],'Database Error')===false,'Loan page remains healthy after manual reconcile');
echo ($checks-$baseChecks)," county-return HTTP checks passed.\n";
