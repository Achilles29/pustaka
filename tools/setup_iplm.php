<?php
require __DIR__.'/library_network_bootstrap.php';
$directory=null;foreach($argv as $arg)if(strpos($arg,'--test=')===0)$directory=substr($arg,7);
if($directory!==null){network_check(preg_match('#^/tmp/pustaka_network_test_[a-zA-Z0-9_]+$#D',$directory)&&is_file($directory.'/connection.php'),'Private test runtime required.');$params=require $directory.'/connection.php';network_check(strpos($params['database'],'pustaka_network_test_')===0,'Test database required.');$db=DB($params);}else $db=DB();
$db->db_debug=false;
if(!in_array('--apply',$argv,true)){echo "DRY RUN: IPLM tables, reference taxonomy, scoped permissions and menus.\n";exit;}
$counts=[];foreach(['auth_user','libraries','books','book_items','members','network_books','network_items','network_members','network_loans','network_visits']as$table)$counts[$table]=(int)$db->count_all($table);
$identity_hash=hash('sha256',json_encode(network_query($db,'SELECT id,code,name,address,latitude,longitude,status,is_verified FROM libraries ORDER BY id')->result_array()));
$account_hash=hash('sha256',json_encode(network_query($db,'SELECT id,username,password_hash,library_id,status FROM auth_user ORDER BY id')->result_array()));
if(!$directory){$backup=network_backup($db,'sql/2026-10-05d_iplm.sql');echo 'Backup: '.$backup.'.sql.gz',PHP_EOL;}
foreach(preg_split('/;\s*(?:\r?\n|$)/',file_get_contents(FCPATH.'sql/2026-10-05d_iplm.sql'))as$sql)if(trim($sql)!=='')network_query($db,$sql);
$db->trans_begin();
try{
    foreach([['umum','Perpustakaan Umum',1,'#2563eb'],['sekolah','Perpustakaan Sekolah',2,'#16a34a'],['khusus','Perpustakaan Khusus',3,'#9333ea']]as$t){
        $existing=$db->where('code',$t[0])->get('library_types')->row_array();
        if(!$existing)network_check($db->insert('library_types',['code'=>$t[0],'name'=>$t[1],'sort_order'=>$t[2],'marker_color'=>$t[3]]),'Cannot seed library type.');
        else network_check($db->where('id',$existing['id'])->update('library_types',['sort_order'=>$t[2]]),'Cannot order type.');
    }
    $types=array_column($db->get('library_types')->result_array(),'id','code');
    $subtypes=[['umum','kabupaten','Perpustakaan Kabupaten/Kota',1,1],['umum','kecamatan','Perpustakaan Kecamatan',2,1],['umum','desa_kelurahan','Perpustakaan Desa/Kelurahan',3,1],['umum','tbm','Perpustakaan TBM/Rumah Baca/penamaan lainnya',4,1],['sekolah','smp','Perpustakaan SMP (Negeri maupun Swasta)',1,1],['sekolah','sd','Perpustakaan SD (Negeri maupun Swasta)',2,1],['khusus','opd_kabupaten','OPD Kabupaten',1,1],['sekolah','tk','Perpustakaan TK',3,0],['sekolah','skb','Perpustakaan SKB',4,0]];
    foreach($subtypes as$s)if(!$db->where('code',$s[1])->count_all_results('library_subtypes'))network_check($db->insert('library_subtypes',['library_type_id'=>$types[$s[0]],'code'=>$s[1],'name'=>$s[2],'sort_order'=>$s[3],'iplm_eligible'=>$s[4],'description'=>$s[4]?'Template IPLM Kab-Kota 2026':'Perluasan lokal; tidak otomatis masuk perhitungan IPLM kabupaten.']),'Cannot seed subtype.');
    $subs=array_column($db->get('library_subtypes')->result_array(),null,'code');$mapped=[];
    foreach($db->get('libraries')->result_array()as$l){
        if(!empty($l['library_subtype_id']))continue;
        $sub=null;
        if($l['source_system']==='school_xlsx'&&preg_match('/^(SD|SMP|TK|SKB)\b/i',$l['name'],$m))$sub=strtolower($m[1]);
        if($l['library_type_id']==($types['perpusda']??0))$sub='kabupaten';
        if(!$sub)continue;
        $data=['library_type_id'=>$subs[$sub]['library_type_id'],'library_subtype_id'=>$subs[$sub]['id']];
        if($l['source_system']==='school_xlsx'&&empty($l['institution_name']))$data['institution_name']=$l['name'];
        network_check($db->where('id',$l['id'])->update('libraries',$data),'Cannot classify library.');$mapped[]=['id'=>$l['id'],'before_type'=>$l['library_type_id'],'after'=>$data];
    }
    $source=json_decode(file_get_contents(FCPATH.'docs/iplm/pemetaan-template.json'),true,512,JSON_THROW_ON_ERROR);
    foreach($source['fields']as$f){
        if($db->where('code',$f['code'])->count_all_results('iplm_fields'))continue;
        $f['is_required']=in_array($f['code'],['library_type_id','library_subtype_id','institution_name','library_name','address','province','regency','respondent_name','respondent_phone'],true)||in_array($f['kind'],['number','money'],true)?1:0;
        if(in_array($f['code'],['teachers','students','employees'],true))$f['is_required']=0;
        if($f['code']==='staff_qualified')$f['definition'].="\nCatatan verifikasi: template menyebut D2, sedangkan Perpusnas 7/2025 Pasal 14 menyebut paling rendah D3. Gunakan minimal D3 sambil menunggu konfirmasi pedoman teknis 2026.";
        if($f['code']==='library_users')$f['definition'].=' Isian orang tidak otomatis disamakan dengan banyaknya transaksi kunjungan; jangan memasukkan data simulasi.';
        if($f['code']==='province'||$f['code']==='regency'){$prefix=$f['code']==='province'?'A':'D';$options=[];foreach($source['validation']as$cell=>$value)if(preg_match('/^'.$prefix.'([0-9]+)$/',$cell,$m)&&(int)$m[1]>=2)$options[]=$value;$f['options_json']=json_encode($options,JSON_UNESCAPED_UNICODE);}
        network_check($db->insert('iplm_fields',$f),'Cannot seed field.');
    }
    if(!$db->where('year',2026)->count_all_results('iplm_periods'))network_check($db->insert('iplm_periods',['year'=>2026,'title'=>'Pendataan IPLM 2026','start_date'=>'2026-01-01','end_date'=>'2026-12-31','state'=>'draft','dates_confirmed'=>0]),'Cannot seed draft period.');
    foreach([['iplm.local','Pendataan IPLM','iplm'],['iplm.manage','Rekap & Verifikasi IPLM','iplm'],['iplm.settings','Pengaturan Form IPLM','iplm/settings'],['library.types','Jenis & Subjenis Perpustakaan','library-types']]as$p){
        if(!$db->where('code',$p[0])->count_all_results('sys_page'))network_check($db->insert('sys_page',['code'=>$p[0],'module'=>'IPLM','title'=>$p[1],'route'=>$p[2]]),'Cannot register page.');
        $pid=$db->where('code',$p[0])->get('sys_page')->row()->id;
        $roles=$db->where_in('code',$p[0]==='iplm.local'?['LIBRARY_ADMIN']:['ADMIN','SUPERADMIN'])->get('auth_role')->result_array();
        foreach($roles as$r)if(!$db->where(['role_id'=>$r['id'],'page_id'=>$pid])->count_all_results('auth_role_permission'))network_check($db->insert('auth_role_permission',['role_id'=>$r['id'],'page_id'=>$pid,'can_view'=>1,'can_create'=>1,'can_edit'=>1,'can_delete'=>$p[0]==='iplm.local'?0:1,'can_export'=>1,'can_approve'=>$p[0]==='iplm.local'?0:1]),'Cannot register permission.');
        $key='menu.'.$p[0];if(!$db->where('menu_key',$key)->count_all_results('sys_menu')){
            $parentkey=$p[0]==='iplm.local'?'network.group.reporting':($p[0]==='library.types'?'master-data':'iplm.group');
            if(!$db->where('menu_key',$parentkey)->count_all_results('sys_menu'))network_check($db->insert('sys_menu',['menu_area'=>$p[0]==='iplm.local'?'LIBRARY':'MAIN','menu_key'=>$parentkey,'title'=>$p[0]==='iplm.local'?'Laporan & Pendataan':'Pendataan IPLM','icon'=>'ti ti-clipboard-data','sort_order'=>$p[0]==='iplm.local'?70:29]),'Cannot add group.');
            $parent=$db->where('menu_key',$parentkey)->get('sys_menu')->row()->id;
            network_check($db->insert('sys_menu',['parent_id'=>$parent,'page_id'=>$pid,'menu_area'=>$p[0]==='iplm.local'?'LIBRARY':'MAIN','menu_key'=>$key,'title'=>$p[1],'url'=>$p[2],'icon'=>'ti ti-clipboard-data','sort_order'=>$p[0]==='iplm.settings'?20:10]),'Cannot add menu.');
            if($p[0]==='iplm.local')$db->where('menu_key','network.reports')->update('sys_menu',['parent_id'=>$parent,'sort_order'=>5]);
        }
    }
    network_check($db->trans_status(),'IPLM transaction failed.');$db->trans_commit();
    if(!$directory)file_put_contents($backup.'.iplm-mapping.json',json_encode($mapped,JSON_PRETTY_PRINT));
    echo 'IPLM prepared; '.count($mapped).' libraries classified; old types and source IDs retained. Period 2026 remains draft.',PHP_EOL;
}catch(Throwable $e){$db->trans_rollback();throw $e;}
if(!network_query($db,"SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='libraries' AND CONSTRAINT_NAME='fk_libraries_subtype_pair'")->num_rows())network_query($db,'ALTER TABLE libraries ADD CONSTRAINT fk_libraries_subtype_pair FOREIGN KEY (library_subtype_id,library_type_id) REFERENCES library_subtypes(id,library_type_id)');
$after=[];foreach($counts as$table=>$n)$after[$table]=(int)$db->count_all($table);
$verification=['installed_at'=>date(DATE_ATOM),'setup_sha256'=>hash_file('sha256',__FILE__),'before_counts'=>$counts,'after_counts'=>$after,'counts_unchanged'=>$counts===$after,'identities_unchanged'=>$identity_hash===hash('sha256',json_encode(network_query($db,'SELECT id,code,name,address,latitude,longitude,status,is_verified FROM libraries ORDER BY id')->result_array())),'accounts_unchanged'=>$account_hash===hash('sha256',json_encode(network_query($db,'SELECT id,username,password_hash,library_id,status FROM auth_user ORDER BY id')->result_array())),'production_submissions'=>(int)$db->count_all('iplm_submissions')];
if(!$directory)file_put_contents($backup.'.iplm-verification.json',json_encode($verification,JSON_PRETTY_PRINT));
echo 'Preserved checks: counts='.($verification['counts_unchanged']?'yes':'review concurrent changes').', identities='.($verification['identities_unchanged']?'yes':'review concurrent changes').', accounts='.($verification['accounts_unchanged']?'yes':'review concurrent changes').PHP_EOL;
