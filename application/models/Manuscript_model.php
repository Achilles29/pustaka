<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Manuscript_model extends CI_Model
{
	public function stats()
	{
		if (! $this->db->table_exists('ancient_manuscripts')) return ['total'=>0,'published'=>0,'digitized'=>0,'featured'=>0];
		$digitized = $this->db->table_exists('ancient_manuscript_pages')
			? (int) $this->db->where('(preview_file_path IS NOT NULL OR EXISTS (SELECT 1 FROM ancient_manuscript_pages mp WHERE mp.manuscript_id = ancient_manuscripts.id))', null, false)->count_all_results('ancient_manuscripts')
			: (int) $this->db->where('preview_file_path IS NOT NULL', null, false)->count_all_results('ancient_manuscripts');
		return [
			'total' => (int) $this->db->count_all('ancient_manuscripts'),
			'published' => (int) $this->db->where('status','published')->count_all_results('ancient_manuscripts'),
			'digitized' => $digitized,
			'featured' => (int) $this->db->where('is_featured',1)->where('status','published')->count_all_results('ancient_manuscripts'),
		];
	}

	public function get_admin(array $filters = [], $limit = 25, $offset = 0)
	{
		$this->admin_query($filters);
		return $this->db->order_by('m.updated_at','DESC')->order_by('m.id','DESC')->limit(max(1,min(100,(int)$limit)),max(0,(int)$offset))->get()->result_array();
	}

	public function count_admin(array $filters = []) { $this->admin_query($filters); return (int) $this->db->count_all_results(); }

	private function admin_query(array $filters)
	{
		$this->db->from('ancient_manuscripts m');
		if (! empty($filters['q'])) { $q=trim($filters['q']); $this->db->group_start()->like('m.inventory_number',$q)->or_like('m.title',$q)->or_like('m.origin',$q)->or_like('m.language',$q)->group_end(); }
		if (! empty($filters['status'])) $this->db->where('m.status',$filters['status']);
		if (! empty($filters['access_level'])) $this->db->where('m.access_level',$filters['access_level']);
	}

	public function find($id, $published_only = false)
	{
		if (! $this->db->table_exists('ancient_manuscripts')) return null;
		$this->db->from('ancient_manuscripts')->where('id',(int)$id);
		if ($published_only) $this->db->where('status','published')->where('permission_status','verified')->where('permission_file_path IS NOT NULL',null,false);
		return $this->db->get()->row_array();
	}

	public function get_pages($manuscript_id)
	{
		if (! $this->db->table_exists('ancient_manuscript_pages')) return [];
		return $this->db->from('ancient_manuscript_pages')->where('manuscript_id', (int) $manuscript_id)->order_by('page_number', 'ASC')->get()->result_array();
	}

	public function find_page($manuscript_id, $page_id)
	{
		if (! $this->db->table_exists('ancient_manuscript_pages')) return null;
		return $this->db->from('ancient_manuscript_pages')->where('manuscript_id', (int) $manuscript_id)->where('id', (int) $page_id)->get()->row_array();
	}

	public function next_page_number($manuscript_id)
	{
		if (! $this->db->table_exists('ancient_manuscript_pages')) return 1;
		$row = $this->db->select_max('page_number')->where('manuscript_id', (int) $manuscript_id)->get('ancient_manuscript_pages')->row_array();
		return max(1, (int) ($row['page_number'] ?? 0) + 1);
	}

	public function add_page(array $data)
	{
		$this->db->insert('ancient_manuscript_pages', $data);
		return (int) $this->db->insert_id();
	}

	public function delete_page($manuscript_id, $page_id)
	{
		return $this->db->where('manuscript_id', (int) $manuscript_id)->where('id', (int) $page_id)->delete('ancient_manuscript_pages');
	}

	public function get_public(array $filters = [], $limit = 18, $offset = 0)
	{
		$this->public_query($filters);
		return $this->db->order_by('is_featured','DESC')->order_by('title','ASC')->limit(max(1,min(60,(int)$limit)),max(0,(int)$offset))->get()->result_array();
	}

	public function count_public(array $filters = []) { $this->public_query($filters); return (int)$this->db->count_all_results(); }

	private function public_query(array $filters)
	{
		$this->db->from('ancient_manuscripts')->where('status','published')->where('permission_status','verified')->where('permission_file_path IS NOT NULL',null,false)->where_in('access_level',['public','member']);
		if (! empty($filters['q'])) { $q=trim($filters['q']); $this->db->group_start()->like('inventory_number',$q)->or_like('title',$q)->or_like('origin',$q)->or_like('language',$q)->group_end(); }
		if (! empty($filters['language'])) $this->db->where('language',$filters['language']);
		if (! empty($filters['script'])) $this->db->where('script',$filters['script']);
	}

	public function featured($limit = 3) { return $this->get_public([], $limit, 0); }
	public function options($column)
	{
		if (! in_array($column,['language','script'],true) || ! $this->db->table_exists('ancient_manuscripts')) return [];
		return array_column($this->db->select($column)->from('ancient_manuscripts')->where('status','published')->where($column.' IS NOT NULL',null,false)->where($column.' !=','')->group_by($column)->order_by($column)->get()->result_array(),$column);
	}

	public function create(array $data, $user_id) { $payload=$this->clean_payload($data); if ($payload['inventory_number'] === '') $payload['inventory_number']=$this->next_inventory_number(); if ($payload['title'] === '') throw new RuntimeException('Judul naskah wajib diisi.'); $payload['created_by']=$user_id?:null; $payload['updated_by']=$user_id?:null; $this->db->insert('ancient_manuscripts',$payload); return (int)$this->db->insert_id(); }
	public function update($id, array $data, $user_id) { $payload=$this->clean_payload($data); $payload['updated_by']=$user_id?:null; return $this->db->where('id',(int)$id)->update('ancient_manuscripts',$payload); }
	public function delete($id) { return $this->db->where('id',(int)$id)->delete('ancient_manuscripts'); }
	public function inventory_exists($number, $exclude_id = null)
	{
		$this->db->from('ancient_manuscripts')->where('inventory_number', strtoupper(trim((string) $number)));
		if ($exclude_id !== null) $this->db->where('id !=', (int) $exclude_id);
		return $this->db->count_all_results() > 0;
	}
	public function next_inventory_number()
	{
		$year = date('Y'); $sequence = (int) $this->db->like('inventory_number', 'NK-' . $year . '-', 'after')->count_all_results('ancient_manuscripts') + 1;
		do { $number = sprintf('NK-%s-%04d', $year, $sequence++); } while ($this->inventory_exists($number));
		return $number;
	}

	public function record_preview($id, $user_id = null, $event_type = 'preview')
	{
		if (! $this->db->table_exists('ancient_manuscript_access_logs')) return;
		$this->db->insert('ancient_manuscript_access_logs',['manuscript_id'=>(int)$id,'auth_user_id'=>(int)$user_id?:null,'event_type'=>substr((string)$event_type,0,40),'ip_address'=>$this->input->ip_address(),'user_agent'=>substr((string)$this->input->user_agent(),0,255)]);
	}

	private function clean_payload(array $data)
	{
		$keys=['inventory_number','title','alternate_title','language','script','material','page_count','dimensions','condition_state','estimated_period','author_scribe','origin','current_location','description','collection_history','conservation_notes','digitized_at','digitized_by','digitization_notes','rights_note','owner_name','permission_reference','permission_date','permission_notes','permission_file_path','permission_original_name','permission_mime_type','permission_file_size','permission_status','permission_verified_by','permission_verified_at','cover_path','preview_file_path','preview_original_name','preview_mime_type','access_level','status','is_featured'];
		$out=[]; foreach($keys as $key) if(array_key_exists($key,$data)) $out[$key]=is_string($data[$key]) ? trim($data[$key]) : $data[$key];
		$out['inventory_number']=strtoupper(substr((string)($out['inventory_number']??''),0,80));
		$out['title']=substr((string)($out['title']??''),0,255);
		$out['page_count']=!empty($out['page_count'])?max(1,min(9999,(int)$out['page_count'])):null;
		$out['digitized_at']=!empty($out['digitized_at'])?$out['digitized_at']:null;
		$out['permission_date']=!empty($out['permission_date'])?$out['permission_date']:null;
		$out['access_level']=in_array($out['access_level']??'', ['public','member','internal'],true)?$out['access_level']:'public';
		$out['status']=in_array($out['status']??'', ['draft','published','archived'],true)?$out['status']:'draft';
		$out['is_featured']=!empty($out['is_featured'])?1:0;
		return $out;
	}
}
