<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Category extends MY_Controller {

	public function __construct() {
		parent::__construct();		
		$this->load->model('Access', 'access', true);		
			
	}	
	
	public function index() {			
		if(!$this->session->userdata('login') || !$this->session->userdata('category')){
			$this->no_akses();
			return false;
		}
		$this->data['judul_browser'] = 'Category';
		$this->data['judul_utama'] = 'Category';
		$this->data['judul_sub'] = 'List';
		$id_merchant = $this->session->userdata('id_merchant') > 0 ? $this->session->userdata('id_merchant') : 0;
		$_level = $this->session->userdata('level');
		$where = array();
		$where = array('deleted_at'=>null, 'nama_kategori !='=>'Banner');
		
		$this->data['category'] = $this->access->readtable('kategori','',$where)->result_array();
		
		$this->data['isi'] = $this->load->view('category/category_v', $this->data, TRUE);
		$this->load->view('themes/layout_utama_v', $this->data);
	}
	
	
	public function del(){
		$tgl = date('Y-m-d H:i:s');
		$where = array(
			'id_kategori' => $_POST['id']
		);
		$data = array(
			'deleted_at'	=> $tgl
		);
		echo $this->access->updatetable('kategori', $data, $where);
	}

	
	public function simpan_cat(){
		$tgl = date('Y-m-d H:i:s');
		$id_category = isset($_POST['id_category']) ? (int)$this->converter->decode($_POST['id_category']) : 0;		
		$category = isset($_POST['category']) ? $_POST['category'] : '';
		$config = array();
		$config['upload_path']   = FCPATH.'/uploads/kategori/';
        $config['allowed_types'] = 'gif|jpg|png|ico|jpeg';
		$config['max_size']	= '2048';
		$config['encrypt_name'] = TRUE;
        $this->load->library('upload',$config);
		$gambar="";	
		$simpan = array(			
			'nama_kategori'		=> $category				
		);
		if(!$this->upload->do_upload('userfile')){
            $gambar="";
        }else{
            $gambar=$this->upload->file_name;
			$simpan += array('img'	=> $gambar);
        }
		$config = array();
		$config['upload_path']   = FCPATH.'/uploads/kategori/';
        $config['allowed_types'] = 'gif|jpg|png|ico|jpeg';
		$config['max_size']	= '2048';
		$config['encrypt_name'] = TRUE;
        $this->load->library('upload',$config);
		$gambar="";	
		if(!$this->upload->do_upload('userfile2')){
            $gambar="";
        }else{
            $gambar=$this->upload->file_name;
			$simpan += array('img2'	=> $gambar);
        }	
		$where = array();
		$save = 0;	
		if($id_category > 0){
			$where = array('id_kategori'=>$id_category);
			$save = $this->access->updatetable('kategori', $simpan, $where);   
		}else{
			$simpan += array('created_at'	=> $tgl);
			$save = $this->access->inserttable('kategori', $simpan);   
		}  
		redirect(site_url('category'));
	}
	
	function subcategory($id_category=0){
		if(!$this->session->userdata('login')){
			$this->no_akses();
			return false;
		}
		$this->data['judul_browser'] = 'Sub Category';
		$this->data['judul_utama'] = 'Sub Category';
		$this->data['judul_sub'] = 'List';
		
		$where = array();
		$where = array('deleted_at'=>null,'id_kategori'=>$id_category);
		$this->data['id_kategori'] = $id_category;
		$this->data['sub_category'] = $this->access->readtable('sub_kategori','',$where)->result_array();
		
		$this->data['isi'] = $this->load->view('category/sub_cat', $this->data, TRUE);
		$this->load->view('themes/layout_utama_v', $this->data);
	}
	
	function ssubcategory($id_sub=0){
		if(!$this->session->userdata('login')){
			$this->no_akses();
			return false;
		}
		$this->data['judul_browser'] = 'Sub Sub Category';
		$this->data['judul_utama'] = 'Sub Sub Category';
		$this->data['judul_sub'] = 'List';
		
		$where = array();
		$where = array('deleted_at'=>null,'id_sub'=>$id_sub);
		$this->data['id_sub'] = $id_sub;
		$this->data['sub_category'] = $this->access->readtable('sub_sub_kategori','',$where)->result_array();
		
		$this->data['isi'] = $this->load->view('category/ssub_cat', $this->data, TRUE);
		$this->load->view('themes/layout_utama_v', $this->data);
	}
	
	public function simpan_sub(){
		$tgl = date('Y-m-d H:i:s');
		$id_sub = isset($_POST['id_sub']) ? (int)$_POST['id_sub'] : 0;
		$id_category = isset($_POST['id_category']) ? (int)$_POST['id_category'] : 0;		
		$priority = isset($_POST['priority']) ? (int)$_POST['priority'] : 0;		
		$sub_category = isset($_POST['sub_category']) ? $_POST['sub_category'] : '';
		$config['upload_path']   = FCPATH.'/uploads/sub_kategori/';
        $config['allowed_types'] = 'gif|jpg|png|ico';
		$config['max_size']	= '2048';
		$config['encrypt_name'] = TRUE;
        $this->load->library('upload',$config);
		$gambar="";	
		$simpan = array(			
			'nama_sub'		=> $sub_category,			
			'id_kategori'	=> $id_category			
		);
		if(!$this->upload->do_upload('userfile')){
            $gambar="";
        }else{
            $gambar=$this->upload->file_name;
			$simpan += array('img'	=> $gambar);
        }	
		$where = array();
		$save = 0;	
		if($id_sub > 0){
			$where = array('id_sub'=>$id_sub);
			$save = $this->access->updatetable('sub_kategori', $simpan, $where);   
		}else{
			$simpan += array('created_at'	=> $tgl);
			$save = $this->access->inserttable('sub_kategori', $simpan);   
		}
		
		if($save > 0){
			redirect(site_url('category/subcategory/'.$id_category));
		}
	}
	
	public function simpan_ssub(){
		$tgl = date('Y-m-d H:i:s');
		$id_sub = isset($_POST['id_sub']) ? (int)$_POST['id_sub'] : 0;
		$id_sub_s = isset($_POST['id_sub_s']) ? (int)$_POST['id_sub_s'] : 0;		
		$priority = isset($_POST['priority']) ? (int)$_POST['priority'] : 0;		
		$sub_category = isset($_POST['sub_category']) ? $_POST['sub_category'] : '';
		$config['upload_path']   = FCPATH.'/uploads/sub_kategori/';
        $config['allowed_types'] = 'gif|jpg|png|ico';
		$config['max_size']	= '2048';
		$config['encrypt_name'] = TRUE;
        $this->load->library('upload',$config);
		$gambar="";	
		$simpan = array(			
			'nama_sub_s'		=> $sub_category,			
			'id_sub'	=> $id_sub			
		);
		if(!$this->upload->do_upload('userfile')){
            $gambar="";
        }else{
            $gambar=$this->upload->file_name;
			$simpan += array('img'	=> $gambar);
        }	
		$where = array();
		$save = 0;	
		if($id_sub_s > 0){
			$where = array('id_sub_s'=>$id_sub_s);
			$save = $this->access->updatetable('sub_sub_kategori', $simpan, $where);   
		}else{
			$simpan += array('created_at'	=> $tgl);
			$save = $this->access->inserttable('sub_sub_kategori', $simpan);   
		}
		
		if($save > 0){
			redirect(site_url('category/ssubcategory/'.$id_sub));
		}
	}
	
	public function del_sub(){
		$tgl = date('Y-m-d H:i:s');
		$where = array(
			'id_sub' => $_POST['id']
		);
		$data = array(
			'deleted_at'	=> $tgl
		);
		echo $this->access->updatetable('sub_kategori', $data, $where);
	}
	
	public function del_ssub(){
		$tgl = date('Y-m-d H:i:s');
		$where = array(
			'id_sub_s' => $_POST['id']
		);
		$data = array(
			'deleted_at'	=> $tgl
		);
		echo $this->access->updatetable('sub_sub_kategori', $data, $where);
	}
	
	public function no_akses() {
		if ($this->session->userdata('login') == FALSE) {
			redirect('/');
			return false;
		}
		$this->data['judul_browser'] = 'Tidak Ada Akses';
		$this->data['judul_utama'] = 'Tidak Ada Akses';
		$this->data['judul_sub'] = '';
		$this->data['isi'] = '<div class="alert alert-danger">Anda tidak memiliki Akses.</div><div class="error-page">
        <h3 class="text-red"><i class="fa fa-warning text-yellow"></i> Oops! No Akses.</h3></div>';
		$this->load->view('themes/layout_utama_v', $this->data);
	}


}
