<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Auth_model');
		$this->load->library('form_validation');
	}

	public function index()
	{
		if ($this->session->userdata('auth_user')) {
			redirect($this->post_login_target());
		}

		$this->load->view('auth/login', [
			'title' => 'Login Pustaka Digital Rembang',
			'error_msg' => $this->session->flashdata('login_error'),
		]);
	}

	public function do_login()
	{
		$this->form_validation->set_rules('identifier', 'Username/Email', 'required|trim|min_length[3]|max_length[180]');
		$this->form_validation->set_rules('password', 'Password', 'required|min_length[6]|max_length[72]');

		if ($this->form_validation->run() === false) {
			$this->session->set_flashdata('login_error', validation_errors('<div>', '</div>'));
			redirect('login');
		}

		$identifier = $this->input->post('identifier', true);
		$password = $this->input->post('password', false);
		$user = $this->Auth_model->attempt_login($identifier, $password);

		if (! $user) {
			$this->Auth_model->write_event('login_failed', null, $identifier);
			$this->session->set_flashdata('login_error', 'Username/email atau password tidak sesuai.');
			redirect('login');
		}

		$roles = $this->Auth_model->load_roles((int) $user['id']);
		$permissions = $this->Auth_model->load_permissions((int) $user['id']);
		$user['is_superadmin'] = isset($permissions['__superadmin__']);

		$this->session->sess_regenerate(true);
		$this->session->set_userdata([
			'auth_user' => $user,
			'user_roles' => $roles,
			'user_perms' => $permissions,
		]);

		$this->Auth_model->write_event('login_success', (int) $user['id'], $identifier);

		$redirect_to = (string) $this->session->flashdata('redirect_after_login');
		$target = $this->post_login_target($roles);
		if ($redirect_to !== '' && $this->is_safe_redirect($redirect_to)) {
			redirect($redirect_to);
		}

		redirect($target);
	}

	public function logout()
	{
		$user = (array) $this->session->userdata('auth_user');
		if (! empty($user['id'])) {
			$this->Auth_model->write_event('logout', (int) $user['id'], $user['username']);
		}

		$this->session->sess_destroy();
		redirect('login');
	}

	private function post_login_target(array $roles = null)
	{
		if ($roles === null) {
			$roles = (array) $this->session->userdata('user_roles');
		}

		$role_codes = array_map(function ($role) {
			return $role['code'];
		}, $roles);

		if (in_array('SUPERADMIN', $role_codes, true) || in_array('ADMIN', $role_codes, true)) {
			return 'admin';
		}

		return 'user/dashboard';
	}

	private function is_safe_redirect($path)
	{
		$path = trim((string) $path);
		if ($path === '' || strpos($path, '://') !== false || strpos($path, '//') === 0) {
			return false;
		}

		if ($path === 'login' || strpos($path, 'auth/') === 0 || $path === '/') {
			return false;
		}

		return true;
	}
}
