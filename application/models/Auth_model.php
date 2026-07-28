<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_model extends CI_Model
{
	public function attempt_login($identifier, $password)
	{
		$row = $this->db
			->select('id, username, email, password_hash, full_name, status, force_password_change, library_id')
			->from('auth_user')
			->group_start()
				->where('username', $identifier)
				->or_where('email', $identifier)
			->group_end()
			->where('status', 'active')
			->limit(1)
			->get()
			->row_array();

		if (empty($row) || ! password_verify($password, $row['password_hash'])) {
			return false;
		}

		if (password_needs_rehash($row['password_hash'], PASSWORD_BCRYPT)) {
			$this->db
				->where('id', $row['id'])
				->update('auth_user', [
					'password_hash' => password_hash($password, PASSWORD_BCRYPT),
				]);
		}

		$this->db
			->where('id', $row['id'])
			->update('auth_user', ['last_login_at' => date('Y-m-d H:i:s')]);

		unset($row['password_hash']);

		return $row;
	}

	public function load_roles($user_id)
	{
		return $this->db
			->select('r.id, r.code, r.name, r.level, r.scope_type')
			->from('auth_user_role ur')
			->join('auth_role r', 'r.id = ur.role_id')
			->where('ur.user_id', (int) $user_id)
			->where('r.is_active', 1)
			->order_by('r.level', 'ASC')
			->get()
			->result_array();
	}

	public function load_permissions($user_id)
	{
		$roles = $this->load_roles($user_id);
		foreach ($roles as $role) {
			if ($role['code'] === 'SUPERADMIN') {
				return ['__superadmin__' => true];
			}
		}

		$rows = $this->db
			->select('p.code, rp.can_view, rp.can_create, rp.can_edit, rp.can_delete, rp.can_export, rp.can_approve')
			->from('auth_user_role ur')
			->join('auth_role_permission rp', 'rp.role_id = ur.role_id')
			->join('auth_role r', 'r.id = ur.role_id')
			->join('sys_page p', 'p.id = rp.page_id')
			->where('ur.user_id', (int) $user_id)
			->where('r.is_active', 1)
			->where('p.is_active', 1)
			->get()
			->result_array();

		$permissions = [];
		foreach ($rows as $row) {
			$code = $row['code'];
			if (! isset($permissions[$code])) {
				$permissions[$code] = [
					'can_view' => 0,
					'can_create' => 0,
					'can_edit' => 0,
					'can_delete' => 0,
					'can_export' => 0,
					'can_approve' => 0,
				];
			}

			foreach (array_keys($permissions[$code]) as $key) {
				$permissions[$code][$key] = max($permissions[$code][$key], (int) $row[$key]);
			}
		}

		$overrides = $this->db
			->select('p.code, o.can_view, o.can_create, o.can_edit, o.can_delete, o.can_export, o.can_approve')
			->from('auth_user_permission_override o')
			->join('sys_page p', 'p.id = o.page_id')
			->where('o.user_id', (int) $user_id)
			->where('p.is_active', 1)
			->get()
			->result_array();

		foreach ($overrides as $override) {
			$code = $override['code'];
			if (! isset($permissions[$code])) {
				$permissions[$code] = [
					'can_view' => 0,
					'can_create' => 0,
					'can_edit' => 0,
					'can_delete' => 0,
					'can_export' => 0,
					'can_approve' => 0,
				];
			}

			foreach (array_keys($permissions[$code]) as $key) {
				if ($override[$key] !== null) {
					$permissions[$code][$key] = (int) $override[$key];
				}
			}
		}

		return $permissions;
	}

	public function write_event($event_type, $user_id = null, $username_attempt = null, array $meta = [])
	{
		$this->db->insert('auth_session_log', [
			'user_id' => $user_id,
			'event_type' => $event_type,
			'username_attempt' => $username_attempt,
			'ip_address' => $this->input->ip_address(),
			'user_agent' => substr((string) $this->input->user_agent(), 0, 255),
			'meta_json' => empty($meta) ? null : json_encode($meta),
		]);

		return (int) $this->db->insert_id();
	}
}
