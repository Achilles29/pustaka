<?php
// CLI-only regression checks. Contact changes are rolled back; catalog reads are read-only.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('FCPATH', dirname(__DIR__).'/');
define('APPPATH', FCPATH.'application/');
define('BASEPATH', FCPATH.'system/');
define('ENVIRONMENT', 'production');
require BASEPATH.'core/Common.php';
require BASEPATH.'core/Model.php';
require BASEPATH.'database/DB.php';
require APPPATH.'models/Catalog_model.php';
require APPPATH.'models/Patron_feedback_model.php';
date_default_timezone_set('Asia/Jakarta');
$db = DB('default');
$catalog = new Catalog_model(); $catalog->db = $db;
$feedback = new Patron_feedback_model(); $feedback->db = $db;
$checks = 0;
function check($condition, $message) {
    global $checks;
    if (!$condition) throw new RuntimeException('FAIL: '.$message);
    $checks++; echo "PASS: $message\n";
}
$start = microtime(true);
$total = $catalog->count_books([], null);
$last = 0; $exported = 0;
do {
    $batch = $catalog->export_batch([], null, $last, 500);
    foreach ($batch as $row) {
        if ((int)$row['id'] <= $last) throw new RuntimeException('Duplicate export row');
        $last = (int)$row['id']; $exported++;
        if (!array_key_exists('publish_year',$row)) throw new RuntimeException('Missing publication year');
    }
} while ($batch);
check($total === $exported, 'Full catalog export matches count without duplicate rows ('.$exported.' titles)');
$report = $catalog->annual_development([], null);
check($total === $report['total_titles'], 'Annual report reconciles with catalog count');
check($report['total_copies'] === (int)$db->query('SELECT COUNT(*) n FROM book_items i JOIN books b ON b.id=i.book_id WHERE i.deleted_at IS NULL AND b.deleted_at IS NULL')->row()->n, 'Report copies reconcile with active items');
check(count($report['rows']) > 1, 'Original INLIS dates preserve historical years');
$scope = max(1, (int)($db->query('SELECT library_id FROM book_items WHERE deleted_at IS NULL AND library_id IS NOT NULL LIMIT 1')->row()->library_id ?? 0));
foreach ([['source_system'=>'manual'],['publish_year'=>'2020'],['q'=>'__no_such_catalog_20260929__'],['availability'=>'available']] as $filters) {
	$filtered = $catalog->annual_development($filters,null);
	check($filtered['total_titles'] === $catalog->count_books($filters,null), 'Report matches unscoped filter: '.json_encode($filters));
	$cursor = 0; $count = 0;
	do {
		$filtered_batch = $catalog->export_batch($filters,null,$cursor,500);
		foreach ($filtered_batch as $row) { $cursor = (int)$row['id']; $count++; }
	} while ($filtered_batch);
	check($count === $filtered['total_titles'], 'Filtered export matches report: '.json_encode($filters));
    $scoped = $catalog->annual_development($filters,$scope);
    check($scoped['total_titles'] === $catalog->count_books($filters,$scope), 'Report matches filter and library scope: '.json_encode($filters));
}
$scoped_export = $catalog->export_batch([], $scope);
foreach ($scoped_export as $row) {
    $n = (int)$db->where('book_id',$row['id'])->where('library_id',$scope)->where('deleted_at',null)->count_all_results('book_items');
    if ($n !== (int)$row['item_count']) throw new RuntimeException('Library scope leaked');
}
check(true, 'Export item counts respect library scope');
$normalized = $feedback->validate_contacts(['whatsapp'=>'+62 851-6580-5518','instagram'=>'@dinarpusrembang','tiktok'=>'perpustakaan.umum.rbg']);
check($normalized['whatsapp']==='6285165805518' && $normalized['instagram']==='dinarpusrembang', 'Contact normalization');
foreach ([['whatsapp'=>'javascript:alert(1)'],['instagram'=>'https://evil.example'],['tiktok'=>['x']],['tiktok'=>str_repeat('a',25)]] as $invalid) {
    $rejected = false;
    try { $feedback->validate_contacts($invalid); } catch (RuntimeException $e) { $rejected = true; }
    check($rejected, 'Reject malformed contact input');
}
$before = $feedback->contacts();
$db->trans_begin();
try {
    $feedback->save_contacts($normalized);
    check($feedback->contacts()===$normalized,'Contact settings round trip');
    check($feedback->contact_links()[0]['url']==='https://wa.me/6285165805518','WhatsApp URL normalization');
    $feedback->save_contacts(['whatsapp'=>'','instagram'=>'','tiktok'=>'']);
    check($feedback->contact_links()===[],'Blank contacts hide public links');
} finally { $db->trans_rollback(); }
check($feedback->contacts()===$before,'Test changes rolled back');
// Exercise the actual CSV encoder without starting an authenticated web session.
class MY_Controller {
    public $permissions = [];
    protected function require_permission($page, $action) {
        if (!in_array($action,$this->permissions,true)) throw new RuntimeException('DENIED '.$action);
    }
}
require APPPATH.'controllers/Catalog.php';
$controller = (new ReflectionClass('Catalog'))->newInstanceWithoutConstructor();
$controller->permissions = ['view']; $denied = false;
try { $controller->export(); } catch (RuntimeException $e) { $denied = $e->getMessage()==='DENIED export'; }
check($denied,'View-only users cannot export catalogs');
require APPPATH.'controllers/Patron_feedback.php';
$contacts_controller = (new ReflectionClass('Patron_feedback'))->newInstanceWithoutConstructor();
$contacts_controller->permissions = ['view']; $denied = false;
try { $contacts_controller->contacts(); } catch (RuntimeException $e) { $denied = $e->getMessage()==='DENIED edit'; }
check($denied,'View-only users cannot change public contacts');
$encoder = new ReflectionMethod('Catalog','csv_row'); $encoder->setAccessible(true);
$stream = fopen('php://temp','w+');
$encoder->invoke($controller,$stream,['=HYPERLINK("x")','  +SUM(A1)','@x',"\t-1",'Judul, "buku"',"Dua\nbaris",'2020']);
rewind($stream); $decoded = fgetcsv($stream,0,',','"',''); fclose($stream);
check($decoded[0][0]==="'" && $decoded[1][0]==="'" && $decoded[2][0]==="'" && $decoded[3][0]==="'",'CSV formula injection prevented');
check($decoded[4]==='Judul, "buku"' && $decoded[5]==="Dua\nbaris" && $decoded[6]==='2020','CSV escaping and year round trip');
echo $checks.' checks passed in '.round(microtime(true)-$start,2)."s\n";
