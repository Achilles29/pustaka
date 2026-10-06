<?php
/** Read-only DB reconciliation. Writes derived workbooks/reports, never source/master data. */
require __DIR__.'/library_network_bootstrap.php';require __DIR__.'/iplm_task2_support.php';require APPPATH.'libraries/Catalog_xlsx.php';
$db=DB('default');$db->db_debug=false;$folder=FCPATH.'docs/iplm/';
$source=$folder.'pendataan.xls';$dom=new DOMDocument();$previous=libxml_use_internal_errors(true);network_check($dom->loadHTMLFile($source,LIBXML_NONET),'Cannot parse HTML-format XLS.');libxml_clear_errors();libxml_use_internal_errors($previous);
$xpath=new DOMXPath($dom);$rows=[];$headers=null;
foreach($xpath->query('//tr')as$tr){$values=[];foreach($xpath->query('./th|./td',$tr)as$cell)$values[]=trim(preg_replace('/\s+/u',' ',$cell->textContent));if(!$values)continue;if($headers===null){$headers=$values;continue;}network_check(count($values)===count($headers),'Unexpected source column count.');$rows[]=array_combine($headers,$values);}
network_check(count($headers)===60&&count($rows)>0,'Unexpected pendataan shape.');
$libraries=network_query($db,'SELECT l.*,s.code subtype_code,s.iplm_eligible,COALESCE(d.name,l.district) district_name,COALESCE(v.name,l.village) village_name FROM libraries l LEFT JOIN library_subtypes s ON s.id=l.library_subtype_id LEFT JOIN ref_districts d ON d.id=l.district_id LEFT JOIN ref_villages v ON v.id=l.village_id ORDER BY l.id')->result_array();
function pendataan_normalize($v){
    $v=mb_strtoupper(trim($v));$v=preg_replace('/[^\pL\pN]+/u',' ',$v);
    $v=preg_replace(['/\bSEKOLAH DASAR\b/u','/\bSEKOLAH MENENGAH PERTAMA\b/u','/\bSD\s*N(?:EGERI)?\b/u','/\bSMP\s*N(?:EGERI)?\b/u'],['SD','SMP','SD NEGERI','SMP NEGERI'],$v);
    $v=preg_replace('/([A-Z])(\d)/u','$1 $2',$v);$v=preg_replace('/\b0+(\d+)\b/','$1',$v);$v=trim(preg_replace('/\s+/',' ',$v));
    if(preg_match('/^(SD|SMP)\b/',$v))$v=preg_replace_callback('/\b(VIII|VII|III|IX|VI|IV|II|V|I)\b/',function($m){return ['I'=>1,'II'=>2,'III'=>3,'IV'=>4,'V'=>5,'VI'=>6,'VII'=>7,'VIII'=>8,'IX'=>9][$m[1]];},$v);
    // Number placement differs across institutional naming conventions.
    $v=preg_replace('/^((?:SD|SMP) NEGERI) ([A-Z ]+?) (\d+)$/','$1 $3 $2',$v);return $v;
}
function pendataan_distance($a,$b,$c,$d){foreach([$a,$b,$c,$d]as$v)if(!is_numeric($v))return null;if(abs((float)$a)>90||abs((float)$c)>90||abs((float)$b)>180||abs((float)$d)>180||((float)$a===0.0&&(float)$b===0.0)||((float)$c===0.0&&(float)$d===0.0))return null;$lat=deg2rad((float)$c-(float)$a);$lon=deg2rad((float)$d-(float)$b);$h=sin($lat/2)**2+cos(deg2rad((float)$a))*cos(deg2rad((float)$c))*sin($lon/2)**2;return round(6371000*2*asin(min(1,sqrt($h))));}
$byId=array_column($libraries,null,'id');$npsn=[];$npp=[];$names=[];
foreach($libraries as$l){if(preg_match('/^\d{8}$/D',$l['code']))$npsn[$l['code']][]=$l['id'];if($l['npp'])$npp[trim($l['npp'])][]=$l['id'];foreach(array_unique([$l['name'],$l['institution_name']?:$l['name']])as$name)$names[pendataan_normalize($name)][$l['id']]=$l['id'];}
$matches=[];$matched=[];$candidateIds=[];$stats=[];$subtypes=[];
foreach($rows as$i=>$r){
    $candidates=[];$method='';$district=pendataan_normalize($r['Kecamatan']);$rnpsn=trim($r['NPSN']);
    if($rnpsn!==''&&isset($npsn[$rnpsn])){$candidates=$npsn[$rnpsn];$method='NPSN';}
    elseif($r['Npp']!==''&&isset($npp[$r['Npp']])){$candidates=$npp[$r['Npp']];$method='NPP';}
    else{
        foreach([$r['Lembaga Induk'],$r['Nama']]as$name){$normalized=pendataan_normalize($name);if(isset($names[$normalized]))$candidates=array_merge($candidates,array_values($names[$normalized]));}
        if($candidates)$method='nama normalisasi persis';
        if(!$candidates){foreach([$r['Lembaga Induk'],$r['Nama']]as$value)if(preg_match('/\b((?:SD|SMP|TK|SKB) .+)$/u',pendataan_normalize($value),$fragment)){$name=pendataan_normalize($fragment[1]);if(isset($names[$name]))$candidates=array_merge($candidates,array_values($names[$name]));}if($candidates)$method='nama sekolah lengkap dalam nama perpustakaan';}
        if(!$candidates&&in_array($r['Subjenis'],['SD','SMP'],true)){
            $sourceNames=[];foreach([$r['Lembaga Induk'],$r['Nama']]as$value){$value=pendataan_normalize($value);if(preg_match('/\b((?:SD|SMP) .+)$/u',$value,$fragment))$sourceNames[]=str_replace(' ','',pendataan_normalize($fragment[1]));}
            foreach($libraries as$l)if($l['subtype_code']===strtolower($r['Subjenis'])&&pendataan_normalize($l['district_name'])===$district&&in_array(str_replace(' ','',pendataan_normalize($l['name'])),$sourceNames,true))$candidates[]=$l['id'];
            if($candidates)$method='nama sekolah persis tanpa variasi spasi + kecamatan + subjenis';
        }
    }
    $candidates=array_values(array_unique($candidates));$sameDistrict=array_values(array_filter($candidates,function($id)use($district,$byId){return $district!==''&&pendataan_normalize($byId[$id]['district_name'])===$district;}));
    if(count($candidates)>1&&count($sameDistrict)===1){$candidates=$sameDistrict;$method.=' + kecamatan';}
    $library=count($candidates)===1?$byId[$candidates[0]]:null;$warning='';
    if($library&&$district!==''&&pendataan_normalize($library['district_name'])!==$district)$warning='Kecamatan berbeda; verifikasi identitas';
    if($library&&$rnpsn!==''&&preg_match('/^\d{8}$/D',$rnpsn)&&$library['subtype_code']!=='kabupaten'&&$library['code']!==$rnpsn)$warning=trim($warning.'; NPSN berbeda','; ');
    if($library&&$method==='NPSN'){
        preg_match_all('/\d+/',pendataan_normalize($library['name']),$targetNumbers);$sourceNumbers=[];
        foreach([$r['Lembaga Induk'],$r['Nama']]as$value)if(preg_match('/\b((?:SD|SMP) .+)$/u',pendataan_normalize($value),$fragment)){preg_match_all('/\d+/',pendataan_normalize($fragment[1]),$numbers);$sourceNumbers[]=$numbers[0];}
        if($sourceNumbers&&!in_array($targetNumbers[0],$sourceNumbers,true))$warning=trim($warning.'; Nomor sekolah berbeda walaupun NPSN sama; periksa perubahan nama/merger','; ');
    }
    $status=$library&&$warning===''?'COCOK':($candidates?'PERLU_VERIFIKASI':'BELUM_DITEMUKAN');
    if(!$candidates){
        $scores=[];foreach($libraries as$l){if(pendataan_normalize($l['district_name'])!==$district)continue;$best=0;foreach([$r['Lembaga Induk'],$r['Nama']]as$name){similar_text(pendataan_normalize($name),pendataan_normalize($l['name']),$score);$best=max($best,$score);}if($best>=75)$scores[$l['id']]=round($best,1);}
        arsort($scores);$candidates=array_keys(array_slice($scores,0,3,true));if($candidates){$status='PERLU_VERIFIKASI';$method='kemiripan nama >=75%, kecamatan sama; BUKAN pasangan otomatis';}
        if(!$candidates&&$r['Jenis']==='UMUM'&&$r['Subjenis']==='KABUPATEN/KOTA'){$candidates=array_column(array_filter($libraries,function($l){return $l['subtype_code']==='kabupaten';}),'id');if($candidates){$status='PERLU_VERIFIKASI';$method='kandidat perpustakaan kabupaten: nama lembaga berbeda, konfirmasi NPP';}}
    }
    foreach($candidates as$id)$candidateIds[$id]=true;
    if($status==='COCOK')$matched[$library['id']][]=$i+2;
    $distance=$library?pendataan_distance($library['latitude'],$library['longitude'],$r['Latitude'],$r['Longitude']):null;
    $gps=$distance===null?'Koordinat tidak lengkap/tidak valid':($distance>1000?'Selisih >1 km; verifikasi':($distance>100?'Selisih 100 m–1 km':'Selisih <=100 m'));
    $matches[]=['row'=>$i+2,'source_id'=>$r['Id'],'status'=>$status,'method'=>$method,'library_id'=>$status==='COCOK'?(int)$library['id']:null,'candidate_ids'=>$candidates,'canonical_name'=>$status==='COCOK'?$library['name']:$r['Nama'],'master_name'=>$library['name']??'','warning'=>$warning,'gps_distance_m'=>$distance,'gps_review'=>$gps,'master_lat'=>$library['latitude']??'','master_lng'=>$library['longitude']??'','source'=>$r];
    $key=$r['Jenis'].' / '.$r['Subjenis'];$subtypes[$key]=($subtypes[$key]??0)+1;
}
foreach($matches as&$m){$id=$m['library_id'];if($id&&count($matched[$id])>1){$m['status']='COCOK_GANDA';$m['warning']=trim($m['warning'].'; Beberapa baris sumber menuju master sama: '.implode(', ',$matched[$id]),'; ');}$stats[$m['status']]=($stats[$m['status']]??0)+1;}unset($m);
$writer=new Catalog_xlsx();
function pendataan_workbook($file,$sheet,$headers,$rows){global$writer,$folder;$path=$writer->build($sheet,$headers,$rows);try{network_check(copy($path,$folder.$file),'Cannot save derived workbook.');chmod($folder.$file,0600);}finally{unlink($path);}}
$columns=['Baris XLS','ID pendataan','Status pencocokan','Dasar pencocokan','ID library terkonfirmasi','Kandidat ID library','Nama master','Nama pendataan diselaraskan','Catatan verifikasi','Selisih GPS meter','Pemeriksaan GPS','Latitude master','Longitude master'];$output=[];
foreach($matches as$m)$output[]=array_merge([$m['row'],$m['source_id'],$m['status'],$m['method'],$m['library_id']??'',implode(', ',$m['candidate_ids']),$m['master_name'],$m['canonical_name'],$m['warning'],$m['gps_distance_m']??'',$m['gps_review'],$m['master_lat'],$m['master_lng']],array_values($m['source']));
pendataan_workbook('PENCOCOKAN_PENDATAAN.xlsx','Pencocokan',array_merge($columns,$headers),$output);
$normalized=[];foreach($matches as$m){$r=$m['source'];$prefix=[$m['row'],$m['status'],$m['library_id']??'',$r['Nama'],$r['Lembaga Induk']];if($m['library_id']){$l=$byId[$m['library_id']];$r['Nama']=$l['name'];$r['Lembaga Induk']=$l['institution_name']?:$l['name'];}$normalized[]=array_merge($prefix,array_values($r));}
pendataan_workbook('PENDATAAN_NAMA_DISELARASKAN.xlsx','Pendataan',array_merge(['Baris sumber','Status verifikasi','ID library','Nama asli pendataan','Lembaga induk asli'],$headers),$normalized);
$missing=[];foreach($libraries as$l)if(!isset($matched[$l['id']]))$missing[]=[$l['id'],$l['code'],$l['name'],$l['subtype_code'],$l['district_name'],$l['village_name'],isset($candidateIds[$l['id']])?'Ada kandidat; belum terkonfirmasi':'Belum ditemukan di pendataan'];
pendataan_workbook('LIBRARIES_BELUM_COCOK.xlsx','Master belum cocok',['ID library','Kode NPSN','Nama master','Subjenis','Kecamatan','Desa','Status'],$missing);
$mapping=[
 'Provinsi'=>['Identitas IPLM','province','Konstanta wilayah; bukan kolom induk baru.'],
 'KabKota'=>['Identitas IPLM','regency','Normalisasi Rembang ke Kab. Rembang; jangan mengubah wilayah berdasarkan nama saja.'],
 'Kecamatan'=>['Induk tersedia','libraries.district_id / district','Cocokkan ke ref_districts; konflik wajib diperiksa.'],
 'Kelurahan'=>['Induk tersedia','libraries.village_id / village','Cocokkan desa dalam kecamatan, bukan nama global.'],
 'Id'=>['Pendataan tambahan','external_source_id','ID eksternal pendataan; bukan ID libraries dan tidak menimpa source_id impor sekolah.'],
 'Npp'=>['Induk + IPLM','libraries.npp / npp','Usulan pengayaan induk sesudah verifikasi; jangan menimpa nilai lama otomatis.'],
 'NPSN'=>['Induk + IPLM','libraries.code / npsn','Kode dipakai sebagai NPSN untuk sekolah; tetap string agar nol awal terjaga.'],
 'Lembaga Induk'=>['Induk + IPLM','libraries.institution_name / institution_name','Nama diselaraskan hanya untuk pasangan terkonfirmasi; nama asli dipertahankan.'],
 'Nama'=>['Induk + IPLM','libraries.name / library_name','Nama diselaraskan hanya pada salinan pendataan; induk tidak diubah.'],
 'Jenis'=>['Induk + IPLM','library_types / library_type_id','Pemetaan enum; jenis di luar kewenangan tidak dipaksakan menjadi peserta IPLM.'],
 'Subjenis'=>['Induk + IPLM','library_subtypes / library_subtype_id','SD/SMP/Umum sesuai template; TK/SKB/SMA/SMK/MI/MTs/dll tidak otomatis masuk.'],
 'Alamat'=>['Induk + IPLM','libraries.address / address','Simpan usulan perbedaan untuk verifikasi, jangan overwrite.'],
 'Telepon'=>['Induk tersedia','libraries.phone','Kontak perpustakaan, bukan otomatis nomor pengisi IPLM.'],
 'Email'=>['Induk tersedia','libraries.email','Pengayaan sesudah verifikasi.'],
 'Website'=>['Induk tersedia','libraries.website','Validasi URL sebelum digunakan.'],
 'Latitude'=>['Induk tersedia','libraries.latitude','Master aplikasi diprioritaskan; selisih hanya ditandai, tidak diubah.'],
 'Longitude'=>['Induk tersedia','libraries.longitude','Master aplikasi diprioritaskan; selisih hanya ditandai, tidak diubah.'],
 'Nama Kepala Perpus'=>['Perlu verifikasi peran','libraries.manager_name / pendataan.head_librarian','PIC induk sekolah saat ini kepala sekolah; kepala perpustakaan bisa orang berbeda.'],
 'Nama Kepala Sekolah'=>['Perlu verifikasi peran','libraries.manager_name / pendataan.head_school','PIC sekolah dapat cocok; jangan menimpa kepala perpustakaan tanpa pemetaan peran.'],
 'Jam Layanan'=>['Induk tersedia','libraries.opening_hours','Jadwal berbentuk teks; satukan dengan hari/zona waktu setelah verifikasi.'],
 'Jumlah Siswa'=>['Metrik IPLM bersyarat','students','Perlu konfirmasi tahun 2025 dan tanggal keadaan.'],
 'Jumlah Judul Koleksi'=>['Metrik IPLM bersyarat','print_titles / digital_titles','Tidak memisahkan cetak/digital; tidak boleh dianggap seluruhnya cetak tanpa konfirmasi.'],
 'Jumlah Eksemplar Koleksi'=>['Metrik IPLM bersyarat','print_copies / digital_copies','Tidak memisahkan cetak/digital; perlu rincian dan tanggal keadaan.'],
 'Jumlah Pustakawan'=>['Metrik IPLM bersyarat','staff_qualified','Jumlah jabatan bukan bukti minimal pendidikan D2; perlu daftar tenaga dan ijazah.'],
 'Jumlah Tenaga Teknis'=>['Metrik IPLM bersyarat','staff_other','Kategori jabatan belum tentu sama dengan tidak berkualifikasi; verifikasi klasifikasi.'],
 'Jumlah Kunjungan'=>['Metrik IPLM bersyarat','library_users','Sesuai konsep kunjungan, tetapi tidak ada rentang data; jangan otomatis menganggap tahun 2025.'],
 'Anggaran'=>['Metrik IPLM bersyarat','budget_*','Anggaran agregat tidak bisa dipecah BOS/non-BOS/koleksi/diklat/pengelolaan tanpa rincian.'],
 'Created At'=>['Pendataan tambahan','external_created_at','Waktu input sumber, bukan awal periode data statistik.'],
 'Updated At'=>['Pendataan tambahan','external_updated_at','Waktu ubah sumber, bukan tahun pengukuran statistik.'],
 'Jumlah Anggota'=>['Pendataan tambahan','reported_member_count','Simpan agregat pendataan; bukan membuat anggota fiktif di members/network_members.']
];
$fieldRows=[];foreach($headers as$header){$spec=$mapping[$header]??['Pendataan tambahan','pendataan.attributes','Belum memiliki field khusus yang sepadan; simpan atribut pendataan non-IPLM dengan sumber dan periode, bukan dipaksakan ke indikator.'];$filled=count(array_filter($rows,function($r)use($header){return $r[$header]!=='';}));$fieldRows[]=[$header,$spec[0],$spec[1],$filled,$spec[2]];}
pendataan_workbook('PEMETAAN_KOLOM_PENDATAAN.xlsx','Pemetaan 60 kolom',['Kolom sumber','Klasifikasi','Tujuan yang disarankan','Baris terisi','Aturan sinkronisasi'],$fieldRows);
$summary=['generated_at'=>date(DATE_ATOM),'source_sha256'=>hash_file('sha256',$source),'source_rows'=>count($rows),'source_columns'=>count($headers),'master_rows'=>count($libraries),'statuses'=>$stats,'matched_unique_libraries'=>count($matched),'master_without_confirmed_match'=>count($missing),'source_subtypes'=>$subtypes,'gps_over_1km'=>count(array_filter($matches,function($m){return $m['library_id']&&$m['gps_distance_m']!==null&&$m['gps_distance_m']>1000;})),'duplicate_target_libraries'=>count(array_filter($matched,function($r){return count($r)>1;}))];
file_put_contents($folder.'ringkasan-pendataan-task2.json',json_encode($summary,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
$safe=[];foreach($matches as$m)$safe[]=['row'=>$m['row'],'source_id'=>$m['source_id'],'status'=>$m['status'],'library_id'=>$m['library_id'],'candidate_ids'=>$m['candidate_ids'],'source_name'=>$m['source']['Nama'],'institution'=>$m['source']['Lembaga Induk'],'canonical_name'=>$m['canonical_name'],'warning'=>$m['warning'],'gps_distance_m'=>$m['gps_distance_m']];
file_put_contents($folder.'pencocokan-pendataan-task2.json',json_encode($safe,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));chmod($folder.'pencocokan-pendataan-task2.json',0600);
$evidence=iplm_evidence_map();file_put_contents($folder.'pemetaan-bukti-dukung-task2.json',json_encode($evidence,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
$evidenceRows=[];foreach($evidence as$key=>$e)$evidenceRows[]=[$key,$e['row'],$e['label'],$e['data'],$e['upload'],$e['format'],$e['note']];
pendataan_workbook('PEMETAAN_BUKTI_DUKUNG.xlsx','28 indikator',['Kode indikator','Baris sumber','Indikator','Data yang disiapkan','Bukti','Format','Catatan pemetaan'],$evidenceRows);
echo json_encode($summary,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT),PHP_EOL;
