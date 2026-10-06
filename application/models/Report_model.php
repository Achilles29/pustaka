<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Report_model extends CI_Model
{
	public function new_members_by_month($year)
	{
		$year = max(2000, min((int) date('Y'), (int) $year));
		$date_expression = 'COALESCE(m.registered_at,m.created_at)';
		$gender_expression = "CASE WHEN LOWER(CONCAT_WS(' ',m.gender,m.gender_label)) REGEXP 'perempuan|female|wanita' THEN 'female' WHEN LOWER(CONCAT_WS(' ',m.gender,m.gender_label)) REGEXP 'laki|male|pria' THEN 'male' ELSE 'unknown' END";
		$sql = "SELECT MONTH({$date_expression}) month_number,
			COUNT(*) total,
			SUM(CASE WHEN {$gender_expression}='male' THEN 1 ELSE 0 END) male,
			SUM(CASE WHEN {$gender_expression}='female' THEN 1 ELSE 0 END) female,
			SUM(CASE WHEN {$gender_expression}='unknown' THEN 1 ELSE 0 END) unknown_gender,
			SUM(CASE WHEN online.member_id IS NOT NULL THEN 1 ELSE 0 END) online,
			SUM(CASE WHEN online.member_id IS NULL THEN 1 ELSE 0 END) offline
			FROM members m
			LEFT JOIN (SELECT DISTINCT member_id FROM member_registration_requests WHERE status='verified' AND member_id IS NOT NULL) online ON online.member_id=m.id
			WHERE {$date_expression}>=? AND {$date_expression}<?
			GROUP BY MONTH({$date_expression}) ORDER BY month_number";
		$raw = $this->db->query($sql, [sprintf('%04d-01-01', $year), sprintf('%04d-01-01', $year + 1)])->result_array();
		$indexed = [];
		foreach ($raw as $row) $indexed[(int) $row['month_number']] = $row;
		$month_names = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
		$rows = [];
		$summary = ['total'=>0,'male'=>0,'female'=>0,'unknown_gender'=>0,'online'=>0,'offline'=>0];
		foreach ($month_names as $number => $name) {
			$row = $indexed[$number] ?? [];
			$item = ['month_number'=>$number,'month_name'=>$name];
			foreach (array_keys($summary) as $key) {
				$item[$key] = (int) ($row[$key] ?? 0);
				$summary[$key] += $item[$key];
			}
			$rows[] = $item;
		}
		$previous = (int) $this->db->query("SELECT COUNT(*) total FROM members m WHERE {$date_expression}>=? AND {$date_expression}<?", [sprintf('%04d-01-01', $year - 1), sprintf('%04d-01-01', $year)])->row()->total;
		$summary['previous_year_total'] = $previous;
		$summary['growth_percent'] = $previous > 0 ? round((($summary['total'] - $previous) / $previous) * 100, 1) : null;
		return ['rows'=>$rows,'summary'=>$summary];
	}

	private function loan_class_expression()
	{
		$digits="REGEXP_REPLACE(COALESCE(NULLIF(b.classification,''),NULLIF(b.call_number,''),NULLIF(bi.call_number,''),''),'[^0-9]','')";
		return "CASE WHEN {$digits} REGEXP '^[0-9]' THEN CONCAT(LEFT({$digits},1),'00') ELSE 'unknown' END";
	}

	public function loans_by_classification($date_from,$date_to,$library_id=0,$class_code='')
	{
		$class=$this->loan_class_expression();$where='li.loan_date >= ? AND li.loan_date <= ?';$params=[$date_from.' 00:00:00',$date_to.' 23:59:59'];if((int)$library_id>0){$where.=' AND bi.library_id = ?';$params[]=(int)$library_id;}if($class_code!==''){$where.=" AND {$class} = ?";$params[]=$class_code;}
		$names="CASE {$class} WHEN '000' THEN 'Karya Umum dan Komputer' WHEN '100' THEN 'Filsafat dan Psikologi' WHEN '200' THEN 'Agama' WHEN '300' THEN 'Ilmu Sosial' WHEN '400' THEN 'Bahasa' WHEN '500' THEN 'Sains' WHEN '600' THEN 'Teknologi dan Ilmu Terapan' WHEN '700' THEN 'Seni dan Olahraga' WHEN '800' THEN 'Sastra' WHEN '900' THEN 'Sejarah dan Geografi' ELSE 'Belum terklasifikasi' END";
		$base=" FROM loan_transaction_items li LEFT JOIN book_items bi ON bi.id=li.book_item_id LEFT JOIN books b ON b.id=bi.book_id LEFT JOIN members m ON m.id=li.member_id WHERE {$where}";
		$classes=$this->db->query("SELECT {$class} class_code,{$names} class_name,COUNT(*) loan_total,COUNT(DISTINCT b.id) title_total,COUNT(DISTINCT li.member_id) borrower_total,SUM(CASE WHEN COALESCE(li.actual_return_at,li.local_return_at) IS NOT NULL OR UPPER(COALESCE(li.loan_status,'')) IN ('RETURN','RETURNED') THEN 1 ELSE 0 END) returned_total,SUM(CASE WHEN COALESCE(li.actual_return_at,li.local_return_at) IS NULL AND COALESCE(li.local_due_date,li.due_date)<CURDATE() AND UPPER(COALESCE(li.loan_status,'LOAN')) NOT IN ('RETURN','RETURNED') THEN 1 ELSE 0 END) overdue_total {$base} GROUP BY class_code,class_name ORDER BY CASE WHEN class_code='unknown' THEN 999 ELSE CAST(class_code AS UNSIGNED) END",$params)->result_array();
		$summary=['loan_total'=>0,'title_total'=>0,'borrower_total'=>0,'returned_total'=>0,'overdue_total'=>0];foreach($classes as $row){$summary['loan_total']+=(int)$row['loan_total'];$summary['returned_total']+=(int)$row['returned_total'];$summary['overdue_total']+=(int)$row['overdue_total'];}$totals=$this->db->query("SELECT COUNT(DISTINCT b.id) title_total,COUNT(DISTINCT li.member_id) borrower_total {$base}",$params)->row_array();$summary['title_total']=(int)($totals['title_total']??0);$summary['borrower_total']=(int)($totals['borrower_total']??0);
		$top=$this->db->query("SELECT {$class} class_code,b.id book_id,COALESCE(NULLIF(b.title,''),'Judul tidak tersedia') title,COALESCE(NULLIF(b.classification,''),NULLIF(b.call_number,''),NULLIF(bi.call_number,''),'-') classification,COUNT(*) loan_total,COUNT(DISTINCT li.member_id) borrower_total {$base} GROUP BY class_code,b.id,title,classification ORDER BY loan_total DESC,title ASC LIMIT 100",$params)->result_array();
		$dates=$this->db->query("SELECT DATE(li.loan_date) loan_day,{$class} class_code,{$names} class_name,COUNT(*) loan_total,COUNT(DISTINCT b.id) title_total,COUNT(DISTINCT li.member_id) borrower_total,SUM(CASE WHEN COALESCE(li.actual_return_at,li.local_return_at) IS NOT NULL OR UPPER(COALESCE(li.loan_status,'')) IN ('RETURN','RETURNED') THEN 1 ELSE 0 END) returned_total {$base} GROUP BY loan_day,class_code,class_name ORDER BY loan_day ASC,CASE WHEN class_code='unknown' THEN 999 ELSE CAST(class_code AS UNSIGNED) END",$params)->result_array();
		$borrowers=$this->db->query("SELECT COALESCE(li.member_id,0) member_id,COALESCE(NULLIF(m.full_name,''),'Pengunjung / data lama') borrower_name,COALESCE(NULLIF(m.member_no,''),NULLIF(li.source_member_id,''),'-') member_no,COALESCE(NULLIF(m.gender_label,''),NULLIF(m.gender,''),'-') gender,{$class} class_code,{$names} class_name,COUNT(*) loan_total,COUNT(DISTINCT b.id) title_total,MIN(li.loan_date) first_loan,MAX(li.loan_date) last_loan,GROUP_CONCAT(DISTINCT COALESCE(NULLIF(b.title,''),'Judul tidak tersedia') ORDER BY b.title SEPARATOR ' | ') book_titles {$base} GROUP BY member_id,borrower_name,member_no,gender,class_code,class_name ORDER BY loan_total DESC,borrower_name ASC",$params)->result_array();
		return ['summary'=>$summary,'classes'=>$classes,'top_books'=>$top,'dates'=>$dates,'borrowers'=>$borrowers];
	}

	public function report_libraries()
	{
		if(!$this->db->table_exists('libraries'))return [];return $this->db->select('id,name')->from('libraries')->where('status','active')->order_by('name')->get()->result_array();
	}
	private function apply_visit_scope($query, $scope, $alias = '')
	{
		$prefix = $alias === '' ? '' : rtrim($alias, '.') . '.';
		if ($scope === 'online') {
			$query->where_in($prefix . 'visit_channel', ['member_dashboard', 'digital_access']);
		} elseif ($scope === 'offline') {
			$query->where_not_in($prefix . 'visit_channel', ['member_dashboard', 'digital_access']);
		}
		return $query;
	}

	public function visit_mode_breakdown($date_from, $date_to)
	{
		if (! $this->db->table_exists('member_visits')) return [];
		return $this->db
			->select("CASE WHEN visit_channel IN ('member_dashboard','digital_access') THEN 'online' ELSE 'offline' END AS label", false)
			->select('COUNT(*) AS entries', false)
			->select('COALESCE(SUM(visitor_count), COUNT(*)) AS people', false)
			->from('member_visits')
			->where('visited_at >=', $date_from . ' 00:00:00')
			->where('visited_at <=', $date_to . ' 23:59:59')
			->group_by('label')->order_by('label')->get()->result_array();
	}

	public function visit_simulation_summary($date_from, $date_to, $scope = 'all')
	{
		if (!$this->db->table_exists('member_visits')) return ['entries'=>0,'people'=>0];
		$query = $this->db->select('COUNT(*) AS entries, COALESCE(SUM(visitor_count),0) AS people', false)
			->from('member_visits')->where('source_system','simulation')
			->where('visited_at >=',$date_from.' 00:00:00')->where('visited_at <=',$date_to.' 23:59:59');
		return $this->apply_visit_scope($query,$scope)->get()->row_array();
	}

	public function visit_summary($date_from, $date_to, $scope = 'all')
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

		$query = $this->db
			->select('COUNT(*) AS entries', false)
			->select('COALESCE(SUM(visitor_count), COUNT(*)) AS people', false)
			->select('SUM(CASE WHEN member_id IS NOT NULL THEN 1 ELSE 0 END) AS members', false)
			->select('SUM(CASE WHEN member_id IS NULL THEN 1 ELSE 0 END) AS non_members', false)
			->select('SUM(CASE WHEN visitor_count > 1 THEN 1 ELSE 0 END) AS groups', false)
			->from('member_visits')
			->where('visited_at >=', $date_from . ' 00:00:00')
			->where('visited_at <=', $date_to . ' 23:59:59');
		$row = $this->apply_visit_scope($query, $scope)
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

	public function visit_breakdown($date_from, $date_to, $field, $scope = 'all')
	{
		if (! $this->db->table_exists('member_visits') || ! $this->db->field_exists($field, 'member_visits')) {
			return [];
		}

		$query = $this->db
			->select("COALESCE(NULLIF({$field}, ''), 'unknown') AS label", false)
			->select('COUNT(*) AS entries', false)
			->select('COALESCE(SUM(visitor_count), COUNT(*)) AS people', false)
			->from('member_visits')
			->where('visited_at >=', $date_from . ' 00:00:00')
			->where('visited_at <=', $date_to . ' 23:59:59');
		return $this->apply_visit_scope($query, $scope)
			->group_by('label')
			->order_by('people', 'DESC')
			->get()
			->result_array();
	}

	public function visit_purpose_expression()
	{
		$reference = $this->db->table_exists('inlislite_master_references')
			? "(SELECT MIN(NULLIF(TRIM(purpose_ref.name), '')) FROM inlislite_master_references purpose_ref WHERE purpose_ref.source_table='tujuan_kunjungan' AND purpose_ref.source_id=mv.purpose_id), " : '';
		// Normalize automatic digital activities to the guestbook purpose in reports only.
		$label = "CASE WHEN TRIM(mv.purpose_label) IN ('Layanan digital', 'Akses layanan digital', 'Baca buku digital') THEN 'Layanan digital' ELSE NULLIF(TRIM(mv.purpose_label), '') END";
		return "COALESCE(".$label.", ".$reference."CONCAT('Tujuan lama (ID ', NULLIF(TRIM(mv.purpose_id), ''), ')'), 'Belum diisi')";
	}

	public function visit_purpose_breakdown($date_from, $date_to, $scope = 'all')
	{
		if (!$this->db->table_exists('member_visits')) return [];
		$query = $this->db->select($this->visit_purpose_expression().' AS label',false)
			->select('COUNT(*) AS entries, COALESCE(SUM(mv.visitor_count), 0) AS people',false)
			->from('member_visits mv')
			->where('mv.visited_at >=',$date_from.' 00:00:00')
			->where('mv.visited_at <=',$date_to.' 23:59:59');
		return $this->apply_visit_scope($query,$scope,'mv')->group_by('label')
			->order_by('people','DESC')->order_by('label','ASC')->get()->result_array();
	}

	public function visit_gender_breakdown($date_from, $date_to, $scope = 'all')
	{
		if (! $this->db->table_exists('member_visits')) return [];
		$gender=$this->visit_gender_expression();
		$query = $this->db
			->select($gender . ' AS label', false)
			->select('COUNT(*) AS entries', false)
			->select('COALESCE(SUM(mv.visitor_count), COUNT(*)) AS people', false)
			->from('member_visits mv')
			->join('members m_gender', 'm_gender.id = mv.member_id', 'left')
			->where('mv.visited_at >=', $date_from . ' 00:00:00')
			->where('mv.visited_at <=', $date_to . ' 23:59:59');
		return $this->apply_visit_scope($query, $scope, 'mv')
			->group_by('label')
			->order_by("FIELD(label, 'male', 'female', 'unknown')", '', false)
			->get()->result_array();
	}

	public function visit_trend($date_from, $date_to, $group_by = 'day', $scope = 'all')
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

		$query = $this->db
			->select($expression . ' AS period', false)
			->select('COUNT(*) AS entries', false)
			->select('COALESCE(SUM(visitor_count), COUNT(*)) AS people', false)
			->from('member_visits')
			->where('visited_at >=', $date_from . ' 00:00:00')
			->where('visited_at <=', $date_to . ' 23:59:59');
		return $this->apply_visit_scope($query, $scope)
			->group_by('period')
			->order_by('period', 'ASC')
			->get()
			->result_array();
	}

	public function recent_visits($date_from, $date_to, $limit = 10, $scope = 'all')
	{
		if (! $this->db->table_exists('member_visits')) {
			return [];
		}

		$query = $this->db
			->select('mv.*, m.full_name AS member_name, m.member_no')
			->select($this->visit_gender_expression('mv', 'm') . ' AS gender_group', false)
			->from('member_visits mv')
			->join('members m', 'm.id = mv.member_id', 'left')
			->where('mv.visited_at >=', $date_from . ' 00:00:00')
			->where('mv.visited_at <=', $date_to . ' 23:59:59');
		return $this->apply_visit_scope($query, $scope, 'mv')
			->order_by('mv.visited_at', 'DESC')
			->order_by('mv.id', 'DESC')
			->limit(max(1, min(50, (int) $limit)))
			->get()
			->result_array();
	}

	public function visit_rows($date_from, $date_to, $limit = 10000, $scope = 'all')
	{
		if (! $this->db->table_exists('member_visits')) {
			return [];
		}

		$query = $this->db
			->select('mv.*, m.full_name AS member_name, m.member_no')
			->from('member_visits mv')
			->join('members m', 'm.id = mv.member_id', 'left')
			->where('mv.visited_at >=', $date_from . ' 00:00:00')
			->where('mv.visited_at <=', $date_to . ' 23:59:59');
		return $this->apply_visit_scope($query, $scope, 'mv')
			->order_by('mv.visited_at', 'ASC')
			->order_by('mv.id', 'ASC')
			->limit(max(1, min(50000, (int) $limit)))
			->get()
			->result_array();
	}
	private function visit_gender_expression($visit_alias = 'mv', $member_alias = 'm_gender')
	{
		$raw="LOWER(TRIM(COALESCE(NULLIF({$visit_alias}.gender_label, ''), NULLIF({$member_alias}.gender_label, ''), NULLIF({$member_alias}.gender, ''), '')))";
		return "CASE WHEN {$raw} IN ('l','lk','laki-laki','laki laki','pria','male') THEN 'male' WHEN {$raw} IN ('p','pr','perempuan','wanita','female') THEN 'female' ELSE 'unknown' END";
	}

}
