<?php
/**
 * Import buku PDF dari direktori lokal ke katalog dan storage non-public Pustaka.
 *
 * Pemakaian:
 *   php tools/import_buku_digital.php --source=/www/wwwroot/Buku --dry-run
 *   php tools/import_buku_digital.php --source=/www/wwwroot/Buku
 *
 * Script aman dijalankan ulang. Identitas sumber memakai SHA-1 path relatif,
 * sehingga metadata dan aset yang sama akan diperbarui, bukan diduplikasi.
 */

declare(strict_types=1);

const IMPORT_SOURCE_SYSTEM = 'buku_kemendikdasmen_2026';
const IMPORT_STORAGE_DIR = 'storage/ebooks/kemendikdasmen-2026';

$options = getopt('', ['source:', 'dry-run', 'limit::', 'offset::']);
$sourceRoot = isset($options['source']) ? (string) $options['source'] : '/www/wwwroot/Buku';
$sourceRoot = realpath($sourceRoot) ?: '';
$dryRun = array_key_exists('dry-run', $options);
$limit = isset($options['limit']) ? max(0, (int) $options['limit']) : 0;
$offset = isset($options['offset']) ? max(0, (int) $options['offset']) : 0;
$projectRoot = dirname(__DIR__);

if ($sourceRoot === '' || ! is_dir($sourceRoot)) {
	stderr('Direktori sumber tidak ditemukan. Berikan --source=/path/ke/Buku.');
	exit(1);
}

define('BASEPATH', $projectRoot . '/system/');
defined('ENVIRONMENT') || define('ENVIRONMENT', 'production');
$db = [];
require $projectRoot . '/application/config/database.php';
$config = $db['default'] ?? [];

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$mysqli = mysqli_init();
if (! $mysqli || ! $mysqli->real_connect(
	(string) ($config['hostname'] ?? 'localhost'),
	(string) ($config['username'] ?? ''),
	(string) ($config['password'] ?? ''),
	(string) ($config['database'] ?? 'pustaka')
)) {
	stderr('Koneksi database Pustaka gagal: ' . mysqli_connect_error());
	exit(1);
}
$mysqli->set_charset('utf8mb4');

$categories = indexedRows($mysqli, 'SELECT id, code FROM book_content_categories');
$classifications = indexedRows($mysqli, 'SELECT id, code FROM book_classification_masters');
foreach (['buku-pelajaran'] as $code) {
	if (! isset($categories[$code])) {
		stderr('Master kategori wajib tidak ditemukan: ' . $code);
		exit(1);
	}
}
foreach (['000', '200', '300', '400', '500', '600', '700', '800', '900', 'buku-pelajaran'] as $code) {
	if (! isset($classifications[$code])) {
		stderr('Master klasifikasi wajib tidak ditemukan: ' . $code);
		exit(1);
	}
}

$uploadedBy = 0;
$adminResult = $mysqli->query("SELECT id FROM auth_user WHERE username = 'superadmin' ORDER BY id ASC LIMIT 1");
if ($adminResult && ($admin = $adminResult->fetch_assoc())) {
	$uploadedBy = (int) $admin['id'];
}

$files = [];
$iterator = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator($sourceRoot, FilesystemIterator::SKIP_DOTS),
	RecursiveIteratorIterator::LEAVES_ONLY
);
foreach ($iterator as $file) {
	if (! $file->isFile() || strtolower($file->getExtension()) !== 'pdf') {
		continue;
	}
	$files[] = $file->getPathname();
}
sort($files, SORT_NATURAL | SORT_FLAG_CASE);
if ($offset > 0 || $limit > 0) {
	$files = array_slice($files, $offset, $limit > 0 ? $limit : null);
}

$summary = [
	'found' => count($files),
	'copied' => 0,
	'reused_file' => 0,
	'books_created' => 0,
	'books_updated' => 0,
	'assets_created' => 0,
	'assets_updated' => 0,
	'items_created' => 0,
	'items_updated' => 0,
	'invalid' => 0,
	'failed' => 0,
	'bytes' => 0,
	'categories' => [],
	'classifications' => [],
];

