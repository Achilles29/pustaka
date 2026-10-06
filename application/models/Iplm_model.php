<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Iplm_model extends CI_Model
{
    public function sections(){return ['identity'=>'Identitas & pengisi','collection'=>'Koleksi','staff'=>'Tenaga perpustakaan','service'=>'Pelayanan','management'=>'Penyelenggaraan & pengelolaan','evidence'=>'Bukti dukung'];}
    private function must($ok){if($ok===false)throw new RuntimeException('Data gagal disimpan. Muat ulang dan coba kembali.');return $ok;}
    private function atomic(callable $fn){$this->db->trans_begin();try{$r=$fn();$this->must($this->db->trans_status());$this->db->trans_commit();return $r;}catch(Throwable $e){$this->db->trans_rollback();throw $e;}}
    public function json($value){return json_encode($value,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);}
    public function text($value,$max=500){if(!is_scalar($value)&&$value!==null)throw new RuntimeException('Format isian tidak valid.');$s=trim((string)$value);if(mb_strlen($s)>$max)throw new RuntimeException('Isian melebihi '.$max.' karakter.');return $s;}
    public function url($value){$s=$this->text($value,2000);if($s!==''&&(!filter_var($s,FILTER_VALIDATE_URL)||strtolower(parse_url($s,PHP_URL_SCHEME)??'')!=='https'||parse_url($s,PHP_URL_USER)||parse_url($s,PHP_URL_PASS)))throw new RuntimeException('Bukti dukung harus berupa tautan HTTPS yang valid.');return $s;}
    public function fields($all=false){if(!$all)$this->db->where('is_active',1);return $this->db->order_by('sort_order')->order_by('id')->get('iplm_fields')->result_array();}
    public function periods(){return $this->db->order_by('year','DESC')->get('iplm_periods')->result_array();}
    /** Satu aturan cakupan untuk formulir, rekap, ekspor dan penyebut populasi. */
    public function eligible_libraries(){
        return $this->db->select('l.id,l.name,l.code,s.code subtype_code,s.name subtype_name')->from('libraries l')->join('library_subtypes s','s.id=l.library_subtype_id AND s.library_type_id=l.library_type_id')->join('library_types t','t.id=l.library_type_id')->where('l.status','active')->where('t.is_active',1)->where('s.is_active',1)->where('s.iplm_eligible',1)->where_not_in('s.code',['tk','skb'])->order_by('l.name')->get()->result_array();
    }
    public function eligible($library){foreach($this->eligible_libraries()as$l)if((int)$l['id']===(int)$library)return true;return false;}
    public function registry_population(){
        $rows=$this->eligible_libraries();
        if($this->db->field_exists('iplm_population_status','libraries')){ $selected=array_column($this->db->select('id')->where('iplm_population_status','included')->get('libraries')->result_array(),'id'); $rows=array_values(array_filter($rows,function($r)use($selected){return in_array($r['id'],$selected); })); }
        $groups=[];
        foreach($rows as$l){$key=$l['subtype_code'];if(!isset($groups[$key]))$groups[$key]=['name'=>$l['subtype_name'],'count'=>0];$groups[$key]['count']++;}
        return ['total'=>count($rows),'groups'=>$groups,'ids'=>array_map('intval',array_column($rows,'id')),'note'=>'Snapshot unit yang dipilih kabupaten untuk populasi IPLM, aktif dan sesuai jenis/subjenis kewenangan; jumlah seluruh sekolah bukan populasi. Dihitung '.date('Y-m-d H:i:s').' WIB.'];
    }
    public function collection_reference($library){
        $counts=['print_titles'=>0,'digital_titles'=>0,'print_copies'=>0];
        foreach($this->db->select('format,COUNT(*) total',false)->where('library_id',(int)$library)->where('deleted_at IS NULL',null,false)->group_by('format')->get('network_books')->result_array()as$r)$counts[$r['format']==='physical'?'print_titles':'digital_titles']=(int)$r['total'];
        $counts['print_copies']=(int)$this->db->where('library_id',(int)$library)->where('deleted_at IS NULL',null,false)->where('status !=','withdrawn')->count_all_results('network_items');
        return $counts;
    }
    public function period($id,$lock=false){return $this->must($this->db->query('SELECT * FROM iplm_periods WHERE id=?'.($lock?' FOR UPDATE':''),[(int)$id]))->row_array();}
    public function library($id){return $this->db->select('l.*,t.code type_code,t.name type_name,s.name subtype_name,s.iplm_eligible')->from('libraries l')->join('library_types t','t.id=l.library_type_id')->join('library_subtypes s','s.id=l.library_subtype_id','left')->where('l.id',(int)$id)->get()->row_array();}
    public function identity($id){$l=$this->library($id);if(!$l)throw new RuntimeException('Perpustakaan tidak ditemukan.');return $this->identity_values($l);}
    private function identity_values(array $l){return ['library_type_id'=>(string)$l['library_type_id'],'library_subtype_id'=>(string)($l['library_subtype_id']??''),'institution_name'=>$l['institution_name']?:$l['name'],'npsn'=>$l['type_code']==='sekolah'&&preg_match('/^\d{8}$/D',$l['code'])?$l['code']:'','npp'=>$l['npp']??'','library_name'=>$l['name'],'address'=>$l['address']??'','province'=>'Jawa Tengah','regency'=>'Kab. Rembang'];}
    public function pending_summary(){
        $eligible=array_column($this->eligible_libraries(),'id');if(!$eligible)return ['differences'=>0,'submitted'=>0];$this->db->where_in('s.library_id',$eligible);
        $rows=$this->db->select('l.*,t.code type_code,s.library_id,s.values_json,s.resolutions_json,s.status submission_status')->from('iplm_submissions s')->join('iplm_periods p','p.id=s.period_id')->join('libraries l','l.id=s.library_id')->join('library_types t','t.id=l.library_type_id')->where('s.deleted_at IS NULL',null,false)->where('p.state !=','closed')->get()->result_array();$counts=['differences'=>0,'submitted'=>0];
        foreach($rows as$r){$r['values']=json_decode($r['values_json'],true);$r['resolutions']=json_decode($r['resolutions_json'],true);$r['baseline']=[];if(array_filter($this->differences($r,$this->identity_values($r)),function($d){return !$d['resolved'];}))$counts['differences']++;elseif($r['submission_status']==='submitted')$counts['submitted']++;}return $counts;
    }
    public function find($id,$scope=null,$lock=false){
        $args=[(int)$id];$where='';if($scope!==null){if((int)$scope<=0)throw new RuntimeException('Cakupan tidak valid.');$where=' AND library_id=?';$args[]=(int)$scope;}
        $r=$this->must($this->db->query('SELECT * FROM iplm_submissions WHERE id=? AND deleted_at IS NULL'.$where.($lock?' FOR UPDATE':''),$args))->row_array();
        if($r)foreach(['schema','values','evidence','baseline','resolutions']as$k)$r[$k]=json_decode($r[$k.'_json'],true,512,JSON_THROW_ON_ERROR);
        return $r;
    }
    public function listing($period,$scope=null){$eligible=array_column($this->eligible_libraries(),'id');if(!$eligible)return [];$this->db->select('s.id,s.library_id,s.status,s.version,s.updated_at,s.values_json,s.resolutions_json,l.name library_name,l.code,l.library_subtype_id,st.iplm_eligible')->from('iplm_submissions s')->join('libraries l','l.id=s.library_id')->join('library_subtypes st','st.id=l.library_subtype_id','left')->where_in('s.library_id',$eligible)->where('s.period_id',(int)$period)->where('s.deleted_at IS NULL',null,false);if($scope!==null)$this->db->where('s.library_id',(int)$scope);return $this->db->order_by('s.updated_at','DESC')->get()->result_array();}
    public function differences(array $row,$current=null){
        $current=$current??$this->identity($row['library_id']);$diff=[];
        foreach($current as$k=>$master){$value=(string)($row['values'][$k]??'');if(trim($value)===trim((string)$master))continue;
            $stamp=hash('sha256',$this->json([$master,$value]));$resolution=$row['resolutions'][$k]??[];
            $diff[$k]=['master'=>$master,'value'=>$value,'original'=>$row['baseline'][$k]??'','resolved'=>($resolution['stamp']??'')===$stamp,'stamp'=>$stamp,'note'=>$resolution['note']??''];
        }return $diff;
    }
    private function history($id,$actor,$event,$payload){$this->must($this->db->insert('iplm_history',['submission_id'=>$id,'actor_id'=>(int)$actor,'event'=>$event,'payload_json'=>$this->json($payload)]));}
    public function history_rows($id){return $this->db->select('id,actor_id,event,created_at')->where('submission_id',(int)$id)->order_by('id','DESC')->limit(50)->get('iplm_history')->result_array();}
    public function create($library,$period,$actor,$name){
        return $this->atomic(function()use($library,$period,$actor,$name){
            $p=$this->period($period,true);$l=$this->library($library);if(!$p||$p['state']==='closed'||!$l||$l['status']!=='active')throw new RuntimeException('Periode/perpustakaan tidak aktif.');
            if(!$this->eligible($library))throw new RuntimeException('Perpustakaan ini di luar cakupan pendataan IPLM kabupaten. TK/SKB tidak diikutkan.');
            $existing=$this->db->where(['library_id'=>(int)$library,'period_id'=>(int)$period,'active_slot'=>1])->get('iplm_submissions')->row_array();if($existing)return (int)$existing['id'];
            $baseline=$this->identity($library);$values=$baseline+['respondent_name'=>$this->text($name,180)];
            $this->must($this->db->insert('iplm_submissions',['library_id'=>(int)$library,'period_id'=>(int)$period,'schema_json'=>$this->json($this->fields()),'values_json'=>$this->json($values),'evidence_json'=>'{}','baseline_json'=>$this->json($baseline),'resolutions_json'=>'{}','created_by'=>(int)$actor,'updated_by'=>(int)$actor]));
            $id=(int)$this->db->insert_id();$this->history($id,$actor,'created',['baseline'=>$baseline]);return $id;
        });
    }
    public function validate(array $schema,array $input,$complete=false){
        $values=[];$evidence=[];
        foreach($schema as$f){$k=$f['code'];$v=$this->text($input['f_'.$k]??'',in_array($f['kind'],['text','url'],true)?2000:100);$ev=$this->url($input['e_'.$k]??'');
            if($complete&&!empty($f['is_required'])&&$v==='')throw new RuntimeException('Wajib diisi: '.$f['label']);
            if($v!==''&&in_array($f['kind'],['number','money'],true)&&(!preg_match('/^\d{1,15}$/D',$v)))throw new RuntimeException($f['label'].': gunakan bilangan bulat tidak negatif tanpa pemisah ribuan.');
            if($v!==''&&$f['kind']==='select'&&!in_array($k,['library_type_id','library_subtype_id'],true)&&!in_array($v,json_decode($f['options_json']??'[]',true)?:[],true))throw new RuntimeException('Pilihan tidak valid: '.$f['label']);
            if($f['kind']==='url')$v=$this->url($v);
            if($complete&&!empty($f['evidence_required'])&&$this->url($input['f_evidence_url']??'')==='')throw new RuntimeException('Isi satu tautan folder bukti dukung pada tab Bukti dukung.');
            $values[$k]=$v;$evidence[$k]=$ev;
        }
        $type=$values['library_type_id']??'';$sub=$values['library_subtype_id']??'';
        if($type!==''&&(!ctype_digit($type)||!$this->db->where(['id'=>(int)$type,'is_active'=>1])->count_all_results('library_types')))throw new RuntimeException('Jenis perpustakaan tidak aktif.');
        if($sub!==''&&(!ctype_digit($sub)||!$this->db->where(['id'=>(int)$sub,'library_type_id'=>(int)$type,'is_active'=>1])->count_all_results('library_subtypes')))throw new RuntimeException('Subjenis tidak sesuai jenis perpustakaan.');
        if($sub!==''&&!$this->db->where(['id'=>(int)$sub,'iplm_eligible'=>1])->where_not_in('code',['tk','skb'])->count_all_results('library_subtypes'))throw new RuntimeException('Subjenis di luar cakupan IPLM kabupaten; TK/SKB tidak diikutkan.');
        if($complete){
            foreach([['print_titles','print_copies'],['digital_titles','digital_copies']]as$pair)if(($values[$pair[0]]??'')!==''&&($values[$pair[1]]??'')!==''&&(float)$values[$pair[0]]>(float)$values[$pair[1]])throw new RuntimeException('Jumlah eksemplar tidak boleh lebih kecil dari jumlah judul.');
            if(!empty($values['respondent_phone'])&&!preg_match('/^\+?[0-9 ()-]{8,30}$/D',$values['respondent_phone']))throw new RuntimeException('Nomor kontak pengisi tidak valid.');
            if(($values['staff_training']??'')!==''&&($values['staff_qualified']??'')!==''&&($values['staff_other']??'')!==''&&(float)$values['staff_training']>(float)$values['staff_qualified']+(float)$values['staff_other'])throw new RuntimeException('Peserta PKB (orang) melebihi total tenaga; hitung orang, bukan frekuensi pelatihan.');
        }
        return [$values,$evidence];
    }
    public function save($id,$scope,$actor,array $input,$central=false){
        return $this->atomic(function()use($id,$scope,$actor,$input,$central){
            $row=$this->find($id,$scope,true);if(!$row)throw new RuntimeException('Isian tidak ditemukan.');$p=$this->period($row['period_id'],true);
            if(!$this->eligible($row['library_id']))throw new RuntimeException('Perpustakaan di luar cakupan pendataan IPLM kabupaten.');
            if((int)($input['version']??0)!==(int)$row['version'])throw new RuntimeException('Isian sudah berubah. Muat ulang sebelum menyimpan.');
            if($p['state']==='closed'||(!$central&&!in_array($row['status'],['draft','revision'],true)))throw new RuntimeException('Isian dikunci. Minta admin mengembalikan untuk revisi.');
            $submit=($input['action']??'')==='submit';if($submit&&($p['state']!=='open'||!$p['dates_confirmed']))throw new RuntimeException('Periode belum dibuka dan dikonfirmasi admin kabupaten. Draft tetap dapat disimpan.');
            // The form now has one shared folder link. Keep historical per-item
            // links if omitted, so a new-form save never erases old evidence.
            foreach($row['evidence']as$key=>$url)if(!array_key_exists('e_'.$key,$input))$input['e_'.$key]=$url;
            if($submit){
                $profile=$this->db->select('manager_name,phone')->where('id',$row['library_id'])->get('libraries')->row_array();
                if(empty($profile['manager_name'])||!preg_match('/^(?:\+?62|0)8[0-9]{7,12}$/D',preg_replace('/[\s().-]/','',(string)($profile['phone']??''))))throw new RuntimeException('Lengkapi contact person dan nomor HP aktif pada profil perpustakaan sebelum mengirim IPLM.');
            }
            [$values,$evidence]=$this->validate($row['schema'],$input,$submit);
            $data=['values_json'=>$this->json($values),'evidence_json'=>$this->json($evidence),'resolutions_json'=>'{}','version'=>(int)$row['version']+1,'updated_by'=>(int)$actor,'updated_at'=>date('Y-m-d H:i:s'),'verified_at'=>null,'status'=>$submit?'submitted':($row['status']==='draft'?'draft':'revision')];
            if($submit)$data['submitted_at']=date('Y-m-d H:i:s');
            $this->must($this->db->where('id',(int)$id)->update('iplm_submissions',$data));$this->history($id,$actor,$submit?'submitted':'saved',['before'=>$row['values'],'after'=>$values,'evidence'=>$evidence,'version'=>$data['version']]);
        });
    }
    public function review($id,$actor,array $input){
        return $this->atomic(function()use($id,$actor,$input){
            $row=$this->find($id,null,true);if(!$row)throw new RuntimeException('Isian tidak ditemukan.');
            if((int)($input['version']??0)!==(int)$row['version'])throw new RuntimeException('Isian berubah. Muat ulang.');
            $action=$input['action']??'';$note=$this->text($input['note']??'',2000);if($note==='')throw new RuntimeException('Catatan verifikasi wajib diisi.');
            if(!in_array($action,['verify','revision','delete','resolve'],true))throw new RuntimeException('Tindakan tidak valid.');
            $data=['updated_by'=>(int)$actor,'updated_at'=>date('Y-m-d H:i:s'),'version'=>(int)$row['version']+1,'review_note'=>$note];
            if($action==='delete'){$data['deleted_at']=date('Y-m-d H:i:s');$data['active_slot']=null;}
            elseif($action==='revision'){$data['status']='revision';$data['verified_at']=null;}
            elseif($action==='resolve'){
                $key=$this->text($input['field']??'',80);$diff=$this->differences($row);if(!isset($diff[$key]))throw new RuntimeException('Perbedaan sudah tidak berlaku.');
                $choice=$input['choice']??'';
                if($choice==='master'){$row['values'][$key]=$diff[$key]['master'];$data['values_json']=$this->json($row['values']);unset($row['resolutions'][$key]);}
                elseif($choice==='form')$row['resolutions'][$key]=['stamp'=>$diff[$key]['stamp'],'note'=>$note,'actor'=>$actor];
                else throw new RuntimeException('Pilih data yang sudah diverifikasi.');
                $data['resolutions_json']=$this->json($row['resolutions']);$data['status']='revision';$data['verified_at']=null;
            }else{
                if(!$this->eligible($row['library_id']))throw new RuntimeException('Perpustakaan di luar cakupan pendataan IPLM kabupaten.');
                if(!in_array($row['status'],['submitted','revision'],true))throw new RuntimeException('Isian harus dikirim atau direvisi terlebih dahulu.');
                $p=$this->period($row['period_id'],true);if(!$p['dates_confirmed'])throw new RuntimeException('Tanggal periode belum dikonfirmasi.');
                foreach($this->differences($row)as$d)if(!$d['resolved'])throw new RuntimeException('Selesaikan perbedaan identitas terlebih dahulu.');
                $check=[];foreach($row['values']as$k=>$v)$check['f_'.$k]=$v;foreach($row['evidence']as$k=>$v)$check['e_'.$k]=$v;$this->validate($row['schema'],$check,true);
                $data['status']='verified';$data['verified_at']=date('Y-m-d H:i:s');
            }
            $this->must($this->db->where('id',(int)$id)->update('iplm_submissions',$data));$this->history($id,$actor,$action,['before_status'=>$row['status'],'before_values'=>$row['values'],'change'=>$data,'note'=>$note]);
        });
    }
    public function save_period(array $input,$actor){
        $id=(int)($input['id']??0);$year=(int)($input['year']??0);$title=$this->text($input['title']??'',180);
        if($year<2020||$year>2100||$title==='')throw new RuntimeException('Tahun/judul periode tidak valid.');
        foreach(['start_date','end_date']as$k){$s=$this->text($input[$k]??'',10);$d=DateTime::createFromFormat('!Y-m-d',$s);if(!$d||$d->format('Y-m-d')!==$s)throw new RuntimeException('Tanggal tidak valid.');}
        if($input['start_date']>$input['end_date'])throw new RuntimeException('Urutan tanggal tidak valid.');
        $state=$input['state']??'draft';if(!in_array($state,['draft','open','closed'],true))throw new RuntimeException('Status periode tidak valid.');
        $confirmed=!empty($input['dates_confirmed']);if($state==='open'&&!$confirmed)throw new RuntimeException('Konfirmasikan rentang data sebelum membuka periode.');
        $population=$this->registry_population();$pop=(string)$population['total'];$note=$population['note'];
        $mode=$input['population_mode']??'auto';if(!in_array($mode,['auto','manual'],true))throw new RuntimeException('Mode populasi tidak valid.');
        if($mode==='manual'){
            $pop=$this->text($input['population']??'',10);if(!ctype_digit($pop)||(int)$pop<1||(int)$pop>10000000)throw new RuntimeException('Populasi manual harus bilangan bulat positif, maksimal 10 juta.');
            $note=$this->text($input['population_note']??'',500);if($note==='')throw new RuntimeException('Alasan/sumber penetapan populasi manual wajib diisi.');
        }
        if($state==='open' && $mode==='auto' && (int)$pop<1)throw new RuntimeException('Pilih unit populasi pada /libraries atau tetapkan populasi manual sebelum membuka periode.');
        if($mode==='manual'&&!$this->db->field_exists('population_mode','iplm_periods'))throw new RuntimeException('Pengaturan populasi manual belum siap. Hubungi admin.');
        return $this->atomic(function()use($id,$year,$title,$input,$state,$confirmed,$pop,$note,$actor,$population,$mode){
            $old=$id?$this->period($id,true):null;if($id&&(!$old||(int)$old['version']!==(int)($input['version']??0)))throw new RuntimeException('Periode berubah. Muat ulang.');
            $date_changed=$old&&($old['start_date']!==$input['start_date']||$old['end_date']!==$input['end_date']);
            if($old&&$this->db->where('period_id',$id)->count_all_results('iplm_submissions')&&(int)$old['year']!==$year)throw new RuntimeException('Tahun periode yang sudah memiliki isian tidak boleh diubah.');
            if($date_changed&&$this->db->where('period_id',$id)->where('deleted_at IS NULL',null,false)->where_in('status',['submitted','verified'])->count_all_results('iplm_submissions'))throw new RuntimeException('Rentang periode yang memiliki isian terkirim/terverifikasi tidak boleh diubah. Kembalikan untuk revisi dahulu.');
            $data=['year'=>$year,'title'=>$title,'start_date'=>$input['start_date'],'end_date'=>$input['end_date'],'dates_confirmed'=>$confirmed?1:0,'population'=>$pop===''?null:(int)$pop,'population_note'=>$note,'state'=>$state,'version'=>$old?(int)$old['version']+1:1];
            if($this->db->field_exists('population_mode','iplm_periods'))$data+=['population_mode'=>$mode,'population_registry_count'=>$population['total']];
            $this->must($id?$this->db->where('id',$id)->update('iplm_periods',$data):$this->db->insert('iplm_periods',$data));
            if($date_changed){$note='Rentang periode diubah dari '.$old['start_date'].'–'.$old['end_date'].' menjadi '.$data['start_date'].'–'.$data['end_date'].'. Periksa ulang angka sebelum mengirim.';$this->must($this->db->where('period_id',$id)->where('deleted_at IS NULL',null,false)->set('version','version+1',false)->update('iplm_submissions',['status'=>'revision','review_note'=>$note,'resolutions_json'=>'{}','verified_at'=>null,'updated_at'=>date('Y-m-d H:i:s'),'updated_by'=>$actor]));}
            $this->history(null,$actor,'period.saved',['before'=>$old,'after'=>$data,'population_library_ids'=>$population['ids']]);
        });
    }
    public function save_field(array $input,$actor){
        $id=(int)($input['id']??0);$old=$id?$this->db->where('id',$id)->get('iplm_fields')->row_array():null;if($id&&!$old)throw new RuntimeException('Komponen tidak ditemukan.');
        $code=$old?$old['code']:$this->text($input['code']??'',80);if(!preg_match('/^[a-z][a-z0-9_]{2,79}$/D',$code))throw new RuntimeException('Kode gunakan huruf kecil/angka/underscore, minimal 3 karakter.');
        $kind=$old?$old['kind']:($input['kind']??'text');if(!in_array($kind,['text','number','money','select','url'],true))throw new RuntimeException('Jenis isian tidak valid.');
        $section=$input['section']??'';if(!isset($this->sections()[$section]))throw new RuntimeException('Kelompok tidak valid.');
        $options=array_values(array_unique(array_filter(array_map('trim',explode("\n",$this->text($input['options']??'',10000))))));
        if($kind==='select'&&!in_array($code,['library_type_id','library_subtype_id'],true)&&!$options)throw new RuntimeException('Pilihan dropdown wajib diisi.');
        $label=$this->text($input['label']??'',500);if($label==='')throw new RuntimeException('Label wajib diisi.');
        $data=['code'=>$code,'label'=>$label,'definition'=>$this->text($input['definition']??'',10000),'kind'=>$kind,'section'=>$section,'options_json'=>$this->json($options),'evidence_hint'=>$this->text($input['evidence_hint']??'',2000),'is_required'=>empty($input['is_required'])?0:1,'evidence_required'=>empty($input['evidence_required'])?0:1,'is_active'=>empty($input['is_active'])?0:1,'sort_order'=>(int)($input['sort_order']??100),'version'=>$old?(int)$old['version']+1:1];
        if(in_array($code,array_keys($this->identity_keys()),true)&&!$data['is_active'])throw new RuntimeException('Komponen identitas inti tidak boleh dinonaktifkan.');
        return $this->atomic(function()use($id,$old,$input,$data,$actor){
            if($id){$locked=$this->must($this->db->query('SELECT version FROM iplm_fields WHERE id=? FOR UPDATE',[$id]))->row();if((int)$locked->version!==(int)($input['version']??0))throw new RuntimeException('Komponen berubah. Muat ulang.');}
            $this->must($id?$this->db->where('id',$id)->update('iplm_fields',$data):$this->db->insert('iplm_fields',$data));$this->history(null,$actor,'field.saved',['before'=>$old,'after'=>$data]);
        });
    }
    private function identity_keys(){return array_fill_keys(['library_type_id','library_subtype_id','institution_name','npsn','npp','library_name','address','province','regency'],true);}
}
