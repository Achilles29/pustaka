<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Central, aggregate-only read model. Never infer a library from a person's address. */
class Network_report_model extends CI_Model
{
    public function metrics()
    {
        return ['titles'=>'Judul lokal','physical'=>'Judul fisik','digital'=>'Judul digital','hybrid'=>'Judul hibrida','unclassified'=>'Jenis belum diketahui','items'=>'Eksemplar fisik','members'=>'Catatan keanggotaan','loans'=>'Eksemplar dipinjam','returns'=>'Eksemplar kembali','active'=>'Pinjaman aktif kini','overdue'=>'Terlambat kini','offline'=>'Kunjungan offline (orang)','online'=>'Kunjungan online (orang)','entries'=>'Entri kunjungan'];
    }

    public function filters(array $input)
    {
        foreach($input as$value)if(!is_scalar($value)&&$value!==null)throw new InvalidArgumentException('Filter tidak valid.');
        $year=(string)($input['year']??date('Y'));$month=(string)($input['month']??'0');
        if(!ctype_digit($year)||(int)$year<2000||(int)$year>2100||!ctype_digit($month)||(int)$month>12)throw new InvalidArgumentException('Tahun atau bulan tidak valid.');
        $out=['year'=>(int)$year,'month'=>(int)$month];
        foreach(['group'=>['library','type','district','village'],'source'=>['all','legacy','network'],'status'=>['all','active','inactive','pending']]as$key=>$allowed){$value=(string)($input[$key]??$allowed[0]);if(!in_array($value,$allowed,true))throw new InvalidArgumentException('Filter tidak dikenal.');$out[$key]=$value;}
        foreach(['library_id','type_id','district_id','village_id']as$key){$value=(string)($input[$key]??'0');if($key==='library_id'&&$value==='unassigned'){$out[$key]=-1;continue;}if(!ctype_digit($value)||strlen($value)>10)throw new InvalidArgumentException('Filter wilayah/perpustakaan tidak valid.');$out[$key]=(int)$value;}
        $out['q']=mb_substr(trim((string)($input['q']??'')),0,180);
        return $out;
    }

    public function options()
    {
        $libraries=$this->db->select('l.id,l.code,l.name,l.library_type_id,l.district_id,l.village_id,l.status,COALESCE(d.name,l.district) district,COALESCE(v.name,l.village) village,COALESCE(t.name,\'Belum diketahui\') type',false)->from('libraries l')->join('library_types t','t.id=l.library_type_id','left')->join('ref_districts d','d.id=l.district_id','left')->join('ref_villages v','v.id=l.village_id','left')->order_by('l.name')->get()->result_array();
        $districts=[];$villages=[];$types=[];
        foreach($libraries as$l){if($l['district_id'])$districts[$l['district_id']]=$l['district'];if($l['village_id'])$villages[$l['village_id']]=['name'=>$l['village'],'district_id'=>(int)$l['district_id'],'district'=>$l['district']];$types[$l['library_type_id']]=$l['type'];}
        asort($districts);asort($types);uasort($villages,function($a,$b){return [$a['district'],$a['name']]<=>[$b['district'],$b['name']];});
        return compact('libraries','districts','villages','types');
    }

    private function selected(array $l,array $f)
    {
        if($f['library_id']===-1&&$l['id']!==0)return false;
        if($f['library_id']>0&&(int)$l['id']!==$f['library_id'])return false;
        foreach(['type_id'=>'library_type_id','district_id'=>'district_id','village_id'=>'village_id']as$key=>$field)if($f[$key]&&(int)$l[$field]!==$f[$key])return false;
        if($f['status']!=='all'&&$l['status']!==$f['status'])return false;
        return $f['q']===''||mb_stripos($l['name'].' '.$l['code'].' '.$l['district'].' '.$l['village'],$f['q'])!==false;
    }

    /** A valid ISBN is a bibliographic grouping hint, not proof of identical physical assets. */
    public function isbn_key($value)
    {
        $isbn=strtoupper(preg_replace('/[\s-]+/u','',trim((string)$value)));
        if(preg_match('/^[0-9]{9}[0-9X]$/D',$isbn)){
            $sum=0;for($i=0;$i<10;$i++)$sum+=(10-$i)*($isbn[$i]==='X'?10:(int)$isbn[$i]);if($sum%11)return null;
            $prefix='978'.substr($isbn,0,9);$sum=0;for($i=0;$i<12;$i++)$sum+=(int)$prefix[$i]*($i%2?3:1);return $prefix.((10-$sum%10)%10);
        }
        if(!preg_match('/^97[89][0-9]{10}$/D',$isbn))return null;
        $sum=0;for($i=0;$i<13;$i++)$sum+=(int)$isbn[$i]*($i%2?3:1);return $sum%10?null:$isbn;
    }

