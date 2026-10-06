<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Public_catalog extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Catalog_model');
		$this->load->model('Member_model');
		$this->load->model('Loan_model');
	}

	public function index()
	{
		$filters = [
			'q' => $this->input->get('q', true),
			'content_category_id' => $this->input->get('content_category_id', true),
			'content_classification_id' => $this->input->get('content_classification_id', true),
			'collection_type' => $this->input->get('collection_type', true),
			'category' => $this->input->get('category', true),
			'media' => $this->input->get('media', true),
			'rule' => $this->input->get('rule', true),
			'location_library' => $this->input->get('location_library', true),
			'publish_year' => $this->input->get('publish_year', true),
			'availability' => $this->input->get('availability', true),
		];
		$per_page = (int) $this->input->get('per_page', true);
		$per_page = in_array($per_page, [12, 24, 48], true) ? $per_page : 12;
		$page = max(1, (int) $this->input->get('page', true));
		$total_rows = $this->Catalog_model->count_public_books($filters);
		$total_pages = max(1, (int) ceil($total_rows / $per_page));
		$page = min($page, $total_pages);
		$offset = ($page - 1) * $per_page;

		$this->load->view('public_catalog/index', [
			'title' => 'Katalog Publik - Pustaka Digital Rembang',
			'filters' => [
				'q' => $filters['q'],
				'content_category_id' => $filters['content_category_id'],
				'content_classification_id' => $filters['content_classification_id'],
				'collection_type' => $filters['collection_type'],
				'category' => $filters['category'],
				'media' => $filters['media'],
				'rule' => $filters['rule'],
				'location_library' => $filters['location_library'],
				'publish_year' => $filters['publish_year'],
				'availability' => $filters['availability'],
				'per_page' => $per_page,
				'page' => $page,
			],
			'books' => $this->Catalog_model->get_public_books($filters, $per_page, $offset),
			'filter_options' => $this->Catalog_model->public_filter_options($filters),
			'current_member' => $this->current_member(),
			'pagination' => [
				'total_rows' => $total_rows,
				'total_pages' => $total_pages,
				'page' => $page,
				'per_page' => $per_page,
				'offset' => $offset,
			],
		]);
	}

	public function detail($id)
	{
		$book = $this->Catalog_model->get_public_book((int) $id);
		if (! $book) {
			show_404();
			return;
		}

		if((string)$this->input->get('qr',true)==='print'){$this->render_qr_label($book);return;}
		$selected_item=null;$selected_id=(int)$this->input->get('item',true);if($selected_id>0){$candidate=$this->Catalog_model->get_book_item($selected_id,(int)$id);if($candidate&&(int)($candidate['is_public']??0)===1)$selected_item=$candidate;}
		$html=$this->load->view('public_catalog/detail', [
			'title' => $book['title'] . ' - Katalog Publik',
			'book' => $book,
			'authors' => $this->Catalog_model->get_book_authors((int) $id),
			'subjects' => $this->Catalog_model->get_book_subjects((int) $id),
			'items' => $this->Catalog_model->get_public_book_items((int) $id, 80),
			'digital_assets' => $this->Catalog_model->get_public_digital_assets((int) $id),
			'current_member' => $this->current_member(),
			'loan_settings' => $this->Loan_model->get_settings(),
		],true);
		$this->output->set_output($this->decorate_public_qr($html,$book,$selected_item));
	}

	public function item_detail($item_id)
	{
		$item=$this->Catalog_model->get_book_item((int)$item_id);if(!$item||(int)($item['is_public']??0)!==1){show_404();return;}$book=$this->Catalog_model->get_public_book((int)$item['book_id']);if(!$book){show_404();return;}redirect('katalog/detail/'.(int)$book['id'].'?item='.(int)$item['id'].'#identitas-eksemplar');
	}

	public function request($id)
	{
		try {
			$member = $this->current_member();
			if (! $member) throw new RuntimeException('Silakan masuk sebagai member untuk mengajukan peminjaman buku fisik.');
			if (empty($this->Loan_model->get_settings()['is_loan_enabled'])) throw new RuntimeException('Layanan peminjaman fisik sedang ditutup.');
			$result = $this->Catalog_model->create_book_request((int) $id, [
				'requester_name' => $this->input->post('requester_name', true),
				'requester_email' => $this->input->post('requester_email', true),
				'requester_phone' => $this->input->post('requester_phone', true),
				'message' => $this->input->post('message', true),
			], $member);
			$this->session->set_flashdata('success', 'Request buku berhasil dikirim. Kode: ' . $result['code']);
		} catch (Throwable $e) {
			$this->session->set_flashdata('error', $e->getMessage());
		}

		redirect('katalog/detail/' . (int) $id . '#request-buku');
	}

	private function decorate_public_qr($html,array $book,array $selected_item=null)
	{
		$url=base_url('katalog/detail/'.(int)$book['id']);$print=$url.'?qr=print';
		$card='<div class="public-catalog-qr" style="display:flex;align-items:center;gap:14px;margin:18px 0;padding:14px;border:1px solid #d7e5f2;border-radius:16px;background:#fff"><div id="public-book-qr" data-url="'.html_escape($url).'" style="flex:none;padding:6px;border:1px solid #e2e8f0;border-radius:10px"></div><div><div class="section-kicker">QR Buku</div><strong>Bagikan katalog ini</strong><p style="margin:4px 0 9px;color:#64748b;font-size:.78rem">Scan untuk kembali ke detail, ketersediaan, dan akses digital buku.</p><a class="btn btn-outline-primary btn-sm" target="_blank" href="'.html_escape($print).'"><i class="ti ti-printer me-1"></i>Cetak QR</a></div></div>';
		if($selected_item){$identity=$selected_item['barcode']?:($selected_item['inventory_number']?:$selected_item['item_code']);$selected='<div id="identitas-eksemplar" style="margin:18px 0;padding:14px;border:1px solid #9cc7ef;border-radius:16px;background:#edf7ff"><div class="section-kicker">Eksemplar hasil scan</div><strong style="display:block;margin:3px 0">'.html_escape($identity).'</strong><div style="color:#52677e;font-size:.78rem">No. induk: '.html_escape($selected_item['inventory_number']?:'-').' · No. panggil: '.html_escape($selected_item['call_number']?:'-').'<br>Lokasi: '.html_escape($selected_item['location_room_name']?:($selected_item['location_name']?:$selected_item['location_library_name'])).' · Status: '.html_escape($selected_item['status_label']?:$selected_item['status']).'</div></div>';$card=$selected.$card;}
		$html=preg_replace('/<div class="public-detail-grid">/',$card.'<div class="public-detail-grid">',$html,1);
		$script='<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script><script>(function(){var el=document.getElementById("public-book-qr");if(el&&window.QRCode)new QRCode(el,{text:el.dataset.url,width:116,height:116,colorDark:"#062d62",colorLight:"#ffffff",correctLevel:QRCode.CorrectLevel.M});})();</script>';
		return str_replace('</body>',$script.'</body>',$html);
	}

	private function render_qr_label(array $book)
	{
		$url=base_url('katalog/detail/'.(int)$book['id']);$title=html_escape($book['title']??'Buku');$author=html_escape($book['statement_responsibility']??'');$call=html_escape($book['call_number']??'');
		$html='<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>QR '. $title .'</title><style>@page{size:A4;margin:12mm}*{box-sizing:border-box}body{margin:0;color:#062d62;font-family:Arial,sans-serif}.tools{text-align:center;margin-bottom:18px}.tools button{padding:10px 18px;border:0;border-radius:8px;background:#075da7;color:#fff;font-weight:700;cursor:pointer}.label{width:90mm;min-height:118mm;margin:auto;padding:12mm;border:1px dashed #8aa6bf;border-radius:5mm;text-align:center}.brand{font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase}.label h1{margin:12px 0 5px;font-size:21px;line-height:1.2}.meta{margin:0 0 14px;color:#52677e;font-size:12px}.qr{display:inline-block;padding:10px;border:1px solid #d5e1ec;border-radius:12px;background:#fff}.hint{margin:12px 0 4px;font-size:12px;font-weight:700}.url{overflow-wrap:anywhere;color:#64748b;font-size:9px}.call{display:inline-block;margin-top:10px;padding:5px 9px;border-radius:99px;background:#eaf3fb;font-family:monospace;font-size:11px}@media print{.tools{display:none}.label{border-color:#9aa9b7}}</style></head><body><div class="tools"><button onclick="window.print()">Cetak / Simpan PDF</button></div><main class="label"><div class="brand">Pustaka Digital Rembang</div><h1>'.$title.'</h1><p class="meta">'.$author.'</p><div id="print-book-qr" class="qr"></div><p class="hint">Scan untuk membuka katalog buku</p><div class="url">'.html_escape($url).'</div>'.($call!==''?'<div class="call">No. Panggil: '.$call.'</div>':'').'</main><script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script><script>new QRCode(document.getElementById("print-book-qr"),{text:'.json_encode($url).',width:260,height:260,colorDark:"#062d62",colorLight:"#ffffff",correctLevel:QRCode.CorrectLevel.H});</script></body></html>';
		$this->output->set_output($html);
	}

	private function current_member()
	{
		$user = (array) $this->session->userdata('auth_user');
		if (empty($user['id'])) {
			return null;
		}

		return $this->Member_model->get_member_by_auth_user_id((int) $user['id']);
	}
}
