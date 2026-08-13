<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Learn_battle_model — Mode Battle adu cepat 2 pemain (berbasis polling).
 *
 * Alur: host buat room (soal dibekukan) → guest join via kode → keduanya
 * menjawab soal yang sama; skor & progress disinkron lewat polling.
 * Penilaian & penentuan pemenang dilakukan di server.
 */
class Learn_battle_model extends CI_Model
{
    // ── Pool soal (CRUD admin) ────────────────────────────────────────────────

    public function get_questions($active_only = false)
    {
        if ($active_only) $this->db->where('is_active', 1);
        return $this->db->order_by('id', 'DESC')->get('learn_battle_questions')->result_array();
    }

    public function get_question($id)
    {
        return $this->db->get_where('learn_battle_questions', ['id' => (int) $id])->row_array();
    }

    public function count_active_questions()
    {
        return (int) $this->db->where('is_active', 1)->count_all_results('learn_battle_questions');
    }

    public function create_question($data)
    {
        $this->db->insert('learn_battle_questions', $this->_clean_q($data));
        return (int) $this->db->insert_id();
    }

    public function update_question($id, $data)
    {
        $this->db->where('id', (int) $id)->update('learn_battle_questions', $this->_clean_q($data));
        return $this->db->affected_rows() >= 0;
    }

    public function delete_question($id)
    {
        $this->db->where('id', (int) $id)->delete('learn_battle_questions');
        return $this->db->affected_rows() > 0;
    }

    private function _clean_q($data)
    {
        $correct = (int) ($data['correct_option'] ?? 0);
        return [
            'question'       => trim((string) $data['question']),
            'option_a'       => trim((string) $data['option_a']),
            'option_b'       => trim((string) $data['option_b']),
            'option_c'       => trim((string) ($data['option_c'] ?? '')) ?: null,
            'option_d'       => trim((string) ($data['option_d'] ?? '')) ?: null,
            'correct_option' => ($correct >= 0 && $correct <= 3) ? $correct : 0,
            'category'       => trim((string) ($data['category'] ?? '')) ?: null,
            'is_active'      => (int) (bool) ($data['is_active'] ?? 1),
        ];
    }

    // ── Battle sessions ─────────────────────────────────────────────────────
    public function get_sessions($open_only=false)
    {
        if($open_only){$now=date('Y-m-d H:i:s');$this->db->where('status','open')->group_start()->where('start_time IS NULL',null,false)->or_where('start_time <=',$now)->group_end()->group_start()->where('end_time IS NULL',null,false)->or_where('end_time >=',$now)->group_end();}
        return $this->db->select('s.*,(SELECT COUNT(*) FROM learn_battle_session_questions sq WHERE sq.session_id=s.id) question_pool_count',false)->order_by('id','DESC')->get('learn_battle_sessions s')->result_array();
    }
    public function get_session($id){return $this->db->get_where('learn_battle_sessions',['id'=>(int)$id])->row_array();}
    public function save_session($id,array $data,$user_id)
    {
        $max_mode=$data['player_limit_mode']??'limited';$payload=['title'=>trim($data['title']),'description'=>trim($data['description']??'')?:null,'status'=>in_array($data['status']??'draft',['draft','open','closed','archived'],true)?$data['status']:'draft','question_count'=>max(3,min(50,(int)$data['question_count'])),'time_limit_seconds'=>max(30,min(7200,(int)$data['time_limit_seconds'])),'max_players'=>$max_mode==='unlimited'?null:max(2,min(100,(int)$data['max_players'])),'shuffle_questions'=>!empty($data['shuffle_questions'])?1:0,'start_time'=>trim($data['start_time']??'')?:null,'end_time'=>trim($data['end_time']??'')?:null];
        if($id){$this->db->where('id',(int)$id)->update('learn_battle_sessions',$payload);return (int)$id;}
        $payload['code']='BTL'.strtoupper(substr(bin2hex(random_bytes(5)),0,8));$payload['created_by']=(int)$user_id;$this->db->insert('learn_battle_sessions',$payload);return (int)$this->db->insert_id();
    }
    public function get_session_questions($session_id){return $this->db->select('q.*,sq.sort_order')->from('learn_battle_session_questions sq')->join('learn_battle_questions q','q.id=sq.question_id')->where('sq.session_id',(int)$session_id)->order_by('sq.sort_order')->get()->result_array();}
    public function set_session_questions($session_id,array $ids){$ids=array_values(array_unique(array_filter(array_map('intval',$ids))));$this->db->trans_start();$this->db->delete('learn_battle_session_questions',['session_id'=>(int)$session_id]);foreach($ids as $i=>$qid)if($this->get_question($qid))$this->db->insert('learn_battle_session_questions',['session_id'=>(int)$session_id,'question_id'=>$qid,'sort_order'=>$i+1]);$this->db->trans_complete();return $this->db->trans_status();}
    public function import_session_csv($session_id,$path)
    {
        $h=fopen($path,'r');if(!$h)throw new RuntimeException('File tidak dapat dibaca.');$header=fgetcsv($h);$expected=['question','option_a','option_b','option_c','option_d','correct_answer','category'];$header=array_map(function($v){return strtolower(trim((string)$v));},$header?:[]);if(array_slice($header,0,7)!==$expected){fclose($h);throw new RuntimeException('Header CSV tidak sesuai template.');}$ids=[];$skipped=0;while(($r=fgetcsv($h))!==false){$r=array_pad($r,7,'');if(trim($r[0])===''||trim($r[1])===''||trim($r[2])===''){$skipped++;continue;}$key=strtoupper(trim($r[5]));$correct=array_search($key,['A','B','C','D'],true);if($correct===false){$skipped++;continue;}$ids[]=$this->create_question(['question'=>$r[0],'option_a'=>$r[1],'option_b'=>$r[2],'option_c'=>$r[3],'option_d'=>$r[4],'correct_option'=>$correct,'category'=>$r[6],'is_active'=>1]);}fclose($h);$existing=array_column($this->get_session_questions($session_id),'id');$this->set_session_questions($session_id,array_merge($existing,$ids));return ['imported'=>count($ids),'skipped'=>$skipped];
    }

