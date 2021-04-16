<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Materials extends MY_Controller {

	public function __construct() {
		parent::__construct();		
		$this->load->model('Access', 'access', true);		
	}	
	
	public function index() {
		if(!$this->session->userdata('login') || !$this->session->userdata('master_bank')){
			$this->no_akses();
			return false;
		}
		$this->data['judul_browser'] = 'Materials';
		$this->data['judul_utama'] = 'List';
		$this->data['judul_sub'] = 'Materials';
		$this->data['title_box'] = 'List of Materials';
		
		$this->data['materials'] = $this->access->readtable('materials','',array('deleted_at'=>null))->result_array();			
		$this->data['isi'] = $this->load->view('materials_v', $this->data, TRUE);
		$this->load->view('themes/layout_utama_v', $this->data);
	}
	
	public function save(){		
		$save = '';
		$id_material = isset($_POST['id_material']) ? (int)$_POST['id_material'] : 0;					
		$material = $_POST['_material'];		
		$data = array(			
			'material'		=> $material			
		);				
		$where = array();
		if($id_material > 0){			
			$where = array('id' => $id_material);
			$save = $this->access->updatetable('materials', $data, $where);
		}else{
			$data += array('created_at' => date('Y-m-d H:i:s'),'created_by'=>$this->session->userdata('operator_id'));
			$save = $this->access->inserttable('materials', $data);
		}		
		echo $save;	
		
	}
	
	public function del(){			
		$data = array(			
			'deleted_at'	=> date('Y-m-d H:i:s'),		
			'deleted_by'	=> $this->session->userdata('operator_id')		
		);
		$where = array('id'=> $_POST['id']);
		echo $this->access->updatetable('materials', $data, $where);
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