    public function report(array $f)
    {
        $owned=!$this->db->trans_active();
        if($owned&&!$this->db->trans_begin())throw new RuntimeException('Laporan belum dapat dimuat.');
        try{
            $result=$this->build_report($f);
            if($owned&&(!$this->db->trans_status()||!$this->db->trans_commit()))throw new RuntimeException('Laporan belum dapat diselesaikan.');
            return $result;
        }catch(Throwable$e){if($owned)$this->db->trans_rollback();throw $e;}
    }

    private function build_report(array $f)
    {
        $options=$this->options();$zero=array_fill_keys(array_keys($this->metrics()),0);
        $libraries=array_column($options['libraries'],null,'id');
        $libraries[0]=['id'=>0,'name'=>'Belum terpetakan','code'=>'—','library_type_id'=>0,'district_id'=>0,'village_id'=>0,'status'=>'unassigned','district'=>'Belum terpetakan','village'=>'Belum terpetakan','type'=>'Belum terpetakan'];
        $rows=[];foreach($libraries as$id=>$l)if($this->selected($l,$f))$rows[$id]=$l+$zero;
        $start=sprintf('%04d-%02d-01 00:00:00',$f['year'],$f['month']?:1);
        $end=(new DateTimeImmutable($start))->modify($f['month']?'+1 month':'+1 year')->format('Y-m-d H:i:s');
        $sources=['legacy'=>['name'=>'Kabupaten / data lama']+$zero,'network'=>['name'=>'Operasional jejaring']+$zero];$months=[];
        for($m=$f['month']?:1;$m<=($f['month']?:12);$m++)$months[sprintf('%04d-%02d',$f['year'],$m)]=['loans'=>0,'returns'=>0,'offline'=>0,'online'=>0,'entries'=>0];
        $records=[];$isbns=[];$unidentified=[];
        $add=function($owner,$source,array $values,$month=null)use(&$rows,&$sources,&$months){
            $owner=(int)$owner;if(!isset($rows[$owner]))return;
            foreach($values as$key=>$value){$rows[$owner][$key]+=(int)$value;$sources[$source][$key]+=(int)$value;if($month!==null&&isset($months[$month][$key]))$months[$month][$key]+=(int)$value;}
        };
        $book=function($source,$row)use(&$records,&$isbns,&$unidentified,&$rows,$add){
            $owner=(int)$row['owner'];if(!isset($rows[$owner]))return;
            $key=$source.':'.$row['id'];$records[$key]=true;$isbn=$this->isbn_key($row['isbn']);if($isbn)$isbns[$isbn]=true;else$unidentified[$key]=true;
            $physical=(int)$row['physical'];$digital=(int)$row['digital'];
            $add($owner,$source,['titles'=>1,'physical'=>$physical,'digital'=>$digital,'hybrid'=>($physical&&$digital)?1:0,'unclassified'=>(!$physical&&!$digital)?1:0,'items'=>$row['items']]);
        };
        if($f['source']!=='network'){
            $digital="(LOWER(TRIM(COALESCE(i.collection_type,''))) IN ('ebook','e-book','e book','buku digital','digital','audiobook') OR LOWER(TRIM(COALESCE(i.media_name,''))) IN ('digital','pdf','epub','ebook','e-book','audiobook'))";
            $sql="SELECT b.id,b.isbn,COALESCE(l.id,0) owner,MAX(i.id IS NOT NULL AND NOT $digital) physical,CASE WHEN MAX(i.id IS NOT NULL AND $digital)=1 OR EXISTS(SELECT 1 FROM digital_assets a WHERE a.book_id=b.id AND a.status='active') THEN 1 ELSE 0 END digital,SUM(i.id IS NOT NULL AND NOT $digital) items FROM books b LEFT JOIN book_items i ON i.book_id=b.id AND i.deleted_at IS NULL LEFT JOIN libraries l ON l.id=i.library_id WHERE b.deleted_at IS NULL GROUP BY b.id,b.isbn,l.id";
            foreach($this->db->query($sql)->result_array()as$r)$book('legacy',$r);
            // members has no institution ownership column: never infer from residence or school name.
            $add(0,'legacy',['members'=>(int)$this->db->where('deleted_at IS NULL',null,false)->count_all_results('members')]);
            $base=' FROM loan_transaction_items n LEFT JOIN book_items i ON i.id=n.book_item_id LEFT JOIN libraries l ON l.id=i.library_id';
            foreach(['loans'=>'n.loan_date','returns'=>'COALESCE(n.actual_return_at,n.local_return_at)']as$metric=>$date){
                foreach($this->db->query("SELECT COALESCE(l.id,0) owner,DATE_FORMAT($date,'%Y-%m') month,COUNT(*) total $base WHERE $date>=? AND $date<? GROUP BY l.id,month",[$start,$end])->result_array()as$r)$add($r['owner'],'legacy',[$metric=>$r['total']],$r['month']);
            }
            foreach($this->db->query("SELECT COALESCE(l.id,0) owner,COUNT(*) active,SUM(COALESCE(n.local_due_date,n.due_date)<NOW()) overdue $base WHERE n.actual_return_at IS NULL AND n.local_return_at IS NULL AND UPPER(COALESCE(n.loan_status,''))='LOAN' GROUP BY l.id")->result_array()as$r)$add($r['owner'],'legacy',['active'=>$r['active'],'overdue'=>$r['overdue']]);
            $online="n.visit_channel IN ('member_dashboard','digital_access')";
            foreach($this->db->query("SELECT COALESCE(l.id,0) owner,DATE_FORMAT(n.visited_at,'%Y-%m') month,SUM(CASE WHEN $online THEN n.visitor_count ELSE 0 END) online,SUM(CASE WHEN $online THEN 0 ELSE n.visitor_count END) offline,COUNT(*) entries FROM member_visits n LEFT JOIN libraries l ON l.id=n.library_id WHERE n.visited_at>=? AND n.visited_at<? GROUP BY l.id,month",[$start,$end])->result_array()as$r)$add($r['owner'],'legacy',['online'=>$r['online'],'offline'=>$r['offline'],'entries'=>$r['entries']],$r['month']);
        }
        if($f['source']!=='legacy'){
            foreach($this->db->query("SELECT b.id,b.isbn,b.library_id owner,(b.format='physical') physical,(b.format='digital') digital,COUNT(i.id) items FROM network_books b LEFT JOIN network_items i ON i.book_id=b.id AND i.library_id=b.library_id AND i.deleted_at IS NULL WHERE b.deleted_at IS NULL GROUP BY b.id,b.isbn,b.library_id,b.format")->result_array()as$r)$book('network',$r);
            foreach($this->db->query('SELECT library_id owner,COUNT(*) total FROM network_members WHERE deleted_at IS NULL GROUP BY library_id')->result_array()as$r)$add($r['owner'],'network',['members'=>$r['total']]);
            foreach(['loans'=>'loaned_at','returns'=>'returned_at']as$metric=>$date){
                foreach($this->db->query("SELECT library_id owner,DATE_FORMAT($date,'%Y-%m') month,COUNT(*) total FROM network_loans WHERE $date>=? AND $date<? GROUP BY library_id,month",[$start,$end])->result_array()as$r)$add($r['owner'],'network',[$metric=>$r['total']],$r['month']);
            }
            foreach($this->db->query('SELECT library_id owner,COUNT(*) active,SUM(due_at<NOW()) overdue FROM network_loans WHERE returned_at IS NULL GROUP BY library_id')->result_array()as$r)$add($r['owner'],'network',['active'=>$r['active'],'overdue'=>$r['overdue']]);
            foreach($this->db->query("SELECT library_id owner,DATE_FORMAT(visited_at,'%Y-%m') month,SUM(channel='offline') offline,SUM(channel='online') online,COUNT(*) entries FROM network_visits WHERE visited_at>=? AND visited_at<? GROUP BY library_id,month",[$start,$end])->result_array()as$r)$add($r['owner'],'network',['offline'=>$r['offline'],'online'=>$r['online'],'entries'=>$r['entries']],$r['month']);
        }
        $summary=$zero;foreach($rows as$row)foreach($zero as$key=>$unused)$summary[$key]+=$row[$key];
        $groups=[];
        foreach($rows as$row){
            if($row['id']===0&&array_sum(array_intersect_key($row,$zero))===0)continue;
            switch($f['group']){
                case 'type':$key='type:'.$row['library_type_id'];$label=$row['type'];break;
                case 'district':$key='district:'.$row['district_id'].':'.$row['district'];$label=$row['district']?:'Belum terpetakan';break;
                case 'village':$key='village:'.$row['district_id'].':'.$row['village_id'].':'.$row['village'];$label=($row['village']?:'Belum terpetakan').' / '.($row['district']?:'Belum terpetakan');break;
                default:$key='library:'.$row['id'];$label=$row['name'];
            }
            if(!isset($groups[$key]))$groups[$key]=['label'=>$label,'libraries'=>0]+$zero;
            if($row['id']>0)$groups[$key]['libraries']++;
            foreach($zero as$metric=>$unused)$groups[$key][$metric]+=$row[$metric];
        }
        uasort($groups,function($a,$b){return strnatcasecmp($a['label'],$b['label']);});
        return ['filters'=>$f,'start'=>$start,'end'=>$end,'groups'=>array_values($groups),'summary'=>$summary,'sources'=>$sources,'months'=>$months,'options'=>$options,'unique_records'=>count($records),'valid_isbns'=>count($isbns),'unidentified_records'=>count($unidentified),'libraries'=>count(array_filter($rows,function($r){return $r['id']>0;}))];
    }
}
