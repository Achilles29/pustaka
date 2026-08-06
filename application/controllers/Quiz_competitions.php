<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Quiz_competitions extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['Quiz_session_model', 'Quiz_config_model', 'Quiz_bank_model']);
        $this->load->library('upload');
    }

    public function index()
    {
        $this->require_permission('quiz_competitions.index', 'view');

        $filters  = ['q' => $this->input->get('q', true), 'status' => $this->input->get('status', true)];
        $per_page = 25;
        $page     = max(1, (int) $this->input->get('page', true));
        $total    = $this->Quiz_session_model->count_sessions($filters, 'competition');
        $pages    = max(1, (int) ceil($total / $per_page));
        $page     = min($page, $pages);

        $this->render('quiz/competitions/index', [
            'title'      => 'Manajemen Kompetisi',
            'stats'      => $this->Quiz_session_model->stats('competition'),
            'sessions'   => $this->Quiz_session_model->get_sessions($filters, $per_page, ($page - 1) * $per_page, 'competition'),
            'filters'    => array_merge($filters, ['per_page' => $per_page, 'page' => $page]),
            'pagination' => ['total_rows' => $total, 'total_pages' => $pages, 'page' => $page, 'per_page' => $per_page],
            'can_create' => $this->can('quiz_competitions.index', 'create'),
            'can_edit'   => $this->can('quiz_competitions.index', 'edit'),
            'can_delete' => $this->can('quiz_competitions.index', 'delete'),
        ]);
    }

    public function create()
    {
        $this->require_permission('quiz_competitions.index', 'create');
        $this->render('quiz/competitions/form', [
            'title'    => 'Tambah Kompetisi',
            'action'   => 'quiz-competitions/store',
            'session'  => null,
            'subjects' => $this->Quiz_config_model->get_subjects(true),
            'grades'   => $this->Quiz_config_model->get_grade_levels(true),
        ]);
    }

    public function store()
    {
        $this->require_permission('quiz_competitions.index', 'create');
        try {
            $data = $this->session_input();
            $data['type'] = 'competition';
            $id = $this->Quiz_session_model->create_session($data, (int) $this->current_user['id']);
            $this->audit_event('quiz.competition.create', 'quiz_sessions', $id);
            $this->session->set_flashdata('success', 'Kompetisi berhasil dibuat.');
            redirect('quiz-competitions/questions/' . $id);
        } catch (Throwable $e) {
            $this->session->set_flashdata('error', $e->getMessage());
            redirect('quiz-competitions/create');
        }
    }

    public function edit($id)
    {
        $this->require_permission('quiz_competitions.index', 'edit');
        $session = $this->get_competition_or_404((int) $id);
        $this->render('quiz/competitions/form', [
            'title'    => 'Edit Kompetisi',
            'action'   => 'quiz-competitions/update/' . (int) $id,
            'session'  => $session,
            'subjects' => $this->Quiz_config_model->get_subjects(true),
            'grades'   => $this->Quiz_config_model->get_grade_levels(true),
        ]);
    }

    public function update($id)
    {
        $this->require_permission('quiz_competitions.index', 'edit');
        $session = $this->get_competition_or_404((int) $id);
        try {
            $data = $this->session_input();
            $data['type'] = 'competition';
            $this->Quiz_session_model->update_session((int) $id, $data, (int) $this->current_user['id']);
            $this->audit_event('quiz.competition.update', 'quiz_sessions', (int) $id);
            $this->session->set_flashdata('success', 'Kompetisi berhasil diperbarui.');
            redirect('quiz-competitions');
        } catch (Throwable $e) {
            $this->session->set_flashdata('error', $e->getMessage());
            redirect('quiz-competitions/edit/' . (int) $id);
        }
    }

    public function delete($id)
    {
        $this->require_permission('quiz_competitions.index', 'delete');
        $session = $this->get_competition_or_404((int) $id);
        $this->Quiz_session_model->delete_session((int) $id, (int) $this->current_user['id']);
        $this->audit_event('quiz.competition.delete', 'quiz_sessions', (int) $id);
        $this->session->set_flashdata('success', 'Kompetisi berhasil dihapus.');
        redirect('quiz-competitions');
    }

    public function announce($id)
    {
        $this->require_permission('quiz_competitions.index', 'edit');
        $session = $this->get_competition_or_404((int) $id);

        $this->load->model('Learn_notifications_model');
        $count = $this->Learn_notifications_model->broadcast([
            'type'           => 'competition',
            'title'          => 'Kompetisi baru: ' . $session['title'],
            'message'        => 'Kompetisi "' . $session['title'] . '" telah dibuka. Ayo ikut berkompetisi dan raih poin!',
            'icon'           => 'ti-trophy',
            'color'          => '#f59e0b',
            'url'            => 'belajar',
            'reference_type' => 'competition',
            'reference_id'   => (int) $id,
        ], (int) $this->current_user['id']);

        $this->audit_event('quiz.competition.announce', 'quiz_sessions', (int) $id, null, ['recipients' => $count]);
        $this->session->set_flashdata('success', "Kompetisi diumumkan ke {$count} member.");
        redirect('quiz-competitions');
    }

    public function set_status($id)
    {
        $this->require_permission('quiz_competitions.index', 'edit');
        $this->get_competition_or_404((int) $id);
        $status = $this->input->post('status', true);
        try {
            $this->Quiz_session_model->set_status((int) $id, $status, (int) $this->current_user['id']);
            $this->session->set_flashdata('success', 'Status kompetisi diperbarui.');
        } catch (Throwable $e) {
            $this->session->set_flashdata('error', $e->getMessage());
        }
        redirect('quiz-competitions');
    }

    // ── Questions ─────────────────────────────────────────────────────────────

    public function questions($id)
    {
        $this->require_permission('quiz_competitions.index', 'view');
        $session = $this->get_competition_or_404((int) $id);

        $filters = ['q' => $this->input->get('q', true), 'subject_id' => $this->input->get('subject_id', true), 'grade_level_id' => $this->input->get('grade_level_id', true)];
        $bank_questions = $this->Quiz_bank_model->get_questions($filters, 50, 0);

        $this->render('quiz/competitions/questions', [
            'title'          => 'Soal Kompetisi — ' . $session['title'],
            'session'        => $session,
            'session_questions' => $this->Quiz_session_model->get_competition_questions((int) $id),
            'bank_questions' => $bank_questions,
            'subjects'       => $this->Quiz_config_model->get_subjects(true),
            'grades'         => $this->Quiz_config_model->get_grade_levels(true),
            'filters'        => $filters,
            'can_edit'       => $this->can('quiz_competitions.index', 'edit'),
        ]);
    }

    public function add_question($id)
    {
        $this->require_permission('quiz_competitions.index', 'edit');
        $this->get_competition_or_404((int) $id);
        $question_id = (int) $this->input->post('question_id', true);
        $result = $this->Quiz_session_model->add_competition_question((int) $id, $question_id);
        if ($this->input->is_ajax_request()) {
            $this->json_response(['ok' => $result !== false, 'message' => $result === false ? 'Soal sudah ada.' : 'Soal ditambahkan.']);
            return;
        }
        $this->session->set_flashdata($result !== false ? 'success' : 'error', $result !== false ? 'Soal berhasil ditambahkan.' : 'Soal sudah ada dalam kompetisi ini.');
        redirect('quiz-competitions/questions/' . (int) $id);
    }

    public function remove_question($id, $question_id)
    {
        $this->require_permission('quiz_competitions.index', 'edit');
        $this->get_competition_or_404((int) $id);
        $this->Quiz_session_model->remove_competition_question((int) $id, (int) $question_id);
        if ($this->input->is_ajax_request()) {
            $this->json_response(['ok' => true]);
            return;
        }
        $this->session->set_flashdata('success', 'Soal berhasil dihapus dari kompetisi.');
        redirect('quiz-competitions/questions/' . (int) $id);
    }

    // ── Participants ──────────────────────────────────────────────────────────

    public function participants($id)
    {
        $this->require_permission('quiz_competitions.index', 'view');
        $session = $this->get_competition_or_404((int) $id);

        $filters  = ['q' => $this->input->get('q', true), 'status' => $this->input->get('status', true)];
        $per_page = 50;
        $page     = max(1, (int) $this->input->get('page', true));
        $total    = $this->Quiz_session_model->count_participants($filters, (int) $id);
        $pages    = max(1, (int) ceil($total / $per_page));
        $page     = min($page, $pages);

        $this->render('quiz/competitions/participants', [
            'title'        => 'Peserta — ' . $session['title'],
            'session'      => $session,
            'participants' => $this->Quiz_session_model->get_participants($filters, $per_page, ($page - 1) * $per_page, (int) $id),
            'filters'      => array_merge($filters, ['per_page' => $per_page, 'page' => $page]),
            'pagination'   => ['total_rows' => $total, 'total_pages' => $pages, 'page' => $page, 'per_page' => $per_page],
            'can_edit'     => $this->can('quiz_competitions.index', 'edit'),
            'can_delete'   => $this->can('quiz_competitions.index', 'delete'),
        ]);
    }

    public function add_participant($id)
    {
        $this->require_permission('quiz_competitions.index', 'edit');
        $session = $this->get_competition_or_404((int) $id);
        try {
            $result = $this->Quiz_session_model->create_participant((int) $id, $this->participant_input(), (int) $this->current_user['id']);
            $this->audit_event('quiz.participant.add', 'quiz_participants', $result['id']);
            $this->session->set_flashdata('success', "Peserta ditambahkan. Kode: <strong>{$result['code']}</strong> | PIN: <strong>{$result['pin']}</strong>");
        } catch (Throwable $e) {
            $this->session->set_flashdata('error', $e->getMessage());
        }
        redirect('quiz-competitions/participants/' . (int) $id);
    }

    public function edit_participant($participant_id)
    {
        $this->require_permission('quiz_competitions.index', 'edit');
        $p = $this->Quiz_session_model->get_participant((int) $participant_id);
        if (! $p) { show_404(); return; }
        try {
            $this->Quiz_session_model->update_participant((int) $participant_id, $this->participant_input());
            $this->session->set_flashdata('success', 'Data peserta berhasil diperbarui.');
        } catch (Throwable $e) {
            $this->session->set_flashdata('error', $e->getMessage());
        }
        redirect('quiz-competitions/participants/' . $p['session_id']);
    }

    public function delete_participant($participant_id)
    {
        $this->require_permission('quiz_competitions.index', 'delete');
        $p = $this->Quiz_session_model->get_participant((int) $participant_id);
        if (! $p) { show_404(); return; }
        $this->Quiz_session_model->delete_participant((int) $participant_id);
        $this->audit_event('quiz.participant.delete', 'quiz_participants', (int) $participant_id);
        $this->session->set_flashdata('success', 'Peserta berhasil dihapus.');
        redirect('quiz-competitions/participants/' . $p['session_id']);
    }

    public function import_participants($id)
    {
        $this->require_permission('quiz_competitions.index', 'edit');
        $this->get_competition_or_404((int) $id);

        $upload_path = FCPATH . 'assets/uploads/quiz/imports/';
        if (! is_dir($upload_path)) { mkdir($upload_path, 0755, true); }

        $this->upload->initialize([
            'upload_path' => $upload_path, 'allowed_types' => 'csv', 'max_size' => 2048, 'encrypt_name' => true,
        ]);
        if (! $this->upload->do_upload('participant_file')) {
            $this->session->set_flashdata('error', 'Upload gagal: ' . $this->upload->display_errors('', ''));
            redirect('quiz-competitions/participants/' . (int) $id);
            return;
        }

        $file   = $this->upload->data();
        $handle = fopen($file['full_path'], 'r');
        $header = fgetcsv($handle);
        $header = array_map('trim', (array) $header);

        $col = [];
        foreach (['full_name','identity_number','identity_type','school_name','grade_class','phone','email','gender'] as $key) {
            $pos = array_search($key, $header);
            $col[$key] = $pos !== false ? $pos : null;
        }

        $added = 0; $errors = [];
        while (($row = fgetcsv($handle)) !== false) {
            $get = function ($key) use ($row, $col) {
                $pos = $col[$key];
                return $pos !== null && isset($row[$pos]) ? trim($row[$pos]) : '';
            };
            $name = $get('full_name');
            if ($name === '') { continue; }
            try {
                $this->Quiz_session_model->create_participant((int) $id, [
                    'full_name'       => $name,
                    'identity_number' => $get('identity_number'),
                    'identity_type'   => $get('identity_type'),
                    'school_name'     => $get('school_name'),
                    'grade_class'     => $get('grade_class'),
                    'phone'           => $get('phone'),
                    'email'           => $get('email'),
                    'gender'          => strtoupper($get('gender')),
                ], (int) $this->current_user['id']);
                $added++;
            } catch (Throwable $e) {
                $errors[] = $name . ': ' . $e->getMessage();
            }
        }
        fclose($handle);

        $this->session->set_flashdata('success', "{$added} peserta berhasil diimport." . (! empty($errors) ? ' Error: ' . implode('; ', $errors) : ''));
        redirect('quiz-competitions/participants/' . (int) $id);
    }

    public function export_participants($id)
    {
        $this->require_permission('quiz_competitions.index', 'view');
        $session      = $this->get_competition_or_404((int) $id);
        $participants = $this->Quiz_session_model->get_participants([], 9999, 0, (int) $id);

        $csv = "Nama,Kode Registrasi,PIN,Sekolah,Kelas,Email,Telepon,Status\n";
        foreach ($participants as $p) {
            $csv .= implode(',', [
                '"' . str_replace('"', '""', $p['full_name']) . '"',
                $p['registration_code'],
                $p['registration_pin'],
                '"' . str_replace('"', '""', ($p['school_name'] ?? '')) . '"',
                '"' . str_replace('"', '""', ($p['grade_class'] ?? '')) . '"',
                $p['email'] ?? '',
                $p['phone'] ?? '',
                $p['registration_status'],
            ]) . "\n";
        }

        $this->output
            ->set_content_type('text/csv')
            ->set_header('Content-Disposition: attachment; filename="peserta_' . $session['code'] . '.csv"')
            ->set_output($csv);
    }

    public function results($id)
    {
        $this->require_permission('quiz_competitions.index', 'view');
        $session = $this->get_competition_or_404((int) $id);

        $this->render('quiz/competitions/results', [
            'title'   => 'Hasil Kompetisi — ' . $session['title'],
            'session' => $session,
            'results' => $this->Quiz_session_model->get_session_results((int) $id),
            'pending' => $this->Quiz_session_model->get_essay_attempts_pending_grade((int) $id),
        ]);
    }

    public function grade($attempt_id)
    {
        $this->require_permission('quiz_competitions.index', 'edit');
        $this->load->model(['Quiz_session_model', 'Quiz_play_model']);

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
            $this->session->set_flashdata('success', 'Penilaian berhasil disimpan.');
            redirect('quiz-competitions/results/' . $attempt['session_id']);
        }

        $review = $this->Quiz_play_model->get_review_data((int) $attempt_id);
        $this->render('quiz/sessions/grade', [
            'title'   => 'Penilaian Essay — ' . $attempt['full_name'],
            'attempt' => $attempt,
            'review'  => $review,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function get_competition_or_404($id)
    {
        $session = $this->Quiz_session_model->get_session($id);
        if (! $session || $session['type'] !== 'competition') { show_404(); exit; }
        return $session;
    }

    private function session_input()
    {
        return [
            'title'                    => $this->input->post('title', true),
            'status'                   => $this->input->post('status', true) ?: 'draft',
            'subject_id'               => $this->input->post('subject_id', true) ?: null,
            'grade_level_id'           => $this->input->post('grade_level_id', true) ?: null,
            'difficulty_filter'        => $this->input->post('difficulty_filter', true) ?: 'mixed',
            'question_count'           => $this->input->post('question_count', true) ?: 20,
            'time_limit_minutes'       => $this->input->post('time_limit_minutes', true) ?: 60,
            'shuffle_questions'        => $this->input->post('shuffle_questions') ? 1 : 0,
            'shuffle_options'          => $this->input->post('shuffle_options') ? 1 : 0,
            'show_result_immediately'  => $this->input->post('show_result_immediately') ? 1 : 0,
            'allow_review'             => $this->input->post('allow_review') ? 1 : 0,
            'max_attempts'             => 1,
            'passing_score'            => $this->input->post('passing_score', true) ?: 60,
            // Extended competition settings
            'scoring_system'           => $this->input->post('scoring_system', true) ?: 'standard',
            'time_mode'                => $this->input->post('time_mode', true) ?: 'per_participant',
            'access_mode'              => $this->input->post('access_mode', true) ?: 'assigned',
            'show_leaderboard'         => $this->input->post('show_leaderboard') ? 1 : 0,
            'announce_results'         => $this->input->post('announce_results') ? 1 : 0,
            'allow_self_reset'         => $this->input->post('allow_self_reset') ? 1 : 0,
            'is_published'             => $this->input->post('is_published') ? 1 : 0,
            'is_paused'                => $this->input->post('is_paused') ? 1 : 0,
            'has_certificate'          => $this->input->post('has_certificate') ? 1 : 0,
            // Schedule
            'registration_start'       => $this->input->post('registration_start', true) ?: null,
            'registration_end'         => $this->input->post('registration_end', true) ?: null,
            'start_time'               => $this->input->post('start_time', true) ?: null,
            'end_time'                 => $this->input->post('end_time', true) ?: null,
            // Anti-fraud
            'fraud_detect_tab_switch'  => $this->input->post('fraud_detect_tab_switch') ? 1 : 0,
            'fraud_detect_time_anomaly'=> $this->input->post('fraud_detect_time_anomaly') ? 1 : 0,
            'fraud_max_tab_switches'   => $this->input->post('fraud_max_tab_switches', true) ?: 3,
            'fraud_action'             => $this->input->post('fraud_action', true) ?: 'flag',
            'description'              => $this->input->post('description'),
            'instructions'             => $this->input->post('instructions'),
        ];
    }

    private function participant_input()
    {
        return [
            'full_name'       => $this->input->post('full_name', true),
            'identity_number' => $this->input->post('identity_number', true),
            'identity_type'   => $this->input->post('identity_type', true),
            'school_name'     => $this->input->post('school_name', true),
            'grade_class'     => $this->input->post('grade_class', true),
            'phone'           => $this->input->post('phone', true),
            'email'           => $this->input->post('email', true),
            'birth_date'      => $this->input->post('birth_date', true) ?: null,
            'gender'          => $this->input->post('gender', true),
            'address'         => $this->input->post('address', true),
            'notes'           => $this->input->post('notes', true),
        ];
    }
}
