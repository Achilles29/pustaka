<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Access_monitor extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Site_access_log_model');
    }

    public function index()
    {
        $this->require_permission('access_monitor.index', 'view');
        $filters = ['q' => trim((string) $this->input->get('q', true)), 'visitor_type' => (string) $this->input->get('visitor_type', true), 'device_type' => (string) $this->input->get('device_type', true), 'uri_path' => (string) $this->input->get('uri_path', true), 'date_from' => (string) $this->input->get('date_from', true), 'date_to' => (string) $this->input->get('date_to', true)];
        $per_page = in_array((int) $this->input->get('per_page', true), [10, 25, 50, 100], true) ? (int) $this->input->get('per_page', true) : 25;
        $page = max(1, (int) $this->input->get('page', true));
        $total = $this->Site_access_log_model->count_logs($filters);
        $pages = max(1, (int) ceil($total / $per_page));
        $page = min($page, $pages);
        $this->render('access_monitor/index', ['title' => 'Monitor Akses Pustaka', 'stats' => $this->Site_access_log_model->stats_today(), 'logs' => $this->Site_access_log_model->get_logs($filters, $per_page, ($page - 1) * $per_page), 'route_options' => $this->Site_access_log_model->route_options(), 'filters' => $filters, 'pagination' => compact('total', 'pages', 'page', 'per_page')]);
    }
}
