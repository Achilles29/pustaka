<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Visit_model extends CI_Model
{
	const SOURCE_SYSTEM = 'pustaka';

	public function record_member_dashboard_visit(array $member, array $user = [])
	{
		if (empty($member['id']) || ! $this->db->table_exists('member_visits')) {
			return false;
		}

		$source_id = 'member_dashboard:' . (int) $member['id'] . ':' . date('Ymd');
		if ($this->source_exists($source_id)) {
			return false;
		}

		return $this->insert_visit([
			'source_system' => self::SOURCE_SYSTEM,
			'source_id' => $source_id,
			'visit_channel' => 'member_dashboard',
			'visit_origin' => 'digital_external',
			'member_id' => (int) $member['id'],
			'auth_user_id' => ! empty($user['id']) ? (int) $user['id'] : null,
			'source_member_no' => $member['member_no'] ?? null,
			'visitor_no' => $member['member_no'] ?? null,
			'visitor_name' => $member['full_name'] ?? null,
			'visitor_count' => 1,
			'checkin_method' => 'dashboard_auto',
			'location_label' => 'Online - Dashboard Pemustaka',
			'purpose_label' => 'Akses layanan digital',
			'information' => 'Kunjungan online otomatis saat membuka dashboard member.',
			'visited_at' => date('Y-m-d H:i:s'),
			'ip_address' => $this->input->ip_address(),
			'user_agent' => substr((string) $this->input->user_agent(), 0, 255),
			'metadata_json' => json_encode(['route' => 'user/dashboard'], JSON_UNESCAPED_UNICODE),
		]);
	}

	public function record_reader_session_visit($session_id, array $asset, array $member, array $context = [])
	{
		if (empty($session_id) || empty($member['id']) || ! $this->db->table_exists('member_visits')) {
			return false;
		}

		$origin = $this->reader_origin($context['origin'] ?? 'external');
		return $this->insert_visit([
			'source_system' => self::SOURCE_SYSTEM,
			'source_id' => 'reader_session:' . (int) $session_id,
			'visit_channel' => 'digital_access',
			'visit_origin' => $origin,
			'member_id' => (int) $member['id'],
			'reading_point_id' => ! empty($context['token']['reading_point_id']) ? (int) $context['token']['reading_point_id'] : null,
			'reading_session_id' => (int) $session_id,
			'auth_user_id' => ! empty($member['auth_user_id']) ? (int) $member['auth_user_id'] : null,
			'source_member_no' => $member['member_no'] ?? null,
			'visitor_no' => $member['member_no'] ?? null,
			'visitor_name' => $member['full_name'] ?? null,
			'visitor_count' => 1,
			'checkin_method' => 'reader_quota',
			'location_label' => $context['location_label'] ?? 'Akses digital',
			'purpose_label' => 'Baca buku digital',
			'information' => trim('Reader: ' . (string) ($asset['title'] ?? 'Koleksi digital')),
			'visited_at' => date('Y-m-d H:i:s'),
			'ip_address' => $this->input->ip_address(),
			'user_agent' => substr((string) $this->input->user_agent(), 0, 255),
			'latitude' => $context['latitude'] ?? null,
			'longitude' => $context['longitude'] ?? null,
			'metadata_json' => json_encode([
				'book_id' => (int) ($asset['book_id'] ?? 0),
				'digital_asset_id' => (int) ($asset['id'] ?? 0),
				'access_policy' => $asset['access_policy'] ?? null,
				'quota_charged' => (int) ($context['quota_charged'] ?? 0),
				'quota_unit' => $context['quota_unit'] ?? null,
			], JSON_UNESCAPED_UNICODE),
		]);
	}

	public function record_reading_point_checkin(array $token, array $point, array $member, array $coordinate = [])
	{
		if (empty($token['id']) || empty($member['id']) || ! $this->db->table_exists('member_visits')) {
			return false;
		}

		return $this->insert_visit([
			'source_system' => self::SOURCE_SYSTEM,
			'source_id' => 'reading_point_checkin:' . (int) $token['id'],
			'visit_channel' => 'reading_point',
			'visit_origin' => 'reading_point',
			'member_id' => (int) $member['id'],
			'library_id' => ! empty($point['library_id']) ? (int) $point['library_id'] : null,
			'reading_point_id' => (int) $point['id'],
			'auth_user_id' => ! empty($member['auth_user_id']) ? (int) $member['auth_user_id'] : null,
			'source_member_no' => $member['member_no'] ?? null,
			'visitor_no' => $member['member_no'] ?? null,
			'visitor_name' => $member['full_name'] ?? null,
			'visitor_count' => 1,
			'checkin_method' => 'member_gps',
			'location_label' => $point['name'] ?? 'Pojok Baca Digital',
			'purpose_label' => 'Update token Pojok Baca',
			'information' => 'Check-in GPS member untuk menerbitkan token baca.',
			'visited_at' => date('Y-m-d H:i:s'),
			'ip_address' => $this->input->ip_address(),
			'user_agent' => substr((string) $this->input->user_agent(), 0, 255),
			'latitude' => $coordinate['latitude'] ?? null,
			'longitude' => $coordinate['longitude'] ?? null,
			'metadata_json' => json_encode([
				'token_id' => (int) $token['id'],
				'quota_total' => (int) ($token['quota_total'] ?? 0),
				'quota_unit' => $token['quota_unit'] ?? null,
			], JSON_UNESCAPED_UNICODE),
		]);
	}

	public function create_kiosk_qr_token($library_id = null, $created_by = null)
	{
		if (! $this->db->table_exists('visit_kiosk_qr_tokens')) {
			return null;
		}

		$this->expire_kiosk_tokens();
		$seconds = max(15, min(600, (int) $this->kiosk_setting('qr_refresh_seconds', 60)));
		$token = strtoupper(substr(hash('sha256', uniqid('', true) . random_int(100000, 999999)), 0, 32));
		$library_id = (int) $library_id ?: (int) $this->kiosk_setting('default_visit_library_id', 0);

		$payload = [
			'library_id' => $library_id > 0 ? $library_id : null,
			'token' => $token,
			'expires_at' => date('Y-m-d H:i:s', time() + $seconds),
			'created_by' => $created_by ? (int) $created_by : null,
		];
		$this->db->insert('visit_kiosk_qr_tokens', $payload);
		$payload['id'] = (int) $this->db->insert_id();
		$payload['refresh_seconds'] = $seconds;

		return $payload;
	}

	public function redeem_kiosk_qr_token($token, array $member, array $user = [])
	{
		if (! $this->db->table_exists('visit_kiosk_qr_tokens')) {
			return ['ok' => false, 'message' => 'Tabel token QR belum tersedia.'];
		}

		$this->expire_kiosk_tokens();
		$row = $this->db
			->from('visit_kiosk_qr_tokens')
			->where('token', trim((string) $token))
			->where('status', 'active')
			->where('expires_at >=', date('Y-m-d H:i:s'))
			->limit(1)
			->get()
			->row_array();
		if (! $row) {
			return ['ok' => false, 'message' => 'QR check-in sudah kedaluwarsa. Silakan scan QR terbaru di monitor pelayanan.'];
		}

		$source_id = 'qr_checkin:' . (int) $row['id'] . ':' . (int) $member['id'];
		$this->insert_visit([
			'source_system' => self::SOURCE_SYSTEM,
			'source_id' => $source_id,
			'visit_channel' => 'qr_checkin',
			'visit_origin' => 'library',
			'library_id' => ! empty($row['library_id']) ? (int) $row['library_id'] : null,
			'member_id' => (int) $member['id'],
			'auth_user_id' => ! empty($user['id']) ? (int) $user['id'] : (! empty($member['auth_user_id']) ? (int) $member['auth_user_id'] : null),
			'qr_token_id' => (int) $row['id'],
			'source_member_no' => $member['member_no'] ?? null,
			'visitor_no' => $member['member_no'] ?? null,
			'visitor_name' => $member['full_name'] ?? null,
			'visitor_count' => 1,
			'checkin_method' => 'member_qr',
			'location_label' => 'Perpustakaan daerah / monitor pelayanan',
			'purpose_label' => 'Kunjungan fisik member',
			'information' => 'Check-in member melalui QR dinamis monitor pelayanan.',
			'visited_at' => date('Y-m-d H:i:s'),
			'ip_address' => $this->input->ip_address(),
			'user_agent' => substr((string) $this->input->user_agent(), 0, 255),
		]);

		$this->db
			->where('id', (int) $row['id'])
			->set('used_count', 'used_count + 1', false)
			->update('visit_kiosk_qr_tokens');

		return ['ok' => true, 'message' => 'Check-in kunjungan berhasil dicatat.'];
	}

	public function record_guestbook_visit(array $data)
	{
		$name = trim((string) ($data['visitor_name'] ?? ''));
		if ($name === '') {
			throw new RuntimeException('Nama pengunjung wajib diisi.');
		}

		return $this->insert_visit([
			'source_system' => self::SOURCE_SYSTEM,
			'source_id' => 'guestbook:' . date('YmdHis') . ':' . strtoupper(substr(hash('sha256', uniqid('', true)), 0, 10)),
			'visit_channel' => 'library_guestbook',
			'visit_origin' => 'library',
			'library_id' => ! empty($data['library_id']) ? (int) $data['library_id'] : null,
			'visitor_name' => substr($name, 0, 180),
			'group_name' => $this->blank_to_null($data['group_name'] ?? null),
			'group_leader_name' => $this->blank_to_null($data['group_leader_name'] ?? null),
			'visitor_count' => max(1, min(1000, (int) ($data['visitor_count'] ?? 1))),
			'checkin_method' => 'guest_form',
			'location_label' => 'Buku Tamu Perpustakaan',
			'purpose_label' => $this->blank_to_null($data['purpose_label'] ?? null) ?: 'Kunjungan perpustakaan',
			'address' => $this->blank_to_null($data['address'] ?? null),
			'information' => $this->blank_to_null($data['information'] ?? null),
			'visited_at' => date('Y-m-d H:i:s'),
			'ip_address' => $this->input->ip_address(),
			'user_agent' => substr((string) $this->input->user_agent(), 0, 255),
		]);
	}

	public function record_member_search_checkin($identifier, array $data = [])
	{
		$member = $this->find_member_for_checkin($data['member_id'] ?? null, $identifier);
		if (! $member) {
			throw new RuntimeException('Data member tidak ditemukan.');
		}

		return $this->insert_visit([
			'source_system' => self::SOURCE_SYSTEM,
			'source_id' => 'member_search_checkin:' . (int) $member['id'] . ':' . date('YmdHis'),
			'visit_channel' => 'service_monitor',
			'visit_origin' => 'library',
			'library_id' => ! empty($data['library_id']) ? (int) $data['library_id'] : null,
			'member_id' => (int) $member['id'],
			'auth_user_id' => ! empty($member['auth_user_id']) ? (int) $member['auth_user_id'] : null,
			'source_member_no' => $member['member_no'] ?? null,
			'visitor_no' => $member['member_no'] ?? null,
			'visitor_name' => $member['full_name'] ?? null,
			'visitor_count' => max(1, min(1000, (int) ($data['visitor_count'] ?? 1))),
			'checkin_method' => 'member_search',
			'location_label' => 'Monitor pelayanan perpustakaan',
			'purpose_label' => $this->blank_to_null($data['purpose_label'] ?? null) ?: 'Kunjungan fisik member',
			'information' => $this->blank_to_null($data['information'] ?? null),
			'visited_at' => date('Y-m-d H:i:s'),
			'ip_address' => $this->input->ip_address(),
			'user_agent' => substr((string) $this->input->user_agent(), 0, 255),
		]);
	}

	public function search_members_for_guestbook($keyword, $limit = 8)
	{
		$keyword = trim((string) $keyword);
		if ($keyword === '' || strlen($keyword) < 2 || ! $this->db->table_exists('members')) {
			return [];
		}

		$rows = $this->db
			->select('id, member_no, identity_number, full_name, status, member_type_label, district, village, expired_at')
			->from('members')
			->group_start()
				->like('full_name', $keyword)
				->or_like('member_no', $keyword)
				->or_like('identity_number', $keyword)
			->group_end()
			->where('deleted_at IS NULL', null, false)
			->order_by("CASE WHEN identity_number = " . $this->db->escape($keyword) . " THEN 0 WHEN member_no = " . $this->db->escape($keyword) . " THEN 1 WHEN full_name LIKE " . $this->db->escape($keyword . '%') . " THEN 2 ELSE 3 END", '', false)
			->order_by('full_name', 'ASC')
			->limit(max(1, min(15, (int) $limit)))
			->get()
			->result_array();

		return array_map(function ($member) {
			return [
				'id' => (int) $member['id'],
				'member_no' => (string) ($member['member_no'] ?? ''),
				'identity_number' => (string) ($member['identity_number'] ?? ''),
				'full_name' => (string) ($member['full_name'] ?? ''),
				'status' => (string) ($member['status'] ?? 'unknown'),
				'member_type' => (string) ($member['member_type_label'] ?? ''),
				'location' => trim((string) ($member['village'] ?? '') . (($member['village'] ?? '') && ($member['district'] ?? '') ? ', ' : '') . (string) ($member['district'] ?? '')),
				'expired_at' => (string) ($member['expired_at'] ?? ''),
			];
		}, $rows);
	}

	public function get_kiosk_settings()
	{
		$settings = [
			'qr_refresh_seconds' => '60',
			'default_visit_library_id' => '0',
		];
		if (! $this->db->table_exists('visit_kiosk_settings')) {
			return $settings;
		}

		$rows = $this->db
			->select('setting_key, setting_value')
			->from('visit_kiosk_settings')
			->where_in('setting_key', array_keys($settings))
			->get()
			->result_array();

		foreach ($rows as $row) {
			$settings[$row['setting_key']] = $row['setting_value'];
		}

		return $settings;
	}

	public function update_kiosk_settings(array $data)
	{
		if (! $this->db->table_exists('visit_kiosk_settings')) {
			throw new RuntimeException('Tabel pengaturan monitor belum tersedia.');
		}

		$refresh_seconds = max(15, min(600, (int) ($data['qr_refresh_seconds'] ?? 60)));
		$library_id = max(0, (int) ($data['default_visit_library_id'] ?? 0));
		$this->upsert_kiosk_setting('qr_refresh_seconds', (string) $refresh_seconds, 'Durasi masa berlaku QR dinamis pada monitor pelayanan, dalam detik.');
		$this->upsert_kiosk_setting('default_visit_library_id', (string) $library_id, 'ID perpustakaan pusat yang dipakai halaman monitor pelayanan jika belum dipilih.');

		return [
			'qr_refresh_seconds' => $refresh_seconds,
			'default_visit_library_id' => $library_id,
		];
	}

	private function insert_visit(array $payload)
	{
		$payload = $this->existing_fields('member_visits', $payload);
		if (empty($payload['source_id']) || $this->source_exists($payload['source_id'])) {
			return false;
		}

		$this->db->insert('member_visits', $payload);
		return $this->db->affected_rows() > 0;
	}

	private function source_exists($source_id)
	{
		return $this->db
			->from('member_visits')
			->where('source_system', self::SOURCE_SYSTEM)
			->where('source_id', (string) $source_id)
			->limit(1)
			->count_all_results() > 0;
	}

	private function reader_origin($origin)
	{
		if ($origin === 'reading_point') {
			return 'reading_point';
		}
		if ($origin === 'library') {
			return 'library';
		}
		if ($origin === 'admin') {
			return 'digital_internal';
		}

		return 'digital_external';
	}

	private function find_member_for_checkin($member_id, $identifier)
	{
		$identifier = trim((string) $identifier);
		$member_id = (int) $member_id;
		if (($identifier === '' && $member_id <= 0) || ! $this->db->table_exists('members')) {
			return null;
		}

		if ($member_id > 0) {
			return $this->db
				->from('members')
				->where('id', $member_id)
				->where('deleted_at IS NULL', null, false)
				->limit(1)
				->get()
				->row_array();
		}

		$exact = $this->db
			->from('members')
			->group_start()
				->where('member_no', $identifier)
				->or_where('identity_number', $identifier)
				->or_where('full_name', $identifier)
			->group_end()
			->where('deleted_at IS NULL', null, false)
			->limit(2)
			->get()
			->result_array();

		if (count($exact) === 1) {
			return $exact[0];
		}

		return null;
	}

	private function kiosk_setting($key, $default)
	{
		if (! $this->db->table_exists('visit_kiosk_settings')) {
			return $default;
		}

		$row = $this->db
			->select('setting_value')
			->from('visit_kiosk_settings')
			->where('setting_key', (string) $key)
			->limit(1)
			->get()
			->row_array();

		return $row ? $row['setting_value'] : $default;
	}

	private function upsert_kiosk_setting($key, $value, $description)
	{
		$exists = $this->db
			->from('visit_kiosk_settings')
			->where('setting_key', (string) $key)
			->limit(1)
			->count_all_results() > 0;

		$payload = [
			'setting_value' => (string) $value,
			'description' => (string) $description,
		];
		if ($exists) {
			$this->db
				->where('setting_key', (string) $key)
				->update('visit_kiosk_settings', $payload);
			return;
		}

		$payload['setting_key'] = (string) $key;
		$this->db->insert('visit_kiosk_settings', $payload);
	}

	private function expire_kiosk_tokens()
	{
		$this->db
			->where('status', 'active')
			->where('expires_at <', date('Y-m-d H:i:s'))
			->update('visit_kiosk_qr_tokens', ['status' => 'expired']);
	}

	private function blank_to_null($value)
	{
		$value = is_string($value) ? trim($value) : $value;
		return $value === '' ? null : $value;
	}

	private function existing_fields($table, array $payload)
	{
		$result = [];
		foreach ($payload as $field => $value) {
			if ($this->db->field_exists($field, $table)) {
				$result[$field] = $value;
			}
		}

		return $result;
	}
}
