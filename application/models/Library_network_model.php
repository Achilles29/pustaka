<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** All operational methods require an explicit library. Never use NULL as global scope. */
class Library_network_model extends CI_Model
{
    const ROLE = 'LIBRARY_ADMIN';
    const SOURCE = 'library_network_v1';

    public function definitions()
    {
        return [
            'books' => ['title'=>'Katalog','fields'=>[
                'title'=>['Judul','text',255,true], 'author'=>['Pengarang','text',180,false],
                'isbn'=>['ISBN','text',80,false], 'publisher'=>['Penerbit','text',180,false],
                'publish_year'=>['Tahun terbit','year',4,false], 'classification'=>['Klasifikasi','text',80,false],
                'format'=>['Jenis koleksi','select',['physical'=>'Fisik','digital'=>'Digital'],true],
                'digital_url'=>['Tautan digital HTTPS (jika berhak membagikan)','url',500,false],
                'description'=>['Deskripsi','textarea',5000,false],
                'status'=>['Publikasi','select',['draft'=>'Draf / internal','published'=>'Tayang di katalog jejaring'],true],
            ],'columns'=>['title'=>'Judul','author'=>'Pengarang','isbn'=>'ISBN','format'=>'Jenis','status'=>'Publikasi']],
            'items' => ['title'=>'Eksemplar','fields'=>[
                'book_id'=>['Judul katalog','book',null,true], 'barcode'=>['Barcode','text',120,true],
                'inventory_no'=>['Nomor inventaris','text',120,false], 'rack'=>['Rak / lokasi','text',120,false],
                'status'=>['Status','select',['available'=>'Tersedia','damaged'=>'Rusak','missing'=>'Hilang','withdrawn'=>'Ditarik'],true],
            ],'columns'=>['barcode'=>'Barcode','book_title'=>'Judul','inventory_no'=>'Inventaris','rack'=>'Rak','status'=>'Status']],
            'members' => ['title'=>'Anggota Lokal','fields'=>[
                'member_no'=>['Nomor anggota / NIS (kosong = otomatis)','text',120,false],
                'full_name'=>['Nama lengkap','text',180,true], 'birth_date'=>['Tanggal lahir','date',10,false],
                'gender'=>['Jenis kelamin','select',[''=>'Belum diisi','L'=>'Laki-laki','P'=>'Perempuan'],false],
                'phone'=>['Telepon','text',40,false], 'email'=>['Email','email',180,false],
                'address'=>['Alamat','textarea',2000,false],
                'status'=>['Status','select',['active'=>'Aktif','inactive'=>'Nonaktif','blocked'=>'Diblokir'],true],
            ],'columns'=>['member_no'=>'Nomor anggota','full_name'=>'Nama','gender'=>'Jenis kelamin','status'=>'Status']],
        ];
    }

    private function scope($library_id)
    {
        if (!is_numeric($library_id) || (int)$library_id < 1 || (string)(int)$library_id !== (string)$library_id) throw new RuntimeException('Cakupan perpustakaan wajib valid.');
        return (int)$library_id;
    }

    private function table($entity)
    {
        if (!isset($this->definitions()[$entity])) throw new RuntimeException('Jenis data tidak dikenal.');
        return 'network_' . $entity;
    }

    private function must($ok)
    {
        if ($ok === false) {
            $code = (int)($this->db->error()['code'] ?? 0);
            throw new RuntimeException($code === 1062 ? 'Kode/barcode/nomor sudah digunakan di perpustakaan ini, termasuk pada arsip.' : 'Penyimpanan gagal. Tidak ada perubahan yang diterapkan.');
        }
        return $ok;
    }

    private function atomic(callable $work)
    {
        $this->must($this->db->trans_begin());
        try {
            $result = $work();
            if (!$this->db->trans_status()) throw new RuntimeException('Transaksi gagal; data tidak diubah.');
            $this->must($this->db->trans_commit());
            return $result;
        } catch (Throwable $e) { $this->db->trans_rollback(); throw $e; }
    }

    public function audit($library_id, $actor, $event, $id = null)
    {
        $this->must($this->db->insert('network_audit',['library_id'=>$this->scope($library_id),'actor_id'=>(int)$actor,'event'=>$event,'entity_id'=>$id]));
    }

    public function library($library_id)
    {
        return $this->db->where('id',$this->scope($library_id))->get('libraries')->row_array();
    }

