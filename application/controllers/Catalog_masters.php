<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Catalog_masters extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Catalog_model');
	}

	public function index()
	{
		$this->require_permission('catalog.masters', 'view');

		$this->render('catalog/masters', [
			'title' => 'Master Katalog',
			'categories' => $this->Catalog_model->get_content_categories(false),
			'classifications' => $this->Catalog_model->get_classification_masters(false),
			'collection_types' => $this->Catalog_model->get_collection_types(false),
			'active_tab' => in_array($this->input->get('tab', true), ['categories', 'classifications', 'collection_types'], true) ? $this->input->get('tab', true) : 'categories',
			'can_create' => $this->can('catalog.masters', 'create'),
			'can_edit' => $this->can('catalog.masters', 'edit'),
			'can_delete' => $this->can('catalog.masters', 'delete'),
		]);
	}

	public function store_category()
	{
		$this->require_permission('catalog.masters', 'create');
		$this->save_category();
	}

	public function update_category($id)
	{
		$this->require_permission('catalog.masters', 'edit');
		$this->save_category((int) $id);
	}

	public function store_classification()
	{
		$this->require_permission('catalog.masters', 'create');
		$this->save_classification();
	}

	public function update_classification($id)
	{
		$this->require_permission('catalog.masters', 'edit');
		$this->save_classification((int) $id);
	}

	public function store_collection_type()
	{
		$this->require_permission('catalog.masters', 'create');
		$this->save_collection_type();
	}

	public function update_collection_type($id)
	{
		$this->require_permission('catalog.masters', 'edit');
		$this->save_collection_type((int) $id);
	}

	public function delete_collection_type($id)
	{
		$this->require_permission('catalog.masters', 'delete');
		try {
			$this->Catalog_model->delete_collection_type((int) $id);
			$this->audit_event('catalog.master_collection_type_delete', 'book_collection_types', (int) $id, null, null);
			$this->session->set_flashdata('success', 'Master jenis koleksi berhasil dihapus.');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
		}
		redirect('catalog/masters?tab=collection_types');
	}

	private function save_category($id = null)
	{
		try {
			$saved_id = $this->Catalog_model->save_content_category($this->master_input(), $id);
			$this->audit_event($id ? 'catalog.master_category_update' : 'catalog.master_category_create', 'book_content_categories', $saved_id, null, $this->input->post(null, true));
			$this->session->set_flashdata('success', 'Master kategori buku berhasil disimpan.');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
		}

		redirect('catalog/masters?tab=categories');
	}

	private function save_classification($id = null)
	{
		try {
			$saved_id = $this->Catalog_model->save_classification_master($this->master_input(), $id);
			$this->audit_event($id ? 'catalog.master_classification_update' : 'catalog.master_classification_create', 'book_classification_masters', $saved_id, null, $this->input->post(null, true));
			$this->session->set_flashdata('success', 'Master klasifikasi buku berhasil disimpan.');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
		}

		redirect('catalog/masters?tab=classifications');
	}

	private function save_collection_type($id = null)
	{
		try {
			$saved_id = $this->Catalog_model->save_collection_type($this->master_input(), $id);
			$this->audit_event($id ? 'catalog.master_collection_type_update' : 'catalog.master_collection_type_create', 'book_collection_types', $saved_id, null, $this->input->post(null, true));
			$this->session->set_flashdata('success', 'Master jenis koleksi berhasil disimpan.');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
		}
		redirect('catalog/masters?tab=collection_types');
	}

	private function master_input()
	{
		return [
			'code' => $this->input->post('code', true),
			'name' => $this->input->post('name', true),
			'description' => $this->input->post('description', true),
			'sort_order' => $this->input->post('sort_order', true),
			'is_active' => $this->input->post('is_active') ? 1 : 0,
		];
	}
}
