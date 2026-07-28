<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/userguide3/general/urls.html
	 */
	public function index()
	{
		$this->require_permission('dashboard.index', 'view');
		$stats = $this->get_stats();

		$this->render('dashboard/index', [
			'title' => 'Dashboard Pustaka Digital Rembang',
			'stats' => $stats,
		]);
	}

	private function get_stats()
	{
		$source_db = $this->load->database('inlislite', TRUE);

		$source_tables = [
			'catalogs' => 'Katalog INLISLite',
			'collections' => 'Eksemplar INLISLite',
			'members' => 'Anggota INLISLite',
			'location_library' => 'Unit layanan INLISLite',
			'locations' => 'Ruang/lokasi INLISLite',
			'collectionloans' => 'Transaksi pinjam INLISLite',
		];

		$app_tables = [
			'auth_user' => 'User aplikasi',
			'auth_role' => 'Role',
			'sys_page' => 'Registry halaman',
			'sys_menu' => 'Menu sidebar',
			'auth_role_permission' => 'Hak akses role',
			'audit_log' => 'Audit log',
		];

		return [
			'app' => $this->count_tables($this->db, $app_tables),
			'source' => $this->count_tables($source_db, $source_tables),
		];
	}

	private function count_tables($db, array $tables)
	{
		$stats = [];
		foreach ($tables as $table => $label) {
			$stats[$table] = [
				'label' => $label,
				'value' => $db->table_exists($table) ? $db->count_all($table) : 0,
			];
		}

		return $stats;
	}
}
