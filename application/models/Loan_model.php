<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Operasi sirkulasi fisik; histori sinkron INLISLite dan transaksi aplikasi memakai tabel yang sama. */
class Loan_model extends CI_Model
{
    const SOURCE_SYSTEM = 'pustaka';

    public function get_settings()
    {
        $defaults = ['is_loan_enabled' => 1, 'default_loan_days' => 7, 'max_active_loans' => 3];
        if (! $this->db->table_exists('library_loan_settings')) return $defaults;
        $row = $this->db->where('id', 1)->get('library_loan_settings')->row_array();
        return array_merge($defaults, (array) $row);
    }

    public function save_settings(array $data, $user_id)
    {
        if (! $this->db->table_exists('library_loan_settings')) {
            throw new RuntimeException('Pengaturan peminjaman belum tersedia. Jalankan patch SQL sirkulasi terlebih dahulu.');
        }
        $payload = [
            'id' => 1,
            'is_loan_enabled' => ! empty($data['is_loan_enabled']) ? 1 : 0,
            'default_loan_days' => max(1, min(60, (int) ($data['default_loan_days'] ?? 7))),
            'max_active_loans' => max(1, min(20, (int) ($data['max_active_loans'] ?? 3))),
            'updated_by' => $user_id ? (int) $user_id : null,
        ];
        $this->db->replace('library_loan_settings', $payload);
        return $this->get_settings();
    }

