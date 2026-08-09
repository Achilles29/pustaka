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
		$this->load->model('Event_model');
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
		$member_id = $member ? (int) $member['id'] : null;
		$dashboard_events = $this->Event_model->get_dashboard_events($member_id, 4);
		$upcoming_event_count = $this->Event_model->count_public_events(['time' => 'upcoming']);
		$highlight_books = $this->Catalog_model->get_active_book_highlights(8);
		$highlight_categories = $this->Catalog_model->get_active_category_highlights(6);
		if (empty($highlight_categories)) {
			$highlight_categories = $this->Catalog_model->get_top_content_categories(6);
		}

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
			'highlight_books' => $highlight_books,
			'highlight_categories' => $highlight_categories,
			'reading_token' => $member ? $this->Reading_point_model->get_member_active_token((int) $member['id']) : null,
			'reading_tokens' => $member ? $this->Reading_point_model->get_member_tokens((int) $member['id'], 5) : [],
			'event_label' => $upcoming_event_count > 0 ? $upcoming_event_count . ' agenda literasi aktif' : 'Belum ada agenda aktif',
			'dashboard_events' => $dashboard_events,
			'upcoming_event_count' => $upcoming_event_count,
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

	public function account()
	{
		$user = $this->require_member_user();
		$this->load->model('Member_model');
		$this->load->model('Auth_model');

		$member = $this->Member_model->get_member_by_auth_user_id((int) ($user['id'] ?? 0));
		$account = $this->Auth_model->get_user_with_password((int) ($user['id'] ?? 0));
		if (! $account) {
			$this->session->sess_destroy();
			redirect('login');
		}
		unset($account['password_hash']);

		$this->load->view('user/account', [
			'title' => 'Pengaturan Akun',
			'current_user' => $user,
			'account' => $account,
			'member' => $member,
		]);
	}

	public function update_username()
	{
		$user = $this->require_member_user();
		$this->load->model('Auth_model');

		$username = trim((string) $this->input->post('username', true));
		$current_password = (string) $this->input->post('current_password', false);
		$account = $this->Auth_model->get_user_with_password((int) ($user['id'] ?? 0));

		if (! $account || ! password_verify($current_password, (string) $account['password_hash'])) {
			$this->session->set_flashdata('error', 'Password saat ini tidak sesuai.');
			redirect('user/account');
		}

		if (! $this->valid_username($username)) {
			$this->session->set_flashdata('error', 'Username hanya boleh huruf, angka, titik, underscore, atau strip. Panjang 5-80 karakter.');
			redirect('user/account');
		}

		if ($this->Auth_model->username_exists($username, (int) $account['id'])) {
			$this->session->set_flashdata('error', 'Username sudah digunakan akun lain.');
			redirect('user/account');
		}

		$this->Auth_model->update_username((int) $account['id'], $username);
		$this->refresh_session_user(['username' => $username]);
		$this->session->set_flashdata('success', 'Username login berhasil diperbarui.');
		redirect('user/account');
	}

	public function update_password()
	{
		$user = $this->require_member_user();
		$this->load->model('Auth_model');

		$current_password = (string) $this->input->post('current_password', false);
		$new_password = (string) $this->input->post('new_password', false);
		$confirm_password = (string) $this->input->post('confirm_password', false);
		$account = $this->Auth_model->get_user_with_password((int) ($user['id'] ?? 0));

		if (! $account || ! password_verify($current_password, (string) $account['password_hash'])) {
			$this->session->set_flashdata('error', 'Password saat ini tidak sesuai.');
			redirect('user/account');
		}

		if (strlen($new_password) < 8 || strlen($new_password) > 72) {
			$this->session->set_flashdata('error', 'Password baru minimal 8 karakter dan maksimal 72 karakter.');
			redirect('user/account');
		}

		if ($new_password !== $confirm_password) {
			$this->session->set_flashdata('error', 'Konfirmasi password baru tidak sama.');
			redirect('user/account');
		}

		$this->Auth_model->update_password((int) $account['id'], $new_password);
		$this->refresh_session_user(['force_password_change' => 0]);
		$this->Auth_model->write_event('password_changed', (int) $account['id'], $account['username']);
		$this->session->set_flashdata('success', 'Password login berhasil diperbarui.');
		redirect('user/account');
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

	private function valid_username($username)
	{
		$username = trim((string) $username);
		return strlen($username) >= 5
			&& strlen($username) <= 80
			&& (bool) preg_match('/^[A-Za-z0-9._-]+$/', $username);
	}

	private function refresh_session_user(array $patch)
	{
		$user = (array) $this->session->userdata('auth_user');
		$this->session->set_userdata('auth_user', array_merge($user, $patch));
	}
}
