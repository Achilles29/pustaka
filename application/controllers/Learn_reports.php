<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Learn_reports — admin: pilih member & lihat/cetak raport belajar.
 */
class Learn_reports extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Learn_report_model');
    }

    public function index()
    {
        $this->require_permission('learn_reports.index', 'view');

        $q        = trim((string) $this->input->get('q', true));
        $per_page = 25;
        $page     = max(1, (int) $this->input->get('page', true));
        $total    = $this->Learn_report_model->count_members($q);
        $pages    = max(1, (int) ceil($total / $per_page));
        $page     = min($page, $pages);

        $this->render('learn/reports/index', [
            'title'       => 'Raport Belajar',
            'active_menu' => 'learn_reports',
            'members'     => $this->Learn_report_model->search_members($q, $per_page, ($page - 1) * $per_page),
            'q'           => $q,
            'pagination'  => ['total_rows' => $total, 'total_pages' => $pages, 'page' => $page, 'per_page' => $per_page],
        ]);
    }

    /** Lembar raport (printable) untuk satu member. */
    public function view($user_id)
    {
        $this->require_permission('learn_reports.index', 'view');
        $member = $this->Learn_report_model->get_member($user_id);
        if (! $member) { show_404(); return; }

        // View standalone (tanpa layout admin) agar rapi saat dicetak.
        $this->load->view('learn/reports/sheet', [
            'member'  => $member,
            'report'  => $this->Learn_report_model->get_report($user_id),
            'is_admin' => true,
        ]);
    }
}