    public function issue_manual_loan($member_lookup, $item_lookup, $due_date, $operator_id)
    {
        $settings = $this->get_settings();
        if (empty($settings['is_loan_enabled'])) throw new RuntimeException('Layanan peminjaman fisik sedang ditutup oleh pengaturan perpustakaan.');

        $member = $this->find_member($member_lookup);
        if (! $member) throw new RuntimeException('Member tidak ditemukan. Gunakan nomor anggota, NIK, atau nomor HP yang terdaftar.');
        $this->assert_member_can_borrow($member);

        $active_loans = $this->count_member_active_loans((int) $member['id']);
        if ($active_loans >= (int) $settings['max_active_loans']) {
            throw new RuntimeException('Member sudah mencapai batas ' . (int) $settings['max_active_loans'] . ' buku yang sedang dipinjam.');
        }

        $item = $this->find_loanable_item($item_lookup);
        if (! $item) throw new RuntimeException('Eksemplar tidak ditemukan, tidak boleh dipinjam, atau statusnya tidak tersedia. Pastikan barcode dan pengaturan eksemplar benar.');

        $loan_date = date('Y-m-d H:i:s');
        $due_at = $this->normalize_due_date($due_date, (int) $settings['default_loan_days']);
        $reference = 'APP-' . date('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(4)));

        $this->db->trans_start();
        $this->db->insert('loan_transactions', [
            'source_system' => self::SOURCE_SYSTEM,
            'source_id' => $reference,
            'member_id' => (int) $member['id'],
            'source_member_id' => (string) ($member['member_no'] ?? ''),
            'collection_count' => 1,
            'loan_count' => 1,
            'return_count' => 0,
            'late_count' => 0,
            'source_created_at' => $loan_date,
            'source_updated_at' => $loan_date,
        ]);
        $transaction_id = (int) $this->db->insert_id();

        // Guard race condition: hanya eksemplar yang masih tersedia yang boleh berubah menjadi dipinjam.
        $this->db->where('id', (int) $item['id'])->where('status', 'available')->update('book_items', [
            'status' => 'loaned',
            'updated_at' => $loan_date,
        ]);
        if ($this->db->affected_rows() !== 1) {
            $this->db->trans_rollback();
            throw new RuntimeException('Eksemplar baru saja dipinjam oleh transaksi lain. Pindai ulang atau pilih eksemplar lain.');
        }

        $this->db->insert('loan_transaction_items', [
            'source_system' => self::SOURCE_SYSTEM,
            'source_id' => $reference . '-01',
            'loan_transaction_id' => $transaction_id,
            'source_loan_id' => $reference,
            'member_id' => (int) $member['id'],
            'book_item_id' => (int) $item['id'],
            'source_member_id' => (string) ($member['member_no'] ?? ''),
            'source_collection_id' => (string) ($item['source_id'] ?: $item['id']),
            'loan_date' => $loan_date,
            'due_date' => $due_at,
            'loan_status' => 'Loan',
            'source_created_at' => $loan_date,
            'source_updated_at' => $loan_date,
        ]);
        $loan_item_id = (int) $this->db->insert_id();
        $this->db->trans_complete();
        if (! $this->db->trans_status()) throw new RuntimeException('Transaksi peminjaman gagal disimpan.');

        return ['loan_item_id' => $loan_item_id, 'reference' => $reference, 'member' => $member, 'item' => $item, 'due_date' => $due_at];
    }

    /**
     * Mengubah reservasi yang sudah disiapkan menjadi peminjaman nyata saat
     * eksemplar diserahkan. Request tetap menjadi jejak antrean, sedangkan
     * loan_transaction_items menjadi satu-satunya sumber status sirkulasi.
     */
    public function issue_book_request($request_id, $due_date, $operator_id)
    {
        $settings = $this->get_settings();
        if (empty($settings['is_loan_enabled'])) {
            throw new RuntimeException('Layanan peminjaman fisik sedang ditutup oleh pengaturan perpustakaan.');
        }

        $this->db->trans_begin();
        try {
            $request = $this->db->query('SELECT * FROM book_requests WHERE id = ? FOR UPDATE', [(int) $request_id])->row_array();
            if (! $request) {
                throw new RuntimeException('Request buku tidak ditemukan.');
            }
            if (($request['status'] ?? '') !== 'approved') {
                throw new RuntimeException('Hanya request berstatus Disiapkan yang dapat dicatat sebagai peminjaman.');
            }
            if (! empty($request['loan_transaction_item_id'])) {
                throw new RuntimeException('Request ini sudah memiliki transaksi peminjaman.');
            }
            if (empty($request['member_id'])) {
                throw new RuntimeException('Request ini tidak terhubung ke akun member. Gunakan Catat Pinjam Manual setelah identitas member diverifikasi.');
            }

            $member = $this->db->from('members')->where('id', (int) $request['member_id'])->where('deleted_at IS NULL', null, false)->limit(1)->get()->row_array();
            if (! $member) {
                throw new RuntimeException('Member pada request tidak ditemukan atau sudah tidak aktif.');
            }
            $this->assert_member_can_borrow($member);
            if ($this->count_member_active_loans((int) $member['id']) >= (int) $settings['max_active_loans']) {
                throw new RuntimeException('Member sudah mencapai batas ' . (int) $settings['max_active_loans'] . ' buku yang sedang dipinjam.');
            }

			$item = $this->db->query('SELECT * FROM book_items WHERE id = ? AND deleted_at IS NULL FOR UPDATE', [(int) $request['book_item_id']])->row_array();
			if (! $item || (int) ($item['is_loanable'] ?? 0) !== 1 || ($item['status'] ?? '') !== 'reserved') {
                throw new RuntimeException('Eksemplar yang direservasi tidak lagi tersedia. Pilih eksemplar lain atau batalkan request ini.');
            }

            $loan_date = date('Y-m-d H:i:s');
            $due_at = $this->normalize_due_date($due_date, (int) $settings['default_loan_days']);
            $reference = 'REQ-' . (string) $request['request_code'];
            $this->db->insert('loan_transactions', [
                'source_system' => self::SOURCE_SYSTEM,
                'source_id' => $reference,
                'member_id' => (int) $member['id'],
                'source_member_id' => (string) ($member['member_no'] ?? ''),
                'collection_count' => 1,
                'loan_count' => 1,
                'return_count' => 0,
                'late_count' => 0,
                'source_created_at' => $loan_date,
                'source_updated_at' => $loan_date,
            ]);
            $transaction_id = (int) $this->db->insert_id();

			$this->db->where('id', (int) $item['id'])->where('status', 'reserved')->update('book_items', [
                'status' => 'loaned',
                'updated_at' => $loan_date,
            ]);
            if ($this->db->affected_rows() !== 1) {
                throw new RuntimeException('Eksemplar baru saja dipinjam oleh transaksi lain.');
            }

            $this->db->insert('loan_transaction_items', [
                'source_system' => self::SOURCE_SYSTEM,
                'source_id' => $reference . '-01',
                'loan_transaction_id' => $transaction_id,
                'source_loan_id' => $reference,
                'member_id' => (int) $member['id'],
                'book_item_id' => (int) $item['id'],
                'source_member_id' => (string) ($member['member_no'] ?? ''),
                'source_collection_id' => (string) ($item['source_id'] ?: $item['id']),
                'loan_date' => $loan_date,
                'due_date' => $due_at,
                'loan_status' => 'Loan',
                'source_created_at' => $loan_date,
                'source_updated_at' => $loan_date,
            ]);
            $loan_item_id = (int) $this->db->insert_id();
            $this->db->where('id', (int) $request['id'])->where('status', 'approved')->update('book_requests', [
                'status' => 'fulfilled',
                'loan_transaction_item_id' => $loan_item_id,
                'processed_by' => (int) $operator_id ?: null,
                'processed_at' => $loan_date,
            ]);
            if ($this->db->affected_rows() !== 1) {
                throw new RuntimeException('Status request berubah saat transaksi diproses.');
            }

            $this->db->trans_commit();
            return ['loan_item_id' => $loan_item_id, 'reference' => $reference, 'member' => $member, 'item' => $item, 'due_date' => $due_at, 'request_code' => $request['request_code']];
        } catch (Throwable $e) {
            $this->db->trans_rollback();
            throw $e;
        }
    }

    /**
     * Pengembalian aplikasi menulis status transaksi utama. Pengembalian legacy
     * disimpan sebagai override lokal supaya riwayat mentah INLISLite tetap
     * terpelihara dan refresh sinkronisasi tidak membuka ulang eksemplar.
     */
    public function return_loan($loan_item_id, $operator_id = null, $note = null)
    {
        $loan = $this->get_loan_item((int) $loan_item_id);
        if (! $loan) throw new RuntimeException('Data peminjaman tidak ditemukan.');
        if (! $this->is_active_loan($loan)) throw new RuntimeException('Buku ini sudah dikembalikan atau transaksi sudah ditutup.');

        $returned_at = date('Y-m-d H:i:s');
        $late_days = ! empty($loan['due_date']) ? max(0, (int) floor((strtotime(date('Y-m-d')) - strtotime(substr($loan['due_date'], 0, 10))) / 86400)) : 0;
        $this->db->trans_start();
        $is_local_transaction = ($loan['source_system'] ?? '') === self::SOURCE_SYSTEM;
        if ($is_local_transaction) {
            $this->db->where('id', (int) $loan_item_id)->update('loan_transaction_items', [
                'actual_return_at' => $returned_at,
                'late_days' => $late_days,
                'loan_status' => 'Return',
                'source_updated_at' => $returned_at,
            ]);
        } else {
            $this->db->where('id', (int) $loan_item_id)->where('local_return_at IS NULL', null, false)->update('loan_transaction_items', [
                'local_return_at' => $returned_at,
                'local_returned_by' => (int) $operator_id ?: null,
                'local_return_note' => trim((string) $note) ?: null,
            ]);
            if ($this->db->affected_rows() !== 1) {
                $this->db->trans_rollback();
                throw new RuntimeException('Pengembalian lokal sudah dicatat oleh petugas lain.');
            }
        }
        $this->db->where('id', (int) $loan['book_item_id'])->where('status', 'loaned')->update('book_items', [
            'status' => 'available',
            'updated_at' => $returned_at,
        ]);
        if ($is_local_transaction) {
            $this->db->where('id', (int) $loan['loan_transaction_id'])->update('loan_transactions', [
                'return_count' => 1,
                'late_count' => $late_days > 0 ? 1 : 0,
                'source_updated_at' => $returned_at,
            ]);
        }
        $this->db->trans_complete();
        if (! $this->db->trans_status()) throw new RuntimeException('Pengembalian buku gagal disimpan.');
        // Item yang sama dapat memiliki riwayat lebih dari satu. Selaraskan
        // ulang terhadap seluruh transaksi efektif agar tidak salah dibuka.
        $availability = $this->reconcile_item_availability();
        return [
            'late_days' => $late_days,
            'title' => $loan['title'] ?? 'Buku',
            'is_legacy' => ! $is_local_transaction,
            'availability' => $availability,
        ];
    }

    /** Menjadikan status eksemplar konsisten dengan transaksi efektif aktif. */
    public function reconcile_item_availability()
    {
        if (! $this->db->table_exists('loan_transaction_items') || ! $this->db->table_exists('book_items')) return ['loaned' => 0, 'available' => 0];
        $active = "li.actual_return_at IS NULL AND li.local_return_at IS NULL AND UPPER(COALESCE(li.loan_status, '')) = 'LOAN'";
        $this->db->query("UPDATE book_items bi SET bi.status = 'loaned', bi.updated_at = NOW() WHERE bi.deleted_at IS NULL AND bi.is_loanable = 1 AND bi.status IN ('available','loaned') AND EXISTS (SELECT 1 FROM loan_transaction_items li WHERE li.book_item_id = bi.id AND {$active})");
        $loaned = $this->db->affected_rows();
        $this->db->query("UPDATE book_items bi SET bi.status = 'available', bi.updated_at = NOW() WHERE bi.deleted_at IS NULL AND bi.is_loanable = 1 AND bi.status = 'loaned' AND NOT EXISTS (SELECT 1 FROM loan_transaction_items li WHERE li.book_item_id = bi.id AND {$active})");
        return ['loaned' => $loaned, 'available' => $this->db->affected_rows()];
    }

    public function get_loans(array $filters = [], $limit = 25, $offset = 0)
    {
        $this->apply_loan_filters($filters);
        return $this->db->select($this->loan_select())
            ->order_by('li.loan_date', 'DESC')->order_by('li.id', 'DESC')
            ->limit(max(1, min(100, (int) $limit)), max(0, (int) $offset))->get()->result_array();
    }

    public function count_loans(array $filters = [])
    {
        $this->apply_loan_filters($filters);
        return (int) $this->db->count_all_results();
    }

    public function stats()
    {
        $today = date('Y-m-d');
        $base = "actual_return_at IS NULL AND local_return_at IS NULL AND UPPER(COALESCE(loan_status, '')) = 'LOAN'";
        $rows = $this->db->query("SELECT
            SUM(CASE WHEN {$base} THEN 1 ELSE 0 END) AS active,
            SUM(CASE WHEN {$base} AND due_date IS NOT NULL AND due_date < ? THEN 1 ELSE 0 END) AS overdue,
            SUM(CASE WHEN DATE(COALESCE(actual_return_at, local_return_at)) = ? THEN 1 ELSE 0 END) AS returned_today,
            COUNT(*) AS total
            FROM loan_transaction_items", [$today, $today])->row_array();
        return array_map('intval', (array) $rows);
    }

    public function get_member_loans($member_id, $limit = 10)
    {
        $this->db->from('loan_transaction_items li')->join('members m', 'm.id = li.member_id', 'left')
            ->join('book_items bi', 'bi.id = li.book_item_id', 'left')->join('books b', 'b.id = bi.book_id', 'left')
            ->where('li.member_id', (int) $member_id);
        return $this->db->select($this->loan_select())->order_by('li.loan_date', 'DESC')->order_by('li.id', 'DESC')
            ->limit(max(1, min(50, (int) $limit)))->get()->result_array();
    }

    public function get_member_summary($member_id)
    {
        $today = date('Y-m-d');
        $base = "actual_return_at IS NULL AND local_return_at IS NULL AND UPPER(COALESCE(loan_status, '')) = 'LOAN'";
        $row = $this->db->query("SELECT
            SUM(CASE WHEN {$base} THEN 1 ELSE 0 END) AS active,
            SUM(CASE WHEN {$base} AND due_date IS NOT NULL AND due_date < ? THEN 1 ELSE 0 END) AS overdue,
            MIN(CASE WHEN {$base} THEN due_date END) AS next_due_date,
            COUNT(*) AS total
            FROM loan_transaction_items WHERE member_id = ?", [$today, (int) $member_id])->row_array();
        return ['active' => (int) ($row['active'] ?? 0), 'overdue' => (int) ($row['overdue'] ?? 0), 'next_due_date' => $row['next_due_date'] ?? null, 'total' => (int) ($row['total'] ?? 0)];
    }

    private function find_member($lookup)
    {
        $lookup = trim((string) $lookup);
        if ($lookup === '') return null;
        return $this->db->from('members')->where('deleted_at IS NULL', null, false)->group_start()
            ->where('member_no', $lookup)->or_where('identity_number', $lookup)->or_where('phone', $lookup)->group_end()->limit(1)->get()->row_array();
    }

    private function find_loanable_item($lookup)
    {
        $lookup = trim((string) $lookup);
        if ($lookup === '') return null;
        return $this->db->from('book_items')->where('deleted_at IS NULL', null, false)->where('status', 'available')->where('is_loanable', 1)
            ->group_start()->where('barcode', $lookup)->or_where('inventory_number', $lookup)->or_where('item_code', $lookup)->group_end()->limit(1)->get()->row_array();
    }

    private function assert_member_can_borrow(array $member)
    {
        if (($member['card_status'] ?? 'active') === 'blocked') throw new RuntimeException('Kartu member sedang diblokir.');
        if (! empty($member['expired_at']) && strtotime($member['expired_at']) < strtotime(date('Y-m-d'))) throw new RuntimeException('Masa berlaku membership sudah berakhir.');
    }

    private function count_member_active_loans($member_id)
    {
        return (int) $this->db->where('member_id', (int) $member_id)->where('actual_return_at IS NULL', null, false)->where('local_return_at IS NULL', null, false)
            ->where("UPPER(COALESCE(loan_status, '')) = 'LOAN'", null, false)->count_all_results('loan_transaction_items');
    }

    private function get_loan_item($id)
    {
        return $this->db->select($this->loan_select())->from('loan_transaction_items li')->join('members m', 'm.id = li.member_id', 'left')
            ->join('book_items bi', 'bi.id = li.book_item_id', 'left')->join('books b', 'b.id = bi.book_id', 'left')
            ->where('li.id', (int) $id)->limit(1)->get()->row_array();
    }

    private function is_active_loan(array $loan)
    {
        return empty($loan['actual_return_at']) && empty($loan['local_return_at']) && strtoupper((string) ($loan['loan_status'] ?? '')) === 'LOAN';
    }

    private function normalize_due_date($due_date, $default_days)
    {
        $date = trim((string) $due_date);
        $timestamp = $date !== '' ? strtotime($date) : strtotime('+' . max(1, $default_days) . ' days');
        if (! $timestamp || date('Y-m-d', $timestamp) < date('Y-m-d')) throw new RuntimeException('Tanggal jatuh tempo harus hari ini atau setelahnya.');
        return date('Y-m-d 23:59:59', $timestamp);
    }

    private function apply_loan_filters(array $filters)
    {
        $this->db->from('loan_transaction_items li')->join('members m', 'm.id = li.member_id', 'left')
            ->join('book_items bi', 'bi.id = li.book_item_id', 'left')->join('books b', 'b.id = bi.book_id', 'left');
        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') $this->db->group_start()->like('m.full_name', $q)->or_like('m.member_no', $q)->or_like('b.title', $q)->or_like('bi.barcode', $q)->or_like('li.source_loan_id', $q)->group_end();
        $status = trim((string) ($filters['status'] ?? ''));
        if ($status === 'active') $this->db->where('li.actual_return_at IS NULL', null, false)->where('li.local_return_at IS NULL', null, false)->where("UPPER(COALESCE(li.loan_status, '')) = 'LOAN'", null, false);
        if ($status === 'overdue') $this->db->where('li.actual_return_at IS NULL', null, false)->where('li.local_return_at IS NULL', null, false)->where("UPPER(COALESCE(li.loan_status, '')) = 'LOAN'", null, false)->where('li.due_date <', date('Y-m-d 00:00:00'));
        if ($status === 'returned') $this->db->group_start()->where('li.actual_return_at IS NOT NULL', null, false)->or_where('li.local_return_at IS NOT NULL', null, false)->or_where("UPPER(COALESCE(li.loan_status, '')) = 'RETURN'", null, false)->group_end();
        if (! empty($filters['source'])) $this->db->where('li.source_system', (string) $filters['source']);
        if (! empty($filters['date_from'])) $this->db->where('li.loan_date >=', $filters['date_from'] . ' 00:00:00');
        if (! empty($filters['date_to'])) $this->db->where('li.loan_date <=', $filters['date_to'] . ' 23:59:59');
    }

    private function loan_select()
    {
        return "li.*, m.full_name AS member_name, m.member_no, b.title, bi.barcode, bi.inventory_number,
            CASE WHEN li.actual_return_at IS NOT NULL OR li.local_return_at IS NOT NULL OR UPPER(COALESCE(li.loan_status, '')) = 'RETURN' THEN 'returned'
                 WHEN li.local_return_at IS NULL AND li.due_date IS NOT NULL AND li.due_date < CURDATE() THEN 'overdue'
                 WHEN UPPER(COALESCE(li.loan_status, '')) = 'LOAN' THEN 'active' ELSE 'history' END AS circulation_status,
            CASE WHEN li.actual_return_at IS NULL AND li.local_return_at IS NULL AND UPPER(COALESCE(li.loan_status, '')) = 'LOAN' AND li.due_date IS NOT NULL
                 THEN DATEDIFF(li.due_date, CURDATE()) ELSE NULL END AS days_remaining";
    }
}
