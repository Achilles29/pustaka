<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Quiz_curriculum_model extends CI_Model
{
    public function is_enabled_scope($subject_id, $grade_level_id)
    {
        if (! $this->db->table_exists('quiz_curriculum_scopes')) {
            return true;
        }
        return (bool) $this->db
            ->where('subject_id', (int) $subject_id)
            ->where('grade_level_id', (int) $grade_level_id)
            ->where('is_active', 1)
            ->count_all_results('quiz_curriculum_scopes');
    }

    public function assert_enabled_scope(array $question)
    {
        $subject_id = (int) ($question['subject_id'] ?? 0);
        $grade_id = (int) ($question['grade_level_id'] ?? 0);
        if (! $subject_id || ! $grade_id || ! $this->is_enabled_scope($subject_id, $grade_id)) {
            throw new RuntimeException('Kombinasi jenjang dan mata pelajaran belum diizinkan dalam matriks Kurikulum Merdeka. Pilih kombinasi yang tersedia atau lengkapi master cakupan kurikulum terlebih dahulu.');
        }
    }
}
