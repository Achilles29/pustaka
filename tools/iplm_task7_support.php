<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require_once __DIR__.'/iplm_task5_support.php';
function task7_sources(){
 $all=json_decode(file_get_contents(FCPATH.'docs/iplm/sumber-task7.json'),true,512,JSON_THROW_ON_ERROR);
 foreach($all['hashes'] as$f=>$hash)network_check(hash_file('sha256',FCPATH.'docs/iplm/'.$f)===$hash,'Source changed; extract/review again');
 $schools=[];$backup=[];
 foreach($all['backup']as$r){network_check(!isset($backup[$r['NPSN']]),'Duplicate backup NPSN');$backup[$r['NPSN']]=$r;}
 foreach($all['update']as$r)$schools[$r['NPSN']]=['code'=>$r['NPSN'],'institution_name'=>$r['Nama Sekolah'],'subtype'=>strtolower($r['Bentuk Pendidikan']),'district'=>preg_replace('/^Kec\.\s*/i','',$r['Kecamatan']),'institution_status'=>strtolower($r['Status Sekolah']),'source'=>'sekolah_kabupaten_rembang.xlsx','row'=>$r['_row']];
 foreach($all['initial']as$r){
  // Initial Dapodik is authoritative for SD/SMP negeri; updated workbook covers the remainder.
  if(!isset($schools[$r['NPSN']])||(in_array($r['Bentuk Pendidikan'],['SD','SMP'],true)&&$r['Kepemilikan (Status)']==='Negeri'))$schools[$r['NPSN']]=['code'=>$r['NPSN'],'institution_name'=>$r['Nama Sekolah'],'subtype'=>strtolower($r['Bentuk Pendidikan']),'district'=>$r['Kecamatan'],'institution_status'=>strtolower($r['Kepemilikan (Status)']),'source'=>'dapodik_awal.xlsx','row'=>$r['_row'],'address'=>$r['Alamat'],'latitude'=>$r['Latitude'],'longitude'=>$r['Longitude']];
 }
 foreach($schools as$code=>&$s){
  $b=$backup[$code]??null;$s['backup']=$b;
  if($b){foreach(['address'=>'Alamat','village'=>'Desa','latitude'=>'Lintang','longitude'=>'Bujur']as$k=>$v)if(empty($s[$k]))$s[$k]=$b[$v];}
  $s['gps_valid']=task7_gps($s['latitude']??'',$s['longitude']??'');
 }unset($s);return ['schools'=>$schools,'hashes'=>$all['hashes']];
}
function task7_gps($lat,$lng){return is_numeric($lat)&&is_numeric($lng)&&(float)$lat>=-7.4&&(float)$lat<=-6.4&&(float)$lng>=110.9&&(float)$lng<=112;}
function task7_key($name){return task5_name(preg_replace('/^PERPUSTAKAAN\s+/iu','',$name));}
function task7_plan($db){
 $sources=task7_sources();$master=$db->order_by('id')->get('libraries')->result_array();$codes=[];$names=[];
 foreach($master as$l){$codes[$l['code']][]=$l;foreach([$l['institution_name'],$l['name']]as$n)if($n)foreach(task5_names($n,$l['district']??'')as$k)$names[$k.'|'.task7_key($l['district']??'')][$l['id']]=$l;}
 $approved=['20315657'=>1624,'20315667'=>1598,'20315824'=>1632,'20315837'=>1586,'20315658'=>1644];$byId=array_column($master,null,'id');$plans=[];
 foreach($sources['schools']as$code=>$s){
  $candidate=[];foreach(task5_names($s['institution_name'],$s['district'])as$k)$candidate+=$names[$k.'|'.task7_key($s['district'])]??[];
  $l=null;$note='';$status='IMPORT';
  if(isset($approved[$code])){$l=$byId[$approved[$code]]??null;network_check($l,'Approved library missing');$note='Keputusan eksplisit task7; ID dan relasi dipertahankan.';}
  elseif(isset($codes[$code])){
   $exact=$codes[$code];
   if(count($exact)===1){$e=$exact[0];$a=task5_names($s['institution_name'],$s['district']);$b=array_merge(task5_names($e['institution_name']?:$e['name'],$e['district']??''),task5_names($e['name'],$e['district']??''));
    if(array_intersect($a,$b))$l=$e;else{$status='TAHAN_IDENTITAS';$note='NPSN sama, nama berbeda. Tidak membuat duplikat atau memindahkan relasi.';}
   }else{$status='TAHAN_GANDA';$note='Kode master ganda.';}
  }elseif(count($candidate)===1){$l=reset($candidate);$note='Nama institusi dan kecamatan cocok unik.';}
  elseif(count($candidate)>1){$status='TAHAN_GANDA';$note='Beberapa kandidat master; tidak digabung otomatis.';}
  $changes=[];
  if($l){$status='PADANAN';foreach(['code','institution_name','institution_status']as$k)if((string)$l[$k]!==$s[$k])$changes[$k]=$s[$k];}
  $plans[]=['source'=>$s,'library_id'=>$l?(int)$l['id']:null,'status'=>$status,'changes'=>$changes,'note'=>$note,'candidates'=>array_keys($candidate)];
 }
 $claims=[];foreach($plans as$i=>$p)if($p['library_id'])$claims[$p['library_id']][]=$i;
 foreach($claims as$ids)if(count($ids)>1)foreach($ids as$i){$plans[$i]['status']='TAHAN_GANDA';$plans[$i]['note']='Satu unit cocok beberapa NPSN; perlu verifikasi.';$plans[$i]['changes']=[];$plans[$i]['library_id']=null;}
 return $plans;
}
function task7_legacy_map(){return [
 'Kode Pos'=>'postal_code','Fax'=>'fax','Mdpl'=>'altitude','Kelas Perpus'=>'library_class','Nama Kepala Perpus'=>'library_head','Sk Pendirian Perpus'=>'library_decree','Tahun Berdiri'=>'established_year','Nama Kepala Lembaga'=>'institution_head','Nama Kepala Sekolah'=>'school_head','Nis'=>'nis','No Induk Kepala Perpus'=>'head_registration','Sk Pendirian Lembaga Induk'=>'institution_decree','Jenis Lembaga'=>'institution_kind','Visi'=>'vision','Misi'=>'mission','Sistem Layanan'=>'service_system','Jumlah Jam Layanan'=>'weekly_hours','Zona Waktu'=>'timezone','Hari Layanan'=>'service_days','Tanggal Terbit Npp'=>'npp_date','Akreditasi Perpus'=>'accreditation','Perpustakaan Membuka Layanan Selama 7 Jam Setiap Hari Kerja'=>'seven_hour_service','Tersedia Anggaran Perpustakaan Minimal 5%'=>'five_percent_budget','Sudah Memanfaatkan Teknologi Informasi'=>'uses_it','Perpustakaan Telah Melakukan Otomasi'=>'automation','Perpustakaan Memiliki Fasilitas Internet Gratis'=>'free_internet','Perpustakaan Memiliki CCTV'=>'cctv','Jenis Bantuan'=>'assistance','Anggaran'=>'legacy_budget','Jumlah Santri'=>'santri','Jumlah Siswa'=>'students','Jumlah Mahasiswa'=>'university_students','Jumlah Rombongan'=>'study_groups','Jumlah Judul Koleksi'=>'collection_titles','Jumlah Eksemplar Koleksi'=>'collection_copies','Jumlah Pustakawan'=>'librarians','Jumlah Tenaga Teknis'=>'technical_staff','Jumlah Anggota'=>'members','Jumlah Kunjungan'=>'visits'];}
