<?php
/** Mengambil cover resmi Project Gutenberg dari mirror untuk koleksi yang sudah diimpor. */
declare(strict_types=1);

const PG_COVER_SOURCE = 'project_gutenberg';
const PG_COVER_DIR = 'assets/uploads/project-gutenberg/covers';

$projectRoot = dirname(__DIR__);
define('BASEPATH', $projectRoot . '/system/');
defined('ENVIRONMENT') || define('ENVIRONMENT', 'production');
$db = [];
require $projectRoot . '/application/config/database.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$mysqli = mysqli_init();
$mysqli->real_connect($db['default']['hostname'], $db['default']['username'], $db['default']['password'], $db['default']['database']);
$mysqli->set_charset('utf8mb4');
$rows = $mysqli->query("SELECT id, source_id, title FROM books WHERE source_system = 'project_gutenberg' ORDER BY id")->fetch_all(MYSQLI_ASSOC);
$summary = ['requested' => count($rows), 'downloaded' => 0, 'reused' => 0, 'missing' => 0, 'failed' => 0];

foreach ($rows as $index => $book) {
	if (! preg_match('/^gutenberg-(\d+)$/', (string) $book['source_id'], $match)) continue;
	$gutenbergId = (int) $match[1];
	$relative = PG_COVER_DIR . '/pg-' . $gutenbergId . '.jpg';
	$target = $projectRoot . '/' . $relative;
	printf("[%d/%d] %s\n", $index + 1, count($rows), $book['title']);
	try {
		if (validImage($target)) {
			$summary['reused']++;
		} else {
			$dir = dirname($target);
			if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) throw new RuntimeException('Folder cover tidak dapat dibuat.');
			$tmp = $target . '.part';
			@unlink($tmp);
			$source = 'gutenberg.pglaf.org::gutenberg-epub/' . $gutenbergId . '/pg' . $gutenbergId . '.cover.medium.jpg';
			if (runRsync($source, $tmp) !== 0 || ! validImage($tmp)) {
				@unlink($tmp);
				$summary['missing']++;
				continue;
			}
			rename($tmp, $target);
			$summary['downloaded']++;
		}
		$stmt = $mysqli->prepare('UPDATE books SET cover_local_path=?, updated_at=NOW() WHERE id=?');
		$stmt->bind_param('si', $relative, $book['id']);
		$stmt->execute();
	} catch (Throwable $e) {
		$summary['failed']++;
		fwrite(STDERR, '  gagal: ' . $e->getMessage() . PHP_EOL);
	}
}
printf("\n%s\n", json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

function runRsync(string $source, string $destination): int {
	$process = proc_open(['timeout', '60s', 'rsync', '--timeout=45', $source, $destination], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
	if (! is_resource($process)) throw new RuntimeException('Mirror Gutenberg tidak dapat dijalankan.');
	stream_get_contents($pipes[1]);
	stream_get_contents($pipes[2]);
	fclose($pipes[1]); fclose($pipes[2]);
	return proc_close($process);
}

function validImage(string $path): bool {
	if (! is_file($path) || filesize($path) < 1000) return false;
	$info = @getimagesize($path);
	return is_array($info) && ($info[2] ?? 0) === IMAGETYPE_JPEG;
}
