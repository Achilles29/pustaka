<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Library_workspace extends MY_Controller
{
    private $scope;
    private $library;

    public function __construct()
    {
        parent::__construct();
        $this->require_permission('library.workspace','view');
        $this->load->model('Library_network_model','network');
        $this->db->db_debug=false;
        $requested=$this->input->get('library_id',true);
        if($requested!==null&&!is_scalar($requested)){show_error('Cakupan tidak valid.',403);exit;}
        if($this->is_superadmin()) $this->scope=ctype_digit((string)$requested)?(int)$requested:0;
        else {
            if(empty($this->current_user['is_library_admin'])){show_error('Penugasan Admin Perpustakaan diperlukan.',403);exit;}
            $this->scope=(int)($this->current_user['library_id']??0);
            if($requested!==null && (string)$requested!==(string)$this->scope){show_error('Cakupan perpustakaan tidak boleh diubah.',403);exit;}
        }
        $this->library=$this->scope>0?$this->network->library($this->scope):null;
        if($this->scope>0&&!$this->library){show_404();exit;}
        if(!$this->session->userdata('network_csrf'))$this->session->set_userdata('network_csrf',bin2hex(random_bytes(32)));
    }

    private function selected()
    {
        if(!$this->library){redirect('library-workspace');exit;}
    }

    private function url($path='')
    {
        return base_url('library-workspace'.($path!==''?'/'.$path:'')).($this->scope?'?library_id='.$this->scope:'');
    }

    private function post($action='edit')
    {
        $this->require_permission('library.workspace',$action);
        if($this->input->method(true)!=='POST'){show_error('Gunakan formulir untuk tindakan ini.',405);exit;}
        $token=$this->input->post('network_csrf',false);
        if(!is_string($token)||!hash_equals((string)$this->session->userdata('network_csrf'),$token)){show_error('Token formulir tidak valid. Muat ulang halaman.',403);exit;}
        // Sessions are locked by CI: consume the form token to reject double submissions.
        $this->session->set_userdata('network_csrf',bin2hex(random_bytes(32)));
    }

    private function input()
    {
        $input=(array)$this->input->post(null,false);
        foreach($input as $value)if(!is_scalar($value)&&$value!==null)throw new RuntimeException('Isian formulir tidak valid.');
        return $input;
    }

    private function query_text($key)
    {
        $value=$this->input->get($key,true);
        return is_scalar($value)?trim((string)$value):'';
    }

    private function page($template,array $data=[])
    {
        $data['network_label']=function($value){return ['physical'=>'Fisik','digital'=>'Digital','draft'=>'Draf','published'=>'Tayang','available'=>'Tersedia','loaned'=>'Dipinjam','damaged'=>'Rusak','missing'=>'Hilang','withdrawn'=>'Ditarik','active'=>'Aktif','inactive'=>'Nonaktif','blocked'=>'Diblokir','L'=>'Laki-laki','P'=>'Perempuan'][$value??'']??($value?:'—');};
        $data+=['title'=>'Operasional Perpustakaan','library'=>$this->library,'scope'=>$this->scope,'network_url'=>function($path=''){return $this->url($path);},'csrf'=>$this->session->userdata('network_csrf'),'is_central'=>$this->is_superadmin()];
        $this->render('library_workspace/'.$template,$data);
    }

    public function index()
    {
        if(!$this->library){
            if(!$this->is_superadmin()){show_error('Perpustakaan belum ditugaskan.',403);return;}
            $q=mb_substr($this->query_text('q'),0,180);
            $this->db->select('l.id,l.code,l.name,l.district,l.village,l.status,(SELECT COUNT(*) FROM network_members m WHERE m.library_id=l.id AND m.deleted_at IS NULL) AS members,(SELECT COUNT(*) FROM network_books b WHERE b.library_id=l.id AND b.deleted_at IS NULL) AS books',false)->from('libraries l');
            if($q!=='')$this->db->group_start()->like('l.name',$q)->or_like('l.code',$q)->or_like('l.district',$q)->group_end();
            $rows=$this->db->order_by('l.name')->get()->result_array();
            $this->page('hub',['libraries'=>$rows,'q'=>$q]);return;
        }
        $this->page('dashboard',['report'=>$this->network->report($this->scope,(int)date('Y')),'settings'=>$this->network->settings($this->scope)]);
    }

    public function records($entity='books')
    {
        $this->selected();$definitions=$this->network->definitions();if(!isset($definitions[$entity])){show_404();return;}
        $per=(int)$this->input->get('per_page');if(!in_array($per,[10,25,50,100],true))$per=25;
        $page=max(1,(int)$this->input->get('page'));$q=mb_substr($this->query_text('q'),0,180);
        $result=$this->network->listing($this->scope,$entity,$q,$per,($page-1)*$per);
        $pages=max(1,(int)ceil($result['total']/$per));
        if($page>$pages){$page=$pages;$result=$this->network->listing($this->scope,$entity,$q,$per,($page-1)*$per);}
        $this->page('records',['entity'=>$entity,'definition'=>$definitions[$entity],'result'=>$result,'q'=>$q,'per_page'=>$per,'page'=>$page,'pages'=>$pages]);
    }

    public function edit($entity='books',$id=0)
    {
        $this->selected();$defs=$this->network->definitions();if(!isset($defs[$entity])){show_404();return;}
        $id=(int)$id;$row=$id?$this->network->find($this->scope,$entity,$id):null;
        if($id&&!$row){show_404();return;}
        if($this->input->method(true)==='POST'){
            $this->post($id?'edit':'create');
            $input=[];
            try{$input=$this->input();$id=$this->network->save($this->scope,$entity,$id,$input,(int)$this->current_user['id']);$this->session->set_flashdata('success','Data berhasil disimpan.');redirect($this->url('records/'.$entity));return;}
            catch(Throwable $e){$this->session->set_flashdata('error',$e->getMessage());$row=array_merge($row?:[],$input);}
        }
        if(!$id&&$entity==='items'&&$this->input->get('book_id'))$row=['book_id'=>(int)$this->input->get('book_id')];
        $books=$entity==='items'?$this->network->listing($this->scope,'books','',1000)['rows']:[];
        $books=array_column($books,null,'id');
        if($entity==='items'&&!empty($row['book_id'])){$selected=$this->network->find($this->scope,'books',$row['book_id']);if($selected)$books[$selected['id']]=$selected;}
        $this->page('edit',['entity'=>$entity,'definition'=>$defs[$entity],'row'=>$row,'record_id'=>$id,'books'=>$books]);
    }

    public function archive($entity,$id)
    {
        $this->selected();$this->post('delete');
        try{$this->network->archive($this->scope,$entity,(int)$id,(int)$this->current_user['id']);$this->session->set_flashdata('success','Data diarsipkan; histori tetap disimpan.');}
        catch(Throwable $e){$this->session->set_flashdata('error',$e->getMessage());}
        redirect($this->url('records/'.(isset($this->network->definitions()[$entity])?$entity:'books')));
    }

    public function loans()
    {
        $this->selected();
        if($this->input->method(true)==='POST'){$this->post('create');try{$input=$this->input();$this->network->issue($this->scope,$input['member_no']??'',$input['barcode']??'',(int)$this->current_user['id']);$this->session->set_flashdata('success','Peminjaman berhasil dicatat.');}catch(Throwable $e){$this->session->set_flashdata('error',$e->getMessage());}redirect($this->url('loans'));return;}
        $state=$this->query_text('state');if(!in_array($state,['active','returned','overdue','all'],true))$state='active';$page=max(1,(int)$this->input->get('page'));
        $result=$this->network->loans($this->scope,$state,25,($page-1)*25);
        $this->page('loans',['state'=>$state,'result'=>$result,'page'=>$page,'pages'=>max(1,(int)ceil($result['total']/25)),'settings'=>$this->network->settings($this->scope)]);
    }

    public function loan_action($id,$action)
    {
        $this->selected();$this->post();
        try{$input=$this->input();$this->network->loan_action($this->scope,(int)$id,$action,(int)$this->current_user['id'],$input['note']??'');$this->session->set_flashdata('success',$action==='return'?'Pengembalian berhasil.':'Pinjaman berhasil diperpanjang.');}
        catch(Throwable $e){$this->session->set_flashdata('error',$e->getMessage());}redirect($this->url('loans'));
    }

    public function visits()
    {
        $this->selected();
        if($this->input->method(true)==='POST'){$this->post('create');try{$this->network->visit($this->scope,$this->input(),(int)$this->current_user['id']);$this->session->set_flashdata('success','Kunjungan berhasil dicatat.');}catch(Throwable $e){$this->session->set_flashdata('error',$e->getMessage());}redirect($this->url('visits'));return;}
        $page=max(1,(int)$this->input->get('page'));$result=$this->network->visits($this->scope,25,($page-1)*25);
        $this->page('visits',['result'=>$result,'purposes'=>$this->network->purposes(),'page'=>$page,'pages'=>max(1,(int)ceil($result['total']/25))]);
    }

    public function settings()
    {
        $this->selected();
        if($this->input->method(true)==='POST'){$this->post();try{$this->network->save_settings($this->scope,$this->input(),(int)$this->current_user['id']);$this->session->set_flashdata('success','Aturan peminjaman disimpan.');}catch(Throwable $e){$this->session->set_flashdata('error',$e->getMessage());}redirect($this->url('settings'));return;}
        $this->page('settings',['settings'=>$this->network->settings($this->scope)]);
    }

    public function profile()
    {
        $this->selected();
        $profile_error=null;$values=$this->library;
        if($this->input->method(true)==='POST'){
            $this->post();$input=$this->input();
            try{$this->network->save_profile($this->scope,$input,(int)$this->current_user['id']);$this->session->set_flashdata('success','Profil perpustakaan diperbarui.');redirect($this->url('profile'));return;}
            catch(Throwable $e){$profile_error=$e->getMessage();$values=array_replace($values,array_intersect_key(array_filter($input,'is_scalar'),array_flip(['manager_name','phone','email','website','address','opening_hours','facilities','description'])));}
        }
        $this->load->model('Library_model');
        $this->page('profile',['taxonomy'=>$this->Library_model->get_library($this->scope),'library'=>$values,'profile_error'=>$profile_error]);
    }

    public function guide()
    {
        $this->selected();$this->page('guide');
    }

    public function survey()
    {
        $this->selected();$this->load->model('Library_survey_model','survey_model');
        $survey=$this->survey_model->get($this->scope);$error=null;
        if($this->input->method(true)==='POST'){
            $this->post();$input=$this->input();
            try{
                $this->survey_model->save($this->scope,$input,$input['survey_version']??'',(int)$this->current_user['id']);
                $this->session->set_flashdata('success','Pendataan tambahan disimpan.');redirect($this->url('survey'));return;
            }catch(Throwable $e){$error=$e->getMessage();$survey['values']=array_filter($input,'is_scalar');if(isset($input['survey_version'])&&is_scalar($input['survey_version'])&&ctype_digit((string)$input['survey_version']))$survey['version']=(int)$input['survey_version'];}
        }
        $this->page('survey',['survey'=>$survey,'survey_fields'=>$this->survey_model->fields(),'survey_error'=>$error]);
    }

    public function admins()
    {
        $this->selected();
        if($this->input->method(true)==='POST'){$this->post('create');try{$this->network->create_admin($this->scope,$this->input(),(int)$this->current_user['id']);$this->session->set_flashdata('success','Admin ditambahkan dengan peran dan scope yang sama. Wajib mengganti password awal.');}catch(Throwable $e){$this->session->set_flashdata('error',$e->getMessage());}redirect($this->url('admins'));return;}
        $this->page('admins',['admins'=>$this->network->admins($this->scope)]);
    }

    public function toggle_admin($id)
    {
        $this->selected();$this->post();
        try{$this->network->toggle_admin($this->scope,(int)$id,(int)$this->current_user['id']);$this->session->set_flashdata('success','Status admin diperbarui.');}catch(Throwable $e){$this->session->set_flashdata('error',$e->getMessage());}redirect($this->url('admins'));
    }

    public function account()
    {
        $this->load->model('Auth_model');
        $grant=$this->session->userdata('activation_password_grant');
        $activation_setup=is_array($grant)&&($grant['user_id']??0)===(int)$this->current_user['id']&&($grant['library_id']??0)===(int)$this->current_user['library_id']&&($grant['expires']??0)>time()&&!empty($this->current_user['force_password_change'])&&hash_equals((string)($grant['stamp']??''),(string)$this->session->userdata('network_password_stamp'));
        if($this->input->method(true)==='POST'){
            $this->post();
            try{
                $input=$this->input();$row=$this->Auth_model->get_user_with_password((int)$this->current_user['id']);$password=(string)($input['password']??'');
                if(!$row||($activation_setup&&!hash_equals($grant['stamp'],hash('sha256',$row['password_hash'])))||(!$activation_setup&&!password_verify((string)($input['current_password']??''),$row['password_hash'])))throw new RuntimeException('Password saat ini tidak sesuai atau sesi aktivasi habis. Hubungi admin kabupaten jika aktivasi belum selesai.');
                if(strlen($password)<10||strlen($password)>72||$password!==($input['confirmation']??''))throw new RuntimeException('Password baru 10–72 karakter dan konfirmasi harus sama.');
                if(password_verify($password,$row['password_hash']))throw new RuntimeException('Password baru harus berbeda dari password awal/lama.');
                if(!$this->Auth_model->update_password((int)$row['id'],$password,$row['password_hash']))throw new RuntimeException('Password berubah bersamaan. Muat ulang atau hubungi admin kabupaten.');
                $user=$this->current_user;$user['force_password_change']=0;$this->session->sess_regenerate(true);$this->session->set_userdata('auth_user',$user);
                $fresh=$this->Auth_model->get_user_with_password((int)$row['id']);$this->session->set_userdata('network_password_stamp',hash('sha256',$fresh['password_hash']));
                $this->Auth_model->write_event('password_changed',(int)$row['id']);
                $this->session->unset_userdata('activation_password_grant');
                if($this->scope)$this->network->audit($this->scope,(int)$row['id'],'password.changed',(int)$row['id']);
                $this->session->set_flashdata('success','Password pribadi berhasil disimpan.');redirect($this->url());return;
            }catch(Throwable $e){$this->session->set_flashdata('error',$e->getMessage());}
        }
        $this->page('account',['activation_setup'=>$activation_setup]);
    }

    public function reports()
    {
        $this->selected();$year=(int)$this->input->get('year');if($year<2000||$year>2100)$year=(int)date('Y');
        $this->page('reports',['year'=>$year,'report'=>$this->network->report($this->scope,$year)]);
    }

    public function export($entity='books',$format='xlsx')
    {
        $this->selected();$this->require_permission('library.workspace','export');
        if(!in_array($format,['csv','xlsx'],true)){show_404();return;}
        $defs=$this->network->definitions();$q=mb_substr($this->query_text('q'),0,180);
        if(isset($defs[$entity]))$columns=$defs[$entity]['columns'];
        elseif($entity==='loans')$columns=['id'=>'Transaksi','member_no'=>'Anggota','full_name'=>'Nama','barcode'=>'Barcode','title'=>'Judul','loaned_at'=>'Pinjam','due_at'=>'Jatuh tempo','returned_at'=>'Kembali'];
        elseif($entity==='visits')$columns=['visited_at'=>'Waktu','visitor_name'=>'Pengunjung','member_no'=>'Anggota','purpose'=>'Tujuan','channel'=>'Kanal'];
        elseif($entity==='reports')$columns=['month'=>'Bulan','loans'=>'Peminjaman','returns'=>'Pengembalian','offline'=>'Kunjungan offline','online'=>'Kunjungan online'];
        else{show_404();return;}
        $state=$this->query_text('state');if(!in_array($state,['active','returned','overdue','all'],true))$state='active';
        $year=(int)$this->input->get('year');if($year<2000||$year>2100)$year=(int)date('Y');
        $rows=(function()use($entity,$q,$columns,$state,$year){
            if($entity==='reports'){foreach($this->network->report($this->scope,$year)['months']as$row)yield array_values($row);return;}
            $offset=0;do{
                if($entity==='loans')$batch=$this->network->loans($this->scope,$state,500,$offset)['rows'];
                elseif($entity==='visits')$batch=$this->network->visits($this->scope,500,$offset)['rows'];
                else$batch=$this->network->listing($this->scope,$entity,$q,500,$offset)['rows'];
                foreach($batch as$row){$values=[];foreach($columns as$key=>$label)$values[]=$row[$key]??'';yield $values;}$offset+=count($batch);
            }while(count($batch)===500);
        })();
        $name='perpustakaan-'.$this->scope.'-'.$entity.'-'.date('Ymd');
        header('Cache-Control: private, no-store');
        if($format==='xlsx'){
            $this->load->library('Catalog_xlsx');$path=$this->catalog_xlsx->build('Perpustakaan',array_values($columns),$rows);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');header('Content-Disposition: attachment; filename="'.$name.'.xlsx"');header('Content-Length: '.filesize($path));readfile($path);unlink($path);
        }else{
            header('Content-Type: text/csv; charset=utf-8');header('Content-Disposition: attachment; filename="'.$name.'.csv"');$out=fopen('php://output','w');fwrite($out,"\xEF\xBB\xBF");fputcsv($out,array_values($columns));
            foreach($rows as$row)fputcsv($out,array_map(function($v){$v=(string)$v;return preg_match('/^[\s]*[=+@-]/u',$v)?"'".$v:$v;},$row));fclose($out);
        }
    }

    public function import($entity='books')
    {
        $this->selected();$defs=$this->network->definitions();if(!isset($defs[$entity])){show_404();return;}$this->require_permission('library.workspace','create');
        $headers=array_keys($defs[$entity]['fields']);
        if($this->input->get('template')==='1'){
            $this->load->library('Catalog_xlsx');$path=$this->catalog_xlsx->build('Data',$headers,[]);header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');header('Content-Disposition: attachment; filename="template-'.$entity.'.xlsx"');readfile($path);unlink($path);return;
        }
        $key='network_import_'.$this->scope.'_'.$entity;
        if($this->input->method(true)==='POST'){
            $this->post('create');
            try{
                if($this->input->post('commit')==='1'){
                    $preview=$this->session->userdata($key);$nonce=$this->input->post('preview_nonce');
                    if(!$preview||$preview['expires']<time()||!is_string($nonce)||!hash_equals($preview['nonce'],$nonce))throw new RuntimeException('Pratinjau kedaluwarsa/sudah dipakai. Unggah ulang berkas.');
                    $this->session->unset_userdata($key);
                    $count=$this->network->import_rows($this->scope,$entity,$preview['rows'],(int)$this->current_user['id']);$this->session->set_flashdata('success',$count.' baris berhasil diimpor.');redirect($this->url('records/'.$entity));return;
                }
                $this->session->unset_userdata($key);$upload=$_FILES['xlsx']??null;
                if(!$upload||$upload['error']!==UPLOAD_ERR_OK||!is_uploaded_file($upload['tmp_name'])||strtolower(pathinfo($upload['name'],PATHINFO_EXTENSION))!=='xlsx')throw new RuntimeException('Unggah berkas .xlsx yang valid.');
                $this->load->library('Network_xlsx');$rows=$this->network_xlsx->read($upload['tmp_name'],$headers);
                foreach($rows as $index=>$row){try{$this->network->validate($entity,$row);}catch(Throwable $e){throw new RuntimeException('Baris '.($index+2).': '.$e->getMessage());}}
                $this->session->set_userdata($key,['rows'=>$rows,'nonce'=>bin2hex(random_bytes(24)),'expires'=>time()+1200]);
                $this->session->set_flashdata('success','Pratinjau siap. Belum ada data disimpan.');
            }catch(Throwable $e){$this->session->set_flashdata('error',$e->getMessage());}
        }
        $this->page('import',['entity'=>$entity,'definition'=>$defs[$entity],'preview'=>$this->session->userdata($key)]);
    }
}
