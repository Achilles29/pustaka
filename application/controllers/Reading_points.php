<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reading_points extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Reading_point_model');
	}

	public function index()
	{
		$this->require_permission('reading_points.index', 'view');
		$filters = [
			'q' => $this->input->get('q', true),
			'status' => $this->input->get('status', true),
		];
		$per_page = (int) $this->input->get('per_page', true);
		$per_page = in_array($per_page, [10, 25, 50, 100], true) ? $per_page : 25;
		$page = max(1, (int) $this->input->get('page', true));
		$total_rows = $this->Reading_point_model->count_points($filters);
		$total_pages = max(1, (int) ceil($total_rows / $per_page));
		$page = min($page, $total_pages);
		$offset = ($page - 1) * $per_page;

		$this->render('reading_points/index', [
			'title' => 'Pojok Baca Digital',
			'stats' => $this->Reading_point_model->stats(),
			'points' => $this->Reading_point_model->get_points($per_page, $offset, $filters),
			'tokens' => $this->Reading_point_model->get_recent_tokens(),
			'filters' => [
				'q' => $filters['q'],
				'status' => $filters['status'],
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
		$this->require_permission('reading_points.index', 'create');
		$this->render('reading_points/form', [
			'title' => 'Tambah Pojok Baca',
			'action' => 'reading-points/store',
			'point' => null,
			'libraries' => $this->Reading_point_model->library_options(),
		]);
	}

	public function store()
	{
		$this->require_permission('reading_points.index', 'create');
		try {
			$id = $this->Reading_point_model->create_point($this->point_input(), (int) ($this->current_user['id'] ?? 0));
			$this->audit_event('reading_points.create', 'reading_points', $id, null, $this->point_input());
			$this->session->set_flashdata('success', 'Titik pojok baca berhasil dibuat.');
			redirect('reading-points');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
			redirect('reading-points/create');
		}
	}

	public function edit($id)
	{
		$this->require_permission('reading_points.index', 'edit');
		$point = $this->Reading_point_model->get_point((int) $id);
		if (! $point) {
			show_404();
			return;
		}
		$this->render('reading_points/form', [
			'title' => 'Edit Pojok Baca',
			'action' => 'reading-points/update/' . (int) $id,
			'point' => $point,
			'libraries' => $this->Reading_point_model->library_options(),
		]);
	}

	public function update($id)
	{
		$this->require_permission('reading_points.index', 'edit');
		$before = $this->Reading_point_model->get_point((int) $id);
		if (! $before) {
			show_404();
			return;
		}

		try {
			$this->Reading_point_model->update_point((int) $id, $this->point_input());
			$this->audit_event('reading_points.update', 'reading_points', (int) $id, $before, $this->point_input());
			$this->session->set_flashdata('success', 'Titik pojok baca berhasil diperbarui.');
			redirect('reading-points');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
			redirect('reading-points/edit/' . (int) $id);
		}
	}

	public function tokens()
	{
		$this->require_permission('reading_tokens.index', 'view');
		$filters = [
			'q' => $this->input->get('q', true),
			'status' => $this->input->get('status', true),
		];
		$per_page = (int) $this->input->get('per_page', true);
		$per_page = in_array($per_page, [10, 25, 50, 100], true) ? $per_page : 25;
		$page = max(1, (int) $this->input->get('page', true));
		$total_rows = $this->Reading_point_model->count_tokens($filters);
		$total_pages = max(1, (int) ceil($total_rows / $per_page));
		$page = min($page, $total_pages);
		$offset = ($page - 1) * $per_page;

		$this->render('reading_points/tokens', [
			'title' => 'Monitoring Token Baca',
			'stats' => $this->Reading_point_model->stats(),
			'tokens' => $this->Reading_point_model->get_tokens($filters, $per_page, $offset),
			'filters' => [
				'q' => $filters['q'],
				'status' => $filters['status'],
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
			'pending_requests' => $this->Reading_point_model->get_pending_token_requests(20),
			'token_settings' => $this->Reading_point_model->get_token_settings(),
		]);
	}

	public function token_settings()
	{
		$this->require_permission('reading_tokens.settings', 'view');
		$this->render('reading_points/token_settings', [
			'title' => 'Pengaturan Token Baca',
			'settings' => $this->Reading_point_model->get_token_settings(),
		]);
	}

	public function update_token_settings()
	{
		$this->require_permission('reading_tokens.settings', 'edit');
		try {
			$before = $this->Reading_point_model->get_token_settings();
			$after = $this->Reading_point_model->update_token_settings([
				'library_checkin_quota' => $this->input->post('library_checkin_quota', true),
				'library_checkin_valid_days' => $this->input->post('library_checkin_valid_days', true),
				'library_checkin_daily_limit' => $this->input->post('library_checkin_daily_limit', true),
				'request_default_quota' => $this->input->post('request_default_quota', true),
				'request_valid_days' => $this->input->post('request_valid_days', true),
				'outside_session_charge' => $this->input->post('outside_session_charge', true),
			], (int) ($this->current_user['id'] ?? 0));
			$this->audit_event('reading_tokens.settings.update', 'reading_token_settings', null, $before, $after);
			$this->session->set_flashdata('success', 'Pengaturan token berhasil disimpan. Pengaturan berlaku untuk token baru berikutnya.');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
		}
		redirect('reading-points/token-settings');
	}

	public function revoke_token($id)
	{
		$this->require_permission('reading_tokens.index', 'approve');
		$before = $this->Reading_point_model->get_token((int) $id);
		if (! $before) {
			show_404();
			return;
		}

		$this->Reading_point_model->revoke_token((int) $id, $this->input->post('reason', true), (int) ($this->current_user['id'] ?? 0));
		$this->audit_event('reading_tokens.revoke', 'reading_tokens', (int) $id, $before, [
			'status' => 'revoked',
			'reason' => $this->input->post('reason', true),
		]);
		$this->session->set_flashdata('success', 'Token baca berhasil dicabut.');
		redirect('reading-points/tokens');
	}

	public function approve_token_request($id)
	{
		$this->require_permission('reading_tokens.index', 'approve');
		try {
			$token_id = $this->Reading_point_model->approve_token_request((int) $id, (int) ($this->current_user['id'] ?? 0), $this->input->post('review_note', true));
			$this->audit_event('reading_tokens.request_approve', 'reading_token_requests', (int) $id, null, ['reading_token_id' => $token_id]);
			$this->session->set_flashdata('success', 'Permohonan disetujui dan token baca diterbitkan.');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
		}
		redirect('reading-points/tokens');
	}

	public function reject_token_request($id)
	{
		$this->require_permission('reading_tokens.index', 'approve');
		try {
			$this->Reading_point_model->reject_token_request((int) $id, (int) ($this->current_user['id'] ?? 0), $this->input->post('review_note', true));
			$this->audit_event('reading_tokens.request_reject', 'reading_token_requests', (int) $id, null, []);
			$this->session->set_flashdata('success', 'Permohonan token ditolak.');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
		}
		redirect('reading-points/tokens');
	}

	private function point_input()
	{
		return [
			'library_id' => $this->input->post('library_id', true),
			'partner_name' => $this->input->post('partner_name', true),
			'name' => $this->input->post('name', true),
			'address' => $this->input->post('address', true),
			'latitude' => $this->input->post('latitude', true),
			'longitude' => $this->input->post('longitude', true),
			'radius_meters' => $this->input->post('radius_meters', true),
			'daily_quota' => $this->input->post('daily_quota', true),
			'quota_unit' => $this->input->post('quota_unit', true),
			'opening_hours' => $this->input->post('opening_hours', true),
			'status' => $this->input->post('status', true),
		];
	}
}
