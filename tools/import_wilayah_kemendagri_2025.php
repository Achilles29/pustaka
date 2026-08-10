<?php
/**
 * Mengimpor master wilayah nasional Kepmendagri 2025 dari XLSX (tidak disimpan Git).
 * Sumber: https://github.com/yonatanyl/KODE-WILAYAH-KEPMENDAGRI-2025 (CC BY 4.0),
 * mengacu Kepmendagri 300.2.2-2138 Tahun 2025.
 *
 * php tools/import_wilayah_kemendagri_2025.php --source=/path/data.xlsx
 * php tools/import_wilayah_kemendagri_2025.php --source=/path/data.xlsx --dry-run
 */
declare(strict_types=1);

$options = getopt('', ['source:', 'dry-run']);
$source = (string) ($options['source'] ?? '');
if ($source === '' || ! is_file($source)) {
	fwrite(STDERR, "Berkas XLSX tidak ditemukan. Gunakan --source=/path/KODE-WILAYAH-KEPMENDAGRI-2025.xlsx\n");
	exit(1);
}
if (! class_exists('ZipArchive')) {
	fwrite(STDERR, "Ekstensi PHP ZipArchive wajib tersedia.\n");
	exit(1);
}

$root = dirname(__DIR__);
define('BASEPATH', $root . '/system/');
defined('ENVIRONMENT') || define('ENVIRONMENT', 'production');
$db = [];
require $root . '/application/config/database.php';
$config = $db['default'] ?? [];
$mysqli = mysqli_init();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$mysqli->real_connect((string) ($config['hostname'] ?? 'localhost'), (string) ($config['username'] ?? ''), (string) ($config['password'] ?? ''), (string) ($config['database'] ?? 'pustaka'));
$mysqli->set_charset('utf8mb4');

$zip = new ZipArchive();
if ($zip->open($source) !== true) {
	fwrite(STDERR, "XLSX tidak dapat dibuka.\n");
	exit(1);
}
$strings = readSharedStrings($zip);
$zip->close();

$stat = ['rows' => 0, 'provinces' => [], 'regencies' => [], 'districts' => [], 'villages' => []];
$dryRun = array_key_exists('dry-run', $options);
if (! $dryRun) $mysqli->begin_transaction();
try {
	$reader = new XMLReader();
	if (! $reader->open('zip://' . $source . '#xl/worksheets/sheet1.xml', null, LIBXML_NONET | LIBXML_COMPACT)) throw new RuntimeException('Sheet XLSX tidak ditemukan.');
	$header = [];
	while ($reader->read()) {
		if ($reader->nodeType !== XMLReader::ELEMENT || $reader->localName !== 'row') continue;
		$row = spreadsheetRow($reader->readOuterXML(), $strings);
		if (! $header) { $header = $row; continue; }
		$record = normalizeRow($row);
		if (! $record) continue;
		$stat['rows']++;
		if ($dryRun) {
			$stat['provinces'][$record['province_code']] = true; $stat['regencies'][$record['regency_code']] = true; $stat['districts'][$record['district_code']] = true; $stat['villages'][$record['village_code']] = true;
		} else {
			importRecord($mysqli, $record, $stat);
		}
	}
	$reader->close();
	if (! $dryRun) $mysqli->commit();
} catch (Throwable $e) {
	if (! $dryRun) $mysqli->rollback();
	fwrite(STDERR, 'Import dibatalkan: ' . $e->getMessage() . "\n");
	exit(1);
}
printf("%s: %d baris | %d provinsi | %d kab/kota | %d kecamatan | %d desa/kelurahan\n", $dryRun ? 'Dry run' : 'Selesai', $stat['rows'], count($stat['provinces']), count($stat['regencies']), count($stat['districts']), count($stat['villages']));

