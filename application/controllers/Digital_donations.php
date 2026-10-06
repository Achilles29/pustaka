<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Digital_donations extends MY_Controller
{
	public function __construct(){parent::__construct();$this->load->model('Digital_donation_model');}
	public function index()
	{
		$this->require_permission('digital_donations.index','view');$filters=['q'=>$this->input->get('q',true),'status'=>$this->input->get('status',true),'type'=>$this->input->get('type',true)];$per=(int)$this->input->get('per_page',true);$per=in_array($per,[10,25,50,100],true)?$per:25;$page=max(1,(int)$this->input->get('page',true));$total=$this->Digital_donation_model->count_admin($filters);$pages=max(1,(int)ceil($total/$per));$page=min($page,$pages);
		$this->render('digital_donations/index',['title'=>'Donasi Digital','stats'=>$this->Digital_donation_model->stats(),'items'=>$this->Digital_donation_model->get_admin($filters,$per,($page-1)*$per),'filters'=>array_merge($filters,['per_page'=>$per]),'pagination'=>['total'=>$total,'pages'=>$pages,'page'=>$page,'offset'=>($page-1)*$per,'per_page'=>$per],'can_review'=>$this->can('digital_donations.index','approve'),'can_create_catalog'=>$this->can('catalog.index','create')]);
	}
	public function review($id)
	{
		if (strtoupper((string) $this->input->method(true)) !== 'POST') { show_error('Metode pengiriman tidak diizinkan.', 405); return; }
		$this->require_permission('digital_donations.index','approve');$before=$this->Digital_donation_model->find((int)$id);if(!$before){show_404();return;}$status=$this->input->post('status',true);try{$result=$this->Digital_donation_model->review((int)$id,$status,$this->input->post('admin_note',true),(int)($this->current_user['id']??0));$this->audit_event('digital_donation.review','digital_donation_submissions',(int)$id,$before,$result);$this->session->set_flashdata('success','Status donasi diperbarui.');if($status==='accepted'&&$this->can('catalog.index','create')){redirect('digital-donations/catalog/'.(int)$id);return;}}catch(Throwable $e){$this->session->set_flashdata('error',$e->getMessage());}redirect('digital-donations');
	}
	public function file($id)
	{
		$this->require_permission('digital_donations.index','view');$item=$this->Digital_donation_model->find((int)$id);if(!$item||empty($item['file_path'])){show_404();return;}$base=realpath(FCPATH.'storage/digital-donations');$file=realpath(FCPATH.str_replace(['/','\\'],DIRECTORY_SEPARATOR,$item['file_path']));if(!$base||!$file||strpos($file,$base.DIRECTORY_SEPARATOR)!==0||!is_file($file)){show_404();return;}$this->audit_event('digital_donation.file_view','digital_donation_submissions',(int)$id,null,['file'=>$item['file_original_name']]);header('X-Content-Type-Options: nosniff');header('Content-Type: '.($item['file_mime_type']?:'application/octet-stream'));header('Content-Disposition: inline; filename="'.preg_replace('/[^A-Za-z0-9._-]/','_',(string)$item['file_original_name']).'"');header('Content-Length: '.filesize($file));readfile($file);exit;
	}
}
