<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Guestbook extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Visit_model');
	}

	public function monitor()
	{
		$library_id = (int) $this->input->get('library_id', true);
		$qr_token = $this->Visit_model->create_kiosk_qr_token($library_id);

		$this->load->view('guestbook/monitor', [
			'title' => 'Monitor Buku Tamu',
			'qr_token' => $qr_token,
			'library_id' => $library_id,
			'success' => $this->session->flashdata('success'),
			'error' => $this->session->flashdata('error'),
		]);
	}

	public function store_guest()
	{
		try {
			$this->Visit_model->record_guestbook_visit($this->guestbook_input());
			$this->session->set_flashdata('success', 'Daftar hadir pengunjung berhasil dicatat.');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
		}

		redirect('guestbook/monitor' . $this->library_query());
	}

	public function store_member()
	{
		try {
			$this->Visit_model->record_member_search_checkin($this->input->post('identifier', true), $this->guestbook_input());
			$this->session->set_flashdata('success', 'Daftar hadir member berhasil dicatat.');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
		}

		redirect('guestbook/monitor' . $this->library_query());
	}

	public function search_members()
	{
		$keyword = $this->input->get('q', true);
		$results = $this->Visit_model->search_members_for_guestbook($keyword, 8);

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'ok' => true,
				'items' => $results,
			], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE));
	}

	public function qr_checkin($token)
	{
		$user = (array) $this->session->userdata('auth_user');
		if (empty($user['id'])) {
			$this->session->set_flashdata('redirect_after_login', 'guestbook/checkin/' . rawurlencode((string) $token));
			redirect('login');
			return;
		}

		$this->load->model('Member_model');
		$member = $this->Member_model->get_member_by_auth_user_id((int) $user['id']);
		if (! $member) {
			show_error('QR check-in hanya untuk akun member.', 403, 'Akses Ditolak');
			return;
		}

		$result = $this->Visit_model->redeem_kiosk_qr_token($token, $member, $user);
		$this->session->set_flashdata($result['ok'] ? 'success' : 'error', $result['message']);
		redirect('user/dashboard');
	}

	private function guestbook_input()
	{
		return [
			'library_id' => $this->input->post('library_id', true),
			'visitor_name' => $this->input->post('visitor_name', true),
			'member_id' => $this->input->post('member_id', true),
			'group_name' => $this->input->post('group_name', true),
			'group_leader_name' => $this->input->post('group_leader_name', true),
			'visitor_count' => $this->input->post('visitor_count', true),
			'purpose_label' => $this->input->post('purpose_label', true),
			'address' => $this->input->post('address', true),
			'information' => $this->input->post('information', true),
		];
	}

	private function library_query()
	{
		$library_id = (int) $this->input->post('library_id', true);
		return $library_id > 0 ? '?library_id=' . $library_id : '';
	}
}
