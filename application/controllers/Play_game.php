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
    }

    /** Halaman daftar semua game */
    public function index()
    {
        $game_types = $this->Learn_games_model->get_game_types(true);
        $user       = $this->_current_user();

        $this->load->view('game/lobby', [
            'title'      => 'Arena Belajar',
            'game_types' => $game_types,
            'user'       => $user,
        ]);
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

    // ── Helpers ───────────────────────────────────────────────────────────────

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
