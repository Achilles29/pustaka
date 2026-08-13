<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller
{
	public function index()
	{
		$this->load->model('Library_model');
		$this->load->model('Catalog_model');
		$this->load->model('Manuscript_model');
		$this->load->model('Event_model');

		$libraries = $this->db->table_exists('libraries')
			? $this->Library_model->get_libraries(['status' => 'active'])
			: [];

		$service_counts = [
			'catalogs' => $this->count_local_table('books'),
			'members' => $this->count_local_table('members'),
			'collections' => $this->count_local_table('book_items'),
		];

		$this->load->view('home/landing', [
			'title' => 'Pustaka Digital Rembang',
			'libraries' => $libraries,
			'map_payload' => $this->Library_model->map_payload($libraries),
			'service_counts' => $service_counts,
			'public_catalog_count' => $this->Catalog_model->count_public_books(['availability' => 'with_items']),
			'public_digital_count' => $this->Catalog_model->count_public_books(['availability' => 'digital']),
			'catalog_preview' => $this->Catalog_model->get_random_public_books(['availability' => 'with_items'], 9),
			'manuscript_preview' => $this->Manuscript_model->featured(3),
			'manuscript_stats' => $this->Manuscript_model->stats(),
			'upcoming_events' => $this->Event_model->get_public_events(['time' => 'upcoming'], 3),
		]);
	}

	private function count_local_table($table)
	{
		return $this->db->table_exists($table) ? $this->db->count_all($table) : 0;
	}
}
