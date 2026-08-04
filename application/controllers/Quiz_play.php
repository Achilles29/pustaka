<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Public-facing quiz controller.
 * Extends CI_Controller directly — auth is handled per-method (member session OR participant session).
 */
class Quiz_play extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['Quiz_play_model', 'Quiz_session_model', 'Quiz_bank_model']);
        $this->load->driver('session');
    }

    // ── Practice (member login required) ─────────────────────────────────────

    public function practice($session_code)
    {
        $auth_user = $this->session->userdata('auth_user');
        if (! $auth_user) {
            $this->session->set_flashdata('redirect_after_login', current_url());
            redirect('login');
            return;
        }

        $quiz_session = $this->Quiz_session_model->get_session_by_code($session_code);
        if (! $quiz_session || $quiz_session['type'] !== 'practice' || $quiz_session['status'] !== 'open') {
            show_error('Sesi latihan tidak tersedia atau sudah ditutup.', 404);
            return;
        }

        // Find or create participant record for this member
        $participant = $this->db->get_where('quiz_participants', [
            'session_id' => (int) $quiz_session['id'],
            'user_id'    => (int) $auth_user['id'],
        ])->row_array();

        if (! $participant) {
            $result = $this->Quiz_session_model->create_participant((int) $quiz_session['id'], [
                'full_name' => $auth_user['full_name'] ?? $auth_user['username'],
                'email'     => $auth_user['email'] ?? '',
                'gender'    => '',
            ]);
            $this->db->update('quiz_participants', ['user_id' => (int) $auth_user['id']], ['id' => $result['id']]);
            $participant = $this->Quiz_session_model->get_participant($result['id']);
        }

        // Check attempt limits
        $max = (int) $quiz_session['max_attempts'];
        if ($max > 0) {
            $count = $this->Quiz_play_model->count_attempts($participant['id'], $quiz_session['id']);
            if ($count >= $max) {
                show_error('Kamu sudah mencapai batas maksimal percobaan untuk sesi ini.', 403);
                return;
            }
        }

        // Check for existing in-progress attempt
        $active = $this->Quiz_play_model->get_active_attempt($participant['id'], $quiz_session['id']);
        if ($active) {
            redirect('quiz/exam/' . $active['session_token']);
            return;
        }

        // Draw questions
        $q_rows = $this->Quiz_bank_model->draw_random(
            $quiz_session['subject_id'],
            $quiz_session['grade_level_id'],
            $quiz_session['difficulty_filter'],
            $quiz_session['question_count']
        );

        if (empty($q_rows)) {
            show_error('Tidak ada soal yang tersedia untuk konfigurasi sesi ini.', 404);
            return;
        }

        $question_ids = array_column($q_rows, 'id');
        if ($quiz_session['shuffle_questions']) {
            shuffle($question_ids);
        }

        $attempt_count = $this->Quiz_play_model->count_attempts($participant['id'], $quiz_session['id']);
        $result = $this->Quiz_play_model->create_attempt($quiz_session['id'], $participant['id'], $question_ids, $attempt_count + 1);

        redirect('quiz/exam/' . $result['token']);
    }

    // ── Competition Login ─────────────────────────────────────────────────────

    public function login($session_code = null)
    {
        // If already logged in as participant, redirect to exam
        $quiz_participant = $this->session->userdata('quiz_participant');
        if ($quiz_participant && ! empty($quiz_participant['active_token'])) {
            redirect('quiz/exam/' . $quiz_participant['active_token']);
            return;
        }

        $sessions = $this->db
            ->select('s.id, s.code, s.title, s.start_time, s.end_time, s.status')
            ->from('quiz_sessions s')
            ->where('s.type', 'competition')
            ->where_in('s.status', ['open', 'ongoing'])
            ->where('s.deleted_at IS NULL', null, false)
            ->order_by('s.start_time', 'ASC')
            ->get()->result_array();

        $this->load->view('quiz/play/login', [
            'title'        => 'Login Peserta Kompetisi',
            'sessions'     => $sessions,
            'presel_code'  => $session_code,
            'error'        => $this->session->flashdata('quiz_login_error'),
        ]);
    }

    public function do_login()
    {
        $reg_code = strtoupper(trim((string) $this->input->post('registration_code', true)));
        $pin      = trim((string) $this->input->post('registration_pin', true));

        if (! $reg_code || ! $pin) {
            $this->session->set_flashdata('quiz_login_error', 'Kode registrasi dan PIN wajib diisi.');
            redirect('quiz/login');
            return;
        }

        $participant = $this->Quiz_session_model->get_participant_by_code($reg_code);

        if (! $participant || $participant['registration_pin'] !== $pin || $participant['registration_status'] === 'disqualified' || $participant['registration_status'] === 'withdrawn') {
            $this->db->insert('quiz_activity_log', [
                'action'      => 'quiz.participant.login_failed',
                'entity_type' => 'quiz_participants',
                'entity_id'   => $participant ? $participant['id'] : null,
                'ip_address'  => $this->input->ip_address(),
                'detail'      => json_encode(['code' => $reg_code]),
            ]);
            $this->session->set_flashdata('quiz_login_error', 'Kode atau PIN tidak valid, atau peserta tidak terdaftar.');
            redirect('quiz/login');
            return;
        }

        $quiz_session = $this->Quiz_session_model->get_session($participant['session_id']);
        if (! $quiz_session || ! in_array($quiz_session['status'], ['open', 'ongoing'], true)) {
            $this->session->set_flashdata('quiz_login_error', 'Kompetisi belum dibuka atau sudah selesai.');
            redirect('quiz/login');
            return;
        }

        // Check for existing in-progress attempt
        $active = $this->Quiz_play_model->get_active_attempt($participant['id'], $quiz_session['id']);
        if ($active) {
            $this->session->set_userdata('quiz_participant', ['id' => $participant['id'], 'name' => $participant['full_name'], 'active_token' => $active['session_token']]);
            redirect('quiz/exam/' . $active['session_token']);
            return;
        }

        // Check if already submitted (competition = max 1 attempt)
        $attempt_count = $this->Quiz_play_model->count_attempts($participant['id'], $quiz_session['id']);
        if ($attempt_count >= 1) {
            $this->session->set_flashdata('quiz_login_error', 'Anda sudah mengerjakan kompetisi ini.');
            redirect('quiz/login');
            return;
        }

        // Build question list for competition
        $sq = $this->Quiz_session_model->get_competition_questions($quiz_session['id']);
        if (empty($sq)) {
            $this->session->set_flashdata('quiz_login_error', 'Soal kompetisi belum tersedia. Hubungi panitia.');
            redirect('quiz/login');
            return;
        }
        $question_ids = array_column($sq, 'question_id');
        if ($quiz_session['shuffle_questions']) {
            shuffle($question_ids);
        }

        $result = $this->Quiz_play_model->create_attempt($quiz_session['id'], $participant['id'], $question_ids, 1);

        $this->db->insert('quiz_activity_log', [
            'action'         => 'quiz.participant.login_success',
            'entity_type'    => 'quiz_participants',
            'entity_id'      => $participant['id'],
            'participant_id' => $participant['id'],
            'ip_address'     => $this->input->ip_address(),
        ]);

        $this->session->set_userdata('quiz_participant', [
            'id'           => $participant['id'],
            'name'         => $participant['full_name'],
            'active_token' => $result['token'],
        ]);

        redirect('quiz/exam/' . $result['token']);
    }

    // ── Exam ─────────────────────────────────────────────────────────────────

    public function exam($token)
    {
        if (! $this->authorize_token($token)) {
            return;
        }

        $attempt = $this->Quiz_play_model->get_attempt_by_token($token);
        if (! $attempt) { show_404(); return; }

        if ($attempt['status'] !== 'in_progress') {
            redirect('quiz/result/' . $attempt['id']);
            return;
        }

        $question_ids = json_decode($attempt['question_order'], true);
        $questions    = $this->Quiz_play_model->get_exam_questions($question_ids, (bool) $attempt['shuffle_options']);

        // Existing answers
        $saved_answers = [];
        $rows = $this->db->where('attempt_id', (int) $attempt['id'])->get('quiz_attempt_answers')->result_array();
        foreach ($rows as $r) {
            $saved_answers[(int) $r['question_id']] = $r;
        }

        $elapsed  = time() - strtotime($attempt['started_at']);
        $time_limit = (int) $attempt['time_limit_minutes'] * 60;
        $remaining  = $time_limit > 0 ? max(0, $time_limit - $elapsed) : -1;

        if ($remaining === 0) {
            $this->Quiz_play_model->mark_timed_out($attempt['id']);
            redirect('quiz/result/' . $attempt['id']);
            return;
        }

        $this->load->view('quiz/play/exam', [
            'title'        => $attempt['session_title'],
            'attempt'      => $attempt,
            'questions'    => $questions,
            'saved_answers'=> $saved_answers,
            'remaining'    => $remaining,
            'time_limit'   => $time_limit,
            'token'        => $token,
        ]);
    }

    // ── AJAX: Save Answer ─────────────────────────────────────────────────────

    public function save_answer()
    {
        $token = $this->input->post('token', true);
        if (! $this->authorize_token($token, true)) { return; }

        $attempt  = $this->Quiz_play_model->get_attempt_by_token($token);
        $question_id     = (int) $this->input->post('question_id', true);
        $selected_option = $this->input->post('selected_option', true);
        $essay_answer    = $this->input->post('essay_answer');
        $time_spent      = (int) $this->input->post('time_spent', true);

        $selected_option = $selected_option !== null && $selected_option !== '' ? (int) $selected_option : null;

        $ok = $this->Quiz_play_model->save_answer($attempt['id'], $question_id, $selected_option, $essay_answer, $time_spent);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['ok' => $ok]));
    }

    // ── AJAX: Fraud Log ───────────────────────────────────────────────────────

    public function fraud_event()
    {
        $token = $this->input->post('token', true);
        if (! $this->authorize_token($token, true)) { return; }

        $attempt     = $this->Quiz_play_model->get_attempt_by_token($token);
        $event_type  = $this->input->post('event_type', true);
        $question_id = $this->input->post('question_id', true);
        $detail      = $this->input->post('detail', true);

        $result = $this->Quiz_play_model->log_fraud_event(
            $attempt['id'],
            $event_type,
            $detail ? ['raw' => $detail] : null,
            $question_id ? (int) $question_id : null
        );

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['ok' => true, 'result' => $result]));
    }

    // ── AJAX: Heartbeat ───────────────────────────────────────────────────────

    public function heartbeat()
    {
        $token = $this->input->post('token', true);
        if (! $this->authorize_token($token, true)) { return; }

        $attempt = $this->Quiz_play_model->get_attempt_by_token($token);
        $status  = $this->Quiz_play_model->check_attempt_status($attempt['id'], $token);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($status));
    }

    // ── Submit ────────────────────────────────────────────────────────────────

    public function submit()
    {
        $token = $this->input->post('token', true);
        if (! $this->authorize_token($token, true)) { return; }

        $attempt     = $this->Quiz_play_model->get_attempt_by_token($token);
        $time_spent  = (int) $this->input->post('time_spent', true);

        $result = $this->Quiz_play_model->submit_attempt($attempt['id'], $time_spent);

        // Clear active token from participant session
        $qp = $this->session->userdata('quiz_participant');
        if ($qp) {
            $qp['active_token'] = null;
            $this->session->set_userdata('quiz_participant', $qp);
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['ok' => true, 'redirect' => base_url('quiz/result/' . $attempt['id'])]));
    }

    // ── Result ────────────────────────────────────────────────────────────────

    public function result($attempt_id)
    {
        $attempt = $this->db
            ->select('a.*, s.title AS session_title, s.show_result_immediately, s.allow_review, s.passing_score, s.type AS session_type, p.full_name')
            ->from('quiz_attempts a')
            ->join('quiz_sessions s', 's.id = a.session_id')
            ->join('quiz_participants p', 'p.id = a.participant_id')
            ->where('a.id', (int) $attempt_id)
            ->get()->row_array();

        if (! $attempt || $attempt['status'] === 'in_progress') {
            show_404();
            return;
        }

        if (! $attempt['show_result_immediately']) {
            $this->load->view('quiz/play/result', ['title' => 'Hasil Quiz', 'attempt' => $attempt, 'hidden' => true]);
            return;
        }

        $this->load->view('quiz/play/result', [
            'title'   => 'Hasil — ' . $attempt['session_title'],
            'attempt' => $attempt,
            'hidden'  => false,
        ]);
    }

    // ── Review / Pembahasan ───────────────────────────────────────────────────

    public function review($attempt_id)
    {
        $data = $this->Quiz_play_model->get_review_data((int) $attempt_id);
        if (! $data) { show_404(); return; }

        $attempt = $data['attempt'];
        if (! $attempt['allow_review']) {
            show_error('Pembahasan tidak diaktifkan untuk sesi ini.', 403);
            return;
        }
        if ($attempt['status'] === 'in_progress') {
            show_error('Selesaikan quiz terlebih dahulu.', 403);
            return;
        }

        $this->load->view('quiz/play/review', [
            'title'   => 'Pembahasan — ' . $attempt['session_title'],
            'attempt' => $attempt,
            'answers' => $data['answers'],
        ]);
    }

    // ── Auth Helper ───────────────────────────────────────────────────────────

    private function authorize_token($token, $json = false)
    {
        // Accept: logged-in auth_user OR quiz_participant session
        $auth_user       = $this->session->userdata('auth_user');
        $quiz_participant = $this->session->userdata('quiz_participant');

        if (! $auth_user && ! $quiz_participant) {
            if ($json) {
                $this->output->set_status_header(401)->set_content_type('application/json')->set_output(json_encode(['ok' => false, 'message' => 'Sesi habis.']));
            } else {
                redirect('quiz/login');
            }
            return false;
        }

        // Validate token exists and belongs to session holder
        $attempt = $this->Quiz_play_model->get_attempt_by_token($token);
        if (! $attempt) {
            if ($json) {
                $this->output->set_status_header(404)->set_content_type('application/json')->set_output(json_encode(['ok' => false, 'message' => 'Token tidak valid.']));
            } else {
                show_404();
            }
            return false;
        }

        return true;
    }
}