function task7_legacy_values($source){
 $config=[];require APPPATH.'config/library_survey.php';$values=[];$omitted=[];
 foreach(task7_legacy_map()as$col=>$key){$v=trim((string)($source[$col]??''));if($v===''||in_array(strtolower($v),['null','-'],true))continue;$type=$config['library_survey_fields'][$key][1];
  if($type==='boolean'){$v=['ya'=>'yes','tidak'=>'no','1'=>'yes','0'=>'no'][$v=strtolower($v)]??'unknown';}
  if($type==='number'&&!ctype_digit($v)){$omitted[]=$col;continue;}
  if($type==='year'&&(!ctype_digit($v)||(int)$v<1800||(int)$v>(int)date('Y'))){$omitted[]=$col;continue;}
  if($type==='date'){$d=DateTime::createFromFormat('!Y-m-d',$v);if(!$d||$d->format('Y-m-d')!==$v){$omitted[]=$col;continue;}}
  if($type==='select'&&!isset($config['library_survey_fields'][$key][2][$v])){$omitted[]=$col;continue;}
  $values[$key]=$v;
 }
 $values['notes']='Sumber pendataan lama ID '.$source['Id'].'. Tahun acuan angka belum dikonfirmasi. Periksa kembali sebelum menggunakan data.'.($omitted?' Kolom berformat tidak valid hanya disimpan dalam arsip: '.implode(', ',$omitted).'.':'');
 return $values;
}
