<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Page_model extends CI_Model
{
	public function get_pages($include_inactive = true)
	{
		$this->db->from('sys_page');
		if (! $include_inactive) {
			$this->db->where('is_active', 1);
		}

		return $this->db
			->order_by('module', 'ASC')
			->order_by('title', 'ASC')
			->get()
			->result_array();
	}

	public function get_page($id)
	{
		return $this->db
			->from('sys_page')
			->where('id', (int) $id)
			->get()
			->row_array();
	}

	public function code_exists($code, $exclude_id = null)
	{
		$this->db->from('sys_page')->where('code', $code);
		if ($exclude_id !== null) {
			$this->db->where('id !=', (int) $exclude_id);
		}

		return $this->db->count_all_results() > 0;
	}

	public function create_page(array $data)
	{
		$this->db->insert('sys_page', $this->clean_payload($data));
		return (int) $this->db->insert_id();
	}

	public function update_page($id, array $data)
	{
		$this->db
			->where('id', (int) $id)
			->update('sys_page', $this->clean_payload($data));
	}

	public function toggle_page($id)
	{
		$page = $this->get_page($id);
		if (! $page) {
			return false;
		}

		$this->db
			->where('id', (int) $id)
			->update('sys_page', ['is_active' => (int) $page['is_active'] === 1 ? 0 : 1]);

		return true;
	}

	private function clean_payload(array $data)
	{
		return [
			'code' => trim((string) $data['code']),
			'module' => trim((string) $data['module']),
			'title' => trim((string) $data['title']),
			'route' => trim((string) $data['route']),
			'description' => trim((string) ($data['description'] ?? '')) ?: null,
			'is_active' => empty($data['is_active']) ? 0 : 1,
		];
	}
}
