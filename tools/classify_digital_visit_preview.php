<?php
// Reclassify only the explicitly generated batch; default is read-only planning.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('FCPATH',dirname(__DIR__).'/'); define('APPPATH',FCPATH.'application/');
define('BASEPATH',FCPATH.'system/'); define('ENVIRONMENT','production');
require BASEPATH.'core/Common.php'; require BASEPATH.'database/DB.php';
date_default_timezone_set('Asia/Jakarta');
$db=DB('default'); $db->db_debug=false;
$batch='digital_u18_2025_2026_sep_v1';
$check=function($ok,$message){if(!$ok)throw new RuntimeException($message);};
$query=function($sql,$params=[])use($db,$check){$result=$db->query($sql,$params);$check($result!==false,'Database operation failed (code '.($db->error()['code']??0).', statement '.substr(hash('sha256',$sql),0,8).'); private data not printed.');return $result;};
$scalar=function($sql,$params=[])use($query){return (int)$query($sql,$params)->row_array()['n'];};
try{
    $source=$query("SELECT id,DATE_FORMAT(visited_at,'%Y-%m') month FROM member_visit_simulations WHERE batch_id=? ORDER BY id",[$batch])->result_array();
    $check(count($source)===31631,'Unexpected source batch count.');
    $months=[];foreach($source as$row)$months[$row['month']][]=(int)$row['id'];ksort($months);
    $check(count($months)===21,'Expected 21 months.');
    $plan=[];$summary=[];
    foreach($months as$month=>$ids){
        $check($month>='2025-01' && $month<='2026-09','Unexpected source period.');
        // Hash ordering spreads the channel assignment across members/dates,
        // deterministically; the 70/30 split is a simulation choice, not measured data.
        $rank=[];foreach($ids as$id)$rank[$id]=hash('sha256',$batch.':channel-v2:'.$id);
        usort($ids,function($a,$b)use($rank){return strcmp($rank[$a],$rank[$b]);});
        $online=substr($month,0,4)==='2025'?0:(int)round(count($ids)*0.30);
        foreach($ids as$i=>$id)$plan[]=['id'=>$id,'visit_channel'=>$i<$online?'digital_access':'library_guestbook'];
        $summary[]=['month'=>$month,'offline'=>count($ids)-$online,'online'=>$online,'total'=>count($ids)];
    }
    echo json_encode($summary,JSON_PRETTY_PRINT),"\n";
    if(!in_array('--apply',$argv,true)){echo "DRY RUN: no data changed. Use --apply --manifest=<fresh backup manifest>.\n";exit(0);}
    $manifestPath=null;foreach($argv as$arg)if(strpos($arg,'--manifest=')===0)$manifestPath=substr($arg,11);
    $check($manifestPath && is_file($manifestPath),'A fresh backup manifest is required.');
    $manifest=json_decode(file_get_contents($manifestPath),true);
    $check(is_array($manifest) && ($manifest['batch']??null)===$batch && ($manifest['database']??null)===$db->database
        && ($manifest['tables']??[])===['member_visits','member_visit_demographics','member_visit_simulations'],'Backup does not match this batch/database.');
    $age=time()-strtotime($manifest['created_at']);$check($age>=0 && $age<7200,'Backup is too old.');
    $check(is_file($manifest['backup']) && hash_equals($manifest['sha256'],hash_file('sha256',$manifest['backup'])),'Backup checksum mismatch.');
    $query('CREATE TEMPORARY TABLE preview_channel_plan (id BIGINT UNSIGNED PRIMARY KEY, visit_channel VARCHAR(50) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    foreach(array_chunk($plan,500)as$chunk)$check($db->insert_batch('preview_channel_plan',$chunk)!==false,'Could not prepare classification plan.');
    $check($db->trans_begin(),'Could not start transaction.');
    try{
        $rows=$query("SELECT v.id FROM member_visit_simulations s STRAIGHT_JOIN member_visits v ON v.source_id=CONCAT(s.batch_id,':',s.id) AND v.source_system='simulation'
            WHERE v.source_system='simulation' AND s.batch_id=? AND JSON_UNQUOTE(JSON_EXTRACT(v.metadata_json,'$.batch_id'))=?
            AND v.member_id=s.member_id AND v.visited_at=s.visited_at AND v.purpose_label='Layanan digital' AND v.visitor_count=1
            FOR UPDATE",[$batch,$batch])->result_array();
        $check(count($rows)===31631,'Imported batch differs from source; review required.');
        $fingerprint=function()use($query,$batch){return $query("SELECT COUNT(*) entries,COALESCE(SUM(v.visitor_count),0) people,
            BIT_XOR(CRC32(CONCAT_WS('|',v.id,v.member_id,v.visited_at,v.visit_channel,v.visit_origin,v.purpose_label,v.location_label,v.visitor_count,v.metadata_json,v.updated_at))) digest
            FROM member_visits v WHERE v.source_system IS NULL OR v.source_system<>'simulation' OR v.source_id IS NULL
            OR LEFT(v.source_id,LENGTH(?)+1)<>CONCAT(?,':')",[$batch,$batch])->row_array();};
        $untouchedBefore=$fingerprint();
        $query("UPDATE member_visit_simulations s JOIN preview_channel_plan p ON p.id=s.id
            SET s.visit_channel=p.visit_channel WHERE s.batch_id=? AND s.is_simulation=1",[$batch]);
        $query("UPDATE member_visit_simulations s STRAIGHT_JOIN member_visits v ON v.source_id=CONCAT(s.batch_id,':',s.id) AND v.source_system='simulation'
            JOIN preview_channel_plan p ON p.id=s.id
            SET v.visit_channel=p.visit_channel,
                v.visit_origin=CASE WHEN p.visit_channel='library_guestbook' THEN 'library' ELSE 'digital_external' END,
                v.location_label=CASE WHEN p.visit_channel='library_guestbook' THEN 'SIMULASI - Layanan digital di perpustakaan' ELSE 'SIMULASI - Layanan digital online' END,
                v.metadata_json=JSON_SET(v.metadata_json,'$.channel_allocation','2025_offline_2026_70_30')
            WHERE v.source_system='simulation' AND s.batch_id=? AND JSON_UNQUOTE(JSON_EXTRACT(v.metadata_json,'$.batch_id'))=?",[$batch,$batch]);
        $check($scalar("SELECT COUNT(*) n FROM member_visit_simulations s STRAIGHT_JOIN member_visits v ON v.source_id=CONCAT(s.batch_id,':',s.id) AND v.source_system='simulation'
            JOIN preview_channel_plan p ON p.id=s.id WHERE v.source_system='simulation' AND s.batch_id=?
            AND v.visit_channel=p.visit_channel AND s.visit_channel=p.visit_channel AND v.purpose_label='Layanan digital'
            AND s.purpose_label='Layanan digital' AND v.visitor_count=1 AND s.visitor_count=1
            AND v.member_id=s.member_id AND v.visited_at=s.visited_at
            AND v.visit_origin=CASE WHEN p.visit_channel='library_guestbook' THEN 'library' ELSE 'digital_external' END",[$batch])===31631,'Classification validation failed.');
        $actual=$query("SELECT DATE_FORMAT(v.visited_at,'%Y-%m') month,SUM(v.visit_channel='library_guestbook') offline,
            SUM(v.visit_channel='digital_access') online,COUNT(*) total FROM member_visit_simulations s
            STRAIGHT_JOIN member_visits v ON v.source_id=CONCAT(s.batch_id,':',s.id) AND v.source_system='simulation'
            WHERE v.source_system='simulation' AND s.batch_id=? GROUP BY month ORDER BY month",[$batch])->result_array();
        $check(count($actual)===count($summary),'Monthly row count changed.');
        foreach($actual as$i=>$row)foreach(['offline','online','total']as$key)$check((int)$row[$key]===$summary[$i][$key] && $row['month']===$summary[$i]['month'],'Monthly totals do not match plan.');
        $check($untouchedBefore===$fingerprint(),'Visits outside the batch changed during classification.');
        $check($db->trans_status() && $db->trans_commit(),'Transaction failed.');
    }catch(Throwable$error){$db->trans_rollback();throw $error;}
    echo "Updated source and live batch classifications. Monthly totals and all visits outside the batch are unchanged.\n";
}catch(Throwable$error){fwrite(STDERR,$error->getMessage()."\n");exit(1);}
