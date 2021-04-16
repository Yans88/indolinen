<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Colors extends MY_Controller {

	public function __construct() {
		parent::__construct();		
		$this->load->model('Access', 'access', true);		
	}	
	
	public function index() {
		if(!$this->session->userdata('login') || !$this->session->userdata('master_bank')){
			$this->no_akses();
			return false;
		}
		$this->data['judul_browser'] = 'Colors';
		$this->data['judul_utama'] = 'List';
		$this->data['judul_sub'] = 'Colors';
		$this->data['title_box'] = 'List of Colors';
		
		$this->data['colors'] = $this->access->readtable('colors','',array('deleted_at'=>null))->result_array();			
		$this->data['isi'] = $this->load->view('colors_v', $this->data, TRUE);
		$this->load->view('themes/layout_utama_v', $this->data);
	}
	
	public function save(){		
		$save = '';
		$id_color = isset($_POST['id_color']) ? (int)$_POST['id_color'] : 0;					
		$color = $_POST['_color'];		
		$data = array(			
			'color'		=> $color			
		);				
		$where = array();
		if($id_color > 0){			
			$where = array('id' => $id_color);
			$save = $this->access->updatetable('colors', $data, $where);
		}else{
			$data += array('created_at' => date('Y-m-d H:i:s'),'created_by'=>$this->session->userdata('operator_id'));
			$save = $this->access->inserttable('colors', $data);
		}		
		echo $save;	
		
	}
	
	public function del(){	
		
		$data = array(			
			'deleted_at'	=> date('Y-m-d H:i:s'),		
			'deleted_by'	=> $this->session->userdata('operator_id')		
		);
		$where = array('id'=> $_POST['id']);
		echo $this->access->updatetable('colors', $data, $where);
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
