<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
	protected $current_user = [];
	protected $user_roles = [];
	protected $user_perms = [];
	protected $library_scope_id = null;

	public function __construct()
	{
		parent::__construct();
		$this->ensure_authenticated();
	}

	protected function ensure_authenticated()
	{
		$user = $this->session->userdata('auth_user');
		if (empty($user)) {
			if ($this->input->is_ajax_request()) {
				$this->json_response(['ok' => false, 'message' => 'Sesi login sudah habis.'], 401);
				$this->output->_display();
				exit;
				return;
			}

			$this->session->set_flashdata('redirect_after_login', uri_string());
			redirect('login');
		}

		$this->current_user = $user;
		$this->user_roles = (array) $this->session->userdata('user_roles');
		$this->user_perms = (array) $this->session->userdata('user_perms');
		$this->library_scope_id = $this->resolve_library_scope_id();
	}

	protected function is_superadmin()
	{
		return ! empty($this->current_user['is_superadmin']);
	}

	protected function can($page_code, $action = 'view')
	{
		if ($this->is_superadmin()) {
			return true;
		}

		return ! empty($this->user_perms[$page_code]['can_' . $action]);
	}

	protected function require_permission($page_code, $action = 'view')
	{
		if ($this->can($page_code, $action)) {
			return;
		}

		$this->audit_event('permission.denied', 'sys_page', $page_code, null, [
			'action' => $action,
			'uri' => uri_string(),
		]);

		if ($this->input->is_ajax_request()) {
			$this->json_response([
				'ok' => false,
				'message' => 'Anda tidak memiliki izin untuk aksi ini.',
			], 403);
			$this->output->_display();
			exit;
			return;
		}

		show_error('Anda tidak memiliki izin untuk mengakses halaman ini.', 403, 'Akses Ditolak');
	}

	protected function render($view, array $data = [], $return = false)
	{
		$this->load->model('Menu_model');

		$data['current_user'] = $this->current_user;
		$data['user_roles'] = $this->user_roles;
		$data['user_perms'] = $this->user_perms;
		$data['library_scope_id'] = $this->library_scope_id;
		$data['menu_items'] = $this->Menu_model->get_sidebar_tree($this->user_perms, $this->is_superadmin());
		$data['content'] = $this->load->view($view, $data, true);

		return $this->load->view('layouts/tabler', $data, $return);
	}

	protected function current_library_scope_id()
	{
		return $this->library_scope_id;
	}

	protected function audit_event($event_code, $entity_type = null, $entity_id = null, array $old_data = null, array $new_data = null)
	{
		$this->load->model('Audit_model');

		return $this->Audit_model->write(
			$event_code,
			$entity_type,
			$entity_id,
			$old_data,
			$new_data,
			(int) ($this->current_user['id'] ?? 0) ?: null
		);
	}

	protected function json_response(array $payload, $status_code = 200)
	{
		$this->output
			->set_status_header($status_code)
			->set_content_type('application/json')
			->set_output(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE));
	}

	private function resolve_library_scope_id()
	{
		if ($this->is_superadmin()) {
			return null;
		}

		$library_id = (int) ($this->current_user['library_id'] ?? 0);
		return $library_id > 0 ? $library_id : null;
	}
}
