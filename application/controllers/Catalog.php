<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Catalog extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Catalog_model');
	}

	public function index()
	{
		$this->require_permission('catalog.index', 'view');

		$filters = [
			'q' => $this->input->get('q', true),
			'status' => $this->input->get('status', true),
			'publish_year' => $this->input->get('publish_year', true),
		];
		$per_page = (int) $this->input->get('per_page', true);
		$per_page = in_array($per_page, [10, 25, 50, 100], true) ? $per_page : 25;
		$page = max(1, (int) $this->input->get('page', true));
		$total_rows = $this->Catalog_model->count_books($filters, $this->current_library_scope_id());
		$total_pages = max(1, (int) ceil($total_rows / $per_page));
		$page = min($page, $total_pages);
		$offset = ($page - 1) * $per_page;

		$this->render('catalog/index', [
			'title' => 'Katalog Buku',
			'stats' => $this->Catalog_model->stats($this->current_library_scope_id()),
			'source_stats' => $this->Catalog_model->source_stats(),
			'books' => $this->Catalog_model->get_books($filters, $per_page, $offset, $this->current_library_scope_id()),
			'sync_runs' => $this->Catalog_model->recent_sync_runs(),
			'filters' => [
				'q' => $filters['q'],
				'status' => $filters['status'],
				'publish_year' => $filters['publish_year'],
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
		$this->require_permission('catalog.index', 'view');

		$book = $this->Catalog_model->get_book((int) $id, $this->current_library_scope_id());
		if (! $book) {
			show_404();
			return;
		}

		$this->render('catalog/detail', [
			'title' => 'Detail Buku',
			'book' => $book,
			'authors' => $this->Catalog_model->get_book_authors((int) $id),
			'subjects' => $this->Catalog_model->get_book_subjects((int) $id),
			'items' => $this->Catalog_model->get_book_items((int) $id, 50, $this->current_library_scope_id()),
		]);
	}

	public function sync()
	{
		$this->require_permission('catalog.sync', 'view');
		$this->render('catalog/sync', [
			'title' => 'Sinkronisasi Katalog',
			'source_stats' => $this->Catalog_model->source_stats(),
			'sync_runs' => $this->Catalog_model->recent_sync_runs(20),
			'can_run_sync' => $this->can('catalog.sync', 'create'),
		]);
	}

	public function run_sync()
	{
		$this->require_permission('catalog.sync', 'create');

		$limit = (int) $this->input->post('limit', true);
		$mode = (string) $this->input->post('mode', true);
		$result = $this->Catalog_model->run_manual_sync((int) ($this->current_user['id'] ?? 0), $limit ?: 500, $mode);

		$this->audit_event('catalog.sync_run', 'catalog_sync_runs', (int) $result['run_id'], null, $result);
		$this->session->set_flashdata('success', $result['message']);
		redirect('catalog/sync');
	}
}