    public function listing($library_id, $entity, $q = '', $limit = 25, $offset = 0)
    {
        $table = $this->table($entity); $library_id = $this->scope($library_id);
        $build = function () use ($table,$entity,$library_id,$q) {
            $this->db->from($table.' n')->where('n.library_id',$library_id)->where('n.deleted_at IS NULL',null,false);
            if ($entity === 'items') $this->db->join('network_books b','b.id=n.book_id AND b.library_id=n.library_id');
            if (trim((string)$q) !== '') {
                $field = $entity === 'books' ? 'n.title' : ($entity === 'members' ? 'n.full_name' : 'n.barcode');
                $other = $entity === 'books' ? 'n.isbn' : ($entity === 'members' ? 'n.member_no' : 'b.title');
                $this->db->group_start()->like($field,trim($q))->or_like($other,trim($q))->group_end();
            }
        };
        $build(); $total = (int)$this->db->count_all_results();
        $build();
        $rows = $this->db->select($entity === 'items' ? 'n.*,b.title AS book_title' : 'n.*')->order_by('n.id','DESC')->limit(max(1,min(1000,(int)$limit)),max(0,(int)$offset))->get()->result_array();
        return ['total'=>$total,'rows'=>$rows];
    }

    public function find($library_id, $entity, $id, $lock = false)
    {
        $table = $this->table($entity);
        return $this->db->query('SELECT * FROM '.$table.' WHERE library_id=? AND id=? AND deleted_at IS NULL'.($lock?' FOR UPDATE':''),[$this->scope($library_id),(int)$id])->row_array();
    }

