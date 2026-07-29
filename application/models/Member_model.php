<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Member_model extends CI_Model
{
	const SOURCE_SYSTEM = 'inlislite_v3';

	public function stats()
	{
		$members = $this->db->table_exists('members') ? (int) $this->db->count_all('members') : 0;
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
			->get()
			->row_array();
	}

	private function apply_member_filters(array $filters = [])
	{
		$this->db
			->from('members m')
			->join('auth_user u', 'u.id = m.auth_user_id', 'left');

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

	public function run_manual_sync($created_by = null, $limit = 500, $default_password = 'PustakaRembang#2026', $mode = 'import_new')
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

		$payload = [
			'source_system' => self::SOURCE_SYSTEM,
			'source_id' => $source_id,
			'member_no' => $member_no,
			'full_name' => $this->clip($row['Fullname'] ?: ('Member ' . $source_id), 180),
			'identity_type' => $this->clip($row['IdentityType_id'] ?? null, 80),
			'identity_number' => $this->clip($row['IdentityNo'] ?? null, 120),
			'gender' => $this->clip($row['Sex_id'] ?? null, 40),
			'birth_place' => $this->clip($row['PlaceOfBirth'] ?? null, 120),
			'birth_date' => $this->date_or_null($row['DateOfBirth'] ?? null),
			'address' => $this->blank_to_null(($row['AddressNow'] ?? '') ?: ($row['Address'] ?? '')),
			'district' => $this->clip(($row['KecamatanNow'] ?? '') ?: ($row['Kecamatan'] ?? ''), 120),
			'village' => $this->clip(($row['KelurahanNow'] ?? '') ?: ($row['Kelurahan'] ?? ''), 120),
			'phone' => $phone,
			'email' => $email,
			'photo_path' => $this->clip($row['PhotoUrl'] ?? null, 255),
			'member_type' => $this->clip($row['JenisAnggota_id'] ?? null, 120),
			'education' => $this->clip(($row['EducationLevel_id'] ?? '') ?: ($row['JenjangPendidikan_id'] ?? ''), 120),
			'occupation' => $this->clip($row['Job_id'] ?? null, 120),
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
			$this->db->where('id', (int) $existing['id'])->update('members', $payload);
			$member_id = (int) $existing['id'];
			$inserted = 0;
			$updated = 1;
		} else {
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
		$username = $this->username_for_member($member['member_no'], $source_id);
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

	private function username_for_member($member_no, $source_id)
	{
		$username = trim((string) $member_no);
		if ($username === '') {
			$username = 'member-' . $source_id;
		}

		return $this->clip($username, 80);
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
