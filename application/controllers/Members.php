<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Members extends MY_Controller
{
	public function index()
	{
		$this->require_permission('members.index', 'view');
		$this->render('modules/placeholder', [
			'title' => 'Membership Digital',
			'module_label' => 'Pemustaka',
			'page_title' => 'Membership Digital',
			'description' => 'Fondasi untuk akun pemustaka, kartu digital, QR member, sinkronisasi anggota INLISLite, dan histori layanan.',
		]);
	}
}
