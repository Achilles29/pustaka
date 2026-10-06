<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/** Public forms disclose no member search/list and never approve their own submissions. */
class Library_public_services extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();$this->load->model('Library_services_model','services');$this->load->model('Library_network_model','network');$this->db->db_debug=false;
        $this->output->set_header('Cache-Control: no-store, private')->set_header('Referrer-Policy: no-referrer')->set_header('X-Robots-Tag: noindex, nofollow');
        if(!$this->db->table_exists('network_registrations')){show_404();exit;}
        if(!$this->session->userdata('public_service_csrf'))$this->session->set_userdata('public_service_csrf',bin2hex(random_bytes(32)));
    }
    private function post($bucket,$limit)
    {
        $token=$this->input->post('public_service_csrf',false);
        if(!is_string($token)||!hash_equals((string)$this->session->userdata('public_service_csrf'),$token)){show_error('Formulir kedaluwarsa. Buka kembali formulir.',403);exit;}
        $this->session->set_userdata('public_service_csrf',bin2hex(random_bytes(32)));
        $input=(array)$this->input->post(null,false);foreach($input as $v)if(!is_scalar($v)&&$v!==null)throw new RuntimeException('Isian formulir tidak valid.');
        $this->services->rate_limit($bucket.'|'.$this->input->ip_address().'|'.$this->config->item('encryption_key'),$limit);
        if(!empty($input['website']))throw new RuntimeException('Formulir belum dapat diterima.');return $input;
    }
    private function local_scope($library)
    {
        $user=(array)$this->session->userdata('auth_user');
        if(!empty($user['is_library_admin'])&&(int)$user['library_id']!==(int)$library){show_error('Akun pengelola ini hanya dapat membuka layanan perpustakaannya sendiri.',403);exit;}
    }
    public function register($id)
    {
        try{$library=$this->services->library((int)$id);}catch(Throwable $e){show_404();return;}
        $this->local_scope($library['id']);
        $error=null;
        if($this->input->method(true)==='POST'){
            try{$input=$this->post('registration',15);if(($input['consent']??'')!=='1')throw new RuntimeException('Persetujuan pengolahan data diperlukan.');$this->services->register((int)$id,$input);$this->session->set_flashdata('public_service_success','Permohonan diterima untuk diperiksa petugas perpustakaan. Hubungi perpustakaan untuk hasil verifikasi; ini belum merupakan kartu anggota.');redirect('jejaring/daftar/'.(int)$id);return;}
            catch(Throwable $e){$error=$e->getMessage();}
        }
        $this->load->view('library_services/public_form',['mode'=>'register','library'=>$library,'error'=>$error,'csrf'=>$this->session->userdata('public_service_csrf')]);
    }
    public function kiosk($token)
    {
        $kiosk=$this->services->kiosk($token);if(!$kiosk){show_error('Tautan buku tamu berakhir atau tidak aktif. Minta tautan baru kepada petugas.',404);return;}$error=null;
        $this->local_scope($kiosk['library_id']);
        if($this->input->method(true)==='POST'){
            try{$input=$this->post('kiosk:'.$kiosk['library_id'],300);$this->services->kiosk_visit($token,$input);$this->session->set_flashdata('public_service_success','Terima kasih. Kunjungan berhasil dicatat. Formulir siap untuk pengunjung berikutnya.');redirect('jejaring/tamu/'.$token);return;}
            catch(Throwable $e){$error=$e->getMessage();}
        }
        $this->load->view('library_services/public_form',['mode'=>'kiosk','library'=>['name'=>$kiosk['library_name']],'error'=>$error,'csrf'=>$this->session->userdata('public_service_csrf'),'purposes'=>$this->network->purposes()]);
    }
}
