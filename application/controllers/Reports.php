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

		$this->render('reports/visits', $this->visit_report_payload(12) + [
			'title' => 'Laporan Kunjungan',
		]);
	}

	public function visits_print()
	{
		$this->require_permission('reports.visits', 'export');

		$this->load->view('reports/visits_print', $this->visit_report_payload(200) + [
			'title' => 'Cetak Laporan Kunjungan',
			'generated_at' => date('Y-m-d H:i:s'),
		]);
	}

	public function visits_excel()
	{
		$this->require_permission('reports.visits', 'export');

		$payload = $this->visit_report_payload(0);
		$payload['rows'] = $this->Report_model->visit_rows($payload['period']['date_from'], $payload['period']['date_to'], 50000);
		$payload['generated_at'] = date('Y-m-d H:i:s');

		$filename = 'laporan-kunjungan-' . $payload['period']['date_from'] . '-sd-' . $payload['period']['date_to'] . '.xls';
		$this->output
			->set_content_type('application/vnd.ms-excel')
			->set_header('Content-Disposition: attachment; filename="' . $filename . '"')
			->set_header('Cache-Control: max-age=0')
			->set_output($this->load->view('reports/visits_excel', $payload, true));
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
		$channel_breakdown = $this->Report_model->visit_breakdown($period['date_from'], $period['date_to'], 'visit_channel');
		$origin_breakdown = $this->Report_model->visit_breakdown($period['date_from'], $period['date_to'], 'visit_origin');
		$method_breakdown = $this->Report_model->visit_breakdown($period['date_from'], $period['date_to'], 'checkin_method');
		$trend = $this->Report_model->visit_trend($period['date_from'], $period['date_to'], $period['group_by']);

		return [
			'period' => $period,
			'summary' => $this->Report_model->visit_summary($period['date_from'], $period['date_to']),
			'channel_breakdown' => $channel_breakdown,
			'origin_breakdown' => $origin_breakdown,
			'method_breakdown' => $method_breakdown,
			'trend' => $trend,
			'recent_visits' => $recent_limit > 0 ? $this->Report_model->recent_visits($period['date_from'], $period['date_to'], $recent_limit) : [],
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
				'origins' => [
					'labels' => array_map([$this, 'origin_label'], array_column($origin_breakdown, 'label')),
					'people' => array_map('intval', array_column($origin_breakdown, 'people')),
				],
			],
		];
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
			'inlislite_guestbook' => 'Buku Tamu INLISLite',
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
			'legacy_sync' => 'Sinkron INLISLite',
			'unknown' => 'Tidak diketahui',
		];
	}
}
