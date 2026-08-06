<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Learn_report_model — agregasi progress belajar member untuk raport.
 *
 * Menggabungkan data lintas modul: poin, lencana, quiz/kompetisi, game,
 * flashcard, story quiz, mode battle. Setiap query lintas-modul dijaga
 * dengan table_exists agar aman walau modul tertentu belum di-migrasi.
 */
class Learn_report_model extends CI_Model
{
    /** Identitas member untuk header raport. */
    public function get_member($user_id)
    {
        return $this->db
            ->select('u.id AS user_id, u.full_name, u.username, m.member_no, m.full_name AS member_name')
            ->from('auth_user u')
            ->join('members m', 'm.auth_user_id = u.id', 'left')
            ->where('u.id', (int) $user_id)
            ->get()->row_array();
    }

    /** Cari member (untuk picker admin). */
    public function search_members($q, $limit = 25, $offset = 0)
    {
        $this->_member_filter($q);
        return $this->db
            ->select('u.id AS user_id, u.full_name, u.username, m.member_no')
            ->from('auth_user u')
            ->join('members m', 'm.auth_user_id = u.id', 'inner')
            ->order_by('u.full_name', 'ASC')
            ->limit($limit, $offset)
            ->get()->result_array();
    }

    public function count_members($q)
    {
        $this->_member_filter($q);
        return (int) $this->db->from('auth_user u')->join('members m', 'm.auth_user_id = u.id', 'inner')->count_all_results();
    }

    private function _member_filter($q)
    {
        if ($q !== '' && $q !== null) {
            $this->db->group_start()
                ->like('u.full_name', $q)
                ->or_like('m.member_no', $q)
                ->or_like('u.username', $q)
                ->group_end();
        }
    }

    /** Agregasi lengkap progress belajar. */
    public function get_report($user_id)
    {
        $user_id = (int) $user_id;
        return [
            'total_points' => $this->_total_points($user_id),
            'points_log'   => $this->_points_log($user_id, 15),
            'badges'       => $this->_badges($user_id),
            'quiz'         => $this->_quiz($user_id),
            'games'        => $this->_games($user_id),
            'flashcard'    => $this->_flashcard($user_id),
            'story'        => $this->_story($user_id),
            'battle'       => $this->_battle($user_id),
        ];
    }

    // ── Bagian-bagian ─────────────────────────────────────────────────────────

    private function _total_points($user_id)
    {
        $row = $this->db->select_sum('points')->where('user_id', $user_id)->get('learn_member_points')->row_array();
        return (int) ($row['points'] ?? 0);
    }

    private function _points_log($user_id, $limit)
    {
        return $this->db
            ->select('mp.points, mp.description, mp.awarded_at, pr.label')
            ->from('learn_member_points mp')
            ->join('learn_point_rules pr', 'pr.id = mp.rule_id', 'left')
            ->where('mp.user_id', $user_id)
            ->order_by('mp.awarded_at', 'DESC')
            ->limit($limit)
            ->get()->result_array();
    }

    private function _badges($user_id)
    {
        return $this->db
            ->select('bd.name, bd.icon, bd.color, mb.awarded_at')
            ->from('learn_member_badges mb')
            ->join('learn_badge_definitions bd', 'bd.id = mb.badge_id')
            ->where('mb.user_id', $user_id)
            ->order_by('mb.awarded_at', 'DESC')
            ->get()->result_array();
    }

    private function _quiz($user_id)
    {
        $summary = ['attempts' => 0, 'passed' => 0, 'avg' => 0, 'recent' => []];
        if (! $this->db->table_exists('quiz_attempts')) return $summary;

        $rows = $this->db
            ->select('qa.percentage, qa.is_passed, qa.status, qa.submitted_at, qs.title, qs.type')
            ->from('quiz_attempts qa')
            ->join('quiz_participants qp', 'qp.id = qa.participant_id')
            ->join('quiz_sessions qs', 'qs.id = qa.session_id', 'left')
            ->where('qp.user_id', $user_id)
            ->where_in('qa.status', ['submitted', 'timed_out'])
            ->order_by('qa.submitted_at', 'DESC')
            ->get()->result_array();

        $summary['attempts'] = count($rows);
        $sum = 0;
        foreach ($rows as $r) {
            $sum += (float) $r['percentage'];
            if ((int) $r['is_passed'] === 1) $summary['passed']++;
        }
        $summary['avg']    = $summary['attempts'] ? round($sum / $summary['attempts'], 1) : 0;
        $summary['recent'] = array_slice($rows, 0, 8);
        return $summary;
    }

    private function _games($user_id)
    {
        $summary = ['played' => 0, 'total_score' => 0, 'recent' => []];
        if (! $this->db->table_exists('learn_game_sessions')) return $summary;

        $rows = $this->db
            ->select('gs.score, gs.max_score, gs.duration_seconds, gs.played_at, gt.name AS game_name')
            ->from('learn_game_sessions gs')
            ->join('learn_game_types gt', 'gt.id = gs.game_type_id', 'left')
            ->where('gs.user_id', $user_id)
            ->where('gs.completed', 1)
            ->order_by('gs.played_at', 'DESC')
            ->get()->result_array();

        $summary['played'] = count($rows);
        foreach ($rows as $r) { $summary['total_score'] += (int) $r['score']; }
        $summary['recent'] = array_slice($rows, 0, 8);
        return $summary;
    }

    private function _flashcard($user_id)
    {
        $summary = ['known' => 0, 'decks' => 0];
        if (! $this->db->table_exists('learn_flashcard_progress')) return $summary;

        $summary['known'] = (int) $this->db->where('user_id', $user_id)->where('status', 'known')->count_all_results('learn_flashcard_progress');
        $row = $this->db->select('COUNT(DISTINCT deck_id) AS d')->where('user_id', $user_id)->get('learn_flashcard_progress')->row_array();
        $summary['decks'] = (int) ($row['d'] ?? 0);
        return $summary;
    }

    private function _story($user_id)
    {
        $summary = ['attempts' => 0, 'avg' => 0, 'perfect' => 0, 'recent' => []];
        if (! $this->db->table_exists('learn_story_attempts')) return $summary;

        $rows = $this->db
            ->select('sa.score_percent, sa.correct_count, sa.total_questions, sa.created_at, p.title')
            ->from('learn_story_attempts sa')
            ->join('learn_story_passages p', 'p.id = sa.passage_id', 'left')
            ->where('sa.user_id', $user_id)
            ->order_by('sa.created_at', 'DESC')
            ->get()->result_array();

        $summary['attempts'] = count($rows);
        $sum = 0;
        foreach ($rows as $r) {
            $sum += (float) $r['score_percent'];
            if ((float) $r['score_percent'] >= 100) $summary['perfect']++;
        }
        $summary['avg']    = $summary['attempts'] ? round($sum / $summary['attempts'], 1) : 0;
        $summary['recent'] = array_slice($rows, 0, 8);
        return $summary;
    }

    private function _battle($user_id)
    {
        $summary = ['played' => 0, 'won' => 0];
        if (! $this->db->table_exists('learn_battle_rooms')) return $summary;

        $summary['played'] = (int) $this->db
            ->where('status', 'finished')
            ->group_start()->where('host_user_id', $user_id)->or_where('guest_user_id', $user_id)->group_end()
            ->count_all_results('learn_battle_rooms');
        $summary['won'] = (int) $this->db
            ->where('status', 'finished')->where('winner_user_id', $user_id)
            ->count_all_results('learn_battle_rooms');
        return $summary;
    }
}
