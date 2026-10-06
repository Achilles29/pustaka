<?php
// Explicit preview import into live visits, with mandatory private backup.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('FCPATH', dirname(__DIR__).'/');
define('APPPATH', FCPATH.'application/');
define('BASEPATH', FCPATH.'system/');
define('ENVIRONMENT', 'production');
require BASEPATH.'core/Common.php';
require BASEPATH.'database/DB.php';
date_default_timezone_set('Asia/Jakarta');
umask(0077);
$db = DB('default');
$db->db_debug = false;
$batch = 'digital_u18_2025_2026_sep_v1';
$expected = 31631;
$tables = ['member_visits', 'member_visit_demographics', 'member_visit_simulations'];
$check = function ($ok, $message) { if (!$ok) throw new RuntimeException($message); };
$query = function ($sql, $params = []) use ($db, $check) {
    $result = $db->query($sql, $params);
    $check($result !== false, 'Database operation failed; no private data printed.');
    return $result;
};
$scalar = function ($sql, $params = []) use ($query) { return (int)$query($sql, $params)->row_array()['n']; };
$monthly = function () use ($query) {
    return $query("SELECT DATE_FORMAT(visited_at,'%Y-%m') month, COUNT(*) entries, SUM(visitor_count) people
        FROM member_visits WHERE visited_at >= '2025-01-01' AND visited_at < '2026-10-01'
        GROUP BY month ORDER BY month")->result_array();
};
$run = function (array $args, $outputPath) use ($check) {
    $process = proc_open($args, [0=>['file','/dev/null','r'], 1=>['file',$outputPath,'w'], 2=>['pipe','w']], $pipes);
    $check(is_resource($process), 'Could not start backup process.');
    $error = stream_get_contents($pipes[2]); fclose($pipes[2]);
    $check(proc_close($process) === 0, 'Backup process failed; import stopped.');
};

try {
    $count = $scalar('SELECT COUNT(*) n FROM member_visit_simulations WHERE batch_id=?', [$batch]);
    $check($count === $expected, 'Source batch count changed; review required.');
    $check($scalar("SELECT COUNT(*) n FROM member_visit_simulations s LEFT JOIN members m ON m.id=s.member_id
        WHERE s.batch_id=? AND (m.id IS NULL OR m.deleted_at IS NOT NULL OR m.birth_date IS NULL
        OR m.registered_at IS NULL OR m.registered_at>s.visited_at OR m.birth_date>DATE(s.visited_at)
        OR TIMESTAMPDIFF(YEAR,m.birth_date,s.visited_at) NOT BETWEEN 0 AND 17
        OR s.visited_at<'2025-01-01' OR s.visited_at>='2026-10-01' OR s.is_simulation<>1
        OR s.purpose_label<>'Layanan digital' OR s.visit_channel NOT IN ('digital_access','library_guestbook') OR s.visitor_count<>1)", [$batch]) === 0, 'Source eligibility validation failed.');
    $sourceMonths = $query("SELECT DATE_FORMAT(visited_at,'%Y-%m') month,COUNT(*) n FROM member_visit_simulations WHERE batch_id=? GROUP BY month ORDER BY month", [$batch])->result_array();
    $check(count($sourceMonths) === 21, 'Source must cover 21 months.');
    foreach ($sourceMonths as $month) $check((int)$month['n'] >= 1000 && (int)$month['n'] <= 2000, 'Monthly source count outside range.');
    $existing = $scalar("SELECT COUNT(*) n FROM member_visits v JOIN member_visit_simulations s ON v.source_id=CONCAT(s.batch_id,':',s.id)
        WHERE v.source_system='simulation' AND s.batch_id=?", [$batch]);
    if (in_array('--status', $argv, true)) {
        echo json_encode(['batch'=>$batch,'source_count'=>$count,'imported_count'=>$existing,'report_months'=>$monthly()], JSON_PRETTY_PRINT), "\n";
        exit(0);
    }
    if (in_array('--backup', $argv, true)) {
        $directory = '/www/backup/pustaka-visits';
        if (!is_dir($directory)) $check(mkdir($directory,0700,true), 'Could not create private backup directory.');
        $check(realpath($directory) === $directory && !is_link($directory), 'Unexpected backup location.');
        chmod($directory,0700);
        $base = $directory.'/before-simulation-'.date('Ymd-His').'-'.bin2hex(random_bytes(4));
        $defaults = tempnam($directory, 'client-');
        $check($defaults !== false, 'Could not create private client configuration.');
        $ini = function ($value) { return '"'.str_replace(['\\','"',"\n","\r"],['\\\\','\\"','\\n','\\r'],(string)$value).'"'; };
        try {
            $config = "[client]\nhost=".$ini($db->hostname==='localhost'?'127.0.0.1':$db->hostname)."\nport=".(int)($db->port?:3306)."\nuser=".$ini($db->username)."\npassword=".$ini($db->password)."\ndefault-character-set=utf8mb4\n";
            $check(file_put_contents($defaults,$config,LOCK_EX) !== false, 'Could not write private client configuration.');
            $stats=[]; foreach ($tables as $table) $stats[$table]=$scalar('SELECT COUNT(*) n FROM '.$table);
            $run(array_merge(['mariadb-dump','--defaults-extra-file='.$defaults,'--single-transaction','--quick','--hex-blob','--skip-lock-tables',$db->database],$tables), $base.'.sql');
        } finally { if (is_file($defaults)) unlink($defaults); }
        $check(is_file($base.'.sql') && filesize($base.'.sql') > 100, 'Empty backup.');
        $run(['gzip','-c',$base.'.sql'], $base.'.sql.gz');
        $run(['gzip','-t',$base.'.sql.gz'], '/dev/null');
        $manifest=['batch'=>$batch,'created_at'=>date(DATE_ATOM),'database'=>$db->database,'tables'=>$tables,
            'row_counts_before_dump'=>$stats,'report_months_before_import'=>$monthly(),
            'backup'=>$base.'.sql.gz','sha256'=>hash_file('sha256',$base.'.sql.gz')];
        $check(file_put_contents($base.'.json',json_encode($manifest,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),LOCK_EX)!==false,'Could not write backup manifest.');
        // Keep both the SQL and its verified compressed copy for recovery.
        echo 'Backup verified: '.$base.'.sql.gz',"\nManifest: ",$base,'.json',"\n";
        exit(0);
    }
    $check($existing === 0, 'This batch already has imported rows; stopped without changes.');
    $manifestPath = null;
    foreach ($argv as $arg) if (strpos($arg,'--manifest=')===0) $manifestPath=substr($arg,11);
    if (!in_array('--apply',$argv,true)) {
        echo "Validated {$count} source rows. No changes. Run --backup, then --apply --manifest=/absolute/path.json.\n";
        exit(0);
    }
    $check($manifestPath && is_file($manifestPath), 'A verified backup manifest is required.');
    $manifest = json_decode(file_get_contents($manifestPath),true);
    $check(is_array($manifest) && $manifest['batch']===$batch && $manifest['database']===$db->database
        && $manifest['tables']===$tables, 'Backup does not match this import.');
    $check(time()-strtotime($manifest['created_at']) >= 0 && time()-strtotime($manifest['created_at']) < 7200, 'Backup is too old; take another backup.');
    $check(is_file($manifest['backup']) && hash_equals($manifest['sha256'],hash_file('sha256',$manifest['backup'])), 'Backup checksum mismatch.');
    $run(['gzip','-t',$manifest['backup']], '/dev/null');
    $check($db->trans_begin(), 'Could not start import transaction.');
    try {
        $before = array_column($monthly(),null,'month');
        $query("INSERT INTO member_visits
            (source_system,source_id,member_id,visit_channel,visit_origin,visitor_count,checkin_method,
             location_label,purpose_label,information,description,visit_status_label,metadata_json,visited_at)
            SELECT 'simulation',CONCAT(s.batch_id,':',s.id),s.member_id,s.visit_channel,
                CASE WHEN s.visit_channel='library_guestbook' THEN 'library' ELSE 'digital_external' END,1,'guest_form',
                CASE WHEN s.visit_channel='library_guestbook' THEN 'SIMULASI - Layanan digital di perpustakaan' ELSE 'SIMULASI - Layanan digital online' END,'Layanan digital',
                'SIMULASI UJI LAPORAN - bukan kunjungan aktual; jangan dipakai sebagai laporan resmi.',
                'Impor simulasi atas permintaan admin untuk pratinjau laporan.','Simulasi',
                JSON_OBJECT('is_simulation',true,'batch_id',s.batch_id,'simulation_id',s.id,'preview_only',true),s.visited_at
            FROM member_visit_simulations s WHERE s.batch_id=? ORDER BY s.visited_at,s.id", [$batch]);
        $check($db->affected_rows()===$expected, 'Inserted count mismatch.');
        $check($scalar("SELECT COUNT(*) n FROM member_visits v JOIN member_visit_simulations s ON v.source_id=CONCAT(s.batch_id,':',s.id)
            WHERE v.source_system='simulation' AND s.batch_id=? AND v.member_id=s.member_id AND v.visited_at=s.visited_at
            AND v.purpose_label=s.purpose_label AND v.visit_channel=s.visit_channel AND v.visitor_count=1
            AND JSON_UNQUOTE(JSON_EXTRACT(v.metadata_json,'$.batch_id'))=?", [$batch,$batch])===$expected,'Imported rows do not match source.');
        $after = array_column($monthly(),null,'month');
        foreach ($sourceMonths as $month) {
            $key=$month['month'];
            foreach (['entries','people'] as $field) $check((int)$after[$key][$field]-(int)($before[$key][$field]??0)===(int)$month['n'],'Report total increase does not match source.');
        }
        $check($db->trans_status(), 'Import transaction failed.');
        $check($db->trans_commit(), 'Commit failed.');
    } catch (Throwable $error) { $db->trans_rollback(); throw $error; }
    echo "Imported and verified {$expected} tagged simulation visits. Original visits were not modified.\n";
    echo json_encode($monthly(),JSON_PRETTY_PRINT),"\n";
} catch (Throwable $error) { fwrite(STDERR,$error->getMessage()."\n"); exit(1); }
