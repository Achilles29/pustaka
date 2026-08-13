<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Panduan operasional internal untuk admin/petugas. */
class Guide extends CI_Controller
{
	public function index()
	{
		$user = (array) $this->session->userdata('auth_user');
		if (empty($user['id'])) {
			$this->session->set_flashdata('redirect_after_login', 'panduan');
			redirect('login');
			return;
		}

		$roles = array_map(function ($role) {
			return $role['code'] ?? '';
		}, (array) $this->session->userdata('user_roles'));
		$is_admin = in_array('SUPERADMIN', $roles, true) || in_array('ADMIN', $roles, true);
		if (! $is_admin) {
			show_error('Panduan operasional hanya dapat diakses admin atau petugas.', 403, 'Akses Ditolak');
			return;
		}

		$this->load->view('guide/index', [
			'title' => 'Panduan Pustaka Digital Rembang',
			'current_user' => $user,
			'is_logged_in' => ! empty($user['id']),
			'is_admin' => $is_admin,
		]);
	}
}
