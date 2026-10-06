<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Quiz_bank_model extends CI_Model
{
    public function stats()
    {
        $base = $this->db->where('deleted_at IS NULL', null, false)->where('is_session_only', 0);
        $total = (int) $this->db->count_all_results('quiz_questions');
        $mc    = (int) $this->db->where('deleted_at IS NULL', null, false)->where('is_session_only', 0)->where('type', 'multiple_choice')->count_all_results('quiz_questions');
        $essay = (int) $this->db->where('deleted_at IS NULL', null, false)->where('is_session_only', 0)->where('type', 'essay')->count_all_results('quiz_questions');
        $easy  = (int) $this->db->where('deleted_at IS NULL', null, false)->where('is_session_only', 0)->where('difficulty', 'easy')->count_all_results('quiz_questions');
        $med   = (int) $this->db->where('deleted_at IS NULL', null, false)->where('is_session_only', 0)->where('difficulty', 'medium')->count_all_results('quiz_questions');
        $hard  = (int) $this->db->where('deleted_at IS NULL', null, false)->where('is_session_only', 0)->where('difficulty', 'hard')->count_all_results('quiz_questions');
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
        $this->assert_curriculum_scope($data);
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
        $this->assert_curriculum_scope($data);
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
            if (! $this->scope_is_enabled($subjects[$sub_code], $grades[$grade_code])) {
                $errors++;
                $error_lines[] = "Baris {$row_num}: kombinasi mapel dan jenjang belum diizinkan dalam matriks kurikulum.";
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
                'is_active'           => 0,
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

    // ── Import multi-format: analisa (preview) lalu commit ─────────────────────

    /**
     * Parse + validasi file import menjadi daftar soal ternormalisasi untuk di-review.
     * $format: csv|xlsx|txt. $default_subject_id/$default_grade_id dipakai untuk TXT
     * (dan sebagai fallback bila kode mapel/jenjang di baris tidak ditemukan).
     * @return array ['questions'=>[...], 'summary'=>['total','valid','ready','warnings','invalid']]
     */
    public function analyze_import($filepath, $format, $default_subject_id = null, $default_grade_id = null)
    {
        if (! file_exists($filepath)) {
            throw new RuntimeException('File tidak ditemukan.');
        }
        switch ($format) {
            case 'csv':  $questions = $this->rows_to_questions($this->read_csv_rows($filepath), $default_subject_id, $default_grade_id); break;
            case 'xlsx': $questions = $this->rows_to_questions($this->read_xlsx_rows($filepath), $default_subject_id, $default_grade_id); break;
            case 'txt':  $questions = $this->parse_txt(file_get_contents($filepath), $default_subject_id, $default_grade_id); break;
            default: throw new RuntimeException('Format tidak didukung.');
        }
        $valid = 0;
        $warnings = 0;
        foreach ($questions as &$q) {
            if ($q['status'] === 'ok' && ! $this->scope_is_enabled($q['subject_id'], $q['grade_id'])) {
                // Matriks kurikulum adalah pagar mutu, bukan kesalahan struktur
                // file. Admin dapat secara sadar meng-override pada tahap commit,
                // misalnya untuk soal pengayaan/level yang lebih lanjut.
                $q['warnings'][] = 'Kombinasi mapel dan jenjang belum tercakup dalam matriks Kurikulum Merdeka.';
                $q['has_curriculum_warning'] = true;
                $warnings++;
            }
            if ($q['status'] === 'ok') $valid++;
        }
        unset($q);
        return [
            'questions' => $questions,
            'summary'   => [
                'total'    => count($questions),
                'valid'    => $valid,
                'ready'    => $valid - $warnings,
                'warnings' => $warnings,
                'invalid'  => count($questions) - $valid,
            ],
        ];
    }

    /** Commit hasil analisa. Override hanya berlaku untuk peringatan cakupan kurikulum. */
    public function commit_import(array $questions, $user_id, $filename, $format, $allow_curriculum_override = false, $session_scope_id = null)
    {
        $session_scope_id = $session_scope_id ? (int) $session_scope_id : null;
        $batch_id = $this->create_import_batch($filename, $format, $session_scope_id ? 'session' : 'bank', $session_scope_id, $user_id);
        $imported = 0; $skipped = 0;
        $question_ids = [];

        foreach ($questions as $q) {
            if (($q['status'] ?? '') !== 'ok') { $skipped++; continue; }
            if (! $this->scope_is_enabled($q['subject_id'], $q['grade_id']) && ! $allow_curriculum_override) {
                $skipped++;
                continue;
            }

            $this->db->insert('quiz_questions', [
                'subject_id'           => (int) $q['subject_id'],
                'grade_level_id'       => (int) $q['grade_id'],
                'type'                 => $q['type'],
                'difficulty'           => $q['difficulty'],
                'question_text'        => $q['question_text'],
                'question_image'       => ! empty($q['question_image']) ? $q['question_image'] : null,
                'explanation'          => $q['explanation'] ?? '',
                'correct_option_index' => $q['type'] === 'multiple_choice' ? (int) $q['correct_index'] : null,
                // Bank umum tetap draft; soal khusus sesi sudah direview pada pratinjau import.
                'is_active'            => $session_scope_id ? 1 : 0,
                'is_session_only'      => $session_scope_id ? 1 : 0,
                'session_scope_id'     => $session_scope_id,
                'import_batch_id'      => $batch_id,
                'created_by'           => $user_id,
            ]);
            $q_id = (int) $this->db->insert_id();
            $question_ids[] = $q_id;

            if ($q['type'] === 'multiple_choice') {
                foreach ($q['options'] as $idx => $opt) {
                    $text = is_array($opt) ? ($opt['text'] ?? '') : $opt;
                    if (trim((string) $text) === '') continue;
                    $this->db->insert('quiz_question_options', [
                        'question_id'  => $q_id,
                        'option_index' => (int) $idx,
                        'option_text'  => trim((string) $text),
                        'option_image' => null,
                    ]);
                }
            }
            if (! empty($q['tags'])) {
                $this->sync_tags($q_id, $q['tags']);
            }
            $imported++;
        }

        $this->db->update('quiz_import_batches', [
            'total_rows' => count($questions),
            'imported'   => $imported,
            'skipped'    => $skipped,
            'errors'     => 0,
        ], ['id' => $batch_id]);

        return ['batch_id' => $batch_id, 'imported' => $imported, 'skipped' => $skipped, 'question_ids' => $question_ids];
    }

    // ── Pembaca file ───────────────────────────────────────────────────────────

    private function read_csv_rows($filepath)
    {
        $rows = [];
        $handle = fopen($filepath, 'r');
        if (! $handle) throw new RuntimeException('Gagal membuka file.');
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") rewind($handle);
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = array_map(function ($v) { return trim((string) $v); }, $row);
        }
        fclose($handle);
        return $rows;
    }

    /** Baca .xlsx (Office Open XML) tanpa library eksternal via ZipArchive + SimpleXML. */
    private function read_xlsx_rows($filepath)
    {
        if (! class_exists('ZipArchive')) throw new RuntimeException('Ekstensi ZipArchive tidak tersedia.');
        $zip = new ZipArchive();
        if ($zip->open($filepath) !== true) throw new RuntimeException('Gagal membuka file Excel.');

        $shared = [];
        $ss = $zip->getFromName('xl/sharedStrings.xml');
        if ($ss !== false) {
            $xml = simplexml_load_string($ss);
            if ($xml) {
                foreach ($xml->si as $si) {
                    if (isset($si->t)) {
                        $shared[] = (string) $si->t;
                    } else {
                        $txt = '';
                        foreach ($si->r as $r) { $txt .= (string) $r->t; }
                        $shared[] = $txt;
                    }
                }
            }
        }

        // Ambil sheet pertama dari workbook.
        $sheetPath = 'xl/worksheets/sheet1.xml';
        $sheetXml  = $zip->getFromName($sheetPath);
        $zip->close();
        if ($sheetXml === false) throw new RuntimeException('Sheet pertama tidak ditemukan di file Excel.');

        $xml  = simplexml_load_string($sheetXml);
        $rows = [];
        foreach ($xml->sheetData->row as $row) {
            $cells = [];
            foreach ($row->c as $c) {
                $ref  = (string) $c['r'];
                $col  = $this->col_letter_to_index(preg_replace('/[0-9]+/', '', $ref));
                $t    = (string) $c['t'];
                if ($t === 's') {
                    $val = $shared[(int) $c->v] ?? '';
                } elseif ($t === 'inlineStr') {
                    $val = (string) $c->is->t;
                } else {
                    $val = (string) $c->v;
                }
                $cells[$col] = trim((string) $val);
            }
            $max  = empty($cells) ? -1 : max(array_keys($cells));
            $line = [];
            for ($i = 0; $i <= $max; $i++) { $line[] = $cells[$i] ?? ''; }
            $rows[] = $line;
        }
        return $rows;
    }

    private function col_letter_to_index($letters)
    {
        $n = 0;
        foreach (str_split((string) $letters) as $ch) {
            $n = $n * 26 + (ord(strtoupper($ch)) - 64);
        }
        return $n - 1;
    }

    // ── Normalisasi baris kolom (CSV/XLSX) → soal ──────────────────────────────

    private function rows_to_questions(array $rows, $default_subject_id, $default_grade_id)
    {
        if (count($rows) < 2) return [];
        $header = array_map(function ($h) { return strtolower(trim((string) $h)); }, array_shift($rows));

        $idx = function ($name) use ($header) {
            $p = array_search($name, $header);
            return $p === false ? null : $p;
        };
        $cols = [];
        foreach (['question_text','type','difficulty','subject_code','grade_code',
                  'option_a','option_b','option_c','option_d','option_e','correct_answer','explanation','tags'] as $k) {
            $cols[$k] = $idx($k);
        }

        $subjects = $this->subject_map();
        $grades   = $this->grade_map();
        $subNames = $this->subject_names();
        $grdNames = $this->grade_names();
        $letters  = ['A' => 0, 'B' => 1, 'C' => 2, 'D' => 3, 'E' => 4];

        $out = [];
        $n = 1;
        foreach ($rows as $row) {
            $get = function ($key) use ($row, $cols) {
                $p = $cols[$key];
                return $p !== null && isset($row[$p]) ? trim((string) $row[$p]) : '';
            };
            $q_text = $get('question_text');
            if ($q_text === '') continue; // baris kosong dilewati diam-diam
            $n++;

            $issues = [];
            $type = in_array($get('type'), ['multiple_choice','essay']) ? $get('type') : 'multiple_choice';
            $diff = in_array($get('difficulty'), ['easy','medium','hard']) ? $get('difficulty') : 'medium';

            // Subjek: pakai kode bila ada & valid, else default form.
            $sub_code = strtolower($get('subject_code'));
            $subject_id = $sub_code !== '' && isset($subjects[$sub_code]) ? $subjects[$sub_code] : ($default_subject_id ?: null);
            if ($sub_code !== '' && ! isset($subjects[$sub_code]) && ! $default_subject_id) {
                $issues[] = "Kode mapel '{$sub_code}' tidak dikenal.";
            }
            $grade_code = strtolower($get('grade_code'));
            $grade_id = $grade_code !== '' && isset($grades[$grade_code]) ? $grades[$grade_code] : ($default_grade_id ?: null);
            if ($grade_code !== '' && ! isset($grades[$grade_code]) && ! $default_grade_id) {
                $issues[] = "Kode jenjang '{$grade_code}' tidak dikenal.";
            }
            if (! $subject_id) $issues[] = 'Mata pelajaran belum ditentukan.';
            if (! $grade_id)   $issues[] = 'Jenjang belum ditentukan.';

            $options = []; $correct_index = 0; $correct_letter = '';
            if ($type === 'multiple_choice') {
                foreach (['option_a','option_b','option_c','option_d','option_e'] as $oi => $k) {
                    $t = $get($k);
                    if ($t !== '') $options[$oi] = ['text' => $t, 'image' => null];
                }
                if (count($options) < 2) $issues[] = 'Minimal 2 pilihan jawaban.';
                $correct_letter = strtoupper($get('correct_answer'));
                $correct_index  = $letters[$correct_letter] ?? -1;
                if (! isset($options[$correct_index])) {
                    $issues[] = "Kunci jawaban '{$correct_letter}' tidak valid / kosong.";
                    if ($correct_index < 0) $correct_index = 0;
                }
            }

            $tags = array_filter(array_map('trim', explode(',', $get('tags'))));

            $out[] = [
                'row'            => $n,
                'question_text'  => $q_text,
                'type'           => $type,
                'difficulty'     => $diff,
                'subject_id'     => $subject_id,
                'subject_label'  => $subject_id ? ($subNames[$subject_id] ?? '') : '—',
                'grade_id'       => $grade_id,
                'grade_label'    => $grade_id ? ($grdNames[$grade_id] ?? '') : '—',
                'options'        => $options,
                'correct_index'  => $correct_index,
                'correct_letter' => $correct_letter ?: (isset($letters) ? array_search($correct_index, $letters) : ''),
                'explanation'    => $get('explanation'),
                'tags'           => array_values($tags),
                'status'         => empty($issues) ? 'ok' : 'error',
                'issues'         => $issues,
            ];
        }
        return $out;
    }

    // ── Parser TXT (format naskah soal) ────────────────────────────────────────

    /**
     * Parse file TXT bergaya naskah:
     *  - "BAGIAN ... MUDAH/SEDANG/SULIT" → set tingkat kesulitan untuk soal berikutnya
     *  - "1. teks soal", opsi "a. ...", "b. ..." dst
     *  - blok "KUNCI JAWABAN" berisi "1.c  2.d  ..." → kunci per nomor
     */
    private function parse_txt($content, $default_subject_id, $default_grade_id)
    {
        $letters_idx = ['A' => 0, 'B' => 1, 'C' => 2, 'D' => 3, 'E' => 4];
        $idx_letter  = ['A', 'B', 'C', 'D', 'E'];
        $subNames = $this->subject_names();
        $grdNames = $this->grade_names();

        $lines = preg_split('/\r\n|\r|\n/', (string) $content);
        $questions = [];   // number => ['text','options'=>[],'difficulty']
        $order = [];
        $answers = [];     // number => index
        $current = null;
        $current_option = null;
        $difficulty = 'medium';
        $in_answer_key = false;

        foreach ($lines as $line) {
            $trim = trim($line);
            if ($trim === '') continue;

            // Header kunci harus berada di awal baris. Frasa "kunci jawaban" yang
            // kebetulan menjadi bagian teks soal tidak boleh mengakhiri blok soal.
            if (preg_match('/^(?:KUNCI(?:\s+JAWABAN)?|ANSWER\s+KEY)\s*:?\s*(.*)$/iu', $trim, $key_header)) {
                $in_answer_key = true;
                $current = null;
                $current_option = null;
                $trim = trim($key_header[1]);
                if ($trim === '') continue;
            }

            if ($in_answer_key) {
                if (preg_match_all('/\b(\d+)\s*(?:[\.\)\-:]|\s)\s*([a-e])\b/iu', $trim, $m, PREG_SET_ORDER)) {
                    foreach ($m as $pair) {
                        $answers[(int) $pair[1]] = $letters_idx[strtoupper($pair[2])] ?? 0;
                    }
                }
                continue;
            }

            // Soal harus dikenali sebelum header. Dengan demikian teks seperti
            // "1) Bagian tumbuhan ..." atau "2 - Soal mudah ..." tetap menjadi soal.
            $question_line = $this->txt_question_line($trim);
            if ($question_line !== null) {
                $num = $question_line['number'];
                $current = $num;
                $current_option = null;
                $questions[$num] = ['text' => $question_line['text'], 'options' => [], 'difficulty' => $difficulty];
                if (! in_array($num, $order, true)) $order[] = $num;
                continue;
            }

            // Opsi: a. / a) / (a) / a: / a -
            $option_line = $this->txt_option_line($trim);
            if ($current !== null && $option_line !== null) {
                $li = $letters_idx[$option_line['letter']] ?? null;
                if ($li !== null) {
                    $questions[$current]['options'][$li] = ['text' => $option_line['text'], 'image' => null];
                    $current_option = $li;
                }
                continue;
            }

            // Hanya bentuk header yang jelas yang boleh mengubah tingkat kesulitan.
            // Kata biasa "bagian", "mudah", atau "sedang" di dalam teks tidak cocok.
            $section = $this->txt_section_header($trim);
            if ($section['is_header']) {
                if ($section['difficulty'] !== null) $difficulty = $section['difficulty'];
                $current = null;
                $current_option = null;
                continue;
            }

            // Baris terbungkus dilanjutkan ke opsi terakhir atau ke teks soal.
            if ($current !== null) {
                if ($current_option !== null && isset($questions[$current]['options'][$current_option])) {
                    $questions[$current]['options'][$current_option]['text'] .= ' ' . $trim;
                } else {
                    $questions[$current]['text'] .= ' ' . $trim;
                }
            }
        }

        $out = [];
        foreach ($order as $num) {
            $q = $questions[$num];
            $issues = [];
            $options = $q['options'];
            ksort($options);
            if (count($options) < 2) $issues[] = 'Minimal 2 pilihan jawaban.';

            $correct_index = $answers[$num] ?? -1;
            if ($correct_index < 0)         $issues[] = "Kunci jawaban untuk soal no. {$num} tidak ditemukan.";
            elseif (! isset($options[$correct_index])) $issues[] = "Kunci jawaban soal no. {$num} menunjuk pilihan kosong.";
            if ($correct_index < 0) $correct_index = 0;

            if (! $default_subject_id) $issues[] = 'Mata pelajaran belum dipilih.';
            if (! $default_grade_id)   $issues[] = 'Jenjang belum dipilih.';

            $out[] = [
                'row'            => $num,
                'question_text'  => $q['text'],
                'type'           => 'multiple_choice',
                'difficulty'     => $q['difficulty'],
                'subject_id'     => $default_subject_id ?: null,
                'subject_label'  => $default_subject_id ? ($subNames[$default_subject_id] ?? '') : '—',
                'grade_id'       => $default_grade_id ?: null,
                'grade_label'    => $default_grade_id ? ($grdNames[$default_grade_id] ?? '') : '—',
                'options'        => $options,
                'correct_index'  => $correct_index,
                'correct_letter' => $idx_letter[$correct_index] ?? '',
                'explanation'    => '',
                'tags'           => [],
                'status'         => empty($issues) ? 'ok' : 'error',
                'issues'         => $issues,
            ];
        }
        return $out;
    }

    /** Ambil nomor dan teks dari bentuk 1. / 1) / (1) / 1: / 1 -. */
    private function txt_question_line($line)
    {
        if (preg_match('/^\((\d+)\)\s*(.+)$/u', $line, $m)
            || preg_match('/^(\d+)\s*[\.\)\-:]\s*(.+)$/u', $line, $m)) {
            return ['number' => (int) $m[1], 'text' => trim($m[2])];
        }
        return null;
    }

    /** Ambil huruf dan teks dari bentuk a. / a) / (a) / a: / a -. */
    private function txt_option_line($line)
    {
        if (preg_match('/^\(([a-e])\)\s*(.+)$/iu', $line, $m)
            || preg_match('/^([a-e])\s*[\.\)\-:]\s*(.+)$/iu', $line, $m)) {
            return ['letter' => strtoupper($m[1]), 'text' => trim($m[2])];
        }
        return null;
    }

    /**
     * Kenali header struktural saja. Nilai difficulty null berarti header bagian
     * valid tetapi tidak menyebut tingkat kesulitan sehingga nilai sebelumnya dipakai.
     */
    private function txt_section_header($line)
    {
        $difficulty_words = '(?:MUDAH|EASY|SEDANG|MEDIUM|MENENGAH|SULIT|SUKAR|HARD)';
        $is_structural = preg_match(
            '/^(?:(?:BAGIAN|SECTION|SEKSI)\s+(?:(?:[A-Z]|\d+|[IVXLCDM]+)\b(?:\s*(?:[-:–—\(]|$)|\s+(?=' . $difficulty_words . '\b))|' . $difficulty_words . '\b)|(?:TINGKAT|LEVEL)\s+' . $difficulty_words . '\b)/iu',
            $line
        ) === 1;
        $is_difficulty_only = preg_match(
            '/^[\[\(]?\s*' . $difficulty_words . '\s*[\]\)]?(?:\s*[-:–—]?\s*(?:SOAL|QUESTIONS?)\s*\d+(?:\s*[-–—]\s*\d+)?)?\s*\.?$/iu',
            $line
        ) === 1;

        if (! $is_structural && ! $is_difficulty_only) {
            return ['is_header' => false, 'difficulty' => null];
        }

        $difficulty = null;
        if (preg_match('/\b(?:MUDAH|EASY)\b/iu', $line)) $difficulty = 'easy';
        elseif (preg_match('/\b(?:SEDANG|MEDIUM|MENENGAH)\b/iu', $line)) $difficulty = 'medium';
        elseif (preg_match('/\b(?:SULIT|SUKAR|HARD)\b/iu', $line)) $difficulty = 'hard';

        return ['is_header' => true, 'difficulty' => $difficulty];
    }

    private function subject_names()
    {
        $rows = $this->db->select('id, name')->get('quiz_subjects')->result_array();
        $m = [];
        foreach ($rows as $r) { $m[(int) $r['id']] = $r['name']; }
        return $m;
    }

    private function grade_names()
    {
        $rows = $this->db->select('id, name')->get('quiz_grade_levels')->result_array();
        $m = [];
        foreach ($rows as $r) { $m[(int) $r['id']] = $r['name']; }
        return $m;
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
        $this->db->where('q.is_session_only', 0);

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

    private function scope_is_enabled($subject_id, $grade_level_id)
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

    private function assert_curriculum_scope(array $data)
    {
        if (! $this->scope_is_enabled($data['subject_id'] ?? 0, $data['grade_level_id'] ?? 0)) {
            throw new RuntimeException('Kombinasi jenjang dan mata pelajaran belum diizinkan dalam matriks Kurikulum Merdeka.');
        }
    }

    private function sanitize_question(array $data)
    {
        $out = [
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
        // Kolom gambar hanya diikutkan bila key-nya disediakan (agar update tak menimpa jadi null tanpa sengaja).
        if (array_key_exists('question_image', $data)) {
            $out['question_image'] = $data['question_image'] !== '' ? $data['question_image'] : null;
        }
        if (array_key_exists('explanation_image', $data)) {
            $out['explanation_image'] = $data['explanation_image'] !== '' ? $data['explanation_image'] : null;
        }
        return $out;
    }

    /**
     * Simpan pilihan. $options bisa berupa:
     *  - [idx => 'teks']  (kompatibel lama), atau
     *  - [idx => ['text' => '...', 'image' => '...|null']]
     * Menyimpan hingga 5 pilihan (A–E).
     */
    private function save_options($question_id, array $options)
    {
        foreach ($options as $idx => $opt) {
            $idx = (int) $idx;
            if ($idx < 0 || $idx > 4) {
                continue;
            }
            if (is_array($opt)) {
                $text  = trim((string) ($opt['text'] ?? ''));
                $image = ($opt['image'] ?? '') !== '' ? $opt['image'] : null;
            } else {
                $text  = trim((string) $opt);
                $image = null;
            }
            if ($text === '' && $image === null) {
                continue;
            }
            $this->db->insert('quiz_question_options', [
                'question_id'  => $question_id,
                'option_index' => $idx,
                'option_text'  => $text,
                'option_image' => $image,
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
        $this->db->where('q.deleted_at IS NULL', null, false)->where('q.is_active', 1)->where('q.is_session_only', 0);
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
