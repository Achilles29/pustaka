<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_dashboard extends CI_Controller
{
	public function index()
	{
		$user = (array) $this->session->userdata('auth_user');
		if (empty($user)) {
			redirect('login');
		}

		$roles = (array) $this->session->userdata('user_roles');
		$role_codes = array_map(function ($role) {
			return $role['code'];
		}, $roles);

		if (in_array('SUPERADMIN', $role_codes, true) || in_array('ADMIN', $role_codes, true)) {
			redirect('admin');
		}

		$this->load->model('Catalog_model');
		$this->load->model('Member_model');
		$this->load->model('Reading_point_model');
		$this->load->model('Visit_model');
		$this->load->model('Learn_points_model');
		$this->load->model('Learn_games_model');
		$member = $this->Member_model->get_member_by_auth_user_id((int) ($user['id'] ?? 0));
		if ($member) {
			$this->Visit_model->record_member_dashboard_visit($member, $user);
		}
		$verify_url = null;
		if ($member) {
			$verify_url = base_url('membership/verify/' . (int) $member['id'] . '/' . $this->Member_model->digital_card_token($member));
		}

		$user_id = (int) ($user['id'] ?? 0);

		$this->load->view('user/dashboard', [
			'title' => 'Dashboard Pemustaka',
			'current_user' => $user,
			'member' => $member,
			'verify_url' => $verify_url,
			'catalog_count' => $this->Catalog_model->count_public_books(['availability' => 'with_items']),
			'recent_loans' => $member ? $this->Member_model->get_member_loans((int) $member['id'], 5) : [],
			'recent_visits' => $member ? $this->Member_model->get_member_visits((int) $member['id'], 5) : [],
			'renewal_requests' => $member ? $this->Member_model->get_member_renewal_requests((int) $member['id'], 5) : [],
			'book_requests' => $member ? $this->Catalog_model->get_member_book_requests((int) $member['id'], 5) : [],
			'digital_books' => $this->Catalog_model->get_member_digital_books(6),
			'reading_token' => $member ? $this->Reading_point_model->get_member_active_token((int) $member['id']) : null,
			'reading_tokens' => $member ? $this->Reading_point_model->get_member_tokens((int) $member['id'], 5) : [],
			'event_label' => 'Agenda literasi segera hadir',
			// Belajar widgets
			'learn_total_points' => $user_id ? $this->Learn_points_model->get_user_total_points($user_id) : 0,
			'learn_badges'       => $user_id ? $this->Learn_points_model->get_user_badges($user_id) : [],
			'learn_points_log'   => $user_id ? $this->Learn_points_model->get_user_points_log($user_id, 5) : [],
			'learn_game_history' => $user_id ? $this->Learn_games_model->get_user_game_history($user_id, 5) : [],
		]);
	}

	public function reading_checkin()
	{
		$user = $this->require_member_user();
		$this->load->model('Member_model');
		$this->load->model('Reading_point_model');
		$member = $this->Member_model->get_member_by_auth_user_id((int) ($user['id'] ?? 0));
		if (! $member) {
			redirect('user/dashboard');
		}

		$this->load->view('user/reading_checkin', [
			'title' => 'Check-in Pojok Baca',
			'current_user' => $user,
			'member' => $member,
			'active_token' => $this->Reading_point_model->get_member_active_token((int) $member['id']),
			'points' => $this->Reading_point_model->get_active_points(200),
			'tokens' => $this->Reading_point_model->get_member_tokens((int) $member['id'], 8),
		]);
	}

	public function store_reading_checkin()
	{
		$user = $this->require_member_user();
		$this->load->model('Member_model');
		$this->load->model('Reading_point_model');
		$member = $this->Member_model->get_member_by_auth_user_id((int) ($user['id'] ?? 0));
		if (! $member) {
			redirect('user/dashboard');
		}

		try {
			$result = $this->Reading_point_model->issue_member_checkin_token(
				(int) $member['id'],
				$this->input->post('latitude', true),
				$this->input->post('longitude', true)
			);
			$message = $result['is_new'] ? 'Check-in berhasil. Token baca harian diterbitkan.' : 'Anda masih punya token aktif di titik ini.';
			$this->session->set_flashdata('success', $message);
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
		}

		redirect('user/reading-checkin');
	}

	private function require_member_user()
	{
		$user = (array) $this->session->userdata('auth_user');
		if (empty($user)) {
			redirect('login');
		}

		$roles = (array) $this->session->userdata('user_roles');
		$role_codes = array_map(function ($role) {
			return $role['code'];
		}, $roles);
		if (in_array('SUPERADMIN', $role_codes, true) || in_array('ADMIN', $role_codes, true)) {
			redirect('admin');
		}

		return $user;
	}
}
