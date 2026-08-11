<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Member_registration_model extends CI_Model
{
	const MAX_UPLOAD_BYTES = 2097152;
	const MAX_IMAGE_PIXELS_FOR_COMPRESSION = 16000000;

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

	public function find_request_for_public_status($lookup_type, $value)
	{
		$lookup_type = strtolower(trim((string) $lookup_type));
		$value = trim((string) $value);
		if ($value === '') {
			throw new RuntimeException('Masukkan data pendaftaran yang ingin digunakan untuk pencarian.');
		}

		if ($lookup_type === 'token') {
			if (preg_match('~membership/register/pending/([a-f0-9]{64})~i', $value, $matches)) {
				$value = $matches[1];
			}
			if (! preg_match('/^[a-f0-9]{64}$/i', $value)) {
				throw new RuntimeException('Tautan atau token status tidak valid. Salin tautan status lengkap yang diterima setelah pendaftaran.');
			}
			$request = $this->get_request_by_public_token($value);
		} elseif ($lookup_type === 'nik') {
			$nik = preg_replace('/\D+/', '', $value);
			if (strlen($nik) !== 16) {
				throw new RuntimeException('NIK harus terdiri dari tepat 16 digit.');
			}
			$request = $this->get_request_by_identity_number($nik);
		} elseif ($lookup_type === 'registration_code') {
			$code = strtoupper(trim($value));
			if (! preg_match('/^REG-\d{8}-\d{4}$/', $code)) {
				throw new RuntimeException('Format kode pendaftaran tidak valid. Contoh: REG-20260811-0001.');
			}
			$request = $this->get_request_by_code($code);
		} elseif ($lookup_type === 'phone') {
			$phone = $this->normalize_phone($value);
			if (strlen($phone) < 10 || strlen($phone) > 15) {
				throw new RuntimeException('Nomor HP tidak valid. Gunakan nomor yang dicantumkan saat pendaftaran.');
			}
			$matches = [];
			foreach ($this->db->from('member_registration_requests')->where('phone IS NOT NULL', null, false)->get()->result_array() as $row) {
				if ($this->normalize_phone($row['phone'] ?? '') === $phone) {
					$matches[] = $row;
				}
			}
			if (count($matches) > 1) {
				throw new RuntimeException('Nomor HP ini digunakan lebih dari satu pendaftaran. Gunakan NIK, kode pendaftaran, atau token status.');
			}
			$request = $matches[0] ?? null;
		} else {
			throw new RuntimeException('Pilih metode pencarian status yang tersedia.');
		}

		if (! $request) {
			throw new RuntimeException('Pendaftaran tidak ditemukan. Periksa kembali data yang dimasukkan atau hubungi petugas perpustakaan.');
		}

		return $request;
	}

	public function create_request(array $data, array $files)
	{
		$identity_number = preg_replace('/\D+/', '', (string) ($data['identity_number'] ?? ''));
		$full_name = trim((string) ($data['full_name'] ?? ''));
		if ($full_name === '') {
			throw new RuntimeException('Nama lengkap wajib diisi.');
		}
		if (strlen($identity_number) !== 16) {
			throw new RuntimeException('NIK wajib terdiri dari tepat 16 digit.');
		}
		$this->ensure_nik_is_available($identity_number);

		$identity_document_type = (string) ($data['identity_document_type'] ?? '');
		if (! in_array($identity_document_type, ['ktp', 'kk'], true)) {
			throw new RuntimeException('Pilih salah satu dokumen identitas: KTP atau Kartu Keluarga.');
		}
		$identity_domicile = (string) ($data['identity_domicile'] ?? '');
		if (! in_array($identity_domicile, ['rembang', 'outside_rembang'], true)) {
			throw new RuntimeException('Pilih status domisili pada dokumen identitas.');
		}

		$is_rembang = $identity_domicile === 'rembang';
		$addresses = $this->resolve_addresses($data, $is_rembang);
		$existing_request = $this->get_request_by_identity_number($identity_number);
		if ($existing_request && ! in_array($existing_request['status'], ['rejected', 'cancelled'], true)) {
			if ($existing_request['status'] === 'pending') {
				throw new RuntimeException('NIK ini sudah memiliki pendaftaran yang sedang menunggu verifikasi.');
			}
			throw new RuntimeException('NIK ini sudah terdaftar sebagai member. Gunakan akun yang sudah ada atau hubungi petugas perpustakaan.');
		}

		$paths = $this->upload_required_files($files, $identity_document_type, $is_rembang);

		$payload = [
			'registration_code' => $this->next_registration_code(),
			'public_token' => $this->new_public_token(),
			'full_name' => $this->clip($full_name, 180),
			'identity_number' => $this->clip($identity_number, 80),
			'birth_place' => $this->blank_to_null($data['birth_place'] ?? null),
			'birth_date' => $this->blank_to_null($data['birth_date'] ?? null),
			'gender' => $this->blank_to_null($data['gender'] ?? null),
			'address' => $addresses['domicile']['address'],
			'province_id' => $addresses['domicile']['province_id'],
			'regency_id' => $addresses['domicile']['regency_id'],
			'district_id' => $addresses['domicile']['district_id'],
			'village_id' => $addresses['domicile']['village_id'],
			'district' => $addresses['domicile']['district'],
			'village' => $addresses['domicile']['village'],
			'province' => $addresses['domicile']['province'],
			'regency' => $addresses['domicile']['regency'],
			'identity_address' => $addresses['identity']['address'],
			'identity_province_id' => $addresses['identity']['province_id'],
			'identity_regency_id' => $addresses['identity']['regency_id'],
			'identity_district_id' => $addresses['identity']['district_id'],
			'identity_village_id' => $addresses['identity']['village_id'],
			'identity_province' => $addresses['identity']['province'],
			'identity_regency' => $addresses['identity']['regency'],
			'identity_district' => $addresses['identity']['district'],
			'identity_village' => $addresses['identity']['village'],
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

		if ($existing_request) {
			// NIK tetap satu rekam jejak. Pengajuan lama yang ditolak/dibatalkan dibuka kembali.
			$payload['public_token'] = $this->new_public_token();
			$payload['admin_note'] = null;
			$payload['verified_by'] = null;
			$payload['verified_at'] = null;
			$payload['member_id'] = null;
			$this->db->where('id', (int) $existing_request['id'])->update('member_registration_requests', $payload);
			return [
				'id' => (int) $existing_request['id'],
				'code' => $existing_request['registration_code'],
				'token' => $payload['public_token'],
			];
		}

		if (! $this->db->insert('member_registration_requests', $payload)) {
			$error = $this->db->error();
			if ((int) ($error['code'] ?? 0) === 1062) {
				throw new RuntimeException('NIK ini sudah digunakan pada data pendaftaran.');
			}
			throw new RuntimeException('Pendaftaran gagal disimpan. Silakan coba lagi.');
		}
		return [
			'id' => (int) $this->db->insert_id(),
			'code' => $payload['registration_code'],
			'token' => $payload['public_token'],
		];
	}

	private function ensure_nik_is_available($identity_number)
	{
		$member_exists = $this->db
			->from('members')
			->where('identity_number', $identity_number)
			->count_all_results() > 0;
		if ($member_exists) {
			throw new RuntimeException('NIK ini sudah terdaftar sebagai member. Gunakan akun yang sudah ada atau hubungi petugas perpustakaan.');
		}
	}

	private function get_request_by_identity_number($identity_number)
	{
		return $this->db
			->from('member_registration_requests')
			->where('identity_number', $identity_number)
			->order_by('id', 'DESC')
			->limit(1)
			->get()
			->row_array();
	}

	private function normalize_phone($phone)
	{
		$phone = preg_replace('/\D+/', '', (string) $phone);
		if (strpos($phone, '62') === 0) {
			$phone = '0' . substr($phone, 2);
		} elseif (strpos($phone, '8') === 0) {
			$phone = '0' . $phone;
		}
		return $phone;
	}

	private function resolve_addresses(array $data, $is_rembang)
	{
		if ($is_rembang) {
			$address = $this->resolve_address((int) ($data['district_id'] ?? 0), (int) ($data['village_id'] ?? 0), $data['address'] ?? null, 'Alamat Rembang');
			if ($address['regency_code'] !== Region_model::REMBANG_REGENCY_CODE) throw new RuntimeException('Pilih kecamatan dan desa/kelurahan di Kabupaten Rembang.');
			return ['identity' => $address, 'domicile' => $address];
		}

		return [
			'identity' => $this->resolve_address((int) ($data['identity_district_id'] ?? 0), (int) ($data['identity_village_id'] ?? 0), $data['identity_address'] ?? null, 'Alamat sesuai KTP/KK'),
			'domicile' => $this->resolve_address((int) ($data['domicile_district_id'] ?? 0), (int) ($data['domicile_village_id'] ?? 0), $data['domicile_address'] ?? null, 'Alamat domisili'),
		];
	}

	private function resolve_address($district_id, $village_id, $address, $label)
	{
		$district = $district_id > 0 ? $this->Region_model->get_district($district_id) : null;
		$village = $village_id > 0 ? $this->Region_model->get_village($village_id) : null;
		if (! $district || ! $village || (int) $district['is_active'] !== 1 || (int) $village['is_active'] !== 1 || (int) $village['district_id'] !== $district_id || (int) $village['regency_id'] !== (int) $district['regency_id']) throw new RuntimeException($label . ' belum lengkap atau tidak valid.');
		return ['address' => $this->blank_to_null($address), 'province_id' => (int) $district['province_id'], 'regency_id' => (int) $district['regency_id'], 'district_id' => (int) $district['id'], 'village_id' => (int) $village['id'], 'province' => $this->clip($district['province_name'], 120), 'regency' => $this->clip($district['regency_name'], 160), 'district' => $this->clip($district['name'], 120), 'village' => $this->clip($village['name'], 120), 'regency_code' => (string) $district['regency_code']];
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
			throw new RuntimeException($this->upload_error_message($label, (int) $files[$field]['error']));
		}
		if (empty($files[$field]['tmp_name']) || ! is_uploaded_file($files[$field]['tmp_name'])) {
			throw new RuntimeException('Berkas ' . $label . ' tidak valid.');
		}

		$extension = strtolower(pathinfo($files[$field]['name'], PATHINFO_EXTENSION));
		if (! in_array($extension, $allowed_extensions, true)) {
			throw new RuntimeException('Format berkas ' . $label . ' tidak sesuai.');
		}
		$source = $files[$field]['tmp_name'];
		$staged_file = null;
		try {
			if ((int) $files[$field]['size'] > self::MAX_UPLOAD_BYTES) {
				$compressed = $this->compress_oversized_file($source, $extension, $label, (int) $files[$field]['size']);
				$source = $compressed['path'];
				$extension = $compressed['extension'];
				$staged_file = $source;
			}

			$directory = $this->registration_upload_directory();
			$name = $field . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
			$target = $directory['absolute'] . '/' . $name;
			$saved = $staged_file !== null
				? (@rename($source, $target) || @copy($source, $target))
				: @move_uploaded_file($source, $target);
			if (! $saved) {
				throw new RuntimeException('Berkas ' . $label . ' tidak dapat disimpan. Server tidak memiliki izin menulis ke folder unggahan.');
			}

			return $directory['relative'] . '/' . $name;
		} finally {
			if ($staged_file !== null && is_file($staged_file)) {
				@unlink($staged_file);
			}
		}
	}

	private function registration_upload_directory()
	{
		$upload_base = FCPATH . 'assets/uploads';
		if (! is_dir($upload_base) && ! @mkdir($upload_base, 0775, true)) {
			throw new RuntimeException('Folder unggahan server belum tersedia. Mohon admin memeriksa folder assets/uploads.');
		}
		if (! is_writable($upload_base)) {
			throw new RuntimeException('Server belum memiliki izin menulis ke folder unggahan. Mohon admin memperbaiki izin folder assets/uploads.');
		}

		$relative = 'assets/uploads/member_registrations/' . date('Y/m');
		$absolute = FCPATH . $relative;
		if (! is_dir($absolute) && ! @mkdir($absolute, 0775, true)) {
			throw new RuntimeException('Folder unggahan pendaftaran tidak dapat dibuat. Mohon admin memeriksa izin folder assets/uploads.');
		}
		if (! is_writable($absolute)) {
			throw new RuntimeException('Server belum memiliki izin menulis ke folder unggahan pendaftaran. Mohon admin memperbaiki izin folder assets/uploads.');
		}

		return ['relative' => $relative, 'absolute' => $absolute];
	}

	private function compress_oversized_file($source, $extension, $label, $original_size)
	{
		if (in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
			return $this->compress_image($source, $extension, $label, $original_size);
		}
		if ($extension === 'pdf') {
			return $this->compress_pdf($source, $label, $original_size);
		}

		throw new RuntimeException('Ukuran berkas ' . $label . ' melebihi 2 MB dan formatnya tidak dapat dikompresi otomatis.');
	}

	private function compress_image($source, $extension, $label, $original_size)
	{
		$info = @getimagesize($source);
		if (! is_array($info) || empty($info[0]) || empty($info[1])) {
			throw new RuntimeException('Berkas ' . $label . ' bukan gambar yang valid sehingga tidak dapat dikompresi.');
		}
		if ((int) $info[0] * (int) $info[1] > self::MAX_IMAGE_PIXELS_FOR_COMPRESSION) {
			throw new RuntimeException('Berkas ' . $label . ' berukuran ' . $this->format_file_size($original_size) . ' dan resolusinya terlalu besar untuk dikompresi aman oleh server. Perkecil resolusi gambar lalu unggah kembali (maksimal 2 MB).');
		}

		$image = $extension === 'png' ? @imagecreatefrompng($source) : @imagecreatefromjpeg($source);
		if (! $image) {
			throw new RuntimeException('Berkas ' . $label . ' tidak dapat dibaca untuk dikompresi. Pastikan gambar tidak rusak.');
		}

		$temp = tempnam(sys_get_temp_dir(), 'member-image-');
		$output = $temp ? $temp . '.jpg' : false;
		if ($temp) @unlink($temp);
		if (! $output) {
			imagedestroy($image);
			throw new RuntimeException('Server tidak dapat menyiapkan proses kompresi untuk berkas ' . $label . '.');
		}

		try {
			$source_width = imagesx($image);
			$source_height = imagesy($image);
			foreach ([2200, 1800, 1400, 1100] as $max_dimension) {
				$scale = min(1, $max_dimension / max($source_width, $source_height));
				$width = max(1, (int) round($source_width * $scale));
				$height = max(1, (int) round($source_height * $scale));
				$canvas = imagecreatetruecolor($width, $height);
				imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
				imagecopyresampled($canvas, $image, 0, 0, 0, 0, $width, $height, $source_width, $source_height);
				foreach ([82, 75, 68] as $quality) {
					@imagejpeg($canvas, $output, $quality);
					if (is_file($output) && (int) filesize($output) <= self::MAX_UPLOAD_BYTES) {
						imagedestroy($canvas);
						return ['path' => $output, 'extension' => 'jpg'];
					}
				}
				imagedestroy($canvas);
			}
		} finally {
			imagedestroy($image);
		}

		$compressed_size = is_file($output) ? (int) filesize($output) : 0;
		@unlink($output);
		throw new RuntimeException('Berkas ' . $label . ' berukuran ' . $this->format_file_size($original_size) . '. Kompresi otomatis sudah dicoba, tetapi hasilnya masih ' . $this->format_file_size($compressed_size) . '. Unggah berkas maksimal 2 MB.');
	}

	private function compress_pdf($source, $label, $original_size)
	{
		$ghostscript = '/usr/bin/gs';
		if (! is_executable($ghostscript)) {
			throw new RuntimeException('Berkas PDF ' . $label . ' berukuran ' . $this->format_file_size($original_size) . ' dan server belum memiliki layanan kompresi PDF. Unggah berkas maksimal 2 MB.');
		}

		$temp = tempnam(sys_get_temp_dir(), 'member-pdf-');
		$output = $temp ? $temp . '.pdf' : false;
		if ($temp) @unlink($temp);
		if (! $output) {
			throw new RuntimeException('Server tidak dapat menyiapkan proses kompresi untuk PDF ' . $label . '.');
		}

		$last_size = 0;
		foreach (['/ebook', '/screen'] as $profile) {
			@unlink($output);
			$command = escapeshellarg($ghostscript)
				. ' -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH'
				. ' -dPDFSETTINGS=' . escapeshellarg($profile)
				. ' ' . escapeshellarg('-sOutputFile=' . $output)
				. ' ' . escapeshellarg($source) . ' 2>&1';
			$output_lines = [];
			$status = 1;
			@exec($command, $output_lines, $status);
			if ($status === 0 && is_file($output)) {
				$last_size = (int) filesize($output);
				if ($last_size <= self::MAX_UPLOAD_BYTES) {
					return ['path' => $output, 'extension' => 'pdf'];
				}
			}
		}

		@unlink($output);
		if ($last_size > 0) {
			throw new RuntimeException('Berkas PDF ' . $label . ' berukuran ' . $this->format_file_size($original_size) . '. Kompresi otomatis sudah dicoba, tetapi hasilnya masih ' . $this->format_file_size($last_size) . '. Unggah berkas maksimal 2 MB.');
		}
		throw new RuntimeException('PDF ' . $label . ' tidak dapat dikompresi. Pastikan PDF tidak rusak atau terkunci, lalu unggah berkas maksimal 2 MB.');
	}

	private function upload_error_message($label, $error)
	{
		$messages = [
			UPLOAD_ERR_INI_SIZE => 'Upload berkas ' . $label . ' ditolak sebelum diproses karena melebihi batas ukuran server. Perkecil berkas lalu coba lagi.',
			UPLOAD_ERR_FORM_SIZE => 'Upload berkas ' . $label . ' melebihi batas yang diizinkan formulir.',
			UPLOAD_ERR_PARTIAL => 'Upload berkas ' . $label . ' hanya terkirim sebagian. Periksa koneksi lalu coba lagi.',
			UPLOAD_ERR_NO_FILE => 'Berkas ' . $label . ' belum dipilih.',
			UPLOAD_ERR_NO_TMP_DIR => 'Upload berkas ' . $label . ' gagal karena folder sementara server tidak tersedia. Mohon hubungi admin.',
			UPLOAD_ERR_CANT_WRITE => 'Upload berkas ' . $label . ' gagal karena server tidak dapat menulis berkas. Mohon hubungi admin.',
			UPLOAD_ERR_EXTENSION => 'Upload berkas ' . $label . ' dihentikan oleh konfigurasi server. Mohon hubungi admin.',
		];

		return $messages[$error] ?? 'Upload berkas ' . $label . ' gagal (kode ' . $error . '). Silakan coba lagi.';
	}

	private function format_file_size($bytes)
	{
		return number_format(max(0, (int) $bytes) / 1048576, 1, ',', '.') . ' MB';
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
