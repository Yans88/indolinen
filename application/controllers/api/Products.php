<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Products extends REST_Controller {

    function __construct(){
        parent::__construct();
		$this->load->model('Access','access',true);
		$this->load->model('Setting_m','sm', true);
		$this->load->library('converter');
		$this->load->library('send_api');
    }    
	
	public function index_post(){
		$param = $this->input->post();
		$keyword = isset($param['keyword']) ? $param['keyword'] : '';
		
		$id_sub_sub = isset($param['id_sub_sub']) ? $param['id_sub_sub'] : '';
		$start_price = isset($param['start_price']) ? $param['start_price'] : '';
		$end_price = isset($param['end_price']) ? $param['end_price'] : '';
		
		$sort = isset($param['sort']) ? (int)$param['sort'] : 0;
		$id_member = isset($param['id_member']) ? (int)$param['id_member'] : 0;
		$filter_color = isset($param['filter_color']) ? (int)$param['filter_color'] : 0;
		$filter_material = isset($param['filter_material']) ? (int)$param['filter_material'] : 0;
		// $login = $this->access->readtable('members','',array('id_member'=>$id_member))->row();
		// $id_tier = (int)$login->id_tier > 0 ? (int)$login->id_tier : 0;
		$_like = array();
		$or_like = array();
		$where = array('product.deleted_at'=>null);
		if(!empty($keyword)){
			$keyword = $this->db->escape_str($keyword);
			$_like = array('product.nama_barang'=>$keyword);
			// $or_like = array('product.deskripsi'=>$keyword);
		}
		$_sort = '';
		if($sort == 1){
			$_sort = array('product.nama_barang','ASC');
		}
		if($sort == 2){
			$_sort = array('product.nama_barang','DESC');
		}
		if($sort == 3){
			$_sort = array('ABS(product.id_product)','DESC');
		}
		if($sort == 4){
			$_sort = array('ABS(product.id_product)','ASC');
		}
		if($sort == 5){
			$_sort = array('ABS(product.min_hrg)','ASC');
		}
		if($sort == 6){
			$_sort = array('ABS(product.min_hrg)','DESC');
		}
		if(!empty($start_price)){
			$where += array('product.min_hrg >= '=> (int)$start_price);
		}
		if(!empty($end_price)){
			$where += array('product.min_hrg <= '=> (int)$end_price);
		}
		if(!empty($id_sub_sub)){			
			$field_in = 'product.id_sub_sub';
			$where_in = array($id_sub_sub);
		}
		if($filter_color > 0) $where += array('product.id_color' => $filter_color);
		if($filter_material > 0) $where += array('product.id_material' => $filter_material);
		$field_in2 = '';		
		$where_in2 = array();
		$select = array('product.*','kategori.nama_kategori','sub_kategori.nama_sub','sub_sub_kategori.nama_sub_s','colors.color','materials.material');
		$products = $this->access->readtable('product',$select,$where,array('kategori'=> 'kategori.id_kategori = product.id_kategori','sub_kategori'=> 'sub_kategori.id_sub = product.id_sub','sub_sub_kategori'=> 'sub_sub_kategori.id_sub_s = product.id_sub_sub','colors'=> 'colors.id = product.id_color','materials'=> 'materials.id = product.id_material'),'',$_sort,'LEFT','',$_like,$or_like, $field_in,$where_in)->result_array();
		
		$tier = '';
		$diskon = 0;
		$_diskon = 0;		
		$is_mywishlist = 0;		
		// if($id_tier > 0){
			// $tier = $this->access->readtable('tier','',array('deleted_at'=>null,'id_tier'=>$id_tier))->row();
			// $diskon = $tier->diskon > 0 ? $tier->diskon : 0;
			// $_diskon = $diskon > 0 ? $diskon / 100 : 0;
		// }
		
		$dt = array();		
		$wishlist = array();		
		$path = '';
		$chk_wishlist = '';
		$diskon_product = 0;
		$chk_wishlist = $this->access->readtable('list_wishlist','',array('id_member'=>$id_member))->result_array();
		if(!empty($chk_wishlist)){
			foreach($chk_wishlist as $cw){
				array_push($wishlist, $cw['id_product']);
			}
		}
		if(!empty($products)){
			foreach($products as $m){
				// $diskon_product = 0;
				// $stok = 0;
				// $diskon_product = (int)$m['diskon'] > 0 ? $m['diskon'] / 100 : 0;
				// $path = '';
				// $hrg_diskon = 0;
				// $harga = 0;
				// $harga = $m['harga'];
				// if($diskon_product > 0){
					// $harga = $harga - ($diskon_product * $harga);
				// }
				// if($id_tier > 0){
					// $hrg_diskon = $harga - ($harga * $_diskon);
				// }		
				
				$path = !empty($m['img']) ? base_url('uploads/products/'.$m['img']) : null;
				if($m['deleted_at'] == null || $m['deleted_at'] == '' || empty($m['deleted_at'])){
					$is_mywishlist = 0;
					if (in_array($m['id_product'], $wishlist)) $is_mywishlist = 1;
					$dt[] = array(
						'id_product'		=> $m['id_product'],					
						'id_kategori'		=> $m['id_kategori'],					
						'id_sub'			=> $m['id_sub'],					
						'id_sub_sub'		=> $m['id_sub_sub'],					
						// 'id_tier'			=> $id_tier,					
						'nama_barang'		=> $m['nama_barang'],
						'harga'				=> $m['min_hrg'],
						'diskon_product'	=> $m['diskon'],
						// 'hrg_stlh_diskon_p'	=> $harga,
						// 'diskon_tier'		=> $diskon,
						// 'hrg_stlh_diskon_t'	=> $hrg_diskon,
						'qty'				=> $m['qty'],						
						'weight'			=> $m['weight'],
						'minimum_order'		=> $m['minimum_order'],
						'kondisi'			=> $m['kondisi'],
						'deskripsi'			=> $m['deskripsi'],
						'img'				=> $path,					
						'nama_kategori'		=> $m['nama_kategori'],				
						'nama_sub'			=> $m['nama_sub'],				
						'nama_sub_sub'		=> $m['nama_sub_s'],	
						'id_color'			=> $m['id_color'],
						'id_material'		=> $m['id_material'],
						'color'				=> $m['color'],
						'material'			=> $m['material'],			
						'special_promo'		=> $m['special_promo'],			
						'is_mywishlist'		=> $is_mywishlist
					);
				}
			}
		}
		if (!empty($dt)){
            $this->set_response([
				'err_code' => '00',
				'err_msg' => 'Ok',
				'data' => $dt
			], REST_Controller::HTTP_OK);
        }else{
            $this->set_response([
                'err_code' => '04',
                'err_msg' => 'Data not be found'
            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
        }
    }	
	
	public function spromo_post(){
		$param = $this->input->post();
		$keyword = isset($param['keyword']) ? $param['keyword'] : '';
		
		$id_sub_sub = isset($param['id_sub_sub']) ? $param['id_sub_sub'] : '';
		$start_price = isset($param['start_price']) ? $param['start_price'] : '';
		$end_price = isset($param['end_price']) ? $param['end_price'] : '';
		
		$sort = isset($param['sort']) ? (int)$param['sort'] : 0;
		$id_member = isset($param['id_member']) ? (int)$param['id_member'] : 0;
		$filter_color = isset($param['filter_color']) ? (int)$param['filter_color'] : 0;
		$filter_material = isset($param['filter_material']) ? (int)$param['filter_material'] : 0;
		// $login = $this->access->readtable('members','',array('id_member'=>$id_member))->row();
		// $id_tier = (int)$login->id_tier > 0 ? (int)$login->id_tier : 0;
		$_like = array();
		$or_like = array();
		$where = array('product.deleted_at'=>null,'product.special_promo'=>1);
		if(!empty($keyword)){
			$keyword = $this->db->escape_str($keyword);
			$_like = array('product.nama_barang'=>$keyword);
			// $or_like = array('product.deskripsi'=>$keyword);
		}
		$_sort = '';
		if($sort == 1){
			$_sort = array('product.nama_barang','ASC');
		}
		if($sort == 2){
			$_sort = array('product.nama_barang','DESC');
		}
		if($sort == 3){
			$_sort = array('ABS(product.id_product)','DESC');
		}
		if($sort == 4){
			$_sort = array('ABS(product.id_product)','ASC');
		}
		if($sort == 5){
			$_sort = array('ABS(product.min_hrg)','ASC');
		}
		if($sort == 6){
			$_sort = array('ABS(product.min_hrg)','DESC');
		}
		if(!empty($start_price)){
			$where += array('product.min_hrg >= '=> (int)$start_price);
		}
		if(!empty($end_price)){
			$where += array('product.min_hrg <= '=> (int)$end_price);
		}
		if(!empty($id_sub_sub)){			
			$field_in = 'product.id_sub_sub';
			$where_in = array($id_sub_sub);
		}
		if($filter_color > 0) $where += array('product.id_color' => $filter_color);
		if($filter_material > 0) $where += array('product.id_material' => $filter_material);
		$field_in2 = '';		
		$where_in2 = array();
		$select = array('product.*','kategori.nama_kategori','sub_kategori.nama_sub','sub_sub_kategori.nama_sub_s','colors.color','materials.material');
		$products = $this->access->readtable('product',$select,$where,array('kategori'=> 'kategori.id_kategori = product.id_kategori','sub_kategori'=> 'sub_kategori.id_sub = product.id_sub','sub_sub_kategori'=> 'sub_sub_kategori.id_sub_s = product.id_sub_sub','colors'=> 'colors.id = product.id_color','materials'=> 'materials.id = product.id_material'),'',$_sort,'LEFT','',$_like,$or_like, $field_in,$where_in)->result_array();
		
		$tier = '';
		$diskon = 0;
		$_diskon = 0;		
		$is_mywishlist = 0;		
		// if($id_tier > 0){
			// $tier = $this->access->readtable('tier','',array('deleted_at'=>null,'id_tier'=>$id_tier))->row();
			// $diskon = $tier->diskon > 0 ? $tier->diskon : 0;
			// $_diskon = $diskon > 0 ? $diskon / 100 : 0;
		// }
		
		$dt = array();		
		$wishlist = array();		
		$path = '';
		$chk_wishlist = '';
		$diskon_product = 0;
		$chk_wishlist = $this->access->readtable('list_wishlist','',array('id_member'=>$id_member))->result_array();
		if(!empty($chk_wishlist)){
			foreach($chk_wishlist as $cw){
				array_push($wishlist, $cw['id_product']);
			}
		}
		if(!empty($products)){
			foreach($products as $m){
				// $diskon_product = 0;
				// $stok = 0;
				// $diskon_product = (int)$m['diskon'] > 0 ? $m['diskon'] / 100 : 0;
				// $path = '';
				// $hrg_diskon = 0;
				// $harga = 0;
				// $harga = $m['harga'];
				// if($diskon_product > 0){
					// $harga = $harga - ($diskon_product * $harga);
				// }
				// if($id_tier > 0){
					// $hrg_diskon = $harga - ($harga * $_diskon);
				// }		
				
				$path = !empty($m['img']) ? base_url('uploads/products/'.$m['img']) : null;
				if($m['deleted_at'] == null || $m['deleted_at'] == '' || empty($m['deleted_at'])){
					$is_mywishlist = 0;
					if (in_array($m['id_product'], $wishlist)) $is_mywishlist = 1;
					$dt[] = array(
						'id_product'		=> $m['id_product'],					
						'id_kategori'		=> $m['id_kategori'],					
						'id_sub'			=> $m['id_sub'],					
						'id_sub_sub'		=> $m['id_sub_sub'],					
						// 'id_tier'			=> $id_tier,					
						'nama_barang'		=> $m['nama_barang'],
						'harga'				=> $m['min_hrg'],
						'diskon_product'	=> $m['diskon'],
						// 'hrg_stlh_diskon_p'	=> $harga,
						// 'diskon_tier'		=> $diskon,
						// 'hrg_stlh_diskon_t'	=> $hrg_diskon,
						'qty'				=> $m['qty'],						
						'weight'			=> $m['weight'],
						'minimum_order'		=> $m['minimum_order'],
						'kondisi'			=> $m['kondisi'],
						'deskripsi'			=> $m['deskripsi'],
						'img'				=> $path,					
						'nama_kategori'		=> $m['nama_kategori'],				
						'nama_sub'			=> $m['nama_sub'],				
						'nama_sub_sub'		=> $m['nama_sub_s'],	
						'id_color'			=> $m['id_color'],
						'id_material'		=> $m['id_material'],
						'color'				=> $m['color'],
						'material'			=> $m['material'],			
						'special_promo'		=> $m['special_promo'],			
						'is_mywishlist'		=> $is_mywishlist
					);
				}
			}
		}
		if (!empty($dt)){
            $this->set_response([
				'err_code' => '00',
				'err_msg' => 'Ok',
				'data' => $dt
			], REST_Controller::HTTP_OK);
        }else{
            $this->set_response([
                'err_code' => '04',
                'err_msg' => 'Data not be found'
            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
        }
    }	
	
	function product_detail_post(){
		$_like = array();
		$dt = array();
		$list_img = array();
		$list_variant = array();
		$param = $this->input->post();
		$id_product = isset($param['id_product']) ? (int)$param['id_product'] : 0;
		$id_member = isset($param['id_member']) ? (int)$param['id_member'] : 0;
		$select = array('product.*','kategori.nama_kategori','sub_kategori.nama_sub','sub_sub_kategori.nama_sub_s','colors.color','materials.material');
		$where = array('product.deleted_at'=>null,'product.id_product'=> $id_product);
		$products = $this->access->readtable('product',$select,$where,array('kategori'=> 'kategori.id_kategori = product.id_kategori','sub_kategori'=> 'sub_kategori.id_sub = product.id_sub','sub_sub_kategori'=> 'sub_sub_kategori.id_sub_s = product.id_sub_sub','colors'=> 'colors.id = product.id_color','materials'=> 'materials.id = product.id_material'),'','','LEFT','',$_like)->row();	
		
		if(!empty($products)){
			$variant = '';
			$variant = $this->access->readtable('product_variant','',array('deleted_at'=>null, 'id_product'=>$id_product))->result_array();
			$login = $this->access->readtable('members','',array('id_member'=>$id_member))->row();
			$id_tier = (int)$login->id_tier > 0 ? (int)$login->id_tier : 0;
			$diskon = 0;
			if($id_tier > 0){
				$tier = $this->access->readtable('tier','',array('deleted_at'=>null,'id_tier'=>$id_tier))->row();
				$diskon = (int)$tier->diskon > 0 ? $tier->diskon : 0;
				$_diskon = $diskon > 0 ? $diskon / 100 : 0;
			}
			$chk_wishlist = $this->access->readtable('list_wishlist',array('id_product'),array('id_member'=>$id_member,'id_product'=>$id_product))->row();
			$is_mywishlist = (int)$chk_wishlist->id_product > 0 ? 1 : 0;
			$diskon_product = (int)$products->diskon > 0 ? $products->diskon : 0;
			if(!empty($variant)){
				foreach($variant as $v){
					$harga = 0;
					$hrg_stlh_diskon_t = 0;
					$pot_diskon_p = 0;
					$pot_diskon_t = 0;
					$harga = $v['hrg'];
					if($diskon_product > 0){
						$pot_diskon_p = (int)$products->diskon / 100 * $harga;
						$harga = $harga - $pot_diskon_p;
					}
					$hrg_stlh_diskon_t = $harga;
					if((int)$diskon > 0){
						$pot_diskon_t = $harga * $_diskon;
						$hrg_stlh_diskon_t = $harga - $pot_diskon_t;
					}
					$list_variant[] = array(
						'id_variant'		=> $v['id_variant'],
						'nama_variant'		=> $v['nama_variant'],
						'hrg_asli'			=> $v['hrg'],
						'diskon_product'	=> $diskon_product,
						'pot_diskon_p'		=> $pot_diskon_p,
						'hrg_stlh_diskon_p'	=> $harga,
						'diskon_tier'		=> $diskon,
						'pot_diskon_t'		=> $pot_diskon_t,
						'hrg_stlh_diskon_t'	=> $hrg_stlh_diskon_t
					);
				}
			}else{
				$list_variant = null;
			}
			$imgs = $this->access->readtable('product_img','',array('deleted_at'=>null,'id_product'=>$id_product))->result_array();
			$path = '';			
			$path = !empty($products->img) ? base_url('uploads/products/'.$products->img) : '';
			if(!empty($imgs)){				
				if(!empty($path)){
					$list_img[] = $path;
				}
				foreach($imgs as $im){
					$_img2 = '';
					$_img2 = !empty($im['img']) ? base_url('uploads/products/'.$im['img']) : '';
					if(!empty($_img2)){
						$list_img[] = $_img2;
					}					
				}
			}else{
				$list_img = null;
			}
			$chk_wishlist = '';
			$is_mywishlist = 0;
			$chk_wishlist = $this->access->readtable('list_wishlist','',array('id_member'=>$id_member,'id_product'=>$id_product))->row();
			if(count($chk_wishlist) > 0) $is_mywishlist = 1;
			$dt = array(
				'id_product'		=> $products->id_product,				
				'id_kategori'		=> $products->id_kategori,	
				'id_sub'			=> $products->id_sub,					
				'id_sub_sub'		=> $products->id_sub_sub,		
				'nama_barang'		=> $products->nama_barang,				
				'diskon_product'	=> $products->diskon,
				'qty'				=> $products->qty,
				'weight'			=> $products->weight,
				'minimum_order'		=> $products->minimum_order,
				'kondisi'			=> $products->kondisi,
				'deskripsi'			=> $products->deskripsi,
				'img'				=> $path,							
				'nama_kategori'		=> $products->nama_kategori,
				'nama_sub'			=> $products->nama_sub,
				'nama_sub_sub'		=> $products->nama_sub_s,
				'id_color'			=> $products->id_color,
				'id_material'		=> $products->id_material,
				'color'				=> $products->color,
				'material'			=> $products->material,
				'special_promo'		=> $products->special_promo,
				'is_mywishlist'		=> $is_mywishlist,
				'list_img'			=> $list_img,
				'list_variant'		=> $list_variant
			);
		}
		if (!empty($dt)){
            $this->set_response([
				'err_code' => '00',
				'err_msg' => 'Ok',
				'data' => $dt
			], REST_Controller::HTTP_OK);
        }else{
            $this->set_response([
                'err_code' => '04',
                'err_msg' => 'Data not be found'
            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
        }
	}

}