<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Password_reset_model extends CI_Model
{
	public function __construct(){parent::__construct();$this->load->library('encryption');}

	public function create_request($identifier,$phone,$ip=null)
	{
		if(!$this->db->table_exists('password_reset_requests'))throw new RuntimeException('Layanan reset password belum siap. Hubungi petugas.');
		$identifier=trim((string)$identifier);$phone=$this->normalise_phone($phone);
		if($identifier===''||$phone==='')throw new InvalidArgumentException('Isi identitas akun dan nomor WhatsApp yang valid.');
		$row=$this->db->select('u.id user_id,u.username,u.full_name,u.status,m.id member_id,m.member_no,m.identity_number,COALESCE(NULLIF(m.phone,\'\'),u.phone) phone',false)->from('auth_user u')->join('members m','m.auth_user_id=u.id AND m.deleted_at IS NULL','left',false)->where('m.id IS NOT NULL',null,false)->group_start()->where('u.username',$identifier)->or_where('u.email',$identifier)->or_where('m.member_no',$identifier)->or_where('m.identity_number',$identifier)->group_end()->limit(1)->get()->row_array();
		if(!$row||$row['status']!=='active'||$this->normalise_phone($row['phone']??'')!==$phone)throw new RuntimeException('Data akun dan nomor WhatsApp tidak cocok dengan data member aktif.');
		$recent=(int)$this->db->where('user_id',(int)$row['user_id'])->where('created_at >=',date('Y-m-d H:i:s',time()-3600))->count_all_results('password_reset_requests');
		if($recent>=3)throw new RuntimeException('Batas permintaan reset tercapai. Coba kembali satu jam lagi.');
		$code='RST-'.date('ymd').'-'.strtoupper(substr(bin2hex(random_bytes(5)),0,8));$token=bin2hex(random_bytes(32));$statusToken=bin2hex(random_bytes(32));
		$this->db->trans_start();
		$this->db->insert('password_reset_requests',['request_code'=>$code,'public_token'=>$token,'status_token'=>$statusToken,'user_id'=>(int)$row['user_id'],'member_id'=>!empty($row['member_id'])?(int)$row['member_id']:null,'phone_number'=>$phone,'password_cipher'=>null,'requester_ip'=>$ip?:null,'expires_at'=>date('Y-m-d H:i:s',time()+86400)]);
		$id=(int)$this->db->insert_id();$this->db->trans_complete();if(!$this->db->trans_status())throw new RuntimeException('Permintaan reset belum dapat diproses.');
		$delivery='unavailable';$outboxId=null;
		if($this->wa_is_active()){
			$link=base_url('auth/reset_password/'.$token);$this->load->model('Whatsapp_model');$message="Halo ".($row['full_name']?:$row['username']).",\n\nAda permintaan reset password Pustaka Digital Rembang. Buat password baru melalui tautan sekali pakai berikut:\n{$link}\n\nKode permintaan: *{$code}*\nTautan berlaku 24 jam. Abaikan pesan ini jika Anda tidak meminta reset.";
			$outboxId=$this->Whatsapp_model->queue('PASSWORD_RESET',$phone,$message,['member_name'=>$row['full_name'],'request_code'=>$code],$row['member_id']??null);
			$delivery=$outboxId?'queued':'failed';$this->db->where('id',$id)->update('password_reset_requests',['delivery_status'=>$delivery,'approval_status'=>$outboxId?'wa_sent':'pending','wa_outbox_id'=>$outboxId?:null]);
		}
		return ['status_token'=>$statusToken,'request_code'=>$code,'delivery_status'=>$delivery];
	}

	public function get_by_token($token)
	{
		$row=$this->base_query()->where('r.status_token',trim((string)$token))->get()->row_array();return $this->status_row($row);
	}

	public function find_status($code,$phone)
	{
		$phone=$this->normalise_phone($phone);if($phone==='')throw new InvalidArgumentException('Nomor WhatsApp tidak valid.');
		$row=$this->base_query()->where('r.request_code',strtoupper(trim((string)$code)))->where('r.phone_number',$phone)->get()->row_array();
		if(!$row)throw new RuntimeException('Kode permintaan dan nomor WhatsApp tidak cocok.');return $this->status_row($row);
	}

	public function get_reset_request($token)
	{
		$row=$this->base_query()->where('r.public_token',trim((string)$token))->get()->row_array();
		if(!$row)return null;$row=$this->status_row($row);return $row;
	}

	public function complete_reset($token,$password,$confirmation)
	{
		$password=(string)$password;if($password!==$confirmation)throw new InvalidArgumentException('Konfirmasi password tidak sama.');
		if(strlen($password)<8||strlen($password)>72)throw new InvalidArgumentException('Password harus terdiri dari 8 sampai 72 karakter.');
		$this->db->trans_begin();
		$row=$this->db->query('SELECT id,user_id,expires_at,completed_at FROM password_reset_requests WHERE public_token=? LIMIT 1 FOR UPDATE',[trim((string)$token)])->row_array();
		if(!$row||!empty($row['completed_at'])||strtotime($row['expires_at'])<time()){$this->db->trans_rollback();throw new RuntimeException('Tautan reset tidak valid, sudah digunakan, atau kedaluwarsa.');}
		$this->db->where('id',(int)$row['user_id'])->update('auth_user',['password_hash'=>password_hash($password,PASSWORD_BCRYPT),'force_password_change'=>0]);
		$this->db->where('id',(int)$row['id'])->update('password_reset_requests',['completed_at'=>date('Y-m-d H:i:s')]);
		if(!$this->db->trans_status()){$this->db->trans_rollback();throw new RuntimeException('Password belum dapat diperbarui.');}$this->db->trans_commit();return true;
	}

	public function admin_requests($limit=200)
	{
		return $this->db->select('r.*,u.username,u.full_name,m.member_no,m.identity_number,m.phone,o.status wa_status,o.sent_at')->from('password_reset_requests r')->join('auth_user u','u.id=r.user_id')->join('members m','m.id=r.member_id','left')->join('wa_outbox o','o.id=r.wa_outbox_id','left')->order_by("FIELD(r.approval_status,'pending','approved','wa_sent','rejected')",'',false)->order_by('r.created_at','DESC')->limit((int)$limit)->get()->result_array();
	}

	public function admin_review($id,$action,$reviewerId,$note='')
	{
		$id=(int)$id;$action=(string)$action;$note=trim((string)$note);if(!in_array($action,['approve','reject'],true))throw new InvalidArgumentException('Aksi verifikasi tidak valid.');
		$this->db->trans_begin();$row=$this->db->query('SELECT * FROM password_reset_requests WHERE id=? LIMIT 1 FOR UPDATE',[$id])->row_array();
		if(!$row){$this->db->trans_rollback();throw new RuntimeException('Permintaan reset tidak ditemukan.');}
		if($row['approval_status']!=='pending'){$this->db->trans_rollback();throw new RuntimeException('Permintaan ini sudah diproses atau tautan WA sudah dikirim.');}
		$update=['approval_status'=>$action==='approve'?'approved':'rejected','reviewed_by'=>(int)$reviewerId,'reviewed_at'=>date('Y-m-d H:i:s'),'review_note'=>$note?:null];
		if($action==='approve'){$password=$this->temporary_password();$cipher=$this->encryption->encrypt($password);if(!$cipher){$this->db->trans_rollback();throw new RuntimeException('Password baru belum dapat diamankan.');}$this->db->where('id',(int)$row['user_id'])->update('auth_user',['password_hash'=>password_hash($password,PASSWORD_BCRYPT),'force_password_change'=>1]);$update['password_cipher']=$cipher;$update['expires_at']=date('Y-m-d H:i:s',time()+86400);}
		$this->db->where('id',$id)->update('password_reset_requests',$update);if(!$this->db->trans_status()){$this->db->trans_rollback();throw new RuntimeException('Verifikasi reset belum dapat disimpan.');}$this->db->trans_commit();return $update['approval_status'];
	}

	private function base_query(){return $this->db->select('r.*,u.username,u.full_name,o.status wa_status,o.sent_at')->from('password_reset_requests r')->join('auth_user u','u.id=r.user_id')->join('wa_outbox o','o.id=r.wa_outbox_id','left');}
	private function status_row($row){if(!$row)return null;$row['is_expired']=strtotime($row['expires_at'])<time();$row['is_completed']=!empty($row['completed_at']);$row['approved_password']=null;if($row['approval_status']==='approved'&&!$row['is_expired']&&!empty($row['password_cipher']))$row['approved_password']=$this->encryption->decrypt($row['password_cipher']);if(!$row['viewed_at'])$this->db->where('id',(int)$row['id'])->update('password_reset_requests',['viewed_at'=>date('Y-m-d H:i:s')]);unset($row['password_cipher'],$row['public_token'],$row['status_token']);return $row;}
	private function wa_is_active(){if(!$this->db->table_exists('wa_session')||!$this->db->table_exists('wa_outbox'))return false;$s=$this->db->where('id',1)->get('wa_session')->row_array();return $s&&!empty($s['is_enabled'])&&strtoupper((string)$s['status'])==='CONNECTED';}
	private function normalise_phone($phone){$phone=preg_replace('/\D+/','',(string)$phone);if(strpos($phone,'0')===0)$phone='62'.substr($phone,1);return preg_match('/^62\d{8,14}$/',$phone)?$phone:'';}
	private function temporary_password(){$chars='ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';$value='Pdr-';for($i=0;$i<10;$i++)$value.=$chars[random_int(0,strlen($chars)-1)];return $value;}
}
