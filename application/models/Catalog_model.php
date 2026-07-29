<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Catalog_model extends CI_Model
{
	const SOURCE_SYSTEM = 'inlislite_v3';

	public function stats($scope_library_id = null)
	{
		$books = $this->db->table_exists('books') ? (int) $this->db->count_all('books') : 0;

		if ($this->db->table_exists('book_items')) {
			if (! empty($scope_library_id)) {
				$this->db->where('library_id', (int) $scope_library_id);
			}
			$items = (int) $this->db->count_all_results('book_items');
		} else {
			$items = 0;
		}

		$digital_assets = $this->db->table_exists('digital_assets') ? (int) $this->db->count_all('digital_assets') : 0;
		$sync_runs = $this->db->table_exists('catalog_sync_runs') ? (int) $this->db->count_all('catalog_sync_runs') : 0;

		return [
			'books' => $books,
			'items' => $items,
			'digital_assets' => $digital_assets,
			'sync_runs' => $sync_runs,
		];
	}

	public function recent_books($limit = 12, $scope_library_id = null)
	{
		if (! $this->db->table_exists('books')) {
			return [];
		}

		$this->db
			->select('b.*')
			->from('books b');

		if (! empty($scope_library_id)) {
			$this->db
				->join('book_items i', 'i.book_id = b.id')
				->where('i.library_id', (int) $scope_library_id)
				->group_by('b.id');
		}

		return $this->db
			->order_by('b.id', 'DESC')
			->limit(max(1, min(50, (int) $limit)))
			->get()
			->result_array();
	}

	public function count_books(array $filters = [], $scope_library_id = null)
	{
		if (! $this->db->table_exists('books')) {
			return 0;
		}

		$this->apply_book_filters($filters, $scope_library_id);

		$row = $this->db
			->select('COUNT(DISTINCT b.id) AS total', false)
			->get()
			->row_array();

		return (int) ($row['total'] ?? 0);
	}

	public function get_books(array $filters = [], $limit = 25, $offset = 0, $scope_library_id = null)
	{
		if (! $this->db->table_exists('books')) {
			return [];
		}

		$this->apply_book_filters($filters, $scope_library_id);

		return $this->db
			->select('b.*, COUNT(DISTINCT i.id) AS item_count')
			->group_by('b.id')
			->order_by('b.id', 'DESC')
			->limit(max(1, min(100, (int) $limit)), max(0, (int) $offset))
			->get()
			->result_array();
	}

	public function get_book($id, $scope_library_id = null)
	{
		if (! $this->db->table_exists('books')) {
			return null;
		}

		$this->db
			->select('b.*, COUNT(DISTINCT i.id) AS item_count')
			->from('books b')
			->join('book_items i', 'i.book_id = b.id', 'left')
			->where('b.id', (int) $id);

		if (! empty($scope_library_id)) {
			$this->db->where('i.library_id', (int) $scope_library_id);
		}

		return $this->db
			->group_by('b.id')
			->get()
			->row_array();
	}

	public function get_book_authors($book_id)
	{
		return $this->db
			->from('book_authors')
			->where('book_id', (int) $book_id)
			->order_by('sort_order', 'ASC')
			->get()
			->result_array();
	}

	public function get_book_subjects($book_id)
	{
		return $this->db
			->from('book_subjects')
			->where('book_id', (int) $book_id)
			->order_by('subject', 'ASC')
			->get()
			->result_array();
	}

	public function get_book_items($book_id, $limit = 20, $scope_library_id = null)
	{
		$this->db
			->from('book_items')
			->where('book_id', (int) $book_id)
			->order_by('id', 'ASC')
			->limit(max(1, min(100, (int) $limit)));

		if (! empty($scope_library_id)) {
			$this->db->where('library_id', (int) $scope_library_id);
		}

		return $this->db->get()->result_array();
	}

	private function apply_book_filters(array $filters = [], $scope_library_id = null)
	{
		$this->db
			->from('books b')
			->join('book_items i', 'i.book_id = b.id', 'left');

		if (! empty($scope_library_id)) {
			$this->db->where('i.library_id', (int) $scope_library_id);
		}

		$q = trim((string) ($filters['q'] ?? ''));
		if ($q !== '') {
			$this->db->group_start()
				->like('b.title', $q)
				->or_like('b.statement_responsibility', $q)
				->or_like('b.publisher', $q)
				->or_like('b.isbn', $q)
				->or_like('b.call_number', $q)
				->or_like('i.barcode', $q)
				->group_end();
		}

		$status = trim((string) ($filters['status'] ?? ''));
		if (in_array($status, ['draft', 'published', 'hidden'], true)) {
			$this->db->where('b.status', $status);
		}

		$year = trim((string) ($filters['publish_year'] ?? ''));
		if ($year !== '') {
			$this->db->where('b.publish_year', $year);
		}
	}

	public function recent_sync_runs($limit = 8)
	{
		if (! $this->db->table_exists('catalog_sync_runs')) {
			return [];
		}

		return $this->db
			->from('catalog_sync_runs')
			->order_by('id', 'DESC')
			->limit(max(1, min(20, (int) $limit)))
			->get()
			->result_array();
	}

	public function source_stats()
	{
		$source_db = $this->load->database('inlislite', true);
		$tables = [
			'catalogs' => 'Bibliografi',
			'collections' => 'Eksemplar',
			'worksheets' => 'Worksheet',
			'catalogfiles' => 'File katalog',
			'catalog_ruas' => 'Metadata ruas',
		];

		$result = [];
		foreach ($tables as $table => $label) {
			$result[$table] = [
				'label' => $label,
				'value' => $source_db->table_exists($table) ? (int) $source_db->count_all($table) : 0,
			];
		}

		return $result;
	}

	public function run_manual_sync($created_by = null, $limit = 500, $mode = 'import_new')
	{
		$limit = max(1, min(2000, (int) $limit));
		$mode = in_array($mode, ['import_new', 'refresh_existing', 'dry_run'], true) ? $mode : 'import_new';
		$sync_type = $mode === 'dry_run' ? 'dry_run' : 'manual';
		$run_id = $this->create_sync_run('catalogs,collections', $created_by, $sync_type);
		$inserted = 0;
		$updated = 0;
		$failed = 0;
		$items_inserted = 0;
		$items_updated = 0;

		if ($mode === 'dry_run') {
			$potential_new = $this->remaining_source_count();
			$potential_refresh = $this->existing_source_count();
			$dry_total = min($limit, $potential_new);
			$message = sprintf(
				'Dry run katalog selesai. Kandidat data baru: %d, kandidat update lama: %d, batch yang akan diproses pada import data baru: %d.',
				$potential_new,
				$potential_refresh,
				$dry_total
			);
			$this->finish_sync_run($run_id, $dry_total, 0, 0, 0, $message);

			return [
				'run_id' => $run_id,
				'processed' => $dry_total,
				'inserted' => 0,
				'updated' => 0,
				'failed' => 0,
				'remaining' => $potential_new,
				'message' => $message,
			];
		}

		$rows = $this->next_catalog_rows($limit, $mode);

		foreach ($rows as $row) {
			try {
				$result = $this->upsert_book_from_inlislite($row, $created_by, $run_id);
				$inserted += $result['inserted'];
				$updated += $result['updated'];
				$items = $this->sync_items_for_catalog($row['ID'], $result['book_id'], $run_id);
				$items_inserted += $items['inserted'];
				$items_updated += $items['updated'];
			} catch (Throwable $e) {
				$failed++;
			}
		}

		$remaining = $this->remaining_source_count();
		$message = sprintf(
			'Batch katalog %s selesai. Buku baru: %d, buku update: %d, eksemplar baru: %d, eksemplar update: %d, gagal: %d, sisa belum masuk: %d.',
			$mode === 'refresh_existing' ? 'update data lama' : 'import data baru',
			$inserted,
			$updated,
			$items_inserted,
			$items_updated,
			$failed,
			$remaining
		);

		$this->finish_sync_run($run_id, count($rows), $inserted + $items_inserted, $updated + $items_updated, $failed, $message);

		return [
			'run_id' => $run_id,
			'processed' => count($rows),
			'inserted' => $inserted + $items_inserted,
			'updated' => $updated + $items_updated,
			'failed' => $failed,
			'remaining' => $remaining,
			'message' => $message,
		];
	}

	private function create_sync_run($source_table, $created_by, $sync_type = 'manual')
	{
		$this->db->insert('catalog_sync_runs', [
			'source_database' => self::SOURCE_SYSTEM,
			'source_table' => $source_table,
			'sync_type' => $sync_type,
			'status' => 'running',
			'started_at' => date('Y-m-d H:i:s'),
			'created_by' => $created_by ? (int) $created_by : null,
		]);

		return (int) $this->db->insert_id();
	}

	private function finish_sync_run($run_id, $total_source, $inserted, $updated, $failed, $message)
	{
		$this->db
			->where('id', (int) $run_id)
			->update('catalog_sync_runs', [
				'status' => $failed > 0 ? 'failed' : 'success',
				'finished_at' => date('Y-m-d H:i:s'),
				'total_source' => (int) $total_source,
				'total_inserted' => (int) $inserted,
				'total_updated' => (int) $updated,
				'total_failed' => (int) $failed,
				'message' => $message,
			]);
	}

	private function next_catalog_rows($limit, $mode = 'import_new')
	{
		$condition = $mode === 'refresh_existing' ? 'b.id IS NOT NULL' : 'b.id IS NULL';

		return $this->db
			->query(
				"SELECT c.*
				FROM inlislite_v3.catalogs c
				LEFT JOIN books b
					ON b.source_system = ?
					AND b.source_id = CONVERT(CAST(c.ID AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
				WHERE " . $condition . "
				ORDER BY c.ID ASC
				LIMIT " . (int) $limit,
				[self::SOURCE_SYSTEM]
			)
			->result_array();
	}

	private function remaining_source_count()
	{
		$row = $this->db
			->query(
				"SELECT COUNT(*) AS total
				FROM inlislite_v3.catalogs c
				LEFT JOIN books b
					ON b.source_system = ?
					AND b.source_id = CONVERT(CAST(c.ID AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
				WHERE b.id IS NULL",
				[self::SOURCE_SYSTEM]
			)
			->row_array();

		return (int) ($row['total'] ?? 0);
	}

	private function existing_source_count()
	{
		$row = $this->db
			->query(
				"SELECT COUNT(*) AS total
				FROM inlislite_v3.catalogs c
				JOIN books b
					ON b.source_system = ?
					AND b.source_id = CONVERT(CAST(c.ID AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci",
				[self::SOURCE_SYSTEM]
			)
			->row_array();

		return (int) ($row['total'] ?? 0);
	}

	private function upsert_book_from_inlislite(array $row, $created_by, $run_id)
	{
		$source_id = $this->source_id($row['ID']);
		$payload = [
			'source_system' => self::SOURCE_SYSTEM,
			'source_id' => $source_id,
			'title' => $this->clip($row['Title'] ?: 'Tanpa Judul', 255),
			'statement_responsibility' => $this->clip($row['Author'] ?? null, 255),
			'edition' => $this->clip($row['Edition'] ?? null, 120),
			'publish_place' => $this->clip($row['PublishLocation'] ?? null, 160),
			'publisher' => $this->clip($row['Publisher'] ?? null, 180),
			'publish_year' => $this->clip($row['PublishYear'] ?? null, 20),
			'isbn' => $this->clip($row['ISBN'] ?? null, 80),
			'classification' => $this->clip($row['DeweyNo'] ?? null, 80),
			'call_number' => $this->clip($row['CallNumber'] ?? null, 120),
			'language' => $this->clip($row['Languages'] ?? null, 60),
			'physical_description' => $this->clip($row['PhysicalDescription'] ?? null, 255),
			'abstract' => $this->blank_to_null($row['Note'] ?? null),
			'cover_path' => $this->clip($row['CoverURL'] ?? null, 255),
			'status' => (int) ($row['IsOPAC'] ?? 0) === 1 ? 'published' : 'hidden',
			'updated_by' => $created_by ? (int) $created_by : null,
		];

		$existing = $this->db
			->from('books')
			->where('source_system', self::SOURCE_SYSTEM)
			->where('source_id', $source_id)
			->get()
			->row_array();

		if ($existing) {
			$this->db->where('id', (int) $existing['id'])->update('books', $payload);
			$book_id = (int) $existing['id'];
			$inserted = 0;
			$updated = 1;
		} else {
			$payload['created_by'] = $created_by ? (int) $created_by : null;
			$this->db->insert('books', $payload);
			$book_id = (int) $this->db->insert_id();
			$inserted = 1;
			$updated = 0;
		}

		$this->sync_book_terms($book_id, $row);
		$this->upsert_sync_map('book', 'catalogs', $source_id, $book_id, $run_id);

		return [
			'book_id' => $book_id,
			'inserted' => $inserted,
			'updated' => $updated,
		];
	}

	private function sync_book_terms($book_id, array $row)
	{
		$this->db->where('book_id', (int) $book_id)->delete('book_authors');
		$this->db->where('book_id', (int) $book_id)->delete('book_subjects');

		foreach ($this->split_terms($row['Author'] ?? '') as $index => $author) {
			$this->db->insert('book_authors', [
				'book_id' => (int) $book_id,
				'name' => $this->clip($author, 180),
				'role' => 'author',
				'sort_order' => ($index + 1) * 10,
			]);
		}

		foreach ($this->split_terms($row['Subject'] ?? '') as $subject) {
			$this->db->insert('book_subjects', [
				'book_id' => (int) $book_id,
				'subject' => $this->clip($subject, 180),
			]);
		}
	}

	private function sync_items_for_catalog($source_catalog_id, $book_id, $run_id)
	{
		$source_db = $this->load->database('inlislite', true);
		$items = $source_db
			->from('collections')
			->where('Catalog_id', $source_catalog_id)
			->order_by('ID', 'ASC')
			->get()
			->result_array();

		$inserted = 0;
		$updated = 0;
		foreach ($items as $item) {
			$source_id = $this->source_id($item['ID']);
			$payload = [
				'book_id' => (int) $book_id,
				'source_system' => self::SOURCE_SYSTEM,
				'source_id' => $source_id,
				'item_code' => $this->clip($item['NoInduk'] ?? null, 120),
				'barcode' => $this->clip($item['NomorBarcode'] ?? null, 120),
				'call_number' => $this->clip($item['CallNumber'] ?? null, 120),
				'location_name' => $this->clip($item['Location_Library_id'] ?? null, 180),
				'collection_type' => $this->clip($item['Category_id'] ?? null, 120),
				'inventory_number' => $this->clip($item['NoInduk'] ?? null, 120),
				'status' => $this->map_item_status($item['Status_id'] ?? null),
			];

			$existing = $this->db
				->from('book_items')
				->where('source_system', self::SOURCE_SYSTEM)
				->where('source_id', $source_id)
				->get()
				->row_array();

			if ($existing) {
				$this->db->where('id', (int) $existing['id'])->update('book_items', $payload);
				$item_id = (int) $existing['id'];
				$updated++;
			} else {
				$this->db->insert('book_items', $payload);
				$item_id = (int) $this->db->insert_id();
				$inserted++;
			}

			$this->upsert_sync_map('book_item', 'collections', $source_id, $item_id, $run_id);
		}

		return [
			'inserted' => $inserted,
			'updated' => $updated,
		];
	}

	private function upsert_sync_map($entity_type, $source_table, $source_id, $target_id, $run_id)
	{
		$existing = $this->db
			->from('catalog_sync_maps')
			->where('entity_type', $entity_type)
			->where('source_system', self::SOURCE_SYSTEM)
			->where('source_table', $source_table)
			->where('source_id', $source_id)
			->get()
			->row_array();

		$payload = [
			'target_id' => (int) $target_id,
			'last_sync_run_id' => (int) $run_id,
		];

		if ($existing) {
			$this->db->where('id', (int) $existing['id'])->update('catalog_sync_maps', $payload);
			return;
		}

		$payload += [
			'entity_type' => $entity_type,
			'source_system' => self::SOURCE_SYSTEM,
			'source_table' => $source_table,
			'source_id' => $source_id,
		];
		$this->db->insert('catalog_sync_maps', $payload);
	}

	private function map_item_status($status_id)
	{
		switch ((int) $status_id) {
			case 1:
				return 'available';
			case 2:
			case 3:
				return 'loaned';
			case 4:
				return 'damaged';
			case 5:
				return 'missing';
			default:
				return 'unknown';
		}
	}

	private function split_terms($value)
	{
		$value = trim((string) $value);
		if ($value === '') {
			return [];
		}

		$parts = preg_split('/[;|]+/', $value);
		$parts = array_filter(array_map('trim', $parts), function ($part) {
			return $part !== '';
		});

		return array_values(array_slice(array_unique($parts), 0, 8));
	}

	private function source_id($value)
	{
		return (string) (int) $value;
	}

	private function clip($value, $length)
	{
		$value = $this->blank_to_null($value);
		if ($value === null) {
			return null;
		}

		return function_exists('mb_substr') ? mb_substr($value, 0, (int) $length) : substr($value, 0, (int) $length);
	}

	private function blank_to_null($value)
	{
		$value = trim((string) $value);
		return $value === '' ? null : $value;
	}
}
