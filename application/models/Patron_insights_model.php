<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Analitik pemustaka berbasis jejak kunjungan dan sirkulasi lokal.
 * Semua peringkat hanya memakai member yang terhubung agar foto/profil dan
 * tindakan lanjut ke halaman member tetap jelas.
 */
class Patron_insights_model extends CI_Model
{
	public function get_dashboard($date_from, $date_to, $all_time = false)
	{
		$date_from = $this->valid_date($date_from, date('Y-m-01'));
		$date_to = $this->valid_date($date_to, date('Y-m-t'));
		if ($date_from > $date_to) {
			$tmp = $date_from; $date_from = $date_to; $date_to = $tmp;
		}

		return [
			'summary' => $this->summary($date_from, $date_to, $all_time),
			'top_offline' => $this->top_visitors($date_from, $date_to, 'offline', $all_time),
			'top_online' => $this->top_visitors($date_from, $date_to, 'online', $all_time),
			'top_borrowers' => $this->top_borrowers($date_from, $date_to, $all_time),
			'overdue_members' => $this->overdue_members(),
			'patron_of_month' => $this->patron_of_period($date_from, $date_to, $all_time),
			'patron_champions' => $this->patron_champions($date_from, $date_to, $all_time),
			'activity_mix' => $this->activity_mix($date_from, $date_to, $all_time),
		];
	}

	/** Tiga apresiasi independen: hadir fisik, aktif digital, dan terpadu. */
	public function patron_champions($date_from, $date_to, $all_time = false)
	{
		$offline = $this->top_visitors($date_from, $date_to, 'offline', $all_time);
		$online = $this->top_visitors($date_from, $date_to, 'online', $all_time);
		return [
			'offline' => $offline[0] ?? null,
			'online' => $online[0] ?? null,
			'integrated' => $this->patron_of_period($date_from, $date_to, $all_time),
		];
	}

