<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Library_model extends CI_Model
{
	public function get_types()
	{
		return $this->db
			->from('library_types')
			->where('is_active', 1)
			->order_by('name', 'ASC')
			->get()
			->result_array();
	}

	public function get_libraries(array $filters = [])
	{
		$this->db
			->select('l.*, t.name AS type_name, t.code AS type_code, t.marker_color')
			->from('libraries l')
			->join('library_types t', 't.id = l.library_type_id');

		if (! empty($filters['q'])) {
			$q = trim((string) $filters['q']);
			$this->db->group_start()
				->like('l.name', $q)
				->or_like('l.code', $q)
				->or_like('l.district', $q)
				->or_like('l.village', $q)
				->group_end();
		}

		if (! empty($filters['type_id'])) {
			$this->db->where('l.library_type_id', (int) $filters['type_id']);
		}

		if (! empty($filters['status'])) {
			$this->db->where('l.status', $filters['status']);
		}

		return $this->db
			->order_by('l.name', 'ASC')
			->get()
			->result_array();
	}

	public function get_library($id)
	{
		return $this->db
			->select('l.*, t.name AS type_name, t.code AS type_code, t.marker_color')
			->from('libraries l')
			->join('library_types t', 't.id = l.library_type_id')
			->where('l.id', (int) $id)
			->get()
			->row_array();
	}

	public function get_photos($library_id)
	{
		return $this->db
			->from('library_photos')
			->where('library_id', (int) $library_id)
			->order_by('is_cover', 'DESC')
			->order_by('sort_order', 'ASC')
			->order_by('id', 'ASC')
			->get()
			->result_array();
	}

	public function create_library(array $data)
	{
		$this->db->insert('libraries', $this->clean_payload($data));
		return (int) $this->db->insert_id();
	}

	public function update_library($id, array $data)
	{
		$this->db
			->where('id', (int) $id)
			->update('libraries', $this->clean_payload($data));
	}

	public function set_status($id, $status, $user_id)
	{
		$this->db
			->where('id', (int) $id)
			->update('libraries', [
				'status' => $status,
				'updated_by' => (int) $user_id,
			]);
	}

	public function add_photo($library_id, $file_path, $caption, $user_id)
	{
		$has_cover = $this->db
			->from('library_photos')
			->where('library_id', (int) $library_id)
			->where('is_cover', 1)
			->count_all_results() > 0;

		$this->db->insert('library_photos', [
			'library_id' => (int) $library_id,
			'file_path' => $file_path,
			'caption' => $caption ?: null,
			'is_cover' => $has_cover ? 0 : 1,
			'uploaded_by' => (int) $user_id,
		]);
	}

	public function map_payload(array $libraries)
	{
		$payload = [];
		foreach ($libraries as $library) {
			$payload[] = [
				'id' => (int) $library['id'],
				'name' => $library['name'],
				'type' => $library['type_name'],
				'color' => $library['marker_color'],
				'lat' => (float) $library['latitude'],
				'lng' => (float) $library['longitude'],
				'radius' => (int) $library['service_radius_meters'],
				'address' => $library['address'],
				'url' => base_url('libraries/edit/' . (int) $library['id']),
			];
		}

		return $payload;
	}

	private function clean_payload(array $data)
	{
		return [
			'library_type_id' => (int) $data['library_type_id'],
			'code' => trim((string) $data['code']),
			'name' => trim((string) $data['name']),
			'manager_name' => trim((string) ($data['manager_name'] ?? '')) ?: null,
			'address' => trim((string) ($data['address'] ?? '')) ?: null,
			'district' => trim((string) ($data['district'] ?? '')) ?: null,
			'village' => trim((string) ($data['village'] ?? '')) ?: null,
			'phone' => trim((string) ($data['phone'] ?? '')) ?: null,
			'email' => trim((string) ($data['email'] ?? '')) ?: null,
			'website' => trim((string) ($data['website'] ?? '')) ?: null,
			'opening_hours' => trim((string) ($data['opening_hours'] ?? '')) ?: null,
			'latitude' => (float) $data['latitude'],
			'longitude' => (float) $data['longitude'],
			'service_radius_meters' => max(10, (int) ($data['service_radius_meters'] ?? 100)),
			'description' => trim((string) ($data['description'] ?? '')) ?: null,
			'facilities' => trim((string) ($data['facilities'] ?? '')) ?: null,
			'status' => in_array(($data['status'] ?? 'active'), ['active', 'inactive', 'pending'], true) ? $data['status'] : 'active',
			'created_by' => empty($data['created_by']) ? null : (int) $data['created_by'],
			'updated_by' => empty($data['updated_by']) ? null : (int) $data['updated_by'],
		];
	}
}
