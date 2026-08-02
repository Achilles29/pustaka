<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Member_model extends CI_Model
{
	const SOURCE_SYSTEM = 'inlislite_v3';

	public function stats()
	{
		$members = 0;
		if ($this->db->table_exists('members')) {
			$members = (int) $this->db
				->from('members')
				->where('deleted_at IS NULL', null, false)
				->count_all_results();
		}
		$linked_users = 0;
		$photo_refs = 0;
		$sync_runs = $this->db->table_exists('member_sync_runs') ? (int) $this->db->count_all('member_sync_runs') : 0;

		if ($this->db->table_exists('auth_user')) {
			$linked_users = (int) $this->db
				->from('auth_user')
				->where('member_source_id IS NOT NULL', null, false)
				->count_all_results();
		}

		if ($this->db->table_exists('members')) {
			$photo_refs = (int) $this->db
				->from('members')
				->where('photo_path IS NOT NULL', null, false)
				->where('photo_path <>', '')
				->count_all_results();
		}

		return [
			'members' => $members,
			'linked_users' => $linked_users,
			'photo_refs' => $photo_refs,
			'sync_runs' => $sync_runs,
		];
	}

	public function form_options()
	{
		return [
			'identity_types' => $this->merge_options($this->reference_names('master_jenis_identitas'), ['NIK/KTP', 'KIA', 'Paspor', 'Kartu Pelajar', 'Lainnya']),
			'genders' => $this->merge_options($this->reference_names('jenis_kelamin'), ['Laki-laki', 'Perempuan']),
			'member_types' => $this->merge_options($this->reference_names('jenis_anggota'), ['Umum', 'Pelajar', 'Mahasiswa', 'Guru/Tenaga Pendidik', 'Peneliti', 'Komunitas/Lembaga', 'Istimewa']),
			'educations' => $this->merge_options($this->reference_names('master_pendidikan'), ['SD/Sederajat', 'SMP/Sederajat', 'SMA/SMK/MA', 'D1', 'D2', 'D3', 'S1', 'S2', 'S3', 'Lainnya']),
			'occupations' => $this->merge_options($this->reference_names('master_pekerjaan'), ['Pelajar', 'Mahasiswa', 'Guru', 'Dosen', 'Peneliti', 'ASN', 'TNI/POLRI', 'Pegawai Swasta', 'Wiraswasta', 'Pensiunan', 'Lainnya']),
		];
	}

	public function recent_members($limit = 12)
	{
		if (! $this->db->table_exists('members')) {
			return [];
		}

		return $this->db
			->from('members')
			->order_by('id', 'DESC')
			->limit(max(1, min(50, (int) $limit)))
			->get()
			->result_array();
	}

	public function count_members(array $filters = [])
	{
		if (! $this->db->table_exists('members')) {
			return 0;
		}

		$this->apply_member_filters($filters);

		return (int) $this->db->count_all_results();
	}

	public function get_members(array $filters = [], $limit = 25, $offset = 0)
	{
		if (! $this->db->table_exists('members')) {
			return [];
		}

		$this->apply_member_filters($filters);

		return $this->db
			->select('m.*, u.username, u.status AS user_status, u.force_password_change')
			->order_by('m.id', 'DESC')
			->limit(max(1, min(100, (int) $limit)), max(0, (int) $offset))
			->get()
			->result_array();
	}

	public function get_member($id)
	{
		if (! $this->db->table_exists('members')) {
			return null;
		}

		return $this->db
			->select('m.*, u.username, u.email AS user_email, u.status AS user_status, u.force_password_change, u.last_login_at')
			->from('members m')
			->join('auth_user u', 'u.id = m.auth_user_id', 'left')
			->where('m.id', (int) $id)
			->where('m.deleted_at IS NULL', null, false)
			->get()
			->row_array();
	}

	public function next_manual_member_no($date = null)
	{
		$timestamp = $date ? strtotime((string) $date) : time();
		$year = date('Y', $timestamp ?: time());
		$prefix = 'PDR-3317-' . $year . '-';
		$row = $this->db
			->select('member_no')
			->from('members')
			->like('member_no', $prefix, 'after')
			->order_by('member_no', 'DESC')
			->limit(1)
			->get()
			->row_array();

		$sequence = 1;
		if (! empty($row['member_no']) && preg_match('/-(\d{6})$/', $row['member_no'], $matches)) {
			$sequence = (int) $matches[1] + 1;
		}

		do {
			$member_no = $prefix . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
			$sequence++;
		} while ($this->member_no_exists($member_no));

		return $member_no;
	}

	public function get_member_by_auth_user_id($auth_user_id)
	{
		if (! $this->db->table_exists('members')) {
			return null;
		}

		return $this->db
			->select('m.*, u.username, u.email AS user_email, u.status AS user_status, u.force_password_change, u.last_login_at')
			->from('members m')
			->join('auth_user u', 'u.id = m.auth_user_id', 'left')
			->where('m.auth_user_id', (int) $auth_user_id)
			->where('m.deleted_at IS NULL', null, false)
			->get()
			->row_array();
	}

	public function digital_card_token(array $member)
	{
		$key = (string) $this->config->item('encryption_key');
		$seed = implode('|', [
			(int) ($member['id'] ?? 0),
			(string) ($member['member_no'] ?? ''),
			(string) ($member['source_id'] ?? ''),
		]);

		return substr(hash_hmac('sha256', $seed, $key), 0, 24);
	}

	public function verify_digital_card($member_id, $token)
	{
		$member = $this->get_member((int) $member_id);
		if (! $member) {
			return null;
		}

		if (($member['card_status'] ?? 'active') === 'blocked') {
			return null;
		}

		return hash_equals($this->digital_card_token($member), (string) $token) ? $member : null;
	}

	public function create_renewal_request($member_id, array $data = [])
	{
		$member = $this->get_member((int) $member_id);
		if (! $member) {
			throw new RuntimeException('Data member tidak ditemukan.');
		}

		$has_pending = $this->db
			->from('membership_renewal_requests')
			->where('member_id', (int) $member_id)
			->where('status', 'pending')
			->count_all_results() > 0;

		if ($has_pending) {
			throw new RuntimeException('Masih ada pengajuan perpanjangan yang menunggu proses.');
		}

		$months = (int) ($data['requested_months'] ?? 12);
		$months = in_array($months, [6, 12, 24], true) ? $months : 12;
		$payload = [
			'request_code' => $this->next_renewal_code(),
			'member_id' => (int) $member_id,
			'current_expired_at' => $member['expired_at'] ?: null,
			'requested_months' => $months,
			'status' => 'pending',
			'reason' => $this->blank_to_null($data['reason'] ?? null),
		];

		$this->db->insert('membership_renewal_requests', $payload);
		return [
			'id' => (int) $this->db->insert_id(),
			'code' => $payload['request_code'],
		];
	}

	public function get_member_renewal_requests($member_id, $limit = 5)
	{
		return $this->db
			->from('membership_renewal_requests')
			->where('member_id', (int) $member_id)
			->order_by('created_at', 'DESC')
			->limit(max(1, min(20, (int) $limit)))
			->get()
			->result_array();
	}

	public function count_renewal_requests(array $filters = [])
	{
		$this->apply_renewal_filters($filters);
		return (int) $this->db->count_all_results();
	}

	public function get_renewal_requests(array $filters = [], $limit = 25, $offset = 0)
	{
		$this->apply_renewal_filters($filters);

		return $this->db
			->select('rr.*, m.full_name, m.member_no, m.status AS member_status, m.expired_at, u.username')
			->order_by('rr.created_at', 'DESC')
			->order_by('rr.id', 'DESC')
			->limit(max(1, min(100, (int) $limit)), max(0, (int) $offset))
			->get()
			->result_array();
	}

	public function update_renewal_status($id, $status, $admin_note, $processed_by)
	{
		$status = in_array($status, ['approved', 'rejected', 'cancelled'], true) ? $status : 'pending';
		$request = $this->db
			->from('membership_renewal_requests')
			->where('id', (int) $id)
			->get()
			->row_array();

		if (! $request) {
			return false;
		}

		$this->db->trans_start();
		$this->db
			->where('id', (int) $id)
			->update('membership_renewal_requests', [
				'status' => $status,
				'admin_note' => $this->blank_to_null($admin_note),
				'processed_by' => (int) $processed_by ?: null,
				'processed_at' => date('Y-m-d H:i:s'),
			]);

		if ($status === 'approved') {
			$member = $this->get_member((int) $request['member_id']);
			$base = ! empty($member['expired_at']) && strtotime($member['expired_at']) > time()
				? $member['expired_at']
				: date('Y-m-d H:i:s');
			$new_expired = date('Y-m-d H:i:s', strtotime('+' . (int) $request['requested_months'] . ' months', strtotime($base)));
			$this->db
				->where('id', (int) $request['member_id'])
				->update('members', [
					'expired_at' => $new_expired,
					'status' => 'active',
				]);
		}

		$this->db->trans_complete();
		return (bool) $this->db->trans_status();
	}

	public function update_card_status($member_id, $status, $reason = null, $user_id = null)
	{
		$status = $status === 'blocked' ? 'blocked' : 'active';
		$payload = [
			'card_status' => $status,
			'card_block_reason' => $status === 'blocked' ? $this->clip($reason, 255) : null,
			'card_blocked_at' => $status === 'blocked' ? date('Y-m-d H:i:s') : null,
			'card_blocked_by' => $status === 'blocked' ? ((int) $user_id ?: null) : null,
		];

		$this->db->where('id', (int) $member_id)->update('members', $payload);
		return $this->db->affected_rows() >= 0;
	}

	public function get_member_visits($member_id, $limit = 10)
	{
		if (! $this->db->table_exists('member_visits')) {
			return [];
		}

		return $this->db
			->from('member_visits')
			->where('member_id', (int) $member_id)
			->order_by('visited_at', 'DESC')
			->limit(max(1, min(50, (int) $limit)))
			->get()
			->result_array();
	}

	public function get_member_loans($member_id, $limit = 10)
	{
		if (! $this->db->table_exists('loan_transaction_items')) {
			return [];
		}

		return $this->db
			->select('li.*, b.title, bi.barcode')
			->from('loan_transaction_items li')
			->join('book_items bi', 'bi.id = li.book_item_id', 'left')
			->join('books b', 'b.id = bi.book_id', 'left')
			->where('li.member_id', (int) $member_id)
			->order_by('li.loan_date', 'DESC')
			->limit(max(1, min(50, (int) $limit)))
			->get()
			->result_array();
	}

	public function get_member_access_rules($member_id, $limit = 20)
	{
		if (! $this->db->table_exists('member_access_rules')) {
			return [];
		}

		return $this->db
			->from('member_access_rules')
			->where('member_id', (int) $member_id)
			->order_by('id', 'DESC')
			->limit(max(1, min(50, (int) $limit)))
			->get()
			->result_array();
	}

	public function create_member(array $data, $default_password = 'perpus2026')
	{
		$this->db->trans_start();

		if (empty($data['registered_at'])) {
			$data['registered_at'] = date('Y-m-d H:i:s');
		}
		$data['member_no'] = $this->next_manual_member_no();

		$payload = $this->member_payload($data);
		$payload['source_system'] = 'manual';
		$payload['source_id'] = null;
		$payload['photo_migration_status'] = empty($payload['photo_path']) ? 'skipped' : 'pending';
		$this->db->insert('members', $payload);
		$member_id = (int) $this->db->insert_id();

		if (! empty($data['create_account'])) {
			$user_id = $this->create_or_update_member_account(null, $member_id, $payload, $data, $default_password);
			$this->db->where('id', $member_id)->update('members', ['auth_user_id' => $user_id]);
		}

		$this->db->trans_complete();
		if (! $this->db->trans_status()) {
			throw new RuntimeException('Gagal membuat member.');
		}

		return $member_id;
	}

	public function update_member($id, array $data, $default_password = 'perpus2026')
	{
		$member = $this->get_member((int) $id);
		if (! $member) {
			return false;
		}

		$this->db->trans_start();

		$payload = $this->member_payload($data);
		if ((string) ($member['photo_path'] ?? '') !== (string) ($payload['photo_path'] ?? '')) {
			$payload['photo_source_path'] = $payload['photo_path'];
			$payload['photo_local_path'] = null;
			$payload['photo_migration_status'] = empty($payload['photo_path']) ? 'skipped' : 'pending';
			$payload['photo_migrated_at'] = null;
		}

		$this->db->where('id', (int) $id)->update('members', $payload);

		if (! empty($data['create_account']) || ! empty($member['auth_user_id'])) {
			$merged = array_merge($member, $payload);
			$user_id = $this->create_or_update_member_account($member['auth_user_id'] ?? null, (int) $id, $merged, $data, $default_password);
			$this->db->where('id', (int) $id)->update('members', ['auth_user_id' => $user_id]);
		}

		$this->db->trans_complete();
		if (! $this->db->trans_status()) {
			throw new RuntimeException('Gagal memperbarui member.');
		}

		return true;
	}

	public function soft_delete_member($id)
	{
		$member = $this->get_member((int) $id);
		if (! $member) {
			return false;
		}

		$this->db->trans_start();
		$this->db
			->where('id', (int) $id)
			->update('members', [
				'deleted_at' => date('Y-m-d H:i:s'),
				'status' => 'inactive',
			]);

		if (! empty($member['auth_user_id'])) {
			$this->db
				->where('id', (int) $member['auth_user_id'])
				->update('auth_user', ['status' => 'inactive']);
		}

		$this->db->trans_complete();
		return (bool) $this->db->trans_status();
	}

	private function member_no_exists($member_no)
	{
		return $this->db
			->from('members')
			->where('member_no', (string) $member_no)
			->count_all_results() > 0;
	}

	private function next_renewal_code()
	{
		$prefix = 'REN-' . date('Ymd') . '-';
		$row = $this->db
			->select('request_code')
			->from('membership_renewal_requests')
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

	private function apply_renewal_filters(array $filters = [])
	{
		$this->db
			->from('membership_renewal_requests rr')
			->join('members m', 'm.id = rr.member_id', 'left')
			->join('auth_user u', 'u.id = m.auth_user_id', 'left');

		$q = trim((string) ($filters['q'] ?? ''));
		if ($q !== '') {
			$this->db->group_start()
				->like('rr.request_code', $q)
				->or_like('m.full_name', $q)
				->or_like('m.member_no', $q)
				->or_like('u.username', $q)
				->group_end();
		}

		$status = trim((string) ($filters['status'] ?? ''));
		if ($status !== '') {
			$this->db->where('rr.status', $status);
		}
	}

	private function apply_member_filters(array $filters = [])
	{
		$this->db
			->from('members m')
			->join('auth_user u', 'u.id = m.auth_user_id', 'left')
			->where('m.deleted_at IS NULL', null, false);

		$q = trim((string) ($filters['q'] ?? ''));
		if ($q !== '') {
			$this->db->group_start()
				->like('m.full_name', $q)
				->or_like('m.member_no', $q)
				->or_like('m.phone', $q)
				->or_like('m.email', $q)
				->or_like('m.district', $q)
				->or_like('m.village', $q)
				->or_like('u.username', $q)
				->group_end();
		}

		$status = trim((string) ($filters['status'] ?? ''));
		if (in_array($status, ['active', 'inactive', 'blocked', 'expired', 'unknown'], true)) {
			$this->db->where('m.status', $status);
		}

		$user_status = trim((string) ($filters['user_status'] ?? ''));
		if (in_array($user_status, ['active', 'inactive', 'suspended'], true)) {
			$this->db->where('u.status', $user_status);
		}
	}

	public function recent_sync_runs($limit = 8)
	{
		if (! $this->db->table_exists('member_sync_runs')) {
			return [];
		}

		return $this->db
			->from('member_sync_runs')
			->order_by('id', 'DESC')
			->limit(max(1, min(20, (int) $limit)))
			->get()
			->result_array();
	}

	public function source_stats()
	{
		$source_db = $this->load->database('inlislite', true);
		$tables = [
			'members' => 'Anggota',
			'memberguesses' => 'Kunjungan tamu',
			'memberloanauthorizecategory' => 'Hak pinjam kategori',
			'memberloanauthorizelocation' => 'Hak pinjam lokasi',
			'member_perpanjangan' => 'Perpanjangan',
			'collectionloans' => 'Histori peminjaman',
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

	public function migration_plan()
	{
		return [
			[
				'source' => 'members',
				'target' => 'members + auth_user',
				'coverage' => 'Identitas, kontak, alamat, tipe/status anggota, foto, masa aktif, dan akun login USER.',
			],
			[
				'source' => 'memberloanauthorizecategory, memberloanauthorizelocation',
				'target' => 'member_access_rules',
				'coverage' => 'Hak pinjam dan pembatasan akses berdasarkan kategori/lokasi koleksi.',
			],
			[
				'source' => 'collectionloans, collectionloanitems',
				'target' => 'loan_history',
				'coverage' => 'Histori peminjaman untuk profil pemustaka dan analitik layanan.',
			],
			[
				'source' => 'memberguesses, opaclogs, opaclogs_keyword',
				'target' => 'reading_analytics',
				'coverage' => 'Kunjungan, pencarian OPAC, dan aktivitas baca sebagai data analitik.',
			],
		];
	}

	public function run_manual_sync($created_by = null, $limit = 500, $default_password = 'perpus2026', $mode = 'import_new')
	{
		$limit = max(1, min(2000, (int) $limit));
		$mode = in_array($mode, ['import_new', 'refresh_existing', 'dry_run'], true) ? $mode : 'import_new';
		$sync_type = $mode === 'dry_run' ? 'dry_run' : 'manual';
		$run_id = $this->create_sync_run($created_by, $sync_type);
		$rows = $mode === 'dry_run' ? [] : $this->next_member_rows($limit, $mode);
		$inserted = 0;
		$updated = 0;
		$users_created = 0;
		$failed = 0;

		if ($mode === 'dry_run') {
			$potential_new = $this->remaining_source_count();
			$potential_refresh = $this->existing_source_count();
			$dry_total = min($limit, $potential_new);
			$message = sprintf(
				'Dry run member selesai. Kandidat member baru: %d, kandidat update lama: %d, batch yang akan diproses pada import data baru: %d.',
				$potential_new,
				$potential_refresh,
				$dry_total
			);
			$this->finish_sync_run($run_id, $dry_total, 0, 0, 0, 0, $message);

			return [
				'run_id' => $run_id,
				'processed' => $dry_total,
				'inserted' => 0,
				'updated' => 0,
				'users_created' => 0,
				'failed' => 0,
				'remaining' => $potential_new,
				'message' => $message,
			];
		}

		foreach ($rows as $row) {
			try {
				$result = $this->upsert_member_from_inlislite($row, $default_password);
				$inserted += $result['inserted'];
				$updated += $result['updated'];
				$users_created += $result['users_created'];
			} catch (Throwable $e) {
				$failed++;
			}
		}
		$this->refresh_member_master_labels();

		$remaining = $this->remaining_source_count();
		$message = sprintf(
			'Batch member %s selesai. Member baru: %d, member update: %d, akun dibuat: %d, gagal: %d, sisa belum masuk: %d.',
			$mode === 'refresh_existing' ? 'update data lama' : 'import data baru',
			$inserted,
			$updated,
			$users_created,
			$failed,
			$remaining
		);

		$this->finish_sync_run($run_id, count($rows), $inserted, $updated, $users_created, $failed, $message);

		return [
			'run_id' => $run_id,
			'processed' => count($rows),
			'inserted' => $inserted,
			'updated' => $updated,
			'users_created' => $users_created,
			'failed' => $failed,
			'remaining' => $remaining,
			'message' => $message,
		];
	}

	private function create_sync_run($created_by, $sync_type = 'manual')
	{
		$this->db->insert('member_sync_runs', [
			'source_database' => self::SOURCE_SYSTEM,
			'source_table' => 'members',
			'sync_type' => $sync_type,
			'status' => 'running',
			'started_at' => date('Y-m-d H:i:s'),
			'created_by' => $created_by ? (int) $created_by : null,
		]);

		return (int) $this->db->insert_id();
	}

	private function finish_sync_run($run_id, $total_source, $inserted, $updated, $users_created, $failed, $message)
	{
		$this->db
			->where('id', (int) $run_id)
			->update('member_sync_runs', [
				'status' => $failed > 0 ? 'failed' : 'success',
				'finished_at' => date('Y-m-d H:i:s'),
				'total_source' => (int) $total_source,
				'total_inserted' => (int) $inserted,
				'total_updated' => (int) $updated,
				'total_users_created' => (int) $users_created,
				'total_failed' => (int) $failed,
				'message' => $message,
			]);
	}

	private function refresh_member_master_labels()
	{
		if (! $this->db->table_exists('inlislite_master_references') || ! $this->db->field_exists('member_type_label', 'members')) {
			return;
		}

		$this->db->query(
			"UPDATE members m
			JOIN inlislite_v3.members sm ON m.source_system = 'inlislite_v3' AND m.source_id = CONVERT(CAST(sm.ID AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
			LEFT JOIN inlislite_master_references ident ON ident.source_table = 'master_jenis_identitas' AND ident.source_id = CONVERT(CAST(sm.IdentityType_id AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
			LEFT JOIN inlislite_master_references gender ON gender.source_table = 'jenis_kelamin' AND gender.source_id = CONVERT(CAST(sm.Sex_id AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
			LEFT JOIN inlislite_master_references mtype ON mtype.source_table = 'jenis_anggota' AND mtype.source_id = CONVERT(CAST(sm.JenisAnggota_id AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
			LEFT JOIN inlislite_master_references edu1 ON edu1.source_table = 'master_pendidikan' AND edu1.source_id = CONVERT(CAST(sm.EducationLevel_id AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
			LEFT JOIN inlislite_master_references edu2 ON edu2.source_table = 'master_jenjang_pendidikan' AND edu2.source_id = CONVERT(CAST(sm.JenjangPendidikan_id AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
			LEFT JOIN inlislite_master_references job ON job.source_table = 'master_pekerjaan' AND job.source_id = CONVERT(CAST(sm.Job_id AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
			LEFT JOIN inlislite_master_references st ON st.source_table = 'status_anggota' AND st.source_id = CONVERT(CAST(sm.StatusAnggota_id AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
			SET m.identity_type_label = ident.name,
			    m.gender_label = gender.name,
			    m.member_type_label = mtype.name,
			    m.education_label = COALESCE(edu1.name, edu2.name),
			    m.occupation_label = job.name,
			    m.member_status_label = st.name"
		);
		$this->normalize_member_operational_labels();
	}

	private function next_member_rows($limit, $mode = 'import_new')
	{
		$condition = $mode === 'refresh_existing' ? 'pm.id IS NOT NULL' : '(pm.id IS NULL OR pm.auth_user_id IS NULL)';

		return $this->db
			->query(
				"SELECT m.*
				FROM inlislite_v3.members m
				LEFT JOIN members pm
					ON pm.source_system = ?
					AND pm.source_id = CONVERT(CAST(m.ID AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
				WHERE " . $condition . "
				ORDER BY m.ID ASC
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
				FROM inlislite_v3.members m
				LEFT JOIN members pm
					ON pm.source_system = ?
					AND pm.source_id = CONVERT(CAST(m.ID AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
				WHERE pm.id IS NULL OR pm.auth_user_id IS NULL",
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
				FROM inlislite_v3.members m
				JOIN members pm
					ON pm.source_system = ?
					AND pm.source_id = CONVERT(CAST(m.ID AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci",
				[self::SOURCE_SYSTEM]
			)
			->row_array();

		return (int) ($row['total'] ?? 0);
	}

	private function upsert_member_from_inlislite(array $row, $default_password)
	{
		$this->db->trans_start();

		$source_id = $this->source_id($row['ID']);
		$member_no = $this->clip($row['MemberNo'] ?? null, 120);
		$member_no = $member_no ?: 'member-' . $source_id;
		$email = $this->clip($row['Email'] ?? null, 180);
		$phone = $this->clip(($row['NoHp'] ?? '') ?: ($row['Phone'] ?? ''), 80);
		$photo_path = $this->clip($row['PhotoUrl'] ?? null, 255);
		$identity_type = $this->reference_name('master_jenis_identitas', $row['IdentityType_id'] ?? null) ?: $this->clip($row['IdentityType_id'] ?? null, 80);
		$gender = $this->reference_name('jenis_kelamin', $row['Sex_id'] ?? null) ?: $this->clip($row['Sex_id'] ?? null, 40);
		$member_type = $this->reference_name('jenis_anggota', $row['JenisAnggota_id'] ?? null) ?: $this->clip($row['JenisAnggota_id'] ?? null, 120);
		$education_source = ($row['EducationLevel_id'] ?? '') ?: ($row['JenjangPendidikan_id'] ?? '');
		$education = $this->reference_name('master_pendidikan', $education_source) ?: $this->reference_name('master_jenjang_pendidikan', $education_source) ?: $this->clip($education_source, 120);
		$occupation = $this->reference_name('master_pekerjaan', $row['Job_id'] ?? null) ?: $this->clip($row['Job_id'] ?? null, 120);

		$payload = [
			'source_system' => self::SOURCE_SYSTEM,
			'source_id' => $source_id,
			'member_no' => $member_no,
			'full_name' => $this->clip($row['Fullname'] ?: ('Member ' . $source_id), 180),
			'identity_type' => $identity_type,
			'identity_number' => $this->clip($row['IdentityNo'] ?? null, 120),
			'gender' => $gender,
			'birth_place' => $this->clip($row['PlaceOfBirth'] ?? null, 120),
			'birth_date' => $this->date_or_null($row['DateOfBirth'] ?? null),
			'address' => $this->blank_to_null(($row['AddressNow'] ?? '') ?: ($row['Address'] ?? '')),
			'district' => $this->clip(($row['KecamatanNow'] ?? '') ?: ($row['Kecamatan'] ?? ''), 120),
			'village' => $this->clip(($row['KelurahanNow'] ?? '') ?: ($row['Kelurahan'] ?? ''), 120),
			'phone' => $phone,
			'email' => $email,
			'photo_path' => $photo_path,
			'member_type' => $member_type,
			'education' => $education,
			'occupation' => $occupation,
			'status' => $this->map_member_status($row['StatusAnggota_id'] ?? null, $row['EndDate'] ?? null),
			'registered_at' => $this->datetime_or_null($row['RegisterDate'] ?? null),
			'expired_at' => $this->datetime_or_null($row['EndDate'] ?? null),
		];

		$existing = $this->db
			->from('members')
			->where('source_system', self::SOURCE_SYSTEM)
			->where('source_id', $source_id)
			->get()
			->row_array();

		if ($existing) {
			if ((string) ($existing['photo_path'] ?? '') !== (string) $photo_path) {
				$payload['photo_source_path'] = $photo_path;
				$payload['photo_local_path'] = null;
				$payload['photo_migration_status'] = $photo_path ? 'pending' : 'skipped';
				$payload['photo_migrated_at'] = null;
			}
			$this->db->where('id', (int) $existing['id'])->update('members', $payload);
			$member_id = (int) $existing['id'];
			$inserted = 0;
			$updated = 1;
		} else {
			$payload['photo_source_path'] = $photo_path;
			$payload['photo_migration_status'] = $photo_path ? 'pending' : 'skipped';
			$this->db->insert('members', $payload);
			$member_id = (int) $this->db->insert_id();
			$inserted = 1;
			$updated = 0;
		}

		$user_result = $this->ensure_member_user($source_id, $member_id, $payload, $default_password);
		if (! empty($user_result['user_id'])) {
			$this->db->where('id', $member_id)->update('members', ['auth_user_id' => (int) $user_result['user_id']]);
		}

		$this->db->trans_complete();
		if (! $this->db->trans_status()) {
			throw new RuntimeException('Gagal menyimpan member sumber ' . $source_id);
		}

		return [
			'inserted' => $inserted,
			'updated' => $updated,
			'users_created' => $user_result['created'] ? 1 : 0,
		];
	}

	private function ensure_member_user($source_id, $member_id, array $member, $default_password)
	{
		$username = $this->username_for_member($member['identity_number'] ?? null, $member['member_no'] ?? null, $source_id);
		$user = $this->db
			->from('auth_user')
			->where('member_source_id', (int) $source_id)
			->get()
			->row_array();

		if (! $user) {
			$user = $this->db
				->from('auth_user')
				->where('username', $username)
				->get()
				->row_array();
		}

		$user_email = $this->valid_unique_email($member['email'] ?? null, $user['id'] ?? null);
		$payload = [
			'username' => $username,
			'email' => $user_email,
			'full_name' => $member['full_name'],
			'phone' => $member['phone'],
			'member_source_id' => (int) $source_id,
			'status' => $member['status'] === 'blocked' ? 'suspended' : 'active',
			'force_password_change' => 1,
		];

		if ($user) {
			$this->db->where('id', (int) $user['id'])->update('auth_user', $payload);
			$user_id = (int) $user['id'];
			$created = false;
		} else {
			$payload['password_hash'] = password_hash((string) $default_password, PASSWORD_BCRYPT);
			$this->db->insert('auth_user', $payload);
			$user_id = (int) $this->db->insert_id();
			$created = true;
		}

		$this->ensure_user_role($user_id, 'USER');

		return [
			'user_id' => $user_id,
			'created' => $created,
		];
	}

	private function ensure_user_role($user_id, $role_code)
	{
		$role = $this->db
			->from('auth_role')
			->where('code', $role_code)
			->get()
			->row_array();

		if (! $role) {
			return;
		}

		$exists = $this->db
			->from('auth_user_role')
			->where('user_id', (int) $user_id)
			->where('role_id', (int) $role['id'])
			->count_all_results() > 0;

		if (! $exists) {
			$this->db->insert('auth_user_role', [
				'user_id' => (int) $user_id,
				'role_id' => (int) $role['id'],
			]);
		}
	}

	private function valid_unique_email($email, $exclude_user_id = null)
	{
		$email = $this->clip($email, 180);
		if ($email === null || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return null;
		}

		$this->db->from('auth_user')->where('email', $email);
		if ($exclude_user_id) {
			$this->db->where('id <>', (int) $exclude_user_id);
		}

		return $this->db->count_all_results() > 0 ? null : $email;
	}

	private function username_for_member($identity_number, $member_no, $source_id)
	{
		$username = trim((string) $identity_number);
		if ($username === '') {
			$username = trim((string) $member_no);
		}
		if ($username === '') {
			$username = 'member-' . $source_id;
		}

		return $this->clip($username, 80);
	}

	private function member_payload(array $data)
	{
		$member_no = $this->clip($data['member_no'] ?? null, 120);
		$photo_path = $this->clip($data['photo_path'] ?? null, 255);

		return [
			'member_no' => $member_no ?: null,
			'full_name' => $this->clip($data['full_name'] ?? '', 180) ?: 'Tanpa Nama',
			'identity_type' => $this->clip($data['identity_type'] ?? null, 80),
			'identity_number' => $this->clip($data['identity_number'] ?? null, 120),
			'gender' => $this->clip($data['gender'] ?? null, 40),
			'birth_place' => $this->clip($data['birth_place'] ?? null, 120),
			'birth_date' => $this->date_or_null($data['birth_date'] ?? null),
			'address' => $this->blank_to_null($data['address'] ?? null),
			'district' => $this->clip($data['district'] ?? null, 120),
			'village' => $this->clip($data['village'] ?? null, 120),
			'phone' => $this->clip($data['phone'] ?? null, 80),
			'email' => $this->clip($data['email'] ?? null, 180),
			'photo_path' => $photo_path,
			'photo_source_path' => $this->clip($data['photo_path'] ?? null, 500),
			'member_type' => $this->clip($data['member_type'] ?? null, 120),
			'education' => $this->clip($data['education'] ?? null, 120),
			'occupation' => $this->clip($data['occupation'] ?? null, 120),
			'status' => in_array(($data['status'] ?? 'active'), ['active', 'inactive', 'blocked', 'expired', 'unknown'], true) ? $data['status'] : 'active',
			'registered_at' => $this->datetime_or_null($data['registered_at'] ?? null),
			'expired_at' => $this->datetime_or_null($data['expired_at'] ?? null),
		];
	}

	private function create_or_update_member_account($auth_user_id, $member_id, array $member, array $data, $default_password)
	{
		$username = $this->username_for_member($member['identity_number'] ?? null, $member['member_no'] ?? null, $member['source_id'] ?? $member_id);
		$password = (string) ($data['password'] ?? '');
		$status = $member['status'] === 'blocked' ? 'suspended' : 'active';
		$email = $this->valid_unique_email($member['email'] ?? null, $auth_user_id);
		$payload = [
			'username' => $username,
			'email' => $email,
			'full_name' => $member['full_name'],
			'phone' => $member['phone'],
			'member_source_id' => ! empty($member['source_id']) ? (int) $member['source_id'] : null,
			'status' => $status,
			'force_password_change' => 1,
		];

		if ($auth_user_id) {
			if ($password !== '') {
				$payload['password_hash'] = password_hash($password, PASSWORD_BCRYPT);
			}
			$this->db->where('id', (int) $auth_user_id)->update('auth_user', $payload);
			$user_id = (int) $auth_user_id;
		} else {
			$payload['password_hash'] = password_hash($password !== '' ? $password : $default_password, PASSWORD_BCRYPT);
			$this->db->insert('auth_user', $payload);
			$user_id = (int) $this->db->insert_id();
		}

		$this->ensure_user_role($user_id, 'USER');

		return $user_id;
	}

	private function map_member_status($status_id, $end_date = null)
	{
		$expired_at = $this->datetime_or_null($end_date);
		if ($expired_at !== null && strtotime($expired_at) < strtotime(date('Y-m-d 00:00:00'))) {
			return 'expired';
		}

		switch ((int) $status_id) {
			case 3:
				return 'active';
			case 4:
				return 'blocked';
			case 2:
				return 'inactive';
			default:
				return 'unknown';
		}
	}

	private function source_id($value)
	{
		return (string) (int) $value;
	}

	private function reference_names($source_table)
	{
		if (! $this->db->table_exists('inlislite_master_references')) {
			return [];
		}

		$rows = $this->db
			->select('name')
			->from('inlislite_master_references')
			->where('source_system', self::SOURCE_SYSTEM)
			->where('source_table', $source_table)
			->where('name IS NOT NULL', null, false)
			->where('name <>', '')
			->order_by('CAST(source_id AS UNSIGNED)', 'ASC', false)
			->get()
			->result_array();

		return array_map(function ($row) {
			return $row['name'];
		}, $rows);
	}

	private function reference_name($source_table, $source_id)
	{
		$source_id = trim((string) $source_id);
		if ($source_id === '' || ! $this->db->table_exists('inlislite_master_references')) {
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

	private function merge_options(array $primary, array $fallback)
	{
		$options = array_merge($primary, $fallback);
		$options = array_filter(array_map('trim', $options), function ($option) {
			return $option !== '';
		});

		return array_values(array_unique($options));
	}

	private function normalize_member_operational_labels()
	{
		$this->db->query(
			"UPDATE members
			SET identity_type = COALESCE(NULLIF(identity_type_label, ''), identity_type),
			    gender = COALESCE(NULLIF(gender_label, ''), gender),
			    member_type = COALESCE(NULLIF(member_type_label, ''), member_type),
			    education = COALESCE(NULLIF(education_label, ''), education),
			    occupation = COALESCE(NULLIF(occupation_label, ''), occupation)"
		);
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

	private function date_or_null($value)
	{
		$value = $this->datetime_or_null($value);
		return $value === null ? null : substr($value, 0, 10);
	}

	private function datetime_or_null($value)
	{
		$value = trim((string) $value);
		if ($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
			return null;
		}

		return date('Y-m-d H:i:s', strtotime($value));
	}
}
