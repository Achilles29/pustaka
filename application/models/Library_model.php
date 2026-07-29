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

	public function get_libraries(array $filters = [], $limit = 25, $offset = 0)
	{
		$this->build_libraries_query($filters);

		return $this->db
			->order_by('l.name', 'ASC')
			->limit(max(1, min(100, (int) $limit)), max(0, (int) $offset))
			->get()
			->result_array();
	}

	public function count_libraries(array $filters = [])
	{
		$this->build_libraries_query($filters);
		return (int) $this->db->count_all_results();
	}

	private function build_libraries_query(array $filters = [])
	{
		$this->db
			->select('l.*, t.name AS type_name, t.code AS type_code, t.marker_color, d.name AS district_name, v.name AS village_name, cover.file_path AS cover_path')
			->from('libraries l')
			->join('library_types t', 't.id = l.library_type_id')
			->join('ref_districts d', 'd.id = l.district_id', 'left')
			->join('ref_villages v', 'v.id = l.village_id', 'left')
			->join('library_photos cover', 'cover.library_id = l.id AND cover.is_cover = 1 AND cover.deleted_at IS NULL', 'left');

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

		if (! empty($filters['district_id'])) {
			$this->db->where('l.district_id', (int) $filters['district_id']);
		}

		if (! empty($filters['status'])) {
			$this->db->where('l.status', $filters['status']);
		}

		if (! empty($filters['library_id'])) {
			$this->db->where('l.id', (int) $filters['library_id']);
		}
	}

	public function get_library($id, $scope_library_id = null)
	{
		$this->db
			->select('l.*, t.name AS type_name, t.code AS type_code, t.marker_color, d.name AS district_name, v.name AS village_name')
			->from('libraries l')
			->join('library_types t', 't.id = l.library_type_id')
			->join('ref_districts d', 'd.id = l.district_id', 'left')
			->join('ref_villages v', 'v.id = l.village_id', 'left')
			->where('l.id', (int) $id);

		if (! empty($scope_library_id)) {
			$this->db->where('l.id', (int) $scope_library_id);
		}

		return $this->db
			->get()
			->row_array();
	}

	public function get_photos($library_id)
	{
		return $this->db
			->from('library_photos')
			->where('library_id', (int) $library_id)
			->where('deleted_at IS NULL', null, false)
			->order_by('is_cover', 'DESC')
			->order_by('sort_order', 'ASC')
			->order_by('id', 'ASC')
			->get()
			->result_array();
	}

	public function get_photo($photo_id, $scope_library_id = null)
	{
		$this->db
			->select('p.*, l.name AS library_name')
			->from('library_photos p')
			->join('libraries l', 'l.id = p.library_id')
			->where('p.id', (int) $photo_id)
			->where('p.deleted_at IS NULL', null, false);

		if (! empty($scope_library_id)) {
			$this->db->where('p.library_id', (int) $scope_library_id);
		}

		return $this->db->get()->row_array();
	}

	public function create_library(array $data)
	{
		$this->db->insert('libraries', $this->clean_payload($data));
		return (int) $this->db->insert_id();
	}

	public function update_library($id, array $data)
	{
		$payload = $this->clean_payload($data);
		if (array_key_exists('created_by', $payload) && $payload['created_by'] === null) {
			unset($payload['created_by']);
		}

		$this->db
			->where('id', (int) $id)
			->update('libraries', $payload);
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

	public function verify_library($id, $user_id)
	{
		$this->db
			->where('id', (int) $id)
			->update('libraries', [
				'is_verified' => 1,
				'verified_by' => (int) $user_id,
				'verified_at' => date('Y-m-d H:i:s'),
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

		return (int) $this->db->insert_id();
	}

	public function set_cover_photo($photo_id, $user_id)
	{
		$photo = $this->get_photo((int) $photo_id);
		if (! $photo) {
			return false;
		}

		$this->db->trans_start();
		$this->db
			->where('library_id', (int) $photo['library_id'])
			->update('library_photos', ['is_cover' => 0]);
		$this->db
			->where('id', (int) $photo_id)
			->update('library_photos', ['is_cover' => 1]);
		$this->db
			->where('id', (int) $photo['library_id'])
			->update('libraries', ['updated_by' => (int) $user_id]);
		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	public function soft_delete_photo($photo_id, $user_id)
	{
		$photo = $this->get_photo((int) $photo_id);
		if (! $photo) {
			return false;
		}

		$this->db
			->where('id', (int) $photo_id)
			->update('library_photos', [
				'deleted_at' => date('Y-m-d H:i:s'),
				'deleted_by' => (int) $user_id,
				'is_cover' => 0,
			]);

		if ((int) $photo['is_cover'] === 1) {
			$next_cover = $this->db
				->from('library_photos')
				->where('library_id', (int) $photo['library_id'])
				->where('deleted_at IS NULL', null, false)
				->order_by('sort_order', 'ASC')
				->order_by('id', 'ASC')
				->limit(1)
				->get()
				->row_array();

			if ($next_cover) {
				$this->db
					->where('id', (int) $next_cover['id'])
					->update('library_photos', ['is_cover' => 1]);
			}
		}

		return $photo;
	}

	public function get_library_options()
	{
		return $this->db
			->select('id, code, name')
			->from('libraries')
			->order_by('name', 'ASC')
			->get()
			->result_array();
	}

	public function dashboard_stats($scope_library_id = null)
	{
		if (! empty($scope_library_id)) {
			$this->db->where('id', (int) $scope_library_id);
		}
		$total = (int) $this->db->count_all_results('libraries');

		if (! empty($scope_library_id)) {
			$this->db->where('id', (int) $scope_library_id);
		}
		$active = (int) $this->db->where('status', 'active')->count_all_results('libraries');

		if (! empty($scope_library_id)) {
			$this->db->where('id', (int) $scope_library_id);
		}
		$verified = (int) $this->db->where('is_verified', 1)->count_all_results('libraries');

		if (! empty($scope_library_id)) {
			$this->db->where('library_id', (int) $scope_library_id);
		}
		$photos = (int) $this->db->where('deleted_at IS NULL', null, false)->count_all_results('library_photos');

		return [
			'total' => $total,
			'active' => $active,
			'verified' => $verified,
			'photos' => $photos,
		];
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
				'district' => $library['district_name'] ?: $library['district'],
				'village' => $library['village_name'] ?: $library['village'],
				'cover' => empty($library['cover_path']) ? null : base_url($library['cover_path']),
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
			'district_id' => empty($data['district_id']) ? null : (int) $data['district_id'],
			'village_id' => empty($data['village_id']) ? null : (int) $data['village_id'],
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
			'is_verified' => empty($data['is_verified']) ? 0 : 1,
			'created_by' => empty($data['created_by']) ? null : (int) $data['created_by'],
			'updated_by' => empty($data['updated_by']) ? null : (int) $data['updated_by'],
		];
	}
}
