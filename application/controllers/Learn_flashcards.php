<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Learn_flashcards — admin: kelola deck & kartu flashcard.
 */
class Learn_flashcards extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Learn_flashcards_model');
        $this->load->model('Quiz_config_model');
    }

    public function index()
    {
        $this->require_permission('learn_flashcards.index', 'view');

        $this->render('learn/flashcards/index', [
            'title'       => 'Flashcard — Deck',
            'active_menu' => 'learn_flashcards',
            'decks'       => $this->Learn_flashcards_model->get_decks(),
            'stats'       => $this->Learn_flashcards_model->stats(),
            'subjects'    => $this->Quiz_config_model->get_subjects(true),
            'grades'      => $this->Quiz_config_model->get_grade_levels(true),
            'can_create'  => $this->can('learn_flashcards.index', 'create'),
            'can_edit'    => $this->can('learn_flashcards.index', 'edit'),
            'can_delete'  => $this->can('learn_flashcards.index', 'delete'),
        ]);
    }

    // ── Deck ──────────────────────────────────────────────────────────────────

    public function store_deck()
    {
        $this->require_permission('learn_flashcards.index', 'create');
        $data = $this->_deck_input();
        if ($data['code'] === '' || $data['name'] === '') {
            $this->session->set_flashdata('error', 'Kode dan nama deck wajib diisi.');
            redirect('learn-flashcards');
            return;
        }
        if ($this->Learn_flashcards_model->code_exists($data['code'])) {
            $this->session->set_flashdata('error', 'Kode deck sudah digunakan.');
            redirect('learn-flashcards');
            return;
        }
        $data['created_by'] = (int) $this->current_user['id'];
        $id = $this->Learn_flashcards_model->create_deck($data);
        $this->audit_event('learn_flashcards.create_deck', 'learn_flashcard_decks', $id, null, ['code' => $data['code']]);
        $this->session->set_flashdata('success', 'Deck berhasil dibuat. Tambahkan kartu di dalamnya.');
        redirect('learn-flashcards/cards/' . $id);
    }

    public function update_deck($id)
    {
        $this->require_permission('learn_flashcards.index', 'edit');
        $deck = $this->Learn_flashcards_model->get_deck($id);
        if (! $deck) { show_404(); return; }
        $data = $this->_deck_input();
        if ($data['code'] === '' || $data['name'] === '') {
            $this->session->set_flashdata('error', 'Kode dan nama deck wajib diisi.');
            redirect('learn-flashcards');
            return;
        }
        if ($this->Learn_flashcards_model->code_exists($data['code'], (int) $id)) {
            $this->session->set_flashdata('error', 'Kode deck sudah digunakan deck lain.');
            redirect('learn-flashcards');
            return;
        }
        $this->Learn_flashcards_model->update_deck((int) $id, $data);
        $this->audit_event('learn_flashcards.update_deck', 'learn_flashcard_decks', (int) $id, $deck, $data);
        $this->session->set_flashdata('success', 'Deck berhasil diperbarui.');
        redirect('learn-flashcards');
    }

    public function delete_deck($id)
    {
        $this->require_permission('learn_flashcards.index', 'delete');
        $deck = $this->Learn_flashcards_model->get_deck($id);
        if (! $deck) { show_404(); return; }
        $this->Learn_flashcards_model->delete_deck((int) $id);
        $this->audit_event('learn_flashcards.delete_deck', 'learn_flashcard_decks', (int) $id, $deck);
        $this->session->set_flashdata('success', 'Deck beserta kartunya dihapus.');
        redirect('learn-flashcards');
    }

    public function toggle_deck($id)
    {
        $this->require_permission('learn_flashcards.index', 'edit');
        if (! $this->Learn_flashcards_model->get_deck($id)) { show_404(); return; }
        $this->Learn_flashcards_model->toggle_deck((int) $id);
        $this->session->set_flashdata('success', 'Status deck diperbarui.');
        redirect('learn-flashcards');
    }

    // ── Cards ─────────────────────────────────────────────────────────────────

    public function cards($deck_id)
    {
        $this->require_permission('learn_flashcards.index', 'view');
        $deck = $this->Learn_flashcards_model->get_deck($deck_id);
        if (! $deck) { show_404(); return; }

        $this->render('learn/flashcards/cards', [
            'title'       => 'Kartu — ' . $deck['name'],
            'active_menu' => 'learn_flashcards',
            'deck'        => $deck,
            'cards'       => $this->Learn_flashcards_model->get_cards((int) $deck_id),
            'can_create'  => $this->can('learn_flashcards.index', 'create'),
            'can_edit'    => $this->can('learn_flashcards.index', 'edit'),
            'can_delete'  => $this->can('learn_flashcards.index', 'delete'),
        ]);
    }

    public function store_card($deck_id)
    {
        $this->require_permission('learn_flashcards.index', 'create');
        $deck = $this->Learn_flashcards_model->get_deck($deck_id);
        if (! $deck) { show_404(); return; }
        $data = $this->_card_input();
        if ($data['front'] === '' || $data['back'] === '') {
            $this->session->set_flashdata('error', 'Sisi depan dan belakang kartu wajib diisi.');
            redirect('learn-flashcards/cards/' . (int) $deck_id);
            return;
        }
        $this->Learn_flashcards_model->create_card((int) $deck_id, $data);
        $this->session->set_flashdata('success', 'Kartu ditambahkan.');
        redirect('learn-flashcards/cards/' . (int) $deck_id);
    }

    public function update_card($id)
    {
        $this->require_permission('learn_flashcards.index', 'edit');
        $card = $this->Learn_flashcards_model->get_card($id);
        if (! $card) { show_404(); return; }
        $data = $this->_card_input();
        if ($data['front'] === '' || $data['back'] === '') {
            $this->session->set_flashdata('error', 'Sisi depan dan belakang kartu wajib diisi.');
            redirect('learn-flashcards/cards/' . (int) $card['deck_id']);
            return;
        }
        $this->Learn_flashcards_model->update_card((int) $id, $data);
        $this->session->set_flashdata('success', 'Kartu diperbarui.');
        redirect('learn-flashcards/cards/' . (int) $card['deck_id']);
    }

    public function delete_card($id)
    {
        $this->require_permission('learn_flashcards.index', 'delete');
        $card = $this->Learn_flashcards_model->get_card($id);
        if (! $card) { show_404(); return; }
        $this->Learn_flashcards_model->delete_card((int) $id);
        $this->session->set_flashdata('success', 'Kartu dihapus.');
        redirect('learn-flashcards/cards/' . (int) $card['deck_id']);
    }

    // ── Input helpers ─────────────────────────────────────────────────────────

    private function _deck_input()
    {
        return [
            'code'           => trim((string) $this->input->post('code', true)),
            'name'           => trim((string) $this->input->post('name', true)),
            'description'    => $this->input->post('description', true),
            'subject_id'     => $this->input->post('subject_id', true) ?: null,
            'grade_level_id' => $this->input->post('grade_level_id', true) ?: null,
            'icon'           => $this->input->post('icon', true) ?: 'ti-cards',
            'color'          => $this->input->post('color', true) ?: '#8b5cf6',
            'sort_order'     => (int) $this->input->post('sort_order', true),
            'is_active'      => $this->input->post('is_active') ? 1 : 0,
        ];
    }

    private function _card_input()
    {
        return [
            'front'      => trim((string) $this->input->post('front', true)),
            'back'       => trim((string) $this->input->post('back', true)),
            'hint'       => $this->input->post('hint', true),
            'sort_order' => (int) $this->input->post('sort_order', true),
            'is_active'  => $this->input->post('is_active') !== null ? 1 : 0,
        ];
    }
}
