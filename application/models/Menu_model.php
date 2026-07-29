<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Menu_model extends CI_Model
{
	public function get_sidebar_tree(array $permissions = [], $is_superadmin = false, $area = 'MAIN')
	{
		$allowed_pages = $this->allowed_page_codes($permissions);

		$this->db
			->select('m.id, m.parent_id, m.page_id, m.menu_key, m.title, m.icon, m.url, m.sort_order, p.code AS page_code')
			->from('sys_menu m')
			->join('sys_page p', 'p.id = m.page_id', 'left')
			->where('m.menu_area', $area)
			->where('m.is_active', 1)
			->where('m.is_visible', 1);

		if (! $is_superadmin) {
			$this->db->group_start();
			$this->db->where('m.page_id IS NULL', null, false);
			if (! empty($allowed_pages)) {
				$this->db->or_where_in('p.code', $allowed_pages);
			}
			$this->db->group_end();
		}

		$rows = $this->db
			->order_by('m.sort_order', 'ASC')
			->order_by('m.title', 'ASC')
			->get()
			->result_array();

		return $this->build_tree($rows, true);
	}

	public function get_menu_tree_raw($area = 'MAIN')
	{
		$rows = $this->get_menu_rows($area, false);
		return $this->build_tree($rows, false);
	}

	public function get_menu_rows($area = 'MAIN', $active_only = false)
	{
		$this->db
			->select('m.*, p.code AS page_code, p.title AS page_title')
			->from('sys_menu m')
			->join('sys_page p', 'p.id = m.page_id', 'left')
			->where('m.menu_area', $area);

		if ($active_only) {
			$this->db->where('m.is_active', 1);
		}

		return $this->db
			->order_by('m.sort_order', 'ASC')
			->order_by('m.title', 'ASC')
			->get()
			->result_array();
	}

	public function get_menu($id)
	{
		return $this->db
			->from('sys_menu')
			->where('id', (int) $id)
			->get()
			->row_array();
	}

	public function get_parent_options($area = 'MAIN', $exclude_id = null)
	{
		$this->db
			->select('id, title, menu_key')
			->from('sys_menu')
			->where('menu_area', $area)
			->where('is_active', 1)
			->where('parent_id IS NULL', null, false)
			->order_by('sort_order', 'ASC');

		if ($exclude_id !== null) {
			$this->db->where('id !=', (int) $exclude_id);
		}

		return $this->db->get()->result_array();
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

	public function menu_key_exists($menu_key, $exclude_id = null)
	{
		$this->db->from('sys_menu')->where('menu_key', $menu_key);
		if ($exclude_id !== null) {
			$this->db->where('id !=', (int) $exclude_id);
		}

		return $this->db->count_all_results() > 0;
	}

	public function create_menu(array $data)
	{
		$this->db->insert('sys_menu', $this->clean_menu_payload($data));
		return (int) $this->db->insert_id();
	}

	public function update_menu($id, array $data)
	{
		$this->db
			->where('id', (int) $id)
			->update('sys_menu', $this->clean_menu_payload($data));
	}

	public function save_order(array $items)
	{
		$parent_map = [];
		foreach ($items as $item) {
			$parent_map[(int) ($item['id'] ?? 0)] = (int) ($item['parent_id'] ?? 0);
		}

		$this->db->trans_start();
		foreach ($items as $item) {
			$id = (int) ($item['id'] ?? 0);
			$parent_id = (int) ($item['parent_id'] ?? 0);
			$sort_order = (int) ($item['sort_order'] ?? 0);

			if ($id <= 0 || $parent_id === $id || $this->would_create_cycle($id, $parent_id, $parent_map)) {
				continue;
			}

			$this->db
				->where('id', $id)
				->update('sys_menu', [
					'parent_id' => $parent_id > 0 ? $parent_id : null,
					'sort_order' => $sort_order,
				]);
		}
		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	public function has_children($id)
	{
		return $this->db
			->from('sys_menu')
			->where('parent_id', (int) $id)
			->where('is_active', 1)
			->count_all_results() > 0;
	}

	private function allowed_page_codes(array $permissions)
	{
		$allowed = [];
		foreach ($permissions as $page_code => $flags) {
			if ($page_code === '__superadmin__') {
				continue;
			}
			if (! empty($flags['can_view'])) {
				$allowed[] = $page_code;
			}
		}

		return $allowed;
	}

	private function build_tree(array $rows, $drop_empty_parents)
	{
		$items = [];
		foreach ($rows as $row) {
			$row['children'] = [];
			$items[(int) $row['id']] = $row;
		}

		$tree = [];
		foreach ($items as $id => &$item) {
			$parent_id = (int) $item['parent_id'];
			if ($parent_id > 0 && isset($items[$parent_id])) {
				$items[$parent_id]['children'][] = &$item;
				continue;
			}
			$tree[] = &$item;
		}
		unset($item);

		if (! $drop_empty_parents) {
			return $tree;
		}

		return array_values(array_filter($tree, function ($item) {
			return ! empty($item['page_id']) || ! empty($item['children']);
		}));
	}

	private function would_create_cycle($id, $parent_id, array $parent_map)
	{
		$seen = [];
		while ($parent_id > 0) {
			if ($parent_id === $id || isset($seen[$parent_id])) {
				return true;
			}
			$seen[$parent_id] = true;
			$parent_id = (int) ($parent_map[$parent_id] ?? 0);
		}

		return false;
	}

	private function clean_menu_payload(array $data)
	{
		return [
			'parent_id' => empty($data['parent_id']) ? null : (int) $data['parent_id'],
			'page_id' => empty($data['page_id']) ? null : (int) $data['page_id'],
			'menu_area' => empty($data['menu_area']) ? 'MAIN' : strtoupper($data['menu_area']),
			'menu_key' => trim((string) $data['menu_key']),
			'title' => trim((string) $data['title']),
			'icon' => empty($data['icon']) ? null : trim((string) $data['icon']),
			'url' => empty($data['url']) ? null : trim((string) $data['url']),
			'sort_order' => isset($data['sort_order']) ? (int) $data['sort_order'] : 100,
			'is_visible' => empty($data['is_visible']) ? 0 : 1,
			'is_active' => empty($data['is_active']) ? 0 : 1,
			'is_locked' => empty($data['is_locked']) ? 0 : 1,
		];
	}
}
