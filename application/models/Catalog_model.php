<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Catalog_model extends CI_Model
{
	const SOURCE_SYSTEM = 'inlislite_v3';

	public function stats($scope_library_id = null)
	{
		$books = 0;
		if ($this->db->table_exists('books')) {
			$books = (int) $this->db
				->from('books')
				->where('deleted_at IS NULL', null, false)
				->count_all_results();
		}

		if ($this->db->table_exists('book_items')) {
			if (! empty($scope_library_id)) {
				$this->db->where('library_id', (int) $scope_library_id);
			}
			$this->db->where('deleted_at IS NULL', null, false);
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
			->from('books b')
			->where('b.deleted_at IS NULL', null, false);

		if (! empty($scope_library_id)) {
			$this->db
				->join('book_items i', 'i.book_id = b.id AND i.deleted_at IS NULL')
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
			->select('b.*, cc.name AS content_category_name, cm.name AS content_classification_name, COUNT(DISTINCT i.id) AS item_count')
			->join('book_content_categories cc', 'cc.id = b.content_category_id', 'left')
			->join('book_classification_masters cm', 'cm.id = b.content_classification_id', 'left')
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
			->select('b.*, cc.name AS content_category_name, cm.name AS content_classification_name, COUNT(DISTINCT i.id) AS item_count')
			->from('books b')
			->join('book_items i', 'i.book_id = b.id AND i.deleted_at IS NULL', 'left')
			->join('book_content_categories cc', 'cc.id = b.content_category_id', 'left')
			->join('book_classification_masters cm', 'cm.id = b.content_classification_id', 'left')
			->where('b.id', (int) $id)
			->where('b.deleted_at IS NULL', null, false);

		if (! empty($scope_library_id)) {
			$this->db->where('i.library_id', (int) $scope_library_id);
		}

		return $this->db
			->group_by('b.id')
			->get()
			->row_array();
	}

	public function count_public_books(array $filters = [])
	{
		$this->apply_public_book_filters($filters);

		$row = $this->db
			->select('COUNT(DISTINCT b.id) AS total', false)
			->get()
			->row_array();

		return (int) ($row['total'] ?? 0);
	}

	public function get_public_books(array $filters = [], $limit = 12, $offset = 0)
	{
		$this->apply_public_book_filters($filters);

		return $this->db
			->select("b.*, cc.name AS content_category_name, cm.name AS content_classification_name, COUNT(DISTINCT i.id) AS public_item_count, SUM(CASE WHEN i.status = 'available' THEN 1 ELSE 0 END) AS available_count, COUNT(DISTINCT da.id) AS digital_asset_count, MIN(da.id) AS first_digital_asset_id", false)
			->join('digital_assets da', "da.book_id = b.id AND da.status = 'active'", 'left')
			->group_by('b.id')
			->order_by('b.title', 'ASC')
			->limit(max(1, min(48, (int) $limit)), max(0, (int) $offset))
			->get()
			->result_array();
	}

	public function get_public_book($id)
	{
		$this->db
			->select("b.*, cc.name AS content_category_name, cm.name AS content_classification_name, COUNT(DISTINCT i.id) AS public_item_count, SUM(CASE WHEN i.status = 'available' THEN 1 ELSE 0 END) AS available_count", false)
			->from('books b')
			->join('book_items i', 'i.book_id = b.id AND i.deleted_at IS NULL AND i.is_public = 1', 'left')
			->join('book_content_categories cc', 'cc.id = b.content_category_id', 'left')
			->join('book_classification_masters cm', 'cm.id = b.content_classification_id', 'left')
			->where('b.id', (int) $id)
			->where('b.status', 'published')
			->where('b.deleted_at IS NULL', null, false)
			->group_by('b.id');

		return $this->db->get()->row_array();
	}

	public function get_public_book_items($book_id, $limit = 50)
	{
		return $this->db
			->from('book_items')
			->where('book_id', (int) $book_id)
			->where('deleted_at IS NULL', null, false)
			->where('is_public', 1)
			->order_by('status', 'ASC')
			->order_by('location_name', 'ASC')
			->limit(max(1, min(100, (int) $limit)))
			->get()
			->result_array();
	}

	public function get_public_digital_assets($book_id)
	{
		if (! $this->db->table_exists('digital_assets')) {
			return [];
		}

		return $this->db
			->select('da.*, b.title, b.statement_responsibility')
			->from('digital_assets da')
			->join('books b', 'b.id = da.book_id', 'left')
			->where('da.book_id', (int) $book_id)
			->where('da.status', 'active')
			->where_in('da.access_policy', ['online_only', 'download_allowed', 'location_only', 'member_only'])
			->order_by('da.is_downloadable', 'DESC')
			->order_by('da.id', 'ASC')
			->get()
			->result_array();
	}

	public function get_book_digital_assets($book_id)
	{
		if (! $this->db->table_exists('digital_assets')) {
			return [];
		}

		return $this->db
			->from('digital_assets')
			->where('book_id', (int) $book_id)
			->order_by("FIELD(status, 'active', 'draft', 'archived')", '', false)
			->order_by('id', 'DESC')
			->get()
			->result_array();
	}

	public function get_member_digital_books($limit = 6)
	{
		if (! $this->db->table_exists('digital_assets')) {
			return [];
		}

		return $this->db
			->select('da.*, b.title, b.statement_responsibility, b.cover_local_path, b.cover_source_path, b.publish_year, cc.name AS content_category_name')
			->from('digital_assets da')
			->join('books b', 'b.id = da.book_id')
			->join('book_content_categories cc', 'cc.id = b.content_category_id', 'left')
			->where('da.status', 'active')
			->where('b.status', 'published')
			->where('b.deleted_at IS NULL', null, false)
			->where_in('da.access_policy', ['online_only', 'download_allowed', 'location_only', 'member_only'])
			->order_by('da.id', 'ASC')
			->limit(max(1, min(12, (int) $limit)))
			->get()
			->result_array();
	}

	public function public_filter_options(array $filters = [])
	{
		$option_query = function ($field, $limit = 40) {
			return $this->db
				->select($field . ' AS name, COUNT(DISTINCT b.id) AS total', false)
				->from('book_items i')
				->join('books b', 'b.id = i.book_id')
				->where('b.status', 'published')
				->where('b.deleted_at IS NULL', null, false)
				->where('i.deleted_at IS NULL', null, false)
				->where('i.is_public', 1)
				->where($field . ' IS NOT NULL', null, false)
				->where($field . ' <>', '')
				->group_by($field)
				->order_by('total', 'DESC')
				->limit($limit)
				->get()
				->result_array();
		};

		$categories = $option_query('i.category_name', 50);
		$medias = $option_query('i.media_name', 30);
		$rules = $option_query('i.rule_name', 30);
		$content_categories = $this->get_content_categories(true);
		$content_classifications = $this->get_public_classification_options((int) ($filters['content_category_id'] ?? 0));
		$locations = $this->db
			->select('COALESCE(l.name, i.location_library_name) AS name, COUNT(DISTINCT b.id) AS total', false)
			->from('book_items i')
			->join('books b', 'b.id = i.book_id')
			->join('libraries l', 'l.id = i.library_id', 'left')
			->where('b.status', 'published')
			->where('b.deleted_at IS NULL', null, false)
			->where('i.deleted_at IS NULL', null, false)
			->where('i.is_public', 1)
			->where('(l.name IS NOT NULL OR i.location_library_name IS NOT NULL)', null, false)
			->group_by('name')
			->order_by('total', 'DESC')
			->limit(40)
			->get()
			->result_array();

		$years = $this->db
			->select('b.publish_year, COUNT(*) AS total', false)
			->from('books b')
			->where('b.status', 'published')
			->where('b.deleted_at IS NULL', null, false)
			->where("b.publish_year REGEXP '^[0-9]{4}$'", null, false)
			->group_by('b.publish_year')
			->order_by('b.publish_year', 'DESC')
			->limit(20)
			->get()
			->result_array();

		return [
			'categories' => $categories,
			'content_categories' => $content_categories,
			'content_classifications' => $content_classifications,
			'medias' => $medias,
			'rules' => $rules,
			'locations' => $locations,
			'years' => $years,
		];
	}

	public function get_content_categories($active_only = false)
	{
		if (! $this->db->table_exists('book_content_categories')) {
			return [];
		}

		if ($active_only) {
			$this->db->where('is_active', 1);
		}

		return $this->db
			->from('book_content_categories')
			->order_by('sort_order', 'ASC')
			->order_by('name', 'ASC')
			->get()
			->result_array();
	}

	public function get_classification_masters($active_only = false)
	{
		if (! $this->db->table_exists('book_classification_masters')) {
			return [];
		}

		if ($active_only) {
			$this->db->where('is_active', 1);
		}

		return $this->db
			->from('book_classification_masters')
			->order_by('sort_order', 'ASC')
			->order_by('code', 'ASC')
			->get()
			->result_array();
	}

	public function get_public_classification_options($content_category_id = 0)
	{
		if (! $this->db->table_exists('book_classification_masters')) {
			return [];
		}

		if ((int) $content_category_id <= 0) {
			return $this->get_classification_masters(true);
		}

		$rows = $this->db
			->select('cm.*, COUNT(DISTINCT b.id) AS total', false)
			->from('book_classification_masters cm')
			->join('books b', 'b.content_classification_id = cm.id AND b.status = "published" AND b.deleted_at IS NULL AND b.content_category_id = ' . (int) $content_category_id, 'inner')
			->where('cm.is_active', 1)
			->group_by('cm.id')
			->order_by('cm.sort_order', 'ASC')
			->order_by('cm.code', 'ASC')
			->get()
			->result_array();

		return $rows ?: $this->get_classification_masters(true);
	}

	public function save_content_category(array $data, $id = null)
	{
		$payload = $this->master_payload($data);
		if ($id) {
			$this->db->where('id', (int) $id)->update('book_content_categories', $payload);
			return (int) $id;
		}

		$this->db->insert('book_content_categories', $payload);
		return (int) $this->db->insert_id();
	}

	public function save_classification_master(array $data, $id = null)
	{
		$payload = $this->master_payload($data);
		if ($id) {
			$this->db->where('id', (int) $id)->update('book_classification_masters', $payload);
			return (int) $id;
		}

		$this->db->insert('book_classification_masters', $payload);
		return (int) $this->db->insert_id();
	}

	public function create_book_request($book_id, array $data, array $member = null)
	{
		$book = $this->get_public_book((int) $book_id);
		if (! $book) {
			throw new RuntimeException('Katalog tidak tersedia untuk request publik.');
		}

		$name = trim((string) ($data['requester_name'] ?? ''));
		if ($member && $name === '') {
			$name = (string) ($member['full_name'] ?? '');
		}
		if ($name === '') {
			throw new RuntimeException('Nama pemohon wajib diisi.');
		}

		$available_item = $this->first_available_public_item((int) $book_id);
		$request_type = $available_item ? 'reservation' : 'request';
		$email = $this->clip($data['requester_email'] ?? ($member['email'] ?? null), 180);
		$phone = $this->clip($data['requester_phone'] ?? ($member['phone'] ?? null), 80);

		$payload = [
			'request_code' => $this->next_book_request_code(),
			'book_id' => (int) $book_id,
			'book_item_id' => $available_item ? (int) $available_item['id'] : null,
			'member_id' => $member ? (int) ($member['id'] ?? 0) ?: null : null,
			'request_type' => $request_type,
			'requester_name' => $this->clip($name, 180),
			'requester_email' => $email,
			'requester_phone' => $phone,
			'message' => $this->blank_to_null($data['message'] ?? null),
			'status' => 'pending',
		];

		$this->db->insert('book_requests', $payload);
		return [
			'id' => (int) $this->db->insert_id(),
			'code' => $payload['request_code'],
			'type' => $request_type,
		];
	}

	public function count_book_requests(array $filters = [])
	{
		$this->apply_book_request_filters($filters);
		return (int) $this->db->count_all_results();
	}

	public function get_book_requests(array $filters = [], $limit = 25, $offset = 0)
	{
		$this->apply_book_request_filters($filters);

		return $this->db
			->select('br.*, b.title, b.call_number, bi.barcode, m.member_no, m.full_name AS member_name')
			->order_by('br.created_at', 'DESC')
			->order_by('br.id', 'DESC')
			->limit(max(1, min(100, (int) $limit)), max(0, (int) $offset))
			->get()
			->result_array();
	}

	public function get_member_book_requests($member_id, $limit = 5)
	{
		return $this->db
			->select('br.*, b.title')
			->from('book_requests br')
			->join('books b', 'b.id = br.book_id', 'left')
			->where('br.member_id', (int) $member_id)
			->order_by('br.created_at', 'DESC')
			->limit(max(1, min(20, (int) $limit)))
			->get()
			->result_array();
	}

	public function update_book_request_status($id, $status, $admin_note, $processed_by)
	{
		$status = in_array($status, ['approved', 'rejected', 'fulfilled', 'cancelled'], true) ? $status : 'pending';
		$this->db
			->where('id', (int) $id)
			->update('book_requests', [
				'status' => $status,
				'admin_note' => $this->blank_to_null($admin_note),
				'processed_by' => (int) $processed_by ?: null,
				'processed_at' => date('Y-m-d H:i:s'),
			]);

		return $this->db->affected_rows() >= 0;
	}

	private function apply_public_book_filters(array $filters = [])
	{
		$this->db
			->from('books b')
			->join('book_items i', 'i.book_id = b.id AND i.deleted_at IS NULL AND i.is_public = 1', 'left')
			->join('libraries l', 'l.id = i.library_id', 'left')
			->join('book_content_categories cc', 'cc.id = b.content_category_id', 'left')
			->join('book_classification_masters cm', 'cm.id = b.content_classification_id', 'left')
			->where('b.status', 'published')
			->where('b.deleted_at IS NULL', null, false);

		$q = trim((string) ($filters['q'] ?? ''));
		if ($q !== '') {
			$this->db->group_start()
				->like('b.title', $q)
				->or_like('b.statement_responsibility', $q)
				->or_like('b.publisher', $q)
				->or_like('b.isbn', $q)
				->or_like('b.call_number', $q)
				->or_like('i.barcode', $q)
				->or_like('i.location_name', $q)
				->group_end();
		}

		$category = trim((string) ($filters['category'] ?? ''));
		if ($category !== '') {
			$this->db->where('i.category_name', $category);
		}

		$content_category = (int) ($filters['content_category_id'] ?? 0);
		if ($content_category > 0) {
			$this->db->where('b.content_category_id', $content_category);
		}

		$content_classification = (int) ($filters['content_classification_id'] ?? 0);
		if ($content_classification > 0) {
			$this->db->where('b.content_classification_id', $content_classification);
		}

		$media = trim((string) ($filters['media'] ?? ''));
		if ($media !== '') {
			$this->db->where('i.media_name', $media);
		}

		$rule = trim((string) ($filters['rule'] ?? ''));
		if ($rule !== '') {
			$this->db->where('i.rule_name', $rule);
		}

		$location_library = trim((string) ($filters['location_library'] ?? ''));
		if ($location_library !== '') {
			$this->db->group_start()
				->where('i.location_library_name', $location_library)
				->or_where('l.name', $location_library)
				->group_end();
		}

		$year = trim((string) ($filters['publish_year'] ?? ''));
		if ($year !== '') {
			$this->db->where('b.publish_year', $year);
		}

		$availability = trim((string) ($filters['availability'] ?? ''));
		if ($availability === 'available') {
			$this->db->where('i.status', 'available');
		} elseif ($availability === 'with_items') {
			$this->db->where('i.id IS NOT NULL', null, false);
		}
	}

	private function first_available_public_item($book_id)
	{
		return $this->db
			->from('book_items')
			->where('book_id', (int) $book_id)
			->where('deleted_at IS NULL', null, false)
			->where('is_public', 1)
			->where('status', 'available')
			->order_by('id', 'ASC')
			->limit(1)
			->get()
			->row_array();
	}

	private function next_book_request_code()
	{
		$prefix = 'REQ-' . date('Ymd') . '-';
		$row = $this->db
			->select('request_code')
			->from('book_requests')
			->like('request_code', $prefix, 'after')
			->order_by('request_code', 'DESC')
			->limit(1)
			->get()
			->row_array();

		$sequence = 1;
		if (! empty($row['request_code']) && preg_match('/-(\d{4})$/', $row['request_code'], $matches)) {
			$sequence = (int) $matches[1] + 1;
		}

		return $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
	}

	private function apply_book_request_filters(array $filters = [])
	{
		$this->db
			->from('book_requests br')
			->join('books b', 'b.id = br.book_id', 'left')
			->join('book_items bi', 'bi.id = br.book_item_id', 'left')
			->join('members m', 'm.id = br.member_id', 'left');

		$q = trim((string) ($filters['q'] ?? ''));
		if ($q !== '') {
			$this->db->group_start()
				->like('br.request_code', $q)
				->or_like('br.requester_name', $q)
				->or_like('br.requester_phone', $q)
				->or_like('b.title', $q)
				->or_like('bi.barcode', $q)
				->or_like('m.member_no', $q)
				->group_end();
		}

		$status = trim((string) ($filters['status'] ?? ''));
		if ($status !== '') {
			$this->db->where('br.status', $status);
		}

		$type = trim((string) ($filters['request_type'] ?? ''));
		if ($type !== '') {
			$this->db->where('br.request_type', $type);
		}
	}

	public function get_master_references($source_table)
	{
		if (! $this->db->table_exists('inlislite_master_references')) {
			return [];
		}

		return $this->db
			->from('inlislite_master_references')
			->where('source_system', self::SOURCE_SYSTEM)
			->where('source_table', (string) $source_table)
			->order_by('name', 'ASC')
			->get()
			->result_array();
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
			->where('deleted_at IS NULL', null, false)
			->order_by('id', 'ASC')
			->limit(max(1, min(100, (int) $limit)));

		if (! empty($scope_library_id)) {
			$this->db->where('library_id', (int) $scope_library_id);
		}

		return $this->db->get()->result_array();
	}

	public function get_book_item($item_id, $book_id = null, $scope_library_id = null)
	{
		$this->db
			->from('book_items')
			->where('id', (int) $item_id)
			->where('deleted_at IS NULL', null, false);

		if ($book_id !== null) {
			$this->db->where('book_id', (int) $book_id);
		}

		if (! empty($scope_library_id)) {
			$this->db->where('library_id', (int) $scope_library_id);
		}

		return $this->db->get()->row_array();
	}

	public function create_book_item($book_id, array $data, $library_id = null)
	{
		$book = $this->get_book((int) $book_id, $library_id);
		if (! $book) {
			return false;
		}

		$payload = $this->book_item_payload((int) $book_id, $data);
		$payload['library_id'] = $library_id ? (int) $library_id : null;
		$payload['source_system'] = 'manual';
		$payload['source_id'] = null;

		$this->db->insert('book_items', $payload);
		return (int) $this->db->insert_id();
	}

	public function update_book_item($item_id, $book_id, array $data, $scope_library_id = null)
	{
		$item = $this->get_book_item((int) $item_id, (int) $book_id, $scope_library_id);
		if (! $item) {
			return false;
		}

		$payload = $this->book_item_payload((int) $book_id, $data);
		$this->db->where('id', (int) $item_id)->update('book_items', $payload);
		return true;
	}

	public function soft_delete_book_item($item_id, $book_id, $scope_library_id = null)
	{
		$item = $this->get_book_item((int) $item_id, (int) $book_id, $scope_library_id);
		if (! $item) {
			return false;
		}

		return $this->db
			->where('id', (int) $item_id)
			->update('book_items', [
				'deleted_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s'),
			]);
	}

	public function create_book(array $data, $created_by = null)
	{
		$this->db->trans_start();

		$payload = $this->book_payload($data);
		$payload['source_system'] = 'manual';
		$payload['source_id'] = null;
		$payload['cover_migration_status'] = empty($payload['cover_path']) ? 'skipped' : 'pending';
		$payload['created_by'] = $created_by ? (int) $created_by : null;
		$payload['updated_by'] = $created_by ? (int) $created_by : null;

		$this->db->insert('books', $payload);
		$book_id = (int) $this->db->insert_id();
		$this->sync_manual_book_terms($book_id, $data);

		$this->db->trans_complete();
		if (! $this->db->trans_status()) {
			throw new RuntimeException('Gagal membuat katalog.');
		}

		return $book_id;
	}

	public function update_book($id, array $data, $updated_by = null)
	{
		$book = $this->get_book((int) $id);
		if (! $book) {
			return false;
		}

		$this->db->trans_start();

		$payload = $this->book_payload($data);
		$payload['updated_by'] = $updated_by ? (int) $updated_by : null;

		if ((string) ($book['cover_path'] ?? '') !== (string) ($payload['cover_path'] ?? '')) {
			$payload['cover_source_path'] = $payload['cover_path'];
			$payload['cover_local_path'] = null;
			$payload['cover_migration_status'] = empty($payload['cover_path']) ? 'skipped' : 'pending';
			$payload['cover_migrated_at'] = null;
		}

		$this->db->where('id', (int) $id)->update('books', $payload);
		$this->sync_manual_book_terms((int) $id, $data);

		$this->db->trans_complete();
		if (! $this->db->trans_status()) {
			throw new RuntimeException('Gagal memperbarui katalog.');
		}

		return true;
	}

	public function soft_delete_book($id, $updated_by = null)
	{
		$payload = [
			'deleted_at' => date('Y-m-d H:i:s'),
			'status' => 'hidden',
			'updated_by' => $updated_by ? (int) $updated_by : null,
		];

		return $this->db->where('id', (int) $id)->update('books', $payload);
	}

	private function apply_book_filters(array $filters = [], $scope_library_id = null)
	{
		$this->db
			->from('books b')
			->join('book_items i', 'i.book_id = b.id AND i.deleted_at IS NULL', 'left')
			->where('b.deleted_at IS NULL', null, false);

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

		$content_category = (int) ($filters['content_category_id'] ?? 0);
		if ($content_category > 0) {
			$this->db->where('b.content_category_id', $content_category);
		}

		$content_classification = (int) ($filters['content_classification_id'] ?? 0);
		if ($content_classification > 0) {
			$this->db->where('b.content_classification_id', $content_classification);
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

		$this->refresh_catalog_taxonomy();
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
		$cover_path = $this->clip($row['CoverURL'] ?? null, 255);
		$content_classification_id = $this->infer_classification_master_id($row['DeweyNo'] ?? null, $row['CallNumber'] ?? null);
		$content_category_id = $this->infer_content_category_id([
			'title' => $row['Title'] ?? '',
			'subtitle' => '',
			'abstract' => $row['Note'] ?? '',
			'publisher' => $row['Publisher'] ?? '',
			'publish_place' => $row['PublishLocation'] ?? '',
			'classification' => $row['DeweyNo'] ?? '',
			'call_number' => $row['CallNumber'] ?? '',
		]);
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
			'content_category_id' => $content_category_id,
			'content_classification_id' => $content_classification_id,
			'call_number' => $this->clip($row['CallNumber'] ?? null, 120),
			'language' => $this->clip($row['Languages'] ?? null, 60),
			'physical_description' => $this->clip($row['PhysicalDescription'] ?? null, 255),
			'abstract' => $this->blank_to_null($row['Note'] ?? null),
			'cover_path' => $cover_path,
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
			if ((string) ($existing['cover_path'] ?? '') !== (string) $cover_path) {
				$payload['cover_source_path'] = $cover_path;
				$payload['cover_local_path'] = null;
				$payload['cover_migration_status'] = $cover_path ? 'pending' : 'skipped';
				$payload['cover_migrated_at'] = null;
			}
			$this->db->where('id', (int) $existing['id'])->update('books', $payload);
			$book_id = (int) $existing['id'];
			$inserted = 0;
			$updated = 1;
		} else {
			$payload['cover_source_path'] = $cover_path;
			$payload['cover_migration_status'] = $cover_path ? 'pending' : 'skipped';
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
				'source_location_library_id' => $this->source_id_or_null($item['Location_Library_id'] ?? null),
				'source_location_id' => $this->source_id_or_null($item['Location_id'] ?? null),
				'source_rule_id' => $this->source_id_or_null($item['Rule_id'] ?? null),
				'source_category_id' => $this->source_id_or_null($item['Category_id'] ?? null),
				'source_media_id' => $this->source_id_or_null($item['Media_id'] ?? null),
				'source_collection_source_id' => $this->source_id_or_null($item['Source_id'] ?? null),
				'source_status_id' => $this->source_id_or_null($item['Status_id'] ?? null),
				'item_code' => $this->clip($item['NoInduk'] ?? null, 120),
				'barcode' => $this->clip($item['NomorBarcode'] ?? null, 120),
				'call_number' => $this->clip($item['CallNumber'] ?? null, 120),
				'inventory_number' => $this->clip($item['NoInduk'] ?? null, 120),
				'status' => $this->map_item_status($item['Status_id'] ?? null),
				'is_public' => (int) ($item['ISOPAC'] ?? 1) === 1 ? 1 : 0,
				'deleted_at' => null,
			];
			$payload = $this->hydrate_book_item_labels($payload);

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
			case 3:
			case 4:
				return 'damaged';
			case 5:
				return 'loaned';
			case 8:
				return 'missing';
			default:
				return 'unknown';
		}
	}

	private function book_item_payload($book_id, array $data)
	{
		$payload = [
			'book_id' => (int) $book_id,
			'source_location_library_id' => $this->source_id_or_null($data['source_location_library_id'] ?? null),
			'source_location_id' => $this->source_id_or_null($data['source_location_id'] ?? null),
			'source_rule_id' => $this->source_id_or_null($data['source_rule_id'] ?? null),
			'source_category_id' => $this->source_id_or_null($data['source_category_id'] ?? null),
			'source_media_id' => $this->source_id_or_null($data['source_media_id'] ?? null),
			'source_collection_source_id' => $this->source_id_or_null($data['source_collection_source_id'] ?? null),
			'source_status_id' => $this->source_id_or_null($data['source_status_id'] ?? null),
			'item_code' => $this->clip($data['item_code'] ?? null, 120),
			'barcode' => $this->clip($data['barcode'] ?? null, 120),
			'call_number' => $this->clip($data['call_number'] ?? null, 120),
			'inventory_number' => $this->clip($data['inventory_number'] ?? null, 120),
			'status' => in_array(($data['status'] ?? 'unknown'), ['available', 'loaned', 'missing', 'damaged', 'unknown'], true) ? $data['status'] : 'unknown',
			'is_public' => ! empty($data['is_public']) ? 1 : 0,
			'updated_at' => date('Y-m-d H:i:s'),
		];

		return $this->hydrate_book_item_labels($payload);
	}

	private function hydrate_book_item_labels(array $payload)
	{
		$refs = [
			'source_location_library_id' => ['location_library', 'location_library_name'],
			'source_location_id' => ['locations', 'location_room_name'],
			'source_rule_id' => ['collectionrules', 'rule_name'],
			'source_category_id' => ['collectioncategorys', 'category_name'],
			'source_media_id' => ['collectionmedias', 'media_name'],
			'source_collection_source_id' => ['collectionsources', 'source_name'],
			'source_status_id' => ['collectionstatus', 'status_label'],
		];

		foreach ($refs as $source_key => $mapping) {
			$payload[$mapping[1]] = $this->reference_name($mapping[0], $payload[$source_key] ?? null);
		}

		$payload['location_name'] = $this->clip($payload['location_room_name'] ?: $payload['location_library_name'], 180);
		$payload['collection_type'] = $this->clip($payload['category_name'], 120);

		if (empty($payload['source_status_id'])) {
			$payload['status_label'] = null;
		} elseif ($payload['status'] === 'unknown') {
			$payload['status'] = $this->map_item_status($payload['source_status_id']);
		}

		return $payload;
	}

	private function reference_name($source_table, $source_id)
	{
		$source_id = $this->source_id_or_null($source_id);
		if ($source_id === null || ! $this->db->table_exists('inlislite_master_references')) {
			return null;
		}

		$row = $this->db
			->select('name')
			->from('inlislite_master_references')
			->where('source_system', self::SOURCE_SYSTEM)
			->where('source_table', $source_table)
			->where('source_id', $source_id)
			->get()
			->row_array();

		return $row['name'] ?? null;
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

	private function book_payload(array $data)
	{
		return [
			'title' => $this->clip($data['title'] ?? '', 255) ?: 'Tanpa Judul',
			'subtitle' => $this->clip($data['subtitle'] ?? null, 255),
			'statement_responsibility' => $this->clip($data['statement_responsibility'] ?? null, 255),
			'edition' => $this->clip($data['edition'] ?? null, 120),
			'publish_place' => $this->clip($data['publish_place'] ?? null, 160),
			'publisher' => $this->clip($data['publisher'] ?? null, 180),
			'publish_year' => $this->clip($data['publish_year'] ?? null, 20),
			'isbn' => $this->clip($data['isbn'] ?? null, 80),
			'classification' => $this->clip($data['classification'] ?? null, 80),
			'content_category_id' => ! empty($data['content_category_id']) ? (int) $data['content_category_id'] : null,
			'content_classification_id' => ! empty($data['content_classification_id']) ? (int) $data['content_classification_id'] : null,
			'call_number' => $this->clip($data['call_number'] ?? null, 120),
			'language' => $this->clip($data['language'] ?? null, 60),
			'physical_description' => $this->clip($data['physical_description'] ?? null, 255),
			'abstract' => $this->blank_to_null($data['abstract'] ?? null),
			'cover_path' => $this->clip($data['cover_path'] ?? null, 255),
			'cover_source_path' => $this->clip($data['cover_path'] ?? null, 500),
			'status' => in_array(($data['status'] ?? 'draft'), ['draft', 'published', 'hidden'], true) ? $data['status'] : 'draft',
		];
	}

	private function sync_manual_book_terms($book_id, array $data)
	{
		$this->db->where('book_id', (int) $book_id)->delete('book_authors');
		$this->db->where('book_id', (int) $book_id)->delete('book_subjects');

		foreach ($this->split_terms($data['authors'] ?? ($data['statement_responsibility'] ?? '')) as $index => $author) {
			$this->db->insert('book_authors', [
				'book_id' => (int) $book_id,
				'name' => $this->clip($author, 180),
				'role' => 'author',
				'sort_order' => ($index + 1) * 10,
			]);
		}

		foreach ($this->split_terms($data['subjects'] ?? '') as $subject) {
			$this->db->insert('book_subjects', [
				'book_id' => (int) $book_id,
				'subject' => $this->clip($subject, 180),
			]);
		}
	}

	private function source_id($value)
	{
		return (string) (int) $value;
	}

	private function source_id_or_null($value)
	{
		$value = trim((string) $value);
		return $value === '' ? null : (string) (int) $value;
	}

	private function clip($value, $length)
	{
		$value = $this->blank_to_null($value);
		if ($value === null) {
			return null;
		}

		return function_exists('mb_substr') ? mb_substr($value, 0, (int) $length) : substr($value, 0, (int) $length);
	}

	private function master_payload(array $data)
	{
		$name = $this->clip($data['name'] ?? '', 160);
		if (! $name) {
			throw new RuntimeException('Nama master wajib diisi.');
		}

		$code = strtolower(trim((string) ($data['code'] ?? '')));
		$code = preg_replace('/[^a-z0-9\-\.]+/', '-', $code ?: $name);
		$code = trim((string) $code, '-.');
		if ($code === '') {
			$code = substr(md5($name), 0, 10);
		}

		return [
			'code' => $this->clip($code, 80),
			'name' => $name,
			'description' => $this->blank_to_null($data['description'] ?? null),
			'sort_order' => (int) ($data['sort_order'] ?? 0),
			'is_active' => ! empty($data['is_active']) ? 1 : 0,
		];
	}

	private function infer_classification_master_id($classification, $call_number = null)
	{
		$digits = preg_replace('/[^0-9]/', '', (string) ($classification ?: $call_number));
		if ($digits === '') {
			return null;
		}

		return $this->master_id_by_code('book_classification_masters', substr($digits, 0, 1) . '00');
	}

	private function infer_content_category_id(array $data)
	{
		$text = strtolower(implode(' ', array_filter([
			$data['title'] ?? '',
			$data['subtitle'] ?? '',
			$data['abstract'] ?? '',
			$data['publisher'] ?? '',
			$data['publish_place'] ?? '',
			$data['call_number'] ?? '',
		])));
		$digits = preg_replace('/[^0-9]/', '', (string) (($data['classification'] ?? '') ?: ($data['call_number'] ?? '')));
		$first = $digits !== '' ? substr($digits, 0, 1) : '';

		if (preg_match('/anak|remaja|dongeng|cerita rakyat|paud|tk|sd|smp|komik/', $text)) {
			return $this->master_id_by_code('book_content_categories', 'anak-remaja');
		}
		if (preg_match('/skripsi|tesis|disertasi|penelitian|jurnal|prosiding|karya ilmiah|laporan akhir/', $text)) {
			return $this->master_id_by_code('book_content_categories', 'karya-ilmiah');
		}
		if (preg_match('/rembang|lasem|sulang|sedan|sarang|pamotan|kragan|sluke|kaliori|gunem|bulu|sumber|sale|pancur/', $text)) {
			return $this->master_id_by_code('book_content_categories', 'lokal-rembang');
		}
		if (preg_match('/kamus|ensiklopedia|atlas|direktori|bibliografi/', $text)) {
			return $this->master_id_by_code('book_content_categories', 'referensi');
		}
		if ($first === '8') {
			return $this->master_id_by_code('book_content_categories', 'fiksi');
		}
		if ($first === '2') {
			return $this->master_id_by_code('book_content_categories', 'agama');
		}
		if (in_array($first, ['5', '6'], true)) {
			return $this->master_id_by_code('book_content_categories', 'teknologi');
		}
		if (in_array($first, ['7', '9'], true)) {
			return $this->master_id_by_code('book_content_categories', 'sejarah-budaya');
		}
		if (in_array($first, ['0', '1', '3', '4'], true)) {
			return $this->master_id_by_code('book_content_categories', 'pengetahuan');
		}

		return $this->master_id_by_code('book_content_categories', 'non-fiksi');
	}

	private function master_id_by_code($table, $code)
	{
		if (! $this->db->table_exists($table)) {
			return null;
		}

		$row = $this->db
			->select('id')
			->from($table)
			->where('code', $code)
			->limit(1)
			->get()
			->row_array();

		return ! empty($row['id']) ? (int) $row['id'] : null;
	}

	private function refresh_catalog_taxonomy()
	{
		if (! $this->db->table_exists('book_content_categories') || ! $this->db->table_exists('book_classification_masters')) {
			return;
		}

		$this->db->query(
			"UPDATE books b
			LEFT JOIN book_classification_masters cm
				ON cm.code = CONCAT(LEFT(REGEXP_REPLACE(COALESCE(b.classification, b.call_number, ''), '[^0-9]', ''), 1), '00')
			SET b.content_classification_id = cm.id
			WHERE b.content_classification_id IS NULL
				AND REGEXP_REPLACE(COALESCE(b.classification, b.call_number, ''), '[^0-9]', '') REGEXP '^[0-9]'"
		);

		$rules = [
			['anak-remaja', "LOWER(CONCAT_WS(' ', b.title, b.subtitle, b.abstract, b.call_number)) REGEXP 'anak|remaja|dongeng|cerita rakyat|paud|tk|sd|smp|komik'", false],
			['karya-ilmiah', "LOWER(CONCAT_WS(' ', b.title, b.subtitle, b.abstract, b.call_number)) REGEXP 'skripsi|tesis|disertasi|penelitian|jurnal|prosiding|karya ilmiah|laporan akhir'", false],
			['lokal-rembang', "LOWER(CONCAT_WS(' ', b.title, b.subtitle, b.abstract, b.publisher, b.publish_place)) REGEXP 'rembang|lasem|sulang|sedan|sarang|pamotan|kragan|sluke|kaliori|gunem|bulu|sumber|sale|pancur'", false],
			['referensi', "LOWER(CONCAT_WS(' ', b.title, b.subtitle, b.abstract)) REGEXP 'kamus|ensiklopedia|atlas|direktori|bibliografi' OR EXISTS (SELECT 1 FROM book_items i WHERE i.book_id = b.id AND i.deleted_at IS NULL AND LOWER(COALESCE(i.category_name, '')) LIKE '%referensi%')", false],
			['fiksi', "LEFT(REGEXP_REPLACE(COALESCE(b.classification, b.call_number, ''), '[^0-9]', ''), 1) = '8'", true],
			['agama', "LEFT(REGEXP_REPLACE(COALESCE(b.classification, b.call_number, ''), '[^0-9]', ''), 1) = '2'", true],
			['teknologi', "LEFT(REGEXP_REPLACE(COALESCE(b.classification, b.call_number, ''), '[^0-9]', ''), 1) IN ('5', '6')", true],
			['sejarah-budaya', "LEFT(REGEXP_REPLACE(COALESCE(b.classification, b.call_number, ''), '[^0-9]', ''), 1) IN ('7', '9')", true],
			['pengetahuan', "LEFT(REGEXP_REPLACE(COALESCE(b.classification, b.call_number, ''), '[^0-9]', ''), 1) IN ('0', '1', '3', '4')", true],
			['non-fiksi', '1 = 1', true],
		];

		foreach ($rules as $rule) {
			$this->db->query(
				"UPDATE books b
				JOIN book_content_categories cc ON cc.code = ?
				SET b.content_category_id = cc.id
				WHERE " . ($rule[2] ? 'b.content_category_id IS NULL AND ' : '') . '(' . $rule[1] . ')',
				[$rule[0]]
			);
		}
	}

	private function blank_to_null($value)
	{
		$value = trim((string) $value);
		return $value === '' ? null : $value;
	}
}
