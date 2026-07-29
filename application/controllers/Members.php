<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Members extends MY_Controller
{
	const DEFAULT_IMPORTED_PASSWORD = 'PustakaRembang#2026';

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Member_model');
	}

	public function index()
	{
		$this->require_permission('members.index', 'view');

		$filters = [
			'q' => $this->input->get('q', true),
			'status' => $this->input->get('status', true),
			'user_status' => $this->input->get('user_status', true),
		];
		$per_page = (int) $this->input->get('per_page', true);
		$per_page = in_array($per_page, [10, 25, 50, 100], true) ? $per_page : 25;
		$page = max(1, (int) $this->input->get('page', true));
		$total_rows = $this->Member_model->count_members($filters);
		$total_pages = max(1, (int) ceil($total_rows / $per_page));
		$page = min($page, $total_pages);
		$offset = ($page - 1) * $per_page;

		$this->render('members/index', [
			'title' => 'Membership Digital',
			'stats' => $this->Member_model->stats(),
			'source_stats' => $this->Member_model->source_stats(),
			'members' => $this->Member_model->get_members($filters, $per_page, $offset),
			'sync_runs' => $this->Member_model->recent_sync_runs(),
			'filters' => [
				'q' => $filters['q'],
				'status' => $filters['status'],
				'user_status' => $filters['user_status'],
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
		]);
	}

	public function detail($id)
	{
		$this->require_permission('members.index', 'view');

		$member = $this->Member_model->get_member((int) $id);
		if (! $member) {
			show_404();
			return;
		}

		$this->render('members/detail', [
			'title' => 'Detail Member',
			'member' => $member,
			'default_password' => self::DEFAULT_IMPORTED_PASSWORD,
		]);
	}

	public function sync()
	{
		$this->require_permission('members.sync', 'view');

		$this->render('members/sync', [
			'title' => 'Sinkronisasi Member',
			'default_password' => self::DEFAULT_IMPORTED_PASSWORD,
			'source_stats' => $this->Member_model->source_stats(),
			'sync_runs' => $this->Member_model->recent_sync_runs(20),
			'migration_plan' => $this->Member_model->migration_plan(),
			'can_run_sync' => $this->can('members.sync', 'create'),
		]);
	}

	public function run_sync()
	{
		$this->require_permission('members.sync', 'create');

		$limit = (int) $this->input->post('limit', true);
		$mode = (string) $this->input->post('mode', true);
		$result = $this->Member_model->run_manual_sync(
			(int) ($this->current_user['id'] ?? 0),
			$limit ?: 500,
			self::DEFAULT_IMPORTED_PASSWORD,
			$mode
		);

		$this->audit_event('members.sync_run', 'member_sync_runs', (int) $result['run_id'], null, $result);
		$this->session->set_flashdata('success', $result['message']);
		redirect('members/sync');
	}
}
