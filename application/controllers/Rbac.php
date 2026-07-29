<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rbac extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Role_model');
		$this->load->model('User_model');
		$this->load->model('Menu_model');
		$this->load->model('Page_model');
	}

	public function index()
	{
		redirect('rbac/roles');
	}

	public function users()
	{
		$this->require_permission('auth.users.index', 'view');

		$this->render('rbac/users', [
			'title' => 'RBAC - User',
			'active_rbac_tab' => 'users',
			'users' => $this->User_model->get_users(),
			'roles' => $this->User_model->get_roles(),
		]);
	}

	public function store_user()
	{
		$this->require_permission('auth.users.index', 'create');

		$username = trim((string) $this->input->post('username', true));
		$full_name = trim((string) $this->input->post('full_name', true));
		$password = (string) $this->input->post('password', false);
		$role_ids = (array) $this->input->post('role_ids');

		if ($username === '' || $full_name === '' || strlen($password) < 6 || empty($role_ids)) {
			$this->session->set_flashdata('error', 'Username, nama, password minimal 6 karakter, dan role wajib diisi.');
			redirect('rbac/users');
		}

		if ($this->User_model->username_exists($username) || $this->User_model->email_exists($this->input->post('email', true))) {
			$this->session->set_flashdata('error', 'Username atau email sudah digunakan.');
			redirect('rbac/users');
		}

		$this->User_model->create_user([
			'username' => $username,
			'email' => trim((string) $this->input->post('email', true)),
			'full_name' => $full_name,
			'password' => $password,
		], $role_ids);

		$this->session->set_flashdata('success', 'User baru berhasil dibuat.');
		redirect('rbac/users');
	}

	public function update_user_roles($user_id)
	{
		$this->require_permission('auth.users.index', 'edit');
		$this->User_model->replace_roles((int) $user_id, (array) $this->input->post('role_ids'));
		$this->session->set_flashdata('success', 'Role user berhasil diperbarui.');
		redirect('rbac/users');
	}

	public function toggle_user($user_id)
	{
		$this->require_permission('auth.users.index', 'edit');

		if ((int) $user_id === (int) ($this->current_user['id'] ?? 0)) {
			$this->session->set_flashdata('error', 'Akun yang sedang dipakai tidak bisa dinonaktifkan dari halaman ini.');
			redirect('rbac/users');
		}

		$status = (string) $this->input->post('status', true);
		$this->User_model->set_status((int) $user_id, $status === 'active' ? 'inactive' : 'active');
		$this->session->set_flashdata('success', 'Status user berhasil diperbarui.');
		redirect('rbac/users');
	}

	public function roles()
	{
		$this->require_permission('auth.roles.index', 'view');

		$selected_role_id = (int) $this->input->get('role_id', true);
		$roles = $this->Role_model->get_roles();
		if ($selected_role_id <= 0 && ! empty($roles)) {
			$selected_role_id = (int) $roles[0]['id'];
		}

		$this->render('rbac/roles', [
			'title' => 'RBAC - Role & Permission',
			'active_rbac_tab' => 'roles',
			'roles' => $roles,
			'selected_role' => $this->Role_model->get_role($selected_role_id),
			'pages' => $this->Role_model->get_pages(),
			'permissions' => $this->Role_model->get_permissions($selected_role_id),
		]);
	}

	public function save_role_permissions($role_id)
	{
		$this->require_permission('auth.roles.index', 'edit');

		if (! $this->Role_model->get_role((int) $role_id)) {
			show_404();
			return;
		}

		$pages = $this->Role_model->get_pages();
		$page_ids = array_map(function ($page) {
			return (int) $page['id'];
		}, $pages);

		$this->Role_model->save_permissions((int) $role_id, $page_ids, (array) $this->input->post('permissions'));
		$this->session->set_flashdata('success', 'Hak akses role berhasil disimpan.');
		redirect('rbac/roles?role_id=' . (int) $role_id);
	}

	public function pages()
	{
		$this->require_permission('system.pages.index', 'view');

		$edit_id = (int) $this->input->get('edit_id', true);
		$this->render('rbac/pages', [
			'title' => 'RBAC - Registry Halaman',
			'active_rbac_tab' => 'pages',
			'pages' => $this->Page_model->get_pages(true),
			'edit_page' => $edit_id > 0 ? $this->Page_model->get_page($edit_id) : null,
		]);
	}

	public function store_page()
	{
		$this->require_permission('system.pages.index', 'create');
		$this->save_page();
	}

	public function update_page($id)
	{
		$this->require_permission('system.pages.index', 'edit');
		if (! $this->Page_model->get_page((int) $id)) {
			show_404();
			return;
		}
		$this->save_page((int) $id);
	}

	public function toggle_page($id)
	{
		$this->require_permission('system.pages.index', 'edit');
		$this->Page_model->toggle_page((int) $id);
		$this->session->set_flashdata('success', 'Status halaman berhasil diperbarui.');
		redirect('rbac/pages');
	}

	public function sidebar()
	{
		$this->require_permission('system.sidebar.manage', 'view');

		$edit_id = (int) $this->input->get('edit_id', true);
		$this->render('rbac/sidebar', [
			'title' => 'RBAC - Sidebar',
			'active_rbac_tab' => 'sidebar',
			'menus' => $this->Menu_model->get_menu_rows('MAIN', false),
			'menu_tree' => $this->Menu_model->get_menu_tree_raw('MAIN'),
			'pages' => $this->Menu_model->get_pages(),
			'parents' => $this->Menu_model->get_parent_options('MAIN', $edit_id ?: null),
			'edit_menu' => $edit_id > 0 ? $this->Menu_model->get_menu($edit_id) : null,
		]);
	}

	public function store_menu()
	{
		$this->require_permission('system.sidebar.manage', 'create');
		$this->save_menu();
	}

	public function update_menu($id)
	{
		$this->require_permission('system.sidebar.manage', 'edit');
		if (! $this->Menu_model->get_menu((int) $id)) {
			show_404();
			return;
		}
		$this->save_menu((int) $id);
	}

	public function toggle_menu($id)
	{
		$this->require_permission('system.sidebar.manage', 'edit');

		$menu = $this->Menu_model->get_menu((int) $id);
		if (! $menu) {
			show_404();
			return;
		}

		if ((int) $menu['is_locked'] === 1) {
			$this->session->set_flashdata('error', 'Menu terkunci tidak dapat dinonaktifkan.');
			redirect('rbac/sidebar');
		}

		if ((int) $menu['is_active'] === 1 && $this->Menu_model->has_children((int) $id)) {
			$this->session->set_flashdata('error', 'Nonaktifkan submenu aktif terlebih dahulu.');
			redirect('rbac/sidebar');
		}

		$menu['is_active'] = (int) $menu['is_active'] === 1 ? 0 : 1;
		$this->Menu_model->update_menu((int) $id, $menu);

		$this->session->set_flashdata('success', 'Status menu berhasil diperbarui.');
		redirect('rbac/sidebar');
	}

	private function save_page($id = null)
	{
		$code = trim((string) $this->input->post('code', true));
		$module = trim((string) $this->input->post('module', true));
		$title = trim((string) $this->input->post('title', true));
		$route = trim((string) $this->input->post('route', true));

		if ($code === '' || $module === '' || $title === '' || $route === '') {
			$this->session->set_flashdata('error', 'Code, module, judul, dan route wajib diisi.');
			redirect('rbac/pages' . ($id ? '?edit_id=' . (int) $id : ''));
		}

		if ($this->Page_model->code_exists($code, $id)) {
			$this->session->set_flashdata('error', 'Code halaman sudah digunakan.');
			redirect('rbac/pages' . ($id ? '?edit_id=' . (int) $id : ''));
		}

		$payload = [
			'code' => $code,
			'module' => $module,
			'title' => $title,
			'route' => $route,
			'description' => $this->input->post('description', true),
			'is_active' => $this->input->post('is_active') ? 1 : 0,
		];

		if ($id) {
			$this->Page_model->update_page($id, $payload);
			$this->session->set_flashdata('success', 'Registry halaman berhasil diperbarui.');
		} else {
			$this->Page_model->create_page($payload);
			$this->session->set_flashdata('success', 'Registry halaman berhasil ditambahkan.');
		}

		redirect('rbac/pages');
	}

	private function save_menu($id = null)
	{
		$menu_key = trim((string) $this->input->post('menu_key', true));
		$title = trim((string) $this->input->post('title', true));

		if ($menu_key === '' || $title === '') {
			$this->session->set_flashdata('error', 'Menu key dan judul wajib diisi.');
			redirect('rbac/sidebar' . ($id ? '?edit_id=' . (int) $id : ''));
		}

		if ($this->Menu_model->menu_key_exists($menu_key, $id)) {
			$this->session->set_flashdata('error', 'Menu key sudah digunakan.');
			redirect('rbac/sidebar' . ($id ? '?edit_id=' . (int) $id : ''));
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

		redirect('rbac/sidebar');
	}
}
