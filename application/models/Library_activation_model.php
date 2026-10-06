<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Library_activation_model extends CI_Model
{
    private function query($sql,$args=[]){$r=$this->db->query($sql,$args?:false);if($r===false)throw new RuntimeException('Penyimpanan aktivasi gagal.');return $r;}
    private function atomic($fn){$this->db->trans_begin();try{$r=$fn();if(!$this->db->trans_status())throw new RuntimeException('Transaksi aktivasi gagal.');$this->db->trans_commit();return$r;}catch(Throwable$e){$this->db->trans_rollback();throw$e;}}
    private function library($id){$l=$this->query('SELECT l.*,s.code subtype_code FROM libraries l LEFT JOIN library_subtypes s ON s.id=l.library_subtype_id WHERE l.id=? FOR UPDATE',[(int)$id])->row_array();if(!$l||$l['status']!=='active'||$l['subtype_code']==='kabupaten')throw new RuntimeException('Perpustakaan tidak dapat diaktivasi mandiri.');return$l;}
    private function unclaimed_users($id){
        if($this->db->table_exists('library_self_registrations') && $this->query('SELECT library_id FROM library_self_registrations WHERE library_id=? FOR UPDATE',[(int)$id])->num_rows())throw new RuntimeException('Perpustakaan sudah diaktivasi. Silakan login; jika ada masalah hubungi admin kabupaten.');
        $users=$this->query('SELECT * FROM auth_user WHERE library_id=? ORDER BY id FOR UPDATE',[(int)$id])->result_array();
        foreach($users as$u)if($u['last_login_at']!==null||!$u['force_password_change'])throw new RuntimeException('Akun perpustakaan sudah pernah digunakan. Hubungi admin kabupaten; klaim ulang tidak diizinkan.');
        $token=$this->query('SELECT * FROM library_activation_codes WHERE library_id=? FOR UPDATE',[(int)$id])->row_array();if($token&&$token['claimed_at'])throw new RuntimeException('Perpustakaan sudah diaktivasi. Hubungi admin kabupaten.');return$users;
    }
    private function local_user($u){$roles=$this->query('SELECT r.code,r.is_active FROM auth_user_role ur JOIN auth_role r ON r.id=ur.role_id WHERE ur.user_id=?',[$u['id']])->result_array();if($u['status']!=='active'||count($roles)!==1||$roles[0]['code']!=='LIBRARY_ADMIN'||!$roles[0]['is_active'])throw new RuntimeException('Penugasan akun tidak valid. Hubungi admin kabupaten.');}
    private function audit($library,$actor,$event){$this->query('INSERT INTO network_audit (library_id,actor_id,event,entity_id) VALUES (?,?,?,?)',[(int)$library,(int)$actor,$event,(int)$library]);}
    public function register_self($id,array $input){
        foreach($input as$value)if(!is_scalar($value)&&$value!==null)throw new RuntimeException('Isian pendaftaran tidak valid.');
        $name=trim((string)($input['registrant_name']??''));$phone=trim((string)($input['registrant_phone']??''));$password=(string)($input['password']??'');
        if(mb_strlen($name)<3||mb_strlen($name)>180)throw new RuntimeException('Isi nama pengelola 3–180 karakter.');
        if(!preg_match('/^\+?[0-9][0-9 ()-]{7,24}$/D',$phone))throw new RuntimeException('Isi nomor telepon pengelola yang valid.');
        if(strlen($password)<10||strlen($password)>72||strpos($password,"\0")!==false||$password!==(string)($input['confirmation']??''))throw new RuntimeException('Password minimal 10, maksimal 72 byte; konfirmasi harus sama.');
        if(($input['ownership']??'')!=='1')throw new RuntimeException('Pernyataan sebagai pengelola resmi wajib disetujui.');
        if(!$this->db->table_exists('library_self_registrations'))throw new RuntimeException('Pendaftaran mandiri belum siap. Silakan coba lagi.');
        $hash=password_hash($password,PASSWORD_BCRYPT);
        return$this->atomic(function()use($id,$name,$phone,$hash){
            $l=$this->library($id);$users=$this->unclaimed_users($id);
            // Do not bypass suspended accounts, mixed roles, or administrator reassignment.
            foreach($users as$candidate)$this->local_user($candidate);
            if($users){$u=$users[0];}
            else{
                $base=mb_substr(strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/','.', $l['institution_name']?:$l['name']),'.')),0,60);$username=($base?:'perpustakaan').'.'.$l['id'];
                $role=$this->query("SELECT id FROM auth_role WHERE code='LIBRARY_ADMIN' AND is_active=1")->row_array();if(!$role)throw new RuntimeException('Peran admin perpustakaan belum tersedia.');
                if($this->db->where('username',$username)->count_all_results('auth_user'))throw new RuntimeException('Username sudah digunakan. Hubungi admin kabupaten untuk memeriksa akun.');
                $this->query('INSERT INTO auth_user(username,full_name,library_id,password_hash,source_system,status,force_password_change) VALUES (?,?,?,?,?,?,0)',[$username,$name,$id,$hash,'library_self_registration','active']);$uid=$this->db->insert_id();
                $this->query('INSERT INTO auth_user_role(user_id,role_id) VALUES (?,?)',[$uid,$role['id']]);$u=$this->query('SELECT * FROM auth_user WHERE id=?',[$uid])->row_array();
            }
            $this->query('UPDATE auth_user SET full_name=?,password_hash=?,force_password_change=0,last_login_at=NOW(),updated_at=NOW() WHERE id=?',[$name,$hash,$u['id']]);
            // Other never-used bootstrap accounts must not retain a shared initial password.
            foreach($users as$other)if($other['id']!==$u['id'])$this->query('UPDATE auth_user SET password_hash=?,updated_at=NOW() WHERE id=?',[password_hash(bin2hex(random_bytes(32)),PASSWORD_BCRYPT),$other['id']]);
            $this->query('INSERT INTO library_self_registrations(library_id,user_id,registrant_name,registrant_phone) VALUES (?,?,?,?)',[$id,$u['id'],$name,$phone]);
            $this->query('UPDATE library_activation_codes SET claimed_at=NOW(),expires_at=NOW() WHERE library_id=?',[$id]);
            $this->audit($id,$u['id'],'activation.self_registered');
            $u['full_name']=$name;$u['password_hash']=$hash;$u['force_password_change']=0;return$u;
        });
    }
    public function moderate_account($library,$user,$action,$actor,$note){
        if(!in_array($action,['suspend','restore','reset'],true)||!is_string($note)||mb_strlen(trim($note))<5||mb_strlen($note)>500)throw new RuntimeException('Pilih tindakan dan isi alasan 5–500 karakter.');
        return$this->atomic(function()use($library,$user,$action,$actor,$note){
            $l=$this->library($library);$u=$this->query('SELECT * FROM auth_user WHERE id=? AND library_id=? FOR UPDATE',[$user,$library])->row_array();if(!$u)throw new RuntimeException('Akun tidak ditemukan dalam perpustakaan ini.');
            $candidate=$u;$candidate['status']='active';$this->local_user($candidate);
            $result=['username'=>$u['username'],'action'=>$action];
            if($action==='reset'){
                if($u['status']!=='active')throw new RuntimeException('Aktifkan kembali akun terlebih dahulu sebelum reset password.');
                $temporary='Rmb!'.bin2hex(random_bytes(6));$this->query('UPDATE auth_user SET password_hash=?,force_password_change=1,updated_at=NOW() WHERE id=?',[password_hash($temporary,PASSWORD_BCRYPT),$user]);$result['temporary_password']=$temporary;
            }else{$this->query('UPDATE auth_user SET status=?,password_hash=?,updated_at=NOW() WHERE id=?',[$action==='suspend'?'inactive':'active',password_hash(bin2hex(random_bytes(32)),PASSWORD_BCRYPT),$user]);}
            $this->query('INSERT INTO iplm_history(actor_id,event,payload_json) VALUES (?,?,?)',[$actor,'activation.account.'.$action,json_encode(['library_id'=>(int)$library,'user_id'=>(int)$user,'note'=>trim($note),'before_status'=>$u['status']],JSON_UNESCAPED_UNICODE)]);
            $this->audit($library,$actor,'activation.account.'.$action);return$result;
        });
    }
    public function issue($id,$actor){return$this->atomic(function()use($id,$actor){
        $l=$this->library($id);$users=$this->unclaimed_users($id);
        if(!$users){
            $name=mb_substr(strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/','.', $l['institution_name']?:$l['name']),'.')),0,60);if($name==='')$name='perpustakaan';$username=$name.'.'.$l['id'];
            if($this->db->where('username',$username)->count_all_results('auth_user'))throw new RuntimeException('Username sudah dipakai. Hubungi admin kabupaten.');
            $this->query('INSERT INTO auth_user (username,full_name,library_id,password_hash,source_system,status,force_password_change) VALUES (?,?,?,?,?,?,1)',[$username,mb_substr($l['name'],0,180),$l['id'],password_hash(bin2hex(random_bytes(32)),PASSWORD_BCRYPT),'library_network_v1','active']);$uid=$this->db->insert_id();
            $role=$this->query("SELECT id FROM auth_role WHERE code='LIBRARY_ADMIN' AND is_active=1")->row_array();if(!$role)throw new RuntimeException('Peran lokal belum tersedia.');$this->query('INSERT INTO auth_user_role(user_id,role_id) VALUES (?,?)',[$uid,$role['id']]);$u=$this->query('SELECT * FROM auth_user WHERE id=?',[$uid])->row_array();
        }else{$u=null;foreach($users as$candidate){try{$this->local_user($candidate);$u=$candidate;break;}catch(RuntimeException$e){}}if(!$u)throw new RuntimeException('Tidak ada akun admin lokal aktif yang dapat diaktivasi.');}
        $this->local_user($u);$raw=strtoupper(bin2hex(random_bytes(16)));$code=implode('-',str_split($raw,4));$expires=date('Y-m-d H:i:s',time()+7*86400);
        $this->query('INSERT INTO library_activation_codes(library_id,user_id,token_hash,expires_at,issued_by) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE user_id=VALUES(user_id),token_hash=VALUES(token_hash),expires_at=VALUES(expires_at),issued_by=VALUES(issued_by),issued_at=NOW()',[$id,$u['id'],hash('sha256',$raw),$expires,$actor]);$this->audit($id,$actor,'activation.issued');
        return ['library_id'=>$id,'name'=>$l['name'],'username'=>$u['username'],'code'=>$code,'expires_at'=>$expires];
    });}
    public function claim($id,$code){
        if(!is_string($code)||strlen($code)>80)throw new RuntimeException('Kode aktivasi tidak valid.');$raw=strtoupper(str_replace(['-',' '],'',trim($code)));if(!preg_match('/^[0-9A-F]{32}$/D',$raw))throw new RuntimeException('Kode aktivasi tidak valid.');
        return$this->atomic(function()use($id,$raw){
            $this->library($id);$users=$this->unclaimed_users($id);$token=$this->query('SELECT * FROM library_activation_codes WHERE library_id=? FOR UPDATE',[(int)$id])->row_array();
            if(!$token||strtotime($token['expires_at'])<=time()||!hash_equals($token['token_hash'],hash('sha256',$raw)))throw new RuntimeException('Kode aktivasi tidak valid atau kedaluwarsa. Hubungi admin kabupaten.');
            $u=null;foreach($users as$candidate)if($candidate['id']===$token['user_id'])$u=$candidate;if(!$u)throw new RuntimeException('Penugasan akun sudah berubah.');$this->local_user($u);
            // Consume once, rotate away any old shared password, then grant a short
            // authenticated password-setup session. Nothing can reclaim this library.
            $hash=password_hash(bin2hex(random_bytes(32)),PASSWORD_BCRYPT);$this->query('UPDATE auth_user SET password_hash=?,last_login_at=NOW(),force_password_change=1 WHERE id=?',[$hash,$u['id']]);$this->query('UPDATE library_activation_codes SET claimed_at=NOW() WHERE library_id=?',[(int)$id]);$this->audit($id,$u['id'],'activation.claimed');$u['password_hash']=$hash;$u['force_password_change']=1;return$u;
        });
    }
    public function revoke($id,$actor){return$this->atomic(function()use($id,$actor){$this->library($id);$this->unclaimed_users($id);$this->query('UPDATE library_activation_codes SET expires_at=NOW() WHERE library_id=?',[(int)$id]);$this->audit($id,$actor,'activation.revoked');});}
}
