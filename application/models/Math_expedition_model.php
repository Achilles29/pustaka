<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Data layer untuk Math Expedition.
 * Soal disimpan sebagai data terkurasi (bukan dibuat acak di browser), agar
 * hint, pembahasan, dan jenjangnya tetap dapat dipertanggungjawabkan.
 */
class Math_expedition_model extends CI_Model
{
    public function levels_for_user($user_id)
    {
        return $this->db
            ->select('l.*, COALESCE(p.best_score, 0) AS best_score, COALESCE(p.best_correct, 0) AS best_correct, COALESCE(p.attempts, 0) AS attempts, p.completed_at')
            ->from('math_expedition_levels l')
            ->join('math_expedition_progress p', 'p.level_id = l.id AND p.user_id = ' . (int) $user_id, 'left', false)
            ->where('l.is_active', 1)
            ->order_by('l.stage_number', 'ASC')
            ->get()->result_array();
    }

    public function level_by_code($code)
    {
        return $this->db->get_where('math_expedition_levels', [
            'code' => (string) $code,
            'is_active' => 1,
        ])->row_array();
    }

    public function questions_for_level($level_id, $limit = 5)
    {
        return $this->db
            ->where('level_id', (int) $level_id)
            ->where('is_active', 1)
            ->order_by('RAND()', '', false)
            ->limit((int) $limit)
            ->get('math_expedition_questions')
            ->result_array();
    }

    public function question($question_id)
    {
        return $this->db->get_where('math_expedition_questions', [
            'id' => (int) $question_id,
            'is_active' => 1,
        ])->row_array();
    }

    public function overview($user_id)
    {
        $row = $this->db
            ->select('COUNT(*) AS levels_completed, COALESCE(SUM(attempts),0) AS attempts, COALESCE(MAX(best_score),0) AS top_score')
            ->from('math_expedition_progress')
            ->where('user_id', (int) $user_id)
            ->where('completed_at IS NOT NULL', null, false)
            ->get()->row_array();
        return [
            'levels_completed' => (int) ($row['levels_completed'] ?? 0),
            'attempts' => (int) ($row['attempts'] ?? 0),
            'top_score' => (int) ($row['top_score'] ?? 0),
        ];
    }

    public function complete_level($user_id, array $level, $correct, $total, $duration_seconds)
    {
        $user_id = (int) $user_id;
        $correct = max(0, (int) $correct);
        $total = max(1, (int) $total);
        $score = (int) round(($correct / $total) * 100);
        $passed = $correct >= (int) $level['passing_correct'];

        $this->db->trans_start();
        $this->db->insert('math_expedition_attempts', [
            'user_id' => $user_id,
            'level_id' => (int) $level['id'],
            'correct_count' => $correct,
            'question_count' => $total,
            'score' => $score,
            'duration_seconds' => max(0, (int) $duration_seconds),
            'completed_at' => date('Y-m-d H:i:s'),
        ]);

        $progress = $this->db->get_where('math_expedition_progress', [
            'user_id' => $user_id,
            'level_id' => (int) $level['id'],
        ])->row_array();
        $now = date('Y-m-d H:i:s');
        if ($progress) {
            $data = [
                'attempts' => (int) $progress['attempts'] + 1,
                'last_played_at' => $now,
                'updated_at' => $now,
            ];
            if ($score > (int) $progress['best_score']) {
                $data['best_score'] = $score;
                $data['best_correct'] = $correct;
            }
            if ($passed && empty($progress['completed_at'])) $data['completed_at'] = $now;
            $this->db->where('id', (int) $progress['id'])->update('math_expedition_progress', $data);
        } else {
            $this->db->insert('math_expedition_progress', [
                'user_id' => $user_id,
                'level_id' => (int) $level['id'],
                'best_score' => $score,
                'best_correct' => $correct,
                'attempts' => 1,
                'completed_at' => $passed ? $now : null,
                'last_played_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
        $this->db->trans_complete();
        return ['ok' => $this->db->trans_status(), 'score' => $score, 'passed' => $passed];
    }
}
