<?php
require __DIR__.'/library_network_bootstrap.php';
require __DIR__.'/iplm_task5_support.php';
require APPPATH.'libraries/Catalog_xlsx.php';
$dir = null;
foreach ($argv as $arg) if (strpos($arg, '--test=') === 0) $dir = substr($arg, 7);
if ($dir !== null) {
    network_check(preg_match('#^/tmp/pustaka_network_test_[a-zA-Z0-9_]+$#D', $dir) && is_file($dir.'/connection.php'), 'Isolated runtime required');
    $params = require $dir.'/connection.php';
    network_check(strpos($params['database'], 'pustaka_network_test_') === 0, 'Test database required');
    $db = DB($params);
} else $db = DB();
$db->db_debug = false;
$apply = in_array('--apply', $argv, true);
$report = $apply || in_array('--report', $argv, true);
$hash = hash_file('sha256', FCPATH.'docs/iplm/sekolah_kabupaten_rembang.xlsx');
$plan = task5_plan($db); $backup = null; $periodChange = null; $updatedDrafts = [];
if ($apply && $db->where('event','task5.2026.applied')->count_all_results('iplm_history') && $db->where(['source_system'=>'sekolah_task5','source_sha256'=>$hash])->count_all_results('library_source_records') === count($plan)) {
    echo "Already applied; administrator edits and original task5 reports preserved.\n";
    exit;
}
if ($apply) {
    if (!$dir) { $backup = network_backup($db, 'docs/iplm/task5.md'); echo 'Backup: '.$backup.'.sql.gz'.PHP_EOL; }
    $db->trans_begin();
    try {
        $before = array_column(network_query($db, 'SELECT * FROM libraries ORDER BY id FOR UPDATE')->result_array(), null, 'id');
        $plan = task5_plan($db);
        $protected = [];
        foreach (['auth_user','iplm_submissions','iplm_periods'] as $table) $protected[$table] = network_query($db, 'SELECT * FROM '.$table.' ORDER BY id')->result_array();
        $already = $db->where('event','task5.2026.applied')->count_all_results('iplm_history') > 0;
        foreach ($plan as &$row) {
            $s = $row['source'];
            $archive = $db->where(['source_system'=>'sekolah_task5','source_id'=>$s['NPSN']])->get('library_source_records')->row_array();
            if ($archive) {
                network_check($archive['source_sha256'] === $hash, 'Source changed after reconciliation; new review required');
                continue; // Never reapply stale source data over subsequent administrator edits.
            }
            if ($row['status'] === 'PERBARUI') {
                $values = array_map(function ($change) { return $change['after']; }, $row['changes']);
                network_check($db->where('id',$row['library_id'])->update('libraries',$values), 'Master update failed');
            }
            network_check($db->insert('library_source_records', [
                'source_system'=>'sekolah_task5', 'source_id'=>$s['NPSN'], 'library_id'=>$row['library_id'],
                'source_row'=>$s['_row'], 'classification'=>$s['Jenjang'].'/'.$s['Bentuk Pendidikan'],
                'decision'=>$row['status'], 'note'=>$row['note'],
                'source_json'=>json_encode(['source'=>$s,'changes'=>$row['changes'],'candidates'=>$row['candidates']],JSON_UNESCAPED_UNICODE),
                'source_sha256'=>$hash
            ]), 'Source/audit archive failed');
        } unset($row);
        // Only the explicitly revised 2026 period changes; population snapshot and other years remain intact.
        if (!$already) {
            $period = network_query($db,'SELECT * FROM iplm_periods WHERE year=2026 FOR UPDATE')->row_array();
            network_check($period, 'Create the 2026 period before task5');
            network_check(!$db->where('period_id',$period['id'])->where('deleted_at IS NULL',null,false)->where_in('status',['submitted','verified'])->count_all_results('iplm_submissions'), 'Submitted/verified forms need explicit review before changing the data year');
            $values = ['title'=>'IPLM 2026 — data tahun 2026','start_date'=>'2026-01-01','end_date'=>'2026-12-31','dates_confirmed'=>1,'version'=>(int)$period['version']+1];
            network_check($db->where('id',$period['id'])->update('iplm_periods',$values), 'Period correction failed');
            $note = 'Ralat task5: pengukuran IPLM 2026 menggunakan data tahun 2026 (1 Januari–31 Desember), bukan 2025. Angka dan tautan lama dipertahankan; periksa dan perbarui sesuai tahun 2026 sebelum mengirim. Tahun berjalan belum selesai.';
            foreach (network_query($db,'SELECT * FROM iplm_submissions WHERE period_id=? AND deleted_at IS NULL FOR UPDATE',[$period['id']])->result_array() as $submission) {
                network_check($db->where('id',$submission['id'])->update('iplm_submissions',['status'=>'revision','review_note'=>$note,'resolutions_json'=>'{}','verified_at'=>null,'version'=>(int)$submission['version']+1,'updated_at'=>date('Y-m-d H:i:s')]), 'Draft revision failed');
                network_check($db->insert('iplm_history',['submission_id'=>$submission['id'],'actor_id'=>0,'event'=>'task5.period.revision','payload_json'=>json_encode(['before'=>$submission,'note'=>$note],JSON_UNESCAPED_UNICODE)]), 'Draft history failed');
                $updatedDrafts[] = (int)$submission['id'];
            }
            $periodChange = ['before'=>$period,'after'=>array_replace($period,$values),'draft_ids'=>$updatedDrafts];
            network_check($db->insert('iplm_history',['actor_id'=>0,'event'=>'task5.2026.applied','payload_json'=>json_encode(['period'=>$periodChange,'source_sha256'=>$hash,'source'=>'docs/iplm/task5.md'],JSON_UNESCAPED_UNICODE)]), 'Task5 history failed');
        }
        // Fail closed if an unrelated master field, account or submitted value was changed.
        $allowed = ['code','institution_name','institution_status','library_type_id','library_subtype_id','district_id','district','name','updated_at'];
        $after = array_column(network_query($db,'SELECT * FROM libraries ORDER BY id')->result_array(), null, 'id');
        network_check(array_keys($before) === array_keys($after), 'Library IDs changed');
        foreach ($before as $id=>$library) foreach ($library as $key=>$value) if (!in_array($key,$allowed,true)) network_check($after[$id][$key] === $value, 'Protected master field changed');
        network_check($protected['auth_user'] === network_query($db,'SELECT * FROM auth_user ORDER BY id')->result_array(), 'Accounts changed');
        $newSubmissions = array_column(network_query($db,'SELECT * FROM iplm_submissions ORDER BY id')->result_array(), null, 'id');
        foreach ($protected['iplm_submissions'] as $old) foreach ($old as $key=>$value) if (!in_array($key,['status','review_note','resolutions_json','verified_at','version','updated_at'],true)) network_check($newSubmissions[$old['id']][$key] === $value, 'IPLM values/identity/schema/evidence changed');
        network_check($db->trans_status() && $db->trans_commit(), 'Task5 transaction failed');
    } catch (Throwable $e) { $db->trans_rollback(); throw $e; }
}
$summary = ['applied'=>$apply,'source_rows'=>count($plan),'source_sha256'=>$hash,'statuses'=>[],'changed_fields'=>[], 'period_change'=>$periodChange,'drafts_for_review'=>$updatedDrafts,'backup'=>$backup ? $backup.'.sql.gz' : null];
foreach ($plan as $row) {
    $summary['statuses'][$row['status']] = ($summary['statuses'][$row['status']] ?? 0)+1;
    if ($row['status']==='PERBARUI') foreach ($row['changes'] as $field=>$value) $summary['changed_fields'][$field] = ($summary['changed_fields'][$field] ?? 0)+1;
}
if ($report) {
    $folder = $dir ?: FCPATH.'docs/iplm';
    task5_reports($db, $plan, $folder, $apply);
    file_put_contents($folder.'/ringkasan-task5.json',json_encode($summary,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    chmod($folder.'/ringkasan-task5.json',0600);
    if ($backup) file_put_contents($backup.'.task5.json',json_encode($summary,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
}
echo json_encode($summary,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).PHP_EOL;
