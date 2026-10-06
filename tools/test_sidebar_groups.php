<?php
require __DIR__.'/library_network_bootstrap.php';
require APPPATH.'models/Menu_model.php';
require APPPATH.'helpers/sidebar_helper.php';
$directory=$argv[1]??'';
network_check(preg_match('#^/tmp/pustaka_network_test_[a-zA-Z0-9_]+$#D',$directory)&&is_file($directory.'/connection.php'),'Private test runtime required.');
$params=require $directory.'/connection.php';
network_check(strpos($params['database'],'pustaka_network_test_')===0,'Test database required.');
$db=DB($params);$model=new Menu_model();$model->db=$db;$checks=0;
function sidebar_ok($ok,$message){global $checks;network_check($ok,$message);$checks++;echo 'PASS '.$message,PHP_EOL;}
$local=$model->get_sidebar_tree(['library.workspace'=>['can_view'=>1],'iplm.local'=>['can_view'=>1]],false,'LIBRARY');
$central=$model->get_sidebar_tree([],true,'MAIN');
$flatten=function($tree)use(&$flatten){$rows=[];foreach($tree as $item){$rows[]=$item;$rows=array_merge($rows,$flatten($item['children']));}return $rows;};
$localRows=$flatten($local);$centralRows=$flatten($central);
sidebar_ok(count($local)===7,'School sidebar has seven top-level entries');
sidebar_ok(count(array_filter($localRows,function($r){return empty($r['children']);}))===20,'Nineteen school destinations plus IPLM retained');
sidebar_ok(count($central)===13,'District sidebar includes grouped IPLM');
sidebar_ok($model->get_sidebar_tree([],false,'LIBRARY')===[],'No empty groups exposed without school permission');
foreach([
 'library-workspace'=>'network.dashboard',
 'library-workspace/records/books'=>'network.books',
 'library-workspace/edit/books/17'=>'network.books',
 'library-workspace/import/items'=>'network.items',
 'library-workspace/archive/members/17'=>'network.members',
 'library-workspace/loans'=>'network.loans',
 'library-services/exchange/17'=>'network.exchange',
 'library-services/fines/17'=>'network.fines',
 'library-workspace/settings'=>'network.settings',
 'library-workspace/account'=>'network.account',
]as$path=>$key){$id=pustaka_sidebar_active_id($local,$path);$rows=array_column($localRows,null,'id');sidebar_ok(isset($rows[$id])&&$rows[$id]['menu_key']===$key,'School active leaf '.$path);}
foreach([
 'catalog'=>'catalog','catalog/edit/17'=>'catalog','catalog/loans'=>'catalog.loans',
 'catalog/requests'=>'catalog.requests','members/cards/design'=>'members.card_design',
 'members/registrations'=>'members.registrations','events/qr'=>'events-qr',
 'reading-points/tokens'=>'reading_tokens.index','wa/outbox'=>'wa.outbox',
 'library-workspace/records/books'=>'library-workspace','library-services/exchange/17'=>'library.exchange',
]as$path=>$key){$id=pustaka_sidebar_active_id($central,$path);$rows=array_column($centralRows,null,'id');sidebar_ok(isset($rows[$id])&&$rows[$id]['menu_key']===$key,'District most-specific active leaf '.$path);}
$limited=$model->get_sidebar_tree(['catalog.loans'=>['can_view'=>1]],false,'MAIN');
sidebar_ok(count($limited)===1&&$limited[0]['menu_key']==='transactions'&&count($limited[0]['children'])===1,'Limited role sees only its circulation group and permitted leaf');
sidebar_ok(pustaka_sidebar_active_id($local,'library-workspace-unrelated')===null,'Route matching respects slash boundaries');
echo $checks," sidebar checks passed.\n";
