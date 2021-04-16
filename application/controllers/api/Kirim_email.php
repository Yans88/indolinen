<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Kirim_email extends REST_Controller {

    function __construct(){
        parent::__construct();
		$this->load->library('send_notif');		
		$this->load->model('Setting_m','sm',true);		
    }  
	
	function index_post(){
		$param = $this->input->post();		
		$opsi_val_arr = $this->sm->get_key_val();
		foreach ($opsi_val_arr as $key => $value){
			$out[$key] = $value;
		}
		$from = $out['email'];
		$pass = $out['pass'];
		$email_to = 'dashmartid@gmail.com';
		$content = isset($param['content']) ? $param['content'] : '';
		$send = $this->send_notif->send_email($from,$pass, $email_to,'Dashmart Message', $content);
		if($send){
			$this->set_response([
				'err_code' 		=> '00',
				'err_msg' 		=> 'Terimakasih telah menghubungi kami, kami segera untuk menghubungi anda',
			], REST_Controller::HTTP_OK);
		}else{
			$this->set_response([
				'err_code'	=> '06',
				'err_msg' 	=> 'somthing wrong'				
			], REST_Controller::HTTP_OK);
		}		
	}
	
	
	
}
