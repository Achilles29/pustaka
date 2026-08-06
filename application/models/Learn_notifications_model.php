<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Learn_notifications_model — notifikasi in-app member.
 *
 * - notify()    : kirim ke satu user (mis. lencana baru).
 * - broadcast() : kirim ke semua member (set-based INSERT..SELECT), log ke learn_broadcasts.
 */
class Learn_notifications_model extends CI_Model
{
    // ── Kirim ─────────────────────────────────────────────────────────────────

    /** Kirim satu notifikasi ke satu user. */
    public function notify($user_id, array $data)
    {
        if (! $user_id) return 0;
        $this->db->insert('learn_notifications', [
            'user_id'        => (int) $user_id,
            'type'           => $data['type'] ?? 'system',
            'title'          => (string) $data['title'],
            'message'        => $data['message'] ?? null,
            'icon'           => $data['icon'] ?? 'ti-bell',
            'color'          => $data['color'] ?? '#3b82f6',
            'url'            => $data['url'] ?? null,
            'reference_type' => $data['reference_type'] ?? null,
            'reference_id'   => isset($data['reference_id']) ? (int) $data['reference_id'] : null,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
        return (int) $this->db->insert_id();
    }

    /**
     * Broadcast ke seluruh member (punya tautan auth_user_id).
     * Satu query set-based agar efisien. Mencatat ringkasan ke learn_broadcasts.
     * @return int jumlah penerima
     */
    public function broadcast(array $data, $sent_by = null)
    {
        $now  = date('Y-m-d H:i:s');
        $type  = $data['type']  ?? 'announcement';
        $title = (string) $data['title'];
        $msg   = $data['message'] ?? null;
        $icon  = $data['icon']  ?? 'ti-speakerphone';
        $color = $data['color'] ?? '#3b82f6';
        $url   = $data['url']   ?? null;
        $rtype = $data['reference_type'] ?? null;
        $rid   = isset($data['reference_id']) ? (int) $data['reference_id'] : null;

        $sql = 'INSERT INTO `learn_notifications`
                    (`user_id`, `type`, `title`, `message`, `icon`, `color`, `url`, `reference_type`, `reference_id`, `created_at`)
                SELECT DISTINCT m.`auth_user_id`, ?, ?, ?, ?, ?, ?, ?, ?, ?
                FROM `members` m
                WHERE m.`auth_user_id` IS NOT NULL';
        $this->db->query($sql, [$type, $title, $msg, $icon, $color, $url, $rtype, $rid, $now]);
        $count = (int) $this->db->affected_rows();

        $this->db->insert('learn_broadcasts', [
            'title'           => $title,
            'message'         => $msg,
            'type'            => $type,
            'icon'            => $icon,
            'color'           => $color,
            'url'             => $url,
            'recipient_count' => $count,
            'sent_by'         => $sent_by ? (int) $sent_by : null,
            'created_at'      => $now,
        ]);

        return $count;
    }

    // ── Baca (member) ─────────────────────────────────────────────────────────

    public function get_for_user($user_id, $limit = 30)
    {
        return $this->db
            ->where('user_id', (int) $user_id)
            ->order_by('created_at', 'DESC')
            ->limit($limit)
            ->get('learn_notifications')
            ->result_array();
    }

    public function unread_count($user_id)
    {
        if (! $user_id) return 0;
        return (int) $this->db
            ->where('user_id', (int) $user_id)
            ->where('is_read', 0)
            ->count_all_results('learn_notifications');
    }

    /** Tandai terbaca. $id null = semua notifikasi user. */
    public function mark_read($user_id, $id = null)
    {
        $this->db->where('user_id', (int) $user_id)->where('is_read', 0);
        if ($id !== null) {
            $this->db->where('id', (int) $id);
        }
        $this->db->update('learn_notifications', ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')]);
        return $this->db->affected_rows();
    }

    // ── Admin ─────────────────────────────────────────────────────────────────

    public function recent_broadcasts($limit = 30)
    {
        return $this->db
            ->select('b.*, u.full_name AS sender_name')
            ->from('learn_broadcasts b')
            ->join('auth_user u', 'u.id = b.sent_by', 'left')
            ->order_by('b.created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->result_array();
    }

    public function stats()
    {
        $total      = (int) $this->db->count_all('learn_notifications');
        $unread     = (int) $this->db->where('is_read', 0)->count_all_results('learn_notifications');
        $broadcasts = (int) $this->db->count_all('learn_broadcasts');
        $recipients = (int) ($this->db->select('COUNT(DISTINCT auth_user_id) AS n')->where('auth_user_id IS NOT NULL', null, false)->get('members')->row_array()['n'] ?? 0);
        return compact('total', 'unread', 'broadcasts', 'recipients');
    }
}
