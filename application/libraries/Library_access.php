<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Request-wide guard for local operators; legacy permissions are not a tenant boundary. */
class Library_access
{
    private $checked = false;

    public function enforce()
    {
        if ($this->checked || is_cli()) return;
        $this->checked = true;
        $ci =& get_instance();
        $cached = (array) $ci->session->userdata('auth_user');
        if (empty($cached['id'])) return;
        $cached_roles = array_column((array) $ci->session->userdata('user_roles'), 'code');
        // Check DB assignment too: changes made during a session must take effect immediately.
        $row = $ci->db->select('u.id,u.username,u.full_name,u.email,u.status,u.library_id,u.source_system,u.force_password_change,u.password_hash')
            ->from('auth_user u')->where('u.id', (int)$cached['id'])->get()->row_array();
        $ci->load->model('Auth_model');
        $roles = $row ? $ci->Auth_model->load_roles((int)$row['id']) : [];
        $codes = array_column($roles, 'code');
        $is_local = in_array('LIBRARY_ADMIN', $codes, true) || in_array('LIBRARY_ADMIN', $cached_roles, true) || ($row['source_system'] ?? '') === 'library_network_v1' || !empty($cached['is_library_admin']);
        if (!$is_local) return;
        $stamp=$row?hash('sha256',$row['password_hash']):'';
        $session_stamp=$ci->session->userdata('network_password_stamp');
        if(is_string($session_stamp)&&!hash_equals($session_stamp,$stamp)){$ci->session->sess_destroy();redirect('login');exit;}
        unset($row['password_hash']);
        $library = !empty($row['library_id']) ? $ci->db->select('id,status')->where('id',(int)$row['library_id'])->get('libraries')->row_array() : null;
        if (!$row || $row['status'] !== 'active' || !$library || $library['status'] !== 'active' || !in_array('LIBRARY_ADMIN', $codes, true) || count($codes) !== 1) {
            $ci->session->sess_destroy();
            show_error('Penugasan Admin Perpustakaan tidak aktif atau tidak valid. Hubungi pengelola kabupaten.',403,'Akses Ditolak');
            exit;
        }
        $row['is_superadmin'] = false;
        $row['is_library_admin'] = true;
        $ci->session->set_userdata('network_password_stamp',$stamp);
        $ci->session->set_userdata(['auth_user'=>$row,'user_roles'=>$roles,'user_perms'=>$ci->Auth_model->load_permissions((int)$row['id'])]);
        $controller = strtolower($ci->router->fetch_class());
        $method = strtolower($ci->router->fetch_method());
        if ($controller === 'auth' && in_array($method,['index','do_login','logout'],true)) return;
        if (!empty($row['force_password_change']) && !($controller === 'library_workspace' && $method === 'account')) {
            if ($ci->input->method(true) !== 'GET') { show_error('Ganti password awal sebelum menggunakan layanan.',403); exit; }
            redirect('library-workspace/account'); exit;
        }
        if (in_array($controller,['library_workspace','library_services','iplm'],true)) return;
        // Public form controller independently pins logged-in local operators to their own library.
        if ($controller==='library_public_services'&&in_array($method,['register','kiosk'],true)) return;
        $public_methods=['home'=>['index','profile'],'library_catalog'=>['index','detail'],'library_onboarding'=>['index','search'],'public_catalog'=>['index','detail','item_detail']];
        if ($ci->input->method(true) === 'GET' && in_array($method,$public_methods[$controller]??[],true)) return;
        if ($ci->input->method(true) === 'GET' && in_array($controller,['admin','user_dashboard'],true)) { redirect('library-workspace'); exit; }
        show_error('Akun ini hanya dapat mengelola perpustakaan yang ditugaskan melalui Operasional Perpustakaan.',403,'Akses Ditolak');
        exit;
    }
}
