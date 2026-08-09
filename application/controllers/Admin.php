<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends MY_Controller
{
	public function index()
	{
		$this->require_permission('dashboard.index', 'view');

		$this->render('dashboard/index', [
			'title' => 'Admin Panel Pustaka Digital Rembang',
			'stats' => $this->get_stats(),
		]);
	}

	private function get_stats()
	{
		$this->load->model('Library_model');

		$service_tables = [
			'books' => 'Katalog buku',
			'book_items' => 'Eksemplar koleksi',
			'members' => 'Member aktif',
			'libraries' => 'Perpustakaan GIS',
			'reading_points' => 'Pojok baca',
			'loan_transactions' => 'Riwayat peminjaman',
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
			'service' => $this->count_tables($this->db, $service_tables),
			'libraries' => $this->Library_model->dashboard_stats($this->current_library_scope_id()),
			'queue' => $this->queue_stats(),
			'activity' => $this->activity_stats(),
			'sync_health' => $this->sync_health(),
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

	private function table_count($table, array $where = [])
	{
		if (! $this->db->table_exists($table)) {
			return 0;
		}

		foreach ($where as $field => $value) {
			$this->db->where($field, $value);
		}

		return (int) $this->db->count_all_results($table);
	}

	private function queue_stats()
	{
		$items = [
			'registrations' => [
				'label' => 'Pendaftaran Online',
				'url' => 'members/registrations',
				'value' => $this->table_count('member_registration_requests', ['status' => 'pending']),
				'icon' => 'ti ti-user-plus',
			],
			'book_requests' => [
				'label' => 'Request Buku',
				'url' => 'catalog/requests',
				'value' => $this->table_count('book_requests', ['status' => 'pending']),
				'icon' => 'ti ti-books',
			],
			'renewals' => [
				'label' => 'Perpanjangan',
				'url' => 'members/renewals',
				'value' => $this->table_count('membership_renewal_requests', ['status' => 'pending']),
				'icon' => 'ti ti-id-badge-2',
			],
		];

		$total = 0;
		foreach ($items as $item) {
			$total += (int) $item['value'];
		}

		return [
			'total' => $total,
			'items' => $items,
		];
	}

	private function activity_stats()
	{
		$today = date('Y-m-d');
		$today_visits = 0;
		if ($this->db->table_exists('member_visits')) {
			$today_visits = (int) $this->db
				->where('DATE(visited_at) = ' . $this->db->escape($today), null, false)
				->count_all_results('member_visits');
		}

		return [
			'today_visits' => $today_visits,
			'active_tokens' => $this->table_count('reading_tokens', ['status' => 'active']),
			'active_reading_sessions' => $this->table_count('reading_sessions', ['status' => 'active']),
			'published_events' => $this->table_count('literacy_events', ['status' => 'published']),
			'digital_assets' => $this->table_count('digital_assets', ['status' => 'active']),
		];
	}

	private function sync_health()
	{
		$tables = [
			'catalog_sync_runs' => 'Katalog',
			'member_sync_runs' => 'Member',
			'transaction_sync_runs' => 'Layanan Harian',
			'asset_migration_runs' => 'Migrasi Aset',
		];

		$rows = [];
		foreach ($tables as $table => $label) {
			if (! $this->db->table_exists($table)) {
				$rows[] = ['label' => $label, 'status' => 'none', 'finished_at' => null, 'message' => 'Belum ada tabel log.'];
				continue;
			}

			$row = $this->db
				->from($table)
				->order_by('id', 'DESC')
				->limit(1)
				->get()
				->row_array();
			$rows[] = [
				'label' => $label,
				'status' => $row['status'] ?? 'none',
				'finished_at' => $row['finished_at'] ?? ($row['created_at'] ?? null),
				'message' => $row['message'] ?? 'Belum pernah dijalankan.',
			];
		}

		return $rows;
	}
}
