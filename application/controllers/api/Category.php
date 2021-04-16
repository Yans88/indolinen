<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';


class Category extends REST_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();       
		$this->load->model('Access','access',true);	
		$this->load->model('Setting_m','sm', true);
		// $this->load->library('converter');
    }

    public function index_get(){
        $id = $this->get('id');	
		$id = (int)$id;
		$kategori = '';
		$sort = array('nama_kategori','ASC');
		if($id > 0){
			$kategori = $this->access->readtable('kategori','',array('id_kategori'=>$id,'deleted_at'=>null),'','',$sort)->result_array();
		}else{
			$kategori = $this->access->readtable('kategori','',array('deleted_at'=>null),'','',$sort)->result_array();
		}
		$dt = array();
		$path = '';
		$path2 = '';
		if(!empty($kategori)){
			foreach($kategori as $k){
				$path = '';
				$path2 = '';
				$path = !empty($k['img']) ? base_url('uploads/kategori/'.$k['img']) : base_url('uploads/no_photo.jpg');
				$path2 = !empty($k['img2']) ? base_url('uploads/kategori/'.$k['img2']) : base_url('uploads/no_photo.jpg');
				if($k['nama_kategori'] != 'Banner'){
					$dt[] = array(
						"id_kategori"		=> $k['id_kategori'],
						"nama_kategori"		=> $k['nama_kategori'],
						'image'				=> $path,
						'image2'			=> $path2,
					);
				}
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
		
	public function index_post(){
		$param = $this->input->post();
		$id = isset($param['id_kategori']) ? $param['id_kategori'] : 0;
		$id = (int)$id;
		$sub = '';
		$sort = array('sub_kategori.nama_sub','ASC');
		$select = array('kategori.nama_kategori','sub_kategori.* ');
		if($id > 0){
			$sub = $this->access->readtable('sub_kategori',$select,array('sub_kategori.id_kategori'=>$id,'sub_kategori.deleted_at'=>null),array('kategori' => 'kategori.id_kategori = sub_kategori.id_kategori'),'',$sort, 'LEFT')->result_array();
		}else{
			$sub = $this->access->readtable('sub_kategori',$select,array('sub_kategori.deleted_at'=>null),array('kategori' => 'kategori.id_kategori = sub_kategori.id_kategori'),'',$sort, 'LEFT')->result_array();
		}
		$dt = array();
		$id_sub = array();
		$path = '';
		$field_in = '';
		$where_in = '';
		if(!empty($sub)){
			$_sort = array('sub_sub_kategori.nama_sub_s','ASC');			
			foreach($sub as $d){
				array_push($id_sub, (int)$d['id_sub']);				
			}				
			$field_in = 'sub_sub_kategori.id_sub';	
			$where_in = $id_sub;
			$ssub_kategori = $this->access->readtable('sub_sub_kategori','',array('sub_sub_kategori.deleted_at'=>null),'','',$_sort,'','','','', $field_in,$where_in)->result_array();
			$dts = array();
			if(!empty($ssub_kategori)){
				foreach($ssub_kategori as $sk){
					$path_s = '';
					$path_s = !empty($sk['img']) ? base_url('uploads/sub_kategori/'.$sk['img']) : null;
					$dts[$sk['id_sub']][] = array(
						"id_sub_s"			=> $sk['id_sub_s'],
						"id_subcategory"	=> $sk['id_sub'],
						"nama_sub_s"		=> $sk['nama_sub_s'],
						"image_sub_s"		=> $path_s
					);
				}
			}
			foreach($sub as $s){
				$path = '';
				$path = !empty($s['img']) ? base_url('uploads/sub_kategori/'.$s['img']) : null;
				$dt[] = array(
					"id_kategori"		=> $s['id_kategori'],
					"nama_kategori"		=> $s['nama_kategori'],
					"id_subcategory"	=> $s['id_sub'],
					"nama_sub"			=> $s['nama_sub'],
					"image_sub"			=> $path,
					"sub_sub_kategori"	=> !empty($dts[$s['id_sub']]) ? $dts[$s['id_sub']] : null,
				);
				
			}
		}
		if (!empty($dt)){
			$res = array(
				'err_code' 	=> '00',
                'err_msg' 	=> 'ok',
                'data' 		=> $dt,
			);
            $this->set_response($res, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }else{
            $this->set_response([
                'err_code' => '04',
                'err_msg' => 'Data not be found'
            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
        }
    }	
	
	public function ssubcategory_post(){
		$param = $this->input->post();
		$id = isset($param['id_subcategory']) ? $param['id_subcategory'] : 0;
		$id = (int)$id;
		$sub = '';
		$sort = array('sub_sub_kategori.nama_sub_s','ASC');		
		if($id > 0){
			$sub = $this->access->readtable('sub_sub_kategori','',array('sub_sub_kategori.id_sub'=>$id,'sub_sub_kategori.deleted_at'=>null),'','',$sort)->result_array();
		}else{
			$sub = $this->access->readtable('sub_sub_kategori','',array('sub_sub_kategori.deleted_at'=>null),'','',$sort)->result_array();
		}
		$dt = array();
		$path = '';
		if(!empty($sub)){
			foreach($sub as $s){
				$path = '';
				$path = !empty($s['img']) ? base_url('uploads/sub_kategori/'.$s['img']) : null;
				$dt[] = array(
					"id_sub_s"			=> $s['id_sub_s'],
					"id_subcategory"	=> $s['id_sub'],
					"nama_sub_s"			=> $s['nama_sub_s'],
					"image_sub_s"			=> $path
				);
				
			}
		}
		if (!empty($dt)){
			$res = array(
				'err_code' 	=> '00',
                'err_msg' 	=> 'ok',
                'data' 		=> $dt,
			);
            $this->set_response($res, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }else{
            $this->set_response([
                'err_code' => '04',
                'err_msg' => 'Data not be found'
            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
        }
    }	
	
	
}
