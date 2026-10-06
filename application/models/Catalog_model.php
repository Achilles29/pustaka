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
			->select("b.*, cc.name AS content_category_name, cm.name AS content_classification_name, COUNT(DISTINCT i.id) AS item_count, GROUP_CONCAT(DISTINCT NULLIF(i.collection_type, '') ORDER BY i.collection_type SEPARATOR ', ') AS collection_types", false)
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
			->select("b.*, cc.name AS content_category_name, cm.name AS content_classification_name, COUNT(DISTINCT i.id) AS item_count, MIN(NULLIF(i.collection_type, '')) AS primary_collection_type", false)
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
			->select("b.*, cc.name AS content_category_name, cm.name AS content_classification_name, COUNT(DISTINCT i.id) AS public_item_count, SUM(CASE WHEN i.status = 'available' AND i.is_loanable = 1 THEN 1 ELSE 0 END) AS available_count, SUM(CASE WHEN i.status = 'reserved' AND i.is_loanable = 1 THEN 1 ELSE 0 END) AS reserved_count, SUM(CASE WHEN i.status = 'loaned' AND i.is_loanable = 1 THEN 1 ELSE 0 END) AS loaned_count, COUNT(DISTINCT da.id) AS digital_asset_count, MIN(da.id) AS first_digital_asset_id, GROUP_CONCAT(DISTINCT NULLIF(i.collection_type, '') ORDER BY i.collection_type SEPARATOR ', ') AS collection_types, (SELECT GROUP_CONCAT(g.name ORDER BY g.sort_order SEPARATOR ' · ') FROM book_textbook_grade_tags gt JOIN textbook_grade_levels g ON g.id = gt.grade_level_id WHERE gt.book_id = b.id) AS textbook_grade_names, (SELECT GROUP_CONCAT(s.name ORDER BY s.sort_order SEPARATOR ' · ') FROM book_textbook_subject_tags st JOIN textbook_subjects s ON s.id = st.subject_id WHERE st.book_id = b.id) AS textbook_subject_names", false)
			->join('digital_assets da', "da.book_id = b.id AND da.status = 'active'", 'left')
			->group_by('b.id')
			->order_by('b.title', 'ASC')
			->limit(max(1, min(48, (int) $limit)), max(0, (int) $offset))
			->get()
			->result_array();
	}

	/**
	 * Pilihan katalog untuk halaman publik. ID dipilih terlebih dahulu agar
	 * pengacakan tetap merata meski satu judul memiliki banyak eksemplar.
	 */
	public function get_random_public_books(array $filters = [], $limit = 9)
	{
		$limit = max(1, min(18, (int) $limit));
		$this->apply_public_book_filters($filters);
		$id_rows = $this->db
			->select('b.id')
			->group_by('b.id')
			->order_by('RAND()', '', false)
			->limit($limit)
			->get()
			->result_array();
		$ids = array_values(array_filter(array_map('intval', array_column($id_rows, 'id'))));
		if (empty($ids)) return [];

		return $this->db
			->select("b.*, cc.name AS content_category_name, COUNT(DISTINCT i.id) AS public_item_count, SUM(CASE WHEN i.status = 'available' AND i.is_loanable = 1 THEN 1 ELSE 0 END) AS available_count, COUNT(DISTINCT da.id) AS digital_asset_count, MIN(da.id) AS first_digital_asset_id", false)
			->from('books b')
			->join('book_items i', 'i.book_id = b.id AND i.deleted_at IS NULL AND i.is_public = 1', 'left')
			->join('book_content_categories cc', 'cc.id = b.content_category_id', 'left')
			->join('digital_assets da', "da.book_id = b.id AND da.status = 'active'", 'left')
			->where_in('b.id', $ids)
			->group_by('b.id')
			->order_by('FIELD(b.id,' . implode(',', $ids) . ')', '', false)
			->get()
			->result_array();
	}

	public function get_public_book($id)
	{
		$this->db
			->select("b.*, cc.name AS content_category_name, cm.name AS content_classification_name, COUNT(DISTINCT i.id) AS public_item_count, SUM(CASE WHEN i.status = 'available' AND i.is_loanable = 1 THEN 1 ELSE 0 END) AS available_count, SUM(CASE WHEN i.status = 'reserved' AND i.is_loanable = 1 THEN 1 ELSE 0 END) AS reserved_count, SUM(CASE WHEN i.status = 'loaned' AND i.is_loanable = 1 THEN 1 ELSE 0 END) AS loaned_count, (SELECT GROUP_CONCAT(g.name ORDER BY g.sort_order SEPARATOR ' · ') FROM book_textbook_grade_tags gt JOIN textbook_grade_levels g ON g.id = gt.grade_level_id WHERE gt.book_id = b.id) AS textbook_grade_names, (SELECT GROUP_CONCAT(s.name ORDER BY s.sort_order SEPARATOR ' · ') FROM book_textbook_subject_tags st JOIN textbook_subjects s ON s.id = st.subject_id WHERE st.book_id = b.id) AS textbook_subject_names", false)
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
			->where('da.reader_audience', 'member')
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
			->where('da.reader_audience', 'member')
			->order_by('da.id', 'DESC')
			->limit(max(1, min(12, (int) $limit)))
			->get()
			->result_array();
	}

	public function get_dashboard_book_shelves($limit = 4)
	{
		$limit = max(3, min(8, (int) $limit));
		return [
			'catalog' => [
				'random' => $this->get_dashboard_shelf('random', false, $limit),
				'popular' => $this->get_dashboard_shelf('popular', false, $limit),
				'newest' => $this->get_dashboard_shelf('newest', false, $limit),
			],
			'digital' => [
				'random' => $this->get_dashboard_shelf('random', true, $limit),
				'popular' => $this->get_dashboard_shelf('popular', true, $limit),
				'newest' => $this->get_dashboard_shelf('newest', true, $limit),
			],
			'textbooks' => [
				'random' => $this->get_dashboard_shelf('random', false, $limit, true),
				'popular' => $this->get_dashboard_shelf('popular', false, $limit, true),
				'newest' => $this->get_dashboard_shelf('newest', false, $limit, true),
			],
		];
	}

	private function get_dashboard_shelf($sort, $digital_only, $limit, $textbook_only = false)
	{
		$has_digital = "EXISTS (SELECT 1 FROM digital_assets da_filter WHERE da_filter.book_id = b.id AND da_filter.status = 'active' AND da_filter.reader_audience = 'member')";
		$digital_asset = "(SELECT MIN(da_asset.id) FROM digital_assets da_asset WHERE da_asset.book_id = b.id AND da_asset.status = 'active' AND da_asset.reader_audience = 'member')";
		$physical_reads = '(SELECT COUNT(*) FROM loan_transaction_items lti JOIN book_items popular_item ON popular_item.id = lti.book_item_id WHERE popular_item.book_id = b.id)';
		$digital_reads = '(SELECT COUNT(*) FROM reading_sessions rs WHERE rs.book_id = b.id)';

		$builder = $this->db
			->select("b.id, b.title, b.statement_responsibility, b.publisher, b.publish_year, b.cover_local_path, b.cover_source_path, cc.name AS content_category_name, {$has_digital} AS has_digital, {$digital_asset} AS digital_asset_id, {$physical_reads} AS physical_read_count, {$digital_reads} AS digital_read_count", false)
			->from('books b')
			->join('book_content_categories cc', 'cc.id = b.content_category_id', 'left')
			->where('b.status', 'published')
			->where('b.deleted_at IS NULL', null, false);

		if ($digital_only) {
			$builder->where($has_digital, null, false);
		}
		if ($textbook_only) {
			$builder->where('cc.code', 'buku-pelajaran');
		}

		if ($sort === 'popular') {
			$builder->order_by("({$physical_reads} + {$digital_reads})", 'DESC', false)
				->order_by('b.created_at', 'DESC');
		} elseif ($sort === 'random') {
			$builder->order_by('RAND()', '', false);
		} else {
			$builder->order_by('b.created_at', 'DESC')->order_by('b.id', 'DESC');
		}

		return $builder
			->limit($limit)
			->get()
			->result_array();
	}

	public function get_textbook_grade_levels($active_only = true)
	{
		if (! $this->db->table_exists('textbook_grade_levels')) return [];
		$builder = $this->db->from('textbook_grade_levels');
		if ($active_only) $builder->where('is_active', 1);
		return $builder->order_by('sort_order', 'ASC')->order_by('name', 'ASC')->get()->result_array();
	}

	public function get_textbook_subjects($active_only = true)
	{
		if (! $this->db->table_exists('textbook_subjects')) return [];
		$builder = $this->db->from('textbook_subjects');
		if ($active_only) $builder->where('is_active', 1);
		return $builder->order_by('sort_order', 'ASC')->order_by('name', 'ASC')->get()->result_array();
	}

	public function get_textbook_filter_options()
	{
		$empty = ['grade_levels' => [], 'subjects' => []];
		if (! $this->db->table_exists('book_textbook_grade_tags') || ! $this->db->table_exists('book_textbook_subject_tags')) return $empty;

		$grade_levels = $this->db
			->select('g.*, COUNT(DISTINCT b.id) AS total', false)
			->from('textbook_grade_levels g')
			->join('book_textbook_grade_tags gt', 'gt.grade_level_id = g.id', 'inner')
			->join('books b', "b.id = gt.book_id AND b.status = 'published' AND b.deleted_at IS NULL", 'inner')
			->join('book_content_categories cc', "cc.id = b.content_category_id AND cc.code = 'buku-pelajaran'", 'inner')
			->where('g.is_active', 1)
			->group_by('g.id')
			->having('total >', 0)
			->order_by('g.sort_order', 'ASC')->get()->result_array();
		$subjects = $this->db
			->select('s.*, COUNT(DISTINCT b.id) AS total', false)
			->from('textbook_subjects s')
			->join('book_textbook_subject_tags st', 'st.subject_id = s.id', 'inner')
			->join('books b', "b.id = st.book_id AND b.status = 'published' AND b.deleted_at IS NULL", 'inner')
			->join('book_content_categories cc', "cc.id = b.content_category_id AND cc.code = 'buku-pelajaran'", 'inner')
			->where('s.is_active', 1)
			->group_by('s.id')
			->having('total >', 0)
			->order_by('s.sort_order', 'ASC')->get()->result_array();

		return compact('grade_levels', 'subjects');
	}

	public function get_book_textbook_tags($book_id)
	{
		$result = ['grades' => [], 'subjects' => []];
		if (! $this->db->table_exists('book_textbook_grade_tags') || ! $this->db->table_exists('book_textbook_subject_tags')) return $result;
		$result['grades'] = $this->db->select('g.*')->from('book_textbook_grade_tags gt')->join('textbook_grade_levels g', 'g.id = gt.grade_level_id')->where('gt.book_id', (int) $book_id)->order_by('g.sort_order', 'ASC')->get()->result_array();
		$result['subjects'] = $this->db->select('s.*')->from('book_textbook_subject_tags st')->join('textbook_subjects s', 's.id = st.subject_id')->where('st.book_id', (int) $book_id)->order_by('s.sort_order', 'ASC')->get()->result_array();
		return $result;
	}

	public function save_book_textbook_tags($book_id, array $grade_ids, array $subject_ids)
	{
		if (! $this->db->table_exists('book_textbook_grade_tags') || ! $this->db->table_exists('book_textbook_subject_tags')) return;
		$grade_ids = array_values(array_unique(array_filter(array_map('intval', $grade_ids))));
		$subject_ids = array_values(array_unique(array_filter(array_map('intval', $subject_ids))));
		$this->db->trans_start();
		$this->db->where('book_id', (int) $book_id)->delete('book_textbook_grade_tags');
		$this->db->where('book_id', (int) $book_id)->delete('book_textbook_subject_tags');
		foreach ($grade_ids as $grade_id) $this->db->insert('book_textbook_grade_tags', ['book_id' => (int) $book_id, 'grade_level_id' => $grade_id]);
		foreach ($subject_ids as $subject_id) $this->db->insert('book_textbook_subject_tags', ['book_id' => (int) $book_id, 'subject_id' => $subject_id]);
		$this->db->trans_complete();
		if (! $this->db->trans_status()) throw new RuntimeException('Tag buku pelajaran gagal disimpan.');
	}

	public function admin_filter_options($scope_library_id = null)
	{
		$option_query = function ($field, $limit = 40) use ($scope_library_id) {
			$this->db
				->select($field . ' AS name, COUNT(DISTINCT b.id) AS total', false)
				->from('book_items i')
				->join('books b', 'b.id = i.book_id')
				->where('b.deleted_at IS NULL', null, false)
				->where('i.deleted_at IS NULL', null, false)
				->where($field . ' IS NOT NULL', null, false)
				->where($field . ' <>', '');
			if (! empty($scope_library_id)) {
				$this->db->where('i.library_id', (int) $scope_library_id);
			}
			return $this->db
				->group_by($field)
				->order_by('total', 'DESC')
				->limit($limit)
				->get()
				->result_array();
		};

		$this->db
			->select('COALESCE(NULLIF(source_system, \'\'), \'manual\') AS name, COUNT(*) AS total', false)
			->from('books')
			->where('deleted_at IS NULL', null, false)
			->group_by('name')
			->order_by('total', 'DESC');
		$sources = $this->db->get()->result_array();

		return [
			'sources' => $sources,
			'collection_types' => $option_query('i.collection_type', 30),
			'categories' => $option_query('i.category_name', 50),
			'medias' => $option_query('i.media_name', 30),
			'rules' => $option_query('i.rule_name', 30),
			'locations' => $option_query('i.location_library_name', 40),
		];
	}

	public function count_highlights(array $filters = [])
	{
		if (! $this->db->table_exists('catalog_highlights')) {
			return 0;
		}

		$this->apply_highlight_filters($filters);
		return (int) $this->db->count_all_results();
	}

	public function get_highlights(array $filters = [], $limit = 25, $offset = 0)
	{
		if (! $this->db->table_exists('catalog_highlights')) {
			return [];
		}

		$this->apply_highlight_filters($filters);

		return $this->db
			->select("ch.*, b.title AS book_title, b.statement_responsibility, b.publish_year, b.status AS book_status, cc.name AS category_name, target_cc.name AS target_category_name, EXISTS (SELECT 1 FROM digital_assets da_kind WHERE da_kind.book_id = b.id AND da_kind.status = 'active' AND da_kind.reader_audience = 'member') AS target_has_digital", false)
			->join('books b', 'b.id = ch.book_id', 'left')
			->join('book_content_categories cc', 'cc.id = b.content_category_id', 'left')
			->join('book_content_categories target_cc', 'target_cc.id = ch.content_category_id', 'left')
			->order_by('ch.is_active', 'DESC')
			->order_by('ch.sort_order', 'ASC')
			->order_by('ch.id', 'DESC')
			->limit(max(1, min(100, (int) $limit)), max(0, (int) $offset))
			->get()
			->result_array();
	}

	public function get_highlight($id)
	{
		if (! $this->db->table_exists('catalog_highlights')) {
			return null;
		}

		return $this->db
			->from('catalog_highlights')
			->where('id', (int) $id)
			->limit(1)
			->get()
			->row_array();
	}

	public function save_highlight(array $data, $id = null, $user_id = null)
	{
		if (! $this->db->table_exists('catalog_highlights')) {
			throw new RuntimeException('Tabel highlight katalog belum tersedia. Jalankan patch SQL terbaru.');
		}

		$payload = $this->highlight_payload($data);
		if ($id) {
			$payload['updated_by'] = $user_id ? (int) $user_id : null;
			$this->db->where('id', (int) $id)->update('catalog_highlights', $payload);
			return (int) $id;
		}

		$payload['created_by'] = $user_id ? (int) $user_id : null;
		$payload['updated_by'] = $user_id ? (int) $user_id : null;
		$this->db->insert('catalog_highlights', $payload);
		return (int) $this->db->insert_id();
	}

	public function delete_highlight($id)
	{
		if (! $this->db->table_exists('catalog_highlights')) {
			return false;
		}

		return $this->db->where('id', (int) $id)->delete('catalog_highlights');
	}

	public function get_active_book_highlights($limit = 8)
	{
		if (! $this->db->table_exists('catalog_highlights')) {
			return [];
		}

		return $this->db
			->select("ch.*, b.title, b.statement_responsibility, b.cover_local_path, b.cover_source_path, b.publish_year, b.call_number, cc.name AS content_category_name, cm.name AS content_classification_name, MIN(da.id) AS first_digital_asset_id, MIN(da.pdf_delivery) AS digital_access_policy", false)
			->from('catalog_highlights ch')
			->join('books b', 'b.id = ch.book_id')
			->join('book_content_categories cc', 'cc.id = b.content_category_id', 'left')
			->join('book_classification_masters cm', 'cm.id = b.content_classification_id', 'left')
			->join('digital_assets da', "da.book_id = b.id AND da.status = 'active' AND da.reader_audience = 'member'", 'left')
			->where('ch.target_type', 'book')
			->where('ch.is_active', 1)
			->where('b.status', 'published')
			->where('b.deleted_at IS NULL', null, false)
			->where('(ch.starts_at IS NULL OR ch.starts_at <= NOW())', null, false)
			->where('(ch.ends_at IS NULL OR ch.ends_at >= NOW())', null, false)
			->group_by('ch.id')
			->order_by('ch.sort_order', 'ASC')
			->order_by('ch.id', 'DESC')
			->limit(max(1, min(16, (int) $limit)))
			->get()
			->result_array();
	}

	public function get_active_category_highlights($limit = 6)
	{
		if (! $this->db->table_exists('catalog_highlights')) {
			return [];
		}

		return $this->db
			->select("ch.*, cc.name AS category_name, cc.code AS category_code, cc.description AS category_description, COUNT(DISTINCT b.id) AS book_count, COUNT(DISTINCT da.id) AS digital_count", false)
			->from('catalog_highlights ch')
			->join('book_content_categories cc', 'cc.id = ch.content_category_id')
			->join('books b', "b.content_category_id = cc.id AND b.status = 'published' AND b.deleted_at IS NULL", 'left')
			->join('digital_assets da', "da.book_id = b.id AND da.status = 'active'", 'left')
			->where('ch.target_type', 'category')
			->where('ch.is_active', 1)
			->where('cc.is_active', 1)
			->where('(ch.starts_at IS NULL OR ch.starts_at <= NOW())', null, false)
			->where('(ch.ends_at IS NULL OR ch.ends_at >= NOW())', null, false)
			->group_by('ch.id')
			->order_by('ch.sort_order', 'ASC')
			->order_by('ch.id', 'DESC')
			->limit(max(1, min(12, (int) $limit)))
			->get()
			->result_array();
	}

	public function get_top_content_categories($limit = 6)
	{
		if (! $this->db->table_exists('book_content_categories')) {
			return [];
		}

		return $this->db
			->select("NULL AS id, 'category' AS target_type, cc.id AS content_category_id, cc.name AS category_name, cc.code AS category_code, cc.description AS category_description, cc.name AS title_override, NULL AS label, COUNT(DISTINCT b.id) AS book_count, COUNT(DISTINCT da.id) AS digital_count", false)
			->from('book_content_categories cc')
			->join('books b', "b.content_category_id = cc.id AND b.status = 'published' AND b.deleted_at IS NULL", 'left')
			->join('digital_assets da', "da.book_id = b.id AND da.status = 'active'", 'left')
			->where('cc.is_active', 1)
			->group_by('cc.id')
			->having('book_count >', 0)
			->order_by('book_count', 'DESC')
			->order_by('cc.sort_order', 'ASC')
			->limit(max(1, min(12, (int) $limit)))
			->get()
			->result_array();
	}

	public function get_highlight_book_options($limit = 500)
	{
		if (! $this->db->table_exists('books')) {
			return [];
		}

		return $this->db
			->select('b.id, b.title, b.statement_responsibility, b.publish_year, cc.name AS content_category_name')
			->from('books b')
			->join('book_content_categories cc', 'cc.id = b.content_category_id', 'left')
			->where('b.deleted_at IS NULL', null, false)
			->where_in('b.status', ['published', 'draft'])
			->order_by('b.title', 'ASC')
			->limit(max(50, min(1000, (int) $limit)))
			->get()
			->result_array();
	}

	public function get_highlight_book($id)
	{
		$id = (int) $id;
		if ($id <= 0) {
			return null;
		}

		return $this->highlight_book_query()
			->where('b.id', $id)
			->limit(1)
			->get()
			->row_array();
	}

	public function search_highlight_books($query = '', $kind = 'all', $limit = 12)
	{
		$query = trim((string) $query);
		$builder = $this->highlight_book_query();
		if ($query !== '') {
			$builder->group_start()
				->like('b.title', $query)
				->or_like('b.statement_responsibility', $query)
				->or_like('b.publisher', $query)
				->or_like('b.isbn', $query)
				->group_end();
		}

		$has_digital = "EXISTS (SELECT 1 FROM digital_assets da_filter WHERE da_filter.book_id = b.id AND da_filter.status = 'active' AND da_filter.reader_audience = 'member')";
		if ($kind === 'digital') {
			$builder->where($has_digital, null, false);
		} elseif ($kind === 'non_digital') {
			$builder->where('NOT ' . $has_digital, null, false);
		}

		return $builder
			->order_by('b.title', 'ASC')
			->limit(max(1, min(20, (int) $limit)))
			->get()
			->result_array();
	}

	private function highlight_book_query()
	{
		$has_digital = "EXISTS (SELECT 1 FROM digital_assets da_filter WHERE da_filter.book_id = b.id AND da_filter.status = 'active' AND da_filter.reader_audience = 'member')";
		$digital_asset = "(SELECT MIN(da_asset.id) FROM digital_assets da_asset WHERE da_asset.book_id = b.id AND da_asset.status = 'active' AND da_asset.reader_audience = 'member')";

		return $this->db
			->select("b.id, b.title, b.statement_responsibility, b.publisher, b.publish_year, b.isbn, b.cover_local_path, b.cover_source_path, cc.name AS content_category_name, cm.name AS content_classification_name, {$has_digital} AS has_digital, {$digital_asset} AS digital_asset_id", false)
			->from('books b')
			->join('book_content_categories cc', 'cc.id = b.content_category_id', 'left')
			->join('book_classification_masters cm', 'cm.id = b.content_classification_id', 'left')
			->where('b.status', 'published')
			->where('b.deleted_at IS NULL', null, false);
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
		$collection_types = $option_query('i.collection_type', 30);
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
			'collection_types' => $collection_types,
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

	public function get_collection_types($active_only = false)
	{
		if (! $this->db->table_exists('book_collection_types')) {
			return [];
		}
		if ($active_only) {
			$this->db->where('is_active', 1);
		}
		return $this->db
			->from('book_collection_types')
			->order_by('sort_order', 'ASC')
			->order_by('name', 'ASC')
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

	public function save_collection_type(array $data, $id = null)
	{
		if (! $this->db->table_exists('book_collection_types')) {
			throw new RuntimeException('Master jenis koleksi belum tersedia. Jalankan patch SQL terbaru.');
		}
		$payload = $this->master_payload($data);
		if ($id) {
			$this->db->where('id', (int) $id)->update('book_collection_types', $payload);
			return (int) $id;
		}
		$this->db->insert('book_collection_types', $payload);
		return (int) $this->db->insert_id();
	}

	public function delete_collection_type($id)
	{
		$type = $this->db->from('book_collection_types')->where('id', (int) $id)->limit(1)->get()->row_array();
		if (! $type) {
			throw new RuntimeException('Master jenis koleksi tidak ditemukan.');
		}
		$in_use = (int) $this->db->from('book_items')->where('collection_type', $type['name'])->count_all_results();
		if ($in_use > 0) {
			throw new RuntimeException('Jenis koleksi masih digunakan oleh ' . $in_use . ' eksemplar. Nonaktifkan atau pindahkan datanya terlebih dahulu.');
		}
		$this->db->where('id', (int) $id)->delete('book_collection_types');
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
		if ($member) {
			$existing = (int) $this->db->where('member_id', (int) $member['id'])->where('book_id', (int) $book_id)
				->where_in('status', ['pending', 'approved'])->count_all_results('book_requests');
			if ($existing > 0) {
				throw new RuntimeException('Member sudah memiliki request aktif untuk buku ini.');
			}
		}

		$available_item = $this->first_available_public_item((int) $book_id);
		if (! $available_item) {
			throw new RuntimeException('Semua eksemplar yang dapat dipinjam sedang tidak tersedia. Request peminjaman tidak dapat dibuat saat ini.');
		}
		$request_type = 'reservation';
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

		$this->db->trans_begin();
		try {
			$this->db->where('id', (int) $available_item['id'])->where('status', 'available')->update('book_items', [
				'status' => 'reserved',
				'updated_at' => date('Y-m-d H:i:s'),
			]);
			if ($this->db->affected_rows() !== 1) {
				throw new RuntimeException('Eksemplar baru saja direservasi oleh member lain. Silakan coba lagi.');
			}
			$this->db->insert('book_requests', $payload);
			if (! $this->db->trans_status()) {
				throw new RuntimeException('Request buku gagal disimpan.');
			}
			$this->db->trans_commit();
		} catch (Throwable $e) {
			$this->db->trans_rollback();
			throw $e;
		}
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
			->select("br.*, b.title, b.call_number, bi.barcode, m.member_no, m.full_name AS member_name, li.loan_status AS linked_loan_status, li.actual_return_at AS linked_actual_return_at,
				CASE
					WHEN br.status = 'fulfilled' AND br.loan_transaction_item_id IS NULL THEN 'completed_legacy'
					WHEN br.status = 'fulfilled' AND (li.actual_return_at IS NOT NULL OR UPPER(COALESCE(li.loan_status, '')) = 'RETURN') THEN 'returned'
					WHEN br.status = 'fulfilled' THEN 'active'
					ELSE br.status
				END AS display_status", false)
			->join('loan_transaction_items li', 'li.id = br.loan_transaction_item_id', 'left')
			->order_by('br.created_at', 'DESC')
			->order_by('br.id', 'DESC')
			->limit(max(1, min(100, (int) $limit)), max(0, (int) $offset))
			->get()
			->result_array();
	}

	public function get_member_book_requests($member_id, $limit = 5)
	{
		return $this->db
			->select("br.*, b.title, li.loan_status AS linked_loan_status, li.actual_return_at AS linked_actual_return_at, li.local_return_note AS return_note,
				COALESCE(NULLIF(br.admin_note, ''), NULLIF(li.local_return_note, '')) AS staff_note,
				CASE
					WHEN br.status = 'fulfilled' AND br.loan_transaction_item_id IS NULL THEN 'completed_legacy'
					WHEN br.status = 'fulfilled' AND (li.actual_return_at IS NOT NULL OR UPPER(COALESCE(li.loan_status, '')) = 'RETURN') THEN 'returned'
					WHEN br.status = 'fulfilled' THEN 'active'
					ELSE br.status
				END AS display_status", false)
			->from('book_requests br')
			->join('books b', 'b.id = br.book_id', 'left')
			->join('loan_transaction_items li', 'li.id = br.loan_transaction_item_id', 'left')
			->where('br.member_id', (int) $member_id)
			->order_by('br.created_at', 'DESC')
			->limit(max(1, min(20, (int) $limit)))
			->get()
			->result_array();
	}

	public function update_book_request_status($id, $status, $admin_note, $processed_by)
	{
		$status = trim((string) $status);
		if (! in_array($status, ['approved', 'rejected', 'cancelled'], true)) {
			throw new RuntimeException('Status request tidak valid. Peminjaman hanya boleh dicatat melalui tombol Serahkan & Catat Pinjam.');
		}
		$request = $this->db->from('book_requests')->where('id', (int) $id)->limit(1)->get()->row_array();
		if (! $request) {
			throw new RuntimeException('Request buku tidak ditemukan.');
		}
		if (($request['status'] ?? '') === 'fulfilled') {
			throw new RuntimeException('Request yang sudah menjadi peminjaman tidak dapat diubah. Kelola melalui transaksi peminjaman.');
		}
		$current_status = (string) ($request['status'] ?? 'pending');
		if (($current_status === 'pending' && ! in_array($status, ['approved', 'rejected', 'cancelled'], true)) || ($current_status === 'approved' && $status !== 'cancelled') || in_array($current_status, ['rejected', 'cancelled'], true)) {
			throw new RuntimeException('Perubahan status request tidak sesuai alur layanan.');
		}
		$this->db->trans_begin();
		try {
			if (in_array($status, ['rejected', 'cancelled'], true) && in_array(($request['status'] ?? ''), ['pending', 'approved'], true) && ! empty($request['book_item_id'])) {
				$this->db->where('id', (int) $request['book_item_id'])->where('status', 'reserved')->update('book_items', [
					'status' => 'available',
					'updated_at' => date('Y-m-d H:i:s'),
				]);
			}
		$this->db
			->where('id', (int) $id)
			->update('book_requests', [
				'status' => $status,
				'admin_note' => $this->blank_to_null($admin_note),
				'processed_by' => (int) $processed_by ?: null,
				'processed_at' => date('Y-m-d H:i:s'),
			]);
			if (! $this->db->trans_status()) {
				throw new RuntimeException('Status request gagal diperbarui.');
			}
			$this->db->trans_commit();
			return true;
		} catch (Throwable $e) {
			$this->db->trans_rollback();
			throw $e;
		}
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

		$this->apply_catalog_keyword_search($filters['q'] ?? '', true);

		$category = trim((string) ($filters['category'] ?? ''));
		if ($category !== '') {
			$this->db->where('i.category_name', $category);
		}

		$collection_type = trim((string) ($filters['collection_type'] ?? ''));
		if ($collection_type !== '') {
			$this->db->where('i.collection_type', $collection_type);
		}

		$content_category = (int) ($filters['content_category_id'] ?? 0);
		if ($content_category > 0) {
			$this->db->where('b.content_category_id', $content_category);
		}

		$content_classification = (int) ($filters['content_classification_id'] ?? 0);
		if ($content_classification > 0) {
			$this->db->where('b.content_classification_id', $content_classification);
		}

		if (! empty($filters['textbook_only'])) {
			$this->db->where('cc.code', 'buku-pelajaran');
		}
		$textbook_grade_id = (int) ($filters['textbook_grade_id'] ?? 0);
		if ($textbook_grade_id > 0 && $this->db->table_exists('book_textbook_grade_tags')) {
			$this->db->where("EXISTS (SELECT 1 FROM book_textbook_grade_tags textbook_grade_filter WHERE textbook_grade_filter.book_id = b.id AND textbook_grade_filter.grade_level_id = {$textbook_grade_id})", null, false);
		}
		$textbook_subject_id = (int) ($filters['textbook_subject_id'] ?? 0);
		if ($textbook_subject_id > 0 && $this->db->table_exists('book_textbook_subject_tags')) {
			$this->db->where("EXISTS (SELECT 1 FROM book_textbook_subject_tags textbook_subject_filter WHERE textbook_subject_filter.book_id = b.id AND textbook_subject_filter.subject_id = {$textbook_subject_id})", null, false);
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
			$this->db->where('i.is_loanable', 1);
		} elseif ($availability === 'with_items') {
			$this->db->where('i.id IS NOT NULL', null, false);
		} elseif ($availability === 'digital') {
			$this->db->where("EXISTS (SELECT 1 FROM digital_assets da_filter WHERE da_filter.book_id = b.id AND da_filter.status = 'active' AND da_filter.reader_audience = 'member')", null, false);
		}
	}

	private function first_available_public_item($book_id)
	{
		return $this->db
			->from('book_items')
			->where('book_id', (int) $book_id)
			->where('deleted_at IS NULL', null, false)
			->where('is_public', 1)
			->where('is_loanable', 1)
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
			->limit(max(1, min(1000, (int) $limit)));

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
		return (bool)$this->db->where('id', (int) $item_id)->update('book_items', $payload);
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
		$payload['cover_migration_status'] = ! empty($payload['cover_local_path']) ? 'copied' : (empty($payload['cover_path']) ? 'skipped' : 'pending');
		if (! empty($payload['cover_local_path'])) $payload['cover_migrated_at'] = date('Y-m-d H:i:s');
		$payload['created_by'] = $created_by ? (int) $created_by : null;
		$payload['updated_by'] = $created_by ? (int) $created_by : null;

		$this->db->insert('books', $payload);
		$book_id = (int) $this->db->insert_id();
		$this->sync_catalog_primary_item($book_id, $data, $payload);
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

		if (! empty($payload['cover_local_path'])) {
			$payload['cover_path'] = $payload['cover_local_path'];
			$payload['cover_source_path'] = null;
			$payload['cover_migration_status'] = 'copied';
			$payload['cover_migrated_at'] = date('Y-m-d H:i:s');
		} elseif ((string) ($book['cover_path'] ?? '') !== (string) ($payload['cover_path'] ?? '')) {
			$payload['cover_source_path'] = $payload['cover_path'];
			$payload['cover_local_path'] = null;
			$payload['cover_migration_status'] = empty($payload['cover_path']) ? 'skipped' : 'pending';
			$payload['cover_migrated_at'] = null;
		}

		$this->db->where('id', (int) $id)->update('books', $payload);
		if (empty($book['source_system']) || $book['source_system'] === 'manual') {
			$this->sync_catalog_primary_item((int) $id, $data, $payload);
		}
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

	/** Keyset pagination bounds memory for downloads of the entire filtered catalog. */
	public function export_batch(array $filters, $scope_library_id, $after_id = 0, $limit = 250)
	{
		$this->apply_book_filters($filters, $scope_library_id);
		return $this->db->select("b.*, cc.name AS content_category_name, cm.name AS content_classification_name,
			COUNT(DISTINCT i.id) AS item_count,
			COUNT(DISTINCT CASE WHEN i.status = 'available' THEN i.id END) AS available_count,
			GROUP_CONCAT(DISTINCT COALESCE(NULLIF(i.location_library_name, ''), l.name) SEPARATOR ' | ') AS libraries,
			GROUP_CONCAT(DISTINCT NULLIF(i.collection_type, '') SEPARATOR ' | ') AS collection_types,
			(SELECT GROUP_CONCAT(a.name ORDER BY a.sort_order SEPARATOR ' | ') FROM book_authors a WHERE a.book_id=b.id) AS authors,
			(SELECT GROUP_CONCAT(s.subject ORDER BY s.id SEPARATOR ' | ') FROM book_subjects s WHERE s.book_id=b.id) AS subjects", false)
			->join('book_content_categories cc', 'cc.id=b.content_category_id', 'left')
			->join('book_classification_masters cm', 'cm.id=b.content_classification_id', 'left')
			->where('b.id >', (int) $after_id)->group_by('b.id')->order_by('b.id', 'ASC')
			->limit(max(1, min(500, (int) $limit)))->get()->result_array();
	}

	public function annual_development(array $filters, $scope_library_id)
	{
		$this->apply_book_filters($filters, $scope_library_id);
		// Use the original INLIS cataloguing date, not the later migration date.
		$this->db->join('inlislite_v3.catalogs original', "b.source_system='inlislite_v3' AND original.ID=CAST(b.source_id AS UNSIGNED)", 'left', false);
		$date = "CASE WHEN b.source_system='inlislite_v3' THEN original.CreateDate ELSE b.created_at END";
		$year = "CASE WHEN YEAR($date) BETWEEN 1900 AND ".(int) date('Y')." THEN YEAR($date) ELSE 0 END";
		// Ebook item records are not physical copies. Asset existence must not multiply items.
		$digital_item = "(LOWER(TRIM(COALESCE(i.collection_type,''))) IN ('ebook','e-book','e book','buku digital','digital','audiobook') OR LOWER(TRIM(COALESCE(i.media_name,''))) IN ('digital','pdf','epub','ebook','e-book','audiobook'))";
		$physical_item = "(i.id IS NOT NULL AND NOT $digital_item)";
		$digital_asset = "EXISTS (SELECT 1 FROM digital_assets report_asset WHERE report_asset.book_id=b.id AND report_asset.status='active')";
		$per_book = $this->db->select("b.id, $year AS year, COUNT(DISTINCT i.id) AS copies,
			COUNT(DISTINCT CASE WHEN $physical_item THEN i.id END) AS physical_copies,
			MAX(CASE WHEN $physical_item THEN 1 ELSE 0 END) AS has_physical,
			CASE WHEN MAX(CASE WHEN i.id IS NOT NULL AND $digital_item THEN 1 ELSE 0 END)=1 OR $digital_asset THEN 1 ELSE 0 END AS has_digital", false)
			->group_by('b.id')->group_by($year,false)->get_compiled_select();
		$raw = $this->db->query("SELECT year, COUNT(*) titles, SUM(copies) copies, SUM(physical_copies) physical_copies,
			SUM(has_physical) physical_titles, SUM(has_digital) digital_titles,
			SUM(has_physical=1 AND has_digital=1) hybrid_titles,
			SUM(has_physical=0 AND has_digital=0) unclassified_titles
			FROM ($per_book) classified GROUP BY year ORDER BY year ASC")->result_array();
		$zero = array_fill_keys(['titles','copies','physical_copies','physical_titles','digital_titles','hybrid_titles','unclassified_titles'],0);
		$known = []; $unknown = $zero;
		foreach ($raw as $row) {
			if (!(int) $row['year']) $unknown = $row;
			else $known[(int) $row['year']] = $row;
		}
		$rows = []; $cumulative = 0; $physical_cumulative = 0; $digital_cumulative = 0; $previous = null;
		if ($known) {
			for ($y = min(array_keys($known)); $y <= (int) date('Y'); $y++) {
				$row = $known[$y] ?? array_merge(['year'=>$y],$zero);
				foreach ($zero as $key=>$value) $row[$key] = (int)$row[$key];
				$cumulative += $row['titles']; $row['cumulative'] = $cumulative;
				$physical_cumulative += $row['physical_titles']; $row['physical_cumulative'] = $physical_cumulative;
				$digital_cumulative += $row['digital_titles']; $row['digital_cumulative'] = $digital_cumulative;
				$row['growth'] = $previous > 0 ? round(($row['titles'] - $previous) / $previous * 100, 1) : null;
				$previous = $row['titles']; $rows[] = $row;
			}
		}
		$out = ['rows'=>$rows, 'unknown'=>$unknown];
		foreach ($zero as $key=>$value) $out['total_'.$key] = array_sum(array_column($rows,$key)) + (int)$unknown[$key];
		return $out;
	}

	private function apply_book_filters(array $filters = [], $scope_library_id = null)
	{
		$this->db
			->from('books b')
			->join('book_items i', 'i.book_id = b.id AND i.deleted_at IS NULL', 'left')
			->join('libraries l', 'l.id = i.library_id', 'left')
			->where('b.deleted_at IS NULL', null, false);

		if (! empty($scope_library_id)) {
			$this->db->where('i.library_id', (int) $scope_library_id);
		}

		$this->apply_catalog_keyword_search($filters['q'] ?? '');

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

		$source_system = trim((string) ($filters['source_system'] ?? ''));
		if ($source_system !== '') {
			if ($source_system === 'manual') {
				$this->db->where('(b.source_system IS NULL OR b.source_system = \'\' OR b.source_system = \'manual\')', null, false);
			} else {
				$this->db->where('b.source_system', $source_system);
			}
		}

		$category = trim((string) ($filters['category'] ?? ''));
		if ($category !== '') {
			$this->db->where('i.category_name', $category);
		}

		$collection_type = trim((string) ($filters['collection_type'] ?? ''));
		if ($collection_type !== '') {
			$this->db->where('i.collection_type', $collection_type);
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

		$availability = trim((string) ($filters['availability'] ?? ''));
		if ($availability === 'available') {
			$this->db->where('i.status', 'available');
		} elseif ($availability === 'with_items') {
			$this->db->where('i.id IS NOT NULL', null, false);
		} elseif ($availability === 'digital') {
			$this->db->where("EXISTS (SELECT 1 FROM digital_assets da_filter WHERE da_filter.book_id = b.id AND da_filter.status = 'active' AND da_filter.reader_audience = 'member')", null, false);
		}

		$year = trim((string) ($filters['publish_year'] ?? ''));
		if ($year !== '') {
			$this->db->where('b.publish_year', $year);
		}
	}

	/**
	 * Pencarian katalog berbasis kata. Data sumber INLISLite kadang memiliki
	 * spasi ganda/baris baru pada judul; memecah input menjadi kata membuat
	 * pencarian tetap menemukan judul tersebut tanpa mengorbankan pencarian
	 * ISBN, pengarang, atau barcode.
	 */
	private function apply_catalog_keyword_search($query, $include_location = false)
	{
		$query = trim((string) $query);
		if ($query === '') {
			return;
		}
		$terms = preg_split('/[\s\p{Z}]+/u', $query, -1, PREG_SPLIT_NO_EMPTY);
		foreach ($terms as $term) {
			// Nomor jilid/kelas yang pendek harus cocok pada judul. Bila dicari
			// ke semua metadata, angka seperti "1" akan mengenai call number
			// atau barcode buku lain dan hasilnya menjadi menyesatkan.
			if (preg_match('/^\d+$/', $term)) {
				$this->db->group_start()->like('b.title', $term)->group_end();
				continue;
			}
			$this->db->group_start()
				->like('b.title', $term)
				->or_like('b.statement_responsibility', $term)
				->or_like('b.publisher', $term)
				->or_like('b.isbn', $term)
				->or_like('b.call_number', $term)
				->or_like('i.barcode', $term);
			if ($include_location) {
				$this->db->or_like('i.location_name', $term);
			}
			$this->db->group_end();
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
				// Status sirkulasi lokal lebih baru daripada snapshot INLISLite.
				// Jangan sampai sync menghidupkan kembali item yang sedang ditahan
				// untuk reservasi atau sedang dipinjam melalui aplikasi ini.
				if (($existing['status'] ?? '') === 'reserved' && $this->has_active_item_reservation((int) $existing['id'])) {
					$payload['status'] = 'reserved';
				} elseif (($existing['status'] ?? '') === 'loaned' && $this->has_active_local_loan((int) $existing['id'])) {
					$payload['status'] = 'loaned';
				}
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

	private function has_active_item_reservation($item_id)
	{
		return $this->db->from('book_requests')
			->where('book_item_id', (int) $item_id)
			->where_in('status', ['pending', 'approved'])
			->limit(1)
			->count_all_results() > 0;
	}

	private function has_active_local_loan($item_id)
	{
		return $this->db->from('loan_transaction_items')
			->where('book_item_id', (int) $item_id)
			->where('source_system', 'pustaka')
			->where('actual_return_at IS NULL', null, false)
			->where("UPPER(COALESCE(loan_status, '')) = 'LOAN'", null, false)
			->limit(1)
			->count_all_results() > 0;
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
			'collection_type' => $this->clip($data['collection_type'] ?? null, 120),
			'status' => in_array(($data['status'] ?? 'unknown'), ['available', 'reserved', 'loaned', 'missing', 'damaged', 'unknown'], true) ? $data['status'] : 'unknown',
			'is_public' => ! empty($data['is_public']) ? 1 : 0,
			'is_loanable' => ! empty($data['is_loanable']) ? 1 : 0,
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
		$payload['collection_type'] = $this->clip($payload['collection_type'] ?: $payload['category_name'], 120);

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
		$payload = [
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
		$uploaded_cover = $this->clip($data['uploaded_cover_path'] ?? null, 500);
		if ($uploaded_cover !== null) {
			$payload['cover_path'] = $uploaded_cover;
			$payload['cover_source_path'] = null;
			$payload['cover_local_path'] = $uploaded_cover;
		}
		return $payload;
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

	private function sync_catalog_primary_item($book_id, array $data, array $book_payload)
	{
		$type = $this->clip($data['collection_type'] ?? null, 120);
		if ($type === null) {
			return;
		}
		$primary_source_id = 'catalog-primary-' . (int) $book_id;

		$item = $this->db
			->from('book_items')
			->where('book_id', (int) $book_id)
			->where('source_system', 'manual')
			->group_start()
				->where('source_id', $primary_source_id)
				->or_where('source_id', 'catalog-primary')
			->group_end()
			->limit(1)
			->get()
			->row_array();

		$media = strtolower($type) === 'ebook' ? 'Digital' : $type;
		$common = [
			'collection_type' => $type,
			'category_name' => $type,
			'media_name' => $media,
			'call_number' => $book_payload['call_number'] ?? null,
			'deleted_at' => null,
			'updated_at' => date('Y-m-d H:i:s'),
		];

		if ($item) {
			$common['source_id'] = $primary_source_id;
			$this->db->where('id', (int) $item['id'])->update('book_items', $common);
			return;
		}

		$this->db->insert('book_items', array_merge($common, [
			'book_id' => (int) $book_id,
			'source_system' => 'manual',
			'source_id' => $primary_source_id,
			'item_code' => 'CAT-' . (int) $book_id,
			'location_name' => 'Belum ditentukan',
			'rule_name' => 'Belum ditentukan',
			'source_name' => 'Input katalog',
			'status' => 'unknown',
			'status_label' => 'Belum diverifikasi',
			'is_public' => 1,
		]));
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

	private function apply_highlight_filters(array $filters = [])
	{
		$this->db->from('catalog_highlights ch');

		$q = trim((string) ($filters['q'] ?? ''));
		if ($q !== '') {
			$this->db
				->join('books b_filter', 'b_filter.id = ch.book_id', 'left')
				->join('book_content_categories cc_filter', 'cc_filter.id = ch.content_category_id', 'left')
				->group_start()
					->like('ch.label', $q)
					->or_like('ch.title_override', $q)
					->or_like('ch.summary', $q)
					->or_like('b_filter.title', $q)
					->or_like('b_filter.statement_responsibility', $q)
					->or_like('cc_filter.name', $q)
				->group_end();
		}

		$target = trim((string) ($filters['target_type'] ?? ''));
		if (in_array($target, ['book', 'category'], true)) {
			$this->db->where('ch.target_type', $target);
		}

		$type = trim((string) ($filters['highlight_type'] ?? ''));
		if (in_array($type, ['featured', 'new_arrival', 'digital', 'local', 'recommendation'], true)) {
			$this->db->where('ch.highlight_type', $type);
		}

		$status = trim((string) ($filters['status'] ?? ''));
		if ($status === 'active') {
			$this->db->where('ch.is_active', 1);
		} elseif ($status === 'inactive') {
			$this->db->where('ch.is_active', 0);
		}
	}

	private function highlight_payload(array $data)
	{
		$target_type = in_array(($data['target_type'] ?? 'book'), ['book', 'category'], true) ? $data['target_type'] : 'book';
		$highlight_type = in_array(($data['highlight_type'] ?? 'featured'), ['featured', 'new_arrival', 'digital', 'local', 'recommendation'], true)
			? $data['highlight_type']
			: 'featured';
		$book_id = (int) ($data['book_id'] ?? 0);
		$content_category_id = (int) ($data['content_category_id'] ?? 0);

		if ($target_type === 'book' && $book_id <= 0) {
			throw new RuntimeException('Pilih buku yang akan di-highlight.');
		}

		if ($target_type === 'category' && $content_category_id <= 0) {
			throw new RuntimeException('Pilih kategori katalog yang akan di-highlight.');
		}

		return [
			'target_type' => $target_type,
			'book_id' => $target_type === 'book' ? $book_id : null,
			'content_category_id' => $target_type === 'category' ? $content_category_id : null,
			'highlight_type' => $highlight_type,
			'label' => $this->clip($data['label'] ?? null, 120),
			'title_override' => $this->clip($data['title_override'] ?? null, 180),
			'summary' => $this->clip($data['summary'] ?? null, 255),
			'sort_order' => max(0, min(9999, (int) ($data['sort_order'] ?? 100))),
			'is_active' => ! empty($data['is_active']) ? 1 : 0,
			'starts_at' => $this->datetime_or_null($data['starts_at'] ?? null),
			'ends_at' => $this->datetime_or_null($data['ends_at'] ?? null),
		];
	}

	private function datetime_or_null($value)
	{
		$value = trim(str_replace('T', ' ', (string) $value));
		if ($value === '') {
			return null;
		}

		if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $value)) {
			$value .= ':00';
		}

		$time = strtotime($value);
		if ($time === false) {
			return null;
		}

		return date('Y-m-d H:i:s', $time);
	}
}
