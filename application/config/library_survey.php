<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Pendataan profil tambahan. Never consumed by Iplm_model or Iplm_analysis.
$config['library_survey_fields'] = [
 'library_presence'=>['Keberadaan perpustakaan','select',['unknown'=>'Belum dikonfirmasi','yes'=>'Ada perpustakaan','no'=>'Belum ada perpustakaan']],
 'postal_code'=>['Kode pos','text'], 'fax'=>['Fax','text'], 'altitude'=>['Ketinggian / MDPL','number'],
 'library_class'=>['Kelas perpustakaan','text'], 'library_head'=>['Nama kepala perpustakaan','text'],
 'library_decree'=>['SK pendirian perpustakaan','text'], 'established_year'=>['Tahun berdiri','year'],
 'institution_head'=>['Nama kepala lembaga','text'], 'school_head'=>['Nama kepala sekolah','text'],
 'nis'=>['NIS','text'], 'head_registration'=>['Nomor induk kepala perpustakaan','text'],
 'institution_decree'=>['SK pendirian lembaga induk','text'], 'institution_kind'=>['Jenis lembaga','text'],
 'vision'=>['Visi','textarea'], 'mission'=>['Misi','textarea'],
 'service_system'=>['Sistem layanan','text'], 'weekly_hours'=>['Jumlah jam layanan per minggu','number'],
 'timezone'=>['Zona waktu','select',[''=>'Belum diisi','WIB'=>'WIB','WITA'=>'WITA','WIT'=>'WIT']],
 'service_days'=>['Hari layanan','text'], 'npp_date'=>['Tanggal terbit NPP','date'], 'accreditation'=>['Akreditasi perpustakaan','text'],
 'seven_hour_service'=>['Layanan minimal 7 jam setiap hari kerja','boolean'],
 'five_percent_budget'=>['Anggaran perpustakaan minimal 5%','boolean'],
 'uses_it'=>['Memanfaatkan teknologi informasi','boolean'], 'automation'=>['Otomasi perpustakaan','boolean'],
 'free_internet'=>['Internet gratis','boolean'], 'cctv'=>['CCTV','boolean'],
 'assistance'=>['Jenis bantuan','textarea'], 'legacy_budget'=>['Anggaran pendataan (rupiah)','number'],
 'santri'=>['Jumlah santri','number'], 'students'=>['Jumlah siswa pendataan','number'],
 'university_students'=>['Jumlah mahasiswa','number'], 'study_groups'=>['Jumlah rombongan belajar','number'],
 'collection_titles'=>['Jumlah judul koleksi pendataan','number'], 'collection_copies'=>['Jumlah eksemplar pendataan','number'],
 'librarians'=>['Jumlah pustakawan pendataan','number'], 'technical_staff'=>['Jumlah tenaga teknis pendataan','number'],
 'members'=>['Jumlah anggota pendataan','number'], 'visits'=>['Jumlah kunjungan pendataan','number'],
 'reference_year'=>['Tahun acuan angka pendataan','year'], 'notes'=>['Catatan pengelola / sumber angka','textarea'],
];
