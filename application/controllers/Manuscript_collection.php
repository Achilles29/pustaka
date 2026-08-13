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
		$this->load->view('manuscripts/viewer', ['title'=>$item['title'], 'item'=>$item, 'pages'=>$pages]);
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
