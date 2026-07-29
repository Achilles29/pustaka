<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Roles extends MY_Controller
{
	public function index()
	{
		redirect('rbac/roles');
	}

	public function save_permissions($role_id)
	{
		redirect('rbac/roles/save-permissions/' . (int) $role_id);
	}
}
