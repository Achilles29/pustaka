<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Play_game — controller publik untuk memainkan mini game.
 * Extends CI_Controller because member pages use their standalone layout.
 * Authentication is enforced centrally in the constructor for every route.
 */
class Play_game extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $user = $this->session->userdata('auth_user');
        $public_battle=$this->uri->segment(2)==='battle';
        if (empty($user['id'])&&!$public_battle) {
            if ($this->input->is_ajax_request()) {
                $this->output
                    ->set_status_header(401)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['ok' => false, 'message' => 'Silakan login untuk mengakses Arena Belajar.']));
                $this->output->_display();
                exit;
            }
            $return_uri=uri_string();
            if(!empty($_SERVER['QUERY_STRING']))$return_uri.='?'.$_SERVER['QUERY_STRING'];
            $this->session->set_flashdata('redirect_after_login',$return_uri);
            redirect('login');
            return;
        }

        $this->load->model('Learn_games_model');
        $this->load->model('Learn_points_model');
        $this->load->model('Learn_rewards_model');
        $this->load->model('Learn_flashcards_model');
        $this->load->model('Learn_story_model');
        $this->load->model('Learn_notifications_model');
        $this->load->model('Learn_battle_model');
        $this->load->model('Learn_report_model');
        $this->load->model('Learn_english_rpg_model');
        $this->load->model('Learn_english_rpg_world_model');
        $this->load->model('Learn_progress_model');
        $this->load->model('Math_expedition_model');
        $this->load->model('Member_model');
        $this->load->model('Quiz_config_model');
        $module=null;$s2=$this->uri->segment(2);$s3=$this->uri->segment(3);
        if($s2==='english-quest')$module='english_rpg';elseif($s2==='flashcard')$module='flashcard';elseif($s2==='cerita')$module='story';elseif($s2==='battle')$module='battle';elseif($s2==='matematika')$module='mini_games';elseif(in_array($s2,['pilih','play'],true)&&in_array($s3,['memory_match','speed_math','word_scramble'],true))$module='mini_games';
        if(!empty($user['id'])&&$module&&($blocked=$this->Learn_progress_model->blocked((int)$user['id'],$module))){$message='Akses ke modul ini dinonaktifkan oleh admin.'.(!empty($blocked['reason'])?' Alasan: '.$blocked['reason']:'');if($this->input->is_ajax_request()){$this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode(['ok'=>false,'message'=>$message],JSON_UNESCAPED_UNICODE));$this->output->_display();exit;}show_error(html_escape($message),403,'Akses Game Dinonaktifkan');}
    }

    /** Halaman daftar semua game */
    public function index()
    {
        $game_types = $this->Learn_games_model->get_game_types(true);
        $user       = $this->_current_user();
		$learning_summary = null;
		if ($user) {
			$report = $this->Learn_report_model->get_report((int) $user['id']);
			$learning_summary = [
				'points' => (int) ($report['total_points'] ?? 0),
				'quiz_attempts' => (int) ($report['quiz']['attempts'] ?? 0),
				'games_played' => (int) ($report['games']['played'] ?? 0),
				'flashcards_known' => (int) ($report['flashcard']['known'] ?? 0),
				'badges' => count($report['badges'] ?? []),
			];
		}

        $this->load->view('game/lobby', [
            'title'        => 'Arena Belajar',
            'game_types'   => $game_types,
            'user'         => $user,
			'learning_summary' => $learning_summary,
            'unread_notif' => $user ? $this->Learn_notifications_model->unread_count($user['id']) : 0,
        ]);
    }

    // ── Math Expedition ─────────────────────────────────────────────────────

    /** Peta level matematika: satu level terbuka setelah level sebelumnya lulus. */
    public function math_expedition()
    {
        $user = $this->_current_user();
        $levels = $this->Math_expedition_model->levels_for_user((int) $user['id']);
        $previous_passed = true;
        foreach ($levels as &$level) {
            $level['is_unlocked'] = $previous_passed;
            $level['is_completed'] = ! empty($level['completed_at']);
            $stage = (int) $level['stage_number'];
            $level['chapter'] = $stage <= 4 ? 1 : ($stage <= 8 ? 2 : 3);
            $previous_passed = $level['is_completed'];
        }
        unset($level);
        $this->load->view('game/math_expedition', [
            'title' => 'Math Expedition',
            'user' => $user,
            'levels' => $levels,
            'overview' => $this->Math_expedition_model->overview((int) $user['id']),
        ]);
    }

    /** Menyiapkan sebuah putaran terverifikasi untuk level yang dipilih. */
    public function math_expedition_level($code)
    {
        $user = $this->_current_user();
        $level = $this->Math_expedition_model->level_by_code($code);
        if (! $level) { show_404(); return; }

        $levels = $this->Math_expedition_model->levels_for_user((int) $user['id']);
        $is_unlocked = true;
        foreach ($levels as $row) {
            if ((int) $row['stage_number'] >= (int) $level['stage_number']) break;
            if (empty($row['completed_at'])) { $is_unlocked = false; break; }
        }
        if (! $is_unlocked && empty($user['is_superadmin'])) {
            $this->session->set_flashdata('error', 'Selesaikan level sebelumnya minimal 3 dari 5 soal untuk membuka level ini.');
            redirect('belajar/matematika');
            return;
        }

        $questions = $this->Math_expedition_model->questions_for_level((int) $level['id'], 5);
        if (count($questions) < 3) {
            show_error('Soal untuk level ini belum cukup tersedia.', 503, 'Math Expedition');
            return;
        }
        $run = [
            'token' => bin2hex(random_bytes(20)),
            'level_id' => (int) $level['id'],
            'level_code' => (string) $level['code'],
            'question_ids' => array_map(static function ($question) { return (int) $question['id']; }, $questions),
            'position' => 0,
            'correct' => 0,
            'started_at' => time(),
        ];
        $this->session->set_userdata('math_expedition_run', $run);
        $this->load->view('game/math_expedition_play', [
            'title' => $level['title'] . ' — Math Expedition',
            'user' => $user,
            'level' => $level,
            'experience' => $this->_math_experience($level),
            'question' => $this->_math_question_payload($questions[0]),
            'total' => count($questions),
            'run_token' => $run['token'],
        ]);
    }

    /** AJAX: validasi satu jawaban, kirim pembahasan, dan catat progres saat selesai. */
    public function math_expedition_submit()
    {
        if (! $this->input->is_ajax_request()) { show_404(); return; }
        $user = $this->_current_user();
        $run = (array) $this->session->userdata('math_expedition_run');
        $question_id = (int) $this->input->post('question_id');
        $token = (string) $this->input->post('run_token');
        if (empty($user['id']) || empty($run['token']) || ! hash_equals((string) $run['token'], $token)) {
            return $this->_json(['ok' => false, 'message' => 'Sesi permainan sudah berakhir. Mulai ulang level untuk melanjutkan.'], 409);
        }
        $expected = $run['question_ids'][$run['position']] ?? 0;
        if ($question_id !== (int) $expected) {
            return $this->_json(['ok' => false, 'message' => 'Urutan soal tidak sesuai. Muat ulang level untuk memulai kembali.'], 409);
        }
        $question = $this->Math_expedition_model->question($question_id);
        if (! $question || (int) $question['level_id'] !== (int) $run['level_id']) {
            return $this->_json(['ok' => false, 'message' => 'Soal tidak tersedia.'], 404);
        }
        $answer = strtoupper(trim((string) $this->input->post('answer')));
        $correct = hash_equals((string) $question['correct_option'], $answer);
        if ($correct) $run['correct'] = (int) $run['correct'] + 1;
        $run['position'] = (int) $run['position'] + 1;
        $finished = $run['position'] >= count($run['question_ids']);

        $response = [
            'ok' => true,
            'correct' => $correct,
            'correct_option' => (string) $question['correct_option'],
            'explanation' => (string) $question['explanation'],
            'tip' => (string) $question['tip'],
            'finished' => $finished,
            'position' => (int) $run['position'],
            'total' => count($run['question_ids']),
        ];
        if ($finished) {
            $level = $this->Math_expedition_model->level_by_code($run['level_code']);
            $result = $this->Math_expedition_model->complete_level(
                (int) $user['id'], $level, (int) $run['correct'], count($run['question_ids']), time() - (int) $run['started_at']
            );
            $points = 0;
            if (! empty($result['passed'])) {
                $points = (int) $this->Learn_points_model->award_points(
                    (int) $user['id'], 'math.expedition.complete', 'math_level', (int) $level['id'], 'Tuntas Math Expedition: ' . $level['title']
                );
            }
            $response += [
                'score' => (int) $result['score'],
                'correct_count' => (int) $run['correct'],
                'passed' => ! empty($result['passed']),
                'passing_correct' => (int) $level['passing_correct'],
                'points_earned' => $points,
                'next_url' => base_url('belajar/matematika'),
            ];
            $this->session->unset_userdata('math_expedition_run');
        } else {
            $next_id = (int) $run['question_ids'][$run['position']];
            $next = $this->Math_expedition_model->question($next_id);
            $response['next_question'] = $this->_math_question_payload($next);
            $this->session->set_userdata('math_expedition_run', $run);
        }
        return $this->_json($response);
    }

    // ── English Quest RPG ───────────────────────────────────────────────────

    public function english_quest()
    {
        $user = $this->_current_user();
        $profile=$this->Learn_english_rpg_model->get_profile((int)$user['id'],$this->_display_name($user));
        $seasons=$this->Learn_english_rpg_model->seasons(true,(int)$user['id']);
        $this->load->view('game/english_rpg_seasons',['title'=>'English Quest','user'=>$user,'seasons'=>$seasons,'inventory'=>$this->Learn_english_rpg_model->get_inventory((int)$user['id']),'profile'=>$profile,'daily_quests'=>$this->Learn_english_rpg_model->daily_quests((int)$user['id'])]);
    }

    public function english_quest_season($season_id)
    {
        $user=$this->_current_user();$season=$this->Learn_english_rpg_model->season((int)$season_id,true);if(!$season){show_404();return;}
        if (($season['code'] ?? '') === 'season_2') { $this->english_quest_world(); return; }
        $episodes=$this->Learn_english_rpg_model->episodes(true,(int)$user['id'],(int)$season_id);if(!empty($user['is_superadmin']))foreach($episodes as &$row)$row['is_unlocked']=true;unset($row);
        $this->load->view('game/english_rpg_list',['title'=>'English Quest — '.$season['title'],'user'=>$user,'season'=>$season,'episodes'=>$episodes,'inventory'=>$this->Learn_english_rpg_model->get_inventory((int)$user['id']),'profile'=>$this->Learn_english_rpg_model->get_profile((int)$user['id'],$this->_display_name($user)),'daily_quests'=>$this->Learn_english_rpg_model->daily_quests((int)$user['id'])]);
    }

    /** Season 2 Chapter 1: explorable Lanternbrook map with objective chain. */
    public function english_quest_world()
    {
        $user = $this->_current_user();
        $world = $this->Learn_english_rpg_world_model->world((int) $user['id']);
        if (! $world) { show_error('The Season 2 world is not available yet.', 503, 'Season 2'); return; }
        $this->load->view('game/english_rpg_world', [
            'title' => 'English Quest — '.$world['map']['title'], 'user' => $user, 'world' => $world,
        ]);
    }

    public function english_quest_world_move()
    {
        if (! $this->input->is_ajax_request()) return $this->_json(['ok' => false, 'message' => 'Permintaan tidak valid.'], 400);
        $user = $this->_current_user();
        return $this->_json($this->Learn_english_rpg_world_model->move((int) $user['id'], (string) $this->input->post('direction', true)));
    }

    public function english_quest_world_interact()
    {
        if (! $this->input->is_ajax_request()) return $this->_json(['ok' => false, 'message' => 'Permintaan tidak valid.'], 400);
        $user = $this->_current_user();
        $result = $this->Learn_english_rpg_world_model->interact((int) $user['id'], (int) $this->input->post('npc_id', true));
        if (! empty($result['claimed']) && ! empty($result['reward'])) {
            $result['points_earned'] = (int) $this->Learn_points_model->award_points((int) $user['id'], 'game.complete', 'english_rpg_world_quest', (int) ($result['quest_id'] ?? 0), 'Menyelesaikan quest Season 2 English RPG');
        }
        return $this->_json($result, ! empty($result['ok']) ? 200 : 409);
    }

    public function english_quest_world_answer()
    {
        if (! $this->input->is_ajax_request()) return $this->_json(['ok' => false, 'message' => 'Permintaan tidak valid.'], 400);
        $user = $this->_current_user();
        $result = $this->Learn_english_rpg_world_model->answer_question(
            (int) $user['id'],
            (int) $this->input->post('npc_id', true),
            (int) $this->input->post('answer', true)
        );
        if (! empty($result['claimed']) && ! empty($result['reward'])) {
            $result['points_earned'] = (int) $this->Learn_points_model->award_points(
                (int) $user['id'], 'game.complete', 'english_rpg_world_quest',
                (int) ($result['quest_id'] ?? 0), 'Menyelesaikan quest Season 2 English RPG'
            );
        }
        return $this->_json($result, ! empty($result['ok']) ? 200 : 409);
    }

    public function english_quest_world_action()
    {
        if (! $this->input->is_ajax_request()) return $this->_json(['ok' => false, 'message' => 'Permintaan tidak valid.'], 400);
        $user = $this->_current_user();
        $result = $this->Learn_english_rpg_world_model->action((int) $user['id'], (string) $this->input->post('action_code', true));
        if (! empty($result['claimed']) && ! empty($result['reward'])) {
            $result['points_earned'] = (int) $this->Learn_points_model->award_points(
                (int) $user['id'], 'game.complete', 'english_rpg_world_quest',
                (int) ($result['quest_id'] ?? 0), 'Menyelesaikan quest Chapter 1 English RPG'
            );
        }
        return $this->_json($result, ! empty($result['ok']) ? 200 : 409);
    }

    public function english_quest_world_help()
    {
        if (! $this->input->is_ajax_request()) return $this->_json(['ok' => false, 'message' => 'Permintaan tidak valid.'], 400);
        $user = $this->_current_user();
        $result = $this->Learn_english_rpg_world_model->help((int) $user['id'], (string) $this->input->post('help_type', true));
        return $this->_json($result, ! empty($result['ok']) ? 200 : 409);
    }

    public function english_quest_play($code)
    {
        $user = $this->_current_user();
        $episode = $this->Learn_english_rpg_model->episode($code);
        if (! $episode) { show_404(); return; }
        $map = $this->Learn_english_rpg_model->episodes(true, (int) $user['id'], (int)($episode['season_id'] ?? 1));
        $entry = null;
        foreach ($map as $row) {
            if ($row['code'] === $code) $entry = $row;
        }
        if (empty($user['is_superadmin']) && (! $entry || empty($entry['is_unlocked']))) {
            $this->session->set_flashdata('error', 'Selesaikan chapter sebelumnya untuk membuka petualangan ini.');
            redirect('belajar/english-quest');
            return;
        }
        $progress = $this->Learn_english_rpg_model->get_progress((int)$user['id'],$code);
        $scenes = $this->Learn_english_rpg_model->scenes($code,(int)$user['id']);if(!$scenes){$this->session->set_flashdata('error','Episode ini belum memiliki adegan.');redirect('belajar/english-quest');return;}
        $hero_profile=$this->Learn_english_rpg_model->get_profile((int)$user['id'],$this->_display_name($user));if(isset($scenes[14])&&(int)$hero_profile['reputation']>=4){$scenes[14]['text']='Your kindness and courage are known across the realm. The people welcome you as a trusted hero. '.$scenes[14]['text'];$scenes[14]['translation']='Kebaikan dan keberanianmu dikenal di seluruh negeri. Penduduk menyambutmu sebagai pahlawan tepercaya. '.$scenes[14]['translation'];}
        $this->load->view('game/english_rpg', [
            'title' => 'English Quest — '.$episode['title'],'user' => $user,'episode' => $episode,
            'progress' => $progress,
            'scenes' => $scenes,'inventory'=>$this->Learn_english_rpg_model->get_inventory((int)$user['id']),'hero_profile'=>$hero_profile,
        ]);
    }

    public function english_quest_answer()
    {
        if (! $this->input->is_ajax_request()) return $this->_json(['ok'=>false,'message'=>'Permintaan tidak valid.'],400);
        $user = $this->_current_user();
        $code=(string)$this->input->post('episode_code',true);$episode=$this->Learn_english_rpg_model->episode($code);if(!$episode)return $this->_json(['ok'=>false,'message'=>'Episode tidak ditemukan.'],404);
        $result = $this->Learn_english_rpg_model->answer((int)$user['id'],(int)$this->input->post('scene'),(int)$this->input->post('choice'),$code);
        if (!empty($result['completed'])) {
            $points = $this->Learn_points_model->award_points((int)$user['id'],'game.complete','english_rpg',(int)$result['progress']['id'],'Menyelesaikan English Quest: '.$episode['title']);
            $result['points_earned'] = (int)$points;
        }
        return $this->_json($result,$result['ok']?200:409);
    }

    public function english_quest_sentence()
    {
        if(!$this->input->is_ajax_request())return $this->_json(['ok'=>false,'message'=>'Permintaan tidak valid.'],400);$user=$this->_current_user();$code=(string)$this->input->post('episode_code',true);if(!$this->Learn_english_rpg_model->episode($code))return $this->_json(['ok'=>false,'message'=>'Episode tidak ditemukan.'],404);$result=$this->Learn_english_rpg_model->answer_sentence((int)$user['id'],(int)$this->input->post('scene'),(string)$this->input->post('sentence'),$code);return $this->_json($result,$result['ok']?200:409);
    }

    public function english_quest_hero()
    {
        $user=$this->_current_user();$this->load->view('game/english_rpg_hero',['title'=>'Hero Profile','user'=>$user,'profile'=>$this->Learn_english_rpg_model->get_profile((int)$user['id'],$this->_display_name($user)),'inventory'=>$this->Learn_english_rpg_model->get_inventory((int)$user['id']),'weak_words'=>$this->Learn_english_rpg_model->weakest_words((int)$user['id'])]);
    }

    public function english_quest_story()
    {
        if(!$this->input->is_ajax_request())return $this->_json(['ok'=>false,'message'=>'Permintaan tidak valid.'],400);$user=$this->_current_user();$result=$this->Learn_english_rpg_model->answer_story((int)$user['id'],(int)$this->input->post('scene'),(int)$this->input->post('choice'),(string)$this->input->post('episode_code',true));return $this->_json($result,$result['ok']?200:409);
    }

    public function english_quest_hero_save()
    {
        $user=$this->_current_user();$this->Learn_english_rpg_model->get_profile((int)$user['id'],$this->_display_name($user));$this->Learn_english_rpg_model->save_profile((int)$user['id'],$this->input->post());$this->session->set_flashdata('success','Hero berhasil diperbarui.');redirect('belajar/english-quest/hero');
    }

    public function english_quest_quests()
    {
        $user=$this->_current_user();$this->load->view('game/english_rpg_quests',['title'=>'Side Quests','user'=>$user,'quests'=>$this->Learn_english_rpg_model->side_quests((int)$user['id']),'profile'=>$this->Learn_english_rpg_model->get_profile((int)$user['id'],$this->_display_name($user))]);
    }

    public function english_quest_claim($id)
    {
        $user=$this->_current_user();$reward=$this->Learn_english_rpg_model->claim_side_quest((int)$user['id'],$id);$this->session->set_flashdata($reward?'success':'error',$reward?'Hadiah '.$reward['reward_item_name'].' masuk ke inventory.':'Quest belum selesai atau hadiah sudah diambil.');redirect('belajar/english-quest/quests');
    }

    public function english_quest_review(){ $u=$this->_current_user();$this->load->view('game/english_rpg_review',['title'=>'Review Camp','user'=>$u,'words'=>$this->Learn_english_rpg_model->review_words((int)$u['id'])]); }
    public function english_quest_review_submit(){if(!$this->input->is_ajax_request())return $this->_json(['ok'=>false],400);$u=$this->_current_user();return $this->_json($this->Learn_english_rpg_model->submit_review((int)$u['id'],$this->input->post('word',true),$this->input->post('answer',true),$this->input->post('mode',true)==='speaking'?'speaking':'choice'));}
    public function english_quest_dictionary(){ $u=$this->_current_user();$this->load->view('game/english_rpg_dictionary',['title'=>'Adventure Dictionary','user'=>$u,'words'=>$this->Learn_english_rpg_model->review_words((int)$u['id'],200)]); }
    public function english_quest_equipment(){ $u=$this->_current_user();$this->load->view('game/english_rpg_equipment',['title'=>'Hero Equipment','user'=>$u,'inventory'=>$this->Learn_english_rpg_model->get_inventory((int)$u['id'])]); }
    public function english_quest_equip($id){$u=$this->_current_user();$this->Learn_english_rpg_model->equip_item((int)$u['id'],$id);redirect('belajar/english-quest/equipment');}
    public function english_quest_potion(){ $u=$this->_current_user();$code=$this->input->post('episode_code',true);$ok=$this->Learn_english_rpg_model->use_potion((int)$u['id'],$code);$this->session->set_flashdata($ok?'success':'error',$ok?'Nyawa dipulihkan.':'Potion tidak tersedia.');redirect('belajar/english-quest/'.$code); }
    public function english_quest_daily(){ $u=$this->_current_user();$qty=$this->Learn_english_rpg_model->claim_daily_reward((int)$u['id']);$this->session->set_flashdata($qty?'success':'error',$qty?'Daily reward: '.$qty.' Heart Potion.':'Hadiah hari ini sudah diambil.');redirect('belajar/english-quest'); }
    public function english_quest_report(){ $u=$this->_current_user();$this->load->view('game/english_rpg_report',['title'=>'Raport English RPG','user'=>$u,'report'=>$this->Learn_english_rpg_model->user_report((int)$u['id'])]); }

    public function english_quest_reset()
    {
        $user=$this->_current_user();$code=(string)$this->input->post('episode_code',true);$this->Learn_english_rpg_model->reset((int)$user['id'],$code);
        $this->session->set_flashdata('success','Petualangan dimulai kembali dari awal.');redirect('belajar/english-quest/'.$code);
    }

    // ── Latihan Soal (daftar sesi latihan untuk member) ───────────────────────

    /** Daftar sesi latihan yang terbuka; member klik untuk mengerjakan via /quiz/practice */
    public function latihan()
    {
        $user = $this->_current_user();
        $now = date('Y-m-d H:i:s');
		$filters = [
			'q' => trim((string) $this->input->get('q', true)),
			'subject_id' => (int) $this->input->get('subject_id', true),
			'grade_level_id' => (int) $this->input->get('grade_level_id', true),
			'difficulty' => (string) $this->input->get('difficulty', true),
			'availability' => (string) $this->input->get('availability', true),
		];
		$filters['difficulty'] = in_array($filters['difficulty'], ['easy', 'medium', 'hard', 'mixed'], true) ? $filters['difficulty'] : '';
		$filters['availability'] = in_array($filters['availability'], ['available', 'upcoming'], true) ? $filters['availability'] : 'available';
		$grades = $this->Quiz_config_model->get_grade_levels(true);
		$subjects = $this->Quiz_config_model->get_subjects(true);
		$recommendation = $this->practice_recommendation($user, $grades);
        $sessions = $this->db
			->select('s.code, s.title, s.question_count, s.time_limit_minutes, s.passing_score, s.start_time, s.end_time, s.difficulty_filter, sub.name AS subject_name, sub.color AS subject_color, sub.icon AS subject_icon, g.id AS grade_id, g.name AS grade_name')
            ->from('quiz_sessions s')
            ->join('quiz_subjects sub', 'sub.id = s.subject_id', 'left')
            ->join('quiz_grade_levels g', 'g.id = s.grade_level_id', 'left')
            ->where('s.type', 'practice')
            ->where('s.status', 'open')
            ->where('s.deleted_at IS NULL', null, false)
			->group_start()->where('s.end_time IS NULL', null, false)->or_where('s.end_time >=', $now)->group_end();
		if ($filters['q'] !== '') $this->db->group_start()->like('s.title', $filters['q'])->or_like('sub.name', $filters['q'])->or_like('g.name', $filters['q'])->group_end();
		if ($filters['subject_id']) $this->db->where('s.subject_id', $filters['subject_id']);
		if ($filters['grade_level_id']) $this->db->where('s.grade_level_id', $filters['grade_level_id']);
		if ($filters['difficulty'] !== '') $this->db->where('s.difficulty_filter', $filters['difficulty']);
		if ($filters['availability'] === 'upcoming') $this->db->where('s.start_time >', $now);
		else $this->db->group_start()->where('s.start_time IS NULL', null, false)->or_where('s.start_time <=', $now)->group_end();
		$sessions = $this->db
			->order_by('s.start_time', 'ASC')
            ->order_by('s.id', 'DESC')
            ->get()->result_array();
		foreach ($sessions as &$session) $session['is_recommended'] = ! empty($recommendation['grade_id']) && (int) $session['grade_id'] === (int) $recommendation['grade_id'];
		unset($session);
		$recommended_sessions = array_values(array_filter($sessions, function ($session) { return ! empty($session['is_recommended']); }));

        $this->load->view('game/latihan_list', [
            'title'    => 'Latihan Soal',
            'user'     => $user,
            'sessions' => $sessions,
			'subjects' => $subjects,
			'grades' => $grades,
			'filters' => $filters,
			'recommendation' => $recommendation,
			'recommended_sessions' => array_slice($recommended_sessions, 0, 3),
        ]);
    }

	private function practice_recommendation($user, array $grades)
	{
		if (empty($user['id'])) return [];
		$member = $this->Member_model->get_member_by_auth_user_id((int) $user['id']);
		if (empty($member['birth_date']) || strtotime($member['birth_date']) === false) return [];
		$age = (new DateTime($member['birth_date']))->diff(new DateTime('today'))->y;
		if ($age < 4) return ['age' => $age, 'label' => 'Eksplorasi awal', 'message' => 'Pilih latihan yang paling nyaman untukmu.'];
		if ($age === 4) $target = 'tk_a';
		elseif ($age === 5) $target = 'tk_b';
		elseif ($age <= 11) $target = 'sd_' . ($age - 5);
		elseif ($age <= 14) $target = 'smp_' . ($age - 5);
		elseif ($age <= 17) $target = 'sma_' . ($age - 5);
		elseif ($age <= 22) $target = 'pt';
		else $target = 'umum';
		foreach ($grades as $grade) if (($grade['code'] ?? '') === $target) return ['age' => $age, 'grade_id' => (int) $grade['id'], 'label' => $grade['name'], 'message' => 'Rekomendasi dihitung dari usia. Kamu tetap bebas memilih jenjang lain.'];
		return [];
	}

    // ── Notifikasi (member) ────────────────────────────────────────────────────

    /** Halaman notifikasi member (menandai semua terbaca saat dibuka) */
    public function notifikasi()
    {
        $user = $this->_current_user();
        if (! $user) {
            redirect('login');
            return;
        }

        $items = $this->Learn_notifications_model->get_for_user($user['id'], 50);
        // Tandai semua sebagai terbaca setelah dimuat
        $this->Learn_notifications_model->mark_read($user['id']);

        $this->load->view('game/notifications', [
            'title' => 'Notifikasi',
            'user'  => $user,
            'items' => $items,
        ]);
    }

    /** AJAX: tandai satu / semua notifikasi terbaca */
    public function notif_read()
    {
        $user = $this->_current_user();
        if (! $user || ! $this->input->is_ajax_request()) {
            return $this->_json(['ok' => false], 401);
        }
        $id = $this->input->post('id');
        $this->Learn_notifications_model->mark_read($user['id'], $id !== null && $id !== '' ? (int) $id : null);
        return $this->_json(['ok' => true, 'unread' => $this->Learn_notifications_model->unread_count($user['id'])]);
    }

    /** Halaman pilih set konten untuk game tertentu */
    public function choose($game_code)
    {
        $game_type = $this->Learn_games_model->get_game_type_by_code($game_code);
        if (!$game_type) show_404();

        // Untuk game yang tidak butuh konten DB (speed_math) langsung redirect ke play
        if (!$game_type['needs_content']) {
            redirect('belajar/play/' . $game_code);
        }

        $categories = $this->Learn_games_model->get_categories((int) $game_type['id'], true);
        $all_categories=$categories;$filters=['subject_id'=>(int)$this->input->get('subject_id',true),'grade_level_id'=>(int)$this->input->get('grade_level_id',true)];
        if(in_array($game_code,['word_scramble','memory_match'],true))$categories=array_values(array_filter($categories,function($c)use($filters){return (!$filters['subject_id']||(int)$c['subject_id']===$filters['subject_id'])&&(!$filters['grade_level_id']||(int)$c['grade_level_id']===$filters['grade_level_id']);}));

        // Load sets for each category
        foreach ($categories as &$cat) {
            $cat['sets'] = $this->Learn_games_model->get_sets($cat['id'], true);
        }
        unset($cat);

        $user = $this->_current_user();

        $this->load->view('game/choose', [
            'title'      => 'Pilih Konten — ' . $game_type['name'],
            'game_type'  => $game_type,
            'categories' => $categories,
            'user'       => $user,
            'filters'=>$filters,'all_categories'=>$all_categories,
        ]);
    }

    /**
     * Halaman play game.
     * - Memory Match: butuh set_id
     * - Speed Math: tidak butuh set_id, baca config dari GET
     */
    public function play($game_code, $set_id = null)
    {
        $game_type = $this->Learn_games_model->get_game_type_by_code($game_code);
        if (!$game_type) show_404();

        $user    = $this->_current_user();
        $content = [];

        if($game_code==='speed_math'){
            $levels=$this->_speed_math_levels();$level=(int)$this->input->get('level');
            if(!isset($levels[$level])){$this->load->view('game/speed_math_levels',['title'=>'Pilih Level Hitung Cepat','user'=>$user,'levels'=>$levels]);return;}
            $config=$levels[$level];$config['level']=$level;
            $this->load->view('game/speed_math',['title'=>'Hitung Cepat: '.$config['name'],'game_type'=>$game_type,'config'=>$config,'user'=>$user,'next_level'=>$levels[$level+1]??null,'next_game'=>$this->Learn_games_model->recommended_other_game($user?(int)$user['id']:0,$game_code)]);return;
        }

        if ($game_type['needs_content']) {
            if (!$set_id) {
                redirect('belajar/pilih/' . $game_code);
            }
            $set = $this->Learn_games_model->get_set($set_id);
            if (!$set || !$set['is_active']) show_404();

            $config_schema = json_decode($game_type['config_schema'] ?? '{}', true);
            $game_config = $this->_parse_game_config($game_type);
            $content_limit = $game_type['code'] === 'word_scramble'
                ? ($game_config['word_count'] ?? ($config_schema['word_count']['default'] ?? 10))
                : ($game_config['pairs'] ?? ($config_schema['pairs']['default'] ?? 6));
            $content = $this->Learn_games_model->get_game_content($set_id, (int) $content_limit);

            if (empty($content)) {
                redirect('belajar/pilih/' . $game_code . '?empty=1');
            }

            $this->load->view('game/' . $game_code, [
                'title'     => $game_type['name'] . ': ' . $set['name'],
                'game_type' => $game_type,
                'set'       => $set,
                'content'   => $content,
				'config'    => $game_config,
                'user'      => $user,
                'next_game' => $this->Learn_games_model->recommended_other_game($user?(int)$user['id']:0,$game_code),
            ]);
        } else {
            // Speed Math dan sejenisnya: baca config dari GET
            $config = $this->_parse_game_config($game_type);

            $this->load->view('game/' . $game_code, [
                'title'     => $game_type['name'],
                'game_type' => $game_type,
                'config'    => $config,
                'user'      => $user,
            ]);
        }
    }

    /**
     * API: catat skor selesai game — dipanggil via AJAX setelah game selesai
     */
    public function finish()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $game_code  = $this->input->post('game_code');
        $set_id     = $this->input->post('set_id') ?: null;
        $score      = (int) $this->input->post('score');
        $max_score  = (int) $this->input->post('max_score');
        $duration   = (int) $this->input->post('duration_seconds');
        $level      = max(0,(int)$this->input->post('level'));
        $user       = $this->_current_user();

        $game_type = $this->Learn_games_model->get_game_type_by_code($game_code);
        if (!$game_type) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['ok' => false]));
        }

        $session_id = $this->Learn_games_model->start_session(
            $game_type['id'],
            $user ? $user['id'] : null,
            $set_id,
            ['score_at_start' => 0, 'level'=>$level]
        );
        $this->Learn_games_model->finish_session($session_id, $score, $max_score, $duration);

        $points_earned = 0;
        $new_badges    = [];

        if ($user) {
            // Anti-farming: sesi wajar berdurasi >= 5 detik, dan poin game
            // dibatasi maksimal 10 award / 5 menit per user (cegah spam skrip).
            $legit = $duration >= 5
                && $this->Learn_points_model->recent_award_count($user['id'], 'game.complete', 5) < 10;

            if ($legit) {
                // Cek highscore sebelumnya (sebelum sesi ini)
                $prev_high = $this->Learn_games_model->get_user_highscore(
                    $user['id'], $game_type['id'], $set_id
                );

                $points_earned += (int) $this->Learn_points_model->award_points(
                    $user['id'], 'game.complete', 'game_session', $session_id,
                    'Selesaikan game: ' . $game_type['name']
                );

                // Bonus highscore hanya jika skor baru lebih tinggi dari sebelumnya
                if ($score > $prev_high && $prev_high > 0) {
                    $points_earned += (int) $this->Learn_points_model->award_points(
                        $user['id'], 'game.highscore', 'game_session', $session_id . '_hs',
                        'Rekor baru di ' . $game_type['name']
                    );
                }

                $new_badges = $this->_new_badges_for_user($user['id']);
            }
        }

        $this->output->set_content_type('application/json')->set_output(json_encode([
            'ok'            => true,
            'session_id'    => $session_id,
            'points_earned' => $points_earned,
            'new_badges'    => $new_badges,
        ]));
    }

    /**
     * API: ambil item konten untuk satu set (untuk game yang fetch via AJAX)
     */
    public function content_api($set_id)
    {
        $max = (int) ($this->input->get('pairs') ?: 6);
        $items = $this->Learn_games_model->get_game_content($set_id, $max);

        $safe = array_map(function ($item) {
            return [
                'id'         => $item['id'],
                'term'       => $item['term'],
                'definition' => $item['definition'],
            ];
        }, $items);

        $this->output->set_content_type('application/json')->set_output(json_encode($safe));
    }

    // ── Tukar Poin → Token Baca ───────────────────────────────────────────────

    /** Halaman katalog hadiah untuk member */
    public function hadiah()
    {
        $user = $this->_current_user();

        $data = [
            'title'        => 'Tukar Poin',
            'user'         => $user,
            'catalog'      => $this->Learn_rewards_model->get_catalog(true),
            'total_points' => $user ? $this->Learn_rewards_model->total_points($user['id']) : 0,
            'redemptions'  => $user ? $this->Learn_rewards_model->get_user_redemptions($user['id'], 10) : [],
            'has_member'   => $user ? (bool) $this->Learn_rewards_model->resolve_member_id($user['id']) : false,
        ];
        $this->load->view('game/rewards', $data);
    }

    /** Proses penukaran (AJAX POST) */
    public function redeem_reward()
    {
        $user = $this->_current_user();
        if (! $user) {
            return $this->_json(['ok' => false, 'message' => 'Kamu harus login untuk menukar poin.'], 401);
        }
        if (! $this->input->is_ajax_request()) {
            show_404();
            return;
        }

        $catalog_id = (int) $this->input->post('catalog_id');
        if ($catalog_id <= 0) {
            return $this->_json(['ok' => false, 'message' => 'Hadiah tidak valid.'], 400);
        }

        $result = $this->Learn_rewards_model->redeem((int) $user['id'], $catalog_id);
        return $this->_json($result, $result['ok'] ? 200 : 400);
    }

    // ── Flashcard (belajar mandiri) ───────────────────────────────────────────

    /** Daftar deck flashcard */
    public function flashcard()
    {
        $user  = $this->_current_user();
        $filters = ['q'=>trim((string)$this->input->get('q',true)),'subject_id'=>(int)$this->input->get('subject_id',true),'grade_level_id'=>(int)$this->input->get('grade_level_id',true)];
        $decks = $this->Learn_flashcards_model->get_decks(true, $filters);
        $all_decks = $this->Learn_flashcards_model->get_decks(true);
        $known = $user ? $this->Learn_flashcards_model->known_counts_for_user($user['id']) : [];
        $daily = $all_decks ? $all_decks[((int)date('z')) % count($all_decks)] : null;

        $this->load->view('game/flashcard_list', [
            'title' => 'Flashcard',
            'user'  => $user,
            'decks' => $decks,
            'known' => $known,
            'filters' => $filters,
            'filter_options' => $this->Learn_flashcards_model->catalog_filters(),
            'daily' => $daily,
            'catalog_stats' => ['decks'=>count($all_decks),'cards'=>array_sum(array_map(function($d){return (int)$d['card_count'];},$all_decks)),'known'=>array_sum($known)],
        ]);
    }

    /** Halaman belajar satu deck */
    public function flashcard_study($deck_code)
    {
        $deck = $this->Learn_flashcards_model->get_deck_by_code($deck_code);
        if (! $deck || ! $deck['is_active']) show_404();

        $user  = $this->_current_user();
        $cards = $this->Learn_flashcards_model->get_deck_cards_with_progress(
            (int) $deck['id'], $user ? (int) $user['id'] : null
        );
        if ($this->input->get('shuffle')) shuffle($cards);

        if (empty($cards)) {
            redirect('belajar/flashcard?empty=1');
            return;
        }

        $this->load->view('game/flashcard_study', [
            'title' => 'Flashcard: ' . $deck['name'],
            'user'  => $user,
            'deck'  => $deck,
            'cards' => $cards,
        ]);
    }

    /** AJAX: catat status satu kartu (learning/known) */
    public function flashcard_progress()
    {
        $user = $this->_current_user();
        if (! $user) {
            return $this->_json(['ok' => false, 'message' => 'Login untuk menyimpan progress.'], 401);
        }
        if (! $this->input->is_ajax_request()) { show_404(); return; }

        $card_id = (int) $this->input->post('card_id');
        $status  = $this->input->post('status');
        $ok = $this->Learn_flashcards_model->set_card_status((int) $user['id'], $card_id, $status);

        return $this->_json(['ok' => (bool) $ok]);
    }

    /** AJAX: selesai belajar satu deck → beri poin (cooldown 12 jam) */
    public function flashcard_finish()
    {
        $user = $this->_current_user();
        if (! $user) {
            return $this->_json(['ok' => true, 'points_earned' => 0, 'new_badges' => []]);
        }
        if (! $this->input->is_ajax_request()) { show_404(); return; }

        $deck_id = (int) $this->input->post('deck_id');
        $points  = (int) $this->Learn_points_model->award_points(
            (int) $user['id'], 'flashcard.study', null, null, 'Belajar flashcard'
        );
        $summary = $this->Learn_flashcards_model->deck_progress_summary((int) $user['id'], $deck_id);

        return $this->_json([
            'ok'            => true,
            'points_earned' => $points,
            'new_badges'    => $this->_new_badges_for_user((int) $user['id']),
            'summary'       => $summary,
        ]);
    }

    // ── Story Quiz (bacaan + pemahaman) ───────────────────────────────────────

    /** Daftar bacaan */
    public function cerita()
    {
        $user     = $this->_current_user();
        $filters=['q'=>trim((string)$this->input->get('q',true)),'subject_id'=>(int)$this->input->get('subject_id',true),'grade_level_id'=>(int)$this->input->get('grade_level_id',true)];
        $passages = $this->Learn_story_model->get_passages(true,$filters);
        $all_passages=$this->Learn_story_model->get_passages(true);
        $best     = $user ? $this->Learn_story_model->best_scores_for_user($user['id']) : [];

        $this->load->view('game/story_list', [
            'title'    => 'Story Quiz',
            'user'     => $user,
            'passages' => $passages,
            'best'     => $best,
            'filters'=>$filters,'filter_options'=>$this->Learn_story_model->catalog_filters(),
            'daily'=>$this->Learn_story_model->recommended_passage($user?(int)$user['id']:0,$all_passages),
            'catalog_stats'=>['stories'=>count($all_passages),'questions'=>array_sum(array_map(function($p){return (int)$p['question_count'];},$all_passages)),'perfect'=>count(array_filter($best,function($v){return $v>=100;}))],
        ]);
    }

    /** Halaman baca + kerjakan soal */
    public function cerita_read($code)
    {
        $passage = $this->Learn_story_model->get_passage_by_code($code);
        if (! $passage || ! $passage['is_active']) show_404();

        $questions = $this->Learn_story_model->get_questions_for_play((int) $passage['id']);

        $this->load->view('game/story_read', [
            'title'     => $passage['title'],
            'user'      => $this->_current_user(),
            'passage'   => $passage,
            'questions' => $questions,
        ]);
    }

    /** AJAX: nilai jawaban + catat attempt + beri poin */
    public function cerita_submit()
    {
        if (! $this->input->is_ajax_request()) { show_404(); return; }

        $passage_id = (int) $this->input->post('passage_id');
        $answers    = (array) $this->input->post('answers');
        $duration   = (int) $this->input->post('duration_seconds');

        $passage = $this->Learn_story_model->get_passage($passage_id);
        if (! $passage) {
            return $this->_json(['ok' => false, 'message' => 'Bacaan tidak ditemukan.'], 404);
        }

        // Normalisasi answers: question_id => selected index
        $norm = [];
        foreach ($answers as $qid => $sel) {
            $norm[(int) $qid] = (int) $sel;
        }

        $result = $this->Learn_story_model->grade($passage_id, $norm);
        $user   = $this->_current_user();
        $points = 0;
        $new_badges = [];

        if ($user) {
            $this->Learn_story_model->record_attempt(
                (int) $user['id'], $passage_id,
                $result['correct'], $result['total'], $result['percent'], $duration
            );
            $points += (int) $this->Learn_points_model->award_points(
                (int) $user['id'], 'story.read', 'story_passage', $passage_id,
                'Selesai baca: ' . $passage['title']
            );
            if ($result['percent'] >= 100 && $result['total'] > 0) {
                $points += (int) $this->Learn_points_model->award_points(
                    (int) $user['id'], 'story.perfect', 'story_passage', $passage_id,
                    'Pemahaman sempurna: ' . $passage['title']
                );
            }
            $new_badges = $this->_new_badges_for_user((int) $user['id']);
        }

        return $this->_json([
            'ok'            => true,
            'correct'       => $result['correct'],
            'total'         => $result['total'],
            'percent'       => $result['percent'],
            'details'       => $result['details'],
            'points_earned' => $points,
            'new_badges'    => $new_badges,
            'logged_in'     => (bool) $user,
        ]);
    }

    // ── Mode Battle (adu cepat 2 pemain, polling) ─────────────────────────────

    /** Lobby: buat room / gabung via kode */
    public function battle()
    {
        $user = $this->_current_user();
        $user_id=(int)($user['id']??0);$requested_session=max(0,(int)$this->input->get('session'));$requested_room=strtoupper(trim((string)$this->input->get('room',true)));$is_active_member=$this->Learn_battle_model->is_active_member($user_id);$battle_sessions=$this->Learn_battle_model->get_sessions(true,$user_id);$available_ids=array_map('intval',array_column($battle_sessions,'id'));$requested_denied=$requested_session&&!in_array($requested_session,$available_ids,true);if($requested_denied)$requested_session=0;$qr_room=null;$qr_session=null;$qr_room_error='';if($requested_room!==''){$qr_room=$this->Learn_battle_model->get_room_by_code($requested_room);if(!$qr_room)$qr_room_error='Room Battle tidak ditemukan.';else{$qr_session=$this->Learn_battle_model->get_session((int)$qr_room['battle_session_id']);if(!$qr_session)$qr_room_error='Sesi Battle untuk room ini tidak ditemukan.';elseif($qr_room['status']!=='waiting')$qr_room_error='Room ini sudah dimulai atau telah selesai.';elseif(!$this->Learn_battle_model->can_access_session($qr_session,$user_id))$qr_room_error='Sesi ini khusus member perpustakaan aktif. Silakan login dengan akun member.';}}
        $this->load->view('game/battle_lobby', [
            'title'          => 'Mode Battle',
            'user'           => $user,
            'pool_ready'     => $this->Learn_battle_model->count_active_questions() >= 3,
            'battle_sessions'=> $battle_sessions,
            'is_active_member'=> $is_active_member,
            'requested_session'=> $requested_session,
            'requested_denied'=> $requested_denied,
            'requested_room'=> $requested_room,
            'qr_room'=> $qr_room,
            'qr_session'=> $qr_session,
            'qr_room_error'=> $qr_room_error,
        ]);
    }

    public function battle_create()
    {
        $user = $this->_current_user();
        if (! $user) { redirect('login'); return; }

        $is_member=$this->Learn_battle_model->is_active_member((int)$user['id']);$battle_name=trim((string)$this->input->post('battle_username',true));
        if(!$is_member&&$battle_name===''){$this->session->set_flashdata('error','Username battle wajib diisi oleh peserta nonmember.');redirect('belajar/battle?room='.rawurlencode((string)$this->input->post('code',true)));return;}
        $res = $this->Learn_battle_model->create_room((int)$user['id'],$battle_name!==''?mb_substr($battle_name,0,40):$this->_display_name($user),(int)$this->input->post('battle_session_id'),(string)$this->input->post('host_mode',true));
        if (! $res['ok']) {
            $this->session->set_flashdata('error', $res['message']);
            redirect('belajar/battle?room='.rawurlencode($code));
            return;
        }
        redirect('belajar/battle/room/' . $res['code']);
    }

    public function battle_join()
    {
        $user = $this->_current_user();
        $user_id=(int)($user['id']??0);$is_member=$this->Learn_battle_model->is_active_member($user_id);$battle_name=trim((string)$this->input->post('battle_username',true));
        if(!$is_member&&$battle_name===''){$this->session->set_flashdata('error','Username battle wajib diisi oleh peserta nonmember.');redirect('belajar/battle');return;}
        $code = strtoupper(trim((string) $this->input->post('code', true)));
        $guest_token=$user_id?null:$this->_battle_guest_token();
        $res  = $this->Learn_battle_model->join_room($code,$user_id?:null,$battle_name!==''?mb_substr($battle_name,0,40):$this->_display_name($user),$guest_token);
        if (! $res['ok']) {
            $this->session->set_flashdata('error', $res['message']);
            redirect('belajar/battle');
            return;
        }
        redirect('belajar/battle/room/' . $res['code']);
    }

    public function battle_start()
    {
        $user=$this->_current_user();
        if(!$user || !$this->input->is_ajax_request()) return $this->_json(['ok'=>false],401);
        $res=$this->Learn_battle_model->start_room(strtoupper(trim((string)$this->input->post('code',true))),(int)$user['id']);
        return $this->_json($res,$res['ok']?200:400);
    }

    public function battle_next()
    {
        $user=$this->_current_user();if(!$user||!$this->input->is_ajax_request())return $this->_json(['ok'=>false,'message'=>'Login operator diperlukan.'],401);$res=$this->Learn_battle_model->advance_question(strtoupper(trim((string)$this->input->post('code',true))),(int)$user['id']);return $this->_json($res,$res['ok']?200:400);
    }

    /** Halaman ruang battle */
    public function battle_room($code)
    {
        $user = $this->_current_user();

        $room = $this->Learn_battle_model->get_room_by_code($code);
        if (! $room) show_404();

        $guest_token=$user?null:$this->_battle_guest_token(false);$role = $this->Learn_battle_model->role_of($room,(int)($user['id']??0),$guest_token);
        if (! $role) {
            // Bukan pemain — jika masih waiting & belum ada guest, arahkan untuk join
            $this->session->set_flashdata('error', 'Kamu bukan pemain di room ini.');
            redirect('belajar/battle');
            return;
        }

        $this->load->view('game/battle_room', [
            'title'     => 'Battle ' . $room['code'],
            'user'      => $user,
            'room'      => $room,
            'role'      => $role,
            'questions' => $this->Learn_battle_model->get_room_questions($room),
            'participant' => $this->Learn_battle_model->get_participant_identity((int)$room['id'],(int)($user['id']??0),$guest_token),
        ]);
    }

    /** AJAX: state room untuk polling (hanya pemain di room ini) */
    public function battle_state($code)
    {
        $this->output
            ->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0')
            ->set_header('Pragma: no-cache')
            ->set_header('Expires: 0');
        $user = $this->_current_user();
        $room = $this->Learn_battle_model->get_room_by_code($code);
        if (! $room) return $this->_json(['ok' => false], 404);
        $guest_token=$user?null:$this->_battle_guest_token(false);if (! $this->Learn_battle_model->role_of($room,(int)($user['id']??0),$guest_token)) {
            return $this->_json(['ok' => false], 403);
        }
        return $this->_json(['ok' => true, 'state' => $this->Learn_battle_model->state($room)]);
    }

    /** AJAX: submit satu jawaban */
    public function battle_answer()
    {
        $user = $this->_current_user();
        if (! $this->input->is_ajax_request()) {
            return $this->_json(['ok' => false], 401);
        }
        $room = $this->Learn_battle_model->get_room_by_code($this->input->post('code', true));
        if (! $room) return $this->_json(['ok' => false, 'message' => 'Room tidak ditemukan.'], 404);

        $res = $this->Learn_battle_model->submit_answer(
            (int)$room['id'],(int)($user['id']??0),
            $user?null:$this->_battle_guest_token(false),
            (int) $this->input->post('index'), (int) $this->input->post('selected')
        );
        return $this->_json($res, $res['ok'] ? 200 : 400);
    }

    // ── Raport belajar (member) ───────────────────────────────────────────────

    /** Raport belajar milik member sendiri (printable) */
    public function raport()
    {
        $user = $this->_current_user();
        if (! $user) { redirect('login'); return; }

        $member = $this->Learn_report_model->get_member((int) $user['id']);
        if (! $member) {
            // Fallback identitas dari sesi bila belum tertaut member
            $member = [
                'user_id'     => (int) $user['id'],
                'full_name'   => $this->_display_name($user),
                'username'    => $user['username'] ?? '',
                'member_no'   => null,
                'member_name' => null,
            ];
        }

        $this->load->view('learn/reports/sheet', [
            'member'   => $member,
            'report'   => $this->Learn_report_model->get_report((int) $user['id']),
            'is_admin' => false,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function _display_name($user)
    {
        return $user['full_name'] ?? ($user['username'] ?? 'Pemain');
    }

    private function _json($payload, $status = 200)
    {
        return $this->output
            ->set_status_header($status)
            ->set_content_type('application/json')
            ->set_output(json_encode($payload, JSON_UNESCAPED_UNICODE));
    }

    private function _battle_guest_token($create=true)
    {
        $token=(string)$this->session->userdata('battle_guest_token');
        if($token===''&&$create){$token=bin2hex(random_bytes(32));$this->session->set_userdata('battle_guest_token',$token);}
        return $token?:null;
    }

    private function _current_user()
    {
        $user = $this->session->userdata('auth_user');
        return !empty($user) ? (array) $user : null;
    }

    private function _parse_game_config($game_type)
    {
        $schema = json_decode($game_type['config_schema'] ?? '{}', true);
        $config = [];
        foreach ($schema as $key => $field) {
            $get_val = $this->input->get($key);
            $config[$key] = $get_val !== null ? $get_val : ($field['default'] ?? null);
        }
        return $config;
    }

    /** Data soal yang aman dikirim ke browser (jawaban benar tetap di server). */
    private function _math_question_payload(array $question)
    {
        $options = json_decode((string) ($question['options_json'] ?? '{}'), true);
        if (! is_array($options)) $options = [];
        return [
            'id' => (int) $question['id'],
            'prompt' => (string) $question['prompt'],
            'options' => $options,
            'hint' => (string) $question['hint'],
            'hint_deeper' => (string) $question['hint_deeper'],
        ];
    }

    /** Tema misi membuat tiap bab memiliki bahasa visual dan suasana berbeda. */
    private function _math_experience(array $level)
    {
        $stage = (int) ($level['stage_number'] ?? 1);
        if ($stage <= 4) {
            return [
                'chapter' => 1,
                'chapter_title' => 'Bab 1 · Pulau Bilangan',
                'mission' => 'Menyalakan mercusuar angka',
                'story' => 'Kumpulkan penanda angka untuk menerangi jalan menuju Kota Operasi.',
                'scene' => 'island',
                'objects' => ['🔢', '⭐', '🧭', '🌴'],
            ];
        }
        if ($stage >= 9) {
            $themes = [
                9 => ['Stasiun Waktu', 'Bantu Kereta Hutan tiba tepat waktu di setiap perhentian.', '🚂', '⏰', '🌲', '✨'],
                10 => ['Bengkel Jembatan', 'Pilih bentuk dan ukuran yang tepat untuk merakit jalan hutan.', '🛠️', '🔺', '🧱', '🌉'],
                11 => ['Kebun Data', 'Baca papan panen untuk menentukan langkah terbaik di kebun.', '🌻', '📊', '🍅', '🐞'],
                12 => ['Menara Logika', 'Pecahkan petunjuk satu per satu untuk membuka puncak menara.', '🏰', '🗝️', '🧩', '🔮'],
            ];
            $theme = $themes[$stage] ?? $themes[12];
            return [
                'chapter' => 3,
                'chapter_title' => 'Bab 3 · Hutan Penjelajah',
                'mission' => $theme[0],
                'story' => $theme[1],
                'scene' => 'forest',
                'objects' => array_slice($theme, 2),
            ];
        }
        $themes = [
            5 => ['Kebun Perkalian', 'Susun panen ke dalam kelompok yang sama banyak.', '🌻', '🍎', '🐝', '🧺'],
            6 => ['Pelabuhan Pembagian', 'Bantu membagi bekal dengan adil untuk semua kru.', '⛵', '📦', '🐚', '⚓'],
            7 => ['Kafe Pecahan', 'Bagikan camilan dengan tepat sebelum para tamu datang.', '🍕', '🍰', '☕', '🍓'],
            8 => ['Pasar Strategi', 'Pilih cara berhitung paling cerdas untuk menyelesaikan misi kota.', '🏪', '🪙', '🎈', '🧮'],
        ];
        $theme = $themes[$stage] ?? $themes[8];
        return [
            'chapter' => 2,
            'chapter_title' => 'Bab 2 · Kota Operasi',
            'mission' => $theme[0],
            'story' => $theme[1],
            'scene' => 'city',
            'objects' => array_slice($theme, 2),
        ];
    }

    private function _speed_math_levels()
    {
        return [
            1=>['name'=>'Pemanasan','subtitle'=>'Penjumlahan sampai 10','duration'=>60,'operators'=>['+'],'max_num'=>10,'color'=>'#22c55e'],
            2=>['name'=>'Pelari Angka','subtitle'=>'Tambah dan kurang sampai 20','duration'=>60,'operators'=>['+','-'],'max_num'=>20,'color'=>'#14b8a6'],
            3=>['name'=>'Penakluk Puluhan','subtitle'=>'Tambah dan kurang sampai 50','duration'=>60,'operators'=>['+','-'],'max_num'=>50,'color'=>'#0ea5e9'],
            4=>['name'=>'Master Perkalian','subtitle'=>'Perkalian 1 sampai 10','duration'=>60,'operators'=>['×'],'max_num'=>10,'color'=>'#6366f1'],
            5=>['name'=>'Pembagi Andal','subtitle'=>'Pembagian dengan hasil bulat','duration'=>65,'operators'=>['÷'],'max_num'=>100,'color'=>'#8b5cf6'],
            6=>['name'=>'Operasi Campuran','subtitle'=>'Empat operasi sampai 50','duration'=>75,'operators'=>['+','-','×','÷'],'max_num'=>50,'color'=>'#d946ef'],
            7=>['name'=>'Arena Ratusan','subtitle'=>'Tambah dan kurang sampai 500','duration'=>75,'operators'=>['+','-'],'max_num'=>500,'color'=>'#f97316'],
            8=>['name'=>'Grandmaster','subtitle'=>'Empat operasi, angka lebih menantang','duration'=>90,'operators'=>['+','-','×','÷'],'max_num'=>1000,'color'=>'#ef4444'],
        ];
    }

    private function _new_badges_for_user($user_id)
    {
        // Return newly awarded badges during this call by comparing before/after
        // (check_and_award_badges is idempotent, badges already inserted)
        return $this->db
            ->select('bd.name, bd.icon, bd.color')
            ->from('learn_member_badges mb')
            ->join('learn_badge_definitions bd', 'bd.id = mb.badge_id')
            ->where('mb.user_id', $user_id)
            ->where('mb.awarded_at >=', date('Y-m-d H:i:s', time() - 5))
            ->get()
            ->result_array();
    }
}
