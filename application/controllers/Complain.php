<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Complain extends MY_Controller {

	public function __construct() {
		parent::__construct();		
		$this->load->model('Access', 'access', true);
		$this->load->library('send_notif');		
	}	
	
	
	
	public function refund(){
		
		if(!$this->session->userdata('login')){
			$this->no_akses();
			return false;
		}
		$from = isset($_REQUEST['froms']) ? $_REQUEST['froms'] : '';
		$to = isset($_REQUEST['to']) ? $_REQUEST['to'] : '';
		$this->data['froms'] = $from;
		$this->data['to'] = $to;
		$this->data['judul_browser'] = 'Complain';
		$this->data['judul_utama'] = 'Refund';
		$this->data['judul_sub'] = 'Indolinen';
		$this->data['title_box'] = 'List of Complain';
		$this->data['status'] = 'payment';
		$this->data['payment'] = 0;
		$where = array('complain_type'=>1);
		
		if(!empty($from) && !empty($to)){
			$where += array('date_format(transaksi.complain_date, "%Y-%m-%d") >= ' => date('Y-m-d', strtotime($from)), 'date_format(transaksi.complain_date, "%Y-%m-%d") <=' => date('Y-m-d', strtotime($to)));
		}
		$sort = array('transaksi.id_transaksi','DESC');
		$this->data['transaksi'] = $this->access->readtable('transaksi', '', $where,'','',$sort)->result_array();
		
		
		$this->data['isi'] = $this->load->view('transaksi/refund_v', $this->data, TRUE);
		$this->load->view('themes/layout_utama_v', $this->data);
	}
	
	public function returns(){
		
		if(!$this->session->userdata('login')){
			$this->no_akses();
			return false;
		}
		$from = isset($_REQUEST['froms']) ? $_REQUEST['froms'] : '';
		$to = isset($_REQUEST['to']) ? $_REQUEST['to'] : '';
		$this->data['froms'] = $from;
		$this->data['to'] = $to;
		$this->data['judul_browser'] = 'Complain';
		$this->data['judul_utama'] = 'Refund';
		$this->data['judul_sub'] = 'Indolinen';
		$this->data['title_box'] = 'List of Complain';
		$this->data['status'] = 'payment';
		$this->data['payment'] = 0;
		$where = array('complain_type'=>2);
		
		if(!empty($from) && !empty($to)){
			$where += array('date_format(transaksi.complain_date, "%Y-%m-%d") >= ' => date('Y-m-d', strtotime($from)), 'date_format(transaksi.complain_date, "%Y-%m-%d") <=' => date('Y-m-d', strtotime($to)));
		}
		$sort = array('transaksi.id_transaksi','DESC');
		$this->data['transaksi'] = $this->access->readtable('transaksi', '', $where,'','',$sort)->result_array();
		
		$this->data['url_report'] = site_url('transaksi/payment');
		
		$this->data['isi'] = $this->load->view('transaksi/refund_v', $this->data, TRUE);
		$this->load->view('themes/layout_utama_v', $this->data);
	}
	
	
	function upd_status(){
		$tgl = date('Y-m-d H:i:s');		
		$id_transaksi = '';
		$transaksi_detail = '';
		
		$id_transaksi = (int)$this->converter->decode($_POST['id_transaksi']) > 0 ? (int)$this->converter->decode($_POST['id_transaksi']) : 0;
		$id_trans = (int)$_POST['id_trans'] > 0 ? (int)$_POST['id_trans'] : 0;
		$id_transaksi = $id_trans > 0 ? $id_trans : $id_transaksi;
		$status = (int)$_POST['status'] > 0 ? (int)$_POST['status'] : 0;
		$simpan = array();
		$simpan = array(
			'status_complain'		=> $status,
			'status_complain_by'	=> $this->session->userdata('operator_id'),
			'status_complain_date'	=> $tgl,
		);
		$this->db->trans_begin();
		$where = array(
			'id_transaksi' => $id_transaksi
		);
		if($status == 2){ //approve
			
		}
		if($status == 3){ //reject
			$simpan += array('status'=>6);
		}
		$this->access->updatetable('transaksi', $simpan, $where);		
		// error_log($this->db->last_query());
		$this->db->trans_commit();
		echo $id_transaksi;
			
	}
		
	
	function detail($id_transaksi=''){
		$id_transaksi = (int)$this->converter->decode($id_transaksi);
		$transaksi = $this->access->readtable('transaksi','', array('id_transaksi' => $id_transaksi))->row();
		$this->data['transaksi_detail'] = $this->access->readtable('transaksi_detail','',array('id_trans'=>$id_transaksi))->result_array();
		$complain_type = $transaksi->complain_type;
		
		$this->data['judul_browser'] = 'Complain';
		$this->data['judul_utama'] = '';
		$this->data['judul_sub'] = 'Indolinen';
		
		$this->data['transaksi'] = $transaksi;
		if($complain_type == 1)	{
			$this->data['judul_utama'] = 'Refund detail';
			$this->data['title_box'] = 'Refund detail';
			$this->data['isi'] = $this->load->view('transaksi/refund_detail', $this->data, TRUE);
		}
		if($complain_type == 2)	{
			$this->data['judul_utama'] = 'Return detail';
			$this->data['title_box'] = 'Return detail';
			$this->data['isi'] = $this->load->view('transaksi/return_detail', $this->data, TRUE);
		}
		$this->load->view('themes/layout_utama_v', $this->data);
	}
	
	
	
	
	public function no_akses() {
		if ($this->session->userdata('login') == FALSE) {
			redirect('/');
			return false;
		}
		$this->data['judul_browser'] = 'Tidak Ada Akses';
		$this->data['judul_utama'] = 'Tidak Ada Akses';
		$this->data['judul_sub'] = '';
		$this->data['isi'] = '<div class="alert alert-danger">Anda tidak memiliki Akses.</div>';
		$this->load->view('themes/layout_utama_v', $this->data);
	}
	

}
