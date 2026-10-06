<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/** Manual records only: no rate calculation, gateway, reminders or automatic charges. */
class Library_fines_model extends CI_Model
{
    private function must($ok){if($ok===false)throw new RuntimeException('Pencatatan gagal atau data berubah. Coba kembali.');return $ok;}
    private function atomic(callable $fn){$this->must($this->db->trans_begin());try{$r=$fn();if(!$this->db->trans_status())throw new RuntimeException('Pencatatan dibatalkan karena transaksi gagal.');$this->must($this->db->trans_commit());return $r;}catch(Throwable $e){$this->db->trans_rollback();throw $e;}}
    private function text($v,$max){if(!is_string($v)||trim($v)===''||mb_strlen($v)>$max)throw new RuntimeException('Alasan, catatan dan nomor bukti wajib diisi sesuai batas panjang.');return trim($v);}
    private function amount($value){if(!is_scalar($value)||!ctype_digit((string)$value)||strlen((string)$value)>10||(int)$value<1||(int)$value>1000000000)throw new RuntimeException('Nominal rupiah harus bilangan bulat 1–1.000.000.000, tanpa titik/koma.');return (int)$value;}
    private function scope($id){if(!ctype_digit((string)$id)||(int)$id<1)throw new RuntimeException('Pilih perpustakaan.');return (int)$id;}
    public function loan($scope,$source,$id,$local=false)
    {
        $scope=$this->scope($scope);
        if($source==='network')$sql='SELECT n.id,m.full_name borrower_label,b.title item_label FROM network_loans n JOIN network_members m ON m.id=n.member_id AND m.library_id=n.library_id JOIN network_items i ON i.id=n.item_id AND i.library_id=n.library_id JOIN network_books b ON b.id=i.book_id WHERE n.library_id=? AND n.id=?';
        elseif($source==='legacy'&&!$local)$sql='SELECT n.id,m.full_name borrower_label,b.title item_label FROM loan_transaction_items n JOIN members m ON m.id=n.member_id JOIN book_items i ON i.id=n.book_item_id JOIN books b ON b.id=i.book_id WHERE i.library_id=? AND n.id=?';
        elseif($source==='interlibrary')$sql="SELECT n.id,l.name borrower_label,n.title item_label FROM inter_library_loans n JOIN libraries l ON l.id=n.borrower_id WHERE n.owner_id=? AND n.id=? AND n.state IN ('dispatched','received','returning','returned')".($local?" AND n.source='network'":'');
        else throw new RuntimeException('Sumber pinjaman tidak diizinkan.');
        return $this->must($this->db->query($sql,[$scope,(int)$id]))->row_array();
    }
    public function listing($scope,$local=false,$all=false)
    {
        $this->db->where('f.library_id',$this->scope($scope));if($local)$this->db->where('f.source !=','legacy')->where("(f.source!='interlibrary' OR EXISTS(SELECT 1 FROM inter_library_loans x WHERE x.id=f.loan_id AND x.source='network'))",null,false);
        $this->db->select("f.*,COALESCE((SELECT SUM(CASE WHEN e.kind='reversal' THEN -CAST(e.amount AS SIGNED) ELSE e.amount END) FROM library_fine_events e WHERE e.fine_id=f.id),0) applied",false)->from('library_fines f')->order_by('f.id','DESC');if(!$all)$this->db->limit(200);return $this->db->get()->result_array();
    }
    public function find($scope,$id,$local=false,$lock=false)
    {
        $r=$this->must($this->db->query('SELECT * FROM library_fines WHERE library_id=? AND id=?'.($lock?' FOR UPDATE':''),[$this->scope($scope),(int)$id]))->row_array();
        if($r&&$local&&($r['source']==='legacy'||($r['source']==='interlibrary'&&!$this->loan($scope,$r['source'],$r['loan_id'],true))))return null;return $r;
    }
    public function events($scope,$id,$local=false){if(!$this->find($scope,$id,$local))throw new RuntimeException('Catatan tidak ditemukan.');return $this->db->where('fine_id',(int)$id)->order_by('id')->get('library_fine_events')->result_array();}
    public function create($scope,$source,$loan_id,$amount,$reason,$actor,$local=false)
    {
        $scope=$this->scope($scope);$amount=$this->amount($amount);$reason=$this->text($reason,1000);
        return $this->atomic(function()use($scope,$source,$loan_id,$amount,$reason,$actor,$local){
            $loan=$this->loan($scope,$source,$loan_id,$local);if(!$loan)throw new RuntimeException('Pinjaman tidak ditemukan dalam perpustakaan ini. Gunakan ID transaksi, bukan ID buku.');
            $this->must($this->db->insert('library_fines',['library_id'=>$scope,'source'=>$source,'loan_id'=>(int)$loan_id,'borrower_label'=>mb_substr($loan['borrower_label'],0,255),'item_label'=>mb_substr($loan['item_label'],0,255),'amount'=>$amount,'reason'=>$reason,'created_by'=>(int)$actor]));return (int)$this->db->insert_id();
        });
    }
    public function act($scope,$id,$action,$amount,$reference,$note,$event_id,$actor,$local=false)
    {
        $note=$this->text($note,1000);if(!in_array($action,['payment','waiver','reversal','void'],true))throw new RuntimeException('Tindakan tidak dikenal.');
        return $this->atomic(function()use($scope,$id,$action,$amount,$reference,$note,$event_id,$actor,$local){
            $fine=$this->find($scope,$id,$local,true);if(!$fine||$fine['state']!=='open')throw new RuntimeException('Catatan aktif tidak ditemukan pada perpustakaan ini.');
            $rows=$this->must($this->db->query('SELECT * FROM library_fine_events WHERE fine_id=? FOR UPDATE',[(int)$id]))->result_array();$applied=0;foreach($rows as $r)$applied+=($r['kind']==='reversal'?-1:1)*(int)$r['amount'];
            if($action==='void'){
                if($applied!==0)throw new RuntimeException('Koreksi catatan pelunasan/pembebasan terlebih dahulu sebelum membatalkan denda.');
                $this->must($this->db->where('id',(int)$id)->update('library_fines',['state'=>'void','void_note'=>$note,'void_by'=>(int)$actor,'void_at'=>date('Y-m-d H:i:s')]));return;
            }
            $reference=$this->text($reference,180);$reverses=null;
            if($action==='reversal'){
                $indexed=array_column($rows,null,'id');$original=$indexed[(int)$event_id]??null;
                if(!$original||$original['kind']==='reversal'||in_array((int)$event_id,array_map('intval',array_column($rows,'reverses_id')),true))throw new RuntimeException('Bukti koreksi tidak sesuai atau sudah dikoreksi.');
                $amount=(int)$original['amount'];$reverses=(int)$original['id'];
            }else{$amount=$this->amount($amount);if($amount>(int)$fine['amount']-$applied)throw new RuntimeException('Nominal melebihi sisa denda.');}
            $this->must($this->db->insert('library_fine_events',['fine_id'=>(int)$id,'kind'=>$action,'amount'=>$amount,'reference'=>$reference,'note'=>$note,'reverses_id'=>$reverses,'actor_id'=>(int)$actor]));
        });
    }
}
