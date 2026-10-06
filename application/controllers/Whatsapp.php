<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Whatsapp extends MY_Controller
{
	public function __construct(){parent::__construct();$this->load->model('Whatsapp_model');}
	public function index(){
		$this->require_permission('wa.dashboard','view');$session=$this->Whatsapp_model->session();$this->render('wa/dashboard',['title'=>'WA Center','session'=>$session,'stats'=>$this->Whatsapp_model->stats(),'recent'=>$this->db->order_by('created_at','DESC')->limit(12)->get('wa_outbox')->result_array()]);
	}
	public function templates(){
		$this->require_permission('wa.templates','view');
		if($this->input->method()==='post'){
			$this->require_permission('wa.templates','edit');$id=(int)$this->input->post('id');$data=['template_code'=>trim($this->input->post('template_code',true)),'name'=>trim($this->input->post('name',true)),'category'=>$this->input->post('category',true),'body'=>$this->input->post('body',false),'is_active'=>$this->input->post('is_active')?1:0];
			if(!$data['template_code']||!$data['name']||!trim($data['body'])){$this->session->set_flashdata('error','Kode, nama, dan isi pesan wajib diisi.');}
			else {try{if($id){$existing=$this->db->where('id',$id)->get('wa_template')->row_array();if(!$existing)throw new RuntimeException('Template tidak ditemukan.');if($this->is_system_template($existing['template_code']))$data['template_code']=$existing['template_code'];$this->db->where('id',$id)->update('wa_template',$data);$this->session->set_flashdata('success','Template berhasil diperbarui.');}else{$data['created_by']=$this->current_user['id'];$this->db->insert('wa_template',$data);$this->session->set_flashdata('success','Template baru berhasil dibuat.');}}catch(Throwable $e){$this->session->set_flashdata('error','Template gagal disimpan: '.$e->getMessage());}}
			redirect('wa/templates');return;
		}
		$this->render('wa/templates',['title'=>'Template Pesan WA','templates'=>$this->Whatsapp_model->templates(),'can_edit'=>$this->can('wa.templates','edit'),'can_delete'=>$this->can('wa.templates','delete')]);
	}
	public function template_action($id){
		$this->require_permission('wa.templates','view');
		$action=$this->input->post('action',true);$template=$this->db->where('id',(int)$id)->get('wa_template')->row_array();
		if(!$template){$this->session->set_flashdata('error','Template tidak ditemukan.');redirect('wa/templates');return;}
		try{
			if($action==='toggle'){$this->require_permission('wa.templates','edit');$next=empty($template['is_active'])?1:0;$this->db->where('id',(int)$id)->update('wa_template',['is_active'=>$next]);$this->session->set_flashdata('success',$next?'Template diaktifkan.':'Template dinonaktifkan. Pesan baru tidak lagi memakai template ini.');}
			elseif($action==='delete'){$this->require_permission('wa.templates','delete');if($this->is_system_template($template['template_code']))throw new RuntimeException('Template sistem tidak dapat dihapus agar otomasi tetap aman. Nonaktifkan bila tidak ingin digunakan.');$used=(int)$this->db->where('template_id',(int)$id)->count_all_results('wa_outbox');if($used>0)throw new RuntimeException('Template sudah dipakai oleh '.$used.' pesan dan tidak dapat dihapus agar riwayat tetap utuh. Nonaktifkan saja.');$this->db->where('id',(int)$id)->delete('wa_template');$this->session->set_flashdata('success','Template berhasil dihapus.');}
			else throw new RuntimeException('Aksi template tidak valid.');
		}catch(Throwable $e){$this->session->set_flashdata('error',$e->getMessage());}
		redirect('wa/templates');
	}
	private function is_system_template($code){return in_array((string)$code,['member_registration_received','member_registration_approved','loan_request_status','loan_due_reminder','loan_overdue'],true);}
	public function outbox(){
		$this->require_permission('wa.outbox','view');
		if($this->input->method()==='post'){$this->require_permission('wa.outbox','create');$phone=$this->input->post('phone',true);$message=$this->input->post('message',false);$id=$this->Whatsapp_model->queue('MANUAL',$phone,$message,[] ,null,null,null,$this->current_user['id']);$this->session->set_flashdata($id?'success':'error',$id?'Pesan dimasukkan ke antrian WA.':'Nomor WA atau pesan tidak valid.');redirect('wa/outbox');return;}
		$this->render('wa/outbox',['title'=>'Antrian & Log WA','rows'=>$this->db->order_by('created_at','DESC')->limit(200)->get('wa_outbox')->result_array(),'can_create'=>$this->can('wa.outbox','create')]);
	}
	public function settings(){
		$this->require_permission('wa.settings','view');
		if($this->input->method()==='post'){
			$this->require_permission('wa.settings','edit');
			if($this->input->post('form_mode',true)==='automation'){
				$this->Whatsapp_model->save_automation_settings($this->input->post(null,true),(int)($this->current_user['id']??0));
				$this->session->set_flashdata('success','Aturan notifikasi otomatis WA diperbarui.');
			}else{$data=['bot_api_url'=>rtrim(trim($this->input->post('bot_api_url',true)),'/'),'bot_api_token'=>trim($this->input->post('bot_api_token',true)),'node_path'=>trim($this->input->post('node_path',true))?:null];$this->db->where('id',1)->update('wa_session',$data);$this->session->set_flashdata('success','Pengaturan WA Engine disimpan.');}
			redirect('wa/settings');return;
		}
		$this->render('wa/settings',['title'=>'Pengaturan WA','session'=>$this->Whatsapp_model->session(),'automation'=>$this->Whatsapp_model->automation_settings(),'can_edit'=>$this->can('wa.settings','edit')]);
	}
	public function api_status(){$this->require_permission('wa.dashboard','view');$this->json_response($this->call_engine('/internal/status'));}
	public function api_qr(){$this->require_permission('wa.settings','view');$this->json_response($this->call_engine('/internal/qr'));}
	public function api_control(){
		$this->require_permission('wa.settings','edit');
		$payload=json_decode($this->input->raw_input_stream,true);$action=is_array($payload)?($payload['action']??''):$this->input->post('action',true);
		if(!in_array($action,['enable','disable','unlink'],true)){$this->json_response(['ok'=>false,'message'=>'Aksi koneksi WA tidak valid.']);return;}
		$this->json_response($this->call_engine('/internal/control','POST',['action'=>$action]));
	}
	private function call_engine($path,$method='GET',$payload=null){
		$s=$this->Whatsapp_model->session();$base=rtrim($s['bot_api_url']??'','/');$url=$base.$path.'?token='.rawurlencode($s['bot_api_token']??'');
		if(!$base||!function_exists('curl_init'))return ['ok'=>false,'message'=>'cURL atau URL WA Engine belum tersedia.'];
		$c=curl_init($url);$options=[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>2,CURLOPT_TIMEOUT=>6];
		if($method==='POST'){$options[CURLOPT_POST]=true;$options[CURLOPT_POSTFIELDS]=json_encode($payload?:[]);$options[CURLOPT_HTTPHEADER]=['Content-Type: application/json'];}
		curl_setopt_array($c,$options);$raw=curl_exec($c);$err=curl_error($c);$code=(int)curl_getinfo($c,CURLINFO_HTTP_CODE);curl_close($c);$data=json_decode((string)$raw,true);
		return is_array($data)?$data:['ok'=>false,'message'=>$err?:($code?'WA Engine mengembalikan respons yang tidak valid.':'WA Engine tidak merespons.')];
	}
}
