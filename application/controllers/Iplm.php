<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Iplm extends MY_Controller
{
    private $local=false,$scope=null,$permission='iplm.manage';
    public function __construct(){
        parent::__construct();$this->local=!empty($this->current_user['is_library_admin']);$this->scope=$this->local?(int)$this->current_user['library_id']:null;
        // Refresh permissions: newly granted/revoked module rights take effect on every request.
        $this->load->model('Auth_model');$this->user_perms=$this->Auth_model->load_permissions((int)$this->current_user['id']);$this->session->set_userdata('user_perms',$this->user_perms);
        $this->permission=$this->local?'iplm.local':'iplm.manage';$this->require_permission($this->permission,'view');
        if(!$this->local&&$this->current_library_scope_id()){show_error('Unsur admin kabupaten diperlukan.',403);exit;}
        $this->load->model('Iplm_model','iplm');$this->db->db_debug=false;
        if(!$this->db->table_exists('iplm_submissions')){show_error('Modul IPLM sedang dipasang.',503);exit;}
        if(!$this->session->userdata('iplm_csrf'))$this->session->set_userdata('iplm_csrf',bin2hex(random_bytes(32)));
    }
    private function post($action='edit',$permission=null){
        $this->require_permission($permission?:$this->permission,$action);
        if($this->input->method(true)!=='POST'){show_error('Gunakan formulir tindakan.',405);exit;}
        $token=$this->input->post('iplm_csrf',false);if(!is_string($token)||!hash_equals((string)$this->session->userdata('iplm_csrf'),$token)){show_error('Formulir kedaluwarsa. Muat ulang.',403);exit;}
        $this->session->set_userdata('iplm_csrf',bin2hex(random_bytes(32)));$data=(array)$this->input->post(null,false);foreach($data as$v)if(!is_scalar($v)&&$v!==null){show_error('Format isian tidak valid.',400);exit;}return $data;
    }
    private function central(){if($this->local){show_error('Khusus admin kabupaten.',403);exit;}}
    private function page($view,$data=[]){$this->render('iplm/'.$view,$data+['title'=>'Pendataan IPLM','local'=>$this->local,'csrf'=>$this->session->userdata('iplm_csrf'),'sections'=>$this->iplm->sections(),'may_create'=>$this->can($this->permission,'create'),'may_edit'=>$this->can($this->permission,'edit'),'may_approve'=>$this->can($this->permission,'approve'),'may_delete'=>$this->can($this->permission,'delete'),'may_export'=>$this->can($this->permission,'export'),'may_settings'=>!$this->local&&$this->can('iplm.settings','view')]);}
    public function index(){
        $periods=$this->iplm->periods();$pid=$this->input->get('period');$pid=is_scalar($pid)?(int)$pid:0;$pid=$pid?:($periods[0]['id']??0);$period=$this->iplm->period($pid);
        $rows=$period?$this->iplm->listing($pid,$this->scope):[];$stats=['draft'=>0,'submitted'=>0,'revision'=>0,'verified'=>0,'differences'=>0];$totals=[];
        foreach($rows as&$r){$row=$this->iplm->find($r['id'],$this->scope);$diff=$this->iplm->differences($row);$r['differences']=count(array_filter($diff,function($d){return !$d['resolved'];}));$stats[$r['status']]++;if($r['differences'])$stats['differences']++;}unset($r);
        foreach($this->iplm->fields(true)as$f)if(in_array($f['kind'],['number','money'],true)){$sum=0;$filled=0;foreach($rows as$r)if($r['status']==='verified'&&!$r['differences']){$values=json_decode($r['values_json'],true);$v=$values[$f['code']]??'';if($v!==''&&is_numeric($v)){$sum+=(float)$v;$filled++;}}$totals[]=['label'=>$f['label'],'sum'=>$filled?$sum:null,'filled'=>$filled];}
        $libraries=$this->local?[]:$this->iplm->eligible_libraries();$in_scope=!$this->local||$this->iplm->eligible($this->scope);
        $this->page('index',compact('periods','period','rows','stats','libraries','totals','in_scope'));
    }
    public function create(){
        $data=$this->post('create');$library=$this->scope??(int)($data['library_id']??0);
        if($this->local&&isset($data['library_id'])&&(int)$data['library_id']!==$this->scope){show_error('Cakupan tidak boleh diubah.',403);return;}
        try{$id=$this->iplm->create($library,(int)($data['period_id']??0),(int)$this->current_user['id'],$this->current_user['full_name']);redirect('iplm/form/'.$id);}catch(Throwable$e){$this->session->set_flashdata('error',$e->getMessage());redirect('iplm');}
    }
    public function form($id){
        $row=$this->iplm->find((int)$id,$this->scope);if(!$row){show_404();return;}$error=null;
        if($this->input->method(true)==='POST'){$data=$this->post();try{$this->iplm->save($id,$this->scope,(int)$this->current_user['id'],$data,!$this->local);$this->session->set_flashdata('success','Isian IPLM disimpan. Data induk /libraries tidak diubah.');redirect('iplm/form/'.(int)$id);return;}catch(Throwable$e){$error=$e->getMessage();foreach($row['schema']as$f){$k=$f['code'];if(isset($data['f_'.$k]))$row['values'][$k]=$data['f_'.$k];if(isset($data['e_'.$k]))$row['evidence'][$k]=$data['e_'.$k];}}}
        $period=$this->iplm->period($row['period_id']);$library=$this->iplm->library($row['library_id']);$differences=$this->iplm->differences($row);$types=$this->db->order_by('sort_order')->order_by('name')->get('library_types')->result_array();$subtypes=$this->db->order_by('sort_order')->order_by('name')->get('library_subtypes')->result_array();$history=$this->iplm->history_rows($id);
        $reference=$this->iplm->collection_reference($row['library_id']);$in_scope=$this->iplm->eligible($row['library_id']);
        $subtypes=array_values(array_filter($subtypes,function($s){return $s['is_active']&&$s['iplm_eligible']&&!in_array($s['code'],['tk','skb'],true);}));$type_ids=array_column($subtypes,'library_type_id');$types=array_values(array_filter($types,function($t)use($type_ids){return $t['is_active']&&in_array($t['id'],$type_ids);}));
        $this->page('form',compact('row','period','library','differences','types','subtypes','history','error','reference','in_scope'));
    }
    public function review($id){$this->central();$action=$this->input->post('action');$data=$this->post($action==='delete'?'delete':'approve');try{$this->iplm->review((int)$id,(int)$this->current_user['id'],$data);$this->session->set_flashdata('success','Tindakan verifikasi dicatat. Data induk tidak ditimpa.');}catch(Throwable$e){$this->session->set_flashdata('error',$e->getMessage());}redirect($action==='delete'?'iplm':'iplm/form/'.(int)$id);}
    public function settings(){
        $this->central();$this->require_permission('iplm.settings','view');$error=null;
        if($this->input->method(true)==='POST'){$data=$this->post(empty($this->input->post('id'))?'create':'edit','iplm.settings');try{if(($data['entity']??'')==='period')$this->iplm->save_period($data,(int)$this->current_user['id']);else $this->iplm->save_field($data,(int)$this->current_user['id']);$this->session->set_flashdata('success','Pengaturan disimpan. Formulir lama mempertahankan versi definisinya.');redirect('iplm/settings?tab='.(($data['entity']??'')==='period'?'periods':'fields'));return;}catch(Throwable$e){$error=$e->getMessage();}}
        $fields=$this->iplm->fields(true);$periods=$this->iplm->periods();$field_id=(int)$this->input->get('field');$period_id=(int)$this->input->get('period');$selected_field=null;$selected_period=null;
        foreach($fields as$f)if((int)$f['id']===$field_id)$selected_field=$f;foreach($periods as$p)if((int)$p['id']===$period_id)$selected_period=$p;
        $settings_tab=($this->input->get('tab')==='fields'||$field_id||($error&&($data['entity']??'')==='field'))?'fields':'periods';
        if(!$selected_period&&$this->input->get('new')!=='period')$selected_period=$periods[0]??null;
        if($error&&isset($data)){if(($data['entity']??'')==='period')$selected_period=array_replace($selected_period?:[],$data);else{$selected_field=array_replace($selected_field?:[],$data);$selected_field['options_json']=json_encode(explode("\n",(string)($data['options']??'')));}}
        $population=$this->iplm->registry_population();
        $this->page('settings',compact('fields','periods','selected_field','selected_period','error','population','settings_tab'));
    }
    public function export($period_id,$format='xlsx'){
        $this->require_permission($this->permission,'export');if(!in_array($format,['csv','xlsx'],true)){show_404();return;}
        $period=$this->iplm->period($period_id);if(!$period){show_404();return;}$fields=$this->iplm->fields(true);$headers=['ID','Periode','Status','Perpustakaan terdaftar'];foreach($fields as$f)$headers[]=$f['label'];foreach($fields as$f)if($f['evidence_hint'])$headers[]='Bukti: '.$f['label'];
        $types=array_column($this->db->get('library_types')->result_array(),'name','id');$subs=array_column($this->db->get('library_subtypes')->result_array(),'name','id');$rows=[];
        foreach($this->iplm->listing($period_id,$this->scope)as$r){$row=$this->iplm->find($r['id'],$this->scope);$line=[$r['id'],$period['title'],$r['status'],$r['library_name']];foreach($fields as$f){$v=$row['values'][$f['code']]??'';if($f['code']==='library_type_id')$v=$types[$v]??$v;if($f['code']==='library_subtype_id')$v=$subs[$v]??$v;$line[]=$v;}foreach($fields as$f)if($f['evidence_hint'])$line[]=$row['evidence'][$f['code']]??'';$rows[]=$line;}
        header('Cache-Control: private, no-store');$name='pendataan-iplm-'.$period['year'];
        if($format==='xlsx'){$this->load->library('Catalog_xlsx');$path=$this->catalog_xlsx->build('IPLM Kab-Kota',$headers,$rows);header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');header('Content-Disposition: attachment; filename="'.$name.'.xlsx"');try{readfile($path);}finally{unlink($path);}}
        else{header('Content-Type: text/csv; charset=utf-8');header('Content-Disposition: attachment; filename="'.$name.'.csv"');$out=fopen('php://output','w');fwrite($out,"\xEF\xBB\xBF");fputcsv($out,$headers);foreach($rows as$row)fputcsv($out,array_map(function($v){return is_string($v)&&preg_match('/^\s*[=+@-]/u',$v)?"'".$v:$v;},$row));fclose($out);}
    }
public function analysis($period_id){$this->central();$period=$this->iplm->period($period_id);if(!$period){show_404();return;}$this->load->library('Iplm_analysis');$rows=[];foreach($this->iplm->listing($period_id)as$r)if($r['status']==='verified'){$row=$this->iplm->find($r['id']);$row['library_name']=$r['library_name'];$sub=$this->db->where('id',(int)($row['values']['library_subtype_id']??0))->get('library_subtypes')->row_array();if(($row['values']['province']??'')==='Jawa Tengah'&&($row['values']['regency']??'')==='Kab. Rembang'&&$sub&&!empty($sub['iplm_eligible'])&&!array_filter($this->iplm->differences($row),function($d){return !$d['resolved'];}))$rows[]=$row;}$analysis=$this->iplm_analysis->calculate($rows,$period);$this->page('analysis',compact('period','analysis'));}
}
