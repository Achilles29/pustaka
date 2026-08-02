<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Asset_migration extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Asset_migration_model');
	}

	public function index()
	{
		$this->require_permission('assets.migration', 'view');

		$this->render('asset_migration/index', [
			'title' => 'Migrasi Aset INLISLite',
			'summary' => $this->Asset_migration_model->summary(),
			'can_run_migration' => $this->can('assets.migration', 'create'),
		]);
	}

	public function run()
	{
		$this->require_permission('assets.migration', 'create');

		$asset_type = (string) $this->input->post('asset_type', true);
		$mode = (string) $this->input->post('mode', true);
		$limit = (int) $this->input->post('limit', true);

		$result = $this->Asset_migration_model->run_manual_migration(
			(int) ($this->current_user['id'] ?? 0),
			$asset_type,
			$limit ?: 500,
			$mode
		);

		$this->audit_event('assets.migration_run', 'asset_migration_runs', (int) $result['run_id'], null, $result);
		$this->session->set_flashdata('success', $result['message']);
		redirect('assets-migration');
	}
}
