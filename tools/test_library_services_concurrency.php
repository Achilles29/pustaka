<?php
require __DIR__.'/library_network_bootstrap.php';require APPPATH.'models/Library_services_model.php';
$dir=$argv[1]??'';network_check(preg_match('#^/tmp/pustaka_network_test_[a-zA-Z0-9_]+$#D',$dir)&&is_file($dir.'/connection.php'),'Private test directory required.');$params=require $dir.'/connection.php';network_check(strpos($params['database'],'pustaka_network_test_')===0,'Isolated database required.');$db=DB($params);$db->db_debug=false;$s=new Library_services_model();$s->db=$db;
if(($argv[2]??'')==='--create'){
    while(microtime(true)<(float)$argv[3])usleep(1000);
    try{$s->stock_create(6,'network','Concurrent snapshot',9000002);echo 'created';}catch(Throwable $e){echo 'rejected';}exit;
}
$jobs=[];$start=microtime(true)+.4;
for($i=0;$i<2;$i++){$process=proc_open([PHP_BINARY,__FILE__,$dir,'--create',(string)$start],[0=>['file','/dev/null','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);$jobs[]=[$process,$pipes];}
$outputs=[];foreach($jobs as [$process,$pipes]){$outputs[]=stream_get_contents($pipes[1]);$error=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);network_check(proc_close($process)===0&&$error==='','Worker failed.');}
sort($outputs);network_check($outputs===['created','rejected'],'Concurrent stock creation not isolated.');
$active=$db->where('library_id',6)->where('source','network')->where('status','open')->get('library_stock_sessions')->result_array();network_check(count($active)===1,'More than one active snapshot.');$s->stock_close(6,'network',$active[0]['id'],'cancelled','Concurrent test complete',9000002);echo "PASS concurrent stock creation: exactly one complete snapshot, second rejected.\n";
