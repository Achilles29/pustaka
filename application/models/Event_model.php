<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Event_model extends CI_Model
{
	public function stats()
	{
		return [
			'events' => $this->count_where('literacy_events'),
			'published' => $this->count_where('literacy_events', ['status' => 'published']),
			'registrations' => $this->count_where('event_registrations'),
			'attended' => $this->count_where('event_registrations', ['status' => 'attended']),
		];
	}

	public function get_events($limit = 25)
	{
		if (! $this->db->table_exists('literacy_events')) {
			return [];
		}

		return $this->db
			->select('e.*, l.name AS library_name, COUNT(er.id) AS registration_count')
			->from('literacy_events e')
			->join('libraries l', 'l.id = e.library_id', 'left')
			->join('event_registrations er', 'er.event_id = e.id', 'left')
			->group_by('e.id')
			->order_by("FIELD(e.status, 'published', 'draft', 'closed', 'cancelled')", '', false)
			->order_by('e.starts_at', 'ASC')
			->limit(max(1, min(50, (int) $limit)))
			->get()
			->result_array();
	}

	private function count_where($table, array $where = [])
	{
		if (! $this->db->table_exists($table)) {
			return 0;
		}

		if (! empty($where)) {
			$this->db->where($where);
		}

		return (int) $this->db->count_all_results($table);
	}
}