function readSharedStrings(ZipArchive $zip): array {
	$xml = $zip->getFromName('xl/sharedStrings.xml');
	if ($xml === false) throw new RuntimeException('sharedStrings XLSX tidak ditemukan.');
	$reader = new XMLReader(); $reader->XML($xml, null, LIBXML_NONET | LIBXML_COMPACT); $result = [];
	while ($reader->read()) if ($reader->nodeType === XMLReader::ELEMENT && $reader->localName === 'si') $result[] = html_entity_decode(trim(strip_tags($reader->readInnerXML())), ENT_QUOTES | ENT_XML1, 'UTF-8');
	$reader->close(); return $result;
}
function spreadsheetRow(string $xml, array $strings): array {
	$row = simplexml_load_string($xml); if (! $row) return [];
	$result = [];
	foreach ($row->children('http://schemas.openxmlformats.org/spreadsheetml/2006/main') as $cell) {
		$attr = $cell->attributes(); $column = preg_replace('/\d+/', '', (string) $attr['r']);
		$value = (string) $cell->children('http://schemas.openxmlformats.org/spreadsheetml/2006/main')->v;
		$result[$column] = ((string) $attr['t'] === 's') ? ($strings[(int) $value] ?? '') : $value;
	}
	return $result;
}
function normalizeRow(array $row): ?array {
	$code = static fn($value) => preg_replace('/\D+/', '', (string) $value);
	$province = $code($row['G'] ?? ''); $regency = $code($row['E'] ?? ''); $district = $code($row['C'] ?? ''); $village = $code($row['A'] ?? '');
	if (strlen($province) !== 2 || strlen($regency) !== 4 || strlen($district) !== 6 || strlen($village) !== 10) return null;
	return ['province_code' => $province, 'province_name' => trim((string) ($row['H'] ?? '')), 'regency_code' => $regency, 'regency_name' => trim((string) ($row['F'] ?? '')), 'district_code' => $district, 'district_name' => trim((string) ($row['D'] ?? '')), 'village_code' => $village, 'village_name' => trim((string) ($row['B'] ?? '')), 'village_type' => trim((string) ($row['I'] ?? ''))];
}
function importRecord(mysqli $db, array $r, array &$stat): void {
	$provinceId = upsert($db, 'ref_provinces', 'code', $r['province_code'], ['name' => $r['province_name'], 'is_active' => 1]);
	$area = stripos($r['regency_name'], 'kota') === 0 ? 'kota' : 'kabupaten';
	$regencyId = upsert($db, 'ref_regencies', 'code', $r['regency_code'], ['province_id' => $provinceId, 'name' => $r['regency_name'], 'area_type' => $area, 'is_active' => 1]);
	$districtId = upsert($db, 'ref_districts', 'code', $r['district_code'], ['province_id' => $provinceId, 'regency_id' => $regencyId, 'province_code' => $r['province_code'], 'regency_code' => $r['regency_code'], 'full_code' => dotted($r['district_code']), 'name' => $r['district_name'], 'is_active' => 1]);
	$type = stripos($r['village_type'], 'kelurahan') !== false ? 'kelurahan' : 'desa';
	upsert($db, 'ref_villages', 'code', $r['village_code'], ['district_id' => $districtId, 'province_id' => $provinceId, 'regency_id' => $regencyId, 'province_code' => $r['province_code'], 'regency_code' => $r['regency_code'], 'district_code' => $r['district_code'], 'area_type' => $type, 'name' => $r['village_name'], 'is_active' => 1]);
	$stat['provinces'][$r['province_code']] = true; $stat['regencies'][$r['regency_code']] = true; $stat['districts'][$r['district_code']] = true; $stat['villages'][$r['village_code']] = true;
}
function upsert(mysqli $db, string $table, string $key, string $value, array $data): int {
	$columns = array_merge([$key], array_keys($data)); $placeholders = implode(',', array_fill(0, count($columns), '?'));
	$updates = implode(',', array_map(static fn($column) => "`$column`=VALUES(`$column`)", array_keys($data)));
	$sql = 'INSERT INTO `' . $table . '` (`' . implode('`,`', $columns) . '`) VALUES (' . $placeholders . ') ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id),' . $updates;
	$stmt = $db->prepare($sql); $values = array_merge([$value], array_values($data)); $types = str_repeat('s', count($values)); $stmt->bind_param($types, ...$values); $stmt->execute(); $id = (int) $db->insert_id; $stmt->close(); return $id;
}
function dotted(string $code): string { return substr($code, 0, 2) . '.' . substr($code, 2, 2) . '.' . substr($code, 4, 2); }
