<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Rak publik khusus koleksi yang telah ditandai Buku Pelajaran. */
class Public_textbooks extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Catalog_model');
	}

	public function index()
	{
		$filters = [
			'q' => $this->input->get('q', true),
			'textbook_only' => true,
			'textbook_grade_id' => (int) $this->input->get('grade', true),
			'textbook_subject_id' => (int) $this->input->get('subject', true),
			'availability' => $this->input->get('availability', true),
		];
		$per_page = (int) $this->input->get('per_page', true);
		$per_page = in_array($per_page, [12, 24, 48], true) ? $per_page : 24;
		$page = max(1, (int) $this->input->get('page', true));
		$total_rows = $this->Catalog_model->count_public_books($filters);
		$total_pages = max(1, (int) ceil($total_rows / $per_page));
		$page = min($page, $total_pages);

		$this->load->view('public_textbooks/index', [
			'title' => 'Buku Pelajaran - Pustaka Digital Rembang',
			'filters' => $filters + ['per_page' => $per_page, 'page' => $page],
			'filter_options' => $this->Catalog_model->get_textbook_filter_options(),
			'books' => $this->Catalog_model->get_public_books($filters, $per_page, ($page - 1) * $per_page),
			'pagination' => ['total_rows' => $total_rows, 'total_pages' => $total_pages, 'page' => $page, 'per_page' => $per_page, 'offset' => ($page - 1) * $per_page],
		]);
	}
}
