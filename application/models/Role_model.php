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

	public function code_exists($code, $exclude_id = null)
	{
		$this->db->from('auth_role')->where('code', $code);
		if ($exclude_id !== null) {
			$this->db->where('id !=', (int) $exclude_id);
		}

		return $this->db->count_all_results() > 0;
	}

	public function create_role(array $data)
	{
		$this->db->insert('auth_role', $this->clean_role_payload($data));
		return (int) $this->db->insert_id();
	}

	public function update_role($id, array $data)
	{
		$this->db
			->where('id', (int) $id)
			->update('auth_role', $this->clean_role_payload($data, true));
	}

	public function toggle_role($id)
	{
		$role = $this->get_role($id);
		if (! $role || (int) $role['is_system'] === 1) {
			return false;
		}

		$this->db
			->where('id', (int) $id)
			->update('auth_role', [
				'is_active' => (int) $role['is_active'] === 1 ? 0 : 1,
				'updated_at' => date('Y-m-d H:i:s'),
			]);

		return $role;
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

	private function clean_role_payload(array $data, $is_update = false)
	{
		$payload = [
			'code' => strtoupper(trim((string) $data['code'])),
			'name' => trim((string) $data['name']),
			'description' => trim((string) ($data['description'] ?? '')) ?: null,
			'level' => max(1, (int) ($data['level'] ?? 100)),
			'scope_type' => in_array(($data['scope_type'] ?? 'self'), ['global', 'library', 'self'], true) ? $data['scope_type'] : 'self',
			'is_active' => empty($data['is_active']) ? 0 : 1,
		];

		if ($is_update) {
			$payload['updated_at'] = date('Y-m-d H:i:s');
		}

		return $payload;
	}
}
