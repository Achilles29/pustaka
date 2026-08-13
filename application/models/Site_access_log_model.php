<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Site_access_log_model extends CI_Model
{
    public function capture_current_request()
    {
        if (! $this->db->table_exists('site_access_logs') || $this->should_skip_request()) return 0;
        $user = (array) $this->session->userdata('auth_user');
        $identity = $this->identity_for_user((int) ($user['id'] ?? 0));
        $agent = substr((string) $this->input->user_agent(), 0, 255);
        $uri = trim((string) uri_string(), '/');
        $ip = $this->input->ip_address();
        $this->db->insert('site_access_logs', [
            'auth_user_id' => ! empty($user['id']) ? (int) $user['id'] : null,
            'member_id' => ! empty($identity['member_id']) ? (int) $identity['member_id'] : null,
            'visitor_type' => $this->visitor_type($user, $identity),
            'identity_label' => $identity['identity_label'] ?? null,
            'member_no' => $identity['member_no'] ?? null,
            'username' => $identity['username'] ?? null,
            'request_method' => strtoupper((string) $this->input->method(true)),
            'uri_path' => substr($uri === '' ? '/' : '/' . $uri, 0, 255),
            'route_name' => substr($this->router->class . '/' . $this->router->method, 0, 160),
            'http_status' => max(100, min(599, (int) (http_response_code() ?: 200))),
            'ip_address' => filter_var($ip, FILTER_VALIDATE_IP) ? $ip : null,
            'country_code' => $this->cloudflare_country(),
            'device_type' => $this->device_type($agent),
            'browser_name' => $this->browser_name($agent),
            'os_name' => $this->os_name($agent),
            'user_agent' => $agent ?: null,
            'referrer' => substr((string) $this->input->server('HTTP_REFERER', true), 0, 255) ?: null,
        ]);
        return (int) $this->db->insert_id();
    }

    public function get_logs(array $filters = [], $limit = 25, $offset = 0)
    {
        $this->build_query($filters);
        return $this->db->order_by('l.id', 'DESC')->limit(max(10, min(100, (int) $limit)), max(0, (int) $offset))->get()->result_array();
    }

    public function count_logs(array $filters = [])
    {
        $this->build_query($filters);
        return (int) $this->db->count_all_results();
    }

    public function stats_today()
    {
        if (! $this->db->table_exists('site_access_logs')) return ['total' => 0, 'guest' => 0, 'member' => 0, 'staff' => 0, 'unique_ips' => 0];
        $row = $this->db->select("COUNT(*) AS total, SUM(visitor_type = 'guest') AS guest, SUM(visitor_type = 'member') AS member, SUM(visitor_type = 'staff') AS staff, COUNT(DISTINCT ip_address) AS unique_ips", false)->from('site_access_logs')->where('created_at >=', date('Y-m-d 00:00:00'))->get()->row_array();
        return array_merge(['total' => 0, 'guest' => 0, 'member' => 0, 'staff' => 0, 'unique_ips' => 0], (array) $row);
    }

    public function route_options()
    {
        return $this->db->select('uri_path')->from('site_access_logs')->group_by('uri_path')->order_by('MAX(created_at)', 'DESC', false)->limit(100)->get()->result_array();
    }

    private function build_query(array $filters)
    {
        $this->db->from('site_access_logs l');
        if (! empty($filters['visitor_type']) && in_array($filters['visitor_type'], ['guest', 'member', 'staff'], true)) $this->db->where('l.visitor_type', $filters['visitor_type']);
        if (! empty($filters['device_type']) && in_array($filters['device_type'], ['mobile', 'tablet', 'desktop', 'bot', 'other'], true)) $this->db->where('l.device_type', $filters['device_type']);
        if (! empty($filters['uri_path'])) $this->db->where('l.uri_path', $filters['uri_path']);
        if (! empty($filters['date_from'])) $this->db->where('l.created_at >=', $filters['date_from'] . ' 00:00:00');
        if (! empty($filters['date_to'])) $this->db->where('l.created_at <=', $filters['date_to'] . ' 23:59:59');
        if (! empty($filters['q'])) {
            $q = trim((string) $filters['q']);
            $this->db->group_start()->like('l.identity_label', $q)->or_like('l.member_no', $q)->or_like('l.username', $q)->or_like('l.ip_address', $q)->or_like('l.uri_path', $q)->or_like('l.user_agent', $q)->group_end();
        }
    }

    private function identity_for_user($auth_user_id)
    {
        if ($auth_user_id <= 0) return [];
        $row = $this->db->select('u.username, u.full_name, m.id AS member_id, m.member_no, m.full_name AS member_name')->from('auth_user u')->join('members m', 'm.auth_user_id = u.id AND m.deleted_at IS NULL', 'left', false)->where('u.id', $auth_user_id)->limit(1)->get()->row_array();
        if (! $row) return [];
        return ['member_id' => $row['member_id'] ?? null, 'member_no' => $row['member_no'] ?? null, 'username' => $row['username'] ?? null, 'identity_label' => $row['member_name'] ?: $row['full_name'] ?: $row['username'] ?: null];
    }

    private function visitor_type(array $user, array $identity) { return empty($user['id']) ? 'guest' : (! empty($identity['member_id']) ? 'member' : 'staff'); }
    private function should_skip_request() { return is_cli() || strpos(trim((string) uri_string(), '/'), 'access-monitor') === 0; }
    private function cloudflare_country() { $country = strtoupper(trim((string) $this->input->server('HTTP_CF_IPCOUNTRY', true))); return preg_match('/^[A-Z]{2}$/', $country) ? $country : null; }
    private function device_type($agent) { $agent = strtolower($agent); if (preg_match('/bot|crawler|spider|slurp|curl|wget/', $agent)) return 'bot'; if (preg_match('/ipad|tablet|kindle|silk/', $agent)) return 'tablet'; if (preg_match('/mobile|android|iphone|ipod/', $agent)) return 'mobile'; return $agent === '' ? 'other' : 'desktop'; }
    private function browser_name($agent) { if (stripos($agent, 'edg/') !== false) return 'Microsoft Edge'; if (stripos($agent, 'opr/') !== false || stripos($agent, 'opera') !== false) return 'Opera'; if (stripos($agent, 'firefox/') !== false) return 'Firefox'; if (stripos($agent, 'chrome/') !== false || stripos($agent, 'crios/') !== false) return 'Chrome'; if (stripos($agent, 'safari/') !== false) return 'Safari'; return 'Lainnya'; }
    private function os_name($agent) { if (stripos($agent, 'windows') !== false) return 'Windows'; if (stripos($agent, 'android') !== false) return 'Android'; if (stripos($agent, 'iphone') !== false || stripos($agent, 'ipad') !== false) return 'iOS'; if (stripos($agent, 'mac os') !== false || stripos($agent, 'macintosh') !== false) return 'macOS'; if (stripos($agent, 'linux') !== false) return 'Linux'; return 'Lainnya'; }
}
