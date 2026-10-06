<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Library_access_hook
{
    public function enforce()
    {
        $ci =& get_instance();
        $ci->load->library('Library_access');
        $ci->library_access->enforce();
    }
}
