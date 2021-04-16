<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Members extends MY_Controller {

	public function __construct() {
		parent::__construct();		
		$this->load->model('Access', 'access', true);	
	}	
	
	public function index() {
		
		if(!$this->session->userdata('login') || !$this->session->userdata('members')){
			$this->no_akses();
			return false;
		}
		
		$this->data['judul_browser'] = 'Member';
		$this->data['judul_utama'] = 'Member';
		$this->data['judul_sub'] = 'Indolinen';
		$this->data['title_box'] = 'List of Member';
		$select = array('members.*','tier.nama_tier','tier.diskon');
		$where = array('members.deleted_at'=>null);
		$this->data['member'] = $this->access->readtable('members',$select,$where,array('tier'=> 'tier.id_tier = members.id_tier'),'','','LEFT')->result_array();
		
		$this->data['isi'] = $this->load->view('member_v', $this->data, TRUE);
		$this->load->view('themes/layout_utama_v', $this->data);
	}
	
	public function view_member($id_member=0){
		if(!$this->session->userdata('login') || !$this->session->userdata('members')){
			$this->no_akses();
			return false;
		}
		$this->data['judul_browser'] = 'Member';
		$this->data['judul_utama'] = 'Member';
		$this->data['judul_sub'] = 'Indolinen';		
		$sort_tier = array('id','DESC');
		$sort2 = array('update_at','ASC');
		$sort = array('id_transaksi','DESC');
		$sort3 = array('created_at','DESC');
		$select = array('members.*','tier.nama_tier','tier.diskon');
		$this->data['member'] = $this->access->readtable('members',$select,array('members.id_member'=>$id_member),array('tier'=> 'tier.id_tier = members.id_tier'),'','','LEFT')->row();
		$this->data['transaksi'] = $this->access->readtable('transaksi','',array('id_member'=>$id_member),'','',$sort)->result_array();
		$this->data['chat_admin'] = $this->access->readtable('chat_admin','',array('id_member'=>$id_member),'','',$sort3)->result_array();
		
		$this->data['tier'] = $this->access->readtable('tier','',array('deleted_at'=>null))->result_array();
		$selects = array('history_tier_member.*','tier.diskon','tier.nama_tier','admin.fullname');
		$this->data['history_tier'] = $this->access->readtable('history_tier_member',$selects,array('history_tier_member.id_member'=>$id_member),array('tier'=> 'tier.id_tier = history_tier_member.id_tier','admin'=>'admin.operator_id = history_tier_member.update_by'),'',$sort_tier,'LEFT')->result_array();
		$select = array();
		$this->data['_alamat'] = $this->access->readtable('alamat_pengiriman',$select,array('alamat_pengiriman.id_member'=>$id_member, 'alamat_pengiriman.deleted_at'=>null))->result_array();	
		$this->data['isi'] = $this->load->view('member_detail', $this->data, TRUE);
		$this->load->view('themes/layout_utama_v', $this->data);
	}
	
	
	
	public function set_tier(){
		$tgl = date('Y-m-d H:i:s');
		$where = array(
			'id_member' => $_POST['id_member']
		);
		$data = array(
			'id_tier'	=> $_POST['id_tier']
		);
		$this->access->inserttable('history_tier_member', array('id_tier'=>$_POST['id_tier'],'id_member'=>$_POST['id_member'],'update_by'=>$this->session->userdata('operator_id')));
		echo $this->access->updatetable('members', $data, $where);
	}
	
	function transaksi_detail($id_transaksi=''){
		if(!$this->session->userdata('login') || !$this->session->userdata('members')){
			$this->no_akses();
			return false;
		}
		$id_transaksi = (int)$this->converter->decode($id_transaksi);
		$this->data['transaksi'] = $this->access->readtable('transaksi','', array('transaksi.id_transaksi' => $id_transaksi))->row();
		$this->data['transaksi_detail'] = $this->access->readtable('transaksi_detail','',array('id_trans'=>$id_transaksi))->result_array();
		$this->data['tagihan'] = $this->access->readtable('tagihan','',array('id_transaksi'=>$id_transaksi))->result_array();
		$this->data['judul_browser'] = 'Transaksi detail';
		$this->data['judul_utama'] = 'Transaksi detail';
		$this->data['judul_sub'] = 'Dashmart';
		$this->data['title_box'] = 'Transaksi detail';
		$this->data['isi'] = $this->load->view('transaksi/transaksi_detail', $this->data, TRUE);
		$this->load->view('themes/layout_utama_v', $this->data);
	}
	
	function export_r(){
		if(!$this->session->userdata('login') || !$this->session->userdata('members')){
			$this->no_akses();
			return false;
		}
		$this->load->library('excel');
		
		$where = array();
		$select = array('members.*','tier.nama_tier','tier.diskon','warehouse.whs_code as ocrcode_p','sales.slp_code as slp_code');
		$where = array('members.deleted_at'=>null);
		$member = $this->access->readtable('members',$select,$where,array('tier'=> 'tier.id_tier = members.id_tier','warehouse'=> 'warehouse.id_whs = members.id_whs','sales'=> 'sales.id_sales = members.sales_id'),'','','LEFT')->result_array();
		error_log($this->db->last_query());
		// $product = $this->access->readtable('product','',$where)->result_array();
		
	
		$this->excel->setActiveSheetIndex(0);		
		
		$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
		$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
		$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
		$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
		$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
		$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
		$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);		
		$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);		
		$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);		
		$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);		
		$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(20);		
		$this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(20);		
		$this->excel->getActiveSheet()->getColumnDimension('M')->setWidth(15);		
		
		
		$this->excel->getActiveSheet()->getStyle('A1:M1')->getFont()->setSize(12);				
		$this->excel->getActiveSheet()->getStyle('A1:M1')->getFont()->setBold(true);
		
		$styleArray = array(
		  'borders' => array(
			'allborders' => array(
			  'style' => PHPExcel_Style_Border::BORDER_THIN
			)
		  )
		);
		$this->excel->getActiveSheet()->getStyle('A1:M1')->applyFromArray($styleArray);	
		
		
		$this->excel->getActiveSheet()->setCellValue('A1', 'Nama');
		$this->excel->getActiveSheet()->setCellValue('B1', 'Nama Toko');
		$this->excel->getActiveSheet()->setCellValue('C1', 'Email');
        $this->excel->getActiveSheet()->setCellValue('D1', 'Phone');
        $this->excel->getActiveSheet()->setCellValue('E1', 'Password');
        $this->excel->getActiveSheet()->setCellValue('F1', 'Alamat');
        $this->excel->getActiveSheet()->setCellValue('G1', 'Status');
		$this->excel->getActiveSheet()->setCellValue('H1', 'Referensi');
		$this->excel->getActiveSheet()->setCellValue('I1', 'Sales Person Code');
		$this->excel->getActiveSheet()->setCellValue('J1', 'Customer Code');
		$this->excel->getActiveSheet()->setCellValue('K1', 'WHSCODE');
		$this->excel->getActiveSheet()->setCellValue('L1', 'Ocrcode');
        $this->excel->getActiveSheet()->setCellValue('M1', 'Id Member');        
        
		$this->excel->getActiveSheet()->getStyle('A1:M1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
		$i=2;
		$no = 1;		
		if(!empty($member)){
			foreach($member as $t){
				$_status = '';
				if($t['status'] == 1){
					$_status = 'waiting approve';
				}
				if($t['status'] == 3){
					$_status = 'reject';
				}
				if($t['status'] == 4){
					$_status = 'approve';
				}
				$this->excel->getActiveSheet()->setCellValue('A'.$i, $t['nama']);
				$this->excel->getActiveSheet()->setCellValue('B'.$i, $t['nama_toko']);
				$this->excel->getActiveSheet()->setCellValue('C'.$i, $t['email']);
				$this->excel->getActiveSheet()->setCellValue('D'.$i, (int)$t['phone']);
				$this->excel->getActiveSheet()->setCellValue('E'.$i, $this->converter->decode($t['pass']));
				$this->excel->getActiveSheet()->setCellValue('F'.$i, $t['address']);
				$this->excel->getActiveSheet()->setCellValue('G'.$i, ucwords($_status));		
				$this->excel->getActiveSheet()->setCellValue('H'.$i, $t['referensi']);
				$this->excel->getActiveSheet()->setCellValue('I'.$i, $t['slp_code']);
				$this->excel->getActiveSheet()->setCellValue('J'.$i, $t['kd_cust']);
				$this->excel->getActiveSheet()->setCellValue('K'.$i, $t['ocrcode_p']);
				$this->excel->getActiveSheet()->setCellValue('L'.$i, $t['ocrcode_c']);
				$this->excel->getActiveSheet()->setCellValue('M'.$i, $t['id_member']);				
				
				$this->excel->getActiveSheet()->getStyle('D'.$i)->getNumberFormat()->setFormatCode('0');
				$this->excel->getActiveSheet()->getStyle('A'.$i.':M'.$i)->applyFromArray($styleArray);
				$this->excel->getActiveSheet()->getStyle('A'.$i.':M'.$i)->getFont()->setSize(12);
				$this->excel->getActiveSheet()->getStyle('A'.$i.':M'.$i)->getAlignment()->setWrapText(true);	
				
				$i++;
			}
			unset($styleArray);	
		}
		
		$this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		$this->excel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
		$this->excel->getActiveSheet()->getPageSetup()->setFitToPage(true);
		$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
		$this->excel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
		
		$filename ='data_member.xls';
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$filename.'"'); 
		header('Cache-Control: max-age=0'); 					 
		$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  		
		$objWriter->save('php://output');
		
	}
	
	public function import(){
		if(!$this->session->userdata('login') || !$this->session->userdata('members')){
			$this->no_akses();
			return false;
		}
		// $this->load->model('Access', 'access', true);
		$config['upload_path']   = FCPATH.'/uploads/members/';
        $config['allowed_types'] = "xls|xlsx|csv";
        $config['remove_spaces'] = true;
        $config['overwrite'] = true;
        $config['max_size'] = '2048';
		
		$this->load->library('upload',$config);
		$this->load->library('excel');
		$barcode = '';
		$point = '';
		$all_data = 0;
		$id_merchant = $this->session->userdata('id_merchant') > 0 ? $this->session->userdata('id_merchant') : 0;
		if (!$this->upload->do_upload('user_import')) {
            $data['error'] = $error = $this->upload->display_errors();
            
        }else {
            $file_data = $this->upload->data('user_import');
            $file_path = './uploads/members/'.$_FILES['user_import']['name'];
			$data['size_file'] = $file_data['file_size'];
			$data['file_name'] = $file_data['file_name'];
			$file_type	= PHPExcel_IOFactory::identify($file_path);
    		$objReader	= PHPExcel_IOFactory::createReader($file_type);
    		$objPHPExcel = $objReader->load($file_path);
    		$sheet_data	= $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
			$varData = array();
			$dt_duplicate = array();
			$i=0;
			$tgl = date('Y-m-d H:i:s');
			foreach($sheet_data as $data){
				if($i>0){					
					$nama = '';			
					$nama_toko = '';			
					$phone = '';
					$email = '';
					$alamat = '';
					$status = '';
					$referensi = '';
					$id_member = '';			
					$_status = '';			
					$pass = '';			
					$sales_id = '';			
					$ocrcode_p = '';			
					$whs_code = '';			
					$kd_cust = '';			
					$nama = $data['A'];
					$nama_toko = $data['B'];	
					$email = $data['C'];	
					$phone = $data['D'];
					$pass = $this->converter->encode($data['E']);
					$alamat = $data['F'];
					$status = strtolower($data['G']);	
					$referensi = $data['H'];
					$slp_code = $data['I'];
					$kd_cust = $data['J'];
					$whs_code = trim($data['K']);	
					
					$ocrcode_c = $data['L'];
					$id_member = $data['M'];
					$ptn = "/^0/";
					$rpltxt = "62";
					$phone = preg_replace($ptn, $rpltxt, $phone);
					if($status == "approve"){
						$_status = 4;
					}
					if($status == "reject"){
						$_status = 3;
					}
					if($status == "waiting approve"){
						$_status = 1;
					}
					$where = array();
					$where = array('lower(whs_code)'=> strtolower($whs_code),'deleted_at'=>null);
					$whs = '';
					$whs = $this->access->readtable('warehouse','',$where)->row();
					$ocrcode_p = (int)$whs->id_whs > 0 ? (int)$whs->id_whs : 0;
					$where = array();					
					$sales = '';
					$sales_id = 0;
					if(!empty($slp_code) || (int)$slp_code >0 ){
						$where = array('lower(slp_code)'=> strtolower($slp_code),'deleted_at'=>null);
						$sales = $this->access->readtable('sales','',$where)->row();
						$sales_id = (int)$sales->id_sales > 0 ? (int)$sales->id_sales : 0;
					}
					
					$simpan = array();
					$simpan = array(			
						'nama'			=> $nama,						
						'email'			=> $email,	
						'address'		=> $alamat,	
						'phone'			=> $phone,
						'pass'			=> $pass,
						'nama_toko'		=> $nama_toko,
						'referensi'		=> $referensi,
						'sales_id'		=> $sales_id,
						'id_whs'		=> $ocrcode_p,
						'ocrcode_c'		=> $ocrcode_c,
						'kd_cust'		=> $kd_cust,
						'status'		=> $_status
					);	
					if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
						$simpan = array();
					}
					
					$chk_email = '';
					$ketemu = 0;
					$chk_phone = '';				
					$ketemu_phone = 0;
					if($id_member > 0){		
						if(!empty($simpan)){
							$chk_email = $this->access->readtable('members','',array('email'=>$email,'deleted_at'=>null,'id_member !='=>$id_member))->row();
							
							$ketemu = count($chk_email);
							$chk_phone = $this->access->readtable('members','',array('phone'=>$phone,'deleted_at'=>null,'id_member !='=>$id_member))->row();						
							$ketemu_phone = count($chk_phone);
							
							if($ketemu <= 0 && $ketemu_phone <= 0){
								$where = array('id_member'=>$id_member);
								$this->access->updatetable('members', $simpan, $where);
								error_log($this->db->last_query());
							}
						}
					}else{
						if(!empty($simpan)){
							// $simpan_alamat =array(
								// 'alamat'		=> $alamat,
								// 'id_member'		=> $login->id_member,
								// 'created_at' 	=> $tgl_reg,
								// 'phone'			=> $phone,
								// 'nama_penerima'	=> $nama_member,
								// 'nama_alamat'	=> 'Alamat Toko',
								// 'id_provinsi'	=> $id_provinsi,
								// 'id_city'		=> $id_city,
								// 'kode_pos'		=> $kode_pos
							// );
							// $save = $this->access->inserttable('alamat_pengiriman',$simpan_alamat);
							$chk_email = $this->access->readtable('members','',array('email'=>$email,'deleted_at'=>null))->row();
							$ketemu = count($chk_email);
							error_log($this->db->last_query());
							$chk_phone = $this->access->readtable('members','',array('phone'=>$phone,'deleted_at'=>null))->row();					
							$ketemu_phone = count($chk_phone);
							if($ketemu <= 0 && $ketemu_phone <= 0){
								$simpan += array('tgl_reg'=> $tgl,'status_by'=>$this->session->userdata('operator_id'),'status_date'=>$tgl);
								$this->access->inserttable('members', $simpan);	
								error_log($this->db->last_query());
							}
						}						
					}
					// error_log($this->db->last_query());	
				}
				
				$i++;
			}
		}
		
		redirect('/members');
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
