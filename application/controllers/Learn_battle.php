<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Learn_battle — admin: kelola pool soal Mode Battle + pantau ronde.
 */
class Learn_battle extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Learn_battle_model');
        $this->load->model('Quiz_bank_model');
        $this->load->model('Quiz_config_model');
    }

    public function index()
    {
        $this->require_permission('learn_battle.index', 'view');
        $this->render('learn/battle/index', [
            'title'       => 'Mode Battle — Pool Soal',
            'active_menu' => 'learn_battle',
            'questions'   => $this->Learn_battle_model->get_questions(),
            'stats'       => $this->Learn_battle_model->stats(),
            'rooms'       => $this->Learn_battle_model->recent_rooms(15),
            'sessions'    => $this->Learn_battle_model->get_sessions(),
            'can_create'  => $this->can('learn_battle.index', 'create'),
            'can_edit'    => $this->can('learn_battle.index', 'edit'),
            'can_delete'  => $this->can('learn_battle.index', 'delete'),
        ]);
    }

    public function store()
    {
        $this->require_permission('learn_battle.index', 'create');
        if (! $this->_valid()) { redirect('learn-battle'); return; }
        $this->Learn_battle_model->create_question($this->_input());
        $this->session->set_flashdata('success', 'Soal battle ditambahkan.');
        redirect('learn-battle');
    }

    public function update($id)
    {
        $this->require_permission('learn_battle.index', 'edit');
        if (! $this->Learn_battle_model->get_question($id)) { show_404(); return; }
        if (! $this->_valid()) { redirect('learn-battle'); return; }
        $this->Learn_battle_model->update_question((int) $id, $this->_input());
        $this->session->set_flashdata('success', 'Soal battle diperbarui.');
        redirect('learn-battle');
    }

    public function delete($id)
    {
        $this->require_permission('learn_battle.index', 'delete');
        if (! $this->Learn_battle_model->get_question($id)) { show_404(); return; }
        $this->Learn_battle_model->delete_question((int) $id);
        $this->session->set_flashdata('success', 'Soal battle dihapus.');
        redirect('learn-battle');
    }

    public function session_create(){ $this->require_permission('learn_battle.index','create');$this->render('learn/battle/session_form',['title'=>'Buat Sesi Battle','session'=>null,'action'=>'learn-battle/sessions/store']); }
    public function session_store(){ $this->require_permission('learn_battle.index','create');try{if(trim((string)$this->input->post('title',true))==='')throw new RuntimeException('Judul sesi wajib diisi.');$id=$this->Learn_battle_model->save_session(null,$this->input->post(),(int)$this->current_user['id']);redirect('learn-battle/sessions/questions/'.$id);}catch(Throwable $e){$this->session->set_flashdata('error',$e->getMessage());redirect('learn-battle/sessions/create');} }
    public function session_edit($id){$this->require_permission('learn_battle.index','edit');$s=$this->Learn_battle_model->get_session($id);if(!$s){show_404();return;}$this->render('learn/battle/session_form',['title'=>'Edit Sesi Battle','session'=>$s,'action'=>'learn-battle/sessions/update/'.$id]);}
    public function session_update($id){$this->require_permission('learn_battle.index','edit');$this->Learn_battle_model->save_session($id,$this->input->post(),(int)$this->current_user['id']);$this->session->set_flashdata('success','Sesi battle diperbarui.');redirect('learn-battle');}
    public function session_questions($id){$this->require_permission('learn_battle.index','view');$s=$this->Learn_battle_model->get_session($id);if(!$s){show_404();return;}$this->render('learn/battle/session_questions',['title'=>'Soal — '.$s['title'],'session'=>$s,'selected'=>$this->Learn_battle_model->get_session_questions($id),'questions'=>$this->Learn_battle_model->get_questions(true),'subjects'=>$this->Quiz_config_model->get_subjects(true),'grades'=>$this->Quiz_config_model->get_grade_levels(true),'can_edit'=>$this->can('learn_battle.index','edit')]);}
    public function session_questions_save($id){$this->require_permission('learn_battle.index','edit');$this->Learn_battle_model->set_session_questions($id,(array)$this->input->post('question_ids'));$this->session->set_flashdata('success','Pilihan soal sesi disimpan.');redirect('learn-battle/sessions/questions/'.$id);}
    public function session_import($id)
    {
        $this->require_permission('learn_battle.index', 'create');
        $target = 'learn-battle/sessions/questions/' . (int) $id;
        if (! $this->Learn_battle_model->get_session($id)) { show_404(); return; }
        if (empty($_FILES['import_file']['name']) || (int) $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            $this->session->set_flashdata('error', 'Pilih file CSV, TXT, atau Excel (.xlsx) yang valid.');
            redirect($target); return;
        }

        $ext = strtolower(pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION));
        if (! in_array($ext, ['csv', 'txt', 'xlsx'], true)) {
            $this->session->set_flashdata('error', 'Format tidak didukung. Gunakan CSV, TXT, atau Excel (.xlsx).');
            redirect($target); return;
        }
        if ((int) $_FILES['import_file']['size'] > 5 * 1024 * 1024) {
            $this->session->set_flashdata('error', 'Ukuran file maksimal 5 MB.');
            redirect($target); return;
        }

        try {
            $analysis=$this->Quiz_bank_model->analyze_import($_FILES['import_file']['tmp_name'],$ext,(int)$this->input->post('subject_id')?:null,(int)$this->input->post('grade_level_id')?:null);$ready=0;$essay=0;$unsupported=0;
            foreach($analysis['questions'] as &$q){$q['battle_issues']=[];if(($q['status']??'')==='ok'&&($q['type']??'')!=='multiple_choice'){$q['battle_issues'][]='Soal essay tidak didukung Mode Battle.';$essay++;}$has_extra=false;foreach(array_slice((array)($q['options']??[]),4) as $opt)if(trim((string)(is_array($opt)?($opt['text']??''):$opt))!=='')$has_extra=true;if(($q['status']??'')==='ok'&&($q['type']??'')==='multiple_choice'&&((int)($q['correct_index']??-1)>3||!isset($q['options'][(int)($q['correct_index']??-1)])||$has_extra)){$q['battle_issues'][]='Kunci jawaban E atau pilihan di luar A–D tidak didukung.';$unsupported++;}$q['battle_ready']=($q['status']??'')==='ok'&&empty($q['battle_issues']);if($q['battle_ready'])$ready++;}unset($q);
            $this->session->set_userdata('battle_import_'.$id,['questions'=>$analysis['questions'],'filename'=>$_FILES['import_file']['name'],'format'=>$ext,'session_id'=>(int)$id]);
            $this->render('learn/battle/import_preview',['title'=>'Analisa Import Battle','session'=>$this->Learn_battle_model->get_session($id),'questions'=>$analysis['questions'],'summary'=>['total'=>count($analysis['questions']),'ready'=>$ready,'invalid'=>count($analysis['questions'])-$ready,'essay'=>$essay,'unsupported'=>$unsupported],'filename'=>$_FILES['import_file']['name'],'format'=>$ext]);return;
        } catch (Throwable $e) {
            $this->session->set_flashdata('error', 'Import gagal: ' . $e->getMessage());
        }
        redirect($target);
    }

    public function session_import_commit($id)
    {
        $this->require_permission('learn_battle.index','create');$session=$this->Learn_battle_model->get_session($id);if(!$session){show_404();return;}$key='battle_import_'.(int)$id;$data=$this->session->userdata($key);if(empty($data['questions'])||(int)($data['session_id']??0)!==(int)$id){$this->session->set_flashdata('error','Data analisa sudah tidak tersedia. Silakan unggah dan analisa ulang.');redirect('learn-battle/sessions/questions/'.$id);return;}
        try{$result=$this->Learn_battle_model->import_normalized_questions($id,$data['questions']);$this->session->unset_userdata($key);$this->audit_event('learn_battle.session_import','learn_battle_sessions',(int)$id,null,$result);$message="Import selesai: {$result['imported']} soal masuk, {$result['skipped']} dilewati.";if($result['essay'])$message.=" {$result['essay']} essay tidak dimasukkan.";if($result['unsupported_options'])$message.=" {$result['unsupported_options']} soal di luar A–D dilewati.";$this->session->set_flashdata($result['imported']?'success':'error',$message);}catch(Throwable $e){$this->session->set_flashdata('error','Import gagal: '.$e->getMessage());}redirect('learn-battle/sessions/questions/'.$id);
    }

    public function template($format = 'csv')
    {
        $this->require_permission('learn_battle.index', 'view');
        $format = in_array($format, ['csv', 'txt', 'xlsx'], true) ? $format : 'csv';
        redirect('quiz-bank/template/' . $format);
    }

    private function _valid()
    {
        if (trim((string) $this->input->post('question', true)) === ''
            || trim((string) $this->input->post('option_a', true)) === ''
            || trim((string) $this->input->post('option_b', true)) === '') {
            $this->session->set_flashdata('error', 'Pertanyaan dan opsi A & B wajib diisi.');
            return false;
        }
        return true;
    }

    private function _input()
    {
        return [
            'question'       => trim((string) $this->input->post('question', true)),
            'option_a'       => trim((string) $this->input->post('option_a', true)),
            'option_b'       => trim((string) $this->input->post('option_b', true)),
            'option_c'       => $this->input->post('option_c', true),
            'option_d'       => $this->input->post('option_d', true),
            'correct_option' => (int) $this->input->post('correct_option', true),
            'category'       => $this->input->post('category', true),
            'is_active'      => $this->input->post('is_active') !== null ? 1 : 0,
        ];
    }
}
