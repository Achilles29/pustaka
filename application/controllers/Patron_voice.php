<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Patron_voice extends CI_Controller
{
	public function __construct() { parent::__construct(); $this->load->model('Patron_feedback_model'); }
	public function form()
	{
		$old=(array)$this->session->flashdata('patron_feedback_old');
		$type=trim((string)$this->input->get('type',true));
		if(empty($old['feedback_type'])&&in_array($type,['service_review','book_request','digital_content','facility','feature','complaint','other'],true))$old['feedback_type']=$type;
		$this->load->view('patron_feedback/form',['title'=>'Suara Pemustaka','user'=>(array)$this->session->userdata('auth_user'),'old'=>$old,'error'=>$this->session->flashdata('patron_feedback_error'),'contact_links'=>$this->Patron_feedback_model->contact_links()]);
	}
	public function submit()
	{
		if (strtoupper((string)$this->input->method(true)) !== 'POST') { show_error('Metode pengiriman tidak diizinkan.',405); return; }
		if (trim((string)$this->input->post('website',true)) !== '') { show_error('Pengiriman tidak dapat diproses.',400); return; }
		try { $user=(array)$this->session->userdata('auth_user'); $result=$this->Patron_feedback_model->submit($this->input->post(null,true),(int)($user['id']??0)); redirect('suara-pemustaka/status/'.rawurlencode($result['token'])); }
		catch(Throwable $e) { $this->session->set_flashdata('patron_feedback_error',$e->getMessage()); $this->session->set_flashdata('patron_feedback_old',$this->input->post(null,true)); redirect('suara-pemustaka'); }
	}
	public function status($token) { $item=$this->Patron_feedback_model->find_by_token($token); if(!$item){show_404();return;} $this->load->view('patron_feedback/status',['title'=>'Status Suara Pemustaka','item'=>$item]); }
}
