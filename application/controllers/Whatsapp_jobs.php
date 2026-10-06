<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Worker terjadwal WA Center. Hanya boleh dijalankan dari CLI/cron, tidak
 * tersedia sebagai URL publik ataupun session admin.
 */
class Whatsapp_jobs extends CI_Controller
{
	public function run()
	{
		if (! is_cli()) {
			show_404();
			return;
		}
		$this->load->model('Whatsapp_model');
		try {
			$result = $this->Whatsapp_model->run_automations();
			echo json_encode(['ok' => true, 'at' => date('c'), 'result' => $result], JSON_UNESCAPED_UNICODE) . PHP_EOL;
		} catch (Throwable $e) {
			echo json_encode(['ok' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE) . PHP_EOL;
			exit(1);
		}
	}
}
