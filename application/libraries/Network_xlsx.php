<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Bounded first-sheet reader for the provided import templates (no formulas/macros). */
class Network_xlsx
{
    public function read($path, array $headers)
    {
        if (!is_file($path) || filesize($path)>3*1024*1024) throw new RuntimeException('Berkas Excel maksimal 3 MB.');
        $zip=new ZipArchive();if($zip->open($path)!==true)throw new RuntimeException('Berkas XLSX tidak valid.');
        try {
            $size=0;
            for($i=0;$i<$zip->numFiles;$i++){$entry=$zip->statIndex($i);$size+=$entry['size'];if($size>20*1024*1024||$zip->numFiles>200)throw new RuntimeException('Arsip Excel terlalu besar.');}
            if($zip->locateName('xl/vbaProject.bin')!==false)throw new RuntimeException('Macro tidak diperbolehkan.');
            $parse=function($part)use($zip){
                $raw=$zip->getFromName($part);
                if($raw===false||stripos($raw,'<!DOCTYPE')!==false||stripos($raw,'<!ENTITY')!==false)throw new RuntimeException('Struktur Excel tidak didukung. Gunakan template.');
                $previous=libxml_use_internal_errors(true);
                try{$xml=simplexml_load_string($raw,'SimpleXMLElement',LIBXML_NONET);if(!$xml)throw new RuntimeException('XML Excel tidak valid.');$xml->registerXPathNamespace('s','http://schemas.openxmlformats.org/spreadsheetml/2006/main');return $xml;}
                finally{libxml_clear_errors();libxml_use_internal_errors($previous);}
            };
            $strings=[];
            if($zip->locateName('xl/sharedStrings.xml')!==false){foreach($parse('xl/sharedStrings.xml')->xpath('//s:si')as$item){$item->registerXPathNamespace('s','http://schemas.openxmlformats.org/spreadsheetml/2006/main');$strings[]=implode('',array_map('strval',$item->xpath('.//s:t')));}}
            $sheet=$parse('xl/worksheets/sheet1.xml');$rows=[];$header=null;
            foreach($sheet->xpath('//s:sheetData/s:row')as$row){
                $cells=[];
                foreach($row->children('http://schemas.openxmlformats.org/spreadsheetml/2006/main')->c as$cell){
                    if(isset($cell->f))throw new RuntimeException('Formula tidak diterima; tempel sebagai nilai dahulu.');
                    if(!preg_match('/^([A-Z]+)[0-9]+$/',(string)$cell->attributes()['r'],$match))throw new RuntimeException('Referensi sel tidak valid.');
                    $col=0;foreach(str_split($match[1])as$letter)$col=$col*26+ord($letter)-64;$col--;
                    if($col>=count($headers))throw new RuntimeException('Kolom melebihi template.');
                    $value=(string)$cell->v;
                    if((string)$cell->attributes()['t']==='s')$value=$strings[(int)$value]??'';
                    elseif((string)$cell->attributes()['t']==='inlineStr'){$cell->registerXPathNamespace('s','http://schemas.openxmlformats.org/spreadsheetml/2006/main');$value=implode('',array_map('strval',$cell->xpath('.//s:t')));}
                    if(mb_strlen($value)>5000)throw new RuntimeException('Isi sel terlalu panjang.');$cells[$col]=trim($value);
                }
                $values=[];for($i=0;$i<count($headers);$i++)$values[]=$cells[$i]??'';
                if(!array_filter($values,function($v){return $v!=='';}))continue;
                if($header===null){if($values!==$headers)throw new RuntimeException('Header berbeda dari template. Unduh template terbaru.');$header=$values;continue;}
                $rows[]=array_combine($headers,$values);if(count($rows)>500)throw new RuntimeException('Maksimal 500 baris per impor.');
            }
            if(!$rows)throw new RuntimeException('Tidak ada data untuk diimpor.');return $rows;
        } finally { $zip->close(); }
    }
}
