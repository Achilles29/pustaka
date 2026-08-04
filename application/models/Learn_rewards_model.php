<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Learn_rewards_model — katalog hadiah "Tukar Poin → Token Baca".
 *
 * Poin belajar (learn_member_points) bisa ditukar dengan token baca
 * (reading_tokens). Penukaran = insert baris poin NEGATIF (pengurangan)
 * + terbitkan reading_tokens untuk member, dalam satu transaksi.
 */
class Learn_rewards_model extends CI_Model
{
    const DEDUCT_RULE = 'redeem.reading_token';

    // ── Katalog (CRUD admin) ──────────────────────────────────────────────────

    public function get_catalog($active_only = false)
    {
        if ($active_only) {
            $this->db->where('is_active', 1);
        }
        return $this->db
            ->order_by('sort_order', 'ASC')
            ->order_by('cost_points', 'ASC')
            ->get('learn_reward_catalog')
            ->result_array();
    }

    public function get_reward($id)
    {
        return $this->db->get_where('learn_reward_catalog', ['id' => (int) $id])->row_array();
    }

    public function code_exists($code, $exclude_id = null)
    {
        $this->db->where('code', $code);
        if ($exclude_id !== null) {
            $this->db->where('id !=', (int) $exclude_id);
        }
        return $this->db->count_all_results('learn_reward_catalog') > 0;
    }

    public function create_reward($data)
    {
        $this->db->insert('learn_reward_catalog', $this->_clean($data));
        return (int) $this->db->insert_id();
    }

    public function update_reward($id, $data)
    {
        $this->db->where('id', (int) $id)->update('learn_reward_catalog', $this->_clean($data));
        return $this->db->affected_rows() >= 0;
    }

    public function delete_reward($id)
    {
        $this->db->where('id', (int) $id)->delete('learn_reward_catalog');
        return $this->db->affected_rows() > 0;
    }

    public function toggle_reward($id)
    {
        $r = $this->get_reward($id);
        if (! $r) return false;
        $this->db->where('id', (int) $id)->update('learn_reward_catalog', [
            'is_active' => (int) $r['is_active'] === 1 ? 0 : 1,
        ]);
        return true;
    }

    private function _clean($data)
    {
        return [
            'code'                => trim((string) $data['code']),
            'name'                => trim((string) $data['name']),
            'description'         => trim((string) ($data['description'] ?? '')) ?: null,
            'icon'                => trim((string) ($data['icon'] ?? 'ti-gift')) ?: 'ti-gift',
            'color'               => trim((string) ($data['color'] ?? '#0ea5e9')) ?: '#0ea5e9',
            'cost_points'         => max(1, (int) ($data['cost_points'] ?? 100)),
            'reward_type'         => 'reading_token',
            'quota_amount'        => max(1, (int) ($data['quota_amount'] ?? 30)),
            'quota_unit'          => in_array($data['quota_unit'] ?? '', ['minutes','pages','books']) ? $data['quota_unit'] : 'minutes',
            'token_validity_days' => max(0, (int) ($data['token_validity_days'] ?? 30)),
            'stock'               => ($data['stock'] === '' || $data['stock'] === null) ? null : max(0, (int) $data['stock']),
            'per_user_limit'      => max(0, (int) ($data['per_user_limit'] ?? 0)),
            'sort_order'          => max(0, (int) ($data['sort_order'] ?? 100)),
            'is_active'           => (int) (bool) ($data['is_active'] ?? 1),
        ];
    }

    // ── Poin & Member ─────────────────────────────────────────────────────────

    public function total_points($user_id)
    {
        $row = $this->db
            ->select_sum('points')
            ->where('user_id', (int) $user_id)
            ->get('learn_member_points')
            ->row_array();
        return (int) ($row['points'] ?? 0);
    }

    public function resolve_member_id($user_id)
    {
        $row = $this->db->select('id')
            ->where('auth_user_id', (int) $user_id)
            ->get('members')
            ->row_array();
        return $row ? (int) $row['id'] : null;
    }

    public function user_redeem_count($user_id, $catalog_id)
    {
        return (int) $this->db
            ->where('user_id', (int) $user_id)
            ->where('catalog_id', (int) $catalog_id)
            ->where('status', 'completed')
            ->count_all_results('learn_reward_redemptions');
    }

    // ── Riwayat penukaran ─────────────────────────────────────────────────────

    public function get_user_redemptions($user_id, $limit = 20)
    {
        return $this->db
            ->where('user_id', (int) $user_id)
            ->order_by('created_at', 'DESC')
            ->limit($limit)
            ->get('learn_reward_redemptions')
            ->result_array();
    }

