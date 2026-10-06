<?php
require __DIR__.'/library_network_bootstrap.php';
$directory=null;foreach($argv as$arg)if(strpos($arg,'--test=')===0)$directory=substr($arg,7);
if($directory!==null){network_check(preg_match('#^/tmp/pustaka_network_test_[a-zA-Z0-9_]+$#D',$directory)&&is_file($directory.'/connection.php'),'Private test runtime required.');$params=require $directory.'/connection.php';network_check(strpos($params['database'],'pustaka_network_test_')===0,'Test database required.');$db=DB($params);}else$db=DB('default');
$db->db_debug=false;
if(!in_array('--apply',$argv,true)){echo "DRY RUN: adds reports.network permission for central ADMIN/SUPERADMIN, report menu, and 11 LIBRARY-area sidebar entries. No accounts or operational rows changed.\n";exit;}
if($directory===null){$backup=network_backup($db,'sql/2026-10-04b_network_reports_sidebar.sql');echo 'Verified backup: '.$backup.'.sql.gz',"\n";}
$sql=file_get_contents(FCPATH.'sql/2026-10-04b_network_reports_sidebar.sql');$db->trans_begin();
try{foreach(preg_split('/;\s*(?:\r?\n|$)/',$sql)as$statement)if(trim($statement)!=='')network_query($db,$statement);network_check($db->trans_status()&&$db->trans_commit(),'Menu migration commit failed.');}catch(Throwable$e){$db->trans_rollback();throw $e;}
$count=(int)$db->where('menu_area','LIBRARY')->like('menu_key','network.','after')->count_all_results('sys_menu');network_check($count===11,'Expected 11 local sidebar menus.');
echo "Report registry, central permissions, and 11 local sidebar entries verified.\n";
