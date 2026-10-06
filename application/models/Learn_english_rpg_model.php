<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Learn_english_rpg_model extends CI_Model
{
    const EPISODE = 'lost_library';

    public function episode($code=self::EPISODE)
    {
        $this->ensure_seeded();
        $episode=$this->db->get_where('learn_english_rpg_episodes',['code'=>$code,'is_active'=>1])->row_array();
        if(!$episode)return null;$episode['total_scenes']=count($this->scenes($code));return $episode;
    }

    public function scenes($code=self::EPISODE,$shuffle_seed=null)
    {
        $this->ensure_seeded();
        $episode=$this->db->get_where('learn_english_rpg_episodes',['code'=>$code])->row_array();
        if(!$episode)return $this->default_scenes();
        $rows=$this->db->where(['episode_id'=>(int)$episode['id'],'is_active'=>1])->order_by('sort_order,id')->get('learn_english_rpg_scenes')->result_array();
        return array_map(function($row)use($shuffle_seed){$choices=json_decode($row['choices_json'],true)?:[];foreach($choices as $i=>&$choice)$choice['answer_index']=$i;unset($choice);if($shuffle_seed!==null&&($row['challenge_type']??'choice')!=='story')usort($choices,function($a,$b)use($shuffle_seed,$row){return strcmp(hash('sha256',$shuffle_seed.':'.$row['id'].':'.$a['answer_index']),hash('sha256',$shuffle_seed.':'.$row['id'].':'.$b['answer_index']));});$words=preg_split('/\s+/u',trim((string)$row['sentence_answer']),-1,PREG_SPLIT_NO_EMPTY);if($shuffle_seed!==null)usort($words,function($a,$b)use($shuffle_seed,$row){return strcmp(hash('sha256',$shuffle_seed.':sentence:'.$row['id'].':'.$a),hash('sha256',$shuffle_seed.':sentence:'.$row['id'].':'.$b));});return ['id'=>(int)$row['id'],'place'=>$row['place'],'speaker'=>$row['speaker'],'emoji'=>$row['emoji'],'text'=>$row['dialogue'],'translation'=>$row['translation'],'prompt'=>$row['prompt'],'challenge_type'=>$row['challenge_type']??'choice','audio_text'=>$row['audio_text']?:$row['dialogue'],'sentence_words'=>$words,'choices'=>$choices,'word'=>[$row['vocabulary_word'],$row['vocabulary_meaning']]];},$rows);
    }

    private function default_scenes()
    {
        return [
            ['place'=>'Village Gate','speaker'=>'Mira','emoji'=>'🧙‍♀️','text'=>'Welcome, young traveler! Our magical book is missing. Will you help me find it?','translation'=>'Selamat datang, petualang muda! Buku ajaib kami hilang. Maukah kamu membantu mencarinya?','prompt'=>'What should you say?','choices'=>[['text'=>'Yes, I will help you.','correct'=>1,'feedback'=>'Excellent! “I will help” berarti kamu bersedia membantu.'],['text'=>'I am a blue table.','correct'=>0,'feedback'=>'Kalimat itu tidak menjawab Mira. Pilih jawaban yang menawarkan bantuan.'],['text'=>'Good night, sandwich.','correct'=>0,'feedback'=>'Mira sedang meminta bantuanmu.']], 'word'=>['help','membantu']],
            ['place'=>'Whispering Forest','speaker'=>'Mira','emoji'=>'🌲','text'=>'The path is dark. We need something that gives us light.','translation'=>'Jalannya gelap. Kita membutuhkan sesuatu yang memberi cahaya.','prompt'=>'Which item should you take?','choices'=>[['text'=>'A lantern','correct'=>1,'feedback'=>'Correct! A lantern gives us light.'],['text'=>'A pillow','correct'=>0,'feedback'=>'A pillow is for sleeping, not lighting a path.'],['text'=>'A spoon','correct'=>0,'feedback'=>'A spoon helps us eat, but it cannot light the forest.']], 'word'=>['lantern','lentera']],
            ['place'=>'Old Stone Bridge','speaker'=>'Bridge Keeper','emoji'=>'🧌','text'=>'Stop! You may cross only if you ask politely.','translation'=>'Berhenti! Kamu boleh menyeberang hanya jika meminta dengan sopan.','prompt'=>'Choose the polite sentence.','choices'=>[['text'=>'Move! I cross now!','correct'=>0,'feedback'=>'That sounds impolite. Gunakan “please” saat meminta izin.'],['text'=>'May I cross the bridge, please?','correct'=>1,'feedback'=>'Perfect! “May I ... please?” adalah permintaan yang sopan.'],['text'=>'The bridge eats apples.','correct'=>0,'feedback'=>'Lucu, tetapi itu bukan sebuah permintaan.']], 'word'=>['cross','menyeberang']],
            ['place'=>'Moonlit Cave','speaker'=>'Pip','emoji'=>'🦊','text'=>'I saw a shadow carrying the book. It went behind the waterfall.','translation'=>'Aku melihat bayangan membawa buku itu. Ia pergi ke belakang air terjun.','prompt'=>'Where did the shadow go?','choices'=>[['text'=>'Behind the waterfall','correct'=>1,'feedback'=>'Right! “Behind” berarti di belakang.'],['text'=>'Under the village','correct'=>0,'feedback'=>'Pip berkata “behind the waterfall.”'],['text'=>'Inside a sandwich','correct'=>0,'feedback'=>'Cari tempat yang disebutkan Pip.']], 'word'=>['behind','di belakang']],
            ['place'=>'Secret Door','speaker'=>'Ancient Door','emoji'=>'🚪','text'=>'To open me, complete the sentence: “The book ___ on the table.”','translation'=>'Untuk membukaku, lengkapi kalimat: “Buku itu berada di atas meja.”','prompt'=>'Choose the correct word.','choices'=>[['text'=>'are','correct'=>0,'feedback'=>'Use “is” for one book.'],['text'=>'is','correct'=>1,'feedback'=>'Correct! One book: “The book is ...”'],['text'=>'am','correct'=>0,'feedback'=>'“Am” digunakan bersama “I.”']], 'word'=>['table','meja']],
            ['place'=>'Hidden Library','speaker'=>'Book Guardian','emoji'=>'🦉','text'=>'Knowledge grows when it is shared. Why do you want the book?','translation'=>'Pengetahuan tumbuh ketika dibagikan. Mengapa kamu menginginkan buku itu?','prompt'=>'Choose the kind answer.','choices'=>[['text'=>'I want to help everyone learn.','correct'=>1,'feedback'=>'Wonderful! Learning becomes powerful when we share it.'],['text'=>'I want to hide it forever.','correct'=>0,'feedback'=>'Perpustakaan membutuhkan buku itu agar semua dapat belajar.'],['text'=>'I do not like books.','correct'=>0,'feedback'=>'Misi kamu adalah mengembalikan buku dan membantu desa.']], 'word'=>['knowledge','pengetahuan']],
            ['place'=>'Village Library','speaker'=>'Mira','emoji'=>'📖','text'=>'You found it! Thank you for bringing the magical book home.','translation'=>'Kamu menemukannya! Terima kasih telah membawa buku ajaib itu pulang.','prompt'=>'How do you reply to “Thank you”?','choices'=>[['text'=>'You are welcome!','correct'=>1,'feedback'=>'Exactly! “You are welcome” adalah jawaban ramah untuk ucapan terima kasih.'],['text'=>'I am under the chair.','correct'=>0,'feedback'=>'Pilih jawaban umum untuk “Thank you.”'],['text'=>'What color is Tuesday?','correct'=>0,'feedback'=>'Pilih balasan yang sopan.']], 'word'=>['welcome','sama-sama']],
        ];
    }

    public function seasons($active_only=false,$user_id=null)
    {
        $this->ensure_seeded();
        $this->db->select('s.*,(SELECT COUNT(*) FROM learn_english_rpg_episodes e WHERE e.season_id=s.id AND e.is_active=1) episode_count,(SELECT COUNT(*) FROM learn_english_rpg_progress p JOIN learn_english_rpg_episodes e ON e.code=p.episode_code WHERE e.season_id=s.id AND p.user_id='.(int)$user_id.' AND p.is_completed=1) completed_count',false)->from('learn_english_rpg_seasons s');
        if($active_only)$this->db->where('s.is_active',1);
        return $this->db->order_by('s.sort_order')->order_by('s.id')->get()->result_array();
    }

    public function season($id,$active_only=false)
    {
        $this->ensure_seeded();$this->db->where('id',(int)$id);if($active_only)$this->db->where('is_active',1);return $this->db->get('learn_english_rpg_seasons')->row_array();
    }

    public function episodes($active_only=false,$user_id=null,$season_id=null)
    {
        $this->ensure_seeded();$this->db->select('e.*,(SELECT COUNT(*) FROM learn_english_rpg_scenes s WHERE s.episode_id=e.id) scene_count',false)->from('learn_english_rpg_episodes e');if($user_id)$this->db->select('p.current_scene,p.xp,p.is_completed')->join('learn_english_rpg_progress p','p.episode_code=e.code AND p.user_id='.(int)$user_id,'left');if($active_only)$this->db->where('e.is_active',1);if($season_id!==null)$this->db->where('e.season_id',(int)$season_id);$rows=$this->db->order_by('e.id')->get()->result_array();if($user_id){$unlocked=true;foreach($rows as &$row){$row['is_unlocked']=$unlocked;$unlocked=$unlocked&&!empty($row['is_completed']);}unset($row);}return $rows;
    }

    public function admin_episode($id=null){$this->ensure_seeded();return $id?$this->db->get_where('learn_english_rpg_episodes',['id'=>(int)$id])->row_array():$this->db->get_where('learn_english_rpg_episodes',['code'=>self::EPISODE])->row_array();}

    public function save_season(array $data)
    {
        $this->db->insert('learn_english_rpg_seasons',['code'=>trim($data['code']),'title'=>trim($data['title']),'subtitle'=>trim($data['subtitle']??''),'description'=>trim($data['description']??''),'cover_emoji'=>trim($data['cover_emoji']??'map')?:'map','color'=>trim($data['color']??'#6750b7')?:'#6750b7','is_active'=>!empty($data['is_active'])?1:0,'sort_order'=>max(1,(int)($data['sort_order']??100))]);return (int)$this->db->insert_id();
    }

    public function admin_scenes($episode_id)
    {
        return $this->db->where('episode_id',(int)$episode_id)->order_by('sort_order,id')->get('learn_english_rpg_scenes')->result_array();
    }

    public function save_episode(array $data,$id=null)
    {
        $season_id=(int)($data['season_id']??0);if($season_id<1){$season=$this->db->select('id')->where('code','season_1')->get('learn_english_rpg_seasons')->row_array();$season_id=(int)($season['id']??1);} $payload=['season_id'=>$season_id,'title'=>trim($data['title']),'subtitle'=>trim($data['subtitle']),'description'=>trim($data['description']),'is_active'=>!empty($data['is_active'])?1:0];if($id){$this->db->where('id',(int)$id)->update('learn_english_rpg_episodes',$payload);return (int)$id;}$payload['code']=trim($data['code']);$this->db->insert('learn_english_rpg_episodes',$payload);return (int)$this->db->insert_id();
    }

    public function save_scene($id,array $data,$episode_id=null)
    {
        $episode=$this->admin_episode($episode_id);$correct=max(0,min(2,(int)($data['correct_choice']??0)));$choices=[];
        for($i=0;$i<3;$i++)$choices[]=['text'=>trim((string)($data['choice_text'][$i]??'')),'correct'=>$i===$correct?1:0,'feedback'=>trim((string)($data['choice_feedback'][$i]??''))];
        $old=$id?$this->db->get_where('learn_english_rpg_scenes',['id'=>(int)$id])->row_array():[];$type=$data['challenge_type']??($old['challenge_type']??'choice');if(!in_array($type,['choice','listening','sentence','story'],true))$type='choice';$stored_choices=($type==='story'&&$old)?(string)$old['choices_json']:json_encode($choices,JSON_UNESCAPED_UNICODE);$payload=['episode_id'=>(int)$episode['id'],'sort_order'=>max(1,(int)$data['sort_order']),'place'=>trim($data['place']),'speaker'=>trim($data['speaker']),'emoji'=>trim($data['emoji'])?:'🧙','dialogue'=>trim($data['dialogue']),'translation'=>trim($data['translation']),'prompt'=>trim($data['prompt']),'challenge_type'=>$type,'audio_text'=>array_key_exists('audio_text',$data)?(trim($data['audio_text'])?:null):($old['audio_text']??null),'sentence_answer'=>array_key_exists('sentence_answer',$data)?(trim($data['sentence_answer'])?:null):($old['sentence_answer']??null),'choices_json'=>$stored_choices,'vocabulary_word'=>trim($data['vocabulary_word']),'vocabulary_meaning'=>trim($data['vocabulary_meaning']),'is_active'=>!empty($data['is_active'])?1:0];
        if($id)$this->db->where('id',(int)$id)->update('learn_english_rpg_scenes',$payload);else $this->db->insert('learn_english_rpg_scenes',$payload);
    }

    public function delete_scene($id){return $this->db->where('id',(int)$id)->delete('learn_english_rpg_scenes');}

    private function ensure_seeded()
    {
        if(!$this->db->table_exists('learn_english_rpg_episodes'))return;
        $episode=$this->db->get_where('learn_english_rpg_episodes',['code'=>self::EPISODE])->row_array();if(!$episode)return;
        if($this->db->where('episode_id',$episode['id'])->count_all_results('learn_english_rpg_scenes'))return;
        foreach($this->default_scenes() as $i=>$scene)$this->db->insert('learn_english_rpg_scenes',['episode_id'=>$episode['id'],'sort_order'=>$i+1,'place'=>$scene['place'],'speaker'=>$scene['speaker'],'emoji'=>$scene['emoji'],'dialogue'=>$scene['text'],'translation'=>$scene['translation'],'prompt'=>$scene['prompt'],'choices_json'=>json_encode($scene['choices'],JSON_UNESCAPED_UNICODE),'vocabulary_word'=>$scene['word'][0],'vocabulary_meaning'=>$scene['word'][1],'is_active'=>1]);
    }

    public function get_progress($user_id,$code=self::EPISODE)
    {
        $row=$this->db->get_where('learn_english_rpg_progress',['user_id'=>(int)$user_id,'episode_code'=>$code])->row_array();
        if(!$row){$now=date('Y-m-d H:i:s');$this->db->insert('learn_english_rpg_progress',['user_id'=>(int)$user_id,'episode_code'=>$code,'created_at'=>$now,'updated_at'=>$now,'vocabulary_json'=>'[]']);$row=$this->db->get_where('learn_english_rpg_progress',['id'=>$this->db->insert_id()])->row_array();}
        return $this->hydrate($row);
    }

    public function answer($user_id,$scene_index,$choice_index,$code=self::EPISODE)
    {
        $progress=$this->get_progress($user_id,$code);$scenes=$this->scenes($code);
        if($progress['is_completed'])return ['ok'=>false,'message'=>'Episode ini sudah selesai.'];
        if((int)$progress['current_scene']!==(int)$scene_index||!isset($scenes[$scene_index]['choices'][$choice_index]))return ['ok'=>false,'message'=>'Pilihan tidak valid atau adegan sudah berubah.'];
        $choice=$scenes[$scene_index]['choices'][$choice_index];$correct=!empty($choice['correct']);$vocabulary=$progress['vocabulary'];$next=(int)$progress['current_scene'];$hearts=(int)$progress['hearts'];$xp=(int)$progress['xp'];
        $profile=$this->get_profile($user_id);$class=$profile['class_code']??'word_knight';$type=$scenes[$scene_index]['challenge_type']??'choice';
        $equipped=[];foreach($this->get_inventory($user_id) as $item)if(!empty($item['is_equipped']))$equipped[$item['item_type']]=$item;
        $xp_gain=15+isset($equipped['weapon'])*2+($scene_index===14&&isset($equipped['artifact'])?3:0);
        $class_bonus=$correct&&(($class==='listening_ranger'&&$type==='listening')||($class==='grammar_mage'&&$type==='sentence')||($class==='word_knight'&&$type==='choice'));
        if($class_bonus)$xp_gain+=5;$loot=null;$protected=false;
        if($correct){$next++;$xp+=$xp_gain;$vocabulary[$scenes[$scene_index]['word'][0]]=$scenes[$scene_index]['word'][1];$loot=$this->award_loot($user_id,$code,$next);$this->db->set('class_energy','class_energy+1',false)->where('user_id',(int)$user_id)->update('learn_english_rpg_profiles');}else{$shield=$class==='word_knight'&&(int)($profile['class_energy']??0)>=5;$armor=isset($equipped['armor'])&&(($scene_index+1)%3===0);$protected=$shield||$armor;if($shield)$this->db->set('class_energy','GREATEST(0,class_energy-5)',false)->where('user_id',(int)$user_id)->update('learn_english_rpg_profiles');elseif(!$armor)$hearts=max(0,$hearts-1);if($hearts===0)$hearts=3;}
        $completed=$next>=count($scenes);$data=['current_scene'=>$next,'xp'=>$xp,'hearts'=>$hearts,'correct_answers'=>(int)$progress['correct_answers']+($correct?1:0),'wrong_answers'=>(int)$progress['wrong_answers']+($correct?0:1),'vocabulary_json'=>json_encode($vocabulary,JSON_UNESCAPED_UNICODE),'is_completed'=>$completed?1:0,'completed_at'=>$completed?date('Y-m-d H:i:s'):null,'updated_at'=>date('Y-m-d H:i:s')];
        $this->db->where('id',(int)$progress['id'])->update('learn_english_rpg_progress',$data);
        $this->record_learning($user_id,$code,$scene_index+1,$scenes[$scene_index],$correct);
        $bonus_note=$correct&&$xp_gain>15?' Bonus equipment/class: +'.($xp_gain-15).' XP!':'';$shield_note=$protected?' Perisai melindungi nyawamu!':'';
        return ['ok'=>true,'correct'=>$correct,'feedback'=>$choice['feedback'].$bonus_note.$shield_note,'completed'=>$completed,'progress'=>$this->get_progress($user_id,$code),'loot'=>$loot,'xp_gain'=>$correct?$xp_gain:0];
    }

    public function reset($user_id,$code=self::EPISODE)
    {
        $scope=['user_id'=>(int)$user_id,'episode_code'=>$code];
        $this->db->trans_start();
        // A story choice is unique per scene. It must be cleared together with the
        // chapter progress or a replay will stop on the old choice.
        if($this->db->table_exists('learn_english_rpg_story_choices')){
            $old=$this->db->select('COALESCE(SUM(reputation_delta),0) total',false)->where($scope)->get('learn_english_rpg_story_choices')->row_array();
            $this->db->where($scope)->delete('learn_english_rpg_story_choices');
            if((int)($old['total']??0)!==0)$this->db->set('reputation','reputation - ('.(int)$old['total'].')',false)->where('user_id',(int)$user_id)->update('learn_english_rpg_profiles');
        }
        $this->db->where($scope)->update('learn_english_rpg_progress',['current_scene'=>0,'xp'=>0,'hearts'=>3,'correct_answers'=>0,'wrong_answers'=>0,'vocabulary_json'=>'[]','is_completed'=>0,'completed_at'=>null,'updated_at'=>date('Y-m-d H:i:s')]);
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function answer_sentence($user_id,$scene_index,$sentence,$code)
    {
        $progress=$this->get_progress($user_id,$code);if((int)$progress['current_scene']!==(int)$scene_index)return ['ok'=>false,'message'=>'Adegan sudah berubah.'];$episode=$this->db->get_where('learn_english_rpg_episodes',['code'=>$code])->row_array();$rows=$this->db->where(['episode_id'=>$episode['id'],'is_active'=>1])->order_by('sort_order,id')->get('learn_english_rpg_scenes')->result_array();if(!isset($rows[$scene_index])||$rows[$scene_index]['challenge_type']!=='sentence')return ['ok'=>false,'message'=>'Tantangan susun kalimat tidak ditemukan.'];$normalize=function($value){return mb_strtolower(trim(preg_replace('/[^\pL\pN\s]+/u','',(string)$value)));};$correct=$normalize($sentence)===$normalize($rows[$scene_index]['sentence_answer']);$choices=json_decode($rows[$scene_index]['choices_json'],true)?:[];$answer_index=0;foreach($choices as $i=>$choice)if(($correct&&!empty($choice['correct']))||(!$correct&&empty($choice['correct']))){$answer_index=$i;break;}$result=$this->answer($user_id,$scene_index,$answer_index,$code);$result['feedback']=$correct?'Kalimat tersusun dengan benar! Great job!':'Urutan kata belum tepat. Coba susun kembali.';return $result;
    }

    public function get_inventory($user_id)
    {
        if(!$this->db->table_exists('learn_english_rpg_inventory'))return [];return $this->db->where('user_id',(int)$user_id)->order_by('is_equipped DESC,item_type,item_name')->get('learn_english_rpg_inventory')->result_array();
    }

    public function answer_story($user_id,$scene_index,$choice_index,$code)
    {
        $progress=$this->get_progress($user_id,$code);$scenes=$this->scenes($code,$user_id);if((int)$progress['current_scene']!==(int)$scene_index||!isset($scenes[$scene_index])||$scenes[$scene_index]['challenge_type']!=='story'||!isset($scenes[$scene_index]['choices'][$choice_index]))return ['ok'=>false,'message'=>'Pilihan cerita tidak valid atau adegan sudah berubah.'];$choice=$scenes[$scene_index]['choices'][$choice_index];$scope=['user_id'=>(int)$user_id,'episode_code'=>$code,'scene_number'=>$scene_index+1];$existing=$this->db->get_where('learn_english_rpg_story_choices',$scope)->row_array();$next=$scene_index+1;$completed=$next>=count($scenes);
        // Repair progress left behind by the old reset implementation. The choice
        // and reputation were already recorded, so only advance the scene once.
        if($existing){$this->db->where('id',(int)$progress['id'])->update('learn_english_rpg_progress',['current_scene'=>$next,'xp'=>(int)$progress['xp']+10,'is_completed'=>$completed?1:0,'completed_at'=>$completed?date('Y-m-d H:i:s'):null,'updated_at'=>date('Y-m-d H:i:s')]);return ['ok'=>true,'feedback'=>'Pilihan tersimpan. Petualangan dilanjutkan.','path'=>$existing['choice_code'],'reputation_delta'=>0,'completed'=>$completed,'progress'=>$this->get_progress($user_id,$code)];}
        $delta=(int)($choice['reputation_delta']??0);$this->db->trans_start();$this->db->insert('learn_english_rpg_story_choices',$scope+['choice_code'=>$choice['choice_code']??'path','choice_text'=>$choice['text'],'reputation_delta'=>$delta]);$this->db->set('reputation','reputation+'.$delta,false)->where('user_id',(int)$user_id)->update('learn_english_rpg_profiles');$this->db->where('id',(int)$progress['id'])->update('learn_english_rpg_progress',['current_scene'=>$next,'xp'=>(int)$progress['xp']+10,'is_completed'=>$completed?1:0,'completed_at'=>$completed?date('Y-m-d H:i:s'):null,'updated_at'=>date('Y-m-d H:i:s')]);$this->db->trans_complete();return ['ok'=>true,'feedback'=>$choice['feedback']??'Your choice changes the journey.','path'=>$choice['choice_code']??'path','reputation_delta'=>$delta,'completed'=>$completed,'progress'=>$this->get_progress($user_id,$code)];
    }

    public function get_profile($user_id,$display_name='Hero')
    {
        if(!$this->db->table_exists('learn_english_rpg_profiles'))return [];$row=$this->db->get_where('learn_english_rpg_profiles',['user_id'=>(int)$user_id])->row_array();if(!$row){$this->db->insert('learn_english_rpg_profiles',['user_id'=>(int)$user_id,'hero_name'=>$display_name,'avatar'=>'🧝','last_active_date'=>date('Y-m-d')]);$row=$this->db->get_where('learn_english_rpg_profiles',['user_id'=>(int)$user_id])->row_array();}else{$today=date('Y-m-d');$last=$row['last_active_date'];if($last!==$today){$yesterday=date('Y-m-d',strtotime('-1 day'));$streak=$last===$yesterday?(int)$row['streak_days']+1:1;$this->db->where('user_id',(int)$user_id)->update('learn_english_rpg_profiles',['streak_days'=>$streak,'longest_streak'=>max($streak,(int)$row['longest_streak']),'last_active_date'=>$today]);$row=$this->db->get_where('learn_english_rpg_profiles',['user_id'=>(int)$user_id])->row_array();}}return $row;
    }

    public function save_profile($user_id,array $data)
    {
        $class=in_array($data['class_code']??'', ['word_knight','grammar_mage','listening_ranger','speaking_bard'],true)?$data['class_code']:'word_knight';$avatars=['🧝','🧙','🛡️','🏹','🎵','🐲'];$avatar=in_array($data['avatar']??'',$avatars,true)?$data['avatar']:'🧝';return $this->db->where('user_id',(int)$user_id)->update('learn_english_rpg_profiles',['hero_name'=>mb_substr(trim($data['hero_name']??'Hero'),0,80),'avatar'=>$avatar,'class_code'=>$class]);
    }

    public function daily_quests($user_id)
    {
        $today=date('Y-m-d 00:00:00');$base=$this->db->where('user_id',(int)$user_id)->where('created_at >=',$today);$correct=(int)(clone $base)->where('is_correct',1)->count_all_results('learn_english_rpg_activity');$listening=(int)(clone $base)->where(['is_correct'=>1,'challenge_type'=>'listening'])->count_all_results('learn_english_rpg_activity');$words=(int)$this->db->select('COUNT(DISTINCT vocabulary_word) total',false)->where('user_id',(int)$user_id)->where('is_correct',1)->where('created_at >=',$today)->get('learn_english_rpg_activity')->row()->total;return [['label'=>'Menangkan 5 pertarungan','current'=>min(5,$correct),'target'=>5],['label'=>'Selesaikan 2 listening','current'=>min(2,$listening),'target'=>2],['label'=>'Pelajari 3 kosakata','current'=>min(3,$words),'target'=>3]];
    }

    public function weakest_words($user_id,$limit=10)
    {
        return $this->db->where('user_id',(int)$user_id)->order_by('mastery_score','ASC')->order_by('last_seen_at','DESC')->limit((int)$limit)->get('learn_english_rpg_word_mastery')->result_array();
    }

    public function side_quests($user_id)
    {
        $quests=$this->db->where('is_active',1)->order_by('id')->get('learn_english_rpg_side_quests')->result_array();$stats=['correct'=>(int)$this->db->where(['user_id'=>(int)$user_id,'is_correct'=>1])->count_all_results('learn_english_rpg_activity'),'listening'=>(int)$this->db->where(['user_id'=>(int)$user_id,'is_correct'=>1,'challenge_type'=>'listening'])->count_all_results('learn_english_rpg_activity'),'sentence'=>(int)$this->db->where(['user_id'=>(int)$user_id,'is_correct'=>1,'challenge_type'=>'sentence'])->count_all_results('learn_english_rpg_activity'),'words'=>(int)$this->db->where('user_id',(int)$user_id)->count_all_results('learn_english_rpg_word_mastery'),'chapters'=>(int)$this->db->where(['user_id'=>(int)$user_id,'is_completed'=>1])->count_all_results('learn_english_rpg_progress')];foreach($quests as &$q){$q['current']=min((int)$q['target'],$stats[$q['metric']]??0);$q['is_complete']=$q['current']>=(int)$q['target'];$q['is_claimed']=(bool)$this->db->get_where('learn_english_rpg_side_quest_claims',['user_id'=>(int)$user_id,'quest_id'=>$q['id']])->row_array();}unset($q);return $quests;
    }

    public function claim_side_quest($user_id,$quest_id)
    {
        $target=null;foreach($this->side_quests($user_id) as $q)if((int)$q['id']===(int)$quest_id)$target=$q;if(!$target||!$target['is_complete']||$target['is_claimed'])return false;$this->db->trans_start();$this->db->insert('learn_english_rpg_side_quest_claims',['user_id'=>(int)$user_id,'quest_id'=>(int)$quest_id]);$existing=$this->db->get_where('learn_english_rpg_inventory',['user_id'=>(int)$user_id,'item_code'=>$target['reward_item_code']])->row_array();if($existing)$this->db->set('quantity','quantity+1',false)->where('id',$existing['id'])->update('learn_english_rpg_inventory');else $this->db->insert('learn_english_rpg_inventory',['user_id'=>(int)$user_id,'item_code'=>$target['reward_item_code'],'item_name'=>$target['reward_item_name'],'item_icon'=>$target['reward_item_icon'],'item_type'=>'artifact','quantity'=>1]);$this->db->trans_complete();return $this->db->trans_status()?$target:false;
    }

    public function review_words($user_id,$limit=20)
    {
        $rows=$this->weakest_words($user_id,$limit);if(count($rows)<5){$extra=$this->db->select('vocabulary_word word,vocabulary_meaning meaning,0 mastery_score,0 correct_count,0 wrong_count')->where('vocabulary_word IS NOT NULL',null,false)->where('vocabulary_word !=','')->group_by('vocabulary_word,vocabulary_meaning')->order_by('RAND()')->limit($limit-count($rows))->get('learn_english_rpg_scenes')->result_array();$rows=array_merge($rows,$extra);}return $rows;
    }

    public function submit_review($user_id,$word,$answer,$mode='choice')
    {
        $row=$this->db->where('LOWER(word)',mb_strtolower(trim($word)))->where('user_id',(int)$user_id)->get('learn_english_rpg_word_mastery')->row_array();if(!$row){$scene=$this->db->where('LOWER(vocabulary_word)',mb_strtolower(trim($word)))->get('learn_english_rpg_scenes')->row_array();if(!$scene)return ['ok'=>false,'message'=>'Kosakata tidak ditemukan.'];$row=['word'=>$scene['vocabulary_word'],'meaning'=>$scene['vocabulary_meaning']];}$normalize=function($v){return mb_strtolower(trim(preg_replace('/[^\pL\pN\s]+/u','',(string)$v)));};$correct=$mode==='speaking'?str_contains($normalize($answer),$normalize($row['word'])):$normalize($answer)===$normalize($row['meaning']);$this->db->insert('learn_english_rpg_review_log',['user_id'=>(int)$user_id,'word'=>$row['word'],'is_correct'=>$correct?1:0,'mode'=>$mode]);$mastery=$this->db->get_where('learn_english_rpg_word_mastery',['user_id'=>(int)$user_id,'word'=>$row['word']])->row_array();if($mastery)$this->db->set($correct?'correct_count':'wrong_count',($correct?'correct_count':'wrong_count').'+1',false)->set('mastery_score','GREATEST(-10,LEAST(100,mastery_score+'.($correct?'10':'-5').'))',false)->set('last_seen_at',date('Y-m-d H:i:s'))->where(['user_id'=>(int)$user_id,'word'=>$row['word']])->update('learn_english_rpg_word_mastery');else $this->db->insert('learn_english_rpg_word_mastery',['user_id'=>(int)$user_id,'word'=>$row['word'],'meaning'=>$row['meaning'],'correct_count'=>$correct?1:0,'wrong_count'=>$correct?0:1,'mastery_score'=>$correct?10:-5,'last_seen_at'=>date('Y-m-d H:i:s')]);if($correct)$this->db->set($mode==='speaking'?'speaking_correct':'review_correct',($mode==='speaking'?'speaking_correct':'review_correct').'+1',false)->where('user_id',(int)$user_id)->update('learn_english_rpg_profiles');return ['ok'=>true,'correct'=>$correct,'word'=>$row['word'],'meaning'=>$row['meaning'],'feedback'=>$correct?'Correct! Great practice.':'Belum tepat. Arti yang benar: '.$row['meaning']];
    }

    public function equip_item($user_id,$item_id)
    {
        $item=$this->db->get_where('learn_english_rpg_inventory',['id'=>(int)$item_id,'user_id'=>(int)$user_id])->row_array();if(!$item||!in_array($item['item_type'],['weapon','armor','artifact'],true))return false;$this->db->trans_start();$this->db->where(['user_id'=>(int)$user_id,'item_type'=>$item['item_type']])->update('learn_english_rpg_inventory',['is_equipped'=>0]);$this->db->where('id',$item['id'])->update('learn_english_rpg_inventory',['is_equipped'=>1]);$this->db->trans_complete();return $this->db->trans_status();
    }

    public function use_potion($user_id,$code)
    {
        $item=$this->db->get_where('learn_english_rpg_inventory',['user_id'=>(int)$user_id,'item_code'=>'heart_potion'])->row_array();if(!$item||(int)$item['quantity']<1)return false;$this->db->trans_start();if((int)$item['quantity']===1)$this->db->where('id',$item['id'])->delete('learn_english_rpg_inventory');else $this->db->set('quantity','quantity-1',false)->where('id',$item['id'])->update('learn_english_rpg_inventory');$this->db->where(['user_id'=>(int)$user_id,'episode_code'=>$code])->update('learn_english_rpg_progress',['hearts'=>3]);$this->db->trans_complete();return $this->db->trans_status();
    }

    public function claim_daily_reward($user_id)
    {
        $p=$this->get_profile($user_id);if($p['daily_reward_date']===date('Y-m-d'))return false;$qty=max(1,min(3,(int)$p['streak_days']>=7?3:((int)$p['streak_days']>=3?2:1)));$item=$this->db->get_where('learn_english_rpg_inventory',['user_id'=>(int)$user_id,'item_code'=>'heart_potion'])->row_array();if($item)$this->db->set('quantity','quantity+'.$qty,false)->where('id',$item['id'])->update('learn_english_rpg_inventory');else $this->db->insert('learn_english_rpg_inventory',['user_id'=>(int)$user_id,'item_code'=>'heart_potion','item_name'=>'Heart Potion','item_icon'=>'🧪','item_type'=>'potion','quantity'=>$qty]);$this->db->where('user_id',(int)$user_id)->update('learn_english_rpg_profiles',['daily_reward_date'=>date('Y-m-d')]);return $qty;
    }

    public function user_report($user_id)
    {
        $profile=$this->get_profile($user_id);$episodes=$this->episodes(true,$user_id);$mastery=$this->db->select('COUNT(*) total,AVG(mastery_score) average,SUM(mastery_score>=60) mastered',false)->where('user_id',(int)$user_id)->get('learn_english_rpg_word_mastery')->row_array();$types=$this->db->select('challenge_type,COUNT(*) attempts,SUM(is_correct) correct',false)->where('user_id',(int)$user_id)->group_by('challenge_type')->get('learn_english_rpg_activity')->result_array();return compact('profile','episodes','mastery','types')+['inventory'=>$this->get_inventory($user_id),'weak_words'=>$this->weakest_words($user_id,15),'story_choices'=>$this->db->where('user_id',(int)$user_id)->order_by('created_at')->get('learn_english_rpg_story_choices')->result_array()];
    }

    private function record_learning($user_id,$code,$scene_number,array $scene,$correct)
    {
        if(!$this->db->table_exists('learn_english_rpg_activity'))return;$word=(string)($scene['word'][0]??'');$meaning=(string)($scene['word'][1]??'');$this->db->insert('learn_english_rpg_activity',['user_id'=>(int)$user_id,'episode_code'=>$code,'scene_number'=>(int)$scene_number,'challenge_type'=>$scene['challenge_type']??'choice','is_correct'=>$correct?1:0,'vocabulary_word'=>$word?:null]);$this->db->set($correct?'total_correct':'total_wrong',($correct?'total_correct':'total_wrong').'+1',false)->where('user_id',(int)$user_id)->update('learn_english_rpg_profiles');if($word==='')return;$row=$this->db->get_where('learn_english_rpg_word_mastery',['user_id'=>(int)$user_id,'word'=>$word])->row_array();if($row)$this->db->set($correct?'correct_count':'wrong_count',($correct?'correct_count':'wrong_count').'+1',false)->set('mastery_score','GREATEST(-10,LEAST(100,mastery_score+'.($correct?'12':'-8').'))',false)->set('last_seen_at',date('Y-m-d H:i:s'))->where(['user_id'=>(int)$user_id,'word'=>$word])->update('learn_english_rpg_word_mastery');else $this->db->insert('learn_english_rpg_word_mastery',['user_id'=>(int)$user_id,'word'=>$word,'meaning'=>$meaning,'correct_count'=>$correct?1:0,'wrong_count'=>$correct?0:1,'mastery_score'=>$correct?12:-8,'last_seen_at'=>date('Y-m-d H:i:s')]);
    }

    private function award_loot($user_id,$code,$scene_number)
    {
        if(!$this->db->table_exists('learn_english_rpg_loot_log')||$scene_number%3!==0)return null;
        if($this->db->get_where('learn_english_rpg_loot_log',['user_id'=>(int)$user_id,'episode_code'=>$code,'scene_number'=>(int)$scene_number])->row_array())return null;
        $items=[['bronze_sword','Bronze Word Sword','⚔️','weapon'],['forest_shield','Guardian Shield','🛡️','armor'],['heart_potion','Heart Potion','🧪','potion'],['silver_key','Silver Quest Key','🗝️','key'],['wisdom_crystal','Wisdom Crystal','💎','artifact']];$item=$items[(int)(($scene_number/3-1)%count($items))];
        $this->db->trans_start();$existing=$this->db->get_where('learn_english_rpg_inventory',['user_id'=>(int)$user_id,'item_code'=>$item[0]])->row_array();if($existing)$this->db->set('quantity','quantity+1',false)->where('id',$existing['id'])->update('learn_english_rpg_inventory');else $this->db->insert('learn_english_rpg_inventory',['user_id'=>(int)$user_id,'item_code'=>$item[0],'item_name'=>$item[1],'item_icon'=>$item[2],'item_type'=>$item[3],'quantity'=>1]);$this->db->insert('learn_english_rpg_loot_log',['user_id'=>(int)$user_id,'episode_code'=>$code,'scene_number'=>(int)$scene_number,'item_code'=>$item[0]]);$this->db->trans_complete();return ['code'=>$item[0],'name'=>$item[1],'icon'=>$item[2],'type'=>$item[3]];
    }

    private function hydrate($row){$row['vocabulary']=json_decode($row['vocabulary_json']?:'[]',true)?:[];$row['is_completed']=(bool)$row['is_completed'];return $row;}
}
