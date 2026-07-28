<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Libraries extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Library_model');
		$this->load->library('upload');
	}

	public function index()
	{
		$this->require_permission('libraries.index', 'view');

		$filters = [
			'q' => $this->input->get('q', true),
			'type_id' => $this->input->get('type_id', true),
			'status' => $this->input->get('status', true),
		];
		$libraries = $this->Library_model->get_libraries($filters);

		$this->render('libraries/index', [
			'title' => 'Perpustakaan GIS',
			'types' => $this->Library_model->get_types(),
			'libraries' => $libraries,
			'map_payload' => $this->Library_model->map_payload($libraries),
			'filters' => $filters,
		]);
	}

	public function create()
	{
		$this->require_permission('libraries.index', 'create');

		$this->render('libraries/form', [
			'title' => 'Tambah Perpustakaan',
			'types' => $this->Library_model->get_types(),
			'library' => null,
			'photos' => [],
			'action' => base_url('libraries/store'),
		]);
	}

	public function store()
	{
		$this->require_permission('libraries.index', 'create');
		$library_id = $this->Library_model->create_library($this->payload_from_post('create'));
		$this->handle_photo_upload($library_id);

		$this->session->set_flashdata('success', 'Perpustakaan berhasil ditambahkan.');
		redirect('libraries');
	}

	public function edit($id)
	{
		$this->require_permission('libraries.index', 'edit');
		$library = $this->Library_model->get_library((int) $id);
		if (! $library) {
			show_404();
			return;
		}

		$this->render('libraries/form', [
			'title' => 'Edit Perpustakaan',
			'types' => $this->Library_model->get_types(),
			'library' => $library,
			'photos' => $this->Library_model->get_photos((int) $id),
			'action' => base_url('libraries/update/' . (int) $id),
		]);
	}

	public function update($id)
	{
		$this->require_permission('libraries.index', 'edit');
		$library = $this->Library_model->get_library((int) $id);
		if (! $library) {
			show_404();
			return;
		}

		$this->Library_model->update_library((int) $id, $this->payload_from_post('update'));
		$this->handle_photo_upload((int) $id);

		$this->session->set_flashdata('success', 'Perpustakaan berhasil diperbarui.');
		redirect('libraries/edit/' . (int) $id);
	}

	public function toggle($id)
	{
		$this->require_permission('libraries.index', 'edit');
		$library = $this->Library_model->get_library((int) $id);
		if (! $library) {
			show_404();
			return;
		}

		$new_status = $library['status'] === 'active' ? 'inactive' : 'active';
		$this->Library_model->set_status((int) $id, $new_status, (int) $this->current_user['id']);

		$this->session->set_flashdata('success', 'Status perpustakaan berhasil diperbarui.');
		redirect('libraries');
	}

	private function payload_from_post($mode)
	{
		return [
			'library_type_id' => $this->input->post('library_type_id', true),
			'code' => $this->input->post('code', true),
			'name' => $this->input->post('name', true),
			'manager_name' => $this->input->post('manager_name', true),
			'address' => $this->input->post('address', true),
			'district' => $this->input->post('district', true),
			'village' => $this->input->post('village', true),
			'phone' => $this->input->post('phone', true),
			'email' => $this->input->post('email', true),
			'website' => $this->input->post('website', true),
			'opening_hours' => $this->input->post('opening_hours', true),
			'latitude' => $this->input->post('latitude', true),
			'longitude' => $this->input->post('longitude', true),
			'service_radius_meters' => $this->input->post('service_radius_meters', true),
			'description' => $this->input->post('description', true),
			'facilities' => $this->input->post('facilities', true),
			'status' => $this->input->post('status', true),
			'created_by' => $mode === 'create' ? (int) $this->current_user['id'] : null,
			'updated_by' => (int) $this->current_user['id'],
		];
	}

	private function handle_photo_upload($library_id)
	{
		if (empty($_FILES['photo']['name'])) {
			return;
		}

		$config = [
			'upload_path' => FCPATH . 'assets/uploads/libraries/',
			'allowed_types' => 'jpg|jpeg|png|webp',
			'max_size' => 4096,
			'encrypt_name' => true,
		];

		$this->upload->initialize($config);
		if (! $this->upload->do_upload('photo')) {
			$this->session->set_flashdata('error', strip_tags($this->upload->display_errors('', '')));
			return;
		}

		$file = $this->upload->data();
		$this->Library_model->add_photo(
			(int) $library_id,
			'assets/uploads/libraries/' . $file['file_name'],
			$this->input->post('photo_caption', true),
			(int) $this->current_user['id']
		);
	}
}
