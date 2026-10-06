<?php
/** Deterministic source classification; never equates numbered schools by fuzzy similarity. */
function task4_rows(){
    $d=new DOMDocument();$old=libxml_use_internal_errors(true);network_check($d->loadHTMLFile(FCPATH.'docs/iplm/pendataan.xls',LIBXML_NONET),'Invalid source');libxml_clear_errors();libxml_use_internal_errors($old);$x=new DOMXPath($d);$h=null;$rows=[];
    foreach($x->query('//tr')as$t){$c=[];foreach($x->query('./th|./td',$t)as$v)$c[]=trim(preg_replace('/\s+/u',' ',$v->textContent));if(!$c)continue;if(!$h){$h=$c;continue;}network_check(count($h)===count($c),'Source shape changed');$r=array_combine($h,$c);$r['_row']=count($rows)+2;$rows[]=$r;}return $rows;
}
function task4_normal($v){
    $v=preg_replace('/\bNEGRI\b/iu','NEGERI',(string)$v);$v=preg_replace('/\b(SMA|SMK|SLB|SDLB)\s*N\b/iu','$1 NEGERI',$v);
    $v=mb_strtoupper(trim((string)$v));$v=preg_replace('/[^\pL\pN]+/u',' ',$v);$v=preg_replace(['/\bSEKOLAH DASAR\b/','/\bSEKOLAH MENENGAH PERTAMA\b/','/\bSD\s*N(?:EGERI)?\b/','/\bSMP\s*N(?:EGERI)?\b/'],['SD','SMP','SD NEGERI','SMP NEGERI'],$v);$v=preg_replace('/([A-Z])(\d)/','$1 $2',$v);$v=preg_replace('/\b0+(\d+)\b/','$1',$v);$v=trim(preg_replace('/\s+/',' ',$v));
    if(preg_match('/\b(SD|SMP)\b/',$v))$v=preg_replace_callback('/\b(VIII|VII|III|IX|VI|IV|II|V|I)\b/',function($m){return ['I'=>1,'II'=>2,'III'=>3,'IV'=>4,'V'=>5,'VI'=>6,'VII'=>7,'VIII'=>8,'IX'=>9][$m[1]];},$v);
    return preg_replace('/\b((?:SD|SMP)(?: NEGERI)?) ([A-Z ]+?) (\d+)$/','$1 $3 $2',$v);
}
function task4_taxonomy(){return [
    'UMUM/KABUPATEN/KOTA'=>['umum','kabupaten','Perpustakaan Kabupaten/Kota',1],
    'UMUM/KELURAHAN/DESA'=>['umum','desa_kelurahan','Perpustakaan Desa/Kelurahan',1],
    'UMUM/KOMUNITAS/TBM/LAINNYA'=>['umum','tbm','Perpustakaan TBM/Rumah Baca/penamaan lainnya',1],
    'SEKOLAH/SD'=>['sekolah','sd','Perpustakaan SD (Negeri maupun Swasta)',1],
    'SEKOLAH/SMP'=>['sekolah','smp','Perpustakaan SMP (Negeri maupun Swasta)',1],
    'SEKOLAH/SMA'=>['sekolah','sma','Perpustakaan SMA',0],
    'SEKOLAH/SMK'=>['sekolah','smk','Perpustakaan SMK',0],
    'SEKOLAH/MTS'=>['sekolah','mts','Perpustakaan MTs',0],
    'SEKOLAH/MA'=>['sekolah','ma','Perpustakaan MA',0],
    'SEKOLAH/SDLB'=>['sekolah','sdlb','Perpustakaan SDLB',0],
    'PERGURUAN TINGGI/SEKOLAH TINGGI'=>['perguruan_tinggi','sekolah_tinggi','Perpustakaan Sekolah Tinggi',0],
    'KHUSUS/RUMAH IBADAH'=>['khusus','rumah_ibadah','Perpustakaan Rumah Ibadah',0],
    'KHUSUS/PONDOK PESANTREN'=>['swasta','pondok_pesantren','Perpustakaan Pondok Pesantren',0],
    'KHUSUS/LAPAS'=>['khusus','lapas','Perpustakaan Lapas',0],
    'KHUSUS/KEMENTERIAN/LEMBAGA'=>['khusus','kementerian_lembaga','Perpustakaan Kementerian/Lembaga',0],
];}
function task4_classify($r){
    $text=task4_normal($r['Nama'].' '.$r['Lembaga Induk']);$tax=task4_taxonomy();$key=$r['Jenis'].'/'.$r['Subjenis'];$spec=$tax[$key]??null;$note='';
    if($r['Jenis']==='KHUSUS'&&preg_match('/\b(PONDOK PESANTREN|PONPES)\b/',$text)){$spec=$tax['KHUSUS/PONDOK PESANTREN'];$note='Pondok pesantren → Swasta / Pondok Pesantren sesuai task4; kategori sumber dipertahankan pada arsip.';}
    if(preg_match('/\b(TAMAN BACAAN MASYARAKAT|TBM)\b/',$text)&&$r['Jenis']!=='SEKOLAH')$spec=$tax['UMUM/KOMUNITAS/TBM/LAINNYA'];
    $school=!!preg_match('/\b(SD|SMP|SMA|SMK|SLB|SDLB|MI|MTS|MA|MADRASAH|SEKOLAH DASAR|SEKOLAH MENENGAH)\b/',$text);
    $negeri=$r['Jenis']==='SEKOLAH'&&(($r['Status Lembaga']??'')==='NEGERI'||preg_match('/\b(NEGERI|NEGRI|SDN|SMPN|SMAN|SMKN|MIN|MTSN|MAN)\b/',$text));
    if(in_array($r['Subjenis'],['SD','SMP'],true)&&$r['Jenis']==='SEKOLAH'&&!$school){$spec=null;$note='Sumber menyebut sekolah tetapi nama dan lembaga induk tidak mengandung identitas sekolah. Perlu klasifikasi, jangan otomatis dianggap sekolah/TBM.';}
    return ['spec'=>$spec,'ownership'=>$r['Jenis']==='SEKOLAH'?($negeri?'negeri':(($school||($r['Status Lembaga']??'')==='SWASTA')?'swasta':'belum_diketahui')):null,'note'=>$note];
}
function task4_school_names($r){
    $out=[];$district=task4_normal($r['Kecamatan']??'');
    foreach([$r['Nama'],$r['Lembaga Induk']]as$v){
        $v=task4_normal($v);$variants=[$v];
        if($district!==''&&str_ends_with($v,' '.$district))$variants[]=task4_normal(substr($v,0,-strlen(' '.$district)));
        foreach($variants as$value)if(preg_match('/\\b((?:SD|SMP|TK|SKB) .+)$/u',$value,$m)){
            $out[]=str_replace(' ','',$m[1]);$out[]=str_replace(' ','',str_replace(' NEGERI ',' ',$m[1]));
        }
    }return array_unique($out);
}
function task4_plan($db){
    $rows=task4_rows();$libraries=network_query($db,'SELECT l.*,s.code subtype_code,COALESCE(d.name,l.district) district_name FROM libraries l LEFT JOIN library_subtypes s ON s.id=l.library_subtype_id LEFT JOIN ref_districts d ON d.id=l.district_id ORDER BY l.id')->result_array();$result=[];$byId=array_column($libraries,null,'id');$schoolNames=[];$codes=[];$sources=[];$seen=[];
    foreach($libraries as$l){$codes[$l['code']]=$l['id'];if($l['source_system']==='pendataan_task4')$sources[$l['source_id']]=$l['id'];foreach(task4_school_names(['Nama'=>$l['name'],'Lembaga Induk'=>$l['institution_name']??''])as$n)$schoolNames[$n][$l['id']]=$l;}
    foreach($rows as$r){$c=task4_classify($r);$id=null;$status='IMPORT';$note=$c['note'];$candidates=[];$spec=$c['spec'];
        if(isset($sources[$r['Id']])){$id=$sources[$r['Id']];$status='SUDAH_IMPORT';}
        elseif($r['Id']==='50964'){$id=2;$status='PADANAN';$note='Satu-satunya Perpusda; nomor urut 1 master adalah ID 2. NPP sumber 3317103E1000003.';}
        elseif(!$spec){$status='TAHAN_KLASIFIKASI';}
        elseif($spec[1]==='kabupaten'){$status='TAHAN_PERPUSDA';$note='Tidak boleh membuat Perpusda kedua.';}
        elseif($r['Jenis']==='SEKOLAH'){
            foreach(task4_school_names($r)as$n)foreach($schoolNames[$n]??[]as$key=>$l)if($l['subtype_code']===$spec[1]&&task4_normal($l['district_name'])===task4_normal($r['Kecamatan']))$candidates[$key]=$key;
            if(!$candidates&&$r['NPSN']!==''&&isset($codes[$r['NPSN']])){
                $candidate=$byId[$codes[$r['NPSN']]];
                if($candidate['subtype_code']===$spec[1]&&($r['Kecamatan']===''||task4_normal($candidate['district_name'])===task4_normal($r['Kecamatan'])))foreach(task4_school_names($r)as$a)foreach(task4_school_names(['Nama'=>$candidate['name'],'Lembaga Induk'=>$candidate['institution_name']??''])as$b){preg_match_all('/[0-9]+/',$a,$an);preg_match_all('/[0-9]+/',$b,$bn);if($an[0]===$bn[0]&&levenshtein($a,$b)<=1)$candidates[$candidate['id']]=$candidate['id'];}
            }
            if(count($candidates)===1){$id=(int)reset($candidates);$status='PADANAN';$note='Nama sekolah sama dalam kecamatan dan subjenis; NPSN database/Dapodik dipertahankan.';}
            elseif(count($candidates)>1){$status='TAHAN_AMBIGU';$note='Beberapa master memiliki identitas sekolah serupa.';}
            elseif(isset($codes[$r['NPSN']])&&$r['NPSN']!==''){$status='TAHAN_NPSN';$note='NPSN menunjuk master tetapi nama/nomor sekolah berbeda; kemungkinan salah pendataan, tidak dibuat duplikat.';$candidates[]=$codes[$r['NPSN']];}
            elseif($c['ownership']==='negeri'&&in_array($spec[1],['sd','smp'],true)){$status='TAHAN_NEGERI';$note='SD/SMP negeri tidak ditemukan persis di Dapodik; kemungkinan beda nomor/nama atau belum terdata. Perlu verifikasi, tidak diimport.';}
        }
        if($status==='TAHAN_NEGERI'){
            $roots=[];foreach(task4_school_names($r)as$n)$roots[]=preg_replace('/NEGERI|[0-9]/','',$n);
            foreach($libraries as$l)if($l['subtype_code']===$spec[1]&&task4_normal($l['district_name'])===task4_normal($r['Kecamatan']))foreach(task4_school_names(['Nama'=>$l['name'],'Lembaga Induk'=>$l['institution_name']??''])as$n)if(in_array(preg_replace('/NEGERI|[0-9]/','',$n),$roots,true))$candidates[$l['id']]=$l['id'];
            if($candidates)$note.=' Kandidat nama dasar sama (nomor mungkin berbeda): '.implode(', ',$candidates).'; BUKAN pasangan otomatis.';
        }
        if($status==='IMPORT'){
            $key=implode('|',[$spec[1],task4_normal($r['Nama']),task4_normal($r['Lembaga Induk']),task4_normal($r['Kecamatan']),task4_normal($r['Kelurahan'])]);
            if(isset($seen[$key])){$status='DUPLIKAT_SUMBER';$note='Identitas sumber sama dengan ID '.$seen[$key].'; satu master, kedua arsip dipertahankan.';}else$seen[$key]=$r['Id'];
            $duplicate_source=$status==='DUPLIKAT_SUMBER'?$seen[$key]:null;
        }else$duplicate_source=null;
        if($id&&$r['NPSN']!==''&&$r['NPSN']!==$byId[$id]['code'])$note.=' NPSN sumber berbeda; acuan tetap database.';
        $result[]=['source'=>$r,'classification'=>$c,'library_id'=>$id,'status'=>$status,'note'=>$note,'candidates'=>array_values($candidates),'duplicate_source'=>$duplicate_source];
    }return $result;
}

