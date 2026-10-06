<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Library_services extends MY_Controller
{
    private $scope=0,$source='legacy',$permission='library.services',$local=false,$library=null;
    public function __construct()
    {
        parent::__construct();$this->local=!empty($this->current_user['is_library_admin']);
        $this->permission=$this->local?'library.workspace':'library.services';$this->require_permission($this->permission,'view');
        if(!$this->local&&!$this->is_superadmin()&&!in_array('ADMIN',array_column($this->user_roles,'code'),true)){show_error('Pengelola perpustakaan diperlukan.',403);exit;}
        $requested=$this->input->get('library_id',true);$source=$this->input->get('source',true);
        if(($requested!==null&&(!is_string($requested)||!ctype_digit($requested)))||($source!==null&&!in_array($source,['legacy','network'],true))){show_error('Cakupan tidak valid.',400);exit;}
        if($this->local){$this->scope=(int)$this->current_user['library_id'];$this->source='network';if(($requested!==null&&(int)$requested!==$this->scope)||($source!==null&&$source!=='network')){show_error('Cakupan tidak boleh diubah.',403);exit;}}
        else{$assigned=$this->is_superadmin()?0:(int)$this->current_library_scope_id();if($assigned&&$requested!==null&&(int)$requested!==$assigned){show_error('Cakupan tidak boleh diubah.',403);exit;}$this->scope=$assigned?:((int)$requested);$this->source=$source?:'legacy';}
        $this->load->model('Library_services_model','services');$this->load->model('Library_network_model','network');$this->db->db_debug=false;
        if(!$this->db->table_exists('library_stock_sessions')){show_error('Modul layanan sedang dipasang. Coba kembali sesaat lagi.',503);exit;}
        if($this->scope){try{$this->library=$this->services->library($this->scope);}catch(Throwable $e){show_404();exit;}}
        if(!$this->session->userdata('service_csrf'))$this->session->set_userdata('service_csrf',bin2hex(random_bytes(32)));
    }
    private function url($path)
    {
        return base_url('library-services/'.$path).'?'.http_build_query(['library_id'=>$this->scope,'source'=>$this->source]);
    }
    private function selected($network=false)
    {
        if(!$this->library){show_error('Pilih perpustakaan terlebih dahulu melalui Stok Opname.',400);exit;}
        if($network&&$this->source!=='network'){show_error('Gunakan modul kabupaten yang sudah tersedia untuk data lama. Layanan ini khusus dataset jejaring.',400);exit;}
    }
    private function post($action='edit')
    {
        $this->require_permission($this->permission,$action);
        if($this->input->method(true)!=='POST'){show_error('Gunakan formulir tindakan.',405);exit;}
        $token=$this->input->post('service_csrf',false);if(!is_string($token)||!hash_equals((string)$this->session->userdata('service_csrf'),$token)){show_error('Formulir kedaluwarsa. Muat ulang halaman.',403);exit;}
        $this->session->set_userdata('service_csrf',bin2hex(random_bytes(32)));
        $data=(array)$this->input->post(null,false);foreach($data as $v)if(!is_scalar($v)&&$v!==null){show_error('Isian tidak valid.',400);exit;}return $data;
    }
    private function page($template,array $data=[])
    {
        $this->render('library_services/'.$template,$data+['title'=>'Layanan Perpustakaan','library'=>$this->library,'scope'=>$this->scope,'source'=>$this->source,'local'=>$this->local,'service_url'=>function($path){return $this->url($path);},'csrf'=>$this->session->userdata('service_csrf'),'may_create'=>$this->can($this->permission,'create'),'may_edit'=>$this->can($this->permission,'edit'),'may_export'=>$this->can($this->permission,'export')]);
    }
    private function notice(Throwable $e){$this->session->set_flashdata('error',$e->getMessage());}
    public function stock($id=0,$format='')
    {
        $id=(int)$id;
        if($this->input->method(true)==='POST'){
            if($format!==''){show_error('Ekspor hanya dapat dibuka dengan GET.',405);return;}
            $this->selected();$input=$this->post($id?'edit':'create');
            try{
                if(!$id)$id=$this->services->stock_create($this->scope,$this->source,$input['title']??'',(int)$this->current_user['id']);
                elseif(($input['action']??'')==='scan')$this->services->stock_scan($this->scope,$this->source,$id,$input['barcode']??'',$input['observed']??'',$input['note']??'',(int)$this->current_user['id']);
                else $this->services->stock_close($this->scope,$this->source,$id,$input['action']??'',$input['note']??'',(int)$this->current_user['id']);
                $this->session->set_flashdata('success','Pemeriksaan disimpan. Status inventaris asli tidak diubah.');
            }catch(Throwable $e){$this->notice($e);}redirect($this->url('stock'.($id?'/'.$id:'')));return;
        }
        if($id){
            $this->selected();$session=$this->services->stock($this->scope,$this->source,$id);if(!$session){show_404();return;}$entries=$this->services->stock_entries($this->scope,$this->source,$id);
            if($format!==''){
                $this->require_permission($this->permission,'export');
                if($format==='print'){$this->load->view('library_services/stock_print',compact('session','entries')+['library'=>$this->library]);return;}
                if(!in_array($format,['csv','xlsx'],true)){show_404();return;}
                $rows=[];foreach($entries as $r)$rows[]=[$r['item_id'],$r['barcode'],$r['title'],$r['location'],$r['initial_status'],$r['on_loan']?'Ya':'Tidak',$r['observed']==='good'?'Ditemukan baik':($r['observed']==='damaged'?'Ditemukan rusak':($r['on_loan']?'Dipinjam saat mulai':'Belum ditemukan')),$r['scanned_at'],$r['note']];
                $this->download($format,'stok-opname-'.$id,['ID eksemplar','Barcode','Judul','Lokasi awal','Status awal','Dipinjam saat mulai','Hasil pemeriksaan','Waktu scan','Catatan'],$rows);return;
            }
            $counts=['total'=>count($entries),'good'=>0,'damaged'=>0,'loaned'=>0,'unseen'=>0];foreach($entries as $r){if($r['observed'])$counts[$r['observed']]++;elseif($r['on_loan'])$counts['loaned']++;else $counts['unseen']++;}
            $page=max(1,(int)$this->input->get('page'));$pages=max(1,(int)ceil(count($entries)/100));$page=min($page,$pages);
            $this->page('stock_detail',compact('session','counts','page','pages')+['entries'=>array_slice($entries,($page-1)*100,100)]);return;
        }
        $libraries=$this->local?[]:$this->db->select('id,name')->where('status','active')->order_by('name')->get('libraries')->result_array();
        $this->page('stock',['sessions'=>$this->services->stock_sessions($this->scope?:null,$this->scope?$this->source:null),'libraries'=>$libraries]);
    }
    private function download($format,$name,$headers,$rows)
    {
        header('Cache-Control: private, no-store');
        if($format==='xlsx'){$this->load->library('Catalog_xlsx');$path=$this->catalog_xlsx->build('Pemeriksaan',$headers,$rows);header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');header('Content-Disposition: attachment; filename="'.$name.'.xlsx"');try{readfile($path);}finally{unlink($path);}}
        else{header('Content-Type: text/csv; charset=utf-8');header('Content-Disposition: attachment; filename="'.$name.'.csv"');$out=fopen('php://output','w');fwrite($out,"\xEF\xBB\xBF");fputcsv($out,$headers);foreach($rows as $row)fputcsv($out,array_map(function($v){return is_string($v)&&preg_match('/^\s*[=+@-]/u',$v)?"'".$v:$v;},$row));fclose($out);}
    }
    public function reservations()
    {
        $this->selected(true);
        if($this->input->method(true)==='POST'){$input=$this->post(($this->input->post('action')==='cancel')?'edit':'create');try{if(($input['action']??'')==='cancel')$this->services->cancel_reservation($this->scope,(int)($input['id']??0),(int)$this->current_user['id']);else $this->services->reserve($this->scope,$input['member_no']??'',(int)($input['book_id']??0),(int)$this->current_user['id']);$this->session->set_flashdata('success','Reservasi diperbarui.');}catch(Throwable $e){$this->notice($e);}redirect($this->url('reservations'));return;}
        $q=$this->input->get('q',true);$q=is_string($q)?mb_substr($q,0,180):'';
        $this->page('reservations',['rows'=>$this->services->reservations($this->scope),'books'=>$this->network->listing($this->scope,'books',$q,100)['rows'],'q'=>$q]);
    }
    public function registrations()
    {
        $this->selected(true);
        if($this->input->method(true)==='POST'){$input=$this->post($this->local?'edit':'approve');try{$this->services->review_registration($this->scope,(int)($input['id']??0),$input['decision']??'',$input['expires_on']??'',$input['note']??'',(int)$this->current_user['id']);$this->session->set_flashdata('success','Verifikasi pendaftaran disimpan.');}catch(Throwable $e){$this->notice($e);}redirect($this->url('registrations'));return;}
        $this->page('registrations',['rows'=>$this->services->registrations($this->scope),'may_edit'=>$this->can($this->permission,$this->local?'edit':'approve')]);
    }
    public function kiosk()
    {
        $this->selected(true);
        if($this->input->method(true)==='POST'){$input=$this->post(($this->input->post('action')==='revoke')?'edit':'create');try{if(($input['action']??'')==='revoke')$this->services->kiosk_revoke($this->scope,(int)($input['id']??0),(int)$this->current_user['id']);else{$token=$this->services->kiosk_create($this->scope,(int)$this->current_user['id']);$this->session->set_flashdata('kiosk_url',base_url('jejaring/tamu/'.$token));}$this->session->set_flashdata('success','Konfigurasi buku tamu disimpan.');}catch(Throwable $e){$this->notice($e);}redirect($this->url('kiosk'));return;}
        $this->page('kiosk',['rows'=>$this->services->kiosks($this->scope),'kiosk_url'=>$this->session->flashdata('kiosk_url')]);
    }
    public function labels(){ $this->print_records('items'); }
    public function cards(){ $this->print_records('members'); }
    public function exchange($id=0,$format='')
    {
        $this->load->model('Library_exchange_model','exchange_model');
        if(!$this->exchange_model->ready()){show_error('Modul antarlembaga sedang dipasang.',503);return;}$id=(int)$id;
        if($this->input->method(true)==='POST'){
            $this->selected();if($format!==''){show_error('Cetak hanya melalui GET.',405);return;}
            $action=$this->input->post('action');$permission=(!$id?'create':((!$this->local&&in_array($action,['approve','reject'],true))?'approve':'edit'));$input=$this->post($permission);
            try{if(!$id)$id=$this->exchange_model->request($this->scope,(int)($input['owner_id']??0),$input['asset_source']??'',(int)($input['item_id']??0),$input['due_on']??'',$input['note']??'',(int)$this->current_user['id']);else $this->exchange_model->act($this->scope,$id,$input['action']??'',$input['note']??'',$input['condition']??'',(int)$this->current_user['id'],$this->local);$this->session->set_flashdata('success','Transaksi antarlembaga diperbarui. Kepemilikan buku tidak berubah.');}catch(Throwable $e){$this->notice($e);}redirect($this->url('exchange'.($id?'/'.$id:'')));return;
        }
        if($id){
            $this->selected();$row=$this->exchange_model->find($this->scope,$id,$this->local);if(!$row){show_404();return;}$events=$this->exchange_model->events($this->scope,$id,$this->local);$labels=$this->exchange_model->labels();
            if($format!==''){if($format!=='print'){show_404();return;}$this->require_permission($this->permission,'export');header('Cache-Control: private, no-store');$this->load->view('library_services/exchange_print',compact('row','events','labels'));return;}
            $actions=$this->exchange_model->actions($row,$this->scope,$this->local);foreach($actions as $action=>$label)if(!$this->can($this->permission,(!$this->local&&in_array($action,['approve','reject'],true))?'approve':'edit'))unset($actions[$action]);
            $this->page('exchange_detail',compact('row','events','actions','labels'));return;
        }
        $export=$this->input->get('format');
        if($export!==null){$this->require_permission($this->permission,'export');if(!in_array($export,['csv','xlsx'],true)){show_404();return;}$rows=$this->exchange_model->listing($this->scope?:null,$this->local,true);$data=[];$labels=$this->exchange_model->labels();foreach($rows as $r)$data[]=[$r['id'],$r['owner_name'],$r['borrower_name'],$r['source'],$r['barcode'],$r['title'],$labels[$r['state']],$r['due_on'],$r['created_at'],$r['returned_at']];$this->download($export,'pinjam-antarlembaga',['ID','Pemilik','Peminjam','Sumber','Barcode','Judul','Status','Jatuh tempo','Diajukan','Kembali'],$data);return;}
        $libraries=$this->db->select('id,name')->where('status','active')->order_by('name')->get('libraries')->result_array();
        $owner=$this->input->get('owner_id',true);$owner=is_string($owner)&&ctype_digit($owner)?(int)$owner:0;$asset_source=$this->input->get('asset_source',true);if(!in_array($asset_source,['network','legacy'],true))$asset_source='network';$q=$this->input->get('q',true);$q=is_string($q)?mb_substr($q,0,180):'';$items=[];
        if($owner&&$this->scope){try{$items=$this->exchange_model->discover($this->scope,$owner,$asset_source,$q);}catch(Throwable $e){$this->notice($e);}}
        $this->page('exchange',['rows'=>$this->exchange_model->listing($this->scope?:null,$this->local),'labels'=>$this->exchange_model->labels()]+compact('libraries','owner','asset_source','q','items'));
    }
    public function fines($id=0)
    {
        if(!$this->db->table_exists('library_fines')){show_error('Modul denda manual sedang dipasang.',503);return;}
        $this->load->model('Library_fines_model','fines_model');$id=(int)$id;
        if(!$this->scope){$this->page('fine_scope',['libraries'=>$this->db->select('id,name')->where('status','active')->order_by('name')->get('libraries')->result_array()]);return;}
        if($this->input->method(true)==='POST'){
            $this->selected();$input=$this->post($id?'edit':'create');
            try{if(!$id)$id=$this->fines_model->create($this->scope,$input['loan_source']??'',(int)($input['loan_id']??0),$input['amount']??'',$input['reason']??'',(int)$this->current_user['id'],$this->local);else $this->fines_model->act($this->scope,$id,$input['action']??'',$input['amount']??'',$input['reference']??'',$input['note']??'',(int)($input['event_id']??0),(int)$this->current_user['id'],$this->local);$this->session->set_flashdata('success','Catatan manual disimpan. Tidak ada penagihan atau transfer uang otomatis.');}catch(Throwable $e){$this->notice($e);}redirect($this->url('fines'.($id?'/'.$id:'')));return;
        }
        if($id){$row=$this->fines_model->find($this->scope,$id,$this->local);if(!$row){show_404();return;}$events=$this->fines_model->events($this->scope,$id,$this->local);$applied=0;foreach($events as $r)$applied+=($r['kind']==='reversal'?-1:1)*(int)$r['amount'];$this->page('fine_detail',compact('row','events','applied'));return;}
        $format=$this->input->get('format');$rows=$this->fines_model->listing($this->scope,$this->local,$format!==null);
        if($format!==null){$this->require_permission($this->permission,'export');if(!in_array($format,['csv','xlsx'],true)){show_404();return;}$data=[];foreach($rows as $r)$data[]=[$r['id'],$r['source'],$r['loan_id'],$r['borrower_label'],$r['item_label'],$r['amount'],$r['applied'],$r['state']==='void'?0:(int)$r['amount']-(int)$r['applied'],$r['state'],$r['reason'],$r['created_at']];$this->download($format,'denda-manual-'.$this->scope,['ID','Sumber','ID pinjam','Peminjam','Koleksi','Nominal','Pelunasan/pembebasan neto','Sisa','Status','Alasan','Dibuat'],$data);return;}
        $this->page('fines',compact('rows'));
    }
    private function print_records($entity)
    {
        $this->selected(true);$q=$this->input->get('q',true);$q=is_string($q)?mb_substr($q,0,180):'';$page=max(1,(int)$this->input->get('page'));
        $result=$this->network->listing($this->scope,$entity,$q,50,($page-1)*50);
        if($entity==='members'){
            $terms=$this->db->where('library_id',$this->scope)->get('network_membership_terms')->result_array();$terms=array_column($terms,'expires_on','member_id');
            foreach($result['rows'] as &$row)$row['expires_on']=$terms[$row['id']]??null;unset($row);
        }
        if($this->input->get('print')==='1'){$this->require_permission($this->permission,'export');header('Cache-Control: private, no-store');$this->load->view('library_services/print_records',['entity'=>$entity,'rows'=>$result['rows'],'library'=>$this->library]);return;}
        $this->page('print_list',compact('entity','q','page','result'));
    }
}
