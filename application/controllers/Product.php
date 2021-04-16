<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Product extends MY_Controller {

	public function __construct() {
		parent::__construct();		
		$this->load->model('Access', 'access', true);		
			
	}	
	
	public function index() {
		if(!$this->session->userdata('login') || !$this->session->userdata('product')){
			$this->no_akses();
			return false;
		}		
		$this->data['judul_browser'] = 'Product';
		$this->data['judul_utama'] = 'Product';
		$this->data['judul_sub'] = 'List';
		$select = array('product.*','kategori.nama_kategori','sub_kategori.nama_sub','sub_sub_kategori.nama_sub_s');
		$where = array();
		$where = array('product.deleted_at'=>null);
			
		$this->data['product'] = $this->access->readtable('product',$select,$where,array('kategori'=> 'kategori.id_kategori = product.id_kategori','sub_kategori'=> 'sub_kategori.id_sub = product.id_sub','sub_sub_kategori'=> 'sub_sub_kategori.id_sub_s = product.id_sub_sub'),'','','LEFT')->result_array();
		$this->data['isi'] = $this->load->view('products/product_v', $this->data, TRUE);
		$this->load->view('themes/layout_utama_v', $this->data);
	}	
	
	public function del(){
		$tgl = date('Y-m-d H:i:s');
		$where = array(
			'id_product' => $_POST['id']
		);
		$data = array(
			'deleted_at'	=> $tgl
		);
		$dt_product = '';
		$dt_product = $this->access->readtable('product', '', array('id_product' => $_POST['id']))->row();
		$simpan = array(			
				'nama_barang'		=> $dt_product->nama_barang,
				'id_kategori'		=> $dt_product->id_kategori,	
				'deskripsi'			=> $dt_product->deskripsi,	
				'harga'				=> $dt_product->harga,
				'paket'				=> $dt_product->paket,
				'qty'				=> $dt_product->qty,
				'diskon'			=> $dt_product->diskon
			);
		$simpan += array('id_product'=>$_POST['id'],'ket'=>'Delete Product','deleted_at'=>$tgl,'update_by'=>$this->session->userdata('operator_id'));
		$this->access->inserttable('history_product', $simpan); 
		echo $this->access->updatetable('product', $data, $where);
	}
	
	public function simpan(){
		$tgl = date('Y-m-d H:i:s');
		
		$id_product = isset($_POST['id_product']) ? (int)$_POST['id_product'] : 0;		
		$id_kategori = isset($_POST['kategori']) ? (int)$_POST['kategori'] : 0;		
		$sub_kategori = isset($_POST['sub_kategori']) ? (int)$_POST['sub_kategori'] : 0;		
		$subs_kategori = isset($_POST['subs_kategori']) ? (int)$_POST['subs_kategori'] : 0;		
		$nama_produk = isset($_POST['nama_produk']) ? trim($_POST['nama_produk']) : '';
		$deskripsi = isset($_POST['deskripsi']) ? $_POST['deskripsi'] : '';
		$kondisi = isset($_POST['kondisi']) ? $_POST['kondisi'] : '';
		$weight = isset($_POST['weight']) ? str_replace('.','',$_POST['weight']) : '';
		$stok = isset($_POST['stok']) ? str_replace('.','',$_POST['stok']) : '';
		$diskon = isset($_POST['diskon']) ? str_replace('.','',$_POST['diskon']) : 0;
		$min_order = isset($_POST['min_order']) ? str_replace('.','',$_POST['min_order']) : 0;
		$color = isset($_POST['color']) ? (int)$_POST['color'] : 0;
		$material = isset($_POST['material']) ? (int)$_POST['material'] : 0;
		$special_promo = isset($_POST['special_promo']) ? (int)$_POST['special_promo'] : 0;
		$config['upload_path']   = FCPATH.'/uploads/products/';
        $config['allowed_types'] = 'jpeg|jpg|png|ico';
		$config['max_size']	= '2048';
		$config['encrypt_name'] = TRUE;
        $this->load->library('upload',$config);
		$gambar="";			
		$simpan = array(			
			'nama_barang'		=> $nama_produk,
			'id_kategori'		=> $id_kategori,	
			'id_sub'			=> $sub_kategori,	
			'id_sub_sub'		=> $subs_kategori,	
			'deskripsi'			=> $deskripsi,	
			'kondisi'			=> $kondisi,	
			'weight'			=> $weight,			
			'qty'				=> (int)$stok,
			'minimum_order'		=> (int)$min_order,
			'id_material'		=> $material,
			'id_color'			=> $color,
			'special_promo'		=> $special_promo,
			'diskon'			=> $diskon
		);
		if(!$this->upload->do_upload('userfile')){
            $gambar="";
        }else{
            $gambar=$this->upload->file_name;
			$simpan += array('img'	=> $gambar);
        }
		
		$where = array();
		$save = 0;		
		if($id_product > 0){
			$where = array('id_product'=>$id_product);
			$this->access->updatetable('product', $simpan, $where);   
			$save = $id_product; 
			$simpan += array('id_product'=>$save,'ket'=>'Update Product','update_by'=>$this->session->userdata('operator_id'));
			$this->access->inserttable('history_product', $simpan);   
		}else{
			$simpan += array('created_at'	=> $tgl);
			$save = $this->access->inserttable('product', $simpan);			
			$simpan += array('id_product'=>$save,'ket'=>'Create Product','update_by'=>$this->session->userdata('operator_id'));
			$this->access->inserttable('history_product', $simpan);   
		}  
	
		if($save > 0){
			redirect(site_url('product'));
		}	 
	}
	
	function add($id_product =''){
		$this->data['judul_browser'] = 'Product';
		$this->data['judul_utama'] = 'Product';
		$this->data['judul_sub'] = 'Add';
		$variant = '';
		
		$this->data['kat'] = $this->access->readtable('kategori','',array('deleted_at'=>null, 'nama_kategori !='=>'Banner'))->result_array();
		$id_product = $this->converter->decode($id_product);
		if($id_product > 0){
			$this->data['judul_sub'] = 'Edit';
			$product = $this->access->readtable('product','',array('id_product'=>$id_product))->row();
			$variant = $this->access->readtable('product_variant','',array('deleted_at'=>null, 'id_product'=>$id_product))->result_array();
		}
		$this->data['colors'] = $this->access->readtable('colors','',array('deleted_at'=>null))->result_array();
		$this->data['materials'] = $this->access->readtable('materials','',array('deleted_at'=>null,))->result_array();
		$this->data['variant'] = $variant;
		$this->data['product'] = $product;
		$this->data['isi'] = $this->load->view('products/product_frm', $this->data, TRUE);
		$this->load->view('themes/layout_utama_v', $this->data);
	}
	
	function get_sub(){
		$kategori = isset($_POST['id']) ? $_POST['id'] : '0';
		$sub_cat = $this->access->readtable('sub_kategori','',array('deleted_at'=>null, 'id_kategori'=>$kategori))->result_array();
		
		if(!empty($sub_cat)){
			$html = '';
			foreach($sub_cat as $sc){
				$html .='<option value="'.$sc['id_sub'].'">'.$sc['nama_sub'].'</option>';
			}
		}else{
			$html ='<option value="">Data not found</option>';
		}
		echo $html;
	}
	
	function get_sub_sub(){
		$id_sub = isset($_POST['id']) ? $_POST['id'] : '0';
		$sub_cat = $this->access->readtable('sub_sub_kategori','',array('deleted_at'=>null, 'id_sub'=>$id_sub))->result_array();
		
		if(!empty($sub_cat)){
			$html = '';
			foreach($sub_cat as $sc){
				$html .='<option value="'.$sc['id_sub_s'].'">'.$sc['nama_sub_s'].'</option>';
			}
		}else{
			$html ='<option value="">Data not found</option>';
		}
		echo $html;
	}
	
	function save_variant(){
		$id_product = isset($_POST['id_product']) ? (int)$_POST['id_product'] : 0;
		$id_variant = isset($_POST['id_variant']) ? (int)$_POST['id_variant'] : 0;
		$nama_variant = isset($_POST['nama_variant']) ? ucfirst($_POST['nama_variant']) : '';		
		$hrg = isset($_POST['hrg_pack']) ? str_replace('.','',$_POST['hrg_pack']) : 0;	
		// $product = $this->access->readtable('product','',array('id_product'=>$id_product))->row();
		$save = 0;
		$simpan = array(			
			'nama_variant'	=> $nama_variant,						
			'id_product'	=> $id_product,		
			'hrg'			=> $hrg
		);
		if($id_variant > 0){
			$save = $this->access->updatetable('product_variant', $simpan, array('id_variant' => $id_variant));
			$save = $id_product;
		}else{
			$simpan += array('created_at' => date('Y-m-d H:i:s'));
			$save = $this->access->inserttable('product_variant', $simpan);
		}
		if($save){
			$sort = array('ABS(hrg)','ASC');
			$variant = $this->access->readtable('product_variant',array('min(ABS(hrg)) as hrg'),array('id_product'=>$id_product,'deleted_at'=>null),'', '',$sort)->row();
			$min_hrg = $variant->hrg > 0 ? $variant->hrg : 0;
			$dt_upd = array('min_hrg'=>$min_hrg);
			$this->access->updatetable('product', $dt_upd, array('id_product' => $id_product));
		}
		
		echo $save;
	}
	
	public function del_variant(){
		$tgl = date('Y-m-d H:i:s');
		$where = array(
			'id_variant' => $_POST['id']
		);
		$data = array(
			'deleted_at'	=> $tgl
		);
		echo $this->access->updatetable('product_variant', $data, $where);
	}
	
	public function banner_product($id_product=0) {	
		$product = $this->access->readtable('product','',array('deleted_at'=>null, 'id_product'=>$id_product))->row();
		$this->data['judul_browser'] = 'Product';
		$this->data['judul_utama'] = $product->nama_product;
		$this->data['judul_sub'] = 'Image';
		$this->data['id_product'] = $id_product;
		$this->data['category'] = $this->access->readtable('product_img','',array('deleted_at'=>null, 'id_product'=>$id_product))->result_array();
		
		$this->data['isi'] = $this->load->view('category/banner_product', $this->data, TRUE);
		$this->load->view('themes/layout_utama_v', $this->data);
	}
	
	public function simpan_img(){
		$tgl = date('Y-m-d H:i:s');
		$id_img = isset($_POST['id_img']) ? (int)$_POST['id_img'] : 0;		
		$id_product = isset($_POST['id_product']) ? (int)$_POST['id_product'] : 0;		
		$config['upload_path']   = FCPATH.'/uploads/products/';
        $config['allowed_types'] = 'gif|jpg|png|ico';
		$config['max_size']	= '2048';
		$config['encrypt_name'] = TRUE;
        $this->load->library('upload',$config);
		$gambar="";	
		$simpan = array(			
			'id_product'		=> $id_product				
		);
		if(!$this->upload->do_upload('userfile')){
            $gambar="";
        }else{
            $gambar=$this->upload->file_name;
			$simpan += array('img'	=> $gambar);
        }	
		$where = array();
		$save = 0;	
		if($id_img > 0){
			$where = array('id_img'=>$id_img);
			$save = $this->access->updatetable('product_img', $simpan, $where);   
		}else{
			$simpan += array('created_at'	=> $tgl);
			$save = $this->access->inserttable('product_img', $simpan);   
		}  
		if($save > 0){
			redirect(site_url('product/banner_product/'.$id_product));
		}
	}
	
	public function del_img(){
		$tgl = date('Y-m-d H:i:s');
		$where = array(
			'id_img' => $_POST['id']
		);
		$data = array(
			'deleted_at'	=> $tgl
		);
		echo $this->access->updatetable('product_img', $data, $where);
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