    public function validate($entity, array $input)
    {
        $this->table($entity); $result=[];
        foreach ($this->definitions()[$entity]['fields'] as $field=>$definition) {
            [$label,$type,$constraint,$required] = $definition;
            $raw = $input[$field] ?? '';
            if (!is_scalar($raw) && $raw !== null) throw new RuntimeException($label.' tidak valid.');
            $value = trim((string)$raw);
            if ($required && $value === '') throw new RuntimeException($label.' wajib diisi.');
            if ($type === 'select' && $value !== '' && !array_key_exists($value,$constraint)) throw new RuntimeException($label.' tidak valid.');
            if (is_int($constraint) && mb_strlen($value) > $constraint) throw new RuntimeException($label.' terlalu panjang.');
            if ($value !== '' && $type === 'year' && (!preg_match('/^\d{4}$/D',$value) || (int)$value < 1000 || (int)$value > (int)date('Y')+1)) throw new RuntimeException('Tahun terbit tidak valid.');
            if ($value !== '' && $type === 'date') {
                $date = DateTimeImmutable::createFromFormat('!Y-m-d',$value);
                if (!$date || $date->format('Y-m-d') !== $value || $value > date('Y-m-d') || $value < '1900-01-01') throw new RuntimeException('Tanggal lahir tidak valid.');
            }
            if ($value !== '' && $type === 'email' && !filter_var($value,FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Email tidak valid.');
            if ($value !== '' && $type === 'url' && (!filter_var($value,FILTER_VALIDATE_URL) || strtolower((string)parse_url($value,PHP_URL_SCHEME)) !== 'https' || parse_url($value,PHP_URL_USER) !== null)) throw new RuntimeException('Tautan digital harus berupa URL HTTPS tanpa kredensial.');
            if ($type === 'book' && (!ctype_digit($value) || (int)$value < 1)) throw new RuntimeException('Pilih judul katalog yang valid.');
            $result[$field] = $value === '' ? null : ($type === 'book' ? (int)$value : $value);
        }
        if ($entity === 'books' && $result['format'] === 'physical') $result['digital_url'] = null;
        return $result;
    }

    public function save($library_id, $entity, $id, array $input, $actor)
    {
        return $this->atomic(function () use ($library_id,$entity,$id,$input,$actor) {
            return $this->save_row($library_id,$entity,$id,$input,$actor);
        });
    }

    private function save_row($library_id, $entity, $id, array $input, $actor)
    {
        $library_id = $this->scope($library_id); $table = $this->table($entity);
        $before = $id ? $this->find($library_id,$entity,$id,true) : null;
        if ($id && !$before) throw new RuntimeException('Data tidak ditemukan dalam perpustakaan ini.');
        if ($entity === 'items' && ($before['status'] ?? '') === 'loaned') throw new RuntimeException('Eksemplar sedang dipinjam. Selesaikan pengembalian sebelum mengedit.');
        $data = $this->validate($entity,$input);
        if ($entity === 'books' && ($before['format'] ?? '') === 'physical' && $data['format'] === 'digital' && $this->db->where('library_id',$library_id)->where('book_id',(int)$id)->where('deleted_at IS NULL',null,false)->count_all_results('network_items')) throw new RuntimeException('Arsipkan eksemplar fisik sebelum mengubah jenis koleksi.');
        if ($entity === 'items') {
            $book = $this->find($library_id,'books',$data['book_id'],true);
            if (!$book || $book['format'] !== 'physical') throw new RuntimeException('Eksemplar harus terhubung ke katalog fisik milik perpustakaan ini.');
            if ($before && (int)$before['book_id'] !== $data['book_id']) throw new RuntimeException('Judul eksemplar yang sudah dibuat tidak dapat dipindahkan.');
        }
        $auto_number = $entity === 'members' && !$id && !$data['member_no'];
        if ($auto_number) $data['member_no'] = 'TMP-'.bin2hex(random_bytes(16));
        if ($entity === 'members' && $id && !$data['member_no']) $data['member_no'] = $before['member_no'];
        $data['updated_at'] = date('Y-m-d H:i:s');
        if ($id) $this->must($this->db->where('library_id',$library_id)->where('id',(int)$id)->update($table,$data));
        else {
            $data['library_id'] = $library_id;
            $this->must($this->db->insert($table,$data)); $id = (int)$this->db->insert_id();
            if ($auto_number) $this->must($this->db->where('library_id',$library_id)->where('id',$id)->update($table,['member_no'=>'L'.$library_id.'-M'.str_pad((string)$id,7,'0',STR_PAD_LEFT)]));
        }
        $this->audit($library_id,$actor,$entity.'.'.($before?'updated':'created'),(int)$id);
        return (int)$id;
    }

    public function import_rows($library_id, $entity, array $rows, $actor)
    {
        if (!$rows || count($rows)>500) throw new RuntimeException('Impor harus berisi 1–500 baris.');
        return $this->atomic(function () use ($library_id,$entity,$rows,$actor) {
            foreach ($rows as $index=>$row) {
                try { $this->save_row($library_id,$entity,0,$row,$actor); }
                catch (Throwable $e) { throw new RuntimeException('Baris '.($index+2).': '.$e->getMessage()); }
            }
            return count($rows);
        });
    }

    public function archive($library_id, $entity, $id, $actor)
    {
        return $this->atomic(function () use ($library_id,$entity,$id,$actor) {
            $library_id=$this->scope($library_id); $row=$this->find($library_id,$entity,$id,true);
            if (!$row) throw new RuntimeException('Data tidak ditemukan.');
            if(in_array($entity,['members','books'],true)&&$this->db->table_exists('network_reservations')&&$this->db->where('library_id',$library_id)->where($entity==='members'?'member_id':'book_id',(int)$id)->where('status','waiting')->where('expires_at >',date('Y-m-d H:i:s'))->count_all_results('network_reservations'))throw new RuntimeException('Selesaikan atau batalkan reservasi aktif sebelum mengarsipkan data ini.');
            if ($entity === 'items' && $row['status'] === 'loaned') throw new RuntimeException('Eksemplar masih dipinjam.');
            if ($entity === 'members' && $this->db->where('library_id',$library_id)->where('member_id',(int)$id)->where('returned_at IS NULL',null,false)->count_all_results('network_loans')) throw new RuntimeException('Anggota masih memiliki pinjaman aktif.');
            if ($entity === 'books' && $this->db->where('library_id',$library_id)->where('book_id',(int)$id)->where('deleted_at IS NULL',null,false)->count_all_results('network_items')) throw new RuntimeException('Arsipkan semua eksemplar dahulu.');
            $this->must($this->db->where('library_id',$library_id)->where('id',(int)$id)->update($this->table($entity),['deleted_at'=>date('Y-m-d H:i:s')]));
            $this->audit($library_id,$actor,$entity.'.archived',(int)$id);
        });
    }

    public function settings($library_id)
    {
        $row = $this->db->where('library_id',$this->scope($library_id))->get('network_settings')->row_array();
        return $row ?: ['loan_days'=>7,'max_loans'=>3,'max_renewals'=>1];
    }

    public function save_settings($library_id,array $input,$actor)
    {
        $data=['library_id'=>$this->scope($library_id),'updated_at'=>date('Y-m-d H:i:s')];
        foreach (['loan_days'=>[1,90],'max_loans'=>[1,100],'max_renewals'=>[0,10]] as $key=>$bounds) {
            if (!isset($input[$key]) || !is_scalar($input[$key]) || !ctype_digit((string)$input[$key]) || (int)$input[$key]<$bounds[0] || (int)$input[$key]>$bounds[1]) throw new RuntimeException('Pengaturan peminjaman tidak valid.');
            $data[$key]=(int)$input[$key];
        }
        $this->atomic(function () use ($data,$actor) { $this->must($this->db->replace('network_settings',$data)); $this->audit($data['library_id'],$actor,'settings.updated'); });
    }

    public function issue($library_id,$member_no,$barcode,$actor)
    {
        $library_id=$this->scope($library_id);
        return $this->atomic(function () use ($library_id,$member_no,$barcode,$actor) {
            $settings=$this->settings($library_id);
            $member=$this->db->query('SELECT * FROM network_members WHERE library_id=? AND member_no=? AND deleted_at IS NULL FOR UPDATE',[$library_id,trim((string)$member_no)])->row_array();
            if (!$member || $member['status'] !== 'active') throw new RuntimeException('Anggota aktif tidak ditemukan di perpustakaan ini.');
            if($this->db->table_exists('network_membership_terms')){
                $term=$this->db->where('library_id',$library_id)->where('member_id',$member['id'])->get('network_membership_terms')->row_array();
                if($term&&$term['expires_on']<date('Y-m-d'))throw new RuntimeException('Keanggotaan sudah berakhir. Proses perpanjangan terlebih dahulu.');
            }
            // Current locking read: a settings SELECT may already have opened a
            // REPEATABLE READ snapshot before another operator committed a loan.
            $active=$this->db->query('SELECT id FROM network_loans WHERE library_id=? AND member_id=? AND returned_at IS NULL FOR UPDATE',[$library_id,(int)$member['id']])->num_rows();
            if ($active >= (int)$settings['max_loans']) throw new RuntimeException('Batas pinjaman aktif anggota telah tercapai.');
            $item=$this->db->query('SELECT * FROM network_items WHERE library_id=? AND barcode=? AND deleted_at IS NULL FOR UPDATE',[$library_id,trim((string)$barcode)])->row_array();
            if (!$item || $item['status'] !== 'available') throw new RuntimeException('Barcode tidak ditemukan atau eksemplar tidak tersedia di perpustakaan ini.');
            $book=$this->find($library_id,'books',$item['book_id'],true);
            if (!$book || $book['format'] !== 'physical') throw new RuntimeException('Katalog fisik tidak tersedia.');
            $reservation=null;
            if($this->db->table_exists('network_reservations')){
                $reservation=$this->db->query("SELECT * FROM network_reservations WHERE library_id=? AND book_id=? AND status='waiting' AND expires_at>NOW() ORDER BY id LIMIT 1 FOR UPDATE",[$library_id,(int)$book['id']])->row_array();
                if($reservation&&(int)$reservation['member_id']!==(int)$member['id'])throw new RuntimeException('Judul ini memiliki antrean reservasi aktif. Layani pemesan pertama atau batalkan reservasinya terlebih dahulu.');
            }
            $this->must($this->db->where('library_id',$library_id)->where('id',(int)$item['id'])->where('status','available')->update('network_items',['status'=>'loaned','updated_at'=>date('Y-m-d H:i:s')]));
            if ($this->db->affected_rows() !== 1) throw new RuntimeException('Eksemplar baru saja berubah. Muat ulang dan coba lagi.');
            $this->must($this->db->insert('network_loans',['library_id'=>$library_id,'member_id'=>(int)$member['id'],'item_id'=>(int)$item['id'],'loaned_at'=>date('Y-m-d H:i:s'),'due_at'=>date('Y-m-d',strtotime('+'.(int)$settings['loan_days'].' days')).' 23:59:59','created_by'=>(int)$actor]));
            $id=(int)$this->db->insert_id();
            if($reservation){$this->must($this->db->where('id',$reservation['id'])->where('library_id',$library_id)->update('network_reservations',['status'=>'fulfilled','active_slot'=>null]));$this->audit($library_id,$actor,'reservation.fulfilled',(int)$reservation['id']);}
            $this->audit($library_id,$actor,'loan.issued',$id); return $id;
        });
    }

    public function loan_action($library_id,$id,$action,$actor,$note='')
    {
        $library_id=$this->scope($library_id);
        if (!in_array($action,['return','renew'],true)) throw new RuntimeException('Tindakan tidak valid.');
        return $this->atomic(function () use ($library_id,$id,$action,$actor,$note) {
            $loan=$this->db->query('SELECT * FROM network_loans WHERE library_id=? AND id=? FOR UPDATE',[$library_id,(int)$id])->row_array();
            if (!$loan || $loan['returned_at']) throw new RuntimeException('Pinjaman aktif tidak ditemukan dalam perpustakaan ini.');
            $item=$this->find($library_id,'items',$loan['item_id'],true);
            if (!$item || $item['status'] !== 'loaned') throw new RuntimeException('Status eksemplar tidak sesuai; hubungi pengelola.');
            if ($action === 'return') {
                if (mb_strlen((string)$note)>500) throw new RuntimeException('Catatan maksimal 500 karakter.');
                $data=['returned_at'=>date('Y-m-d H:i:s'),'returned_by'=>(int)$actor,'return_note'=>trim((string)$note)?:null];
                $this->must($this->db->where('library_id',$library_id)->where('id',(int)$item['id'])->update('network_items',['status'=>'available','updated_at'=>date('Y-m-d H:i:s')]));
            } else {
                $settings=$this->settings($library_id);
                if ((int)$loan['renewal_count'] >= (int)$settings['max_renewals']) throw new RuntimeException('Batas perpanjangan sudah tercapai.');
                if ($loan['due_at'] < date('Y-m-d H:i:s')) throw new RuntimeException('Pinjaman terlambat harus diselesaikan terlebih dahulu.');
                $member=$this->find($library_id,'members',$loan['member_id']);
                if (!$member || $member['status'] !== 'active') throw new RuntimeException('Anggota tidak aktif.');
                if($this->db->table_exists('network_membership_terms')){
                    $term=$this->db->where('library_id',$library_id)->where('member_id',$member['id'])->get('network_membership_terms')->row_array();
                    if($term&&$term['expires_on']<date('Y-m-d'))throw new RuntimeException('Perpanjang keanggotaan sebelum memperpanjang pinjaman.');
                }
                $data=['due_at'=>date('Y-m-d H:i:s',strtotime($loan['due_at'].' +'.(int)$settings['loan_days'].' days')),'renewal_count'=>(int)$loan['renewal_count']+1];
            }
            $this->must($this->db->where('library_id',$library_id)->where('id',(int)$id)->update('network_loans',$data));
            $this->audit($library_id,$actor,'loan.'.$action,(int)$id);
        });
    }

    public function loans($library_id,$state='active',$limit=25,$offset=0)
    {
        $library_id=$this->scope($library_id);
        $build=function()use($library_id,$state){
            $this->db->from('network_loans n')->join('network_members m','m.id=n.member_id AND m.library_id=n.library_id')->join('network_items i','i.id=n.item_id AND i.library_id=n.library_id')->join('network_books b','b.id=i.book_id AND b.library_id=i.library_id')->where('n.library_id',$library_id);
            if ($state === 'returned') $this->db->where('n.returned_at IS NOT NULL',null,false);
            elseif ($state !== 'all') { $this->db->where('n.returned_at IS NULL',null,false); if ($state === 'overdue') $this->db->where('n.due_at <',date('Y-m-d H:i:s')); }
        };
        $build();$total=(int)$this->db->count_all_results();$build();
        return ['total'=>$total,'rows'=>$this->db->select('n.*,m.full_name,m.member_no,i.barcode,b.title')->order_by('n.id','DESC')->limit(max(1,min(1000,(int)$limit)),max(0,(int)$offset))->get()->result_array()];
    }

    public function purposes()
    {
        return ['Membaca di tempat','Meminjam / mengembalikan buku','Mencari referensi','Mengikuti kegiatan literasi','Kunjungan rombongan','Layanan digital'];
    }

    public function visit($library_id,array $input,$actor)
    {
        $library_id=$this->scope($library_id);
        if (!in_array($input['purpose']??'', $this->purposes(),true) || !in_array($input['channel']??'',['offline','online'],true)) throw new RuntimeException('Tujuan atau kanal kunjungan tidak valid.');
        $member_no=trim((string)($input['member_no']??'')); $name=trim((string)($input['guest_name']??''));
        $member=$member_no!=='' ? $this->db->where('library_id',$library_id)->where('member_no',$member_no)->where('deleted_at IS NULL',null,false)->get('network_members')->row_array() : null;
        if ($member_no!=='' && !$member) throw new RuntimeException('Nomor anggota tidak ditemukan di perpustakaan ini.');
        if (!$member && ($name==='' || mb_strlen($name)>180)) throw new RuntimeException('Isi nama pengunjung (maksimal 180 karakter) atau nomor anggota lokal.');
        return $this->atomic(function()use($library_id,$input,$actor,$member,$name){
            $this->must($this->db->insert('network_visits',['library_id'=>$library_id,'member_id'=>$member?(int)$member['id']:null,'guest_name'=>$member?null:$name,'purpose'=>$input['purpose'],'channel'=>$input['channel'],'visited_at'=>date('Y-m-d H:i:s'),'created_by'=>(int)$actor]));
            $id=(int)$this->db->insert_id();$this->audit($library_id,$actor,'visit.created',$id);return $id;
        });
    }

    public function visits($library_id,$limit=25,$offset=0)
    {
        $library_id=$this->scope($library_id);
        $total=(int)$this->db->where('library_id',$library_id)->count_all_results('network_visits');
        $rows=$this->db->select('v.*,COALESCE(m.full_name,v.guest_name) AS visitor_name,m.member_no',false)->from('network_visits v')->join('network_members m','m.id=v.member_id AND m.library_id=v.library_id','left')->where('v.library_id',$library_id)->order_by('v.id','DESC')->limit(max(1,min(1000,(int)$limit)),max(0,(int)$offset))->get()->result_array();
        return ['total'=>$total,'rows'=>$rows];
    }

    public function report($library_id,$year)
    {
        $library_id=$this->scope($library_id); $year=(int)$year;
        if ($year<2000 || $year>2100) throw new RuntimeException('Tahun laporan tidak valid.');
        $stats=[];
        foreach (['books','items','members'] as $entity) $stats[$entity]=$this->listing($library_id,$entity,'',1)['total'];
        $stats['active_loans']=$this->loans($library_id,'active',1)['total'];
        $stats['overdue']=$this->loans($library_id,'overdue',1)['total'];
        $stats['physical']=(int)$this->db->where('library_id',$library_id)->where('format','physical')->where('deleted_at IS NULL',null,false)->count_all_results('network_books');
        $stats['digital']=$stats['books']-$stats['physical'];
        $start=$year.'-01-01 00:00:00'; $end=($year+1).'-01-01 00:00:00';
        $months=[];for($i=1;$i<=12;$i++)$months[$i]=['month'=>sprintf('%04d-%02d',$year,$i),'loans'=>0,'returns'=>0,'offline'=>0,'online'=>0];
        foreach (['loans'=>'loaned_at','returns'=>'returned_at'] as $label=>$field) {
            $rows=$this->db->query('SELECT MONTH('.$field.') month,COUNT(*) total FROM network_loans WHERE library_id=? AND '.$field.'>=? AND '.$field.'<? GROUP BY MONTH('.$field.')',[$library_id,$start,$end])->result_array();
            foreach($rows as $row)$months[(int)$row['month']][$label]=(int)$row['total'];
        }
        $rows=$this->db->query('SELECT MONTH(visited_at) month,channel,COUNT(*) total FROM network_visits WHERE library_id=? AND visited_at>=? AND visited_at<? GROUP BY MONTH(visited_at),channel',[$library_id,$start,$end])->result_array();
        foreach($rows as $row)$months[(int)$row['month']][$row['channel']]=(int)$row['total'];
        $purposes=$this->db->query('SELECT purpose,COUNT(*) total FROM network_visits WHERE library_id=? AND visited_at>=? AND visited_at<? GROUP BY purpose ORDER BY purpose',[$library_id,$start,$end])->result_array();
        return ['stats'=>$stats,'months'=>array_values($months),'purposes'=>$purposes];
    }

    public function save_profile($library_id,array $data,$actor)
    {
        $library_id=$this->scope($library_id); $clean=[];
        foreach (['address'=>2000,'manager_name'=>180,'phone'=>40,'email'=>180,'website'=>180,'opening_hours'=>180,'facilities'=>2000,'description'=>5000] as $key=>$max) {
            if (!is_scalar($data[$key]??'')) throw new RuntimeException('Data profil tidak valid.');
            $value=trim((string)($data[$key]??''));if(mb_strlen($value)>$max)throw new RuntimeException('Isian profil terlalu panjang.');$clean[$key]=$value?:null;
        }
        if (!$clean['manager_name'] || !preg_match('/^(?:\+?62|0)8[0-9]{7,12}$/D',preg_replace('/[\s().-]/','',(string)$clean['phone']))) throw new RuntimeException('Contact person dan nomor HP aktif wajib diisi (08… atau +628…).');
        if ($clean['email'] && !filter_var($clean['email'],FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Email tidak valid.');
        if ($clean['website'] && (!filter_var($clean['website'],FILTER_VALIDATE_URL) || strtolower((string)parse_url($clean['website'],PHP_URL_SCHEME))!=='https')) throw new RuntimeException('Website harus URL HTTPS.');
        // Identity, NPSN, region, status, verification and owner are central-controlled.
        $clean['updated_by']=(int)$actor;
        $this->atomic(function()use($library_id,$clean,$actor){$this->must($this->db->where('id',$library_id)->update('libraries',$clean));$this->audit($library_id,$actor,'profile.updated',$library_id);});
    }

    public function admins($library_id)
    {
        return $this->db->select('u.id,u.username,u.full_name,u.status,u.force_password_change,u.last_login_at')->from('auth_user u')->join('auth_user_role ur','ur.user_id=u.id')->join('auth_role r','r.id=ur.role_id')->where('u.library_id',$this->scope($library_id))->where('r.code',self::ROLE)->order_by('u.id','ASC')->get()->result_array();
    }

    public function create_admin($library_id,array $input,$actor)
    {
        $library_id=$this->scope($library_id);$name=trim((string)($input['full_name']??''));$username=trim((string)($input['username']??''));$password=(string)($input['password']??'');
        if ($name==='' || mb_strlen($name)>180 || !preg_match('/^[a-z0-9][a-z0-9._-]{4,79}$/D',$username)) throw new RuntimeException('Nama wajib diisi dan username 5–80 karakter huruf kecil, angka, titik, strip atau underscore.');
        if (strlen($password)<10 || strlen($password)>72) throw new RuntimeException('Password awal minimal 10 dan maksimal 72 karakter.');
        return $this->atomic(function()use($library_id,$name,$username,$password,$actor){
            $role=$this->db->where('code',self::ROLE)->where('is_active',1)->get('auth_role')->row_array();if(!$role)throw new RuntimeException('Peran Admin Perpustakaan belum tersedia.');
            $this->must($this->db->insert('auth_user',['username'=>$username,'full_name'=>$name,'password_hash'=>password_hash($password,PASSWORD_BCRYPT),'library_id'=>$library_id,'source_system'=>self::SOURCE,'status'=>'active','force_password_change'=>1]));$id=(int)$this->db->insert_id();
            $this->must($this->db->insert('auth_user_role',['user_id'=>$id,'role_id'=>(int)$role['id']]));$this->audit($library_id,$actor,'admin.created',$id);return $id;
        });
    }

    public function toggle_admin($library_id,$id,$actor)
    {
        $library_id=$this->scope($library_id);
        if ((int)$id===(int)$actor) throw new RuntimeException('Anda tidak dapat menonaktifkan akun sendiri.');
        $this->atomic(function()use($library_id,$id,$actor){
            $this->db->query('SELECT id FROM libraries WHERE id=? FOR UPDATE',[$library_id]);
            $users=$this->admins($library_id);$target=null;$active=0;
            foreach($users as $user){if((int)$user['id']===(int)$id)$target=$user;if($user['status']==='active')$active++;}
            if(!$target)throw new RuntimeException('Admin tidak ditemukan dalam perpustakaan ini.');
            if($target['status']==='active'&&$active<=1)throw new RuntimeException('Harus tersisa minimal satu admin aktif.');
            $status=$target['status']==='active'?'inactive':'active';
            $this->must($this->db->where('library_id',$library_id)->where('id',(int)$id)->update('auth_user',['status'=>$status]));$this->audit($library_id,$actor,'admin.'.$status,(int)$id);
        });
    }
}
