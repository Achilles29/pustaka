<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Roles extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Role_model');
	}

	public function index()
	{
		$this->require_permission('auth.roles.index', 'view');

		$selected_role_id = (int) $this->input->get('role_id', true);
		$roles = $this->Role_model->get_roles();
		if ($selected_role_id <= 0 && ! empty($roles)) {
			$selected_role_id = (int) $roles[0]['id'];
		}

		$this->render('roles/index', [
			'title' => 'Role & Hak Akses',
			'roles' => $roles,
			'selected_role' => $this->Role_model->get_role($selected_role_id),
			'pages' => $this->Role_model->get_pages(),
			'permissions' => $this->Role_model->get_permissions($selected_role_id),
		]);
	}

	public function save_permissions($role_id)
	{
		$this->require_permission('auth.roles.index', 'edit');

		$role = $this->Role_model->get_role((int) $role_id);
		if (! $role) {
			show_404();
			return;
		}

		$pages = $this->Role_model->get_pages();
		$page_ids = array_map(function ($page) {
			return (int) $page['id'];
		}, $pages);

		$this->Role_model->save_permissions(
			(int) $role_id,
			$page_ids,
			(array) $this->input->post('permissions')
		);

		$this->session->set_flashdata('success', 'Hak akses role berhasil disimpan.');
		redirect('roles?role_id=' . (int) $role_id);
	}
}
