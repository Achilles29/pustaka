<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Learn_english_rpg_progress_model extends CI_Model
{
    public function summary()
    {
        return [
            'players'=>(int)$this->db->count_all('learn_english_rpg_profiles'),
            'active'=>(int)$this->db->where('last_active_date >=',date('Y-m-d',strtotime('-30 days')))->count_all_results('learn_english_rpg_profiles'),
            'completed'=>(int)$this->db->where('is_completed',1)->count_all_results('learn_english_rpg_progress'),
            'attempts'=>(int)$this->db->count_all('learn_english_rpg_activity'),
        ];
    }

    public function players(array $filters=[])
    {
        $this->db->select("p.*,u.full_name,u.username,u.email,COUNT(DISTINCT pr.episode_code) chapters_started,COUNT(DISTINCT CASE WHEN pr.is_completed=1 THEN pr.episode_code END) chapters_completed,COALESCE(SUM(pr.xp),0) total_xp,MAX(pr.updated_at) last_progress_at,(SELECT e.title FROM learn_english_rpg_progress px JOIN learn_english_rpg_episodes e ON e.code=px.episode_code WHERE px.user_id=p.user_id ORDER BY e.id DESC LIMIT 1) latest_chapter",false)->from('learn_english_rpg_profiles p')->join('auth_user u','u.id=p.user_id','left')->join('learn_english_rpg_progress pr','pr.user_id=p.user_id','left');
        if(!empty($filters['q'])){$q=$this->db->escape_like_str($filters['q']);$this->db->where("(u.full_name LIKE '%{$q}%' ESCAPE '!' OR u.username LIKE '%{$q}%' ESCAPE '!' OR p.hero_name LIKE '%{$q}%' ESCAPE '!')",null,false);}
        if(($filters['status']??'')==='completed')$this->db->having('chapters_completed >',0);
        if(($filters['status']??'')==='playing')$this->db->having('chapters_started > chapters_completed',null,false);
        return $this->db->group_by('p.user_id')->order_by('last_progress_at','DESC')->order_by('u.full_name')->limit(300)->get()->result_array();
    }

    public function user_detail($user_id)
    {
        $user=$this->db->select('u.id,u.full_name,u.username,u.email,u.status,p.*')->from('auth_user u')->join('learn_english_rpg_profiles p','p.user_id=u.id')->where('u.id',$user_id)->get()->row_array();
        if(!$user)return null;
        $episodes=$this->db->select("e.*,COUNT(DISTINCT s.id) total_scenes,pr.id progress_id,pr.current_scene,pr.xp,pr.hearts,pr.correct_answers,pr.wrong_answers,pr.is_completed,pr.created_at started_at,pr.updated_at last_at,(SELECT COUNT(*) FROM learn_english_rpg_activity a WHERE a.user_id=".(int)$user_id." AND a.episode_code=e.code) attempts,(SELECT COALESCE(SUM(a.is_correct),0) FROM learn_english_rpg_activity a WHERE a.user_id=".(int)$user_id." AND a.episode_code=e.code) correct_attempts,(SELECT MIN(a.created_at) FROM learn_english_rpg_activity a WHERE a.user_id=".(int)$user_id." AND a.episode_code=e.code) first_attempt_at",false)->from('learn_english_rpg_episodes e')->join('learn_english_rpg_scenes s','s.episode_id=e.id AND s.is_active=1','left')->join('learn_english_rpg_progress pr','pr.episode_code=e.code AND pr.user_id='.(int)$user_id,'left')->group_by('e.id')->order_by('e.id')->get()->result_array();
        $latest_id=0;foreach($episodes as $episode)if(!empty($episode['progress_id'])||(int)$episode['attempts']>0)$latest_id=max($latest_id,(int)$episode['id']);
        foreach($episodes as &$episode){$episode['wrong_attempts']=(int)$episode['attempts']-(int)$episode['correct_attempts'];$episode['can_reset']=(int)$episode['id']<=$latest_id&&(!empty($episode['progress_id'])||(int)$episode['attempts']>0);$episode['affected_titles']=array_values(array_column(array_filter($episodes,fn($x)=>(int)$x['season_id']===(int)$episode['season_id']&&(int)$x['id']>=(int)$episode['id']&&(int)$x['id']<=$latest_id),'title'));}unset($episode);
        $activity=$this->db->select('a.*,e.title episode_title')->from('learn_english_rpg_activity a')->join('learn_english_rpg_episodes e','e.code=a.episode_code','left')->where('a.user_id',$user_id)->order_by('a.created_at','DESC')->limit(150)->get()->result_array();
        $choices=$this->db->select('c.*,e.title episode_title')->from('learn_english_rpg_story_choices c')->join('learn_english_rpg_episodes e','e.code=c.episode_code','left')->where('c.user_id',$user_id)->order_by('c.created_at','DESC')->limit(100)->get()->result_array();
        return compact('user','episodes','activity','choices','latest_id');
    }

    public function reset_from($user_id,$episode_id)
    {
        $target=$this->db->get_where('learn_english_rpg_episodes',['id'=>$episode_id])->row_array();if(!$target)return ['ok'=>false,'message'=>'Chapter tidak ditemukan.'];
        $episodes=$this->db->where('season_id',(int)$target['season_id'])->where('id >=',$episode_id)->order_by('id')->get('learn_english_rpg_episodes')->result_array();$codes=array_column($episodes,'code');
        $has=$this->db->where('user_id',$user_id)->where_in('episode_code',$codes)->count_all_results('learn_english_rpg_progress')+$this->db->where('user_id',$user_id)->where_in('episode_code',$codes)->count_all_results('learn_english_rpg_activity');
        if(!$has)return ['ok'=>false,'message'=>'Pengguna belum memiliki progres mulai chapter tersebut.','episode_code'=>$target['code']];
        $this->db->trans_begin();
        $loot=$this->db->select('item_code,COUNT(*) quantity',false)->where('user_id',$user_id)->where_in('episode_code',$codes)->group_by('item_code')->get('learn_english_rpg_loot_log')->result_array();
        foreach($loot as $row){$item=$this->db->get_where('learn_english_rpg_inventory',['user_id'=>$user_id,'item_code'=>$row['item_code']])->row_array();if(!$item)continue;$remaining=max(0,(int)$item['quantity']-(int)$row['quantity']);if($remaining===0)$this->db->where('id',$item['id'])->delete('learn_english_rpg_inventory');else $this->db->where('id',$item['id'])->update('learn_english_rpg_inventory',['quantity'=>$remaining]);}
        foreach(['learn_english_rpg_progress','learn_english_rpg_activity','learn_english_rpg_story_choices','learn_english_rpg_loot_log'] as $table)$this->db->where('user_id',$user_id)->where_in('episode_code',$codes)->delete($table);
        $this->rebuild_learning_totals($user_id);
        if($this->db->trans_status()===false){$this->db->trans_rollback();return ['ok'=>false,'message'=>'Reset gagal dan seluruh perubahan dibatalkan.','episode_code'=>$target['code']];}
        $this->db->trans_commit();return ['ok'=>true,'message'=>'Progres direset mulai “'.$target['title'].'”. Chapter sebelumnya tetap tersimpan.','episode_code'=>$target['code'],'affected_chapters'=>array_column($episodes,'title')];
    }

    private function rebuild_learning_totals($user_id)
    {
        $meanings=[];foreach($this->db->where('user_id',$user_id)->get('learn_english_rpg_word_mastery')->result_array() as $row)$meanings[$row['word']]=$row['meaning'];
        $stats=$this->db->select('vocabulary_word word,SUM(is_correct) correct_count,SUM(is_correct=0) wrong_count,MAX(created_at) last_seen_at',false)->where('user_id',$user_id)->where('vocabulary_word IS NOT NULL',null,false)->where('vocabulary_word !=','')->group_by('vocabulary_word')->get('learn_english_rpg_activity')->result_array();
        $this->db->where('user_id',$user_id)->delete('learn_english_rpg_word_mastery');foreach($stats as $row)$this->db->insert('learn_english_rpg_word_mastery',['user_id'=>$user_id,'word'=>$row['word'],'meaning'=>$meanings[$row['word']]??'','correct_count'=>(int)$row['correct_count'],'wrong_count'=>(int)$row['wrong_count'],'mastery_score'=>max(-10,min(100,(int)$row['correct_count']*12-(int)$row['wrong_count']*8)),'last_seen_at'=>$row['last_seen_at']]);
        $totals=$this->db->select('COALESCE(SUM(is_correct),0) correct,COALESCE(SUM(is_correct=0),0) wrong',false)->where('user_id',$user_id)->get('learn_english_rpg_activity')->row_array();$rep=$this->db->select('COALESCE(SUM(reputation_delta),0) reputation',false)->where('user_id',$user_id)->get('learn_english_rpg_story_choices')->row_array();$this->db->where('user_id',$user_id)->update('learn_english_rpg_profiles',['total_correct'=>(int)$totals['correct'],'total_wrong'=>(int)$totals['wrong'],'reputation'=>(int)$rep['reputation']]);
    }
}
