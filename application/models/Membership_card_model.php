<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Membership_card_model extends CI_Model
{
	private $defaults = [
		'primary' => '#062D62', 'secondary' => '#087D82', 'accent' => '#F6C85F',
		'surface' => 'grid', 'photo_position' => 'left', 'corner_style' => 'rounded',
		'org_label' => 'PEMERINTAH KABUPATEN REMBANG', 'card_title' => 'Pustaka Digital Rembang',
		'footer_label' => 'KARTU ANGGOTA DIGITAL', 'tagline' => 'Merawat Ingatan, Membuka Pengetahuan.',
		'show_brand' => true, 'show_photo' => true, 'show_qr' => true, 'show_status' => true, 'show_expiry' => true, 'show_member_type' => true,
		'background_id' => null, 'background_path' => null,
		'layout' => [], 'custom_objects' => [
			['id' => 'heritage_line', 'type' => 'line', 'text' => '', 'color' => '#F6C85F', 'fill' => '#F6C85F', 'font_size' => 14, 'opacity' => 55, 'x' => 5, 'y' => 77, 'w' => 90, 'h' => .45],
		],
	];

	/* Coordinates are stored as percentages of the card (100 x 100).  This
	 * keeps a design usable on the digital card, gallery and print layout. */
	private $default_layout = [
		'brand' => ['x' => 6, 'y' => 8, 'w' => 56, 'h' => 14],
		'status' => ['x' => 77, 'y' => 9, 'w' => 17, 'h' => 9],
		'photo' => ['x' => 6, 'y' => 29, 'w' => 17, 'h' => 37],
		'member_label' => ['x' => 27, 'y' => 29, 'w' => 52, 'h' => 5],
		'member_name' => ['x' => 27, 'y' => 34, 'w' => 56, 'h' => 22],
		'member_number' => ['x' => 27, 'y' => 58, 'w' => 54, 'h' => 5],
		'member_type' => ['x' => 27, 'y' => 64, 'w' => 38, 'h' => 5],
		'expiry' => ['x' => 6, 'y' => 82, 'w' => 27, 'h' => 11],
		'footer' => ['x' => 42, 'y' => 82, 'w' => 30, 'h' => 11],
		'tagline' => ['x' => 42, 'y' => 92, 'w' => 38, 'h' => 4],
		'qr' => ['x' => 86, 'y' => 79, 'w' => 9, 'h' => 14],
	];

	private $default_back_layout = [
		'brand' => ['x' => 6, 'y' => 8, 'w' => 56, 'h' => 14],
		'title' => ['x' => 8, 'y' => 34, 'w' => 62, 'h' => 16],
		'message' => ['x' => 8, 'y' => 53, 'w' => 61, 'h' => 14],
		'serial' => ['x' => 8, 'y' => 82, 'w' => 35, 'h' => 8],
		'footer' => ['x' => 50, 'y' => 82, 'w' => 30, 'h' => 8],
		'qr' => ['x' => 84, 'y' => 76, 'w' => 10, 'h' => 16],
	];

	private $default_back = [
		'primary' => '#062D62', 'secondary' => '#087D82', 'accent' => '#F6C85F',
		'surface' => 'grid', 'corner_style' => 'rounded',
		'org_label' => 'PEMERINTAH KABUPATEN REMBANG', 'card_title' => 'Pustaka Digital Rembang',
		'tagline' => 'Kartu ini adalah identitas layanan Pustaka Digital Rembang. Pindai QR untuk memverifikasi keaslian kartu.',
		'footer_label' => 'pustaka.rembangkab.go.id',
		'show_brand' => true, 'show_qr' => true, 'show_serial' => true,
		'background_id' => null, 'background_path' => null,
		'layout' => [], 'custom_objects' => [
			['id' => 'back_line', 'type' => 'line', 'text' => '', 'color' => '#F6C85F', 'fill' => '#F6C85F', 'font_size' => 14, 'opacity' => 55, 'x' => 6, 'y' => 75, 'w' => 88, 'h' => .45],
		],
	];

	public function get_design()
	{
		if (! $this->db->table_exists('membership_card_settings')) {
			$config = $this->defaults;
			$config['back'] = $this->normalise_back([]);
			return $config;
		}
		$row = $this->db->where('id', 1)->get('membership_card_settings')->row_array();
		$data = json_decode((string) ($row['config_json'] ?? ''), true);
		return $this->normalise(is_array($data) ? $data : []);
	}

	public function save_design(array $input, $updated_by = null)
	{
		$config = $this->normalise($input);
		$this->db->replace('membership_card_settings', [
			'id' => 1,
			'config_json' => json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
			'updated_by' => $updated_by ? (int) $updated_by : null,
			'updated_at' => date('Y-m-d H:i:s'),
		]);
		return $config;
	}

	public function backgrounds($includeInactive = false)
	{
		if (! $this->db->table_exists('membership_card_backgrounds')) return [];
		if (! $includeInactive) $this->db->where('is_active', 1);
		return $this->db->order_by('created_at', 'DESC')->get('membership_card_backgrounds')->result_array();
	}

	public function add_background(array $file, $label, $userId = null)
	{
		if (! $this->db->table_exists('membership_card_backgrounds')) throw new RuntimeException('Galeri background belum tersedia. Jalankan pembaruan database kartu anggota.');
		if (empty($file['tmp_name']) || ! is_uploaded_file($file['tmp_name'])) throw new RuntimeException('Pilih berkas gambar background terlebih dahulu.');
		if ((int) ($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) throw new RuntimeException('Upload background gagal. Kode upload: ' . (int) $file['error']);
		if ((int) ($file['size'] ?? 0) < 1 || (int) $file['size'] > 8 * 1024 * 1024) throw new RuntimeException('Ukuran background maksimal 8 MB.');
		$info=@getimagesize($file['tmp_name']);$mime=(string) ($info['mime'] ?? '');$map=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
		if (! isset($map[$mime])) throw new RuntimeException('Background harus berupa gambar JPG, PNG, atau WebP yang valid.');
		if ((int) ($info[0] ?? 0) < 480 || (int) ($info[1] ?? 0) < 300) throw new RuntimeException('Ukuran gambar minimal 480 × 300 piksel agar hasil kartu tetap tajam.');
		$relative='assets/uploads/membership_cards/backgrounds/'.date('Y/m').'/';$dir=FCPATH.$relative;
		if (! is_dir($dir) && ! mkdir($dir, 0755, true)) throw new RuntimeException('Folder background kartu tidak dapat dibuat.');
		$name=bin2hex(random_bytes(16)).'.'.$map[$mime];
		if (! move_uploaded_file($file['tmp_name'], $dir.$name)) throw new RuntimeException('Server tidak dapat menyimpan background. Periksa permission folder upload.');
		$this->db->insert('membership_card_backgrounds',['label'=>mb_substr(trim(strip_tags((string)$label)) ?: 'Background kartu',0,100),'file_path'=>$relative.$name,'mime_type'=>$mime,'file_size'=>(int)$file['size'],'uploaded_by'=>$userId ? (int)$userId : null]);
		return (int) $this->db->insert_id();
	}

	/** Gambar/logo yang dapat ditaruh sebagai objek terpisah pada kanvas kartu. */
	public function object_assets($includeInactive = false)
	{
		if (! $this->db->table_exists('membership_card_design_assets')) return [];
		$this->db->where('asset_type', 'OBJECT');
		if (! $includeInactive) $this->db->where('is_active', 1);
		return $this->db->order_by('created_at', 'DESC')->get('membership_card_design_assets')->result_array();
	}

	public function add_object_asset(array $file, $label, $userId = null)
	{
		if (! $this->db->table_exists('membership_card_design_assets')) throw new RuntimeException('Galeri objek kartu belum tersedia. Jalankan pembaruan database kartu anggota.');
		if (empty($file['tmp_name']) || ! is_uploaded_file($file['tmp_name'])) throw new RuntimeException('Pilih berkas gambar atau logo terlebih dahulu.');
		if ((int) ($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) throw new RuntimeException('Upload objek gagal. Kode upload: ' . (int) $file['error']);
		if ((int) ($file['size'] ?? 0) < 1 || (int) $file['size'] > 4 * 1024 * 1024) throw new RuntimeException('Ukuran gambar atau logo maksimal 4 MB.');
		$info = @getimagesize($file['tmp_name']);
		$mime = (string) ($info['mime'] ?? '');
		$map = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
		if (! isset($map[$mime])) throw new RuntimeException('Objek harus berupa gambar JPG, PNG, atau WebP yang valid.');
		if ((int) ($info[0] ?? 0) < 24 || (int) ($info[1] ?? 0) < 24) throw new RuntimeException('Ukuran gambar minimal 24 × 24 piksel.');
		$relative = 'assets/uploads/membership_cards/objects/' . date('Y/m') . '/';
		$dir = FCPATH . $relative;
		if (! is_dir($dir) && ! mkdir($dir, 0755, true)) throw new RuntimeException('Folder objek kartu tidak dapat dibuat.');
		$name = bin2hex(random_bytes(16)) . '.' . $map[$mime];
		if (! move_uploaded_file($file['tmp_name'], $dir . $name)) throw new RuntimeException('Server tidak dapat menyimpan objek. Periksa permission folder upload.');
		$this->db->insert('membership_card_design_assets', [
			'asset_type' => 'OBJECT',
			'label' => mb_substr(trim(strip_tags((string) $label)) ?: 'Logo / gambar kartu', 0, 100),
			'file_path' => $relative . $name,
			'mime_type' => $mime,
			'file_size' => (int) $file['size'],
			'uploaded_by' => $userId ? (int) $userId : null,
		]);
		return $this->db->where('id', (int) $this->db->insert_id())->get('membership_card_design_assets')->row_array();
	}

	public function reset_layout($updatedBy = null)
	{
		$config=$this->get_design();$config['layout']=$this->default_layout;$config['custom_objects']=$this->defaults['custom_objects'];$config['back']=array_merge($this->default_back,['layout'=>$this->default_back_layout]);
		$this->db->replace('membership_card_settings',['id'=>1,'config_json'=>json_encode($config,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),'updated_by'=>$updatedBy ? (int)$updatedBy : null,'updated_at'=>date('Y-m-d H:i:s')]);
		return $config;
	}

	private function normalise(array $input)
	{
		$config = $this->defaults;
		foreach (['primary', 'secondary', 'accent'] as $key) {
			$value = strtoupper(trim((string) ($input[$key] ?? '')));
			if (preg_match('/^#[0-9A-F]{6}$/', $value)) $config[$key] = $value;
		}
		foreach (['surface' => ['aurora', 'grid', 'solid'], 'photo_position' => ['left', 'right'], 'corner_style' => ['rounded', 'soft', 'square']] as $key => $allowed) {
			if (in_array(($input[$key] ?? ''), $allowed, true)) $config[$key] = $input[$key];
		}
		foreach (['org_label' => 90, 'card_title' => 70, 'footer_label' => 70, 'tagline' => 120] as $key => $length) {
			$value = trim(strip_tags((string) ($input[$key] ?? '')));
			if ($value !== '') $config[$key] = mb_substr($value, 0, $length);
		}
		foreach (['show_brand', 'show_photo', 'show_qr', 'show_status', 'show_expiry', 'show_member_type'] as $key) {
			if (array_key_exists($key, $input)) $config[$key] = ! empty($input[$key]);
		}
		$backgroundId=(int) ($input['background_id'] ?? 0);
		if ($backgroundId > 0 && $this->db->table_exists('membership_card_backgrounds')) {
			$background=$this->db->where('id',$backgroundId)->where('is_active',1)->get('membership_card_backgrounds')->row_array();
			if ($background) {$config['background_id']=(int)$background['id'];$config['background_path']=$background['file_path'];}
		}
		$layout = $this->decode_editor_value($input['layout_json'] ?? ($input['layout'] ?? []));
		$config['layout'] = $this->normalise_layout($layout);
		if (array_key_exists('custom_objects_json', $input) || array_key_exists('custom_objects', $input)) {
			$custom = $this->decode_editor_value($input['custom_objects_json'] ?? $input['custom_objects']);
			$config['custom_objects'] = $this->normalise_custom_objects($custom);
		}
		$back = $this->decode_editor_value($input['back_json'] ?? ($input['back'] ?? []));
		$config['back'] = $this->normalise_back($back);
		return $config;
	}

	private function normalise_back(array $input)
	{
		$config = $this->default_back;
		foreach (['primary', 'secondary', 'accent'] as $key) {
			$value = strtoupper(trim((string) ($input[$key] ?? '')));
			if (preg_match('/^#[0-9A-F]{6}$/', $value)) $config[$key] = $value;
		}
		foreach (['surface' => ['aurora', 'grid', 'solid'], 'corner_style' => ['rounded', 'soft', 'square']] as $key => $allowed) {
			if (in_array(($input[$key] ?? ''), $allowed, true)) $config[$key] = $input[$key];
		}
		foreach (['org_label' => 90, 'card_title' => 70, 'footer_label' => 70, 'tagline' => 220] as $key => $length) {
			$value = trim(strip_tags((string) ($input[$key] ?? '')));
			if ($value !== '') $config[$key] = mb_substr($value, 0, $length);
		}
		foreach (['show_brand', 'show_qr', 'show_serial'] as $key) if (array_key_exists($key, $input)) $config[$key] = ! empty($input[$key]);
		$backgroundId = (int) ($input['background_id'] ?? 0);
		if ($backgroundId > 0 && $this->db->table_exists('membership_card_backgrounds')) {
			$background = $this->db->where('id', $backgroundId)->where('is_active', 1)->get('membership_card_backgrounds')->row_array();
			if ($background) {$config['background_id'] = (int) $background['id']; $config['background_path'] = $background['file_path'];}
		}
		$config['layout'] = $this->normalise_layout($this->decode_editor_value($input['layout'] ?? []), $this->default_back_layout);
		$config['custom_objects'] = $this->normalise_custom_objects($this->decode_editor_value($input['custom_objects'] ?? []));
		return $config;
	}

	private function decode_editor_value($value)
	{
		if (is_string($value)) {
			$value = json_decode($value, true);
		}
		return is_array($value) ? $value : [];
	}

	private function normalise_layout(array $layout, array $defaults = null)
	{
		$defaults = $defaults ?: $this->default_layout;
		$result = [];
		foreach ($defaults as $key => $fallback) {
			$source = isset($layout[$key]) && is_array($layout[$key]) ? $layout[$key] : [];
			$result[$key] = [];
			foreach (['x', 'y', 'w', 'h'] as $field) {
				$value = isset($source[$field]) && is_numeric($source[$field]) ? (float) $source[$field] : $fallback[$field];
				$result[$key][$field] = round(max($field === 'x' || $field === 'y' ? -40 : 2, min(140, $value)), 2);
			}
			$result[$key]['w'] = max(3, min(140, $result[$key]['w']));
			$result[$key]['h'] = max(2, min(140, $result[$key]['h']));
		}
		return $result;
	}

	private function normalise_custom_objects(array $objects)
	{
		$result = [];
		foreach (array_slice($objects, 0, 20) as $item) {
			if (! is_array($item) || ! in_array(($item['type'] ?? ''), ['text', 'badge', 'line', 'shape', 'image'], true)) continue;
			$id = preg_replace('/[^a-z0-9_-]/i', '', (string) ($item['id'] ?? ''));
			if ($id === '') $id = 'obj_' . substr(md5(uniqid('', true)), 0, 8);
			$text = trim(strip_tags((string) ($item['text'] ?? '')));
			$color = strtoupper(trim((string) ($item['color'] ?? '#FFFFFF')));
			$fill = strtoupper(trim((string) ($item['fill'] ?? '#FFFFFF')));
			if (! preg_match('/^#[0-9A-F]{6}$/', $color)) $color = '#FFFFFF';
			if (! preg_match('/^#[0-9A-F]{6}$/', $fill)) $fill = '#FFFFFF';
			$coords = [];
			foreach (['x' => 10, 'y' => 10, 'w' => 20, 'h' => 8] as $field => $fallback) {
				$value = isset($item[$field]) && is_numeric($item[$field]) ? (float) $item[$field] : $fallback;
				$coords[$field] = round(max($field === 'x' || $field === 'y' ? -40 : 2, min(140, $value)), 2);
			}
			$coords['w'] = max(3, min(140, $coords['w']));
			$coords['h'] = max(2, min(140, $coords['h']));
			$object = array_merge($coords, [
				'id' => $id, 'type' => $item['type'], 'text' => mb_substr($text, 0, 70),
				'color' => $color, 'fill' => $fill,
				'font_size' => max(8, min(48, (int) ($item['font_size'] ?? 14))),
				'opacity' => max(10, min(100, (int) ($item['opacity'] ?? 100))),
			]);
			if ($item['type'] === 'image') {
				$assetId = (int) ($item['asset_id'] ?? 0);
				$asset = $assetId > 0 && $this->db->table_exists('membership_card_design_assets')
					? $this->db->where('id', $assetId)->where('asset_type', 'OBJECT')->where('is_active', 1)->get('membership_card_design_assets')->row_array()
					: null;
				if (! $asset) continue;
				$object['asset_id'] = (int) $asset['id'];
				$object['image_path'] = (string) $asset['file_path'];
				$object['text'] = (string) $asset['label'];
			}
			$result[] = $object;
		}
		return $result;
	}
}
