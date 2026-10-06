<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Learn_english_rpg_progress extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Learn_english_rpg_progress_model');
        $this->load->model('Learn_english_rpg_world_model');
    }

    public function index()
    {
        $this->require_permission('learn_english_rpg.index', 'view');
        $filters = [
            'q' => trim((string) $this->input->get('q', true)),
            'status' => (string) $this->input->get('status', true),
        ];
        $this->render('learn/english_rpg/progress_users', [
            'title' => 'Progres Pengguna English RPG',
            'active_menu' => 'learn_english_rpg',
            'players' => $this->Learn_english_rpg_progress_model->players($filters),
            'summary' => $this->Learn_english_rpg_progress_model->summary(),
            'filters' => $filters,
        ]);
    }

    public function user($user_id)
    {
        $this->require_permission('learn_english_rpg.index', 'view');
        $data = $this->Learn_english_rpg_progress_model->user_detail((int) $user_id);
        if (!$data) show_404();
        $this->render('learn/english_rpg/progress_user', [
            'title' => 'Riwayat English RPG - '.$data['user']['full_name'],
            'active_menu' => 'learn_english_rpg',
            'data' => $data,
            'season2' => $this->Learn_english_rpg_world_model->progress_for_user((int) $user_id),
        ]);
    }

    public function reset_season2($user_id)
    {
        $this->require_permission('learn_english_rpg.index', 'delete');
        if (strtoupper((string) $this->input->method()) !== 'POST') show_error('Metode tidak diizinkan.', 405);
        $result = $this->Learn_english_rpg_world_model->reset((int) $user_id);
        $this->audit_event('learn_english_rpg.season2_progress_reset', 'auth_user', (int) $user_id, null, ['ok' => !empty($result['ok']), 'map' => 'season2_village']);
        $this->session->set_flashdata(!empty($result['ok']) ? 'success' : 'error', $result['message']);
        redirect('learn-english-rpg/progress/user/'.(int) $user_id);
    }

    public function reset_from($user_id, $episode_id)
    {
        $this->require_permission('learn_english_rpg.index', 'delete');
        if (strtoupper((string) $this->input->method()) !== 'POST') show_error('Metode tidak diizinkan.', 405);
        $result = $this->Learn_english_rpg_progress_model->reset_from((int) $user_id, (int) $episode_id);
        $this->audit_event('learn_english_rpg.progress_reset_from', 'auth_user', (int) $user_id, null, [
            'episode_id' => (int) $episode_id,
            'episode_code' => $result['episode_code'] ?? null,
            'affected_chapters' => $result['affected_chapters'] ?? [],
            'ok' => !empty($result['ok']),
        ]);
        $this->session->set_flashdata(!empty($result['ok']) ? 'success' : 'error', $result['message']);
        redirect('learn-english-rpg/progress/user/'.(int) $user_id);
    }
}
