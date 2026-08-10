<?php
/**
 * Mengunduh dan mengimpor 20 klasik Project Gutenberg ke katalog Pustaka.
 * PDF dibuat lokal dari HTML sumber dengan WeasyPrint agar Reader tidak
 * bergantung pada tautan berkas Gutenberg.
 *
 * Pemakaian: php tools/import_project_gutenberg.php [--dry-run] [--limit=20]
 * Cover resmi dapat diambil setelah impor dengan tools/fetch_project_gutenberg_covers.php.
 */
declare(strict_types=1);

const PG_SOURCE_SYSTEM = 'project_gutenberg';
const PG_STORAGE_DIR = 'storage/ebooks/project-gutenberg';

$books = [
	[35, 'The Time Machine', 'H. G. Wells', '1895', '823', 'fiksi', 'Time travel fiction'],
	[43, 'The Strange Case of Dr. Jekyll and Mr. Hyde', 'Robert Louis Stevenson', '1886', '823', 'fiksi', 'Psychological fiction'],
	[46, 'A Christmas Carol', 'Charles Dickens', '1843', '823', 'fiksi', 'Christmas fiction'],
	[55, 'The Wonderful Wizard of Oz', 'L. Frank Baum', '1900', '813', 'anak-remaja', 'Fantasy fiction'],
	[76, 'Adventures of Huckleberry Finn', 'Mark Twain', '1884', '813', 'anak-remaja', 'Adventure fiction'],
	[113, 'The Secret Garden', 'Frances Hodgson Burnett', '1911', '823', 'anak-remaja', 'Children’s fiction'],
	[161, 'Sense and Sensibility', 'Jane Austen', '1811', '823', 'fiksi', 'Love stories'],
	[174, 'The Picture of Dorian Gray', 'Oscar Wilde', '1890', '823', 'fiksi', 'Psychological fiction'],
	[209, 'Wuthering Heights', 'Emily Brontë', '1847', '823', 'fiksi', 'Love stories'],
	[215, 'The Call of the Wild', 'Jack London', '1903', '813', 'fiksi', 'Adventure fiction'],
	[514, 'Little Women', 'Louisa May Alcott', '1868', '813', 'anak-remaja', 'Family fiction'],
	[844, 'The Importance of Being Earnest', 'Oscar Wilde', '1895', '822', 'fiksi', 'English drama'],
	[996, 'Don Quixote', 'Miguel de Cervantes Saavedra', '1605', '863', 'fiksi', 'Spanish fiction'],
	[1184, 'The Count of Monte Cristo', 'Alexandre Dumas', '1844', '843', 'fiksi', 'Adventure fiction'],
	[1264, 'Robinson Crusoe', 'Daniel Defoe', '1719', '823', 'fiksi', 'Adventure fiction'],
	[1952, 'The Yellow Wallpaper', 'Charlotte Perkins Gilman', '1892', '813', 'fiksi', 'Psychological fiction'],
	[1998, 'Thus Spoke Zarathustra', 'Friedrich Wilhelm Nietzsche', '1883', '193', 'non-fiksi', 'Philosophy'],
	[2554, 'Crime and Punishment', 'Fyodor Dostoyevsky', '1866', '891', 'fiksi', 'Russian fiction'],
	[2852, 'The Hound of the Baskervilles', 'Arthur Conan Doyle', '1902', '823', 'fiksi', 'Detective and mystery stories'],
	[3600, 'The Essays of Montaigne', 'Michel de Montaigne', '1580', '844', 'non-fiksi', 'Essays'],
];

$options = getopt('', ['dry-run', 'limit::']);
$dryRun = array_key_exists('dry-run', $options);
$limit = isset($options['limit']) ? max(1, min(count($books), (int) $options['limit'])) : count($books);
$books = array_slice($books, 0, $limit);
$projectRoot = dirname(__DIR__);

define('BASEPATH', $projectRoot . '/system/');
defined('ENVIRONMENT') || define('ENVIRONMENT', 'production');
$db = [];
require $projectRoot . '/application/config/database.php';
$config = $db['default'];

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$mysqli = mysqli_init();
$mysqli->real_connect($config['hostname'], $config['username'], $config['password'], $config['database']);
$mysqli->set_charset('utf8mb4');