    public function get_all_redemptions($limit = 50, $offset = 0)
    {
        return $this->db
            ->select('r.*, u.full_name AS user_name, u.username, m.member_no, rt.status AS token_status, rt.quota_used, rt.expires_at')
            ->from('learn_reward_redemptions r')
            ->join('auth_user u', 'u.id = r.user_id', 'left')
            ->join('members m', 'm.id = r.member_id', 'left')
            ->join('reading_tokens rt', 'rt.id = r.reading_token_id', 'left')
            ->order_by('r.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->result_array();
    }

    public function count_redemptions()
    {
        return (int) $this->db->count_all('learn_reward_redemptions');
    }

    public function stats()
    {
        $rewards        = (int) $this->db->count_all('learn_reward_catalog');
        $active_rewards = (int) $this->db->where('is_active', 1)->count_all_results('learn_reward_catalog');
        $redemptions    = (int) $this->db->count_all('learn_reward_redemptions');
        $points_spent   = (int) ($this->db->select_sum('cost_points')->get('learn_reward_redemptions')->row_array()['cost_points'] ?? 0);
        return compact('rewards', 'active_rewards', 'redemptions', 'points_spent');
    }

    // ── Inti: proses penukaran ────────────────────────────────────────────────

    /**
     * Tukar poin user dengan hadiah (reading token).
     * @return array ['ok'=>bool, 'message'=>string, ...detail]
     */
    public function redeem($user_id, $catalog_id)
    {
        $user_id = (int) $user_id;
        $reward  = $this->get_reward($catalog_id);

        if (! $reward || ! (int) $reward['is_active']) {
            return ['ok' => false, 'message' => 'Hadiah tidak tersedia atau sudah dinonaktifkan.'];
        }
        if ($reward['stock'] !== null && (int) $reward['stock'] <= 0) {
            return ['ok' => false, 'message' => 'Maaf, stok hadiah ini sudah habis.'];
        }
        if ((int) $reward['per_user_limit'] > 0
            && $this->user_redeem_count($user_id, $catalog_id) >= (int) $reward['per_user_limit']) {
            return ['ok' => false, 'message' => 'Kamu sudah mencapai batas penukaran untuk hadiah ini.'];
        }

        $total = $this->total_points($user_id);
        $cost  = (int) $reward['cost_points'];
        if ($total < $cost) {
            return ['ok' => false, 'message' => "Poin belum cukup. Butuh {$cost} poin, kamu punya {$total}."];
        }

        $member_id = $this->resolve_member_id($user_id);
        if (! $member_id) {
            return ['ok' => false, 'message' => 'Akunmu belum tertaut ke data anggota perpustakaan. Hubungi petugas.'];
        }

        $rule = $this->db->get_where('learn_point_rules', ['action_code' => self::DEDUCT_RULE])->row_array();
        if (! $rule) {
            return ['ok' => false, 'message' => 'Aturan poin penukaran belum dikonfigurasi.'];
        }

        $now     = date('Y-m-d H:i:s');
        $expires = (int) $reward['token_validity_days'] > 0
            ? date('Y-m-d H:i:s', strtotime('+' . (int) $reward['token_validity_days'] . ' days'))
            : null;
        $token = 'RDM-' . strtoupper(substr(hash('sha256', uniqid('', true) . random_int(100000, 999999)), 0, 24));

        $this->db->trans_begin();
        try {
            // 1) Pengurangan poin (baris negatif)
            $this->db->insert('learn_member_points', [
                'user_id'        => $user_id,
                'rule_id'        => (int) $rule['id'],
                'points'         => -1 * $cost,
                'description'    => 'Tukar poin: ' . $reward['name'],
                'reference_type' => 'reward_redeem',
                'reference_id'   => (int) $catalog_id,
                'awarded_at'     => $now,
            ]);
            $points_entry_id = (int) $this->db->insert_id();

            // 2) Terbitkan token baca
            $this->db->insert('reading_tokens', [
                'member_id'        => $member_id,
                'reading_point_id' => null,
                'token'            => $token,
                'quota_total'      => (int) $reward['quota_amount'],
                'quota_used'       => 0,
                'quota_unit'       => $reward['quota_unit'],
                'issued_at'        => $now,
                'expires_at'       => $expires,
                'status'           => 'active',
                'issued_by'        => null,
            ]);
            $token_id = (int) $this->db->insert_id();

            // 3) Log penukaran
            $this->db->insert('learn_reward_redemptions', [
                'user_id'          => $user_id,
                'member_id'        => $member_id,
                'catalog_id'       => (int) $catalog_id,
                'reward_name'      => $reward['name'],
                'cost_points'      => $cost,
                'quota_amount'     => (int) $reward['quota_amount'],
                'quota_unit'       => $reward['quota_unit'],
                'reading_token_id' => $token_id,
                'points_entry_id'  => $points_entry_id,
                'token_code'       => $token,
                'status'           => 'completed',
                'created_at'       => $now,
            ]);
            $redeem_id = (int) $this->db->insert_id();

            // 4) Update stok & counter
            $this->db->where('id', (int) $catalog_id);
            if ($reward['stock'] !== null) {
                $this->db->set('stock', 'GREATEST(stock - 1, 0)', false);
            }
            $this->db->set('redeemed_count', 'redeemed_count + 1', false)
                     ->update('learn_reward_catalog');

            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                return ['ok' => false, 'message' => 'Transaksi gagal diproses. Coba lagi.'];
            }

            $this->db->trans_commit();
            return [
                'ok'               => true,
                'message'          => 'Berhasil! Token baca kamu sudah terbit.',
                'redeem_id'        => $redeem_id,
                'token'            => $token,
                'token_id'         => $token_id,
                'reward_name'      => $reward['name'],
                'quota_amount'     => (int) $reward['quota_amount'],
                'quota_unit'       => $reward['quota_unit'],
                'expires_at'       => $expires,
                'cost_points'      => $cost,
                'remaining_points' => $total - $cost,
            ];
        } catch (\Throwable $e) {
            $this->db->trans_rollback();
            return ['ok' => false, 'message' => 'Kesalahan sistem: ' . $e->getMessage()];
        }
    }
}
