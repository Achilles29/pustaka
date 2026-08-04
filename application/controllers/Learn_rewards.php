<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Learn_rewards — admin: katalog hadiah "Tukar Poin → Token Baca"
 * + riwayat penukaran oleh member.
 */
class Learn_rewards extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Learn_rewards_model');
    }

    public function index()
    {
        $this->require_permission('learn_rewards.index', 'view');

        $this->render('learn/rewards/index', [
            'title'        => 'Tukar Poin — Katalog Hadiah',
            'active_menu'  => 'learn_rewards',
            'catalog'      => $this->Learn_rewards_model->get_catalog(),
            'stats'        => $this->Learn_rewards_model->stats(),
            'redemptions'  => $this->Learn_rewards_model->get_all_redemptions(15, 0),
            'can_create'   => $this->can('learn_rewards.index', 'create'),
            'can_edit'     => $this->can('learn_rewards.index', 'edit'),
            'can_delete'   => $this->can('learn_rewards.index', 'delete'),
        ]);
    }

    public function store()
    {
        $this->require_permission('learn_rewards.index', 'create');
        $data = $this->_input();

        if ($data['code'] === '' || $data['name'] === '') {
            $this->session->set_flashdata('error', 'Kode dan nama hadiah wajib diisi.');
            redirect('learn-rewards');
            return;
        }
        if ($this->Learn_rewards_model->code_exists($data['code'])) {
            $this->session->set_flashdata('error', 'Kode hadiah sudah digunakan.');
            redirect('learn-rewards');
            return;
        }

        $id = $this->Learn_rewards_model->create_reward($data);
        $this->audit_event('learn_rewards.create', 'learn_reward_catalog', $id, null, ['code' => $data['code']]);
        $this->session->set_flashdata('success', 'Hadiah berhasil ditambahkan.');
        redirect('learn-rewards');
    }

    public function update($id)
    {
        $this->require_permission('learn_rewards.index', 'edit');
        $reward = $this->Learn_rewards_model->get_reward($id);
        if (! $reward) { show_404(); return; }

        $data = $this->_input();
        if ($data['code'] === '' || $data['name'] === '') {
            $this->session->set_flashdata('error', 'Kode dan nama hadiah wajib diisi.');
            redirect('learn-rewards');
            return;
        }
        if ($this->Learn_rewards_model->code_exists($data['code'], (int) $id)) {
            $this->session->set_flashdata('error', 'Kode hadiah sudah digunakan hadiah lain.');
            redirect('learn-rewards');
            return;
        }

        $this->Learn_rewards_model->update_reward((int) $id, $data);
        $this->audit_event('learn_rewards.update', 'learn_reward_catalog', (int) $id, $reward, $data);
        $this->session->set_flashdata('success', 'Hadiah berhasil diperbarui.');
        redirect('learn-rewards');
    }

    public function delete($id)
    {
        $this->require_permission('learn_rewards.index', 'delete');
        $reward = $this->Learn_rewards_model->get_reward($id);
        if (! $reward) { show_404(); return; }

        $this->Learn_rewards_model->delete_reward((int) $id);
        $this->audit_event('learn_rewards.delete', 'learn_reward_catalog', (int) $id, $reward);
        $this->session->set_flashdata('success', 'Hadiah dihapus.');
        redirect('learn-rewards');
    }

    public function toggle($id)
    {
        $this->require_permission('learn_rewards.index', 'edit');
        if (! $this->Learn_rewards_model->get_reward($id)) { show_404(); return; }
        $this->Learn_rewards_model->toggle_reward((int) $id);
        $this->audit_event('learn_rewards.toggle', 'learn_reward_catalog', (int) $id);
        $this->session->set_flashdata('success', 'Status hadiah diperbarui.');
        redirect('learn-rewards');
    }

    public function redemptions()
    {
        $this->require_permission('learn_rewards.index', 'view');

        $per_page = 50;
        $page     = max(1, (int) $this->input->get('page', true));
        $total    = $this->Learn_rewards_model->count_redemptions();
        $pages    = max(1, (int) ceil($total / $per_page));
        $page     = min($page, $pages);

        $this->render('learn/rewards/redemptions', [
            'title'        => 'Riwayat Penukaran Poin',
            'active_menu'  => 'learn_rewards',
            'redemptions'  => $this->Learn_rewards_model->get_all_redemptions($per_page, ($page - 1) * $per_page),
            'pagination'   => ['total_rows' => $total, 'total_pages' => $pages, 'page' => $page, 'per_page' => $per_page],
        ]);
    }

    private function _input()
    {
        return [
            'code'                => trim((string) $this->input->post('code', true)),
            'name'                => trim((string) $this->input->post('name', true)),
            'description'         => $this->input->post('description', true),
            'icon'                => $this->input->post('icon', true) ?: 'ti-gift',
            'color'               => $this->input->post('color', true) ?: '#0ea5e9',
            'cost_points'         => (int) $this->input->post('cost_points', true),
            'quota_amount'        => (int) $this->input->post('quota_amount', true),
            'quota_unit'          => $this->input->post('quota_unit', true) ?: 'minutes',
            'token_validity_days' => (int) $this->input->post('token_validity_days', true),
            'stock'               => $this->input->post('stock', true),
            'per_user_limit'      => (int) $this->input->post('per_user_limit', true),
            'sort_order'          => (int) $this->input->post('sort_order', true),
            'is_active'           => $this->input->post('is_active') ? 1 : 0,
        ];
    }
}
