<?php
require __DIR__.'/library_network_bootstrap.php';
$directory=null;
foreach($argv as $arg)if(strpos($arg,'--test=')===0)$directory=substr($arg,7);
if($directory!==null){
    network_check(preg_match('#^/tmp/pustaka_network_test_[a-zA-Z0-9_]+$#D',$directory)&&is_file($directory.'/connection.php'),'Private test runtime required.');
    $params=require $directory.'/connection.php';
    network_check(strpos($params['database'],'pustaka_network_test_')===0,'Test database required.');
    $db=DB($params);
}else $db=DB('default');
$db->db_debug=false;
if(!in_array('--apply',$argv,true)){echo "DRY RUN: group existing school and district sidebar entries. No permissions or operational data changed.\n";exit;}
$migration='sql/2026-10-05c_sidebar_groups.sql';
$before=network_query($db,'SELECT * FROM sys_menu ORDER BY id')->result_array();
$permissions=network_query($db,'SELECT * FROM auth_role_permission ORDER BY role_id,page_id')->result_array();
if($directory===null){
    $backup=network_backup($db,$migration);
    file_put_contents($backup.'.sidebar-before.json',json_encode($before,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    echo 'Backup: '.$backup.'.sql.gz',PHP_EOL;
}
$db->trans_begin();
try{
    foreach(preg_split('/;\s*(?:\r?\n|$)/',file_get_contents(FCPATH.$migration))as$sql)if(trim($sql)!=='')network_query($db,$sql);
    $after=network_query($db,'SELECT * FROM sys_menu ORDER BY id')->result_array();
    $indexed=array_column($after,null,'id');
    foreach($before as $row){
        network_check(isset($indexed[$row['id']]),'Existing menu removed.');
        foreach(['page_id','menu_area','menu_key','url','is_visible','is_active']as$field)
            network_check($indexed[$row['id']][$field]===$row[$field],'Existing route/permission/visibility changed.');
    }
    network_check($permissions===network_query($db,'SELECT * FROM auth_role_permission ORDER BY role_id,page_id')->result_array(),'Permissions changed.');
    network_check($db->trans_status(),'Sidebar transaction failed.');
    $db->trans_commit();
}catch(Throwable $e){$db->trans_rollback();throw $e;}
if($directory===null)file_put_contents($backup.'.sidebar-verification.json',json_encode(['verified_at'=>date(DATE_ATOM),'existing_menus_preserved'=>count($before),'routes_permissions_visibility_unchanged'=>true,'after'=>$after],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
echo "Sidebar grouped; existing menu IDs, routes, visibility and permissions preserved.\n";
