<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Report_model extends CI_Model
{
	public function visit_summary($date_from, $date_to)
	{
		if (! $this->db->table_exists('member_visits')) {
			return [
				'entries' => 0,
				'people' => 0,
				'members' => 0,
				'non_members' => 0,
				'groups' => 0,
			];
		}

		$row = $this->db
			->select('COUNT(*) AS entries', false)
			->select('COALESCE(SUM(visitor_count), COUNT(*)) AS people', false)
			->select('SUM(CASE WHEN member_id IS NOT NULL THEN 1 ELSE 0 END) AS members', false)
			->select('SUM(CASE WHEN member_id IS NULL THEN 1 ELSE 0 END) AS non_members', false)
			->select('SUM(CASE WHEN visitor_count > 1 THEN 1 ELSE 0 END) AS groups', false)
			->from('member_visits')
			->where('visited_at >=', $date_from . ' 00:00:00')
			->where('visited_at <=', $date_to . ' 23:59:59')
			->get()
			->row_array();

		return [
			'entries' => (int) ($row['entries'] ?? 0),
			'people' => (int) ($row['people'] ?? 0),
			'members' => (int) ($row['members'] ?? 0),
			'non_members' => (int) ($row['non_members'] ?? 0),
			'groups' => (int) ($row['groups'] ?? 0),
		];
	}

	public function visit_breakdown($date_from, $date_to, $field)
	{
		if (! $this->db->table_exists('member_visits') || ! $this->db->field_exists($field, 'member_visits')) {
			return [];
		}

		return $this->db
			->select("COALESCE(NULLIF({$field}, ''), 'unknown') AS label", false)
			->select('COUNT(*) AS entries', false)
			->select('COALESCE(SUM(visitor_count), COUNT(*)) AS people', false)
			->from('member_visits')
			->where('visited_at >=', $date_from . ' 00:00:00')
			->where('visited_at <=', $date_to . ' 23:59:59')
			->group_by('label')
			->order_by('people', 'DESC')
			->get()
			->result_array();
	}

	public function visit_trend($date_from, $date_to, $group_by = 'day')
	{
		if (! $this->db->table_exists('member_visits')) {
			return [];
		}

		$expression = "DATE_FORMAT(visited_at, '%Y-%m-%d')";
		if ($group_by === 'month') {
			$expression = "DATE_FORMAT(visited_at, '%Y-%m')";
		} elseif ($group_by === 'hour') {
			$expression = "DATE_FORMAT(visited_at, '%H:00')";
		}

		return $this->db
			->select($expression . ' AS period', false)
			->select('COUNT(*) AS entries', false)
			->select('COALESCE(SUM(visitor_count), COUNT(*)) AS people', false)
			->from('member_visits')
			->where('visited_at >=', $date_from . ' 00:00:00')
			->where('visited_at <=', $date_to . ' 23:59:59')
			->group_by('period')
			->order_by('period', 'ASC')
			->get()
			->result_array();
	}

	public function recent_visits($date_from, $date_to, $limit = 10)
	{
		if (! $this->db->table_exists('member_visits')) {
			return [];
		}

		return $this->db
			->select('mv.*, m.full_name AS member_name, m.member_no')
			->from('member_visits mv')
			->join('members m', 'm.id = mv.member_id', 'left')
			->where('mv.visited_at >=', $date_from . ' 00:00:00')
			->where('mv.visited_at <=', $date_to . ' 23:59:59')
			->order_by('mv.visited_at', 'DESC')
			->order_by('mv.id', 'DESC')
			->limit(max(1, min(50, (int) $limit)))
			->get()
			->result_array();
	}

	public function visit_rows($date_from, $date_to, $limit = 10000)
	{
		if (! $this->db->table_exists('member_visits')) {
			return [];
		}

		return $this->db
			->select('mv.*, m.full_name AS member_name, m.member_no')
			->from('member_visits mv')
			->join('members m', 'm.id = mv.member_id', 'left')
			->where('mv.visited_at >=', $date_from . ' 00:00:00')
			->where('mv.visited_at <=', $date_to . ' 23:59:59')
			->order_by('mv.visited_at', 'ASC')
			->order_by('mv.id', 'ASC')
			->limit(max(1, min(50000, (int) $limit)))
			->get()
			->result_array();
	}
}
