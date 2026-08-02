<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reader_model extends CI_Model
{
	public function stats()
	{
		return [
			'assets' => $this->count_where('digital_assets'),
			'active_assets' => $this->count_where('digital_assets', ['status' => 'active']),
			'online_only' => $this->count_where('digital_assets', ['access_policy' => 'online_only']),
			'downloadable' => $this->count_where('digital_assets', ['is_downloadable' => 1]),
			'locked_assets' => $this->count_locked_assets(),
			'rights_expiring' => $this->count_rights_expiring(),
			'sessions' => $this->count_where('reading_sessions'),
		];
	}

	public function get_assets($limit = 25, $offset = 0, array $filters = [])
	{
		if (! $this->db->table_exists('digital_assets')) {
			return [];
		}

		$this->apply_asset_filters($filters);
		return $this->db
			->select('da.*, b.title, b.call_number')
			->order_by('da.id', 'DESC')
			->limit(max(1, min(100, (int) $limit)), max(0, (int) $offset))
			->get()
			->result_array();
	}

	public function count_assets(array $filters = [])
	{
		if (! $this->db->table_exists('digital_assets')) {
			return 0;
		}

		$this->apply_asset_filters($filters);
		return (int) $this->db->count_all_results();
	}

	public function get_asset($id)
	{
		if (! $this->db->table_exists('digital_assets')) {
			return null;
		}

		return $this->db
			->select('da.*, b.title, b.statement_responsibility, b.call_number')
			->from('digital_assets da')
			->join('books b', 'b.id = da.book_id', 'left')
			->where('da.id', (int) $id)
			->limit(1)
			->get()
			->row_array();
	}

	public function book_options($limit = 500)
	{
		if (! $this->db->table_exists('books')) {
			return [];
		}

		return $this->db
			->select('id, title, call_number, publish_year')
			->from('books')
			->where('deleted_at IS NULL', null, false)
			->order_by('title', 'ASC')
			->limit(max(1, min(1000, (int) $limit)))
			->get()
			->result_array();
	}

	public function book_option($id)
	{
		if (! $this->db->table_exists('books')) {
			return null;
		}

		return $this->db
			->select('id, title, call_number, publish_year')
			->from('books')
			->where('id', (int) $id)
			->where('deleted_at IS NULL', null, false)
			->limit(1)
			->get()
			->row_array();
	}

	public function create_asset(array $data, $uploaded_by = null)
	{
		$payload = $this->asset_payload($data);
		$payload['uploaded_by'] = (int) $uploaded_by ?: null;
		$this->db->insert('digital_assets', $payload);
		return (int) $this->db->insert_id();
	}

	public function update_asset($id, array $data)
	{
		$payload = $this->asset_payload($data, false);
		if (empty($data['file_path'])) {
			unset(
				$payload['file_original_name'],
				$payload['file_path'],
				$payload['mime_type'],
				$payload['file_size'],
				$payload['source_path'],
				$payload['migration_status'],
				$payload['migrated_at']
			);
		}
		$this->db->where('id', (int) $id)->update('digital_assets', $payload);
		return $this->db->affected_rows() >= 0;
	}

	public function change_asset_status($id, $status)
	{
		if (! in_array($status, ['draft', 'active', 'archived'], true)) {
			throw new InvalidArgumentException('Status aset tidak valid.');
		}

		return $this->db
			->where('id', (int) $id)
			->update('digital_assets', [
				'status' => $status,
				'updated_at' => date('Y-m-d H:i:s'),
			]);
	}

	public function get_access_logs($limit = 50, array $filters = [])
	{
		if (! $this->db->table_exists('reader_access_logs')) {
			return [];
		}

		$this->db
			->select('ral.*, m.full_name, m.member_no, b.title')
			->from('reader_access_logs ral')
			->join('members m', 'm.id = ral.member_id', 'left')
			->join('books b', 'b.id = ral.book_id', 'left');

		$event_type = trim((string) ($filters['event_type'] ?? ''));
		if (in_array($event_type, ['session_opened', 'pdf_stream', 'page_rendered', 'rate_limited', 'blocked', 'finished'], true)) {
			$this->db->where('ral.event_type', $event_type);
		}

		return $this->db
			->order_by('ral.id', 'DESC')
			->limit(max(1, min(200, (int) $limit)))
			->get()
			->result_array();
	}

	public function get_recent_sessions($limit = 10)
	{
		if (! $this->db->table_exists('reading_sessions')) {
			return [];
		}

		return $this->db
			->select('rs.*, m.full_name, m.member_no, b.title')
			->from('reading_sessions rs')
			->join('members m', 'm.id = rs.member_id', 'left')
			->join('books b', 'b.id = rs.book_id', 'left')
			->order_by('rs.id', 'DESC')
			->limit(max(1, min(30, (int) $limit)))
			->get()
			->result_array();
	}

	public function create_session(array $asset, array $member = null)
	{
		return $this->create_session_with_context($asset, $member);
	}

	public function create_session_with_context(array $asset, array $member = null, array $context = [])
	{
		if (! $this->db->table_exists('reading_sessions') || empty($asset['book_id']) || empty($member['id'])) {
			return 0;
		}

		$secure_token = $this->new_secure_token();
		$this->db->insert('reading_sessions', [
			'member_id' => (int) $member['id'],
			'book_id' => (int) $asset['book_id'],
			'digital_asset_id' => (int) $asset['id'],
			'reading_point_id' => ! empty($context['token']['reading_point_id']) ? (int) $context['token']['reading_point_id'] : null,
			'reading_token_id' => ! empty($context['token']['id']) ? (int) $context['token']['id'] : null,
			'secure_token' => $this->db->field_exists('secure_token', 'reading_sessions') ? $secure_token : null,
			'ip_address' => $this->input->ip_address(),
			'user_agent' => substr((string) $this->input->user_agent(), 0, 255),
			'latitude' => $context['latitude'] ?? null,
			'longitude' => $context['longitude'] ?? null,
			'access_policy' => $asset['access_policy'],
			'access_origin' => $context['origin'] ?? 'external',
			'access_location_label' => $context['location_label'] ?? null,
			'quota_charged' => (int) ($context['quota_charged'] ?? 0),
			'quota_unit' => $context['quota_unit'] ?? null,
			'status' => 'active',
		]);

		$session_id = (int) $this->db->insert_id();
		if ($session_id > 0 && (($context['origin'] ?? 'external') !== 'admin')) {
			$this->load->model('Visit_model');
			$this->Visit_model->record_reader_session_visit($session_id, $asset, $member, $context);
			$this->log_access($session_id, $member, $asset, 'session_opened', null, $context);
		}

		return $session_id;
	}

	public function get_session($session_id)
	{
		if (! $this->db->table_exists('reading_sessions')) {
			return null;
		}

		return $this->db
			->from('reading_sessions')
			->where('id', (int) $session_id)
			->limit(1)
			->get()
			->row_array();
	}

	public function secure_session_context(array $asset, array $member, $session_id, $secure_token, $reason_prefix = 'reader')
	{
		$session = $this->get_session((int) $session_id);
		if (! $session) {
			$this->log_access(null, $member, $asset, 'blocked', null, ['reason' => $reason_prefix . '_missing_session']);
			return null;
		}

		$expected = (string) ($session['secure_token'] ?? '');
		if ((int) $session['member_id'] !== (int) $member['id']
			|| (int) $session['digital_asset_id'] !== (int) $asset['id']
			|| $session['status'] !== 'active'
			|| $expected === ''
			|| ! hash_equals($expected, (string) $secure_token)
		) {
			$this->log_access((int) ($session['id'] ?? 0), $member, $asset, 'blocked', null, ['reason' => $reason_prefix . '_invalid_token']);
			return null;
		}

		if (! empty($session['started_at']) && strtotime($session['started_at']) < time() - 10800) {
			$this->db->where('id', (int) $session['id'])->update('reading_sessions', ['status' => 'expired']);
			$this->log_access((int) $session['id'], $member, $asset, 'blocked', null, ['reason' => $reason_prefix . '_expired_session']);
			return null;
		}

		return $session;
	}

	public function secure_stream_context(array $asset, array $member, $session_id, $secure_token)
	{
		$session = $this->secure_session_context($asset, $member, $session_id, $secure_token, 'stream');
		if (! $session) {
			return null;
		}

		if ($this->is_rate_limited((int) $session['id'], (int) $member['id'])) {
			$this->log_access((int) $session['id'], $member, $asset, 'rate_limited', null, ['reason' => 'stream_rate_limit']);
			return null;
		}

		$this->db
			->where('id', (int) $session['id'])
			->update('reading_sessions', ['last_seen_at' => date('Y-m-d H:i:s')]);
		$this->log_access((int) $session['id'], $member, $asset, 'pdf_stream');

		return $session;
	}

	public function secure_page_context(array $asset, array $member, $session_id, $secure_token, $page_number)
	{
		$session = $this->secure_session_context($asset, $member, $session_id, $secure_token, 'page');
		if (! $session) {
			return null;
		}

		if ($this->is_page_rate_limited((int) $session['id'], (int) $member['id'])) {
			$this->log_access((int) $session['id'], $member, $asset, 'rate_limited', null, ['reason' => 'page_rate_limit']);
			return null;
		}

		return $session;
	}

	public function record_rendered_page(array $session, array $asset, array $member, $page_number, array $meta = [])
	{
		$page_number = max(1, (int) $page_number);
		$this->db
			->where('id', (int) $session['id'])
			->update('reading_sessions', [
				'last_page' => $page_number,
				'last_seen_at' => date('Y-m-d H:i:s'),
			]);

		return $this->log_access((int) $session['id'], $member, $asset, 'page_rendered', $page_number, $meta);
	}

	public function log_reader_page($session_id, $secure_token, $page_number, array $member)
	{
		$session = $this->get_session((int) $session_id);
		if (! $session || (int) $session['member_id'] !== (int) $member['id'] || ! hash_equals((string) ($session['secure_token'] ?? ''), (string) $secure_token)) {
			return false;
		}

		$asset = [
			'id' => (int) $session['digital_asset_id'],
			'book_id' => (int) $session['book_id'],
		];
		$page_number = max(1, (int) $page_number);
		$this->db
			->where('id', (int) $session['id'])
			->update('reading_sessions', [
				'last_page' => $page_number,
				'last_seen_at' => date('Y-m-d H:i:s'),
			]);

		return $this->log_access((int) $session['id'], $member, $asset, 'page_rendered', $page_number);
	}

	public function log_reader_block(array $asset, array $member = null, $reason = 'blocked', $session_id = null)
	{
		return $this->log_access($session_id, (array) $member, $asset, 'blocked', null, [
			'reason' => $reason,
		]);
	}

	public function log_admin_event(array $asset, array $user, $event_type, $page_number = null, array $meta = [])
	{
		$meta['admin_user_id'] = (int) ($user['id'] ?? 0);
		$meta['admin_username'] = (string) ($user['username'] ?? '');

		return $this->log_access(null, [], $asset, $event_type, $page_number, $meta);
	}

	public function has_recent_active_session($asset_id, $member_id, $minutes = 180)
	{
		if (! $this->db->table_exists('reading_sessions')) {
			return false;
		}

		$row = $this->db
			->select('id')
			->from('reading_sessions')
			->where('digital_asset_id', (int) $asset_id)
			->where('member_id', (int) $member_id)
			->where('status', 'active')
			->where('started_at >=', date('Y-m-d H:i:s', time() - (max(5, (int) $minutes) * 60)))
			->order_by('id', 'DESC')
			->limit(1)
			->get()
			->row_array();

		return ! empty($row);
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

	private function count_locked_assets()
	{
		if (! $this->db->table_exists('digital_assets')) {
			return 0;
		}

		return (int) $this->db
			->where('is_downloadable', 0)
			->where('status', 'active')
			->count_all_results('digital_assets');
	}

	private function count_rights_expiring()
	{
		if (! $this->db->table_exists('digital_assets') || ! $this->db->field_exists('permission_ends_at', 'digital_assets')) {
			return 0;
		}

		return (int) $this->db
			->where('permission_ends_at IS NOT NULL', null, false)
			->where('permission_ends_at >=', date('Y-m-d'))
			->where('permission_ends_at <=', date('Y-m-d', strtotime('+60 days')))
			->where('status', 'active')
			->count_all_results('digital_assets');
	}

	private function apply_asset_filters(array $filters = [])
	{
		$this->db
			->from('digital_assets da')
			->join('books b', 'b.id = da.book_id', 'left');

		$q = trim((string) ($filters['q'] ?? ''));
		if ($q !== '') {
			$this->db->group_start()
				->like('b.title', $q)
				->or_like('b.call_number', $q)
				->or_like('da.file_original_name', $q)
				->or_like('da.source_id', $q)
				->or_like('da.rights_holder', $q)
				->group_end();
		}

		$status = trim((string) ($filters['status'] ?? ''));
		if (in_array($status, ['draft', 'active', 'archived'], true)) {
			$this->db->where('da.status', $status);
		}

		$policy = trim((string) ($filters['access_policy'] ?? ''));
		if (in_array($policy, ['online_only', 'download_allowed', 'location_only', 'member_only', 'internal'], true)) {
			$this->db->where('da.access_policy', $policy);
		}

		$rights = trim((string) ($filters['rights_basis'] ?? ''));
		if (in_array($rights, ['public_domain', 'licensed', 'owned', 'permission_letter', 'internal_use', 'unknown'], true)) {
			$this->db->where('da.rights_basis', $rights);
		}
	}

	private function asset_payload(array $data, $require_file = true)
	{
		$book_id = (int) ($data['book_id'] ?? 0);
		if ($book_id <= 0) {
			throw new InvalidArgumentException('Buku wajib dipilih.');
		}

		$access_policy = (string) ($data['access_policy'] ?? 'internal');
		if (! in_array($access_policy, ['online_only', 'download_allowed', 'location_only', 'member_only', 'internal'], true)) {
			$access_policy = 'internal';
		}

		$status = (string) ($data['status'] ?? 'draft');
		if (! in_array($status, ['draft', 'active', 'archived'], true)) {
			$status = 'draft';
		}

		$rights_basis = (string) ($data['rights_basis'] ?? 'unknown');
		if (! in_array($rights_basis, ['public_domain', 'licensed', 'owned', 'permission_letter', 'internal_use', 'unknown'], true)) {
			$rights_basis = 'unknown';
		}

		$is_downloadable = $access_policy === 'download_allowed' ? 1 : (int) ! empty($data['is_downloadable']);
		if ($access_policy !== 'download_allowed') {
			$is_downloadable = 0;
		}

		$file_path = trim((string) ($data['file_path'] ?? ''));
		if ($require_file && $file_path === '') {
			throw new InvalidArgumentException('File PDF wajib diunggah.');
		}

		$payload = [
			'book_id' => $book_id,
			'source_system' => $this->blank_to_null($data['source_system'] ?? 'manual_upload'),
			'source_id' => $this->blank_to_null($data['source_id'] ?? null),
			'source_path' => $this->blank_to_null($data['source_path'] ?? null),
			'migration_status' => $data['migration_status'] ?? 'copied',
			'migrated_at' => $data['migrated_at'] ?? date('Y-m-d H:i:s'),
			'file_original_name' => $this->blank_to_null($data['file_original_name'] ?? null),
			'file_path' => $file_path !== '' ? $file_path : null,
			'mime_type' => $this->blank_to_null($data['mime_type'] ?? 'application/pdf'),
			'file_size' => ! empty($data['file_size']) ? (int) $data['file_size'] : null,
			'access_policy' => $access_policy,
			'is_downloadable' => $is_downloadable,
			'status' => $status,
			'updated_at' => date('Y-m-d H:i:s'),
		];

		if ($this->db->field_exists('rights_basis', 'digital_assets')) {
			$payload['rights_basis'] = $rights_basis;
			$payload['rights_holder'] = $this->blank_to_null($data['rights_holder'] ?? null);
			$payload['license_url'] = $this->blank_to_null($data['license_url'] ?? null);
			$payload['permission_reference'] = $this->blank_to_null($data['permission_reference'] ?? null);
			$payload['permission_starts_at'] = $this->date_or_null($data['permission_starts_at'] ?? null);
			$payload['permission_ends_at'] = $this->date_or_null($data['permission_ends_at'] ?? null);
			$payload['access_notes'] = $this->blank_to_null($data['access_notes'] ?? null);
		}

		return $payload;
	}

	private function blank_to_null($value)
	{
		$value = trim((string) $value);
		return $value === '' ? null : $value;
	}

	private function date_or_null($value)
	{
		$value = trim((string) $value);
		return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : null;
	}

	private function new_secure_token()
	{
		return bin2hex(random_bytes(24));
	}

	private function is_rate_limited($session_id, $member_id)
	{
		if (! $this->db->table_exists('reader_access_logs')) {
			return false;
		}

		$count = (int) $this->db
			->where('member_id', (int) $member_id)
			->where('reading_session_id', (int) $session_id)
			->where('event_type', 'pdf_stream')
			->where('created_at >=', date('Y-m-d H:i:s', time() - 60))
			->count_all_results('reader_access_logs');

		return $count >= 12;
	}

	private function is_page_rate_limited($session_id, $member_id)
	{
		if (! $this->db->table_exists('reader_access_logs')) {
			return false;
		}

		$count = (int) $this->db
			->where('member_id', (int) $member_id)
			->where('reading_session_id', (int) $session_id)
			->where('event_type', 'page_rendered')
			->where('created_at >=', date('Y-m-d H:i:s', time() - 60))
			->count_all_results('reader_access_logs');

		return $count >= 80;
	}

	private function log_access($session_id, array $member, array $asset, $event_type, $page_number = null, array $meta = [])
	{
		if (! $this->db->table_exists('reader_access_logs')) {
			return false;
		}

		$this->db->insert('reader_access_logs', [
			'reading_session_id' => $session_id ? (int) $session_id : null,
			'member_id' => ! empty($member['id']) ? (int) $member['id'] : null,
			'digital_asset_id' => ! empty($asset['id']) ? (int) $asset['id'] : null,
			'book_id' => ! empty($asset['book_id']) ? (int) $asset['book_id'] : null,
			'event_type' => $event_type,
			'page_number' => $page_number ? (int) $page_number : null,
			'ip_address' => $this->input->ip_address(),
			'user_agent' => substr((string) $this->input->user_agent(), 0, 255),
			'meta_json' => empty($meta) ? null : json_encode($meta, JSON_UNESCAPED_UNICODE),
		]);

		return $this->db->affected_rows() > 0;
	}
}
