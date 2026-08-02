<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transactions extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Transaction_sync_model');
	}

	public function index()
	{
		$this->require_permission('transactions.index', 'view');

		$active_tab = (string) $this->input->get('tab', true);
		$active_tab = in_array($active_tab, ['visits', 'access', 'loans', 'items'], true) ? $active_tab : 'visits';
		$filters = [
			'q' => $this->input->get('q', true),
			'date_from' => $this->input->get('date_from', true),
			'date_to' => $this->input->get('date_to', true),
			'visit_channel' => $this->input->get('visit_channel', true),
			'rule_type' => $this->input->get('rule_type', true),
			'loan_status' => $this->input->get('loan_status', true),
		];
		$per_page = (int) $this->input->get('per_page', true);
		$per_page = in_array($per_page, [10, 25, 50, 100], true) ? $per_page : 25;
		$page = max(1, (int) $this->input->get('page', true));

		$total_rows = $this->count_for_tab($active_tab, $filters);
		$total_pages = max(1, (int) ceil($total_rows / $per_page));
		$page = min($page, $total_pages);
		$offset = ($page - 1) * $per_page;

		$this->render('transactions/index', [
			'title' => 'Layanan Harian',
			'stats' => $this->Transaction_sync_model->stats(),
			'active_tab' => $active_tab,
			'filters' => [
				'q' => $filters['q'],
				'date_from' => $filters['date_from'],
				'date_to' => $filters['date_to'],
				'visit_channel' => $filters['visit_channel'],
				'rule_type' => $filters['rule_type'],
				'loan_status' => $filters['loan_status'],
				'per_page' => $per_page,
				'page' => $page,
			],
			'pagination' => [
				'total_rows' => $total_rows,
				'total_pages' => $total_pages,
				'page' => $page,
				'per_page' => $per_page,
				'offset' => $offset,
			],
			'visits' => $active_tab === 'visits' ? $this->Transaction_sync_model->get_visits($filters, $per_page, $offset) : [],
			'access_rules' => $active_tab === 'access' ? $this->Transaction_sync_model->get_access_rules($filters, $per_page, $offset) : [],
			'loans' => $active_tab === 'loans' ? $this->Transaction_sync_model->get_loan_transactions($filters, $per_page, $offset) : [],
			'loan_items' => $active_tab === 'items' ? $this->Transaction_sync_model->get_loan_items($filters, $per_page, $offset) : [],
		]);
	}

	public function sync()
	{
		$this->require_permission('transactions.sync', 'view');

		$this->render('transactions/sync', [
			'title' => 'Sinkronisasi Layanan Harian',
			'stats' => $this->Transaction_sync_model->stats(),
			'source_stats' => $this->Transaction_sync_model->source_stats(),
			'sync_runs' => $this->Transaction_sync_model->recent_runs(20),
			'can_run_sync' => $this->can('transactions.sync', 'create'),
		]);
	}

	public function run_sync()
	{
		$this->require_permission('transactions.sync', 'create');

		$domain = (string) $this->input->post('domain', true);
		$mode = (string) $this->input->post('mode', true);
		$limit = (int) $this->input->post('limit', true);
		$result = $this->Transaction_sync_model->run_manual_sync(
			(int) ($this->current_user['id'] ?? 0),
			$domain,
			$limit ?: 5000,
			$mode
		);

		$this->audit_event('transactions.sync_run', 'transaction_sync_runs', (int) $result['run_id'], null, $result);
		$this->session->set_flashdata('success', $result['message']);
		redirect('transactions/sync');
	}

	private function count_for_tab($tab, array $filters)
	{
		if ($tab === 'access') {
			return $this->Transaction_sync_model->count_access_rules($filters);
		}
		if ($tab === 'loans') {
			return $this->Transaction_sync_model->count_loan_transactions($filters);
		}
		if ($tab === 'items') {
			return $this->Transaction_sync_model->count_loan_items($filters);
		}

		return $this->Transaction_sync_model->count_visits($filters);
	}
}
