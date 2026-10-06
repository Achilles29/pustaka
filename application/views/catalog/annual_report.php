<?php defined('BASEPATH') OR exit('No direct script access allowed');
$active_filters = array_filter($filters, function ($v) { return $v !== ''; });
$query = http_build_query($active_filters);
$max_titles = max(array_merge([1], array_column($report['rows'], 'titles')));
$number = function ($v) { return number_format((int) $v, 0, ',', '.'); };
$can_export = !empty($current_user['is_superadmin']) || !empty($user_perms['catalog.index']['can_export']);
?>
<style>@media print{@page{size:A4 landscape}.catalog-annual-report .table-responsive{overflow:visible!important}.catalog-annual-report table{font-size:9px}.catalog-annual-report th{white-space:normal!important;min-width:0!important}.catalog-annual-report td,.catalog-annual-report th{padding:5px!important}.catalog-annual-report .progress{display:none}.catalog-annual-report tr{break-inside:avoid}}</style>
<div class="page-header"><div class="container-xl"><div class="row g-3 align-items-center">
 <div class="col"><div class="page-pretitle">Dari tahun ke tahun</div><h1 class="page-title">Perkembangan katalog</h1><p class="text-secondary mt-2 mb-0">Pertumbuhan koleksi buku fisik dan digital, dalam satu laporan.</p></div>
 <div class="col-auto btn-list d-print-none">
  <a class="btn btn-outline-primary" href="<?=html_escape(base_url('catalog?'.$query));?>">Kembali ke katalog</a>
  <?php if ($can_export): ?>
  <a class="btn btn-success" href="<?=html_escape(base_url('catalog/annual-report?'.$query.'&format=xlsx'));?>" title="Unduh laporan Excel (.xlsx)" aria-label="Unduh laporan Excel (.xlsx)"><i class="ti ti-download me-1" aria-hidden="true"></i>Excel</a>
  <a class="btn btn-primary" href="<?=html_escape(base_url('catalog/annual-report?'.$query.'&format=csv'));?>" title="Unduh laporan CSV" aria-label="Unduh laporan CSV"><i class="ti ti-download me-1" aria-hidden="true"></i>CSV</a>
  <?php endif; ?><button class="btn btn-outline-secondary" onclick="window.print()">Cetak</button>
 </div>
</div></div></div>
<div class="page-body catalog-annual-report"><div class="container-xl">
 <div class="row row-cards mb-3">
 <?php foreach ([['Total judul unik','total_titles','primary'],['Judul fisik','total_physical_titles','blue'],['Judul digital','total_digital_titles','green'],['Tersedia dalam keduanya','total_hybrid_titles','purple'],['Belum teridentifikasi','total_unclassified_titles','secondary'],['Eksemplar fisik','total_physical_copies','blue']] as $metric): ?>
 <div class="col-6 col-lg-4 col-xl-2"><div class="card h-100"><div class="card-body"><div class="text-secondary"><?=html_escape($metric[0]);?></div><div class="h1 mb-0 text-<?=$metric[2];?>"><?=$number($report[$metric[1]]);?></div></div></div></div>
 <?php endforeach; ?>
 </div>
 <div class="alert alert-info d-block">
 <strong>Dasar laporan: tahun masuk katalog, bukan tahun terbit atau tahun unggah file digital.</strong>
 <p class="mb-2 mt-1">INLIS memakai tanggal pembuatan katalog asli; sumber lain memakai tanggal masuk aplikasi. Jenis koleksi dan eksemplar berdasarkan kondisi saat ini, bukan rekonstruksi pengadaan tahunan. Tahun berjalan belum penuh.</p>
 <ul class="mb-2">
  <li><strong>Fisik:</strong> judul dengan eksemplar non-digital sesuai filter dan cakupan perpustakaan. Eksemplar INLIS yang jenis medianya kosong/tidak diketahui diperlakukan sebagai fisik.</li>
  <li><strong>Digital:</strong> judul dengan item Ebook/media digital sesuai filter, atau aset digital aktif (termasuk akses internal).</li>
  <li><strong>Keduanya:</strong> termasuk pada kolom fisik dan digital, tetapi hanya satu kali dalam total judul unik. Total = fisik + digital − keduanya + belum teridentifikasi.</li>
  <li><strong>Belum teridentifikasi:</strong> tidak memiliki eksemplar sesuai filter maupun penanda digital aktif.</li>
 </ul>
 <?php if ($report['unknown']['titles']): ?><div><?=$number($report['unknown']['titles']);?> judul tanpa tanggal valid dipisahkan dari kumulatif.</div><?php endif; ?>
 <?php if ($query): ?><div class="mt-2"><strong>Filter katalog:</strong> <?=html_escape(implode(' · ', array_map(function ($k, $v) { return $k.': '.$v; }, array_keys($active_filters), array_values($active_filters))));?></div><?php endif; ?>
 </div>
 <div class="card"><div class="card-header"><h2 class="card-title">Jejak pertumbuhan koleksi</h2></div><div class="table-responsive"><table class="table table-vcenter card-table">
 <thead><tr><th>Tahun masuk</th><th style="min-width:150px">Total judul unik</th><th class="text-blue">Fisik</th><th class="text-green">Digital</th><th>Keduanya</th><th>Belum teridentifikasi</th><th>Eksemplar fisik</th><th>Kumulatif total</th><th>Kumulatif fisik</th><th>Kumulatif digital</th><th>Perubahan total</th></tr></thead><tbody>
 <?php foreach ($report['rows'] as $row): ?><tr>
  <td class="fw-bold"><?=(int)$row['year'];?></td>
  <td><div class="d-flex align-items-center gap-2"><span style="min-width:45px"><?=$number($row['titles']);?></span><div class="progress flex-fill" style="height:8px" aria-hidden="true"><div class="progress-bar bg-primary" style="width:<?=round($row['titles']/$max_titles*100,2);?>%"></div></div></div></td>
  <td class="text-blue fw-semibold"><?=$number($row['physical_titles']);?></td><td class="text-green fw-semibold"><?=$number($row['digital_titles']);?></td>
  <td><?=$number($row['hybrid_titles']);?></td><td><?=$number($row['unclassified_titles']);?></td><td><?=$number($row['physical_copies']);?></td>
  <td class="fw-semibold"><?=$number($row['cumulative']);?></td><td><?=$number($row['physical_cumulative']);?></td><td><?=$number($row['digital_cumulative']);?></td>
  <td><?=$row['growth']===null?'—':html_escape(($row['growth']>0?'+':'').number_format($row['growth'],1,',','.').'%');?></td>
 </tr><?php endforeach; ?>
 <?php if ($report['unknown']['titles']): $r=$report['unknown']; ?><tr class="bg-light"><td>Tidak diketahui</td><?php foreach (['titles','physical_titles','digital_titles','hybrid_titles','unclassified_titles','physical_copies'] as $key): ?><td><?=$number($r[$key]);?></td><?php endforeach; ?><td colspan="4">Tidak masuk kumulatif</td></tr><?php endif; ?>
 <?php if (!$report['total_titles']): ?><tr><td colspan="11" class="text-center py-5 text-secondary">Belum ada katalog yang sesuai dengan filter ini.</td></tr><?php endif; ?>
 </tbody></table></div><div class="card-footer small text-secondary">Fisik, digital, dan keduanya dihitung dalam judul, bukan jumlah file. Perubahan dihitung dari total judul terhadap tahun sebelumnya; — berarti pembanding tidak tersedia atau nol. Diambil <?=date('d/m/Y H:i');?> WIB.</div></div>
</div></div>
