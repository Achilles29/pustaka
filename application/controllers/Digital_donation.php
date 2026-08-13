<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Digital_donation extends CI_Controller
{
	public function __construct() { parent::__construct(); $this->load->model('Digital_donation_model'); }
	public function form()
	{
		$user=(array)$this->session->userdata('auth_user');
		$this->load->view('digital_donations/form',['title'=>'Donasi Koleksi Digital','user'=>$user,'old'=>(array)$this->session->flashdata('digital_donation_old'),'error'=>$this->session->flashdata('digital_donation_error')]);
	}
	public function submit()
	{
		if (strtoupper((string) $this->input->method(true)) !== 'POST') { show_error('Metode pengiriman tidak diizinkan.', 405); return; }
		if($this->post_too_large()){ $this->session->set_flashdata('digital_donation_error','Pengiriman gagal karena total lampiran melebihi batas server 200 MB. Unggah berkas lebih kecil atau gunakan tautan penyimpanan eksternal.'); redirect('donasi-digital'); return; }
		if(trim((string)$this->input->post('website',true))!==''){ show_error('Pengiriman tidak dapat diproses.',400); return; }
		try {
			$user=(array)$this->session->userdata('auth_user');
			$result=$this->Digital_donation_model->create_submission($this->input->post(null,true),$_FILES['donation_file']??[],(int)($user['id']??0));
			redirect('donasi-digital/status/'.rawurlencode($result['token']));
		} catch(Throwable $e) { $this->session->set_flashdata('digital_donation_error',$e->getMessage());$this->session->set_flashdata('digital_donation_old',$this->input->post(null,true));redirect('donasi-digital'); }
	}
	public function status($token)
	{
		$item=$this->Digital_donation_model->find_by_token((string)$token);if(!$item){show_404();return;}$this->load->view('digital_donations/status',['title'=>'Status Donasi Digital','item'=>$item]);
	}
	private function post_too_large(){return (int)($_SERVER['CONTENT_LENGTH']??0)>209715200;}
}
