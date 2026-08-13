<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Patron_insights extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Patron_insights_model');
	}

	public function index()
	{
		// Menggunakan hak laporan yang sudah ada sehingga tidak membuka foto dan
		// histori pemustaka untuk peran di luar petugas analitik.
		$this->require_permission('reports.visits', 'view');
		$scope = $this->input->get('scope', true) === 'all' ? 'all' : 'month';
		$tab = $this->input->get('tab', true) === 'champions' ? 'champions' : 'overview';
		$month = (string) $this->input->get('month', true);
		$month = preg_match('/^\d{4}-\d{2}$/', $month) ? $month : date('Y-m');
		$date_from = $month . '-01';
		$date_to = date('Y-m-t', strtotime($date_from));
		$month_names = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
		$period_label = $scope === 'all' ? 'Sepanjang data tersedia' : $month_names[(int) date('n', strtotime($date_from))] . ' ' . date('Y', strtotime($date_from));

		$this->render('reports/patron_insights', [
			'title' => 'Sorotan Pemustaka',
			'filters' => compact('scope', 'month', 'tab'),
			'active_tab' => $tab,
			'period' => compact('date_from', 'date_to', 'period_label'),
			'insights' => $this->Patron_insights_model->get_dashboard($date_from, $date_to, $scope === 'all'),
		]);
	}
}
