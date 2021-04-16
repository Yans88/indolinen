<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Complete_trans extends CI_Controller {

	public function __construct() {
		parent::__construct();		
		$this->load->model('Access', 'access', true);		
			
	}	
	
	public function index() {	
		$tgl = date('Y-m-d H:i');	
		$where = array('date_format(completed_at, "%Y-%m-%d %H:%i") <='=> $tgl, 'deleted_at'=>null);
		$auto = $this->access->readtable('id_trans_auto','',$where)->result_array();
		if(!empty($auto)){
			foreach($auto as $a){
				$where = array(
					'id_transaksi' => $a['id_trans']
				);
				$simpan = array(
					'status'		=> 6
				);
				$this->access->updatetable('transaksi', $simpan, $where);	
				$this->access->updatetable('id_trans_auto', array('deleted_at'=> date('Y-m-d H:i:s')), array('id_trans'=>$a['id_trans']));
			}
			
		}
	}
	
	function cancel_transaksi(){
		$tgl = date('Y-m-d H:i', time() - (60 * 60 * 6));		
		$where = array('date_format(create_at, "%Y-%m-%d %H:%i") <='=> $tgl,'status'=>0,'confirm_date'=>null);
		$auto = $this->access->readtable('transaksi','',$where)->result_array();
		if(!empty($auto)){
			foreach($auto as $a){
				$dt_upd[] = array(
					'id_transaksi'	=> $a['id_transaksi'],
					'status'		=> 7,
					'appr_rej_date'	=> date('Y-m-d H:i:s'),
					'appr_rej_by'	=> -1
				);
				$dt_upd_detail[] = array(
					'id_trans'	=> $a['id_transaksi'],
					'return_stock'	=> 2,
				);
			}
			$this->db->update_batch('transaksi', $dt_upd, 'id_transaksi');
			$this->db->update_batch('transaksi_detail', $dt_upd_detail, 'id_trans');
		}
		if(empty($dt_upd)) $this->return_stock();
	}

	function return_stock(){
		$where = array('return_stock'=> 2);
		$auto = $this->access->readtable('transaksi_detail','',$where,'',15,$sort)->result_array();
		if(!empty($auto)){
			foreach($auto as $td){
				$id_produk = 0;
				$id_trans = 0;
				$jml = 0;
				$id_produk = (int)$td['id_product'];
				$id_trans = (int)$td['id_trans'];					
				$jml = (int)$td['jml'];
				$dt_product = '';					
				$stok = 0;
				$dt_product = $this->access->readtable('product', '', array('id_product' => $id_produk))->row();				
				$stok = (int)$dt_product->qty > 0 ? (int)$dt_product->qty : 0;
				$stok = $stok + $jml;
				$this->access->updatetable('product', array('qty' => $stok), array('id_product' => $id_produk));				
				$this->access->updatetable('transaksi_detail', array('return_stock' => 1), array('id_product' => $id_produk,'return_stock'=> 2,'id_trans'=>$id_trans));					
			}
		}
	}
	
	function get_trans(){
		$where = array('appr_rej_by'=> -1,'status'=>7);
		$auto = $this->access->readtable('transaksi','',$where)->result_array();
		
		if(!empty($auto)){
			foreach($auto as $a){				
				$dt_upd_detail[] = array(
					'id_trans'	=> $a['id_trans'],
					'return_stock'	=> 2,
				);
			}
			$this->db->update_batch('transaksi_detail', $dt_upd_detail, 'id_trans');
		}
	}


}
