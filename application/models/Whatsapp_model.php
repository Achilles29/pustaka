<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Whatsapp_model extends CI_Model
{
	public function session(){ return $this->db->where('id',1)->get('wa_session')->row_array() ?: []; }
	public function automation_settings(){
		$defaults=['id'=>1,'due_reminder_enabled'=>1,'overdue_auto_enabled'=>0,'overdue_repeat_days'=>3,'last_run_at'=>null];
		if(!$this->db->table_exists('wa_automation_settings')) return $defaults;
		return array_merge($defaults,(array)$this->db->where('id',1)->get('wa_automation_settings')->row_array());
	}
	public function save_automation_settings(array $input,$userId=null){
		if(!$this->db->table_exists('wa_automation_settings')) throw new RuntimeException('Pengaturan otomatis WA belum tersedia. Jalankan pembaruan database WA Center.');
		$data=['id'=>1,'due_reminder_enabled'=>!empty($input['due_reminder_enabled'])?1:0,'overdue_auto_enabled'=>!empty($input['overdue_auto_enabled'])?1:0,'overdue_repeat_days'=>max(1,min(30,(int)($input['overdue_repeat_days']??3))),'updated_by'=>(int)$userId?:null];
		$this->db->replace('wa_automation_settings',$data);return $this->automation_settings();
	}
	public function stats(){
		return ['pending'=>(int)$this->db->where('status','PENDING')->count_all_results('wa_outbox'),'failed'=>(int)$this->db->where('status','FAILED')->count_all_results('wa_outbox'),'sent_today'=>(int)$this->db->where('status','SENT')->where('DATE(sent_at)=',date('Y-m-d'),false)->count_all_results('wa_outbox')];
	}
	public function templates(){return $this->db->select('t.*, (SELECT COUNT(*) FROM wa_outbox o WHERE o.template_id=t.id) AS usage_count',false)->from('wa_template t')->order_by('t.category')->order_by('t.name')->get()->result_array();}
	public function queue($eventCode,$phone,$message,array $context=[], $memberId=null,$templateId=null,$scheduledAt=null,$createdBy=null){
		$phone=$this->normalise_phone($phone); if($phone===''||trim($message)==='') return false;
		$this->db->insert('wa_outbox',['event_code'=>$eventCode,'template_id'=>$templateId,'member_id'=>$memberId,'phone_number'=>$phone,'recipient_name'=>$context['member_name']??null,'message'=>$message,'scheduled_at'=>$scheduledAt,'context_json'=>json_encode($context,JSON_UNESCAPED_UNICODE),'created_by'=>$createdBy]);
		return (int)$this->db->insert_id();
	}
	/** Menempatkan template ke outbox. Kunci deduplikasi mencegah pesan otomatis ganda. */
	public function queue_template($templateCode,$phone,array $context=[], $memberId=null,$createdBy=null,$dedupeKey=null){
		$template=$this->db->where('template_code',(string)$templateCode)->where('is_active',1)->get('wa_template')->row_array();
		if(!$template) return false;
		$phone=$this->normalise_phone($phone);if($phone==='') return false;
		if($dedupeKey!==null&&$dedupeKey!==''){
			$exists=$this->db->where('dedupe_key',(string)$dedupeKey)->count_all_results('wa_outbox');
			if($exists>0) return 0;
		}
		$ok=$this->db->insert('wa_outbox',['event_code'=>strtoupper((string)$templateCode),'template_id'=>(int)$template['id'],'member_id'=>$memberId?:null,'phone_number'=>$phone,'recipient_name'=>$context['member_name']??null,'message'=>$this->resolve($template['body'],$context),'context_json'=>json_encode($context,JSON_UNESCAPED_UNICODE),'created_by'=>$createdBy?:null,'dedupe_key'=>$dedupeKey?:null]);
		if(!$ok){$error=$this->db->error();if((int)($error['code']??0)===1062)return 0;return false;}
		return (int)$this->db->insert_id();
	}
	public function queue_overdue_loan($loanItemId,$createdBy=null,$manual=false){
		$row=$this->loan_notification_row((int)$loanItemId);if(!$row||empty($row['phone'])) throw new RuntimeException('Nomor WhatsApp member belum tersedia atau transaksi tidak lagi aktif.');
		$late=max(1,(int)$row['late_days']);$context=$this->loan_context($row)+['late_days'=>$late];
		$key=$manual?null:'overdue-'.$row['id'].'-'.date('Ymd');
		$id=$this->queue_template('loan_overdue',$row['phone'],$context,(int)$row['member_id'],$createdBy,$key);
		if($id===0) return ['queued'=>false,'message'=>'Pemberitahuan keterlambatan untuk hari ini sudah masuk antrian.'];
		if(!$id) throw new RuntimeException('Template pengingat keterlambatan tidak aktif atau nomor WhatsApp tidak valid.');
		return ['queued'=>true,'message'=>'Pemberitahuan keterlambatan dimasukkan ke antrian WA.'];
	}
	public function run_automations(){
		$settings=$this->automation_settings();$result=['due_reminders'=>0,'overdue_notices'=>0,'skipped'=>[]];
		if(!$this->db->table_exists('loan_transaction_items')||!$this->db->table_exists('members')) return $result;
		$active="li.actual_return_at IS NULL AND li.local_return_at IS NULL AND UPPER(COALESCE(li.loan_status, '')) = 'LOAN'";
		if(!empty($settings['due_reminder_enabled'])){
			$rows=$this->db->query("SELECT li.id,li.member_id,COALESCE(li.local_due_date,li.due_date) AS due_date,m.full_name,m.member_no,m.phone,b.title,DATEDIFF(COALESCE(li.local_due_date,li.due_date),CURDATE()) AS days_left FROM loan_transaction_items li JOIN members m ON m.id=li.member_id LEFT JOIN book_items bi ON bi.id=li.book_item_id LEFT JOIN books b ON b.id=bi.book_id WHERE {$active} AND DATE(COALESCE(li.local_due_date,li.due_date))=DATE_ADD(CURDATE(),INTERVAL 1 DAY) AND m.phone IS NOT NULL AND m.phone<>''")->result_array();
			foreach($rows as $row){$id=$this->queue_template('loan_due_reminder',$row['phone'],$this->loan_context($row),(int)$row['member_id'],null,'due-'.$row['id'].'-'.date('Ymd',strtotime($row['due_date'])));if($id>0)$result['due_reminders']++;}
		}
		if(!empty($settings['overdue_auto_enabled'])){
			$repeat=max(1,(int)$settings['overdue_repeat_days']);$rows=$this->db->query("SELECT li.id,li.member_id,COALESCE(li.local_due_date,li.due_date) AS due_date,m.full_name,m.member_no,m.phone,b.title,DATEDIFF(CURDATE(),DATE(COALESCE(li.local_due_date,li.due_date))) AS late_days FROM loan_transaction_items li JOIN members m ON m.id=li.member_id LEFT JOIN book_items bi ON bi.id=li.book_item_id LEFT JOIN books b ON b.id=bi.book_id WHERE {$active} AND COALESCE(li.local_due_date,li.due_date)<CURDATE() AND m.phone IS NOT NULL AND m.phone<>''")->result_array();
			foreach($rows as $row){$late=max(1,(int)$row['late_days']);$cycle=(int)floor(($late-1)/$repeat);$id=$this->queue_template('loan_overdue',$row['phone'],$this->loan_context($row)+['late_days'=>$late],(int)$row['member_id'],null,'overdue-'.$row['id'].'-'.$cycle);if($id>0)$result['overdue_notices']++;}
		}
		if($this->db->table_exists('wa_automation_settings'))$this->db->where('id',1)->update('wa_automation_settings',['last_run_at'=>date('Y-m-d H:i:s')]);
		return $result;
	}
	private function loan_notification_row($loanItemId){
		$active="li.actual_return_at IS NULL AND li.local_return_at IS NULL AND UPPER(COALESCE(li.loan_status, '')) = 'LOAN'";
		return $this->db->query("SELECT li.id,li.member_id,COALESCE(li.local_due_date,li.due_date) AS due_date,m.full_name,m.member_no,m.phone,b.title,DATEDIFF(CURDATE(),DATE(COALESCE(li.local_due_date,li.due_date))) AS late_days FROM loan_transaction_items li JOIN members m ON m.id=li.member_id LEFT JOIN book_items bi ON bi.id=li.book_item_id LEFT JOIN books b ON b.id=bi.book_id WHERE li.id=? AND {$active}",[(int)$loanItemId])->row_array();
	}
	private function loan_context(array $row){return ['member_name'=>$row['full_name']??'Pemustaka','member_no'=>$row['member_no']??'-','book_title'=>$row['title']??'Koleksi perpustakaan','due_date'=>!empty($row['due_date'])?date('d M Y',strtotime($row['due_date'])):'-'];}
	public function resolve($body,array $vars=[]){return preg_replace_callback('/\{([a-z_]+)\}/i',function($m)use($vars){return isset($vars[$m[1]])?(string)$vars[$m[1]]:$m[0];},$body);}
	public function normalise_phone($phone){$phone=preg_replace('/\D+/','',(string)$phone);if(strpos($phone,'0')===0)$phone='62'.substr($phone,1);return preg_match('/^62\d{8,14}$/',$phone)?$phone:'';}
}
