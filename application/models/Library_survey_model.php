<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Library_survey_model extends CI_Model
{
    public function fields(){ $config=[]; require APPPATH.'config/library_survey.php'; return $config['library_survey_fields']; }
    public function get($id){
        if(!$this->db->table_exists('library_survey_profiles'))return ['values'=>[],'version'=>0];
        $r=$this->db->where('library_id',(int)$id)->get('library_survey_profiles')->row_array();
        return $r ? ['values'=>json_decode($r['values_json'],true)?:[],'version'=>(int)$r['version'],'updated_at'=>$r['updated_at']] : ['values'=>[],'version'=>0];
    }
    public function validate(array $input){
        $clean=[];
        foreach($this->fields() as $key=>$spec){
            $value=$input[$key]??'';if(!is_scalar($value))throw new RuntimeException('Format '.$spec[0].' tidak valid.');
            $value=trim((string)$value);if(mb_strlen($value)>($spec[1]==='textarea'?5000:250))throw new RuntimeException($spec[0].' terlalu panjang.');
            if($value!==''){
                if(in_array($spec[1],['number','year'],true)&&(!ctype_digit($value)||strlen($value)>12))throw new RuntimeException($spec[0].' harus bilangan bulat nol atau positif.');
                if($spec[1]==='year'&&((int)$value<1800||(int)$value>(int)date('Y')))throw new RuntimeException('Tahun harus 1800 sampai tahun berjalan.');
                if($spec[1]==='boolean'&&!in_array($value,['yes','no','unknown'],true))throw new RuntimeException('Pilihan tidak valid.');
                if($spec[1]==='select'&&!array_key_exists($value,$spec[2]))throw new RuntimeException('Pilihan tidak valid.');
                if($spec[1]==='date'){ $d=DateTime::createFromFormat('!Y-m-d',$value);if(!$d||$d->format('Y-m-d')!==$value)throw new RuntimeException('Tanggal tidak valid.'); }
            }
            $clean[$key]=$value;
        }
        return $clean;
    }
    public function save($id,array $input,$version,$actor){
        $id=(int)$id;$clean=$this->validate($input);
        if(!is_scalar($version)||!ctype_digit((string)$version))throw new RuntimeException('Versi formulir tidak valid.');
        $this->db->trans_begin();
        try{
            $l=$this->db->query('SELECT manager_name,phone FROM libraries WHERE id=? FOR UPDATE',[$id])->row_array();
            if(!$l||!trim((string)$l['manager_name'])||!preg_match('/^(?:\+?62|0)8[0-9]{7,12}$/D',preg_replace('/[\s().-]/','',(string)$l['phone'])))throw new RuntimeException('Lengkapi contact person dan nomor HP aktif pada profil terlebih dahulu.');
            $old=$this->get($id);if((int)$version!==$old['version'])throw new RuntimeException('Data sudah berubah. Muat ulang sebelum menyimpan.');
            $row=['values_json'=>json_encode($clean,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR),'version'=>$old['version']+1,'updated_by'=>(int)$actor,'updated_at'=>date('Y-m-d H:i:s')];
            $ok=$old['version']?$this->db->where('library_id',$id)->update('library_survey_profiles',$row):$this->db->insert('library_survey_profiles',['library_id'=>$id]+$row);
            if(!$ok||!$this->db->insert('iplm_history',['actor_id'=>(int)$actor,'event'=>'survey.updated','payload_json'=>json_encode(['library_id'=>$id,'before'=>$old,'after'=>$clean],JSON_UNESCAPED_UNICODE)])||!$this->db->trans_status())throw new RuntimeException('Pendataan gagal disimpan.');
            $this->db->trans_commit();
        }catch(Throwable $e){$this->db->trans_rollback();throw $e;}
    }
}
