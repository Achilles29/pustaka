<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Library_type_model extends CI_Model
{
    public function types(){return $this->db->order_by('sort_order')->order_by('name')->get('library_types')->result_array();}
    public function subtypes(){return $this->db->select('s.*,t.name type_name')->from('library_subtypes s')->join('library_types t','t.id=s.library_type_id')->order_by('t.sort_order')->order_by('s.sort_order')->order_by('s.name')->get()->result_array();}
    public function save($entity,array $input,$actor){
        if(!in_array($entity,['type','subtype'],true))throw new RuntimeException('Master tidak valid.');$table=$entity==='type'?'library_types':'library_subtypes';$id=(int)($input['id']??0);
        foreach($input as$v)if(!is_scalar($v)&&$v!==null)throw new RuntimeException('Isian tidak valid.');
        $code=trim($input['code']??'');$name=trim($input['name']??'');$description=trim($input['description']??'');
        if(!preg_match('/^[a-z0-9_]{2,50}$/D',$code)||$name===''||mb_strlen($name)>($entity==='type'?120:180)||mb_strlen($description)>255)throw new RuntimeException('Kode/nama/deskripsi tidak valid. Kode gunakan huruf kecil, angka atau underscore.');
        $data=['code'=>$code,'name'=>$name,'description'=>$description,'sort_order'=>(int)($input['sort_order']??100),'is_active'=>empty($input['is_active'])?0:1];
        if($entity==='type'){$color=$input['marker_color']??'';if(!preg_match('/^#[0-9a-f]{6}$/iD',$color))throw new RuntimeException('Warna harus kode hex 6 digit.');$data['marker_color']=$color;}
        else{$parent=(int)($input['library_type_id']??0);if(!$this->db->where(['id'=>$parent,'is_active'=>1])->count_all_results('library_types'))throw new RuntimeException('Pilih jenis aktif.');$data['library_type_id']=$parent;$data['iplm_eligible']=empty($input['iplm_eligible'])?0:1;}
        $this->db->trans_begin();try{
            $old=$id?$this->db->query('SELECT * FROM '.$table.' WHERE id=? FOR UPDATE',[$id])->row_array():null;if($id&&!$old)throw new RuntimeException('Master tidak ditemukan.');
            $normalize = function ($value) { return preg_replace('/[^a-z0-9]/', '', strtolower(preg_replace('/^perpustakaan\s+/i', '', trim($value)))); };
            if ($entity === 'type') {
                $effective_code = $old['code'] ?? $code;
                $retired_codes = ['perpusda','desa','komunitas'];
                $retired_names = ['daerah','desa','komunitasliterasi','perpusda'];
                if (in_array($effective_code,$retired_codes,true) || in_array($normalize($name),$retired_names,true)) throw new RuntimeException('Kategori ini sudah menjadi subjenis Perpustakaan Umum. Gunakan Kabupaten/Kota, Desa/Kelurahan, atau TBM/Rumah Baca, bukan membuat jenis duplikat.');
                foreach ($this->db->select('code,name')->get('library_subtypes')->result_array() as $sub) {
                    if ($normalize($effective_code)===$normalize($sub['code']) || $normalize($name)===$normalize($sub['name'])) throw new RuntimeException('Jenis tidak boleh menduplikasi kode/nama subjenis yang sudah ada.');
                }
            } else {
                foreach ($this->db->select('code,name')->get('library_types')->result_array() as $type) {
                    if ($normalize($old['code']??$code)===$normalize($type['code']) || $normalize($name)===$normalize($type['name'])) throw new RuntimeException('Subjenis tidak boleh menduplikasi kode/nama jenis induk.');
                }
            }
            if($old){$data['code']=$old['code'];if(!$data['is_active']||($entity==='subtype'&&(int)$old['library_type_id']!==(int)$data['library_type_id'])){
                if($this->db->where($entity==='type'?'library_type_id':'library_subtype_id',$id)->count_all_results('libraries'))throw new RuntimeException('Master sedang dipakai perpustakaan; tidak boleh dihapus/dipindahkan.');
                if($entity==='type'&&$this->db->where('library_type_id',$id)->where('is_active',1)->count_all_results('library_subtypes'))throw new RuntimeException('Jenis masih memiliki subjenis aktif.');
            }}
            if(!($id?$this->db->where('id',$id)->update($table,$data):$this->db->insert($table,$data)))throw new RuntimeException('Kode master sudah digunakan atau data gagal disimpan.');
            if(!$this->db->insert('iplm_history',['actor_id'=>(int)$actor,'event'=>'taxonomy.saved','payload_json'=>json_encode(['entity'=>$entity,'id'=>$id?:$this->db->insert_id(),'before'=>$old,'after'=>$data],JSON_UNESCAPED_UNICODE)]))throw new RuntimeException('Riwayat gagal disimpan.');
            if(!$this->db->trans_status())throw new RuntimeException('Transaksi gagal.');$this->db->trans_commit();
        }catch(Throwable$e){$this->db->trans_rollback();throw $e;}
    }
}
