<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Guestbook_settings extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Visit_model');
		$this->load->model('Library_model');
	}

	public function index()
	{
		$this->require_permission('guestbook.settings', 'view');

		$this->render('guestbook/settings', [
			'title' => 'Pengaturan Buku Tamu',
			'settings' => $this->Visit_model->get_kiosk_settings(),
			'libraries' => $this->Library_model->get_library_options(),
		]);
	}

	public function update()
	{
		$this->require_permission('guestbook.settings', 'edit');

		try {
			$before = $this->Visit_model->get_kiosk_settings();
			$after = $this->Visit_model->update_kiosk_settings([
				'qr_refresh_seconds' => $this->input->post('qr_refresh_seconds', true),
				'default_visit_library_id' => $this->input->post('default_visit_library_id', true),
			]);
			$this->audit_event('guestbook.settings.update', 'visit_kiosk_settings', null, $before, $after);
			$this->session->set_flashdata('success', 'Pengaturan monitor buku tamu berhasil disimpan.');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
		}

		redirect('guestbook/settings');
	}
}
