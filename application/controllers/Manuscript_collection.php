<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Manuscript_collection extends CI_Controller
{
	public function __construct() { parent::__construct(); $this->load->model('Manuscript_model'); }
	public function index()
	{
		$filters=['q'=>$this->input->get('q',true),'language'=>$this->input->get('language',true),'script'=>$this->input->get('script',true)]; $per=18; $page=max(1,(int)$this->input->get('page',true)); $total=$this->Manuscript_model->count_public($filters); $pages=max(1,(int)ceil($total/$per)); $page=min($page,$pages);
		$this->load->view('manuscripts/public_index',['title'=>'Naskah Kuno Rembang','items'=>$this->Manuscript_model->get_public($filters,$per,($page-1)*$per),'filters'=>$filters,'languages'=>$this->Manuscript_model->options('language'),'scripts'=>$this->Manuscript_model->options('script'),'pagination'=>['total'=>$total,'pages'=>$pages,'page'=>$page]]);
	}
	public function detail($id) { $item=$this->Manuscript_model->find($id,true); if(!$item||$item['access_level']==='internal'){show_404();return;} $this->load->view('manuscripts/public_detail',['title'=>$item['title'],'item'=>$item,'auth_user'=>(array)$this->session->userdata('auth_user')]); }
	public function viewer($id)
	{
		$item = $this->content_manuscript($id, 'naskah-kuno/baca/' . (int) $id);
		if (! $item) return;
		$pages = $this->Manuscript_model->get_pages((int) $id);
		if (empty($pages)) { redirect('naskah-kuno/detail/' . (int) $id); return; }
		$user = (array) $this->session->userdata('auth_user');
		$this->Manuscript_model->record_preview((int) $id, (int) ($user['id'] ?? 0), 'viewer');
		$html=$this->load->view('manuscripts/viewer', ['title'=>$item['title'], 'item'=>$item, 'pages'=>$pages],true);
		$this->output->set_output($this->decorate_fit_page_viewer($html));
	}

	public function page($manuscript_id, $page_id)
	{
		$item = $this->content_manuscript($manuscript_id, 'naskah-kuno/baca/' . (int) $manuscript_id);
		if (! $item) return;
		$page = $this->Manuscript_model->find_page((int) $manuscript_id, (int) $page_id);
		if (! $page) { show_404(); return; }
		$base = realpath(FCPATH . 'storage/manuscripts');
		$file = realpath(FCPATH . str_replace(['/','\\'], DIRECTORY_SEPARATOR, $page['file_path']));
		if (! $base || ! $file || strpos($file, $base . DIRECTORY_SEPARATOR) !== 0 || ! is_file($file)) { show_404(); return; }
		$user = (array) $this->session->userdata('auth_user');
		$this->Manuscript_model->record_preview((int) $manuscript_id, (int) ($user['id'] ?? 0), 'page_view');
		header('X-Content-Type-Options: nosniff');
		header('Cache-Control: private, no-store, max-age=0');
		header('Content-Type: ' . ($page['mime_type'] ?: 'application/octet-stream'));
		header('Content-Disposition: inline');
		header('Content-Length: ' . filesize($file));
		readfile($file); exit;
	}
	public function preview($id)
	{
		// Endpoint lama tidak lagi menyajikan PDF/citra mentah. Dengan demikian
		// tidak ada URL unduhan untuk berkas digitalisasi. Isi naskah selalu lewat
		// viewer halaman yang meminta sesi member.
		$item = $this->Manuscript_model->find((int) $id, true);
		if (! $item || $item['access_level'] === 'internal') { show_404(); return; }
		if (! empty($this->session->userdata('auth_user')) && ! empty($this->Manuscript_model->get_pages((int) $id))) {
			redirect('naskah-kuno/baca/' . (int) $id); return;
		}
		redirect('naskah-kuno/detail/' . (int) $id);
	}

	private function decorate_fit_page_viewer($html)
	{
		$html=str_replace('<span class="zoom" id="zoom">100%</span>','<span class="zoom" id="zoom">Pas</span>',$html);
		$html=str_replace('Perbesar lalu gulir citra untuk melihat detail.','Halaman ditampilkan utuh. Gunakan zoom hanya saat ingin melihat detail.',$html);
		$old="touchX=0;function apply(){image.style.width=(scale*100)+'%';zoom.textContent=Math.round(scale*100)+'%'}function turn";
		$new="touchX=0,baseWidth=0;function fit(){if(!image.naturalWidth)return;var pad=window.innerWidth<=720?26:60,availableWidth=Math.max(240,stage.clientWidth-pad),availableHeight=Math.max(320,window.innerHeight-stage.getBoundingClientRect().top-24);baseWidth=Math.min(image.naturalWidth,availableWidth,image.naturalWidth*(availableHeight/image.naturalHeight));apply()}function apply(){if(!baseWidth){zoom.textContent='Pas';return}image.style.width=Math.round(baseWidth*scale)+'px';image.style.maxWidth='none';zoom.textContent=scale===1?'Pas':Math.round(scale*100)+'%'}function turn";
		$html=str_replace($old,$new,$html);
		$html=str_replace("label.textContent='Halaman '+page.number;scale=1;apply();","label.textContent='Halaman '+page.number;scale=1;baseWidth=0;zoom.textContent='Pas';",$html);
		$html=str_replace("image.addEventListener('contextmenu'","image.addEventListener('load',fit);window.addEventListener('resize',function(){if(scale===1)fit()});document.addEventListener('keydown',function(event){if(event.key==='ArrowLeft'&&index>0){event.preventDefault();index--;render(-1)}else if(event.key==='ArrowRight'&&index<pages.length-1){event.preventDefault();index++;render(1)}});image.addEventListener('contextmenu'",$html);
		return $html;
	}

	private function content_manuscript($id, $redirect_after_login)
	{
		$item = $this->Manuscript_model->find((int) $id, true);
		if (! $item || $item['access_level'] === 'internal') { show_404(); return null; }
		$user = (array) $this->session->userdata('auth_user');
		if (empty($user['id'])) {
			$this->session->set_flashdata('redirect_after_login', $redirect_after_login);
			redirect('login'); return null;
		}
		return $item;
	}
}
