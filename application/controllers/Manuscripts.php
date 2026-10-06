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
		$items=$this->Manuscript_model->get_admin($filters,$per,($page-1)*$per);$html=$this->render('manuscripts/index',['title'=>'Naskah Kuno','stats'=>$this->Manuscript_model->stats(),'items'=>$items,'filters'=>array_merge($filters,['per_page'=>$per]),'pagination'=>['total'=>$total,'pages'=>$pages,'page'=>$page,'offset'=>($page-1)*$per,'per_page'=>$per],'can_create'=>$this->can('manuscripts.index','create'),'can_edit'=>$this->can('manuscripts.index','edit'),'can_delete'=>$this->can('manuscripts.index','delete')],true);foreach($items as $item){$code='<code>'.html_escape($item['inventory_number']).'</code>';$badge=!empty($item['permission_file_path'])&&($item['permission_status']??'')==='verified'?'<span class="badge bg-green-lt text-green ms-2"><i class="ti ti-file-certificate me-1"></i>Izin terverifikasi</span>':'<span class="badge bg-red-lt text-red ms-2"><i class="ti ti-file-alert me-1"></i>Belum ada izin</span>';$html=str_replace($code,$code.$badge,$html);$html=str_replace(base_url('naskah-kuno/preview/'.(int)$item['id']),base_url('manuscripts/preview/'.(int)$item['id']),$html);} $this->output->set_output($html);
	}
	public function create() { $this->require_permission('manuscripts.index','create'); $html=$this->render('manuscripts/form',['title'=>'Tambah Naskah Kuno','item'=>null,'pages'=>[],'action'=>'manuscripts/store'],true); $this->output->set_output($this->decorate_permission_form($html,[])); }
	public function store()
	{
		$this->require_permission('manuscripts.index','create');
		$id = null;
		try {
			$payload = $this->input_data();
			if ($payload['inventory_number'] === '') $payload['inventory_number'] = $this->Manuscript_model->next_inventory_number();
			if ($this->Manuscript_model->inventory_exists($payload['inventory_number'])) throw new RuntimeException('Nomor inventaris sudah digunakan. Gunakan nomor lain atau kosongkan agar sistem membuat nomor otomatis.');
			$payload = array_merge($payload, $this->uploads());
			$this->validate_publication_permission($payload);
			$id=$this->Manuscript_model->create($payload,(int)$this->current_user['id']);
			$page_count = $this->upload_page_images($id);
			$this->audit_event('manuscript.create','ancient_manuscripts',$id,null,$payload + ['pages_uploaded'=>$page_count]); $this->session->set_flashdata('success','Naskah kuno berhasil disimpan.' . ($page_count ? ' ' . $page_count . ' halaman digital ditambahkan.' : '')); redirect('manuscripts');
		}
		catch(Throwable $e) { $this->session->set_flashdata('error',$e->getMessage()); redirect($id ? 'manuscripts/edit/' . (int) $id : 'manuscripts/create'); }
	}
	public function edit($id) { $this->require_permission('manuscripts.index','edit'); $item=$this->Manuscript_model->find($id); if(!$item){show_404();return;} $html=$this->render('manuscripts/form',['title'=>'Edit Naskah Kuno','item'=>$item,'pages'=>$this->Manuscript_model->get_pages((int)$id),'action'=>'manuscripts/update/'.(int)$id],true); $this->output->set_output($this->decorate_permission_form($html,$item)); }
	public function update($id)
	{
		$this->require_permission('manuscripts.index','edit'); $before=$this->Manuscript_model->find($id); if(!$before){show_404();return;}
		try {
			$payload = $this->input_data($before['inventory_number']);
			if ($this->Manuscript_model->inventory_exists($payload['inventory_number'], (int) $id)) throw new RuntimeException('Nomor inventaris sudah digunakan oleh naskah lain.');
			$payload = array_merge($payload, $this->uploads());
			$this->validate_publication_permission($payload, $before);
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

	public function permission_document($id)
	{
		$this->require_permission('manuscripts.index','view');
		$item=$this->Manuscript_model->find((int)$id);if(!$item||empty($item['permission_file_path'])){show_404();return;}
		$base=realpath(FCPATH.'storage/manuscripts');$file=realpath(FCPATH.str_replace(['/','\\'],DIRECTORY_SEPARATOR,$item['permission_file_path']));if(!$base||!$file||strpos($file,$base.DIRECTORY_SEPARATOR)!==0||!is_file($file)){show_404();return;}
		header('X-Content-Type-Options: nosniff');header('Cache-Control: private, no-store, max-age=0');header('Content-Type: '.($item['permission_mime_type']?:'application/octet-stream'));header('Content-Disposition: inline; filename="'.str_replace('"','',basename((string)$item['permission_original_name'])).'"');header('Content-Length: '.filesize($file));readfile($file);exit;
	}

	public function admin_preview($id)
	{
		$this->require_permission('manuscripts.index','view');$item=$this->Manuscript_model->find((int)$id);if(!$item){show_404();return;}$pages=$this->Manuscript_model->get_pages((int)$id);if($pages){redirect('manuscripts/read/'.(int)$id);return;}if(empty($item['preview_file_path'])){redirect('manuscripts/edit/'.(int)$id);return;}$this->serve_admin_manuscript_file($item['preview_file_path'],$item['preview_mime_type']??'application/octet-stream',$item['preview_original_name']??'preview');
	}

	public function admin_reader($id)
	{
		$this->require_permission('manuscripts.index','view');$item=$this->Manuscript_model->find((int)$id);if(!$item){show_404();return;}$pages=$this->Manuscript_model->get_pages((int)$id);if(!$pages){redirect('manuscripts/preview/'.(int)$id);return;}$html=$this->load->view('manuscripts/viewer',['title'=>$item['title'],'item'=>$item,'pages'=>$pages],true);$publicBase=base_url('naskah-kuno/page/'.(int)$id.'/');$adminBase=base_url('manuscripts/page/'.(int)$id.'/');$html=str_replace([$publicBase,str_replace('/','\\/',$publicBase)],[$adminBase,str_replace('/','\\/',$adminBase)],$html);$html=str_replace(base_url('naskah-kuno/detail/'.(int)$id),base_url('manuscripts/edit/'.(int)$id),$html);$html=str_replace('href="'.base_url('naskah-kuno').'"','href="'.base_url('manuscripts').'"',$html);$this->output->set_output($this->decorate_admin_reader($html));
	}

	public function admin_page($manuscript_id,$page_id)
	{
		$this->require_permission('manuscripts.index','view');$item=$this->Manuscript_model->find((int)$manuscript_id);$page=$this->Manuscript_model->find_page((int)$manuscript_id,(int)$page_id);if(!$item||!$page){show_404();return;}$this->serve_admin_manuscript_file($page['file_path'],$page['mime_type']??'application/octet-stream',$page['original_name']??('halaman-'.$page_id));
	}

	private function serve_admin_manuscript_file($path,$mime,$original_name)
	{
		$base=realpath(FCPATH.'storage/manuscripts');$file=realpath(FCPATH.str_replace(['/','\\'],DIRECTORY_SEPARATOR,(string)$path));if(!$base||!$file||strpos($file,$base.DIRECTORY_SEPARATOR)!==0||!is_file($file)){show_404();return;}header('X-Content-Type-Options: nosniff');header('Cache-Control: private, no-store, max-age=0');header('Content-Type: '.$mime);header('Content-Disposition: inline; filename="'.str_replace('"','',basename((string)$original_name)).'"');header('Content-Length: '.filesize($file));readfile($file);exit;
	}

	private function decorate_admin_reader($html)
	{
		$html=str_replace('<span class="zoom" id="zoom">100%</span>','<span class="zoom" id="zoom">Pas</span>',$html);$html=str_replace('Perbesar lalu gulir citra untuk melihat detail.','Gunakan ← → atau swipe untuk berpindah halaman.',$html);
		$html=str_replace('</head>','<style>.head{padding-block:9px}.shell{grid-template-columns:190px minmax(0,1fr);min-height:calc(100vh - 52px)}.rail{padding:14px 10px}.pages{max-height:calc(100vh - 145px)}.choice{padding:7px}.reader{padding:12px 16px}.tools{margin-bottom:8px}.stage{height:calc(100vh - 148px);min-height:360px;padding:16px;overflow:auto}.foot{margin-top:8px}.page-image{width:auto;max-width:none}@media(max-width:720px){.stage{height:calc(100vh - 168px);min-height:55vh}.shell{display:block}.rail{display:none}}</style></head>',$html);
		$old="touchX=0;function apply(){image.style.width=(scale*100)+'%';zoom.textContent=Math.round(scale*100)+'%'}function turn";$new="touchX=0,baseWidth=0;function fit(){if(!image.naturalWidth)return;var pad=window.innerWidth<=720?24:40,availableWidth=Math.max(220,stage.clientWidth-pad),availableHeight=Math.max(280,stage.clientHeight-pad);baseWidth=Math.min(image.naturalWidth,availableWidth,image.naturalWidth*(availableHeight/image.naturalHeight));apply()}function apply(){if(!baseWidth)return;image.style.width=Math.round(baseWidth*scale)+'px';zoom.textContent=scale===1?'Pas':Math.round(scale*100)+'%'}function turn";$html=str_replace($old,$new,$html);
		$html=str_replace("label.textContent='Halaman '+page.number;scale=1;apply();","label.textContent='Halaman '+page.number;scale=1;baseWidth=0;zoom.textContent='Pas';",$html);
		$html=str_replace("image.addEventListener('contextmenu'","image.addEventListener('load',fit);image.addEventListener('error',function(){label.textContent='Gambar gagal dimuat — coba muat ulang halaman'});window.addEventListener('resize',function(){if(scale===1)fit()});document.addEventListener('keydown',function(event){if(event.key==='ArrowLeft'&&index>0){event.preventDefault();index--;render(-1)}else if(event.key==='ArrowRight'&&index<pages.length-1){event.preventDefault();index++;render(1)}});image.addEventListener('contextmenu'",$html);return $html;
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
		$keys=['inventory_number','title','alternate_title','language','script','material','page_count','dimensions','condition_state','estimated_period','author_scribe','origin','current_location','description','collection_history','conservation_notes','digitized_at','digitized_by','digitization_notes','rights_note','owner_name','permission_reference','permission_date','permission_notes','access_level','status']; $out=[]; foreach($keys as $key)$out[$key]=$this->input->post($key,true); $out['is_featured']=$this->input->post('is_featured')?1:0;
		if (trim((string) $out['title']) === '') throw new RuntimeException('Judul naskah wajib diisi. Data metadata lain boleh dilengkapi kemudian.');
		if (trim((string) $out['inventory_number']) === '' && $inventory_fallback !== '') $out['inventory_number'] = $inventory_fallback;
		return $out;
	}
	private function uploads()
	{
		$out=[];
		if(!empty($_FILES['permission_file']['name'])){$error=(int)($_FILES['permission_file']['error']??UPLOAD_ERR_NO_FILE);if($error!==UPLOAD_ERR_OK)throw new RuntimeException('Upload surat izin gagal. Periksa ukuran berkas dan konfigurasi server.');$tmp=(string)$_FILES['permission_file']['tmp_name'];$size=(int)$_FILES['permission_file']['size'];if($size<=0||$size>20971520)throw new RuntimeException('Ukuran surat izin maksimal 20 MB.');$mime=$this->detect_upload_mime($tmp);$extensions=['application/pdf'=>'pdf','image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];if(!isset($extensions[$mime]))throw new RuntimeException('Surat izin harus berupa PDF, JPG, PNG, atau WEBP yang valid.');$dir='storage/manuscripts/permissions/'.date('Y/m');$absolute=FCPATH.str_replace('/',DIRECTORY_SEPARATOR,$dir);if(!is_dir($absolute)&&!mkdir($absolute,0775,true))throw new RuntimeException('Folder surat izin tidak bisa dibuat.');$name='izin-'.date('His').'-'.bin2hex(random_bytes(8)).'.'.$extensions[$mime];if(!move_uploaded_file($tmp,$absolute.DIRECTORY_SEPARATOR.$name))throw new RuntimeException('Surat izin gagal disimpan.');$out+=['permission_file_path'=>$dir.'/'.$name,'permission_original_name'=>substr(basename((string)$_FILES['permission_file']['name']),0,255),'permission_mime_type'=>$mime,'permission_file_size'=>$size,'permission_status'=>'verified','permission_verified_by'=>(int)$this->current_user['id'],'permission_verified_at'=>date('Y-m-d H:i:s')];}
		if(!empty($_FILES['cover']['name'])) { $dir=FCPATH.'assets/uploads/manuscripts/covers/'.date('Y/m').'/'; if(!is_dir($dir)&&!mkdir($dir,0755,true))throw new RuntimeException('Folder cover tidak bisa dibuat.'); $this->load->library('upload'); $this->upload->initialize(['upload_path'=>$dir,'allowed_types'=>'jpg|jpeg|png|webp','max_size'=>204800,'encrypt_name'=>true]); if(!$this->upload->do_upload('cover'))throw new RuntimeException(strip_tags($this->upload->display_errors('',''))); $file=$this->upload->data(); $out['cover_path']='assets/uploads/manuscripts/covers/'.date('Y/m').'/'.$file['file_name']; }
		if(empty($_FILES['preview_file']['name'])) return $out;
		if((int)($_FILES['preview_file']['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK) throw new RuntimeException('Upload preview digital gagal. Periksa ukuran berkas dan konfigurasi PHP.');
		$original=(string)$_FILES['preview_file']['name']; $tmp=(string)$_FILES['preview_file']['tmp_name']; $mime=$this->detect_upload_mime($tmp); $extensions=['application/pdf'=>'pdf','image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp']; if(!isset($extensions[$mime]))throw new RuntimeException('Preview harus berupa PDF, JPG, PNG, atau WEBP yang valid.'); $ext=$extensions[$mime]; if((int)$_FILES['preview_file']['size']>209715200)throw new RuntimeException('Ukuran preview maksimal 200 MB.');
		$dir='storage/manuscripts/'.date('Y/m'); $absolute=FCPATH.str_replace('/',DIRECTORY_SEPARATOR,$dir); if(!is_dir($absolute)&&!mkdir($absolute,0775,true))throw new RuntimeException('Folder penyimpanan digitalisasi tidak bisa dibuat.'); $name='naskah-'.date('His').'-'.bin2hex(random_bytes(5)).'.'.$ext; if(!move_uploaded_file($_FILES['preview_file']['tmp_name'],$absolute.DIRECTORY_SEPARATOR.$name))throw new RuntimeException('Berkas preview gagal disimpan.');
		return array_merge($out,['preview_file_path'=>$dir.'/'.$name,'preview_original_name'=>$original,'preview_mime_type'=>$mime]);
	}
	private function detect_upload_mime($path)
	{
		if(!is_string($path)||$path===''||!is_file($path))return '';
		$image=@getimagesize($path);if($image&&!empty($image['mime'])&&in_array($image['mime'],['image/jpeg','image/png','image/webp'],true))return $image['mime'];
		$mime='';if(function_exists('finfo_open')&&function_exists('finfo_file')&&function_exists('finfo_close')){$finfo=@finfo_open(FILEINFO_MIME_TYPE);if($finfo){$mime=(string)@finfo_file($finfo,$path);finfo_close($finfo);}}elseif(function_exists('mime_content_type')){$mime=(string)@mime_content_type($path);}
		if(in_array($mime,['application/pdf','image/jpeg','image/png','image/webp'],true))return $mime;
		$handle=@fopen($path,'rb');$header=$handle?(string)fread($handle,12):'';if($handle)fclose($handle);
		if(strncmp($header,'%PDF-',5)===0)return 'application/pdf';if(substr($header,0,3)==="\xFF\xD8\xFF")return 'image/jpeg';if(substr($header,0,8)==="\x89PNG\r\n\x1A\n")return 'image/png';if(substr($header,0,4)==='RIFF'&&substr($header,8,4)==='WEBP')return 'image/webp';return '';
	}
	private function validate_publication_permission(array $payload,array $existing=[])
	{
		if(($payload['status']??'draft')!=='published')return;$path=$payload['permission_file_path']??($existing['permission_file_path']??null);$status=$payload['permission_status']??($existing['permission_status']??'missing');if(empty($path)||$status!=='verified')throw new RuntimeException('Naskah belum dapat ditayangkan. Unggah surat izin pemilik terlebih dahulu.');
	}
	private function decorate_permission_form($html,array $item)
	{
		$e=function($value){return html_escape((string)$value);};$has=!empty($item['permission_file_path']);$document=$has?'<a class="btn btn-sm btn-outline-primary mt-2" target="_blank" href="'.base_url('manuscripts/permission/'.(int)$item['id']).'"><i class="ti ti-eye me-1"></i>Lihat surat izin</a><span class="badge bg-green-lt text-green ms-1">Terverifikasi</span>':'<div class="alert alert-warning py-2 mt-2 mb-0"><i class="ti ti-alert-triangle me-1"></i>Belum ada surat izin. Status Tayang akan ditolak.</div>';
		$card='<div class="card bg-blue-lt border-blue mb-3"><div class="card-body"><div class="d-flex align-items-center gap-2 mb-3"><span class="avatar avatar-sm bg-blue text-white"><i class="ti ti-file-certificate"></i></span><div><div class="fw-bold">Surat Izin Penayangan</div><div class="small text-secondary">Wajib tersedia sebelum naskah dapat ditayangkan.</div></div></div><div class="mb-3"><label class="form-label">Nama Pemilik / Pemberi Izin</label><input class="form-control" name="owner_name" value="'.$e($item['owner_name']??'').'" placeholder="Nama pemilik naskah"></div><div class="row"><div class="col-md-6 mb-3"><label class="form-label">Nomor Surat</label><input class="form-control" name="permission_reference" value="'.$e($item['permission_reference']??'').'" placeholder="Opsional"></div><div class="col-md-6 mb-3"><label class="form-label">Tanggal Izin</label><input type="date" class="form-control" name="permission_date" value="'.$e($item['permission_date']??'').'"></div></div><div class="mb-3"><label class="form-label">Dokumen Surat Izin</label><input class="form-control" type="file" name="permission_file" accept="application/pdf,image/jpeg,image/png,image/webp"><div class="form-hint">PDF, JPG, PNG, atau WEBP; maksimal 20 MB. Dokumen hanya dapat dibuka admin.</div>'.$document.'</div><div><label class="form-label">Catatan Izin</label><textarea class="form-control" rows="2" name="permission_notes" placeholder="Ruang lingkup izin atau ketentuan pemilik.">'.$e($item['permission_notes']??'').'</textarea></div></div></div>';
		$needle='<div class="mb-3"><label class="form-label">Akses Digital</label>';$html=str_replace($needle,$card.$needle,$html);if(!empty($item['id']))$html=str_replace(base_url('naskah-kuno/preview/'.(int)$item['id']),base_url('manuscripts/preview/'.(int)$item['id']),$html);return $html;
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
				$mime = $this->detect_upload_mime($tmp);
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
		return $stored;
	}
}
