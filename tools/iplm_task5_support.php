<?php
// CLI-only reconciliation helpers. No database writes when loading this file.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__.'/iplm_task4_support.php';

function task5_school_rows()
{
    $zip = new ZipArchive();
    network_check($zip->open(FCPATH.'docs/iplm/sekolah_kabupaten_rembang.xlsx') === true, 'Cannot open school workbook');
    try {
        $shared = [];
        if (($raw = $zip->getFromName('xl/sharedStrings.xml')) !== false) {
            $xml = simplexml_load_string($raw, 'SimpleXMLElement', LIBXML_NONET);
            foreach ($xml->si as $si) $shared[] = implode('', array_map('strval', $si->xpath('.//*[local-name()="t"]')));
        }
        $xml = simplexml_load_string($zip->getFromName('xl/worksheets/sheet1.xml'), 'SimpleXMLElement', LIBXML_NONET);
        network_check($xml !== false, 'Invalid worksheet');
        $headers = ['Kecamatan','No.','NPSN','Nama Sekolah','Jenjang','Bentuk Pendidikan','Status Sekolah','Status Sinkronisasi','PD','Rombel','Guru','Tendik','R. Kelas','Perpustakaan','Toilet'];
        $result = [];
        foreach ($xml->sheetData->row as $row) {
            $values = array_fill(0, 15, '');
            foreach ($row->c as $cell) {
                network_check(!isset($cell->f), 'Formula in source workbook requires review');
                preg_match('/^([A-Z]+)[0-9]+$/', (string)$cell['r'], $m);
                $index = 0; foreach (str_split($m[1]) as $c) $index = $index * 26 + ord($c) - 64;
                network_check($index >= 1 && $index <= 15, 'Unexpected column');
                $type = (string)$cell['t'];
                $values[$index-1] = trim($type === 's' ? $shared[(int)$cell->v] : ($type === 'inlineStr' ? implode('', array_map('strval', $cell->xpath('.//*[local-name()="t"]'))) : (string)$cell->v));
            }
            if ((int)$row['r'] === 1) { network_check($values === $headers, 'School headers changed'); continue; }
            if (!array_filter($values, 'strlen')) continue;
            $item = array_combine($headers, $values); $item['_row'] = (int)$row['r'];
            $item['_district'] = mb_strtoupper(trim(preg_replace('/^Kec\.\s*/i', '', $item['Kecamatan'])));
            $result[] = $item;
        }
        network_check(count($result) > 0 && count($result) <= 10000, 'Unexpected workbook size');
        return $result;
    } finally { $zip->close(); }
}

function task5_name($name)
{
    $name = preg_replace(['/\bSEKOLAH MENENGAH KEJURUAN\b/iu', '/\bSEKOLAH MENENGAH ATAS\b/iu', '/^PERPUSTAKAAN\s+/iu', '/\bKATHOLIK\b/iu', '/\bIT\b/iu'], ['SMK', 'SMA', '', 'KATOLIK', 'ISLAM TERPADU'], $name);
    return preg_replace('/\s+/', '', task4_normal($name));
}

function task5_names($name, $district)
{
    $variants = [task5_name($name)];
    $trimmed = preg_replace('/\s+'.preg_quote(trim($district), '/').'\s*$/iu', '', $name);
    if ($trimmed !== $name) $variants[] = task5_name($trimmed);
    return array_unique($variants);
}

