<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Libraries extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Library_model');
		$this->load->model('Region_model');
		$this->load->library('upload');
	}

	public function index()
	{
		$this->require_permission('libraries.index', 'view');

		$filters = [
			'q' => $this->input->get('q', true),
			'type_id' => $this->input->get('type_id', true),
			'district_id' => $this->input->get('district_id', true),
			'status' => $this->input->get('status', true),
			'library_id' => $this->current_library_scope_id(),
		];
		$per_page = (int) $this->input->get('per_page', true);
		$per_page = in_array($per_page, [10, 25, 50, 100], true) ? $per_page : 25;
		$page = max(1, (int) $this->input->get('page', true));
		$total_libraries = $this->Library_model->count_libraries($filters);
		$total_pages = max(1, (int) ceil($total_libraries / $per_page));
		$page = min($page, $total_pages);
		$offset = ($page - 1) * $per_page;
		$libraries = $this->Library_model->get_libraries($filters, $per_page, $offset);

		$this->render('libraries/index', [
			'title' => 'Perpustakaan GIS',
			'types' => $this->Library_model->get_types(),
			'districts' => $this->Region_model->get_districts(),
			'libraries' => $libraries,
			'map_payload' => $this->Library_model->map_payload($libraries),
			'filters' => $filters,
			'per_page' => $per_page,
			'page' => $page,
			'total_pages' => $total_pages,
			'total_libraries' => $total_libraries,
			'can_create' => $this->can('libraries.index', 'create'),
			'can_edit' => $this->can('libraries.index', 'edit'),
		]);
	}

	public function create()
	{
		$this->require_permission('libraries.index', 'create');

		$this->render('libraries/form', [
			'title' => 'Tambah Perpustakaan',
			'types' => $this->Library_model->get_types(),
			'districts' => $this->Region_model->get_districts(),
			'villages' => $this->Region_model->village_payload(),
			'library' => null,
			'photos' => [],
			'action' => base_url('libraries/store'),
		]);
	}

	public function store()
	{
		$this->require_permission('libraries.index', 'create');
		$library_id = $this->Library_model->create_library($this->payload_from_post('create'));
		$photo_id = $this->handle_photo_upload($library_id);

		$this->audit_event('library.created', 'library', $library_id, null, $this->Library_model->get_library($library_id));
		if ($photo_id) {
			$this->audit_event('library.photo_uploaded', 'library_photo', $photo_id, null, ['library_id' => $library_id]);
		}

		$this->session->set_flashdata('success', 'Perpustakaan berhasil ditambahkan.');
		redirect('libraries');
	}

	public function edit($id)
	{
		$this->require_permission('libraries.index', 'edit');
		$library = $this->Library_model->get_library((int) $id, $this->current_library_scope_id());
		if (! $library) {
			show_404();
			return;
		}

		$this->render('libraries/form', [
			'title' => 'Edit Perpustakaan',
			'types' => $this->Library_model->get_types(),
			'districts' => $this->Region_model->get_districts(),
			'villages' => $this->Region_model->village_payload(),
			'library' => $library,
			'photos' => $this->Library_model->get_photos((int) $id),
			'action' => base_url('libraries/update/' . (int) $id),
		]);
	}

	public function update($id)
	{
		$this->require_permission('libraries.index', 'edit');
		$library = $this->Library_model->get_library((int) $id, $this->current_library_scope_id());
		if (! $library) {
			show_404();
			return;
		}

		$this->Library_model->update_library((int) $id, $this->payload_from_post('update'));
		$photo_id = $this->handle_photo_upload((int) $id);
		$updated = $this->Library_model->get_library((int) $id);

		$this->audit_event('library.updated', 'library', (int) $id, $library, $updated);
		if ($photo_id) {
			$this->audit_event('library.photo_uploaded', 'library_photo', $photo_id, null, ['library_id' => (int) $id]);
		}

		$this->session->set_flashdata('success', 'Perpustakaan berhasil diperbarui.');
		redirect('libraries/edit/' . (int) $id);
	}

	public function toggle($id)
	{
		$this->require_permission('libraries.index', 'edit');
		$library = $this->Library_model->get_library((int) $id, $this->current_library_scope_id());
		if (! $library) {
			show_404();
			return;
		}

		$new_status = $library['status'] === 'active' ? 'inactive' : 'active';
		$this->Library_model->set_status((int) $id, $new_status, (int) $this->current_user['id']);
		$this->audit_event('library.status_changed', 'library', (int) $id, ['status' => $library['status']], ['status' => $new_status]);

		$this->session->set_flashdata('success', 'Status perpustakaan berhasil diperbarui.');
		redirect('libraries');
	}

	public function verify($id)
	{
		$this->require_permission('libraries.index', 'approve');
		$library = $this->Library_model->get_library((int) $id, $this->current_library_scope_id());
		if (! $library) {
			show_404();
			return;
		}

		$this->Library_model->verify_library((int) $id, (int) $this->current_user['id']);
		$this->audit_event('library.verified', 'library', (int) $id, ['is_verified' => (int) $library['is_verified']], ['is_verified' => 1]);

		$this->session->set_flashdata('success', 'Data perpustakaan berhasil diverifikasi.');
		redirect('libraries/edit/' . (int) $id);
	}

	public function set_cover($photo_id)
	{
		$this->require_permission('libraries.index', 'edit');
		$photo = $this->Library_model->get_photo((int) $photo_id, $this->current_library_scope_id());
		if (! $photo) {
			show_404();
			return;
		}

		$this->Library_model->set_cover_photo((int) $photo_id, (int) $this->current_user['id']);
		$this->audit_event('library.photo_cover_set', 'library_photo', (int) $photo_id, null, ['library_id' => (int) $photo['library_id']]);

		$this->session->set_flashdata('success', 'Foto utama berhasil diperbarui.');
		redirect('libraries/edit/' . (int) $photo['library_id']);
	}

	public function delete_photo($photo_id)
	{
		$this->require_permission('libraries.index', 'delete');
		$photo = $this->Library_model->get_photo((int) $photo_id, $this->current_library_scope_id());
		if (! $photo) {
			show_404();
			return;
		}

		$deleted = $this->Library_model->soft_delete_photo((int) $photo_id, (int) $this->current_user['id']);
		$this->audit_event('library.photo_deleted', 'library_photo', (int) $photo_id, $deleted ?: $photo, null);

		$this->session->set_flashdata('success', 'Foto perpustakaan berhasil dihapus dari galeri.');
		redirect('libraries/edit/' . (int) $photo['library_id']);
	}

	private function payload_from_post($mode)
	{
		$district_id = (int) $this->input->post('district_id', true);
		$village_id = (int) $this->input->post('village_id', true);
		$village = $village_id > 0 ? $this->Region_model->get_village($village_id) : null;
		$district = null;
		foreach ($this->Region_model->get_districts() as $row) {
			if ((int) $row['id'] === $district_id) {
				$district = $row;
				break;
			}
		}

		return [
			'library_type_id' => $this->input->post('library_type_id', true),
			'code' => $this->input->post('code', true),
			'name' => $this->input->post('name', true),
			'manager_name' => $this->input->post('manager_name', true),
			'address' => $this->input->post('address', true),
			'district_id' => $district_id,
			'village_id' => $village_id,
			'district' => $district['name'] ?? $this->input->post('district', true),
			'village' => $village['name'] ?? $this->input->post('village', true),
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
			'is_verified' => $this->input->post('is_verified') ? 1 : 0,
			'created_by' => $mode === 'create' ? (int) $this->current_user['id'] : null,
			'updated_by' => (int) $this->current_user['id'],
		];
	}

	private function handle_photo_upload($library_id)
	{
		if (empty($_FILES['photo']['name'])) {
			return 0;
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
			return 0;
		}

		$file = $this->upload->data();
		return $this->Library_model->add_photo(
			(int) $library_id,
			'assets/uploads/libraries/' . $file['file_name'],
			$this->input->post('photo_caption', true),
			(int) $this->current_user['id']
		);
	}
}