foreach ($files as $index => $sourceFile) {
	$relative = ltrim(str_replace('\\', '/', substr($sourceFile, strlen(rtrim($sourceRoot, DIRECTORY_SEPARATOR)))), '/');
	$title = titleFromFilename(pathinfo($relative, PATHINFO_FILENAME));
	$classCode = classificationFor($title);
	$categoryCode = 'buku-pelajaran';
	$summary['categories'][$categoryCode] = ($summary['categories'][$categoryCode] ?? 0) + 1;
	$summary['classifications'][$classCode] = ($summary['classifications'][$classCode] ?? 0) + 1;

	$size = filesize($sourceFile);
	$summary['bytes'] += $size === false ? 0 : $size;
	$prefix = file_get_contents($sourceFile, false, null, 0, 4);
	if ($prefix !== '%PDF') {
		$summary['invalid']++;
		stderr('Bukan PDF valid, dilewati: ' . $relative);
		continue;
	}

	if ($dryRun) {
		printf("[%d/%d] %s | %s | %s\n", $index + 1, count($files), $classCode, $categoryCode, $title);
		continue;
	}

	$sourceId = sha1($relative);
	$storageRelative = IMPORT_STORAGE_DIR . '/' . substr($sourceId, 0, 2) . '/' . $sourceId . '.pdf';
	$storageFile = $projectRoot . '/' . $storageRelative;
	try {
		if (is_file($storageFile) && filesize($storageFile) === $size) {
			$summary['reused_file']++;
		} else {
			$storageDir = dirname($storageFile);
			if (! is_dir($storageDir) && ! mkdir($storageDir, 0775, true) && ! is_dir($storageDir)) {
				throw new RuntimeException('Tidak dapat membuat folder storage.');
			}
			copyFile($sourceFile, $storageFile);
			clearstatcache(true, $storageFile);
			if (! is_file($storageFile) || filesize($storageFile) !== $size) {
				throw new RuntimeException('Ukuran file hasil salin tidak sesuai.');
			}
			$summary['copied']++;
		}

		$mysqli->begin_transaction();
		$book = findBook($mysqli, $sourceId);
		$bookPayload = [
			'title' => $title,
			'statement_responsibility' => 'Kementerian Pendidikan Dasar dan Menengah Republik Indonesia',
			'publisher' => 'Kementerian Pendidikan Dasar dan Menengah Republik Indonesia',
			'classification' => $classCode,
			'content_category_id' => (int) $categories[$categoryCode]['id'],
			'content_classification_id' => (int) $classifications['buku-pelajaran']['id'],
			'call_number' => $classCode,
			'language' => 'ind',
			'abstract' => 'Buku ajar digital Kurikulum Merdeka. Sumber impor: ' . $relative,
			'status' => 'published',
			'updated_by' => $uploadedBy ?: null,
		];
		if ($book) {
			updateBook($mysqli, (int) $book['id'], $bookPayload);
			$bookId = (int) $book['id'];
			$summary['books_updated']++;
		} else {
			$bookId = insertBook($mysqli, $sourceId, $bookPayload, $uploadedBy ?: null);
			$summary['books_created']++;
		}

		$asset = findAsset($mysqli, $sourceId);
		$assetPayload = [
			'book_id' => $bookId,
			'source_path' => $sourceFile,
			'migration_status' => 'copied',
			'migrated_at' => date('Y-m-d H:i:s'),
			'file_original_name' => basename($sourceFile),
			'file_path' => $storageRelative,
			'mime_type' => 'application/pdf',
			'file_size' => (int) $size,
			'access_policy' => 'online_only',
			'is_downloadable' => 0,
			'rights_basis' => 'unknown',
			'access_notes' => 'Diimpor dari koleksi lokal /Buku. Akses online tanpa unduh; status hak publikasi perlu diverifikasi oleh admin.',
			'status' => 'active',
			'uploaded_by' => $uploadedBy ?: null,
		];
		if ($asset) {
			updateAsset($mysqli, (int) $asset['id'], $assetPayload);
			$summary['assets_updated']++;
		} else {
			insertAsset($mysqli, $sourceId, $assetPayload);
			$summary['assets_created']++;
		}

		// Setiap ebook juga mendapat satu eksemplar katalog digital. Ini membuat
		// filter Media/Aturan katalog mengenal koleksi impor sebagai "Baca digital".
		$item = findItem($mysqli, $sourceId);
		$itemPayload = [
			'book_id' => $bookId,
			'item_code' => 'EBOOK-' . strtoupper(substr($sourceId, 0, 16)),
			'barcode' => 'EBOOK-' . strtoupper(substr($sourceId, 0, 16)),
			'call_number' => $classCode,
			'location_name' => 'Koleksi Digital',
			'location_library_name' => 'Pustaka Digital Rembang',
			'location_room_name' => 'Reader Online',
			'rule_name' => 'Baca digital',
			'collection_type' => 'Ebook',
			'category_name' => 'Buku Pelajaran',
			'media_name' => 'PDF',
			'source_name' => 'Kementerian Pendidikan Dasar dan Menengah Republik Indonesia',
			'inventory_number' => 'DIG-' . strtoupper(substr($sourceId, 0, 16)),
			'status' => 'available',
			'status_label' => 'Tersedia digital',
			'is_public' => 1,
		];
		if ($item) {
			updateItem($mysqli, (int) $item['id'], $itemPayload);
			$summary['items_updated']++;
		} else {
			insertItem($mysqli, $sourceId, $itemPayload);
			$summary['items_created']++;
		}
		$mysqli->commit();
		printf("[%d/%d] tersimpan: %s\n", $index + 1, count($files), $title);
	} catch (Throwable $e) {
		$mysqli->rollback();
		$summary['failed']++;
		stderr('Gagal (' . $relative . '): ' . $e->getMessage());
	}
}

