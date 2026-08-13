<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Quiz_sessions extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['Quiz_session_model', 'Quiz_config_model', 'Quiz_bank_model']);
    }

    public function index()
    {
        $this->require_permission('quiz_sessions.index', 'view');

        $filters  = [
            'q'              => trim((string) $this->input->get('q', true)),
            'status'         => $this->input->get('status', true),
            'subject_id'     => (int) $this->input->get('subject_id', true),
            'grade_level_id' => (int) $this->input->get('grade_level_id', true),
            'difficulty'     => $this->input->get('difficulty', true),
        ];
        $filters['status'] = in_array($filters['status'], ['draft', 'open', 'ongoing', 'closed', 'archived'], true) ? $filters['status'] : '';
        $filters['difficulty'] = in_array($filters['difficulty'], ['easy', 'medium', 'hard', 'mixed'], true) ? $filters['difficulty'] : '';
        $per_page = in_array((int) $this->input->get('per_page', true), [10, 25, 50], true) ? (int) $this->input->get('per_page', true) : 25;
        $page     = max(1, (int) $this->input->get('page', true));
        $total    = $this->Quiz_session_model->count_sessions($filters, 'practice');
        $pages    = max(1, (int) ceil($total / $per_page));
        $page     = min($page, $pages);

        $this->render('quiz/sessions/index', [
            'title'      => 'Latihan Soal',
            'stats'      => $this->Quiz_session_model->stats('practice'),
            'sessions'   => $this->Quiz_session_model->get_sessions($filters, $per_page, ($page - 1) * $per_page, 'practice'),
            'subjects'   => $this->Quiz_config_model->get_subjects(true),
            'grades'     => $this->Quiz_config_model->get_grade_levels(true),
            'filters'    => array_merge($filters, ['per_page' => $per_page, 'page' => $page]),
            'pagination' => ['total_rows' => $total, 'total_pages' => $pages, 'page' => $page, 'per_page' => $per_page],
            'can_create' => $this->can('quiz_sessions.index', 'create'),
            'can_edit'   => $this->can('quiz_sessions.index', 'edit'),
            'can_delete' => $this->can('quiz_sessions.index', 'delete'),
        ]);
    }

    public function create()
    {
        $this->require_permission('quiz_sessions.index', 'create');
        $this->render('quiz/sessions/form', [
            'title'    => 'Tambah Sesi Latihan',
            'action'   => 'quiz-sessions/store',
            'session'  => null,
            'subjects' => $this->Quiz_config_model->get_subjects(true),
            'grades'   => $this->Quiz_config_model->get_grade_levels(true),
            'active_tab' => 'settings',
        ]);
    }

    public function store()
    {
        $this->require_permission('quiz_sessions.index', 'create');
        try {
            $data       = $this->session_input();
            $data['type'] = 'practice';
            $id         = $this->Quiz_session_model->create_session($data, (int) $this->current_user['id']);
            $this->audit_event('quiz.session.create', 'quiz_sessions', $id);
            $this->session->set_flashdata('success', 'Sesi latihan berhasil dibuat.');
            redirect('quiz-sessions');
        } catch (Throwable $e) {
            $this->session->set_flashdata('error', $e->getMessage());
            redirect('quiz-sessions/create');
        }
    }

    public function edit($id)
    {
        $this->require_permission('quiz_sessions.index', 'edit');
        $session = $this->Quiz_session_model->get_session((int) $id);
        if (! $session || $session['type'] !== 'practice') { show_404(); return; }

        $this->render('quiz/sessions/form', [
            'title'    => 'Edit Sesi Latihan',
            'action'   => 'quiz-sessions/update/' . (int) $id,
            'session'  => $session,
            // Editor harus tetap dapat menampilkan pilihan yang sudah tersimpan,
            // termasuk master yang kebetulan dinonaktifkan setelah sesi dibuat.
            'subjects' => $this->Quiz_config_model->get_subjects(false),
            'grades'   => $this->Quiz_config_model->get_grade_levels(false),
            'active_tab' => $this->input->get('tab', true) === 'questions' ? 'questions' : 'settings',
            'session_questions' => $this->Quiz_session_model->get_competition_questions((int) $id),
            'bank_questions' => $this->Quiz_session_model->get_bank_questions_for_session($session, 100),
        ]);
    }

    public function update($id)
    {
        $this->require_permission('quiz_sessions.index', 'edit');
        $session = $this->Quiz_session_model->get_session((int) $id);
        if (! $session || $session['type'] !== 'practice') { show_404(); return; }
        try {
            $data       = $this->session_input();
            $data['type'] = 'practice';
            $data['question_source'] = $session['question_source'] ?? 'bank_all';
            $this->Quiz_session_model->update_session((int) $id, $data, (int) $this->current_user['id']);
            $this->audit_event('quiz.session.update', 'quiz_sessions', (int) $id);
            $this->session->set_flashdata('success', 'Sesi latihan berhasil diperbarui.');
            redirect('quiz-sessions');
        } catch (Throwable $e) {
            $this->session->set_flashdata('error', $e->getMessage());
            redirect('quiz-sessions/edit/' . (int) $id);
        }
    }

    public function update_question_source($id)
    {
        $this->require_permission('quiz_sessions.index', 'edit');
        $session = $this->Quiz_session_model->get_session((int) $id);
        if (! $session || $session['type'] !== 'practice') { show_404(); return; }
        try {
            $source = (string) $this->input->post('question_source', true);
            $ids = (array) $this->input->post('question_ids');
            if ($source === 'selected' && empty(array_filter(array_map('intval', $ids)))) {
                throw new RuntimeException('Pilih minimal satu soal bank untuk mode soal tertentu.');
            }
            if ($source === 'session_only' && $this->Quiz_session_model->count_session_only_questions((int) $id) === 0) {
                throw new RuntimeException('Belum ada soal khusus pada sesi ini. Impor atau tambahkan soal khusus terlebih dahulu.');
            }
            $this->Quiz_session_model->set_question_source((int) $id, $source, $ids);
            $this->audit_event('quiz.session.question_source', 'quiz_sessions', (int) $id, ['question_source' => $session['question_source'] ?? 'bank_all'], ['question_source' => $source, 'question_ids' => array_map('intval', $ids)]);
            $this->session->set_flashdata('success', 'Sumber soal sesi diperbarui.');
        } catch (Throwable $e) {
            $this->session->set_flashdata('error', $e->getMessage());
        }
        redirect('quiz-sessions/edit/' . (int) $id . '?tab=questions');
    }

    public function delete($id)
    {
        $this->require_permission('quiz_sessions.index', 'delete');
        $session = $this->Quiz_session_model->get_session((int) $id);
        if (! $session) { show_404(); return; }
        $this->Quiz_session_model->delete_session((int) $id, (int) $this->current_user['id']);
        $this->audit_event('quiz.session.delete', 'quiz_sessions', (int) $id);
        $this->session->set_flashdata('success', 'Sesi latihan berhasil dihapus.');
        redirect('quiz-sessions');
    }

    public function toggle_status($id)
    {
        $this->require_permission('quiz_sessions.index', 'edit');
        $session = $this->Quiz_session_model->get_session((int) $id);
        if (! $session) { show_404(); return; }
        $new_status = $session['status'] === 'open' ? 'closed' : 'open';
        $this->Quiz_session_model->set_status((int) $id, $new_status, (int) $this->current_user['id']);
        $this->audit_event('quiz.session.status', 'quiz_sessions', (int) $id, ['status' => $session['status']], ['status' => $new_status]);
        $this->session->set_flashdata('success', 'Status sesi diperbarui menjadi ' . $new_status . '.');
        redirect('quiz-sessions');
    }

    public function attempts($id)
    {
        $this->require_permission('quiz_sessions.index', 'view');
        $session = $this->Quiz_session_model->get_session((int) $id);
        if (! $session) { show_404(); return; }

        $this->render('quiz/sessions/attempts', [
            'title'    => 'Hasil — ' . $session['title'],
            'session'  => $session,
            'attempts' => $this->Quiz_session_model->get_attempts((int) $id),
            'results'  => $this->Quiz_session_model->get_session_results((int) $id),
            'pending_grades' => $this->Quiz_session_model->get_essay_attempts_pending_grade((int) $id),
            'can_grade'=> $this->can('quiz_sessions.index', 'edit'),
        ]);
    }

    public function grade($attempt_id)
    {
        $this->require_permission('quiz_sessions.index', 'edit');
        $this->load->model('Quiz_session_model');
        $attempt = $this->Quiz_session_model->get_attempt((int) $attempt_id);
        if (! $attempt) { show_404(); return; }

        if ($this->input->post()) {
            $grades = (array) $this->input->post('grade');
            $parsed = [];
            foreach ($grades as $answer_id => $g) {
                $parsed[(int) $answer_id] = ['score' => (float) $g['score'], 'feedback' => $g['feedback'] ?? ''];
            }
            $this->Quiz_session_model->save_essay_grades((int) $attempt_id, $parsed, (int) $this->current_user['id']);
            $this->audit_event('quiz.attempt.grade', 'quiz_attempts', (int) $attempt_id);
            $this->session->set_flashdata('success', 'Penilaian essay berhasil disimpan.');
            redirect('quiz-sessions/attempts/' . $attempt['session_id']);
        }

        $this->load->model('Quiz_play_model');
        $review = $this->Quiz_play_model->get_review_data((int) $attempt_id);

        $this->render('quiz/sessions/grade', [
            'title'   => 'Penilaian Essay — ' . $attempt['full_name'],
            'attempt' => $attempt,
            'review'  => $review,
        ]);
    }

    private function session_input()
    {
        return [
            'title'                    => $this->input->post('title', true),
            'type'                     => 'practice',
            'status'                   => $this->input->post('status', true) ?: 'draft',
            'subject_id'               => $this->input->post('subject_id', true) ?: null,
            'grade_level_id'           => $this->input->post('grade_level_id', true) ?: null,
            'difficulty_filter'        => $this->input->post('difficulty_filter', true) ?: 'mixed',
            'question_count'           => $this->input->post('question_count', true),
            'time_limit_minutes'       => $this->input->post('time_limit_minutes', true),
            'shuffle_questions'        => $this->input->post('shuffle_questions') ? 1 : 0,
            'shuffle_options'          => $this->input->post('shuffle_options') ? 1 : 0,
            'show_result_immediately'  => $this->input->post('show_result_immediately') ? 1 : 0,
            'allow_review'             => $this->input->post('allow_review') ? 1 : 0,
            'max_attempts'             => $this->input->post('max_attempts', true) ?: 0,
            'passing_score'            => $this->input->post('passing_score', true) ?: 60,
            'start_time'               => $this->input->post('start_time', true) ?: null,
            'end_time'                 => $this->input->post('end_time', true) ?: null,
            'fraud_detect_tab_switch'  => $this->input->post('fraud_detect_tab_switch') ? 1 : 0,
            'fraud_detect_time_anomaly'=> $this->input->post('fraud_detect_time_anomaly') ? 1 : 0,
            'fraud_max_tab_switches'   => $this->input->post('fraud_max_tab_switches', true) ?: 3,
            'fraud_action'             => $this->input->post('fraud_action', true) ?: 'flag',
            'description'              => $this->input->post('description'),
            'instructions'             => $this->input->post('instructions'),
        ];
    }
}
