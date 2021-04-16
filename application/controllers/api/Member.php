<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Member extends REST_Controller {

    function __construct(){
        parent::__construct();
		$this->load->model('Access','access',true);
		$this->load->model('Setting_m','sm', true);
		$this->load->library('converter');
    }

    public function index_get(){
		$id = $this->get('id');
		$id = (int)$id;
		$dt = array();
		$login = '';
		$status_name = '';	
		if($id > 0){
			$login = $this->access->readtable('members','',array('id_member'=>$id))->row();
			if(!empty($login)){	
				if($login->status == 2){
					$status_name = 'approved';
				}
				if($login->status == 3){
					$status_name = 'rejected';
				}
				if($login->status == 4){
					$status_name = 'active';
				}
				if($login->status == 5){
					$status_name = 'inactive';
				}
				$dt = [
					"id_member"			=> $login->id_member,
					"id_tier"			=> $login->id_tier,					
					"id_chat"			=> $login->id_chat,					
					"nama"				=> $login->nama,							
					"surname"			=> $login->surname,							
					"email"				=> $login->email,
					"phone"				=> $login->phone,																	
					"fcm_token"			=> $login->gcm_token,														
					"status"			=> $login->status,							
					"status_name"		=> $status_name,						
					"photo"				=> !empty($login->photo) ? base_url('uploads/members/'.$login->photo) : null,	
					"current_pass"		=> $this->converter->decode($login->pass),
					"tgl_reg"			=> date('d-M-Y', strtotime($login->tgl_reg))
				];
			}		
			$this->set_response([
				'err_code' => '00',
				'err_msg' => 'Ok',
				'profile_info' => $dt
			], REST_Controller::HTTP_OK);
			return false;
		}else{
			$login = $this->access->readtable('members','',array('deleted_at'=>null))->result_array();
		}
		
		$status_name = '';
		if(!empty($login)){
			foreach($login as $l){
				if($l['status'] == 2){
					$status_name = 'approved';
				}
				if($l['status'] == 3){
					$status_name = 'rejected';
				}
				if($l['status'] == 4){
					$status_name = 'active';
				}
				if($l['status'] == 1){
					$status_name = 'inactive';
				}
				$dt[] = array(
					"id_member"			=> $l['id_member'],
					"id_tier"			=> $l['id_tier'],
					"id_chat"			=> $l['id_chat'],
					"nama"				=> $l['nama'],							
					"surname"			=> $l['surname'],							
					"email"				=> $l['email'],
					"phone"				=> $l['phone'],						
					"fcm_token"			=> $l['gcm_token'],	
					"status"			=> $l['status'],							
					"status_name"		=> $status_name,
					"photo"				=> !empty($l['photo']) ? base_url('uploads/members/'.$l['photo']) : null,								
					"current_pass"		=> $this->converter->decode($l['pass']),
					"tgl_reg"			=> date('d-M-Y', strtotime($l['tgl_reg']))
				);
				
			}
		}
		if (!empty($dt)){
			// error_log(serialize($dt));
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

	public function reg_post(){
		$param = $this->input->post();
		$id_member = isset($param['id_member']) ? (int)$param['id_member'] : 0;
		
		$nama_member = isset($param['nama_member']) ? $param['nama_member'] : '';
		$surname = isset($param['surname']) ? $param['surname'] : '';
		$nama_toko = isset($param['nama_toko']) ? $param['nama_toko'] : '';
		$gcm_token = isset($param['fcm_token']) ? $param['fcm_token'] : '';
		$password = isset($param['password']) ? $this->converter->encode($param['password']) : '';
		$old_password = isset($param['old_password']) ? $this->converter->encode($param['old_password']) : '';
		$email = isset($param['email']) ? $param['email'] : ''; 	
				
		$phone = isset($param['phone']) ? $param['phone'] : '';
		$tgl_reg = date('Y-m-d H:i:s');		
		$user_id = isset($param['user_id']) ? $param['user_id'] : '';
		$device = isset($param['device']) ? $param['device'] : '';		
			
		$save_sms = 0;
		$save = 0;
		$upl = '';
		$upload = array();
		$config['upload_path'] = "./uploads/members/";
		$config['allowed_types'] = "jpg|png|jpeg|";
		$config['max_size']	= '1048';
		$name = $_FILES['photo']['name'];
		$config['file_name'] = date('YmdHis').$name;
		$config['encrypt_name'] = TRUE;
		$this->load->library('upload',$config);
		if(empty($email) && ($id_member == 0 || $id_member ==  '')){
			$result = array( 'err_code'	=> '01',
                             'err_msg'	=> 'Param Password can\'t empty.' );
			$this->set_response($result, REST_Controller::HTTP_OK);
			return false;
		}
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$result = array('err_code'	=> '06',
							'err_msg'	=> 'Email invalid format.' );
			$this->set_response($result, REST_Controller::HTTP_OK);
			return false;
		}
		if(empty($password) && ($id_member == 0 || $id_member ==  '')){
			$result = array( 'err_code'	=> '01',
                             'err_msg'	=> 'Param Password can\'t empty.' );
			$this->set_response($result, REST_Controller::HTTP_OK);
			return false;
		}
		if(empty($phone) && ($id_member == 0 || $id_member ==  '')){
			$result = array( 'err_code'	=> '01',
                             'err_msg'	=> 'Param Phone can\'t empty.' );
			$this->set_response($result, REST_Controller::HTTP_OK);
			return false;
		}
		$ptn = "/^0/";
		$rpltxt = "62";
		$phone = preg_replace($ptn, $rpltxt, $phone);
		
		$chk_email = $this->access->readtable('members','',array('email'=>$email,'deleted_at'=>null))->row();
		$ketemu = count($chk_email);
		$login = '';
		$data = array();		
		$details = '';		
		$a = '';		
		if($ketemu > 0 && $id_member != $chk_email->id_member){
			$this->set_response([
                'err_code' => '04',
                'err_msg' => 'Email already exist'
            ], REST_Controller::HTTP_OK);
			return false;
		}
		$chk_phone = $this->access->readtable('members','',array('phone'=>$phone,'deleted_at'=>null))->row();
		// error_log($this->db->last_query());
		$ketemu = count($chk_phone);
		// error_log($ketemu);
		if($ketemu > 0 && $id_member != $chk_phone->id_member){
			$this->set_response([
                'err_code' => '04',
                'err_msg' => 'Phone already exist'
            ], REST_Controller::HTTP_OK);
			return false;
		}
		$chk_email = '';
		$date_expired = '';		
		$simpan = array();
		if(!empty($surname)){
			$simpan += array("surname"	=> $surname);
		}
		if(!empty($nama_member)){
			$simpan += array("nama"	=> $nama_member);
		}
		if(!empty($alamat)){
			$simpan += array("address"	=> $alamat);
		}	
		if(!empty($phone)){
			$simpan += array("phone"	=> $phone);
		}
		
		if(!empty($gcm_token)){
			$simpan += array("gcm_token"	=> $gcm_token);
		}
		if(!empty($device)){
			$simpan += array("device"	=> $device);
		}		
		
		if(!empty($password)){
			$simpan += array("pass"	=> $password);
		}
		if(!empty($_FILES['photo'])){
			$upl = '';
			if($this->upload->do_upload('photo')){
				$upl = $this->upload->data();
				$simpan += array("photo"	=> $upl['file_name']);
			}
		}
		
		if($id_member > 0){
			$chk_member = '';
			if(!empty($password) && empty($old_password)){
				$result = array( 'err_code'	=> '01',
                             'err_msg'	=> 'Param password can\'t empty.' );
				$this->set_response($result, REST_Controller::HTTP_OK);
				return false;
			}
			
			if(!empty($password) && !empty($old_password)){
				$chk_member = $this->access->readtable('members','',array('id_member'=>$id_member,'pass'=>$old_password))->row();
				
				if((int)$chk_member <= 0){
					$result = array( 'err_code'	=> '04',
									 'err_msg'	=> 'Password tidak sesuai' );
					$this->set_response($result, REST_Controller::HTTP_OK);
					return false;					
				}
			}
			$this->access->updatetable('members',$simpan, array("id_member"=>$id_member));
			$save = $id_member;
		}else{
			$simpan +=array('tgl_reg' => $tgl_reg,'email' => $email,'phone'	=> $phone,'status'=>4,'id_tier'=>0);
			$save = $this->access->inserttable('members',$simpan);	
		}

		$status_name = '';	
		if($save){
			$login = $this->access->readtable('members','',array('id_member'=>$save))->row();
			if(!empty($login)){	
				if($login->status == 2){
					$status_name = 'approved';
				}
				if($login->status == 3){
					$status_name = 'rejected';
				}
				if($login->status == 4){
					$status_name = 'active';
				}
				if($login->status == 5){
					$status_name = 'inactive';
				}
				$details = [
					"id_member"			=> $login->id_member,					
					"nama"				=> $login->nama,										
					"surname"				=> $login->surname,										
					"email"				=> $login->email,
					"phone"				=> $login->phone,																			
					"status"			=> $login->status,							
					"status_name"		=> $status_name,		
					"photo"				=> !empty($login->photo) ? base_url('uploads/members/'.$login->photo) : null,
					"current_pass"		=> $this->converter->decode($login->pass),
					"tgl_reg"			=> date('d-M-Y', strtotime($login->tgl_reg))
				];
			}
			$simpan_alamat =array();
			$this->set_response([
					'err_code' => '00',
					'err_msg' => 'Terima kasih telah mendaftar di indolinen',
					'profile_info' => $details,
					
				], REST_Controller::HTTP_OK);
			
		}else{
			$this->set_response([
				'err_code' => '03',
				'err_msg' => 'Insert has problem'
			], REST_Controller::HTTP_OK);
		}
	}

	function login_post(){
		$result = array();
		$login = '';
		$param = $this->input->post();
		$email = isset($param['email']) ? $param['email'] : '';
		$password = isset($param['password']) ? $this->converter->encode($param['password']) : '';
		$phone = '';
		$ptn = "/^0/";
		$rpltxt = "62";
		$phone = preg_replace($ptn, $rpltxt, $email);
		$login = $this->access->readtable('members','',array('phone'=>$phone,'pass'=>$password,'deleted_at'=>null))->row();
		// error_log($this->db->last_query());
		if(!empty($email) && !empty($password)){
			if(!filter_var($email, FILTER_VALIDATE_EMAIL) && count($login) == 0) {
				$result = [
					'err_code'	=> '06',
					'err_msg'	=> 'Email invalid format'
				];
				$this->set_response($result, REST_Controller::HTTP_OK);
				return false;
			}else{
				if(count($login) == 0){
					$login = $this->access->readtable('members','',array('email'=>$email,'pass'=>$password,'deleted_at'=>null))->row();
				}
			}
		}else{
			$result = array( 'err_code'	=> '01',
                             'err_msg'	=> 'Param Email or Password can\'t empty.' );
			$this->set_response($result, REST_Controller::HTTP_OK);
			return false;
		}
		$status_name = '';
		$details = '';	
		if(!empty($login)){				
			if($login->status == 1){
				$result = array( 'err_code'	=> '06',
								 'err_msg'	=> 'Waiting approval' );
				$this->set_response($result, REST_Controller::HTTP_OK);
				return false;
			}
			if($login->status == 3){
				$result = array( 'err_code'	=> '04',
								 'err_msg'	=> 'Account rejected' );
				$this->set_response($result, REST_Controller::HTTP_OK);
				return false;
			}
			if($login->status == 4){
				$status_name = 'active';
			}
			if($login->status == 5){
				$result = array( 'err_code'	=> '05',
								 'err_msg'	=> 'Account inactive' );
				$this->set_response($result, REST_Controller::HTTP_OK);
				return false;
			}
			$details = [
				"id_member"			=> $login->id_member,				
				"id_tier"			=> $login->id_tier,	
				"id_chat"			=> $login->id_chat,					
				"nama"				=> $login->nama,							
				"surname"			=> $login->surname,												
				"email"				=> $login->email,
				"phone"				=> $login->phone,																	
				"fcm_token"			=> $login->gcm_token,														
				"status"			=> $login->status,							
				"status_name"		=> $status_name,
				"photo"				=> !empty($login->photo) ? base_url('uploads/members/'.$login->photo) : null,				
				"current_pass"		=> $this->converter->decode($login->pass),
				"tgl_reg"			=> date('d-M-Y', strtotime($login->tgl_reg))
			];
			$this->set_response([
				'err_code' => '00',
				'err_msg' => 'Ok',
				'profile_info' => $details
			], REST_Controller::HTTP_OK);
		}else{
			$result = [
				'err_code'	=> '04',
				'err_msg'	=> 'Login failed'
			];
			$this->set_response($result, REST_Controller::HTTP_OK);
		}

	}
	
	function add_address_post(){
		$param = $this->input->post();
		$id_address = isset($param['id_address']) ? (int)$param['id_address'] : 0;		
		$tgl_reg = date('Y-m-d H:i:s');
		$simpan = array();
		$nama_provinsi = '';
		$nama_city = '';
		foreach($param as $key=>$val){
			$simpan += array( $key => $val);
			if($key == 'id_provinsi'){
				$this->load->library('send_api');
				$url = URL_ONGKIR.'city';
				$auth = KEY_ONGKIR;
				$province = (int)$this->input->post('id_provinsi');
				if($province > 0){
					$url .='?province='.$val;
				}
				$dt_provinces = '';
				$dt = array();
				$provinces = $this->send_api->send_data($url, '', $auth,'', 'GET');
				$dt_provinces = json_decode($provinces);
				$_id_city = (int)$param['id_city'];
				if(!empty($dt_provinces->rajaongkir->results)){
					foreach($dt_provinces->rajaongkir->results as $pr){
						$nama_provinsi = $pr->province;
						if($_id_city == (int)$pr->city_id) $nama_city = $pr->city_name;
					}
				}
			}
		}
		$simpan += array( 'nama_provinsi' => $nama_provinsi, 'nama_city'=>$nama_city);
		if($id_address > 0){
			unset($simpan['id_member']);
			unset($simpan['id_address']);
			$this->access->updatetable('alamat_pengiriman',$simpan, array("id_address"=>$id_address));			
			$save = $id_address;
		}else{
			$simpan +=array('created_at' => $tgl_reg);
			$save = $this->access->inserttable('alamat_pengiriman',$simpan);
			
		}
		if($save){
			$this->set_response([
				'err_code' => '00',
				'err_msg' => 'Ok',
				'id_address'	=> $save
			], REST_Controller::HTTP_OK);
		}else{
			$this->set_response([
				'err_code' => '03',
				'err_msg' => 'Insert has problem'
			], REST_Controller::HTTP_OK);
		}
	}
	
	public function del_address_post(){
		$tgl = date('Y-m-d H:i:s');
		$param = $this->input->post();
		$id_address = isset($param['id_address']) ? (int)$param['id_address'] : 0;	
		$where = array(
			'id_address' => $id_address
		);
		$data = array(
			'deleted_at'	=> $tgl
		);
		$this->access->updatetable('alamat_pengiriman', $data, $where);
		$this->set_response([
				'err_code' => '00',
				'err_msg' => 'Ok'				
			], REST_Controller::HTTP_OK);
	}
	
	public function get_address_post(){
		$tgl = date('Y-m-d H:i:s');
		$param = $this->input->post();
		$id_address = isset($param['id_address']) ? (int)$param['id_address'] : 0;	
		$id_member = isset($param['id_member']) ? (int)$param['id_member'] : 0;	
		$login = '';
		$dt = array();
		$select = array();
		if($id_address > 0){
			$login = $this->access->readtable('alamat_pengiriman',$select,array('alamat_pengiriman.id_address'=>$id_address, 'alamat_pengiriman.deleted_at'=>null))->row();
			$dt = [
				"id_address"	=> $login->id_address,
				"id_country"	=> $login->id_country,
				"id_provinsi"	=> $login->id_provinsi,
				"id_city"		=> $login->id_city,
				"id_member"		=> $login->id_member,							
				"kode_pos"		=> $login->kode_pos,							
				"alamat"		=> $login->alamat,														
				"provinsi"		=> $login->nama_provinsi,														
				"nama_city"		=> $login->nama_city,														
				"phone"			=> $login->phone,							
				"nama_penerima"	=> $login->nama_penerima,											
				"surname"		=> $login->surname,											
				"nama_alamat"		=> $login->nama_alamat,											
											
				
			];
		}
		if($id_member > 0){
			$login = $this->access->readtable('alamat_pengiriman',$select,array('alamat_pengiriman.id_member'=>$id_member, 'alamat_pengiriman.deleted_at'=>null))->result_array();
			if(!empty($login)){
				foreach($login as $l){
					$dt[] = array(
						"id_address"	=> $l['id_address'],
						"id_country"	=> $l['id_country'],
						"id_provinsi"	=> $l['id_provinsi'],
						"id_city"		=> $l['id_city'],
						"id_member"		=> $l['id_member'],										
						"kode_pos"		=> $l['kode_pos'],
						"alamat"		=> $l['alamat'],
						"phone"			=> $l['phone'],
						"nama_penerima"	=> $l['nama_penerima'],
						"nama_alamat"	=> $l['nama_alamat'],
						"surname"		=> $l['surname'],
						"provinsi"		=> $l['nama_provinsi'],
						"nama_city"		=> $l['nama_city']
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
	
	function add_wishlist_post(){
		$param = $this->input->post();
		$id_member = isset($param['id_member']) ? (int)$param['id_member'] : '';
		$id_product = isset($param['id_product']) ? (int)$param['id_product'] : '';
		if($id_member <= 0){
			$result = array( 'err_code'	=> '01',
                             'err_msg'	=> 'id_member is required' );
			$this->set_response($result, REST_Controller::HTTP_OK);
			return false;
		}
		if($id_product <= 0){
			$result = array( 'err_code'	=> '01',
                             'err_msg'	=> 'id_product is required' );
			$this->set_response($result, REST_Controller::HTTP_OK);
			return false;
		}
		$chk_favorite = $this->access->readtable('list_wishlist','',array('id_member'=>$id_member, 'id_product'=> $id_product))->row();
		$simpan = array(
			'id_member'		=> $id_member,
			'id_product'	=> $id_product
		);
		if(count($chk_favorite) > 0){
			$this->access->deletetable('list_wishlist',$simpan);
		}
		$simpan += array(
			'created_at'	=> date('Y-m-d H:i:s')
		);
		$this->access->inserttable('list_wishlist',$simpan);
		$chk_favorite = $this->access->readtable('list_wishlist','',$simpan)->row();
		if(count($chk_favorite) > 0){
			$this->set_response([
                'err_code' => '00',
                'message' => 'Ok'
            ], REST_Controller::HTTP_OK);
		}else{
			$this->set_response([
				'err_code' => '03',
				'message' => 'Insert has problem'
			], REST_Controller::HTTP_OK);
		}
	}
	
	function del_wishlist_post(){
		$param = $this->input->post();
		$id_member = isset($param['id_member']) ? (int)$param['id_member'] : 0;
		$id_product = isset($param['id_product']) ? (int)$param['id_product'] : 0;
		if($id_member <= 0){
			$result = array( 'err_code'	=> '01',
                             'err_msg'	=> 'id_member is required' );
			$this->set_response($result, REST_Controller::HTTP_OK);
			return false;
		}
		if($id_product <= 0){
			$result = array( 'err_code'	=> '01',
                             'err_msg'	=> 'id_product is required' );
			$this->set_response($result, REST_Controller::HTTP_OK);
			return false;
		}
		$simpan = array();
		$simpan = array(
			'id_member'		=> $id_member,
			'id_product'	=> $id_product
		);		
		$chk_favorite = $this->access->readtable('list_wishlist','',$simpan)->row();
		if(count($chk_favorite) > 0){
			$this->access->deletetable('list_wishlist',$simpan);
			$this->set_response([
				'err_code' => '00',
				'message' => 'Ok'
			], REST_Controller::HTTP_OK);
		}else{
			$this->set_response([
                'err_code' => '04',
                'message' => 'Data not be found'
            ], REST_Controller::HTTP_OK);
		}		
	}
	
	function get_wishlist_post(){
		$param = $this->input->post();
		$id_member = isset($param['id_member']) ? $param['id_member'] : '';	
		$keyword = isset($param['keyword']) ? $param['keyword'] : '';
		if($id_member <= 0){
			$result = array( 'err_code'	=> '01',
                             'err_msg'	=> 'id_member is required' );
			$this->set_response($result, REST_Controller::HTTP_OK);
			return false;
		}
		$_like = array();
		$or_like = array();
		
		if(!empty($keyword)){
			$keyword = $this->db->escape_str($keyword);
			$_like = array('product.nama_barang'=>$keyword);
			// $or_like = array('product.deskripsi'=>$keyword);
		}
		$sort = array('list_wishlist.created_at','DESC');
		$limit = isset($param['limit']) ? (int)$param['limit'] : 0;
		$select = array('product.*','kategori.nama_kategori','list_wishlist.created_at','sub_kategori.nama_sub','sub_sub_kategori.nama_sub_s');
		$products = $this->access->readtable('list_wishlist',$select,array('list_wishlist.id_member'=>$id_member),array('product'=>'product.id_product = list_wishlist.id_product','kategori'=> 'kategori.id_kategori = product.id_kategori','sub_kategori'=> 'sub_kategori.id_sub = product.id_sub','sub_sub_kategori'=> 'sub_sub_kategori.id_sub_s = product.id_sub_sub'), $limit, $sort,'LEFT','',$_like)->result_array();
		
		$dt = array();		
		$path = '';
		if(!empty($products)){
			foreach($products as $m){
				$path = '';
				$status_delete = 'Deleted';
				if($m['deleted_at'] == null){
					$status_delete = 'Available';
				}
				$path = !empty($m['img']) ? base_url('uploads/products/'.$m['img']) : null;
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
					'is_mywishlist'		=> 1,
					'status'			=> $status_delete
				);
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
                'message' => 'Data not be found'
            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
        }
	}
	
	function click_recent_post(){
		$param = $this->input->post();
		$id_member = isset($param['id_member']) ? $param['id_member'] : '';
		$id_product = isset($param['id_product']) ? $param['id_product'] : '';
		$chk_favorite = $this->access->readtable('recent_v','',array('id_member'=>$id_member, 'id_product'=> $id_product))->row();
		$simpan = array(
			'id_member'		=> $id_member,
			'id_product'	=> $id_product
		);
		if(count($chk_favorite) > 0){
			$this->access->deletetable('recent_v',$simpan);
		}
		$simpan += array(
			'created_at'	=> date('Y-m-d H:i:s')
		);
		$this->access->inserttable('recent_v',$simpan);
		$chk_favorite = $this->access->readtable('recent_v','',$simpan)->row();
		if(count($chk_favorite) > 0){
			$this->set_response([
                'err_code' => '00',
                'message' => 'Ok'
            ], REST_Controller::HTTP_OK);
		}else{
			$this->set_response([
				'err_code' => '03',
				'message' => 'Insert has problem'
			], REST_Controller::HTTP_OK);
		}
	}
	
	function get_recent_post(){
		$param = $this->input->post();
		$id_member = isset($param['id_member']) ? $param['id_member'] : '';
		
		$sort = array('recent_v.created_at','DESC');
		$limit = isset($param['limit']) ? (int)$param['limit'] : 0;
		$select = array('product.*','kategori.nama_kategori','recent_v.created_at','sub_kategori.nama_sub','sub_sub_kategori.nama_sub_s');
		$products = $this->access->readtable('recent_v',$select,array('recent_v.id_member'=>$id_member),array('product'=>'product.id_product = recent_v.id_product','kategori'=> 'kategori.id_kategori = product.id_kategori','sub_kategori'=> 'sub_kategori.id_sub = product.id_sub','sub_sub_kategori'=> 'sub_sub_kategori.id_sub_s = product.id_sub_sub'), $limit, $sort,'LEFT')->result_array();
		
		$is_mywishlist = 0;
		$dt = array();		
		$wishlist = array();		
		$path = '';
		$chk_wishlist = '';
		$chk_wishlist = $this->access->readtable('list_wishlist','',array('id_member'=>$id_member))->result_array();
		if(!empty($chk_wishlist)){
			foreach($chk_wishlist as $cw){
				array_push($wishlist, $cw['id_product']);
			}
		}
		if(!empty($products)){
			foreach($products as $m){
				$path = '';
				$status_delete = 'Deleted';
				if($m['deleted_at'] == null){
					$status_delete = 'Available';
				}
				$path = !empty($m['img']) ? base_url('uploads/products/'.$m['img']) : null;
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
					'is_mywishlist'		=> $is_mywishlist,
					'last_seen_date'	=> $m['created_at'],
					'status'			=> $status_delete
				);
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
                'message' => 'Data not be found'
            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
        }
	}
	
	function history_transaksi_post(){
		$param = $this->input->post();
		$id_member = isset($param['id_member']) ? $param['id_member'] : '';
		$from = isset($param['start_date']) ? $param['start_date'] : '';
		$to = isset($param['end_date']) ? $param['end_date'] : '';
		$status = isset($param['status']) ? (int)$param['status'] : -1;
		$transaksi = '';
		$tgl_now = date('Y-m-d H:i:s');
		$where = array('status'=>0,'id_member'=>$id_member,'date_format(expired_payment, "%Y-%m-%d %H:%i") <= '=>$tgl_now);
		$this->access->updatetable('transaksi', array('status'=>7), $where);	
		$sort = array('transaksi.id_transaksi','DESC');
		$where = array();
		if($status == 1) $status = 3;
		$where = array('transaksi.id_member'=>$id_member);
		if($status >= 0) $where += array('transaksi.status' => $status);
		if(!empty($from) && !empty($to)){
			$where += array('date_format(transaksi.created_at, "%Y-%m-%d") >= ' => date('Y-m-d', strtotime($from)), 'date_format(transaksi.created_at, "%Y-%m-%d") <=' => date('Y-m-d', strtotime($to)));
		}
		if($id_member > 0){
			$transaksi = $this->access->readtable('transaksi','',$where,'','',$sort)->result_array();
			
		}else{
			$msg = 'Id member is required';
			$err_code = '05';
		}
		
		$dt = array();		

		$status_name = array(
			'0' =>'Menungu Pembayaran', 
			'1' =>'Payment Complete/Pesanan diproses', 
			'2' =>'Reject',
			'3' =>'Approve', 
			'4' =>'Pesanan Dikirim',
			'5' =>'Sampai Tujuan',
			'6' =>'Selesai',
			'7' =>'Cancel Payment',
			'8' =>'Complain',
			'9' =>'Cancel by Customer',
		);
		if(!empty($transaksi)){
			foreach($transaksi as $t){
				
				$requestURL == '';
				$no_va == '';
				$valid_date == '';
				$valid_time == '';
				$bank_name == '';
				$url_payment = null;
				if($t['status'] == 0 && ($t['payment'] == 4)){
					$url_payment = $transaksi->url_payment;
				}
				// $_dt = array();
				$dt[] = array(
					'id_transaksi'		=> $t['id_transaksi'],									
					'id_member'			=> $t['id_member'],					
					'id_tier'			=> $t['id_tier'],					
					'nama_member'		=> $t['nama_member'],
					'email_member'		=> $t['email_member'],
					'phone_member'		=> $t['phone_member'],
					'payment'			=> $t['payment'],
					'payment_name'		=> $t['payment_name'],
					'diskon_tier'		=> $t['diskon_tier'],
					'id_bank'			=> $t['id_bank'],	
					'id_myvoucher'		=> $t['id_myvoucher'],
					'id_voucher'		=> $t['id_voucher'],
					'kode_voucher'		=> $t['kode_voucher'],
					'diskon_voucher'	=> $t['pot_voucher'],
					'total'				=> $t['ttl_all'],
					'id_address'		=> $t['id_address'],					
					'id_country'		=> $t['id_country'],					
					'id_provinsi'		=> $t['id_provinsi'],					
					'id_city'			=> $t['id_city'],					
					'provinsi'			=> $t['provinsi'],					
					'nama_city'			=> $t['nama_city'],					
					'kode_pos'			=> $t['kode_pos'],
					'alamat_penerima'	=> $t['alamat_penerima'],
					'phone_penerima'	=> $t['phone_penerima'],
					'nama_penerima'		=> $t['nama_penerima'],
					'nama_alamat'		=> $t['nama_alamat'],
					'status'			=> $t['status'],
					'status_name'		=> $status_name[$t['status']],
					'status_date'		=> $t['appr_rej_date'] != '' ? $t['appr_rej_date'] : $t['create_at'],					
					'ttl_weight'		=> $t['ttl_weight'],					
					'ongkir'			=> $t['ongkir'],					
					'pengiriman'		=> $t['pengiriman'],
					'nama_pengiriman'	=> $t['nama_pengiriman'],
					'kurir_code'		=> $t['kurir_code'],
					'kurir_service'		=> $t['kurir_service'],
					'nama_bank'			=> $t['nama_bank'],
					'no_rek'			=> $t['no_rek'],
					'holder_name'		=> $t['holder_name'],
					'url_payment'		=> $url_payment,
					'tgl_kirim'			=> $t['tgl_kirim'],
					'expired_payment'	=> $t['expired_payment'],
					'tgl_transaksi'		=> $t['created_at']
					
				);
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
                'message' => 'Data not be found'
            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
        }
	}
	
	function transaksi_detail_post(){
		$param = $this->input->post();
		$id_transaksi = isset($param['id_transaksi']) ? (int)$param['id_transaksi'] : '';
		$transaksi = $this->access->readtable('transaksi','', array('transaksi.id_transaksi' => $id_transaksi))->row();
		$transaksi_detail = $this->access->readtable('transaksi_detail','',array('id_trans'=>$id_transaksi))->result_array();
		
		$dt = array();
		$_dt = array();
		if(!empty($transaksi_detail)){
			foreach($transaksi_detail as $td){	
				unset($td['return_stock']);
				unset($td['updated_at']);
				$_dt[] = $td;
			}
		}
		$status_name = array(
			'0' =>'Menungu Pembayaran', 
			'1' =>'Payment Complete/Pesanan diproses', 
			'2' =>'Reject',
			'3' =>'Approve', 
			'4' =>'Pesanan Dikirim',
			'5' =>'Sampai Tujuan',
			'6' =>'Selesai',
			'7' =>'Cancel Payment',
			'8' =>'Complain',
			'9' =>'Cancel by Customer',
		);
		$url_payment = null;
		if($transaksi->status == 0 && ($transaksi->payment ==4)){
			$url_payment = $transaksi->url_payment;
		}
		$dt = array(			
			"id_transaksi"		=> $id_transaksi,
			"id_address"		=> $transaksi->id_address,
			"id_member"			=> $transaksi->id_member,
			"id_tier"			=> $transaksi->id_tier,			
			"id_country"		=> $transaksi->id_country,							
			"id_provinsi"		=> $transaksi->id_provinsi,							
			"id_city"			=> $transaksi->id_city,
			"id_bank"			=> $transaksi->id_bank,
			"no_rek"			=> $transaksi->no_rek,
			"holder_name"		=> $transaksi->holder_name,
			"nama_bank"			=> $transaksi->nama_bank,			
			"nama_member"		=> $transaksi->nama_member,
			"email_member"		=> $transaksi->email_member,
			"phone_member"		=> $transaksi->phone_member,
			"payment"			=> $transaksi->payment,
			"payment_name"		=> $transaksi->payment_name,			
			"diskon_tier"		=> $transaksi->diskon_tier,	
			"nama_provinsi"		=> $transaksi->nama_provinsi,
			"nama_city"			=> $transaksi->nama_city,
			"kode_pos"			=> $transaksi->kode_pos,							
			"alamat_penerima"	=> $transaksi->alamat_penerima,														
			"phone_penerima"	=> $transaksi->phone_penerima,							
			"nama_penerima"		=> $transaksi->nama_penerima,											
			"nama_alamat"		=> $transaksi->nama_alamat,			
			"ttl_weight"		=> $transaksi->ttl_weight,			
			"ongkir"			=> $transaksi->ongkir,			
			"pengiriman"		=> $transaksi->pengiriman,			
			"nama_pengiriman"	=> $transaksi->nama_pengiriman,			
			"kurir_code"		=> $transaksi->kurir_code,			
			"kurir_service"		=> $transaksi->kurir_service,
			"id_myvoucher"		=> $transaksi->id_myvoucher,	
			"id_voucher"		=> $transaksi->id_voucher,	
			"kode_voucher"		=> $transaksi->kode_voucher,	
			"diskon_voucher"	=> $transaksi->pot_voucher,	
			"total"				=> $transaksi->ttl_all,	
			"status"			=> $transaksi->status,			
			"status_name"		=> $status_name[$transaksi->status],			
			"tgl_kirim"			=> $transaksi->tgl_kirim,			
			"tanggal"			=> $transaksi->created_at,			
			"expired_payment"	=> $transaksi->expired_payment,			
			'url_payment'		=> $url_payment,
			"transaksi_detail"	=> $_dt
		);
		if(!empty($dt)){
			$this->set_response([
				'err_code' 	=> '00',
				'err_msg' 	=> 'Ok',
				'data' 		=> $dt
			], REST_Controller::HTTP_OK);
		}else{
			$result = [
				'err_code'	=> '04',
				'err_msg'	=> 'Login failed'
			];
			$this->set_response($result, REST_Controller::HTTP_OK);
		}
	}

	function forgot_post(){

		$result = array();
		$nama = '';
		$new_pass = '';
		$save = 0;
		$param = $this->input->post();
		$email = isset($param['email']) ? $param['email'] : '';

		if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$result = [
				'err_code'	=> '06',
				'err_msg'	=> 'Email invalid format'
			];
			$this->set_response($result, REST_Controller::HTTP_OK);
			return false;
		}
		$chk_email = $this->access->readtable('members','',array('email'=>$email,'deleted_at'=>null))->row();
		$ketemu = count($chk_email);
		if($ketemu > 0){
			$new_pass = $this->converter->random(8);
			$data = array("pass" => $this->converter->encode($new_pass));
			$this->access->updatetable('members',$data, array("id_member" => $chk_email->id_member));
			$save = $email;
		}else{
			$result = [
				'err_code'	=> '07',
				'err_msg'	=> 'Email Not Registered'
			];
			$this->set_response($result, REST_Controller::HTTP_OK);
			return false;
		}

		if($save == $email){

			$opsi_val_arr = $this->sm->get_key_val();
			foreach ($opsi_val_arr as $key => $value){
				$out[$key] = $value;
			}

			$nama = $chk_email->nama;

			// $this->load->library('email');
			$this->load->library('send_notif');
			$from = $out['email'];
			$pass = $out['pass'];
			$to = $email;
			$subject = $out['subj_email_forgot'];
			$content_member = $out['content_forgotPass'];

			$content = str_replace('[#name#]', $nama, $content_member);
			$content = str_replace('[#new_pass#]', $new_pass, $content);
			$content = str_replace('[#email#]', $email, $content);
			$send = $this->send_notif->send_email($from,$pass, $to,$subject, $content);
			$result = [
				'err_code'	=> '00',
				'err_msg'	=> 'OK, New password was send to your email'
			];
			
			
			$this->set_response($result, REST_Controller::HTTP_OK);
		}else{

			$result = [
				'err_code'	=> '05',
				'err_msg'	=> 'Insert has problem'
			];
			$this->set_response($result, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);

		}

	}
	
	function upd_status_post(){
		$this->load->library('send_notif');
		$param = $this->input->post();		
		$id_transaksi = isset($param['id_transaksi']) ? (int)$param['id_transaksi'] : 0;
		$status = isset($param['status']) ? (int)$param['status'] : 6;
		$dt_transaksi = $this->access->readtable('transaksi', '', array('transaksi.id_transaksi' => $id_transaksi))->row();		
		$id_member = $dt_transaksi->id_member;
		$id_principle = $dt_transaksi->id_principle;
		$ids = array();
		if($status == 2){
			$pesan_fcm = 'Pesanan direject';
			$ttl = $dt_transaksi->ttl_all;
			$dt_members = $this->access->readtable('members', '', array('id_member' => $id_member))->row();
			$transaksi_detail = $this->access->readtable('transaksi_detail', '', array('id_trans' => $id_transaksi))->result_array();
			$ids = array($dt_members->gcm_token);
			if(!empty($transaksi_detail)){
				foreach($transaksi_detail as $td){
					$id_produk = 0;
					$jml = 0;
					$id_produk = (int)$td['id_product'];
					$jml = (int)$td['jml'];
					$id_whs = (int)$td['id_whs'];
					$dt_product = '';					
					$stok = 0;
					if($jml > 0 && $id_produk > 0){
						$dt_product = $this->access->readtable('stok', '', array('id_product' => $id_produk,'deleted_at'=>null,'id_wh'=>$id_whs))->row();
						$stok = (int)$dt_product->stok > 0 ? (int)$dt_product->stok : 0;
						$stok = $stok + $jml;
						$this->access->updatetable('stok', array('stok' => $stok), array('id_product' => $id_produk,'deleted_at'=>null,'id_wh'=>$id_whs));
					}
				}
			}
			$sisa_credit = 0;
			$use_credit = 0;
			$sisa_credit = $dt_members->sisa_credit + $ttl;
			$use_credit = $dt_members->use_credit - $ttl;
			$this->access->updatetable('members',array('sisa_credit'=>$sisa_credit,'use_credit' => $use_credit), array('id_member' => $id_member));	
			if(!empty($ids)){
				$data_fcm = array(
					'id_notif'		=> $id_transaksi,
					'title'			=> 'Indolinen',
					'status'		=> $status,	
					'message' 		=> $pesan_fcm,
					'notif_type' 	=> '2'
				);
				$notif_fcm = array(
					'body'			=> $pesan_fcm,
					'title'			=> 'Indolinen',
					'badge'			=> '1',
					'sound'			=> 'Default'
				);	
				$send_fcms = $this->send_notif->send_fcm($data_fcm, $notif_fcm, $ids);	
				$dtt =array();
				$dtt =array(
					'id_member'		=> $id_member,
					'id_transaksi'	=> $id_transaksi,
					'fcm_token'		=> $dt_members->gcm_token,
					'created_at'	=> $tgl,
					'type'			=> 2
				);
				$this->access->inserttable('history_notif', $dtt); 
			}
		}
		$where = array(
			'id_transaksi' => $id_transaksi
		);
		$simpan = array(
			'status'		=> $status
		);
		$this->access->updatetable('transaksi', $simpan, $where);
		if($status == 6) $this->access->updatetable('id_trans_auto', array('deleted_at'=> date('Y-m-d H:i:s')), array('id_trans'=>$id_transaksi));
		$this->set_response([
			'err_code' => '00',
			'message' => 'Ok'
		], REST_Controller::HTTP_OK);
	}
	
	function send_chat_post(){
		$tgl = date('Y-m-d H:i:s');
		$datas = array();
		$master_chat = '';
		$path = '';
		$param = $this->input->post();
		$user_id_form = isset($param['id_member']) ? (int)$param['id_member'] : 0;
		$id_product = isset($param['id_product']) ? (int)$param['id_product'] : 0;
		$id_chat = isset($param['id_chat']) ? (int)$param['id_chat'] : 0;
		$user_id_to = 'admin';
		$pesan = isset($param['pesan']) ? $param['pesan'] : '';		
		
		$select = array('product.nama_barang','img');
		$where = array('product.deleted_at'=>null,'product.id_product'=> $id_product);
		$products = $this->access->readtable('product',$select,$where)->row();
		$path = !empty($products->img) ? base_url('uploads/products/'.$products->img) : '';
		if($id_chat > 0){
			$datas = array('id_member_from'=>$user_id_form,'id_member_to'=>$user_id_to,'pesan'=>$pesan,'status_from'=>0,'status_to'=>1);
			$this->access->updatetable('master_chat', $datas, array('id_chat'=>$id_chat));
			$this->access->updatetable('chat_admin', array('status'=>1,'pesan'=>$pesan),array('id_chat'=>$id_chat));
			$id_chat = $id_chat;
		}else{
			$datas = array('id_member_from'=>$user_id_form,'id_member_to'=>$user_id_to,'pesan'=>$pesan,'status_from'=>0,'status_to'=>1,'created_at'=>$tgl);
			$save_chat = $this->access->inserttable('master_chat', $datas);
			$this->access->inserttable('chat_admin', array('status'=>1,'pesan'=>$pesan,'id_chat'=>$save_chat,'id_member'=>$user_id_form,'created_at'=>$tgl));	
			$id_chat = $save_chat;
			$this->access->updatetable('members',array('id_chat'=>$id_chat), array("id_member"=>$user_id_form));			
		}
		
		$data = array(	
			'id_member_from'	=> $user_id_form,
			'id_member_to'	=> $user_id_to,
			'pesan'			=> $pesan,
			'id_product'	=> $id_product,
			'nama_item'		=> $products->nama_barang,
			'img_item'		=> $path,
			'created_at'	=> $tgl,			
			'status_from'	=> 0,
			'status_to'		=> 1,
			'id_chat'		=> $id_chat			
		);
		$save = $this->access->inserttable('chat_detail', $data);
		if($save){
			$this->set_response([
				'err_code' 	=> '00',
				'message' 	=> 'Ok',
				'data'		=> $data,
			], REST_Controller::HTTP_OK);
		}else{
			$this->set_response([
				'err_code' => '03',
				'message' => 'Insert has problem'
			], REST_Controller::HTTP_OK);
		}
	}	
	
	function list_chat_post(){
		$param = $this->input->post();
		$id_chat = isset($param['id_chat']) ? (int)$param['id_chat'] : 0;		
		$id_member = isset($param['id_member']) ? (int)$param['id_member'] : 0;		
		$dt = array();		
		$chats = '';		
		$this->access->updatetable('chat_detail',array('status_to'=>0), array("id_chat"=>$id_chat,'id_member_to'=>$id_member));
		$this->access->updatetable('master_chat',array('status_to'=>0), array("id_chat"=>$id_chat,'id_member_to'=>$id_member));
		$chats = $this->access->readtable('chat_detail','',array('id_chat'=>$id_chat))->result_array();
		
		if(!empty($chats)){
			foreach($chats as $c){			
				$dt[] = array(
					'id_member_from'	=> $c['id_member_from'],
					'id_member_to'		=> $c['id_member_to'],					
					'pesan'				=> $c['pesan'],					
					'id_product'		=> $c['id_product'],					
					'nama_item'			=> $c['nama_item'],					
					'img_item'			=> $c['img_item'],					
					'status_from'		=> $c['status_from'],					
					'status_to'			=> $c['status_to'],					
					'created_at'		=> $c['created_at'],					
					'id_chat'			=> $c['id_chat'],				
				);
			}
		}
		
		if (!empty($dt)){
            $res = array(
				'err_code' 	=> '00',
                'message' 	=> 'ok',
                'data' 		=> $dt,
			);
            $this->set_response($res, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }else{
            $this->set_response([
                'err_code' => '04',
                'message' => 'Data not be found'
            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
        }	
	}	
	
	function fcmid_post(){
		$result = array();	
		
		$param = $this->input->post();
		$user_id = isset($param['id_member']) ? $param['id_member'] : '';
		$gcm_id = isset($param['fcm_token']) ? $param['fcm_token'] : '';		
		$device = isset($param['device']) ? $param['device'] : 1;		
		//if($device == 1){
		//	$device = "ios";
		//}else if($device == 2){
		//	$device = "android";
		////}else{
			$device = $device;
		//}
		$simpan = array(
			'device'	=> $device,
			'gcm_token'	=> $gcm_id
		);
		if(!empty($user_id) && !empty($gcm_id)){
			$this->access->updatetable('members',$simpan, array("id_member"=>$user_id));
			$result = [
				'err_code'	=> '00',
				'err_msg'	=> 'OK'
			];					
		}else{
			$result = [
				'err_code'	=> '01',
                'err_msg'	=> 'Param id_member or gcm_token can\'t empty.' 
			];			
		}
		$this->set_response($result, REST_Controller::HTTP_OK);		
	}
	
	function history_complain_post(){
		$param = $this->input->post();
		$id_member = isset($param['id_member']) ? $param['id_member'] : '';
		$from = isset($param['start_date']) ? $param['start_date'] : '';
		$to = isset($param['end_date']) ? $param['end_date'] : '';
		$type = isset($param['type']) ? (int)$param['type'] : 0;
		$transaksi = '';
		$sort = array('transaksi.id_transaksi','DESC');
		$where = array();
		
		$where = array('transaksi.id_member'=>$id_member,'transaksi.status'=>8);
		if($type > 0) $where += array('transaksi.complain_type' => $type);
		if(!empty($from) && !empty($to)){
			$where += array('transaksi.create_at >= ' => date('Y-m-d', strtotime($from)), 'transaksi.create_at <=' => date('Y-m-d', strtotime($to)));
		}
		if($id_member > 0){
			$transaksi = $this->access->readtable('transaksi','',$where,'','',$sort)->result_array();
			
		}else{
			$msg = 'Id member is required';
			$err_code = '05';
		}
		
		$dt = array();		
		$status_name = array(			 
			'1' =>'On Process', 
			'3' =>'Reject',
			'2' =>'Approve'			
		);
		if(!empty($transaksi)){
			foreach($transaksi as $t){			
				$dt[] = array(
					'id_transaksi'			=> $t['id_transaksi'],									
					'id_member'				=> $t['id_member'],										
					'nama_member'			=> $t['nama_member'],
					'status_complain'		=> $t['status_complain'],										
					'status_complain_name'	=> $status_name[$t['status_complain']],										
					'delivery_date'			=> $t['tgl_kirim'],					
					'complain_date'			=> $t['complain_date']					
				);
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
                'message' => 'Data not be found'
            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
        }
	}
	
	function complain_detail_post(){
		$param = $this->input->post();
		$transaksi = '';
		$id_transaksi = isset($param['id_transaksi']) ? (int)$param['id_transaksi'] : '';
		$transaksi = $this->access->readtable('transaksi','', array('transaksi.id_transaksi' => $id_transaksi,'status'=>8))->row();	
		
		$dt = array();		
		if(!empty($transaksi)){
			$status_name = array(			 
				'1' =>'On Process', 
				'3' =>'Reject',
				'2' =>'Approve'			
			);
			$dt = array(			
				"id_transaksi"		=> $id_transaksi,			
				"id_member"			=> $transaksi->id_member,						
				"nama_member"		=> $transaksi->nama_member,
				"complain_type"		=> $transaksi->complain_type,
				"good_details"		=> $transaksi->good_details,
				"refund_reason"		=> $transaksi->refund_reason,
				"status_complain"	=> $transaksi->status_complain,						
				"status_complain"	=> $status_name[$transaksi->status_complain],						
				"signature"			=> !empty($transaksi->signature) ? base_url('uploads/complain/'.$transaksi->signature) : null,					
				"tgl_kirim"			=> $transaksi->tgl_kirim,			
				"complain_date"		=> $transaksi->complain_date,			
				"tanggal"			=> $transaksi->created_at,			
			);
			if($transaksi->complain_type == 1){
				$dt += array(
					'refund_amount'		=> $transaksi->refund_amount,
					'bank_name'			=> $transaksi->address_bn,
					'account_holder'	=> $transaksi->acc_holder_cp,
					'account_no'		=> $transaksi->acc_no_hp,
				);
			}
			if($transaksi->complain_type == 2){
				$dt += array(				
					'address'			=> $transaksi->address_bn,
					'contact_person'	=> $transaksi->acc_holder_cp,
					'phone_number'		=> $transaksi->acc_no_hp,
				);
			}
			$this->set_response([
				'err_code' 	=> '00',
				'err_msg' 	=> 'Ok',
				'data' 		=> $dt
			], REST_Controller::HTTP_OK);
		}else{
			$result = [
				'err_code'	=> '04',
				'err_msg'	=> 'not found'
			];
			$this->set_response($result, REST_Controller::HTTP_OK);
		}
	}
	
}