ksort($summary['categories']);
ksort($summary['classifications']);
printf("\nMode: %s\n", $dryRun ? 'dry-run (tanpa perubahan)' : 'import');
foreach ($summary as $key => $value) {
	if (is_array($value)) {
		printf("%s: %s\n", $key, json_encode($value, JSON_UNESCAPED_UNICODE));
	} else {
		printf("%s: %d\n", $key, $value);
	}
}

function indexedRows(mysqli $mysqli, string $sql): array
{
	$result = $mysqli->query($sql);
	$rows = [];
	while ($result && ($row = $result->fetch_assoc())) {
		$rows[$row['code']] = $row;
	}
	return $rows;
}

function titleFromFilename(string $name): string
{
	$name = str_replace("\xC2\xA0", ' ', $name);
	$name = str_replace(['SMA_MA_SMK_MAK', 'SMA_SMK_MA_MAK', 'SMA_MA', 'SMA_SMK', 'SMK_MAK', 'SD_MI', 'SMP_MTs'], ['SMA/MA/SMK/MAK', 'SMA/SMK/MA/MAK', 'SMA/MA', 'SMA/SMK', 'SMK/MAK', 'SD/MI', 'SMP/MTs'], $name);
	$name = str_replace('_', ': ', $name);
	$name = preg_replace('/\s+/u', ' ', $name) ?: $name;
	return trim($name);
}

