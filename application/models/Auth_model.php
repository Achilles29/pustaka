<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_model extends CI_Model
{
	public function get_user_with_password($user_id)
	{
		return $this->db
			->select('id, username, email, password_hash, full_name, status, force_password_change, library_id')
			->from('auth_user')
			->where('id', (int) $user_id)
			->limit(1)
			->get()
			->row_array();
	}

	public function attempt_login($identifier, $password)
	{
        // Serialize password verification with activation; a login that wins the
        // user-row lock prevents any later ownership claim for this library.
        $this->db->trans_begin();
        try {
            $query=$this->db->query("SELECT id,username,email,password_hash,full_name,status,force_password_change,library_id FROM auth_user WHERE (username=? OR email=?) AND status='active' LIMIT 1 FOR UPDATE",[$identifier,$identifier]);
            $row=$query?$query->row_array():null;
            if(!$row||!password_verify((string)$password,$row['password_hash'])){$this->db->trans_rollback();return false;}
            if(!empty($row['library_id'])&&$this->db->table_exists('library_activation_codes')){
                $pending=$this->db->where('library_id',$row['library_id'])->where('claimed_at IS NULL',null,false)->count_all_results('library_activation_codes');
                if($pending){$this->db->trans_rollback();return false;}
            }
            $data=['last_login_at'=>date('Y-m-d H:i:s')];
            if(password_needs_rehash($row['password_hash'],PASSWORD_BCRYPT))$data['password_hash']=password_hash((string)$password,PASSWORD_BCRYPT);
            if(!$this->db->where('id',$row['id'])->update('auth_user',$data)||!$this->db->trans_status())throw new RuntimeException('Login gagal disimpan.');
            $this->db->trans_commit();unset($row['password_hash']);return $row;
        }catch(Throwable $e){$this->db->trans_rollback();return false;}
	}

	public function username_exists($username, $exclude_user_id = null)
	{
		$this->db
			->from('auth_user')
			->where('username', (string) $username);

		if ($exclude_user_id !== null) {
			$this->db->where('id <>', (int) $exclude_user_id);
		}

		return $this->db->count_all_results() > 0;
	}

	public function update_username($user_id, $username)
	{
		$this->db
			->where('id', (int) $user_id)
			->update('auth_user', [
				'username' => trim((string) $username),
				'updated_at' => date('Y-m-d H:i:s'),
			]);

		return $this->db->affected_rows() >= 0;
	}

	public function update_password($user_id, $password, $expected_hash = null)
	{
		if($expected_hash!==null)$this->db->where('password_hash',$expected_hash);
		$this->db
			->where('id', (int) $user_id)
			->update('auth_user', [
				'password_hash' => password_hash((string) $password, PASSWORD_BCRYPT),
				'force_password_change' => 0,
				'updated_at' => date('Y-m-d H:i:s'),
			]);

		return $expected_hash!==null?$this->db->affected_rows()===1:$this->db->affected_rows()>=0;
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
