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

		$source_db = $this->load->database('inlislite', TRUE);
		$this->load->view('user/dashboard', [
			'title' => 'Dashboard Pemustaka',
			'current_user' => $user,
			'catalog_count' => $source_db->table_exists('catalogs') ? $source_db->count_all('catalogs') : 0,
			'event_label' => 'Agenda literasi segera hadir',
		]);
	}
}
