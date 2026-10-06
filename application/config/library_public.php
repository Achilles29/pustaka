<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Public profile template: replace the history text with approved institutional copy.
$config['profile_intro'] = 'Perpustakaan adalah tempat bertemunya rasa ingin tahu dan kesempatan untuk berkembang. Pustaka Digital Rembang menghubungkan masyarakat dengan koleksi bacaan, pengetahuan, dan warisan lokal melalui satu pintu layanan.';
$config['profile_history'] = 'Informasi sejarah, tonggak perkembangan, serta visi dan misi resmi perpustakaan akan dilengkapi oleh pengelola.';
$config['service_hours'] = [
    ['label'=>'Senin – Kamis', 'days'=>[1,2,3,4], 'open'=>'08:00', 'close'=>'16:00'],
    ['label'=>'Jumat', 'days'=>[5], 'open'=>'08:00', 'close'=>'11:00'],
    ['label'=>'Sabtu – Minggu', 'days'=>[6,7], 'open'=>'08:00', 'close'=>'16:00'],
];
