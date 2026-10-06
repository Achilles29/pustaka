<?php
// Read-only regression checks; no users, sessions, or application records are changed.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('FCPATH', dirname(__DIR__) . '/');
define('APPPATH', FCPATH . 'application/');
define('BASEPATH', FCPATH . 'system/');
define('ENVIRONMENT', 'production');
require BASEPATH . 'core/Common.php';
require BASEPATH . 'core/Model.php';
require BASEPATH . 'database/DB.php';
require APPPATH . 'models/Library_model.php';
require APPPATH . 'models/Reading_point_model.php';
function base_url($uri = '') { return 'http://localhost/' . $uri; }
$model = new Library_model();
$model->db = DB('default');
$checks = 0;
function check($condition, $message) {
    global $checks;
    if (!$condition) throw new RuntimeException('FAIL: ' . $message);
    $checks++;
    echo 'PASS: ' . $message . PHP_EOL;
}
$all = $model->get_map_libraries();
check(count($all) === $model->count_libraries([]), 'Map query includes all libraries without pagination');
check(count(array_unique(array_column($all, 'id'))) === count($all), 'No duplicate map records');
$points = $model->map_payload($all, true);
check(count($points) > 0, 'Valid map coordinates exist');
foreach ([10, 25, 50, 100] as $limit) {
    $table = $model->get_libraries([], $limit);
    check(count($table) === min($limit, count($all)), 'Table limit ' . $limit);
    check($model->map_payload($model->get_map_libraries(), true) === $points, 'Map unchanged for table limit ' . $limit);
}
$sample = null;
foreach ($all as $library) if ((int)$library['id'] === (int)$points[0]['id']) { $sample = $library; break; }
check($sample !== null, 'Use valid-coordinate fixture; first alphabetic library may lack GPS');
check(count($model->get_libraries(['q' => '__missing_library_test__'])) === 0, 'Empty search affects table');
check($model->map_payload($model->get_map_libraries(), true) === $points, 'Empty search leaves all map points intact');
$scoped = $model->get_map_libraries((int)$sample['id']);
check(count($scoped) === 1 && (int)$scoped[0]['id'] === (int)$sample['id'], 'Map retains library scope');
check($model->get_map_libraries(0) === [], 'Explicit invalid scope does not become global');
$public = $model->map_payload([$sample]);
$admin = $model->map_payload([$sample], true);
check(!array_key_exists('manager', $public[0]), 'Home payload does not gain admin-only details');
check(isset($admin[0]['code'], $admin[0]['verified']) && array_key_exists('manager', $admin[0]), 'Admin popup metadata present');
check(!empty($admin[0]['type_code']), 'Admin payload provides stable type code for colored icons');
check(!array_key_exists('type_code', $public[0]), 'Home payload remains unchanged by admin icon metadata');
foreach ([[null, 111], ['', 111], ['bad', 111], [91, 111], [-6, 181], [0, 0], [INF, 111]] as $coordinates) {
    $invalid = array_replace($sample, ['latitude' => $coordinates[0], 'longitude' => $coordinates[1]]);
    check($model->map_payload([$invalid]) === [], 'Invalid coordinate excluded: ' . var_export($coordinates[0], true));
}
$malicious = array_replace($sample, ['name' => '</script><script>alert(1)</script>', 'latitude' => -6.7, 'longitude' => 111.3]);
$json = json_encode($model->map_payload([$malicious], true), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
check(strpos($json, '</script>') === false && json_decode($json, true)[0]['name'] === $malicious['name'], 'Embedded JSON safely preserves hostile text');
$public_points = $model->public_map_payload();
$active_points = $model->map_payload($model->get_map_libraries(null, true), true);
check(count($public_points) === count($active_points), 'Public map includes all active valid libraries, not just 25');
check(array_filter($public_points, function ($p) { return $p['status'] !== 'active'; }) === [], 'Public map excludes pending/inactive libraries');
foreach (['manager', 'email', 'phone', 'url', 'source_id', 'created_by'] as $private_field) {
    check(array_filter($public_points, function ($p) use ($private_field) { return array_key_exists($private_field, $p); }) === [], 'Public projection excludes ' . $private_field);
}
$reading = new Reading_point_model();
$reading->db = $model->db;
$reading->load = new class { public function model($name) {} };
$reading->Library_model = $model;
$member_points = $reading->member_map_payload();
$library_points = array_values(array_filter($member_points, function ($p) { return $p['location_kind'] === 'library'; }));
check($library_points === $public_points, 'Member map shares safe active library projection');
check(count(array_filter($member_points, function ($p) { return $p['location_kind'] === 'reading_point'; })) <= count($reading->get_active_points(500)), 'Member map contains only valid active reading points');
check(array_filter($member_points, function ($p) { return isset($p['token']) || isset($p['member_id']) || isset($p['manager']); }) === [], 'Member map does not serialize tokens, members or PIC');
echo $checks . ' checks passed; ' . count($points) . ' map points.' . PHP_EOL;
