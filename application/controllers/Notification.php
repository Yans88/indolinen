<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Notification extends CI_Controller {

	public function __construct(){
        parent::__construct();
        $params = array('server_key' => 'SB-Mid-server-O1OR2d6XUeWB2B1syiSwSWwY', 'production' => true);
		$this->load->library('veritrans');
		$this->veritrans->config($params);
		$this->load->model('Access', 'access', true);		
		$this->load->model('Setting_m','sm', true);
		$this->load->library('send_notif');
    }

	public function index()
	{
		//echo 'test notification handler';
		$json_result = file_get_contents('php://input');
		$result = json_decode($json_result);

		if($result){
			$notif = $this->veritrans->status($result->order_id);
		}

		$transaction = $notif->transaction_status;
		$type = $notif->payment_type;
		$order_id = $notif->order_id;
		$fraud = $notif->fraud_status;
		
		$simpan = array();
		
		$status_transaksi = 0;
		if ($transaction == 'capture') {
		  // For credit card transaction, we need to check whether transaction is challenge by FDS or not
		  if ($type == 'credit_card'){
		    if($fraud == 'challenge'){
		      // TODO set payment status in merchant's database to 'Challenge by FDS'
		      // TODO merchant should decide whether this transaction is authorized or not in MAP
		      	error_log("Transaction order_id: " . $order_id ." is challenged by FDS");
				$status_transaksi = 2; //reject payment
		      } 
		      else {
		      // TODO set payment status in merchant's database to 'Success'
		      	error_log("Transaction order_id: " . $order_id ." successfully captured using " . $type);
				$status_transaksi = 1; //complete payment
		      }
		    }
		  }
		else if ($transaction == 'settlement'){
		  // TODO set payment status in merchant's database to 'Settlement'
			error_log("Transaction order_id: " . $order_id ." successfully transfered using " . $type);
			$status_transaksi = 1; //complete payment
		}else if($transaction == 'pending'){
		  // TODO set payment status in merchant's database to 'Pending'
			error_log("Waiting customer to finish transaction order_id: " . $order_id . " using " . $type);
		}else if ($transaction == 'deny') {
		  // TODO set payment status in merchant's database to 'Denied'
		  	$status_transaksi = 2; //deny payment
			error_log("Payment using " . $type . " for transaction order_id: " . $order_id . " is denied.");
		}else if ($transaction == 'expire') {
		  // TODO set payment status in merchant's database to 'Denied'
		  	$status_transaksi = 7; //expired
			error_log("Payment using " . $type . " for transaction order_id: " . $order_id . " is expired.");
		}
		$id_member = 0;
		$id_transaksi = 0;
		$chk_transaksi = $this->access->readtable('transaksi','',array('id_transaksi'=>$order_id,'transaksi.status'=>0))->row();
		$id_member = (int)$chk_transaksi->id_member;
		$id_transaksi = (int)$chk_transaksi->id_transaksi;
		
		$upd_dt = array();
	
		$upd_dt = array(
			'transaction_time' 		=> date('Y-m-d H:i:s', strtotime($notif->transaction_time)),
			'transaction_status'	=> $transaction,
			'transaction_id'		=> $notif->transaction_id,
			'status_code'			=> $notif->status_code,
			'payment_types'			=> $type,
			'status'				=> $status_transaksi
		);
		$this->access->updatetable('transaksi', $upd_dt, array('id_transaksi' => $id_transaksi));
		
	}
	
	public function succes_payment(){
		$res['dt'] = 'Congratulation';
		//echo '<script>console.log(\'RECEIVEOK\');</script>';
		$this->load->view('themes/succes_payment', $res);
	}
	
	
	
}