	private function summary($date_from, $date_to, $all_time)
	{
		$empty = ['visit_entries' => 0, 'unique_visitors' => 0, 'offline_visits' => 0, 'online_visits' => 0, 'loan_items' => 0, 'unique_borrowers' => 0, 'overdue_items' => 0, 'overdue_members' => 0];
		if (! $this->db->table_exists('member_visits') || ! $this->db->table_exists('loan_transaction_items')) return $empty;
		$visit_where = $this->period_where('mv.visited_at', $date_from, $date_to, $all_time);
		$visit = $this->db->query("SELECT
			COUNT(*) AS visit_entries,
			COUNT(DISTINCT mv.member_id) AS unique_visitors,
			SUM(CASE WHEN " . $this->offline_condition('mv') . " THEN 1 ELSE 0 END) AS offline_visits,
			SUM(CASE WHEN " . $this->online_condition('mv') . " THEN 1 ELSE 0 END) AS online_visits
			FROM member_visits mv WHERE mv.member_id IS NOT NULL {$visit_where}")->row_array();
		$loan_where = $this->period_where('li.loan_date', $date_from, $date_to, $all_time);
		$loan = $this->db->query("SELECT COUNT(*) AS loan_items, COUNT(DISTINCT li.member_id) AS unique_borrowers
			FROM loan_transaction_items li WHERE li.member_id IS NOT NULL {$loan_where}")->row_array();
		$overdue = $this->db->query("SELECT COUNT(*) AS overdue_items, COUNT(DISTINCT li.member_id) AS overdue_members
			FROM loan_transaction_items li WHERE " . $this->active_loan_condition('li') . " AND li.due_date IS NOT NULL AND li.due_date < CURDATE()")->row_array();
		return array_map('intval', array_merge($empty, (array) $visit, (array) $loan, (array) $overdue));
	}

	private function top_visitors($date_from, $date_to, $mode, $all_time)
	{
		if (! $this->db->table_exists('member_visits')) return [];
		$where = $this->period_where('mv.visited_at', $date_from, $date_to, $all_time);
		$condition = $mode === 'online' ? $this->online_condition('mv') : $this->offline_condition('mv');
		return $this->db->query("SELECT m.id, m.full_name, m.member_no, m.photo_local_path, m.photo_source_path, m.photo_path,
			COUNT(*) AS visit_total,
			SUM(CASE WHEN " . $this->offline_condition('mv') . " THEN 1 ELSE 0 END) AS offline_total,
			SUM(CASE WHEN " . $this->online_condition('mv') . " THEN 1 ELSE 0 END) AS online_total,
			MAX(mv.visited_at) AS last_visit
			FROM member_visits mv JOIN members m ON m.id = mv.member_id
			WHERE m.deleted_at IS NULL {$where} AND ({$condition})
			GROUP BY m.id ORDER BY visit_total DESC, last_visit DESC LIMIT 6")->result_array();
	}

	private function top_borrowers($date_from, $date_to, $all_time)
	{
		if (! $this->db->table_exists('loan_transaction_items')) return [];
		$where = $this->period_where('li.loan_date', $date_from, $date_to, $all_time);
		return $this->db->query("SELECT m.id, m.full_name, m.member_no, m.photo_local_path, m.photo_source_path, m.photo_path,
			COUNT(*) AS loan_total, COUNT(DISTINCT li.loan_transaction_id) AS transaction_total,
			SUM(CASE WHEN " . $this->active_loan_condition('li') . " THEN 1 ELSE 0 END) AS active_total,
			SUM(CASE WHEN " . $this->active_loan_condition('li') . " AND li.due_date < CURDATE() THEN 1 ELSE 0 END) AS overdue_total,
			MAX(li.loan_date) AS last_loan
			FROM loan_transaction_items li JOIN members m ON m.id = li.member_id
			WHERE m.deleted_at IS NULL {$where}
			GROUP BY m.id ORDER BY loan_total DESC, last_loan DESC LIMIT 6")->result_array();
	}

	private function overdue_members()
	{
		if (! $this->db->table_exists('loan_transaction_items')) return [];
		return $this->db->query("SELECT m.id, m.full_name, m.member_no, m.phone, m.photo_local_path, m.photo_source_path, m.photo_path,
			COUNT(*) AS overdue_total, MAX(DATEDIFF(CURDATE(), DATE(li.due_date))) AS max_late_days,
			MIN(li.due_date) AS earliest_due_date,
			GROUP_CONCAT(DISTINCT b.title ORDER BY li.due_date ASC SEPARATOR ' | ') AS book_titles
			FROM loan_transaction_items li JOIN members m ON m.id = li.member_id
			LEFT JOIN book_items bi ON bi.id = li.book_item_id LEFT JOIN books b ON b.id = bi.book_id
			WHERE m.deleted_at IS NULL AND " . $this->active_loan_condition('li') . " AND li.due_date IS NOT NULL AND li.due_date < CURDATE()
			GROUP BY m.id ORDER BY max_late_days DESC, overdue_total DESC, earliest_due_date ASC LIMIT 12")->result_array();
	}

	private function patron_of_period($date_from, $date_to, $all_time)
	{
		if (! $this->db->table_exists('member_visits') || ! $this->db->table_exists('loan_transaction_items')) return null;
		$visit_period = $this->period_where('mv.visited_at', $date_from, $date_to, $all_time);
		$loan_period = $this->period_where('li.loan_date', $date_from, $date_to, $all_time);
		// Poin menyeimbangkan kehadiran fisik, penggunaan digital, dan peminjaman.
		return $this->db->query("SELECT m.id, m.full_name, m.member_no, m.photo_local_path, m.photo_source_path, m.photo_path,
			COALESCE(v.offline_total, 0) AS offline_total, COALESCE(v.online_total, 0) AS online_total,
			COALESCE(l.loan_total, 0) AS loan_total, COALESCE(l.ontime_return_total, 0) AS ontime_return_total,
			(COALESCE(v.offline_total, 0) * 3 + COALESCE(v.online_total, 0) * 2 + COALESCE(l.loan_total, 0) * 2 + COALESCE(l.ontime_return_total, 0)) AS activity_score
			FROM members m
			LEFT JOIN (
				SELECT mv.member_id,
				SUM(CASE WHEN " . $this->offline_condition('mv') . " THEN 1 ELSE 0 END) AS offline_total,
				SUM(CASE WHEN " . $this->online_condition('mv') . " THEN 1 ELSE 0 END) AS online_total
				FROM member_visits mv WHERE mv.member_id IS NOT NULL {$visit_period} GROUP BY mv.member_id
			) v ON v.member_id = m.id
			LEFT JOIN (
				SELECT li.member_id, COUNT(*) AS loan_total,
				SUM(CASE WHEN COALESCE(li.actual_return_at, li.local_return_at) IS NOT NULL AND li.due_date IS NOT NULL AND COALESCE(li.actual_return_at, li.local_return_at) <= li.due_date THEN 1 ELSE 0 END) AS ontime_return_total
				FROM loan_transaction_items li WHERE li.member_id IS NOT NULL {$loan_period} GROUP BY li.member_id
			) l ON l.member_id = m.id
			WHERE m.deleted_at IS NULL AND (COALESCE(v.offline_total, 0) + COALESCE(v.online_total, 0) + COALESCE(l.loan_total, 0)) > 0
			ORDER BY activity_score DESC, (COALESCE(v.offline_total, 0) + COALESCE(v.online_total, 0)) DESC, m.id DESC LIMIT 1")->row_array();
	}

	private function activity_mix($date_from, $date_to, $all_time)
	{
		if (! $this->db->table_exists('member_visits')) return [];
		$where = $this->period_where('mv.visited_at', $date_from, $date_to, $all_time);
		return $this->db->query("SELECT
			SUM(CASE WHEN " . $this->offline_condition('mv') . " THEN 1 ELSE 0 END) AS offline,
			SUM(CASE WHEN " . $this->online_condition('mv') . " THEN 1 ELSE 0 END) AS online,
			SUM(CASE WHEN NOT(" . $this->offline_condition('mv') . ") AND NOT(" . $this->online_condition('mv') . ") THEN 1 ELSE 0 END) AS other
			FROM member_visits mv WHERE mv.member_id IS NOT NULL {$where}")->row_array();
	}

	private function active_loan_condition($alias)
	{
		return "{$alias}.actual_return_at IS NULL AND {$alias}.local_return_at IS NULL AND UPPER(COALESCE({$alias}.loan_status, '')) = 'LOAN'";
	}

	private function offline_condition($alias)
	{
		return "({$alias}.visit_origin IN ('library','reading_point','legacy') AND {$alias}.visit_channel NOT IN ('member_dashboard','digital_access'))";
	}

	private function online_condition($alias)
	{
		return "({$alias}.visit_origin IN ('digital_external','digital_internal') OR {$alias}.visit_channel IN ('member_dashboard','digital_access'))";
	}

	private function period_where($field, $date_from, $date_to, $all_time)
	{
		return $all_time ? '' : "AND {$field} >= '{$date_from} 00:00:00' AND {$field} <= '{$date_to} 23:59:59'";
	}

	private function valid_date($value, $fallback)
	{
		$date = DateTime::createFromFormat('Y-m-d', (string) $value);
		return $date && $date->format('Y-m-d') === $value ? $value : $fallback;
	}
}
