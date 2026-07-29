<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Audit extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Audit_model');
	}

	public function index()
	{
		$this->require_permission('audit.index', 'view');

		$filters = [
			'q' => $this->input->get('q', true),
			'event_code' => $this->input->get('event_code', true),
			'entity_type' => $this->input->get('entity_type', true),
		];
		$per_page = (int) $this->input->get('per_page', true);
		$per_page = in_array($per_page, [10, 25, 50, 100], true) ? $per_page : 25;
		$page = max(1, (int) $this->input->get('page', true));
		$total_logs = $this->Audit_model->count_logs($filters);
		$total_pages = max(1, (int) ceil($total_logs / $per_page));
		$page = min($page, $total_pages);
		$offset = ($page - 1) * $per_page;

		$this->render('audit/index', [
			'title' => 'Audit Log',
			'logs' => $this->Audit_model->get_logs($filters, $per_page, $offset),
			'event_options' => $this->Audit_model->get_event_options(),
			'entity_options' => $this->Audit_model->get_entity_options(),
			'filters' => $filters,
			'per_page' => $per_page,
			'page' => $page,
			'total_pages' => $total_pages,
			'total_logs' => $total_logs,
		]);
	}
}
