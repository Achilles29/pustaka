<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Library_activation extends MY_Controller
{
    public function __construct(){
        parent::__construct();
        if(!empty($this->current_user['is_library_admin'])||$this->current_library_scope_id()){show_error('Khusus admin kabupaten.',403);exit;}
        $this->load->model('Auth_model');
        $user=$this->db->select('status,library_id')->where('id',$this->current_user['id'])->get('auth_user')->row_array();
        if(!$user||$user['status']!=='active'||$user['library_id']){show_error('Akun admin kabupaten tidak aktif atau penugasan berubah.',403);exit;}
        $this->user_roles=$this->Auth_model->load_roles($this->current_user['id']);
        $this->current_user['is_superadmin']=in_array('SUPERADMIN',array_column($this->user_roles,'code'),true);
        $this->user_perms=$this->Auth_model->load_permissions($this->current_user['id']);
        $this->session->set_userdata(['auth_user'=>$this->current_user,'user_roles'=>$this->user_roles,'user_perms'=>$this->user_perms]);
        $this->require_permission('library.activation','view');
        $this->load->model('Library_activation_model','activation');$this->db->db_debug=false;
        if(!$this->db->table_exists('library_activation_codes')){show_error('Aktivasi belum siap.',503);exit;}
        if(!$this->session->userdata('activation_admin_csrf'))$this->session->set_userdata('activation_admin_csrf',bin2hex(random_bytes(32)));
        $this->output->set_header('Cache-Control: private, no-store')->set_header('Referrer-Policy: no-referrer');
    }
    private function post($action){$this->require_permission('library.activation',$action);if($this->input->method(true)!=='POST'){show_error('Gunakan formulir.',405);exit;}$t=$this->input->post('activation_admin_csrf');if(!is_string($t)||!hash_equals((string)$this->session->userdata('activation_admin_csrf'),$t)){show_error('Formulir kedaluwarsa.',403);exit;}$this->session->set_userdata('activation_admin_csrf',bin2hex(random_bytes(32)));$id=$this->input->post('library_id');if(!is_string($id)||!ctype_digit($id)){show_error('ID tidak valid.',400);exit;}return(int)$id;}
    public function index(){
        $q=$this->input->get('q');$q=is_string($q)?mb_substr(trim($q),0,180):'';$page=max(1,(int)$this->input->get('page'));
        $build=function()use($q){$this->db->from('libraries l')->join('library_subtypes s','s.id=l.library_subtype_id','left')->where('l.status','active')->group_start()->where('s.code !=','kabupaten')->or_where('s.code IS NULL',null,false)->group_end();if($q!=='')$this->db->group_start()->like('l.name',$q)->or_like('l.code',$q)->or_like('l.institution_name',$q)->group_end();};$build();$total=$this->db->count_all_results();$page=min($page,max(1,(int)ceil($total/30)));$build();$rows=$this->db->select('l.id,l.code,l.name,l.district,l.village')->order_by('l.name')->limit(30,($page-1)*30)->get()->result_array();
        foreach($rows as&$r){$r['claimed']=(bool)$this->db->where('library_id',$r['id'])->group_start()->where('last_login_at IS NOT NULL',null,false)->or_where('force_password_change',0)->group_end()->count_all_results('auth_user');$r['activation']=$this->db->select('issued_at,expires_at,claimed_at')->where('library_id',$r['id'])->get('library_activation_codes')->row_array();if(!empty($r['activation']['claimed_at']))$r['claimed']=true;
            $r['registration']=$this->db->table_exists('library_self_registrations')?$this->db->where('library_id',$r['id'])->get('library_self_registrations')->row_array():null;if($r['registration'])$r['claimed']=true;
            $r['accounts']=$this->db->select('id,username,full_name,status')->where('library_id',$r['id'])->order_by('id')->get('auth_user')->result_array();
        }unset($r);
        $this->render('library_workspace/activation_admin',['title'=>'Aktivasi & Moderasi Perpustakaan','rows'=>$rows,'q'=>$q,'page'=>$page,'pages'=>max(1,(int)ceil($total/30)),'total'=>$total,'csrf'=>$this->session->userdata('activation_admin_csrf'),'reset_result'=>$this->session->flashdata('activation_reset_result'),'can_moderate'=>$this->can('library.activation','edit')&&$this->can('auth.users.index','edit')]);
    }
    public function moderate(){
        $id=$this->post('edit');$this->require_permission('auth.users.index','edit');
        $user=$this->input->post('user_id');$action=$this->input->post('action');$note=$this->input->post('note',true);
        if(!is_string($user)||!ctype_digit($user)||!is_string($action)||!is_string($note)){show_error('Formulir tidak valid.',400);return;}
        try{$result=$this->activation->moderate_account($id,(int)$user,$action,(int)$this->current_user['id'],$note);if($action==='reset')$this->session->set_flashdata('activation_reset_result',$result);$this->session->set_flashdata('success',$action==='restore'?'Akun diaktifkan kembali. Lakukan reset password dan berikan kepada pengelola yang benar.':'Tindakan moderasi berhasil dicatat.');}catch(Throwable$e){$this->session->set_flashdata('error',$e->getMessage());}
        redirect('library-activation');
    }
    public function issue(){$this->post('create');show_error('Penerbitan kode sudah digantikan aktivasi mandiri. Gunakan halaman moderasi jika ada masalah akun.',410);}
    public function revoke(){$this->post('edit');show_error('Pembatalan kode sudah digantikan moderasi akun. Gunakan tangguhkan atau reset akun.',410);}
}
