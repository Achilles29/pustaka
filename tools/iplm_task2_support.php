<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
function iplm_evidence_map(){
    $file=dirname(__DIR__).'/docs/iplm/bukti_dukung.xlsx';$zip=new ZipArchive();
    network_check($zip->open($file)===true,'Cannot open evidence workbook.');
    try{
        $strings=[];$xml=new DOMDocument();$xml->loadXML($zip->getFromName('xl/sharedStrings.xml'),LIBXML_NONET);
        foreach($xml->getElementsByTagName('si')as$si)$strings[]=$si->textContent;
        $xml->loadXML($zip->getFromName('xl/worksheets/sheet1.xml'),LIBXML_NONET);$cells=[];
        foreach($xml->getElementsByTagName('c')as$c){$v=$c->getElementsByTagName('v')->item(0);if($v)$cells[$c->getAttribute('r')]=$c->getAttribute('t')==='s'?$strings[(int)$v->textContent]:$v->textContent;}
    }finally{$zip->close();}
    $keys=[5=>'print_titles',6=>'print_copies',7=>'digital_titles',8=>'digital_copies',9=>'added_print_titles',10=>'added_print_copies',11=>'added_digital_titles',12=>'added_digital_copies',13=>'budget_bos',14=>'budget_non_bos',15=>'budget_collection',20=>'staff_qualified',21=>'staff_other',22=>'staff_training',23=>'budget_training',28=>'used_print_titles',29=>'used_print_copies',30=>'used_digital_titles',31=>'used_digital_copies',32=>'literacy_participants',33=>'library_users',34=>'ict_users',54=>'literacy_events',55=>'partnerships',56=>'service_types',57=>'service_policies',58=>'regional_policies',59=>'budget_management'];
    $out=[];foreach($keys as$row=>$key){
        network_check(!empty($cells['B'.$row])&&!empty($cells['D'.$row]),'Missing evidence row '.$row);
        $upload=$cells['D'.$row];$note='';
        if($row===57){$upload='Excel daftar dokumen + scan/foto dokumen kebijakan/SOP pelayanan.';$note='D57 memuat tambahan teks anggaran yang tidak sesuai indikator; petunjuk SOP mengikuti B57/C57/F57. Teks sumber tetap disimpan.';}
        $out[$key]=['row'=>$row,'label'=>$cells['B'.$row],'data'=>$cells['C'.$row]??'','upload'=>$upload,'format'=>$cells['E'.$row]??'','source_upload'=>$cells['D'.$row],'source_checklist'=>$cells['F'.$row]??'','note'=>$note,'hint'=>"Siapkan: ".($cells['C'.$row]??'')."\nBukti: ".$upload."\nFormat: ".($cells['E'.$row]??'')."\nSumber: bukti_dukung.xlsx, baris ".$row.'.'];
    }return $out;
}
