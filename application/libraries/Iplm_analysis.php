<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Reproducible local diagnostic only; never a nationally calibrated/official score. */
class Iplm_analysis
{
    public function groups(){return [
        'collection'=>['print_titles','print_copies','digital_titles','digital_copies','added_print_titles','added_print_copies','added_digital_titles','added_digital_copies'],
        'staff'=>['staff_qualified','staff_other','staff_training'],
        'service'=>['literacy_participants','library_users','ict_users','used_print_titles','used_print_copies','used_digital_titles','used_digital_copies'],
        'management'=>['literacy_events','partnerships','service_types','service_policies']
    ];}
    public function transform($x,$lambda){if($x<0||!is_finite($x)||!is_finite($lambda))throw new InvalidArgumentException('Nonnegative finite inputs required.');return abs($lambda)<1e-8?log1p($x):expm1($lambda*log1p($x))/$lambda;}
    private function fit(array $xs){
        $n=count($xs);$jac=array_sum(array_map('log1p',$xs));
        $loss=function($lambda)use($xs,$n,$jac){$ys=array_map(function($x)use($lambda){return $this->transform($x,$lambda);},$xs);$mean=array_sum($ys)/$n;$var=0;foreach($ys as$y)$var+=($y-$mean)**2;$var/=$n;if($var<=0||!is_finite($var))return INF;return $n/2*log($var)-($lambda-1)*$jac;};
        // Bounded MLE, explicitly reported as a local modelling choice.
        $lo=-5.0;$hi=5.0;for($i=0;$i<100;$i++){$a=$lo+($hi-$lo)/3;$b=$hi-($hi-$lo)/3;if($loss($a)<$loss($b))$hi=$b;else $lo=$a;}return ($lo+$hi)/2;
    }
    public function calculate(array $rows,array $period){
        $groups=$this->groups();$keys=array_merge(...array_values($groups));$valid=[];$excluded=[];
        foreach($rows as$row){$missing=[];foreach($keys as$k){$v=$row['values'][$k]??'';if(!is_scalar($v)||!preg_match('/^\d{1,15}$/D',(string)$v))$missing[]=$k;}if($missing)$excluded[]=['id'=>$row['id'],'missing'=>$missing];else $valid[]=$row;}
        $result=['rows'=>[],'parameters'=>[],'excluded'=>$excluded,'n'=>count($valid),'population'=>$period['population'],'minimum'=>null,'coverage'=>null,'raw'=>null,'adjusted'=>null,'warnings'=>[]];
        if(count($valid)<2){$result['warnings'][]='Minimal dua isian terverifikasi lengkap diperlukan untuk normalisasi lokal.';return $result;}
        $normalized=[];$varying=0;
        foreach($keys as$k){$xs=array_map(function($r)use($k){return (float)$r['values'][$k];},$valid);$constant=max($xs)===min($xs);$lambda=$constant?1.0:$this->fit($xs);$ys=array_map(function($x)use($lambda){return $this->transform($x,$lambda);},$xs);$min=min($ys);$max=max($ys);$span=$max-$min;
            $result['parameters'][$k]=['lambda'=>$lambda,'min'=>$min,'max'=>$max,'constant'=>$constant];if(!$constant)$varying++;
            foreach($ys as$i=>$y)$normalized[$i][$k]=$constant||$span<=0?0.0:max(0,min(1,($y-$min)/$span));
        }
        if(!$varying){$result['warnings'][]='Semua indikator konstan; indeks lokal tidak diterbitkan karena normalisasi tidak memiliki pembanding.';return $result;}
        foreach($valid as$i=>$row){$sub=[];foreach($groups as$group=>$codes){$sum=0;foreach($codes as$k)$sum+=$normalized[$i][$k];$sub[$group]=$sum/count($codes);}$compliance=($sub['collection']+$sub['staff'])/2;$performance=($sub['service']+$sub['management'])/2;$score=100*(.30*$compliance+.70*$performance);$result['rows'][]=['id'=>$row['id'],'name'=>$row['library_name'],'sub'=>$sub,'compliance'=>$compliance,'performance'=>$performance,'score'=>$score];}
        $result['raw']=array_sum(array_column($result['rows'],'score'))/count($valid);$population=(int)($period['population']??0);
        if($population>=count($valid)){$result['minimum']=(int)ceil(.68*$population);$result['coverage']=count($valid)/$population;$result['adjusted']=$result['raw']*min(1,count($valid)/$result['minimum']);}
        else $result['warnings'][]=$population?'Populasi lebih kecil dari jumlah responden; koreksi populasi sebelum penyesuaian.':'Populasi resmi belum diisi; skor dengan penyesuaian cakupan belum tersedia.';
        if(array_filter($result['parameters'],function($p){return $p['constant'];}))$result['warnings'][]='Indikator konstan diberi normalisasi 0 dalam simulasi ini, bukan penilaian kualitas. Perlakuan resmi perlu konfirmasi.';
        return $result;
    }
}
