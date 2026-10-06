<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Patron_feedback extends MY_Controller
{
	public function __construct(){parent::__construct();$this->load->model('Patron_feedback_model');}
	public function contacts()
	{
		$this->require_permission('patron_feedback.index', 'edit');
		if ($this->input->method(true) !== 'POST') { show_error('Metode pengiriman tidak diizinkan.',405); return; }
		$token = $this->input->post('contacts_token');
		$expected = (string) $this->session->userdata('patron_contacts_token');
		if (!is_string($token) || $expected === '' || !hash_equals($expected, $token)) { show_error('Sesi formulir tidak valid. Muat ulang halaman pengaturan.',403); return; }
		try {
			$before = $this->Patron_feedback_model->contacts();
			$after = $this->Patron_feedback_model->save_contacts((array) $this->input->post(null, true));
			$this->audit_event('patron_feedback.contacts', 'patron_feedback_contacts', 1, $before, $after);
			$this->session->set_flashdata('success', 'Kontak publik berhasil diperbarui.');
		} catch (RuntimeException $e) {
			$this->session->set_flashdata('error', $e->getMessage());
			$this->session->set_flashdata('contacts_old', (array) $this->input->post(null, true));
		}
		redirect('patron-feedback');
	}
	public function index()
	{
		$this->require_permission('patron_feedback.index', 'view');
		$filters = ['q'=>$this->input->get('q',true),'status'=>$this->input->get('status',true),'type'=>$this->input->get('type',true)];
		$per = (int) $this->input->get('per_page',true);
		$per = in_array($per,[10,25,50,100],true) ? $per : 25;
		$page = max(1,(int)$this->input->get('page',true));
		$total = $this->Patron_feedback_model->count_admin($filters);
		$pages = max(1,(int)ceil($total/$per)); $page = min($page,$pages);
		$token = $this->session->userdata('patron_contacts_token');
		if (!$token) { $token = bin2hex(random_bytes(32)); $this->session->set_userdata('patron_contacts_token',$token); }
		$contacts = $this->Patron_feedback_model->contacts();
		$old = (array) $this->session->flashdata('contacts_old');
		foreach ($contacts as $key=>$value) if (isset($old[$key]) && is_scalar($old[$key])) $contacts[$key] = $old[$key];
		$this->render('patron_feedback/index', [
			'title'=>'Suara Pemustaka','stats'=>$this->Patron_feedback_model->stats(),
			'items'=>$this->Patron_feedback_model->list_admin($filters,$per,($page-1)*$per),
			'filters'=>array_merge($filters,['per_page'=>$per]),'pagination'=>compact('total','pages','page','per'),
			'can_review'=>$this->can('patron_feedback.index','approve'),
			'can_edit_contacts'=>$this->can('patron_feedback.index','edit'), 'contacts'=>$contacts, 'contacts_token'=>$token,
		]);
	}
	public function review($id){if(strtoupper((string)$this->input->method(true))!=='POST'){show_error('Metode pengiriman tidak diizinkan.',405);return;}$this->require_permission('patron_feedback.index','approve');$before=$this->Patron_feedback_model->find($id);if(!$before){show_404();return;}try{$after=$this->Patron_feedback_model->review($id,$this->input->post('status',true),$this->input->post('admin_note',true),(int)($this->current_user['id']??0));$this->audit_event('patron_feedback.review','patron_feedback',(int)$id,$before,$after);$this->session->set_flashdata('success','Tindak lanjut suara pemustaka disimpan.');}catch(Throwable $e){$this->session->set_flashdata('error',$e->getMessage());}redirect('patron-feedback');}
}
