<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Learn_english_course extends MY_Controller
{
    public function __construct(){parent::__construct();$this->load->model('English_course_model');}
    public function index(){$this->require_permission('learn_english_course.index','view');$this->render('learn/english_course/index',['title'=>'Kelas Bahasa Inggris','levels'=>$this->English_course_model->levels(0,true),'stats'=>$this->English_course_model->admin_stats(),'learners'=>$this->English_course_model->learners(),'settings'=>$this->English_course_model->settings(),'can_edit'=>$this->can('learn_english_course.index','edit')]);}
    public function settings(){$this->require_permission('learn_english_course.index','edit');$data=['course_title'=>trim((string)$this->input->post('course_title',true)),'tts_voice'=>(string)$this->input->post('tts_voice',true),'tts_rate'=>(string)max(.5,min(1.2,(float)$this->input->post('tts_rate',true))),'show_translation'=>$this->input->post('show_translation')?'1':'0','require_level_test'=>$this->input->post('require_level_test')?'1':'0'];$this->English_course_model->save_settings($data);$this->audit_event('english_course.settings','english_course_settings',null,null,$data);$this->session->set_flashdata('success','Pengaturan Kelas Bahasa Inggris disimpan.');redirect('learn-english-course');}
    public function toggle($type,$id){$this->require_permission('learn_english_course.index','edit');if(!in_array($type,['level','unit','lesson'],true)||!$this->English_course_model->toggle($type,(int)$id)){show_404();return;}$this->session->set_flashdata('success','Status konten diperbarui.');redirect('learn-english-course');}
}
