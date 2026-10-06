<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Auth_model');
		$this->load->library('form_validation');
	}

	public function index()
	{
		if ($this->session->userdata('auth_user')) redirect($this->post_login_target());
		$this->load->view('auth/login',['title'=>'Login Pustaka Digital Rembang','error_msg'=>$this->session->flashdata('login_error')]);
		$html=$this->output->get_output();
		$link='<div class="text-center mt-3"><a href="'.base_url('auth/forgot_password').'" class="fw-semibold"><i class="ti ti-key me-1"></i>Lupa password?</a> <span class="text-secondary mx-1">·</span> <a href="'.base_url('auth/password_reset_status').'">Cek status reset</a></div>';
		$this->output->set_output(str_replace('</form>','</form>'.$link,$html));
	}

	public function forgot_password()
	{
		$this->load->view('game/password_reset',['title'=>'Reset Password','heading'=>'Lupa Password?','intro'=>'Masukkan identitas akun dan nomor WhatsApp yang sama dengan data member.','mode'=>'request','request'=>null,'error'=>$this->session->flashdata('reset_error'),'success'=>null]);
	}

	public function submit_password_reset()
	{
		if($this->input->method(true)!=='POST'){redirect('auth/forgot_password');return;}
		try{
			$this->load->model('Password_reset_model');
			$result=$this->Password_reset_model->create_request($this->input->post('identifier',true),$this->input->post('phone',true),$this->input->ip_address());
			$this->Auth_model->write_event('password_reset_requested',null,(string)$this->input->post('identifier',true));
			redirect('auth/password_reset_status/'.rawurlencode($result['status_token']));
		}catch(Throwable $e){$this->session->set_flashdata('reset_error',$e->getMessage());redirect('auth/forgot_password');}
	}

	public function password_reset_status($token='')
	{
		$request=null;$error=null;$this->load->model('Password_reset_model');
		try{
			if($token!=='')$request=$this->Password_reset_model->get_by_token($token);
			elseif($this->input->method(true)==='POST')$request=$this->Password_reset_model->find_status($this->input->post('request_code',true),$this->input->post('phone',true));
			if($token!==''&&!$request)$error='Permintaan reset tidak ditemukan.';
		}catch(Throwable $e){$error=$e->getMessage();}
		$this->load->view('game/password_reset',['title'=>'Status Reset Password','heading'=>'Status Reset Password','intro'=>'Gunakan kode permintaan dan nomor WhatsApp terdaftar untuk memantau pengiriman atau hasil verifikasi petugas.','mode'=>$request?'result':'lookup','request'=>$request,'error'=>$error,'success'=>null]);
	}

	public function reset_password($token='')
	{
		$this->load->model('Password_reset_model');$request=$this->Password_reset_model->get_reset_request($token);
		$error=$this->session->flashdata('reset_error');
		if(!$request||!empty($request['is_expired'])||!empty($request['is_completed']))$error=$error?:'Tautan reset tidak valid, sudah digunakan, atau kedaluwarsa.';
		$this->load->view('game/password_reset',['title'=>'Buat Password Baru','heading'=>'Buat Password Baru','intro'=>'Tentukan password pribadi Anda. Tautan ini hanya dapat digunakan satu kali.','mode'=>'set_password','request'=>$request,'reset_token'=>$token,'error'=>$error,'success'=>null]);
	}

	public function complete_password_reset()
	{
		if($this->input->method(true)!=='POST'){redirect('login');return;}$token=(string)$this->input->post('token',true);
		try{$this->load->model('Password_reset_model');$this->Password_reset_model->complete_reset($token,$this->input->post('password',false),$this->input->post('password_confirmation',false));$this->session->set_flashdata('login_error','Password baru berhasil disimpan. Silakan masuk.');redirect('login');}
		catch(Throwable $e){$this->session->set_flashdata('reset_error',$e->getMessage());redirect('auth/reset_password/'.rawurlencode($token));}
	}

	public function do_login()
	{
		$this->form_validation->set_rules('identifier','Username/Email','required|trim|min_length[3]|max_length[180]');
		$this->form_validation->set_rules('password','Password','required|min_length[6]|max_length[72]');
		if($this->form_validation->run()===false){$this->session->set_flashdata('login_error',validation_errors('<div>','</div>'));redirect('login');}
		$identifier=$this->input->post('identifier',true);$password=$this->input->post('password',false);$user=$this->Auth_model->attempt_login($identifier,$password);
		if(!$user){$this->Auth_model->write_event('login_failed',null,$identifier);$this->session->set_flashdata('login_error','Username/email atau password tidak sesuai.');redirect('login');}
		$roles=$this->Auth_model->load_roles((int)$user['id']);$permissions=$this->Auth_model->load_permissions((int)$user['id']);$user['is_superadmin']=isset($permissions['__superadmin__']);
		$this->session->sess_regenerate(true);$this->session->set_userdata(['auth_user'=>$user,'user_roles'=>$roles,'user_perms'=>$permissions]);$this->Auth_model->write_event('login_success',(int)$user['id'],$identifier);
		$redirect_to=(string)$this->session->flashdata('redirect_after_login');$target=$this->post_login_target($roles);
		if(in_array('LIBRARY_ADMIN',array_column($roles,'code'),true)){$redirect_to='';$target=!empty($user['force_password_change'])?'library-workspace/account':'library-workspace';$credentials=$this->Auth_model->get_user_with_password((int)$user['id']);$this->session->set_userdata('network_password_stamp',hash('sha256',$credentials['password_hash']));}
		if(!empty($user['force_password_change'])&&$target==='user/dashboard'){$target='user/account';$redirect_to='';}
		if($redirect_to!==''&&$this->is_safe_redirect($redirect_to))redirect($redirect_to);redirect($target);
	}

	public function logout()
	{
		$user=(array)$this->session->userdata('auth_user');if(!empty($user['id']))$this->Auth_model->write_event('logout',(int)$user['id'],$user['username']);$this->session->sess_destroy();redirect('login');
	}

	private function post_login_target(array $roles=null)
	{
		if($roles===null)$roles=(array)$this->session->userdata('user_roles');$role_codes=array_map(function($role){return $role['code'];},$roles);if(in_array('LIBRARY_ADMIN',$role_codes,true))return 'library-workspace';if(in_array('SUPERADMIN',$role_codes,true)||in_array('ADMIN',$role_codes,true))return 'admin';return 'user/dashboard';
	}

	private function is_safe_redirect($path)
	{
		$path=trim((string)$path);if($path===''||strpos($path,'://')!==false||strpos($path,'//')===0)return false;if($path==='login'||strpos($path,'auth/')===0||$path==='/')return false;return true;
	}
}