function classificationFor(string $title): string
{
	$text = mb_strtolower($title, 'UTF-8');
	if (preg_match('/agama|kepercayaan|tuhan|budi pekerti/', $text)) return '200';
	if (preg_match('/pancasila|sosiologi|ekonomi|antropologi|pendidikan kewarganegaraan/', $text)) return '300';
	if (preg_match('/bahasa|berbahasa|english|mandarin|korea/', $text)) return '400';
	if (preg_match('/matematika|fisika|kimia|biologi|ilmu pengetahuan alam|\bipas\b/', $text)) return '500';
	if (preg_match('/informatika|koding|kecerdasan artifisial|pengembangan gim|jaringan komputer/', $text)) return '000';
	if (preg_match('/sejarah|geografi/', $text)) return '900';
	if (preg_match('/jasmani|olahraga|seni |seni$|musik|tari|teater|rupa|animasi/', $text)) return '700';
	if (preg_match('/kesehatan|keperawatan|caregiving|kecantikan|teknik|konstruksi|nautika|kuliner|logistik|desain pemodelan|pengelasan|irigasi|jalan|energi|manajemen|prakarya|kewirausahaan/', $text)) return '600';
	return '300';
}

function categoryFor(string $relative, string $title): string
{
	$studentBook = ! preg_match('/panduan guru|buku panduan guru/', $title);
	return $studentBook && str_starts_with($relative, 'SD_MI/') ? 'anak-remaja' : 'non-fiksi';
}

function findBook(mysqli $mysqli, string $sourceId): ?array
{
	$stmt = $mysqli->prepare('SELECT id FROM books WHERE source_system = ? AND source_id = ? LIMIT 1');
	$system = IMPORT_SOURCE_SYSTEM;
	$stmt->bind_param('ss', $system, $sourceId);
	$stmt->execute();
	return $stmt->get_result()->fetch_assoc() ?: null;
}

function findAsset(mysqli $mysqli, string $sourceId): ?array
{
	$stmt = $mysqli->prepare('SELECT id FROM digital_assets WHERE source_system = ? AND source_id = ? LIMIT 1');
	$system = IMPORT_SOURCE_SYSTEM;
	$stmt->bind_param('ss', $system, $sourceId);
	$stmt->execute();
	return $stmt->get_result()->fetch_assoc() ?: null;
}

function findItem(mysqli $mysqli, string $sourceId): ?array
{
	$stmt = $mysqli->prepare('SELECT id FROM book_items WHERE source_system = ? AND source_id = ? LIMIT 1');
	$system = IMPORT_SOURCE_SYSTEM;
	$stmt->bind_param('ss', $system, $sourceId);
	$stmt->execute();
	return $stmt->get_result()->fetch_assoc() ?: null;
}

function insertBook(mysqli $mysqli, string $sourceId, array $data, ?int $createdBy): int
{
	$sql = 'INSERT INTO books (source_system, source_id, title, statement_responsibility, publisher, classification, content_category_id, content_classification_id, call_number, language, abstract, status, created_by, updated_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
	$stmt = $mysqli->prepare($sql);
	$system = IMPORT_SOURCE_SYSTEM;
	$stmt->bind_param('ssssssiissssii', $system, $sourceId, $data['title'], $data['statement_responsibility'], $data['publisher'], $data['classification'], $data['content_category_id'], $data['content_classification_id'], $data['call_number'], $data['language'], $data['abstract'], $data['status'], $createdBy, $data['updated_by']);
	$stmt->execute();
	return (int) $mysqli->insert_id;
}

function updateBook(mysqli $mysqli, int $id, array $data): void
{
	$sql = 'UPDATE books SET title = ?, statement_responsibility = ?, publisher = ?, classification = ?, content_category_id = ?, content_classification_id = ?, call_number = ?, language = ?, abstract = ?, status = ?, updated_by = ? WHERE id = ?';
	$stmt = $mysqli->prepare($sql);
	$stmt->bind_param('ssssiissssii', $data['title'], $data['statement_responsibility'], $data['publisher'], $data['classification'], $data['content_category_id'], $data['content_classification_id'], $data['call_number'], $data['language'], $data['abstract'], $data['status'], $data['updated_by'], $id);
	$stmt->execute();
}

