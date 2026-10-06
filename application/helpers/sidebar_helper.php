<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Select the most specific visible leaf; parent groups derive state from it. */
function pustaka_sidebar_active_id(array $items, $path)
{
    $path=trim((string)$path,'/');
    $path=preg_replace('#^library-workspace/(?:edit|import|archive)/(books|items|members)(?:/.*)?$#','library-workspace/records/$1',$path);
    $best_id=null;
    $best_length=-1;
    $visit=function(array $nodes)use(&$visit,$path,&$best_id,&$best_length){
        foreach($nodes as $item){
            if(!empty($item['children'])){$visit($item['children']);continue;}
            $url=trim((string)($item['url']??''),'/');
            if($url!=='' && ($path===$url || strpos($path,$url.'/')===0) && strlen($url)>$best_length){
                $best_id=(int)$item['id'];
                $best_length=strlen($url);
            }
        }
    };
    $visit($items);
    return $best_id;
}
