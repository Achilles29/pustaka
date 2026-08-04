<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Learn_games_model extends CI_Model
{
    // ── Game Types ────────────────────────────────────────────────────────────

    public function get_game_types($active_only = false)
    {
        if ($active_only) {
            $this->db->where('is_active', 1);
        }
        return $this->db->order_by('sort_order, id')->get('learn_game_types')->result_array();
    }

    public function get_game_type($id)
    {
        return $this->db->get_where('learn_game_types', ['id' => $id])->row_array();
    }

    public function get_game_type_by_code($code)
    {
        return $this->db->get_where('learn_game_types', ['code' => $code, 'is_active' => 1])->row_array();
    }

    public function toggle_game_type($id)
    {
        $row = $this->get_game_type($id);
        if (!$row) return false;
        $this->db->where('id', $id)->update('learn_game_types', ['is_active' => $row['is_active'] ? 0 : 1]);
        return true;
    }

    public function update_game_type($id, $data)
    {
        $this->db->where('id', $id)->update('learn_game_types', [
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'icon'        => $data['icon'] ?? 'ti-puzzle',
            'color'       => $data['color'] ?? '#6366f1',
            'sort_order'  => (int) ($data['sort_order'] ?? 0),
        ]);
        return $this->db->affected_rows() > 0;
    }

    // ── Categories ────────────────────────────────────────────────────────────

    public function get_categories($game_type_id = null, $active_only = false)
    {
        $this->db
            ->select('gc.*, gt.name AS game_type_name, gl.label AS grade_label, qs.name AS subject_name')
            ->from('learn_game_categories gc')
            ->join('learn_game_types gt', 'gt.id = gc.game_type_id', 'left')
            ->join('quiz_grade_levels gl', 'gl.id = gc.grade_level_id', 'left')
            ->join('quiz_subjects qs', 'qs.id = gc.subject_id', 'left');

        if ($game_type_id) {
            $this->db->where('gc.game_type_id', $game_type_id);
        }
        if ($active_only) {
            $this->db->where('gc.is_active', 1);
        }
        return $this->db->order_by('gt.sort_order, gc.name')->get()->result_array();
    }

    public function get_category($id)
    {
        return $this->db
            ->select('gc.*, gt.name AS game_type_name')
            ->from('learn_game_categories gc')
            ->join('learn_game_types gt', 'gt.id = gc.game_type_id', 'left')
            ->where('gc.id', $id)
            ->get()
            ->row_array();
    }

    public function create_category($data)
    {
        $this->db->insert('learn_game_categories', [
            'game_type_id'   => (int) $data['game_type_id'],
            'grade_level_id' => !empty($data['grade_level_id']) ? (int) $data['grade_level_id'] : null,
            'subject_id'     => !empty($data['subject_id']) ? (int) $data['subject_id'] : null,
            'name'           => $data['name'],
            'description'    => $data['description'] ?? null,
            'is_active'      => (int) ($data['is_active'] ?? 1),
        ]);
        return $this->db->insert_id();
    }

    public function update_category($id, $data)
    {
        $this->db->where('id', $id)->update('learn_game_categories', [
            'game_type_id'   => (int) $data['game_type_id'],
            'grade_level_id' => !empty($data['grade_level_id']) ? (int) $data['grade_level_id'] : null,
            'subject_id'     => !empty($data['subject_id']) ? (int) $data['subject_id'] : null,
            'name'           => $data['name'],
            'description'    => $data['description'] ?? null,
            'is_active'      => (int) ($data['is_active'] ?? 1),
        ]);
        return $this->db->affected_rows() > 0;
    }

    public function delete_category($id)
    {
        $count = $this->db->where('category_id', $id)->count_all_results('learn_game_content_sets');
        if ($count > 0) return false;
        $this->db->where('id', $id)->delete('learn_game_categories');
        return true;
    }

    // ── Content Sets ──────────────────────────────────────────────────────────

    public function get_sets($category_id, $active_only = false)
    {
        $this->db
            ->select('cs.*, (SELECT COUNT(*) FROM learn_game_content_items ci WHERE ci.set_id = cs.id) AS item_count')
            ->from('learn_game_content_sets cs')
            ->where('cs.category_id', $category_id);

        if ($active_only) {
            $this->db->where('cs.is_active', 1);
        }
        return $this->db->order_by('cs.difficulty, cs.name')->get()->result_array();
    }

    public function get_set($id)
    {
        return $this->db
            ->select('cs.*, gc.name AS category_name, gc.game_type_id')
            ->from('learn_game_content_sets cs')
            ->join('learn_game_categories gc', 'gc.id = cs.category_id', 'left')
            ->where('cs.id', $id)
            ->get()
            ->row_array();
    }

    public function create_set($data, $user_id)
    {
        $this->db->insert('learn_game_content_sets', [
            'category_id' => (int) $data['category_id'],
            'name'        => $data['name'],
            'difficulty'  => $data['difficulty'] ?? 'easy',
            'is_active'   => (int) ($data['is_active'] ?? 1),
            'created_by'  => (int) $user_id,
        ]);
        return $this->db->insert_id();
    }

    public function update_set($id, $data)
    {
        $this->db->where('id', $id)->update('learn_game_content_sets', [
            'name'       => $data['name'],
            'difficulty' => $data['difficulty'] ?? 'easy',
            'is_active'  => (int) ($data['is_active'] ?? 1),
        ]);
        return $this->db->affected_rows() > 0;
    }

    public function delete_set($id)
    {
        $this->db->where('set_id', $id)->delete('learn_game_content_items');
        $this->db->where('id', $id)->delete('learn_game_content_sets');
        return true;
    }

    // ── Content Items ─────────────────────────────────────────────────────────

    public function get_items($set_id)
    {
        return $this->db
            ->where('set_id', $set_id)
            ->order_by('sort_order, id')
            ->get('learn_game_content_items')
            ->result_array();
    }

    public function get_item($id)
    {
        return $this->db->get_where('learn_game_content_items', ['id' => $id])->row_array();
    }

    public function create_item($data)
    {
        $this->db->insert('learn_game_content_items', [
            'set_id'         => (int) $data['set_id'],
            'term'           => $data['term'],
            'definition'     => $data['definition'],
            'term_image_url' => $data['term_image_url'] ?? null,
            'def_image_url'  => $data['def_image_url'] ?? null,
            'sort_order'     => (int) ($data['sort_order'] ?? 0),
        ]);
        return $this->db->insert_id();
    }

    public function update_item($id, $data)
    {
        $this->db->where('id', $id)->update('learn_game_content_items', [
            'term'           => $data['term'],
            'definition'     => $data['definition'],
            'term_image_url' => $data['term_image_url'] ?? null,
            'def_image_url'  => $data['def_image_url'] ?? null,
            'sort_order'     => (int) ($data['sort_order'] ?? 0),
        ]);
        return $this->db->affected_rows() > 0;
    }

    public function delete_item($id)
    {
        $this->db->where('id', $id)->delete('learn_game_content_items');
        return $this->db->affected_rows() > 0;
    }

    // ── Game Sessions (log) ───────────────────────────────────────────────────

    public function start_session($game_type_id, $user_id = null, $content_set_id = null, $config = [])
    {
        $this->db->insert('learn_game_sessions', [
            'game_type_id'   => (int) $game_type_id,
            'content_set_id' => $content_set_id ? (int) $content_set_id : null,
            'user_id'        => $user_id ? (int) $user_id : null,
            'config'         => $config ? json_encode($config) : null,
            'score'          => 0,
            'max_score'      => 0,
            'completed'      => 0,
            'played_at'      => date('Y-m-d H:i:s'),
        ]);
        return $this->db->insert_id();
    }

    public function finish_session($session_id, $score, $max_score, $duration_seconds)
    {
        $this->db->where('id', $session_id)->update('learn_game_sessions', [
            'score'            => (int) $score,
            'max_score'        => (int) $max_score,
            'completed'        => 1,
            'duration_seconds' => (int) $duration_seconds,
        ]);
        return $this->db->affected_rows() > 0;
    }

    public function get_user_game_history($user_id, $limit = 10)
    {
        return $this->db
            ->select('gs.*, gt.name AS game_name, gt.icon AS game_icon, gt.color AS game_color, cs.name AS set_name')
            ->from('learn_game_sessions gs')
            ->join('learn_game_types gt', 'gt.id = gs.game_type_id', 'left')
            ->join('learn_game_content_sets cs', 'cs.id = gs.content_set_id', 'left')
            ->where('gs.user_id', $user_id)
            ->where('gs.completed', 1)
            ->order_by('gs.played_at', 'DESC')
            ->limit($limit)
            ->get()
            ->result_array();
    }

    public function get_user_highscore($user_id, $game_type_id, $content_set_id = null)
    {
        $this->db
            ->select_max('score')
            ->where('user_id', $user_id)
            ->where('game_type_id', $game_type_id)
            ->where('completed', 1);

        if ($content_set_id) {
            $this->db->where('content_set_id', $content_set_id);
        }
        $row = $this->db->get('learn_game_sessions')->row_array();
        return (int) ($row['score'] ?? 0);
    }

    public function get_stats()
    {
        $game_types = $this->db->where('is_active', 1)->count_all_results('learn_game_types');
        $categories = $this->db->count_all('learn_game_categories');
        $sets       = $this->db->count_all('learn_game_content_sets');
        $sessions   = $this->db->where('completed', 1)->count_all_results('learn_game_sessions');
        return compact('game_types', 'categories', 'sets', 'sessions');
    }

    // ── Public API: get content for a game ───────────────────────────────────

    /**
     * Ambil item konten untuk satu set, di-shuffle, dibatasi sesuai config.
     */
    public function get_game_content($set_id, $max_pairs = 0)
    {
        $items = $this->get_items($set_id);
        shuffle($items);
        if ($max_pairs > 0) {
            $items = array_slice($items, 0, $max_pairs);
        }
        return $items;
    }
}
