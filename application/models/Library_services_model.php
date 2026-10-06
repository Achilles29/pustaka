<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Explicit ownership everywhere. Stock observations never mutate live inventory. */
class Library_services_model extends CI_Model
{
    private function must($result)
    {
        if($result===false)throw new RuntimeException('Data gagal disimpan atau sudah berubah. Muat ulang dan coba kembali.');
        return $result;
    }
    private function atomic(callable $fn)
    {
        $this->must($this->db->trans_begin());
        try{$result=$fn();if(!$this->db->trans_status())throw new RuntimeException('Transaksi gagal; perubahan dibatalkan.');$this->must($this->db->trans_commit());return $result;}
        catch(Throwable $e){$this->db->trans_rollback();throw $e;}
    }
    private function text($value,$max,$required=false)
    {
        if(!is_scalar($value)&&$value!==null)throw new RuntimeException('Isian tidak valid.');
        $value=trim((string)$value);if(mb_strlen($value)>$max||($required&&$value===''))throw new RuntimeException('Isian wajib dilengkapi sesuai panjang yang diizinkan.');return $value;
    }
    public function library($id)
    {
        if(!ctype_digit((string)$id)||(int)$id<1)throw new RuntimeException('Pilih perpustakaan terlebih dahulu.');
        $row=$this->db->where('id',(int)$id)->where('status','active')->get('libraries')->row_array();
        if(!$row)throw new RuntimeException('Perpustakaan tidak aktif atau tidak ditemukan.');return $row;
    }
    private function source($source)
    {
        if(!in_array($source,['legacy','network'],true))throw new RuntimeException('Sumber koleksi tidak valid.');return $source;
    }
    private function audit($library,$actor,$event,$id)
    {
        $this->must($this->db->insert('network_audit',['library_id'=>(int)$library,'actor_id'=>(int)$actor,'event'=>$event,'entity_id'=>(int)$id]));
    }
    public function stock_sessions($library=null,$source=null)
    {
        if($library!==null){$this->library($library);$this->db->where('s.library_id',(int)$library);}
        if($source!==null)$this->db->where('s.source',$this->source($source));
        return $this->db->select('s.*,l.name library_name,(SELECT COUNT(*) FROM library_stock_entries e WHERE e.session_id=s.id) total,(SELECT COUNT(*) FROM library_stock_entries e WHERE e.session_id=s.id AND e.scanned_at IS NOT NULL) scanned',false)->from('library_stock_sessions s')->join('libraries l','l.id=s.library_id')->order_by('s.id','DESC')->limit(200)->get()->result_array();
    }
    public function stock($library,$source,$id,$lock=false)
    {
        $this->library($library);$this->source($source);
        return $this->must($this->db->query('SELECT * FROM library_stock_sessions WHERE library_id=? AND source=? AND id=?'.($lock?' FOR UPDATE':''),[(int)$library,$source,(int)$id]))->row_array();
    }
    public function stock_entries($library,$source,$id)
    {
        if(!$this->stock($library,$source,$id))throw new RuntimeException('Sesi tidak ditemukan dalam perpustakaan ini.');
        return $this->db->where('session_id',(int)$id)->order_by('id')->get('library_stock_entries')->result_array();
    }
    public function stock_create($library,$source,$title,$actor)
    {
        $this->library($library);$this->source($source);$title=$this->text($title,180,true);
        return $this->atomic(function()use($library,$source,$title,$actor){
            $this->must($this->db->query('SELECT id FROM libraries WHERE id=? FOR UPDATE',[(int)$library]));
            $active=$this->must($this->db->query('SELECT id FROM library_stock_sessions WHERE library_id=? AND source=? AND status=? FOR UPDATE',[(int)$library,$source,'open']))->row_array();
            if($active)throw new RuntimeException('Selesaikan atau batalkan sesi terbuka pada sumber ini terlebih dahulu.');
            $this->must($this->db->insert('library_stock_sessions',['library_id'=>(int)$library,'source'=>$source,'title'=>$title,'created_by'=>(int)$actor]));$id=(int)$this->db->insert_id();
            if($source==='network'){
                $sql="INSERT INTO library_stock_entries (session_id,item_id,barcode,title,location,initial_status,on_loan) SELECT ?,i.id,i.barcode,LEFT(b.title,255),i.rack,i.status,(i.status='loaned' OR EXISTS(SELECT 1 FROM network_loans n WHERE n.library_id=i.library_id AND n.item_id=i.id AND n.returned_at IS NULL)) FROM network_items i JOIN network_books b ON b.id=i.book_id AND b.library_id=i.library_id WHERE i.library_id=? AND i.deleted_at IS NULL AND b.deleted_at IS NULL AND b.format='physical' AND i.status<>'withdrawn'";
            }else{
                $sql="INSERT INTO library_stock_entries (session_id,item_id,barcode,title,location,initial_status,on_loan) SELECT ?,i.id,LEFT(COALESCE(NULLIF(i.barcode,''),NULLIF(i.item_code,''),CONCAT('ITEM-',i.id)),120),LEFT(b.title,255),LEFT(COALESCE(i.location_room_name,i.location_name),255),i.status,EXISTS(SELECT 1 FROM loan_transaction_items n WHERE n.book_item_id=i.id AND n.actual_return_at IS NULL AND n.local_return_at IS NULL AND UPPER(n.loan_status)='LOAN') FROM book_items i JOIN books b ON b.id=i.book_id WHERE i.library_id=? AND i.deleted_at IS NULL AND b.deleted_at IS NULL AND LOWER(TRIM(COALESCE(i.collection_type,''))) NOT IN ('ebook','e-book','e book','buku digital','digital','audiobook') AND LOWER(TRIM(COALESCE(i.media_name,''))) NOT IN ('digital','pdf','epub','ebook','e-book','audiobook')";
            }
            $this->must($this->db->query($sql,[$id,(int)$library]));
            if($this->db->affected_rows()<1)throw new RuntimeException('Tidak ada eksemplar fisik aktif untuk diperiksa. Sesi tidak dibuat.');
            if($source==='legacy'&&$this->db->table_exists('inter_library_loans'))$this->must($this->db->query("UPDATE library_stock_entries e JOIN inter_library_loans x ON x.item_id=e.item_id AND x.source='legacy' AND x.active_slot=1 SET e.on_loan=1 WHERE e.session_id=?",[$id]));
            $this->audit($library,$actor,'stock.created',$id);return $id;
        });
    }
    public function stock_scan($library,$source,$id,$barcode,$observed,$note,$actor)
    {
        $barcode=$this->text($barcode,120,true);$note=$this->text($note,500);
        if(!in_array($observed,['good','damaged'],true))throw new RuntimeException('Kondisi pemeriksaan tidak valid.');
        return $this->atomic(function()use($library,$source,$id,$barcode,$observed,$note,$actor){
            $session=$this->stock($library,$source,$id,true);if(!$session||$session['status']!=='open')throw new RuntimeException('Sesi tidak ditemukan atau sudah ditutup.');
            $entries=$this->must($this->db->query('SELECT * FROM library_stock_entries WHERE session_id=? AND barcode=? FOR UPDATE',[(int)$id,$barcode]))->result_array();
            if(count($entries)!==1)throw new RuntimeException(count($entries)?'Barcode ganda dalam snapshot. Perbaiki identitas eksemplar sebelum membuat sesi baru.':'Barcode tidak ada dalam snapshot sesi ini; koleksi tidak ditambahkan otomatis.');
            $e=$entries[0];$this->must($this->db->where('id',$e['id'])->update('library_stock_entries',['observed'=>$observed,'note'=>$note,'scanned_by'=>(int)$actor,'scanned_at'=>date('Y-m-d H:i:s')]));
            $this->audit($library,$actor,'stock.scan.'.$observed,(int)$e['id']);return $e['title'];
        });
    }
    public function stock_close($library,$source,$id,$status,$note,$actor)
    {
        $note=$this->text($note,2000,true);if(!in_array($status,['closed','cancelled'],true))throw new RuntimeException('Tindakan tidak valid.');
        $this->atomic(function()use($library,$source,$id,$status,$note,$actor){
            $s=$this->stock($library,$source,$id,true);if(!$s||$s['status']!=='open')throw new RuntimeException('Sesi sudah ditutup atau tidak ditemukan.');
            $this->must($this->db->where('id',(int)$id)->update('library_stock_sessions',['status'=>$status,'open_slot'=>null,'note'=>$note,'closed_by'=>(int)$actor,'closed_at'=>date('Y-m-d H:i:s')]));$this->audit($library,$actor,'stock.'.$status,$id);
        });
    }
    public function reservations($library)
    {
        $this->library($library);
        return $this->db->select('r.*,m.member_no,m.full_name,b.title')->from('network_reservations r')->join('network_members m','m.id=r.member_id AND m.library_id=r.library_id')->join('network_books b','b.id=r.book_id AND b.library_id=r.library_id')->where('r.library_id',(int)$library)->order_by('r.id','DESC')->limit(200)->get()->result_array();
    }
    public function reserve($library,$number,$book_id,$actor)
    {
        $this->library($library);$number=$this->text($number,120,true);
        return $this->atomic(function()use($library,$number,$book_id,$actor){
            $m=$this->must($this->db->query('SELECT * FROM network_members WHERE library_id=? AND member_no=? AND deleted_at IS NULL FOR UPDATE',[(int)$library,$number]))->row_array();
            $b=$this->must($this->db->query('SELECT * FROM network_books WHERE library_id=? AND id=? AND deleted_at IS NULL FOR UPDATE',[(int)$library,(int)$book_id]))->row_array();
            if(!$m||$m['status']!=='active'||!$b||$b['format']!=='physical')throw new RuntimeException('Anggota aktif dan katalog fisik harus berasal dari perpustakaan ini.');
            $term=$this->db->where('library_id',(int)$library)->where('member_id',$m['id'])->get('network_membership_terms')->row_array();
            if($term&&$term['expires_on']<date('Y-m-d'))throw new RuntimeException('Keanggotaan perlu diperpanjang.');
            $this->must($this->db->where('library_id',(int)$library)->where('book_id',(int)$book_id)->where('status','waiting')->where('expires_at <=',date('Y-m-d H:i:s'))->update('network_reservations',['status'=>'cancelled','active_slot'=>null]));
            if($this->db->where('library_id',(int)$library)->where('member_id',$m['id'])->where('status','waiting')->where('expires_at >',date('Y-m-d H:i:s'))->count_all_results('network_reservations')>=3)throw new RuntimeException('Maksimal tiga reservasi aktif per anggota.');
            $this->must($this->db->insert('network_reservations',['library_id'=>(int)$library,'member_id'=>(int)$m['id'],'book_id'=>(int)$b['id'],'expires_at'=>date('Y-m-d H:i:s',strtotime('+7 days')),'created_by'=>(int)$actor]));$id=(int)$this->db->insert_id();$this->audit($library,$actor,'reservation.created',$id);return $id;
        });
    }
    public function cancel_reservation($library,$id,$actor)
    {
        $this->library($library);$this->atomic(function()use($library,$id,$actor){
            $r=$this->must($this->db->query('SELECT * FROM network_reservations WHERE library_id=? AND id=? FOR UPDATE',[(int)$library,(int)$id]))->row_array();
            if(!$r||$r['status']!=='waiting')throw new RuntimeException('Reservasi aktif tidak ditemukan.');
            $this->must($this->db->where('id',$r['id'])->update('network_reservations',['status'=>'cancelled','active_slot'=>null]));$this->audit($library,$actor,'reservation.cancelled',$id);
        });
    }
    public function registrations($library)
    {
        $this->library($library);return $this->db->where('library_id',(int)$library)->order_by('id','DESC')->limit(200)->get('network_registrations')->result_array();
    }
    public function register($library,array $input)
    {
        $this->library($library);$ci=&get_instance();$ci->load->model('Library_network_model');
        if(!in_array($input['kind']??'',['new','renewal'],true))throw new RuntimeException('Jenis pendaftaran tidak valid.');
        $data=$ci->Library_network_model->validate('members',array_merge($input,['status'=>'active']));unset($data['status']);
        if($input['kind']==='new')$data['member_no']=null;
        elseif(!$data['member_no']||!$data['birth_date'])throw new RuntimeException('Perpanjangan memerlukan nomor anggota dan tanggal lahir untuk diperiksa petugas.');
        $data+=['kind'=>$input['kind'],'library_id'=>(int)$library];
        $this->must($this->db->insert('network_registrations',$data));return (int)$this->db->insert_id();
    }
    public function review_registration($library,$id,$decision,$expires,$note,$actor)
    {
        $this->library($library);$note=$this->text($note,500,true);
        if(!in_array($decision,['approved','rejected'],true))throw new RuntimeException('Keputusan tidak valid.');
        if($decision==='approved'){$date=DateTimeImmutable::createFromFormat('!Y-m-d',(string)$expires);if(!$date||$date->format('Y-m-d')!==$expires||$expires<date('Y-m-d')||$expires>date('Y-m-d',strtotime('+10 years')))throw new RuntimeException('Masa berlaku harus hari ini sampai sepuluh tahun mendatang.');}
        return $this->atomic(function()use($library,$id,$decision,$expires,$note,$actor){
            $r=$this->must($this->db->query('SELECT * FROM network_registrations WHERE library_id=? AND id=? FOR UPDATE',[(int)$library,(int)$id]))->row_array();
            if(!$r||$r['status']!=='pending')throw new RuntimeException('Permohonan sudah diproses atau tidak ditemukan.');
            $member=null;
            if($decision==='approved'){
                if($r['kind']==='new'){
                    $data=array_intersect_key($r,array_flip(['full_name','birth_date','gender','phone','email','address']));$data+=['library_id'=>(int)$library,'member_no'=>'L'.$library.'-R'.$id,'status'=>'active'];
                    $this->must($this->db->insert('network_members',$data));$member=(int)$this->db->insert_id();
                }else{
                    $m=$this->must($this->db->query('SELECT * FROM network_members WHERE library_id=? AND member_no=? AND deleted_at IS NULL FOR UPDATE',[(int)$library,$r['member_no']]))->row_array();
                    if(!$m||$m['status']==='blocked'||!$m['birth_date']||$m['birth_date']!==$r['birth_date']||mb_strtolower(trim($m['full_name']))!==mb_strtolower(trim($r['full_name'])))throw new RuntimeException('Identitas perpanjangan tidak cocok atau anggota diblokir. Verifikasi langsung; data lama tidak ditimpa.');
                    $member=(int)$m['id'];$term=$this->db->where('library_id',(int)$library)->where('member_id',$member)->get('network_membership_terms')->row_array();if($term&&$expires<$term['expires_on'])throw new RuntimeException('Perpanjangan tidak boleh memendekkan masa berlaku.');
                    $this->must($this->db->where('id',$member)->where('library_id',(int)$library)->update('network_members',['status'=>'active']));
                }
                $this->must($this->db->replace('network_membership_terms',['library_id'=>(int)$library,'member_id'=>$member,'expires_on'=>$expires,'updated_by'=>(int)$actor]));
            }
            $this->must($this->db->where('id',(int)$id)->update('network_registrations',['status'=>$decision,'member_id'=>$member,'reviewed_by'=>(int)$actor,'reviewed_at'=>date('Y-m-d H:i:s'),'review_note'=>$note]));$this->audit($library,$actor,'registration.'.$decision,$id);return $member;
        });
    }
    public function rate_limit($key,$max)
    {
        // No raw address is stored. Atomic increment survives parallel requests and fresh cookies.
        $bucket=hash('sha256',$key.'|'.date('Y-m-d-H'));
        $this->must($this->db->query('INSERT INTO network_public_limits (bucket,hits,expires_at) VALUES (?,1,DATE_ADD(NOW(),INTERVAL 2 HOUR)) ON DUPLICATE KEY UPDATE hits=hits+1',[$bucket]));
        $row=$this->db->where('bucket',$bucket)->get('network_public_limits')->row_array();
        if((int)$row['hits']>$max)throw new RuntimeException('Batas pengiriman sementara tercapai. Silakan coba lagi nanti.');
    }
    public function kiosk_create($library,$actor)
    {
        $this->library($library);$token=bin2hex(random_bytes(32));
        $this->atomic(function()use($library,$actor,$token){$this->must($this->db->insert('network_kiosks',['library_id'=>(int)$library,'token_hash'=>hash('sha256',$token),'expires_at'=>date('Y-m-d H:i:s',strtotime('+1 day')),'created_by'=>(int)$actor]));$this->audit($library,$actor,'kiosk.created',$this->db->insert_id());});return $token;
    }
    public function kiosks($library)
    {
        $this->library($library);return $this->db->select('id,expires_at,revoked_at,created_at')->where('library_id',(int)$library)->order_by('id','DESC')->limit(50)->get('network_kiosks')->result_array();
    }
    public function kiosk_revoke($library,$id,$actor)
    {
        $this->library($library);$this->atomic(function()use($library,$id,$actor){$this->must($this->db->where('library_id',(int)$library)->where('id',(int)$id)->where('revoked_at IS NULL',null,false)->update('network_kiosks',['revoked_at'=>date('Y-m-d H:i:s')]));if($this->db->affected_rows()!==1)throw new RuntimeException('Tautan aktif tidak ditemukan.');$this->audit($library,$actor,'kiosk.revoked',$id);});
    }
    public function kiosk($token)
    {
        if(!is_string($token)||!preg_match('/^[a-f0-9]{64}$/D',$token))return null;
        return $this->db->select('k.*,l.name library_name')->from('network_kiosks k')->join('libraries l','l.id=k.library_id')->where('k.token_hash',hash('sha256',$token))->where('k.revoked_at IS NULL',null,false)->where('k.expires_at >',date('Y-m-d H:i:s'))->where('l.status','active')->get()->row_array();
    }
    public function kiosk_visit($token,array $input)
    {
        return $this->atomic(function()use($token,$input){
            $k=$this->kiosk($token);if(!$k)throw new RuntimeException('Tautan sudah berakhir atau dinonaktifkan.');
            $locked=$this->must($this->db->query('SELECT * FROM network_kiosks WHERE id=? FOR UPDATE',[$k['id']]))->row_array();if($locked['revoked_at']||$locked['expires_at']<=date('Y-m-d H:i:s'))throw new RuntimeException('Tautan sudah berakhir atau dinonaktifkan.');
            $ci=&get_instance();$ci->load->model('Library_network_model');
            if(!in_array($input['purpose']??'',$ci->Library_network_model->purposes(),true))throw new RuntimeException('Pilih tujuan kunjungan.');
            $number=$this->text($input['member_no']??'',120);$name=$this->text($input['guest_name']??'',180);
            $m=$number!==''?$this->db->where('library_id',$k['library_id'])->where('member_no',$number)->where('status','active')->where('deleted_at IS NULL',null,false)->get('network_members')->row_array():null;
            if(($number!==''&&!$m)||(!$m&&$name===''))throw new RuntimeException('Isian belum dapat diterima. Periksa nomor anggota atau isi nama pengunjung.');
            $query=$this->db->where('library_id',$k['library_id'])->where('visited_at >',date('Y-m-d H:i:s',strtotime('-5 minutes')));
            if($m)$query->where('member_id',$m['id']);else $query->where('member_id IS NULL',null,false)->where('guest_name',$name);
            if($query->count_all_results('network_visits'))throw new RuntimeException('Kunjungan ini sudah dicatat dalam lima menit terakhir.');
            $this->must($this->db->insert('network_visits',['library_id'=>$k['library_id'],'member_id'=>$m?$m['id']:null,'guest_name'=>$m?null:$name,'purpose'=>$input['purpose'],'channel'=>'offline','visited_at'=>date('Y-m-d H:i:s'),'created_by'=>$k['created_by']]));$id=(int)$this->db->insert_id();$this->audit($k['library_id'],$k['created_by'],'visit.kiosk',$id);return $id;
        });
    }
}
