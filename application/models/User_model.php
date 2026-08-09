<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{
	public function get_users(array $filters = [], $limit = 25, $offset = 0)
	{
		$this->db
			->select('u.id, u.username, u.email, u.full_name, u.library_id, u.source_system, u.source_table, u.source_id, u.source_role, u.status, u.last_login_at, u.created_at, l.name AS library_name')
			->from('auth_user u')
			->join('libraries l', 'l.id = u.library_id', 'left');

		$this->apply_user_filters($filters);

		$users = $this->db
			->order_by('u.full_name', 'ASC')
			->limit($this->bounded_limit($limit), max(0, (int) $offset))
			->get()
			->result_array();

		foreach ($users as &$user) {
			$user['roles'] = $this->get_user_roles((int) $user['id']);
		}
		unset($user);

		return $users;
	}

	public function count_users(array $filters = [])
	{
		$this->db
			->select('COUNT(DISTINCT u.id) AS total_rows', false)
			->from('auth_user u')
			->join('libraries l', 'l.id = u.library_id', 'left');

		$this->apply_user_filters($filters);

		$row = $this->db->get()->row_array();
		return (int) ($row['total_rows'] ?? 0);
	}

	public function get_user_stats(array $filters = [])
	{
		$base_filters = $filters;
		unset($base_filters['status']);

		return [
			'total' => $this->count_users($base_filters),
			'active' => $this->count_users(array_merge($base_filters, ['status' => 'active'])),
			'inactive' => $this->count_users(array_merge($base_filters, ['status' => 'inactive'])),
			'suspended' => $this->count_users(array_merge($base_filters, ['status' => 'suspended'])),
		];
	}

	public function get_user($id)
	{
		$user = $this->db
			->select('u.id, u.username, u.email, u.full_name, u.library_id, u.source_system, u.source_table, u.source_id, u.source_role, u.status, u.last_login_at, u.created_at, l.name AS library_name')
			->from('auth_user u')
			->join('libraries l', 'l.id = u.library_id', 'left')
			->where('u.id', (int) $id)
			->get()
			->row_array();

		if ($user) {
			$user['roles'] = $this->get_user_roles((int) $user['id']);
		}

		return $user;
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

	public function get_admin_roles()
	{
		return $this->db
			->from('auth_role')
			->where('is_active', 1)
			->where('code !=', 'USER')
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
			'library_id' => empty($data['library_id']) ? null : (int) $data['library_id'],
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

	public function update_library_scope($user_id, $library_id)
	{
		$this->db
			->where('id', (int) $user_id)
			->update('auth_user', [
				'library_id' => empty($library_id) ? null : (int) $library_id,
			]);
	}

	public function set_status($user_id, $status)
	{
		$this->db
			->where('id', (int) $user_id)
			->update('auth_user', ['status' => $status]);
	}

	private function apply_user_filters(array $filters)
	{
		if (($filters['account_type'] ?? '') === 'admin') {
			$this->db->where("EXISTS (
				SELECT 1
				FROM auth_user_role ur_admin
				JOIN auth_role r_admin ON r_admin.id = ur_admin.role_id
				WHERE ur_admin.user_id = u.id
				  AND r_admin.code <> 'USER'
			)", null, false);
		}

		$q = trim((string) ($filters['q'] ?? ''));
		if ($q !== '') {
			$this->db->group_start()
				->like('u.username', $q)
				->or_like('u.email', $q)
				->or_like('u.full_name', $q)
				->or_like('l.name', $q)
				->or_like('u.source_role', $q)
				->group_end();
		}

		$status = trim((string) ($filters['status'] ?? ''));
		if (in_array($status, ['active', 'inactive', 'suspended'], true)) {
			$this->db->where('u.status', $status);
		}

		$role_id = (int) ($filters['role_id'] ?? 0);
		if ($role_id > 0) {
			$this->db->where("EXISTS (
				SELECT 1
				FROM auth_user_role ur_filter
				WHERE ur_filter.user_id = u.id
				  AND ur_filter.role_id = " . (int) $role_id . "
			)", null, false);
		}

		$library_id = (int) ($filters['library_id'] ?? 0);
		if ($library_id > 0) {
			$this->db->where('u.library_id', $library_id);
		}

		$source = trim((string) ($filters['source'] ?? ''));
		if ($source === 'inlislite_admin') {
			$this->db->where('u.source_system', 'inlislite_v3');
			$this->db->where('u.source_table', 'users');
		} elseif ($source === 'local') {
			$this->db->group_start()
				->where('u.source_system IS NULL', null, false)
				->or_where('u.source_system', '')
				->group_end();
		}
	}

	private function bounded_limit($limit)
	{
		return max(1, min(100, (int) $limit));
	}
}
