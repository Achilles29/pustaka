<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Learn_story — admin: kelola bacaan (passage) & soal pemahaman.
 */
class Learn_story extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Learn_story_model');
        $this->load->model('Quiz_config_model');
    }

    public function index()
    {
        $this->require_permission('learn_story.index', 'view');
        $this->render('learn/story/index', [
            'title'       => 'Story Quiz — Bacaan',
            'active_menu' => 'learn_story',
            'passages'    => $this->Learn_story_model->get_passages(),
            'stats'       => $this->Learn_story_model->stats(),
            'subjects'    => $this->Quiz_config_model->get_subjects(true),
            'grades'      => $this->Quiz_config_model->get_grade_levels(true),
            'can_create'  => $this->can('learn_story.index', 'create'),
            'can_edit'    => $this->can('learn_story.index', 'edit'),
            'can_delete'  => $this->can('learn_story.index', 'delete'),
        ]);
    }

    // ── Passage ───────────────────────────────────────────────────────────────

    public function store_passage()
    {
        $this->require_permission('learn_story.index', 'create');
        $data = $this->_passage_input();
        if ($data['code'] === '' || $data['title'] === '' || trim($data['body']) === '') {
            $this->session->set_flashdata('error', 'Kode, judul, dan isi bacaan wajib diisi.');
            redirect('learn-story');
            return;
        }
        if ($this->Learn_story_model->code_exists($data['code'])) {
            $this->session->set_flashdata('error', 'Kode bacaan sudah digunakan.');
            redirect('learn-story');
            return;
        }
        $data['created_by'] = (int) $this->current_user['id'];
        $id = $this->Learn_story_model->create_passage($data);
        $this->audit_event('learn_story.create_passage', 'learn_story_passages', $id, null, ['code' => $data['code']]);
        $this->session->set_flashdata('success', 'Bacaan dibuat. Tambahkan soal pemahaman.');
        redirect('learn-story/questions/' . $id);
    }

    public function update_passage($id)
    {
        $this->require_permission('learn_story.index', 'edit');
        $passage = $this->Learn_story_model->get_passage($id);
        if (! $passage) { show_404(); return; }
        $data = $this->_passage_input();
        if ($data['code'] === '' || $data['title'] === '' || trim($data['body']) === '') {
            $this->session->set_flashdata('error', 'Kode, judul, dan isi bacaan wajib diisi.');
            redirect('learn-story');
            return;
        }
        if ($this->Learn_story_model->code_exists($data['code'], (int) $id)) {
            $this->session->set_flashdata('error', 'Kode bacaan sudah digunakan bacaan lain.');
            redirect('learn-story');
            return;
        }
        $this->Learn_story_model->update_passage((int) $id, $data);
        $this->audit_event('learn_story.update_passage', 'learn_story_passages', (int) $id, $passage, $data);
        $this->session->set_flashdata('success', 'Bacaan diperbarui.');
        redirect('learn-story');
    }

    public function delete_passage($id)
    {
        $this->require_permission('learn_story.index', 'delete');
        $passage = $this->Learn_story_model->get_passage($id);
        if (! $passage) { show_404(); return; }
        $this->Learn_story_model->delete_passage((int) $id);
        $this->audit_event('learn_story.delete_passage', 'learn_story_passages', (int) $id, $passage);
        $this->session->set_flashdata('success', 'Bacaan beserta soalnya dihapus.');
        redirect('learn-story');
    }

    public function toggle_passage($id)
    {
        $this->require_permission('learn_story.index', 'edit');
        if (! $this->Learn_story_model->get_passage($id)) { show_404(); return; }
        $this->Learn_story_model->toggle_passage((int) $id);
        $this->session->set_flashdata('success', 'Status bacaan diperbarui.');
        redirect('learn-story');
    }

    // ── Questions ─────────────────────────────────────────────────────────────

    public function questions($passage_id)
    {
        $this->require_permission('learn_story.index', 'view');
        $passage = $this->Learn_story_model->get_passage($passage_id);
        if (! $passage) { show_404(); return; }

        $this->render('learn/story/questions', [
            'title'       => 'Soal — ' . $passage['title'],
            'active_menu' => 'learn_story',
            'passage'     => $passage,
            'questions'   => $this->Learn_story_model->get_questions((int) $passage_id),
            'can_create'  => $this->can('learn_story.index', 'create'),
            'can_edit'    => $this->can('learn_story.index', 'edit'),
            'can_delete'  => $this->can('learn_story.index', 'delete'),
        ]);
    }

    public function store_question($passage_id)
    {
        $this->require_permission('learn_story.index', 'create');
        $passage = $this->Learn_story_model->get_passage($passage_id);
        if (! $passage) { show_404(); return; }
        if (! $this->_valid_question()) {
            redirect('learn-story/questions/' . (int) $passage_id);
            return;
        }
        $this->Learn_story_model->create_question((int) $passage_id, $this->_question_input());
        $this->session->set_flashdata('success', 'Soal ditambahkan.');
        redirect('learn-story/questions/' . (int) $passage_id);
    }

    public function update_question($id)
    {
        $this->require_permission('learn_story.index', 'edit');
        $q = $this->Learn_story_model->get_question($id);
        if (! $q) { show_404(); return; }
        if (! $this->_valid_question()) {
            redirect('learn-story/questions/' . (int) $q['passage_id']);
            return;
        }
        $this->Learn_story_model->update_question((int) $id, $this->_question_input());
        $this->session->set_flashdata('success', 'Soal diperbarui.');
        redirect('learn-story/questions/' . (int) $q['passage_id']);
    }

    public function delete_question($id)
    {
        $this->require_permission('learn_story.index', 'delete');
        $q = $this->Learn_story_model->get_question($id);
        if (! $q) { show_404(); return; }
        $this->Learn_story_model->delete_question((int) $id);
        $this->session->set_flashdata('success', 'Soal dihapus.');
        redirect('learn-story/questions/' . (int) $q['passage_id']);
    }

    // ── Input helpers ─────────────────────────────────────────────────────────

    private function _valid_question()
    {
        if (trim((string) $this->input->post('question', true)) === ''
            || trim((string) $this->input->post('option_a', true)) === ''
            || trim((string) $this->input->post('option_b', true)) === '') {
            $this->session->set_flashdata('error', 'Pertanyaan dan minimal opsi A & B wajib diisi.');
            return false;
        }
        return true;
    }

    private function _passage_input()
    {
        return [
            'code'              => trim((string) $this->input->post('code', true)),
            'title'             => trim((string) $this->input->post('title', true)),
            'body'              => (string) $this->input->post('body'),
            'summary'           => $this->input->post('summary', true),
            'subject_id'        => $this->input->post('subject_id', true) ?: null,
            'grade_level_id'    => $this->input->post('grade_level_id', true) ?: null,
            'icon'              => $this->input->post('icon', true) ?: 'ti-book',
            'color'             => $this->input->post('color', true) ?: '#0891b2',
            'estimated_minutes' => (int) $this->input->post('estimated_minutes', true),
            'sort_order'        => (int) $this->input->post('sort_order', true),
            'is_active'         => $this->input->post('is_active') ? 1 : 0,
        ];
    }

    private function _question_input()
    {
        return [
            'question'       => trim((string) $this->input->post('question', true)),
            'option_a'       => trim((string) $this->input->post('option_a', true)),
            'option_b'       => trim((string) $this->input->post('option_b', true)),
            'option_c'       => $this->input->post('option_c', true),
            'option_d'       => $this->input->post('option_d', true),
            'correct_option' => (int) $this->input->post('correct_option', true),
            'explanation'    => $this->input->post('explanation', true),
            'sort_order'     => (int) $this->input->post('sort_order', true),
            'is_active'      => $this->input->post('is_active') !== null ? 1 : 0,
        ];
    }
}
