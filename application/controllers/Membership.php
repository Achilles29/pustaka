<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Membership extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Member_model');
		$this->load->model('Member_registration_model');
	}

	public function register()
	{
		$this->load->view('membership/register', [
			'title' => 'Pendaftaran Member Online',
			'form_options' => $this->Member_model->form_options(),
		]);
	}

	public function pending($code)
	{
		$request = $this->Member_registration_model->get_request_by_public_token((string) $code);
		if (! $request) {
			show_404();
			return;
		}

		$this->load->view('membership/pending', [
			'title' => 'Status Pendaftaran Member',
			'request' => $request,
			'default_password' => 'perpus2026',
		]);
	}

	public function submit_registration()
	{
		try {
			$result = $this->Member_registration_model->create_request([
				'full_name' => $this->input->post('full_name', true),
				'identity_number' => $this->input->post('identity_number', true),
				'birth_place' => $this->input->post('birth_place', true),
				'birth_date' => $this->input->post('birth_date', true),
				'gender' => $this->input->post('gender', true),
				'address' => $this->input->post('address', true),
				'district' => $this->input->post('district', true),
				'village' => $this->input->post('village', true),
				'phone' => $this->input->post('phone', true),
				'email' => $this->input->post('email', true),
				'member_type' => $this->input->post('member_type', true),
				'education' => $this->input->post('education', true),
				'occupation' => $this->input->post('occupation', true),
				'residency_note' => $this->input->post('residency_note', true),
			], $_FILES);
			$this->session->set_flashdata('registration_success', 'Pendaftaran berhasil dikirim. Kode antrean: ' . $result['code']);
			redirect('membership/register/pending/' . rawurlencode($result['token']));
		} catch (Throwable $e) {
			$this->session->set_flashdata('registration_error', $e->getMessage());
			$this->session->set_flashdata('registration_old', $this->input->post(null, true));
			redirect('membership/register');
		}
	}

	public function verify($member_id, $token = '')
	{
		$member = $this->Member_model->verify_digital_card((int) $member_id, (string) $token);

		$this->load->view('membership/verify', [
			'title' => 'Verifikasi Kartu Anggota',
			'is_valid' => ! empty($member),
			'member' => $member,
			'token' => $token,
		]);
	}

	public function renewal_request()
	{
		$user = (array) $this->session->userdata('auth_user');
		if (empty($user['id'])) {
			redirect('login');
		}

		try {
			$member = $this->Member_model->get_member_by_auth_user_id((int) $user['id']);
			if (! $member) {
				throw new RuntimeException('Akun belum terhubung ke data member.');
			}
			$result = $this->Member_model->create_renewal_request((int) $member['id'], [
				'requested_months' => $this->input->post('requested_months', true),
				'reason' => $this->input->post('reason', true),
			]);
			$this->session->set_flashdata('success', 'Pengajuan perpanjangan terkirim. Kode: ' . $result['code']);
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
		}

		redirect('user/dashboard#membership-renewal');
	}
}