    /** Import hasil normalisasi parser Bank Soal ke pool Battle dan sesi tujuan. */
    public function import_normalized_questions($session_id, array $questions)
    {
        if (! $this->get_session($session_id)) {
            throw new RuntimeException('Sesi battle tidak ditemukan.');
        }

        $ids = [];
        $skipped = 0;
        $essay = 0;
        $unsupported_options = 0;
        $this->db->trans_start();

        foreach ($questions as $question) {
            if (($question['status'] ?? '') !== 'ok') {
                $skipped++;
                continue;
            }
            if (($question['type'] ?? '') !== 'multiple_choice') {
                $essay++;
                $skipped++;
                continue;
            }

            $options = $question['options'] ?? [];
            $correct = (int) ($question['correct_index'] ?? -1);
            // Mesin Battle saat ini memakai maksimum empat pilihan (A-D).
            if ($correct < 0 || $correct > 3 || ! isset($options[$correct])) {
                $unsupported_options++;
                $skipped++;
                continue;
            }

            $texts = [];
            for ($i = 0; $i < 4; $i++) {
                $texts[$i] = isset($options[$i]['text']) ? trim((string) $options[$i]['text']) : '';
            }
            if ($texts[0] === '' || $texts[1] === '') {
                $skipped++;
                continue;
            }

            $category = ! empty($question['tags'])
                ? implode(', ', (array) $question['tags'])
                : trim(($question['subject_label'] ?? '') . ' · ' . ucfirst($question['difficulty'] ?? 'medium'), ' ·');
            $ids[] = $this->create_question([
                'question' => $question['question_text'],
                'option_a' => $texts[0],
                'option_b' => $texts[1],
                'option_c' => $texts[2],
                'option_d' => $texts[3],
                'correct_option' => $correct,
                'category' => $category,
                'is_active' => 1,
            ]);
        }

        $existing = array_column($this->get_session_questions($session_id), 'id');
        $this->set_session_questions($session_id, array_merge($existing, $ids));
        $this->db->trans_complete();
        if (! $this->db->trans_status()) {
            throw new RuntimeException('Gagal menyimpan hasil import soal battle.');
        }

        return [
            'imported' => count($ids),
            'skipped' => $skipped,
            'essay' => $essay,
            'unsupported_options' => $unsupported_options,
        ];
    }

