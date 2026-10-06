<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__.'/test_visit_purposes.php';
$reportSource=file_get_contents(APPPATH.'controllers/Reports.php');
verify_purpose(strpos($reportSource,'decorate_visit_simulation_notice')===false,'Simulation banner injection removed from report routes');
verify_purpose(strpos($reportSource,'Memuat data simulasi')===false && strpos($reportSource,'UJI COBA:')===false,'Requested warning text removed');
$period=['date_from'=>'2026-09-01','date_to'=>'2026-09-30'];
foreach(['all'=>1223,'online'=>367,'offline'=>856]as$scope=>$expected){
    $actual=$model->visit_simulation_summary($period['date_from'],$period['date_to'],$scope);
    verify_purpose((int)$actual['entries']===$expected,'September simulation count: '.$scope);
}
foreach([2025=>18442,2026=>13189]as$year=>$expected){
    $p=['date_from'=>$year.'-01-01','date_to'=>$year===2026?'2026-09-30':'2025-12-31'];
    $actual=$model->visit_simulation_summary($p['date_from'],$p['date_to']);
    verify_purpose((int)$actual['entries']===$expected,'Imported yearly count: '.$year);
    foreach(['offline'=>$year===2025?18442:9233,'online'=>$year===2025?0:3956]as$scope=>$expectedScope){
        $scoped=$model->visit_simulation_summary($p['date_from'],$p['date_to'],$scope);
        verify_purpose((int)$scoped['entries']===$expectedScope,'Simulation classification: '.$year.' '.$scope);
    }
    foreach(['all','offline','online']as$scope){
        $report=$build->invoke($controller,$p,['row'=>'purpose','column'=>'none','split'=>'none','scope'=>$scope]);
        $summary=$model->visit_summary($p['date_from'],$p['date_to'],$scope);
        verify_purpose($report['grand_total']===$summary['people'],'Live purpose format matches summary: '.$year.' '.$scope);
    }
}
$batch='digital_u18_2025_2026_sep_v1';
$months=$db->query("SELECT DATE_FORMAT(visited_at,'%Y-%m') month,COUNT(*) total,SUM(visit_channel='digital_access') online,SUM(visit_channel='library_guestbook') offline FROM member_visit_simulations WHERE batch_id=? GROUP BY month ORDER BY month",[$batch])->result_array();
foreach($months as$row){
    $expectedOnline=substr($row['month'],0,4)==='2025'?0:(int)round((int)$row['total']*0.30);
    verify_purpose((int)$row['online']===$expectedOnline && (int)$row['offline']===(int)$row['total']-$expectedOnline,'Monthly channel split: '.$row['month']);
}
$matched=$db->query("SELECT COUNT(*) n FROM member_visit_simulations s STRAIGHT_JOIN member_visits v ON v.source_id=CONCAT(s.batch_id,':',s.id) AND v.source_system='simulation' WHERE s.batch_id=? AND v.visit_channel=s.visit_channel AND v.purpose_label='Layanan digital' AND v.member_id=s.member_id AND v.visited_at=s.visited_at AND v.visit_origin=CASE WHEN s.visit_channel='library_guestbook' THEN 'library' ELSE 'digital_external' END",[$batch])->row_array();
verify_purpose((int)$matched['n']===31631,'Live classifications match source without changing purpose/member/date');
echo $checks." total preview and purpose checks passed.\n";
