<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reader extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Reader_model');
	}

	public function assets()
	{
		$this->require_permission('reader.assets', 'view');
		$filters = [
			'q' => $this->input->get('q', true),
			'status' => $this->input->get('status', true),
			'access_policy' => $this->input->get('access_policy', true),
			'rights_basis' => $this->input->get('rights_basis', true),
		];
		$per_page = (int) $this->input->get('per_page', true);
		$per_page = in_array($per_page, [10, 25, 50, 100], true) ? $per_page : 25;
		$page = max(1, (int) $this->input->get('page', true));
		$total_rows = $this->Reader_model->count_assets($filters);
		$total_pages = max(1, (int) ceil($total_rows / $per_page));
		$page = min($page, $total_pages);
		$offset = ($page - 1) * $per_page;

		$this->render('reader/assets', [
			'title' => 'Reader PDF Aman',
			'stats' => $this->Reader_model->stats(),
			'assets' => $this->Reader_model->get_assets($per_page, $offset, $filters),
			'sessions' => $this->Reader_model->get_recent_sessions(),
			'filters' => [
				'q' => $filters['q'],
				'status' => $filters['status'],
				'access_policy' => $filters['access_policy'],
				'rights_basis' => $filters['rights_basis'],
				'per_page' => $per_page,
				'page' => $page,
			],
			'pagination' => [
				'total_rows' => $total_rows,
				'total_pages' => $total_pages,
				'page' => $page,
				'per_page' => $per_page,
				'offset' => $offset,
			],
			'logs' => $this->Reader_model->get_access_logs(12, ['event_type' => $this->input->get('event_type', true)]),
			'can_create_asset' => $this->can('reader.assets', 'create'),
			'can_edit_asset' => $this->can('reader.assets', 'edit'),
		]);
	}

	public function create()
	{
		$this->require_permission('reader.assets', 'create');
		$selected_book_id = (int) $this->input->get('book_id', true);
		$this->render('reader/form', [
			'title' => 'Tambah Ebook',
			'action' => 'reader/assets/store',
			'asset' => null,
			'selected_book_id' => $selected_book_id,
			'selected_book' => $selected_book_id > 0 ? $this->Reader_model->book_option($selected_book_id) : null,
			'books' => $this->Reader_model->book_options(),
		]);
	}

	public function store()
	{
		$this->require_permission('reader.assets', 'create');

		try {
			$upload = $this->handle_pdf_upload(true);
			$payload = array_merge($this->asset_input(), $upload);
			$id = $this->Reader_model->create_asset($payload, (int) ($this->current_user['id'] ?? 0));
			$this->audit_event('reader.asset_create', 'digital_assets', $id, null, $payload);
			$this->session->set_flashdata('success', 'Ebook berhasil ditambahkan.');
			redirect('reader/assets');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
			redirect('reader/assets/create');
		}
	}

	public function edit($id)
	{
		$this->require_permission('reader.assets', 'edit');
		$asset = $this->Reader_model->get_asset((int) $id);
		if (! $asset) {
			show_404();
			return;
		}

		$this->render('reader/form', [
			'title' => 'Edit Ebook',
			'action' => 'reader/assets/update/' . (int) $id,
			'asset' => $asset,
			'books' => $this->Reader_model->book_options(),
		]);
	}

	public function update($id)
	{
		$this->require_permission('reader.assets', 'edit');
		$before = $this->Reader_model->get_asset((int) $id);
		if (! $before) {
			show_404();
			return;
		}

		try {
			$upload = $this->handle_pdf_upload(false);
			$payload = array_merge($this->asset_input(), $upload);
			$this->Reader_model->update_asset((int) $id, $payload);
			$this->audit_event('reader.asset_update', 'digital_assets', (int) $id, $before, $payload);
			$this->session->set_flashdata('success', 'Ebook berhasil diperbarui.');
			redirect('reader/assets');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
			redirect('reader/assets/edit/' . (int) $id);
		}
	}

	public function status($id)
	{
		$this->require_permission('reader.assets', 'edit');
		$before = $this->Reader_model->get_asset((int) $id);
		if (! $before) {
			show_404();
			return;
		}

		$status = (string) $this->input->post('status', true);
		try {
			$this->Reader_model->change_asset_status((int) $id, $status);
			$this->audit_event('reader.asset_status', 'digital_assets', (int) $id, $before, ['status' => $status]);
			$this->session->set_flashdata('success', 'Status ebook berhasil diubah.');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
		}
		redirect('reader/assets');
	}

	public function audit()
	{
		$this->require_permission('reader.assets', 'view');
		$filters = [
			'event_type' => $this->input->get('event_type', true),
		];

		$this->render('reader/audit', [
			'title' => 'Audit Reader',
			'filters' => $filters,
			'logs' => $this->Reader_model->get_access_logs(150, $filters),
		]);
	}

	public function read($id)
	{
		$asset = $this->Reader_model->get_asset((int) $id);
		if (! $asset || $asset['status'] !== 'active') {
			show_404();
			return;
		}

		if ($this->can('reader.assets', 'view')) {
			$can_stream_pdf = $this->can_stream_raw_pdf($asset);
			$this->render('reader/read', [
				'title' => 'Baca PDF',
				'asset' => $asset,
				'reader_context' => ['origin' => 'admin', 'quota_charged' => 0],
				'stream_url' => $can_stream_pdf ? base_url('reader/stream/' . (int) $asset['id']) : null,
				'page_info_url' => $can_stream_pdf ? null : base_url('reader/admin-page-info/' . (int) $asset['id']),
				'page_url_base' => $can_stream_pdf ? null : base_url('reader/admin-page/' . (int) $asset['id']),
			]);
			return;
		}

		$this->load->model('Member_model');
		$member = $this->Member_model->get_member_by_auth_user_id((int) ($this->current_user['id'] ?? 0));
		if (! $member) {
			show_error('Akun belum terhubung ke data member.', 403, 'Akses Ditolak');
			return;
		}

		if ($asset['access_policy'] === 'internal') {
			show_error('Aset ini hanya untuk internal petugas.', 403, 'Akses Ditolak');
			return;
		}

		$context = [
			'origin' => 'external',
			'location_label' => null,
			'quota_charged' => 0,
			'quota_unit' => null,
		];

		if ($asset['access_policy'] === 'location_only') {
			$lat = $this->input->get_post('lat', true);
			$lng = $this->input->get_post('lng', true);
			$external = (int) $this->input->get_post('external', true) === 1;
			if (! $external && ($lat === null || $lat === '' || $lng === null || $lng === '')) {
				$this->load->view('reader/location_gate', [
					'title' => 'Validasi Lokasi Baca',
					'asset' => $asset,
				]);
				return;
			}

			$this->load->model('Reading_point_model');
			try {
				$context = $this->Reading_point_model->consume_reader_token((int) $member['id'], $lat, $lng, 1);
			} catch (Throwable $e) {
				$this->session->set_flashdata('error', $e->getMessage());
				redirect('user/reading-checkin');
				return;
			}
		}

		$session_id = $this->Reader_model->create_session_with_context($asset, $member, $context);
		$reader_session = $this->Reader_model->get_session($session_id);
		$can_stream_pdf = $this->can_stream_raw_pdf($asset);
		$stream_url = null;
		$page_info_url = null;
		$page_url_base = null;
		$page_query = null;
		if ($can_stream_pdf && $reader_session && ! empty($reader_session['secure_token'])) {
			$stream_url = base_url('reader/stream/' . (int) $asset['id']);
			$stream_url .= '?' . http_build_query([
				'session' => (int) $reader_session['id'],
				'token' => $reader_session['secure_token'],
			]);
		} elseif ($reader_session && ! empty($reader_session['secure_token'])) {
			$page_query = http_build_query([
				'session' => (int) $reader_session['id'],
				'token' => $reader_session['secure_token'],
			]);
			$page_info_url = base_url('reader/page-info/' . (int) $asset['id']) . '?' . $page_query;
			$page_url_base = base_url('reader/page/' . (int) $asset['id']);
		}

		$this->load->view('reader/member_read', [
			'title' => 'Baca PDF',
			'asset' => $asset,
			'member' => $member,
			'reader_context' => $context,
			'reader_session' => $reader_session,
			'stream_url' => $stream_url,
			'page_info_url' => $page_info_url,
			'page_url_base' => $page_url_base,
			'page_query' => $page_query,
			'can_stream_pdf' => $can_stream_pdf,
		]);
	}

	public function stream($id)
	{
		$asset = $this->Reader_model->get_asset((int) $id);
		if (! $asset || $asset['status'] !== 'active') {
			show_404();
			return;
		}

		if ($this->can('reader.assets', 'view')) {
			if (! $this->can_stream_raw_pdf($asset)) {
				$this->Reader_model->log_admin_event($asset, $this->current_user, 'blocked', null, [
					'reason' => 'admin_non_downloadable_raw_pdf_denied',
				]);
				show_error('File PDF ini tidak boleh dikirim utuh ke browser. Gunakan reader halaman aman.', 403, 'Akses Ditolak');
				return;
			}
		} else {
			$this->load->model('Member_model');
			$member = $this->Member_model->get_member_by_auth_user_id((int) ($this->current_user['id'] ?? 0));
			if (! $member || $asset['access_policy'] === 'internal') {
				show_error('Akses file ditolak.', 403, 'Akses Ditolak');
				return;
			}

			if (! $this->can_stream_raw_pdf($asset)) {
				$this->Reader_model->log_reader_block(
					$asset,
					$member,
					'non_downloadable_raw_pdf_denied',
					$this->input->get('session', true)
				);
				show_error('File PDF ini tidak boleh dikirim utuh ke browser. Gunakan reader halaman aman.', 403, 'Akses Ditolak');
				return;
			}

			$session = $this->Reader_model->secure_stream_context(
				$asset,
				$member,
				$this->input->get('session', true),
				$this->input->get('token', true)
			);
			if (! $session) {
				show_error('Sesi baca belum aktif. Buka buku melalui halaman reader terlebih dahulu.', 403, 'Akses Ditolak');
				return;
			}
		}

		$path = trim((string) ($asset['file_path'] ?? ''));
		if ($path === '') {
			show_404();
			return;
		}

		$base = realpath(FCPATH);
		$file = realpath(FCPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path));
		if (! $base || ! $file || strpos($file, $base) !== 0 || ! is_file($file)) {
			show_404();
			return;
		}

		$filename = preg_replace('/[^A-Za-z0-9._-]+/', '_', (string) ($asset['file_original_name'] ?: basename($file)));
		if (! headers_sent()) {
			header('Content-Type: application/pdf');
			header('Content-Length: ' . filesize($file));
			header('Content-Disposition: inline; filename="' . $filename . '"');
			header('X-Content-Type-Options: nosniff');
			header('Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0');
			header('Pragma: no-cache');
		}
		readfile($file);
		exit;
	}

	public function admin_page_info($id)
	{
		$payload = $this->admin_page_request_payload((int) $id);
		if (! $payload) {
			return;
		}

		$info = $this->renderer_info($payload['file']);
		if (! $info['ok']) {
			$this->output
				->set_status_header(503)
				->set_content_type('application/json')
				->set_output(json_encode(['ok' => false, 'message' => 'Renderer halaman PDF belum siap.']));
			return;
		}

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode(['ok' => true, 'pages' => (int) $info['pages']]));
	}

	public function admin_page($id, $page_number = 1)
	{
		$payload = $this->admin_page_request_payload((int) $id);
		if (! $payload) {
			return;
		}

		$page_number = max(1, (int) $page_number);
		$watermark = $this->admin_watermark($this->current_user, $page_number);
		$rendered = $this->render_pdf_page($payload['file'], (int) $payload['asset']['id'], 0, $page_number, $watermark);
		if (! $rendered || ! is_file($rendered)) {
			$this->Reader_model->log_admin_event($payload['asset'], $this->current_user, 'blocked', null, ['reason' => 'admin_page_renderer_failed']);
			show_error('Renderer halaman PDF belum siap. Pastikan PyMuPDF terpasang di server.', 503, 'Renderer Belum Siap');
			return;
		}
		$this->Reader_model->log_admin_event($payload['asset'], $this->current_user, 'page_rendered', $page_number, [
			'delivery' => 'admin_server_rendered_png',
		]);

		if (! headers_sent()) {
			header('Content-Type: image/png');
			header('Content-Length: ' . filesize($rendered));
			header('Content-Disposition: inline; filename="admin-reader-page-' . $page_number . '.png"');
			header('X-Content-Type-Options: nosniff');
			header('Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0');
			header('Pragma: no-cache');
		}
		readfile($rendered);
		exit;
	}

	public function page_info($id)
	{
		$payload = $this->member_page_request_payload((int) $id);
		if (! $payload) {
			return;
		}

		$info = $this->renderer_info($payload['file']);
		if (! $info['ok']) {
			$this->output
				->set_status_header(503)
				->set_content_type('application/json')
				->set_output(json_encode([
					'ok' => false,
					'message' => 'Renderer halaman PDF belum siap.',
				]));
			return;
		}

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'ok' => true,
				'pages' => (int) $info['pages'],
			]));
	}

	public function page($id, $page_number = 1)
	{
		$payload = $this->member_page_request_payload((int) $id);
		if (! $payload) {
			return;
		}

		$page_number = max(1, (int) $page_number);
		$session = $this->Reader_model->secure_page_context(
			$payload['asset'],
			$payload['member'],
			$this->input->get('session', true),
			$this->input->get('token', true),
			$page_number
		);
		if (! $session) {
			show_error('Sesi baca halaman tidak valid atau terlalu sering diminta.', 403, 'Akses Ditolak');
			return;
		}

		$watermark = $this->reader_watermark($payload['member'], $session, $page_number);
		$rendered = $this->render_pdf_page($payload['file'], (int) $payload['asset']['id'], (int) $session['id'], $page_number, $watermark);
		if (! $rendered || ! is_file($rendered)) {
			$this->Reader_model->log_reader_block($payload['asset'], $payload['member'], 'page_renderer_failed', (int) $session['id']);
			show_error('Renderer halaman PDF belum siap. Pastikan PyMuPDF terpasang di server.', 503, 'Renderer Belum Siap');
			return;
		}
		$this->Reader_model->record_rendered_page($session, $payload['asset'], $payload['member'], $page_number, [
			'delivery' => 'server_rendered_png',
		]);

		if (! headers_sent()) {
			header('Content-Type: image/png');
			header('Content-Length: ' . filesize($rendered));
			header('Content-Disposition: inline; filename="reader-page-' . $page_number . '.png"');
			header('X-Content-Type-Options: nosniff');
			header('Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0');
			header('Pragma: no-cache');
		}
		readfile($rendered);
		exit;
	}

	public function audit_page()
	{
		$this->load->model('Member_model');
		$user = (array) $this->session->userdata('auth_user');
		if (empty($user['id'])) {
			$this->output->set_status_header(401)->set_content_type('application/json')->set_output(json_encode(['ok' => false]));
			return;
		}

		$member = $this->Member_model->get_member_by_auth_user_id((int) $user['id']);
		if (! $member) {
			$this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode(['ok' => false]));
			return;
		}

		$ok = $this->Reader_model->log_reader_page(
			$this->input->post('session', true),
			$this->input->post('token', true),
			$this->input->post('page', true),
			$member
		);

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode(['ok' => (bool) $ok]));
	}

	private function can_stream_raw_pdf(array $asset)
	{
		return (int) ($asset['is_downloadable'] ?? 0) === 1
			&& ($asset['access_policy'] ?? '') === 'download_allowed';
	}

	private function asset_input()
	{
		return [
			'book_id' => $this->input->post('book_id', true),
			'access_policy' => $this->input->post('access_policy', true),
			'is_downloadable' => $this->input->post('is_downloadable', true),
			'status' => $this->input->post('status', true),
			'rights_basis' => $this->input->post('rights_basis', true),
			'rights_holder' => $this->input->post('rights_holder', true),
			'license_url' => $this->input->post('license_url', true),
			'permission_reference' => $this->input->post('permission_reference', true),
			'permission_starts_at' => $this->input->post('permission_starts_at', true),
			'permission_ends_at' => $this->input->post('permission_ends_at', true),
			'access_notes' => $this->input->post('access_notes', true),
			'source_system' => 'manual_upload',
			'migration_status' => 'copied',
		];
	}

	private function handle_pdf_upload($required = false)
	{
		if (empty($_FILES['pdf_file']) || empty($_FILES['pdf_file']['name'])) {
			if ($required) {
				throw new RuntimeException('File PDF wajib diunggah.');
			}
			return [];
		}

		if ((int) ($_FILES['pdf_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
			throw new RuntimeException('Upload PDF gagal. Periksa ukuran file dan konfigurasi PHP.');
		}

		$tmp = (string) $_FILES['pdf_file']['tmp_name'];
		$original = (string) $_FILES['pdf_file']['name'];
		$ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
		if ($ext !== 'pdf') {
			throw new RuntimeException('File harus berformat PDF.');
		}

		$handle = fopen($tmp, 'rb');
		$signature = $handle ? fread($handle, 4) : '';
		if ($handle) {
			fclose($handle);
		}
		if ($signature !== '%PDF') {
			throw new RuntimeException('File tidak dikenali sebagai PDF valid.');
		}

		$relative_dir = 'storage/ebooks/manual/' . date('Y/m');
		$absolute_dir = FCPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative_dir);
		if (! is_dir($absolute_dir) && ! mkdir($absolute_dir, 0775, true)) {
			throw new RuntimeException('Folder penyimpanan PDF tidak bisa dibuat.');
		}

		$safe_name = preg_replace('/[^A-Za-z0-9._-]+/', '-', pathinfo($original, PATHINFO_FILENAME));
		$safe_name = trim($safe_name, '-_') ?: 'ebook';
		$filename = $safe_name . '-' . date('His') . '-' . bin2hex(random_bytes(4)) . '.pdf';
		$target = $absolute_dir . DIRECTORY_SEPARATOR . $filename;
		if (! move_uploaded_file($tmp, $target)) {
			throw new RuntimeException('File PDF gagal disimpan ke storage.');
		}

		return [
			'file_original_name' => $original,
			'file_path' => $relative_dir . '/' . $filename,
			'mime_type' => 'application/pdf',
			'file_size' => filesize($target),
			'source_path' => $relative_dir . '/' . $filename,
			'migrated_at' => date('Y-m-d H:i:s'),
		];
	}

	private function member_page_request_payload($asset_id)
	{
		$asset = $this->Reader_model->get_asset((int) $asset_id);
		if (! $asset || $asset['status'] !== 'active') {
			show_404();
			return null;
		}

		if ($asset['access_policy'] === 'internal' || $this->can_stream_raw_pdf($asset)) {
			show_error('Endpoint halaman aman hanya untuk aset non-downloadable.', 403, 'Akses Ditolak');
			return null;
		}

		$this->load->model('Member_model');
		$member = $this->Member_model->get_member_by_auth_user_id((int) ($this->current_user['id'] ?? 0));
		if (! $member) {
			show_error('Akun belum terhubung ke data member.', 403, 'Akses Ditolak');
			return null;
		}

		$session = $this->Reader_model->secure_session_context(
			$asset,
			$member,
			$this->input->get('session', true),
			$this->input->get('token', true),
			'page_info'
		);
		if (! $session) {
			show_error('Sesi baca halaman tidak valid.', 403, 'Akses Ditolak');
			return null;
		}

		$file = $this->asset_pdf_path($asset);
		if (! $file) {
			show_404();
			return null;
		}

		return [
			'asset' => $asset,
			'member' => $member,
			'session' => $session,
			'file' => $file,
		];
	}

	private function admin_page_request_payload($asset_id)
	{
		$this->require_permission('reader.assets', 'view');
		$asset = $this->Reader_model->get_asset((int) $asset_id);
		if (! $asset) {
			show_404();
			return null;
		}

		if ($this->can_stream_raw_pdf($asset)) {
			show_error('Endpoint halaman admin hanya untuk aset non-downloadable.', 403, 'Akses Ditolak');
			return null;
		}

		$file = $this->asset_pdf_path($asset);
		if (! $file) {
			show_404();
			return null;
		}

		return [
			'asset' => $asset,
			'file' => $file,
		];
	}

	private function asset_pdf_path(array $asset)
	{
		$path = trim((string) ($asset['file_path'] ?? ''));
		if ($path === '') {
			return null;
		}

		$base = realpath(FCPATH);
		$file = realpath(FCPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path));
		if (! $base || ! $file || strpos($file, $base) !== 0 || ! is_file($file)) {
			return null;
		}

		return $file;
	}

	private function renderer_info($pdf_file)
	{
		$result = $this->run_renderer([
			'info',
			'--input', $pdf_file,
		]);

		if (! $result['ok'] || empty($result['json']['ok'])) {
			return ['ok' => false, 'pages' => 0];
		}

		return ['ok' => true, 'pages' => (int) ($result['json']['pages'] ?? 0)];
	}

	private function render_pdf_page($pdf_file, $asset_id, $session_id, $page_number, $watermark)
	{
		$cache_dir = FCPATH . 'storage' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'reader_pages';
		if (! is_dir($cache_dir)) {
			@mkdir($cache_dir, 0775, true);
		}

		$key = hash('sha256', implode('|', [
			$pdf_file,
			(int) $asset_id,
			(int) $session_id,
			(int) $page_number,
			$watermark,
			filemtime($pdf_file),
		]));
		$output = $cache_dir . DIRECTORY_SEPARATOR . $key . '.png';
		if (is_file($output)) {
			return $output;
		}

		$result = $this->run_renderer([
			'render',
			'--input', $pdf_file,
			'--output', $output,
			'--page', (string) $page_number,
			'--watermark', $this->ascii_watermark($watermark),
			'--zoom', '1.65',
		]);

		return ($result['ok'] && is_file($output)) ? $output : null;
	}

	private function run_renderer(array $args)
	{
		$script = FCPATH . 'scripts' . DIRECTORY_SEPARATOR . 'render_pdf_page.py';
		if (! is_file($script)) {
			return ['ok' => false, 'json' => null, 'stderr' => 'renderer_script_missing'];
		}

		$python = getenv('PUSTAKA_PYTHON') ?: 'python';
		$command = escapeshellcmd($python) . ' ' . escapeshellarg($script);
		foreach ($args as $arg) {
			$command .= ' ' . escapeshellarg($arg);
		}

		$descriptors = [
			1 => ['pipe', 'w'],
			2 => ['pipe', 'w'],
		];
		$process = proc_open($command, $descriptors, $pipes, FCPATH);
		if (! is_resource($process)) {
			return ['ok' => false, 'json' => null, 'stderr' => 'proc_open_failed'];
		}

		$stdout = stream_get_contents($pipes[1]);
		$stderr = stream_get_contents($pipes[2]);
		fclose($pipes[1]);
		fclose($pipes[2]);
		$exit_code = proc_close($process);
		$json = json_decode(trim($stdout), true);

		return [
			'ok' => $exit_code === 0 && is_array($json) && ! empty($json['ok']),
			'json' => is_array($json) ? $json : null,
			'stderr' => $stderr,
		];
	}

	private function reader_watermark(array $member, array $session, $page_number)
	{
		return trim(implode(' | ', [
			'Pustaka Digital Rembang',
			(string) ($member['full_name'] ?? 'Member'),
			(string) ($member['member_no'] ?? '-'),
			'Sesi ' . (int) ($session['id'] ?? 0),
			'Hal ' . max(1, (int) $page_number),
			date('Y-m-d H:i:s'),
		]));
	}

	private function admin_watermark(array $user, $page_number)
	{
		return trim(implode(' | ', [
			'Pustaka Digital Rembang',
			'Preview Admin',
			(string) ($user['full_name'] ?? $user['username'] ?? 'Admin'),
			'Hal ' . max(1, (int) $page_number),
			date('Y-m-d H:i:s'),
		]));
	}

	private function ascii_watermark($text)
	{
		$text = preg_replace('/[^\x20-\x7E]/', ' ', (string) $text);
		$text = preg_replace('/\s+/', ' ', $text);
		return trim($text);
	}
}
