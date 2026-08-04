<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Learn_games extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Learn_games_model');
        $this->load->model('Quiz_config_model');
    }

    public function index()
    {
        $this->require_permission('learn_games.index', 'view');
        $game_types = $this->Learn_games_model->get_game_types();
        $categories = $this->Learn_games_model->get_categories();
        $stats      = $this->Learn_games_model->get_stats();

        $this->render('learn/games/index', [
            'title'       => 'Kelola Konten Game',
            'active_menu' => 'learn_games',
            'game_types'  => $game_types,
            'categories'  => $categories,
            'stats'       => $stats,
            'grades'      => $this->Quiz_config_model->get_grade_levels(false),
            'subjects'    => $this->Quiz_config_model->get_subjects(false),
        ]);
    }

    public function toggle_game_type($id)
    {
        $this->require_permission('learn_games.index', 'update');
        $this->Learn_games_model->toggle_game_type($id);
        $this->audit_event('learn_games', 'toggle_game_type', $id);
        $this->session->set_flashdata('success', 'Status tipe game diperbarui.');
        redirect('learn-games');
    }

    public function update_game_type($id)
    {
        $this->require_permission('learn_games.index', 'update');
        $data = [
            'name'        => $this->input->post('name'),
            'description' => $this->input->post('description'),
            'icon'        => $this->input->post('icon') ?: 'ti-puzzle',
            'color'       => $this->input->post('color') ?: '#6366f1',
            'sort_order'  => (int) $this->input->post('sort_order'),
        ];
        $this->Learn_games_model->update_game_type($id, $data);
        $this->audit_event('learn_games', 'update_game_type', $id);
        $this->session->set_flashdata('success', 'Tipe game diperbarui.');
        redirect('learn-games');
    }

    // ── Categories ────────────────────────────────────────────────────────────

    public function store_category()
    {
        $this->require_permission('learn_games.index', 'create');
        $data = [
            'game_type_id'   => (int) $this->input->post('game_type_id'),
            'grade_level_id' => $this->input->post('grade_level_id') ?: null,
            'subject_id'     => $this->input->post('subject_id') ?: null,
            'name'           => $this->input->post('name'),
            'description'    => $this->input->post('description'),
            'is_active'      => $this->input->post('is_active') ? 1 : 0,
        ];
        if (empty($data['name']) || !$data['game_type_id']) {
            $this->session->set_flashdata('error', 'Nama dan tipe game wajib diisi.');
            redirect('learn-games');
        }
        $this->Learn_games_model->create_category($data);
        $this->audit_event('learn_games', 'create_category', null, ['name' => $data['name']]);
        $this->session->set_flashdata('success', 'Kategori konten berhasil ditambahkan.');
        redirect('learn-games');
    }

    public function update_category($id)
    {
        $this->require_permission('learn_games.index', 'update');
        $cat = $this->Learn_games_model->get_category($id);
        if (!$cat) show_404();

        $data = [
            'game_type_id'   => (int) $this->input->post('game_type_id'),
            'grade_level_id' => $this->input->post('grade_level_id') ?: null,
            'subject_id'     => $this->input->post('subject_id') ?: null,
            'name'           => $this->input->post('name'),
            'description'    => $this->input->post('description'),
            'is_active'      => $this->input->post('is_active') ? 1 : 0,
        ];
        $this->Learn_games_model->update_category($id, $data);
        $this->audit_event('learn_games', 'update_category', $id);
        $this->session->set_flashdata('success', 'Kategori diperbarui.');
        redirect('learn-games');
    }

    public function delete_category($id)
    {
        $this->require_permission('learn_games.index', 'delete');
        $ok = $this->Learn_games_model->delete_category($id);
        if (!$ok) {
            $this->session->set_flashdata('error', 'Kategori masih memiliki konten. Hapus konten terlebih dahulu.');
        } else {
            $this->audit_event('learn_games', 'delete_category', $id);
            $this->session->set_flashdata('success', 'Kategori dihapus.');
        }
        redirect('learn-games');
    }

    // ── Content Sets ──────────────────────────────────────────────────────────

    public function content($category_id)
    {
        $this->require_permission('learn_games.index', 'view');
        $cat  = $this->Learn_games_model->get_category($category_id);
        if (!$cat) show_404();

        $sets = $this->Learn_games_model->get_sets($category_id);

        $this->render('learn/games/content', [
            'title'       => 'Konten: ' . $cat['name'],
            'active_menu' => 'learn_games',
            'category'    => $cat,
            'sets'        => $sets,
        ]);
    }

    public function store_set($category_id)
    {
        $this->require_permission('learn_games.index', 'create');
        $cat = $this->Learn_games_model->get_category($category_id);
        if (!$cat) show_404();

        $data = [
            'category_id' => $category_id,
            'name'        => $this->input->post('name'),
            'difficulty'  => $this->input->post('difficulty') ?: 'easy',
            'is_active'   => $this->input->post('is_active') ? 1 : 0,
        ];
        if (empty($data['name'])) {
            $this->session->set_flashdata('error', 'Nama set wajib diisi.');
            redirect('learn-games/content/' . $category_id);
        }
        $set_id = $this->Learn_games_model->create_set($data, $this->current_user['id']);
        $this->audit_event('learn_games', 'create_set', $set_id, ['name' => $data['name']]);
        $this->session->set_flashdata('success', 'Set konten berhasil ditambahkan.');
        redirect('learn-games/items/' . $set_id);
    }

    public function update_set($id)
    {
        $this->require_permission('learn_games.index', 'update');
        $set = $this->Learn_games_model->get_set($id);
        if (!$set) show_404();

        $data = [
            'name'       => $this->input->post('name'),
            'difficulty' => $this->input->post('difficulty') ?: 'easy',
            'is_active'  => $this->input->post('is_active') ? 1 : 0,
        ];
        $this->Learn_games_model->update_set($id, $data);
        $this->audit_event('learn_games', 'update_set', $id);
        $this->session->set_flashdata('success', 'Set diperbarui.');
        redirect('learn-games/items/' . $id);
    }

    public function delete_set($id)
    {
        $this->require_permission('learn_games.index', 'delete');
        $set = $this->Learn_games_model->get_set($id);
        if (!$set) show_404();

        $cat_id = $set['category_id'];
        $this->Learn_games_model->delete_set($id);
        $this->audit_event('learn_games', 'delete_set', $id);
        $this->session->set_flashdata('success', 'Set dan semua itemnya dihapus.');
        redirect('learn-games/content/' . $cat_id);
    }

    // ── Content Items ─────────────────────────────────────────────────────────

    public function items($set_id)
    {
        $this->require_permission('learn_games.index', 'view');
        $set = $this->Learn_games_model->get_set($set_id);
        if (!$set) show_404();

        $items = $this->Learn_games_model->get_items($set_id);

        $this->render('learn/games/items', [
            'title'       => 'Item: ' . $set['name'],
            'active_menu' => 'learn_games',
            'set'         => $set,
            'items'       => $items,
        ]);
    }

    public function store_item($set_id)
    {
        $this->require_permission('learn_games.index', 'create');
        $set = $this->Learn_games_model->get_set($set_id);
        if (!$set) show_404();

        $data = [
            'set_id'     => $set_id,
            'term'       => trim($this->input->post('term')),
            'definition' => trim($this->input->post('definition')),
            'sort_order' => (int) $this->input->post('sort_order'),
        ];
        if (empty($data['term']) || empty($data['definition'])) {
            $this->session->set_flashdata('error', 'Istilah dan definisi wajib diisi.');
            redirect('learn-games/items/' . $set_id);
        }
        $this->Learn_games_model->create_item($data);
        $this->audit_event('learn_games', 'create_item', $set_id);
        $this->session->set_flashdata('success', 'Item berhasil ditambahkan.');
        redirect('learn-games/items/' . $set_id);
    }

    public function update_item($id)
    {
        $this->require_permission('learn_games.index', 'update');
        $item = $this->Learn_games_model->get_item($id);
        if (!$item) show_404();

        $data = [
            'term'       => trim($this->input->post('term')),
            'definition' => trim($this->input->post('definition')),
            'sort_order' => (int) $this->input->post('sort_order'),
        ];
        $this->Learn_games_model->update_item($id, $data);
        $this->audit_event('learn_games', 'update_item', $id);
        $this->session->set_flashdata('success', 'Item diperbarui.');
        redirect('learn-games/items/' . $item['set_id']);
    }

    public function delete_item($id)
    {
        $this->require_permission('learn_games.index', 'delete');
        $item = $this->Learn_games_model->get_item($id);
        if (!$item) show_404();

        $set_id = $item['set_id'];
        $this->Learn_games_model->delete_item($id);
        $this->audit_event('learn_games', 'delete_item', $id);
        $this->session->set_flashdata('success', 'Item dihapus.');
        redirect('learn-games/items/' . $set_id);
    }
}
