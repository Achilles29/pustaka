<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Quiz_config_model extends CI_Model
{
    // ── Grade Levels ──────────────────────────────────────────────────────────

    public function get_grade_levels($active_only = false)
    {
        if ($active_only) {
            $this->db->where('is_active', 1);
        }
        return $this->db->order_by('sort_order', 'ASC')->get('quiz_grade_levels')->result_array();
    }

    public function get_grade_level($id)
    {
        return $this->db->get_where('quiz_grade_levels', ['id' => (int) $id])->row_array();
    }

    public function create_grade(array $data)
    {
        $this->db->insert('quiz_grade_levels', [
            'code'            => trim($data['code']),
            'name'            => trim($data['name']),
            'education_level' => $data['education_level'],
            'grade_number'    => isset($data['grade_number']) && $data['grade_number'] !== '' ? (int) $data['grade_number'] : null,
            'sort_order'      => (int) ($data['sort_order'] ?? 0),
            'is_active'       => (int) (bool) ($data['is_active'] ?? true),
        ]);
        return (int) $this->db->insert_id();
    }

    public function update_grade($id, array $data)
    {
        $this->db->update('quiz_grade_levels', [
            'code'            => trim($data['code']),
            'name'            => trim($data['name']),
            'education_level' => $data['education_level'],
            'grade_number'    => isset($data['grade_number']) && $data['grade_number'] !== '' ? (int) $data['grade_number'] : null,
            'sort_order'      => (int) ($data['sort_order'] ?? 0),
            'is_active'       => (int) (bool) ($data['is_active'] ?? true),
        ], ['id' => (int) $id]);
        return $this->db->affected_rows();
    }

    public function delete_grade($id)
    {
        $used = (int) $this->db->where('grade_level_id', (int) $id)->count_all_results('quiz_questions');
        if ($used > 0) {
            throw new RuntimeException('Jenjang kelas sudah digunakan oleh ' . $used . ' soal, tidak bisa dihapus.');
        }
        $this->db->delete('quiz_grade_levels', ['id' => (int) $id]);
        return $this->db->affected_rows();
    }

    // ── Subjects ─────────────────────────────────────────────────────────────

    public function get_subjects($active_only = false)
    {
        if ($active_only) {
            $this->db->where('is_active', 1);
        }
        return $this->db->order_by('sort_order', 'ASC')->get('quiz_subjects')->result_array();
    }

    public function get_subject($id)
    {
        return $this->db->get_where('quiz_subjects', ['id' => (int) $id])->row_array();
    }

    public function create_subject(array $data)
    {
        $this->db->insert('quiz_subjects', [
            'code'       => trim($data['code']),
            'name'       => trim($data['name']),
            'icon'       => trim($data['icon'] ?? 'ti ti-book'),
            'color'      => trim($data['color'] ?? '#4a90d9'),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active'  => (int) (bool) ($data['is_active'] ?? true),
        ]);
        return (int) $this->db->insert_id();
    }

    public function update_subject($id, array $data)
    {
        $this->db->update('quiz_subjects', [
            'code'       => trim($data['code']),
            'name'       => trim($data['name']),
            'icon'       => trim($data['icon'] ?? 'ti ti-book'),
            'color'      => trim($data['color'] ?? '#4a90d9'),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active'  => (int) (bool) ($data['is_active'] ?? true),
        ], ['id' => (int) $id]);
        return $this->db->affected_rows();
    }

    public function delete_subject($id)
    {
        $used = (int) $this->db->where('subject_id', (int) $id)->count_all_results('quiz_questions');
        if ($used > 0) {
            throw new RuntimeException('Mata pelajaran sudah digunakan oleh ' . $used . ' soal, tidak bisa dihapus.');
        }
        $this->db->delete('quiz_subjects', ['id' => (int) $id]);
        return $this->db->affected_rows();
    }

    // ── Stats ─────────────────────────────────────────────────────────────────

    public function stats()
    {
        return [
            'grades'   => (int) $this->db->count_all('quiz_grade_levels'),
            'subjects' => (int) $this->db->count_all('quiz_subjects'),
            'questions'=> (int) $this->db->where('deleted_at IS NULL', null, false)->count_all_results('quiz_questions'),
            'sessions' => (int) $this->db->where('deleted_at IS NULL', null, false)->count_all_results('quiz_sessions'),
        ];
    }
}
