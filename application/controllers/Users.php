<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends MY_Controller
{
	public function index()
	{
		redirect('rbac/admins');
	}

	public function store()
	{
		redirect('rbac/admins');
	}

	public function update_roles($user_id)
	{
		redirect('rbac/admins');
	}

	public function toggle($user_id)
	{
		redirect('rbac/admins');
	}
}
