<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Public discovery and one-time self-registration; ownership is self-declared. */
class Library_onboarding extends CI_Controller
{
    private function points()
    {
        $this->load->model('Library_model');
        $excluded=array_column($this->db->select('l.id')->from('libraries l')->join('library_subtypes s','s.id=l.library_subtype_id')->where('s.code','kabupaten')->get()->result_array(),'id');
        return array_values(array_filter($this->Library_model->public_map_payload(),function($p)use($excluded){return !in_array($p['id'],$excluded); }));
    }

    public function index()
    {
        if($this->input->method(true)!=='GET'){show_error('Metode tidak diizinkan.',405);return;}
        if(!$this->session->userdata('activation_csrf'))$this->session->set_userdata('activation_csrf',bin2hex(random_bytes(32)));
        $this->output->set_header('Cache-Control: private, no-store');
        $this->load->view('library_workspace/onboarding',['map_payload'=>$this->points(),'locatable'=>true,'select_library'=>true,'map_title'=>'Temukan perpustakaan Anda','activation_ready'=>$this->db->table_exists('library_self_registrations'),'activation_csrf'=>$this->session->userdata('activation_csrf')]);
    }

    public function claim()
    {
        $this->db->db_debug=false;
        if($this->input->method(true)!=='POST'){show_error('Metode tidak diizinkan.',405);return;}
        if($this->session->userdata('auth_user')){show_error('Keluar dari akun saat ini sebelum aktivasi akun lain.',403);return;}
        $token=$this->input->post('activation_csrf',false);$expected=$this->session->userdata('activation_csrf');
        if(!is_string($expected)||!is_string($token)||!hash_equals($expected,$token)){show_error('Formulir kedaluwarsa. Muat ulang.',403);return;}
        $this->session->set_userdata('activation_csrf',bin2hex(random_bytes(32)));
        $id=$this->input->post('library_id');
        if(!is_string($id)||!ctype_digit($id)||$this->input->post('ownership')!=='1'){show_error('Pilih perpustakaan dan konfirmasi sebagai pengelola resmi.',400);return;}
        try{
            $this->load->model('Library_services_model');$this->Library_services_model->rate_limit('activation.ip.'.$this->input->ip_address(),20);$this->Library_services_model->rate_limit('activation.library.'.$id,40);
            $this->load->model('Library_activation_model');$user=$this->Library_activation_model->register_self((int)$id,(array)$this->input->post(null,false));
            $stamp=hash('sha256',$user['password_hash']);unset($user['password_hash']);$this->load->model('Auth_model');$roles=$this->Auth_model->load_roles($user['id']);$user['is_library_admin']=true;$user['is_superadmin']=false;
            $this->session->sess_regenerate(true);$this->session->unset_userdata('activation_password_grant');$this->session->set_userdata(['auth_user'=>$user,'user_roles'=>$roles,'user_perms'=>$this->Auth_model->load_permissions($user['id']),'network_password_stamp'=>$stamp]);
            $this->session->set_flashdata('success','Aktivasi berhasil. Username Anda: '.$user['username'].'. Simpan username ini; gunakan password yang baru Anda buat untuk login berikutnya.');
            redirect('library-workspace');
        }catch(Throwable$e){$this->session->set_flashdata('activation_error',$e->getMessage());redirect('aktivasi-perpustakaan');}
    }

    public function search()
    {
        if($this->input->method(true)!=='GET'){show_error('Metode tidak diizinkan.',405);return;}
        $raw=$this->input->get('q');$q=is_scalar($raw)?mb_substr(trim((string)$raw),0,180):'';$rows=[];
        if(mb_strlen($q)>=2){
            $rows=$this->db->select('l.id,l.code,l.name,l.institution_name,l.address,l.district,l.village')->from('libraries l')->join('library_subtypes s','s.id=l.library_subtype_id','left')->where('l.status','active')->group_start()->where('s.code !=','kabupaten')->or_where('s.code IS NULL',null,false)->group_end()->group_start()->like('l.name',$q)->or_like('l.institution_name',$q)->or_like('l.code',$q)->group_end()->order_by('l.name')->limit(30)->get()->result_array();
        }
        $this->output->set_header('Cache-Control: no-store')->set_content_type('application/json')->set_output(json_encode(['results'=>$rows,'limit'=>30],JSON_HEX_TAG|JSON_HEX_AMP|JSON_INVALID_UTF8_SUBSTITUTE));
    }
}