$masters = [];
foreach (['book_content_categories', 'book_classification_masters'] as $table) {
	$result = $mysqli->query("SELECT id, code FROM {$table}");
	while ($row = $result->fetch_assoc()) $masters[$table][$row['code']] = (int) $row['id'];
}
foreach (['fiksi', 'anak-remaja', 'non-fiksi'] as $code) {
	if (empty($masters['book_content_categories'][$code])) throw new RuntimeException("Kategori {$code} tidak tersedia.");
}
foreach (['100', '800'] as $code) {
	if (empty($masters['book_classification_masters'][$code])) throw new RuntimeException("Klasifikasi {$code} tidak tersedia.");
}
$admin = $mysqli->query("SELECT id FROM auth_user WHERE username = 'superadmin' ORDER BY id LIMIT 1")->fetch_assoc();
$adminId = (int) ($admin['id'] ?? 0);
$summary = ['requested' => count($books), 'rendered' => 0, 'reused' => 0, 'books_created' => 0, 'books_updated' => 0, 'assets_created' => 0, 'assets_updated' => 0, 'items_created' => 0, 'items_updated' => 0, 'failed' => 0, 'bytes' => 0];

foreach ($books as $position => [$id, $title, $author, $year, $callNumber, $category, $subject]) {
	$sourceId = 'gutenberg-' . $id;
	$sourceUrl = "https://www.gutenberg.org/ebooks/{$id}";
	// Varian tanpa gambar menjaga ukuran dan waktu render tetap wajar di server.
	$htmlUrl = "https://www.gutenberg.org/cache/epub/{$id}/pg{$id}.html";
	$relativePdf = PG_STORAGE_DIR . "/{$id}/pg-{$id}.pdf";
	$absolutePdf = $projectRoot . '/' . $relativePdf;
	printf("[%d/%d] %s\n", $position + 1, count($books), $title);

	try {
		if ($dryRun) {
			printf("  dry-run: %s\n", $htmlUrl);
			continue;
		}
		if (validPdf($absolutePdf)) {
			$summary['reused']++;
		} else {
			$directory = dirname($absolutePdf);
			if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) throw new RuntimeException('Folder storage tidak dapat dibuat.');
			$tmp = $absolutePdf . '.part';
			@unlink($tmp);
			$localHtml = $absolutePdf . '.source.html';
			buildLocalSourceHtml($id, $localHtml);
			$exit = runWeasyPrint($localHtml, $tmp);
			@unlink($localHtml);
			if ($exit !== 0 || ! validPdf($tmp)) {
				@unlink($tmp);
				throw new RuntimeException('Gagal membuat PDF dari sumber Project Gutenberg.');
			}
			rename($tmp, $absolutePdf);
			$summary['rendered']++;
		}

		$size = filesize($absolutePdf);
		if ($size === false) throw new RuntimeException('Ukuran PDF tidak dapat dibaca.');
		$summary['bytes'] += $size;
		$classCode = str_starts_with($callNumber, '1') ? '100' : '800';
		$categoryId = $masters['book_content_categories'][$category];
		$classId = $masters['book_classification_masters'][$classCode];
		$now = date('Y-m-d H:i:s');
		$abstract = "Ebook domain publik. Sumber: Project Gutenberg #{$id}; katalog permanen: {$sourceUrl}.";
		$notes = 'Sumber Project Gutenberg; PDF dibuat dan disimpan lokal oleh Pustaka. Hak publikasi ditandai domain publik berdasarkan karya klasik/penulis yang telah lama wafat; tetap patuhi hukum setempat.';

		$mysqli->begin_transaction();
		$bookId = findId($mysqli, 'books', $sourceId);
		if ($bookId) {
			query($mysqli, 'UPDATE books SET title=?, statement_responsibility=?, publisher=?, publish_year=?, classification=?, content_category_id=?, content_classification_id=?, call_number=?, language=?, abstract=?, status=?, updated_by=? WHERE id=?', [$title, $author, 'Project Gutenberg', $year, $classCode, $categoryId, $classId, $callNumber, 'eng', $abstract, 'published', $adminId ?: null, $bookId]);
			$summary['books_updated']++;
		} else {
			query($mysqli, 'INSERT INTO books (source_system,source_id,title,statement_responsibility,publisher,publish_year,classification,content_category_id,content_classification_id,call_number,language,abstract,status,created_by,updated_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [PG_SOURCE_SYSTEM, $sourceId, $title, $author, 'Project Gutenberg', $year, $classCode, $categoryId, $classId, $callNumber, 'eng', $abstract, 'published', $adminId ?: null, $adminId ?: null]);
			$bookId = (int) $mysqli->insert_id;
			$summary['books_created']++;
		}
		query($mysqli, 'DELETE FROM book_authors WHERE book_id=?', [$bookId]);
		query($mysqli, 'INSERT INTO book_authors (book_id,name,role,sort_order) VALUES (?,?,?,?)', [$bookId, $author, 'Author', 10]);
		query($mysqli, 'DELETE FROM book_subjects WHERE book_id=?', [$bookId]);
		query($mysqli, 'INSERT INTO book_subjects (book_id,subject) VALUES (?,?)', [$bookId, $subject]);
		query($mysqli, 'INSERT INTO book_subjects (book_id,subject) VALUES (?,?)', [$bookId, 'Public domain']);

		$assetId = findId($mysqli, 'digital_assets', $sourceId);
		$assetFields = [$bookId, $sourceUrl, 'copied', $now, "pg-{$id}.pdf", $relativePdf, 'application/pdf', $size, 'online_only', 'member', 'render_locked', 0, 'public_domain', $notes, 'active', $adminId ?: null];
		if ($assetId) {
			query($mysqli, 'UPDATE digital_assets SET book_id=?,source_path=?,migration_status=?,migrated_at=?,file_original_name=?,file_path=?,mime_type=?,file_size=?,access_policy=?,reader_audience=?,pdf_delivery=?,is_downloadable=?,rights_basis=?,access_notes=?,status=?,uploaded_by=? WHERE id=?', array_merge($assetFields, [$assetId]));
			$summary['assets_updated']++;
		} else {
			query($mysqli, 'INSERT INTO digital_assets (book_id,source_system,source_id,source_path,migration_status,migrated_at,file_original_name,file_path,mime_type,file_size,access_policy,reader_audience,pdf_delivery,is_downloadable,rights_basis,access_notes,status,uploaded_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', array_merge([$bookId, PG_SOURCE_SYSTEM, $sourceId], array_slice($assetFields, 1)));
			$summary['assets_created']++;
		}

		$itemSourceId = $sourceId . '-digital-item';
		$itemId = findId($mysqli, 'book_items', $itemSourceId);
		$itemFields = [$bookId, 'EBOOK-PG-' . $id, 'EBOOK-PG-' . $id, $callNumber, 'Koleksi Digital', 'Pustaka Digital Rembang', 'Reader Online', 'Baca digital', 'Ebook', $category === 'anak-remaja' ? 'Anak dan Remaja' : ($category === 'fiksi' ? 'Fiksi' : 'Non Fiksi'), 'PDF', 'Project Gutenberg', 'DIG-PG-' . $id, 'available', 'Tersedia digital', 1];
		if ($itemId) {
			query($mysqli, 'UPDATE book_items SET book_id=?,item_code=?,barcode=?,call_number=?,location_name=?,location_library_name=?,location_room_name=?,rule_name=?,collection_type=?,category_name=?,media_name=?,source_name=?,inventory_number=?,status=?,status_label=?,is_public=?,deleted_at=NULL WHERE id=?', array_merge($itemFields, [$itemId]));
			$summary['items_updated']++;
		} else {
			query($mysqli, 'INSERT INTO book_items (book_id,source_system,source_id,item_code,barcode,call_number,location_name,location_library_name,location_room_name,rule_name,collection_type,category_name,media_name,source_name,inventory_number,status,status_label,is_public) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', array_merge([$bookId, PG_SOURCE_SYSTEM, $itemSourceId], array_slice($itemFields, 1)));
			$summary['items_created']++;
		}
		$mysqli->commit();
	} catch (Throwable $e) {
		$mysqli->rollback();
		$summary['failed']++;
		fwrite(STDERR, '  gagal: ' . $e->getMessage() . PHP_EOL);
	}
}

