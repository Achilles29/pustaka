<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('FCPATH',dirname(__DIR__).'/'); define('APPPATH',FCPATH.'application/'); define('BASEPATH',FCPATH.'system/'); define('ENVIRONMENT','production');
require BASEPATH.'core/Common.php'; require BASEPATH.'core/Model.php'; require BASEPATH.'database/DB.php';
require APPPATH.'models/Report_model.php';
date_default_timezone_set('Asia/Jakarta');
$db=DB('default'); $model=new Report_model(); $model->db=$db; $checks=0;
function verify_purpose($condition,$label){global $checks;if(!$condition)throw new RuntimeException('FAIL: '.$label);$checks++;echo 'PASS: '.$label."\n";}
foreach(['all','offline','online']as$scope){
    $rows=$model->visit_purpose_breakdown('2026-01-01','2026-12-31',$scope);
    $summary=$model->visit_summary('2026-01-01','2026-12-31',$scope);
    verify_purpose(array_sum(array_column($rows,'people'))===$summary['people'],'Live people total reconciles: '.$scope);
    verify_purpose(array_sum(array_column($rows,'entries'))===$summary['entries'],'Live entries total reconciles: '.$scope);
    verify_purpose(!array_intersect(['Akses layanan digital','Baca buku digital'],array_column($rows,'label')),'Live digital aliases merged: '.$scope);
}
class MY_Controller {}
require APPPATH.'controllers/Reports.php';
$controller=(new ReflectionClass('Reports'))->newInstanceWithoutConstructor(); $controller->db=$db; $controller->Report_model=$model;
$build=new ReflectionMethod('Reports','build_custom_visit_report');$build->setAccessible(true);
$excel_text=new ReflectionMethod('Reports','visit_excel_text');$excel_text->setAccessible(true);
verify_purpose(strpos(html_entity_decode($excel_text->invoke($controller,'=1+1'),ENT_QUOTES,'UTF-8'),"'")===0,'Spreadsheet formula-looking purpose is escaped');
verify_purpose($excel_text->invoke($controller,'<script>')==='&lt;script&gt;','Purpose HTML is escaped');
$temporary=[];
try{
    foreach(['member_visits','member_visit_demographics']as$table){
        $definition=$db->query('SHOW CREATE TABLE '.$table)->row_array()['Create Table'];
        $definition=preg_replace('/^\s*CONSTRAINT[^\n]*\n/m','',$definition);
        $definition=preg_replace('/,\n\)/',"\n)",$definition);
        $definition=preg_replace('/^CREATE TABLE /','CREATE TEMPORARY TABLE ',$definition);
        if(!$db->query($definition))throw new RuntimeException($db->error()['message']);$temporary[]=$table;
    }
    $fixtures=[
        ['Membaca di tempat',1,'library_guestbook','2026-09-30 00:00:00',null],
        [' Membaca di tempat ',7,'library_guestbook','2026-09-30 23:59:59',null],
        ['Layanan digital',3,'library_guestbook','2026-09-30 12:00:00',null],
        ['   ',2,'library_guestbook','2026-09-30 12:00:00',null],
        [null,1,'inlislite_guestbook','2026-09-30 12:00:00','1'],
        [null,1,'inlislite_guestbook','2026-09-30 12:00:00','__missing_reference__'],
        ['<script>contoh</script>',1,'library_guestbook','2026-09-30 12:00:00',null],
        ['Membaca di tempat',99,'library_guestbook','2026-10-01 00:00:00',null],
        ['Membaca di tempat',100,'library_guestbook','2026-09-29 23:59:59',null],
        [' Akses layanan digital ',2,'member_dashboard','2026-09-30 12:00:00',null],
        ['Baca buku digital',1,'digital_access','2026-09-30 12:00:00',null],
    ];
    foreach($fixtures as$i=>$r){
        if(!$db->insert('member_visits',['id'=>$i+1,'source_system'=>'test','source_id'=>'purpose-fixture-'.($i+1),'purpose_label'=>$r[0],'visitor_count'=>$r[1],'visit_channel'=>$r[2],'visited_at'=>$r[3],'purpose_id'=>$r[4]]))throw new RuntimeException($db->error()['message']);
    }
    foreach([['male',4],['female',3]]as$d)if(!$db->insert('member_visit_demographics',['visit_id'=>2,'gender'=>$d[0],'people_count'=>$d[1]]))throw new RuntimeException($db->error()['message']);
    $rows=$model->visit_purpose_breakdown('2026-09-30','2026-09-30','all');$by_label=array_column($rows,null,'label');
    verify_purpose(array_sum(array_column($rows,'people'))===19 && array_sum(array_column($rows,'entries'))===9,'Date boundaries and group counts');
    verify_purpose((int)$by_label['Layanan digital']['people']===6 && (int)$by_label['Layanan digital']['entries']===3,'Digital guestbook and automatic activities merged with correct totals');
    verify_purpose(!isset($by_label['Akses layanan digital']) && !isset($by_label['Baca buku digital']),'No separate digital alias categories');
    verify_purpose($db->select('purpose_label')->where('id',10)->get('member_visits')->row_array()['purpose_label']===' Akses layanan digital ','Stored purpose remains unchanged');
    verify_purpose((int)$by_label['Membaca di tempat']['people']===8 && (int)$by_label['Membaca di tempat']['entries']===2,'Trimmed purposes grouped together');
    verify_purpose((int)$by_label['Belum diisi']['people']===2,'Blank purpose retained in totals');
    $legacy=$db->where('source_table','tujuan_kunjungan')->where('source_id','1')->get('inlislite_master_references')->row_array();
    verify_purpose(isset($by_label[trim($legacy['name'])]),'Legacy purpose name resolved');
    verify_purpose(isset($by_label['Tujuan lama (ID __missing_reference__)']),'Unmapped legacy ID retained');
    verify_purpose($rows[0]['label']==='Membaca di tempat','Largest purpose first');
    foreach(['offline'=>16,'online'=>3]as$scope=>$expected){
        $scopeRows=$model->visit_purpose_breakdown('2026-09-30','2026-09-30',$scope);
        verify_purpose(array_sum(array_column($scopeRows,'people'))===$expected,'Scope filter: '.$scope);
        $scopeLabels=array_column($scopeRows,null,'label');
        verify_purpose((int)$scopeLabels['Layanan digital']['people']===3,'Digital purpose respects channel scope: '.$scope);
    }
    verify_purpose($model->visit_purpose_breakdown('2099-01-01','2099-01-01','all')===[],'Empty period');
    $period=['date_from'=>'2026-09-30','date_to'=>'2026-09-30'];
    foreach([['purpose','none'],['date','purpose']]as$dimensions){
        $r=$build->invoke($controller,$period,['row'=>$dimensions[0],'column'=>$dimensions[1],'split'=>'none','scope'=>'all']);
        verify_purpose($r['grand_total']===19,'Purpose pivot '.$dimensions[0].' × '.$dimensions[1].' includes demographic group counts once');
        $labels=$dimensions[0]==='purpose'?$r['rows']:$r['columns'];
        verify_purpose(in_array('Layanan digital',$labels,true) && !array_intersect(['Akses layanan digital','Baca buku digital'],$labels),'Digital purpose normalized in pivot '.$dimensions[0].' × '.$dimensions[1]);
        $digitalTotal=$dimensions[0]==='purpose'?$r['matrix']['Layanan digital']['Jumlah']['total']:$r['column_totals']['Layanan digital']['total'];
        verify_purpose($digitalTotal===6,'Merged digital pivot count '.$dimensions[0].' × '.$dimensions[1]);
    }
}finally{foreach(array_reverse($temporary)as$table)$db->query('DROP TEMPORARY TABLE '.$table);}
echo $checks." checks passed; no production visit data modified.\n";