function task5_plan($db)
{
    $rows = task5_school_rows();
    $master = network_query($db, 'SELECT l.*,s.code subtype_code,COALESCE(d.name,l.district) district_name FROM libraries l LEFT JOIN library_subtypes s ON s.id=l.library_subtype_id LEFT JOIN ref_districts d ON d.id=l.district_id ORDER BY l.id')->result_array();
    $codes = []; $names = []; $sourceCodes = []; $districts = [];
    foreach ($db->where('regency_code','3317')->get('ref_districts')->result_array() as $d) $districts[task5_name($d['name'])] = $d;
    foreach ($rows as $r) $sourceCodes[$r['NPSN']][] = $r['_row'];
    foreach ($master as $l) {
        $codes[$l['code']][] = $l;
        foreach ([$l['institution_name'], $l['name']] as $name) if ($name) foreach(task5_names($name, $l['district_name']) as $variant) $names[$variant.'|'.task5_name($l['district_name'])][$l['id']] = $l;
    }
    $result = []; $claims = [];
    $subs = array_column($db->get('library_subtypes')->result_array(), null, 'code');
    foreach ($rows as $r) {
        $name = task5_name($r['Nama Sekolah']); $district = task5_name($r['_district']);
        $candidate = []; foreach (task5_names($r['Nama Sekolah'], $r['_district']) as $variant) $candidate += $names[$variant.'|'.$district] ?? [];
        $exact = $codes[$r['NPSN']] ?? [];
        $record = ['source'=>$r, 'library_id'=>null, 'status'=>'BELUM_TERDAFTAR', 'note'=>'Tidak ditambahkan otomatis: task5 meminta pembaruan data yang sudah terdaftar.', 'changes'=>[], 'candidates'=>array_keys($candidate)];
        $l = null;
        if (count($sourceCodes[$r['NPSN']]) !== 1 || !preg_match('/^(?:\d{8}|P\d{7})$/D', $r['NPSN'])) {
            $record['status'] = 'TAHAN_NPSN_SUMBER'; $record['note'] = 'NPSN tidak valid atau berulang di workbook.';
        } elseif (count($exact) === 1) {
            $e = $exact[0];
            $sameName = in_array($name, [task5_name($e['institution_name'] ?: ''), task5_name($e['name'])], true);
            if ($sameName && (!$candidate || isset($candidate[$e['id']]))) $l = $e;
            else { $record['status'] = 'TAHAN_KONFLIK_IDENTITAS'; $record['note'] = 'NPSN sama tetapi nama institusi berbeda; tidak mengalihkan akun/transaksi. Periksa ID '. $e['id'].'.'; $record['candidates'][] = (int)$e['id']; }
        } elseif (count($candidate) === 1) {
            $l = reset($candidate);
        } elseif (count($candidate) > 1 || count($exact) > 1) {
            $record['status'] = 'TAHAN_PADANAN_GANDA'; $record['note'] = 'Lebih dari satu master cocok; tidak digabung otomatis.';
        }
        if ($l) {
            $record['library_id'] = (int)$l['id']; $record['candidates'] = [(int)$l['id']];
            $sub = strtolower($r['Bentuk Pendidikan']); $ref = $districts[$district] ?? null;
            if (!isset($subs[$sub]) || !$ref || !in_array($r['Status Sekolah'], ['Negeri','Swasta'], true)) {
                $record['status'] = 'TAHAN_REFERENSI'; $record['note'] = 'Padanan ditemukan, tetapi jenis/wilayah/status perlu verifikasi; tidak mengubah master.';
            } elseif ($l['village_id'] && (int)$l['district_id'] !== (int)$ref['id']) {
                $record['status'] = 'TAHAN_WILAYAH'; $record['note'] = 'Kecamatan berubah tetapi desa lama masih terikat; verifikasi alamat sebelum perubahan.';
            } else {
                $desired = ['code'=>$r['NPSN'], 'institution_name'=>$r['Nama Sekolah'], 'institution_status'=>strtolower($r['Status Sekolah']), 'library_type_id'=>$subs[$sub]['library_type_id'], 'library_subtype_id'=>$subs[$sub]['id'], 'district_id'=>$ref['id'], 'district'=>$ref['name']];
                // Preserve dedicated library names, descriptions and all fields not present in the workbook.
                if ($l['source_system'] === 'school_xlsx' || task5_name($l['name']) === task5_name($l['institution_name'] ?: $r['Nama Sekolah'])) $desired['name'] = $r['Nama Sekolah'];
                foreach ($desired as $key=>$value) if ((string)$l[$key] !== (string)$value) $record['changes'][$key] = ['before'=>$l[$key], 'after'=>$value];
                $record['status'] = $record['changes'] ? 'PERBARUI' : 'SESUAI';
                $record['note'] = $exact ? 'NPSN dan identitas sekolah cocok.' : 'Nama institusi dan kecamatan cocok unik; NPSN diselaraskan.';
                $claims[$l['id']][] = count($result);
            }
        }
        $result[] = $record;
    }
    foreach ($claims as $indexes) if (count($indexes) > 1) foreach ($indexes as $i) {
        $result[$i]['status'] = 'TAHAN_PADANAN_GANDA'; $result[$i]['changes'] = []; $result[$i]['note'] = 'Satu master dipadankan ke lebih dari satu sekolah; verifikasi diperlukan.';
    }
    return $result;
}

