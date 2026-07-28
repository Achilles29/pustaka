<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sidebar extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Menu_model');
	}

	public function manage()
	{
		$this->require_permission('system.sidebar.manage', 'view');

		$edit_id = (int) $this->input->get('edit_id', true);
		$edit_menu = $edit_id > 0 ? $this->Menu_model->get_menu($edit_id) : null;

		$this->render('sidebar/manage', [
			'title' => 'Manajemen Sidebar',
			'menus' => $this->Menu_model->get_menu_rows('MAIN', false),
			'menu_tree' => $this->Menu_model->get_menu_tree_raw('MAIN'),
			'pages' => $this->Menu_model->get_pages(),
			'parents' => $this->Menu_model->get_parent_options('MAIN', $edit_id ?: null),
			'edit_menu' => $edit_menu,
		]);
	}

	public function store()
	{
		$this->require_permission('system.sidebar.manage', 'create');
		$this->save_menu();
	}

	public function update($id)
	{
		$this->require_permission('system.sidebar.manage', 'edit');
		if (! $this->Menu_model->get_menu((int) $id)) {
			show_404();
			return;
		}
		$this->save_menu((int) $id);
	}

	public function toggle($id)
	{
		$this->require_permission('system.sidebar.manage', 'edit');

		$menu = $this->Menu_model->get_menu((int) $id);
		if (! $menu) {
			show_404();
			return;
		}

		if ((int) $menu['is_locked'] === 1) {
			$this->session->set_flashdata('error', 'Menu terkunci tidak dapat dinonaktifkan.');
			redirect('sidebar/manage');
		}

		if ((int) $menu['is_active'] === 1 && $this->Menu_model->has_children((int) $id)) {
			$this->session->set_flashdata('error', 'Nonaktifkan submenu aktif terlebih dahulu.');
			redirect('sidebar/manage');
		}

		$this->Menu_model->update_menu((int) $id, [
			'parent_id' => $menu['parent_id'],
			'page_id' => $menu['page_id'],
			'menu_area' => $menu['menu_area'],
			'menu_key' => $menu['menu_key'],
			'title' => $menu['title'],
			'icon' => $menu['icon'],
			'url' => $menu['url'],
			'sort_order' => $menu['sort_order'],
			'is_visible' => $menu['is_visible'],
			'is_active' => (int) $menu['is_active'] === 1 ? 0 : 1,
			'is_locked' => $menu['is_locked'],
		]);

		$this->session->set_flashdata('success', 'Status menu berhasil diperbarui.');
		redirect('sidebar/manage');
	}

	private function save_menu($id = null)
	{
		$menu_key = trim((string) $this->input->post('menu_key', true));
		$title = trim((string) $this->input->post('title', true));

		if ($menu_key === '' || $title === '') {
			$this->session->set_flashdata('error', 'Menu key dan judul wajib diisi.');
			redirect('sidebar/manage' . ($id ? '?edit_id=' . (int) $id : ''));
		}

		if ($this->Menu_model->menu_key_exists($menu_key, $id)) {
			$this->session->set_flashdata('error', 'Menu key sudah digunakan.');
			redirect('sidebar/manage' . ($id ? '?edit_id=' . (int) $id : ''));
		}

		$payload = [
			'parent_id' => (int) $this->input->post('parent_id', true),
			'page_id' => (int) $this->input->post('page_id', true),
			'menu_area' => 'MAIN',
			'menu_key' => $menu_key,
			'title' => $title,
			'icon' => trim((string) $this->input->post('icon', true)),
			'url' => trim((string) $this->input->post('url', true)),
			'sort_order' => (int) $this->input->post('sort_order', true),
			'is_visible' => $this->input->post('is_visible') ? 1 : 0,
			'is_active' => $this->input->post('is_active') ? 1 : 0,
			'is_locked' => $id ? (int) ($this->Menu_model->get_menu($id)['is_locked'] ?? 0) : 0,
		];

		if ($id) {
			$this->Menu_model->update_menu($id, $payload);
			$this->session->set_flashdata('success', 'Menu sidebar berhasil diperbarui.');
		} else {
			$this->Menu_model->create_menu($payload);
			$this->session->set_flashdata('success', 'Menu sidebar berhasil ditambahkan.');
		}

		redirect('sidebar/manage');
	}
}
