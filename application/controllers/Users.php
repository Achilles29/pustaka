<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('User_model');
	}

	public function index()
	{
		$this->require_permission('auth.users.index', 'view');

		$this->render('users/index', [
			'title' => 'Manajemen User',
			'users' => $this->User_model->get_users(),
			'roles' => $this->User_model->get_roles(),
		]);
	}

	public function store()
	{
		$this->require_permission('auth.users.index', 'create');

		$username = trim((string) $this->input->post('username', true));
		$full_name = trim((string) $this->input->post('full_name', true));
		$password = (string) $this->input->post('password', false);
		$role_ids = (array) $this->input->post('role_ids');

		if ($username === '' || $full_name === '' || strlen($password) < 6 || empty($role_ids)) {
			$this->session->set_flashdata('error', 'Username, nama, password minimal 6 karakter, dan role wajib diisi.');
			redirect('users');
		}

		if ($this->User_model->username_exists($username) || $this->User_model->email_exists($this->input->post('email', true))) {
			$this->session->set_flashdata('error', 'Username atau email sudah digunakan.');
			redirect('users');
		}

		$this->User_model->create_user([
			'username' => $username,
			'email' => trim((string) $this->input->post('email', true)),
			'full_name' => $full_name,
			'password' => $password,
		], $role_ids);

		$this->session->set_flashdata('success', 'User baru berhasil dibuat.');
		redirect('users');
	}

	public function update_roles($user_id)
	{
		$this->require_permission('auth.users.index', 'edit');

		$this->User_model->replace_roles((int) $user_id, (array) $this->input->post('role_ids'));
		$this->session->set_flashdata('success', 'Role user berhasil diperbarui.');
		redirect('users');
	}

	public function toggle($user_id)
	{
		$this->require_permission('auth.users.index', 'edit');

		$current_id = (int) ($this->current_user['id'] ?? 0);
		if ((int) $user_id === $current_id) {
			$this->session->set_flashdata('error', 'Akun yang sedang dipakai tidak bisa dinonaktifkan dari halaman ini.');
			redirect('users');
		}

		$status = (string) $this->input->post('status', true);
		$this->User_model->set_status((int) $user_id, $status === 'active' ? 'inactive' : 'active');
		$this->session->set_flashdata('success', 'Status user berhasil diperbarui.');
		redirect('users');
	}
}
