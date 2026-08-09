<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Events extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Event_model');
	}

	public function index()
	{
		$this->require_permission('events.index', 'view');

		$filters = [
			'q' => $this->input->get('q', true),
			'status' => $this->input->get('status', true),
			'category_id' => $this->input->get('category_id', true),
			'library_id' => $this->input->get('library_id', true),
		];
		$per_page = (int) $this->input->get('per_page', true);
		$per_page = in_array($per_page, [10, 25, 50, 100], true) ? $per_page : 25;
		$page = max(1, (int) $this->input->get('page', true));
		$total_rows = $this->Event_model->count_events($filters, $this->current_library_scope_id());
		$total_pages = max(1, (int) ceil($total_rows / $per_page));
		$page = min($page, $total_pages);
		$offset = ($page - 1) * $per_page;

		$this->render('events/index', [
			'title' => 'Event Literasi',
			'stats' => $this->Event_model->stats($this->current_library_scope_id()),
			'events' => $this->Event_model->get_events($filters, $per_page, $offset, $this->current_library_scope_id()),
			'categories' => $this->Event_model->category_options(),
			'libraries' => $this->Event_model->library_options($this->current_library_scope_id()),
			'filters' => [
				'q' => $filters['q'],
				'status' => $filters['status'],
				'category_id' => $filters['category_id'],
				'library_id' => $filters['library_id'],
				'per_page' => $per_page,
				'page' => $page,
			],
			'pagination' => [
				'total_rows' => $total_rows,
				'total_pages' => $total_pages,
				'page' => $page,
				'per_page' => $per_page,
				'offset' => $offset,
			],
		]);
	}

	public function create()
	{
		$this->require_permission('events.index', 'create');
		$this->render('events/form', [
			'title' => 'Tambah Event Literasi',
			'action' => 'events/store',
			'event' => null,
			'categories' => $this->Event_model->category_options(),
			'libraries' => $this->Event_model->library_options($this->current_library_scope_id()),
		]);
	}

	public function qr()
	{
		$this->require_permission('events.qr', 'view');

		$filters = [
			'q' => $this->input->get('q', true),
			'status' => $this->input->get('status', true),
			'category_id' => $this->input->get('category_id', true),
			'library_id' => $this->input->get('library_id', true),
		];
		$events = $this->Event_model->get_events($filters, 100, 0, $this->current_library_scope_id());
		$selected_id = (int) $this->input->get('event_id', true);
		if ($selected_id <= 0 && ! empty($events)) {
			$selected_id = (int) $events[0]['id'];
		}

		$selected_event = $selected_id > 0
			? $this->Event_model->get_event($selected_id, $this->current_library_scope_id())
			: null;

		$public_url = $selected_event
			? base_url('agenda/detail/' . (int) $selected_event['id'] . '?from=qr-event#daftar-event')
			: '';

		$this->render('events/qr', [
			'title' => 'QR Event & Pendaftaran',
			'events' => $events,
			'categories' => $this->Event_model->category_options(),
			'libraries' => $this->Event_model->library_options($this->current_library_scope_id()),
			'filters' => [
				'q' => $filters['q'],
				'status' => $filters['status'],
				'category_id' => $filters['category_id'],
				'library_id' => $filters['library_id'],
				'event_id' => $selected_id,
			],
			'selected_event' => $selected_event,
			'public_url' => $public_url,
			'is_public_ready' => $selected_event ? $this->Event_model->registration_is_open($selected_event) : false,
		]);
	}

	public function store()
	{
		$this->require_permission('events.index', 'create');
		try {
			$poster_path = $this->upload_poster();
			$event_id = $this->Event_model->create_event($this->event_input(), (int) ($this->current_user['id'] ?? 0), $poster_path);
			$this->audit_event('events.create', 'literacy_events', $event_id, null, $this->event_input());
			$this->session->set_flashdata('success', 'Event literasi berhasil dibuat. Sekarang atur formulir pendaftarannya.');
			redirect('events/detail/' . $event_id);
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
			redirect('events/create');
		}
	}

	public function store_category()
	{
		$this->require_permission('events.index', 'create');
		try {
			$category_id = $this->Event_model->create_category([
				'name' => $this->input->post('name', true),
				'description' => $this->input->post('description', true),
				'color' => $this->input->post('color', true),
				'sort_order' => $this->input->post('sort_order', true),
			]);
			$this->audit_event('events.category_create', 'event_categories', $category_id, null, $this->input->post(null, true));
			$this->session->set_flashdata('success', 'Kategori event berhasil ditambahkan.');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
		}

		$return_to = trim((string) $this->input->post('return_to', true));
		redirect($this->safe_return_path($return_to, 'events/create'));
	}

	public function edit($id)
	{
		$this->require_permission('events.index', 'edit');
		$event = $this->Event_model->get_event((int) $id, $this->current_library_scope_id());
		if (! $event) {
			show_404();
			return;
		}

		$this->render('events/form', [
			'title' => 'Edit Event Literasi',
			'action' => 'events/update/' . (int) $id,
			'event' => $event,
			'categories' => $this->Event_model->category_options(),
			'libraries' => $this->Event_model->library_options($this->current_library_scope_id()),
		]);
	}

	public function update($id)
	{
		$this->require_permission('events.index', 'edit');
		$before = $this->Event_model->get_event((int) $id, $this->current_library_scope_id());
		if (! $before) {
			show_404();
			return;
		}

		try {
			$poster_path = $this->upload_poster();
			$this->Event_model->update_event((int) $id, $this->event_input(), $poster_path);
			$this->audit_event('events.update', 'literacy_events', (int) $id, $before, $this->event_input());
			$this->session->set_flashdata('success', 'Event literasi berhasil diperbarui.');
			redirect('events/detail/' . (int) $id);
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
			redirect('events/edit/' . (int) $id);
		}
	}

	public function detail($id)
	{
		$this->require_permission('events.index', 'view');
		$event = $this->Event_model->get_event((int) $id, $this->current_library_scope_id());
		if (! $event) {
			show_404();
			return;
		}

		$registration_filters = [
			'q' => $this->input->get('q', true),
			'status' => $this->input->get('registration_status', true),
		];

		$this->render('events/detail', [
			'title' => 'Detail Event Literasi',
			'event' => $event,
			'fields' => $this->Event_model->get_form_fields((int) $id),
			'registrations' => $this->Event_model->get_registrations((int) $id, $registration_filters, 80),
			'registration_filters' => $registration_filters,
			'can_edit' => $this->can('events.index', 'edit'),
			'can_approve' => $this->can('events.index', 'approve'),
			'can_delete' => $this->can('events.index', 'delete'),
		]);
	}

	public function status($id)
	{
		$this->require_permission('events.index', 'edit');
		$event = $this->Event_model->get_event((int) $id, $this->current_library_scope_id());
		if (! $event) {
			show_404();
			return;
		}

		$status = (string) $this->input->post('status', true);
		$this->Event_model->update_status((int) $id, $status, $this->input->post('cancelled_reason', true));
		$this->audit_event('events.status', 'literacy_events', (int) $id, $event, ['status' => $status]);
		$this->session->set_flashdata('success', 'Status event diperbarui.');
		redirect('events/detail/' . (int) $id);
	}

	public function store_field($event_id)
	{
		$this->require_permission('events.index', 'edit');
		$event = $this->Event_model->get_event((int) $event_id, $this->current_library_scope_id());
		if (! $event) {
			show_404();
			return;
		}

		try {
			$field_id = $this->Event_model->create_form_field((int) $event_id, $this->field_input());
			$this->audit_event('events.field_create', 'event_form_fields', $field_id, null, $this->field_input());
			$this->session->set_flashdata('success', 'Field formulir ditambahkan.');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
		}

		redirect('events/detail/' . (int) $event_id . '#form-pendaftaran');
	}

	public function update_field($event_id, $field_id)
	{
		$this->require_permission('events.index', 'edit');
		$field = $this->Event_model->get_form_field((int) $field_id, (int) $event_id);
		if (! $field) {
			show_404();
			return;
		}

		try {
			$this->Event_model->update_form_field((int) $field_id, (int) $event_id, $this->field_input());
			$this->audit_event('events.field_update', 'event_form_fields', (int) $field_id, $field, $this->field_input());
			$this->session->set_flashdata('success', 'Field formulir diperbarui.');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
		}

		redirect('events/detail/' . (int) $event_id . '#form-pendaftaran');
	}

	public function toggle_field($event_id, $field_id)
	{
		$this->require_permission('events.index', 'edit');
		$this->Event_model->toggle_form_field((int) $field_id, (int) $event_id);
		$this->audit_event('events.field_toggle', 'event_form_fields', (int) $field_id, null, null);
		$this->session->set_flashdata('success', 'Status field formulir diperbarui.');
		redirect('events/detail/' . (int) $event_id . '#form-pendaftaran');
	}

	public function update_registration($event_id, $registration_id)
	{
		$this->require_permission('events.index', 'approve');
		$event = $this->Event_model->get_event((int) $event_id, $this->current_library_scope_id());
		if (! $event) {
			show_404();
			return;
		}

		try {
			$before = $this->Event_model->update_registration_status(
				(int) $registration_id,
				(string) $this->input->post('status', true),
				$this->input->post('admin_note', true),
				(int) ($this->current_user['id'] ?? 0)
			);
			$this->audit_event('events.registration_update', 'event_registrations', (int) $registration_id, $before, $this->input->post(null, true));
			$this->session->set_flashdata('success', 'Status peserta diperbarui.');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
		}

		redirect('events/detail/' . (int) $event_id . '#peserta');
	}

	public function checkin($token)
	{
		$this->require_permission('events.index', 'approve');
		$registration = $this->Event_model->get_registration_by_attendance_token((string) $token);
		if (! $registration) {
			show_404();
			return;
		}

		$this->Event_model->update_registration_status(
			(int) $registration['id'],
			'attended',
			'Check-in via QR attendance.',
			(int) ($this->current_user['id'] ?? 0)
		);
		$this->audit_event('events.registration_checkin', 'event_registrations', (int) $registration['id'], $registration, ['status' => 'attended']);
		$this->session->set_flashdata('success', 'Check-in peserta berhasil: ' . $registration['participant_name']);
		redirect('events/detail/' . (int) $registration['event_id'] . '#peserta');
	}

	private function event_input()
	{
		$library_id = $this->input->post('library_id', true);
		if ($this->current_library_scope_id()) {
			$library_id = $this->current_library_scope_id();
		}

		return [
			'library_id' => $library_id,
			'event_category_id' => $this->input->post('event_category_id', true),
			'title' => $this->input->post('title', true),
			'summary' => $this->input->post('summary', true),
			'description' => $this->input->post('description', true),
			'event_type' => $this->input->post('event_type', true),
			'organizer_name' => $this->input->post('organizer_name', true),
			'speaker_name' => $this->input->post('speaker_name', true),
			'target_audience' => $this->input->post('target_audience', true),
			'starts_at' => $this->combine_datetime_post('starts_date', 'starts_time'),
			'ends_at' => $this->combine_datetime_post('ends_date', 'ends_time'),
			'venue_type' => $this->input->post('venue_type', true),
			'location_name' => $this->input->post('location_name', true),
			'online_url' => $this->input->post('online_url', true),
			'latitude' => $this->input->post('latitude', true),
			'longitude' => $this->input->post('longitude', true),
			'quota' => $this->input->post('quota', true),
			'registration_mode' => $this->input->post('registration_mode', true),
			'approval_mode' => $this->input->post('approval_mode', true),
			'registration_opens_at' => $this->combine_datetime_post('registration_opens_date', 'registration_opens_time'),
			'registration_closes_at' => $this->combine_datetime_post('registration_closes_date', 'registration_closes_time'),
			'attendance_enabled' => $this->input->post('attendance_enabled') ? 1 : 0,
			'certificate_enabled' => $this->input->post('certificate_enabled') ? 1 : 0,
			'status' => $this->input->post('status', true),
			'cancelled_reason' => $this->input->post('cancelled_reason', true),
		];
	}

	private function field_input()
	{
		return [
			'field_key' => $this->input->post('field_key', true),
			'field_label' => $this->input->post('field_label', true),
			'field_type' => $this->input->post('field_type', true),
			'placeholder' => $this->input->post('placeholder', true),
			'helper_text' => $this->input->post('helper_text', true),
			'options_text' => $this->input->post('options_text', true),
			'is_required' => $this->input->post('is_required') ? 1 : 0,
			'is_active' => $this->input->post('is_active') ? 1 : 0,
			'sort_order' => $this->input->post('sort_order', true),
		];
	}

	private function upload_poster()
	{
		if (empty($_FILES['poster']['name'])) {
			return null;
		}

		$dir = FCPATH . 'assets/uploads/events/posters/' . date('Y/m') . '/';
		if (! is_dir($dir)) {
			mkdir($dir, 0755, true);
		}

		$this->load->library('upload');
		$this->upload->initialize([
			'upload_path' => $dir,
			'allowed_types' => 'jpg|jpeg|png|webp',
			'max_size' => 4096,
			'encrypt_name' => true,
		]);

		if (! $this->upload->do_upload('poster')) {
			throw new RuntimeException(strip_tags($this->upload->display_errors('', '')));
		}

		$file = $this->upload->data();
		return 'assets/uploads/events/posters/' . date('Y/m') . '/' . $file['file_name'];
	}

	private function combine_datetime_post($date_key, $time_key)
	{
		$date = trim((string) $this->input->post($date_key, true));
		$time = trim((string) $this->input->post($time_key, true));
		if ($date === '') {
			return null;
		}

		if ($time === '') {
			$time = '00:00';
		}

		return $date . ' ' . $time;
	}

	private function safe_return_path($path, $fallback)
	{
		$path = trim((string) $path);
		if ($path === '' || strpos($path, '://') !== false || strpos($path, '//') === 0) {
			return $fallback;
		}

		return ltrim($path, '/');
	}
}
