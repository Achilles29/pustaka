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
			'title' => 'Master Buku',
			'categories' => $this->Catalog_model->get_content_categories(false),
			'classifications' => $this->Catalog_model->get_classification_masters(false),
			'active_tab' => $this->input->get('tab', true) === 'classifications' ? 'classifications' : 'categories',
			'can_create' => $this->can('catalog.masters', 'create'),
			'can_edit' => $this->can('catalog.masters', 'edit'),
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