function insertAsset(mysqli $mysqli, string $sourceId, array $data): void
{
	$sql = 'INSERT INTO digital_assets (book_id, source_system, source_id, source_path, migration_status, migrated_at, file_original_name, file_path, mime_type, file_size, access_policy, is_downloadable, rights_basis, access_notes, status, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
	$stmt = $mysqli->prepare($sql);
	$system = IMPORT_SOURCE_SYSTEM;
	$stmt->bind_param('issssssssisisssi', $data['book_id'], $system, $sourceId, $data['source_path'], $data['migration_status'], $data['migrated_at'], $data['file_original_name'], $data['file_path'], $data['mime_type'], $data['file_size'], $data['access_policy'], $data['is_downloadable'], $data['rights_basis'], $data['access_notes'], $data['status'], $data['uploaded_by']);
	$stmt->execute();
}

function updateAsset(mysqli $mysqli, int $id, array $data): void
{
	$sql = 'UPDATE digital_assets SET book_id = ?, source_path = ?, migration_status = ?, migrated_at = ?, file_original_name = ?, file_path = ?, mime_type = ?, file_size = ?, access_policy = ?, is_downloadable = ?, rights_basis = ?, access_notes = ?, status = ?, uploaded_by = ? WHERE id = ?';
	$stmt = $mysqli->prepare($sql);
	$stmt->bind_param('issssssisisssii', $data['book_id'], $data['source_path'], $data['migration_status'], $data['migrated_at'], $data['file_original_name'], $data['file_path'], $data['mime_type'], $data['file_size'], $data['access_policy'], $data['is_downloadable'], $data['rights_basis'], $data['access_notes'], $data['status'], $data['uploaded_by'], $id);
	$stmt->execute();
}

function insertItem(mysqli $mysqli, string $sourceId, array $data): void
{
	$sql = 'INSERT INTO book_items (book_id, source_system, source_id, item_code, barcode, call_number, location_name, location_library_name, location_room_name, rule_name, collection_type, category_name, media_name, source_name, inventory_number, status, status_label, is_public) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
	$stmt = $mysqli->prepare($sql);
	$system = IMPORT_SOURCE_SYSTEM;
	$types = 'i' . str_repeat('s', 16) . 'i';
	$stmt->bind_param($types, $data['book_id'], $system, $sourceId, $data['item_code'], $data['barcode'], $data['call_number'], $data['location_name'], $data['location_library_name'], $data['location_room_name'], $data['rule_name'], $data['collection_type'], $data['category_name'], $data['media_name'], $data['source_name'], $data['inventory_number'], $data['status'], $data['status_label'], $data['is_public']);
	$stmt->execute();
}

function updateItem(mysqli $mysqli, int $id, array $data): void
{
	$sql = 'UPDATE book_items SET book_id = ?, item_code = ?, barcode = ?, call_number = ?, location_name = ?, location_library_name = ?, location_room_name = ?, rule_name = ?, collection_type = ?, category_name = ?, media_name = ?, source_name = ?, inventory_number = ?, status = ?, status_label = ?, is_public = ?, deleted_at = NULL WHERE id = ?';
	$stmt = $mysqli->prepare($sql);
	$types = 'i' . str_repeat('s', 14) . 'ii';
	$stmt->bind_param($types, $data['book_id'], $data['item_code'], $data['barcode'], $data['call_number'], $data['location_name'], $data['location_library_name'], $data['location_room_name'], $data['rule_name'], $data['collection_type'], $data['category_name'], $data['media_name'], $data['source_name'], $data['inventory_number'], $data['status'], $data['status_label'], $data['is_public'], $id);
	$stmt->execute();
}

function copyFile(string $source, string $destination): void
{
	$in = fopen($source, 'rb');
	$out = fopen($destination, 'wb');
	if (! $in || ! $out) {
		throw new RuntimeException('Tidak dapat membuka berkas untuk disalin.');
	}
	try {
		if (stream_copy_to_stream($in, $out) === false) {
			throw new RuntimeException('Penyalinan berkas gagal.');
		}
	} finally {
		fclose($in);
		fclose($out);
	}
}

function stderr(string $message): void
{
	fwrite(STDERR, $message . PHP_EOL);
}
