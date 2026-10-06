<?php
// CLI only: this generator writes only the isolated source simulation table.
// Explicit backed-up report preview imports use import_digital_visit_simulations.php.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('FCPATH', dirname(__DIR__).'/');
define('APPPATH', FCPATH.'application/');
define('BASEPATH', FCPATH.'system/');
define('ENVIRONMENT', 'production');
require BASEPATH.'core/Common.php';
require BASEPATH.'database/DB.php';
date_default_timezone_set('Asia/Jakarta');

$apply = in_array('--apply', $argv, true);
$db = DB('default');
$db->db_debug = false;
$batch = 'digital_u18_2025_2026_sep_v1';
$table = 'member_visit_simulations';
$check = function ($ok, $message) { if (!$ok) throw new RuntimeException($message); };
$query = function ($sql, $params = []) use ($db, $check) {
    $result = $db->query($sql, $params);
    $check($result !== false, 'Database operation failed; no member details printed.');
    return $result;
};
$validate = function () use ($query, $check, $table, $batch) {
    $invalid = $query("SELECT COUNT(*) n FROM {$table} s LEFT JOIN members m ON m.id=s.member_id
        WHERE s.batch_id=? AND (m.id IS NULL OR m.birth_date IS NULL OR m.registered_at IS NULL
        OR m.registered_at>s.visited_at OR m.birth_date>DATE(s.visited_at)
        OR TIMESTAMPDIFF(YEAR,m.birth_date,s.visited_at) NOT BETWEEN 0 AND 17
        OR s.visited_at<'2025-01-01' OR s.visited_at>='2026-10-01'
        OR s.purpose_label<>'Layanan digital' OR s.is_simulation<>1 OR s.visitor_count<>1)", [$batch])->row_array();
    $check((int)$invalid['n'] === 0, 'Simulation validation failed.');
    $rows = $query("SELECT DATE_FORMAT(visited_at,'%Y-%m') month, COUNT(*) visits,
        COUNT(DISTINCT member_id) members FROM {$table} WHERE batch_id=? GROUP BY month ORDER BY month", [$batch])->result_array();
    $check(count($rows) === 21, 'Expected exactly 21 months.');
    foreach ($rows as $row) $check((int)$row['visits'] >= 1000 && (int)$row['visits'] <= 2000, 'Monthly count outside requested range.');
    $check(count(array_unique(array_column($rows, 'visits'))) > 1, 'Monthly totals must fluctuate.');
    return $rows;
};

try {
    if ($db->table_exists($table)) {
        $existing = $query("SELECT COUNT(*) n FROM {$table} WHERE batch_id=?", [$batch])->row_array();
        if ((int)$existing['n'] > 0) {
            $rows = $validate();
            echo "Batch already exists and is valid; no inserts or changes.\n";
            echo json_encode($rows, JSON_PRETTY_PRINT), "\n";
            exit(0);
        }
    }
    mt_srand(20260930);
    $visits = [];
    $summary = [];
    $previous = null;
    for ($index = 0; $index < 21; $index++) {
        $start = new DateTimeImmutable('2025-01-01');
        $start = $start->modify('+'.$index.' months');
        $next = $start->modify('+1 month');
        $month = $start->format('Y-m');
        do { $target = mt_rand(1000, 2000); } while ($target === $previous);
        $previous = $target;
        $members = $query("SELECT id,birth_date,registered_at FROM members
            WHERE deleted_at IS NULL AND birth_date > DATE_SUB(?, INTERVAL 18 YEAR)
            AND birth_date < ? AND registered_at >= '1900-01-01' AND registered_at < ?
            ORDER BY id", [$start->format('Y-m-d'), $next->format('Y-m-d'), $next->format('Y-m-d')])->result_array();
        $candidates = [];
        for ($day = $start; $day < $next; $day = $day->modify('+1 day')) {
            foreach ($members as $member) {
                $at = $day->setTime(mt_rand(8,21), mt_rand(0,59), mt_rand(0,59));
                $birth = new DateTimeImmutable($member['birth_date']);
                if ($birth > $at || $birth->diff($at)->y >= 18 || $member['registered_at'] > $at->format('Y-m-d H:i:s')) continue;
                $candidates[] = [
                    'batch_id' => $batch,
                    'member_id' => (int)$member['id'],
                    'visited_at' => $at->format('Y-m-d H:i:s'),
                    'visit_date' => $at->format('Y-m-d'),
                    'purpose_label' => 'Layanan digital',
                    'visit_channel' => 'digital_access',
                    'visitor_count' => 1,
                    'is_simulation' => 1,
                    'source_system' => 'simulation',
                    'information' => 'SIMULASI — bukan bukti kunjungan aktual; tidak untuk laporan resmi.',
                ];
            }
        }
        $check(count($candidates) >= $target, 'Not enough eligible member-days for '.$month.'; no data inserted.');
        shuffle($candidates);
        $selected = array_slice($candidates, 0, $target);
        $summary[] = ['month'=>$month, 'visits'=>$target, 'members'=>count(array_unique(array_column($selected,'member_id')))];
        $onlineTarget = $start->format('Y') === '2025' ? 0 : (int)round($target * 0.30);
        foreach ($selected as $position => $visit) {
            $visit['visit_channel'] = $position < $onlineTarget ? 'digital_access' : 'library_guestbook';
            $visits[] = $visit;
        }
    }
    echo ($apply ? 'APPLY' : 'DRY RUN'), ': simulation batch ', $batch, "\n";
    echo json_encode($summary, JSON_PRETTY_PRINT), "\nTotal: ", count($visits), " simulated visits.\n";
    if (!$apply) { echo "No changes. Use --apply to insert into member_visit_simulations only.\n"; exit(0); }

    // This isolated table is deliberately not consumed by operational reports.
    $query("CREATE TABLE IF NOT EXISTS {$table} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        batch_id VARCHAR(80) NOT NULL,
        member_id BIGINT UNSIGNED NOT NULL,
        visited_at DATETIME NOT NULL,
        visit_date DATE NOT NULL,
        purpose_label VARCHAR(180) NOT NULL,
        visit_channel VARCHAR(50) NOT NULL,
        visitor_count INT UNSIGNED NOT NULL DEFAULT 1,
        is_simulation TINYINT UNSIGNED NOT NULL DEFAULT 1,
        source_system VARCHAR(50) NOT NULL DEFAULT 'simulation',
        information VARCHAR(255) NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_simulation_member_day (batch_id,member_id,visit_date),
        KEY idx_simulation_batch_date (batch_id,visited_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    COMMENT='Synthetic visits only; excluded from actual attendance reports'");
    $check($db->trans_begin(), 'Could not start transaction.');
    try {
        foreach (array_chunk($visits, 500) as $chunk) {
            $check($db->insert_batch($table, $chunk) !== false, 'Insert failed.');
        }
        $actual = $validate();
        foreach ($actual as $index=>$row) {
            $check($row['month'] === $summary[$index]['month'] && (int)$row['visits'] === $summary[$index]['visits'], 'Persisted totals differ from plan.');
        }
        $check($db->trans_status(), 'Transaction failed.');
        $check($db->trans_commit(), 'Commit failed.');
    } catch (Throwable $error) {
        $db->trans_rollback();
        throw $error;
    }
    echo "Committed and validated. Actual visits and member records were not changed.\n";
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage()."\n");
    exit(1);
}
