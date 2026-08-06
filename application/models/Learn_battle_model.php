<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Learn_battle_model — Mode Battle adu cepat 2 pemain (berbasis polling).
 *
 * Alur: host buat room (soal dibekukan) → guest join via kode → keduanya
 * menjawab soal yang sama; skor & progress disinkron lewat polling.
 * Penilaian & penentuan pemenang dilakukan di server.
 */
class Learn_battle_model extends CI_Model
{
    // ── Pool soal (CRUD admin) ────────────────────────────────────────────────

    public function get_questions($active_only = false)
    {
        if ($active_only) $this->db->where('is_active', 1);
        return $this->db->order_by('id', 'DESC')->get('learn_battle_questions')->result_array();
    }

    public function get_question($id)
    {
        return $this->db->get_where('learn_battle_questions', ['id' => (int) $id])->row_array();
    }

    public function count_active_questions()
    {
        return (int) $this->db->where('is_active', 1)->count_all_results('learn_battle_questions');
    }

    public function create_question($data)
    {
        $this->db->insert('learn_battle_questions', $this->_clean_q($data));
        return (int) $this->db->insert_id();
    }

    public function update_question($id, $data)
    {
        $this->db->where('id', (int) $id)->update('learn_battle_questions', $this->_clean_q($data));
        return $this->db->affected_rows() >= 0;
    }

    public function delete_question($id)
    {
        $this->db->where('id', (int) $id)->delete('learn_battle_questions');
        return $this->db->affected_rows() > 0;
    }

    private function _clean_q($data)
    {
        $correct = (int) ($data['correct_option'] ?? 0);
        return [
            'question'       => trim((string) $data['question']),
            'option_a'       => trim((string) $data['option_a']),
            'option_b'       => trim((string) $data['option_b']),
            'option_c'       => trim((string) ($data['option_c'] ?? '')) ?: null,
            'option_d'       => trim((string) ($data['option_d'] ?? '')) ?: null,
            'correct_option' => ($correct >= 0 && $correct <= 3) ? $correct : 0,
            'category'       => trim((string) ($data['category'] ?? '')) ?: null,
            'is_active'      => (int) (bool) ($data['is_active'] ?? 1),
        ];
    }

    // ── Room lifecycle ────────────────────────────────────────────────────────

