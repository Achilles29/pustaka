<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Patron_feedback extends MY_Controller
{
	public function __construct(){parent::__construct();$this->load->model('Patron_feedback_model');}
	public function index(){ $this->require_permission('patron_feedback.index','view'); $filters=['q'=>$this->input->get('q',true),'status'=>$this->input->get('status',true),'type'=>$this->input->get('type',true)]; $per=(int)$this->input->get('per_page',true);$per=in_array($per,[10,25,50,100],true)?$per:25;$page=max(1,(int)$this->input->get('page',true));$total=$this->Patron_feedback_model->count_admin($filters);$pages=max(1,(int)ceil($total/$per));$page=min($page,$pages);$this->render('patron_feedback/index',['title'=>'Suara Pemustaka','stats'=>$this->Patron_feedback_model->stats(),'items'=>$this->Patron_feedback_model->list_admin($filters,$per,($page-1)*$per),'filters'=>array_merge($filters,['per_page'=>$per]),'pagination'=>compact('total','pages','page','per'),'can_review'=>$this->can('patron_feedback.index','approve')]); }
	public function review($id){if(strtoupper((string)$this->input->method(true))!=='POST'){show_error('Metode pengiriman tidak diizinkan.',405);return;}$this->require_permission('patron_feedback.index','approve');$before=$this->Patron_feedback_model->find($id);if(!$before){show_404();return;}try{$after=$this->Patron_feedback_model->review($id,$this->input->post('status',true),$this->input->post('admin_note',true),(int)($this->current_user['id']??0));$this->audit_event('patron_feedback.review','patron_feedback',(int)$id,$before,$after);$this->session->set_flashdata('success','Tindak lanjut suara pemustaka disimpan.');}catch(Throwable $e){$this->session->set_flashdata('error',$e->getMessage());}redirect('patron-feedback');}
}
