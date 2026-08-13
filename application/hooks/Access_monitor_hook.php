<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Access_monitor_hook
{
    public function capture()
    {
        if (is_cli()) return;
        try {
            $ci =& get_instance();
            $ci->load->model('Site_access_log_model');
            $ci->Site_access_log_model->capture_current_request();
        } catch (Throwable $e) {
            log_message('error', 'Access monitor capture failed: ' . $e->getMessage());
        }
    }
}
