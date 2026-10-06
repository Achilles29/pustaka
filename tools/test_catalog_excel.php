<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__.'/test_public_catalog_updates.php';
require APPPATH.'libraries/Catalog_xlsx.php';
$controller->input = new class { public function get($key,$clean=false) { return $key==='format'?'xlsx':null; } };
$denied = false;
try { $controller->annual_report(); } catch (RuntimeException $e) { $denied = $e->getMessage()==='DENIED export'; }
check($denied,'View-only users cannot export annual Excel report');
$writer = new Catalog_xlsx();
$report = $catalog->annual_development([],null);
foreach (array_merge($report['rows'],[$report['unknown']]) as $row) {
    if ((int)$row['titles'] !== (int)$row['physical_titles']+(int)$row['digital_titles']-(int)$row['hybrid_titles']+(int)$row['unclassified_titles']) throw new RuntimeException('Format counts do not reconcile');
}
check(true,'Annual format counts reconcile with unique titles');
check(end($report['rows'])['physical_cumulative']===$report['total_physical_titles']-(int)$report['unknown']['physical_titles'],'Physical cumulative total reconciles');
check(end($report['rows'])['digital_cumulative']===$report['total_digital_titles']-(int)$report['unknown']['digital_titles'],'Digital cumulative total reconciles');
echo 'Live totals: '.json_encode(array_filter($report,function($key){return strpos($key,'total_')===0;},ARRAY_FILTER_USE_KEY))."\n";

$file = $writer->build('Uji Excel',['ISBN','Judul','Jumlah','Catatan'],[
    ['001234567890123456','=HYPERLINK("https://invalid.example")',7,"A & B < C\nBaris kedua\x01"],
    ['9780000000000','Buku Indonesia 📚',-1.5,str_repeat('x',33000)],
]);
try {
    $zip = new ZipArchive(); check($zip->open($file)===true,'XLSX is a real ZIP/OOXML package');
    $sheet = simplexml_load_string($zip->getFromName('xl/worksheets/sheet1.xml'));
    $sheet->registerXPathNamespace('m','http://schemas.openxmlformats.org/spreadsheetml/2006/main');
    check((string)$sheet->xpath('//m:c[@r="A2"]/m:is/m:t')[0]==='001234567890123456','XLSX preserves leading zeros and long identifiers');
    check(count($sheet->xpath('//m:f'))===0 && (string)$sheet->xpath('//m:c[@r="B2"]/@t')[0]==='inlineStr','Formula-looking input remains literal text');
    check((string)$sheet->xpath('//m:c[@r="C3"]/m:v')[0]==='-1.5','Numeric cells remain numeric');
    check((string)$sheet->xpath('//m:c[@r="D2"]/m:is/m:t')[0]==="A & B < C\nBaris kedua",'XML escapes and invalid control character removal');
    check(mb_strlen((string)$sheet->xpath('//m:c[@r="D3"]/m:is/m:t')[0])===32767,'Excel cell text limit respected');
    check((string)$sheet->xpath('//m:pane/@state')[0]==='frozen' && (string)$sheet->xpath('//m:autoFilter/@ref')[0]==='A1:D3','Header freeze and column filters available');
    for($i=0;$i<$zip->numFiles;$i++) check(simplexml_load_string($zip->getFromIndex($i))!==false,'Valid XML: '.$zip->getNameIndex($i));
    $zip->close();
} finally { unlink($file); }

// Large real-data workbook, streamed in batches, without retaining every row in memory.
$rows = (function()use($catalog){$last=0;do{$batch=$catalog->export_batch([],null,$last,500);foreach($batch as $book){$last=(int)$book['id'];yield [$last,$book['title'],$book['isbn'],$book['publish_year']];}}while($batch);})();
$file = $writer->build('Katalog',['ID','Judul','ISBN','Tahun terbit'],$rows);
try {
    $zip=new ZipArchive();$zip->open($file);
    $sheet=simplexml_load_string($zip->getFromName('xl/worksheets/sheet1.xml'));
    $sheet->registerXPathNamespace('m','http://schemas.openxmlformats.org/spreadsheetml/2006/main');
    check(count($sheet->xpath('//m:sheetData/m:row'))===$total+1,'Full XLSX includes all catalog rows');
    $zip->close();
} finally { unlink($file); }

// Connection-local shadow tables: production catalog data is never changed.
$temporary=[];
try {
    foreach(['books','book_items','digital_assets']as$table){
        $definition = $db->query('SHOW CREATE TABLE '.$table)->row_array()['Create Table'];
        $definition = preg_replace('/^\s*CONSTRAINT[^\n]*\n/m','',$definition);
        $definition = preg_replace('/,\n\)/',"\n)",$definition);
        $definition = preg_replace('/^CREATE TABLE /','CREATE TEMPORARY TABLE ',$definition);
        if(!$db->query($definition)) throw new RuntimeException('Cannot create test table: '.$db->error()['message']);
        $temporary[]=$table;
    }
    foreach(range(1,7)as$id) $db->insert('books',['id'=>$id,'title'=>'Fixture '.$id,'source_system'=>'manual','created_at'=>$id===7?'2999-01-01 00:00:00':'2020-01-01 00:00:00']);
    foreach([
        [1,'Buku','Buku',1,null], [2,'Ebook','PDF',1,null],
        [3,'Buku','Buku',1,null], [3,'Ebook','Digital',1,null],
        [6,'Buku','Buku',1,'2020-01-02 00:00:00'], [7,'Buku','Buku',2,null],
    ] as $item) $db->insert('book_items',['book_id'=>$item[0],'collection_type'=>$item[1],'media_name'=>$item[2],'library_id'=>$item[3],'deleted_at'=>$item[4]]);
    foreach([[3,'active','member'],[3,'active','internal'],[5,'active','internal'],[6,'draft','member']]as$asset) $db->insert('digital_assets',['book_id'=>$asset[0],'status'=>$asset[1],'reader_audience'=>$asset[2]]);
    $r=$catalog->annual_development([],null);
    check($r['total_titles']===7 && $r['total_physical_titles']===3 && $r['total_digital_titles']===3 && $r['total_hybrid_titles']===1 && $r['total_unclassified_titles']===2,'Physical, digital, hybrid, unclassified fixtures');
    check($r['total_physical_copies']===3 && $r['total_copies']===5,'Ebook records excluded from physical copies; assets do not duplicate copies');
    check((int)$r['unknown']['physical_titles']===1 && end($r['rows'])['physical_cumulative']===2,'Unknown dates excluded from physical cumulative');
    $r=$catalog->annual_development([],1);
    check($r['total_titles']===3 && $r['total_physical_titles']===2 && $r['total_digital_titles']===2,'Library scope respected for both formats');
    $r=$catalog->annual_development(['collection_type'=>'Ebook'],1);
    check($r['total_titles']===2 && $r['total_physical_titles']===0 && $r['total_digital_titles']===2,'Ebook filter never counted as physical');
    $r=$catalog->annual_development(['q'=>'no_matching_fixture'],null);
    check($r['rows']===[] && $r['total_physical_titles']===0 && $r['total_digital_titles']===0,'Empty report totals remain zero');
} finally { foreach(array_reverse($temporary)as$table)$db->query('DROP TEMPORARY TABLE '.$table); }
echo $checks." combined checks passed.\n";
