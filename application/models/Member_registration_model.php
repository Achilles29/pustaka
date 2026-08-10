<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Member_registration_model extends CI_Model
{
	const MAX_UPLOAD_BYTES = 2097152;

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Region_model');
	}

	public function stats()
	{
		return [
			'total' => $this->count_where([]),
			'pending' => $this->count_where(['status' => 'pending']),
			'verified' => $this->count_where(['status' => 'verified']),
			'rejected' => $this->count_where(['status' => 'rejected']),
		];
	}

	public function count_requests(array $filters = [])
	{
		$this->apply_filters($filters);
		return (int) $this->db->count_all_results();
	}

	public function get_requests(array $filters = [], $limit = 25, $offset = 0)
	{
		$this->apply_filters($filters);

		return $this->db
			->select('r.*, m.member_no')
			->join('members m', 'm.id = r.member_id', 'left')
			->order_by("FIELD(r.status, 'pending', 'verified', 'rejected', 'cancelled')", '', false)
			->order_by('r.id', 'DESC')
			->limit(max(1, min(100, (int) $limit)), max(0, (int) $offset))
			->get()
			->result_array();
	}

	public function get_request($id)
	{
		return $this->db
			->from('member_registration_requests')
			->where('id', (int) $id)
			->limit(1)
			->get()
			->row_array();
	}

	public function get_request_by_code($code)
	{
		return $this->db
			->from('member_registration_requests')
			->where('registration_code', trim((string) $code))
			->limit(1)
			->get()
			->row_array();
	}

	public function get_request_by_public_token($token)
	{
		return $this->db
			->from('member_registration_requests')
			->where('public_token', trim((string) $token))
			->limit(1)
			->get()
			->row_array();
	}

	public function create_request(array $data, array $files)
	{
		$identity_number = preg_replace('/\D+/', '', (string) ($data['identity_number'] ?? ''));
		$full_name = trim((string) ($data['full_name'] ?? ''));
		if ($full_name === '') {
			throw new RuntimeException('Nama lengkap wajib diisi.');
		}
		if (strlen($identity_number) < 12) {
			throw new RuntimeException('NIK/nomor identitas wajib diisi dengan benar.');
		}

		$identity_document_type = (string) ($data['identity_document_type'] ?? '');
		if (! in_array($identity_document_type, ['ktp', 'kk'], true)) {
			throw new RuntimeException('Pilih salah satu dokumen identitas: KTP atau Kartu Keluarga.');
		}
		$identity_domicile = (string) ($data['identity_domicile'] ?? '');
		if (! in_array($identity_domicile, ['rembang', 'outside_rembang'], true)) {
			throw new RuntimeException('Pilih status domisili pada dokumen identitas.');
		}

		$is_rembang = $identity_domicile === 'rembang';
		$location = $this->resolve_location($data, $is_rembang);
		$paths = $this->upload_required_files($files, $identity_document_type, $is_rembang);

		$payload = [
			'registration_code' => $this->next_registration_code(),
			'public_token' => $this->new_public_token(),
			'full_name' => $this->clip($full_name, 180),
			'identity_number' => $this->clip($identity_number, 80),
			'birth_place' => $this->blank_to_null($data['birth_place'] ?? null),
			'birth_date' => $this->blank_to_null($data['birth_date'] ?? null),
			'gender' => $this->blank_to_null($data['gender'] ?? null),
			'address' => $this->blank_to_null($data['address'] ?? null),
			'district' => $location['district'],
			'village' => $location['village'],
			'phone' => $this->clip($data['phone'] ?? null, 80),
			'email' => $this->clip($data['email'] ?? null, 180),
			'member_type' => $this->clip($data['member_type'] ?? 'Umum', 80),
			'education' => $this->blank_to_null($data['education'] ?? null),
			'occupation' => $this->blank_to_null($data['occupation'] ?? null),
			'is_rembang_resident' => $is_rembang ? 1 : 0,
			'residency_note' => $is_rembang ? null : $this->blank_to_null($data['residency_note'] ?? null),
			'photo_path' => $paths['photo'],
			'ktp_path' => $paths['ktp'],
			'kk_path' => $paths['kk'],
			'support_letter_path' => $paths['support_letter'],
			'status' => 'pending',
		];

		$this->db->insert('member_registration_requests', $payload);
		return [
			'id' => (int) $this->db->insert_id(),
			'code' => $payload['registration_code'],
			'token' => $payload['public_token'],
		];
	}

	private function resolve_location(array $data, $is_rembang)
	{
		if (! $is_rembang) {
			$district = $this->blank_to_null($data['district'] ?? null);
			$village = $this->blank_to_null($data['village'] ?? null);
			if ($district === null || $village === null) {
				throw new RuntimeException('Kecamatan/kota dan desa/kelurahan wajib diisi untuk domisili luar Rembang.');
			}

			return [
				'district' => $this->clip($district, 120),
				'village' => $this->clip($village, 120),
			];
		}

		$district_id = (int) ($data['district_id'] ?? 0);
		$village_id = (int) ($data['village_id'] ?? 0);
		$district = $district_id > 0 ? $this->Region_model->get_district($district_id) : null;
		$village = $village_id > 0 ? $this->Region_model->get_village($village_id) : null;
		if (! $district || ! $village || (int) $district['is_active'] !== 1 || (int) $village['is_active'] !== 1 || (int) $village['district_id'] !== $district_id) {
			throw new RuntimeException('Pilih kecamatan dan desa/kelurahan Rembang yang valid.');
		}

		return [
			'district' => $this->clip($district['name'], 120),
			'village' => $this->clip($village['name'], 120),
		];
	}

	public function update_status($id, $status, $admin_note, $verified_by, callable $create_member)
	{
		$status = in_array($status, ['verified', 'rejected', 'cancelled'], true) ? $status : 'pending';
		$request = $this->get_request((int) $id);
		if (! $request) {
			throw new RuntimeException('Pendaftaran tidak ditemukan.');
		}
		if ($request['status'] !== 'pending') {
			throw new RuntimeException('Pendaftaran ini sudah diproses.');
		}

		$member_id = null;
		$this->db->trans_start();
		if ($status === 'verified') {
			$member_id = (int) $create_member($request);
		}

		$this->db
			->where('id', (int) $id)
			->update('member_registration_requests', [
				'status' => $status,
				'admin_note' => $this->blank_to_null($admin_note),
				'verified_by' => (int) $verified_by ?: null,
				'verified_at' => date('Y-m-d H:i:s'),
				'member_id' => $member_id ?: null,
			]);
		$this->db->trans_complete();

		if (! $this->db->trans_status()) {
			throw new RuntimeException('Pendaftaran gagal diproses.');
		}

		return $member_id;
	}

	private function upload_required_files(array $files, $identity_document_type, $is_rembang)
	{
		$paths = [
			'photo' => $this->store_file($files, 'photo_file', true, 'foto diri', ['jpg', 'jpeg', 'png']),
			'ktp' => $identity_document_type === 'ktp' ? $this->store_file($files, 'ktp_file', true, 'KTP') : null,
			'kk' => $identity_document_type === 'kk' ? $this->store_file($files, 'kk_file', true, 'Kartu Keluarga') : null,
			// Surat pendukung hanya relevan untuk identitas luar Rembang dan tetap opsional.
			'support_letter' => $is_rembang ? null : $this->store_file($files, 'support_letter_file', false, 'surat keterangan luar Rembang'),
		];

		return $paths;
	}

	private function store_file(array $files, $field, $required, $label = '', array $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'])
	{
		$label = $label ?: str_replace('_file', '', $field);
		if (empty($files[$field]['name'])) {
			if ($required) {
				throw new RuntimeException('Berkas ' . $label . ' wajib diunggah.');
			}
			return null;
		}
		if (! empty($files[$field]['error']) && (int) $files[$field]['error'] !== UPLOAD_ERR_OK) {
			throw new RuntimeException('Upload berkas ' . $label . ' gagal. Ukuran maksimum setiap berkas adalah 2 MB.');
		}
		if (empty($files[$field]['tmp_name']) || ! is_uploaded_file($files[$field]['tmp_name'])) {
			throw new RuntimeException('Berkas ' . $label . ' tidak valid.');
		}

		$extension = strtolower(pathinfo($files[$field]['name'], PATHINFO_EXTENSION));
		if (! in_array($extension, $allowed_extensions, true)) {
			throw new RuntimeException('Format berkas ' . $label . ' tidak sesuai.');
		}
		if ((int) $files[$field]['size'] > self::MAX_UPLOAD_BYTES) {
			throw new RuntimeException('Ukuran setiap berkas maksimal 2 MB.');
		}

		$relative_dir = 'assets/uploads/member_registrations/' . date('Y/m');
		$absolute_dir = FCPATH . $relative_dir;
		if (! is_dir($absolute_dir)) {
			mkdir($absolute_dir, 0775, true);
		}

		$name = $field . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
		$target = $absolute_dir . '/' . $name;
		if (! move_uploaded_file($files[$field]['tmp_name'], $target)) {
			throw new RuntimeException('Berkas gagal disimpan.');
		}

		return $relative_dir . '/' . $name;
	}

	private function apply_filters(array $filters = [])
	{
		$this->db->from('member_registration_requests r');

		$q = trim((string) ($filters['q'] ?? ''));
		if ($q !== '') {
			$this->db->group_start()
				->like('r.registration_code', $q)
				->or_like('r.full_name', $q)
				->or_like('r.identity_number', $q)
				->or_like('r.phone', $q)
				->or_like('r.email', $q)
				->group_end();
		}

		$status = trim((string) ($filters['status'] ?? ''));
		if (in_array($status, ['pending', 'verified', 'rejected', 'cancelled'], true)) {
			$this->db->where('r.status', $status);
		}
	}

	private function next_registration_code()
	{
		$prefix = 'REG-' . date('Ymd') . '-';
		$row = $this->db
			->select('registration_code')
			->from('member_registration_requests')
			->like('registration_code', $prefix, 'after')
			->order_by('registration_code', 'DESC')
			->limit(1)
			->get()
			->row_array();
		$sequence = 1;
		if (! empty($row['registration_code']) && preg_match('/-(\d{4})$/', $row['registration_code'], $matches)) {
			$sequence = (int) $matches[1] + 1;
		}

		return $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
	}

	private function new_public_token()
	{
		return hash('sha256', uniqid('', true) . '-' . random_int(100000, 999999));
	}

	private function count_where(array $where)
	{
		if (! $this->db->table_exists('member_registration_requests')) {
			return 0;
		}

		if (! empty($where)) {
			$this->db->where($where);
		}

		return (int) $this->db->count_all_results('member_registration_requests');
	}

	private function blank_to_null($value)
	{
		$value = is_string($value) ? trim($value) : $value;
		return $value === '' ? null : $value;
	}

	private function clip($value, $limit)
	{
		$value = $this->blank_to_null($value);
		return $value === null ? null : substr((string) $value, 0, (int) $limit);
	}
}
