<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Regions extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Region_model');
	}

	public function index()
	{
		$this->require_permission('regions.index', 'view');

		$district_id = (int) $this->input->get('district_id', true);
		$filters = [
			'q' => $this->input->get('q', true),
			'area_type' => $this->input->get('area_type', true),
		];
		$per_page = (int) $this->input->get('per_page', true);
		$per_page = in_array($per_page, [10, 25, 50, 100], true) ? $per_page : 25;
		$page = max(1, (int) $this->input->get('page', true));
		$total_villages = $this->Region_model->count_villages($district_id ?: null, false, $filters);
		$total_pages = max(1, (int) ceil($total_villages / $per_page));
		$page = min($page, $total_pages);
		$offset = ($page - 1) * $per_page;

		$this->render('regions/index', [
			'title' => 'Master Wilayah',
			'districts' => $this->Region_model->get_districts(false),
			'active_districts' => $this->Region_model->get_districts(true),
			'villages' => $this->Region_model->get_villages($district_id ?: null, false, $filters, $per_page, $offset),
			'edit_district' => $this->get_edit_district(),
			'edit_village' => $this->get_edit_village(),
			'filters' => [
				'district_id' => $district_id,
				'q' => $filters['q'],
				'area_type' => $filters['area_type'],
				'per_page' => $per_page,
				'page' => $page,
			],
			'pagination' => [
				'total_rows' => $total_villages,
				'total_pages' => $total_pages,
				'page' => $page,
				'per_page' => $per_page,
				'offset' => $offset,
			],
			'can_create' => $this->can('regions.index', 'create'),
			'can_edit' => $this->can('regions.index', 'edit'),
		]);
	}

	public function store_district()
	{
		$this->require_permission('regions.index', 'create');
		$this->save_district();
	}

	public function update_district($id)
	{
		$this->require_permission('regions.index', 'edit');
		if (! $this->Region_model->get_district((int) $id)) {
			show_404();
			return;
		}
		$this->save_district((int) $id);
	}

	public function toggle_district($id)
	{
		$this->require_permission('regions.index', 'edit');
		$old = $this->Region_model->toggle_district((int) $id);
		if (! $old) {
			show_404();
			return;
		}

		$this->audit_event('region.district_toggled', 'ref_districts', (int) $id, $old, [
			'is_active' => (int) $old['is_active'] === 1 ? 0 : 1,
		]);
		$this->session->set_flashdata('success', 'Status kecamatan berhasil diperbarui.');
		redirect('regions');
	}

	public function store_village()
	{
		$this->require_permission('regions.index', 'create');
		$this->save_village();
	}

	public function update_village($id)
	{
		$this->require_permission('regions.index', 'edit');
		if (! $this->Region_model->get_village((int) $id)) {
			show_404();
			return;
		}
		$this->save_village((int) $id);
	}

	public function toggle_village($id)
	{
		$this->require_permission('regions.index', 'edit');
		$old = $this->Region_model->toggle_village((int) $id);
		if (! $old) {
			show_404();
			return;
		}

		$this->audit_event('region.village_toggled', 'ref_villages', (int) $id, $old, [
			'is_active' => (int) $old['is_active'] === 1 ? 0 : 1,
		]);
		$this->session->set_flashdata('success', 'Status Desa / Kelurahan berhasil diperbarui.');
		redirect('regions?district_id=' . (int) $old['district_id']);
	}

	private function save_district($id = null)
	{
		$code = trim((string) $this->input->post('code', true));
		$name = trim((string) $this->input->post('name', true));

		if (! preg_match('/^\d{1,2}$/', $code) || $name === '') {
			$this->session->set_flashdata('error', 'Kode kecamatan harus 1-2 digit dan nama wajib diisi.');
			redirect('regions' . ($id ? '?edit_district_id=' . (int) $id : ''));
		}

		$code = str_pad($code, 2, '0', STR_PAD_LEFT);
		if ($this->Region_model->district_code_exists($code, $id)) {
			$this->session->set_flashdata('error', 'Kode kecamatan sudah digunakan.');
			redirect('regions' . ($id ? '?edit_district_id=' . (int) $id : ''));
		}

		$payload = [
			'code' => $code,
			'name' => $name,
			'is_active' => $this->input->post('is_active') ? 1 : 0,
		];

		$old = $id ? $this->Region_model->get_district($id) : null;
		$district_id = $this->Region_model->save_district($payload, $id);
		$this->audit_event($id ? 'region.district_updated' : 'region.district_created', 'ref_districts', $district_id, $old, $this->Region_model->get_district($district_id));

		$this->session->set_flashdata('success', 'Data kecamatan berhasil disimpan.');
		redirect('regions');
	}

	private function save_village($id = null)
	{
		$district_id = (int) $this->input->post('district_id', true);
		$code = trim((string) $this->input->post('code', true));
		$name = trim((string) $this->input->post('name', true));
		$area_type = (string) $this->input->post('area_type', true);

		if ($district_id <= 0 || $code === '' || $name === '') {
			$this->session->set_flashdata('error', 'Kecamatan, kode, dan nama Desa / Kelurahan wajib diisi.');
			redirect('regions' . ($id ? '?edit_village_id=' . (int) $id : ''));
		}

		if ($this->Region_model->village_code_exists($code, $id)) {
			$this->session->set_flashdata('error', 'Kode Desa / Kelurahan sudah digunakan.');
			redirect('regions' . ($id ? '?edit_village_id=' . (int) $id : ''));
		}

		$payload = [
			'district_id' => $district_id,
			'code' => $code,
			'name' => $name,
			'area_type' => $area_type,
			'is_active' => $this->input->post('is_active') ? 1 : 0,
		];

		$old = $id ? $this->Region_model->get_village($id) : null;
		$village_id = $this->Region_model->save_village($payload, $id);
		$this->audit_event($id ? 'region.village_updated' : 'region.village_created', 'ref_villages', $village_id, $old, $this->Region_model->get_village($village_id));

		$this->session->set_flashdata('success', 'Data Desa / Kelurahan berhasil disimpan.');
		redirect('regions?district_id=' . $district_id);
	}

	private function get_edit_district()
	{
		$id = (int) $this->input->get('edit_district_id', true);
		return $id > 0 ? $this->Region_model->get_district($id) : null;
	}

	private function get_edit_village()
	{
		$id = (int) $this->input->get('edit_village_id', true);
		return $id > 0 ? $this->Region_model->get_village($id) : null;
	}
}