function task4_style_workbook($path){
    $template=new ZipArchive();network_check($template->open(FCPATH.'docs/iplm/PERSANDINGAN_DATABASE_PENDATAAN_TASK3.xlsx')===true,'Task3 style template missing');$styles=$template->getFromName('xl/styles.xml');$template->close();$z=new ZipArchive();network_check($z->open($path)===true,'Workbook unavailable');$d=new DOMDocument();network_check($d->loadXML($z->getFromName('xl/worksheets/sheet1.xml'),LIBXML_NONET),'Invalid worksheet');$x=new DOMXPath($d);$x->registerNamespace('s','http://schemas.openxmlformats.org/spreadsheetml/2006/main');
    foreach($x->query('//s:sheetView')as$v)$v->setAttribute('zoomScale','85');$visible=[1=>10,2=>16,3=>35,6=>17,10=>10,11=>16,12=>35,15=>17,19=>23,22=>65];
    foreach($x->query('//s:col')as$c){$i=(int)$c->getAttribute('min');if(!isset($visible[$i])){$c->setAttribute('hidden','1');$c->setAttribute('outlineLevel','1');}else$c->setAttribute('width',(string)$visible[$i]);}
    foreach($x->query('//s:sheetData/s:row')as$row){$num=(int)$row->getAttribute('r');$cells=[];foreach($row->childNodes as$c)if($c instanceof DOMElement)$cells[]=$c;if($num===1){foreach($cells as$i=>$c)$c->setAttribute('s',(string)($i<9?1:($i<18?2:3)));continue;}$status=$cells[18]->textContent??'';$style=strpos($status,'TAHAN')===0?5:($status==='HANYA_DATABASE'?7:($status==='IMPORT'?8:4));foreach($cells as$c)$c->setAttribute('s',(string)$style);if(($cells[10]->textContent??'')!==''&&($cells[1]->textContent??'')!==''&&$cells[10]->textContent!==$cells[1]->textContent)$cells[10]->setAttribute('s','9');$row->setAttribute('ht','66');$row->setAttribute('customHeight','1');}
    network_check($z->addFromString('xl/styles.xml',$styles)&&$z->addFromString('xl/worksheets/sheet1.xml',$d->saveXML()),'Cannot style workbook');network_check($z->close(),'Cannot finalize workbook');
}
