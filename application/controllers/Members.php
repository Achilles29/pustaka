<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Members extends MY_Controller
{
	const DEFAULT_IMPORTED_PASSWORD = 'perpus2026';

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Member_model');
	}

	public function index()
	{
		$this->require_permission('members.index', 'view');

		$filters = [
			'q' => $this->input->get('q', true),
			'status' => $this->input->get('status', true),
			'user_status' => $this->input->get('user_status', true),
		];
		$per_page = (int) $this->input->get('per_page', true);
		$per_page = in_array($per_page, [10, 25, 50, 100], true) ? $per_page : 25;
		$page = max(1, (int) $this->input->get('page', true));
		$total_rows = $this->Member_model->count_members($filters);
		$total_pages = max(1, (int) ceil($total_rows / $per_page));
		$page = min($page, $total_pages);
		$offset = ($page - 1) * $per_page;

		$this->render('members/index', [
			'title' => 'Membership Digital',
			'stats' => $this->Member_model->stats(),
			'source_stats' => $this->Member_model->source_stats(),
			'members' => $this->Member_model->get_members($filters, $per_page, $offset),
			'sync_runs' => $this->Member_model->recent_sync_runs(),
			'filters' => [
				'q' => $filters['q'],
				'status' => $filters['status'],
				'user_status' => $filters['user_status'],
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
		]);
	}

	public function detail($id)
	{
		$this->require_permission('members.index', 'view');

		$member = $this->Member_model->get_member((int) $id);
		if (! $member) {
			show_404();
			return;
		}

		$this->render('members/detail', [
			'title' => 'Detail Member',
			'member' => $member,
			'visits' => $this->Member_model->get_member_visits((int) $id),
			'loans' => $this->Member_model->get_member_loans((int) $id),
			'access_rules' => $this->Member_model->get_member_access_rules((int) $id),
			'default_password' => self::DEFAULT_IMPORTED_PASSWORD,
		]);
	}

	public function create()
	{
		$this->require_permission('members.index', 'create');

		$this->render('members/form', [
			'title' => 'Tambah Member',
			'action' => 'members/store',
			'member' => null,
			'generated_member_no' => $this->Member_model->next_manual_member_no(),
			'default_password' => self::DEFAULT_IMPORTED_PASSWORD,
			'form_options' => $this->Member_model->form_options(),
		]);
	}

	public function store()
	{
		$this->require_permission('members.index', 'create');

		try {
			$member_id = $this->Member_model->create_member($this->member_input(), self::DEFAULT_IMPORTED_PASSWORD);
			$this->audit_event('members.create', 'members', $member_id, null, $this->member_input());
			$this->session->set_flashdata('success', 'Member baru berhasil disimpan.');
			redirect('members/detail/' . $member_id);
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
			redirect('members/create');
		}
	}

	public function edit($id)
	{
		$this->require_permission('members.index', 'edit');

		$member = $this->Member_model->get_member((int) $id);
		if (! $member) {
			show_404();
			return;
		}

		$this->render('members/form', [
			'title' => 'Edit Member',
			'action' => 'members/update/' . (int) $id,
			'member' => $member,
			'default_password' => self::DEFAULT_IMPORTED_PASSWORD,
			'form_options' => $this->Member_model->form_options(),
		]);
	}

	public function update($id)
	{
		$this->require_permission('members.index', 'edit');

		$before = $this->Member_model->get_member((int) $id);
		if (! $before) {
			show_404();
			return;
		}

		try {
			$this->Member_model->update_member((int) $id, $this->member_input(), self::DEFAULT_IMPORTED_PASSWORD);
			$this->audit_event('members.update', 'members', (int) $id, $before, $this->member_input());
			$this->session->set_flashdata('success', 'Member berhasil diperbarui.');
			redirect('members/detail/' . (int) $id);
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
			redirect('members/edit/' . (int) $id);
		}
	}

	public function delete($id)
	{
		$this->require_permission('members.index', 'delete');

		$before = $this->Member_model->get_member((int) $id);
		if (! $before) {
			show_404();
			return;
		}

		$this->Member_model->soft_delete_member((int) $id);
		$this->audit_event('members.delete', 'members', (int) $id, $before, ['deleted_at' => date('Y-m-d H:i:s')]);
		$this->session->set_flashdata('success', 'Member dinonaktifkan dari data aktif.');
		redirect('members');
	}

	public function update_card($id)
	{
		$this->require_permission('members.index', 'edit');

		$status = (string) $this->input->post('card_status', true);
		$reason = $this->input->post('card_block_reason', true);
		$this->Member_model->update_card_status((int) $id, $status, $reason, (int) ($this->current_user['id'] ?? 0));
		$this->audit_event('members.card_status', 'members', (int) $id, null, [
			'card_status' => $status,
			'card_block_reason' => $reason,
		]);
		$this->session->set_flashdata('success', $status === 'blocked' ? 'Kartu digital member diblokir.' : 'Kartu digital member diaktifkan kembali.');
		redirect('members/detail/' . (int) $id);
	}

	public function renewals()
	{
		$this->require_permission('members.renewals', 'view');

		$filters = [
			'q' => $this->input->get('q', true),
			'status' => $this->input->get('status', true),
		];
		$per_page = (int) $this->input->get('per_page', true);
		$per_page = in_array($per_page, [10, 25, 50, 100], true) ? $per_page : 25;
		$page = max(1, (int) $this->input->get('page', true));
		$total_rows = $this->Member_model->count_renewal_requests($filters);
		$total_pages = max(1, (int) ceil($total_rows / $per_page));
		$page = min($page, $total_pages);
		$offset = ($page - 1) * $per_page;

		$this->render('members/renewals', [
			'title' => 'Perpanjangan Membership',
			'requests' => $this->Member_model->get_renewal_requests($filters, $per_page, $offset),
			'filters' => [
				'q' => $filters['q'],
				'status' => $filters['status'],
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
		]);
	}

	public function registrations()
	{
		$this->require_permission('members.registrations', 'view');
		$this->load->model('Member_registration_model');

		$filters = [
			'q' => $this->input->get('q', true),
			'status' => $this->input->get('status', true),
		];
		$per_page = (int) $this->input->get('per_page', true);
		$per_page = in_array($per_page, [10, 25, 50, 100], true) ? $per_page : 25;
		$page = max(1, (int) $this->input->get('page', true));
		$total_rows = $this->Member_registration_model->count_requests($filters);
		$total_pages = max(1, (int) ceil($total_rows / $per_page));
		$page = min($page, $total_pages);
		$offset = ($page - 1) * $per_page;

		$this->render('members/registrations', [
			'title' => 'Pendaftaran Online',
			'stats' => $this->Member_registration_model->stats(),
			'requests' => $this->Member_registration_model->get_requests($filters, $per_page, $offset),
			'filters' => [
				'q' => $filters['q'],
				'status' => $filters['status'],
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
		]);
	}

	public function update_registration($id)
	{
		$this->require_permission('members.registrations', 'approve');
		$this->load->model('Member_registration_model');

		try {
			$status = (string) $this->input->post('status', true);
			$member_id = $this->Member_registration_model->update_status(
				(int) $id,
				$status,
				$this->input->post('admin_note', true),
				(int) ($this->current_user['id'] ?? 0),
				function (array $request) {
					return $this->Member_model->create_member([
						'full_name' => $request['full_name'],
						'identity_type' => 'NIK',
						'identity_number' => $request['identity_number'],
						'gender' => $request['gender'],
						'birth_place' => $request['birth_place'],
						'birth_date' => $request['birth_date'],
						'address' => $request['address'],
						'district' => $request['district'],
						'village' => $request['village'],
						'phone' => $request['phone'],
						'email' => $request['email'],
						'photo_path' => $request['photo_path'],
						'member_type' => $request['member_type'] ?: 'Umum',
						'education' => $request['education'],
						'occupation' => $request['occupation'],
						'status' => 'active',
						'registered_at' => date('Y-m-d H:i:s'),
						'expired_at' => date('Y-m-d H:i:s', strtotime('+1 year')),
						'create_account' => true,
					], self::DEFAULT_IMPORTED_PASSWORD);
				}
			);

			$this->audit_event('members.registration_update', 'member_registration_requests', (int) $id, null, [
				'status' => $status,
				'member_id' => $member_id,
			]);
			$this->session->set_flashdata('success', $status === 'verified' ? 'Pendaftaran diverifikasi dan akun member aktif dibuat.' : 'Status pendaftaran diperbarui.');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
		}

		redirect('members/registrations');
	}

	public function update_renewal($id)
	{
		$this->require_permission('members.renewals', 'approve');

		$this->Member_model->update_renewal_status(
			(int) $id,
			(string) $this->input->post('status', true),
			$this->input->post('admin_note', true),
			(int) ($this->current_user['id'] ?? 0)
		);
		$this->audit_event('members.renewal_update', 'membership_renewal_requests', (int) $id, null, $this->input->post(null, true));
		$this->session->set_flashdata('success', 'Status pengajuan perpanjangan diperbarui.');
		redirect('members/renewals');
	}

	public function sync()
	{
		$this->require_permission('members.sync', 'view');

		$this->render('members/sync', [
			'title' => 'Sinkronisasi Member',
			'default_password' => self::DEFAULT_IMPORTED_PASSWORD,
			'source_stats' => $this->Member_model->source_stats(),
			'sync_runs' => $this->Member_model->recent_sync_runs(20),
			'migration_plan' => $this->Member_model->migration_plan(),
			'can_run_sync' => $this->can('members.sync', 'create'),
		]);
	}

	public function run_sync()
	{
		$this->require_permission('members.sync', 'create');

		$limit = (int) $this->input->post('limit', true);
		$mode = (string) $this->input->post('mode', true);
		$result = $this->Member_model->run_manual_sync(
			(int) ($this->current_user['id'] ?? 0),
			$limit ?: 500,
			self::DEFAULT_IMPORTED_PASSWORD,
			$mode
		);

		$this->audit_event('members.sync_run', 'member_sync_runs', (int) $result['run_id'], null, $result);
		$this->session->set_flashdata('success', $result['message']);
		redirect('members/sync');
	}

	private function member_input()
	{
		return [
			'member_no' => $this->input->post('member_no', true),
			'full_name' => $this->input->post('full_name', true),
			'identity_type' => $this->input->post('identity_type', true),
			'identity_number' => $this->input->post('identity_number', true),
			'gender' => $this->input->post('gender', true),
			'birth_place' => $this->input->post('birth_place', true),
			'birth_date' => $this->input->post('birth_date', true),
			'address' => $this->input->post('address', true),
			'district' => $this->input->post('district', true),
			'village' => $this->input->post('village', true),
			'phone' => $this->input->post('phone', true),
			'email' => $this->input->post('email', true),
			'photo_path' => $this->input->post('photo_path', true),
			'member_type' => $this->input->post('member_type', true),
			'education' => $this->input->post('education', true),
			'occupation' => $this->input->post('occupation', true),
			'status' => $this->input->post('status', true),
			'registered_at' => $this->input->post('registered_at', true),
			'expired_at' => $this->input->post('expired_at', true),
			'create_account' => (int) $this->input->post('create_account', true) === 1,
			'password' => $this->input->post('password', false),
		];
	}
}