function task5_reports($db, $plan, $folder, $applied)
{
    $master = array_column(network_query($db,'SELECT l.id,l.code,l.name,l.institution_name,l.institution_status,l.district,s.name subtype_name,l.source_id,l.npp FROM libraries l LEFT JOIN library_subtypes s ON s.id=l.library_subtype_id ORDER BY l.id')->result_array(), null, 'id');
    $lines = []; $seen = [];
    foreach ($plan as $r) {
        $s = $r['source']; $id = $r['library_id'];
        if (!$id && count($r['candidates']) === 1) $id = $r['candidates'][0];
        $m = $master[$id] ?? null; if ($m) $seen[$m['id']] = true;
        $before = []; $after = [];
        foreach ($r['changes'] as $field=>$change) { $before[] = $field.': '.($change['before']??''); $after[] = $field.': '.($change['after']??''); }
        $lines[] = array_merge($m ? array_values($m) : array_fill(0,9,''), [
            $s['_row'], $s['NPSN'], $s['Nama Sekolah'], $s['Jenjang'], $s['Bentuk Pendidikan'], $s['_district'], $s['Status Sekolah'], $s['Perpustakaan'], $s['PD'],
            $r['status']==='PERBARUI' && $applied ? 'DIPERBARUI' : $r['status'], implode(', ',array_keys($r['changes'])), implode("\n",$before), $r['note'], implode("\n",$after), implode(', ',$r['candidates'])
        ]);
    }
    foreach ($master as $m) if (!isset($seen[$m['id']])) $lines[] = array_merge(array_values($m),array_fill(0,9,''),['HANYA_DATABASE','','','Tidak ditemukan di workbook sekolah; bukan berarti lembaga tutup. Workbook tidak mencakup perpustakaan desa, madrasah, TBM, dan kategori lain.','','']);
    $headers = ['ID database','NPSN/kode database','Nama perpustakaan database','Nama institusi database','Status institusi database','Kecamatan database','Subjenis database','ID sumber lama','NPP database','Baris sekolah','NPSN sekolah','Nama sekolah','Jenjang sekolah','Bentuk pendidikan','Kecamatan sekolah','Status sekolah','Jumlah perpustakaan sumber','PD sumber (bukan isian IPLM)','Keputusan','Kolom diubah','Nilai sebelum','Keterangan','Nilai sesudah','Kandidat ID'];
    task5_write_workbook($folder.'/PERSANDINGAN_SEKOLAH_TASK5.xlsx',$headers,$lines);
    // Rebuild the earlier side-by-side comparison using the CURRENT master and preserved source associations.
    $archives = $db->where('source_system','pendataan_task4')->order_by('source_row')->get('library_source_records')->result_array();
    if ($archives) {
        $lines = []; $seen = [];
        foreach ($archives as $a) {
            $s = json_decode($a['source_json'],true); $m = $master[$a['library_id']] ?? null;
            if ($m) $seen[$m['id']] = true;
            $lines[] = array_merge($m?array_values($m):array_fill(0,9,''), [$s['Id'],$s['NPSN'],$s['Nama'],$s['Lembaga Induk'],$s['Subjenis'],$s['Kecamatan'],$s['Kelurahan'],$s['Jenis'],$s['Npp'],$a['decision'],'','',$a['note'],'',$a['source_row']]);
        }
        foreach ($master as $m) if (!isset($seen[$m['id']])) $lines[] = array_merge(array_values($m),array_fill(0,9,''),['HANYA_DATABASE','','','Belum dipadankan ke pendataan; mungkin belum didata.','','']);
        task5_write_workbook($folder.'/PERSANDINGAN_PENDATAAN_TASK5.xlsx',array_merge(array_slice($headers,0,9),['ID pendataan','NPSN pendataan','Nama perpustakaan pendataan','Institusi pendataan','Subjenis pendataan','Kecamatan pendataan','Desa pendataan','Jenis pendataan','NPP pendataan','Keputusan terdahulu','Kolom diubah','Nilai sebelum','Keterangan','Nilai sesudah','Baris pendataan']),$lines);
    }
}

function task5_write_workbook($destination, $headers, $lines)
{
    usort($lines,function($a,$b) { $rank = function($s) { return strpos($s,'TAHAN')===0 ? 0 : ($s==='DIPERBARUI' || $s==='PERBARUI' ? 1 : ($s==='BELUM_TERDAFTAR' ? 3 : ($s==='HANYA_DATABASE' ? 4 : 2))); }; return [$rank($a[18]),$a[11],$a[0]] <=> [$rank($b[18]),$b[11],$b[0]]; });
    $writer = new Catalog_xlsx(); $path = $writer->build('Penyandingan task5',$headers,$lines);
    try {
        task4_style_workbook($path);
        $zip = new ZipArchive(); network_check($zip->open($path)===true,'Workbook unavailable');
        $dom = new DOMDocument(); $dom->loadXML($zip->getFromName('xl/worksheets/sheet1.xml'),LIBXML_NONET);
        $x = new DOMXPath($dom); $x->registerNamespace('s','http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        foreach ($x->query('//s:col') as $col) if ((int)$col->getAttribute('min')===4) { $col->removeAttribute('hidden'); $col->removeAttribute('outlineLevel'); $col->setAttribute('width','35'); }
        foreach ($x->query('//s:sheetData/s:row[position()>1]') as $row) {
            $cells = $x->query('s:c',$row); $status = $cells[18]->textContent;
            if ($status==='BELUM_TERDAFTAR') foreach ($cells as $cell) $cell->setAttribute('s','8');
            if ($status==='DIPERBARUI') foreach ($cells as $cell) $cell->setAttribute('s','4');
        }
        $zip->addFromString('xl/worksheets/sheet1.xml',$dom->saveXML()); network_check($zip->close(),'Workbook finalization failed');
        network_check(copy($path,$destination),'Cannot save comparison'); chmod($destination,0600);
    } finally { if (is_file($path)) unlink($path); }
}
