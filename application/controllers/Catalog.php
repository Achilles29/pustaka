<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Catalog extends MY_Controller
{
	public function index()
	{
		$this->require_permission('catalog.index', 'view');
		$this->render('modules/placeholder', [
			'title' => 'Katalog Buku',
			'module_label' => 'Koleksi',
			'page_title' => 'Katalog Buku',
			'description' => 'Fondasi untuk database buku, eksemplar, cover, metadata INLISLite, dan status koleksi digital.',
		]);
	}
}
