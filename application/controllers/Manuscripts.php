<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Manuscripts extends MY_Controller
{
	public function __construct() { parent::__construct(); $this->load->model('Manuscript_model'); }

	public function index()
	{
		$this->require_permission('manuscripts.index','view');
		$filters=['q'=>$this->input->get('q',true),'status'=>$this->input->get('status',true),'access_level'=>$this->input->get('access_level',true)];
		$per=(int)$this->input->get('per_page',true); $per=in_array($per,[10,25,50,100],true)?$per:25; $page=max(1,(int)$this->input->get('page',true)); $total=$this->Manuscript_model->count_admin($filters); $pages=max(1,(int)ceil($total/$per)); $page=min($page,$pages);
		$this->render('manuscripts/index',['title'=>'Naskah Kuno','stats'=>$this->Manuscript_model->stats(),'items'=>$this->Manuscript_model->get_admin($filters,$per,($page-1)*$per),'filters'=>array_merge($filters,['per_page'=>$per]),'pagination'=>['total'=>$total,'pages'=>$pages,'page'=>$page,'offset'=>($page-1)*$per,'per_page'=>$per],'can_create'=>$this->can('manuscripts.index','create'),'can_edit'=>$this->can('manuscripts.index','edit'),'can_delete'=>$this->can('manuscripts.index','delete')]);
	}
	public function create() { $this->require_permission('manuscripts.index','create'); $this->render('manuscripts/form',['title'=>'Tambah Naskah Kuno','item'=>null,'pages'=>[],'action'=>'manuscripts/store']); }
	public function store()
	{
		$this->require_permission('manuscripts.index','create');
		$id = null;
		try {
			$payload = $this->input_data();
			if ($payload['inventory_number'] === '') $payload['inventory_number'] = $this->Manuscript_model->next_inventory_number();
			if ($this->Manuscript_model->inventory_exists($payload['inventory_number'])) throw new RuntimeException('Nomor inventaris sudah digunakan. Gunakan nomor lain atau kosongkan agar sistem membuat nomor otomatis.');
			$payload = array_merge($payload, $this->uploads());
			$id=$this->Manuscript_model->create($payload,(int)$this->current_user['id']);
			$page_count = $this->upload_page_images($id);
			$this->audit_event('manuscript.create','ancient_manuscripts',$id,null,$payload + ['pages_uploaded'=>$page_count]); $this->session->set_flashdata('success','Naskah kuno berhasil disimpan.' . ($page_count ? ' ' . $page_count . ' halaman digital ditambahkan.' : '')); redirect('manuscripts');
		}
		catch(Throwable $e) { $this->session->set_flashdata('error',$e->getMessage()); redirect($id ? 'manuscripts/edit/' . (int) $id : 'manuscripts/create'); }
	}
	public function edit($id) { $this->require_permission('manuscripts.index','edit'); $item=$this->Manuscript_model->find($id); if(!$item){show_404();return;} $this->render('manuscripts/form',['title'=>'Edit Naskah Kuno','item'=>$item,'pages'=>$this->Manuscript_model->get_pages((int)$id),'action'=>'manuscripts/update/'.(int)$id]); }
	public function update($id)
	{
		$this->require_permission('manuscripts.index','edit'); $before=$this->Manuscript_model->find($id); if(!$before){show_404();return;}
		try {
			$payload = $this->input_data($before['inventory_number']);
			if ($this->Manuscript_model->inventory_exists($payload['inventory_number'], (int) $id)) throw new RuntimeException('Nomor inventaris sudah digunakan oleh naskah lain.');
			$payload = array_merge($payload, $this->uploads());
			$this->Manuscript_model->update($id,$payload,(int)$this->current_user['id']);
			$page_count = $this->upload_page_images((int)$id);
			$this->audit_event('manuscript.update','ancient_manuscripts',$id,$before,$payload + ['pages_uploaded'=>$page_count]); $this->session->set_flashdata('success','Data naskah kuno diperbarui.' . ($page_count ? ' ' . $page_count . ' halaman digital ditambahkan.' : '')); redirect('manuscripts');
		}
		catch(Throwable $e) { $this->session->set_flashdata('error',$e->getMessage()); redirect('manuscripts/edit/'.(int)$id); }
	}
	public function delete($id)
	{
		$this->require_permission('manuscripts.index','delete'); $before=$this->Manuscript_model->find($id); if(!$before){show_404();return;} $this->Manuscript_model->delete($id); $this->audit_event('manuscript.delete','ancient_manuscripts',$id,$before,[]); $this->session->set_flashdata('success','Data naskah dihapus dari katalog. Berkas digital tidak dihapus otomatis.'); redirect('manuscripts');
	}

	public function delete_page($manuscript_id, $page_id)
	{
		$this->require_permission('manuscripts.index','edit');
		$item = $this->Manuscript_model->find((int) $manuscript_id);
		$page = $this->Manuscript_model->find_page((int) $manuscript_id, (int) $page_id);
		if (! $item || ! $page) { show_404(); return; }
		$this->Manuscript_model->delete_page((int) $manuscript_id, (int) $page_id);
		$this->audit_event('manuscript.page_remove','ancient_manuscript_pages',(int)$page_id,$page,[]);
		$this->session->set_flashdata('success','Halaman digital dihapus dari viewer. Berkas sumber tetap disimpan di arsip aman.');
		redirect('manuscripts/edit/' . (int) $manuscript_id);
	}

	/** Ubah master PDF menjadi citra halaman viewer; PDF mentah tetap di storage tertutup. */
	public function convert_pdf($id)
	{
		$this->require_permission('manuscripts.index','edit');
		$item = $this->Manuscript_model->find((int) $id);
		if (! $item) { show_404(); return; }
		try {
			if (empty($item['preview_file_path']) || strtolower((string) $item['preview_mime_type']) !== 'application/pdf') throw new RuntimeException('Tidak ada master PDF yang dapat diubah menjadi halaman viewer.');
			if (! empty($this->Manuscript_model->get_pages((int) $id))) throw new RuntimeException('Halaman viewer sudah tersedia. Hapus halaman terlebih dahulu bila ingin membuat ulang dari PDF.');
			$base = realpath(FCPATH . 'storage/manuscripts');
			$source = realpath(FCPATH . str_replace(['/','\\'], DIRECTORY_SEPARATOR, $item['preview_file_path']));
			if (! $base || ! $source || strpos($source, $base . DIRECTORY_SEPARATOR) !== 0 || ! is_file($source)) throw new RuntimeException('Master PDF tidak ditemukan di storage aman.');
			$dir = FCPATH . 'storage/manuscripts/' . (int) $id . '/pages';
			if (! is_dir($dir) && ! mkdir($dir, 0775, true)) throw new RuntimeException('Folder halaman viewer tidak bisa dibuat.');
			$prefix = $dir . '/converted';
			set_time_limit(300);
			exec('/usr/bin/pdftoppm -jpeg -jpegopt quality=78 -scale-to 1600 -f 1 -l 300 ' . escapeshellarg($source) . ' ' . escapeshellarg($prefix) . ' 2>&1', $output, $status);
			$files = glob($prefix . '-*.jpg');
			natsort($files);
			if ($status !== 0 || empty($files)) throw new RuntimeException('PDF tidak dapat diproses menjadi halaman viewer. ' . trim(implode(' ', array_slice($output ?? [], -2))));
			$this->db->trans_begin();
			try {
				$page_no = 1;
				foreach ($files as $file) {
					$image = @getimagesize($file);
					if (! $image) throw new RuntimeException('Salah satu citra hasil konversi PDF tidak valid.');
					$this->Manuscript_model->add_page(['manuscript_id'=>(int)$id,'page_number'=>$page_no++,'file_path'=>'storage/manuscripts/'.(int)$id.'/pages/'.basename($file),'original_name'=>basename($file),'mime_type'=>'image/jpeg','file_size'=>(int)filesize($file),'width'=>(int)$image[0],'height'=>(int)$image[1],'created_by'=>(int)($this->current_user['id']??0)?:null]);
				}
				$this->db->trans_commit();
			} catch (Throwable $e) { $this->db->trans_rollback(); throw $e; }
			$this->audit_event('manuscript.pdf_to_pages','ancient_manuscripts',(int)$id,null,['pages'=>count($files)]);
			$this->session->set_flashdata('success',count($files).' halaman viewer berhasil dibuat dari master PDF. File PDF tetap tersimpan aman dan tidak disajikan ke user.');
		} catch (Throwable $e) { $this->session->set_flashdata('error',$e->getMessage()); }
		redirect('manuscripts/edit/'.(int)$id);
	}

	private function input_data($inventory_fallback = '')
	{
		$keys=['inventory_number','title','alternate_title','language','script','material','page_count','dimensions','condition_state','estimated_period','author_scribe','origin','current_location','description','collection_history','conservation_notes','digitized_at','digitized_by','digitization_notes','rights_note','access_level','status']; $out=[]; foreach($keys as $key)$out[$key]=$this->input->post($key,true); $out['is_featured']=$this->input->post('is_featured')?1:0;
		if (trim((string) $out['title']) === '') throw new RuntimeException('Judul naskah wajib diisi. Data metadata lain boleh dilengkapi kemudian.');
		if (trim((string) $out['inventory_number']) === '' && $inventory_fallback !== '') $out['inventory_number'] = $inventory_fallback;
		return $out;
	}
	private function uploads()
	{
		$out=[];
		if(!empty($_FILES['cover']['name'])) { $dir=FCPATH.'assets/uploads/manuscripts/covers/'.date('Y/m').'/'; if(!is_dir($dir)&&!mkdir($dir,0755,true))throw new RuntimeException('Folder cover tidak bisa dibuat.'); $this->load->library('upload'); $this->upload->initialize(['upload_path'=>$dir,'allowed_types'=>'jpg|jpeg|png|webp','max_size'=>204800,'encrypt_name'=>true]); if(!$this->upload->do_upload('cover'))throw new RuntimeException(strip_tags($this->upload->display_errors('',''))); $file=$this->upload->data(); $out['cover_path']='assets/uploads/manuscripts/covers/'.date('Y/m').'/'.$file['file_name']; }
		if(empty($_FILES['preview_file']['name'])) return $out;
		if((int)($_FILES['preview_file']['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK) throw new RuntimeException('Upload preview digital gagal. Periksa ukuran berkas dan konfigurasi PHP.');
		$original=(string)$_FILES['preview_file']['name']; $ext=strtolower(pathinfo($original,PATHINFO_EXTENSION)); $allowed=['pdf'=>'application/pdf','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','webp'=>'image/webp']; if(!isset($allowed[$ext]))throw new RuntimeException('Preview harus berupa PDF, JPG, PNG, atau WEBP.'); if((int)$_FILES['preview_file']['size']>209715200)throw new RuntimeException('Ukuran preview maksimal 200 MB.');
		$dir='storage/manuscripts/'.date('Y/m'); $absolute=FCPATH.str_replace('/',DIRECTORY_SEPARATOR,$dir); if(!is_dir($absolute)&&!mkdir($absolute,0775,true))throw new RuntimeException('Folder penyimpanan digitalisasi tidak bisa dibuat.'); $name='naskah-'.date('His').'-'.bin2hex(random_bytes(5)).'.'.$ext; if(!move_uploaded_file($_FILES['preview_file']['tmp_name'],$absolute.DIRECTORY_SEPARATOR.$name))throw new RuntimeException('Berkas preview gagal disimpan.');
		return array_merge($out,['preview_file_path'=>$dir.'/'.$name,'preview_original_name'=>$original,'preview_mime_type'=>$allowed[$ext]]);
	}

	/** Simpan citra setiap halaman di storage tertutup; tampilkan hanya melalui viewer. */
	private function upload_page_images($manuscript_id)
	{
		if (empty($_FILES['page_images']) || ! is_array($_FILES['page_images']['name'] ?? null)) return 0;
		$names = $_FILES['page_images']['name'];
		$stored = 0;
		$total_size = 0;
		$page_no = $this->Manuscript_model->next_page_number((int) $manuscript_id);
		$dir = 'storage/manuscripts/' . (int) $manuscript_id . '/pages';
		$absolute = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $dir);
		if (! is_dir($absolute) && ! mkdir($absolute, 0775, true)) throw new RuntimeException('Folder halaman digital tidak bisa dibuat.');
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		try {
			foreach ($names as $index => $original) {
				if (trim((string) $original) === '') continue;
				$error = (int) ($_FILES['page_images']['error'][$index] ?? UPLOAD_ERR_NO_FILE);
				if ($error !== UPLOAD_ERR_OK) throw new RuntimeException('Upload halaman digital gagal pada berkas ' . ($index + 1) . '.');
				$size = (int) ($_FILES['page_images']['size'][$index] ?? 0);
				if ($size <= 0 || $size > 209715200) throw new RuntimeException('Ukuran setiap halaman digital maksimal 200 MB.');
				$total_size += $size;
				if ($total_size > 209715200) throw new RuntimeException('Total unggahan halaman digital dalam satu kali simpan maksimal 200 MB. Unggah dalam beberapa batch.');
				$tmp = (string) ($_FILES['page_images']['tmp_name'][$index] ?? '');
				$image = @getimagesize($tmp);
				$mime = $finfo ? finfo_file($finfo, $tmp) : ($image['mime'] ?? '');
				$extension_map = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
				if (! $image || ! isset($extension_map[$mime])) throw new RuntimeException('Halaman digital harus berupa citra JPG, PNG, atau WEBP yang valid.');
				$name = sprintf('page-%04d-%s.%s', $page_no, bin2hex(random_bytes(5)), $extension_map[$mime]);
				if (! move_uploaded_file($tmp, $absolute . DIRECTORY_SEPARATOR . $name)) throw new RuntimeException('Halaman digital gagal disimpan.');
				$this->Manuscript_model->add_page([
					'manuscript_id'=>(int)$manuscript_id, 'page_number'=>$page_no++, 'file_path'=>$dir . '/' . $name,
					'original_name'=>substr(basename((string)$original),0,255), 'mime_type'=>$mime, 'file_size'=>$size,
					'width'=>(int)$image[0], 'height'=>(int)$image[1], 'created_by'=>(int)($this->current_user['id'] ?? 0) ?: null,
				]);
				$stored++;
			}
		} finally {
			if ($finfo) finfo_close($finfo);
		}
		return $stored;
	}
}
