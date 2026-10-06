<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Database_backups extends MY_Controller
{
	public function __construct(){parent::__construct();$this->load->model('Database_backup_model');}
	public function index(){
		$this->require_permission('system.database_backups','view');$this->load->add_package_path(APPPATH.'controllers');
		$this->render('system/database_backups',['title'=>'Backup Database','settings'=>$this->Database_backup_model->settings(),'runs'=>$this->Database_backup_model->recent_runs(),'backup_directory'=>Database_backup_model::BACKUP_DIR,'can_edit'=>$this->can('system.database_backups','edit'),'can_run'=>$this->can('system.database_backups','approve')]);
	}
	public function update(){
		$this->require_permission('system.database_backups','edit');try{$before=$this->Database_backup_model->settings();$after=$this->Database_backup_model->save_settings($this->input->post(null,true),(int)($this->current_user['id']??0));$this->audit_event('system.database_backup.settings','database_backup_settings',1,$before,$after);$this->session->set_flashdata('success','Pengaturan backup database disimpan.');}catch(Throwable$e){$this->session->set_flashdata('error',$e->getMessage());}redirect('system/database-backups');
	}
	public function run_now(){
		$this->require_permission('system.database_backups','approve');try{$id=$this->Database_backup_model->queue_manual((int)($this->current_user['id']??0));$this->audit_event('system.database_backup.queue','database_backup_runs',$id,null,['trigger_type'=>'manual']);$this->session->set_flashdata('success','Backup masuk antrean dan akan diproses oleh cron maksimal satu menit.');}catch(Throwable$e){$this->session->set_flashdata('error',$e->getMessage());}redirect('system/database-backups');
	}
	public function download($id){
		$this->require_permission('system.database_backups','export');$run=$this->Database_backup_model->find_run((int)$id);$file=$run?$this->Database_backup_model->backup_absolute_path($run):null;if(!$file){show_404();return;}$this->audit_event('system.database_backup.download','database_backup_runs',(int)$id,null,['file_name'=>$run['file_name']]);header('X-Content-Type-Options: nosniff');header('Content-Type: application/octet-stream');header('Content-Disposition: attachment; filename="'.preg_replace('/[^A-Za-z0-9._-]/','_',basename($file)).'"');header('Content-Length: '.filesize($file));readfile($file);exit;
	}
}
