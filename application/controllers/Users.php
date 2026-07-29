<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends MY_Controller
{
	public function index()
	{
		redirect('rbac/users');
	}

	public function store()
	{
		redirect('rbac/users');
	}

	public function update_roles($user_id)
	{
		redirect('rbac/users');
	}

	public function toggle($user_id)
	{
		redirect('rbac/users');
	}
}