    public function create_room($host_id, $host_name, $question_count = 5)
    {
        $count = max(3, min(15, (int) $question_count));

        // Bekukan soal acak dari pool aktif.
        $ids = $this->db->select('id')->where('is_active', 1)
            ->order_by('RAND()')->limit($count)
            ->get('learn_battle_questions')->result_array();
        $ids = array_map(function ($r) { return (int) $r['id']; }, $ids);

        if (count($ids) < 3) {
            return ['ok' => false, 'message' => 'Pool soal battle belum cukup (min. 3 soal aktif).'];
        }

        $code = $this->_unique_code();
        $this->db->insert('learn_battle_rooms', [
            'code'           => $code,
            'status'         => 'waiting',
            'question_count' => count($ids),
            'question_ids'   => json_encode(array_values($ids)),
            'host_user_id'   => (int) $host_id,
            'host_name'      => $host_name,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
        return ['ok' => true, 'code' => $code, 'id' => (int) $this->db->insert_id()];
    }

    public function join_room($code, $guest_id, $guest_name)
    {
        $room = $this->get_room_by_code($code);
        if (! $room) return ['ok' => false, 'message' => 'Kode room tidak ditemukan.'];
        if ($room['status'] !== 'waiting') return ['ok' => false, 'message' => 'Room sudah dimulai atau selesai.'];
        if ((int) $room['host_user_id'] === (int) $guest_id) return ['ok' => false, 'message' => 'Kamu tidak bisa join room sendiri.'];

        $this->db->where('id', $room['id'])->where('status', 'waiting')->update('learn_battle_rooms', [
            'guest_user_id' => (int) $guest_id,
            'guest_name'    => $guest_name,
            'status'        => 'playing',
            'started_at'    => date('Y-m-d H:i:s'),
        ]);
        if ($this->db->affected_rows() < 1) {
            return ['ok' => false, 'message' => 'Room baru saja terisi pemain lain.'];
        }
        return ['ok' => true, 'code' => $room['code'], 'id' => (int) $room['id']];
    }

    public function get_room($id)
    {
        return $this->db->get_where('learn_battle_rooms', ['id' => (int) $id])->row_array();
    }

    public function get_room_by_code($code)
    {
        return $this->db->get_where('learn_battle_rooms', ['code' => $code])->row_array();
    }

    /** Soal untuk dimainkan (tanpa kunci jawaban). */
    public function get_room_questions($room)
    {
        $ids = json_decode($room['question_ids'] ?? '[]', true);
        if (empty($ids)) return [];
        $rows = $this->db->where_in('id', $ids)->get('learn_battle_questions')->result_array();
        // Pertahankan urutan sesuai question_ids
        $map = [];
        foreach ($rows as $r) { $map[(int) $r['id']] = $r; }
        $ordered = [];
        foreach ($ids as $id) {
            if (! isset($map[$id])) continue;
            $q = $map[$id];
            $ordered[] = [
                'id'       => (int) $q['id'],
                'question' => $q['question'],
                'options'  => array_values(array_filter([
                    $q['option_a'], $q['option_b'], $q['option_c'], $q['option_d'],
                ], function ($v) { return $v !== null && $v !== ''; })),
            ];
        }
        return $ordered;
    }

    /** Peran user dalam room: 'host' | 'guest' | null */
    public function role_of($room, $user_id)
    {
        if ((int) $room['host_user_id'] === (int) $user_id) return 'host';
        if ((int) $room['guest_user_id'] === (int) $user_id) return 'guest';
        return null;
    }

    /**
     * Submit jawaban untuk soal pada posisi `index` (0-based).
     * Server yang menilai. Hanya menerima jawaban untuk posisi = progress saat ini.
     */
    public function submit_answer($room_id, $user_id, $index, $selected)
    {
        $room = $this->get_room($room_id);
        if (! $room || $room['status'] !== 'playing') {
            return ['ok' => false, 'message' => 'Room tidak aktif.'];
        }
        $role = $this->role_of($room, $user_id);
        if (! $role) return ['ok' => false, 'message' => 'Kamu bukan pemain di room ini.'];

        $progress_col = $role . '_progress';
        $score_col    = $role . '_score';
        $finished_col = $role . '_finished';

        $progress = (int) $room[$progress_col];
        $index    = (int) $index;

        // Abaikan jika tidak berurutan (anti-dobel / out-of-order).
        if ($index !== $progress) {
            return ['ok' => true, 'ignored' => true, 'progress' => $progress];
        }

        $ids = json_decode($room['question_ids'] ?? '[]', true);
        if (! isset($ids[$index])) {
            return ['ok' => false, 'message' => 'Soal tidak valid.'];
        }
        $q = $this->get_question((int) $ids[$index]);
        $is_correct = $q && ((int) $selected === (int) $q['correct_option']);

        $new_progress = $progress + 1;
        $update = [
            $progress_col => $new_progress,
        ];
        if ($is_correct) {
            $update[$score_col] = (int) $room[$score_col] + 1;
        }
        if ($new_progress >= (int) $room['question_count']) {
            $update[$finished_col] = 1;
        }
        $this->db->where('id', $room_id)->update('learn_battle_rooms', $update);

        // Cek apakah battle selesai (kedua pemain selesai).
        $this->_maybe_finish($room_id);

        return [
            'ok'          => true,
            'is_correct'  => (bool) $is_correct,
            'correct'     => $q ? (int) $q['correct_option'] : null,
            'progress'    => $new_progress,
            'finished'    => $new_progress >= (int) $room['question_count'],
        ];
    }

    /** State untuk polling. */
    public function state($room)
    {
        return [
            'status'         => $room['status'],
            'question_count' => (int) $room['question_count'],
            'host_name'      => $room['host_name'],
            'guest_name'     => $room['guest_name'],
            'host_score'     => (int) $room['host_score'],
            'guest_score'    => (int) $room['guest_score'],
            'host_progress'  => (int) $room['host_progress'],
            'guest_progress' => (int) $room['guest_progress'],
            'host_finished'  => (int) $room['host_finished'],
            'guest_finished' => (int) $room['guest_finished'],
            'winner_user_id' => $room['winner_user_id'] !== null ? (int) $room['winner_user_id'] : null,
        ];
    }

    // ── Internal ──────────────────────────────────────────────────────────────

    /** Jika kedua pemain selesai → tentukan pemenang, beri poin (sekali). */
    private function _maybe_finish($room_id)
    {
        $room = $this->get_room($room_id);
        if (! $room || $room['status'] !== 'playing') return;
        if (! ((int) $room['host_finished'] && (int) $room['guest_finished'])) return;

        $hs = (int) $room['host_score'];
        $gs = (int) $room['guest_score'];
        $winner = null;
        if ($hs > $gs) $winner = (int) $room['host_user_id'];
        elseif ($gs > $hs) $winner = (int) $room['guest_user_id'];
        // seri → winner null

        $this->db->where('id', $room_id)->where('status', 'playing')->update('learn_battle_rooms', [
            'status'         => 'finished',
            'winner_user_id' => $winner,
            'finished_at'    => date('Y-m-d H:i:s'),
        ]);

        // Beri poin sekali (guard: hanya jika update di atas mengubah status).
        if ($this->db->affected_rows() > 0) {
            $this->_award_points($room, $winner);
        }
    }

    private function _award_points($room, $winner)
    {
        if (! isset($this->Learn_points_model)) {
            $this->load->model('Learn_points_model');
        }
        foreach ([(int) $room['host_user_id'], (int) $room['guest_user_id']] as $uid) {
            if (! $uid) continue;
            $this->Learn_points_model->award_points($uid, 'battle.play', 'battle_room', (int) $room['id'], 'Ikut Mode Battle');
            if ($winner && $uid === $winner) {
                $this->Learn_points_model->award_points($uid, 'battle.win', 'battle_room', (int) $room['id'], 'Menang Mode Battle');
            }
        }
    }

    private function _unique_code()
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // tanpa karakter ambigu
        do {
            $code = '';
            for ($i = 0; $i < 5; $i++) { $code .= $chars[random_int(0, strlen($chars) - 1)]; }
        } while ($this->db->where('code', $code)->count_all_results('learn_battle_rooms') > 0);
        return $code;
    }

    // ── Stats (admin) ─────────────────────────────────────────────────────────

    public function stats()
    {
        $questions = (int) $this->db->count_all('learn_battle_questions');
        $active    = (int) $this->db->where('is_active', 1)->count_all_results('learn_battle_questions');
        $rooms     = (int) $this->db->count_all('learn_battle_rooms');
        $finished  = (int) $this->db->where('status', 'finished')->count_all_results('learn_battle_rooms');
        return compact('questions', 'active', 'rooms', 'finished');
    }

    public function recent_rooms($limit = 15)
    {
        return $this->db->order_by('id', 'DESC')->limit($limit)->get('learn_battle_rooms')->result_array();
    }
}
