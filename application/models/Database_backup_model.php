<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Database_backup_model extends CI_Model
{
	const BACKUP_DIR = 'storage/digital-donations/.system/database-backups';

	public function settings()
	{
		$defaults=['id'=>1,'is_enabled'=>1,'frequency'=>'daily','run_time'=>'02:00:00','day_of_week'=>0,'day_of_month'=>1,'retention_count'=>14,'compress_backup'=>1,'last_scheduled_key'=>null];
		if(!$this->db->table_exists('database_backup_settings'))return $defaults;
		return array_merge($defaults,(array)$this->db->where('id',1)->get('database_backup_settings')->row_array());
	}

	public function save_settings(array $input,$userId=null)
	{
		$frequency=in_array(($input['frequency']??''),['daily','weekly','monthly'],true)?$input['frequency']:'daily';
		$time=preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/',(string)($input['run_time']??''))?$input['run_time'].':00':'02:00:00';
		$current=$this->settings();
		$data=['id'=>1,'is_enabled'=>empty($input['is_enabled'])?0:1,'frequency'=>$frequency,'run_time'=>$time,'day_of_week'=>max(0,min(6,(int)($input['day_of_week']??0))),'day_of_month'=>max(1,min(28,(int)($input['day_of_month']??1))),'retention_count'=>max(2,min(365,(int)($input['retention_count']??14))),'compress_backup'=>empty($input['compress_backup'])?0:1,'last_scheduled_key'=>$current['last_scheduled_key']??null,'updated_by'=>$userId?(int)$userId:null];
		$this->db->replace('database_backup_settings',$data);return $this->settings();
	}

	public function recent_runs($limit=30)
	{
		if(!$this->db->table_exists('database_backup_runs'))return [];
		return $this->db->order_by('id','DESC')->limit(max(1,min(100,(int)$limit)))->get('database_backup_runs')->result_array();
	}

	public function queue_manual($userId=null)
	{
		if($this->db->where_in('status',['pending','running'])->count_all_results('database_backup_runs')>0)throw new RuntimeException('Masih ada proses backup dalam antrean atau sedang berjalan.');
		$this->db->insert('database_backup_runs',['trigger_type'=>'manual','status'=>'pending','requested_by'=>$userId?(int)$userId:null]);return (int)$this->db->insert_id();
	}

	public function queue_scheduled_if_due()
	{
		$s=$this->settings();if(empty($s['is_enabled']))return false;
		$now=new DateTimeImmutable('now');$scheduled=$this->scheduled_time($now,$s);if($now<$scheduled)return false;
		$key=$this->period_key($now,$s['frequency']);if(($s['last_scheduled_key']??'')===$key)return false;
		$this->db->trans_start();
		$this->db->where('id',1)->where("COALESCE(last_scheduled_key,'') !=",$key)->update('database_backup_settings',['last_scheduled_key'=>$key]);
		if($this->db->affected_rows()===1)$this->db->insert('database_backup_runs',['trigger_type'=>'scheduled','status'=>'pending']);
		$this->db->trans_complete();return $this->db->affected_rows()>=0;
	}

	public function process_next()
	{
		$run=$this->db->where('status','pending')->order_by('id','ASC')->limit(1)->get('database_backup_runs')->row_array();if(!$run)return null;
		$this->db->where('id',(int)$run['id'])->where('status','pending')->update('database_backup_runs',['status'=>'running','started_at'=>date('Y-m-d H:i:s')]);if($this->db->affected_rows()!==1)return null;
		try{$result=$this->create_dump((int)$run['id']);$this->db->where('id',(int)$run['id'])->update('database_backup_runs',['status'=>'success','file_name'=>$result['name'],'file_path'=>$result['path'],'file_size'=>$result['size'],'message'=>'Backup terverifikasi dan selesai.','finished_at'=>date('Y-m-d H:i:s')]);$this->enforce_retention();return ['ok'=>true,'run_id'=>(int)$run['id']]+$result;}
		catch(Throwable $e){$this->db->where('id',(int)$run['id'])->update('database_backup_runs',['status'=>'failed','message'=>mb_substr($e->getMessage(),0,2000),'finished_at'=>date('Y-m-d H:i:s')]);throw $e;}
	}

	public function find_run($id){return $this->db->where('id',(int)$id)->limit(1)->get('database_backup_runs')->row_array();}
	public function backup_absolute_path(array $run){$base=realpath($this->directory());$file=realpath(FCPATH.str_replace(['/','\\'],DIRECTORY_SEPARATOR,(string)($run['file_path']??'')));return $base&&$file&&strpos($file,$base.DIRECTORY_SEPARATOR)===0&&is_file($file)?$file:null;}
	public function directory(){return FCPATH.str_replace('/',DIRECTORY_SEPARATOR,self::BACKUP_DIR);}

	private function create_dump($runId)
	{
		$dir=$this->directory();if(!is_dir($dir)&&!mkdir($dir,0700,true))throw new RuntimeException('Folder backup tidak dapat dibuat.');@chmod($dir,0700);
		$config=$this->db->conn_id;$dbName=$this->db->database;$stamp=date('Ymd-His');$base='pustaka-'.$stamp.'-'.substr(hash('sha256',$runId.'|'.microtime(true)),0,8);$sql=$dir.DIRECTORY_SEPARATOR.$base.'.sql';$part=$sql.'.part';$err=$dir.DIRECTORY_SEPARATOR.$base.'.err';$defaults=tempnam(sys_get_temp_dir(),'pustaka-db-');if(!$defaults)throw new RuntimeException('File kredensial sementara tidak dapat dibuat.');chmod($defaults,0600);
		$host=$this->db->hostname==='localhost'?'127.0.0.1':$this->db->hostname;$user=$this->db->username;$pass=$this->db->password;$port=(int)($this->db->port?:3306);
		file_put_contents($defaults,"[client]\nhost=".$this->ini_value($host)."\nport={$port}\nuser=".$this->ini_value($user)."\npassword=".$this->ini_value($pass)."\ndefault-character-set=utf8mb4\n",LOCK_EX);
		$cmd='timeout 1800s mariadb-dump --defaults-extra-file='.escapeshellarg($defaults).' --single-transaction --quick --routines --triggers --events --hex-blob --databases '.escapeshellarg($dbName).' > '.escapeshellarg($part).' 2> '.escapeshellarg($err);$output=[];$code=1;exec($cmd,$output,$code);@unlink($defaults);
		if($code!==0||!is_file($part)||filesize($part)<100){$message=is_file($err)?trim((string)file_get_contents($err)):'mariadb-dump gagal';@unlink($part);@unlink($err);throw new RuntimeException('Backup database gagal: '.$message);}@unlink($err);rename($part,$sql);chmod($sql,0600);
		$path=$sql;if(!empty($this->settings()['compress_backup'])){$gz=$sql.'.gz';$in=fopen($sql,'rb');$out=gzopen($gz,'wb9');if(!$in||!$out)throw new RuntimeException('Kompresi backup gagal.');while(!feof($in))gzwrite($out,fread($in,1024*1024));fclose($in);gzclose($out);chmod($gz,0600);@unlink($sql);$path=$gz;}
		return ['name'=>basename($path),'path'=>self::BACKUP_DIR.'/'.basename($path),'size'=>(int)filesize($path)];
	}

	private function enforce_retention()
	{
		$keep=(int)$this->settings()['retention_count'];$runs=$this->db->where('status','success')->where('file_path IS NOT NULL',null,false)->order_by('id','DESC')->get('database_backup_runs')->result_array();foreach(array_slice($runs,$keep)as$row){$file=$this->backup_absolute_path($row);if($file)@unlink($file);$this->db->where('id',(int)$row['id'])->update('database_backup_runs',['file_name'=>null,'file_path'=>null,'file_size'=>null,'message'=>'File dihapus otomatis sesuai retensi.']);}
	}
	private function period_key(DateTimeImmutable $now,$frequency){return $frequency==='weekly'?$now->format('o-W'):($frequency==='monthly'?$now->format('Y-m'):$now->format('Y-m-d'));}
	private function scheduled_time(DateTimeImmutable $now,array$s){[$h,$m]=array_map('intval',explode(':',$s['run_time']));if($s['frequency']==='weekly'){$date=$now->modify('sunday this week')->modify('+'.(int)$s['day_of_week'].' days');}elseif($s['frequency']==='monthly'){$date=$now->setDate((int)$now->format('Y'),(int)$now->format('m'),(int)$s['day_of_month']);}else{$date=$now;}return $date->setTime($h,$m);}
	private function ini_value($value){return '"'.str_replace(['\\','"'],['\\\\','\\"'],(string)$value).'"';}
}
