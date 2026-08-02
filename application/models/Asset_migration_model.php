<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Asset_migration_model extends CI_Model
{
	const SOURCE_SYSTEM = 'inlislite_v3';

	private $status_keys = ['pending', 'copied', 'missing', 'failed', 'skipped'];

	public function summary()
	{
		return [
			'source_root' => $this->source_root(),
			'source_root_exists' => is_dir($this->source_root()),
			'target_root' => $this->target_root(),
			'source_files' => [
				'covers' => $this->count_files($this->source_root('sampul_koleksi' . DIRECTORY_SEPARATOR . 'original')),
				'member_photos' => $this->count_files($this->source_root('foto_anggota')),
				'digital_files' => $this->count_files($this->source_root('dokumen_isi')),
			],
			'target_files' => [
				'covers' => $this->count_files($this->target_root('covers')),
				'member_photos' => $this->count_files($this->target_root('member_photos')),
				'digital_files' => $this->count_files($this->target_root('digital_files')),
			],
			'covers' => $this->status_counts('books', 'cover_migration_status', 'cover_path'),
			'member_photos' => $this->status_counts('members', 'photo_migration_status', 'photo_path'),
			'digital_files' => $this->digital_status_counts(),
			'runs' => $this->recent_runs(12),
			'recent_issues' => $this->recent_items(['missing', 'failed'], 15),
		];
	}

	public function recent_runs($limit = 10)
	{
		if (! $this->db->table_exists('asset_migration_runs')) {
			return [];
		}

		return $this->db
			->from('asset_migration_runs')
			->order_by('id', 'DESC')
			->limit(max(1, min(30, (int) $limit)))
			->get()
			->result_array();
	}

	public function recent_items(array $statuses = [], $limit = 15)
	{
		if (! $this->db->table_exists('asset_migration_items')) {
			return [];
		}

		$this->db
			->from('asset_migration_items')
			->order_by('id', 'DESC')
			->limit(max(1, min(50, (int) $limit)));

		if (! empty($statuses)) {
			$this->db->where_in('status', $statuses);
		}

		return $this->db->get()->result_array();
	}

	public function run_manual_migration($created_by = null, $asset_type = 'cover', $limit = 500, $mode = 'copy_missing')
	{
		$asset_type = in_array($asset_type, ['all', 'cover', 'member_photo', 'digital_file'], true) ? $asset_type : 'cover';
		$mode = in_array($mode, ['copy_missing', 'refresh_existing', 'dry_run'], true) ? $mode : 'copy_missing';
		$limit = max(1, min(5000, (int) $limit));

		$this->ensure_target_dirs();

		$run_id = $this->create_run($created_by, $asset_type, $limit, $mode);
		$stats = ['source' => 0, 'copied' => 0, 'skipped' => 0, 'missing' => 0, 'failed' => 0];

		if ($mode === 'dry_run') {
			$stats['source'] = $this->count_candidates($asset_type, 'copy_missing');
			$message = sprintf('Dry run migrasi aset selesai. Kandidat file yang perlu diproses: %d.', $stats['source']);
			$this->finish_run($run_id, 'success', $stats, $message);

			return [
				'run_id' => $run_id,
				'message' => $message,
			] + $stats;
		}

		foreach ($this->type_sequence($asset_type) as $type) {
			$remaining = $limit - $stats['source'];
			if ($remaining <= 0) {
				break;
			}

			$result = $this->process_type($type, $remaining, $mode, $run_id);
			foreach ($stats as $key => $value) {
				$stats[$key] += (int) ($result[$key] ?? 0);
			}
		}

		$status = $stats['failed'] > 0 ? 'failed' : 'success';
		$message = sprintf(
			'Migrasi aset selesai. Diproses: %d, tersalin: %d, sudah ada: %d, hilang: %d, gagal: %d.',
			$stats['source'],
			$stats['copied'],
			$stats['skipped'],
			$stats['missing'],
			$stats['failed']
		);
		$this->finish_run($run_id, $status, $stats, $message);

		return [
			'run_id' => $run_id,
			'message' => $message,
		] + $stats;
	}

	private function process_type($asset_type, $limit, $mode, $run_id)
	{
		if ($asset_type === 'cover') {
			return $this->process_covers($limit, $mode, $run_id);
		}

		if ($asset_type === 'member_photo') {
			return $this->process_member_photos($limit, $mode, $run_id);
		}

		return $this->process_digital_files($limit, $mode, $run_id);
	}

	private function process_covers($limit, $mode, $run_id)
	{
		$stats = ['source' => 0, 'copied' => 0, 'skipped' => 0, 'missing' => 0, 'failed' => 0];
		$condition = $mode === 'refresh_existing'
			? "b.cover_path IS NOT NULL AND b.cover_path <> ''"
			: "b.cover_path IS NOT NULL AND b.cover_path <> '' AND b.cover_migration_status IN ('pending','failed')";

		$rows = $this->db
			->query(
				"SELECT b.id, b.source_id, b.cover_path, w.Name AS worksheet_name
				FROM books b
				LEFT JOIN inlislite_v3.catalogs c
					ON b.source_system = ?
					AND b.source_id = CONVERT(CAST(c.ID AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
				LEFT JOIN inlislite_v3.worksheets w ON w.ID = c.Worksheet_id
				WHERE " . $condition . "
				ORDER BY b.id ASC
				LIMIT " . (int) $limit,
				[self::SOURCE_SYSTEM]
			)
			->result_array();

		foreach ($rows as $row) {
			$stats['source']++;
			$result = $this->copy_cover($row, $mode === 'refresh_existing');
			$this->update_book_asset_status((int) $row['id'], $result);
			$this->log_item($run_id, 'cover', 'book', (int) $row['id'], (string) $row['source_id'], $result);
			$stats[$result['status']]++;
		}

		return $stats;
	}

	private function process_member_photos($limit, $mode, $run_id)
	{
		$stats = ['source' => 0, 'copied' => 0, 'skipped' => 0, 'missing' => 0, 'failed' => 0];
		$condition = $mode === 'refresh_existing'
			? "photo_path IS NOT NULL AND photo_path <> ''"
			: "photo_path IS NOT NULL AND photo_path <> '' AND photo_migration_status IN ('pending','failed')";

		$rows = $this->db
			->from('members')
			->where($condition, null, false)
			->order_by('id', 'ASC')
			->limit((int) $limit)
			->get()
			->result_array();

		foreach ($rows as $row) {
			$stats['source']++;
			$result = $this->copy_member_photo($row, $mode === 'refresh_existing');
			$this->update_member_asset_status((int) $row['id'], $result);
			$this->log_item($run_id, 'member_photo', 'member', (int) $row['id'], (string) $row['source_id'], $result);
			$stats[$result['status']]++;
		}

		return $stats;
	}

	private function process_digital_files($limit, $mode, $run_id)
	{
		$stats = ['source' => 0, 'copied' => 0, 'skipped' => 0, 'missing' => 0, 'failed' => 0];
		$base = $this->source_root('dokumen_isi');
		if (! is_dir($base)) {
			return $stats;
		}

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS)
		);

		foreach ($iterator as $file) {
			if (! $file->isFile()) {
				continue;
			}

			$source_path = $this->source_relative_path($file->getPathname());
			if ($mode !== 'refresh_existing' && $this->already_migrated_digital_file($source_path)) {
				continue;
			}

			$stats['source']++;
			$relative = $this->relative_to($base, $file->getPathname());
			$target_path = 'assets/uploads/inlislite/digital_files/' . $this->sanitize_relative_path($relative);
			$result = $this->copy_file($file->getPathname(), $target_path, $mode === 'refresh_existing');
			$result['source_path'] = $source_path;
			$this->log_item($run_id, 'digital_file', 'digital_file', null, sha1($source_path), $result);
			$stats[$result['status']]++;

			if ($stats['source'] >= (int) $limit) {
				break;
			}
		}

		return $stats;
	}

	private function copy_cover(array $row, $overwrite = false)
	{
		$filename = $this->safe_basename($row['cover_path'] ?? '');
		$worksheet = trim((string) ($row['worksheet_name'] ?? ''));
		$worksheet = $worksheet !== '' ? $worksheet : 'Monograf';
		$source = $this->source_root('sampul_koleksi' . DIRECTORY_SEPARATOR . 'original' . DIRECTORY_SEPARATOR . $worksheet . DIRECTORY_SEPARATOR . $filename);

		if (! is_file($source)) {
			$found = $this->find_file_in_direct_subdirs($this->source_root('sampul_koleksi' . DIRECTORY_SEPARATOR . 'original'), $filename);
			$source = $found ?: $source;
		}

		$target = 'assets/uploads/inlislite/covers/' . $this->slug($worksheet) . '/' . $this->source_prefix($row['source_id'] ?? $row['id']) . '_' . $this->sanitize_filename($filename);
		$result = $this->copy_file($source, $target, $overwrite);
		$result['source_path'] = $this->source_relative_path($source);

		return $result;
	}

	private function copy_member_photo(array $row, $overwrite = false)
	{
		$filename = $this->safe_basename($row['photo_path'] ?? '');
		$source = $this->source_root('foto_anggota' . DIRECTORY_SEPARATOR . $filename);

		if (! is_file($source)) {
			$source = $this->find_member_photo_fallback((string) ($row['source_id'] ?? ''), $filename) ?: $source;
		}

		$target = 'assets/uploads/inlislite/member_photos/' . $this->source_prefix($row['source_id'] ?? $row['id']) . '_' . $this->sanitize_filename($filename);
		$result = $this->copy_file($source, $target, $overwrite);
		$result['source_path'] = $this->source_relative_path($source);

		return $result;
	}

	private function copy_file($source_abs, $target_relative, $overwrite = false)
	{
		$target_abs = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $target_relative);
		if (! is_file($source_abs)) {
			return [
				'status' => 'missing',
				'source_path' => $this->source_relative_path($source_abs),
				'local_path' => null,
				'file_size' => null,
				'error_message' => 'File sumber tidak ditemukan.',
			];
		}

		$target_dir = dirname($target_abs);
		if (! is_dir($target_dir) && ! @mkdir($target_dir, 0775, true)) {
			return [
				'status' => 'failed',
				'source_path' => $this->source_relative_path($source_abs),
				'local_path' => $target_relative,
				'file_size' => @filesize($source_abs) ?: null,
				'error_message' => 'Folder tujuan tidak bisa dibuat.',
			];
		}

		$source_size = @filesize($source_abs) ?: 0;
		if (! $overwrite && is_file($target_abs) && (@filesize($target_abs) ?: 0) === $source_size) {
			return [
				'status' => 'skipped',
				'source_path' => $this->source_relative_path($source_abs),
				'local_path' => $target_relative,
				'file_size' => $source_size,
				'error_message' => null,
			];
		}

		if (! @copy($source_abs, $target_abs)) {
			return [
				'status' => 'failed',
				'source_path' => $this->source_relative_path($source_abs),
				'local_path' => $target_relative,
				'file_size' => $source_size,
				'error_message' => 'Gagal menyalin file.',
			];
		}

		return [
			'status' => 'copied',
			'source_path' => $this->source_relative_path($source_abs),
			'local_path' => $target_relative,
			'file_size' => $source_size,
			'error_message' => null,
		];
	}

	private function update_book_asset_status($book_id, array $result)
	{
		$status = in_array($result['status'], ['copied', 'skipped'], true) ? 'copied' : $result['status'];
		$this->db
			->where('id', (int) $book_id)
			->update('books', [
				'cover_source_path' => $result['source_path'],
				'cover_local_path' => $status === 'copied' ? $result['local_path'] : null,
				'cover_migration_status' => $status,
				'cover_migrated_at' => date('Y-m-d H:i:s'),
			]);
	}

	private function update_member_asset_status($member_id, array $result)
	{
		$status = in_array($result['status'], ['copied', 'skipped'], true) ? 'copied' : $result['status'];
		$this->db
			->where('id', (int) $member_id)
			->update('members', [
				'photo_source_path' => $result['source_path'],
				'photo_local_path' => $status === 'copied' ? $result['local_path'] : null,
				'photo_migration_status' => $status,
				'photo_migrated_at' => date('Y-m-d H:i:s'),
			]);
	}

	private function log_item($run_id, $asset_type, $entity_type, $entity_id, $source_id, array $result)
	{
		$this->db->insert('asset_migration_items', [
			'run_id' => (int) $run_id,
			'asset_type' => $asset_type,
			'entity_type' => $entity_type,
			'entity_id' => $entity_id ? (int) $entity_id : null,
			'source_system' => self::SOURCE_SYSTEM,
			'source_id' => $source_id,
			'source_path' => $result['source_path'],
			'local_path' => $result['local_path'],
			'status' => $result['status'],
			'file_size' => $result['file_size'],
			'error_message' => $result['error_message'],
		]);
	}

	private function create_run($created_by, $asset_type, $limit, $mode)
	{
		$this->db->insert('asset_migration_runs', [
			'source_system' => self::SOURCE_SYSTEM,
			'asset_type' => $asset_type,
			'mode' => $mode,
			'status' => 'running',
			'source_root' => $this->source_root(),
			'target_root' => $this->target_root(),
			'started_at' => date('Y-m-d H:i:s'),
			'total_source' => (int) $limit,
			'created_by' => $created_by ? (int) $created_by : null,
		]);

		return (int) $this->db->insert_id();
	}

	private function finish_run($run_id, $status, array $stats, $message)
	{
		$this->db
			->where('id', (int) $run_id)
			->update('asset_migration_runs', [
				'status' => $status,
				'finished_at' => date('Y-m-d H:i:s'),
				'total_source' => (int) $stats['source'],
				'total_copied' => (int) $stats['copied'],
				'total_skipped' => (int) $stats['skipped'],
				'total_missing' => (int) $stats['missing'],
				'total_failed' => (int) $stats['failed'],
				'message' => $message,
			]);
	}

	private function status_counts($table, $status_column, $reference_column)
	{
		$counts = array_fill_keys($this->status_keys, 0);
		if (! $this->db->table_exists($table) || ! $this->db->field_exists($status_column, $table)) {
			return $counts + ['referenced' => 0];
		}

		$rows = $this->db
			->select($status_column . ' AS status, COUNT(*) AS total', false)
			->from($table)
			->where($reference_column . ' IS NOT NULL', null, false)
			->where($reference_column . ' <>', '')
			->group_by($status_column)
			->get()
			->result_array();

		$referenced = 0;
		foreach ($rows as $row) {
			$status = $row['status'];
			if (isset($counts[$status])) {
				$counts[$status] = (int) $row['total'];
				$referenced += (int) $row['total'];
			}
		}

		$counts['referenced'] = $referenced;
		return $counts;
	}

	private function digital_status_counts()
	{
		$counts = ['referenced' => $this->count_files($this->source_root('dokumen_isi')), 'copied' => 0, 'missing' => 0, 'failed' => 0, 'skipped' => 0, 'pending' => 0];
		if (! $this->db->table_exists('asset_migration_items')) {
			return $counts;
		}

		$rows = $this->db
			->select('status, COUNT(DISTINCT source_path) AS total', false)
			->from('asset_migration_items')
			->where('asset_type', 'digital_file')
			->group_by('status')
			->get()
			->result_array();

		foreach ($rows as $row) {
			if (isset($counts[$row['status']])) {
				$counts[$row['status']] = (int) $row['total'];
			}
		}
		$counts['pending'] = max(0, $counts['referenced'] - $counts['copied'] - $counts['skipped']);

		return $counts;
	}

	private function count_candidates($asset_type, $mode)
	{
		$total = 0;
		foreach ($this->type_sequence($asset_type) as $type) {
			if ($type === 'cover') {
				$condition = $mode === 'refresh_existing'
					? "cover_path IS NOT NULL AND cover_path <> ''"
					: "cover_path IS NOT NULL AND cover_path <> '' AND cover_migration_status IN ('pending','failed')";
				$total += (int) $this->db->from('books')->where($condition, null, false)->count_all_results();
			} elseif ($type === 'member_photo') {
				$condition = $mode === 'refresh_existing'
					? "photo_path IS NOT NULL AND photo_path <> ''"
					: "photo_path IS NOT NULL AND photo_path <> '' AND photo_migration_status IN ('pending','failed')";
				$total += (int) $this->db->from('members')->where($condition, null, false)->count_all_results();
			} else {
				$total += $this->count_unmigrated_digital_files($mode);
			}
		}

		return $total;
	}

	private function count_unmigrated_digital_files($mode)
	{
		$base = $this->source_root('dokumen_isi');
		if (! is_dir($base)) {
			return 0;
		}

		if ($mode === 'refresh_existing') {
			return $this->count_files($base);
		}

		$count = 0;
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS)
		);
		foreach ($iterator as $file) {
			if ($file->isFile() && ! $this->already_migrated_digital_file($this->source_relative_path($file->getPathname()))) {
				$count++;
			}
		}

		return $count;
	}

	private function already_migrated_digital_file($source_path)
	{
		if (! $this->db->table_exists('asset_migration_items')) {
			return false;
		}

		return (int) $this->db
			->from('asset_migration_items')
			->where('asset_type', 'digital_file')
			->where('source_path', $source_path)
			->where_in('status', ['copied', 'skipped'])
			->count_all_results() > 0;
	}

	private function type_sequence($asset_type)
	{
		return $asset_type === 'all' ? ['cover', 'member_photo', 'digital_file'] : [$asset_type];
	}

	private function ensure_target_dirs()
	{
		foreach (['covers', 'member_photos', 'digital_files', 'system'] as $dir) {
			$path = $this->target_root($dir);
			if (! is_dir($path)) {
				@mkdir($path, 0775, true);
			}
		}
	}

	private function source_root($append = '')
	{
		$base = realpath(dirname(FCPATH) . DIRECTORY_SEPARATOR . 'inlislite3' . DIRECTORY_SEPARATOR . 'uploaded_files');
		$base = $base ?: dirname(FCPATH) . DIRECTORY_SEPARATOR . 'inlislite3' . DIRECTORY_SEPARATOR . 'uploaded_files';
		return $append === '' ? $base : $base . DIRECTORY_SEPARATOR . $append;
	}

	private function target_root($append = '')
	{
		$base = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'inlislite';
		return $append === '' ? $base : $base . DIRECTORY_SEPARATOR . $append;
	}

	private function source_relative_path($path)
	{
		$path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, (string) $path);
		$root = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $this->source_root()), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		if (strpos($path, $root) === 0) {
			return 'uploaded_files/' . str_replace(DIRECTORY_SEPARATOR, '/', substr($path, strlen($root)));
		}

		return str_replace(DIRECTORY_SEPARATOR, '/', $path);
	}

	private function relative_to($base, $path)
	{
		$base = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $base), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		$path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
		return strpos($path, $base) === 0 ? substr($path, strlen($base)) : basename($path);
	}

	private function count_files($path)
	{
		if (! is_dir($path)) {
			return 0;
		}

		$count = 0;
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)
		);
		foreach ($iterator as $file) {
			if ($file->isFile()) {
				$count++;
			}
		}

		return $count;
	}

	private function find_file_by_name($base, $filename)
	{
		if ($filename === '' || ! is_dir($base)) {
			return null;
		}

		$candidate = $base . DIRECTORY_SEPARATOR . $filename;
		return is_file($candidate) ? $candidate : null;
	}

	private function find_file_in_direct_subdirs($base, $filename)
	{
		if ($filename === '' || ! is_dir($base)) {
			return null;
		}

		foreach (new DirectoryIterator($base) as $dir) {
			if ($dir->isDot() || ! $dir->isDir()) {
				continue;
			}

			$candidate = $dir->getPathname() . DIRECTORY_SEPARATOR . $filename;
			if (is_file($candidate)) {
				return $candidate;
			}
		}

		return null;
	}

	private function find_member_photo_fallback($source_id, $filename)
	{
		$base = $this->source_root('foto_anggota');
		if ($filename !== '') {
			$found = $this->find_file_by_name($base, $filename);
			if ($found) {
				return $found;
			}
		}

		$source_id = trim((string) $source_id);
		if ($source_id === '') {
			return null;
		}

		$direct = $base . DIRECTORY_SEPARATOR . $source_id;
		if (is_file($direct)) {
			return $direct;
		}

		foreach (glob($base . DIRECTORY_SEPARATOR . $source_id . '.*') ?: [] as $candidate) {
			if (is_file($candidate)) {
				return $candidate;
			}
		}

		return null;
	}

	private function safe_basename($filename)
	{
		return basename(str_replace('\\', '/', trim((string) $filename)));
	}

	private function sanitize_filename($filename)
	{
		$filename = $this->safe_basename($filename);
		$filename = preg_replace('/[^A-Za-z0-9._-]+/', '-', $filename);
		$filename = trim($filename, '.-');

		return $filename !== '' ? $filename : 'file';
	}

	private function sanitize_relative_path($path)
	{
		$segments = preg_split('#[\\\\/]+#', (string) $path);
		$segments = array_map([$this, 'sanitize_filename'], array_filter($segments, function ($segment) {
			return trim((string) $segment) !== '';
		}));

		return implode('/', $segments);
	}

	private function slug($value)
	{
		$value = strtolower(trim((string) $value));
		$value = preg_replace('/[^a-z0-9]+/', '-', $value);
		$value = trim($value, '-');

		return $value !== '' ? $value : 'unknown';
	}

	private function source_prefix($value)
	{
		$value = preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) $value);
		$value = trim($value, '-');

		return $value !== '' ? $value : 'source';
	}
}
