<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Region_model extends CI_Model
{
	public function get_districts($active_only = true, array $filters = [])
	{
		$this->db->from('ref_districts');

		if ($active_only) {
			$this->db->where('is_active', 1);
		}

		if (! empty($filters['q'])) {
			$q = trim((string) $filters['q']);
			$this->db->group_start()
				->like('code', $q)
				->or_like('full_code', $q)
				->or_like('name', $q)
				->group_end();
		}

		return $this->db
			->order_by('code', 'ASC')
			->get()
			->result_array();
	}

	public function get_district($id)
	{
		return $this->db
			->from('ref_districts')
			->where('id', (int) $id)
			->get()
			->row_array();
	}

	public function get_villages($district_id = null, $active_only = true, array $filters = [], $limit = null, $offset = 0)
	{
		$this->build_village_query($district_id, $active_only, $filters);

		$this->db
			->select('v.*, d.name AS district_name, d.full_code AS district_full_code')
			->order_by('d.code', 'ASC')
			->order_by('v.name', 'ASC');

		if ($limit !== null) {
			$this->db->limit((int) $limit, (int) $offset);
		}

		return $this->db->get()->result_array();
	}

	public function count_villages($district_id = null, $active_only = true, array $filters = [])
	{
		$this->build_village_query($district_id, $active_only, $filters);

		return (int) $this->db->count_all_results();
	}

	private function build_village_query($district_id = null, $active_only = true, array $filters = [])
	{
		$this->db
			->from('ref_villages v')
			->join('ref_districts d', 'd.id = v.district_id');

		if (! empty($district_id)) {
			$this->db->where('v.district_id', (int) $district_id);
		}

		if ($active_only) {
			$this->db->where('v.is_active', 1);
		}

		if (! empty($filters['area_type'])) {
			$this->db->where('v.area_type', $filters['area_type']);
		}

		if (! empty($filters['q'])) {
			$q = trim((string) $filters['q']);
			$this->db->group_start()
				->like('v.code', $q)
				->or_like('v.name', $q)
				->or_like('d.name', $q)
				->or_like('d.full_code', $q)
				->group_end();
		}
	}

	public function get_village($id)
	{
		return $this->db
			->select('v.*, d.name AS district_name, d.full_code AS district_full_code')
			->from('ref_villages v')
			->join('ref_districts d', 'd.id = v.district_id')
			->where('v.id', (int) $id)
			->get()
			->row_array();
	}

	public function village_payload()
	{
		$payload = [];
		foreach ($this->get_villages() as $village) {
			$district_id = (int) $village['district_id'];
			if (! isset($payload[$district_id])) {
				$payload[$district_id] = [];
			}
			$payload[$district_id][] = [
				'id' => (int) $village['id'],
				'name' => $village['name'],
				'area_type' => $village['area_type'],
			];
		}

		return $payload;
	}

	public function district_code_exists($code, $exclude_id = null)
	{
		$this->db->from('ref_districts')->where('code', $code);
		if ($exclude_id !== null) {
			$this->db->where('id !=', (int) $exclude_id);
		}

		return $this->db->count_all_results() > 0;
	}

	public function village_code_exists($code, $exclude_id = null)
	{
		$this->db->from('ref_villages')->where('code', $code);
		if ($exclude_id !== null) {
			$this->db->where('id !=', (int) $exclude_id);
		}

		return $this->db->count_all_results() > 0;
	}

	public function save_district(array $data, $id = null)
	{
		$payload = $this->clean_district_payload($data);

		if ($id) {
			$old = $this->get_district((int) $id);
			$this->db->where('id', (int) $id)->update('ref_districts', $payload);
			if ($old && $old['code'] !== $payload['code']) {
				$this->db
					->where('district_id', (int) $id)
					->update('ref_villages', ['district_code' => $payload['code']]);
			}
			return (int) $id;
		}

		$this->db->insert('ref_districts', $payload);
		return (int) $this->db->insert_id();
	}

	public function save_village(array $data, $id = null)
	{
		$payload = $this->clean_village_payload($data);

		if ($id) {
			$this->db->where('id', (int) $id)->update('ref_villages', $payload);
			return (int) $id;
		}

		$this->db->insert('ref_villages', $payload);
		return (int) $this->db->insert_id();
	}

	public function toggle_district($id)
	{
		$row = $this->get_district((int) $id);
		if (! $row) {
			return false;
		}

		$this->db
			->where('id', (int) $id)
			->update('ref_districts', ['is_active' => (int) $row['is_active'] === 1 ? 0 : 1]);

		return $row;
	}

	public function toggle_village($id)
	{
		$row = $this->get_village((int) $id);
		if (! $row) {
			return false;
		}

		$this->db
			->where('id', (int) $id)
			->update('ref_villages', ['is_active' => (int) $row['is_active'] === 1 ? 0 : 1]);

		return $row;
	}

	private function clean_district_payload(array $data)
	{
		$code = str_pad(substr(trim((string) $data['code']), -2), 2, '0', STR_PAD_LEFT);

		return [
			'province_code' => '33',
			'regency_code' => '17',
			'code' => $code,
			'full_code' => '33.17.' . $code,
			'name' => trim((string) $data['name']),
			'is_active' => empty($data['is_active']) ? 0 : 1,
		];
	}

	private function clean_village_payload(array $data)
	{
		$district = $this->get_district((int) $data['district_id']);

		return [
			'district_id' => (int) $data['district_id'],
			'province_code' => '33',
			'regency_code' => '17',
			'district_code' => $district['code'] ?? null,
			'code' => trim((string) $data['code']),
			'area_type' => in_array(($data['area_type'] ?? 'desa'), ['desa', 'kelurahan'], true) ? $data['area_type'] : 'desa',
			'name' => trim((string) $data['name']),
			'is_active' => empty($data['is_active']) ? 0 : 1,
		];
	}
}
