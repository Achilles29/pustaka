<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Quiz_bank extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['Quiz_bank_model', 'Quiz_config_model']);
        $this->load->library('upload');
    }

    public function index()
    {
        $this->require_permission('quiz_bank.index', 'view');

        $filters = [
            'q'              => $this->input->get('q', true),
            'subject_id'     => $this->input->get('subject_id', true),
            'grade_level_id' => $this->input->get('grade_level_id', true),
            'type'           => $this->input->get('type', true),
            'difficulty'     => $this->input->get('difficulty', true),
            'is_active'      => $this->input->get('is_active', true),
        ];
        $per_page    = (int) $this->input->get('per_page', true);
        $per_page    = in_array($per_page, [10, 25, 50, 100], true) ? $per_page : 25;
        $page        = max(1, (int) $this->input->get('page', true));
        $total_rows  = $this->Quiz_bank_model->count_questions($filters);
        $total_pages = max(1, (int) ceil($total_rows / $per_page));
        $page        = min($page, $total_pages);
        $offset      = ($page - 1) * $per_page;

        $this->render('quiz/bank/index', [
            'title'      => 'Bank Soal',
            'stats'      => $this->Quiz_bank_model->stats(),
            'questions'  => $this->Quiz_bank_model->get_questions($filters, $per_page, $offset),
            'subjects'   => $this->Quiz_config_model->get_subjects(true),
            'grades'     => $this->Quiz_config_model->get_grade_levels(true),
            'tags'       => $this->Quiz_bank_model->get_all_tags(),
            'batches'    => $this->Quiz_bank_model->get_import_batches(),
            'filters'    => array_merge($filters, ['per_page' => $per_page, 'page' => $page]),
            'pagination' => compact('total_rows', 'total_pages', 'page', 'per_page', 'offset'),
            'can_create' => $this->can('quiz_bank.index', 'create'),
            'can_edit'   => $this->can('quiz_bank.index', 'edit'),
            'can_delete' => $this->can('quiz_bank.index', 'delete'),
        ]);
    }

    public function create()
    {
        $this->require_permission('quiz_bank.index', 'create');
        $this->render('quiz/bank/form', [
            'title'    => 'Tambah Soal',
            'action'   => 'quiz-bank/store',
            'question' => null,
            'subjects' => $this->Quiz_config_model->get_subjects(true),
            'grades'   => $this->Quiz_config_model->get_grade_levels(true),
            'tags'     => $this->Quiz_bank_model->get_all_tags(),
        ]);
    }

    public function store()
    {
        $this->require_permission('quiz_bank.index', 'create');
        try {
            [$data, $options, $tags] = $this->question_input();
            $data['created_by'] = (int) $this->current_user['id'];
            $id = $this->Quiz_bank_model->create_question($data, $options, $tags);
            $this->audit_event('quiz.question.create', 'quiz_questions', $id, null, ['subject_id' => $data['subject_id']]);
            $this->session->set_flashdata('success', 'Soal berhasil ditambahkan.');
            redirect('quiz-bank');
        } catch (Throwable $e) {
            $this->session->set_flashdata('error', $e->getMessage());
            redirect('quiz-bank/create');
        }
    }

    public function edit($id)
    {
        $this->require_permission('quiz_bank.index', 'edit');
        $question = $this->Quiz_bank_model->get_question((int) $id);
        if (! $question) {
            show_404();
            return;
        }
        $this->render('quiz/bank/form', [
            'title'    => 'Edit Soal',
            'action'   => 'quiz-bank/update/' . (int) $id,
            'question' => $question,
            'subjects' => $this->Quiz_config_model->get_subjects(true),
            'grades'   => $this->Quiz_config_model->get_grade_levels(true),
            'tags'     => $this->Quiz_bank_model->get_all_tags(),
        ]);
    }

    public function update($id)
    {
        $this->require_permission('quiz_bank.index', 'edit');
        $question = $this->Quiz_bank_model->get_question((int) $id);
        if (! $question) {
            show_404();
            return;
        }
        try {
            [$data, $options, $tags] = $this->question_input();
            $this->Quiz_bank_model->update_question((int) $id, $data, $options, $tags);
            $this->audit_event('quiz.question.update', 'quiz_questions', (int) $id, $question, $data);
            $this->session->set_flashdata('success', 'Soal berhasil diperbarui.');
            redirect('quiz-bank');
        } catch (Throwable $e) {
            $this->session->set_flashdata('error', $e->getMessage());
            redirect('quiz-bank/edit/' . (int) $id);
        }
    }

    public function delete($id)
    {
        $this->require_permission('quiz_bank.index', 'delete');
        $question = $this->Quiz_bank_model->get_question((int) $id);
        if (! $question) {
            show_404();
            return;
        }
        $this->Quiz_bank_model->delete_question((int) $id);
        $this->audit_event('quiz.question.delete', 'quiz_questions', (int) $id, $question);
        $this->session->set_flashdata('success', 'Soal berhasil dihapus.');
        redirect('quiz-bank');
    }

    public function bulk_delete()
    {
        $this->require_permission('quiz_bank.index', 'delete');
        $ids = (array) $this->input->post('ids');
        $ids = array_filter(array_map('intval', $ids));
        if (empty($ids)) {
            $this->session->set_flashdata('error', 'Tidak ada soal yang dipilih.');
            redirect('quiz-bank');
            return;
        }
        $deleted = $this->Quiz_bank_model->bulk_delete($ids, (int) $this->current_user['id']);
        $this->audit_event('quiz.question.bulk_delete', 'quiz_questions', 0, null, ['ids' => $ids, 'count' => $deleted]);
        $this->session->set_flashdata('success', "{$deleted} soal berhasil dihapus.");
        redirect('quiz-bank');
    }

    // ── Import ────────────────────────────────────────────────────────────────

    public function import()
    {
        $this->require_permission('quiz_bank.index', 'create');
        $session_scope = null;
        $session_id = (int) $this->input->get('session_id', true);
        if ($session_id > 0) {
            $this->load->model('Quiz_session_model');
            $session_scope = $this->Quiz_session_model->get_session($session_id);
            if (! $session_scope || $session_scope['type'] !== 'practice') { show_404(); return; }
        }
        $this->render('quiz/bank/import', [
            'title'    => 'Import Bank Soal',
            'subjects' => $this->Quiz_config_model->get_subjects(true),
            'grades'   => $this->Quiz_config_model->get_grade_levels(true),
            'batches'  => $this->Quiz_bank_model->get_import_batches(),
            'session_scope' => $session_scope,
        ]);
    }

    /** Langkah 1: upload → parse & validasi → tampilkan pratinjau untuk direview. */
    public function analyze()
    {
        $this->require_permission('quiz_bank.index', 'create');

        $subject_id = (int) $this->input->post('subject_id') ?: null;
        $grade_id   = (int) $this->input->post('grade_level_id') ?: null;
        $session_scope_id = (int) $this->input->post('session_scope_id') ?: null;
        $session_scope = null;
        if ($session_scope_id) {
            $this->load->model('Quiz_session_model');
            $session_scope = $this->Quiz_session_model->get_session($session_scope_id);
            if (! $session_scope || $session_scope['type'] !== 'practice') {
                $this->session->set_flashdata('error', 'Sesi latihan tujuan tidak ditemukan.');
                redirect('quiz-bank/import');
                return;
            }
            $subject_id = $subject_id ?: (int) $session_scope['subject_id'];
            $grade_id = $grade_id ?: (int) $session_scope['grade_level_id'];
        }

        if (empty($_FILES['import_file']['name']) || (int) $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            $this->session->set_flashdata('error', 'Silakan pilih file untuk dianalisa.');
            redirect('quiz-bank/import');
            return;
        }

        $orig = $_FILES['import_file']['name'];
        $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        if (! in_array($ext, ['csv', 'txt', 'xlsx'], true)) {
            $this->session->set_flashdata('error', 'Format tidak didukung. Gunakan CSV, TXT, atau Excel (.xlsx).');
            redirect('quiz-bank/import');
            return;
        }
        if ($_FILES['import_file']['size'] > 5 * 1024 * 1024) {
            $this->session->set_flashdata('error', 'Ukuran file maksimal 5 MB.');
            redirect('quiz-bank/import');
            return;
        }

        $dir = FCPATH . 'assets/uploads/quiz/imports/';
        if (! is_dir($dir)) { mkdir($dir, 0755, true); }
        $stored = $dir . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        if (! @move_uploaded_file($_FILES['import_file']['tmp_name'], $stored)) {
            $this->session->set_flashdata('error', 'Gagal menyimpan file unggahan.');
            redirect('quiz-bank/import');
            return;
        }

        try {
            $result = $this->Quiz_bank_model->analyze_import($stored, $ext, $subject_id, $grade_id);
        } catch (Throwable $e) {
            @unlink($stored);
            $this->session->set_flashdata('error', 'Gagal menganalisa file: ' . $e->getMessage());
            redirect('quiz-bank/import');
            return;
        }
        @unlink($stored); // data sudah ternormalisasi di memori/sesi

        // Simpan hasil analisa untuk tahap commit.
        $this->session->set_userdata('quiz_import', [
            'questions' => $result['questions'],
            'filename'  => $orig,
            'format'    => $ext,
            'session_scope_id' => $session_scope_id,
        ]);

        $this->render('quiz/bank/import_preview', [
            'title'                    => 'Analisa Import',
            'questions'                => $result['questions'],
            'summary'                  => $result['summary'],
            'filename'                 => $orig,
            'format'                   => $ext,
            'can_curriculum_override' => $this->can('quiz_bank.index', 'edit'),
            'session_scope'            => $session_scope,
        ]);
        $this->output->set_output($this->_decorate_import_image_preview($this->output->get_output(), $result['questions']));
    }

    /** Langkah 2: import soal yang sudah direview (hanya yang valid). */
    public function commit_import()
    {
        $this->require_permission('quiz_bank.index', 'create');

        $data = $this->session->userdata('quiz_import');
        if (empty($data['questions'])) {
            $this->session->set_flashdata('error', 'Tidak ada data analisa. Silakan unggah & analisa file lagi.');
            redirect('quiz-bank/import');
            return;
        }

        $allow_curriculum_override = $this->input->post('override_curriculum_scope') === '1';
        if ($allow_curriculum_override && ! $this->can('quiz_bank.index', 'edit')) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki izin untuk melakukan override matriks kurikulum.');
            redirect('quiz-bank/import');
            return;
        }
        $session_scope_id = (int) ($data['session_scope_id'] ?? 0) ?: null;
        try {
            $questions = $this->_attach_import_question_images($data['questions']);
            $res = $this->Quiz_bank_model->commit_import(
                $questions, (int) $this->current_user['id'], $data['filename'], $data['format'], $allow_curriculum_override, $session_scope_id
            );
        } catch (Throwable $e) {
            $this->session->set_flashdata('error', 'Import gambar/soal gagal: ' . $e->getMessage());
            redirect($session_scope_id ? 'quiz-bank/import?session_id=' . $session_scope_id : 'quiz-bank/import');
            return;
        }
        if ($session_scope_id) {
            $this->load->model('Quiz_session_model');
            $this->Quiz_session_model->add_session_only_questions($session_scope_id, $res['question_ids'] ?? []);
        }
        $this->audit_event('quiz.bank.import', 'quiz_import_batches', $res['batch_id'], null, array_merge($res, [
            'curriculum_override' => $allow_curriculum_override,
        ]));
        $this->session->unset_userdata('quiz_import');

        $message = "Import selesai: {$res['imported']} soal masuk" . ($res['skipped'] ? ", {$res['skipped']} dilewati (bermasalah atau di luar matriks)" : '') . '.';
        if ($allow_curriculum_override) {
            $message .= ' Override matriks kurikulum dicatat pada audit log.';
        }
        $this->session->set_flashdata('success', $message);
        redirect($session_scope_id ? 'quiz-sessions/edit/' . $session_scope_id . '?tab=questions' : 'quiz-bank');
    }

    public function template($format = 'csv')
    {
        if ($format === 'xlsx') {
            $this->_download_xlsx_template();
            return;
        }
        if ($format === 'txt') {
            $txt  = "MATEMATIKA - KELAS 4 SD\n";
            $txt .= "Contoh naskah soal | Pilihan Ganda\n\n";
            $txt .= "BAGIAN A - MUDAH (Soal 1-2)\n\n";
            $txt .= "1. Berapakah hasil dari 2 + 2?\n   a. 3\n   b. 4\n   c. 5\n   d. 6\n\n";
            $txt .= "2. Berapakah hasil dari 5 x 2?\n   a. 7\n   b. 10\n   c. 12\n   d. 25\n\n";
            $txt .= "BAGIAN B - SEDANG (Soal 3)\n\n";
            $txt .= "3. Hasil dari 100 : 4 adalah ....\n   a. 20\n   b. 25\n   c. 30\n   d. 40\n\n";
            $txt .= "KUNCI JAWABAN\n1.b   2.b   3.b\n";

            $this->output->set_status_header(200)->set_content_type('text/plain; charset=utf-8')
                ->set_header('Content-Disposition: attachment; filename="template_soal.txt"')
                ->set_output($txt);
            return;
        }

        // CSV (juga jadi acuan kolom untuk Excel .xlsx)
        $csv  = "question_text,type,difficulty,subject_code,grade_code,option_a,option_b,option_c,option_d,option_e,correct_answer,explanation,tags\n";
        $csv .= "\"Berapakah hasil dari 2 + 2?\",multiple_choice,easy,matematika,sd_4,\"3\",\"4\",\"5\",\"6\",\"7\",B,\"2 + 2 = 4\",\"aritmatika,penjumlahan\"\n";
        $csv .= "\"Contoh sumber daya alam yang dapat diperbarui adalah ....\",multiple_choice,easy,ipas,sd_4,\"Batu bara\",\"Matahari\",\"Minyak bumi\",\"Gas alam\",,B,\"Matahari tersedia kembali secara alami.\",\"sumber-daya-alam\"\n";
        $csv .= "\"Sebutkan 3 contoh benda padat di sekitarmu!\",essay,medium,ipas,sd_5,,,,,,,\"Contoh: batu, kayu, dan besi.\",\"benda-padat\"\n";

        $this->output->set_status_header(200)->set_content_type('text/csv; charset=utf-8')
            ->set_header('Content-Disposition: attachment; filename="template_bank_soal.csv"')
            ->set_output("\xEF\xBB\xBF" . $csv);
    }

    /** Bangun & unduh template Excel (.xlsx) tanpa library eksternal (ZipArchive). */
    private function _download_xlsx_template()
    {
        $rows = [
            ['question_text', 'type', 'difficulty', 'subject_code', 'grade_code', 'option_a', 'option_b', 'option_c', 'option_d', 'option_e', 'correct_answer', 'explanation', 'tags'],
            ['Berapakah hasil dari 2 + 2?', 'multiple_choice', 'easy', 'matematika', 'sd_4', '3', '4', '5', '6', '7', 'B', '2 + 2 = 4', 'aritmatika,penjumlahan'],
            ['Contoh sumber daya alam yang dapat diperbarui adalah ....', 'multiple_choice', 'easy', 'ipas', 'sd_4', 'Batu bara', 'Matahari', 'Minyak bumi', 'Gas alam', '', 'B', 'Matahari tersedia kembali secara alami.', 'sumber-daya-alam'],
            ['Sebutkan 3 contoh benda padat di sekitarmu!', 'essay', 'medium', 'ipas', 'sd_5', '', '', '', '', '', '', 'Contoh: batu, kayu, dan besi.', 'benda-padat'],
        ];

        // Shared strings
        $strings = []; $map = [];
        foreach ($rows as $r) {
            foreach ($r as $c) {
                if ($c !== '' && ! isset($map[$c])) { $map[$c] = count($strings); $strings[] = $c; }
            }
        }
        $si = '';
        foreach ($strings as $s) { $si .= '<si><t xml:space="preserve">' . htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</t></si>'; }
        $sharedXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($strings) . '" uniqueCount="' . count($strings) . '">' . $si . '</sst>';

        $sheet = '';
        foreach ($rows as $ri => $r) {
            $rn = $ri + 1;
            $sheet .= '<row r="' . $rn . '">';
            foreach ($r as $ci => $c) {
                if ($c === '') continue;
                $col = $this->_xlsx_col($ci);
                $sheet .= '<c r="' . $col . $rn . '" t="s"><v>' . $map[$c] . '</v></c>';
            }
            $sheet .= '</row>';
        }
        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>' . $sheet . '</sheetData></worksheet>';

        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/></Types>';
        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>';
        $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Soal" sheetId="1" r:id="rId1"/></sheets></workbook>';
        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/></Relationships>';

        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        $zip = new ZipArchive();
        $zip->open($tmp, ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', $contentTypes);
        $zip->addFromString('_rels/.rels', $rels);
        $zip->addFromString('xl/workbook.xml', $workbook);
        $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);
        $zip->addFromString('xl/sharedStrings.xml', $sharedXml);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        $data = file_get_contents($tmp);
        @unlink($tmp);

        $this->output
            ->set_content_type('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->set_header('Content-Disposition: attachment; filename="template_bank_soal.xlsx"')
            ->set_header('Content-Length: ' . strlen($data))
            ->set_output($data);
    }

    private function _xlsx_col($idx)
    {
        $s = '';
        $idx++;
        while ($idx > 0) {
            $m = ($idx - 1) % 26;
            $s = chr(65 + $m) . $s;
            $idx = intdiv($idx - 1, 26);
        }
        return $s;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function _decorate_import_image_preview($html, array $questions)
    {
        $html = preg_replace_callback('/<span class="badge bg-secondary">#(\d+)<\/span>/', function ($match) {
            return '<span class="badge bg-dark text-white px-2">Soal ' . (int) $match[1] . '</span>';
        }, $html);
        $index = 0;
        $html = preg_replace_callback('/(<div class="fw-medium mb-2">.*?<\/div>)/s', function ($match) use (&$index, $questions) {
            $current = $index++;
            if (! isset($questions[$current]) || ($questions[$current]['status'] ?? '') !== 'ok') return $match[1];
            $number = $current + 1;
            $block = '<div class="import-question-image mt-3"><div class="d-flex align-items-center gap-2 mb-2"><span class="avatar avatar-sm bg-purple-lt"><i class="ti ti-photo-plus"></i></span><div><strong class="small">Gambar soal (opsional)</strong><div class="text-secondary small">Tambahkan diagram, ilustrasi, peta, atau foto untuk soal ini.</div></div></div><input type="file" class="form-control form-control-sm" name="question_images['.$current.']" form="commit-import-form" accept="image/jpeg,image/png,image/webp,image/gif" data-question-image data-preview="question-image-preview-'.$current.'"><div class="form-hint">JPG, PNG, WebP, atau GIF · maksimal 4 MB.</div><div class="question-image-preview mt-2" id="question-image-preview-'.$current.'" hidden><img alt="Preview gambar soal #'.$number.'"><button type="button" class="btn btn-sm btn-outline-danger" data-remove-question-image><i class="ti ti-trash me-1"></i>Hapus pilihan</button></div></div>';
            return $match[1] . $block;
        }, $html);
        $html = preg_replace('/<form\b(?![^>]*enctype=)([^>]*id="commit-import-form"[^>]*)>/i', '<form$1 enctype="multipart/form-data">', $html, 1);
        $assets = '<style>.import-question-image{padding:12px;border:1px dashed #c4b5fd;border-radius:12px;background:#faf8ff}.question-image-preview{display:flex;align-items:flex-start;gap:12px;padding:10px;border-radius:12px;background:#fff}.question-image-preview[hidden]{display:none!important}.question-image-preview img{display:block;max-width:240px;max-height:180px;object-fit:contain;border:1px solid #e2e8f0;border-radius:9px}</style><script>(function(){document.querySelectorAll("[data-question-image]").forEach(function(input){var wrap=document.getElementById(input.dataset.preview),img=wrap&&wrap.querySelector("img"),remove=wrap&&wrap.querySelector("[data-remove-question-image]"),objectUrl="";input.addEventListener("change",function(){var file=this.files&&this.files[0];if(!file){wrap.hidden=true;return}if(file.size>4*1024*1024){this.value="";alert("Ukuran gambar maksimal 4 MB.");return}if(objectUrl)URL.revokeObjectURL(objectUrl);objectUrl=URL.createObjectURL(file);img.src=objectUrl;wrap.hidden=false});if(remove)remove.addEventListener("click",function(){input.value="";wrap.hidden=true;if(objectUrl){URL.revokeObjectURL(objectUrl);objectUrl=""}})})})();</script>';
        return str_replace('</body>', $assets . '</body>', $html);
    }

    /**
     * Menautkan upload gambar pada indeks soal hasil analisis.
     * File baru dipindahkan saat commit agar unggah ulang file sumber tidak diperlukan.
     */
    private function _attach_import_question_images(array $questions)
    {
        $files = $_FILES['question_images'] ?? null;
        if (! is_array($files) || empty($files['name']) || ! is_array($files['name'])) return $questions;

        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        $uploaded = [];
        try {
            foreach ($files['name'] as $index => $original) {
                if ((string) $original === '') continue;
                $index = (int) $index;
                if (! isset($questions[$index]) || ($questions[$index]['status'] ?? '') !== 'ok') {
                    throw new RuntimeException('Gambar mengarah ke soal yang tidak valid.');
                }
                $error = (int) ($files['error'][$index] ?? UPLOAD_ERR_NO_FILE);
                if ($error !== UPLOAD_ERR_OK) throw new RuntimeException('Upload gambar soal #' . ($index + 1) . ' gagal.');
                $size = (int) ($files['size'][$index] ?? 0);
                if ($size <= 0 || $size > 4 * 1024 * 1024) throw new RuntimeException('Gambar soal #' . ($index + 1) . ' maksimal 4 MB.');
                $tmp = (string) ($files['tmp_name'][$index] ?? '');
                if ($tmp === '' || ! is_uploaded_file($tmp)) throw new RuntimeException('Berkas gambar soal #' . ($index + 1) . ' tidak valid.');
                $info = @getimagesize($tmp);
                $mime = strtolower((string) ($info['mime'] ?? ''));
                if (! $info || ! isset($allowed[$mime])) throw new RuntimeException('Gambar soal #' . ($index + 1) . ' harus JPG, PNG, WebP, atau GIF.');
                if ((int) $info[0] < 40 || (int) $info[1] < 40 || (int) $info[0] > 8000 || (int) $info[1] > 8000) {
                    throw new RuntimeException('Dimensi gambar soal #' . ($index + 1) . ' harus 40–8000 piksel.');
                }
                $relative = 'assets/uploads/quiz/questions/' . date('Y/m');
                $absolute = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $relative);
                if (! is_dir($absolute) && ! mkdir($absolute, 0775, true)) throw new RuntimeException('Folder gambar soal tidak dapat dibuat.');
                $name = 'import-' . date('YmdHis') . '-' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
                $target = $absolute . DIRECTORY_SEPARATOR . $name;
                if (! move_uploaded_file($tmp, $target)) throw new RuntimeException('Gambar soal #' . ($index + 1) . ' belum dapat disimpan.');
                $path = $relative . '/' . $name;
                $uploaded[] = $path;
                $questions[$index]['question_image'] = $path;
            }
        } catch (Throwable $e) {
            foreach ($uploaded as $path) if (is_file(FCPATH . $path)) @unlink(FCPATH . $path);
            throw $e;
        }
        return $questions;
    }

    private function question_input()
    {
        $type = $this->input->post('type', true);

        $data = [
            'subject_id'           => $this->input->post('subject_id', true),
            'grade_level_id'       => $this->input->post('grade_level_id', true),
            'type'                 => in_array($type, ['multiple_choice','essay']) ? $type : 'multiple_choice',
            'difficulty'           => $this->input->post('difficulty', true),
            'question_text'        => $this->input->post('question_text'),
            'explanation'          => $this->input->post('explanation'),
            'correct_option_index' => $this->input->post('correct_option_index', true),
            'essay_rubric'         => $this->input->post('essay_rubric'),
            'score_weight'         => $this->input->post('score_weight', true),
            'is_active'            => $this->input->post('is_active') !== null ? 1 : 0,
            'question_image'       => $this->_resolve_single_image('question_image_file', 'question_image_existing', 'question_image_remove', 'questions'),
            'explanation_image'    => $this->_resolve_single_image('explanation_image_file', 'explanation_image_existing', 'explanation_image_remove', 'explanations'),
        ];

        // Pilihan A–E (indeks 0–4) beserta gambar per pilihan.
        $options = [];
        $raw_options = (array) $this->input->post('options');
        for ($i = 0; $i < 5; $i++) {
            $text  = isset($raw_options[$i]) ? (string) $raw_options[$i] : '';
            $image = $this->_resolve_option_image($i);
            if (trim($text) !== '' || $image !== null) {
                $options[$i] = ['text' => $text, 'image' => $image];
            }
        }

        $tags_raw = $this->input->post('tags', true);
        $tags = $tags_raw ? array_filter(array_map('trim', explode(',', $tags_raw))) : [];

        return [$data, $options, $tags];
    }

    // ── Image upload helpers ───────────────────────────────────────────────────

    /** Resolve gambar untuk field file tunggal (soal/pembahasan). */
    private function _resolve_single_image($file_field, $existing_field, $remove_field, $subdir)
    {
        $existing = (string) $this->input->post($existing_field);
        if ($this->input->post($remove_field)) {
            return '';
        }
        if (! empty($_FILES[$file_field]['name']) && (int) $_FILES[$file_field]['error'] === UPLOAD_ERR_OK) {
            $path = $this->_store_upload($_FILES[$file_field], $subdir);
            if ($path) return $path;
        }
        return $existing;
    }

    /** Resolve gambar untuk pilihan ke-$i (field array option_image_file[$i]). */
    private function _resolve_option_image($i)
    {
        $existing_all = (array) $this->input->post('option_image_existing');
        $remove_all   = (array) $this->input->post('option_image_remove');
        $existing = (string) ($existing_all[$i] ?? '');

        if (! empty($remove_all[$i])) {
            return null;
        }
        if (isset($_FILES['option_image_file']['name'][$i])
            && $_FILES['option_image_file']['name'][$i] !== ''
            && (int) $_FILES['option_image_file']['error'][$i] === UPLOAD_ERR_OK) {
            $file = [
                'name'     => $_FILES['option_image_file']['name'][$i],
                'type'     => $_FILES['option_image_file']['type'][$i],
                'tmp_name' => $_FILES['option_image_file']['tmp_name'][$i],
                'error'    => $_FILES['option_image_file']['error'][$i],
                'size'     => $_FILES['option_image_file']['size'][$i],
            ];
            $path = $this->_store_upload($file, 'options');
            if ($path) return $path;
        }
        return $existing !== '' ? $existing : null;
    }

    /** Validasi (MIME via fileinfo, maks 2MB) & simpan; return path relatif atau null. */
    private function _store_upload(array $file, $subdir)
    {
        $map = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        if (($file['size'] ?? 0) <= 0 || $file['size'] > 2 * 1024 * 1024) {
            return null;
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (! isset($map[$mime])) {
            return null;
        }
        $dir = FCPATH . 'assets/uploads/quiz/' . $subdir . '/';
        if (! is_dir($dir)) { mkdir($dir, 0755, true); }
        $name = date('Ymd') . '_' . bin2hex(random_bytes(8)) . '.' . $map[$mime];
        if (! @move_uploaded_file($file['tmp_name'], $dir . $name)) {
            return null;
        }
        return 'assets/uploads/quiz/' . $subdir . '/' . $name;
    }
}
