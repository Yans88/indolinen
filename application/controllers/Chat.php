<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Chat extends MY_Controller {

	public function __construct() {
		parent::__construct();		
		$this->load->model('Access', 'access', true);		
			
	}	
	
	public function index() {
		if(!$this->session->userdata('login') || !$this->session->userdata('chat')){
			$this->no_akses();
			return false;
		}
		$this->data['judul_browser'] = 'Chat';
		$this->data['judul_utama'] = 'Chat';
		$this->data['judul_sub'] = 'List';
		//$this->data['messages'] = $this->access->readtable('messages')->result_array();
		$this->data['isi'] = $this->load->view('chat/messages_v', $this->data, TRUE);
		$this->load->view('themes/layout_utama_v', $this->data);
	}
	
	function count_chat(){
		$chat = $this->access->readtable('master_chat','',array('master_chat.status_to >'=>0,'id_member_to'=>'admin'))->result_array();
		$cnt = count($chat);
		echo $cnt;
	}
	
	function get_list_chat(){
		$search = isset($_POST['search_member']) ? $_POST['search_member'] : 0;
		$sort = array('chat_admin.updated_at','DESC');
		$select = array('chat_admin.*','members.nama','members.email','members.surname');
		if(!empty($search)){
			$list_chat = $this->access->readtable('chat_admin',$select,'',array('members'=>'members.id_member = chat_admin.id_member'),'',$sort,'LEFT','id_chat',array('members.nama'=>$search))->result_array();
		}else{
			$list_chat = $this->access->readtable('chat_admin',$select,'',array('members'=>'members.id_member = chat_admin.id_member'),'',$sort,'LEFT','id_chat')->result_array();
		}
		
		$json = array();
		$list_msg = $this->access->readtable('chat_detail')->result_array();
		$input = '';
		$_img = '';
		$i=1;
		if(!empty($list_chat)){
			foreach($list_chat as $lc){				
				
				$input = '';
				if($i == 1){
					$input = '<input type="hidden" name="all_msg" class="all_msg" value="'.count($list_msg).'">';
				}
				if($lc['status'] > 0){
					$json[] = '<div class="chat_list" id="'.$lc['id_chat'].'">
              <div class="chat_people">               
                <div class="chat_ibs" id="chat_ibs_'.$lc['id_chat'].'">
                  <h5>'.$lc['nama'].'<span class="chat_date">'.date('d-m-Y', strtotime($lc['updated_at'])).'</span></h5>
                  <p>'.$lc['pesan'].'</p>
                </div>
              </div>'.$input.'
            </div>';
				}else{
					$json[] = '<div class="chat_list" id="'.$lc['id_chat'].'">
				  <div class="chat_people">               
					<div class="chat_ib">
					  <h5>'.$lc['nama'].'<span class="chat_date">'.date('d-m-Y', strtotime($lc['updated_at'])).'</span></h5>
					  <p>'.$lc['pesan'].'</p>
					</div>
				  </div>'.$input.'
				</div>';
				}
				
			$i++;	
			}
		}
		echo json_encode($json);
		// print_r($id_gym);
	}
	
	function get_chat(){
		$select = array('chat_detail.*','members.surname','members.nama','members.email');
		$id_chat = isset($_POST['id_chat']) ? $_POST['id_chat'] : 0;
		
		$sort = array('ABS(chat_detail.id)','ASC');
		$list_chat = $this->access->readtable('chat_detail',$select,array('chat_detail.id_chat'=>$id_chat),array('members'=>'members.id_member = chat_detail.id_member_from'),'',$sort,'LEFT')->result_array();
		
		$json = array();
		if(!empty($list_chat)){
			foreach($list_chat as $lc){
				$tgl = date('d M y | H:i', strtotime($lc['created_at']));
				if($lc['id_member_from'] == 'admin'){
					$json[] = ' <div class="outgoing_msg">
              <div class="sent_msg">
				<strong>Admin</strong>
                <p>'.$lc['pesan'].'</p>
                <span class="time_date">'.$tgl.'</span> </div>
            </div>';
				}else{
					$item = '';
					if((int)$lc['id_product'] > 0){
						$item = '<strong>'.$lc['nama'].'</strong><div class="box box-solid" style="width:50%;">
                    <div class="box-body">
                        
                        <div class="media">
                            <div class="media-left">
                               
                                    <img src="'.$lc['img_item'].'" alt="'.$lc['nama_item'].'" class="media-object" style="width: 150px;height: 120px;border-radius: 4px;box-shadow: 0 1px 3px rgba(0,0,0,.15);">
                                </a>
								
                            </div>
                            <div class="media-body">
                                <div class="clearfix">
                                    <p style="margin-top: 0">
                                        '.$lc['nama_item'].'
                                    </p>
                                    
                                      <a href="'.site_url('product/add/'.$this->converter->encode($lc['id_product'])).'">
                                    <button type="button" class="btn btn-success btn-sm btn-block mt-auto">View Detail</button></a>
                                </div>
								
                            </div>
							
                        </div>
                    </div>
                </div>';	
					}else{
						$_nama = '<strong>'.$lc['nama'].'</strong>';
					}
					$json[] = $item.
				'<div class="incoming_msg">
              
              <div class="received_msg">
				'.$_nama.'
                <div class="received_withd_msg">
				  
                  <p>'.$lc['pesan'].'</p>
                  <span class="time_date">'.$tgl.'</span></div>
              </div>
            </div>';
				}
				
			}
		}
		$datas = array('status_to'=>0);
		$this->access->updatetable('master_chat', $datas, array('id_chat'=>$id_chat));
		$this->access->updatetable('chat_admin', array('status'=>0), array('id_chat'=>$id_chat));
		$this->access->updatetable('chat_detail', $datas, array('id_chat'=>$id_chat,'id_member_to'=>'admin'));
		echo json_encode($json);
	}
	
	function send_chat(){	
		$tgl = date('Y-m-d H:i:s');
		$id_chat = isset($_POST['id_chat']) ? (int)$_POST['id_chat'] : 0;
		$pesan = isset($_POST['message']) ? $_POST['message'] : '';
		$master_chat = $this->access->readtable('chat_admin','',array('id_chat'=>$id_chat))->row();
		$id_member = !empty($master_chat) ? (int)$master_chat->id_member : 0;
		$members = $this->access->readtable('members','',array('members.id_member'=>$id_to))->row();
		$ids = array($members->gcm_token);
		if($id_chat > 0){
			$datas = array('id_member_from'=>'admin','id_member_to'=>$id_member,'pesan'=>$pesan,'status_from'=>0,'status_to'=>1);
			$this->access->updatetable('master_chat', $datas, array('id_chat'=>$id_chat));
			$this->access->updatetable('chat_admin', array('status'=>1,'pesan'=>$pesan),array('id_chat'=>$id_chat));
		}
		$data = array(	
			'id_member_from'	=> 'admin',
			'id_member_to'	=> $id_member,
			'pesan'			=> $pesan,
			'id_product'	=> 0,
			'nama_item'		=> '',
			'img_item'		=> '',
			'created_at'	=> $tgl,			
			'status_from'	=> 0,
			'status_to'		=> 1,
			'id_chat'		=> $id_chat			
		);
		$save = $this->access->inserttable('chat_detail', $data);
		if(!empty($ids)){
			$data_fcm = array(
				'id_chat'		=> $id_chat,
				'id_member_to'	=> $id_member,
				'title'			=> 'Indolinen',				
				'message' 		=> $pesan,
				'notif_type' 	=> '1'
			);
			$notif_fcm = array(
				'body'			=> $pesan,
				'title'			=> 'Indolinen',
				'badge'			=> '1',
				'sound'			=> 'Default'
			);	
			$send_fcms = $this->send_notif->send_fcm($data_fcm, $notif_fcm, $ids);	
			$dtt =array();
			$dtt =array(
				'id_member'		=> $id_to,
				'id_transaksi'	=> $id_transaksi,
				'fcm_token'		=> $members->gcm_token,
				'created_at'	=> $tgl,
				'type'			=> 2
			);
			$this->access->inserttable('history_notif', $dtt); 
		}
		echo $save;
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
