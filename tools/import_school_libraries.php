<?php
// Only reads the Data Sekolah worksheet and the fields requested for /libraries.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('FCPATH',dirname(__DIR__).'/'); define('APPPATH',FCPATH.'application/');
define('BASEPATH',FCPATH.'system/'); define('ENVIRONMENT','production');
require BASEPATH.'core/Common.php'; require BASEPATH.'database/DB.php';
date_default_timezone_set('Asia/Jakarta'); umask(0077);
$db=DB('default'); $db->db_debug=false;
$source=FCPATH.'docs/data/sekolah_edit.xlsx';
$verifiedSource=FCPATH.'docs/data/sekolah_village_verified.json';
$levelOrder=['SD'=>0,'SMP'=>1,'TK'=>2,'SKB'=>3];
$check=function($ok,$message){if(!$ok)throw new RuntimeException($message);};
$query=function($sql,$params=[])use($db,$check){$r=$db->query($sql,$params);$check($r!==false,'Database operation failed ('.$db->error()['code'].').');return $r;};
$normalize=function($value){return preg_replace('/[^A-Z0-9]/','',strtoupper(trim((string)$value)));};
try{
    $verified=json_decode(file_get_contents($verifiedSource),true,512,JSON_THROW_ON_ERROR);
    $check(isset($verified['schools']) && count($verified['schools'])===51,'Expected 51 verified village mappings.');
    $zip=new ZipArchive();$check($zip->open($source)===true,'Cannot open source Excel.');
    $xml=function($path)use($zip,$check){$raw=$zip->getFromName($path);$check($raw!==false,'Missing Excel part: '.$path);$node=simplexml_load_string($raw,'SimpleXMLElement',LIBXML_NONET);$check($node!==false,'Invalid Excel XML.');$node->registerXPathNamespace('x','http://schemas.openxmlformats.org/spreadsheetml/2006/main');return $node;};
    $book=$xml('xl/workbook.xml');$sheetId=null;
    foreach($book->xpath('//x:sheets/x:sheet')as$sheet)if((string)$sheet['name']==='Data Sekolah')$sheetId=(string)$sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
    $check($sheetId!==null,'Data Sekolah sheet missing.');
    $relations=$xml('xl/_rels/workbook.xml.rels');$sheetPath=null;
    foreach($relations->children('http://schemas.openxmlformats.org/package/2006/relationships')as$relation)if((string)$relation->attributes()['Id']===$sheetId)$sheetPath='xl/'.ltrim((string)$relation->attributes()['Target'],'/');
    $check($sheetPath!==null && strpos($sheetPath,'..')===false,'Invalid sheet relationship.');
    $strings=[];foreach($xml('xl/sharedStrings.xml')->xpath('//x:si')as$item){$item->registerXPathNamespace('x','http://schemas.openxmlformats.org/spreadsheetml/2006/main');$strings[]=implode('',array_map('strval',$item->xpath('.//x:t')));}
    $wanted=['B'=>'NPSN','C'=>'Nama Sekolah','D'=>'Perpus','E'=>'Jenjang','H'=>'Kecamatan','I'=>'Alamat','K'=>'Kepala Sekolah','N'=>'Latitude','O'=>'Longitude'];
    $sheet=$xml($sheetPath);$schools=[];$skipped=[];$seen=[];
    foreach($sheet->xpath('//x:sheetData/x:row')as$row){
        $rowNumber=(int)$row['r'];if($rowNumber<4)continue;
        $values=[];
        foreach($row->children('http://schemas.openxmlformats.org/spreadsheetml/2006/main')->c as$cell){
            $column=preg_replace('/\d/','',(string)$cell->attributes()['r']);if(!isset($wanted[$column]))continue;
            $value=(string)$cell->v;
            if((string)$cell->attributes()['t']==='s')$value=$strings[(int)$value]??'';
            elseif((string)$cell->attributes()['t']==='inlineStr'){$cell->registerXPathNamespace('x','http://schemas.openxmlformats.org/spreadsheetml/2006/main');$value=implode('',array_map('strval',$cell->xpath('.//x:t')));}
            $values[$column]=trim($value);
        }
        if($rowNumber===4){foreach($wanted as$column=>$header)$check(($values[$column]??'')===$header,'Unexpected Excel header in '.$column);continue;}
        if(empty($values['B']))continue;
        if(!isset($levelOrder[$values['E']??''])){$skipped[$values['E']??'unknown']=($skipped[$values['E']??'unknown']??0)+1;continue;}
        $check(preg_match('/^(?:\d{8}|P\d{7})$/',$values['B'])===1,'Invalid NPSN at row '.$rowNumber);
        $check(!isset($seen[$values['B']]),'Duplicate NPSN '.$values['B']);$seen[$values['B']]=true;
        $values['row']=$rowNumber;$schools[]=$values;
    }
    $zip->close();
    $type=$query("SELECT id FROM library_types WHERE code='sekolah' AND is_active=1")->row_array();$check($type,'School library type missing.');
    $districts=[];foreach($query("SELECT id,name FROM ref_districts WHERE code LIKE '3317%' AND is_active=1 ORDER BY id")->result_array()as$d)$districts[$normalize($d['name'])]=$d;
    $villages=[];foreach($query("SELECT v.id,v.name,v.district_id FROM ref_villages v JOIN ref_districts d ON d.id=v.district_id WHERE d.code LIKE '3317%' AND v.is_active=1 ORDER BY v.id")->result_array()as$v)$villages[(int)$v['district_id']][]=$v;
    $plans=[];$unresolved=[];$warnings=[];$counts=['SD'=>0,'SMP'=>0,'TK'=>0,'SKB'=>0,'with_library'=>0,'without_library'=>0,'matched_villages'=>0];
    foreach($schools as$s){
        $d=$districts[$normalize($s['H']??'')]??null;$check($d,'Unknown district at row '.$s['row']);
        $candidate=null;$method=null;$available=$villages[(int)$d['id']]??[];
        if(isset($verified['schools'][$s['B']])){
            $mapping=$verified['schools'][$s['B']];
            $check($normalize($mapping['district'])===$normalize($d['name']),'Verified district differs from Excel for '.$s['B']);
            $matches=array_values(array_filter($available,function($v)use($normalize,$mapping){return $normalize($v['name'])===$normalize($mapping['village']);}));
            $check(count($matches)===1,'Verified village missing or ambiguous in district for '.$s['B']);
            $candidate=$matches[0];$method=$mapping['method'];
        }
        if(!$candidate && $s['E']==='SD'){
            $villageName=preg_replace('/^SD\s+(?:(?:NEGERI|N)\s*)?\d*\s*/i','',$s['C']);
            $villageName=preg_replace('/\s+\d+$/','',$villageName);
            $matches=array_values(array_filter($available,function($v)use($normalize,$villageName){return $normalize($v['name'])===$normalize($villageName);}));
            if(count($matches)===1){$candidate=$matches[0];$method='school_name';}
        }
        if(!$candidate){
            // Only explicit Desa/Ds/Kelurahan markers or an exact whole address;
            // road names and district names are not assumed to be villages.
            $address=trim($s['I']??'');$matches=[];
            foreach($available as$v){
                $same=$normalize($address)===$normalize($v['name']);
                $villagePattern=implode('\\s*',array_map(function($part){return preg_quote($part,'/');},preg_split('/\s+/',trim($v['name']))));
                $explicit=preg_match('/(?:\bDESA\s+|\bDS\.?\s*|\bKELURAHAN\s+|\bKEL\.?\s*)'.$villagePattern.'(?=\s|[.,]|$)/iu',$address)===1;
                if($same||$explicit)$matches[]=$v;
            }
            if(count($matches)===1){$candidate=$matches[0];$method='explicit_address';}
        }
        if(!$candidate)$unresolved[]=['npsn'=>$s['B'],'name'=>$s['C'],'district'=>$d['name'],'address'=>$s['I']??''];
        else $counts['matched_villages']++;
        $hasLibrary=strtolower($s['D']??'')==='ruang perpus';
        $check($hasLibrary || strtolower($s['D']??'')==='tidak punya','Unknown Perpus value at row '.$s['row']);
        foreach(['N','O']as$column)$check(isset($s[$column]) && is_numeric($s[$column]),'Missing/non-numeric coordinate at row '.$s['row']);
        $lat=(float)$s['N'];$lng=(float)$s['O'];$check(is_finite($lat)&&is_finite($lng)&&abs($lat)<=90&&abs($lng)<=180,'Invalid coordinate range at row '.$s['row']);
        if($lat===0.0||$lng===0.0)$warnings[]=['npsn'=>$s['B'],'issue'=>'zero_coordinate'];
        $counts[$s['E']]++;$counts[$hasLibrary?'with_library':'without_library']++;
        $payload=['library_type_id'=>(int)$type['id'],'code'=>$s['B'],'name'=>$s['C'],
            'manager_name'=>!empty($s['K'])&&$s['K']!=='-'?$s['K']:null,'address'=>$s['I']?:null,
            'district_id'=>(int)$d['id'],'district'=>$d['name'],'village_id'=>$candidate?(int)$candidate['id']:null,'village'=>$candidate?$candidate['name']:null,
            'phone'=>null,'email'=>null,'website'=>null,
            'opening_hours'=>'Senin 08:00-15:00; Selasa 08:00-15:00; Rabu 08:00-15:00; Kamis 08:00-15:00; Jumat 08:00-15:00',
            'latitude'=>number_format($lat,7,'.',''),'longitude'=>number_format($lng,7,'.',''),'service_radius_meters'=>50,
            'description'=>$hasLibrary?'Perpustakaan Sekolah '.$s['C']:$s['C'],
            'facilities'=>$hasLibrary?'perpustakaan':'belum ada perpustakaan','status'=>'active','is_verified'=>1,
            'source_system'=>'school_xlsx','source_id'=>$s['B']];
        $check(mb_strlen($payload['name'])<=180 && mb_strlen($payload['manager_name']??'')<=180,'Field exceeds database length at row '.$s['row']);
        $plans[]=['level'=>$s['E'],'row'=>$s['row'],'village_match'=>$method,'payload'=>$payload];
    }
    usort($plans,function($a,$b)use($levelOrder){return [$levelOrder[$a['level']],$a['payload']['district_id'],$a['payload']['village_id']??PHP_INT_MAX,$a['payload']['name'],$a['payload']['code']]<=>[$levelOrder[$b['level']],$b['payload']['district_id'],$b['payload']['village_id']??PHP_INT_MAX,$b['payload']['name'],$b['payload']['code']];});
    $existing=[];foreach($query('SELECT id,code,name,source_system FROM libraries')->result_array()as$row)$existing[$row['code']]=$row;
    $duplicates=[];foreach($plans as$p)if(isset($existing[$p['payload']['code']]))$duplicates[]=$existing[$p['payload']['code']];
    if(in_array('--verify',$argv,true)){
        $lastId=0;$checks=0;$levels=[];
        foreach($plans as$p){
            $payload=$p['payload'];$stored=$query('SELECT * FROM libraries WHERE code=?',[$payload['code']])->row_array();
            $check($stored!==null,'Missing imported school: '.$payload['code']);
            foreach($payload as$key=>$value){$check($value===null?$stored[$key]===null:(string)$stored[$key]===(string)$value,'Imported field mismatch: '.$key.' for '.$payload['code']);$checks++;}
            $check(!empty($stored['verified_at']),'Verification timestamp missing.');
            $check((int)$stored['id']>$lastId,'Stored IDs do not follow the requested import order.');$lastId=(int)$stored['id'];
            $levels[$p['level']][]=$lastId;
        }
        $photoCount=$query("SELECT COUNT(*) n FROM library_photos p JOIN libraries l ON l.id=p.library_id WHERE l.source_system='school_xlsx'")->row_array();
        $check((int)$photoCount['n']===0,'Imported schools must not have photos.');
        $ranges=[];foreach($levels as$level=>$ids)$ranges[$level]=['count'=>count($ids),'first_id'=>min($ids),'last_id'=>max($ids)];
        echo json_encode(['verified'=>count($plans),'field_checks'=>$checks,'ranges'=>$ranges,'unresolved'=>$unresolved,'photos'=>0],JSON_PRETTY_PRINT),"\n";exit(0);
    }
    echo json_encode(['counts'=>$counts,'skipped_levels'=>$skipped,'unresolved_villages'=>$unresolved,'warnings'=>$warnings,'existing_npsn'=>$duplicates],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE),"\n";
    if(!in_array('--apply',$argv,true)){echo "DRY RUN: no database changes.\n";exit(0);}
    $check(!$unresolved || in_array('--allow-unresolved-villages',$argv,true),'Unresolved villages require user direction before import.');
    $check(!$duplicates,'Existing NPSN found; no overwrite or duplicate import performed.');
    $check(!$warnings,'Coordinate warnings require review before import.');
    $check(count($plans)===410 && $counts['SD']===363 && $counts['SMP']===39 && $counts['TK']===7 && $counts['SKB']===1,'Unexpected school counts; review changed workbook before importing.');
    $sourceHash=hash_file('sha256',$source);
    $directory='/www/backup/pustaka-libraries';
    if(!is_dir($directory))$check(mkdir($directory,0700,true),'Cannot create private backup directory.');
    $check(realpath($directory)===$directory && !is_link($directory),'Unexpected backup path.');chmod($directory,0700);
    $base=$directory.'/before-school-import-'.date('Ymd-His').'-'.bin2hex(random_bytes(4));
    $run=function(array$args,$output)use($check){$proc=proc_open($args,[0=>['file','/dev/null','r'],1=>['file',$output,'w'],2=>['pipe','w']],$pipes);$check(is_resource($proc),'Cannot start backup.');stream_get_contents($pipes[2]);fclose($pipes[2]);$check(proc_close($proc)===0,'Backup failed; no import performed.');};
    $defaults=tempnam($directory,'client-');$check($defaults!==false,'Cannot create private client config.');
    $ini=function($v){return '"'.str_replace(['\\','"',"\n","\r"],['\\\\','\\"','\\n','\\r'],(string)$v).'"';};
    try{
        $config="[client]\nhost=".$ini($db->hostname==='localhost'?'127.0.0.1':$db->hostname)."\nport=".(int)($db->port?:3306)."\nuser=".$ini($db->username)."\npassword=".$ini($db->password)."\ndefault-character-set=utf8mb4\n";
        $check(file_put_contents($defaults,$config,LOCK_EX)!==false,'Cannot write private client config.');
        $run(['mariadb-dump','--defaults-extra-file='.$defaults,'--single-transaction','--quick','--hex-blob','--skip-lock-tables',$db->database,'libraries','library_photos'],$base.'.sql');
    }finally{if(is_file($defaults))unlink($defaults);}
    $check(is_file($base.'.sql') && filesize($base.'.sql')>100,'Empty backup.');
    $run(['gzip','-c',$base.'.sql'],$base.'.sql.gz');$run(['gzip','-t',$base.'.sql.gz'],'/dev/null');
    $manifest=['source'=>'docs/data/sekolah_edit.xlsx','source_sha256'=>$sourceHash,'database'=>$db->database,'tables'=>['libraries','library_photos'],
        'created_at'=>date(DATE_ATOM),'backup'=>$base.'.sql.gz','backup_sha256'=>hash_file('sha256',$base.'.sql.gz'),
        'counts'=>$counts,'skipped_levels'=>$skipped,'unresolved_villages'=>$unresolved,'verified_villages_sha256'=>hash_file('sha256',$verifiedSource)];
    $check(file_put_contents($base.'.json',json_encode($manifest,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),LOCK_EX)!==false,'Cannot write backup manifest.');
    echo 'Backup verified: '.$base.'.sql.gz',"\n";
    $check(hash_equals($sourceHash,hash_file('sha256',$source)),'Workbook changed during preparation.');
    $check($db->trans_begin(),'Cannot start import transaction.');
    try{
        $before=$query('SELECT * FROM libraries ORDER BY id')->result_array();
        $photosBefore=$query('SELECT * FROM library_photos ORDER BY id')->result_array();
        $inserted=[];$verifiedAt=date('Y-m-d H:i:s');$lastId=0;
        foreach($plans as$p){
            $payload=$p['payload'];$payload['verified_at']=$verifiedAt;
            $check($db->insert('libraries',$payload),'Insert failed at spreadsheet row '.$p['row']);
            $id=(int)$db->insert_id();$check($id>$lastId,'Unexpected insertion ID order.');$lastId=$id;
            $stored=$query('SELECT * FROM libraries WHERE id=?',[$id])->row_array();
            foreach($payload as$key=>$value)$check($value===null?$stored[$key]===null:(string)$stored[$key]===(string)$value,'Stored field mismatch: '.$key.' at row '.$p['row']);
            $inserted[]=['id'=>$id,'npsn'=>$payload['code'],'level'=>$p['level'],'sheet_row'=>$p['row'],'district_id'=>$payload['district_id'],'village_id'=>$payload['village_id'],'village_match'=>$p['village_match']];
        }
        foreach($before as$old)$check($query('SELECT * FROM libraries WHERE id=?',[(int)$old['id']])->row_array()===$old,'An existing library changed.');
        $check($query('SELECT * FROM library_photos ORDER BY id')->result_array()===$photosBefore,'Library photos changed.');
        $check((int)$db->count_all('libraries')===count($before)+count($plans),'Final library count mismatch.');
        $journal=['backup_manifest'=>$base.'.json','source_sha256'=>$sourceHash,'inserted'=>$inserted];
        $check(file_put_contents($base.'.import.json',json_encode($journal,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),LOCK_EX)!==false,'Cannot write import journal.');
        $check($db->trans_status() && $db->trans_commit(),'Commit failed.');
    }catch(Throwable$error){$db->trans_rollback();throw $error;}
    echo json_encode(['imported'=>count($inserted),'first_id'=>$inserted[0]['id'],'last_id'=>$lastId,'journal'=>$base.'.import.json'],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),"\nAll inserted fields, ID order, existing libraries, and unchanged photos validated.\n";
}catch(Throwable$error){fwrite(STDERR,$error->getMessage()."\n");exit(1);}
