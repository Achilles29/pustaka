<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Learn_story_model — bacaan (passage) + soal pemahaman + attempt.
 *
 * Member membaca passage lalu menjawab soal pilihan ganda.
 * Poin diberikan sekali per bacaan (dedup via reference).
 */
class Learn_story_model extends CI_Model
{
    // ── Passages ──────────────────────────────────────────────────────────────

    public function get_passages($active_only = false)
    {
        $this->db
            ->select('p.*, s.name AS subject_name, g.name AS grade_name,
                      (SELECT COUNT(*) FROM learn_story_questions q WHERE q.passage_id = p.id AND q.is_active = 1) AS question_count', false)
            ->from('learn_story_passages p')
            ->join('quiz_subjects s', 's.id = p.subject_id', 'left')
            ->join('quiz_grade_levels g', 'g.id = p.grade_level_id', 'left');
        if ($active_only) {
            $this->db->where('p.is_active', 1);
        }
        return $this->db
            ->order_by('p.sort_order', 'ASC')->order_by('p.title', 'ASC')
            ->get()->result_array();
    }

    public function get_passage($id)
    {
        return $this->db->get_where('learn_story_passages', ['id' => (int) $id])->row_array();
    }

    public function get_passage_by_code($code)
    {
        return $this->db->get_where('learn_story_passages', ['code' => $code])->row_array();
    }

    public function code_exists($code, $exclude_id = null)
    {
        $this->db->where('code', $code);
        if ($exclude_id !== null) {
            $this->db->where('id !=', (int) $exclude_id);
        }
        return $this->db->count_all_results('learn_story_passages') > 0;
    }

    public function create_passage($data)
    {
        $this->db->insert('learn_story_passages', $this->_clean_passage($data));
        return (int) $this->db->insert_id();
    }

    public function update_passage($id, $data)
    {
        $this->db->where('id', (int) $id)->update('learn_story_passages', $this->_clean_passage($data));
        return $this->db->affected_rows() >= 0;
    }

    public function delete_passage($id)
    {
        $this->db->where('id', (int) $id)->delete('learn_story_passages');
        return $this->db->affected_rows() > 0;
    }

    public function toggle_passage($id)
    {
        $p = $this->get_passage($id);
        if (! $p) return false;
        $this->db->where('id', (int) $id)->update('learn_story_passages', [
            'is_active' => (int) $p['is_active'] === 1 ? 0 : 1,
        ]);
        return true;
    }

    private function _clean_passage($data)
    {
        return [
            'code'              => trim((string) $data['code']),
            'title'             => trim((string) $data['title']),
            'body'              => (string) $data['body'],
            'summary'           => trim((string) ($data['summary'] ?? '')) ?: null,
            'subject_id'        => ! empty($data['subject_id']) ? (int) $data['subject_id'] : null,
            'grade_level_id'    => ! empty($data['grade_level_id']) ? (int) $data['grade_level_id'] : null,
            'icon'              => trim((string) ($data['icon'] ?? 'ti-book')) ?: 'ti-book',
            'color'             => trim((string) ($data['color'] ?? '#0891b2')) ?: '#0891b2',
            'estimated_minutes' => max(1, (int) ($data['estimated_minutes'] ?? 3)),
            'sort_order'        => max(0, (int) ($data['sort_order'] ?? 100)),
            'is_active'         => (int) (bool) ($data['is_active'] ?? 1),
        ];
    }

    // ── Questions ─────────────────────────────────────────────────────────────

    public function get_questions($passage_id, $active_only = false)
    {
        $this->db->where('passage_id', (int) $passage_id);
        if ($active_only) {
            $this->db->where('is_active', 1);
        }
        return $this->db
            ->order_by('sort_order', 'ASC')->order_by('id', 'ASC')
            ->get('learn_story_questions')->result_array();
    }

    public function get_question($id)
    {
        return $this->db->get_where('learn_story_questions', ['id' => (int) $id])->row_array();
    }

    public function create_question($passage_id, $data)
    {
        $payload = $this->_clean_question($data);
        $payload['passage_id'] = (int) $passage_id;
        $this->db->insert('learn_story_questions', $payload);
        return (int) $this->db->insert_id();
    }

    public function update_question($id, $data)
    {
        $this->db->where('id', (int) $id)->update('learn_story_questions', $this->_clean_question($data));
        return $this->db->affected_rows() >= 0;
    }

    public function delete_question($id)
    {
        $this->db->where('id', (int) $id)->delete('learn_story_questions');
        return $this->db->affected_rows() > 0;
    }

    private function _clean_question($data)
    {
        $correct = (int) ($data['correct_option'] ?? 0);
        return [
            'question'       => trim((string) $data['question']),
            'option_a'       => trim((string) $data['option_a']),
            'option_b'       => trim((string) $data['option_b']),
            'option_c'       => trim((string) ($data['option_c'] ?? '')) ?: null,
            'option_d'       => trim((string) ($data['option_d'] ?? '')) ?: null,
            'correct_option' => ($correct >= 0 && $correct <= 3) ? $correct : 0,
            'explanation'    => trim((string) ($data['explanation'] ?? '')) ?: null,
            'sort_order'     => max(0, (int) ($data['sort_order'] ?? 0)),
            'is_active'      => (int) (bool) ($data['is_active'] ?? 1),
        ];
    }

    /** Soal untuk player (tanpa membocorkan kunci ke HTML awal bila perlu) */
    public function get_questions_for_play($passage_id)
    {
        return $this->get_questions($passage_id, true);
    }

    // ── Attempts / penilaian ──────────────────────────────────────────────────

    /**
     * Nilai jawaban member. $answers = [question_id => selected_index].
     * @return array hasil: per soal benar/salah + skor + kunci + penjelasan
     */
    public function grade($passage_id, array $answers)
    {
        $questions = $this->get_questions($passage_id, true);
        $total   = count($questions);
        $correct = 0;
        $details = [];

        foreach ($questions as $q) {
            $qid      = (int) $q['id'];
            $selected = array_key_exists($qid, $answers) ? (int) $answers[$qid] : -1;
            $is_ok    = ($selected === (int) $q['correct_option']);
            if ($is_ok) $correct++;
            $details[] = [
                'id'             => $qid,
                'question'       => $q['question'],
                'selected'       => $selected,
                'correct_option' => (int) $q['correct_option'],
                'is_correct'     => $is_ok,
                'explanation'    => $q['explanation'],
            ];
        }

        $percent = $total > 0 ? round($correct / $total * 100, 2) : 0;
        return [
            'total'   => $total,
            'correct' => $correct,
            'percent' => $percent,
            'details' => $details,
        ];
    }

    public function record_attempt($user_id, $passage_id, $correct, $total, $percent, $duration = 0)
    {
        $this->db->insert('learn_story_attempts', [
            'user_id'          => (int) $user_id,
            'passage_id'       => (int) $passage_id,
            'correct_count'    => (int) $correct,
            'total_questions'  => (int) $total,
            'score_percent'    => (float) $percent,
            'duration_seconds' => (int) $duration,
            'created_at'       => date('Y-m-d H:i:s'),
        ]);
        return (int) $this->db->insert_id();
    }

    public function best_scores_for_user($user_id)
    {
        $rows = $this->db
            ->select('passage_id, MAX(score_percent) AS best')
            ->where('user_id', (int) $user_id)
            ->group_by('passage_id')
            ->get('learn_story_attempts')->result_array();
        $map = [];
        foreach ($rows as $r) {
            $map[(int) $r['passage_id']] = (float) $r['best'];
        }
        return $map;
    }

    // ── Stats (admin) ─────────────────────────────────────────────────────────

    public function stats()
    {
        $passages  = (int) $this->db->count_all('learn_story_passages');
        $active    = (int) $this->db->where('is_active', 1)->count_all_results('learn_story_passages');
        $questions = (int) $this->db->count_all('learn_story_questions');
        $attempts  = (int) $this->db->count_all('learn_story_attempts');
        return compact('passages', 'active', 'questions', 'attempts');
    }
}
