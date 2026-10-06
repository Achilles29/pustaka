<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Report_model');
	}

	public function visits()
	{
		$this->require_permission('reports.visits', 'view');
		if($this->input->get('tab',true)==='format'){$this->visits_format();return;}

		$payload=$this->visit_report_payload(12) + ['title' => 'Laporan Kunjungan'];
		$this->render('reports/visits', $payload);
		$html=$this->decorate_gender_report($this->output->get_output(),$payload['gender_breakdown'],'screen');
		$html=$this->decorate_visit_scope($html,$payload['visit_scope'],$payload['mode_totals'],'summary');
		$this->output->set_output($this->decorate_visit_tabs($html,'summary'));
	}

	public function visits_format()
	{
		$this->require_permission('reports.visits','view');$period=$this->resolve_period();$config=$this->custom_visit_config();
		$this->render('game/visits_format',['title'=>'Format Laporan Kunjungan','period'=>$period,'period_heading'=>$this->custom_period_heading($period),'config'=>$config,'variables'=>$this->custom_visit_variables(),'report'=>$this->build_custom_visit_report($period,$config)]);
		$modeRows=$this->Report_model->visit_mode_breakdown($period['date_from'],$period['date_to']);$modeTotals=['offline'=>['people'=>0,'entries'=>0],'online'=>['people'=>0,'entries'=>0]];foreach($modeRows as $row)if(isset($modeTotals[$row['label']]))$modeTotals[$row['label']]=['people'=>(int)$row['people'],'entries'=>(int)$row['entries']];
		$this->output->set_output($this->decorate_visit_scope($this->output->get_output(),$config['scope'],$modeTotals,'format'));
	}

	public function visits_format_excel()
	{
		$this->require_permission('reports.visits','export');$period=$this->resolve_period();$config=$this->custom_visit_config();$report=$this->build_custom_visit_report($period,$config);
		$filename='format-laporan-kunjungan-'.$period['date_from'].'-sd-'.$period['date_to'].'.xls';
		$html=$this->custom_visit_excel($period,$config,$report);
		$this->output->set_content_type('application/vnd.ms-excel')->set_header('Content-Disposition: attachment; filename="'.$filename.'"')->set_header('Cache-Control: max-age=0')->set_output($html);
	}

	public function visits_print()
	{
		$this->require_permission('reports.visits', 'export');

		$payload=$this->visit_report_payload(200) + ['title'=>'Cetak Laporan Kunjungan','generated_at'=>date('Y-m-d H:i:s')];
		$this->load->view('reports/visits_print', $payload);
		$html=$this->decorate_gender_report($this->output->get_output(),$payload['gender_breakdown'],'print');
		$this->output->set_output($this->decorate_visit_scope_export($html,$payload['visit_scope'],'print'));
	}

	public function visits_excel()
	{
		$this->require_permission('reports.visits', 'export');

		$payload = $this->visit_report_payload(0);
		$payload['rows'] = $this->Report_model->visit_rows($payload['period']['date_from'], $payload['period']['date_to'], 50000, $payload['visit_scope']);
		$payload['generated_at'] = date('Y-m-d H:i:s');

		$filename = 'laporan-kunjungan-' . $payload['period']['date_from'] . '-sd-' . $payload['period']['date_to'] . '.xls';
		$html = $this->decorate_visit_scope_export(
			$this->decorate_gender_report(
				$this->load->view('reports/visits_excel', $payload, true),
				$payload['gender_breakdown'],
				'excel'
			),
			$payload['visit_scope'],
			'excel'
		);
		$html = $this->preserve_long_numbers_in_excel($html);
		$this->output
			->set_content_type('application/vnd.ms-excel')
			->set_header('Content-Disposition: attachment; filename="' . $filename . '"')
			->set_header('Cache-Control: max-age=0')
			->set_output($html);
	}

	/**
	 * Excel hanya mempertahankan 15 digit signifikan untuk sel numerik. Nomor
	 * anggota yang berupa NIK (16 digit) harus diberi format teks pada HTML XLS.
	 */
	private function preserve_long_numbers_in_excel($html)
	{
		return preg_replace_callback(
			'/<td([^>]*)>\s*(\d{12,})\s*<\/td>/i',
			function ($matches) {
				$attributes = rtrim((string) $matches[1]);
				if (stripos($attributes, 'mso-number-format') === false) {
					$attributes .= ' style="mso-number-format:\'\\@\';"';
				}
				return '<td' . $attributes . '>' . $matches[2] . '</td>';
			},
			(string) $html
		);
	}

	public function loans_by_classification()
	{
		$this->require_permission('reports.loan_classification','view');$period=$this->resolve_period();$library_id=max(0,(int)$this->input->get('library_id',true));$class_code=(string)$this->input->get('class_code',true);if(!in_array($class_code,['','000','100','200','300','400','500','600','700','800','900','unknown'],true))$class_code='';$tab=(string)$this->input->get('tab',true);if(!in_array($tab,['summary','date','borrower'],true))$tab='summary';$report=$this->Report_model->loans_by_classification($period['date_from'],$period['date_to'],$library_id,$class_code);
		$this->render('game/loan_classification_report',['title'=>'Peminjaman per Klasifikasi','period'=>$period,'library_id'=>$library_id,'class_code'=>$class_code,'tab'=>$tab,'libraries'=>$this->Report_model->report_libraries(),'report'=>$report]);
	}

	public function members()
	{
		$this->require_permission('reports.visits', 'view');
		$year = (int) $this->input->get('year', true);
		$current_year = (int) date('Y');
		if ($year < 2000 || $year > $current_year) $year = $current_year;
		$report = $this->Report_model->new_members_by_month($year);
		$this->render('game/member_growth_report', [
			'title' => 'Laporan Anggota Baru',
			'year' => $year,
			'current_year' => $current_year,
			'report' => $report,
		]);
	}

	public function loans_by_classification_excel()
	{
		$this->require_permission('reports.loan_classification','export');$period=$this->resolve_period();$library_id=max(0,(int)$this->input->get('library_id',true));$class_code=(string)$this->input->get('class_code',true);if(!in_array($class_code,['','000','100','200','300','400','500','600','700','800','900','unknown'],true))$class_code='';$tab=(string)$this->input->get('tab',true);if(!in_array($tab,['summary','date','borrower'],true))$tab='summary';$report=$this->Report_model->loans_by_classification($period['date_from'],$period['date_to'],$library_id,$class_code);$html='<!doctype html><html><head><meta charset="utf-8"><style>table{border-collapse:collapse}th,td{border:1px solid #777;padding:6px}th{background:#dbeafe}.number{text-align:right}.title{font-size:16px;text-align:center;border:0}</style></head><body><table><tr><th class="title" colspan="8">LAPORAN PEMINJAMAN BERDASARKAN KELAS KLASIFIKASI</th></tr><tr><th class="title" colspan="8">'.html_escape($period['label']).'</th></tr><tr><td colspan="8"></td></tr>';
		if($tab==='date'){$html.='<tr><th>Tanggal</th><th>Kelas</th><th>Klasifikasi</th><th>Dipinjam</th><th>Judul</th><th>Peminjam</th><th>Dikembalikan</th></tr>';foreach($report['dates'] as $row)$html.='<tr><td>'.html_escape($row['loan_day']).'</td><td>'.html_escape($row['class_code']).'</td><td>'.html_escape($row['class_name']).'</td><td>'.(int)$row['loan_total'].'</td><td>'.(int)$row['title_total'].'</td><td>'.(int)$row['borrower_total'].'</td><td>'.(int)$row['returned_total'].'</td></tr>';}
		elseif($tab==='borrower'){$html.='<tr><th>No. Anggota</th><th>Nama Peminjam</th><th>JK</th><th>Kelas</th><th>Klasifikasi</th><th>Pinjaman Pertama</th><th>Pinjaman Terakhir</th><th>Dipinjam</th><th>Judul</th><th>Daftar Buku</th></tr>';foreach($report['borrowers'] as $row)$html.='<tr><td>'.html_escape($row['member_no']).'</td><td>'.html_escape($row['borrower_name']).'</td><td>'.html_escape($row['gender']).'</td><td>'.html_escape($row['class_code']).'</td><td>'.html_escape($row['class_name']).'</td><td>'.html_escape(substr($row['first_loan'],0,10)).'</td><td>'.html_escape(substr($row['last_loan'],0,10)).'</td><td>'.(int)$row['loan_total'].'</td><td>'.(int)$row['title_total'].'</td><td>'.html_escape(str_replace(' | ',', ',$row['book_titles'])).'</td></tr>';}
		else{$html.='<tr><th>Kelas</th><th>Nama Klasifikasi</th><th>Dipinjam</th><th>Judul</th><th>Peminjam</th><th>Dikembalikan</th><th>Terlambat Aktif</th></tr>';foreach($report['classes'] as $row)$html.='<tr><td>'.html_escape($row['class_code']).'</td><td>'.html_escape($row['class_name']).'</td><td>'.(int)$row['loan_total'].'</td><td>'.(int)$row['title_total'].'</td><td>'.(int)$row['borrower_total'].'</td><td>'.(int)$row['returned_total'].'</td><td>'.(int)$row['overdue_total'].'</td></tr>';}$html.='</table></body></html>';$html=$this->preserve_long_numbers_in_excel($html);$filename='peminjaman-klasifikasi-'.$tab.'-'.$period['date_from'].'-sd-'.$period['date_to'].'.xls';$this->output->set_content_type('application/vnd.ms-excel')->set_header('Content-Disposition: attachment; filename="'.$filename.'"')->set_header('Cache-Control: max-age=0')->set_output($html);
	}

	private function resolve_period()
	{
		$mode = (string) $this->input->get('mode', true);
		$mode = in_array($mode, ['year', 'month', 'day', 'custom'], true) ? $mode : 'month';
		$today = new DateTime('today');

		if ($mode === 'year') {
			$year = (int) $this->input->get('year', true);
			$year = $year >= 2000 && $year <= 2100 ? $year : (int) $today->format('Y');
			$date_from = sprintf('%04d-01-01', $year);
			$date_to = sprintf('%04d-12-31', $year);
			return compact('mode', 'year', 'date_from', 'date_to') + [
				'month' => $today->format('Y-m'),
				'day' => $today->format('Y-m-d'),
				'group_by' => 'month',
				'label' => 'Tahun ' . $year,
			];
		}

		if ($mode === 'day') {
			$day = $this->valid_date((string) $this->input->get('day', true), $today->format('Y-m-d'));
			return [
				'mode' => $mode,
				'year' => (int) substr($day, 0, 4),
				'month' => substr($day, 0, 7),
				'day' => $day,
				'date_from' => $day,
				'date_to' => $day,
				'group_by' => 'hour',
				'label' => 'Tanggal ' . $day,
			];
		}

		if ($mode === 'custom') {
			$date_from = $this->valid_date((string) $this->input->get('date_from', true), $today->format('Y-m-01'));
			$date_to = $this->valid_date((string) $this->input->get('date_to', true), $today->format('Y-m-d'));
			if ($date_from > $date_to) {
				$tmp = $date_from;
				$date_from = $date_to;
				$date_to = $tmp;
			}
			$days = (new DateTime($date_from))->diff(new DateTime($date_to))->days;
			return [
				'mode' => $mode,
				'year' => (int) substr($date_from, 0, 4),
				'month' => substr($date_from, 0, 7),
				'day' => $today->format('Y-m-d'),
				'date_from' => $date_from,
				'date_to' => $date_to,
				'group_by' => $days > 92 ? 'month' : 'day',
				'label' => $date_from . ' s.d. ' . $date_to,
			];
		}

		$month = preg_match('/^\d{4}-\d{2}$/', (string) $this->input->get('month', true)) ? (string) $this->input->get('month', true) : $today->format('Y-m');
		$date_from = $month . '-01';
		$date_to = (new DateTime($date_from))->format('Y-m-t');

		return [
			'mode' => 'month',
			'year' => (int) substr($month, 0, 4),
			'month' => $month,
			'day' => $today->format('Y-m-d'),
			'date_from' => $date_from,
			'date_to' => $date_to,
			'group_by' => 'day',
			'label' => 'Bulan ' . $month,
		];
	}

	private function valid_date($value, $fallback)
	{
		$date = DateTime::createFromFormat('Y-m-d', $value);
		return $date && $date->format('Y-m-d') === $value ? $value : $fallback;
	}

	private function visit_report_payload($recent_limit)
	{
		$period = $this->resolve_period();
		$scope = $this->visit_scope();
		$channel_breakdown = $this->Report_model->visit_breakdown($period['date_from'], $period['date_to'], 'visit_channel', $scope);
		$origin_breakdown = $this->Report_model->visit_breakdown($period['date_from'], $period['date_to'], 'visit_origin', $scope);
		$method_breakdown = $this->Report_model->visit_breakdown($period['date_from'], $period['date_to'], 'checkin_method', $scope);
		$gender_breakdown = $this->Report_model->visit_gender_breakdown($period['date_from'], $period['date_to'], $scope);
		$trend = $this->Report_model->visit_trend($period['date_from'], $period['date_to'], $period['group_by'], $scope);
		$mode_breakdown = $this->Report_model->visit_mode_breakdown($period['date_from'], $period['date_to']);
		$mode_totals = ['offline' => ['people' => 0, 'entries' => 0], 'online' => ['people' => 0, 'entries' => 0]];
		foreach ($mode_breakdown as $row) if (isset($mode_totals[$row['label']])) $mode_totals[$row['label']] = ['people' => (int) $row['people'], 'entries' => (int) $row['entries']];

		return [
			'period' => $period,
			'summary' => $this->Report_model->visit_summary($period['date_from'], $period['date_to'], $scope),
			'visit_scope' => $scope,
			'mode_totals' => $mode_totals,
			'channel_breakdown' => $channel_breakdown,
			'origin_breakdown' => $origin_breakdown,
			'method_breakdown' => $method_breakdown,
			'gender_breakdown' => $gender_breakdown,
			'purpose_breakdown' => $this->Report_model->visit_purpose_breakdown($period['date_from'], $period['date_to'], $scope),
			'gender_labels' => $this->gender_labels(),
			'trend' => $trend,
			'recent_visits' => $recent_limit > 0 ? $this->Report_model->recent_visits($period['date_from'], $period['date_to'], $recent_limit, $scope) : [],
			'channel_labels' => $this->channel_labels(),
			'origin_labels' => $this->origin_labels(),
			'method_labels' => $this->method_labels(),
			'chart_payload' => [
				'trend' => [
					'labels' => array_column($trend, 'period'),
					'people' => array_map('intval', array_column($trend, 'people')),
					'entries' => array_map('intval', array_column($trend, 'entries')),
				],
				'channels' => [
					'labels' => array_map([$this, 'channel_label'], array_column($channel_breakdown, 'label')),
					'people' => array_map('intval', array_column($channel_breakdown, 'people')),
				],
				'genders' => [
					'labels' => array_map(function($value){$labels=$this->gender_labels();return $labels[$value]??$value;},array_column($gender_breakdown,'label')),
					'people' => array_map('intval',array_column($gender_breakdown,'people')),
				],
				'origins' => [
					'labels' => array_map([$this, 'origin_label'], array_column($origin_breakdown, 'label')),
					'people' => array_map('intval', array_column($origin_breakdown, 'people')),
				],
			],
		];
	}

	private function gender_labels()
	{
		return ['male'=>'Laki-laki','female'=>'Perempuan','unknown'=>'Belum diketahui'];
	}

	private function decorate_visit_tabs($html,$active)
	{
		$tabs='<div class="container-xl mt-3"><div class="nav workspace-tabs"><a class="nav-link '.($active==='summary'?'active':'').'" href="'.base_url('reports/visits').'"><i class="ti ti-chart-bar me-1"></i>Kunjungan</a><a class="nav-link '.($active==='format'?'active':'').'" href="'.base_url('reports/visits?tab=format').'"><i class="ti ti-table-options me-1"></i>Format Laporan</a><a class="nav-link '.($active==='members'?'active':'').'" href="'.base_url('reports/members').'"><i class="ti ti-users-plus me-1"></i>Anggota Baru</a></div></div>';
		return str_replace('<div class="page-body">',$tabs.'<div class="page-body">',$html);
	}

	private function decorate_visit_scope($html,$scope,array $totals,$page)
	{
		$options='<option value="all"'.($scope==='all'?' selected':'').'>Semua kunjungan</option><option value="offline"'.($scope==='offline'?' selected':'').'>Offline / kunjungan fisik</option><option value="online"'.($scope==='online'?' selected':'').'>Online / layanan digital</option>';
		$field='<div class="col-md-3"><label class="form-label">Jenis kunjungan</label><select class="form-select" name="visit_scope">'.$options.'</select><div class="form-hint">Offline mencakup buku tamu/check-in fisik dari INLIS dan Pustaka.</div></div>';
		if($page==='summary'){
			$cards='<div class="row g-3 mb-3"><div class="col-md-6"><div class="card admin-card h-100"><div class="card-body d-flex align-items-center gap-3"><span class="avatar bg-blue-lt text-blue"><i class="ti ti-building-community"></i></span><div><div class="text-secondary small fw-bold">KUNJUNGAN OFFLINE</div><div class="h2 mb-0">'.number_format((int)$totals['offline']['people'],0,',','.').' orang</div><div class="text-secondary small">'.number_format((int)$totals['offline']['entries'],0,',','.').' entri · legacy INLIS dan check-in fisik Pustaka</div></div></div></div></div><div class="col-md-6"><div class="card admin-card h-100"><div class="card-body d-flex align-items-center gap-3"><span class="avatar bg-green-lt text-green"><i class="ti ti-world"></i></span><div><div class="text-secondary small fw-bold">KUNJUNGAN ONLINE</div><div class="h2 mb-0">'.number_format((int)$totals['online']['people'],0,',','.').' orang</div><div class="text-secondary small">'.number_format((int)$totals['online']['entries'],0,',','.').' entri · dashboard dan akses koleksi digital</div></div></div></div></div></div>';
			return str_replace('<div class="report-kpi-grid">',$cards.'<div class="report-kpi-grid">',$html);
		}
		$marker='<div class="col-md-3"><label class="form-label">Variabel baris</label>';
		$html=str_replace($marker,$field.$marker,$html);
		$scopeLabel=$scope==='offline'?'Offline / kunjungan fisik':($scope==='online'?'Online / layanan digital':'Semua kunjungan');
		return str_replace('<div class="text-secondary small">Pilih dimensi baris, kelompok kolom, dan pemecah jenis kelamin.</div>','<div class="text-secondary small">Pilih jenis kunjungan, dimensi baris, kelompok kolom, dan pemecah jenis kelamin.</div><span class="badge bg-blue-lt mt-1">'.html_escape($scopeLabel).'</span>',$html);
	}

	private function decorate_visit_scope_export($html,$scope,$mode)
	{
		$label=$scope==='offline'?'Offline / kunjungan fisik':($scope==='online'?'Online / layanan digital':'Semua kunjungan');
		if($mode==='print')return str_replace('<div><span>Sumber</span><strong>member_visits</strong></div>','<div><span>Jenis kunjungan</span><strong>'.html_escape($label).'</strong></div>',$html);
		return str_replace('<tr><td>Dibuat</td>','<tr><td>Jenis kunjungan</td><td colspan="9">'.html_escape($label).'</td></tr><tr><td>Dibuat</td>',$html);
	}

	private function custom_visit_variables()
	{
		return ['date'=>'Tanggal','month'=>'Bulan','purpose'=>'Tujuan Kunjungan','visit_mode'=>'Jenis Kunjungan (Offline/Online)','profile_group'=>'Kelompok Pengunjung','gender'=>'Jenis Kelamin','age_group'=>'Kelompok Usia','institution'=>'Instansi / Rombongan','education'=>'Pendidikan','profession'=>'Pekerjaan','member_type'=>'Jenis Anggota','location'=>'Lokasi','channel'=>'Kanal Kunjungan','origin'=>'Asal Layanan','none'=>'Tanpa Pengelompokan'];
	}

	private function custom_visit_config()
	{
		$allowed=array_keys($this->custom_visit_variables());$row=(string)$this->input->get('row_variable',true);$column=(string)$this->input->get('column_variable',true);$split=(string)$this->input->get('split_variable',true);
		$row=in_array($row,$allowed,true)&&$row!=='none'?$row:'date';$column=in_array($column,$allowed,true)?$column:'profile_group';$split=in_array($split,['none','gender'],true)?$split:'gender';if($column==='gender')$split='none';return ['row'=>$row,'column'=>$column,'split'=>$split,'scope'=>$this->visit_scope(),'title'=>trim((string)$this->input->get('report_title',true))?:'PENGUNJUNG'];
	}

	private function build_custom_visit_report(array $period,array $config)
	{
		$hasDemographics=$this->db->table_exists('member_visit_demographics');
		$select='mv.visited_at,mv.visitor_count,mv.gender_label visit_gender,mv.education_label visit_education,mv.profession_label visit_profession,mv.group_name,mv.location_label,mv.visit_channel,mv.visit_origin,mv.metadata_json,m.gender member_gender,m.gender_label member_gender_label,m.birth_date,m.education,m.education_label member_education,m.occupation,m.occupation_label,m.member_type,m.member_type_label';
		if($hasDemographics)$select.=',d.gender demographic_gender,d.age_group demographic_age_group,d.education_label demographic_education,d.profession_label demographic_profession,d.people_count demographic_count';
		if(in_array('purpose',[$config['row'],$config['column']],true))$select.=','.$this->Report_model->visit_purpose_expression().' AS visit_purpose';
		$query=$this->db->select($select,false)->from('member_visits mv')->join('members m','m.id=mv.member_id','left');if($hasDemographics)$query->join('member_visit_demographics d','d.visit_id=mv.id','left');if($config['scope']==='online')$query->where_in('mv.visit_channel',['member_dashboard','digital_access']);elseif($config['scope']==='offline')$query->where_not_in('mv.visit_channel',['member_dashboard','digital_access']);
		$rows=$query->where('mv.visited_at >=',$period['date_from'].' 00:00:00')->where('mv.visited_at <=',$period['date_to'].' 23:59:59')->order_by('mv.visited_at','ASC')->get()->result_array();
		$matrix=[];$columnTotals=[];$splitTotals=[];$grand=0;$excludedGender=0;$observedColumns=[];$observedSplits=[];$usesGender=$config['split']==='gender'||$config['row']==='gender'||$config['column']==='gender';
		foreach($rows as $raw){$derived=$this->custom_visit_dimensions($raw);$count=max(1,(int)($raw['demographic_count']??$raw['visitor_count']??1));if($usesGender&&($derived['gender']??'unknown')==='unknown'){$excludedGender+=$count;continue;}$rk=$derived[$config['row']]??'Tidak diketahui';$ck=$config['column']==='none'?'Jumlah':($derived[$config['column']]??'Tidak diketahui');$sk=$config['split']==='gender'?($derived['gender']??'unknown'):'total';$matrix[$rk][$ck][$sk]=($matrix[$rk][$ck][$sk]??0)+$count;$columnTotals[$ck][$sk]=($columnTotals[$ck][$sk]??0)+$count;$splitTotals[$sk]=($splitTotals[$sk]??0)+$count;$observedColumns[$ck]=1;$observedSplits[$sk]=1;$grand+=$count;}
		$rowKeys=$this->sort_custom_values(array_keys($matrix),$config['row']);$columns=$this->sort_custom_values(array_keys($observedColumns),$config['column']);$splits=$config['split']==='gender'?['male','female']:['total'];
		return ['rows'=>$rowKeys,'columns'=>$columns,'splits'=>$splits,'matrix'=>$matrix,'column_totals'=>$columnTotals,'split_totals'=>$splitTotals,'grand_total'=>$grand,'excluded_gender'=>$excludedGender,'row_label'=>$this->custom_visit_variables()[$config['row']]??$config['row'],'column_label'=>$this->custom_visit_variables()[$config['column']]??$config['column']];
	}

	private function custom_visit_dimensions(array $r)
	{
		$genderRaw=strtolower(trim((string)(($r['demographic_gender']??'')?:($r['visit_gender']?:($r['member_gender_label']?:$r['member_gender'])))));$gender=in_array($genderRaw,['l','lk','laki-laki','laki laki','pria','male'],true)?'male':(in_array($genderRaw,['p','pr','perempuan','wanita','female'],true)?'female':'unknown');
		$age=null;if(!empty($r['birth_date'])&&!empty($r['visited_at'])){$birth=new DateTime($r['birth_date']);$visit=new DateTime($r['visited_at']);if($birth<=$visit)$age=$birth->diff($visit)->y;}
		$ageGroup=trim((string)($r['demographic_age_group']??''))?:($age===null?'Tidak diketahui':($age<=6?'0–6':($age<=12?'7–12':($age<=15?'13–15':($age<=18?'16–18':($age<=24?'19–24':($age<=44?'25–44':($age<=59?'45–59':'60+'))))))));
		$education=trim((string)(($r['demographic_education']??'')?:($r['visit_education']?:($r['member_education']?:$r['education']))))?:'Tidak diketahui';$profession=trim((string)(($r['demographic_profession']??'')?:($r['visit_profession']?:($r['occupation_label']?:$r['occupation']))))?:'Tidak diketahui';$memberType=trim((string)($r['member_type_label']?:$r['member_type']))?:'Nonmember';
		$institution=trim((string)($r['group_name']??''));if($institution===''&&!empty($r['metadata_json'])){$meta=json_decode($r['metadata_json'],true);if(is_array($meta))$institution=trim((string)($meta['institution']??$meta['agency']??$meta['school']??''));}$institution=$institution?:'Tidak diketahui';
		$profile=$this->profile_group($education,$profession,$memberType,$age);
		$visitMode=in_array($r['visit_channel']??'', ['member_dashboard','digital_access'], true)?'Online':'Offline';
		return ['date'=>date('d-m-Y',strtotime($r['visited_at'])),'month'=>date('Y-m',strtotime($r['visited_at'])),'purpose'=>$r['visit_purpose']??'Belum diisi','visit_mode'=>$visitMode,'profile_group'=>$profile,'gender'=>$gender,'age_group'=>$ageGroup,'institution'=>$institution,'education'=>$education,'profession'=>$profession,'member_type'=>$memberType,'location'=>trim((string)$r['location_label'])?:'Tidak diketahui','channel'=>$this->channel_label($r['visit_channel']?:'unknown'),'origin'=>$this->origin_label($r['visit_origin']?:'unknown'),'none'=>'Jumlah'];
	}

	private function visit_scope()
	{
		$scope=(string)$this->input->get('visit_scope',true);
		return in_array($scope,['all','offline','online'],true)?$scope:'all';
	}

	private function profile_group($education,$profession,$memberType,$age)
	{
		$text=strtolower($education.' '.$memberType);$job=strtolower($profession);if(preg_match('/pegawai|pns|asn|karyawan|guru|dosen|polisi|tni/',$job))return 'PEGAWAI';if(preg_match('/mahasiswa|universitas|sarjana|diploma|\bs[123]\b|\bd[1-4]\b/',$text))return 'MAHASISWA';if(preg_match('/\bsd\b|mi|sekolah dasar/',$text)||($age!==null&&$age>=7&&$age<=12))return 'SD';if(preg_match('/sltp|smp|mts/',$text)||($age!==null&&$age>=13&&$age<=15))return 'SLTP';if(preg_match('/slta|sma|smk|madrasah aliyah|\bma\b/',$text)||($age!==null&&$age>=16&&$age<=18))return 'SLTA';return 'UMUM';
	}

	private function sort_custom_values(array $values,$dimension)
	{
		if($dimension==='date'){usort($values,function($a,$b){$da=DateTime::createFromFormat('d-m-Y',$a);$db=DateTime::createFromFormat('d-m-Y',$b);return ($da?$da->getTimestamp():0)<=>($db?$db->getTimestamp():0);});return $values;}
		$referenceTables=['education'=>['master_pendidikan','master_jenjang_pendidikan'],'profession'=>['master_pekerjaan'],'member_type'=>['jenis_anggota'],'location'=>['locations']];
		if(isset($referenceTables[$dimension])&&$this->db->table_exists('inlislite_master_references')){
			$references=$this->db->select('source_id,name')->from('inlislite_master_references')->where_in('source_table',$referenceTables[$dimension])->get()->result_array();
			usort($references,function($a,$b){$ai=(string)$a['source_id'];$bi=(string)$b['source_id'];if(ctype_digit($ai)&&ctype_digit($bi))return (int)$ai<=>(int)$bi;return strnatcasecmp($ai,$bi);});
			$rank=[];foreach($references as $index=>$reference){$key=strtolower(trim((string)$reference['name']));if($key!==''&&!isset($rank[$key]))$rank[$key]=$index;}
			usort($values,function($a,$b)use($rank){$ar=$rank[strtolower(trim((string)$a))]??PHP_INT_MAX;$br=$rank[strtolower(trim((string)$b))]??PHP_INT_MAX;return $ar===$br?strnatcasecmp($a,$b):$ar<=>$br;});return $values;
		}
		$order=$dimension==='profile_group'?['SD','SLTP','SLTA','MAHASISWA','UMUM','PEGAWAI']:($dimension==='age_group'?['0–6','7–12','13–15','16–18','19–24','25–44','45–59','60+','Tidak diketahui']:[]);if(!$order){sort($values,SORT_NATURAL|SORT_FLAG_CASE);return $values;}$rank=array_flip($order);usort($values,function($a,$b)use($rank){$ar=$rank[$a]??999;$br=$rank[$b]??999;return $ar===$br?strnatcasecmp($a,$b):$ar<=>$br;});return $values;
	}

	private function visit_excel_text($value)
	{
		$value = (string)$value;
		if (preg_match('/^[\x00-\x20]*[=+@-]/u',$value)) $value = "'".$value;
		return html_escape($value);
	}

	private function custom_visit_excel(array $period,array $config,array $report)
	{
		$gender=['male'=>'L','female'=>'P','total'=>'Jumlah'];$splitCount=max(1,count($report['splits']));$colspan=1+count($report['columns'])*$splitCount+$splitCount;$scopeLabel=$config['scope']==='offline'?'OFFLINE / KUNJUNGAN FISIK':($config['scope']==='online'?'ONLINE / LAYANAN DIGITAL':'SEMUA KUNJUNGAN');$html='<!doctype html><html><head><meta charset="utf-8"><style>table{border-collapse:collapse}th,td{border:1px solid #555;padding:5px;text-align:center}.title{font-size:16px;font-weight:bold;border:0}.left{text-align:left}</style></head><body><table><tr><th class="title" colspan="'.$colspan.'">'.html_escape($config['title']).'</th></tr><tr><th class="title" colspan="'.$colspan.'">PERPUSTAKAAN UMUM KABUPATEN REMBANG</th></tr><tr><th class="title" colspan="'.$colspan.'">'.html_escape($this->custom_period_heading($period)).'</th></tr><tr><th class="title" colspan="'.$colspan.'">'.html_escape($scopeLabel).'</th></tr><tr><td colspan="'.$colspan.'"></td></tr><tr><th rowspan="2">'.html_escape($report['row_label']).'</th>';
		foreach($report['columns'] as $column)$html.='<th colspan="'.$splitCount.'">'.$this->visit_excel_text($column).'</th>';$html.='<th colspan="'.$splitCount.'">JUMLAH</th></tr><tr>';foreach($report['columns'] as $column)foreach($report['splits'] as $split)$html.='<th>'.html_escape($gender[$split]??$split).'</th>';foreach($report['splits'] as $split)$html.='<th>'.html_escape($gender[$split]??$split).'</th>';$html.='</tr>';
		foreach($report['rows'] as $row){$html.='<tr><td class="left">'.$this->visit_excel_text($row).'</td>';foreach($report['columns'] as $column)foreach($report['splits'] as $split)$html.='<td>'.(int)($report['matrix'][$row][$column][$split]??0).'</td>';foreach($report['splits'] as $split){$total=0;foreach($report['columns'] as $column)$total+=(int)($report['matrix'][$row][$column][$split]??0);$html.='<td>'.$total.'</td>';}$html.='</tr>';}$html.='<tr><th class="left">TOTAL</th>';foreach($report['columns'] as $column)foreach($report['splits'] as $split)$html.='<th>'.(int)($report['column_totals'][$column][$split]??0).'</th>';foreach($report['splits'] as $split)$html.='<th>'.(int)($report['split_totals'][$split]??0).'</th>';$html.='</tr>';if(!empty($report['excluded_gender']))$html.='<tr><td class="left" colspan="'.$colspan.'">Catatan: '.(int)$report['excluded_gender'].' kunjungan tidak dimasukkan ke tabel L/P karena jenis kelaminnya belum tersedia.</td></tr>';$html.='</table></body></html>';return $html;
	}

	private function custom_period_heading(array $period)
	{
		$months=[1=>'JANUARI','FEBRUARI','MARET','APRIL','MEI','JUNI','JULI','AGUSTUS','SEPTEMBER','OKTOBER','NOVEMBER','DESEMBER'];if($period['mode']==='month'){$time=strtotime($period['date_from']);return 'BULAN '.$months[(int)date('n',$time)].' '.date('Y',$time);}if($period['mode']==='year')return 'TAHUN '.(int)$period['year'];if($period['mode']==='day')return 'TANGGAL '.date('d-m-Y',strtotime($period['date_from']));return 'PERIODE '.date('d-m-Y',strtotime($period['date_from'])).' S.D. '.date('d-m-Y',strtotime($period['date_to']));
	}

	private function decorate_gender_report($html,array $rows,$mode)
	{
		$labels=$this->gender_labels();$body='';$grand_total=array_sum(array_map('intval',array_column($rows,'people')));
		foreach($rows as $row){$label=html_escape($labels[$row['label']]??$row['label']);$people=(int)$row['people'];$entries=(int)$row['entries'];
			if($mode==='screen')$body.='<tr><td>'.$label.'</td><td class="fw-bold">'.number_format($people,0,',','.').'</td><td>'.number_format($entries,0,',','.').'</td><td>'.number_format($people/max(1,$grand_total)*100,1,',','.').'%</td></tr>';
			elseif($mode==='print')$body.='<tr><td>'.$label.'</td><td class="number">'.number_format($people,0,',','.').'</td><td class="number">'.number_format($entries,0,',','.').'</td></tr>';
			else $body.='<tr><td>'.$label.'</td><td class="number">'.$people.'</td><td class="number">'.$entries.'</td><td colspan="7"></td></tr>';
		}
		if($mode==='screen'){$block='<div class="card admin-card report-card mt-3 mb-3"><div class="card-header"><div><h2 class="card-title">Kunjungan Berdasarkan Jenis Kelamin</h2><div class="text-secondary small">Menggabungkan data kunjungan dan profil member; data kosong tetap dihitung.</div></div></div><div class="table-responsive"><table class="table table-vcenter card-table"><thead><tr><th>Jenis Kelamin</th><th>Orang</th><th>Entri</th><th>Persentase</th></tr></thead><tbody>'.$body.'</tbody></table></div></div>';return str_replace('<div class="row g-3 mt-0">',$block.'<div class="row g-3 mt-0">',$html);}
		if($mode==='print'){$block='<section class="section"><h2>Kunjungan Berdasarkan Jenis Kelamin</h2><table><thead><tr><th>Jenis Kelamin</th><th>Orang</th><th>Entri</th></tr></thead><tbody>'.$body.'</tbody></table></section>'; $marker="<section class=\"section\">\n\t\t<h2>Tren Kunjungan</h2>";return str_replace($marker,$block.$marker,$html);}
		$block='<tr><td colspan="10"></td></tr><tr><td class="section" colspan="10">Kunjungan Berdasarkan Jenis Kelamin</td></tr><tr><th>Jenis Kelamin</th><th>Orang</th><th>Entri</th><th colspan="7"></th></tr>'.$body.'<tr><td colspan="10"></td></tr>';return str_replace('<tr><td class="section" colspan="10">Tren</td></tr>',$block.'<tr><td class="section" colspan="10">Tren</td></tr>',$html);
	}

	private function channel_label($value)
	{
		$labels = $this->channel_labels();

		return $labels[$value] ?? $value;
	}

	private function origin_label($value)
	{
		$labels = $this->origin_labels();

		return $labels[$value] ?? $value;
	}

	private function channel_labels()
	{
		return [
			'inlislite_guestbook' => 'Buku Tamu Legacy',
			'library_guestbook' => 'Buku Tamu Perpus',
			'member_dashboard' => 'Online Dashboard',
			'digital_access' => 'Baca Digital',
			'reading_point' => 'Pojok Baca',
			'service_monitor' => 'Monitor Pelayanan',
			'qr_checkin' => 'Scan QR',
			'unknown' => 'Tidak diketahui',
		];
	}

	private function origin_labels()
	{
		return [
			'library' => 'Perpustakaan',
			'reading_point' => 'Pojok Baca',
			'digital_external' => 'Online Luar Lokasi',
			'digital_internal' => 'Internal',
			'legacy' => 'Data Lama',
			'unknown' => 'Tidak diketahui',
		];
	}

	private function method_labels()
	{
		return [
			'guest_form' => 'Form Tamu',
			'member_search' => 'Cari Member',
			'member_qr' => 'QR Member',
			'member_gps' => 'GPS Pojok Baca',
			'dashboard_auto' => 'Dashboard',
			'reader_quota' => 'Reader',
			'legacy_sync' => 'Sinkron Data Lama',
			'unknown' => 'Tidak diketahui',
		];
	}
}
