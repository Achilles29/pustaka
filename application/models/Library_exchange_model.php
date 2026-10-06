<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Library_exchange_model extends CI_Model
{
    public function ready()
    {
        if(!$this->db->table_exists('inter_library_loans'))return false;
        $r=$this->db->query("SELECT COUNT(*) n FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA=DATABASE() AND LEFT(TRIGGER_NAME,12)='pustaka_ill_'")->row_array();return (int)($r['n']??0)===8;
    }
    private function must($result){if($result===false)throw new RuntimeException('Penyimpanan gagal atau data berubah. Tidak ada perubahan yang diterapkan.');return $result;}
    private function atomic(callable $fn){$this->must($this->db->trans_begin());try{$r=$fn();if(!$this->db->trans_status())throw new RuntimeException('Transaksi gagal. Coba kembali.');$this->must($this->db->trans_commit());return $r;}catch(Throwable $e){$this->db->trans_rollback();throw $e;}}
    private function scope($id){if(!ctype_digit((string)$id)||(int)$id<1)throw new RuntimeException('Cakupan perpustakaan tidak valid.');$l=$this->db->where('id',(int)$id)->where('status','active')->get('libraries')->row_array();if(!$l)throw new RuntimeException('Perpustakaan tidak aktif.');return (int)$id;}
    private function source($source){if(!in_array($source,['legacy','network'],true))throw new RuntimeException('Sumber koleksi tidak valid.');return $source;}
    private function note($note){if(!is_string($note)||trim($note)===''||mb_strlen($note)>1000)throw new RuntimeException('Catatan wajib diisi, maksimal 1000 karakter.');return trim($note);}
    public function labels(){return ['requested'=>'Menunggu pemilik','approved'=>'Disetujui / ditahan','dispatched'=>'Dikirim pemilik','received'=>'Diterima peminjam','returning'=>'Dalam pengembalian','returned'=>'Kembali ke pemilik','rejected'=>'Ditolak','cancelled'=>'Dibatalkan'];}
    public function actions(array $r,$scope,$local=false)
    {
        $owner=(int)$r['owner_id']===(int)$scope&&(!$local||$r['source']==='network');$borrower=(int)$r['borrower_id']===(int)$scope;
        $out=[];
        if($r['state']==='requested'){if($owner)$out=['approve'=>'Setujui permintaan','reject'=>'Tolak permintaan'];if($borrower)$out=['cancel'=>'Batalkan permintaan'];}
        if($r['state']==='approved'){if($owner)$out=['dispatch'=>'Catat pengiriman','cancel'=>'Batalkan sebelum dikirim'];elseif($borrower)$out=['cancel'=>'Batalkan sebelum dikirim'];}
        if($r['state']==='dispatched'&&$borrower)$out=['receive'=>'Konfirmasi buku diterima'];
        if($r['state']==='received'&&$borrower)$out=['return'=>'Catat pengiriman kembali'];
        if($r['state']==='returning'&&$owner)$out=['complete'=>'Konfirmasi kembali ke pemilik'];return $out;
    }
    public function listing($scope=null,$local=false,$all=false)
    {
        if($scope!==null){$scope=$this->scope($scope);$this->db->group_start()->where('x.borrower_id',$scope)->or_group_start()->where('x.owner_id',$scope);if($local)$this->db->where('x.source','network');$this->db->group_end()->group_end();}
        $this->db->select('x.*,o.name owner_name,b.name borrower_name')->from('inter_library_loans x')->join('libraries o','o.id=x.owner_id')->join('libraries b','b.id=x.borrower_id')->order_by('x.id','DESC');if(!$all)$this->db->limit(200);return $this->db->get()->result_array();
    }
    public function find($scope,$id,$local=false)
    {
        $scope=$this->scope($scope);$r=$this->db->select('x.*,o.name owner_name,b.name borrower_name')->from('inter_library_loans x')->join('libraries o','o.id=x.owner_id')->join('libraries b','b.id=x.borrower_id')->where('x.id',(int)$id)->get()->row_array();
        if(!$r||((int)$r['borrower_id']!==$scope&&((int)$r['owner_id']!==$scope||($local&&$r['source']!=='network'))))return null;return $r;
    }
    public function events($scope,$id,$local=false){if(!$this->find($scope,$id,$local))throw new RuntimeException('Transaksi tidak ditemukan.');return $this->db->select('e.*,l.name library_name')->from('inter_library_events e')->join('libraries l','l.id=e.library_id')->where('loan_id',(int)$id)->order_by('e.id')->get()->result_array();}
    private function event($id,$scope,$actor,$action,$note){$this->must($this->db->insert('inter_library_events',['loan_id'=>(int)$id,'library_id'=>(int)$scope,'actor_id'=>(int)$actor,'action'=>$action,'note'=>$note]));}
    private function physical_sql($source)
    {
        return $source==='network'?"b.format='physical'":"LOWER(TRIM(COALESCE(i.collection_type,''))) NOT IN ('ebook','e-book','e book','buku digital','digital','audiobook') AND LOWER(TRIM(COALESCE(i.media_name,''))) NOT IN ('digital','pdf','epub','ebook','e-book','audiobook') AND i.is_public=1 AND i.is_loanable=1";
    }
    public function discover($borrower,$owner,$source,$q='')
    {
        $this->scope($borrower);$this->scope($owner);$this->source($source);if((int)$owner===(int)$borrower)return [];
        $items=$source==='network'?'network_items':'book_items';$books=$source==='network'?'network_books':'books';$q=mb_substr(trim((string)$q),0,180);
        $this->db->select('i.id,i.barcode,b.title')->from($items.' i')->join($books.' b','b.id=i.book_id')->where('i.library_id',(int)$owner)->where('i.deleted_at IS NULL',null,false)->where('b.deleted_at IS NULL',null,false)->where('b.status','published')->where('i.status','available')->where($this->physical_sql($source),null,false);
        if($q!=='')$this->db->group_start()->like('b.title',$q)->or_like('i.barcode',$q)->group_end();return $this->db->order_by('b.title')->limit(50)->get()->result_array();
    }
    private function item($source,$id,$lock=false)
    {
        $this->source($source);$items=$source==='network'?'network_items':'book_items';return $this->must($this->db->query("SELECT * FROM $items WHERE id=?".($lock?' FOR UPDATE':''),[(int)$id]))->row_array();
    }
    private function eligible($source,array $item)
    {
        if($item['deleted_at']||$item['status']!=='available')throw new RuntimeException('Eksemplar tidak tersedia.');
        $books=$source==='network'?'network_books':'books';$b=$this->must($this->db->query("SELECT * FROM $books WHERE id=? FOR UPDATE",[(int)$item['book_id']]))->row_array();
        if(!$b||$b['deleted_at']||$b['status']!=='published')throw new RuntimeException('Hanya koleksi fisik yang dipublikasikan dapat diminta.');
        if($source==='network'){
            if($b['format']!=='physical')throw new RuntimeException('Hanya koleksi fisik dapat dipinjam antarlembaga.');
            $active=$this->db->query('SELECT id FROM network_loans WHERE library_id=? AND item_id=? AND returned_at IS NULL FOR UPDATE',[$item['library_id'],$item['id']])->row_array();
            $reserved=$this->db->query("SELECT id FROM network_reservations WHERE library_id=? AND book_id=? AND status='waiting' AND expires_at>NOW() LIMIT 1 FOR UPDATE",[$item['library_id'],$item['book_id']])->row_array();
        }else{
            $digital=['ebook','e-book','e book','buku digital','digital','audiobook','pdf','epub'];
            if(!$item['is_public']||!$item['is_loanable']||in_array(strtolower(trim((string)$item['collection_type'])),$digital,true)||in_array(strtolower(trim((string)$item['media_name'])),$digital,true))throw new RuntimeException('Eksemplar bukan koleksi fisik publik yang dapat dipinjam.');
            $active=$this->db->query("SELECT id FROM loan_transaction_items WHERE book_item_id=? AND actual_return_at IS NULL AND local_return_at IS NULL AND UPPER(loan_status)='LOAN' LIMIT 1 FOR UPDATE",[$item['id']])->row_array();
            $reserved=$this->db->query("SELECT id FROM book_requests WHERE book_item_id=? AND status='approved' LIMIT 1 FOR UPDATE",[$item['id']])->row_array();
        }
        if($active||$reserved)throw new RuntimeException('Selesaikan pinjaman atau antrean reservasi lokal terlebih dahulu.');return $b;
    }
    public function request($borrower,$owner,$source,$item_id,$due,$note,$actor)
    {
        if(!$this->ready())throw new RuntimeException('Pengaman inventaris belum siap.');$borrower=$this->scope($borrower);$owner=$this->scope($owner);$this->source($source);$note=$this->note($note);
        if($owner===$borrower)throw new RuntimeException('Gunakan peminjaman biasa untuk perpustakaan sendiri.');
        $date=DateTimeImmutable::createFromFormat('!Y-m-d',(string)$due);if(!$date||$date->format('Y-m-d')!==$due||$due<date('Y-m-d')||$due>date('Y-m-d',strtotime('+90 days')))throw new RuntimeException('Jatuh tempo harus hari ini sampai 90 hari mendatang.');
        return $this->atomic(function()use($borrower,$owner,$source,$item_id,$due,$note,$actor){
            $i=$this->item($source,$item_id,true);if(!$i||(int)$i['library_id']!==$owner)throw new RuntimeException('Eksemplar tidak ditemukan pada pemilik yang dipilih.');$b=$this->eligible($source,$i);
            $duplicate=$this->db->query("SELECT id FROM inter_library_loans WHERE borrower_id=? AND source=? AND item_id=? AND state IN ('requested','approved','dispatched','received','returning') LIMIT 1 FOR UPDATE",[$borrower,$source,(int)$item_id])->row_array();if($duplicate)throw new RuntimeException('Permintaan aktif untuk eksemplar ini sudah ada.');
            $this->must($this->db->insert('inter_library_loans',['owner_id'=>$owner,'borrower_id'=>$borrower,'source'=>$source,'item_id'=>(int)$i['id'],'book_id'=>(int)$b['id'],'barcode'=>(string)$i['barcode'],'title'=>$b['title'],'due_on'=>$due,'requested_by'=>(int)$actor]));$id=(int)$this->db->insert_id();$this->event($id,$borrower,$actor,'request',$note);return $id;
        });
    }
    public function act($scope,$id,$action,$note,$condition,$actor,$local=false)
    {
        if(!$this->ready())throw new RuntimeException('Pengaman inventaris belum siap.');$scope=$this->scope($scope);$note=$this->note($note);$seed=$this->find($scope,$id,$local);if(!$seed)throw new RuntimeException('Transaksi tidak ditemukan dalam perpustakaan ini.');
        return $this->atomic(function()use($scope,$id,$action,$note,$condition,$actor,$local,$seed){
            // Lock item first, matching regular issue/update and database hold guards.
            $i=$this->item($seed['source'],$seed['item_id'],true);$r=$this->must($this->db->query('SELECT * FROM inter_library_loans WHERE id=? FOR UPDATE',[(int)$id]))->row_array();
            if(!$r||!isset($this->actions($r,$scope,$local)[$action]))throw new RuntimeException('Tindakan tidak sesuai pihak atau status transaksi.');
            if(!$i||(int)$i['library_id']!==(int)$r['owner_id'])throw new RuntimeException('Inventaris pemilik perlu diperiksa.');
            $state=['approve'=>'approved','reject'=>'rejected','cancel'=>'cancelled','dispatch'=>'dispatched','receive'=>'received','return'=>'returning','complete'=>'returned'][$action];$data=['state'=>$state,'updated_at'=>date('Y-m-d H:i:s')];$status=null;
            if($action==='approve'){
                if((int)$r['requested_by']===(int)$actor)throw new RuntimeException('Persetujuan pemilik harus dilakukan akun petugas berbeda dari pembuat permintaan.');
                if($r['due_on']<date('Y-m-d'))throw new RuntimeException('Jatuh tempo permintaan sudah lewat. Tolak dan ajukan kembali.');
                $this->scope($r['borrower_id']);$this->eligible($r['source'],$i);$data+=['active_slot'=>1,'approved_by'=>(int)$actor];$status='loaned';
            }
            if($action==='cancel'||$action==='reject'){$data['active_slot']=null;if($r['active_slot'])$status='available';}
            if($action==='complete'){
                if(!in_array($condition,['good','damaged'],true))throw new RuntimeException('Pilih kondisi buku saat kembali.');
                $data+=['active_slot'=>null,'returned_at'=>date('Y-m-d H:i:s'),'return_condition'=>$condition];$status=$condition==='good'?'available':'damaged';
            }
            $this->must($this->db->where('id',(int)$id)->update('inter_library_loans',$data));
            if($status!==null){$table=$r['source']==='network'?'network_items':'book_items';$update=['status'=>$status,'updated_at'=>date('Y-m-d H:i:s')];if($r['source']==='legacy')$update['status_label']=$status==='loaned'?'Dipinjam antarlembaga':($status==='damaged'?'Rusak':'Tersedia');$this->must($this->db->where('id',$i['id'])->where('library_id',$r['owner_id'])->update($table,$update));}
            $this->event($id,$scope,$actor,$action,$note);return $state;
        });
    }
}
