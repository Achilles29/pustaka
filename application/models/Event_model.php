<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Event_model extends CI_Model
{
	public function stats($scope_library_id = null)
	{
		return [
			'events' => $this->count_where('literacy_events', [], $scope_library_id),
			'published' => $this->count_where('literacy_events', ['status' => 'published'], $scope_library_id),
			'open' => $this->count_open_events($scope_library_id),
			'registrations' => $this->count_registrations($scope_library_id),
			'pending' => $this->count_registrations($scope_library_id, ['pending']),
			'attended' => $this->count_registrations($scope_library_id, ['attended']),
		];
	}

	public function get_events(array $filters = [], $limit = 25, $offset = 0, $scope_library_id = null)
	{
		if (! $this->db->table_exists('literacy_events')) {
			return [];
		}

		$this->build_events_query($filters, $scope_library_id);

		return $this->db
			->order_by("FIELD(e.status, 'published', 'draft', 'closed', 'cancelled')", '', false)
			->order_by('e.starts_at IS NULL', 'ASC', false)
			->order_by('e.starts_at', 'ASC')
			->limit(max(1, min(100, (int) $limit)), max(0, (int) $offset))
			->get()
			->result_array();
	}

	public function count_events(array $filters = [], $scope_library_id = null)
	{
		if (! $this->db->table_exists('literacy_events')) {
			return 0;
		}

		$this->db
			->from('literacy_events e')
			->join('event_categories c', 'c.id = e.event_category_id', 'left')
			->join('libraries l', 'l.id = e.library_id', 'left');

		$this->apply_admin_event_filters($filters, $scope_library_id);
		return (int) $this->db->count_all_results();
	}

	public function get_event($id, $scope_library_id = null)
	{
		$this->db
			->select('e.*, c.name AS category_name, c.color AS category_color, l.name AS library_name')
			->from('literacy_events e')
			->join('event_categories c', 'c.id = e.event_category_id', 'left')
			->join('libraries l', 'l.id = e.library_id', 'left')
			->where('e.id', (int) $id);

		if (! empty($scope_library_id)) {
			$this->db->where('e.library_id', (int) $scope_library_id);
		}

		return $this->db->limit(1)->get()->row_array();
	}

	public function get_public_event($id)
	{
		return $this->db
			->select('e.*, c.name AS category_name, c.color AS category_color, l.name AS library_name')
			->from('literacy_events e')
			->join('event_categories c', 'c.id = e.event_category_id', 'left')
			->join('libraries l', 'l.id = e.library_id', 'left')
			->where('e.id', (int) $id)
			->where('e.status', 'published')
			->limit(1)
			->get()
			->row_array();
	}

	public function get_public_events(array $filters = [], $limit = 12, $offset = 0)
	{
		$this->build_public_events_query($filters);
		return $this->db
			->order_by('e.starts_at IS NULL', 'ASC', false)
			->order_by('e.starts_at', 'ASC')
			->limit(max(1, min(48, (int) $limit)), max(0, (int) $offset))
			->get()
			->result_array();
	}

	public function count_public_events(array $filters = [])
	{
		$this->db
			->from('literacy_events e')
			->join('event_categories c', 'c.id = e.event_category_id', 'left')
			->join('libraries l', 'l.id = e.library_id', 'left')
			->where('e.status', 'published');

		$this->apply_public_event_filters($filters);
		return (int) $this->db->count_all_results();
	}

	public function get_dashboard_events($member_id = null, $limit = 4)
	{
		$events = $this->get_public_events(['time' => 'upcoming'], max(1, min(8, (int) $limit)), 0);
		if (empty($events) || empty($member_id)) {
			return $events;
		}

		$event_ids = array_map(function ($event) {
			return (int) $event['id'];
		}, $events);

		$registrations = $this->db
			->select('event_id, registration_code, status, ticket_token')
			->from('event_registrations')
			->where('member_id', (int) $member_id)
			->where_in('event_id', $event_ids)
			->where_not_in('status', ['rejected', 'cancelled'])
			->order_by('registered_at', 'DESC')
			->get()
			->result_array();

		$registration_map = [];
		foreach ($registrations as $registration) {
			if (! isset($registration_map[(int) $registration['event_id']])) {
				$registration_map[(int) $registration['event_id']] = $registration;
			}
		}

		foreach ($events as &$event) {
			$event['member_registration'] = $registration_map[(int) $event['id']] ?? null;
		}
		unset($event);

		return $events;
	}

	public function create_event(array $data, $created_by = null, $poster_path = null)
	{
		$payload = $this->event_payload($data, null);
		$payload['created_by'] = (int) $created_by ?: null;
		if ($poster_path) {
			$payload['poster_path'] = $poster_path;
		}

		$this->db->insert('literacy_events', $payload);
		return (int) $this->db->insert_id();
	}

	public function update_event($id, array $data, $poster_path = null)
	{
		$payload = $this->event_payload($data, (int) $id);
		if ($poster_path) {
			$payload['poster_path'] = $poster_path;
		}

		$this->db
			->where('id', (int) $id)
			->update('literacy_events', $payload);

		return $this->db->affected_rows() >= 0;
	}

	public function update_status($id, $status, $reason = null)
	{
		$status = in_array($status, ['draft', 'published', 'closed', 'cancelled'], true) ? $status : 'draft';
		$payload = [
			'status' => $status,
			'cancelled_reason' => $status === 'cancelled' ? $this->blank_to_null($reason) : null,
		];
		if ($status === 'published') {
			$payload['published_at'] = date('Y-m-d H:i:s');
		}

		return $this->db->where('id', (int) $id)->update('literacy_events', $payload);
	}

	public function category_options()
	{
		if (! $this->db->table_exists('event_categories')) {
			return [];
		}

		return $this->db
			->from('event_categories')
			->where('is_active', 1)
			->order_by('sort_order', 'ASC')
			->order_by('name', 'ASC')
			->get()
			->result_array();
	}

	public function create_category(array $data)
	{
		$name = $this->required_text($data['name'] ?? '', 'Nama kategori wajib diisi.', 120);
		$slug = $this->unique_category_slug($name);
		$color = trim((string) ($data['color'] ?? '#005baa'));
		if (! preg_match('/^#[0-9a-f]{6}$/i', $color)) {
			$color = '#005baa';
		}

		$this->db->insert('event_categories', [
			'name' => $name,
			'slug' => $slug,
			'description' => $this->limit_text($data['description'] ?? null, 255),
			'color' => $color,
			'is_active' => 1,
			'sort_order' => max(0, (int) ($data['sort_order'] ?? 100)),
		]);

		return (int) $this->db->insert_id();
	}

	public function library_options($scope_library_id = null)
	{
		if (! $this->db->table_exists('libraries')) {
			return [];
		}

		$this->db
			->select('id, code, name, address, latitude, longitude')
			->from('libraries')
			->order_by('name', 'ASC');

		if (! empty($scope_library_id)) {
			$this->db->where('id', (int) $scope_library_id);
		}

		return $this->db->get()->result_array();
	}

	public function get_form_fields($event_id, $active_only = false)
	{
		$this->db
			->from('event_form_fields')
			->where('event_id', (int) $event_id);

		if ($active_only) {
			$this->db->where('is_active', 1);
		}

		$rows = $this->db
			->order_by('sort_order', 'ASC')
			->order_by('id', 'ASC')
			->get()
			->result_array();

		foreach ($rows as &$row) {
			$row['options'] = $this->parse_options($row['options_text'] ?? '');
		}

		return $rows;
	}

	public function get_form_field($field_id, $event_id)
	{
		return $this->db
			->from('event_form_fields')
			->where('id', (int) $field_id)
			->where('event_id', (int) $event_id)
			->limit(1)
			->get()
			->row_array();
	}

	public function create_form_field($event_id, array $data)
	{
		$payload = $this->field_payload($event_id, $data);
		$this->db->insert('event_form_fields', $payload);
		return (int) $this->db->insert_id();
	}

	public function update_form_field($field_id, $event_id, array $data)
	{
		$payload = $this->field_payload($event_id, $data);
		unset($payload['event_id']);

		return $this->db
			->where('id', (int) $field_id)
			->where('event_id', (int) $event_id)
			->update('event_form_fields', $payload);
	}

	public function toggle_form_field($field_id, $event_id)
	{
		$field = $this->get_form_field($field_id, $event_id);
		if (! $field) {
			return false;
		}

		return $this->db
			->where('id', (int) $field_id)
			->where('event_id', (int) $event_id)
			->update('event_form_fields', ['is_active' => empty($field['is_active']) ? 1 : 0]);
	}

	public function get_registrations($event_id, array $filters = [], $limit = 50, $offset = 0)
	{
		$this->build_registration_query($event_id, $filters);
		return $this->db
			->order_by("FIELD(er.status, 'pending', 'registered', 'approved', 'attended', 'rejected', 'cancelled')", '', false)
			->order_by('er.registered_at', 'DESC')
			->limit(max(1, min(200, (int) $limit)), max(0, (int) $offset))
			->get()
			->result_array();
	}

	public function count_event_registrations($event_id, array $filters = [])
	{
		$this->build_registration_query($event_id, $filters);
		return (int) $this->db->count_all_results();
	}

	public function get_registration($id)
	{
		return $this->db
			->select('er.*, e.title AS event_title')
			->from('event_registrations er')
			->join('literacy_events e', 'e.id = er.event_id')
			->where('er.id', (int) $id)
			->limit(1)
			->get()
			->row_array();
	}

	public function get_registration_by_ticket($token)
	{
		$registration = $this->db
			->select('er.*, e.title AS event_title, e.starts_at, e.ends_at, e.location_name, e.venue_type, e.online_url, c.name AS category_name, l.name AS library_name')
			->from('event_registrations er')
			->join('literacy_events e', 'e.id = er.event_id')
			->join('event_categories c', 'c.id = e.event_category_id', 'left')
			->join('libraries l', 'l.id = e.library_id', 'left')
			->where('er.ticket_token', (string) $token)
			->limit(1)
			->get()
			->row_array();

		if ($registration) {
			$registration['answers'] = $this->get_registration_answers((int) $registration['id']);
		}

		return $registration;
	}

	public function get_registration_by_attendance_token($token)
	{
		return $this->db
			->select('er.*, e.title AS event_title')
			->from('event_registrations er')
			->join('literacy_events e', 'e.id = er.event_id')
			->where('er.attendance_token', (string) $token)
			->limit(1)
			->get()
			->row_array();
	}

	public function get_registration_answers($registration_id)
	{
		return $this->db
			->from('event_registration_answers')
			->where('registration_id', (int) $registration_id)
			->order_by('id', 'ASC')
			->get()
			->result_array();
	}

	public function create_registration($event_id, array $data, array $answers = [], array $member = null)
	{
		$event = $this->get_public_event((int) $event_id);
		if (! $event) {
			throw new RuntimeException('Event tidak ditemukan atau belum dipublikasikan.');
		}

		if ($event['registration_mode'] === 'none') {
			throw new RuntimeException('Event ini tidak membuka pendaftaran online.');
		}

		if ($event['registration_mode'] === 'member_only' && empty($member['id'])) {
			throw new RuntimeException('Event ini khusus member. Silakan login terlebih dahulu.');
		}

		if (! $this->registration_is_open($event)) {
			throw new RuntimeException('Pendaftaran event belum dibuka atau sudah ditutup.');
		}

		$participant_count = max(1, min(1000, (int) ($data['participant_count'] ?? 1)));
		$this->assert_quota_available($event, $participant_count);

		$fields = $this->get_form_fields((int) $event_id, true);
		$this->validate_dynamic_answers($fields, $answers);

		$status = $event['approval_mode'] === 'manual' ? 'pending' : 'registered';
		$payload = [
			'registration_code' => $this->next_registration_code(),
			'event_id' => (int) $event_id,
			'member_id' => ! empty($member['id']) ? (int) $member['id'] : null,
			'participant_type' => ! empty($member['id']) ? 'member' : $this->clean_participant_type($data['participant_type'] ?? 'public'),
			'participant_name' => $this->required_text($data['participant_name'] ?? ($member['full_name'] ?? ''), 'Nama peserta wajib diisi.', 180),
			'participant_phone' => $this->blank_to_null($data['participant_phone'] ?? ($member['phone'] ?? null)),
			'participant_email' => $this->blank_to_null($data['participant_email'] ?? ($member['email'] ?? null)),
			'institution' => $this->blank_to_null($data['institution'] ?? null),
			'participant_count' => $participant_count,
			'attendance_token' => $this->new_token(32),
			'ticket_token' => $this->new_token(48),
			'status' => $status,
		];

		$this->db->trans_start();
		$this->db->insert('event_registrations', $payload);
		$registration_id = (int) $this->db->insert_id();
		$this->insert_answers($registration_id, $fields, $answers);
		$this->db->trans_complete();

		if (! $this->db->trans_status()) {
			throw new RuntimeException('Pendaftaran gagal disimpan.');
		}

		$payload['id'] = $registration_id;
		return $payload;
	}

	public function update_registration_status($id, $status, $admin_note = null, $user_id = null)
	{
		$registration = $this->get_registration((int) $id);
		if (! $registration) {
			throw new RuntimeException('Data peserta tidak ditemukan.');
		}

		$status = in_array($status, ['pending', 'registered', 'approved', 'rejected', 'attended', 'cancelled'], true) ? $status : 'registered';
		$payload = [
			'status' => $status,
			'admin_note' => $this->blank_to_null($admin_note),
		];

		if (in_array($status, ['registered', 'approved'], true)) {
			$payload['approved_by'] = (int) $user_id ?: null;
			$payload['approved_at'] = date('Y-m-d H:i:s');
		}

		if ($status === 'attended') {
			$payload['attended_at'] = date('Y-m-d H:i:s');
			$payload['checked_in_by'] = (int) $user_id ?: null;
		}

		$this->db->where('id', (int) $id)->update('event_registrations', $payload);
		return $registration;
	}

	public function registration_is_open(array $event)
	{
		if (($event['status'] ?? '') !== 'published') {
			return false;
		}

		if (($event['registration_mode'] ?? 'open') === 'none') {
			return false;
		}

		$now = time();
		if (! empty($event['registration_opens_at']) && strtotime($event['registration_opens_at']) > $now) {
			return false;
		}

		if (! empty($event['registration_closes_at']) && strtotime($event['registration_closes_at']) < $now) {
			return false;
		}

		return true;
	}

	private function build_events_query(array $filters = [], $scope_library_id = null)
	{
		$this->db
			->select('e.*, c.name AS category_name, c.color AS category_color, l.name AS library_name, COUNT(er.id) AS registration_count, COALESCE(SUM(er.participant_count), 0) AS participant_total')
			->from('literacy_events e')
			->join('event_categories c', 'c.id = e.event_category_id', 'left')
			->join('libraries l', 'l.id = e.library_id', 'left')
			->join('event_registrations er', "er.event_id = e.id AND er.status NOT IN ('rejected', 'cancelled')", 'left')
			->group_by('e.id');

		$this->apply_admin_event_filters($filters, $scope_library_id);
	}

	private function build_public_events_query(array $filters = [])
	{
		$this->db
			->select('e.*, c.name AS category_name, c.color AS category_color, l.name AS library_name, COUNT(er.id) AS registration_count, COALESCE(SUM(er.participant_count), 0) AS participant_total')
			->from('literacy_events e')
			->join('event_categories c', 'c.id = e.event_category_id', 'left')
			->join('libraries l', 'l.id = e.library_id', 'left')
			->join('event_registrations er', "er.event_id = e.id AND er.status NOT IN ('rejected', 'cancelled')", 'left')
			->where('e.status', 'published')
			->group_by('e.id');

		$this->apply_public_event_filters($filters);
	}

	private function apply_admin_event_filters(array $filters = [], $scope_library_id = null)
	{
		if (! empty($scope_library_id)) {
			$this->db->where('e.library_id', (int) $scope_library_id);
		}

		$q = trim((string) ($filters['q'] ?? ''));
		if ($q !== '') {
			$this->db->group_start()
				->like('e.title', $q)
				->or_like('e.summary', $q)
				->or_like('e.location_name', $q)
				->or_like('e.organizer_name', $q)
				->or_like('c.name', $q)
				->group_end();
		}

		if (! empty($filters['status']) && in_array($filters['status'], ['draft', 'published', 'closed', 'cancelled'], true)) {
			$this->db->where('e.status', $filters['status']);
		}

		if (! empty($filters['category_id'])) {
			$this->db->where('e.event_category_id', (int) $filters['category_id']);
		}

		if (! empty($filters['library_id'])) {
			$this->db->where('e.library_id', (int) $filters['library_id']);
		}
	}

	private function apply_public_event_filters(array $filters = [])
	{
		$q = trim((string) ($filters['q'] ?? ''));
		if ($q !== '') {
			$this->db->group_start()
				->like('e.title', $q)
				->or_like('e.summary', $q)
				->or_like('e.organizer_name', $q)
				->or_like('e.target_audience', $q)
				->or_like('c.name', $q)
				->group_end();
		}

		if (! empty($filters['category_id'])) {
			$this->db->where('e.event_category_id', (int) $filters['category_id']);
		}

		if (($filters['time'] ?? '') === 'upcoming') {
			$this->db->where('(e.starts_at IS NULL OR e.starts_at >= NOW())', null, false);
		} elseif (($filters['time'] ?? '') === 'past') {
			$this->db->where('e.starts_at < NOW()', null, false);
		}
	}

	private function build_registration_query($event_id, array $filters = [])
	{
		$this->db
			->select('er.*, m.member_no, m.identity_number')
			->from('event_registrations er')
			->join('members m', 'm.id = er.member_id', 'left')
			->where('er.event_id', (int) $event_id);

		$q = trim((string) ($filters['q'] ?? ''));
		if ($q !== '') {
			$this->db->group_start()
				->like('er.registration_code', $q)
				->or_like('er.participant_name', $q)
				->or_like('er.participant_phone', $q)
				->or_like('er.participant_email', $q)
				->or_like('er.institution', $q)
				->or_like('m.member_no', $q)
				->or_like('m.identity_number', $q)
				->group_end();
		}

		if (! empty($filters['status']) && in_array($filters['status'], ['pending', 'registered', 'approved', 'rejected', 'attended', 'cancelled'], true)) {
			$this->db->where('er.status', $filters['status']);
		}
	}

	private function event_payload(array $data, $current_id = null)
	{
		$title = $this->required_text($data['title'] ?? '', 'Judul event wajib diisi.', 220);
		$status = in_array(($data['status'] ?? 'draft'), ['draft', 'published', 'closed', 'cancelled'], true) ? $data['status'] : 'draft';
		$registration_mode = in_array(($data['registration_mode'] ?? 'open'), ['none', 'open', 'member_only', 'invite'], true) ? $data['registration_mode'] : 'open';
		$approval_mode = in_array(($data['approval_mode'] ?? 'auto'), ['auto', 'manual'], true) ? $data['approval_mode'] : 'auto';
		$venue_type = in_array(($data['venue_type'] ?? 'onsite'), ['onsite', 'online', 'hybrid'], true) ? $data['venue_type'] : 'onsite';

		return [
			'library_id' => ! empty($data['library_id']) ? (int) $data['library_id'] : null,
			'event_category_id' => ! empty($data['event_category_id']) ? (int) $data['event_category_id'] : null,
			'title' => $title,
			'slug' => $this->unique_slug($title, $current_id),
			'summary' => $this->limit_text($data['summary'] ?? null, 255),
			'description' => $this->blank_to_null($data['description'] ?? null),
			'event_type' => $this->limit_text($data['event_type'] ?? null, 120),
			'organizer_name' => $this->limit_text($data['organizer_name'] ?? null, 180),
			'speaker_name' => $this->limit_text($data['speaker_name'] ?? null, 180),
			'target_audience' => $this->limit_text($data['target_audience'] ?? null, 180),
			'starts_at' => $this->datetime_or_null($data['starts_at'] ?? null),
			'ends_at' => $this->datetime_or_null($data['ends_at'] ?? null),
			'venue_type' => $venue_type,
			'location_name' => $this->limit_text($data['location_name'] ?? null, 220),
			'online_url' => $this->limit_text($data['online_url'] ?? null, 255),
			'latitude' => $this->decimal_or_null($data['latitude'] ?? null),
			'longitude' => $this->decimal_or_null($data['longitude'] ?? null),
			'quota' => $this->positive_int_or_null($data['quota'] ?? null),
			'registration_mode' => $registration_mode,
			'registration_required' => $registration_mode === 'none' ? 0 : 1,
			'approval_mode' => $approval_mode,
			'registration_opens_at' => $this->datetime_or_null($data['registration_opens_at'] ?? null),
			'registration_closes_at' => $this->datetime_or_null($data['registration_closes_at'] ?? null),
			'attendance_enabled' => empty($data['attendance_enabled']) ? 0 : 1,
			'certificate_enabled' => empty($data['certificate_enabled']) ? 0 : 1,
			'status' => $status,
			'published_at' => $status === 'published' ? date('Y-m-d H:i:s') : null,
			'cancelled_reason' => $status === 'cancelled' ? $this->limit_text($data['cancelled_reason'] ?? null, 255) : null,
		];
	}

	private function field_payload($event_id, array $data)
	{
		$label = $this->required_text($data['field_label'] ?? '', 'Label field wajib diisi.', 160);
		$key = trim((string) ($data['field_key'] ?? ''));
		if ($key === '') {
			$key = $this->slugify($label);
		}
		$key = preg_replace('/[^a-z0-9_]/', '_', strtolower($key));
		$key = trim((string) preg_replace('/_+/', '_', $key), '_');
		if ($key === '') {
			$key = 'field_' . time();
		}

		$type = in_array(($data['field_type'] ?? 'text'), ['text', 'textarea', 'select', 'radio', 'checkbox', 'number', 'date', 'email', 'phone'], true) ? $data['field_type'] : 'text';

		return [
			'event_id' => (int) $event_id,
			'field_key' => substr($key, 0, 80),
			'field_label' => $label,
			'field_type' => $type,
			'placeholder' => $this->limit_text($data['placeholder'] ?? null, 180),
			'helper_text' => $this->limit_text($data['helper_text'] ?? null, 255),
			'options_text' => in_array($type, ['select', 'radio', 'checkbox'], true) ? $this->blank_to_null($data['options_text'] ?? null) : null,
			'is_required' => empty($data['is_required']) ? 0 : 1,
			'is_active' => empty($data['is_active']) ? 0 : 1,
			'sort_order' => max(0, (int) ($data['sort_order'] ?? 0)),
		];
	}

	private function validate_dynamic_answers(array $fields, array $answers)
	{
		foreach ($fields as $field) {
			$value = $answers[$field['field_key']] ?? '';
			if (is_array($value)) {
				$value = array_filter(array_map('trim', $value), 'strlen');
			} else {
				$value = trim((string) $value);
			}

			if (! empty($field['is_required']) && (empty($value) && $value !== '0')) {
				throw new RuntimeException($field['field_label'] . ' wajib diisi.');
			}
		}
	}

	private function insert_answers($registration_id, array $fields, array $answers)
	{
		foreach ($fields as $field) {
			$value = $answers[$field['field_key']] ?? null;
			if (is_array($value)) {
				$value = implode(', ', array_filter(array_map('trim', $value), 'strlen'));
			}

			$this->db->insert('event_registration_answers', [
				'registration_id' => (int) $registration_id,
				'field_id' => (int) $field['id'],
				'field_key' => $field['field_key'],
				'field_label' => $field['field_label'],
				'value_text' => $this->blank_to_null($value),
			]);
		}
	}

	private function assert_quota_available(array $event, $new_count)
	{
		$quota = (int) ($event['quota'] ?? 0);
		if ($quota <= 0) {
			return;
		}

		$current = (int) $this->db
			->select('COALESCE(SUM(participant_count), 0) AS total', false)
			->from('event_registrations')
			->where('event_id', (int) $event['id'])
			->where_not_in('status', ['rejected', 'cancelled'])
			->get()
			->row()
			->total;

		if ($current + (int) $new_count > $quota) {
			throw new RuntimeException('Kuota event sudah penuh atau tidak cukup untuk jumlah peserta.');
		}
	}

	private function count_open_events($scope_library_id = null)
	{
		if (! $this->db->table_exists('literacy_events')) {
			return 0;
		}

		$this->db
			->where('status', 'published')
			->where('registration_mode !=', 'none')
			->where('(registration_closes_at IS NULL OR registration_closes_at >= NOW())', null, false);
		if (! empty($scope_library_id)) {
			$this->db->where('library_id', (int) $scope_library_id);
		}

		return (int) $this->db->count_all_results('literacy_events');
	}

	private function count_registrations($scope_library_id = null, array $statuses = [])
	{
		if (! $this->db->table_exists('event_registrations')) {
			return 0;
		}

		$this->db
			->from('event_registrations er')
			->join('literacy_events e', 'e.id = er.event_id');

		if (! empty($scope_library_id)) {
			$this->db->where('e.library_id', (int) $scope_library_id);
		}

		if (! empty($statuses)) {
			$this->db->where_in('er.status', $statuses);
		}

		return (int) $this->db->count_all_results();
	}

	private function count_where($table, array $where = [], $scope_library_id = null)
	{
		if (! $this->db->table_exists($table)) {
			return 0;
		}

		if (! empty($where)) {
			$this->db->where($where);
		}

		if ($table === 'literacy_events' && ! empty($scope_library_id)) {
			$this->db->where('library_id', (int) $scope_library_id);
		}

		return (int) $this->db->count_all_results($table);
	}

	private function next_registration_code()
	{
		$prefix = 'EVT-' . date('Ymd') . '-';
		$count = (int) $this->db
			->like('registration_code', $prefix, 'after')
			->count_all_results('event_registrations');

		return $prefix . str_pad((string) ($count + 1), 4, '0', STR_PAD_LEFT);
	}

	private function unique_slug($title, $current_id = null)
	{
		$base = $this->slugify($title);
		$slug = $base;
		$counter = 2;

		while ($this->slug_exists($slug, $current_id)) {
			$slug = $base . '-' . $counter;
			$counter++;
		}

		return $slug;
	}

	private function unique_category_slug($name)
	{
		$base = $this->slugify($name);
		$slug = $base;
		$counter = 2;

		while ($this->db->from('event_categories')->where('slug', $slug)->count_all_results() > 0) {
			$slug = $base . '-' . $counter;
			$counter++;
		}

		return $slug;
	}

	private function slug_exists($slug, $current_id = null)
	{
		$this->db->from('literacy_events')->where('slug', $slug);
		if (! empty($current_id)) {
			$this->db->where('id !=', (int) $current_id);
		}

		return $this->db->count_all_results() > 0;
	}

	private function slugify($text)
	{
		$text = strtolower(trim((string) $text));
		$text = preg_replace('/[^a-z0-9]+/i', '-', $text);
		$text = trim((string) $text, '-');
		return $text !== '' ? substr($text, 0, 160) : 'event-' . time();
	}

	private function parse_options($text)
	{
		$lines = preg_split('/\r\n|\r|\n/', (string) $text);
		$options = [];
		foreach ($lines as $line) {
			$line = trim($line);
			if ($line !== '') {
				$options[] = $line;
			}
		}

		return $options;
	}

	private function clean_participant_type($type)
	{
		return in_array($type, ['public', 'group'], true) ? $type : 'public';
	}

	private function required_text($value, $message, $limit)
	{
		$value = trim((string) $value);
		if ($value === '') {
			throw new RuntimeException($message);
		}

		return substr($value, 0, (int) $limit);
	}

	private function limit_text($value, $limit)
	{
		$value = trim((string) $value);
		return $value === '' ? null : substr($value, 0, (int) $limit);
	}

	private function blank_to_null($value)
	{
		$value = is_string($value) ? trim($value) : $value;
		return $value === '' ? null : $value;
	}

	private function positive_int_or_null($value)
	{
		$value = trim((string) $value);
		if ($value === '') {
			return null;
		}

		return max(0, (int) $value);
	}

	private function decimal_or_null($value)
	{
		$value = trim((string) $value);
		if ($value === '' || ! is_numeric($value)) {
			return null;
		}

		return number_format((float) $value, 7, '.', '');
	}

	private function datetime_or_null($value)
	{
		$value = trim((string) $value);
		if ($value === '') {
			return null;
		}

		$value = str_replace('T', ' ', $value);
		$timestamp = strtotime($value);
		return $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
	}

	private function new_token($length = 40)
	{
		return strtoupper(substr(hash('sha256', uniqid('', true) . random_int(100000, 999999)), 0, max(16, (int) $length)));
	}
}
