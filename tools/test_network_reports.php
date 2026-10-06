<?php
require __DIR__.'/library_network_bootstrap.php';require APPPATH.'models/Network_report_model.php';
$dir=$argv[1]??'';network_check(preg_match('#^/tmp/pustaka_network_test_[a-zA-Z0-9_]+$#D',$dir)&&is_file($dir.'/connection.php'),'Private test directory required.');$params=require $dir.'/connection.php';network_check(strpos($params['database'],'pustaka_network_test_')===0,'Only isolated test databases allowed.');$db=DB($params);$db->db_debug=false;$m=new Network_report_model();$m->db=$db;$checks=0;
function check_report($ok,$message){global$checks;network_check($ok,'FAIL '.$message);$checks++;echo 'PASS '.$message,"\n";}
$insert=function($table,$row)use($db){network_check($db->insert($table,$row),'Fixture insert failed: '.$table);return(int)$db->insert_id();};
$f=$m->filters(['year'=>'2026']);$before=$m->report($f);
$isbn='9780306406157';$book=$insert('books',['title'=>'Shared legacy title','isbn'=>$isbn]);$digital=$insert('books',['title'=>'Digital legacy','isbn'=>'bad']);$unknown=$insert('books',['title'=>'Unassigned title']);$deleted=$insert('books',['title'=>'Deleted excluded','deleted_at'=>'2026-01-01 00:00:00']);
$itemA=$insert('book_items',['book_id'=>$book,'library_id'=>4,'barcode'=>'legacy-A','collection_type'=>'Buku']);$itemB=$insert('book_items',['book_id'=>$book,'library_id'=>6,'barcode'=>'legacy-B','collection_type'=>'Buku']);$insert('book_items',['book_id'=>$digital,'library_id'=>4,'barcode'=>'legacy-digital','collection_type'=>'Ebook']);$insert('book_items',['book_id'=>$deleted,'library_id'=>4,'barcode'=>'deleted-item','collection_type'=>'Buku']);
$member=$insert('members',['full_name'=>'Same Name Is Not Identity','member_no'=>'TEST-L']);
$insert('network_books',['library_id'=>4,'title'=>'Another edition display','isbn'=>'0-306-40615-2','format'=>'physical']);
$insert('member_visits',['library_id'=>4,'visitor_count'=>5,'visit_channel'=>'library_guestbook','purpose_label'=>'Layanan digital','visited_at'=>'2026-01-15 10:00:00']);
$insert('member_visits',['library_id'=>6,'visitor_count'=>1,'visit_channel'=>'member_dashboard','visited_at'=>'2026-02-15 10:00:00']);
$insert('member_visits',['library_id'=>null,'visitor_count'=>2,'visit_channel'=>'library_guestbook','visited_at'=>'2026-01-15 10:00:00']);
$insert('member_visits',['library_id'=>4,'visitor_count'=>9,'visit_channel'=>'library_guestbook','visited_at'=>'2025-01-15 10:00:00']);
$insert('loan_transaction_items',['book_item_id'=>$itemA,'member_id'=>$member,'loan_date'=>'2026-01-15 10:00:00','local_return_at'=>'2026-02-01 09:00:00','loan_status'=>'RETURN']);
$insert('loan_transaction_items',['book_item_id'=>$itemB,'member_id'=>$member,'loan_date'=>'2026-01-15 10:00:00','due_date'=>'2020-01-20 00:00:00','loan_status'=>'LOAN']);
$r=$m->report($f);$s=$r['summary'];$b=$before['summary'];
check_report($s['titles']===$b['titles']+5,'Local holdings counted once per owning library');
check_report($r['unique_records']===$before['unique_records']+4,'Shared legacy title does not multiply unique source records');
check_report($r['valid_isbns']===$before['valid_isbns']+1,'Equivalent ISBN10/13 grouped across datasets');
check_report($s['physical']===$b['physical']+3&&$s['digital']===$b['digital']+1&&$s['unclassified']===$b['unclassified']+1,'Physical/digital/unknown classification correct');
check_report($s['items']===$b['items']+2,'Digital item records excluded from physical copies');
check_report($s['members']===$b['members']+1,'Legacy and local member records not falsely deduplicated');
check_report($s['loans']===$b['loans']+2&&$s['returns']===$b['returns']+1,'Legacy loan details and local return dates counted');
check_report($s['offline']===$b['offline']+7&&$s['online']===$b['online']+1&&$s['entries']===$b['entries']+3,'Groups counted as people, not visit entries');
check_report($r['months']['2026-01']['offline']===7&&$r['months']['2026-02']['online']===1,'Monthly attribution and digital purpose independent of channel');
check_report($r['summary']['physical']+$r['summary']['digital']-$r['summary']['hybrid']+$r['summary']['unclassified']===$r['summary']['titles'],'Format totals reconcile');
foreach(['library','district','village','type']as$group){$g=$m->report(array_replace($f,['group'=>$group]));foreach($m->metrics()as$key=>$label)check_report(array_sum(array_column($g['groups'],$key))===$s[$key],'Grouping '.$group.' reconciles '.$key);}
$legacy=$m->report(array_replace($f,['source'=>'legacy']));$network=$m->report(array_replace($f,['source'=>'network']));foreach($m->metrics()as$key=>$label)check_report($legacy['summary'][$key]+$network['summary'][$key]===$s[$key],'Source totals reconcile '.$key);
$onlyA=$m->report(array_replace($f,['source'=>'legacy','library_id'=>4]));check_report($onlyA['summary']['titles']===2&&$onlyA['summary']['members']===0&&$onlyA['summary']['offline']===5,'Library filter includes only attributable data');
$unassigned=$m->report($m->filters(['year'=>'2026','source'=>'legacy','library_id'=>'unassigned']));check_report($unassigned['summary']['members']===1&&$unassigned['summary']['titles']===1&&$unassigned['summary']['offline']===2,'Unassigned bucket retains records without guessing ownership');
$jan=$m->report(array_replace($f,['source'=>'legacy','month'=>1]));check_report($jan['summary']['returns']===0&&$jan['summary']['loans']===2&&count($jan['months'])===1,'Month filter respects transaction dates');
check_report($jan['summary']['items']===$legacy['summary']['items'],'Inventory explicitly current snapshot, independent of period');
$empty=$m->report(array_replace($f,['q'=>'__no_such_library__']));check_report(!$empty['groups']&&array_sum($empty['summary'])===0,'Empty filters return zero without unassigned leakage');
$school=$db->where('id',4)->get('libraries')->row_array();$district=$m->report(array_replace($f,['district_id'=>(int)$school['district_id']]));check_report($district['summary']['members']<$s['members'],'Spatial filter does not infer legacy members from residence');
foreach([['q'=>['bad']],['year'=>'999'],['group'=>'sql'],['source'=>'other'],['library_id'=>'-1'],['month'=>'13']]as$bad){$rejected=false;try{$m->filters($bad);}catch(InvalidArgumentException$e){$rejected=true;}check_report($rejected,'Malformed report filter rejected');}
check_report($m->isbn_key('9780306406158')===null&&$m->isbn_key('123')===null,'Invalid check digit does not create a shared identity');
echo $checks," report checks passed.\n";
