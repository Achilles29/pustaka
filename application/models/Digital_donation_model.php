<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Digital_donation_model extends CI_Model
{
	const MAX_UPLOAD_BYTES = 209715200;

	public function create_submission(array $data, array $file, $auth_user_id = null)
	{
		$payload = $this->validate_payload($data);
		$upload = $this->store_upload($file);
		if (empty($upload['file_path']) && empty($payload['external_url'])) throw new RuntimeException('Unggah berkas digital atau cantumkan tautan berkas yang dapat ditinjau petugas.');
		$payload = array_merge($payload, $upload, [
			'public_token' => bin2hex(random_bytes(32)),
			'submitted_by_auth_user_id' => $auth_user_id ? (int) $auth_user_id : null,
			'status' => 'pending',
		]);
		if (! $this->db->insert('digital_donation_submissions', $payload)) throw new RuntimeException('Donasi digital belum dapat disimpan. Silakan coba lagi.');
		return ['id'=>(int)$this->db->insert_id(),'token'=>$payload['public_token']];
	}

	public function find($id) { return $this->db->from('digital_donation_submissions')->where('id',(int)$id)->limit(1)->get()->row_array(); }
	public function find_by_token($token) { return $this->db->from('digital_donation_submissions')->where('public_token',trim((string)$token))->limit(1)->get()->row_array(); }
	public function get_member_submissions($auth_user_id, $limit = 5)
	{
		return $this->db
			->select('id,title,contribution_type,status,admin_note,created_at')
			->from('digital_donation_submissions')
			->where('submitted_by_auth_user_id', (int) $auth_user_id)
			->order_by('id', 'DESC')
			->limit(max(1, min(10, (int) $limit)))
			->get()
			->result_array();
	}
	public function get_admin(array $filters, $limit, $offset)
	{
		$this->admin_query($filters);
		return $this->db->order_by("FIELD(d.status,'pending','reviewing','revision_requested','accepted','rejected')",'',false)->order_by('d.id','DESC')->limit(max(1,min(100,(int)$limit)),max(0,(int)$offset))->get()->result_array();
	}
	public function count_admin(array $filters) { $this->admin_query($filters); return (int)$this->db->count_all_results(); }
	public function stats()
	{
		$rows=$this->db->query("SELECT status,COUNT(*) total FROM digital_donation_submissions GROUP BY status")->result_array(); $out=['total'=>0,'pending'=>0,'reviewing'=>0,'accepted'=>0,'rejected'=>0,'revision_requested'=>0]; foreach($rows as $row){$out[$row['status']]=(int)$row['total'];$out['total']+=(int)$row['total'];} return $out;
	}
	public function review($id, $status, $note, $reviewer_id)
	{
		if (! in_array($status,['pending','reviewing','accepted','revision_requested','rejected'],true)) throw new RuntimeException('Status tindak lanjut tidak valid.');
		$payload=['status'=>$status,'admin_note'=>trim((string)$note)?:null,'reviewed_by'=>$reviewer_id?(int)$reviewer_id:null,'reviewed_at'=>date('Y-m-d H:i:s')];
		if (! $this->db->where('id',(int)$id)->update('digital_donation_submissions',$payload)) throw new RuntimeException('Status donasi tidak dapat diperbarui.');
		return $payload;
	}

	private function admin_query(array $filters)
	{
		$this->db->from('digital_donation_submissions d');
		$q=trim((string)($filters['q']??'')); if($q!=='')$this->db->group_start()->like('d.title',$q)->or_like('d.donor_name',$q)->or_like('d.creator_names',$q)->or_like('d.donor_email',$q)->group_end();
		if (! empty($filters['status'])) $this->db->where('d.status',$filters['status']);
		if (! empty($filters['type'])) $this->db->where('d.contribution_type',$filters['type']);
	}

	private function validate_payload(array $data)
	{
		$name=trim((string)($data['donor_name']??'')); $title=trim((string)($data['title']??'')); $email=trim((string)($data['donor_email']??'')); $phone=trim((string)($data['donor_phone']??''));
		if($name==='')throw new RuntimeException('Nama donor wajib diisi.'); if($title==='')throw new RuntimeException('Judul karya atau koleksi wajib diisi.'); if($email===''&&$phone==='')throw new RuntimeException('Isi minimal email atau nomor WhatsApp agar petugas dapat menghubungi Anda.'); if($email!==''&&!filter_var($email,FILTER_VALIDATE_EMAIL))throw new RuntimeException('Format email tidak valid.');
		$type=(string)($data['contribution_type']??''); if(!in_array($type,['book','manuscript','research','scientific_paper','thesis','dissertation','teaching_material','other'],true))throw new RuntimeException('Pilih jenis kontribusi.');
		$license=(string)($data['license_code']??''); if(!in_array($license,['cc0','cc_by','cc_by_sa','public_domain'],true))throw new RuntimeException('Pilih lisensi bebas yang sesuai.');
		if(empty($data['rights_confirm']))throw new RuntimeException('Pernyataan hak cipta/lisensi harus disetujui sebelum karya dikirim.');
		$url=trim((string)($data['external_url']??'')); if($url!==''&&(!filter_var($url,FILTER_VALIDATE_URL)||!in_array(strtolower((string)parse_url($url,PHP_URL_SCHEME)),['http','https'],true)))throw new RuntimeException('Tautan berkas harus berupa URL HTTP atau HTTPS yang valid.');
		$year=(int)($data['publication_year']??0);
		return ['donor_name'=>$this->clip($name,180),'donor_email'=>$this->blank($email,180),'donor_phone'=>$this->blank($phone,80),'donor_organization'=>$this->blank($data['donor_organization']??'',180),'contribution_type'=>$type,'title'=>$this->clip($title,255),'creator_names'=>$this->blank($data['creator_names']??'',500),'publication_year'=>$year>=1000&&$year<=date('Y')+1?$year:null,'language'=>$this->blank($data['language']??'',120),'description'=>$this->blank_text($data['description']??''),'license_code'=>$license,'rights_statement'=>$this->clip((string)($data['rights_statement']??''),2000) ?: 'Pernyataan lisensi disetujui melalui formulir.','external_url'=>$url?:null];
	}

	private function store_upload(array $file)
	{
		if (empty($file['name'])) return [];
		if ((int)($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK) throw new RuntimeException('Upload berkas gagal. Periksa ukuran berkas dan koneksi, lalu coba lagi.');
		$size=(int)($file['size']??0); if($size<=0||$size>self::MAX_UPLOAD_BYTES)throw new RuntimeException('Berkas digital maksimal 200 MB.');
		$tmp=(string)($file['tmp_name']??''); $finfo=finfo_open(FILEINFO_MIME_TYPE); $mime=$finfo?finfo_file($finfo,$tmp):''; if($finfo)finfo_close($finfo);
		$allowed=['application/pdf'=>'pdf','application/epub+zip'=>'epub','application/msword'=>'doc','application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>'docx','application/vnd.oasis.opendocument.text'=>'odt','text/plain'=>'txt']; if(!isset($allowed[$mime]))throw new RuntimeException('Format berkas belum didukung. Gunakan PDF, EPUB, DOC/DOCX, ODT, atau TXT.');
		$dir='storage/digital-donations/'.date('Y/m');$absolute=FCPATH.str_replace('/',DIRECTORY_SEPARATOR,$dir);if(!is_dir($absolute)&&!mkdir($absolute,0775,true))throw new RuntimeException('Folder penerimaan donasi tidak dapat dibuat.');$name='donasi-'.date('His').'-'.bin2hex(random_bytes(6)).'.'.$allowed[$mime];if(!move_uploaded_file($tmp,$absolute.DIRECTORY_SEPARATOR.$name))throw new RuntimeException('Berkas donasi tidak dapat disimpan.');
		return ['file_path'=>$dir.'/'.$name,'file_original_name'=>$this->clip(basename((string)$file['name']),255),'file_mime_type'=>$mime,'file_size'=>$size];
	}
	private function clip($value,$length){return substr(trim((string)$value),0,$length);} private function blank($value,$length){$v=$this->clip($value,$length);return $v===''?null:$v;} private function blank_text($value){$v=trim((string)$value);return $v===''?null:$v;}
}
