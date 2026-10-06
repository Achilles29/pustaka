<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Patron_feedback_model extends CI_Model
{
	public function contacts()
	{
		$defaults = ['whatsapp'=>'085165805518','instagram'=>'dinarpusrembang','tiktok'=>'perpustakaan.umum.rbg'];
		if (!$this->db->table_exists('patron_feedback_contacts')) return $defaults;
		$row = $this->db->where('id', 1)->get('patron_feedback_contacts')->row_array();
		return $row ? array_intersect_key($row, $defaults) : $defaults;
	}

	public function validate_contacts(array $data)
	{
		$out = [];
		foreach (['whatsapp','instagram','tiktok'] as $key) {
			if (isset($data[$key]) && !is_scalar($data[$key])) throw new RuntimeException('Format kontak tidak valid.');
			$out[$key] = trim((string) ($data[$key] ?? ''));
		}
		$out['whatsapp'] = preg_replace('/[\s()+-]/', '', $out['whatsapp']);
		if ($out['whatsapp'] !== '' && !preg_match('/^(?:08[0-9]{8,11}|628[0-9]{8,11})$/D', $out['whatsapp'])) {
			throw new RuntimeException('WhatsApp harus berupa nomor Indonesia dengan awalan 08 atau 628.');
		}
		foreach (['instagram'=>30,'tiktok'=>24] as $key=>$max) {
			$out[$key] = ltrim($out[$key], '@');
			if ($out[$key] !== '' && !preg_match('/^[a-zA-Z0-9_][a-zA-Z0-9_.]{0,'.($max-1).'}$/D', $out[$key])) {
				throw new RuntimeException(ucfirst($key).' diisi nama pengguna saja (huruf, angka, titik, atau garis bawah; maksimal '.$max.' karakter).');
			}
		}
		return $out;
	}

	public function save_contacts(array $data)
	{
		$payload = $this->validate_contacts($data);
		if (!$this->db->table_exists('patron_feedback_contacts')) throw new RuntimeException('Tabel pengaturan kontak belum tersedia. Jalankan migrasi kontak pemustaka.');
		$payload['updated_at'] = date('Y-m-d H:i:s');
		if (!$this->db->query('INSERT INTO patron_feedback_contacts (id, whatsapp, instagram, tiktok, updated_at) VALUES (1, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE whatsapp=VALUES(whatsapp), instagram=VALUES(instagram), tiktok=VALUES(tiktok), updated_at=VALUES(updated_at)', array_values($payload))) throw new RuntimeException('Pengaturan kontak belum dapat disimpan.');
		return $payload;
	}

	public function contact_links()
	{
		$c = $this->contacts(); $links = [];
		if ($c['whatsapp'] !== '') {
			$phone = $c['whatsapp'][0] === '0' ? '62'.substr($c['whatsapp'],1) : $c['whatsapp'];
			$links[] = ['name'=>'WhatsApp','handle'=>$c['whatsapp'],'url'=>'https://wa.me/'.rawurlencode($phone),'icon'=>'brand-whatsapp','note'=>'Hubungi perpustakaan'];
		}
		foreach (['instagram'=>['Instagram','https://www.instagram.com/','brand-instagram','Cerita & kabar terbaru'], 'tiktok'=>['TikTok','https://www.tiktok.com/@','brand-tiktok','Temukan inspirasi membaca']] as $key=>$meta) {
			if ($c[$key] !== '') $links[] = ['name'=>$meta[0],'handle'=>'@'.$c[$key],'url'=>$meta[1].rawurlencode($c[$key]),'icon'=>$meta[2],'note'=>$meta[3]];
		}
		return $links;
	}

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
