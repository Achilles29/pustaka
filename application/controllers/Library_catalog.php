<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Public local-library catalogs. Member, inventory and circulation identities stay private. */
class Library_catalog extends CI_Controller
{
    public function index()
    {
        $raw=$this->input->get('q',true);$q=is_scalar($raw)?mb_substr(trim((string)$raw),0,180):'';
        $library_id=max(0,(int)$this->input->get('library_id',true));$page=max(1,(int)$this->input->get('page'));$per=24;
        $libraries=$this->db->select('id,name')->where('status','active')->order_by('name')->get('libraries')->result_array();
        $rows=[];$total=0;
        if($this->db->table_exists('network_books')){
            $build=function()use($q,$library_id){$this->db->from('network_books b')->join('libraries l','l.id=b.library_id')->where('l.status','active')->where('b.status','published')->where('b.deleted_at IS NULL',null,false);if($library_id)$this->db->where('b.library_id',$library_id);if($q!=='')$this->db->group_start()->like('b.title',$q)->or_like('b.author',$q)->or_like('b.isbn',$q)->group_end();};
            $build();$total=(int)$this->db->count_all_results();$page=min($page,max(1,(int)ceil($total/$per)));$build();
            $rows=$this->db->select('b.id,b.title,b.author,b.isbn,b.format,l.name AS library_name')->order_by('b.title')->limit($per,($page-1)*$per)->get()->result_array();
        }
        $this->load->view('library_workspace/public_catalog',['title'=>'Katalog Jejaring Perpustakaan','libraries'=>$libraries,'library_id'=>$library_id,'q'=>$q,'rows'=>$rows,'total'=>$total,'page'=>$page,'pages'=>max(1,(int)ceil($total/$per)),'book'=>null]);
    }

    public function detail($id)
    {
        if(!$this->db->table_exists('network_books')){show_404();return;}
        $book=$this->db->select('b.id,b.library_id,b.title,b.author,b.publisher,b.publish_year,b.isbn,b.classification,b.format,b.description,b.digital_url,l.name AS library_name,l.address AS library_address')->from('network_books b')->join('libraries l','l.id=b.library_id')->where('b.id',(int)$id)->where('b.status','published')->where('b.deleted_at IS NULL',null,false)->where('l.status','active')->get()->row_array();
        if(!$book){show_404();return;}
        $book['available']=(int)$this->db->where('library_id',(int)$book['library_id'])->where('book_id',(int)$id)->where('status','available')->where('deleted_at IS NULL',null,false)->count_all_results('network_items');
        $this->load->view('library_workspace/public_catalog',['title'=>$book['title'],'book'=>$book]);
    }
}
