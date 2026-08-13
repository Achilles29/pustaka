<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Patron_feedback_model extends CI_Model
{
	public function submit(array $data, $user_id = null)
	{
		$type = (string) ($data['feedback_type'] ?? '');
		$types = ['service_review','book_request','digital_content','facility','feature','complaint','other'];
		if (! in_array($type, $types, true)) throw new RuntimeException('Pilih jenis pesan yang sesuai.');
		$subject = $this->clip($data['subject'] ?? '', 255);
		$message = trim((string) ($data['message'] ?? ''));
		if ($subject === '' || $message === '') throw new RuntimeException('Judul dan isi pesan wajib diisi.');
		$rating = (int) ($data['rating'] ?? 0);
		if ($rating && ($rating < 1 || $rating > 5)) throw new RuntimeException('Nilai ulasan harus antara 1 sampai 5.');
		if ($type !== 'service_review') $rating = 0;
		$is_collection_suggestion = in_array($type, ['book_request', 'digital_content'], true);
		$uses_location_context = in_array($type, ['service_review', 'facility', 'complaint'], true);
		$email = $this->blank($data['submitter_email'] ?? '', 180);
		if ($email && ! filter_var($email, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Format email tidak valid.');
		$payload = [
			'public_token' => bin2hex(random_bytes(32)), 'submitted_by_auth_user_id' => $user_id ? (int) $user_id : null,
			'feedback_type' => $type, 'rating' => $rating ?: null, 'subject' => $subject,
			'message' => substr($message, 0, 5000), 'suggested_author' => $is_collection_suggestion ? $this->blank($data['suggested_author'] ?? '', 255) : null,
			'suggested_isbn' => $is_collection_suggestion ? $this->blank($data['suggested_isbn'] ?? '', 32) : null, 'suggested_format' => $is_collection_suggestion ? $this->blank($data['suggested_format'] ?? '', 80) : null,
			'location_context' => $uses_location_context ? $this->blank($data['location_context'] ?? '', 500) : null,
			'submitter_name' => $this->blank($data['submitter_name'] ?? '', 180), 'submitter_email' => $email,
			'submitter_phone' => $this->blank($data['submitter_phone'] ?? '', 80), 'status' => 'pending',
		];
		if (! $this->db->insert('patron_feedback', $payload)) throw new RuntimeException('Pesan belum dapat disimpan. Silakan coba lagi.');
		return ['id' => (int) $this->db->insert_id(), 'token' => $payload['public_token']];
	}

	public function find($id) { return $this->db->where('id', (int) $id)->get('patron_feedback')->row_array(); }
	public function find_by_token($token) { return $this->db->where('public_token', trim((string) $token))->get('patron_feedback')->row_array(); }
	public function stats() { $out=['total'=>0,'pending'=>0,'reviewing'=>0,'planned'=>0,'resolved'=>0]; foreach($this->db->select('status,COUNT(*) total')->group_by('status')->get('patron_feedback')->result_array() as $row){$out[$row['status']]=(int)$row['total'];$out['total']+=(int)$row['total'];} return $out; }
	public function list_admin(array $filters, $limit, $offset) { $this->admin_query($filters); return $this->db->order_by("FIELD(status,'pending','reviewing','planned','resolved','declined')",'',false)->order_by('id','DESC')->limit($limit,$offset)->get()->result_array(); }
	public function count_admin(array $filters) { $this->admin_query($filters); return (int) $this->db->count_all_results(); }
	public function review($id, $status, $note, $user_id)
	{
		if (! in_array($status, ['pending','reviewing','planned','resolved','declined'], true)) throw new RuntimeException('Status tindak lanjut tidak valid.');
		$payload=['status'=>$status,'admin_note'=>$this->blank($note,5000),'reviewed_by'=>$user_id?:null,'reviewed_at'=>date('Y-m-d H:i:s')];
		if (! $this->db->where('id',(int)$id)->update('patron_feedback',$payload)) throw new RuntimeException('Tindak lanjut tidak dapat disimpan.');
		return $payload;
	}
	public function get_member_items($user_id, $limit=5) { return $this->db->select('id,feedback_type,rating,subject,status,admin_note,created_at')->where('submitted_by_auth_user_id',(int)$user_id)->order_by('id','DESC')->limit(max(1,min(10,(int)$limit)))->get('patron_feedback')->result_array(); }
	private function admin_query(array $filters) { $this->db->from('patron_feedback'); if($q=trim((string)($filters['q']??'')))$this->db->group_start()->like('subject',$q)->or_like('message',$q)->or_like('submitter_name',$q)->or_like('submitter_email',$q)->group_end(); if(!empty($filters['status']))$this->db->where('status',$filters['status']); if(!empty($filters['type']))$this->db->where('feedback_type',$filters['type']); }
	private function clip($value,$length) { return substr(trim((string)$value),0,$length); }
	private function blank($value,$length) { $value=$this->clip($value,$length); return $value===''?null:$value; }
}
