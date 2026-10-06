<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Network_reports extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_permission('reports.network','view');
        // A permission accidentally assigned to a local role must not create global access.
        $codes=array_column($this->user_roles,'code');
        if(!empty($this->current_user['is_library_admin'])||(!in_array('ADMIN',$codes,true)&&!$this->is_superadmin())){show_error('Laporan gabungan hanya untuk pengelola kabupaten.',403);exit;}
        $this->load->model('Network_report_model','network_report');
    }

    private function payload()
    {
        try{$filters=$this->network_report->filters((array)$this->input->get(null,true));}
        catch(InvalidArgumentException$e){show_error($e->getMessage(),400);exit;}
        return $this->network_report->report($filters);
    }

    public function index()
    {
        $report=$this->payload();$per=(int)$this->input->get('per_page');if(!in_array($per,[10,25,50,100],true))$per=25;
        $pages=max(1,(int)ceil(count($report['groups'])/$per));$page=min($pages,max(1,(int)$this->input->get('page')));
        $this->render('reports/network',['title'=>'Laporan Gabungan Perpustakaan','report'=>$report,'metrics'=>$this->network_report->metrics(),'can_export'=>$this->can('reports.network','export'),'per'=>$per,'page'=>$page,'pages'=>$pages]);
    }

    public function export($format='xlsx')
    {
        $this->require_permission('reports.network','export');
        if(!in_array($format,['csv','xlsx'],true)){show_404();return;}
        $report=$this->payload();$columns=['label'=>'Kelompok','libraries'=>'Perpustakaan']+$this->network_report->metrics();
        $rows=[];foreach($report['groups']as$row){$values=[];foreach($columns as$key=>$label)$values[]=$row[$key];$rows[]=$values;}
        $name='rekap-perpustakaan-'.$report['filters']['year'].'-'.$report['filters']['group'];
        header('Cache-Control: private, no-store');
        if($format==='xlsx'){
            $this->load->library('Catalog_xlsx');$path=$this->catalog_xlsx->build('Rekap Perpustakaan',array_values($columns),$rows);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');header('Content-Disposition: attachment; filename="'.$name.'.xlsx"');
            try{readfile($path);}finally{unlink($path);}
        }else{
            header('Content-Type: text/csv; charset=utf-8');header('Content-Disposition: attachment; filename="'.$name.'.csv"');
            $out=fopen('php://output','w');fwrite($out,"\xEF\xBB\xBF");fputcsv($out,array_values($columns));
            foreach($rows as$row)fputcsv($out,array_map(function($v){return is_string($v)&&preg_match('/^\s*[=+@-]/u',$v)?"'".$v:$v;},$row));fclose($out);
        }
    }
}
