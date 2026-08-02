<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transaction_sync_model extends CI_Model
{
	const SOURCE_SYSTEM = 'inlislite_v3';

	public function stats()
	{
		return [
			'member_visits' => $this->table_count('member_visits'),
			'access_rules' => $this->table_count('member_access_rules'),
			'loan_transactions' => $this->table_count('loan_transactions'),
			'loan_items' => $this->table_count('loan_transaction_items'),
			'digital_sessions' => $this->table_count('reading_sessions'),
			'external_digital_sessions' => $this->table_count('reading_sessions', ['access_origin' => 'external']),
			'sync_runs' => $this->table_count('transaction_sync_runs'),
		];
	}

	public function source_stats()
	{
		$source_db = $this->load->database('inlislite', true);
		$tables = [
			'memberguesses' => 'Kunjungan tamu',
			'memberloanauthorizecategory' => 'Hak pinjam kategori',
			'memberloanauthorizelocation' => 'Hak pinjam lokasi',
			'collectionloans' => 'Transaksi pinjam',
			'collectionloanitems' => 'Detail pinjam',
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

	public function recent_runs($limit = 12)
	{
		if (! $this->db->table_exists('transaction_sync_runs')) {
			return [];
		}

		return $this->db
			->from('transaction_sync_runs')
			->order_by('id', 'DESC')
			->limit(max(1, min(30, (int) $limit)))
			->get()
			->result_array();
	}

	public function count_visits(array $filters = [])
	{
		$this->apply_visit_filters($filters);
		return (int) $this->db->count_all_results();
	}

	public function get_visits(array $filters = [], $limit = 25, $offset = 0)
	{
		$this->apply_visit_filters($filters);

		return $this->db
			->select('mv.*, m.full_name AS member_name, m.member_no')
			->order_by('mv.visited_at', 'DESC')
			->order_by('mv.id', 'DESC')
			->limit($this->bounded_limit($limit), max(0, (int) $offset))
			->get()
			->result_array();
	}

	public function count_access_rules(array $filters = [])
	{
		$this->apply_access_filters($filters);
		return (int) $this->db->count_all_results();
	}

	public function get_access_rules(array $filters = [], $limit = 25, $offset = 0)
	{
		$this->apply_access_filters($filters);

		return $this->db
			->select('r.*, m.full_name AS member_name, m.member_no')
			->order_by('r.id', 'DESC')
			->limit($this->bounded_limit($limit), max(0, (int) $offset))
			->get()
			->result_array();
	}

	public function count_loan_transactions(array $filters = [])
	{
		$this->apply_loan_filters($filters);
		return (int) $this->db->count_all_results();
	}

	public function get_loan_transactions(array $filters = [], $limit = 25, $offset = 0)
	{
		$this->apply_loan_filters($filters);

		return $this->db
			->select('lt.*, m.full_name AS member_name, m.member_no, loc.name AS location_library_name')
			->order_by('lt.source_created_at', 'DESC')
			->order_by('lt.id', 'DESC')
			->limit($this->bounded_limit($limit), max(0, (int) $offset))
			->get()
			->result_array();
	}

	public function count_loan_items(array $filters = [])
	{
		$this->apply_loan_item_filters($filters);
		return (int) $this->db->count_all_results();
	}

	public function get_loan_items(array $filters = [], $limit = 25, $offset = 0)
	{
		$this->apply_loan_item_filters($filters);

		return $this->db
			->select('li.*, m.full_name AS member_name, m.member_no, b.title, bi.barcode, bi.call_number')
			->order_by('li.loan_date', 'DESC')
			->order_by('li.id', 'DESC')
			->limit($this->bounded_limit($limit), max(0, (int) $offset))
			->get()
			->result_array();
	}

	private function apply_visit_filters(array $filters = [])
	{
		$this->db
			->from('member_visits mv')
			->join('members m', 'm.id = mv.member_id', 'left');

		$q = trim((string) ($filters['q'] ?? ''));
		if ($q !== '') {
			$this->db->group_start()
				->like('mv.visitor_name', $q)
				->or_like('mv.source_member_no', $q)
				->or_like('mv.visitor_no', $q)
				->or_like('m.full_name', $q)
				->or_like('m.member_no', $q)
				->or_like('mv.location_label', $q)
				->or_like('mv.address', $q)
				->group_end();
		}

		$channel = trim((string) ($filters['visit_channel'] ?? ''));
		if ($channel !== '' && $this->db->field_exists('visit_channel', 'member_visits')) {
			$this->db->where('mv.visit_channel', $channel);
		}

		$this->apply_date_filters('mv.visited_at', $filters);
	}

	private function apply_access_filters(array $filters = [])
	{
		$this->db
			->from('member_access_rules r')
			->join('members m', 'm.id = r.member_id', 'left');

		$q = trim((string) ($filters['q'] ?? ''));
		if ($q !== '') {
			$this->db->group_start()
				->like('m.full_name', $q)
				->or_like('m.member_no', $q)
				->or_like('r.source_member_id', $q)
				->or_like('r.rule_label', $q)
				->or_like('r.source_rule_id', $q)
				->group_end();
		}

		$rule_type = trim((string) ($filters['rule_type'] ?? ''));
		if (in_array($rule_type, ['category', 'location'], true)) {
			$this->db->where('r.rule_type', $rule_type);
		}
	}

	private function apply_loan_filters(array $filters = [])
	{
		$this->db
			->from('loan_transactions lt')
			->join('members m', 'm.id = lt.member_id', 'left')
			->join('inlislite_master_references loc', "loc.source_table = 'location_library' AND loc.source_id = lt.location_library_id", 'left');

		$q = trim((string) ($filters['q'] ?? ''));
		if ($q !== '') {
			$this->db->group_start()
				->like('m.full_name', $q)
				->or_like('m.member_no', $q)
				->or_like('lt.source_member_id', $q)
				->or_like('lt.source_id', $q)
				->or_like('loc.name', $q)
				->group_end();
		}

		$this->apply_date_filters('lt.source_created_at', $filters);
	}

	private function apply_loan_item_filters(array $filters = [])
	{
		$this->db
			->from('loan_transaction_items li')
			->join('members m', 'm.id = li.member_id', 'left')
			->join('book_items bi', 'bi.id = li.book_item_id', 'left')
			->join('books b', 'b.id = bi.book_id', 'left');

		$q = trim((string) ($filters['q'] ?? ''));
		if ($q !== '') {
			$this->db->group_start()
				->like('m.full_name', $q)
				->or_like('m.member_no', $q)
				->or_like('b.title', $q)
				->or_like('bi.barcode', $q)
				->or_like('li.source_collection_id', $q)
				->or_like('li.source_loan_id', $q)
				->group_end();
		}

		$status = trim((string) ($filters['loan_status'] ?? ''));
		if ($status !== '') {
			$this->db->where('li.loan_status', $status);
		}

		$this->apply_date_filters('li.loan_date', $filters);
	}

	private function apply_date_filters($field, array $filters = [])
	{
		$date_from = trim((string) ($filters['date_from'] ?? ''));
		if ($date_from !== '') {
			$this->db->where($field . ' >=', $date_from . ' 00:00:00');
		}

		$date_to = trim((string) ($filters['date_to'] ?? ''));
		if ($date_to !== '') {
			$this->db->where($field . ' <=', $date_to . ' 23:59:59');
		}
	}

	private function bounded_limit($limit)
	{
		return max(1, min(100, (int) $limit));
	}

	public function run_manual_sync($created_by = null, $domain = 'all', $limit = 5000, $mode = 'import_new')
	{
		$domain = in_array($domain, ['all', 'visits', 'access_rules', 'loans'], true) ? $domain : 'all';
		$mode = in_array($mode, ['import_new', 'refresh_existing', 'dry_run'], true) ? $mode : 'import_new';
		$limit = max(1, min(10000, (int) $limit));
		$run_id = $this->create_run($created_by, $domain, $mode);
		$stats = ['processed' => 0, 'inserted' => 0, 'updated' => 0, 'failed' => 0];

		if ($mode === 'dry_run') {
			$stats['processed'] = $this->count_candidates($domain, 'import_new');
			$message = sprintf('Dry run transaksi selesai. Kandidat data baru: %d.', $stats['processed']);
			$this->finish_run($run_id, $mode, $stats, $message);
			return ['run_id' => $run_id, 'message' => $message] + $stats;
		}

		foreach ($this->domain_sequence($domain) as $type) {
			$remaining = $limit - $stats['processed'];
			if ($remaining <= 0) {
				break;
			}

			try {
				$result = $this->process_type($type, $remaining, $mode);
				foreach ($stats as $key => $value) {
					$stats[$key] += (int) ($result[$key] ?? 0);
				}
			} catch (Throwable $e) {
				$stats['failed']++;
			}
		}
		$this->refresh_master_labels($domain);

		$message = sprintf(
			'Sinkronisasi transaksi selesai. Diproses: %d, baru: %d, update: %d, gagal: %d.',
			$stats['processed'],
			$stats['inserted'],
			$stats['updated'],
			$stats['failed']
		);
		$this->finish_run($run_id, $mode, $stats, $message);

		return ['run_id' => $run_id, 'message' => $message] + $stats;
	}

	private function process_type($type, $limit, $mode)
	{
		if ($type === 'visits') {
			return $this->sync_visits($limit, $mode);
		}
		if ($type === 'access_category') {
			return $this->sync_access_category($limit, $mode);
		}
		if ($type === 'access_location') {
			return $this->sync_access_location($limit, $mode);
		}
		if ($type === 'loans') {
			return $this->sync_loans($limit, $mode);
		}

		return $this->sync_loan_items($limit, $mode);
	}

	private function sync_visits($limit, $mode)
	{
		$where = $mode === 'refresh_existing' ? 'mv.id IS NOT NULL' : 'mv.id IS NULL';
		$before = $this->table_count('member_visits');
		$processed = $this->candidate_count(
			"SELECT COUNT(*) total FROM (
				SELECT g.ID
				FROM inlislite_v3.memberguesses g
				LEFT JOIN member_visits mv ON mv.source_system = ? AND mv.source_id = CONVERT(CAST(g.ID AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
				WHERE {$where}
				LIMIT " . (int) $limit . "
			) x",
			[self::SOURCE_SYSTEM]
		);

		$this->db->query(
			"INSERT INTO member_visits (
				source_system, source_id, member_id, source_member_no, visitor_no, visitor_name,
				gender_id, profession_id, education_id, status_id, location_id, location_loan_id,
				purpose_id, address, description, information, visited_at, source_created_at, source_updated_at
			)
			SELECT ?, CONVERT(CAST(g.ID AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci, m.id,
				CONVERT(g.NoAnggota USING utf8mb4) COLLATE utf8mb4_unicode_ci,
				CONVERT(g.NoPengunjung USING utf8mb4) COLLATE utf8mb4_unicode_ci,
				CONVERT(g.Nama USING utf8mb4) COLLATE utf8mb4_unicode_ci,
				CONVERT(CAST(g.JenisKelamin_id AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci,
				CONVERT(CAST(g.Profesi_id AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci,
				CONVERT(CAST(g.PendidikanTerakhir_id AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci,
				CONVERT(CAST(g.Status_id AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci,
				CONVERT(CAST(g.Location_Id AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci,
				CONVERT(CAST(g.LOCATIONLOANS_ID AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci,
				CONVERT(CAST(g.TujuanKunjungan_Id AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci,
				CONVERT(g.Alamat USING utf8mb4) COLLATE utf8mb4_unicode_ci,
				CONVERT(g.Deskripsi USING utf8mb4) COLLATE utf8mb4_unicode_ci,
				CONVERT(g.Information USING utf8mb4) COLLATE utf8mb4_unicode_ci,
				NULLIF(g.CreateDate, '0000-00-00 00:00:00'),
				NULLIF(g.CreateDate, '0000-00-00 00:00:00'),
				NULLIF(g.UpdateDate, '0000-00-00 00:00:00')
			FROM inlislite_v3.memberguesses g
			LEFT JOIN members m ON m.member_no = CONVERT(g.NoAnggota USING utf8mb4) COLLATE utf8mb4_unicode_ci AND m.deleted_at IS NULL
			LEFT JOIN member_visits mv ON mv.source_system = ? AND mv.source_id = CONVERT(CAST(g.ID AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
			WHERE {$where}
			ORDER BY g.ID ASC
			LIMIT " . (int) $limit . "
			ON DUPLICATE KEY UPDATE
				member_id = VALUES(member_id),
				visitor_name = VALUES(visitor_name),
				visited_at = VALUES(visited_at),
				source_updated_at = VALUES(source_updated_at)",
			[self::SOURCE_SYSTEM, self::SOURCE_SYSTEM]
		);

		return $this->result_stats($processed, $before, 'member_visits', $mode);
	}

	private function sync_access_category($limit, $mode)
	{
		return $this->sync_access_table('memberloanauthorizecategory', 'CategoryLoan_id', 'category', $limit, $mode);
	}

	private function sync_access_location($limit, $mode)
	{
		return $this->sync_access_table('memberloanauthorizelocation', 'LocationLoan_id', 'location', $limit, $mode);
	}

	private function sync_access_table($source_table, $rule_column, $rule_type, $limit, $mode)
	{
		$where = $mode === 'refresh_existing' ? 'r.id IS NOT NULL' : 'r.id IS NULL';
		$before = $this->table_count('member_access_rules');
		$processed = $this->candidate_count(
			"SELECT COUNT(*) total FROM (
				SELECT s.DataID
				FROM inlislite_v3.{$source_table} s
				LEFT JOIN member_access_rules r ON r.source_system = ? AND r.source_table = ? AND r.source_id = CONVERT(CAST(s.DataID AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
				WHERE {$where}
				LIMIT " . (int) $limit . "
			) x",
			[self::SOURCE_SYSTEM, $source_table]
		);

		$this->db->query(
			"INSERT INTO member_access_rules (
				source_system, source_table, source_id, member_id, source_member_id, rule_type,
				source_rule_id, created_at_source, updated_at_source
			)
			SELECT ?, ?, CONVERT(CAST(s.DataID AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci,
				m.id,
				CONVERT(CAST(s.Member_id AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci,
				?,
				CONVERT(CAST(s.{$rule_column} AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci,
				NULLIF(s.CreateDate, '0000-00-00 00:00:00'),
				NULLIF(s.UpdateDate, '0000-00-00 00:00:00')
			FROM inlislite_v3.{$source_table} s
			LEFT JOIN members m ON m.source_system = ? AND m.source_id = CONVERT(CAST(s.Member_id AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci AND m.deleted_at IS NULL
			LEFT JOIN member_access_rules r ON r.source_system = ? AND r.source_table = ? AND r.source_id = CONVERT(CAST(s.DataID AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
			WHERE {$where}
			ORDER BY s.DataID ASC
			LIMIT " . (int) $limit . "
			ON DUPLICATE KEY UPDATE
				member_id = VALUES(member_id),
				source_rule_id = VALUES(source_rule_id),
				updated_at_source = VALUES(updated_at_source)",
			[self::SOURCE_SYSTEM, $source_table, $rule_type, self::SOURCE_SYSTEM, self::SOURCE_SYSTEM, $source_table]
		);

		return $this->result_stats($processed, $before, 'member_access_rules', $mode);
	}

	private function sync_loans($limit, $mode)
	{
		$where = $mode === 'refresh_existing' ? 'lt.id IS NOT NULL' : 'lt.id IS NULL';
		$before = $this->table_count('loan_transactions');
		$processed = $this->candidate_count(
			"SELECT COUNT(*) total FROM (
				SELECT l.ID
				FROM inlislite_v3.collectionloans l
				LEFT JOIN loan_transactions lt ON lt.source_system = ? AND lt.source_id = CONVERT(l.ID USING utf8mb4) COLLATE utf8mb4_unicode_ci
				WHERE {$where}
				LIMIT " . (int) $limit . "
			) x",
			[self::SOURCE_SYSTEM]
		);

		$this->db->query(
			"INSERT INTO loan_transactions (
				source_system, source_id, member_id, source_member_id, branch_id, location_library_id,
				collection_count, loan_count, return_count, late_count, extend_count, source_created_at, source_updated_at
			)
			SELECT ?, CONVERT(l.ID USING utf8mb4) COLLATE utf8mb4_unicode_ci,
				m.id,
				CONVERT(CAST(l.Member_id AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci,
				CONVERT(CAST(l.Branch_id AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci,
				CONVERT(CAST(l.LocationLibrary_id AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci,
				IFNULL(l.CollectionCount, 0),
				IFNULL(l.LoanCount, 0),
				IFNULL(l.ReturnCount, 0),
				IFNULL(l.LateCount, 0),
				IFNULL(l.ExtendCount, 0),
				NULLIF(l.CreateDate, '0000-00-00 00:00:00'),
				NULLIF(l.UpdateDate, '0000-00-00 00:00:00')
			FROM inlislite_v3.collectionloans l
			LEFT JOIN members m ON m.source_system = ? AND m.source_id = CONVERT(CAST(l.Member_id AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci AND m.deleted_at IS NULL
			LEFT JOIN loan_transactions lt ON lt.source_system = ? AND lt.source_id = CONVERT(l.ID USING utf8mb4) COLLATE utf8mb4_unicode_ci
			WHERE {$where}
			ORDER BY l.ID ASC
			LIMIT " . (int) $limit . "
			ON DUPLICATE KEY UPDATE
				member_id = VALUES(member_id),
				collection_count = VALUES(collection_count),
				loan_count = VALUES(loan_count),
				return_count = VALUES(return_count),
				late_count = VALUES(late_count),
				extend_count = VALUES(extend_count),
				source_updated_at = VALUES(source_updated_at)",
			[self::SOURCE_SYSTEM, self::SOURCE_SYSTEM, self::SOURCE_SYSTEM]
		);

		return $this->result_stats($processed, $before, 'loan_transactions', $mode);
	}

	private function sync_loan_items($limit, $mode)
	{
		$where = $mode === 'refresh_existing' ? 'li.id IS NOT NULL' : 'li.id IS NULL';
		$before = $this->table_count('loan_transaction_items');
		$processed = $this->candidate_count(
			"SELECT COUNT(*) total FROM (
				SELECT i.ID
				FROM inlislite_v3.collectionloanitems i
				LEFT JOIN loan_transaction_items li ON li.source_system = ? AND li.source_id = CONVERT(CAST(i.ID AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
				WHERE {$where}
				LIMIT " . (int) $limit . "
			) x",
			[self::SOURCE_SYSTEM]
		);

		$this->db->query(
			"INSERT INTO loan_transaction_items (
				source_system, source_id, loan_transaction_id, source_loan_id, member_id, book_item_id,
				source_member_id, source_collection_id, loan_date, due_date, actual_return_at,
				late_days, loan_status, source_created_at, source_updated_at
			)
			SELECT ?, CONVERT(CAST(i.ID AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci,
				lt.id,
				CONVERT(i.CollectionLoan_id USING utf8mb4) COLLATE utf8mb4_unicode_ci,
				m.id,
				bi.id,
				CONVERT(CAST(i.member_id AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci,
				CONVERT(CAST(i.Collection_id AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci,
				NULLIF(i.LoanDate, '0000-00-00 00:00:00'),
				NULLIF(i.DueDate, '0000-00-00 00:00:00'),
				NULLIF(i.ActualReturn, '0000-00-00 00:00:00'),
				i.LateDays,
				CONVERT(i.LoanStatus USING utf8mb4) COLLATE utf8mb4_unicode_ci,
				NULLIF(i.CreateDate, '0000-00-00 00:00:00'),
				NULLIF(i.UpdateDate, '0000-00-00 00:00:00')
			FROM inlislite_v3.collectionloanitems i
			LEFT JOIN loan_transactions lt ON lt.source_system = ? AND lt.source_id = CONVERT(i.CollectionLoan_id USING utf8mb4) COLLATE utf8mb4_unicode_ci
			LEFT JOIN members m ON m.source_system = ? AND m.source_id = CONVERT(CAST(i.member_id AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci AND m.deleted_at IS NULL
			LEFT JOIN book_items bi ON bi.source_system = ? AND bi.source_id = CONVERT(CAST(i.Collection_id AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
			LEFT JOIN loan_transaction_items li ON li.source_system = ? AND li.source_id = CONVERT(CAST(i.ID AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
			WHERE {$where}
			ORDER BY i.ID ASC
			LIMIT " . (int) $limit . "
			ON DUPLICATE KEY UPDATE
				loan_transaction_id = VALUES(loan_transaction_id),
				member_id = VALUES(member_id),
				book_item_id = VALUES(book_item_id),
				loan_date = VALUES(loan_date),
				due_date = VALUES(due_date),
				actual_return_at = VALUES(actual_return_at),
				loan_status = VALUES(loan_status),
				source_updated_at = VALUES(source_updated_at)",
			[self::SOURCE_SYSTEM, self::SOURCE_SYSTEM, self::SOURCE_SYSTEM, self::SOURCE_SYSTEM, self::SOURCE_SYSTEM]
		);

		return $this->result_stats($processed, $before, 'loan_transaction_items', $mode);
	}

	private function create_run($created_by, $domain, $mode)
	{
		$this->db->insert('transaction_sync_runs', [
			'source_database' => self::SOURCE_SYSTEM,
			'source_table' => $domain,
			'sync_type' => $mode === 'dry_run' ? 'dry_run' : 'manual',
			'mode' => $mode,
			'status' => 'running',
			'started_at' => date('Y-m-d H:i:s'),
			'created_by' => $created_by ? (int) $created_by : null,
		]);

		return (int) $this->db->insert_id();
	}

	private function finish_run($run_id, $mode, array $stats, $message)
	{
		$this->db
			->where('id', (int) $run_id)
			->update('transaction_sync_runs', [
				'mode' => $mode,
				'status' => $stats['failed'] > 0 ? 'failed' : 'success',
				'finished_at' => date('Y-m-d H:i:s'),
				'total_source' => (int) $stats['processed'],
				'total_inserted' => (int) $stats['inserted'],
				'total_updated' => (int) $stats['updated'],
				'total_failed' => (int) $stats['failed'],
				'message' => $message,
			]);
	}

	private function result_stats($processed, $before, $table, $mode)
	{
		$after = $this->table_count($table);
		$inserted = max(0, $after - $before);
		return [
			'processed' => (int) $processed,
			'inserted' => $inserted,
			'updated' => $mode === 'refresh_existing' ? (int) $processed : max(0, (int) $processed - $inserted),
			'failed' => 0,
		];
	}

	private function refresh_master_labels($domain = 'all')
	{
		if (! $this->db->table_exists('inlislite_master_references')) {
			return;
		}

		if (($domain === 'all' || $domain === 'visits') && $this->db->field_exists('purpose_label', 'member_visits')) {
			$this->db->query(
				"UPDATE member_visits mv
				LEFT JOIN inlislite_master_references gender ON gender.source_table = 'jenis_kelamin' AND gender.source_id = mv.gender_id
				LEFT JOIN inlislite_master_references job ON job.source_table = 'master_pekerjaan' AND job.source_id = mv.profession_id
				LEFT JOIN inlislite_master_references edu ON edu.source_table = 'master_pendidikan' AND edu.source_id = mv.education_id
				LEFT JOIN inlislite_master_references st ON st.source_table = 'status_anggota' AND st.source_id = mv.status_id
				LEFT JOIN inlislite_master_references loc ON loc.source_table = 'locations' AND loc.source_id = mv.location_id
				LEFT JOIN inlislite_master_references loan_loc ON loan_loc.source_table = 'collectionlocations' AND loan_loc.source_id = mv.location_loan_id
				LEFT JOIN inlislite_master_references purpose ON purpose.source_table = 'tujuan_kunjungan' AND purpose.source_id = mv.purpose_id
				SET mv.gender_label = gender.name,
				    mv.profession_label = job.name,
				    mv.education_label = edu.name,
				    mv.visit_status_label = st.name,
				    mv.location_label = loc.name,
				    mv.location_loan_label = loan_loc.name,
				    mv.purpose_label = purpose.name"
			);
		}

		if (($domain === 'all' || $domain === 'access_rules') && $this->db->field_exists('rule_label', 'member_access_rules')) {
			$this->db->query(
				"UPDATE member_access_rules r
				LEFT JOIN inlislite_master_references cat ON r.rule_type = 'category' AND cat.source_table = 'collectioncategorys' AND cat.source_id = r.source_rule_id
				LEFT JOIN inlislite_master_references loc ON r.rule_type = 'location' AND loc.source_table = 'collectionlocations' AND loc.source_id = r.source_rule_id
				SET r.rule_label = COALESCE(cat.name, loc.name)"
			);
		}
	}

	private function count_candidates($domain, $mode)
	{
		$total = 0;
		foreach ($this->domain_sequence($domain) as $type) {
			$total += $this->candidate_for_type($type, $mode);
		}

		return $total;
	}

	private function candidate_for_type($type, $mode)
	{
		$map = [
			'visits' => ['source' => 'memberguesses', 'source_id' => 'ID', 'target' => 'member_visits', 'alias' => 'g', 'target_alias' => 'mv'],
			'access_category' => ['source' => 'memberloanauthorizecategory', 'source_id' => 'DataID', 'target' => 'member_access_rules', 'alias' => 's', 'target_alias' => 'r', 'source_table' => 'memberloanauthorizecategory'],
			'access_location' => ['source' => 'memberloanauthorizelocation', 'source_id' => 'DataID', 'target' => 'member_access_rules', 'alias' => 's', 'target_alias' => 'r', 'source_table' => 'memberloanauthorizelocation'],
			'loans' => ['source' => 'collectionloans', 'source_id' => 'ID', 'target' => 'loan_transactions', 'alias' => 'l', 'target_alias' => 'lt'],
			'loan_items' => ['source' => 'collectionloanitems', 'source_id' => 'ID', 'target' => 'loan_transaction_items', 'alias' => 'i', 'target_alias' => 'li'],
		];
		$item = $map[$type];
		$where = $mode === 'refresh_existing' ? $item['target_alias'] . '.id IS NOT NULL' : $item['target_alias'] . '.id IS NULL';
		$params = [self::SOURCE_SYSTEM];
		$extra = '';
		if (! empty($item['source_table'])) {
			$extra = ' AND ' . $item['target_alias'] . '.source_table = ?';
			$params[] = $item['source_table'];
		}

		return $this->candidate_count(
			"SELECT COUNT(*) total
			FROM inlislite_v3.{$item['source']} {$item['alias']}
			LEFT JOIN {$item['target']} {$item['target_alias']}
				ON {$item['target_alias']}.source_system = ?
				{$extra}
				AND {$item['target_alias']}.source_id = CONVERT(CAST({$item['alias']}.{$item['source_id']} AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
			WHERE {$where}",
			$params
		);
	}

	private function domain_sequence($domain)
	{
		if ($domain === 'visits') {
			return ['visits'];
		}
		if ($domain === 'access_rules') {
			return ['access_category', 'access_location'];
		}
		if ($domain === 'loans') {
			return ['loans', 'loan_items'];
		}

		return ['visits', 'access_category', 'access_location', 'loans', 'loan_items'];
	}

	private function candidate_count($sql, array $params = [])
	{
		$row = $this->db->query($sql, $params)->row_array();
		return (int) ($row['total'] ?? 0);
	}

	private function table_count($table, array $where = [])
	{
		if (! $this->db->table_exists($table)) {
			return 0;
		}
		if (! empty($where)) {
			$this->db->where($where);
			return (int) $this->db->count_all_results($table);
		}

		return (int) $this->db->count_all($table);
	}
}