printf("\n%s\n", json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

function validPdf(string $path): bool {
	return is_file($path) && filesize($path) > 1000 && file_get_contents($path, false, null, 0, 4) === '%PDF';
}

function runWeasyPrint(string $url, string $output): int {
	$process = proc_open(['timeout', '120s', 'weasyprint', '--presentational-hints', $url, $output], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
	if (! is_resource($process)) throw new RuntimeException('WeasyPrint tidak dapat dijalankan.');
	stream_get_contents($pipes[1]);
	$error = trim(stream_get_contents($pipes[2]));
	fclose($pipes[1]); fclose($pipes[2]);
	$status = proc_close($process);
	if ($status !== 0 && $error !== '') fwrite(STDERR, '  renderer: ' . $error . PHP_EOL);
	return $status;
}

function buildLocalSourceHtml(int $id, string $output): void {
	// Project Gutenberg mengarahkan unduhan berulang ke mirror/rsync. Ambil hanya
	// satu berkas HTML per judul dari mirror resmi, lalu render sepenuhnya lokal.
	$source = 'gutenberg.pglaf.org::gutenberg-epub/' . $id . '/pg' . $id . '-images.html';
	$tmp = $output . '.download';
	@unlink($tmp);
	$process = proc_open(['rsync', '--timeout=60', $source, $tmp], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
	if (! is_resource($process)) throw new RuntimeException('Mirror Gutenberg tidak dapat dijalankan.');
	stream_get_contents($pipes[1]);
	stream_get_contents($pipes[2]);
	fclose($pipes[1]); fclose($pipes[2]);
	if (proc_close($process) !== 0 || ! is_file($tmp)) throw new RuntimeException('Sumber Gutenberg tidak dapat diunduh dari mirror.');
	$html = file_get_contents($tmp);
	@unlink($tmp);
	if (! is_string($html) || $html === '') throw new RuntimeException('Sumber Gutenberg kosong.');
	$dom = new DOMDocument();
	libxml_use_internal_errors(true);
	$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
	libxml_clear_errors();
	$xpath = new DOMXPath($dom);
	foreach ($xpath->query('//script|//style|//link|//img|//svg|//noscript|//nav') as $node) $node->parentNode?->removeChild($node);
	$body = $xpath->query('//body')->item(0);
	$content = $body ? $dom->saveHTML($body) : $dom->saveHTML();
	$document = '<!doctype html><html><head><meta charset="utf-8"><style>'
		. '@page { size: A4; margin: 18mm 17mm; } body { font-family: serif; font-size: 11pt; line-height: 1.45; color: #111; } h1,h2,h3 { page-break-after: avoid; } p { margin: 0 0 .7em; } .pgheader,.pgfooter { font-size: 8pt; color: #666; }'
		. '</style></head>' . $content . '</html>';
	if (file_put_contents($output, $document) === false) throw new RuntimeException('Sumber lokal tidak dapat disimpan.');
}

function findId(mysqli $mysqli, string $table, string $sourceId): int {
	$stmt = $mysqli->prepare("SELECT id FROM {$table} WHERE source_system=? AND source_id=? LIMIT 1");
	$system = PG_SOURCE_SYSTEM;
	$stmt->bind_param('ss', $system, $sourceId);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_assoc();
	return (int) ($row['id'] ?? 0);
}

function query(mysqli $mysqli, string $sql, array $values): void {
	$stmt = $mysqli->prepare($sql);
	$types = '';
	foreach ($values as $value) $types .= is_int($value) ? 'i' : 's';
	$refs = [];
	foreach ($values as $key => $value) $refs[$key] = &$values[$key];
	$stmt->bind_param($types, ...$refs);
	$stmt->execute();
}
