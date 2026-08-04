<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Quiz_session_model extends CI_Model
{
    // ── Sessions ──────────────────────────────────────────────────────────────

    public function stats($type = null)
    {
        $this->db->where('deleted_at IS NULL', null, false);
        if ($type) {
            $this->db->where('type', $type);
        }
        $total = (int) $this->db->count_all_results('quiz_sessions');

        $open  = (int) $this->db->where('deleted_at IS NULL', null, false)->where('type', $type ?: 'practice')->where('status', 'open')->count_all_results('quiz_sessions');
        $draft = (int) $this->db->where('deleted_at IS NULL', null, false)->where('type', $type ?: 'practice')->where('status', 'draft')->count_all_results('quiz_sessions');

        $attempts = (int) $this->db->count_all('quiz_attempts');
        return compact('total', 'open', 'draft', 'attempts');
    }

    public function count_sessions(array $filters = [], $type = null)
    {
        $this->apply_filters($filters, $type);
        return (int) $this->db->count_all_results('quiz_sessions s');
    }

    public function get_sessions(array $filters = [], $limit = 25, $offset = 0, $type = null)
    {
        $this->apply_filters($filters, $type);
        return $this->db
            ->select('s.*, sub.name AS subject_name, g.name AS grade_name, u.full_name AS creator_name,
                      (SELECT COUNT(*) FROM quiz_attempts a WHERE a.session_id = s.id) AS attempt_count,
                      (SELECT COUNT(*) FROM quiz_participants p WHERE p.session_id = s.id) AS participant_count')
            ->join('quiz_subjects sub', 'sub.id = s.subject_id', 'left')
            ->join('quiz_grade_levels g', 'g.id = s.grade_level_id', 'left')
            ->join('auth_user u', 'u.id = s.created_by', 'left')
            ->order_by('s.id', 'DESC')
            ->limit($limit, $offset)
            ->get('quiz_sessions s')
            ->result_array();
    }

    public function get_session($id)
    {
        return $this->db
            ->select('s.*, sub.name AS subject_name, sub.color AS subject_color, g.name AS grade_name')
            ->from('quiz_sessions s')
            ->join('quiz_subjects sub', 'sub.id = s.subject_id', 'left')
            ->join('quiz_grade_levels g', 'g.id = s.grade_level_id', 'left')
            ->where('s.id', (int) $id)
            ->where('s.deleted_at IS NULL', null, false)
            ->get()->row_array();
    }

    public function get_session_by_code($code)
    {
        return $this->db
            ->where('code', $code)
            ->where('deleted_at IS NULL', null, false)
            ->get('quiz_sessions')->row_array();
    }

    public function create_session(array $data, $user_id)
    {
        $payload = $this->sanitize_session($data);
        $payload['code']       = $this->generate_code($data['type']);
        $payload['created_by'] = $user_id;
        $this->db->insert('quiz_sessions', $payload);
        $id = (int) $this->db->insert_id();
        $this->log($id, 'quiz.session.create', null, $payload, $user_id);
        return $id;
    }

    public function update_session($id, array $data, $user_id)
    {
        $payload = $this->sanitize_session($data);
        $this->db->update('quiz_sessions', $payload, ['id' => (int) $id]);
        $this->log($id, 'quiz.session.update', null, $payload, $user_id);
        return $this->db->affected_rows();
    }

    public function delete_session($id, $user_id)
    {
        $this->db->update('quiz_sessions', ['deleted_at' => date('Y-m-d H:i:s')], ['id' => (int) $id]);
        $this->log($id, 'quiz.session.delete', null, null, $user_id);
        return $this->db->affected_rows();
    }

    public function set_status($id, $status, $user_id)
    {
        $allowed = ['draft', 'open', 'ongoing', 'closed', 'archived'];
        if (! in_array($status, $allowed, true)) {
            throw new InvalidArgumentException('Status tidak valid.');
        }
        $this->db->update('quiz_sessions', ['status' => $status], ['id' => (int) $id]);
        $this->log($id, 'quiz.session.status', null, ['status' => $status], $user_id);
        return $this->db->affected_rows();
    }

    // ── Competition Questions ─────────────────────────────────────────────────

    public function get_competition_questions($session_id)
    {
        return $this->db
            ->select('sq.*, q.question_text, q.type, q.difficulty, s.name AS subject_name, g.name AS grade_name')
            ->from('quiz_session_questions sq')
            ->join('quiz_questions q', 'q.id = sq.question_id')
            ->join('quiz_subjects s', 's.id = q.subject_id', 'left')
            ->join('quiz_grade_levels g', 'g.id = q.grade_level_id', 'left')
            ->where('sq.session_id', (int) $session_id)
            ->order_by('sq.sort_order', 'ASC')
            ->get()->result_array();
    }

    public function add_competition_question($session_id, $question_id, $score_override = null)
    {
        $exists = $this->db->where('session_id', (int) $session_id)->where('question_id', (int) $question_id)->count_all_results('quiz_session_questions');
        if ($exists) {
            return false;
        }
        $max_order = (int) $this->db->select_max('sort_order')->where('session_id', (int) $session_id)->get('quiz_session_questions')->row_array()['sort_order'] ?? 0;
        $this->db->insert('quiz_session_questions', [
            'session_id'     => (int) $session_id,
            'question_id'    => (int) $question_id,
            'sort_order'     => $max_order + 1,
            'score_override' => $score_override !== null ? (float) $score_override : null,
        ]);
        return (int) $this->db->insert_id();
    }

    public function remove_competition_question($session_id, $question_id)
    {
        $this->db->delete('quiz_session_questions', [
            'session_id'  => (int) $session_id,
            'question_id' => (int) $question_id,
        ]);
        return $this->db->affected_rows();
    }

    public function import_questions_to_competition($session_id, $question_ids)
    {
        $added = 0;
        foreach ($question_ids as $qid) {
            if ($this->add_competition_question($session_id, $qid) !== false) {
                $added++;
            }
        }
        return $added;
    }

    // ── Participants ──────────────────────────────────────────────────────────

    public function count_participants(array $filters = [], $session_id = null)
    {
        $this->apply_participant_filters($filters, $session_id);
        return (int) $this->db->count_all_results('quiz_participants p');
    }

    public function get_participants(array $filters = [], $limit = 50, $offset = 0, $session_id = null)
    {
        $this->apply_participant_filters($filters, $session_id);
        return $this->db
            ->select('p.*, (SELECT COUNT(*) FROM quiz_attempts a WHERE a.participant_id = p.id) AS attempt_count,
                      (SELECT MAX(a.percentage) FROM quiz_attempts a WHERE a.participant_id = p.id AND a.status = "submitted") AS best_score')
            ->order_by('p.id', 'ASC')
            ->limit($limit, $offset)
            ->get('quiz_participants p')
            ->result_array();
    }

    public function get_participant($id)
    {
        return $this->db->get_where('quiz_participants', ['id' => (int) $id])->row_array();
    }

    public function get_participant_by_code($code)
    {
        return $this->db->get_where('quiz_participants', ['registration_code' => $code])->row_array();
    }

    public function create_participant($session_id, array $data, $registered_by = null)
    {
        $code = $this->generate_participant_code();
        $pin  = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        $this->db->insert('quiz_participants', [
            'session_id'          => (int) $session_id,
            'member_id'           => ! empty($data['member_id']) ? (int) $data['member_id'] : null,
            'user_id'             => ! empty($data['user_id'])   ? (int) $data['user_id']   : null,
            'full_name'           => trim($data['full_name']),
            'identity_number'     => trim($data['identity_number'] ?? ''),
            'identity_type'       => trim($data['identity_type'] ?? ''),
            'school_name'         => trim($data['school_name'] ?? ''),
            'grade_class'         => trim($data['grade_class'] ?? ''),
            'phone'               => trim($data['phone'] ?? ''),
            'email'               => trim($data['email'] ?? ''),
            'birth_date'          => ! empty($data['birth_date']) ? $data['birth_date'] : null,
            'gender'              => in_array($data['gender'] ?? '', ['L', 'P']) ? $data['gender'] : null,
            'address'             => trim($data['address'] ?? ''),
            'registration_code'   => $code,
            'registration_pin'    => $pin,
            'registration_status' => 'registered',
            'registered_by'       => $registered_by,
            'notes'               => trim($data['notes'] ?? ''),
        ]);
        $id = (int) $this->db->insert_id();
        $this->log($session_id, 'quiz.participant.create', $id, null, $registered_by);
        return ['id' => $id, 'code' => $code, 'pin' => $pin];
    }

    public function update_participant($id, array $data)
    {
        $this->db->update('quiz_participants', [
            'full_name'       => trim($data['full_name']),
            'identity_number' => trim($data['identity_number'] ?? ''),
            'identity_type'   => trim($data['identity_type'] ?? ''),
            'school_name'     => trim($data['school_name'] ?? ''),
            'grade_class'     => trim($data['grade_class'] ?? ''),
            'phone'           => trim($data['phone'] ?? ''),
            'email'           => trim($data['email'] ?? ''),
            'birth_date'      => ! empty($data['birth_date']) ? $data['birth_date'] : null,
            'gender'          => in_array($data['gender'] ?? '', ['L', 'P']) ? $data['gender'] : null,
            'address'         => trim($data['address'] ?? ''),
            'notes'           => trim($data['notes'] ?? ''),
        ], ['id' => (int) $id]);
        return $this->db->affected_rows();
    }

    public function set_participant_status($id, $status)
    {
        $allowed = ['registered', 'confirmed', 'disqualified', 'withdrawn'];
        if (! in_array($status, $allowed, true)) {
            throw new InvalidArgumentException('Status tidak valid.');
        }
        $this->db->update('quiz_participants', ['registration_status' => $status], ['id' => (int) $id]);
        return $this->db->affected_rows();
    }

    public function delete_participant($id)
    {
        $this->db->delete('quiz_participants', ['id' => (int) $id]);
        return $this->db->affected_rows();
    }

    // ── Results ───────────────────────────────────────────────────────────────

    public function get_session_results($session_id, $limit = 100)
    {
        return $this->db
            ->select('a.*, p.full_name, p.school_name, p.grade_class, p.registration_code, p.registration_status')
            ->from('quiz_attempts a')
            ->join('quiz_participants p', 'p.id = a.participant_id')
            ->where('a.session_id', (int) $session_id)
            ->where('a.status', 'submitted')
            ->order_by('a.percentage', 'DESC')
            ->limit($limit)
            ->get()->result_array();
    }

    public function get_attempts($session_id, $limit = 100)
    {
        return $this->db
            ->select('a.*, p.full_name, p.registration_code')
            ->from('quiz_attempts a')
            ->join('quiz_participants p', 'p.id = a.participant_id')
            ->where('a.session_id', (int) $session_id)
            ->order_by('a.id', 'DESC')
            ->limit($limit)
            ->get()->result_array();
    }

    public function get_attempt($id)
    {
        return $this->db
            ->select('a.*, p.full_name, p.school_name, p.grade_class, p.registration_code, s.title AS session_title, s.type AS session_type, s.allow_review, s.show_result_immediately')
            ->from('quiz_attempts a')
            ->join('quiz_participants p', 'p.id = a.participant_id')
            ->join('quiz_sessions s', 's.id = a.session_id')
            ->where('a.id', (int) $id)
            ->get()->row_array();
    }

    public function get_attempt_answers($attempt_id)
    {
        return $this->db
            ->select('aa.*, q.question_text, q.type, q.correct_option_index, q.explanation, q.score_weight, sub.name AS subject_name, g.name AS grade_name')
            ->from('quiz_attempt_answers aa')
            ->join('quiz_questions q', 'q.id = aa.question_id')
            ->join('quiz_subjects sub', 'sub.id = q.subject_id', 'left')
            ->join('quiz_grade_levels g', 'g.id = q.grade_level_id', 'left')
            ->where('aa.attempt_id', (int) $attempt_id)
            ->get()->result_array();
    }

    public function get_essay_attempts_pending_grade($session_id)
    {
        return $this->db
            ->select('a.*, p.full_name')
            ->from('quiz_attempts a')
            ->join('quiz_participants p', 'p.id = a.participant_id')
            ->where('a.session_id', (int) $session_id)
            ->where('a.status', 'submitted')
            ->where('a.essay_graded', 0)
            ->get()->result_array();
    }

    public function save_essay_grades($attempt_id, array $grades, $grader_id)
    {
        foreach ($grades as $answer_id => $grade_data) {
            $this->db->update('quiz_attempt_answers', [
                'essay_score'    => (float) $grade_data['score'],
                'essay_feedback' => trim($grade_data['feedback'] ?? ''),
                'score_earned'   => (float) $grade_data['score'],
                'is_correct'     => (float) $grade_data['score'] > 0 ? 1 : 0,
            ], ['id' => (int) $answer_id, 'attempt_id' => (int) $attempt_id]);
        }

        // Recalculate total score
        $answers = $this->db->where('attempt_id', (int) $attempt_id)->get('quiz_attempt_answers')->result_array();
        $total = 0;
        $max   = 0;
        foreach ($answers as $ans) {
            $weight = (float) ($ans['score_weight'] ?? 1);
            $max   += $weight;
            $total += (float) ($ans['score_earned'] ?? 0);
        }
        $percentage = $max > 0 ? round(($total / $max) * 100, 2) : 0;
        $session    = $this->get_attempt($attempt_id);

        $this->db->update('quiz_attempts', [
            'total_score'       => $total,
            'max_possible_score'=> $max,
            'percentage'        => $percentage,
            'is_passed'         => $percentage >= (float) ($session['passing_score'] ?? 60),
            'essay_graded'      => 1,
            'graded_by'         => $grader_id,
            'graded_at'         => date('Y-m-d H:i:s'),
        ], ['id' => (int) $attempt_id]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function apply_filters(array $filters, $type = null)
    {
        $this->db->where('s.deleted_at IS NULL', null, false);
        if ($type) {
            $this->db->where('s.type', $type);
        }
        if (! empty($filters['status'])) {
            $this->db->where('s.status', $filters['status']);
        }
        if (! empty($filters['subject_id'])) {
            $this->db->where('s.subject_id', (int) $filters['subject_id']);
        }
        if (! empty($filters['q'])) {
            $this->db->like('s.title', $filters['q']);
        }
    }

    private function apply_participant_filters(array $filters, $session_id = null)
    {
        if ($session_id) {
            $this->db->where('p.session_id', (int) $session_id);
        }
        if (! empty($filters['status'])) {
            $this->db->where('p.registration_status', $filters['status']);
        }
        if (! empty($filters['q'])) {
            $this->db->group_start()
                ->like('p.full_name', $filters['q'])
                ->or_like('p.registration_code', $filters['q'])
                ->or_like('p.school_name', $filters['q'])
                ->or_like('p.identity_number', $filters['q'])
                ->group_end();
        }
    }

    private function sanitize_session(array $data)
    {
        return [
            'title'                    => trim($data['title']),
            'type'                     => $data['type'],
            'status'                   => $data['status'] ?? 'draft',
            'subject_id'               => ! empty($data['subject_id']) ? (int) $data['subject_id'] : null,
            'grade_level_id'           => ! empty($data['grade_level_id']) ? (int) $data['grade_level_id'] : null,
            'difficulty_filter'        => in_array($data['difficulty_filter'] ?? '', ['easy','medium','hard','mixed']) ? $data['difficulty_filter'] : 'mixed',
            'question_count'           => max(1, (int) ($data['question_count'] ?? 10)),
            'time_limit_minutes'       => max(0, (int) ($data['time_limit_minutes'] ?? 30)),
            'shuffle_questions'        => (int) (bool) ($data['shuffle_questions'] ?? true),
            'shuffle_options'          => (int) (bool) ($data['shuffle_options'] ?? true),
            'show_result_immediately'  => (int) (bool) ($data['show_result_immediately'] ?? true),
            'allow_review'             => (int) (bool) ($data['allow_review'] ?? true),
            'max_attempts'             => max(0, (int) ($data['max_attempts'] ?? 0)),
            'passing_score'            => max(0, min(100, (float) ($data['passing_score'] ?? 60))),
            // New columns (added 2026-08-04)
            'scoring_system'           => in_array($data['scoring_system'] ?? '', ['standard','tka']) ? $data['scoring_system'] : 'standard',
            'time_mode'                => in_array($data['time_mode'] ?? '', ['per_participant','simultaneous']) ? $data['time_mode'] : 'per_participant',
            'access_mode'              => in_array($data['access_mode'] ?? '', ['assigned','public']) ? $data['access_mode'] : 'assigned',
            'show_leaderboard'         => (int) (bool) ($data['show_leaderboard'] ?? false),
            'announce_results'         => (int) (bool) ($data['announce_results'] ?? true),
            'allow_self_reset'         => (int) (bool) ($data['allow_self_reset'] ?? false),
            'is_published'             => (int) (bool) ($data['is_published'] ?? false),
            'is_paused'                => (int) (bool) ($data['is_paused'] ?? false),
            'has_certificate'          => (int) (bool) ($data['has_certificate'] ?? false),
            // Schedule
            'registration_start'       => ! empty($data['registration_start']) ? $data['registration_start'] : null,
            'registration_end'         => ! empty($data['registration_end'])   ? $data['registration_end']   : null,
            'start_time'               => ! empty($data['start_time'])          ? $data['start_time']          : null,
            'end_time'                 => ! empty($data['end_time'])             ? $data['end_time']             : null,
            // Anti-fraud
            'fraud_detect_tab_switch'  => (int) (bool) ($data['fraud_detect_tab_switch'] ?? true),
            'fraud_detect_time_anomaly'=> (int) (bool) ($data['fraud_detect_time_anomaly'] ?? true),
            'fraud_max_tab_switches'   => max(1, (int) ($data['fraud_max_tab_switches'] ?? 3)),
            'fraud_action'             => in_array($data['fraud_action'] ?? '', ['warn','flag','disqualify']) ? $data['fraud_action'] : 'flag',
            'description'              => trim($data['description'] ?? ''),
            'instructions'             => trim($data['instructions'] ?? ''),
        ];
    }

    private function generate_code($type)
    {
        $prefix = $type === 'competition' ? 'KOMP' : 'LAT';
        do {
            $code = $prefix . date('y') . strtoupper(substr(uniqid(), -5));
        } while ($this->db->get_where('quiz_sessions', ['code' => $code])->row_array());
        return $code;
    }

    private function generate_participant_code()
    {
        do {
            $code = 'PST' . strtoupper(substr(uniqid(), -7));
        } while ($this->db->get_where('quiz_participants', ['registration_code' => $code])->row_array());
        return $code;
    }

    private function log($session_id, $action, $entity_id = null, $detail = null, $user_id = null)
    {
        $this->db->insert('quiz_activity_log', [
            'action'      => $action,
            'entity_type' => 'quiz_sessions',
            'entity_id'   => $entity_id ?? $session_id,
            'user_id'     => $user_id,
            'ip_address'  => $this->input->ip_address(),
            'detail'      => $detail ? json_encode($detail, JSON_UNESCAPED_UNICODE) : null,
        ]);
    }
}
