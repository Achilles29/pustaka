<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{
	public function get_users()
	{
		$users = $this->db
			->select('u.id, u.username, u.email, u.full_name, u.status, u.last_login_at, u.created_at')
			->from('auth_user u')
			->order_by('u.id', 'ASC')
			->get()
			->result_array();

		foreach ($users as &$user) {
			$user['roles'] = $this->get_user_roles((int) $user['id']);
		}
		unset($user);

		return $users;
	}

	public function get_roles()
	{
		return $this->db
			->from('auth_role')
			->where('is_active', 1)
			->order_by('level', 'ASC')
			->get()
			->result_array();
	}

	public function get_user_roles($user_id)
	{
		return $this->db
			->select('r.id, r.code, r.name')
			->from('auth_user_role ur')
			->join('auth_role r', 'r.id = ur.role_id')
			->where('ur.user_id', (int) $user_id)
			->order_by('r.level', 'ASC')
			->get()
			->result_array();
	}

	public function create_user(array $data, array $role_ids)
	{
		$this->db->trans_start();
		$this->db->insert('auth_user', [
			'username' => trim((string) $data['username']),
			'email' => trim((string) $data['email']) !== '' ? trim((string) $data['email']) : null,
			'password_hash' => password_hash((string) $data['password'], PASSWORD_BCRYPT),
			'full_name' => trim((string) $data['full_name']),
			'status' => 'active',
			'force_password_change' => 1,
		]);

		$user_id = (int) $this->db->insert_id();
		$this->replace_roles($user_id, $role_ids);
		$this->db->trans_complete();

		return $this->db->trans_status() ? $user_id : 0;
	}

	public function username_exists($username)
	{
		return $this->db
			->from('auth_user')
			->where('username', $username)
			->count_all_results() > 0;
	}

	public function email_exists($email)
	{
		if (trim((string) $email) === '') {
			return false;
		}

		return $this->db
			->from('auth_user')
			->where('email', $email)
			->count_all_results() > 0;
	}

	public function replace_roles($user_id, array $role_ids)
	{
		$user_id = (int) $user_id;
		$this->db->where('user_id', $user_id)->delete('auth_user_role');

		foreach (array_unique(array_map('intval', $role_ids)) as $role_id) {
			if ($role_id > 0) {
				$this->db->insert('auth_user_role', [
					'user_id' => $user_id,
					'role_id' => $role_id,
				]);
			}
		}
	}

	public function set_status($user_id, $status)
	{
		$this->db
			->where('id', (int) $user_id)
			->update('auth_user', ['status' => $status]);
	}
}
