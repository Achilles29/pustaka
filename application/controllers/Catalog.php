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
			'content_category_id' => $this->input->get('content_category_id', true),
			'content_classification_id' => $this->input->get('content_classification_id', true),
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
				'content_category_id' => $filters['content_category_id'],
				'content_classification_id' => $filters['content_classification_id'],
				'publish_year' => $filters['publish_year'],
				'per_page' => $per_page,
				'page' => $page,
			],
			'content_categories' => $this->Catalog_model->get_content_categories(true),
			'classification_masters' => $this->Catalog_model->get_classification_masters(true),
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
			'digital_assets' => $this->Catalog_model->get_book_digital_assets((int) $id),
			'reference_options' => $this->book_item_reference_options(),
			'can_create_item' => $this->can('catalog.index', 'create'),
			'can_edit_item' => $this->can('catalog.index', 'edit'),
			'can_delete_item' => $this->can('catalog.index', 'delete'),
		]);
	}

	public function create()
	{
		$this->require_permission('catalog.index', 'create');

		$this->render('catalog/form', [
			'title' => 'Tambah Katalog',
			'action' => 'catalog/store',
			'book' => null,
			'authors' => [],
			'subjects' => [],
			'content_categories' => $this->Catalog_model->get_content_categories(true),
			'classification_masters' => $this->Catalog_model->get_classification_masters(true),
		]);
	}

	public function store()
	{
		$this->require_permission('catalog.index', 'create');

		try {
			$book_id = $this->Catalog_model->create_book($this->book_input(), (int) ($this->current_user['id'] ?? 0));
			$this->audit_event('catalog.create', 'books', $book_id, null, $this->book_input());
			$this->session->set_flashdata('success', 'Katalog baru berhasil disimpan.');
			redirect('catalog/detail/' . $book_id);
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
			redirect('catalog/create');
		}
	}

	public function edit($id)
	{
		$this->require_permission('catalog.index', 'edit');

		$book = $this->Catalog_model->get_book((int) $id, $this->current_library_scope_id());
		if (! $book) {
			show_404();
			return;
		}

		$this->render('catalog/form', [
			'title' => 'Edit Katalog',
			'action' => 'catalog/update/' . (int) $id,
			'book' => $book,
			'authors' => $this->Catalog_model->get_book_authors((int) $id),
			'subjects' => $this->Catalog_model->get_book_subjects((int) $id),
			'content_categories' => $this->Catalog_model->get_content_categories(true),
			'classification_masters' => $this->Catalog_model->get_classification_masters(true),
		]);
	}

	public function update($id)
	{
		$this->require_permission('catalog.index', 'edit');

		$before = $this->Catalog_model->get_book((int) $id, $this->current_library_scope_id());
		if (! $before) {
			show_404();
			return;
		}

		try {
			$this->Catalog_model->update_book((int) $id, $this->book_input(), (int) ($this->current_user['id'] ?? 0));
			$this->audit_event('catalog.update', 'books', (int) $id, $before, $this->book_input());
			$this->session->set_flashdata('success', 'Katalog berhasil diperbarui.');
			redirect('catalog/detail/' . (int) $id);
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
			redirect('catalog/edit/' . (int) $id);
		}
	}

	public function delete($id)
	{
		$this->require_permission('catalog.index', 'delete');

		$before = $this->Catalog_model->get_book((int) $id, $this->current_library_scope_id());
		if (! $before) {
			show_404();
			return;
		}

		$this->Catalog_model->soft_delete_book((int) $id, (int) ($this->current_user['id'] ?? 0));
		$this->audit_event('catalog.delete', 'books', (int) $id, $before, ['deleted_at' => date('Y-m-d H:i:s')]);
		$this->session->set_flashdata('success', 'Katalog disembunyikan dari data aktif.');
		redirect('catalog');
	}

	public function store_item($book_id)
	{
		$this->require_permission('catalog.index', 'create');

		$book = $this->Catalog_model->get_book((int) $book_id, $this->current_library_scope_id());
		if (! $book) {
			show_404();
			return;
		}

		try {
			$item_id = $this->Catalog_model->create_book_item((int) $book_id, $this->book_item_input(), $this->current_library_scope_id());
			$this->audit_event('catalog.item_create', 'book_items', $item_id, null, $this->book_item_input());
			$this->session->set_flashdata('success', 'Eksemplar berhasil ditambahkan.');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
		}

		redirect('catalog/detail/' . (int) $book_id);
	}

	public function update_item($book_id, $item_id)
	{
		$this->require_permission('catalog.index', 'edit');

		$before = $this->Catalog_model->get_book_item((int) $item_id, (int) $book_id, $this->current_library_scope_id());
		if (! $before) {
			show_404();
			return;
		}

		try {
			$this->Catalog_model->update_book_item((int) $item_id, (int) $book_id, $this->book_item_input(), $this->current_library_scope_id());
			$this->audit_event('catalog.item_update', 'book_items', (int) $item_id, $before, $this->book_item_input());
			$this->session->set_flashdata('success', 'Eksemplar berhasil diperbarui.');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
		}

		redirect('catalog/detail/' . (int) $book_id);
	}

	public function delete_item($book_id, $item_id)
	{
		$this->require_permission('catalog.index', 'delete');

		$before = $this->Catalog_model->get_book_item((int) $item_id, (int) $book_id, $this->current_library_scope_id());
		if (! $before) {
			show_404();
			return;
		}

		$this->Catalog_model->soft_delete_book_item((int) $item_id, (int) $book_id, $this->current_library_scope_id());
		$this->audit_event('catalog.item_delete', 'book_items', (int) $item_id, $before, ['deleted_at' => date('Y-m-d H:i:s')]);
		$this->session->set_flashdata('success', 'Eksemplar dinonaktifkan dari data aktif.');
		redirect('catalog/detail/' . (int) $book_id);
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

	public function requests()
	{
		$this->require_permission('catalog.requests', 'view');

		$filters = [
			'q' => $this->input->get('q', true),
			'status' => $this->input->get('status', true),
			'request_type' => $this->input->get('request_type', true),
		];
		$per_page = (int) $this->input->get('per_page', true);
		$per_page = in_array($per_page, [10, 25, 50, 100], true) ? $per_page : 25;
		$page = max(1, (int) $this->input->get('page', true));
		$total_rows = $this->Catalog_model->count_book_requests($filters);
		$total_pages = max(1, (int) ceil($total_rows / $per_page));
		$page = min($page, $total_pages);
		$offset = ($page - 1) * $per_page;

		$this->render('catalog/requests', [
			'title' => 'Request Buku',
			'requests' => $this->Catalog_model->get_book_requests($filters, $per_page, $offset),
			'filters' => [
				'q' => $filters['q'],
				'status' => $filters['status'],
				'request_type' => $filters['request_type'],
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

	public function update_request($id)
	{
		$this->require_permission('catalog.requests', 'approve');

		$this->Catalog_model->update_book_request_status(
			(int) $id,
			(string) $this->input->post('status', true),
			$this->input->post('admin_note', true),
			(int) ($this->current_user['id'] ?? 0)
		);
		$this->audit_event('catalog.request_update', 'book_requests', (int) $id, null, $this->input->post(null, true));
		$this->session->set_flashdata('success', 'Status request buku diperbarui.');
		redirect('catalog/requests');
	}

	private function book_input()
	{
		return [
			'title' => $this->input->post('title', true),
			'subtitle' => $this->input->post('subtitle', true),
			'statement_responsibility' => $this->input->post('statement_responsibility', true),
			'authors' => $this->input->post('authors', true),
			'subjects' => $this->input->post('subjects', true),
			'edition' => $this->input->post('edition', true),
			'publish_place' => $this->input->post('publish_place', true),
			'publisher' => $this->input->post('publisher', true),
			'publish_year' => $this->input->post('publish_year', true),
			'isbn' => $this->input->post('isbn', true),
			'classification' => $this->input->post('classification', true),
			'content_category_id' => $this->input->post('content_category_id', true),
			'content_classification_id' => $this->input->post('content_classification_id', true),
			'call_number' => $this->input->post('call_number', true),
			'language' => $this->input->post('language', true),
			'physical_description' => $this->input->post('physical_description', true),
			'abstract' => $this->input->post('abstract', true),
			'cover_path' => $this->input->post('cover_path', true),
			'status' => $this->input->post('status', true),
		];
	}

	private function book_item_input()
	{
		return [
			'barcode' => $this->input->post('barcode', true),
			'item_code' => $this->input->post('item_code', true),
			'inventory_number' => $this->input->post('inventory_number', true),
			'call_number' => $this->input->post('call_number', true),
			'source_location_library_id' => $this->input->post('source_location_library_id', true),
			'source_location_id' => $this->input->post('source_location_id', true),
			'source_rule_id' => $this->input->post('source_rule_id', true),
			'source_category_id' => $this->input->post('source_category_id', true),
			'source_media_id' => $this->input->post('source_media_id', true),
			'source_collection_source_id' => $this->input->post('source_collection_source_id', true),
			'source_status_id' => $this->input->post('source_status_id', true),
			'status' => $this->input->post('status', true),
			'is_public' => $this->input->post('is_public') ? 1 : 0,
		];
	}

	private function book_item_reference_options()
	{
		return [
			'location_libraries' => $this->Catalog_model->get_master_references('location_library'),
			'locations' => $this->Catalog_model->get_master_references('locations'),
			'rules' => $this->Catalog_model->get_master_references('collectionrules'),
			'categories' => $this->Catalog_model->get_master_references('collectioncategorys'),
			'medias' => $this->Catalog_model->get_master_references('collectionmedias'),
			'sources' => $this->Catalog_model->get_master_references('collectionsources'),
			'statuses' => $this->Catalog_model->get_master_references('collectionstatus'),
		];
	}
}
