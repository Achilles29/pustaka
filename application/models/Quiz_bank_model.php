<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Quiz_bank_model extends CI_Model
{
    public function stats()
    {
        $base = $this->db->where('deleted_at IS NULL', null, false);
        $total = (int) $this->db->count_all_results('quiz_questions');
        $mc    = (int) $this->db->where('deleted_at IS NULL', null, false)->where('type', 'multiple_choice')->count_all_results('quiz_questions');
        $essay = (int) $this->db->where('deleted_at IS NULL', null, false)->where('type', 'essay')->count_all_results('quiz_questions');
        $easy  = (int) $this->db->where('deleted_at IS NULL', null, false)->where('difficulty', 'easy')->count_all_results('quiz_questions');
        $med   = (int) $this->db->where('deleted_at IS NULL', null, false)->where('difficulty', 'medium')->count_all_results('quiz_questions');
        $hard  = (int) $this->db->where('deleted_at IS NULL', null, false)->where('difficulty', 'hard')->count_all_results('quiz_questions');
        return compact('total', 'mc', 'essay', 'easy', 'med', 'hard');
    }

    public function count_questions(array $filters = [])
    {
        $this->apply_filters($filters);
        return (int) $this->db->count_all_results('quiz_questions q');
    }

    public function get_questions(array $filters = [], $limit = 25, $offset = 0)
    {
        $this->apply_filters($filters);
        return $this->db
            ->select('q.*, s.name AS subject_name, s.color AS subject_color, s.icon AS subject_icon, g.name AS grade_name, g.education_level, (SELECT COUNT(*) FROM quiz_session_questions WHERE question_id = q.id) AS usage_count', false)
            ->join('quiz_subjects s', 's.id = q.subject_id', 'left')
            ->join('quiz_grade_levels g', 'g.id = q.grade_level_id', 'left')
            ->order_by('q.id', 'DESC')
            ->limit($limit, $offset)
            ->get('quiz_questions q')
            ->result_array();
    }

    public function bulk_delete(array $ids, $deleted_by)
    {
        if (empty($ids)) return 0;
        $safe_ids = array_map('intval', $ids);
        $this->db->where_in('id', $safe_ids)->where('deleted_at IS NULL', null, false)
                 ->update('quiz_questions', ['deleted_at' => date('Y-m-d H:i:s')]);
        return $this->db->affected_rows();
    }

    public function get_question($id)
    {
        $q = $this->db
            ->select('q.*, s.name AS subject_name, g.name AS grade_name')
            ->from('quiz_questions q')
            ->join('quiz_subjects s', 's.id = q.subject_id', 'left')
            ->join('quiz_grade_levels g', 'g.id = q.grade_level_id', 'left')
            ->where('q.id', (int) $id)
            ->where('q.deleted_at IS NULL', null, false)
            ->get()->row_array();

        if ($q) {
            $q['options'] = $this->get_options((int) $id);
            $q['tags']    = $this->get_tags((int) $id);
        }
        return $q;
    }

    public function get_options($question_id)
    {
        return $this->db
            ->where('question_id', (int) $question_id)
            ->order_by('option_index', 'ASC')
            ->get('quiz_question_options')
            ->result_array();
    }

    public function get_tags($question_id)
    {
        return $this->db
            ->select('t.id, t.name')
            ->from('quiz_tags t')
            ->join('quiz_question_tags qt', 'qt.tag_id = t.id')
            ->where('qt.question_id', (int) $question_id)
            ->get()->result_array();
    }

    public function create_question(array $data, array $options = [], array $tags = [])
    {
        $this->db->insert('quiz_questions', $this->sanitize_question($data));
        $id = (int) $this->db->insert_id();

        if ($data['type'] === 'multiple_choice') {
            $this->save_options($id, $options);
        }
        $this->sync_tags($id, $tags);

        $this->log_activity('quiz.question.create', 'quiz_questions', $id, null, ['subject_id' => $data['subject_id']]);
        return $id;
    }

    public function update_question($id, array $data, array $options = [], array $tags = [])
    {
        $this->db->update('quiz_questions', $this->sanitize_question($data), ['id' => (int) $id]);

        $this->db->delete('quiz_question_options', ['question_id' => (int) $id]);
        if ($data['type'] === 'multiple_choice') {
            $this->save_options((int) $id, $options);
        }
        $this->sync_tags((int) $id, $tags);

        $this->log_activity('quiz.question.update', 'quiz_questions', (int) $id);
        return $this->db->affected_rows();
    }

    public function delete_question($id)
    {
        $this->db->update('quiz_questions', ['deleted_at' => date('Y-m-d H:i:s')], ['id' => (int) $id]);
        $this->log_activity('quiz.question.delete', 'quiz_questions', (int) $id);
        return $this->db->affected_rows();
    }

    // ── Import ────────────────────────────────────────────────────────────────

    public function import_from_csv($filepath, $user_id, $context_type = 'bank', $context_id = null)
    {
        if (! file_exists($filepath)) {
            throw new RuntimeException('File tidak ditemukan.');
        }

        $handle = fopen($filepath, 'r');
        if (! $handle) {
            throw new RuntimeException('Gagal membuka file.');
        }

        // Skip BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);
            throw new RuntimeException('File CSV kosong atau header tidak valid.');
        }

        $header = array_map('trim', $header);

        // Expected columns (flexible order)
        $expected = ['question_text','type','difficulty','subject_code','grade_code',
                     'option_a','option_b','option_c','option_d','correct_answer',
                     'explanation','tags'];

        $col = [];
        foreach ($expected as $key) {
            $pos = array_search($key, $header);
            $col[$key] = $pos !== false ? $pos : null;
        }

        $batch_id  = $this->create_import_batch(basename($filepath), 'csv', $context_type, $context_id, $user_id);
        $subjects  = $this->subject_map();
        $grades    = $this->grade_map();

        $imported = 0; $skipped = 0; $errors = 0;
        $error_lines = [];
        $row_num = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $row_num++;
            $get = function ($key) use ($row, $col) {
                $pos = $col[$key];
                return $pos !== null && isset($row[$pos]) ? trim($row[$pos]) : '';
            };

            $q_text = $get('question_text');
            if ($q_text === '') {
                $skipped++;
                continue;
            }

            $type       = in_array($get('type'), ['multiple_choice','essay']) ? $get('type') : 'multiple_choice';
            $difficulty = in_array($get('difficulty'), ['easy','medium','hard']) ? $get('difficulty') : 'medium';
            $sub_code   = strtolower($get('subject_code'));
            $grade_code = strtolower($get('grade_code'));

            if (! isset($subjects[$sub_code])) {
                $errors++;
                $error_lines[] = "Baris {$row_num}: kode mapel '{$sub_code}' tidak ditemukan.";
                continue;
            }
            if (! isset($grades[$grade_code])) {
                $errors++;
                $error_lines[] = "Baris {$row_num}: kode jenjang '{$grade_code}' tidak ditemukan.";
                continue;
            }

            $options = [];
            $correct_idx = null;
            if ($type === 'multiple_choice') {
                $opt_keys = ['option_a','option_b','option_c','option_d'];
                foreach ($opt_keys as $idx => $key) {
                    $text = $get($key);
                    if ($text !== '') {
                        $options[] = ['option_index' => $idx, 'option_text' => $text, 'option_image' => null];
                    }
                }
                $correct_raw = strtoupper(trim($get('correct_answer')));
                $map = ['A' => 0, 'B' => 1, 'C' => 2, 'D' => 3, 'E' => 4];
                $correct_idx = $map[$correct_raw] ?? 0;
            }

            $q_data = [
                'subject_id'          => $subjects[$sub_code],
                'grade_level_id'      => $grades[$grade_code],
                'type'                => $type,
                'difficulty'          => $difficulty,
                'question_text'       => $q_text,
                'explanation'         => $get('explanation'),
                'correct_option_index'=> $correct_idx,
                'is_active'           => 1,
                'import_batch_id'     => $batch_id,
                'created_by'          => $user_id,
            ];

            $this->db->insert('quiz_questions', $q_data);
            $q_id = (int) $this->db->insert_id();

            if ($type === 'multiple_choice' && ! empty($options)) {
                foreach ($options as $opt) {
                    $this->db->insert('quiz_question_options', array_merge($opt, ['question_id' => $q_id]));
                }
            }

            $tags_raw = $get('tags');
            if ($tags_raw !== '') {
                $tag_names = array_filter(array_map('trim', explode(',', $tags_raw)));
                $this->sync_tags($q_id, $tag_names);
            }

            $imported++;
        }

        fclose($handle);

        $this->db->update('quiz_import_batches', [
            'total_rows' => $imported + $skipped + $errors,
            'imported'   => $imported,
            'skipped'    => $skipped,
            'errors'     => $errors,
            'error_log'  => empty($error_lines) ? null : implode("\n", $error_lines),
        ], ['id' => $batch_id]);

        return [
            'batch_id' => $batch_id,
            'imported' => $imported,
            'skipped'  => $skipped,
            'errors'   => $errors,
            'error_log'=> $error_lines,
        ];
    }

    public function get_import_batches($limit = 20)
    {
        return $this->db
            ->select('b.*, u.full_name AS imported_by_name')
            ->from('quiz_import_batches b')
            ->join('auth_user u', 'u.id = b.imported_by', 'left')
            ->where('b.context_type', 'bank')
            ->order_by('b.id', 'DESC')
            ->limit($limit)
            ->get()->result_array();
    }

    public function get_all_tags()
    {
        return $this->db->order_by('name', 'ASC')->get('quiz_tags')->result_array();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function apply_filters(array $filters)
    {
        $this->db->where('q.deleted_at IS NULL', null, false);

        if (! empty($filters['subject_id'])) {
            $this->db->where('q.subject_id', (int) $filters['subject_id']);
        }
        if (! empty($filters['grade_level_id'])) {
            $this->db->where('q.grade_level_id', (int) $filters['grade_level_id']);
        }
        if (! empty($filters['type'])) {
            $this->db->where('q.type', $filters['type']);
        }
        if (! empty($filters['difficulty'])) {
            $this->db->where('q.difficulty', $filters['difficulty']);
        }
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $this->db->where('q.is_active', (int) $filters['is_active']);
        }
        if (! empty($filters['q'])) {
            $this->db->like('q.question_text', $filters['q']);
        }
        if (! empty($filters['tag'])) {
            $this->db->where("q.id IN (SELECT question_id FROM quiz_question_tags qt JOIN quiz_tags t ON t.id=qt.tag_id WHERE t.name='{$this->db->escape_str($filters['tag'])}')", null, false);
        }
    }

    private function sanitize_question(array $data)
    {
        return [
            'subject_id'           => (int) $data['subject_id'],
            'grade_level_id'       => (int) $data['grade_level_id'],
            'type'                 => $data['type'],
            'difficulty'           => $data['difficulty'],
            'question_text'        => trim($data['question_text']),
            'explanation'          => trim($data['explanation'] ?? ''),
            'correct_option_index' => $data['type'] === 'multiple_choice' ? (int) $data['correct_option_index'] : null,
            'essay_rubric'         => $data['type'] === 'essay' ? trim($data['essay_rubric'] ?? '') : null,
            'score_weight'         => max(0.5, (float) ($data['score_weight'] ?? 1)),
            'is_active'            => (int) (bool) ($data['is_active'] ?? true),
            'created_by'           => (int) ($data['created_by'] ?? 0) ?: null,
        ];
    }

    private function save_options($question_id, array $options)
    {
        $letters = ['A', 'B', 'C', 'D', 'E'];
        foreach ($options as $idx => $text) {
            if ($text === '' || $text === null) {
                continue;
            }
            $this->db->insert('quiz_question_options', [
                'question_id'  => $question_id,
                'option_index' => $idx,
                'option_text'  => trim((string) $text),
                'option_image' => null,
            ]);
        }
    }

    private function sync_tags($question_id, array $tag_names)
    {
        $this->db->delete('quiz_question_tags', ['question_id' => (int) $question_id]);
        foreach ($tag_names as $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }
            $tag = $this->db->get_where('quiz_tags', ['name' => $name])->row_array();
            if (! $tag) {
                $this->db->insert('quiz_tags', ['name' => $name]);
                $tag_id = (int) $this->db->insert_id();
            } else {
                $tag_id = (int) $tag['id'];
            }
            $this->db->replace('quiz_question_tags', ['question_id' => (int) $question_id, 'tag_id' => $tag_id]);
        }
    }

    private function subject_map()
    {
        $rows = $this->db->get('quiz_subjects')->result_array();
        $map = [];
        foreach ($rows as $r) {
            $map[$r['code']] = (int) $r['id'];
        }
        return $map;
    }

    private function grade_map()
    {
        $rows = $this->db->get('quiz_grade_levels')->result_array();
        $map = [];
        foreach ($rows as $r) {
            $map[$r['code']] = (int) $r['id'];
        }
        return $map;
    }

    private function create_import_batch($filename, $format, $context_type, $context_id, $user_id)
    {
        $this->db->insert('quiz_import_batches', [
            'filename'     => $filename,
            'format'       => $format,
            'context_type' => $context_type,
            'context_id'   => $context_id,
            'imported_by'  => $user_id,
        ]);
        return (int) $this->db->insert_id();
    }

    private function log_activity($action, $entity_type, $entity_id, $user_id = null, array $detail = [])
    {
        $this->db->insert('quiz_activity_log', [
            'action'      => $action,
            'entity_type' => $entity_type,
            'entity_id'   => $entity_id,
            'user_id'     => $user_id,
            'ip_address'  => $this->input->ip_address(),
            'detail'      => empty($detail) ? null : json_encode($detail, JSON_UNESCAPED_UNICODE),
        ]);
    }

    // Draw random questions for a practice session
    public function draw_random($subject_id, $grade_level_id, $difficulty, $count)
    {
        $this->db->where('q.deleted_at IS NULL', null, false)->where('q.is_active', 1);
        if ($subject_id) {
            $this->db->where('q.subject_id', (int) $subject_id);
        }
        if ($grade_level_id) {
            $this->db->where('q.grade_level_id', (int) $grade_level_id);
        }
        if ($difficulty && $difficulty !== 'mixed') {
            $this->db->where('q.difficulty', $difficulty);
        }

        return $this->db
            ->select('q.id')
            ->from('quiz_questions q')
            ->order_by('RAND()')
            ->limit(max(1, (int) $count))
            ->get()->result_array();
    }
}