    // ── Room lifecycle ────────────────────────────────────────────────────────

    public function create_room($host_id, $host_name, $session_id)
    {
        $session=$this->get_session($session_id);$now=date('Y-m-d H:i:s');if(!$session||$session['status']!=='open'||(!empty($session['start_time'])&&$now<$session['start_time'])||(!empty($session['end_time'])&&$now>$session['end_time']))return ['ok'=>false,'message'=>'Sesi battle tidak tersedia atau berada di luar jadwal.'];
        $count=max(3,min(50,(int)$session['question_count']));

        // Bekukan soal acak dari pool aktif.
        $ids=$this->db->select('q.id')->from('learn_battle_session_questions sq')->join('learn_battle_questions q','q.id=sq.question_id')->where('sq.session_id',(int)$session_id)->where('q.is_active',1)->order_by(!empty($session['shuffle_questions'])?'RAND()':'sq.sort_order')->limit($count)->get()->result_array();
        $ids = array_map(function ($r) { return (int) $r['id']; }, $ids);

        if (count($ids) < $count) {
            return ['ok' => false, 'message' => 'Soal aktif pada sesi ini belum cukup. Dibutuhkan ' . $count . ', tersedia ' . count($ids) . '.'];
        }

        $code = $this->_unique_code();
        $max_players = $session['max_players']===null?null:(int)$session['max_players'];
        $this->db->insert('learn_battle_rooms', [
            'code'           => $code,
            'battle_session_id'=>(int)$session_id,
            'status'         => 'waiting',
            'question_count' => count($ids),
            'question_ids'   => json_encode(array_values($ids)),
            'max_players'    => $max_players,
            'time_limit_seconds'=>(int)$session['time_limit_seconds'],
            'host_user_id'   => (int) $host_id,
            'host_name'      => $host_name,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
        $room_id = (int) $this->db->insert_id();
        $this->db->insert('learn_battle_participants', ['room_id'=>$room_id,'user_id'=>(int)$host_id,'player_name'=>$host_name,'is_host'=>1]);
        return ['ok' => true, 'code' => $code, 'id' => $room_id];
    }

    public function join_room($code, $guest_id, $guest_name)
    {
        $room = $this->get_room_by_code($code);
        if (! $room) return ['ok' => false, 'message' => 'Kode room tidak ditemukan.'];
        if ($room['status'] !== 'waiting') return ['ok' => false, 'message' => 'Room sudah dimulai atau selesai.'];
        if ($this->get_participant((int)$room['id'], $guest_id)) return ['ok'=>true,'code'=>$room['code'],'id'=>(int)$room['id']];
        $count = $this->participant_count((int)$room['id']);
        if ($room['max_players'] !== null && $count >= (int)$room['max_players']) return ['ok'=>false,'message'=>'Room sudah penuh.'];
        $this->db->insert('learn_battle_participants', ['room_id'=>(int)$room['id'],'user_id'=>(int)$guest_id,'player_name'=>$guest_name,'is_host'=>0]);
        if (! $this->db->affected_rows()) return ['ok'=>false,'message'=>'Gagal bergabung ke room.'];
        return ['ok' => true, 'code' => $room['code'], 'id' => (int) $room['id']];
    }

    public function start_room($code, $host_id)
    {
        $room=$this->get_room_by_code($code);
        if(!$room || $room['status']!=='waiting') return ['ok'=>false,'message'=>'Room tidak dapat dimulai.'];
        $host=$this->get_participant((int)$room['id'],$host_id);
        if(!$host || !(int)$host['is_host']) return ['ok'=>false,'message'=>'Hanya host yang dapat memulai.'];
        if($this->participant_count((int)$room['id'])<2) return ['ok'=>false,'message'=>'Minimal 2 pemain diperlukan.'];
        $this->db->where('id',(int)$room['id'])->where('status','waiting')->update('learn_battle_rooms',['status'=>'playing','started_at'=>date('Y-m-d H:i:s')]);
        return ['ok'=>$this->db->affected_rows()>0,'message'=>'Battle dimulai.'];
    }

    public function get_room($id)
    {
        return $this->db->get_where('learn_battle_rooms', ['id' => (int) $id])->row_array();
    }

    public function get_room_by_code($code)
    {
        return $this->db->get_where('learn_battle_rooms', ['code' => $code])->row_array();
    }

    /** Soal untuk dimainkan (tanpa kunci jawaban). */
    public function get_room_questions($room)
    {
        $ids = json_decode($room['question_ids'] ?? '[]', true);
        if (empty($ids)) return [];
        $rows = $this->db->where_in('id', $ids)->get('learn_battle_questions')->result_array();
        // Pertahankan urutan sesuai question_ids
        $map = [];
        foreach ($rows as $r) { $map[(int) $r['id']] = $r; }
        $ordered = [];
        foreach ($ids as $id) {
            if (! isset($map[$id])) continue;
            $q = $map[$id];
            $ordered[] = [
                'id'       => (int) $q['id'],
                'question' => $q['question'],
                'options'  => array_values(array_filter([
                    $q['option_a'], $q['option_b'], $q['option_c'], $q['option_d'],
                ], function ($v) { return $v !== null && $v !== ''; })),
            ];
        }
        return $ordered;
    }

    public function get_participants($room_id)
    { return $this->db->where('room_id',(int)$room_id)->order_by('is_host','DESC')->order_by('joined_at','ASC')->get('learn_battle_participants')->result_array(); }
    public function get_participant($room_id,$user_id)
    { return $this->db->get_where('learn_battle_participants',['room_id'=>(int)$room_id,'user_id'=>(int)$user_id])->row_array(); }
    public function participant_count($room_id)
    { return (int)$this->db->where('room_id',(int)$room_id)->count_all_results('learn_battle_participants'); }

    public function role_of($room, $user_id)
    {
        $p=$this->get_participant((int)$room['id'],$user_id);
        return $p ? ((int)$p['is_host']?'host':'player') : null;
    }

    /**
     * Submit jawaban untuk soal pada posisi `index` (0-based).
     * Server yang menilai. Hanya menerima jawaban untuk posisi = progress saat ini.
     */
    public function submit_answer($room_id, $user_id, $index, $selected)
    {
        $room = $this->get_room($room_id);
        $this->_expire_room($room);
        $room = $this->get_room($room_id);
        if (! $room || $room['status'] !== 'playing') {
            return ['ok' => false, 'message' => 'Room tidak aktif.'];
        }
        $player=$this->get_participant($room_id,$user_id);
        if(!$player) return ['ok'=>false,'message'=>'Kamu bukan pemain di room ini.'];
        $progress=(int)$player['progress'];
        $index    = (int) $index;

        // Abaikan jika tidak berurutan (anti-dobel / out-of-order).
        if ($index !== $progress) {
            return ['ok' => true, 'ignored' => true, 'progress' => $progress];
        }

        $ids = json_decode($room['question_ids'] ?? '[]', true);
        if (! isset($ids[$index])) {
            return ['ok' => false, 'message' => 'Soal tidak valid.'];
        }
        $q = $this->get_question((int) $ids[$index]);
        $is_correct = $q && ((int) $selected === (int) $q['correct_option']);

        $new_progress = $progress + 1;
        $update=['progress'=>$new_progress,'score'=>(int)$player['score']+($is_correct?1:0)];
        if ($new_progress >= (int) $room['question_count']) {
            $update['finished']=1;$update['finished_at']=(new DateTime('now'))->format('Y-m-d H:i:s.u');
        }
        $this->db->where('id',(int)$player['id'])->where('progress',$progress)->update('learn_battle_participants',$update);
        if(!$this->db->affected_rows()) { $fresh=$this->get_participant($room_id,$user_id); return ['ok'=>true,'ignored'=>true,'progress'=>(int)$fresh['progress']]; }

        // Cek apakah battle selesai (kedua pemain selesai).
        $this->_maybe_finish($room_id);

        return [
            'ok'          => true,
            'is_correct'  => (bool) $is_correct,
            'correct'     => $q ? (int) $q['correct_option'] : null,
            'progress'    => $new_progress,
            'finished'    => $new_progress >= (int) $room['question_count'],
        ];
    }

    /** State untuk polling. */
    public function state($room)
    {
        $this->_expire_room($room);$room=$this->get_room((int)$room['id']);
        $players=array_map(function($p){return ['user_id'=>(int)$p['user_id'],'name'=>$p['player_name'],'is_host'=>(bool)$p['is_host'],'score'=>(int)$p['score'],'progress'=>(int)$p['progress'],'finished'=>(bool)$p['finished'],'finished_at'=>$p['finished_at']];},$this->get_participants((int)$room['id']));
        $remaining=$room['status']==='playing'&&$room['started_at']?max(0,(strtotime($room['started_at'])+(int)$room['time_limit_seconds'])-time()):(int)$room['time_limit_seconds'];
        return [
            'status'         => $room['status'],
            'question_count' => (int) $room['question_count'],
            'max_players'    => $room['max_players']===null?null:(int)$room['max_players'],
            'players'        => $players,
            'remaining'      => $remaining,
            'winner_user_id' => $room['winner_user_id'] !== null ? (int) $room['winner_user_id'] : null,
        ];
    }

    // ── Internal ──────────────────────────────────────────────────────────────

    /** Selesai setelah semua pemain yang start telah selesai; tercepat menang. */
    private function _maybe_finish($room_id)
    {
        $room = $this->get_room($room_id);
        if (! $room || $room['status'] !== 'playing') return;
        $players=$this->get_participants($room_id);
        if(count($players)<2) return;
        foreach($players as $p) if(!(int)$p['finished']) return;
        $completed=array_values(array_filter($players,function($p)use($room){return (int)$p['progress']>=(int)$room['question_count'];}));
        usort($completed,function($a,$b){return strcmp((string)$a['finished_at'],(string)$b['finished_at']);});
        $winner=$completed?(int)$completed[0]['user_id']:null;

        $this->db->where('id', $room_id)->where('status', 'playing')->update('learn_battle_rooms', [
            'status'         => 'finished',
            'winner_user_id' => $winner,
            'finished_at'    => date('Y-m-d H:i:s'),
        ]);

        // Beri poin sekali (guard: hanya jika update di atas mengubah status).
        if ($this->db->affected_rows() > 0) {
            $this->_award_points($room, $winner, $players);
        }
    }

    private function _expire_room($room)
    {
        if(!$room||$room['status']!=='playing'||!$room['started_at'])return;
        $deadline=strtotime($room['started_at'])+(int)$room['time_limit_seconds'];if(time()<$deadline)return;
        $stamp=date('Y-m-d H:i:s.000000',$deadline);
        $this->db->where('room_id',(int)$room['id'])->where('finished',0)->update('learn_battle_participants',['finished'=>1,'finished_at'=>$stamp]);
        $this->_maybe_finish((int)$room['id']);
    }

    private function _award_points($room, $winner, array $players)
    {
        if (! isset($this->Learn_points_model)) {
            $this->load->model('Learn_points_model');
        }
        foreach ($players as $player) {
            $uid=(int)$player['user_id'];
            if (! $uid) continue;
            $this->Learn_points_model->award_points($uid, 'battle.play', 'battle_room', (int) $room['id'], 'Ikut Mode Battle');
            if ($winner && $uid === $winner) {
                $this->Learn_points_model->award_points($uid, 'battle.win', 'battle_room', (int) $room['id'], 'Menang Mode Battle');
            }
        }
    }

    private function _unique_code()
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // tanpa karakter ambigu
        do {
            $code = '';
            for ($i = 0; $i < 5; $i++) { $code .= $chars[random_int(0, strlen($chars) - 1)]; }
        } while ($this->db->where('code', $code)->count_all_results('learn_battle_rooms') > 0);
        return $code;
    }

    // ── Stats (admin) ─────────────────────────────────────────────────────────

    public function stats()
    {
        $questions = (int) $this->db->count_all('learn_battle_questions');
        $active    = (int) $this->db->where('is_active', 1)->count_all_results('learn_battle_questions');
        $rooms     = (int) $this->db->count_all('learn_battle_rooms');
        $finished  = (int) $this->db->where('status', 'finished')->count_all_results('learn_battle_rooms');
        return compact('questions', 'active', 'rooms', 'finished');
    }

    public function recent_rooms($limit = 15)
    {
        return $this->db->order_by('id', 'DESC')->limit($limit)->get('learn_battle_rooms')->result_array();
    }
}
