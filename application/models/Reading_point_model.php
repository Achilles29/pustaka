<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reading_point_model extends CI_Model
{
	private $token_settings_cache = null;

	public function stats()
	{
		return [
			'points' => $this->count_where('reading_points'),
			'active_points' => $this->count_where('reading_points', ['status' => 'active']),
			'tokens' => $this->count_where('reading_tokens'),
			'active_tokens' => $this->count_where('reading_tokens', ['status' => 'active']),
			'sessions' => $this->count_where('reading_sessions'),
		];
	}

	public function get_token_settings()
	{
		$defaults = [
			'library_checkin_quota' => 5,
			'library_checkin_valid_days' => 0,
			'library_checkin_daily_limit' => 1,
			'request_default_quota' => 3,
			'request_valid_days' => 7,
			'outside_session_charge' => 1,
		];
		if ($this->token_settings_cache !== null) {
			return $this->token_settings_cache;
		}
		if (! $this->db->table_exists('reading_token_settings')) {
			return $this->token_settings_cache = $defaults;
		}

		$settings = $defaults;
		foreach ($this->db->select('setting_key, setting_value')->from('reading_token_settings')->get()->result_array() as $row) {
			if (array_key_exists($row['setting_key'], $settings)) {
				$settings[$row['setting_key']] = (int) $row['setting_value'];
			}
		}
		$settings['library_checkin_quota'] = max(1, min(1000, $settings['library_checkin_quota']));
		$settings['library_checkin_valid_days'] = max(0, min(365, $settings['library_checkin_valid_days']));
		$settings['library_checkin_daily_limit'] = max(1, min(20, $settings['library_checkin_daily_limit']));
		$settings['request_default_quota'] = max(1, min(1000, $settings['request_default_quota']));
		$settings['request_valid_days'] = max(1, min(365, $settings['request_valid_days']));
		$settings['outside_session_charge'] = max(1, min(100, $settings['outside_session_charge']));
		return $this->token_settings_cache = $settings;
	}

	public function update_token_settings(array $data, $updated_by = null)
	{
		if (! $this->db->table_exists('reading_token_settings')) {
			throw new RuntimeException('Tabel pengaturan token belum diaktifkan. Jalankan migrasi pengaturan token terlebih dahulu.');
		}
		$settings = [
			'library_checkin_quota' => max(1, min(1000, (int) ($data['library_checkin_quota'] ?? 5))),
			'library_checkin_valid_days' => max(0, min(365, (int) ($data['library_checkin_valid_days'] ?? 0))),
			'library_checkin_daily_limit' => max(1, min(20, (int) ($data['library_checkin_daily_limit'] ?? 1))),
			'request_default_quota' => max(1, min(1000, (int) ($data['request_default_quota'] ?? 3))),
			'request_valid_days' => max(1, min(365, (int) ($data['request_valid_days'] ?? 7))),
			'outside_session_charge' => max(1, min(100, (int) ($data['outside_session_charge'] ?? 1))),
		];
		foreach ($settings as $key => $value) {
			$payload = ['setting_value' => (string) $value, 'updated_by' => (int) $updated_by ?: null];
			$exists = $this->db->from('reading_token_settings')->where('setting_key', $key)->count_all_results() > 0;
			if ($exists) {
				$this->db->where('setting_key', $key)->update('reading_token_settings', $payload);
			} else {
				$payload['setting_key'] = $key;
				$this->db->insert('reading_token_settings', $payload);
			}
		}
		$this->token_settings_cache = null;
		return $this->get_token_settings();
	}

	public function get_points($limit = 25, $offset = 0, array $filters = [])
	{
		if (! $this->db->table_exists('reading_points')) {
			return [];
		}

		$this->apply_point_filters($filters);

		return $this->db
			->order_by("FIELD(rp.status, 'active', 'draft', 'inactive')", '', false)
			->order_by('rp.id', 'DESC')
			->limit(max(1, min(100, (int) $limit)), max(0, (int) $offset))
			->get()
			->result_array();
	}

	public function count_points(array $filters = [])
	{
		if (! $this->db->table_exists('reading_points')) {
			return 0;
		}

		$this->apply_point_filters($filters);
		return (int) $this->db->count_all_results();
	}

	public function get_point($id)
	{
		return $this->db
			->from('reading_points')
			->where('id', (int) $id)
			->limit(1)
			->get()
			->row_array();
	}

	public function get_active_points($limit = 200)
	{
		if (! $this->db->table_exists('reading_points')) {
			return [];
		}

		return $this->db
			->select('rp.*, l.name AS library_name')
			->from('reading_points rp')
			->join('libraries l', 'l.id = rp.library_id', 'left')
			->where('rp.status', 'active')
			->where('rp.latitude IS NOT NULL', null, false)
			->where('rp.longitude IS NOT NULL', null, false)
			->order_by('rp.name', 'ASC')
			->limit(max(1, min(500, (int) $limit)))
			->get()
			->result_array();
	}

	/**
	 * Semua lokasi GIS yang membebaskan pemakaian token.  Pojok Baca adalah
	 * titik tersendiri; perpustakaan terdaftar memakai koordinat GIS pada
	 * master libraries.  Keduanya sengaja dikembalikan dalam format yang sama
	 * agar halaman member tidak lagi hanya menampilkan Pojok Baca.
	 */
	public function get_free_access_locations($limit = 500)
	{
		$limit = max(1, min(500, (int) $limit));
		$locations = [];

		foreach ($this->get_active_points($limit) as $point) {
			$locations[] = [
				'type' => 'reading_point',
				'name' => $point['name'],
				'subtitle' => $point['partner_name'] ?: ($point['library_name'] ?: 'Pojok Baca Digital'),
				'address' => $point['address'] ?? null,
				'radius_meters' => (int) ($point['radius_meters'] ?? 0),
				'quota_total' => (int) ($point['daily_quota'] ?? 0),
				'quota_unit' => $point['quota_unit'] ?? 'books',
			];
		}

		if ($this->db->table_exists('libraries')) {
			$libraries = $this->db
				->select('name, address, district, village, service_radius_meters, is_verified')
				->from('libraries')
				->where('status', 'active')
				->where('latitude IS NOT NULL', null, false)
				->where('longitude IS NOT NULL', null, false)
				->order_by('name', 'ASC')
				->limit($limit)
				->get()
				->result_array();
			foreach ($libraries as $library) {
				$area = array_filter([$library['village'] ?? null, $library['district'] ?? null]);
				$locations[] = [
					'type' => 'library',
					'name' => $library['name'],
					'subtitle' => ((int) ($library['is_verified'] ?? 0) === 1 ? 'Perpustakaan terverifikasi' : 'Perpustakaan terdaftar GIS'),
					'address' => $library['address'] ?: implode(', ', $area),
					'radius_meters' => (int) ($library['service_radius_meters'] ?? 100),
					'quota_total' => 0,
					'quota_unit' => 'books',
				];
			}
		}

		usort($locations, function ($a, $b) {
			return strcmp((string) $a['name'], (string) $b['name']);
		});
		return $locations;
	}

	/** Member map uses only public institution/location data, never tokens or member records. */
	public function member_map_payload()
	{
		$this->load->model('Library_model');
		$points = $this->Library_model->public_map_payload();
		foreach ($this->get_active_points(500) as $point) {
			if (! is_numeric($point['latitude']) || ! is_numeric($point['longitude'])) continue;
			$lat = (float) $point['latitude']; $lng = (float) $point['longitude'];
			if (! is_finite($lat) || ! is_finite($lng) || abs($lat) > 90 || abs($lng) > 180 || ($lat == 0 && $lng == 0)) continue;
			$points[] = [
				'id' => (int) $point['id'], 'location_kind' => 'reading_point',
				'name' => $point['name'], 'type' => 'Pojok Baca Digital', 'type_code' => 'reading_point',
				'lat' => $lat, 'lng' => $lng, 'radius' => max(10, (int) $point['radius_meters']),
				'address' => $point['address'] ?? '', 'subtitle' => $point['library_name'] ?: 'Pojok Baca Digital',
				'status' => 'active',
			];
		}
		return $points;
	}

	public function library_options()
	{
		if (! $this->db->table_exists('libraries')) {
			return [];
		}

		return $this->db
			->select('id, name, district, village')
			->from('libraries')
			->where_in('status', ['active', 'pending'])
			->order_by('name', 'ASC')
			->limit(300)
			->get()
			->result_array();
	}

	public function create_point(array $data, $created_by = null)
	{
		$payload = $this->point_payload($data);
		$payload['created_by'] = (int) $created_by ?: null;
		$this->db->insert('reading_points', $payload);
		return (int) $this->db->insert_id();
	}

	public function update_point($id, array $data)
	{
		$this->db
			->where('id', (int) $id)
			->update('reading_points', $this->point_payload($data));

		return $this->db->affected_rows() >= 0;
	}

	public function get_recent_tokens($limit = 10)
	{
		if (! $this->db->table_exists('reading_tokens')) {
			return [];
		}

		return $this->db
			->select("rt.*, m.full_name, m.member_no, COALESCE(rp.name, CASE WHEN rt.token LIKE 'VIS-%' THEN 'Check-in Perpustakaan Daerah' ELSE NULL END) AS point_name", false)
			->from('reading_tokens rt')
			->join('members m', 'm.id = rt.member_id', 'left')
			->join('reading_points rp', 'rp.id = rt.reading_point_id', 'left')
			->order_by('rt.id', 'DESC')
			->limit(max(1, min(30, (int) $limit)))
			->get()
			->result_array();
	}

	public function count_tokens(array $filters = [])
	{
		$this->apply_token_filters($filters);
		return (int) $this->db->count_all_results();
	}

	public function get_tokens(array $filters = [], $limit = 25, $offset = 0)
	{
		$this->apply_token_filters($filters);

		return $this->db
			->select("rt.*, m.full_name, m.member_no, m.identity_number, COALESCE(rp.name, CASE WHEN rt.token LIKE 'VIS-%' THEN 'Check-in Perpustakaan Daerah' ELSE NULL END) AS point_name, rp.partner_name", false)
			->order_by("FIELD(rt.status, 'active', 'used', 'expired', 'revoked')", '', false)
			->order_by('rt.id', 'DESC')
			->limit(max(1, min(100, (int) $limit)), max(0, (int) $offset))
			->get()
			->result_array();
	}

	public function get_token($id)
	{
		return $this->db
			->select("rt.*, m.full_name, m.member_no, COALESCE(rp.name, CASE WHEN rt.token LIKE 'VIS-%' THEN 'Check-in Perpustakaan Daerah' ELSE NULL END) AS point_name", false)
			->from('reading_tokens rt')
			->join('members m', 'm.id = rt.member_id', 'left')
			->join('reading_points rp', 'rp.id = rt.reading_point_id', 'left')
			->where('rt.id', (int) $id)
			->limit(1)
			->get()
			->row_array();
	}

	public function revoke_token($id, $reason = null, $revoked_by = null)
	{
		return $this->db
			->where('id', (int) $id)
			->where('status', 'active')
			->update('reading_tokens', [
				'status' => 'revoked',
				'revoked_by' => (int) $revoked_by ?: null,
				'revoked_at' => date('Y-m-d H:i:s'),
				'revoke_reason' => $this->blank_to_null($reason),
			]);
	}

	public function get_member_active_token($member_id)
	{
		$this->expire_old_tokens();

		return $this->db
			->select("rt.*, COALESCE(rp.name, CASE WHEN rt.token LIKE 'VIS-%' THEN 'Check-in Perpustakaan Daerah' ELSE NULL END) AS point_name, rp.partner_name, rp.address AS point_address", false)
			->from('reading_tokens rt')
			->join('reading_points rp', 'rp.id = rt.reading_point_id', 'left')
			->where('rt.member_id', (int) $member_id)
			->where('rt.status', 'active')
			->where('(rt.expires_at IS NULL OR rt.expires_at >= NOW())', null, false)
			->where('(rt.quota_total = 0 OR rt.quota_used < rt.quota_total)', null, false)
			->order_by('rt.id', 'DESC')
			->limit(1)
			->get()
			->row_array();
	}

	/**
	 * Token kunjungan fisik untuk Perpustakaan Daerah/pusat.
	 * Kuota, masa berlaku, dan batas penerbitan per hari diambil dari modul
	 * Pengaturan Token. Penerbitan ini terpisah dari check-in GPS Pojok Baca.
	 */
	public function issue_library_visit_token($member_id, $library_id = null)
	{
		if (! $this->db->table_exists('reading_tokens')) {
			return ['issued' => false, 'message' => 'Modul token baca belum tersedia.'];
		}
		$member = $this->db->select('id, status, card_status')->from('members')->where('id', (int) $member_id)->limit(1)->get()->row_array();
		if (! $member || ($member['status'] ?? '') !== 'active' || ($member['card_status'] ?? 'active') === 'blocked') {
			return ['issued' => false, 'message' => 'Token kunjungan hanya diterbitkan untuk member aktif.'];
		}
		$central = $this->central_library();
		if (! $central || ((int) $library_id > 0 && (int) $library_id !== (int) $central['id'])) {
			return ['issued' => false, 'message' => 'Check-in tidak tercatat di Perpustakaan Daerah; token kunjungan tidak diterbitkan.'];
		}

		$settings = $this->get_token_settings();
		$today = date('Y-m-d');
		$issued_today = $this->db->from('reading_tokens')
			->where('member_id', (int) $member_id)
			->like('token', 'VIS-', 'after')
			->where('issued_at >=', $today . ' 00:00:00')
			->where('issued_at <=', $today . ' 23:59:59')
			->count_all_results();
		if ($issued_today >= (int) $settings['library_checkin_daily_limit']) {
			$existing = $this->db->from('reading_tokens')->where('member_id', (int) $member_id)->like('token', 'VIS-', 'after')->where('issued_at >=', $today . ' 00:00:00')->order_by('id', 'DESC')->limit(1)->get()->row_array();
			return ['issued' => false, 'token' => $existing, 'message' => 'Batas token check-in hari ini sudah tercapai.'];
		}

		$payload = [
			'member_id' => (int) $member_id,
			'reading_point_id' => null,
			'token' => 'VIS-' . $this->new_token(),
			'quota_total' => (int) $settings['library_checkin_quota'],
			'quota_used' => 0,
			'quota_unit' => 'books',
			'expires_at' => $this->token_expiry_at((int) $settings['library_checkin_valid_days']),
			'status' => 'active',
			'issued_by' => null,
		];
		$this->db->insert('reading_tokens', $payload);
		$payload['id'] = (int) $this->db->insert_id();
		return ['issued' => true, 'token' => $payload, 'message' => 'Token kunjungan ' . (int) $settings['library_checkin_quota'] . ' sesi berhasil diterbitkan.'];
	}

	public function get_member_tokens($member_id, $limit = 8)
	{
		$this->expire_old_tokens();

		return $this->db
			->select("rt.*, COALESCE(rp.name, CASE WHEN rt.token LIKE 'VIS-%' THEN 'Check-in Perpustakaan Daerah' ELSE NULL END) AS point_name", false)
			->from('reading_tokens rt')
			->join('reading_points rp', 'rp.id = rt.reading_point_id', 'left')
			->where('rt.member_id', (int) $member_id)
			->order_by('rt.id', 'DESC')
			->limit(max(1, min(20, (int) $limit)))
			->get()
			->result_array();
	}

	public function get_member_pending_token_request($member_id)
	{
		if (! $this->db->table_exists('reading_token_requests')) {
			return null;
		}
		return $this->db->from('reading_token_requests')
			->where('member_id', (int) $member_id)
			->where('status', 'pending')
			->order_by('id', 'DESC')
			->limit(1)
			->get()->row_array();
	}

	public function create_member_token_request($member_id, $note = null)
	{
		if (! $this->db->table_exists('reading_token_requests')) {
			throw new RuntimeException('Modul permohonan token belum diaktifkan.');
		}
		$member = $this->db->select('id, status')->from('members')->where('id', (int) $member_id)->limit(1)->get()->row_array();
		if (! $member || ($member['status'] ?? '') !== 'active') {
			throw new RuntimeException('Permohonan token hanya tersedia untuk member aktif.');
		}
		if ($this->get_member_pending_token_request((int) $member_id)) {
			throw new RuntimeException('Anda masih memiliki permohonan token yang menunggu keputusan petugas.');
		}
		if ($this->get_member_active_token((int) $member_id)) {
			throw new RuntimeException('Token aktif masih tersedia. Gunakan token tersebut terlebih dahulu.');
		}

		$settings = $this->get_token_settings();
		$this->db->insert('reading_token_requests', [
			'member_id' => (int) $member_id,
			'requested_quota' => (int) $settings['request_default_quota'],
			'quota_unit' => 'books',
			'request_note' => $this->blank_to_null(substr(trim((string) $note), 0, 500)),
			'status' => 'pending',
		]);
		return (int) $this->db->insert_id();
	}

	public function get_pending_token_requests($limit = 20)
	{
		if (! $this->db->table_exists('reading_token_requests')) {
			return [];
		}
		return $this->db
			->select('r.*, m.full_name, m.member_no, m.status AS member_status')
			->from('reading_token_requests r')
			->join('members m', 'm.id = r.member_id', 'left')
			->where('r.status', 'pending')
			->order_by('r.requested_at', 'ASC')
			->limit(max(1, min(100, (int) $limit)))
			->get()->result_array();
	}

	public function approve_token_request($request_id, $reviewer_id = null, $review_note = null)
	{
		if (! $this->db->table_exists('reading_token_requests')) {
			throw new RuntimeException('Modul permohonan token belum diaktifkan.');
		}
		$this->db->trans_begin();
		try {
			$request = $this->db->query('SELECT * FROM reading_token_requests WHERE id = ? FOR UPDATE', [(int) $request_id])->row_array();
			if (! $request || $request['status'] !== 'pending') {
				throw new RuntimeException('Permohonan token tidak lagi menunggu keputusan.');
			}
			$member = $this->db->select('id, status')->from('members')->where('id', (int) $request['member_id'])->limit(1)->get()->row_array();
			if (! $member || ($member['status'] ?? '') !== 'active') {
				throw new RuntimeException('Member tidak aktif; permohonan tidak dapat disetujui.');
			}

			$settings = $this->get_token_settings();
			$this->db->insert('reading_tokens', [
				'member_id' => (int) $request['member_id'],
				'reading_point_id' => null,
				'token' => 'REQ-' . $this->new_token(),
				'quota_total' => max(1, (int) $request['requested_quota']),
				'quota_used' => 0,
				'quota_unit' => 'books',
				'expires_at' => $this->token_expiry_at((int) $settings['request_valid_days']),
				'status' => 'active',
				'issued_by' => (int) $reviewer_id ?: null,
			]);
			$token_id = (int) $this->db->insert_id();
			$this->db->where('id', (int) $request_id)->update('reading_token_requests', [
				'status' => 'approved',
				'reviewed_at' => date('Y-m-d H:i:s'),
				'reviewed_by' => (int) $reviewer_id ?: null,
				'review_note' => $this->blank_to_null(substr(trim((string) $review_note), 0, 500)),
				'reading_token_id' => $token_id,
			]);
			if ($this->db->trans_status() === false) {
				throw new RuntimeException('Gagal menerbitkan token.');
			}
			$this->db->trans_commit();
			return $token_id;
		} catch (Throwable $e) {
			$this->db->trans_rollback();
			throw $e;
		}
	}

	public function reject_token_request($request_id, $reviewer_id = null, $review_note = null)
	{
		if (! $this->db->table_exists('reading_token_requests')) {
			throw new RuntimeException('Modul permohonan token belum diaktifkan.');
		}
		$updated = $this->db->where('id', (int) $request_id)->where('status', 'pending')->update('reading_token_requests', [
			'status' => 'rejected',
			'reviewed_at' => date('Y-m-d H:i:s'),
			'reviewed_by' => (int) $reviewer_id ?: null,
			'review_note' => $this->blank_to_null(substr(trim((string) $review_note), 0, 500)),
		]);
		if (! $updated || $this->db->affected_rows() < 1) {
			throw new RuntimeException('Permohonan token tidak lagi menunggu keputusan.');
		}
		return true;
	}

	public function issue_member_checkin_token($member_id, $latitude, $longitude)
	{
		$lat = $this->decimal_or_null($latitude);
		$lng = $this->decimal_or_null($longitude);
		if ($lat === null || $lng === null) {
			throw new RuntimeException('Lokasi GPS belum terbaca.');
		}

		$location = $this->free_access_location($lat, $lng);
		if (($location['origin'] ?? 'external') === 'external') {
			throw new RuntimeException('Lokasi Anda belum berada dalam radius Pojok Baca atau perpustakaan terdaftar.');
		}
		$member = $this->db
			->from('members')
			->where('id', (int) $member_id)
			->limit(1)
			->get()
			->row_array();
		if (! $member || ($member['status'] ?? '') !== 'active' || ($member['card_status'] ?? 'active') === 'blocked') {
			throw new RuntimeException('Check-in zona baca hanya tersedia untuk member aktif.');
		}

		// Pojok Baca dan perpustakaan GIS adalah zona bebas: check-in hanya
		// mencatat kehadiran/lokasi, bukan menerbitkan token atau kuota sesi.
		if ($member) {
			$this->load->model('Visit_model');
			$this->Visit_model->record_free_zone_checkin($location, $member, [
				'latitude' => $lat,
				'longitude' => $lng,
			]);
		}

		return [
			'token' => null,
			'location' => $location,
			'free_access' => true,
			'is_new' => false,
		];
	}

	public function consume_reader_token($member_id, $latitude = null, $longitude = null, $amount = null)
	{
		$location = $this->free_access_location($latitude, $longitude);
		if ($location['origin'] !== 'external') {
			return [
				// Zona layanan tidak menggunakan token sama sekali. Reader tetap
				// membuat sesi aman untuk audit dan proteksi PDF, bukan sesi kuota.
				'token' => null,
				'origin' => $location['origin'],
				'location_label' => $location['label'],
				'reading_point_id' => $location['reading_point_id'] ?? null,
				'library_id' => $location['library_id'] ?? null,
				'quota_charged' => 0,
				'quota_unit' => null,
				'latitude' => $this->decimal_or_null($latitude),
				'longitude' => $this->decimal_or_null($longitude),
			];
		}

		$token = $this->get_member_active_token((int) $member_id);
		if (! $token) {
			throw new RuntimeException('Token baca luar zona tidak tersedia atau kuota sudah habis. Silakan login/update token di perpustakaan daerah, lalu coba baca kembali.');
		}

		$charge_amount = $amount === null ? (int) $this->get_token_settings()['outside_session_charge'] : max(1, (int) $amount);
		$charge = ($location['origin'] === 'external' && (int) $token['quota_total'] > 0) ? $charge_amount : 0;

		if ($charge > 0 && (int) $token['quota_total'] > 0) {
			$new_used = min((int) $token['quota_total'], (int) $token['quota_used'] + $charge);
			$status = $new_used >= (int) $token['quota_total'] ? 'used' : 'active';
			$this->db
				->where('id', (int) $token['id'])
				->update('reading_tokens', [
					'quota_used' => $new_used,
					'status' => $status,
				]);
			$token['quota_used'] = $new_used;
			$token['status'] = $status;
		}

		return [
			'token' => $token,
			'origin' => $location['origin'],
			'location_label' => $location['label'],
			'reading_point_id' => $location['reading_point_id'] ?? null,
			'library_id' => $location['library_id'] ?? null,
			'quota_charged' => $charge,
			'quota_unit' => $token['quota_unit'],
			'latitude' => $this->decimal_or_null($latitude),
			'longitude' => $this->decimal_or_null($longitude),
		];
	}

	private function count_where($table, array $where = [])
	{
		if (! $this->db->table_exists($table)) {
			return 0;
		}

		if (! empty($where)) {
			$this->db->where($where);
		}

		return (int) $this->db->count_all_results($table);
	}

	private function apply_token_filters(array $filters = [])
	{
		$this->expire_old_tokens();
		$this->db
			->from('reading_tokens rt')
			->join('members m', 'm.id = rt.member_id', 'left')
			->join('reading_points rp', 'rp.id = rt.reading_point_id', 'left');

		$q = trim((string) ($filters['q'] ?? ''));
		if ($q !== '') {
			$this->db->group_start()
				->like('rt.token', $q)
				->or_like('m.full_name', $q)
				->or_like('m.member_no', $q)
				->or_like('m.identity_number', $q)
				->or_like('rp.name', $q)
				->group_end();
		}

		$status = trim((string) ($filters['status'] ?? ''));
		if (in_array($status, ['active', 'used', 'expired', 'revoked'], true)) {
			$this->db->where('rt.status', $status);
		}
	}

	private function free_access_location($latitude, $longitude)
	{
		$lat = $this->decimal_or_null($latitude);
		$lng = $this->decimal_or_null($longitude);
		if ($lat === null || $lng === null) {
			return ['origin' => 'external', 'label' => 'Luar lokasi / GPS tidak tersedia', 'reading_point_id' => null, 'library_id' => null];
		}

		$point = $this->nearest_point_within_radius((float) $lat, (float) $lng);
		if ($point) {
			return [
				'origin' => 'reading_point',
				'label' => $point['name'],
				'reading_point_id' => (int) $point['id'],
				'library_id' => ! empty($point['library_id']) ? (int) $point['library_id'] : null,
			];
		}

		$library = $this->nearest_library_within_radius((float) $lat, (float) $lng);
		if ($library) {
			return [
				'origin' => 'library',
				'label' => $library['name'],
				'reading_point_id' => null,
				'library_id' => (int) $library['id'],
			];
		}

		return ['origin' => 'external', 'label' => 'Akses luar lokasi', 'reading_point_id' => null, 'library_id' => null];
	}

	private function nearest_point_within_radius($latitude, $longitude)
	{
		$nearest = null;
		foreach ($this->get_active_points(500) as $point) {
			$distance = $this->distance_meters($latitude, $longitude, (float) $point['latitude'], (float) $point['longitude']);
			if ($distance <= (int) $point['radius_meters'] && ($nearest === null || $distance < $nearest['distance_meters'])) {
				$point['distance_meters'] = $distance;
				$nearest = $point;
			}
		}

		return $nearest;
	}

	private function nearest_library_within_radius($latitude, $longitude)
	{
		if (! $this->db->table_exists('libraries')) {
			return null;
		}

		$rows = $this->db
			->select('id, name, latitude, longitude, service_radius_meters')
			->from('libraries')
			->where('status', 'active')
			->where('latitude IS NOT NULL', null, false)
			->where('longitude IS NOT NULL', null, false)
			->limit(500)
			->get()
			->result_array();

		$nearest = null;
		foreach ($rows as $library) {
			$radius = max(10, (int) ($library['service_radius_meters'] ?? 100));
			$distance = $this->distance_meters($latitude, $longitude, (float) $library['latitude'], (float) $library['longitude']);
			if ($distance <= $radius && ($nearest === null || $distance < $nearest['distance_meters'])) {
				$library['distance_meters'] = $distance;
				$nearest = $library;
			}
		}

		return $nearest;
	}

	private function central_library()
	{
		if (! $this->db->table_exists('libraries')) {
			return null;
		}
		return $this->db->select('id, name, code')
			->from('libraries')
			->where('status', 'active')
			->where('code', '001')
			->limit(1)
			->get()
			->row_array();
	}

	private function distance_meters($lat1, $lng1, $lat2, $lng2)
	{
		$earth = 6371000;
		$d_lat = deg2rad($lat2 - $lat1);
		$d_lng = deg2rad($lng2 - $lng1);
		$a = sin($d_lat / 2) * sin($d_lat / 2)
			+ cos(deg2rad($lat1)) * cos(deg2rad($lat2))
			* sin($d_lng / 2) * sin($d_lng / 2);

		return $earth * 2 * atan2(sqrt($a), sqrt(1 - $a));
	}

	private function expire_old_tokens()
	{
		if (! $this->db->table_exists('reading_tokens')) {
			return;
		}

		$this->db
			->where('status', 'active')
			->where('expires_at IS NOT NULL', null, false)
			->where('expires_at < NOW()', null, false)
			->update('reading_tokens', ['status' => 'expired']);
	}

	private function new_token()
	{
		return strtoupper(substr(hash('sha256', uniqid('', true) . random_int(100000, 999999)), 0, 24));
	}

	private function token_expiry_at($days)
	{
		$days = max(0, (int) $days);
		return $days === 0
			? date('Y-m-d 23:59:59')
			: date('Y-m-d 23:59:59', strtotime('+' . $days . ' days'));
	}

	private function apply_point_filters(array $filters = [])
	{
		$this->db
			->select('rp.*, l.name AS library_name, l.district, l.village')
			->from('reading_points rp')
			->join('libraries l', 'l.id = rp.library_id', 'left');

		$q = trim((string) ($filters['q'] ?? ''));
		if ($q !== '') {
			$this->db->group_start()
				->like('rp.name', $q)
				->or_like('rp.partner_name', $q)
				->or_like('rp.address', $q)
				->or_like('l.name', $q)
				->group_end();
		}

		$status = trim((string) ($filters['status'] ?? ''));
		if (in_array($status, ['draft', 'active', 'inactive'], true)) {
			$this->db->where('rp.status', $status);
		}
	}

	private function point_payload(array $data)
	{
		$name = trim((string) ($data['name'] ?? ''));
		if ($name === '') {
			throw new RuntimeException('Nama titik pojok baca wajib diisi.');
		}

		$status = in_array(($data['status'] ?? ''), ['draft', 'active', 'inactive'], true) ? $data['status'] : 'draft';
		$quota_unit = in_array(($data['quota_unit'] ?? ''), ['minutes', 'pages', 'books'], true) ? $data['quota_unit'] : 'books';

		return [
			'library_id' => ! empty($data['library_id']) ? (int) $data['library_id'] : null,
			'partner_name' => $this->blank_to_null($data['partner_name'] ?? null),
			'name' => substr($name, 0, 180),
			'address' => $this->blank_to_null($data['address'] ?? null),
			'latitude' => $this->decimal_or_null($data['latitude'] ?? null),
			'longitude' => $this->decimal_or_null($data['longitude'] ?? null),
			'radius_meters' => max(10, min(5000, (int) ($data['radius_meters'] ?? 100))),
			'daily_quota' => max(0, min(100000, (int) ($data['daily_quota'] ?? 0))),
			'quota_unit' => $quota_unit,
			'opening_hours' => $this->blank_to_null($data['opening_hours'] ?? null),
			'status' => $status,
		];
	}

	private function blank_to_null($value)
	{
		$value = is_string($value) ? trim($value) : $value;
		return $value === '' ? null : $value;
	}

	private function decimal_or_null($value)
	{
		$value = trim((string) $value);
		if ($value === '' || ! is_numeric($value)) {
			return null;
		}

		return number_format((float) $value, 7, '.', '');
	}
}
