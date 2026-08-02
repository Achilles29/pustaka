<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller
{
	public function index()
	{
		$this->load->model('Library_model');
		$this->load->model('Catalog_model');

		$libraries = $this->db->table_exists('libraries')
			? $this->Library_model->get_libraries(['status' => 'active'])
			: [];

		$source_db = $this->load->database('inlislite', TRUE);
		$source_counts = [
			'catalogs' => $source_db->table_exists('catalogs') ? $source_db->count_all('catalogs') : 0,
			'members' => $source_db->table_exists('members') ? $source_db->count_all('members') : 0,
			'collections' => $source_db->table_exists('collections') ? $source_db->count_all('collections') : 0,
		];

		$this->load->view('home/landing', [
			'title' => 'Pustaka Digital Rembang',
			'libraries' => $libraries,
			'map_payload' => $this->Library_model->map_payload($libraries),
			'source_counts' => $source_counts,
			'public_catalog_count' => $this->Catalog_model->count_public_books(['availability' => 'with_items']),
			'catalog_preview' => $this->Catalog_model->get_public_books(['availability' => 'available'], 4, 0),
		]);
	}
}
