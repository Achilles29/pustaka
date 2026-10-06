<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Members extends MY_Controller
{
	const DEFAULT_IMPORTED_PASSWORD = 'perpus2026';
	const DEFAULT_MEMBERSHIP_YEARS = 5;

	public function __construct()
	{
		parent::__construct();
		$this->load->model(['Member_model', 'Membership_card_model', 'Region_model']);
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
		$stats = $this->Member_model->stats();

		$this->render('members/index', [
			'title' => 'Membership Digital',
			'stats' => $stats,
			'local_stats' => [
				['label' => 'Profil member lokal', 'value' => $stats['members'] ?? 0],
				['label' => 'Akun login aktif', 'value' => $stats['linked_users'] ?? 0],
				['label' => 'Referensi foto', 'value' => $stats['photo_refs'] ?? 0],
				['label' => 'Riwayat sinkronisasi', 'value' => $stats['sync_runs'] ?? 0],
			],
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
		if($this->db->table_exists('password_reset_requests')){$pending=(int)$this->db->where('approval_status','pending')->count_all_results('password_reset_requests');$shortcut='<div class="container-xl mt-3"><div class="alert alert-info d-flex align-items-center mb-0"><i class="ti ti-key fs-2 me-2"></i><div><strong>Verifikasi Reset Password</strong><div class="small">Tinjau permintaan cadangan ketika tautan WhatsApp tidak diterima.</div></div><a class="btn btn-primary ms-auto" href="'.base_url('members/password_resets').'">Buka Antrean'.($pending?' <span class="badge bg-white text-primary ms-1">'.$pending.'</span>':'').'</a></div></div>';$this->output->set_output(str_replace('<div class="page-body">','<div class="page-body">'.$shortcut,$this->output->get_output()));}
	}

	public function password_resets()
	{
		$this->require_permission('members.index','edit');$this->load->model('Password_reset_model');
		$this->render('game/password_reset_admin',['title'=>'Verifikasi Reset Password','rows'=>$this->Password_reset_model->admin_requests()]);
	}

	public function review_password_reset($id)
	{
		$this->require_permission('members.index','edit');
		try{$this->load->model('Password_reset_model');$status=$this->Password_reset_model->admin_review((int)$id,$this->input->post('action',true),(int)$this->current_user['id'],$this->input->post('review_note',true));$this->audit_event('members.password_reset.'.$status,'password_reset_requests',(int)$id,null,['status'=>$status]);$this->session->set_flashdata('success',$status==='approved'?'Permintaan disetujui. Password baru sudah tersedia pada halaman status pemohon.':'Permintaan reset ditolak.');}
		catch(Throwable $e){$this->session->set_flashdata('error',$e->getMessage());}
		redirect('members/password_resets');
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
			'districts' => $this->Region_model->get_districts(),
			'villages' => [],
		]);
		$html = $this->decorate_member_photo_form($this->output->get_output(), []);
		$this->output->set_output($this->decorate_default_membership_expiry($html));
	}

	public function store()
	{
		$this->require_permission('members.index', 'create');

		$new_photo = null;
		$member_id = 0;
		$previous_db_debug = $this->db->db_debug;
		$this->db->db_debug = false;
		try {
			$input = $this->member_input(true);
			if (empty($input['expired_at'])) {
				$input['expired_at'] = $this->default_membership_expiry();
			}
			$new_photo = $this->handle_member_photo_upload(0);
			// Form tambah tidak menerima referensi nama/path dari pengguna. Foto
			// hanya boleh berasal dari berkas yang lolos validasi upload.
			$input['photo_path'] = $new_photo;
			$member_id = $this->Member_model->create_member($input, self::DEFAULT_IMPORTED_PASSWORD);
			if ($new_photo) {
				$this->db->where('id', (int) $member_id)->update('members', [
					'photo_path' => $new_photo,
					'photo_source_path' => $new_photo,
					'photo_local_path' => $new_photo,
					'photo_migration_status' => 'copied',
					'photo_migrated_at' => date('Y-m-d H:i:s'),
				]);
			}
			$audit_input = $input;
			$audit_input['photo_uploaded'] = (bool) $new_photo;
			$audit_input['password_set'] = $input['password'] !== '';
			unset($audit_input['password']);
			$this->audit_event('members.create', 'members', $member_id, null, $audit_input);
			$this->session->set_flashdata('success', $new_photo ? 'Member baru dan foto berhasil disimpan.' : 'Member baru berhasil disimpan.');
			$this->db->db_debug = $previous_db_debug;
			redirect('members/detail/' . $member_id);
		} catch (Throwable $e) {
			$this->db->db_debug = $previous_db_debug;
			if (! $member_id && $new_photo && is_file(FCPATH . $new_photo)) @unlink(FCPATH . $new_photo);
			$this->session->set_flashdata('error', $this->friendly_member_error($e));
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
			'districts' => $this->Region_model->get_districts(),
			'villages' => ! empty($member['district_id'])
				? $this->Region_model->get_villages_by_district((int) $member['district_id'])
				: [],
		]);
		$html=$this->decorate_member_photo_form($this->output->get_output(),$member);
		$this->output->set_output($this->decorate_member_account_form($html,$member));
	}

	public function update($id)
	{
		$this->require_permission('members.index', 'edit');

		$before = $this->Member_model->get_member((int) $id);
		if (! $before) {
			show_404();
			return;
		}

		$new_photo=null;
		try {
			$input=$this->member_input();
			$this->validate_member_account_input($input,(int)($before['auth_user_id']??0));
			$new_photo=$this->handle_member_photo_upload((int)$id);
			if($new_photo)$input['photo_path']=$new_photo;
			$this->Member_model->update_member((int) $id, $input, self::DEFAULT_IMPORTED_PASSWORD);
			$this->update_member_login_account((int)$id,$input);
			if($new_photo)$this->db->where('id',(int)$id)->update('members',['photo_path'=>$new_photo,'photo_source_path'=>$new_photo,'photo_local_path'=>$new_photo,'photo_migration_status'=>'copied','photo_migrated_at'=>date('Y-m-d H:i:s')]);
			$audit_input=$input;$audit_input['password_changed']=$input['password']!=='';unset($audit_input['password']);
			$this->audit_event('members.update', 'members', (int) $id, $before, $audit_input);
			$this->session->set_flashdata('success', $new_photo?'Member dan foto profil berhasil diperbarui.':'Member berhasil diperbarui.');
			redirect('members/detail/' . (int) $id);
		} catch (Throwable $e) {
			if($new_photo&&is_file(FCPATH.$new_photo))@unlink(FCPATH.$new_photo);
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

	/** Galeri operasional: petugas dapat menemukan, melihat, dan mencetak kartu tanpa login sebagai member. */
	public function cards()
	{
		$this->require_permission('members.index', 'view');
		$filters = [
			'q' => $this->input->get('q', true),
			'status' => $this->input->get('status', true),
			'user_status' => '',
		];
		$per_page = (int) $this->input->get('per_page', true);
		$per_page = in_array($per_page, [12, 24, 48], true) ? $per_page : 24;
		$page = max(1, (int) $this->input->get('page', true));
		$total = $this->Member_model->count_members($filters);
		$pages = max(1, (int) ceil($total / $per_page));
		$page = min($page, $pages);
		$this->render('members/cards', [
			'title' => 'Kartu Anggota',
			'design' => $this->Membership_card_model->get_design(),
			'members' => $this->Member_model->get_members($filters, $per_page, ($page - 1) * $per_page),
			'filters' => $filters + ['per_page' => $per_page, 'page' => $page],
			'pagination' => compact('total', 'pages', 'page', 'per_page'),
		]);
	}

	public function card_design()
	{
		$this->require_permission('members.index', 'edit');
		$sample = $this->Member_model->get_members(['status' => 'active'], 1, 0);
		$this->render('members/card_design', [
			'title' => 'Studio Desain Kartu',
			'design' => $this->Membership_card_model->get_design(),
			'backgrounds' => $this->Membership_card_model->backgrounds(),
			'object_assets' => $this->Membership_card_model->object_assets(),
			'sample_member' => $sample[0] ?? [
				'id' => 0, 'full_name' => 'NAMA PEMUSTAKA', 'member_no' => 'PDR-3317-2026-000001',
				'member_type_label' => 'Anggota aktif', 'status' => 'active', 'card_status' => 'active', 'expired_at' => $this->default_membership_expiry(),
			],
		]);
	}

	public function save_card_design()
	{
		$this->require_permission('members.index', 'edit');
		try {
			$config = $this->Membership_card_model->save_design((array) $this->input->post(null, true), (int) ($this->current_user['id'] ?? 0));
			$this->audit_event('members.card_design_saved', 'membership_card_settings', 1, null, $config);
			$this->session->set_flashdata('success', 'Desain kartu anggota telah dipublikasikan ke seluruh kartu digital.');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', 'Desain kartu gagal disimpan: ' . $e->getMessage());
		}
		redirect('members/cards/design');
	}

	public function upload_card_background()
	{
		$this->require_permission('members.index', 'edit');
		try {
			$id=$this->Membership_card_model->add_background($_FILES['background_file'] ?? [], $this->input->post('label', true), (int) ($this->current_user['id'] ?? 0));
			$this->audit_event('members.card_background_uploaded', 'membership_card_backgrounds', $id, null, ['label'=>$this->input->post('label',true)]);
			$this->session->set_flashdata('success','Background berhasil disimpan ke galeri dan siap dipakai pada desain kartu.');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error','Background gagal diunggah: '.$e->getMessage());
		}
		redirect('members/cards/design');
	}

	public function upload_card_object()
	{
		$this->require_permission('members.index', 'edit');
		try {
			$asset = $this->Membership_card_model->add_object_asset($_FILES['object_file'] ?? [], $this->input->post('label', true), (int) ($this->current_user['id'] ?? 0));
			$this->audit_event('members.card_object_uploaded', 'membership_card_design_assets', (int) $asset['id'], null, ['label' => $asset['label']]);
			if ($this->input->is_ajax_request()) {
				$this->output->set_content_type('application/json')->set_output(json_encode(['ok' => true, 'asset' => [
					'id' => (int) $asset['id'], 'label' => $asset['label'], 'path' => $asset['file_path'], 'url' => base_url($asset['file_path']),
				]]));
				return;
			}
			$this->session->set_flashdata('success', 'Gambar atau logo berhasil disimpan ke galeri objek.');
		} catch (Throwable $e) {
			if ($this->input->is_ajax_request()) {
				$this->output->set_status_header(422)->set_content_type('application/json')->set_output(json_encode(['ok' => false, 'message' => $e->getMessage()]));
				return;
			}
			$this->session->set_flashdata('error', 'Objek gagal diunggah: ' . $e->getMessage());
		}
		redirect('members/cards/design');
	}

	public function reset_card_layout()
	{
		$this->require_permission('members.index', 'edit');
		try {
			$config=$this->Membership_card_model->reset_layout((int) ($this->current_user['id'] ?? 0));
			$this->audit_event('members.card_layout_reset', 'membership_card_settings', 1, null, ['layout'=>$config['layout']]);
			$this->session->set_flashdata('success','Tata letak kartu telah dipulihkan ke desain default dan langsung dipublikasikan.');
		} catch (Throwable $e) {
			$this->session->set_flashdata('error','Reset tata letak gagal: '.$e->getMessage());
		}
		redirect('members/cards/design');
	}

	public function card_print($id)
	{
		$this->require_permission('members.index', 'view');
		$member = $this->Member_model->get_member((int) $id);
		if (! $member) { show_404(); return; }
		$this->load->view('members/card_print', [
			'title' => 'Cetak Kartu - ' . $member['full_name'],
			'member' => $member,
			'design' => $this->Membership_card_model->get_design(),
			'verify_url' => base_url('membership/verify/' . (int) $member['id'] . '/' . $this->Member_model->digital_card_token($member)),
			'print_mode' => true,
		]);
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
						'province_id' => $request['province_id'] ?? null,
						'regency_id' => $request['regency_id'] ?? null,
						'district_id' => $request['district_id'] ?? null,
						'village_id' => $request['village_id'] ?? null,
						'province' => $request['province'] ?? null,
						'regency' => $request['regency'] ?? null,
						'district' => $request['district'],
						'village' => $request['village'],
						'identity_address' => $request['identity_address'] ?? $request['address'],
						'identity_province_id' => $request['identity_province_id'] ?? null,
						'identity_regency_id' => $request['identity_regency_id'] ?? null,
						'identity_district_id' => $request['identity_district_id'] ?? null,
						'identity_village_id' => $request['identity_village_id'] ?? null,
						'identity_province' => $request['identity_province'] ?? null,
						'identity_regency' => $request['identity_regency'] ?? null,
						'identity_district' => $request['identity_district'] ?? $request['district'],
						'identity_village' => $request['identity_village'] ?? $request['village'],
						'phone' => $request['phone'],
						'email' => $request['email'],
						'photo_path' => $request['photo_path'],
						'member_type' => $request['member_type'] ?: 'Umum',
						'education' => $request['education'],
						'occupation' => $request['occupation'],
						'status' => 'active',
						'registered_at' => date('Y-m-d H:i:s'),
						'expired_at' => $this->default_membership_expiry(),
						'create_account' => true,
					], self::DEFAULT_IMPORTED_PASSWORD);
				}
			);
			try {
				if ($status === 'verified' && $this->db->table_exists('wa_outbox')) {
					$this->load->model('Whatsapp_model');
					$request = $this->Member_registration_model->get_request((int) $id);
					$member = $member_id ? $this->Member_model->get_member((int) $member_id) : null;
					if ($request) $this->Whatsapp_model->queue_template('member_registration_approved', $request['phone'] ?? '', [
						'member_name' => $request['full_name'] ?? 'Pemustaka',
						'member_no' => $member['member_no'] ?? '-',
						'request_code' => $request['registration_code'] ?? '-',
					], $member_id ?: null, (int) ($this->current_user['id'] ?? 0));
				}
			} catch (Throwable $notification_error) {
				log_message('error', 'Antrean WA persetujuan pendaftaran #' . (int) $id . ' gagal: ' . $notification_error->getMessage());
			}

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
		// Jika anggota lebih dahulu mendaftar di Pustaka lalu muncul di INLIS,
		// tautkan record yang sama sebelum import. PK lokal tetap dipertahankan,
		// sehingga transaksi Pustaka tidak putus dan unique member/NIK tidak bentrok.
		$this->link_local_members_to_inlislite();
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

	private function link_local_members_to_inlislite()
	{
		$this->db->query("UPDATE members m
			JOIN inlislite_v3.members s ON (
				(NULLIF(TRIM(m.identity_number),'') IS NOT NULL AND m.identity_number = CONVERT(s.IdentityNo USING utf8mb4) COLLATE utf8mb4_unicode_ci)
				OR (NULLIF(TRIM(m.member_no),'') IS NOT NULL AND m.member_no = CONVERT(s.MemberNo USING utf8mb4) COLLATE utf8mb4_unicode_ci)
			)
			LEFT JOIN members mapped ON mapped.source_system='inlislite_v3' AND mapped.source_id=CONVERT(CAST(s.ID AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci AND mapped.id<>m.id
			SET m.source_system='inlislite_v3',m.source_id=CAST(s.ID AS CHAR)
			WHERE (m.source_system IS NULL OR m.source_system<>'inlislite_v3') AND mapped.id IS NULL");
		$this->db->query("UPDATE auth_user u JOIN members m ON m.auth_user_id=u.id AND m.source_system='inlislite_v3'
			SET u.source_system='inlislite_v3',u.source_id=m.source_id,u.member_source_id=CAST(m.source_id AS UNSIGNED)
			WHERE u.source_system IS NULL OR u.source_system<>'inlislite_v3' OR u.source_id IS NULL");
	}

	private function handle_member_photo_upload($member_id)
	{
		// Cloudflare Tunnel pada instalasi ini sesekali menahan POST multipart sampai
		// gateway timeout. Form modern mengirim foto terkompresi sebagai data URL,
		// sehingga data member dan foto tetap masuk dalam satu POST biasa.
		$data_url=(string)$this->input->post('member_photo_data',false);
		if($data_url!==''){
			if(!preg_match('#^data:image/(jpeg|png|webp);base64,([A-Za-z0-9+/=\r\n]+)$#',$data_url,$matches))throw new RuntimeException('Data foto tidak valid. Pilih ulang foto dan coba lagi.');
			$binary=base64_decode(preg_replace('/\s+/','',$matches[2]),true);
			if($binary===false||strlen($binary)===0||strlen($binary)>4194304)throw new RuntimeException('Ukuran foto hasil proses maksimal 4 MB.');
			$info=@getimagesizefromstring($binary);$types=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
			if(!$info||empty($types[strtolower((string)($info['mime']??''))]))throw new RuntimeException('Foto harus berupa JPG, PNG, atau WebP yang valid.');
			$relative='assets/uploads/members/'.date('Y/m');$absolute=FCPATH.str_replace('/',DIRECTORY_SEPARATOR,$relative);
			if(!is_dir($absolute)&&!mkdir($absolute,0775,true))throw new RuntimeException('Folder penyimpanan foto tidak dapat dibuat.');
			$name='member-'.(int)$member_id.'-'.bin2hex(random_bytes(8)).'.'.$types[strtolower((string)$info['mime'])];
			if(file_put_contents($absolute.DIRECTORY_SEPARATOR.$name,$binary,LOCK_EX)===false)throw new RuntimeException('Foto belum dapat disimpan.');
			return $relative.'/'.$name;
		}
		$file=$_FILES['member_photo']??[];
		if(empty($file['name'])||(int)($file['error']??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE)return null;
		if((int)($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK)throw new RuntimeException('Upload foto gagal. Pilih ulang foto dan coba lagi.');
		if((int)($file['size']??0)<=0||(int)$file['size']>4194304)throw new RuntimeException('Ukuran foto maksimal 4 MB.');
		$tmp=(string)($file['tmp_name']??'');if($tmp===''||!is_uploaded_file($tmp))throw new RuntimeException('Berkas foto tidak valid.');
		$info=@getimagesize($tmp);$types=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
		if(!$info||empty($types[strtolower((string)($info['mime']??''))]))throw new RuntimeException('Foto harus berupa JPG, PNG, atau WebP yang valid.');
		if((int)$info[0]<100||(int)$info[1]<100||(int)$info[0]>8000||(int)$info[1]>8000)throw new RuntimeException('Dimensi foto minimal 100×100 dan maksimal 8000×8000 piksel.');
		$relative='assets/uploads/members/'.date('Y/m');$absolute=FCPATH.str_replace('/',DIRECTORY_SEPARATOR,$relative);
		if(!is_dir($absolute)&&!mkdir($absolute,0775,true))throw new RuntimeException('Folder penyimpanan foto tidak dapat dibuat.');
		$name='member-'.(int)$member_id.'-'.bin2hex(random_bytes(8)).'.'.$types[strtolower((string)$info['mime'])];
		if(!move_uploaded_file($tmp,$absolute.DIRECTORY_SEPARATOR.$name))throw new RuntimeException('Foto belum dapat disimpan.');
		return $relative.'/'.$name;
	}

	private function decorate_member_photo_form($html,array $member)
	{
		$path=($member['photo_local_path']??'')?: (($member['photo_source_path']??'')?:($member['photo_path']??''));
		$url='';if($path){$normalized=str_replace('\\','/',trim((string)$path,'/'));$url=base_url(strpos($normalized,'assets/')===0?$normalized:'assets/uploads/inlislite/source_mirror/'.$normalized);}
		$preview=$url?'<img id="member-photo-preview" src="'.html_escape($url).'" alt="Foto member" style="width:112px;height:140px;object-fit:cover;border-radius:14px;border:1px solid #dbe5ef">':'<div id="member-photo-placeholder" style="width:112px;height:140px;display:grid;place-items:center;border-radius:14px;background:#edf3f9;color:#64809c;font-size:2rem"><i class="ti ti-user"></i></div><img id="member-photo-preview" alt="Preview foto" hidden style="width:112px;height:140px;object-fit:cover;border-radius:14px;border:1px solid #dbe5ef">';
		$hint=empty($member)?'JPG, PNG, atau WebP. Foto beresolusi besar otomatis diperkecil sebelum dikirim agar penyimpanan tidak timeout.':'JPG, PNG, atau WebP. Foto beresolusi besar otomatis diperkecil sebelum dikirim agar penyimpanan tidak timeout.';
		$block='<div class="mb-3"><label class="form-label">Foto Member</label><div class="d-flex gap-3 align-items-center mb-2">'.$preview.'<div class="flex-fill"><input type="hidden" name="photo_path" value="'.html_escape((string)($member['photo_path']??'')).'"><input id="member-photo-data" type="hidden" name="member_photo_data" value=""><input id="member-photo-input" type="file" class="form-control" accept="image/jpeg,image/png,image/webp"><div class="form-hint">'.$hint.'</div></div></div></div>';
		$html=preg_replace('/<div class="mb-3">\\s*<label class="form-label">Nama File Foto<\\/label>\\s*<input[^>]*name="photo_path"[^>]*>\\s*<\\/div>/',$block,$html,1);
		$script='<script>(function(){
			var input=document.getElementById("member-photo-input"),data=document.getElementById("member-photo-data"),preview=document.getElementById("member-photo-preview"),placeholder=document.getElementById("member-photo-placeholder");
			if(!input||!preview)return;
			var form=input.form,processing=false;
			input.addEventListener("change",function(){
				var file=this.files&&this.files[0];data.value="";if(!file)return;if(file.size>15728640){this.value="";alert("Ukuran foto sumber maksimal 15 MB.");return;}
				processing=true;var button=form&&form.querySelector("button[type=submit]");if(button){button.disabled=true;button.dataset.originalText=button.innerHTML;button.innerHTML="Memproses foto...";}
				var image=new Image(),url=URL.createObjectURL(file);
				image.onload=function(){
					URL.revokeObjectURL(url);var max=1200,scale=Math.min(1,max/Math.max(image.naturalWidth,image.naturalHeight));
					var canvas=document.createElement("canvas");canvas.width=Math.max(1,Math.round(image.naturalWidth*scale));canvas.height=Math.max(1,Math.round(image.naturalHeight*scale));
					canvas.getContext("2d").drawImage(image,0,0,canvas.width,canvas.height);
					data.value=canvas.toDataURL("image/jpeg",0.8);preview.src=data.value;preview.hidden=false;if(placeholder)placeholder.hidden=true;input.value="";processing=false;if(button){button.disabled=false;button.innerHTML=button.dataset.originalText||"Simpan Member";}
				};
				image.onerror=function(){URL.revokeObjectURL(url);input.value="";processing=false;if(button){button.disabled=false;button.innerHTML=button.dataset.originalText||"Simpan Member";}alert("Foto tidak dapat diproses. Pilih file JPG, PNG, atau WebP lain.");};image.src=url;
			});
			if(form)form.addEventListener("submit",function(event){if(processing){event.preventDefault();alert("Foto masih diproses. Tunggu sebentar lalu simpan kembali.");}});
		})();</script>';
		return str_replace('</body>',$script.'</body>',$html);
	}

	/** Isi tampilan default form admin tanpa mengubah tanggal yang dipilih petugas. */
	private function decorate_default_membership_expiry($html)
	{
		$value = date('Y-m-d\TH:i', strtotime($this->default_membership_expiry()));
		$html = preg_replace_callback(
			'/(<input\b[^>]*name="expired_at"[^>]*value=")[^"]*(")/i',
			function ($matches) use ($value) {
				return $matches[1] . $value . $matches[2];
			},
			$html,
			1
		);
		// Form tambah: pembuatan akun aktif secara default dan petugas dapat
		// memilih masa aktif sampai lima tahun tanpa menghitung tanggal manual.
		$html = preg_replace(
			'/(<input\b[^>]*name="create_account"[^>]*)(>)/i',
			'$1 checked$2',
			$html,
			1
		);
		$duration = '<div class="mb-3"><label class="form-label">Masa Aktif Membership</label><select class="form-select" id="membership-duration-months"><option value="6">6 bulan</option><option value="12">12 bulan (1 tahun)</option><option value="24">24 bulan (2 tahun)</option><option value="36">36 bulan (3 tahun)</option><option value="48">48 bulan (4 tahun)</option><option value="60" selected>60 bulan (5 tahun)</option></select><div class="form-hint">Tanggal berlaku sampai dihitung otomatis dari tanggal daftar.</div></div>';
		$html = preg_replace('/(<label class="form-check mb-3">)/', $duration . '$1', $html, 1);
		$script = '<script>(function(){var duration=document.getElementById("membership-duration-months"),registered=document.querySelector("[name=registered_at]"),expired=document.querySelector("[name=expired_at]");if(!duration||!expired)return;function apply(){var base=registered&&registered.value?new Date(registered.value):new Date();if(isNaN(base.getTime()))base=new Date();var day=base.getDate();base.setDate(1);base.setMonth(base.getMonth()+parseInt(duration.value,10));base.setDate(Math.min(day,new Date(base.getFullYear(),base.getMonth()+1,0).getDate()));function pad(v){return String(v).padStart(2,"0")}expired.value=base.getFullYear()+"-"+pad(base.getMonth()+1)+"-"+pad(base.getDate())+"T"+pad(base.getHours())+":"+pad(base.getMinutes())}duration.addEventListener("change",apply);if(registered)registered.addEventListener("change",apply);apply()})();</script>';
		return str_replace('</body>', $script . '</body>', $html);
	}

	private function default_membership_expiry()
	{
		return date('Y-m-d H:i:s', strtotime('+' . self::DEFAULT_MEMBERSHIP_YEARS . ' years'));
	}

	private function decorate_member_account_form($html,array $member)
	{
		$username=html_escape((string)($member['username']??''));
		$replacement='<input type="text" class="form-control" id="member-login-username-preview" name="username" value="'.$username.'" minlength="3" maxlength="80" autocomplete="username"><div class="form-hint">Dapat diganti. Minimal 3 karakter dan tanpa spasi.</div>';
		$html=preg_replace('/<input type="text" class="form-control" id="member-login-username-preview"[^>]*>\s*<div class="form-hint">.*?<\/div>/s',$replacement,$html,1);
		$html=preg_replace('/<input type="password" class="form-control" name="password"/','<input type="password" class="form-control" name="password" minlength="8" maxlength="72" autocomplete="new-password"',$html,1);
		$html=str_replace("identityInput.addEventListener('input', updatePreview);\n\tmemberNoInput.addEventListener('input', updatePreview);\n\tupdatePreview();",'', $html);
		return $html;
	}

	private function validate_member_account_input(array $input,$auth_user_id=0)
	{
		$username=trim((string)($input['username']??''));
		$password=(string)($input['password']??'');
		if($username!==''&&(strlen($username)<3||strlen($username)>80||preg_match('/\s/',$username)))throw new InvalidArgumentException('Username harus 3–80 karakter dan tidak boleh mengandung spasi.');
		if($username!==''){$this->db->where('username',$username);if($auth_user_id)$this->db->where('id <>',$auth_user_id);if($this->db->count_all_results('auth_user')>0)throw new InvalidArgumentException('Username sudah digunakan oleh akun lain.');}
		if($password!==''&&(strlen($password)<8||strlen($password)>72))throw new InvalidArgumentException('Password harus terdiri dari 8 sampai 72 karakter.');
	}

	private function update_member_login_account($member_id,array $input)
	{
		$member=$this->Member_model->get_member($member_id);$user_id=(int)($member['auth_user_id']??0);if(!$user_id)return;
		$payload=[];$username=trim((string)($input['username']??''));$password=(string)($input['password']??'');
		if($username!=='')$payload['username']=$username;
		if($password!==''){$payload['password_hash']=password_hash($password,PASSWORD_BCRYPT);$payload['force_password_change']=0;}
		if($payload)$this->db->where('id',$user_id)->update('auth_user',$payload);
	}

	private function member_input($region_required = false)
	{
		$input = [
			'member_no' => $this->input->post('member_no', true),
			'full_name' => $this->input->post('full_name', true),
			'identity_type' => $this->input->post('identity_type', true),
			'identity_number' => $this->input->post('identity_number', true),
			'gender' => $this->input->post('gender', true),
			'birth_place' => $this->input->post('birth_place', true),
			'birth_date' => $this->input->post('birth_date', true),
			'address' => $this->input->post('address', true),
			'district_id' => (int) $this->input->post('district_id', true),
			'village_id' => (int) $this->input->post('village_id', true),
			'district' => $this->input->post('existing_district', true),
			'village' => $this->input->post('existing_village', true),
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
			'username' => $this->input->post('username', true),
			'password' => $this->input->post('password', false),
		];

		return $this->resolve_member_region_input($input, (bool) $region_required);
	}

	/** Jangan tampilkan detail SQL/constraint kepada petugas. */
	private function friendly_member_error(Throwable $error)
	{
		$message = trim((string) $error->getMessage());
		$database_error = (array) $this->db->error();
		$combined = strtolower($message . ' ' . ($database_error['message'] ?? ''));
		if ((int) ($database_error['code'] ?? 0) === 1062
			|| strpos($combined, 'duplicate entry') !== false
			|| strpos($combined, 'uq_members_identity_number') !== false) {
			return 'NIK tersebut sudah terdaftar. Silakan cari dan perbarui data member yang sudah ada, atau gunakan NIK lain.';
		}
		if ($message === '' || preg_match('/\b(sql|database|query|constraint|1062)\b/i', $message)) {
			return 'Data member belum dapat disimpan. Periksa kembali isian lalu coba lagi.';
		}
		return $message;
	}

	/** Validasi pasangan kecamatan-desa dan isi nama wilayah dari master resmi. */
	private function resolve_member_region_input(array $input, $required = false)
	{
		$district_id = (int) ($input['district_id'] ?? 0);
		$village_id = (int) ($input['village_id'] ?? 0);
		if ($district_id === 0 && $village_id === 0) {
			if ($required) {
				throw new InvalidArgumentException('Kecamatan dan desa/kelurahan wajib dipilih.');
			}
			$input['district_id'] = null;
			$input['village_id'] = null;
			return $input;
		}
		if ($district_id === 0 || $village_id === 0) {
			throw new InvalidArgumentException('Kecamatan dan desa/kelurahan harus dipilih lengkap.');
		}

		$district = $this->Region_model->get_district($district_id);
		$village = $this->Region_model->get_village($village_id);
		if (! $district || ! $village
			|| (int) ($district['is_active'] ?? 0) !== 1
			|| (int) ($village['is_active'] ?? 0) !== 1
			|| (int) ($village['district_id'] ?? 0) !== $district_id
			|| (string) ($district['regency_code'] ?? '') !== Region_model::REMBANG_REGENCY_CODE) {
			throw new InvalidArgumentException('Kecamatan dan desa/kelurahan yang dipilih tidak valid.');
		}

		$input['province_id'] = (int) $district['province_id'];
		$input['regency_id'] = (int) $district['regency_id'];
		$input['district_id'] = $district_id;
		$input['village_id'] = $village_id;
		$input['province'] = $district['province_name'] ?? null;
		$input['regency'] = $district['regency_name'] ?? null;
		$input['district'] = $district['name'];
		$input['village'] = $village['name'];

		// Form admin memakai satu alamat utama; jadikan alamat tersebut juga
		// sebagai alamat identitas agar kolom wilayah tidak terhapus saat edit.
		$input['identity_address'] = $input['address'] ?? null;
		$input['identity_province_id'] = $input['province_id'];
		$input['identity_regency_id'] = $input['regency_id'];
		$input['identity_district_id'] = $district_id;
		$input['identity_village_id'] = $village_id;
		$input['identity_province'] = $input['province'];
		$input['identity_regency'] = $input['regency'];
		$input['identity_district'] = $input['district'];
		$input['identity_village'] = $input['village'];

		return $input;
	}
}
