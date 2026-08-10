<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Region_model extends CI_Model
{
	const REMBANG_REGENCY_CODE = '3317';

	/** Backward-compatible list for GIS and Rembang-only admin surfaces. */
	public function get_districts($active_only = true, array $filters = [])
	{
		$filters['regency_code'] = $filters['regency_code'] ?? self::REMBANG_REGENCY_CODE;
		return $this->district_rows($active_only, $filters);
	}

	public function get_provinces($active_only = true)
	{
		if ($active_only) {
			$this->db->where('is_active', 1);
		}
		return $this->db->from('ref_provinces')->order_by('name', 'ASC')->get()->result_array();
	}

	public function get_regencies($province_id, $active_only = true)
	{
		$this->db->from('ref_regencies')->where('province_id', (int) $province_id);
		if ($active_only) {
			$this->db->where('is_active', 1);
		}
		return $this->db->order_by('name', 'ASC')->get()->result_array();
	}

	public function get_districts_by_regency($regency_id, $active_only = true)
	{
		return $this->district_rows($active_only, ['regency_id' => (int) $regency_id]);
	}

	public function get_villages_by_district($district_id, $active_only = true)
	{
		return $this->get_villages((int) $district_id, $active_only);
	}

	public function get_province($id)
	{
		return $this->db->from('ref_provinces')->where('id', (int) $id)->get()->row_array();
	}

	public function get_regency($id)
	{
		return $this->db
			->select('r.*, p.name AS province_name, p.code AS province_code')
			->from('ref_regencies r')
			->join('ref_provinces p', 'p.id = r.province_id')
			->where('r.id', (int) $id)
			->get()->row_array();
	}

	public function get_district($id)
	{
		return $this->db
			->select('d.*, r.name AS regency_name, p.name AS province_name')
			->from('ref_districts d')
			->join('ref_regencies r', 'r.id = d.regency_id', 'left')
			->join('ref_provinces p', 'p.id = d.province_id', 'left')
			->where('d.id', (int) $id)
			->get()->row_array();
	}

	public function get_villages($district_id = null, $active_only = true, array $filters = [], $limit = null, $offset = 0)
	{
		$this->build_village_query($district_id, $active_only, $filters);
		$this->db
			->select('v.*, d.name AS district_name, d.full_code AS district_full_code, r.name AS regency_name, p.name AS province_name')
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

	public function get_village($id)
	{
		return $this->db
			->select('v.*, d.name AS district_name, d.full_code AS district_full_code, r.name AS regency_name, p.name AS province_name')
			->from('ref_villages v')
			->join('ref_districts d', 'd.id = v.district_id')
			->join('ref_regencies r', 'r.id = v.regency_id', 'left')
			->join('ref_provinces p', 'p.id = v.province_id', 'left')
			->where('v.id', (int) $id)
			->get()->row_array();
	}

	public function village_payload()
	{
		$payload = [];
		foreach ($this->get_villages(null, true, ['regency_code' => self::REMBANG_REGENCY_CODE]) as $village) {
			$district_id = (int) $village['district_id'];
			$payload[$district_id][] = ['id' => $district_id ? (int) $village['id'] : 0, 'name' => $village['name'], 'area_type' => $village['area_type']];
		}
		return $payload;
	}

	public function district_code_exists($code, $exclude_id = null)
	{
		$code = preg_match('/^\d{1,2}$/', (string) $code) ? self::REMBANG_REGENCY_CODE . str_pad((string) $code, 2, '0', STR_PAD_LEFT) : (string) $code;
		$this->db->from('ref_districts')->where('code', $code);
		if ($exclude_id !== null) $this->db->where('id !=', (int) $exclude_id);
		return $this->db->count_all_results() > 0;
	}

	public function village_code_exists($code, $exclude_id = null)
	{
		$this->db->from('ref_villages')->where('code', $code);
		if ($exclude_id !== null) $this->db->where('id !=', (int) $exclude_id);
		return $this->db->count_all_results() > 0;
	}

	public function save_district(array $data, $id = null)
	{
		$payload = $this->clean_district_payload($data);
		if ($id) {
			$this->db->where('id', (int) $id)->update('ref_districts', $payload);
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

	public function toggle_district($id) { return $this->toggle('ref_districts', (int) $id); }
	public function toggle_village($id) { return $this->toggle('ref_villages', (int) $id); }

	private function district_rows($active_only, array $filters)
	{
		$this->db->from('ref_districts d')->join('ref_regencies r', 'r.id = d.regency_id', 'left')->join('ref_provinces p', 'p.id = d.province_id', 'left');
		if ($active_only) $this->db->where('d.is_active', 1);
		if (! empty($filters['regency_id'])) $this->db->where('d.regency_id', (int) $filters['regency_id']);
		if (! empty($filters['regency_code'])) $this->db->where('d.regency_code', (string) $filters['regency_code']);
		if (! empty($filters['q'])) {
			$this->db->group_start()->like('d.code', trim((string) $filters['q']))->or_like('d.full_code', trim((string) $filters['q']))->or_like('d.name', trim((string) $filters['q']))->group_end();
		}
		return $this->db->select('d.*, r.name AS regency_name, p.name AS province_name')->order_by('d.code', 'ASC')->get()->result_array();
	}

	private function build_village_query($district_id, $active_only, array $filters)
	{
		$this->db->from('ref_villages v')->join('ref_districts d', 'd.id = v.district_id')->join('ref_regencies r', 'r.id = v.regency_id', 'left')->join('ref_provinces p', 'p.id = v.province_id', 'left');
		if (! empty($district_id)) $this->db->where('v.district_id', (int) $district_id);
		if (! empty($filters['regency_code'])) $this->db->where('v.regency_code', (string) $filters['regency_code']);
		if ($active_only) $this->db->where('v.is_active', 1);
		if (! empty($filters['area_type'])) $this->db->where('v.area_type', $filters['area_type']);
		if (! empty($filters['q'])) {
			$q = trim((string) $filters['q']);
			$this->db->group_start()->like('v.code', $q)->or_like('v.name', $q)->or_like('d.name', $q)->or_like('d.full_code', $q)->group_end();
		}
	}

	private function toggle($table, $id)
	{
		$row = $this->db->from($table)->where('id', $id)->get()->row_array();
		if (! $row) return false;
		$this->db->where('id', $id)->update($table, ['is_active' => (int) $row['is_active'] === 1 ? 0 : 1]);
		return $row;
	}

	private function clean_district_payload(array $data)
	{
		$local = str_pad(substr(trim((string) $data['code']), -2), 2, '0', STR_PAD_LEFT);
		$regency = $this->db->from('ref_regencies')->where('code', self::REMBANG_REGENCY_CODE)->get()->row_array();
		return ['province_id' => $regency['province_id'], 'regency_id' => $regency['id'], 'province_code' => '33', 'regency_code' => self::REMBANG_REGENCY_CODE, 'code' => self::REMBANG_REGENCY_CODE . $local, 'full_code' => '33.17.' . $local, 'name' => trim((string) $data['name']), 'is_active' => empty($data['is_active']) ? 0 : 1];
	}

	private function clean_village_payload(array $data)
	{
		$district = $this->get_district((int) $data['district_id']);
		return ['district_id' => (int) $data['district_id'], 'province_id' => $district['province_id'] ?? null, 'regency_id' => $district['regency_id'] ?? null, 'province_code' => $district['province_code'] ?? '33', 'regency_code' => $district['regency_code'] ?? self::REMBANG_REGENCY_CODE, 'district_code' => $district['code'] ?? null, 'code' => trim((string) $data['code']), 'area_type' => in_array(($data['area_type'] ?? 'desa'), ['desa', 'kelurahan'], true) ? $data['area_type'] : 'desa', 'name' => trim((string) $data['name']), 'is_active' => empty($data['is_active']) ? 0 : 1];
	}
}
