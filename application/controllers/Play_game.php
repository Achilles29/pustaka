<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Play_game — controller publik untuk memainkan mini game.
 * Extends CI_Controller (bukan MY_Controller) karena bisa dimainkan
 * oleh pengunjung yang belum login, meski fitur poin hanya untuk user login.
 */
class Play_game extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Learn_games_model');
        $this->load->model('Learn_points_model');
        $this->load->model('Learn_rewards_model');
        $this->load->model('Learn_flashcards_model');
        $this->load->model('Learn_story_model');
        $this->load->model('Learn_notifications_model');
        $this->load->model('Learn_battle_model');
        $this->load->model('Learn_report_model');
        $this->load->model('Member_model');
        $this->load->model('Quiz_config_model');
    }

    /** Halaman daftar semua game */
    public function index()
    {
        $game_types = $this->Learn_games_model->get_game_types(true);
        $user       = $this->_current_user();

        $this->load->view('game/lobby', [
            'title'        => 'Arena Belajar',
            'game_types'   => $game_types,
            'user'         => $user,
            'unread_notif' => $user ? $this->Learn_notifications_model->unread_count($user['id']) : 0,
        ]);
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

        if ($game_type['needs_content']) {
            if (!$set_id) {
                redirect('belajar/pilih/' . $game_code);
            }
            $set = $this->Learn_games_model->get_set($set_id);
            if (!$set || !$set['is_active']) show_404();

            $config_schema = json_decode($game_type['config_schema'] ?? '{}', true);
            $pairs_config  = $config_schema['pairs']['default'] ?? 6;
            $content = $this->Learn_games_model->get_game_content($set_id, $pairs_config);

            if (empty($content)) {
                redirect('belajar/pilih/' . $game_code . '?empty=1');
            }

            $this->load->view('game/' . $game_code, [
                'title'     => $game_type['name'] . ': ' . $set['name'],
                'game_type' => $game_type,
                'set'       => $set,
                'content'   => $content,
                'user'      => $user,
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
            ['score_at_start' => 0]
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
        $decks = $this->Learn_flashcards_model->get_decks(true);
        $known = $user ? $this->Learn_flashcards_model->known_counts_for_user($user['id']) : [];

        $this->load->view('game/flashcard_list', [
            'title' => 'Flashcard',
            'user'  => $user,
            'decks' => $decks,
            'known' => $known,
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
        $passages = $this->Learn_story_model->get_passages(true);
        $best     = $user ? $this->Learn_story_model->best_scores_for_user($user['id']) : [];

        $this->load->view('game/story_list', [
            'title'    => 'Story Quiz',
            'user'     => $user,
            'passages' => $passages,
            'best'     => $best,
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
        $this->load->view('game/battle_lobby', [
            'title'          => 'Mode Battle',
            'user'           => $user,
            'pool_ready'     => $this->Learn_battle_model->count_active_questions() >= 3,
        ]);
    }

    public function battle_create()
    {
        $user = $this->_current_user();
        if (! $user) { redirect('login'); return; }

        $res = $this->Learn_battle_model->create_room(
            (int) $user['id'], $this->_display_name($user), (int) $this->input->post('question_count')
        );
        if (! $res['ok']) {
            $this->session->set_flashdata('error', $res['message']);
            redirect('belajar/battle');
            return;
        }
        redirect('belajar/battle/room/' . $res['code']);
    }

    public function battle_join()
    {
        $user = $this->_current_user();
        if (! $user) { redirect('login'); return; }

        $code = strtoupper(trim((string) $this->input->post('code', true)));
        $res  = $this->Learn_battle_model->join_room($code, (int) $user['id'], $this->_display_name($user));
        if (! $res['ok']) {
            $this->session->set_flashdata('error', $res['message']);
            redirect('belajar/battle');
            return;
        }
        redirect('belajar/battle/room/' . $res['code']);
    }

    /** Halaman ruang battle */
    public function battle_room($code)
    {
        $user = $this->_current_user();
        if (! $user) { redirect('login'); return; }

        $room = $this->Learn_battle_model->get_room_by_code($code);
        if (! $room) show_404();

        $role = $this->Learn_battle_model->role_of($room, (int) $user['id']);
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
        ]);
    }

    /** AJAX: state room untuk polling (hanya pemain di room ini) */
    public function battle_state($code)
    {
        $user = $this->_current_user();
        $room = $this->Learn_battle_model->get_room_by_code($code);
        if (! $room) return $this->_json(['ok' => false], 404);
        if (! $user || ! $this->Learn_battle_model->role_of($room, (int) $user['id'])) {
            return $this->_json(['ok' => false], 403);
        }
        return $this->_json(['ok' => true, 'state' => $this->Learn_battle_model->state($room)]);
    }

    /** AJAX: submit satu jawaban */
    public function battle_answer()
    {
        $user = $this->_current_user();
        if (! $user || ! $this->input->is_ajax_request()) {
            return $this->_json(['ok' => false], 401);
        }
        $room = $this->Learn_battle_model->get_room_by_code($this->input->post('code', true));
        if (! $room) return $this->_json(['ok' => false, 'message' => 'Room tidak ditemukan.'], 404);

        $res = $this->Learn_battle_model->submit_answer(
            (int) $room['id'], (int) $user['id'],
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
