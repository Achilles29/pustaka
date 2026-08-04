<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Quiz_config extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Quiz_config_model');
    }

    public function index()
    {
        $this->require_permission('quiz_config.index', 'view');
        $this->render('quiz/config/index', [
            'title'        => 'Konfigurasi Quiz',
            'stats'        => $this->Quiz_config_model->stats(),
            'grades'       => $this->Quiz_config_model->get_grade_levels(),
            'subjects'     => $this->Quiz_config_model->get_subjects(),
            'active_tab'   => $this->input->get('tab') ?: 'grades',
        ]);
    }

    // ── Grade Levels ──────────────────────────────────────────────────────────

    public function store_grade()
    {
        $this->require_permission('quiz_config.index', 'create');
        try {
            $id = $this->Quiz_config_model->create_grade($this->grade_input());
            $this->audit_event('quiz.grade.create', 'quiz_grade_levels', $id, null, $this->grade_input());
            $this->session->set_flashdata('success', 'Jenjang kelas berhasil ditambahkan.');
        } catch (Throwable $e) {
            $this->session->set_flashdata('error', $e->getMessage());
        }
        redirect('quiz-config?tab=grades');
    }

    public function update_grade($id)
    {
        $this->require_permission('quiz_config.index', 'edit');
        try {
            $this->Quiz_config_model->update_grade((int) $id, $this->grade_input());
            $this->audit_event('quiz.grade.update', 'quiz_grade_levels', (int) $id, null, $this->grade_input());
            $this->session->set_flashdata('success', 'Jenjang kelas berhasil diperbarui.');
        } catch (Throwable $e) {
            $this->session->set_flashdata('error', $e->getMessage());
        }
        redirect('quiz-config?tab=grades');
    }

    public function delete_grade($id)
    {
        $this->require_permission('quiz_config.index', 'delete');
        try {
            $this->Quiz_config_model->delete_grade((int) $id);
            $this->audit_event('quiz.grade.delete', 'quiz_grade_levels', (int) $id);
            $this->session->set_flashdata('success', 'Jenjang kelas berhasil dihapus.');
        } catch (Throwable $e) {
            $this->session->set_flashdata('error', $e->getMessage());
        }
        redirect('quiz-config?tab=grades');
    }

    // ── Subjects ─────────────────────────────────────────────────────────────

    public function store_subject()
    {
        $this->require_permission('quiz_config.index', 'create');
        try {
            $id = $this->Quiz_config_model->create_subject($this->subject_input());
            $this->audit_event('quiz.subject.create', 'quiz_subjects', $id, null, $this->subject_input());
            $this->session->set_flashdata('success', 'Mata pelajaran berhasil ditambahkan.');
        } catch (Throwable $e) {
            $this->session->set_flashdata('error', $e->getMessage());
        }
        redirect('quiz-config?tab=subjects');
    }

    public function update_subject($id)
    {
        $this->require_permission('quiz_config.index', 'edit');
        try {
            $this->Quiz_config_model->update_subject((int) $id, $this->subject_input());
            $this->audit_event('quiz.subject.update', 'quiz_subjects', (int) $id, null, $this->subject_input());
            $this->session->set_flashdata('success', 'Mata pelajaran berhasil diperbarui.');
        } catch (Throwable $e) {
            $this->session->set_flashdata('error', $e->getMessage());
        }
        redirect('quiz-config?tab=subjects');
    }

    public function delete_subject($id)
    {
        $this->require_permission('quiz_config.index', 'delete');
        try {
            $this->Quiz_config_model->delete_subject((int) $id);
            $this->audit_event('quiz.subject.delete', 'quiz_subjects', (int) $id);
            $this->session->set_flashdata('success', 'Mata pelajaran berhasil dihapus.');
        } catch (Throwable $e) {
            $this->session->set_flashdata('error', $e->getMessage());
        }
        redirect('quiz-config?tab=subjects');
    }

    // ── Input helpers ─────────────────────────────────────────────────────────

    private function grade_input()
    {
        return [
            'code'            => $this->input->post('code', true),
            'name'            => $this->input->post('name', true),
            'education_level' => $this->input->post('education_level', true),
            'grade_number'    => $this->input->post('grade_number', true),
            'sort_order'      => $this->input->post('sort_order', true),
            'is_active'       => $this->input->post('is_active'),
        ];
    }

    private function subject_input()
    {
        return [
            'code'       => $this->input->post('code', true),
            'name'       => $this->input->post('name', true),
            'icon'       => $this->input->post('icon', true) ?: 'ti ti-book',
            'color'      => $this->input->post('color', true) ?: '#4a90d9',
            'sort_order' => $this->input->post('sort_order', true),
            'is_active'  => $this->input->post('is_active'),
        ];
    }
}
