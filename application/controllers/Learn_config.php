<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Learn_config extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Learn_points_model');
    }

    public function index()
    {
        $this->require_permission('learn_config.index', 'view');
        $rules  = $this->Learn_points_model->get_rules();
        $badges = $this->Learn_points_model->get_badges();
        $stats  = $this->Learn_points_model->get_stats();

        $this->render('learn/config/index', [
            'title'         => 'Konfigurasi Poin & Lencana',
            'active_menu'   => 'learn_config',
            'rules'         => $rules,
            'badges'        => $badges,
            'stats'         => $stats,
            'badge_criteria_labels' => $this->_criteria_labels(),
        ]);
    }

    // ── Point Rules ───────────────────────────────────────────────────────────

    public function store_rule()
    {
        $this->require_permission('learn_config.index', 'create');
        $data = [
            'action_code'    => $this->input->post('action_code'),
            'label'          => $this->input->post('label'),
            'description'    => $this->input->post('description'),
            'points'         => (int) $this->input->post('points'),
            'cooldown_hours' => (int) $this->input->post('cooldown_hours'),
            'is_active'      => $this->input->post('is_active') ? 1 : 0,
        ];

        if (empty($data['action_code']) || empty($data['label'])) {
            $this->session->set_flashdata('error', 'Kode aksi dan label wajib diisi.');
            redirect('learn-config');
        }

        $this->Learn_points_model->create_rule($data);
        $this->audit_event('learn_config', 'create_rule', null, ['code' => $data['action_code']]);
        $this->session->set_flashdata('success', 'Aturan poin berhasil ditambahkan.');
        redirect('learn-config');
    }

    public function update_rule($id)
    {
        $this->require_permission('learn_config.index', 'update');
        $rule = $this->Learn_points_model->get_rule($id);
        if (!$rule) show_404();

        $data = [
            'action_code'    => $this->input->post('action_code'),
            'label'          => $this->input->post('label'),
            'description'    => $this->input->post('description'),
            'points'         => (int) $this->input->post('points'),
            'cooldown_hours' => (int) $this->input->post('cooldown_hours'),
            'is_active'      => $this->input->post('is_active') ? 1 : 0,
        ];

        $this->Learn_points_model->update_rule($id, $data);
        $this->audit_event('learn_config', 'update_rule', $id, ['code' => $data['action_code']]);
        $this->session->set_flashdata('success', 'Aturan poin berhasil diperbarui.');
        redirect('learn-config');
    }

    public function delete_rule($id)
    {
        $this->require_permission('learn_config.index', 'delete');
        $rule = $this->Learn_points_model->get_rule($id);
        if (!$rule) show_404();

        $this->Learn_points_model->delete_rule($id);
        $this->audit_event('learn_config', 'delete_rule', $id);
        $this->session->set_flashdata('success', 'Aturan poin dihapus.');
        redirect('learn-config');
    }

    // ── Badges ────────────────────────────────────────────────────────────────

    public function store_badge()
    {
        $this->require_permission('learn_config.index', 'create');
        $data = [
            'code'                => $this->input->post('code'),
            'name'                => $this->input->post('name'),
            'description'         => $this->input->post('description'),
            'icon'                => $this->input->post('icon') ?: 'ti-award',
            'color'               => $this->input->post('color') ?: '#3b82f6',
            'criteria_type'       => $this->input->post('criteria_type'),
            'criteria_value'      => (int) $this->input->post('criteria_value'),
            'criteria_subject_id' => $this->input->post('criteria_subject_id') ?: null,
            'is_active'           => $this->input->post('is_active') ? 1 : 0,
        ];

        if (empty($data['code']) || empty($data['name'])) {
            $this->session->set_flashdata('error', 'Kode dan nama lencana wajib diisi.');
            redirect('learn-config#badges');
        }

        $this->Learn_points_model->create_badge($data);
        $this->audit_event('learn_config', 'create_badge', null, ['code' => $data['code']]);
        $this->session->set_flashdata('success', 'Lencana berhasil ditambahkan.');
        redirect('learn-config#badges');
    }

    public function update_badge($id)
    {
        $this->require_permission('learn_config.index', 'update');
        $badge = $this->Learn_points_model->get_badge($id);
        if (!$badge) show_404();

        $data = [
            'code'                => $this->input->post('code'),
            'name'                => $this->input->post('name'),
            'description'         => $this->input->post('description'),
            'icon'                => $this->input->post('icon') ?: 'ti-award',
            'color'               => $this->input->post('color') ?: '#3b82f6',
            'criteria_type'       => $this->input->post('criteria_type'),
            'criteria_value'      => (int) $this->input->post('criteria_value'),
            'criteria_subject_id' => $this->input->post('criteria_subject_id') ?: null,
            'is_active'           => $this->input->post('is_active') ? 1 : 0,
        ];

        $this->Learn_points_model->update_badge($id, $data);
        $this->audit_event('learn_config', 'update_badge', $id, ['code' => $data['code']]);
        $this->session->set_flashdata('success', 'Lencana berhasil diperbarui.');
        redirect('learn-config#badges');
    }

    public function delete_badge($id)
    {
        $this->require_permission('learn_config.index', 'delete');
        $badge = $this->Learn_points_model->get_badge($id);
        if (!$badge) show_404();

        $this->Learn_points_model->delete_badge($id);
        $this->audit_event('learn_config', 'delete_badge', $id);
        $this->session->set_flashdata('success', 'Lencana dihapus.');
        redirect('learn-config#badges');
    }

    // ── Leaderboard ───────────────────────────────────────────────────────────

    public function leaderboard()
    {
        $this->require_permission('learn_config.index', 'view');
        $days   = (int) ($this->input->get('days') ?: 30);
        $board  = $this->Learn_points_model->get_leaderboard(50, $days);

        $this->render('learn/config/leaderboard', [
            'title'       => 'Leaderboard Poin',
            'active_menu' => 'learn_config',
            'leaderboard' => $board,
            'days'        => $days,
        ]);
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    private function _criteria_labels()
    {
        return [
            'points_total'   => 'Total Poin',
            'quiz_complete'  => 'Jumlah Quiz Selesai',
            'quiz_pass'      => 'Jumlah Quiz Lulus',
            'quiz_perfect'   => 'Nilai Sempurna (100%)',
            'game_complete'  => 'Jumlah Game Selesai',
            'visit_count'    => 'Jumlah Kunjungan',
            'book_read'      => 'Buku Dibaca',
            'custom'         => 'Custom (manual)',
        ];
    }
}
