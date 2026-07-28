<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reading_points extends MY_Controller
{
	public function index()
	{
		$this->require_permission('reading_points.index', 'view');
		$this->render('modules/placeholder', [
			'title' => 'Pojok Baca Digital',
			'module_label' => 'Akses Berbasis Lokasi',
			'page_title' => 'Pojok Baca Digital',
			'description' => 'Fondasi untuk titik GPS, radius, mitra, kuota/token baca, dan absensi ulang di perpustakaan.',
		]);
	}
}
