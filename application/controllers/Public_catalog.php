<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Public_catalog extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Catalog_model');
		$this->load->model('Member_model');
	}

	public function index()
	{
		$filters = [
			'q' => $this->input->get('q', true),
			'content_category_id' => $this->input->get('content_category_id', true),
			'content_classification_id' => $this->input->get('content_classification_id', true),
			'category' => $this->input->get('category', true),
			'media' => $this->input->get('media', true),
			'rule' => $this->input->get('rule', true),
			'location_library' => $this->input->get('location_library', true),
			'publish_year' => $this->input->get('publish_year', true),
			'availability' => $this->input->get('availability', true),
		];
		$per_page = (int) $this->input->get('per_page', true);
		$per_page = in_array($per_page, [12, 24, 48], true) ? $per_page : 12;
		$page = max(1, (int) $this->input->get('page', true));
		$total_rows = $this->Catalog_model->count_public_books($filters);
		$total_pages = max(1, (int) ceil($total_rows / $per_page));
		$page = min($page, $total_pages);
		$offset = ($page - 1) * $per_page;

		$this->load->view('public_catalog/index', [
			'title' => 'Katalog Publik - Pustaka Digital Rembang',
			'filters' => [
				'q' => $filters['q'],
				'content_category_id' => $filters['content_category_id'],
				'content_classification_id' => $filters['content_classification_id'],
				'category' => $filters['category'],
				'media' => $filters['media'],
				'rule' => $filters['rule'],
				'location_library' => $filters['location_library'],
				'publish_year' => $filters['publish_year'],
				'availability' => $filters['availability'],
				'per_page' => $per_page,
				'page' => $page,
			],
			'books' => $this->Catalog_model->get_public_books($filters, $per_page, $offset),
			'filter_options' => $this->Catalog_model->public_filter_options($filters),
			'current_member' => $this->current_member(),
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
		$book = $this->Catalog_model->get_public_book((int) $id);
		if (! $book) {
			show_404();
			return;
		}

		$this->load->view('public_catalog/detail', [
			'title' => $book['title'] . ' - Katalog Publik',
			'book' => $book,
			'authors' => $this->Catalog_model->get_book_authors((int) $id),
			'subjects' => $this->Catalog_model->get_book_subjects((int) $id),
			'items' => $this->Catalog_model->get_public_book_items((int) $id, 80),
			'digital_assets' => $this->Catalog_model->get_public_digital_assets((int) $id),
			'current_member' => $this->current_member(),
		]);
	}

	public function request($id)
	{
		try {
			$result = $this->Catalog_model->create_book_request((int) $id, [
				'requester_name' => $this->input->post('requester_name', true),
				'requester_email' => $this->input->post('requester_email', true),
				'requester_phone' => $this->input->post('requester_phone', true),
				'message' => $this->input->post('message', true),
			], $this->current_member());
			$this->session->set_flashdata('success', 'Request buku berhasil dikirim. Kode: ' . $result['code']);
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
		}

		redirect('katalog/detail/' . (int) $id . '#request-buku');
	}

	private function current_member()
	{
		$user = (array) $this->session->userdata('auth_user');
		if (empty($user['id'])) {
			return null;
		}

		return $this->Member_model->get_member_by_auth_user_id((int) $user['id']);
	}
}
