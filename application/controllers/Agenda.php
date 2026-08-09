<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Agenda extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Event_model');
		$this->load->model('Member_model');
	}

	public function index()
	{
		$filters = [
			'q' => $this->input->get('q', true),
			'category_id' => $this->input->get('category_id', true),
			'time' => $this->input->get('time', true) ?: 'upcoming',
		];
		$per_page = 12;
		$page = max(1, (int) $this->input->get('page', true));
		$total_rows = $this->Event_model->count_public_events($filters);
		$total_pages = max(1, (int) ceil($total_rows / $per_page));
		$page = min($page, $total_pages);
		$offset = ($page - 1) * $per_page;

		$this->load->view('agenda/index', [
			'title' => 'Agenda Literasi - Pustaka Digital Rembang',
			'events' => $this->Event_model->get_public_events($filters, $per_page, $offset),
			'categories' => $this->Event_model->category_options(),
			'filters' => [
				'q' => $filters['q'],
				'category_id' => $filters['category_id'],
				'time' => $filters['time'],
				'page' => $page,
			],
			'pagination' => [
				'total_rows' => $total_rows,
				'total_pages' => $total_pages,
				'page' => $page,
				'per_page' => $per_page,
			],
			'current_member' => $this->current_member(),
		]);
	}

	public function detail($id)
	{
		$event = $this->Event_model->get_public_event((int) $id);
		if (! $event) {
			show_404();
			return;
		}
		$fields = $this->Event_model->get_form_fields((int) $id, true);
		$current_member = $this->current_member();

		$this->load->view('agenda/detail', [
			'title' => $event['title'] . ' - Agenda Literasi',
			'event' => $event,
			'fields' => $fields,
			'current_member' => $current_member,
			'member_answer_defaults' => $this->member_answer_defaults($fields, $current_member),
			'is_open' => $this->Event_model->registration_is_open($event),
		]);
	}

	public function register($id)
	{
		$member = $this->current_member();
		try {
			$registration = $this->Event_model->create_registration((int) $id, [
				'participant_type' => $this->input->post('participant_type', true),
				'participant_name' => $this->input->post('participant_name', true),
				'participant_phone' => $this->input->post('participant_phone', true),
				'participant_email' => $this->input->post('participant_email', true),
				'institution' => $this->input->post('institution', true),
				'participant_count' => $this->input->post('participant_count', true),
			], (array) $this->input->post('answers', true), $member);
			$this->session->set_flashdata('success', 'Pendaftaran event berhasil. Kode: ' . $registration['registration_code']);
			redirect('agenda/ticket/' . rawurlencode($registration['ticket_token']));
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
			$this->session->set_flashdata('event_registration_old', $this->input->post(null, true));
			redirect('agenda/detail/' . (int) $id . '#daftar-event');
		}
	}

	public function ticket($token)
	{
		$registration = $this->Event_model->get_registration_by_ticket((string) $token);
		if (! $registration) {
			show_404();
			return;
		}

		$this->load->view('agenda/ticket', [
			'title' => 'Tiket Event Literasi',
			'registration' => $registration,
			'current_member' => $this->current_member(),
		]);
	}

	private function current_member()
	{
		$user = (array) $this->session->userdata('auth_user');
		if (empty($user['id'])) {
			return null;
		}

		return $this->Member_model->get_member_by_auth_user_id((int) $user['id']);
	}

	private function member_answer_defaults(array $fields, array $member = null)
	{
		if (empty($member)) {
			return [];
		}

		$source = [
			'nama' => $member['full_name'] ?? '',
			'namalengkap' => $member['full_name'] ?? '',
			'namapeserta' => $member['full_name'] ?? '',
			'penanggungjawab' => $member['full_name'] ?? '',
			'nomoranggota' => $member['member_no'] ?? '',
			'noanggota' => $member['member_no'] ?? '',
			'memberno' => $member['member_no'] ?? '',
			'kartuanggota' => $member['member_no'] ?? '',
			'nik' => $member['identity_number'] ?? '',
			'nomoridentitas' => $member['identity_number'] ?? '',
			'noidentitas' => $member['identity_number'] ?? '',
			'identitas' => $member['identity_number'] ?? '',
			'email' => $member['email'] ?? '',
			'surel' => $member['email'] ?? '',
			'hp' => $member['phone'] ?? '',
			'nohp' => $member['phone'] ?? '',
			'nomorhp' => $member['phone'] ?? '',
			'telepon' => $member['phone'] ?? '',
			'whatsapp' => $member['phone'] ?? '',
			'wa' => $member['phone'] ?? '',
			'alamat' => $member['address'] ?? '',
			'kecamatan' => $member['district'] ?? '',
			'desa' => $member['village'] ?? '',
			'kelurahan' => $member['village'] ?? '',
			'tempatlahir' => $member['birth_place'] ?? '',
			'tanggallahir' => $member['birth_date'] ?? '',
			'jeniskelamin' => $member['gender_label'] ?: ($member['gender'] ?? ''),
			'tipeanggota' => $member['member_type_label'] ?: ($member['member_type'] ?? ''),
			'pendidikan' => $member['education_label'] ?: ($member['education'] ?? ''),
			'pekerjaan' => $member['occupation_label'] ?: ($member['occupation'] ?? ''),
		];

		$defaults = [];
		foreach ($fields as $field) {
			$key = $this->normalize_field_key($field['field_key'] ?? '');
			$label = $this->normalize_field_key($field['field_label'] ?? '');
			$value = $source[$key] ?? null;
			if ($value === null) {
				foreach ($source as $alias => $candidate) {
					if ($candidate !== '' && ($label === $alias || strpos($label, $alias) !== false)) {
						$value = $candidate;
						break;
					}
				}
			}

			if ($value !== null && $value !== '') {
				$defaults[$field['field_key']] = $value;
			}
		}

		return $defaults;
	}

	private function normalize_field_key($value)
	{
		return preg_replace('/[^a-z0-9]+/', '', strtolower((string) $value));
	}
}
