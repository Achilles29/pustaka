<?php
require __DIR__.'/library_network_bootstrap.php';foreach(['Library_network_model','Library_exchange_model','Library_fines_model','Library_services_model','Loan_model'] as $name)require APPPATH.'models/'.$name.'.php';
$dir=$argv[1]??'';network_check(preg_match('#^/tmp/pustaka_network_test_[a-zA-Z0-9_]+$#D',$dir)&&is_file($dir.'/connection.php'),'Private isolated directory required.');$params=require $dir.'/connection.php';network_check(strpos($params['database'],'pustaka_network_test_')===0,'Isolated database required.');$db=DB($params);$db->db_debug=false;
$n=new Library_network_model();$x=new Library_exchange_model();$f=new Library_fines_model();$s=new Library_services_model();$legacy=new Loan_model();foreach([$n,$x,$f,$s,$legacy] as $model)$model->db=$db;$checks=0;
function check_exchange($v,$m){global $checks;network_check($v,'FAIL '.$m);$checks++;echo 'PASS '.$m,PHP_EOL;}
function deny_exchange($fn,$m){$fail=false;try{$fn();}catch(Throwable $e){$fail=true;}check_exchange($fail,$m);}
$tag=bin2hex(random_bytes(4));$book=$n->save(6,'books',0,['title'=>'Exchange '.$tag,'format'=>'physical','status'=>'published'],9000002);$item=$n->save(6,'items',0,['book_id'=>$book,'barcode'=>'EX-'.$tag,'status'=>'available'],9000002);$n->save(6,'members',0,['member_no'=>'EXM-'.$tag,'full_name'=>'Exchange local borrower','status'=>'active'],9000002);
check_exchange($x->ready(),'Eight inventory guards installed');check_exchange(count($x->discover(4,6,'network',$tag))===1,'Published foreign physical inventory discoverable');
deny_exchange(function()use($x,$item){$x->request(6,6,'network',$item,date('Y-m-d'),'bad',9000002);},'Self-library request rejected');
$due=date('Y-m-d',strtotime('+10 days'));$id=$x->request(4,6,'network',$item,$due,'Requested by A',9000000);check_exchange($x->find(4,$id,true)&&$x->find(6,$id,true)&&!$x->find(8,$id,true),'Only the two parties can read transaction');
deny_exchange(function()use($x,$item,$due){$x->request(4,6,'network',$item,$due,'duplicate',9000000);},'Duplicate active request rejected');
deny_exchange(function()use($x,$id){$x->act(4,$id,'approve','wrong side','',9000000,true);},'Borrower cannot approve as owner');
deny_exchange(function()use($x,$id){$x->act(6,$id,'approve','same account','',9000000,true);},'Same actor cannot consent for both parties');
$x->act(6,$id,'approve','Owner agrees','',9000002,true);check_exchange($n->find(6,'items',$item)['status']==='loaned','Approval holds item against ordinary circulation');
deny_exchange(function()use($n,$tag){$n->issue(6,'EXM-'.$tag,'EX-'.$tag,9000002);},'Ordinary local loan cannot use held asset');
$db->where('id',$item)->update('network_items',['status'=>'available']);check_exchange($n->find(6,'items',$item)['status']==='loaned','Direct status update cannot unlock held item');
check_exchange($db->where('id',$item)->update('network_items',['library_id'=>4])===false,'Ownership reassignment blocked by database');
check_exchange($db->where('id',$item)->update('network_items',['deleted_at'=>date('Y-m-d H:i:s')])===false,'Archiving held asset blocked');
check_exchange($db->where('id',$item)->delete('network_items')===false,'Hard deletion of held asset blocked');
check_exchange($db->where('id',$book)->update('network_books',['deleted_at'=>date('Y-m-d H:i:s')])===false,'Archiving held catalog blocked');
check_exchange($db->where('id',$book)->delete('network_books')===false,'Hard deletion of held catalog blocked');
deny_exchange(function()use($x,$id){$x->act(4,$id,'receive','premature','',9000000,true);},'Receipt before dispatch rejected');
$x->act(6,$id,'dispatch','Shipment 1','',9000002,true);deny_exchange(function()use($x,$id){$x->act(6,$id,'cancel','already dispatched','',9000002,true);},'Cannot release inventory after dispatch by cancellation');
$x->act(4,$id,'receive','Received at A','',9000000,true);$x->act(4,$id,'return','Return shipment','',9000000,true);
deny_exchange(function()use($x,$id){$x->act(4,$id,'complete','wrong party','good',9000000,true);},'Borrower cannot confirm owner receipt');
$x->act(6,$id,'complete','Returned damaged','damaged',9000002,true);$after=$n->find(6,'items',$item);check_exchange($after['status']==='damaged'&&(int)$after['library_id']===6,'Damaged return retains owner and is not made available');
deny_exchange(function()use($x,$id){$x->act(6,$id,'complete','double','good',9000002,true);},'Double completion rejected');check_exchange(count($x->events(4,$id,true))===6,'Every exchange transition recorded');
$n->save(6,'items',$item,['book_id'=>$book,'barcode'=>'EX-'.$tag,'status'=>'available'],9000002);$cancel=$x->request(4,6,'network',$item,$due,'Again',9000000);$x->act(6,$cancel,'approve','Approve','',9000002,true);$x->act(4,$cancel,'cancel','No longer needed','',9000000,true);check_exchange($n->find(6,'items',$item)['status']==='available','Pre-dispatch cancellation releases hold');
// Legacy source, including the synchronization/reconciliation path.
$db->insert('books',['title'=>'Legacy exchange '.$tag,'status'=>'published']);$legacyBook=(int)$db->insert_id();$db->insert('book_items',['book_id'=>$legacyBook,'library_id'=>2,'barcode'=>'LEX-'.$tag,'status'=>'available','collection_type'=>'Buku','is_public'=>1,'is_loanable'=>1]);$legacyItem=(int)$db->insert_id();
$legacyId=$x->request(4,2,'legacy',$legacyItem,$due,'Borrow Perpusda',9000000);$x->act(2,$legacyId,'approve','Perpusda consents','',9000004,false);$reconciled=$legacy->reconcile_item_availability();check_exchange($db->error()['code']===0&&$reconciled['loaned']>=0&&$reconciled['available']>=0,'Legacy reconciliation queries succeed, not silently fail');check_exchange($db->where('id',$legacyItem)->get('book_items')->row()->status==='loaned','Legacy availability reconciliation respects institutional hold');
$db->where('id',$legacyItem)->update('book_items',['status'=>'available','status_label'=>'Tersedia']);check_exchange($db->where('id',$legacyItem)->get('book_items')->row()->status_label==='Dipinjam antarlembaga','Legacy synchronizer-style overwrite preserves held status');
$stock=$s->stock_create(2,'legacy','Exchange stock',9000004);$entries=array_column($s->stock_entries(2,'legacy',$stock),null,'item_id');check_exchange($entries[$legacyItem]['on_loan']==1,'Stock opname recognizes legacy institutional loan');$s->stock_close(2,'legacy',$stock,'closed','Checked',9000004);
$x->act(2,$legacyId,'dispatch','Sent','',9000004);$x->act(4,$legacyId,'receive','Received','',9000000,true);$x->act(4,$legacyId,'return','Return','',9000000,true);$x->act(2,$legacyId,'complete','Received back','good',9000004);check_exchange($db->where('id',$legacyItem)->get('book_items')->row()->status==='available','Legacy full exchange cycle releases original item');
// Manual fines anchored to existing scoped loans, never free-form cross-library members.
$netLoan=$db->where('library_id',4)->get('network_loans')->row_array();network_check($netLoan,'Run core model tests first.');
$fine=$f->create(4,'network',$netLoan['id'],'5000','Manual late charge',9000000,true);check_exchange($f->find(4,$fine,true)&&!$f->find(6,$fine,true),'Manual fine scoped to creditor library');
deny_exchange(function()use($f,$netLoan){$f->create(6,'network',$netLoan['id'],'5000','Foreign',9000002,true);},'Cannot charge foreign member loan');
deny_exchange(function()use($f){$f->create(4,'legacy',1,'5000','Legacy bypass',9000000,true);},'School cannot charge legacy member dataset');
foreach(['0','-1','1.5','1000000001','1e3'] as $amount)deny_exchange(function()use($f,$netLoan,$amount){$f->create(4,'network',$netLoan['id'],$amount,'invalid',9000000,true);},'Invalid manual amount rejected: '.$amount);
$f->act(4,$fine,'payment','1000','Receipt 1','Verified payment',0,9000000,true);$events=$f->events(4,$fine,true);$payment=$events[0]['id'];$f->act(4,$fine,'waiver','500','Decision 1','Approved waiver',0,9000000,true);
deny_exchange(function()use($f,$fine){$f->act(4,$fine,'payment','4000','Over','overpayment',0,9000000,true);},'Payment cannot exceed remaining balance');
deny_exchange(function()use($f,$fine){$f->act(6,$fine,'payment','100','Wrong','foreign',0,9000002,true);},'Foreign fine mutation rejected');
deny_exchange(function()use($f,$fine){$f->act(4,$fine,'void','','','not empty',0,9000000,true);},'Cannot void charge with net settlements');
$f->act(4,$fine,'reversal','','Correction 1','Correction only, not money transfer',$payment,9000000,true);deny_exchange(function()use($f,$fine,$payment){$f->act(4,$fine,'reversal','','Twice','duplicate',$payment,9000000,true);},'Same receipt cannot be reversed twice');
$events=$f->events(4,$fine,true);$f->act(4,$fine,'reversal','','Correction 2','Reverse waiver',$events[1]['id'],9000000,true);$f->act(4,$fine,'void','','','Entered for wrong reason',0,9000000,true);check_exchange($f->find(4,$fine,true)['state']==='void'&&count($f->events(4,$fine,true))===4,'Void preserves all original and correction records');
deny_exchange(function()use($f,$fine){$f->act(4,$fine,'payment','1','Closed','closed',0,9000000,true);},'Void charge immutable');
$institutionFine=$f->create(6,'interlibrary',$id,2000,'Manual damage decision',9000002,true);check_exchange($f->find(6,$institutionFine,true)['borrower_label']!==null,'Institutional fine belongs to original owner');
deny_exchange(function()use($f,$id){$f->create(4,'interlibrary',$id,2000,'Wrong creditor',9000000,true);},'Borrower cannot charge as asset owner');
$db->insert('members',['full_name'=>'Legacy manual fine fixture','member_no'=>'LFM-'.$tag]);$legacyMember=(int)$db->insert_id();$db->insert('loan_transaction_items',['book_item_id'=>$legacyItem,'member_id'=>$legacyMember,'loan_date'=>date('Y-m-d H:i:s'),'loan_status'=>'Return','actual_return_at'=>date('Y-m-d H:i:s')]);$legacyMemberLoan=(int)$db->insert_id();
$legacyFine=$f->create(2,'legacy',$legacyMemberLoan,3000,'Legacy manual decision',9000004,false);check_exchange($f->find(2,$legacyFine,false)['source']==='legacy','Perpusda can record manual fine for its legacy loan');check_exchange(!$f->find(2,$legacyFine,true),'Local mode cannot read legacy fine even with matching institution');
$f->act(2,$legacyFine,'payment',3000,'Legacy receipt','Verified at Perpusda',0,9000004,false);check_exchange(count($f->events(2,$legacyFine,false))===1,'Legacy fine accepts manual settlement');
file_put_contents($dir.'/exchange.json',json_encode(compact('book','item','id','legacyBook','legacyItem','legacyId','fine','institutionFine','tag','netLoan')));echo $checks," exchange/fine model checks passed.\n";
