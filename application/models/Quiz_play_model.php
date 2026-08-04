<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Quiz_play_model extends CI_Model
{
    // ── Attempt Management ────────────────────────────────────────────────────

    public function get_active_attempt($participant_id, $session_id)
    {
        return $this->db
            ->where('participant_id', (int) $participant_id)
            ->where('session_id', (int) $session_id)
            ->where('status', 'in_progress')
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get('quiz_attempts')->row_array();
    }

    public function get_attempt_by_token($token)
    {
        return $this->db
            ->select('a.*, s.title AS session_title, s.type AS session_type, s.time_limit_minutes,
                      s.shuffle_options, s.show_result_immediately, s.allow_review, s.passing_score,
                      s.fraud_detect_tab_switch, s.fraud_max_tab_switches, s.fraud_action, s.fraud_detect_time_anomaly,
                      p.full_name AS participant_name')
            ->from('quiz_attempts a')
            ->join('quiz_sessions s', 's.id = a.session_id')
            ->join('quiz_participants p', 'p.id = a.participant_id')
            ->where('a.session_token', $token)
            ->get()->row_array();
    }

    public function count_attempts($participant_id, $session_id)
    {
        return (int) $this->db
            ->where('participant_id', (int) $participant_id)
            ->where('session_id', (int) $session_id)
            ->where_in('status', ['submitted', 'timed_out', 'in_progress'])
            ->count_all_results('quiz_attempts');
    }

    public function create_attempt($session_id, $participant_id, array $question_ids, $attempt_number = 1)
    {
        $token = bin2hex(random_bytes(32));

        $this->db->insert('quiz_attempts', [
            'session_id'     => (int) $session_id,
            'participant_id' => (int) $participant_id,
            'attempt_number' => $attempt_number,
            'ip_address'     => $this->input->ip_address(),
            'user_agent'     => substr((string) $this->input->user_agent(), 0, 500),
            'session_token'  => $token,
            'status'         => 'in_progress',
            'question_order' => json_encode($question_ids),
            'started_at'     => date('Y-m-d H:i:s'),
        ]);

        $attempt_id = (int) $this->db->insert_id();

        // Pre-create answer rows so saving is always UPDATE
        foreach ($question_ids as $qid) {
            $this->db->insert('quiz_attempt_answers', [
                'attempt_id'   => $attempt_id,
                'question_id'  => (int) $qid,
                'answered_at'  => date('Y-m-d H:i:s'),
            ]);
        }

        $this->write_log('quiz.attempt.start', $attempt_id, $participant_id);
        return ['attempt_id' => $attempt_id, 'token' => $token];
    }

    // ── Fetch Questions for Exam ──────────────────────────────────────────────

    public function get_exam_questions(array $question_ids, $shuffle_options = true)
    {
        if (empty($question_ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($question_ids), '?'));
        $rows = $this->db
            ->select('q.id, q.type, q.question_text, q.question_image, q.score_weight, q.difficulty')
            ->from('quiz_questions q')
            ->where_in('q.id', $question_ids)
            ->get()->result_array();

        $question_map = [];
        foreach ($rows as $q) {
            $q['options'] = [];
            $question_map[(int) $q['id']] = $q;
        }

        // Fetch options
        if (! empty($question_map)) {
            $opts = $this->db->where_in('question_id', array_keys($question_map))->order_by('option_index', 'ASC')->get('quiz_question_options')->result_array();
            foreach ($opts as $opt) {
                $qid = (int) $opt['question_id'];
                if ($shuffle_options) {
                    $question_map[$qid]['options'][] = $opt;
                } else {
                    $question_map[$qid]['options'][] = $opt;
                }
            }

            if ($shuffle_options) {
                foreach ($question_map as &$q) {
                    if ($q['type'] === 'multiple_choice' && ! empty($q['options'])) {
                        // Build shuffle map so we can track correct answer remapping
                        shuffle($q['options']);
                        // Re-index option_index after shuffle
                        foreach ($q['options'] as $i => &$opt) {
                            $opt['display_index'] = $i;
                        }
                    }
                }
            }
        }

        // Return in the original question_ids order
        $ordered = [];
        foreach ($question_ids as $id) {
            if (isset($question_map[(int) $id])) {
                $ordered[] = $question_map[(int) $id];
            }
        }
        return $ordered;
    }

    // ── Save Answer ───────────────────────────────────────────────────────────

    public function save_answer($attempt_id, $question_id, $selected_option = null, $essay_answer = null, $time_spent = 0)
    {
        $attempt = $this->db->get_where('quiz_attempts', ['id' => (int) $attempt_id, 'status' => 'in_progress'])->row_array();
        if (! $attempt) {
            return false;
        }

        $existing = $this->db->where('attempt_id', (int) $attempt_id)->where('question_id', (int) $question_id)->get('quiz_attempt_answers')->row_array();

        $was_answered = $existing && ($existing['selected_option'] !== null || ($existing['essay_answer'] !== null && $existing['essay_answer'] !== ''));
        $change_count = $existing ? (int) $existing['answer_change_count'] : 0;
        if ($was_answered) {
            $change_count++;
        }

        $this->db->update('quiz_attempt_answers', [
            'selected_option'     => $selected_option !== null ? (int) $selected_option : null,
            'essay_answer'        => $essay_answer,
            'time_spent_seconds'  => max(0, (int) $time_spent),
            'answer_change_count' => $change_count,
            'answered_at'         => date('Y-m-d H:i:s'),
        ], ['attempt_id' => (int) $attempt_id, 'question_id' => (int) $question_id]);

        return true;
    }

    // ── Submit ────────────────────────────────────────────────────────────────

    public function submit_attempt($attempt_id, $time_spent_seconds)
    {
        $attempt = $this->db->get_where('quiz_attempts', ['id' => (int) $attempt_id])->row_array();
        if (! $attempt || $attempt['status'] !== 'in_progress') {
            return null;
        }

        $answers = $this->db->where('attempt_id', (int) $attempt_id)->get('quiz_attempt_answers')->result_array();
        $questions = $this->get_questions_for_scoring($attempt['question_order']);

        $total = 0;
        $max   = 0;
        $has_essay = false;

        foreach ($answers as $ans) {
            $q = $questions[(int) $ans['question_id']] ?? null;
            if (! $q) {
                continue;
            }

            $weight = (float) $q['score_weight'];
            $max += $weight;

            if ($q['type'] === 'multiple_choice') {
                $is_correct = (int) $ans['selected_option'] === (int) $q['correct_option_index'] && $ans['selected_option'] !== null;
                $score = $is_correct ? $weight : 0;
                $this->db->update('quiz_attempt_answers', [
                    'is_correct'  => $is_correct ? 1 : 0,
                    'score_earned'=> $score,
                ], ['id' => (int) $ans['id']]);
                $total += $score;
            } else {
                $has_essay = true;
                // Essay graded separately
            }
        }

        $percentage = $max > 0 ? round(($total / $max) * 100, 2) : 0;

        $session = $this->db->get_where('quiz_sessions', ['id' => (int) $attempt['session_id']])->row_array();
        $passing = (float) ($session['passing_score'] ?? 60);

        $this->db->update('quiz_attempts', [
            'status'            => 'submitted',
            'submitted_at'      => date('Y-m-d H:i:s'),
            'time_spent_seconds'=> max(0, (int) $time_spent_seconds),
            'total_score'       => $total,
            'max_possible_score'=> $max,
            'percentage'        => $percentage,
            'is_passed'         => $has_essay ? null : ($percentage >= $passing ? 1 : 0),
            'essay_graded'      => $has_essay ? 0 : 1,
        ], ['id' => (int) $attempt_id]);

        $this->write_log('quiz.attempt.submit', (int) $attempt_id, (int) $attempt['participant_id']);

        return [
            'attempt_id'  => (int) $attempt_id,
            'total_score' => $total,
            'max'         => $max,
            'percentage'  => $percentage,
            'is_passed'   => $has_essay ? null : ($percentage >= $passing),
            'has_essay'   => $has_essay,
        ];
    }

    public function mark_timed_out($attempt_id)
    {
        $this->db->update('quiz_attempts', [
            'status'       => 'timed_out',
            'submitted_at' => date('Y-m-d H:i:s'),
        ], ['id' => (int) $attempt_id, 'status' => 'in_progress']);
        if ($this->db->affected_rows()) {
            return $this->submit_attempt($attempt_id, 0);
        }
        return null;
    }

    // ── Anti-Fraud ────────────────────────────────────────────────────────────

    public function log_fraud_event($attempt_id, $event_type, $detail = null, $question_id = null)
    {
        $allowed = ['tab_switch','window_blur','copy_attempt','paste_attempt','right_click','devtools_open','time_anomaly','suspicious_pattern'];
        if (! in_array($event_type, $allowed, true)) {
            return false;
        }

        $this->db->insert('quiz_fraud_logs', [
            'attempt_id'   => (int) $attempt_id,
            'event_type'   => $event_type,
            'event_detail' => $detail ? json_encode($detail, JSON_UNESCAPED_UNICODE) : null,
            'question_id'  => $question_id ? (int) $question_id : null,
            'occurred_at'  => date('Y-m-d H:i:s'),
        ]);

        // Update fraud score
        $fraud_score_delta = ['tab_switch' => 5, 'window_blur' => 2, 'copy_attempt' => 10,
                              'paste_attempt' => 10, 'right_click' => 3, 'devtools_open' => 20,
                              'time_anomaly' => 8, 'suspicious_pattern' => 15];
        $delta = $fraud_score_delta[$event_type] ?? 5;

        $this->db->set('fraud_score', 'fraud_score + ' . $delta, false)->where('id', (int) $attempt_id)->update('quiz_attempts');

        // Check if action should be taken
        $attempt = $this->db->select('fraud_score, session_id')->get_where('quiz_attempts', ['id' => (int) $attempt_id])->row_array();
        $session  = $this->db->select('fraud_action, fraud_max_tab_switches')->get_where('quiz_sessions', ['id' => (int) $attempt['session_id']])->row_array();

        if ($event_type === 'tab_switch' && $session) {
            $tab_count = (int) $this->db->where('attempt_id', (int) $attempt_id)->where('event_type', 'tab_switch')->count_all_results('quiz_fraud_logs');
            if ($tab_count >= (int) $session['fraud_max_tab_switches']) {
                if ($session['fraud_action'] === 'disqualify') {
                    $this->db->update('quiz_attempts', ['status' => 'disqualified', 'is_flagged' => 1, 'flag_reason' => 'Pindah tab terlalu sering.'], ['id' => (int) $attempt_id]);
                    return ['action' => 'disqualified', 'tab_count' => $tab_count];
                }
                if ($session['fraud_action'] === 'flag') {
                    $this->db->update('quiz_attempts', ['is_flagged' => 1, 'flag_reason' => 'Tab switch melebihi batas.'], ['id' => (int) $attempt_id]);
                }
                return ['action' => $session['fraud_action'], 'tab_count' => $tab_count];
            }
        }

        return ['action' => 'logged', 'fraud_score' => (int) ($attempt['fraud_score'] ?? 0) + $delta];
    }

    public function get_fraud_logs($attempt_id)
    {
        return $this->db->where('attempt_id', (int) $attempt_id)->order_by('id', 'ASC')->get('quiz_fraud_logs')->result_array();
    }

    // ── Review ────────────────────────────────────────────────────────────────

    public function get_review_data($attempt_id)
    {
        $attempt = $this->db
            ->select('a.*, s.allow_review, s.show_result_immediately, s.title AS session_title, p.full_name')
            ->from('quiz_attempts a')
            ->join('quiz_sessions s', 's.id = a.session_id')
            ->join('quiz_participants p', 'p.id = a.participant_id')
            ->where('a.id', (int) $attempt_id)
            ->get()->row_array();

        if (! $attempt) {
            return null;
        }

        $answers = $this->db
            ->select('aa.*, q.question_text, q.type, q.correct_option_index, q.explanation, q.score_weight')
            ->from('quiz_attempt_answers aa')
            ->join('quiz_questions q', 'q.id = aa.question_id')
            ->where('aa.attempt_id', (int) $attempt_id)
            ->get()->result_array();

        $question_ids = array_column($answers, 'question_id');
        $options_map  = [];
        if (! empty($question_ids)) {
            $opts = $this->db->where_in('question_id', $question_ids)->order_by('option_index','ASC')->get('quiz_question_options')->result_array();
            foreach ($opts as $opt) {
                $options_map[(int) $opt['question_id']][] = $opt;
            }
        }

        foreach ($answers as &$ans) {
            $ans['options'] = $options_map[(int) $ans['question_id']] ?? [];
        }

        return ['attempt' => $attempt, 'answers' => $answers];
    }

    // ── Heartbeat ─────────────────────────────────────────────────────────────

    public function check_attempt_status($attempt_id, $token)
    {
        $attempt = $this->db->get_where('quiz_attempts', ['id' => (int) $attempt_id, 'session_token' => $token])->row_array();
        if (! $attempt) {
            return null;
        }

        if ($attempt['status'] !== 'in_progress') {
            return ['status' => $attempt['status'], 'action' => 'stop'];
        }

        // Check time limit
        $session = $this->db->select('time_limit_minutes')->get_where('quiz_sessions', ['id' => (int) $attempt['session_id']])->row_array();
        $time_limit = (int) ($session['time_limit_minutes'] ?? 0);
        if ($time_limit > 0) {
            $elapsed = time() - strtotime($attempt['started_at']);
            $remaining = ($time_limit * 60) - $elapsed;
            if ($remaining <= 0) {
                $this->mark_timed_out((int) $attempt_id);
                return ['status' => 'timed_out', 'action' => 'submit', 'remaining' => 0];
            }
            return ['status' => 'in_progress', 'action' => 'continue', 'remaining' => $remaining];
        }

        return ['status' => 'in_progress', 'action' => 'continue', 'remaining' => -1];
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function get_questions_for_scoring($question_order_json)
    {
        $ids = json_decode($question_order_json, true);
        if (empty($ids)) {
            return [];
        }
        $rows = $this->db->where_in('id', $ids)->get('quiz_questions')->result_array();
        $map = [];
        foreach ($rows as $r) {
            $map[(int) $r['id']] = $r;
        }
        return $map;
    }

    private function write_log($action, $attempt_id, $participant_id)
    {
        $this->db->insert('quiz_activity_log', [
            'action'         => $action,
            'entity_type'    => 'quiz_attempts',
            'entity_id'      => $attempt_id,
            'participant_id' => $participant_id,
            'ip_address'     => $this->input->ip_address(),
        ]);
    }
}
