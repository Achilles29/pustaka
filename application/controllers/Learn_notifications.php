<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Learn_notifications — admin: kirim broadcast/pengumuman ke member + riwayat.
 */
class Learn_notifications extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Learn_notifications_model');
    }

    public function index()
    {
        $this->require_permission('learn_notifications.index', 'view');
        $this->render('learn/notifications/index', [
            'title'       => 'Notifikasi & Pengumuman',
            'active_menu' => 'learn_notifications',
            'stats'       => $this->Learn_notifications_model->stats(),
            'broadcasts'  => $this->Learn_notifications_model->recent_broadcasts(30),
            'can_create'  => $this->can('learn_notifications.index', 'create'),
        ]);
    }

    public function send()
    {
        $this->require_permission('learn_notifications.index', 'create');

        $title   = trim((string) $this->input->post('title', true));
        $message = trim((string) $this->input->post('message', true));
        if ($title === '') {
            $this->session->set_flashdata('error', 'Judul pengumuman wajib diisi.');
            redirect('learn-notifications');
            return;
        }

        $count = $this->Learn_notifications_model->broadcast([
            'type'    => 'announcement',
            'title'   => $title,
            'message' => $message ?: null,
            'icon'    => $this->input->post('icon', true) ?: 'ti-speakerphone',
            'color'   => $this->input->post('color', true) ?: '#3b82f6',
            'url'     => trim((string) $this->input->post('url', true)) ?: null,
        ], (int) $this->current_user['id']);

        $this->audit_event('learn_notifications.broadcast', 'learn_broadcasts', null, null, ['title' => $title, 'recipients' => $count]);
        $this->session->set_flashdata('success', "Pengumuman terkirim ke {$count} member.");
        redirect('learn-notifications');
    }
}
