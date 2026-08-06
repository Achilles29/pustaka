<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Learn_flashcards_model — deck & kartu flashcard belajar mandiri.
 *
 * Deck berisi kartu (front↔back). Member belajar mandiri, menandai
 * kartu "sudah dikuasai" (learning/known) di learn_flashcard_progress.
 */
class Learn_flashcards_model extends CI_Model
{
    // ── Decks ─────────────────────────────────────────────────────────────────

    public function get_decks($active_only = false)
    {
        $this->db
            ->select('d.*, s.name AS subject_name, s.color AS subject_color, g.name AS grade_name,
                      (SELECT COUNT(*) FROM learn_flashcard_cards c WHERE c.deck_id = d.id AND c.is_active = 1) AS card_count', false)
            ->from('learn_flashcard_decks d')
            ->join('quiz_subjects s', 's.id = d.subject_id', 'left')
            ->join('quiz_grade_levels g', 'g.id = d.grade_level_id', 'left');
        if ($active_only) {
            $this->db->where('d.is_active', 1);
        }
        return $this->db
            ->order_by('d.sort_order', 'ASC')
            ->order_by('d.name', 'ASC')
            ->get()
            ->result_array();
    }

    public function get_deck($id)
    {
        return $this->db->get_where('learn_flashcard_decks', ['id' => (int) $id])->row_array();
    }

    public function get_deck_by_code($code)
    {
        return $this->db->get_where('learn_flashcard_decks', ['code' => $code])->row_array();
    }

    public function code_exists($code, $exclude_id = null)
    {
        $this->db->where('code', $code);
        if ($exclude_id !== null) {
            $this->db->where('id !=', (int) $exclude_id);
        }
        return $this->db->count_all_results('learn_flashcard_decks') > 0;
    }

    public function create_deck($data)
    {
        $this->db->insert('learn_flashcard_decks', $this->_clean_deck($data));
        return (int) $this->db->insert_id();
    }

    public function update_deck($id, $data)
    {
        $this->db->where('id', (int) $id)->update('learn_flashcard_decks', $this->_clean_deck($data));
        return $this->db->affected_rows() >= 0;
    }

    public function delete_deck($id)
    {
        // Kartu ikut terhapus via FK ON DELETE CASCADE.
        $this->db->where('id', (int) $id)->delete('learn_flashcard_decks');
        return $this->db->affected_rows() > 0;
    }

    public function toggle_deck($id)
    {
        $d = $this->get_deck($id);
        if (! $d) return false;
        $this->db->where('id', (int) $id)->update('learn_flashcard_decks', [
            'is_active' => (int) $d['is_active'] === 1 ? 0 : 1,
        ]);
        return true;
    }

    private function _clean_deck($data)
    {
        return [
            'code'           => trim((string) $data['code']),
            'name'           => trim((string) $data['name']),
            'description'    => trim((string) ($data['description'] ?? '')) ?: null,
            'subject_id'     => ! empty($data['subject_id']) ? (int) $data['subject_id'] : null,
            'grade_level_id' => ! empty($data['grade_level_id']) ? (int) $data['grade_level_id'] : null,
            'icon'           => trim((string) ($data['icon'] ?? 'ti-cards')) ?: 'ti-cards',
            'color'          => trim((string) ($data['color'] ?? '#8b5cf6')) ?: '#8b5cf6',
            'sort_order'     => max(0, (int) ($data['sort_order'] ?? 100)),
            'is_active'      => (int) (bool) ($data['is_active'] ?? 1),
        ];
    }

    // ── Cards ─────────────────────────────────────────────────────────────────

    public function get_cards($deck_id, $active_only = false)
    {
        $this->db->where('deck_id', (int) $deck_id);
        if ($active_only) {
            $this->db->where('is_active', 1);
        }
        return $this->db
            ->order_by('sort_order', 'ASC')
            ->order_by('id', 'ASC')
            ->get('learn_flashcard_cards')
            ->result_array();
    }

    public function get_card($id)
    {
        return $this->db->get_where('learn_flashcard_cards', ['id' => (int) $id])->row_array();
    }

    public function create_card($deck_id, $data)
    {
        $payload = $this->_clean_card($data);
        $payload['deck_id'] = (int) $deck_id;
        $this->db->insert('learn_flashcard_cards', $payload);
        return (int) $this->db->insert_id();
    }

    public function update_card($id, $data)
    {
        $this->db->where('id', (int) $id)->update('learn_flashcard_cards', $this->_clean_card($data));
        return $this->db->affected_rows() >= 0;
    }

    public function delete_card($id)
    {
        $this->db->where('id', (int) $id)->delete('learn_flashcard_cards');
        return $this->db->affected_rows() > 0;
    }

    private function _clean_card($data)
    {
        return [
            'front'      => trim((string) $data['front']),
            'back'       => trim((string) $data['back']),
            'hint'       => trim((string) ($data['hint'] ?? '')) ?: null,
            'sort_order' => max(0, (int) ($data['sort_order'] ?? 0)),
            'is_active'  => (int) (bool) ($data['is_active'] ?? 1),
        ];
    }

    // ── Progress (member) ─────────────────────────────────────────────────────

    /** Kartu satu deck + status progress untuk user tertentu */
    public function get_deck_cards_with_progress($deck_id, $user_id = null)
    {
        $this->db
            ->select('c.id, c.front, c.back, c.hint, c.sort_order')
            ->from('learn_flashcard_cards c')
            ->where('c.deck_id', (int) $deck_id)
            ->where('c.is_active', 1);

        if ($user_id) {
            $this->db
                ->select('p.status AS progress_status')
                ->join(
                    'learn_flashcard_progress p',
                    'p.card_id = c.id AND p.user_id = ' . (int) $user_id,
                    'left'
                );
        } else {
            $this->db->select('NULL AS progress_status', false);
        }

        return $this->db
            ->order_by('c.sort_order', 'ASC')
            ->order_by('c.id', 'ASC')
            ->get()
            ->result_array();
    }

    /** Catat/ubah status satu kartu untuk user (upsert) */
    public function set_card_status($user_id, $card_id, $status)
    {
        $card = $this->get_card($card_id);
        if (! $card) return false;
        $status = in_array($status, ['learning', 'known']) ? $status : 'learning';
        $now = date('Y-m-d H:i:s');

        $existing = $this->db->get_where('learn_flashcard_progress', [
            'user_id' => (int) $user_id,
            'card_id' => (int) $card_id,
        ])->row_array();

        if ($existing) {
            $this->db->where('id', (int) $existing['id'])->update('learn_flashcard_progress', [
                'status'           => $status,
                'review_count'     => (int) $existing['review_count'] + 1,
                'last_reviewed_at' => $now,
            ]);
        } else {
            $this->db->insert('learn_flashcard_progress', [
                'user_id'          => (int) $user_id,
                'card_id'          => (int) $card_id,
                'deck_id'          => (int) $card['deck_id'],
                'status'           => $status,
                'review_count'     => 1,
                'last_reviewed_at' => $now,
            ]);
        }
        return true;
    }

    /** Ringkasan progress user untuk sebuah deck: total, dikuasai */
    public function deck_progress_summary($user_id, $deck_id)
    {
        $total = (int) $this->db
            ->where('deck_id', (int) $deck_id)->where('is_active', 1)
            ->count_all_results('learn_flashcard_cards');
        $known = (int) $this->db
            ->where('user_id', (int) $user_id)->where('deck_id', (int) $deck_id)->where('status', 'known')
            ->count_all_results('learn_flashcard_progress');
        return ['total' => $total, 'known' => $known];
    }

    /** Progress ringkas untuk banyak deck sekaligus (map deck_id => known count) */
    public function known_counts_for_user($user_id)
    {
        $rows = $this->db
            ->select('deck_id, COUNT(*) AS known')
            ->where('user_id', (int) $user_id)
            ->where('status', 'known')
            ->group_by('deck_id')
            ->get('learn_flashcard_progress')
            ->result_array();
        $map = [];
        foreach ($rows as $r) {
            $map[(int) $r['deck_id']] = (int) $r['known'];
        }
        return $map;
    }

    // ── Stats (admin) ─────────────────────────────────────────────────────────

    public function stats()
    {
        $decks   = (int) $this->db->count_all('learn_flashcard_decks');
        $active  = (int) $this->db->where('is_active', 1)->count_all_results('learn_flashcard_decks');
        $cards   = (int) $this->db->count_all('learn_flashcard_cards');
        $learners = (int) ($this->db->select('COUNT(DISTINCT user_id) AS n')->get('learn_flashcard_progress')->row_array()['n'] ?? 0);
        return compact('decks', 'active', 'cards', 'learners');
    }
}
