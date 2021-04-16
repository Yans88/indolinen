<?php defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Master extends REST_Controller {

    function __construct(){        
        parent::__construct();	
		$this->load->library('send_notif');	
		$this->load->model('Api_m');	
		$this->load->model('Access','access',true);	
		$this->load->library('send_api');		
    }	
	
	public function banners_get(){
        $id = $this->get('id');	
		$id = (int)$id;
		$_banner = '';
		$_banner = $this->access->readtable('banner','',array('deleted_at'=>null))->result_array();		
		$dt = array();
		$path = '';
		$dataku = array();		
		if(!empty($_banner)){
			foreach($_banner as $k){
				$path = !empty($k['img']) ? base_url('uploads/banner/'.$k['img']) : base_url('uploads/no_photo.jpg');
				$dt[] = array(
					'id_banner'		=> $k['id_banner'],						
					'image'			=> $path
				);
			}
			$dataku = array(
				'err_msg' 	=> 'ok',
				'err_code' 	=> '00',
				'data' 		=> $dt	
			);
		}
				
		if (!empty($dataku)){
            $this->set_response($dataku, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }else{
            $this->set_response([
                'err_code' => '04',
                'err_msg' => 'Data not be found'
            ], REST_Controller::HTTP_OK);
        }
    }
	
	function tc_get(){
		$term_condition = $this->Api_m->get_key_val();
		$tc = isset($term_condition['term_condition']) ? $term_condition['term_condition'] : '';
		$tcku = array();
		$dataku = array();
		if(!empty($tc)){
			$tc = preg_replace("/<p[^>]*?>/", "", $tc);
			$tc = str_replace("</p>", "", $tc);
			//$tc = str_replace("\r\n","<br />",$tc);
			$tcku = [
					'term_condition' 	=> $tc		
			];
			$dataku = array(
				'err_msg' 	=> 'ok',
				'err_code' 	=> '00',
				'data' 		=> $tc	
			);
			$this->set_response($dataku, REST_Controller::HTTP_OK);
		}else{
			$this->set_response([
				'data' 	=> $tc,
                'err_code' => '04',
                'message' => 'Data not be found'
            ], REST_Controller::HTTP_OK);
		}
	}
	
	function policy_get(){
		$policy = $this->Api_m->get_key_val();
		$p = isset($policy['policy']) ? $policy['policy'] : '';
		$tc = array();
		$dataku = array();
		if(!empty($p)){
			$p = preg_replace("/<p[^>]*?>/", "", $p);
			$p = str_replace("</p>", "", $p);
			//$p = str_replace("\r\n","<br />",$p);
			$tc = [
					'policy' 	=> $p		
			];
			$dataku = array(
				'err_msg' 	=> 'ok',
				'err_code' 	=> '00',
				'data' 		=> $p	
			);
			$this->set_response($dataku, REST_Controller::HTTP_OK);
		}else{
			$this->set_response([
				'data' 	=> $p,
                'err_code' => '04',
                'message' => 'Data not be found'
            ], REST_Controller::HTTP_OK);
		}
	}
	
	function faq_get(){
		$faq = $this->access->readtable('faq','',array('deleted_at'=>null))->result_array();
		$dt = array();
		$dataku = array();
		$answer = '';
		$question = '';
		if(!empty($faq)){
			foreach($faq as $f){
				$answer = preg_replace("/<p[^>]*?>/", "", $f['answer']);
				$answer = str_replace("</p>", "", $answer);
				//$answer = str_replace("\r\n","<br />",$answer);
				$question = preg_replace("/<p[^>]*?>/", "", $f['question']);
				$question = str_replace("</p>", "", $question);
				//$question = str_replace("\r\n","<br />",$question);
				$dt[] = array(
					"id_faq"		=> $f['id_faq'],
					"answer"		=> $answer,
					"question"		=> $question
				);
			}
		}
		if (!empty($dt)){
			$dataku = array(
				'err_msg' 	=> 'ok',
				'err_code' 	=> '00',
				'data' 		=> $dt	
			);
            $this->set_response($dataku, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }else{
            $this->set_response([
                'err_code' => '04',
                'message' => 'Data not be found'
            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
        }
	}
	
	public function master_bank_get(){
		$_banner = '';
		$_banner = $this->access->readtable('master_bank','',array('deleted_at'=>null))->result_array();		
		$dt = array();
		$path = '';
		$dataku = array();		
		if(!empty($_banner)){
			foreach($_banner as $k){
				
				$dt[] = array(
					'id_bank'		=> $k['id_bank'],						
					'nama_bank'		=> $k['nama_bank'],
					'holder_name'	=> $k['holder_name'],
					'no_rek'		=> $k['no_rek']	
				);
			}
			$dataku = array(
				'err_msg' 	=> 'ok',
				'err_code' 	=> '00',
				'data' 		=> $dt	
			);
		}
				
		if (!empty($dataku)){
            $this->set_response($dataku, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }else{
            $this->set_response([
                'err_code' => '04',
                'err_msg' => 'Data not be found'
            ], REST_Controller::HTTP_OK);
        }
    }
	
	function disc_va_get(){
		$policy = $this->Api_m->get_key_val();
		$p = isset($policy['disc_va']) ? $policy['disc_va'] : '';
		$dataku = array();
		if(!empty($p)){
			$p = str_replace(",", "", $p);
			$p = str_replace(".", "", $p);			
			$dataku = array(
				'err_msg' 	=> 'ok',
				'err_code' 	=> '00',
				'data' 		=> $p	
			);
			$this->set_response($dataku, REST_Controller::HTTP_OK);
		}else{
			$this->set_response([
				'data' 	=> $p,
                'err_code' => '04',
                'message' => 'Data not be found'
            ], REST_Controller::HTTP_OK);
		}
	}
	
	function disc_mt_get(){
		$policy = $this->Api_m->get_key_val();
		$p = isset($policy['disc_payment']) ? $policy['disc_payment'] : '';
		$dataku = array();
		if(!empty($p)){
			$p = str_replace(",", "", $p);
			$p = str_replace(".", "", $p);			
			$dataku = array(
				'err_msg' 	=> 'ok',
				'err_code' 	=> '00',
				'data' 		=> $p	
			);
			$this->set_response($dataku, REST_Controller::HTTP_OK);
		}else{
			$this->set_response([
				'data' 	=> $p,
                'err_code' => '04',
                'message' => 'Data not be found'
            ], REST_Controller::HTTP_OK);
		}
	}
	
	function province_get(){
		$url = URL_ONGKIR.'province';
		$auth = KEY_ONGKIR;
		$dt_provinces = '';
		$dt = array();
		$provinces = $this->send_api->send_data($url, '', $auth,'', 'GET');
		$dt_provinces = json_decode($provinces);
		if(!empty($dt_provinces->rajaongkir->results)){
			foreach($dt_provinces->rajaongkir->results as $pr){
				$dt[] = array('id_provinsi' => $pr->province_id, 'nama_provinsi' => $pr->province);
			}
		}
		if (!empty($dt)){
			// error_log(serialize($dt));
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
	
	function city_get(){
		$url = URL_ONGKIR.'city';
		$auth = KEY_ONGKIR;
		$province = (int)$this->input->get('id_provinsi');
		if($province > 0){
			$url .='?province='.$province;
		}
		$dt_provinces = '';
		$dt = array();
		$provinces = $this->send_api->send_data($url, '', $auth,'', 'GET');
		$dt_provinces = json_decode($provinces);
		if(!empty($dt_provinces->rajaongkir->results)){
			foreach($dt_provinces->rajaongkir->results as $pr){
				$dt[] = array('id_provinsi'=>$province,'nama_provinsi'=>$pr->province,'id_city' => $pr->city_id, 'nama_city' => $pr->city_name,'type' => $pr->type, 'postal_code' => $pr->postal_code);
			}
		}
		if (!empty($dt)){
			// error_log(serialize($dt));
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
	
	function cek_ongkir_post(){
		$url = URL_ONGKIR.'cost';
		$auth = KEY_ONGKIR;		
		$param = $this->input->post();
		$id_city = isset($param['id_city']) ? (int)$param['id_city'] : 0;
		$weight = isset($param['weight']) ? str_replace('.','',$param['weight']) : 1000; 
		$dt_post = array(
			'origin'		=> 152,
			'destination'	=> $id_city,
			'originType'	=> 'city',
			'destinationType'	=> 'city',
			'weight'		=> (int)$weight,
			'courier'		=> 'jne:tiki:pos'
		);
		$costs = '';
		$service_name = '';
		$service = '';
		$id_service = '';
		$cek_ongkirs = $this->send_api->send_data($url, $dt_post, $auth,'', 'POST');
		$cek_ongkir = json_decode($cek_ongkirs);		
		$dt = array();
		if(!empty($cek_ongkir->rajaongkir->results)){
			$costs = $cek_ongkir->rajaongkir->results;			
			foreach($costs as $pr){
				$service = '';
				$service_name = '';
				$id_service = '';
				$service = $pr->service;				
				if($service == 'CTC'){
					$service_name = 'JNE Reg';
					$id_service = 1;
				}
				if($service == 'CTCYES'){
					$service_name = 'JNE Express';
					$id_service = 2;
				}
				
				$dt[] = array(
					'service' 				=> $service,
					'id_service_local'		=> $id_service,
					'service_local'			=> $service_name,
					'biaya' 				=> $pr->cost[0]->value,
					'etd'					=> $pr->cost[0]->etd,
					'description'			=> $pr->description
				);
			}
		}
		if (!empty($dt)){
			// error_log(serialize($dt));
            $res = array(
				'err_code' 	=> '00',
                'err_msg' 	=> 'ok',
                'data' 		=> $cek_ongkir->rajaongkir->results,
			);
            $this->set_response($res, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }else{
            $this->set_response([
                'err_code' => '04',
                'err_msg' => $cek_ongkir
            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
        }
	}

	function about_get(){
		$policy = $this->Api_m->get_key_val();
		$p = isset($policy['about_us']) ? $policy['about_us'] : '';
		$tc = array();
		$dataku = array();
		if(!empty($p)){
			$p = preg_replace("/<p[^>]*?>/", "", $p);
			$p = str_replace("</p>", "", $p);
			//$p = str_replace("\r\n","<br />",$p);
			$tc = [
					'policy' 	=> $p		
			];
			$dataku = array(
				'err_msg' 	=> 'ok',
				'err_code' 	=> '00',
				'data' 		=> $p	
			);
			$this->set_response($dataku, REST_Controller::HTTP_OK);
		}else{
			$this->set_response([
				'data' 	=> $p,
                'err_code' => '04',
                'err_msg' => 'Data not be found'
            ], REST_Controller::HTTP_OK);
		}
	}
	
	function customer_care_get(){
		$term_condition = $this->Api_m->get_key_val();
		$dt = array();
		$dt = array(
			'call_center'	=> $term_condition['call_center'],
			'message'		=> $term_condition['message'],
			'wa'			=> $term_condition['wa'],
			'email'			=> $term_condition['email_cc'],
		);
		if(!empty($dt)){			
			$dataku = array(
				'err_msg' 	=> 'ok',
				'err_code' 	=> '00',
				'data' 		=> $dt	
			);
			$this->set_response($dataku, REST_Controller::HTTP_OK);
		}else{
			$this->set_response([				
                'err_code' => '04',
                'err_msg' => 'Data not be found',
				'data' 	=> null
            ], REST_Controller::HTTP_OK);
		}
	}
	
	public function colors_get(){
		$_banner = '';
		$_banner = $this->access->readtable('colors','',array('deleted_at'=>null))->result_array();		
		$dt = array();
		$path = '';
		$dataku = array();		
		if(!empty($_banner)){
			foreach($_banner as $k){				
				$dt[] = array(
					'id_color'		=> $k['id'],						
					'color'		=> $k['color']
				);
			}
			$dataku = array(
				'err_msg' 	=> 'ok',
				'err_code' 	=> '00',
				'data' 		=> $dt	
			);
		}
				
		if (!empty($dataku)){
            $this->set_response($dataku, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }else{
            $this->set_response([
                'err_code' => '04',
                'err_msg' => 'Data not be found'
            ], REST_Controller::HTTP_OK);
        }
    }
	
	public function materials_get(){
		$_banner = '';
		$_banner = $this->access->readtable('materials','',array('deleted_at'=>null))->result_array();		
		$dt = array();
		$path = '';
		$dataku = array();		
		if(!empty($_banner)){
			foreach($_banner as $k){				
				$dt[] = array(
					'id_material'	=> $k['id'],						
					'material'		=> $k['material']
				);
			}
			$dataku = array(
				'err_msg' 	=> 'ok',
				'err_code' 	=> '00',
				'data' 		=> $dt	
			);
		}
				
		if (!empty($dataku)){
            $this->set_response($dataku, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }else{
            $this->set_response([
                'err_code' => '04',
                'err_msg' => 'Data not be found'
            ], REST_Controller::HTTP_OK);
        }
    }
	
}
