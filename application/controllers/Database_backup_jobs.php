<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Database_backup_jobs extends CI_Controller
{
	public function __construct(){parent::__construct();if(!is_cli()){show_404();exit;}$this->load->model('Database_backup_model');}
	public function run(){
		$lock=fopen(sys_get_temp_dir().'/pustaka-database-backup.lock','c');if(!$lock||!flock($lock,LOCK_EX|LOCK_NB)){echo "Backup worker already running.\n";return;}
		$this->Database_backup_model->queue_scheduled_if_due();$processed=0;while($processed<2){try{$result=$this->Database_backup_model->process_next();if(!$result)break;echo 'Backup #'.$result['run_id'].' selesai: '.$result['name']."\n";}catch(Throwable$e){fwrite(STDERR,$e->getMessage()."\n");}$processed++;}echo "Processed: {$processed}.\n";flock($lock,LOCK_UN);fclose($lock);
	}
}
