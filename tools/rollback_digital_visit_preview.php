<?php
// Dry run by default. Only removes the explicitly tagged preview batch.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('FCPATH',dirname(__DIR__).'/'); define('APPPATH',FCPATH.'application/');
define('BASEPATH',FCPATH.'system/'); define('ENVIRONMENT','production');
require BASEPATH.'core/Common.php'; require BASEPATH.'database/DB.php';
$db=DB('default'); $db->db_debug=false;
$batch='digital_u18_2025_2026_sep_v1';
$check=function($ok,$message){if(!$ok)throw new RuntimeException($message);};
$query=function($sql,$params=[])use($db,$check){$result=$db->query($sql,$params);$check($result!==false,'Database operation failed.');return $result;};
$join="FROM member_visit_simulations s STRAIGHT_JOIN member_visits v ON v.source_id=CONCAT(s.batch_id,':',s.id) AND v.source_system='simulation'
    WHERE v.source_system='simulation' AND s.batch_id=? AND JSON_UNQUOTE(JSON_EXTRACT(v.metadata_json,'$.batch_id'))=?";
try{
    $check($db->trans_begin(),'Could not start transaction.');
    $rows=$query('SELECT v.id,v.member_id,v.visited_at,v.purpose_label,v.visitor_count,s.member_id expected_member,s.visited_at expected_date '.$join.' FOR UPDATE',[$batch,$batch])->result_array();
    if(!$rows){$db->trans_rollback();echo "No matching preview rows. No changes.\n";exit(0);}
    $check(count($rows)===31631,'Unexpected batch size; review before removing anything.');
    foreach($rows as$row)$check($row['member_id']===$row['expected_member'] && $row['visited_at']===$row['expected_date'] && $row['purpose_label']==='Layanan digital' && (int)$row['visitor_count']===1,'Preview data was edited; manual review required.');
    $children=$query("SELECT COUNT(*) n FROM member_visit_simulations s STRAIGHT_JOIN member_visits v ON v.source_id=CONCAT(s.batch_id,':',s.id) AND v.source_system='simulation'
        JOIN member_visit_demographics d ON v.id=d.visit_id
        WHERE v.source_system='simulation' AND s.batch_id=?",[$batch])->row_array();
    $check((int)$children['n']===0,'Preview rows have demographic edits; manual review required.');
    if(!in_array('--apply',$argv,true)){$db->trans_rollback();echo 'DRY RUN: '.count($rows)." tagged preview rows can be removed; no changes.\n";exit(0);}
    $check(in_array('--confirm-batch='.$batch,$argv,true),'Explicit batch confirmation is required.');
    $query('DELETE v '.$join,[$batch,$batch]);
    $check($db->affected_rows()===31631 && $db->trans_status(),'Rollback count mismatch.');
    $check($db->trans_commit(),'Commit failed.');
    echo "Removed 31631 preview rows only. Source simulations and private backup remain available.\n";
}catch(Throwable$error){$db->trans_rollback();fwrite(STDERR,$error->getMessage()."\n");exit(1);}
