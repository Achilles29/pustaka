<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Learn_points_model extends CI_Model
{
    // ── Point Rules ───────────────────────────────────────────────────────────

    public function get_rules($active_only = false)
    {
        if ($active_only) {
            $this->db->where('is_active', 1);
        }
        return $this->db->order_by('action_code')->get('learn_point_rules')->result_array();
    }

    public function get_rule($id)
    {
        return $this->db->get_where('learn_point_rules', ['id' => $id])->row_array();
    }

    public function get_rule_by_code($code)
    {
        return $this->db->get_where('learn_point_rules', ['action_code' => $code, 'is_active' => 1])->row_array();
    }

    public function create_rule($data)
    {
        $this->db->insert('learn_point_rules', [
            'action_code'    => $data['action_code'],
            'label'          => $data['label'],
            'description'    => $data['description'] ?? null,
            'points'         => (int) $data['points'],
            'cooldown_hours' => (int) ($data['cooldown_hours'] ?? 0),
            'is_active'      => (int) ($data['is_active'] ?? 1),
        ]);
        return $this->db->insert_id();
    }

    public function update_rule($id, $data)
    {
        $this->db->where('id', $id)->update('learn_point_rules', [
            'action_code'    => $data['action_code'],
            'label'          => $data['label'],
            'description'    => $data['description'] ?? null,
            'points'         => (int) $data['points'],
            'cooldown_hours' => (int) ($data['cooldown_hours'] ?? 0),
            'is_active'      => (int) ($data['is_active'] ?? 1),
        ]);
        return $this->db->affected_rows() > 0;
    }

    public function delete_rule($id)
    {
        $this->db->where('id', $id)->delete('learn_point_rules');
        return $this->db->affected_rows() > 0;
    }

    // ── Award Points ──────────────────────────────────────────────────────────

    /**
     * Berikan poin ke user berdasarkan action_code.
     * Memperhatikan cooldown dan apakah rule aktif.
     * Setelah berhasil awarding, cek badge.
     *
     * @return int|false  Jumlah poin yang diberikan, atau false jika tidak bisa
     */
    public function award_points($user_id, $action_code, $ref_type = null, $ref_id = null, $description = null)
    {
        $rule = $this->get_rule_by_code($action_code);
        if (!$rule || $rule['points'] <= 0) {
            return false;
        }

        // Cooldown check
        if ((int) $rule['cooldown_hours'] > 0 && $ref_id === null) {
            $since = date('Y-m-d H:i:s', strtotime('-' . $rule['cooldown_hours'] . ' hours'));
            $exists = $this->db
                ->where('user_id', $user_id)
                ->where('rule_id', $rule['id'])
                ->where('awarded_at >=', $since)
                ->count_all_results('learn_member_points');
            if ($exists) {
                return false;
            }
        }

        // ref_id-based duplicate check: jangan dobel poin untuk reference yang sama
        if ($ref_type && $ref_id) {
            $exists = $this->db
                ->where('user_id', $user_id)
                ->where('rule_id', $rule['id'])
                ->where('reference_type', $ref_type)
                ->where('reference_id', $ref_id)
                ->count_all_results('learn_member_points');
            if ($exists) {
                return false;
            }
        }

        $this->db->insert('learn_member_points', [
            'user_id'        => (int) $user_id,
            'rule_id'        => (int) $rule['id'],
            'points'         => (int) $rule['points'],
            'description'    => $description ?: $rule['label'],
            'reference_type' => $ref_type,
            'reference_id'   => $ref_id ? (int) $ref_id : null,
            'awarded_at'     => date('Y-m-d H:i:s'),
        ]);

        // Trigger badge check
        $this->check_and_award_badges($user_id);

        return (int) $rule['points'];
    }

    public function get_user_total_points($user_id)
    {
        $row = $this->db
            ->select_sum('points')
            ->where('user_id', $user_id)
            ->get('learn_member_points')
            ->row_array();
        return (int) ($row['points'] ?? 0);
    }

    public function get_user_points_log($user_id, $limit = 20)
    {
        return $this->db
            ->select('mp.*, pr.label AS rule_label, pr.action_code')
            ->from('learn_member_points mp')
            ->join('learn_point_rules pr', 'pr.id = mp.rule_id', 'left')
            ->where('mp.user_id', $user_id)
            ->order_by('mp.awarded_at', 'DESC')
            ->limit($limit)
            ->get()
            ->result_array();
    }

    public function get_leaderboard($limit = 20, $days = 30)
    {
        $since = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        return $this->db
            ->select('mp.user_id, SUM(mp.points) AS total_points, u.full_name, u.username')
            ->from('learn_member_points mp')
            ->join('auth_user u', 'u.id = mp.user_id', 'left')
            ->where('mp.awarded_at >=', $since)
            ->group_by('mp.user_id')
            ->order_by('total_points', 'DESC')
            ->limit($limit)
            ->get()
            ->result_array();
    }

    public function get_stats()
    {
        $rules  = $this->db->count_all('learn_point_rules');
        $badges = $this->db->count_all('learn_badge_definitions');
        $awarded = $this->db->count_all('learn_member_points');
        $badges_awarded = $this->db->count_all('learn_member_badges');
        return compact('rules', 'badges', 'awarded', 'badges_awarded');
    }

    // ── Badge Definitions ─────────────────────────────────────────────────────

    public function get_badges($active_only = false)
    {
        if ($active_only) {
            $this->db->where('is_active', 1);
        }
        return $this->db->order_by('criteria_type, criteria_value')->get('learn_badge_definitions')->result_array();
    }

    public function get_badge($id)
    {
        return $this->db->get_where('learn_badge_definitions', ['id' => $id])->row_array();
    }

    public function create_badge($data)
    {
        $this->db->insert('learn_badge_definitions', [
            'code'                => $data['code'],
            'name'                => $data['name'],
            'description'         => $data['description'] ?? null,
            'icon'                => $data['icon'] ?? 'ti-award',
            'color'               => $data['color'] ?? '#3b82f6',
            'criteria_type'       => $data['criteria_type'],
            'criteria_value'      => (int) $data['criteria_value'],
            'criteria_subject_id' => !empty($data['criteria_subject_id']) ? (int) $data['criteria_subject_id'] : null,
            'is_active'           => (int) ($data['is_active'] ?? 1),
        ]);
        return $this->db->insert_id();
    }

    public function update_badge($id, $data)
    {
        $this->db->where('id', $id)->update('learn_badge_definitions', [
            'code'                => $data['code'],
            'name'                => $data['name'],
            'description'         => $data['description'] ?? null,
            'icon'                => $data['icon'] ?? 'ti-award',
            'color'               => $data['color'] ?? '#3b82f6',
            'criteria_type'       => $data['criteria_type'],
            'criteria_value'      => (int) $data['criteria_value'],
            'criteria_subject_id' => !empty($data['criteria_subject_id']) ? (int) $data['criteria_subject_id'] : null,
            'is_active'           => (int) ($data['is_active'] ?? 1),
        ]);
        return $this->db->affected_rows() > 0;
    }

    public function delete_badge($id)
    {
        $this->db->where('id', $id)->delete('learn_badge_definitions');
        return $this->db->affected_rows() > 0;
    }

    // ── Member Badges ─────────────────────────────────────────────────────────

    public function get_user_badges($user_id)
    {
        return $this->db
            ->select('mb.*, bd.name, bd.description, bd.icon, bd.color, bd.code')
            ->from('learn_member_badges mb')
            ->join('learn_badge_definitions bd', 'bd.id = mb.badge_id')
            ->where('mb.user_id', $user_id)
            ->order_by('mb.awarded_at', 'DESC')
            ->get()
            ->result_array();
    }

    /**
     * Periksa semua badge yang belum diperoleh user dan berikan jika kriteria terpenuhi.
     * Dipanggil otomatis setelah award_points.
     */
    public function check_and_award_badges($user_id)
    {
        $all_badges = $this->get_badges(true);
        $owned_ids  = array_column($this->db
            ->select('badge_id')
            ->where('user_id', $user_id)
            ->get('learn_member_badges')
            ->result_array(), 'badge_id');

        foreach ($all_badges as $badge) {
            if (in_array($badge['id'], $owned_ids)) {
                continue;
            }
            if ($this->_check_badge_criteria($user_id, $badge)) {
                $this->db->insert('learn_member_badges', [
                    'user_id'    => (int) $user_id,
                    'badge_id'   => (int) $badge['id'],
                    'awarded_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    private function _check_badge_criteria($user_id, $badge)
    {
        $type  = $badge['criteria_type'];
        $value = (int) $badge['criteria_value'];
        $subj  = $badge['criteria_subject_id'];

        switch ($type) {
            case 'points_total':
                return $this->get_user_total_points($user_id) >= $value;

            case 'quiz_complete':
                return $this->_count_quiz_attempts($user_id, null, null, $subj) >= $value;

            case 'quiz_pass':
                return $this->_count_quiz_attempts($user_id, null, true, $subj) >= $value;

            case 'quiz_perfect':
                return $this->_count_quiz_attempts($user_id, 100, null, $subj) >= $value;

            case 'game_complete':
                return $this->_count_game_sessions($user_id) >= $value;

            case 'visit_count':
                return $this->_count_visits($user_id) >= $value;

            case 'book_read':
                return $this->_count_books_read($user_id) >= $value;

            default:
                return false;
        }
    }

    private function _count_quiz_attempts($user_id, $pct_min = null, $passed = null, $subject_id = null)
    {
        $this->db
            ->from('quiz_attempts qa')
            ->join('quiz_participants qp', 'qp.id = qa.participant_id', 'left')
            ->join('quiz_sessions qs', 'qs.id = qa.session_id', 'left')
            ->where('qp.user_id', $user_id)
            ->where('qa.status IN ("submitted","timed_out")');

        if ($pct_min !== null) {
            $this->db->where('qa.percentage >=', $pct_min);
        }
        if ($passed !== null) {
            $this->db->where('qa.is_passed', $passed ? 1 : 0);
        }
        if ($subject_id) {
            $this->db->where('qs.subject_id', $subject_id);
        }
        return $this->db->count_all_results();
    }

    private function _count_game_sessions($user_id)
    {
        return $this->db
            ->where('user_id', $user_id)
            ->where('completed', 1)
            ->count_all_results('learn_game_sessions');
    }

    private function _count_visits($user_id)
    {
        return $this->db
            ->join('members m', 'm.id = v.member_id')
            ->join('auth_user u', 'u.id = m.auth_user_id')
            ->where('u.id', $user_id)
            ->count_all_results('visits v');
    }

    private function _count_books_read($user_id)
    {
        return $this->db
            ->where('user_id', $user_id)
            ->where('reference_type', 'book')
            ->count_all_results('learn_member_points');
    }
}
