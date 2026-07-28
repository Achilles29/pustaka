<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Events extends MY_Controller
{
	public function index()
	{
		$this->require_permission('events.index', 'view');
		$this->render('modules/placeholder', [
			'title' => 'Event Literasi',
			'module_label' => 'Agenda',
			'page_title' => 'Event Literasi',
			'description' => 'Fondasi untuk agenda event, pendaftaran peserta, QR attendance, dokumentasi, dan laporan kegiatan.',
		]);
	}
}
