<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Audit_model extends CI_Model
{
	public function write($event_code, $entity_type = null, $entity_id = null, array $old_data = null, array $new_data = null, $user_id = null)
	{
		$this->db->insert('audit_log', [
			'user_id' => $user_id,
			'event_code' => $event_code,
			'entity_type' => $entity_type,
			'entity_id' => $entity_id === null ? null : (string) $entity_id,
			'ip_address' => $this->input->ip_address(),
			'user_agent' => substr((string) $this->input->user_agent(), 0, 255),
			'old_json' => $old_data === null ? null : json_encode($old_data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
			'new_json' => $new_data === null ? null : json_encode($new_data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
		]);

		return (int) $this->db->insert_id();
	}

	public function get_logs(array $filters = [], $limit = 25, $offset = 0)
	{
		$this->build_logs_query($filters);

		return $this->db
			->order_by('a.id', 'DESC')
			->limit(max(10, min(100, (int) $limit)), max(0, (int) $offset))
			->get()
			->result_array();
	}

	public function count_logs(array $filters = [])
	{
		$this->build_logs_query($filters);
		return (int) $this->db->count_all_results();
	}

	private function build_logs_query(array $filters = [])
	{
		$this->db
			->select('a.*, u.username, u.full_name')
			->from('audit_log a')
			->join('auth_user u', 'u.id = a.user_id', 'left');

		if (! empty($filters['event_code'])) {
			$this->db->where('a.event_code', $filters['event_code']);
		}

		if (! empty($filters['entity_type'])) {
			$this->db->where('a.entity_type', $filters['entity_type']);
		}

		if (! empty($filters['q'])) {
			$q = trim((string) $filters['q']);
			$this->db->group_start()
				->like('a.event_code', $q)
				->or_like('a.entity_type', $q)
				->or_like('a.entity_id', $q)
				->or_like('u.username', $q)
				->or_like('u.full_name', $q)
				->group_end();
		}
	}

	public function get_event_options()
	{
		return $this->db
			->select('event_code')
			->from('audit_log')
			->group_by('event_code')
			->order_by('event_code', 'ASC')
			->get()
			->result_array();
	}

	public function get_entity_options()
	{
		return $this->db
			->select('entity_type')
			->from('audit_log')
			->where('entity_type IS NOT NULL', null, false)
			->group_by('entity_type')
			->order_by('entity_type', 'ASC')
			->get()
			->result_array();
	}
}
