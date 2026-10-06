<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Library_types extends MY_Controller
{
    public function index(){
        if(!empty($this->current_user['is_library_admin'])||$this->current_library_scope_id()){show_error('Khusus admin kabupaten.',403);return;}
        $this->load->model('Auth_model');$this->user_perms=$this->Auth_model->load_permissions((int)$this->current_user['id']);$this->require_permission('library.types','view');$this->load->model('Library_type_model','taxonomy');$this->db->db_debug=false;
        if(!$this->session->userdata('taxonomy_csrf'))$this->session->set_userdata('taxonomy_csrf',bin2hex(random_bytes(32)));$error=null;
        if($this->input->method(true)==='POST'){
            $this->require_permission('library.types',$this->input->post('id')?($this->input->post('is_active')?'edit':'delete'):'create');$token=$this->input->post('taxonomy_csrf',false);if(!is_string($token)||!hash_equals($this->session->userdata('taxonomy_csrf'),$token)){show_error('Formulir kedaluwarsa.',403);return;}$this->session->set_userdata('taxonomy_csrf',bin2hex(random_bytes(32)));
            try{$this->taxonomy->save($this->input->post('entity'),(array)$this->input->post(null,false),(int)$this->current_user['id']);$this->session->set_flashdata('success','Master disimpan. Data lama tidak dihapus.');redirect('library-types');return;}catch(Throwable$e){$error=$e->getMessage();}
        }
        $types=$this->taxonomy->types();$subtypes=$this->taxonomy->subtypes();$entity=$this->input->get('entity')==='subtype'?'subtype':'type';$id=(int)$this->input->get('id');$selected=null;foreach($entity==='type'?$types:$subtypes as$r)if((int)$r['id']===$id)$selected=$r;
        $this->render('libraries/types',['title'=>'Master Jenis & Subjenis Perpustakaan','types'=>$types,'subtypes'=>$subtypes,'entity'=>$entity,'selected'=>$selected,'error'=>$error,'csrf'=>$this->session->userdata('taxonomy_csrf')]);
    }
}
