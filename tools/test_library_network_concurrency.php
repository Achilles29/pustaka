<?php
require __DIR__.'/library_network_bootstrap.php';require APPPATH.'models/Library_network_model.php';
$directory=$argv[1]??'';network_check(preg_match('#^/tmp/pustaka_network_test_[a-zA-Z0-9_]+$#D',$directory)&&is_file($directory.'/connection.php'),'Private isolated test directory required.');
$params=require $directory.'/connection.php';network_check(strpos($params['database'],'pustaka_network_test_')===0,'Isolated database required.');$db=DB($params);$db->db_debug=false;$n=new Library_network_model();$n->db=$db;
if(($argv[2]??'')==='--issue'){
    $start=(float)$argv[5];while(microtime(true)<$start)usleep(1000);
    try{$n->issue(4,$argv[3],$argv[4],9000000);echo 'issued';}catch(Throwable$e){echo 'rejected';}exit;
}
$tag=bin2hex(random_bytes(4));$book=$n->save(4,'books',0,['title'=>'Concurrency '.$tag,'format'=>'physical','status'=>'draft'],9000000);
$n->save_settings(4,['loan_days'=>7,'max_loans'=>1,'max_renewals'=>1],9000000);
for($round=0;$round<6;$round++){
    $sameItem=$round%2===1;$members=['C'.$tag.$round.'A','C'.$tag.$round.'B'];$barcodes=['C'.$tag.$round.'A','C'.$tag.$round.'B'];
    foreach($members as$member)$n->save(4,'members',0,['full_name'=>'Concurrency fixture','member_no'=>$member,'status'=>'active'],9000000);
    foreach($barcodes as$barcode)$n->save(4,'items',0,['book_id'=>$book,'barcode'=>$barcode,'status'=>'available'],9000000);
    $jobs=[];$start=microtime(true)+0.4;
    for($i=0;$i<2;$i++){$member=$members[$sameItem?$i:0];$barcode=$barcodes[$sameItem?0:$i];$p=proc_open([PHP_BINARY,__FILE__,$directory,'--issue',$member,$barcode,(string)$start],[0=>['file','/dev/null','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);$jobs[]=[$p,$pipes];}
    $outputs=[];foreach($jobs as[$process,$pipes]){$outputs[]=stream_get_contents($pipes[1]);$error=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);network_check(proc_close($process)===0&&$error==='','Worker failed.');}
    sort($outputs);network_check($outputs===['issued','rejected'],'Concurrent operation failed isolation.');
    echo 'PASS concurrent '.($sameItem?'same item, different members':'same member, different items (limit=1)').' round '.$round,"\n";
}
echo "6 concurrent transaction checks passed.\n";
