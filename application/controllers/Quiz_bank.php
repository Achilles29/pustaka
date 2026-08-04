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
        $this->render('quiz/bank/import', [
            'title'    => 'Import Bank Soal',
            'subjects' => $this->Quiz_config_model->get_subjects(true),
            'grades'   => $this->Quiz_config_model->get_grade_levels(true),
            'batches'  => $this->Quiz_bank_model->get_import_batches(),
        ]);
    }

    public function do_import()
    {
        $this->require_permission('quiz_bank.index', 'create');

        $upload_path = FCPATH . 'assets/uploads/quiz/imports/';
        if (! is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }

        $this->upload->initialize([
            'upload_path'   => $upload_path,
            'allowed_types' => 'csv',
            'max_size'      => 5120,
            'overwrite'     => false,
            'encrypt_name'  => true,
        ]);

        if (! $this->upload->do_upload('import_file')) {
            $this->session->set_flashdata('error', 'Upload gagal: ' . $this->upload->display_errors('', ''));
            redirect('quiz-bank/import');
            return;
        }

        $file = $this->upload->data();
        try {
            $result = $this->Quiz_bank_model->import_from_csv(
                $file['full_path'],
                (int) $this->current_user['id']
            );
            $this->audit_event('quiz.bank.import', 'quiz_import_batches', $result['batch_id'], null, [
                'imported' => $result['imported'],
                'errors'   => $result['errors'],
            ]);
            $msg = "Import selesai: {$result['imported']} soal berhasil";
            if ($result['skipped']) {
                $msg .= ", {$result['skipped']} dilewati";
            }
            if ($result['errors']) {
                $msg .= ", {$result['errors']} error";
            }
            $this->session->set_flashdata('success', $msg);
        } catch (Throwable $e) {
            $this->session->set_flashdata('error', 'Import gagal: ' . $e->getMessage());
        }

        redirect('quiz-bank/import');
    }

    public function template()
    {
        $csv = "question_text,type,difficulty,subject_code,grade_code,option_a,option_b,option_c,option_d,correct_answer,explanation,tags\n";
        $csv .= "\"Berapakah hasil dari 2 + 2?\",multiple_choice,easy,matematika,sd_4,\"3\",\"4\",\"5\",\"6\",B,\"2 + 2 = 4\",\"aritmatika,penjumlahan\"\n";
        $csv .= "\"Sebutkan 3 contoh benda padat!\",essay,medium,ipa,sd_5,,,,,,\"Batu, kayu, besi, dll.\",\"benda padat\"\n";

        $this->output
            ->set_status_header(200)
            ->set_content_type('text/csv')
            ->set_header('Content-Disposition: attachment; filename="template_bank_soal.csv"')
            ->set_output($csv);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

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
        ];

        $options = [];
        $raw_options = (array) $this->input->post('options');
        foreach ($raw_options as $idx => $text) {
            $options[(int) $idx] = (string) $text;
        }

        $tags_raw = $this->input->post('tags', true);
        $tags = $tags_raw ? array_filter(array_map('trim', explode(',', $tags_raw))) : [];

        return [$data, $options, $tags];
    }
}
