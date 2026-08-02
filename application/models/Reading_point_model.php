<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reading_point_model extends CI_Model
{
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
			->select('rt.*, m.full_name, m.member_no, rp.name AS point_name')
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
			->select('rt.*, m.full_name, m.member_no, m.identity_number, rp.name AS point_name, rp.partner_name')
			->order_by("FIELD(rt.status, 'active', 'used', 'expired', 'revoked')", '', false)
			->order_by('rt.id', 'DESC')
			->limit(max(1, min(100, (int) $limit)), max(0, (int) $offset))
			->get()
			->result_array();
	}

	public function get_token($id)
	{
		return $this->db
			->select('rt.*, m.full_name, m.member_no, rp.name AS point_name')
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
			->select('rt.*, rp.name AS point_name, rp.partner_name, rp.address AS point_address')
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

	public function get_member_tokens($member_id, $limit = 8)
	{
		$this->expire_old_tokens();

		return $this->db
			->select('rt.*, rp.name AS point_name')
			->from('reading_tokens rt')
			->join('reading_points rp', 'rp.id = rt.reading_point_id', 'left')
			->where('rt.member_id', (int) $member_id)
			->order_by('rt.id', 'DESC')
			->limit(max(1, min(20, (int) $limit)))
			->get()
			->result_array();
	}

	public function issue_member_checkin_token($member_id, $latitude, $longitude)
	{
		$lat = $this->decimal_or_null($latitude);
		$lng = $this->decimal_or_null($longitude);
		if ($lat === null || $lng === null) {
			throw new RuntimeException('Lokasi GPS belum terbaca.');
		}

		$point = $this->nearest_point_within_radius((float) $lat, (float) $lng);
		if (! $point) {
			throw new RuntimeException('Lokasi Anda belum berada dalam radius Pojok Baca aktif.');
		}

		$existing = $this->get_member_active_token((int) $member_id);
		if ($existing && (int) ($existing['reading_point_id'] ?? 0) === (int) $point['id']) {
			return [
				'token' => $existing,
				'point' => $point,
				'is_new' => false,
			];
		}

		$expires_at = date('Y-m-d 23:59:59');
		$payload = [
			'member_id' => (int) $member_id,
			'reading_point_id' => (int) $point['id'],
			'token' => $this->new_token(),
			'quota_total' => (int) $point['daily_quota'],
			'quota_used' => 0,
			'quota_unit' => $point['quota_unit'],
			'expires_at' => $expires_at,
			'status' => 'active',
		];

		$this->db->insert('reading_tokens', $payload);
		$payload['id'] = (int) $this->db->insert_id();
		$payload['point_name'] = $point['name'];
		$member = $this->db
			->from('members')
			->where('id', (int) $member_id)
			->limit(1)
			->get()
			->row_array();
		if ($member) {
			$this->load->model('Visit_model');
			$this->Visit_model->record_reading_point_checkin($payload, $point, $member, [
				'latitude' => $lat,
				'longitude' => $lng,
			]);
		}

		return [
			'token' => $payload,
			'point' => $point,
			'is_new' => true,
		];
	}

	public function consume_reader_token($member_id, $latitude = null, $longitude = null, $amount = 1)
	{
		$token = $this->get_member_active_token((int) $member_id);
		if (! $token) {
			throw new RuntimeException('Token baca tidak tersedia atau kuota sudah habis. Silakan update token di perpustakaan daerah atau titik layanan.');
		}

		$location = $this->free_access_location($latitude, $longitude);
		$charge = ($location['origin'] === 'external' && (int) $token['quota_total'] > 0) ? max(1, (int) $amount) : 0;

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
			return ['origin' => 'external', 'label' => 'Luar lokasi / GPS tidak tersedia'];
		}

		$point = $this->nearest_point_within_radius((float) $lat, (float) $lng);
		if ($point) {
			return ['origin' => 'reading_point', 'label' => $point['name']];
		}

		$library = $this->nearest_library_within_radius((float) $lat, (float) $lng);
		if ($library) {
			return ['origin' => 'library', 'label' => $library['name']];
		}

		return ['origin' => 'external', 'label' => 'Akses luar lokasi'];
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
		$quota_unit = in_array(($data['quota_unit'] ?? ''), ['minutes', 'pages', 'books'], true) ? $data['quota_unit'] : 'minutes';

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
