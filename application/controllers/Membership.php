<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Membership extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Member_model');
		$this->load->model('Member_registration_model');
		$this->load->model('Region_model');
	}

	public function register()
	{
		$this->load->view('membership/register', [
			'title' => 'Pendaftaran Member Online',
			'form_options' => $this->Member_model->form_options(),
			'provinces' => $this->Region_model->get_provinces(),
			'districts' => $this->Region_model->get_districts(),
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

	public function registration_status()
	{
		$lookup_type = (string) $this->input->post('lookup_type', true);
		$lookup_value = (string) $this->input->post('lookup_value', true);
		$request = null;
		$error = null;

		if ($this->input->method(true) === 'POST') {
			try {
				$request = $this->Member_registration_model->find_request_for_public_status($lookup_type, $lookup_value);
			} catch (Throwable $e) {
				$error = $e->getMessage();
			}
		}

		$this->load->view('membership/status_lookup', [
			'title' => 'Cek Status Pendaftaran Member',
			'lookup_type' => $lookup_type ?: 'nik',
			'lookup_value' => $lookup_value,
			'request' => $request,
			'error' => $error,
			'default_password' => 'perpus2026',
		]);
	}

	public function submit_registration()
	{
		if ($this->post_body_exceeds_server_limit()) {
			$this->session->set_flashdata('registration_error', 'Pendaftaran belum terkirim karena total ukuran berkas melebihi batas penerimaan server (' . $this->format_ini_size(ini_get('post_max_size')) . '). Perkecil berkas atau kurangi lampiran opsional hingga totalnya di bawah batas tersebut.');
			redirect('membership/register');
			return;
		}

		$previous_db_debug = $this->db->db_debug;
		$this->db->db_debug = false;
		try {
			$result = $this->Member_registration_model->create_request([
				'full_name' => $this->input->post('full_name', true),
				'identity_number' => $this->input->post('identity_number', true),
				'birth_place' => $this->input->post('birth_place', true),
				'birth_date' => $this->input->post('birth_date', true),
				'gender' => $this->input->post('gender', true),
				'address' => $this->input->post('address', true),
				'district_id' => $this->input->post('district_id', true),
				'village_id' => $this->input->post('village_id', true),
				'identity_address' => $this->input->post('identity_address', true),
				'identity_province_id' => $this->input->post('identity_province_id', true),
				'identity_regency_id' => $this->input->post('identity_regency_id', true),
				'identity_district_id' => $this->input->post('identity_district_id', true),
				'identity_village_id' => $this->input->post('identity_village_id', true),
				'domicile_address' => $this->input->post('domicile_address', true),
				'domicile_province_id' => $this->input->post('domicile_province_id', true),
				'domicile_regency_id' => $this->input->post('domicile_regency_id', true),
				'domicile_district_id' => $this->input->post('domicile_district_id', true),
				'domicile_village_id' => $this->input->post('domicile_village_id', true),
				'phone' => $this->input->post('phone', true),
				'email' => $this->input->post('email', true),
				'member_type' => $this->input->post('member_type', true),
				'education' => $this->input->post('education', true),
				'occupation' => $this->input->post('occupation', true),
				'identity_document_type' => $this->input->post('identity_document_type', true),
				'identity_domicile' => $this->input->post('identity_domicile', true),
				'residency_note' => $this->input->post('residency_note', true),
			], $_FILES);
			// Notifikasi adalah proses tambahan. Pendaftaran yang sudah tersimpan
			// harus tetap dianggap berhasil saat tabel/template WA sedang bermasalah.
			try {
				if ($this->db->table_exists('wa_outbox')) {
					$this->load->model('Whatsapp_model');
					$this->Whatsapp_model->queue_template('member_registration_received', $this->input->post('phone', true), [
						'member_name' => $this->input->post('full_name', true),
						'request_code' => $result['code'],
					]);
				}
			} catch (Throwable $notification_error) {
				log_message('error', 'Antrean WA pendaftaran ' . $result['code'] . ' gagal: ' . $notification_error->getMessage());
			}
			$this->session->set_flashdata('registration_success', 'Pendaftaran berhasil dikirim. Kode antrean: ' . $result['code']);
			$this->db->db_debug = $previous_db_debug;
			redirect('membership/register/pending/' . rawurlencode($result['token']));
		} catch (Throwable $e) {
			$this->db->db_debug = $previous_db_debug;
			$this->session->set_flashdata('registration_error', $this->friendly_registration_error($e));
			$this->session->set_flashdata('registration_old', $this->input->post(null, true));
			redirect('membership/register');
		}
	}

	private function friendly_registration_error(Throwable $error)
	{
		$message = trim((string) $error->getMessage());
		$database_error = (array) $this->db->error();
		$combined = strtolower($message . ' ' . ($database_error['message'] ?? ''));
		if ((int) ($database_error['code'] ?? 0) === 1062
			|| strpos($combined, 'duplicate entry') !== false
			|| strpos($combined, 'identity_number') !== false) {
			return 'NIK tersebut sudah terdaftar atau sudah memiliki pengajuan. Silakan gunakan menu Cek Status atau masuk dengan akun yang sudah ada.';
		}
		if ($message === '' || preg_match('/\b(sql|database|query|constraint|1062)\b/i', $message)) {
			return 'Pendaftaran belum dapat disimpan. Periksa kembali data yang diisi lalu coba lagi.';
		}
		return $message;
	}

	private function post_body_exceeds_server_limit()
	{
		$content_length = isset($_SERVER['CONTENT_LENGTH']) ? (int) $_SERVER['CONTENT_LENGTH'] : 0;
		$limit = $this->ini_size_to_bytes(ini_get('post_max_size'));
		return $content_length > 0 && $limit > 0 && $content_length > $limit;
	}

	private function ini_size_to_bytes($value)
	{
		$value = trim((string) $value);
		if ($value === '') return 0;
		$unit = strtolower(substr($value, -1));
		$number = (float) $value;
		if ($unit === 'g') return (int) ($number * 1073741824);
		if ($unit === 'm') return (int) ($number * 1048576);
		if ($unit === 'k') return (int) ($number * 1024);
		return (int) $number;
	}

	private function format_ini_size($value)
	{
		$bytes = $this->ini_size_to_bytes($value);
		return $bytes > 0 ? number_format($bytes / 1048576, 0, ',', '.') . ' MB' : 'konfigurasi server';
	}

	public function region_regencies($province_id)
	{
		$this->region_json($this->Region_model->get_regencies((int) $province_id));
	}

	public function region_districts($regency_id)
	{
		$this->region_json($this->Region_model->get_districts_by_regency((int) $regency_id));
	}

	public function region_villages($district_id)
	{
		$this->region_json($this->Region_model->get_villages_by_district((int) $district_id));
	}

	private function region_json(array $rows)
	{
		$payload = array_map(function ($row) {
			return ['id' => (int) $row['id'], 'code' => (string) $row['code'], 'name' => (string) $row['name'], 'area_type' => $row['area_type'] ?? null];
		}, $rows);
		$this->output
			->set_header('Cache-Control: private, max-age=3600')
			->set_content_type('application/json', 'utf-8')
			->set_output(json_encode(['data' => $payload], JSON_UNESCAPED_UNICODE));
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
