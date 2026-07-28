<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Role_model extends CI_Model
{
	public function get_roles()
	{
		return $this->db
			->from('auth_role')
			->order_by('level', 'ASC')
			->order_by('name', 'ASC')
			->get()
			->result_array();
	}

	public function get_role($id)
	{
		return $this->db
			->from('auth_role')
			->where('id', (int) $id)
			->get()
			->row_array();
	}

	public function get_pages()
	{
		return $this->db
			->from('sys_page')
			->where('is_active', 1)
			->order_by('module', 'ASC')
			->order_by('title', 'ASC')
			->get()
			->result_array();
	}

	public function get_permissions($role_id)
	{
		$rows = $this->db
			->select('page_id, can_view, can_create, can_edit, can_delete, can_export, can_approve')
			->from('auth_role_permission')
			->where('role_id', (int) $role_id)
			->get()
			->result_array();

		$result = [];
		foreach ($rows as $row) {
			$result[(int) $row['page_id']] = $row;
		}

		return $result;
	}

	public function save_permissions($role_id, array $page_ids, array $posted_permissions)
	{
		$role_id = (int) $role_id;
		$actions = ['view', 'create', 'edit', 'delete', 'export', 'approve'];

		$this->db->trans_start();
		foreach ($page_ids as $page_id) {
			$page_id = (int) $page_id;
			$row = [
				'role_id' => $role_id,
				'page_id' => $page_id,
			];

			foreach ($actions as $action) {
				$row['can_' . $action] = ! empty($posted_permissions[$page_id][$action]) ? 1 : 0;
			}

			$this->db->replace('auth_role_permission', $row);
		}
		$this->db->trans_complete();

		return $this->db->trans_status();
	}
}
