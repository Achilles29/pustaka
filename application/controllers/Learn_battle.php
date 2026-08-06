<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Learn_battle — admin: kelola pool soal Mode Battle + pantau ronde.
 */
class Learn_battle extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Learn_battle_model');
    }

    public function index()
    {
        $this->require_permission('learn_battle.index', 'view');
        $this->render('learn/battle/index', [
            'title'       => 'Mode Battle — Pool Soal',
            'active_menu' => 'learn_battle',
            'questions'   => $this->Learn_battle_model->get_questions(),
            'stats'       => $this->Learn_battle_model->stats(),
            'rooms'       => $this->Learn_battle_model->recent_rooms(15),
            'can_create'  => $this->can('learn_battle.index', 'create'),
            'can_edit'    => $this->can('learn_battle.index', 'edit'),
            'can_delete'  => $this->can('learn_battle.index', 'delete'),
        ]);
    }

    public function store()
    {
        $this->require_permission('learn_battle.index', 'create');
        if (! $this->_valid()) { redirect('learn-battle'); return; }
        $this->Learn_battle_model->create_question($this->_input());
        $this->session->set_flashdata('success', 'Soal battle ditambahkan.');
        redirect('learn-battle');
    }

    public function update($id)
    {
        $this->require_permission('learn_battle.index', 'edit');
        if (! $this->Learn_battle_model->get_question($id)) { show_404(); return; }
        if (! $this->_valid()) { redirect('learn-battle'); return; }
        $this->Learn_battle_model->update_question((int) $id, $this->_input());
        $this->session->set_flashdata('success', 'Soal battle diperbarui.');
        redirect('learn-battle');
    }

    public function delete($id)
    {
        $this->require_permission('learn_battle.index', 'delete');
        if (! $this->Learn_battle_model->get_question($id)) { show_404(); return; }
        $this->Learn_battle_model->delete_question((int) $id);
        $this->session->set_flashdata('success', 'Soal battle dihapus.');
        redirect('learn-battle');
    }

    private function _valid()
    {
        if (trim((string) $this->input->post('question', true)) === ''
            || trim((string) $this->input->post('option_a', true)) === ''
            || trim((string) $this->input->post('option_b', true)) === '') {
            $this->session->set_flashdata('error', 'Pertanyaan dan opsi A & B wajib diisi.');
            return false;
        }
        return true;
    }

    private function _input()
    {
        return [
            'question'       => trim((string) $this->input->post('question', true)),
            'option_a'       => trim((string) $this->input->post('option_a', true)),
            'option_b'       => trim((string) $this->input->post('option_b', true)),
            'option_c'       => $this->input->post('option_c', true),
            'option_d'       => $this->input->post('option_d', true),
            'correct_option' => (int) $this->input->post('correct_option', true),
            'category'       => $this->input->post('category', true),
            'is_active'      => $this->input->post('is_active') !== null ? 1 : 0,
        ];
    }
}
