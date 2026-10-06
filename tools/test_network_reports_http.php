<?php
// Reuse full authentication/security regression against the isolated test service.
require __DIR__.'/test_library_network_http.php';
$baseChecks=$checks;
ok(request('/reports/network',null,'anonymous-report')['status']===302,'Anonymous central report redirects to login');
login('test-school-a',$new);
foreach(['/reports/network','/reports/network/export/csv','/reports/network/export/xlsx','/network_reports/index']as$path)ok(request($path)['status']===403,'School cannot access central report: '.$path);
ok(request('/reports/network',null,'test-member')['status']===403,'Member cannot access central report');
foreach(['test-central','test-legacy']as$who){$r=request('/reports/network',null,$who);ok($r['status']===200&&strpos($r['body'],'A PHP Error')===false&&strpos($r['body'],'Rekap per')!==false,'Central report renders for '.$who);}
foreach(['type','district','village','library']as$group){$r=request('/reports/network?group='.$group.'&year=2026',null,'test-central');ok($r['status']===200&&strpos($r['body'],'A PHP Error')===false,'HTTP grouping '.$group);}
foreach(['year=1','source=bad','q%5B%5D=malformed','library_id=-1']as$q)ok(request('/reports/network?'.$q,null,'test-central')['status']===400,'Invalid HTTP report filter rejected');
$r=request('/reports/network/export/csv?source=legacy&library_id=4&year=2026',null,'test-central');$lines=array_map('str_getcsv',preg_split('/\r?\n/',trim($r['body'])));ok($r['status']===200&&count($lines)===2,'CSV exports only selected library');ok((int)$lines[1][2]===2&&(int)$lines[1][7]===1,'CSV catalog and physical-item totals match selected fixtures');
$r=request('/reports/network/export/xlsx?source=legacy&library_id=4&year=2026',null,'test-central');$path=$directory.'/network-report.xlsx';file_put_contents($path,$r['body']);$z=new ZipArchive();ok($z->open($path)===true,'Central export is real XLSX');$xml=simplexml_load_string($z->getFromName('xl/worksheets/sheet1.xml'));$z->close();$xml->registerXPathNamespace('s','http://schemas.openxmlformats.org/spreadsheetml/2006/main');ok(count($xml->xpath('//s:sheetData/s:row'))===2&&(string)$xml->xpath('//s:c[@r="C2"]/s:v')[0]==='2','Excel matches filtered CSV');
$r=request('/reports/network/export/csv?source=legacy&library_id=unassigned&year=2026',null,'test-central');ok($r['status']===200&&strpos($r['body'],'Belum terpetakan')!==false,'Unassigned filter survives export');
$r=request('/reports/network?q=__nothing__',null,'test-central');ok($r['status']===200&&strpos($r['body'],'Tidak ada data yang cocok')!==false,'Empty report handled');
$r=request('/library-workspace/records/books');ok(strpos($r['body'],'network-workspace-tabs')===false,'School top tabs removed');
foreach(['Dashboard','Katalog Buku','Eksemplar','Anggota Lokal','Peminjaman','Buku Tamu','Laporan','Profil Perpustakaan','Aturan Peminjaman','Admin Perpustakaan','Ganti Password']as$title)ok(strpos($r['body'],'admin-menu-title">'.$title.'</span>')!==false,'Sidebar menu '.$title);
ok(strpos($r['body'],'reports/network')===false,'School sidebar does not expose central aggregate menu');
// A central role still needs the specific export permission.
$pageId=$db->where('code','reports.network')->get('sys_page')->row()->id;$roleId=$db->where('code','ADMIN')->get('auth_role')->row()->id;
$db->where('role_id',$roleId)->where('page_id',$pageId)->update('auth_role_permission',['can_export'=>0]);request('/logout',null,'test-legacy');login('test-legacy',$fixture['password'],'test-legacy');ok(request('/reports/network/export/xlsx',null,'test-legacy')['status']===403,'View-only central operator cannot export');
$db->where('role_id',$roleId)->where('page_id',$pageId)->update('auth_role_permission',['can_export'=>1]);
echo ($checks-$baseChecks)," new report/sidebar HTTP checks passed; ",$checks," including authentication regression.\n";
