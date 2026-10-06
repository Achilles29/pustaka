<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Worker CLI konversi donasi dokumen menjadi PDF untuk Reader. */
class Digital_donation_jobs extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		if (! is_cli()) { show_404(); exit; }
		$this->load->model('Reader_model');
	}

	public function run($limit = 10)
	{
		$lock = fopen(sys_get_temp_dir() . '/pustaka-digital-donation-converter.lock', 'c');
		if (! $lock || ! flock($lock, LOCK_EX | LOCK_NB)) { echo "Worker already running.\n"; return; }
		$limit = max(1, min(50, (int)$limit));
		$rows = $this->db->select('d.*')->from('digital_donation_submissions d')
			->join('digital_assets da', "da.source_system='digital_donation' AND CAST(da.source_id AS UNSIGNED)=d.id", 'left', false)
			->where('d.status','accepted')->where('d.catalog_book_id IS NOT NULL',null,false)->where('d.file_path IS NOT NULL',null,false)
			->where('d.file_mime_type !=','application/pdf')->where('da.id IS NULL',null,false)->order_by('d.id','ASC')->limit($limit)->get()->result_array();
		$ok=0; $failed=0;
		foreach ($rows as $donation) {
			try { $this->convert($donation); $ok++; echo 'Converted donation #' . (int)$donation['id'] . "\n"; }
			catch (Throwable $e) { $failed++; fwrite(STDERR, 'Donation #' . (int)$donation['id'] . ': ' . $e->getMessage() . "\n"); }
		}
		echo "Completed: {$ok}; failed: {$failed}.\n";
		flock($lock, LOCK_UN); fclose($lock);
	}

	private function convert(array $donation)
	{
		$convertible=['application/epub+zip','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/vnd.oasis.opendocument.text','text/plain'];
		if (! in_array(strtolower((string)$donation['file_mime_type']),$convertible,true)) throw new RuntimeException('Format tidak didukung.');
		$base=realpath(FCPATH.'storage/digital-donations');
		$source=realpath(FCPATH.str_replace(['/','\\'],DIRECTORY_SEPARATOR,$donation['file_path']));
		if(!$base||!$source||strpos($source,$base.DIRECTORY_SEPARATOR)!==0||!is_file($source))throw new RuntimeException('Berkas sumber tidak ditemukan atau tidak aman.');
		$relative_dir='storage/digital-donations/reader/'.date('Y/m');
		$target_dir=FCPATH.str_replace('/',DIRECTORY_SEPARATOR,$relative_dir);
		if(!is_dir($target_dir)&&!mkdir($target_dir,0775,true))throw new RuntimeException('Folder hasil konversi tidak dapat dibuat.');
		$profile=sys_get_temp_dir().'/lo-donation-'.bin2hex(random_bytes(8));
		if(!mkdir($profile,0700,true))throw new RuntimeException('Folder sementara tidak dapat dibuat.');
		$output=[];$code=1;
		$command='timeout 120s libreoffice --headless --nologo --nodefault --nolockcheck --nofirststartwizard '.escapeshellarg('-env:UserInstallation=file://'.$profile).' --convert-to pdf --outdir '.escapeshellarg($target_dir).' '.escapeshellarg($source).' 2>&1';
		exec($command,$output,$code); $this->remove_tree($profile);
		$pdf=$target_dir.DIRECTORY_SEPARATOR.pathinfo($source,PATHINFO_FILENAME).'.pdf';
		if($code!==0||!is_file($pdf)||filesize($pdf)<1)throw new RuntimeException('Konversi gagal. '.trim(implode(' ',$output)));
		$name=preg_replace('/\.[^.]+$/','',(string)($donation['file_original_name']?:'dokumen')).'.pdf';
		$license_urls=['cc0'=>'https://creativecommons.org/publicdomain/zero/1.0/','cc_by'=>'https://creativecommons.org/licenses/by/4.0/','cc_by_sa'=>'https://creativecommons.org/licenses/by-sa/4.0/','public_domain'=>'https://creativecommons.org/publicdomain/mark/1.0/'];
		$this->Reader_model->create_asset(['book_id'=>(int)$donation['catalog_book_id'],'source_system'=>'digital_donation','source_id'=>(string)$donation['id'],'source_path'=>$donation['file_path'],'file_original_name'=>$name,'file_path'=>$relative_dir.'/'.basename($pdf),'mime_type'=>'application/pdf','file_size'=>(int)filesize($pdf),'reader_audience'=>'internal','pdf_delivery'=>'render_locked','status'=>'draft','rights_basis'=>$donation['license_code']==='public_domain'?'public_domain':'licensed','rights_holder'=>$donation['donor_name'],'license_url'=>$license_urls[$donation['license_code']]??null,'permission_reference'=>$donation['rights_statement'],'access_notes'=>'Dikonversi ke PDF dari '.$donation['file_mime_type'].'; donasi #'.(int)$donation['id']],(int)($donation['cataloged_by']??0));
	}

	private function remove_tree($directory)
	{
		if(!is_dir($directory))return;
		$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);
		foreach($it as $item){$item->isDir()?@rmdir($item->getPathname()):@unlink($item->getPathname());}@rmdir($directory);
	}
}
