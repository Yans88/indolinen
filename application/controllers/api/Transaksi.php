<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Transaksi extends REST_Controller {

    function __construct(){
        parent::__construct();
		$this->load->library('send_notif');
		$this->load->library('send_api');
		$this->load->model('Setting_m','sm',true);
		$this->load->model('Access','access',true);
		
    }  
	
	function send_transaksi_post(){
		$param = $this->input->post();
		error_log(serialize($param));
		$id_member = isset($param['id_member']) ? (int)$param['id_member'] : 0;	
		$id_myvoucher = isset($param['id_myvoucher']) ? (int)$param['id_myvoucher'] : 0;	
		$cash = isset($param['cash']) ? (int)$param['cash'] : 0;
		$payment_id = isset($param['payment_id']) ? (int)$param['payment_id'] : 0;
		$alamat_kirim = isset($param['alamat_kirim']) ? (int)$param['alamat_kirim'] : 0;
		$list_item = isset($param['list_item']) ? json_decode($param['list_item']) : '';	
		$bank_code = isset($param['bank_code']) ? strtoupper($param['bank_code']) : 0;
		$manual_transfer = isset($param['manual_transfer']) ? (int)$param['manual_transfer'] : 0;
		$id_bank = isset($param['id_bank']) ? (int)$param['id_bank'] : 0;
		$pengiriman = isset($param['pengiriman']) ? (int)$param['pengiriman'] : 1;
		$kurir_code = isset($param['code']) ? $param['code'] : '';
		$service = isset($param['service']) ? $param['service'] : '';
		$ttl_weight = isset($param['weight']) ? $param['weight'] : '';
		$ongkir = isset($param['ongkir']) ? $param['ongkir'] : '';
		$diskon_voucher = isset($param['diskon_voucher']) ? str_replace('.','',$param['diskon_voucher']) : '';
		$diskon_voucher = str_replace(',','',$diskon_voucher);
		$kode_payment = '';
		$payment = '';
		$nama_bank = '';
		$holder_name = '';		
		$no_rek = '';		
		$master_bank = '';		
		$_status = 0;		
		$_tgl = date('ymdHi');
		$tgl_now = date('Y-m-d H:i:s');
		$expired_payment = date("Y-m-d H:i", strtotime('+8 hours', strtotime($tgl_now)));
		$kode_unik = 0;
		$this->db->trans_begin();
		// $kode_payment = $id_member.''.$_tgl;
		if($cash > 0){
			// $kode_payment = $id_member.''.$_tgl;
			$payment = 1;
			$payment_name = 'Cash';
			$_status = 3;
		}
		
		if($manual_transfer > 0){
			// $kode_unik = rand(100,1000);
			if($id_bank <= 0){
				$this->db->trans_rollback();
				$this->set_response([
					'err_code'	=> '05',
					'err_msg' 	=> 'id_bank require'
				], REST_Controller::HTTP_OK);
				return false;
			}
			$payment = 3;
			$payment_name = 'Manual Transfer';
			$_status = 0;
			$master_bank = $this->access->readtable('master_bank','',array('master_bank.id_bank'=>$id_bank))->row();
			$nama_bank = !empty($master_bank->nama_bank) ? $master_bank->nama_bank : '';
			$holder_name = !empty($master_bank->holder_name) ? $master_bank->holder_name : '';
			$no_rek = !empty($master_bank->no_rek) ? $master_bank->no_rek : '';			
		}
		if($manual_transfer == 0){
			$id_bank = 0;
			$nama_bank = '';
			$holder_name = '';
			$no_rek = '';
			$payment = 4;
			$payment_name = 'Midtrans';
		}
		$select = array('alamat_pengiriman.*','members.nama','members.surname','members.email','members.email','members.phone as phone_member','members.id_tier');
		$_address = $this->access->readtable('alamat_pengiriman',$select,array('alamat_pengiriman.id_address'=>$alamat_kirim, 'alamat_pengiriman.deleted_at'=>null),array('members' => 'members.id_member = alamat_pengiriman.id_member'),'','','LEFT')->row();
		$nama_pengiriman = '';
		if($pengiriman == 1) {
			$nama_pengiriman = 'Pick up at Store';
			$select = array('members.nama','members.surname','members.email','members.email','members.phone as phone_member','members.id_tier');
			$_address = $this->access->readtable('members',$select,array('members.id_member'=>$id_member))->row();		
		}
		if($pengiriman == 2) {
			$nama_pengiriman = 'Delivery';
		}
		$id_tier = (int)$_address->id_tier > 0 ? (int)$_address->id_tier : 0;
		$diskon = 0;
		$_diskon = 0;
		$tier = '';
		
		if($id_tier > 0){
			$tier = $this->access->readtable('tier','',array('deleted_at'=>null,'id_tier'=>$id_tier))->row();
			$diskon = $tier->diskon > 0 ? $tier->diskon : 0;
			$_diskon = $diskon > 0 ? $diskon / 100 : 0;
		}	
		$status_name = array(
			'0' =>'Menungu Pembayaran', 
			'1' =>'Payment Complete/Pesanan diproses', 
			'2' =>'Reject',
			'3' =>'Approve', 
			'4' =>'Pesanan Dikirim',
			'5' =>'Sampai Tujuan',
			'6' =>'Selesai',
			'7' =>'Cancel Payment',
			'8' =>'Complain'
		);
		
		$kode_voucher = '';
		$id_voucher = '';
		$diskon_voucher = 0;
		if($id_myvoucher > 0){
			$vouchers = $this->access->readtable('my_voucher','',array('id'=>$id_myvoucher,'id_member'=>$id_member,'date_format(expired_date, "%Y-%m-%d") >=' => date('Y-m-d')))->row();
			error_log($this->db->last_query());
			if(empty($vouchers)){
				$this->db->trans_rollback();
				$this->set_response([
					'err_code'	=> '04',
					'err_msg' 	=> 'voucher tidak ditemukan',
					'data'		=> null
				], REST_Controller::HTTP_OK);
				return false;
			}
			$is_used = !empty($vouchers->is_used) ? 1 : 0;
			$kode_voucher = $vouchers->kode_voucher;
			$id_voucher = $vouchers->id_voucher;
			$diskon_voucher = isset($param['diskon_voucher']) ? str_replace('.','',$param['diskon_voucher']) : '';
			$diskon_voucher = str_replace(',','',$diskon_voucher);
			if($is_used > 0){
				$dt = array();
				$diskon_voucher = 0;
				$path = '';
				$path = !empty($vouchers->img) ? base_url('uploads/vouchers/'.$vouchers->img) : null;
				$dt = array(
					"id_myvoucher"		=> $vouchers->id,
					"id_voucher"		=> $vouchers->id_voucher,
					"kode_voucher"		=> $vouchers->kode_voucher,
					"tipe_voucher"		=> $vouchers->tipe_voucher,
					"nilai_potongan"	=> $vouchers->nilai_potongan,
					"maks_potongan"		=> $vouchers->maks_potongan,
					"min_pembelian"		=> $vouchers->min_pembelian,
					"expired_date"		=> $vouchers->expired_date,
					"deskripsi"			=> $vouchers->deskripsi,
					"is_used"			=> $is_used,
					"image"				=> $path,
				);
				$this->db->trans_rollback();
				$this->set_response([
					'err_code'	=> '07',
					'err_msg' 	=> 'voucher sudah digunakan sebelumnya',
					'data'		=> $dt
				], REST_Controller::HTTP_OK);
				return false;
			}
		}
		$simpan_dt = array(			
			"id_address"		=> $alamat_kirim,
			"id_member"			=> $id_member,				
			"id_country"		=> $_address->id_country,				
			"id_provinsi"		=> $_address->id_provinsi,				
			"id_city"			=> $_address->id_city,			
			"nama_member"		=> $_address->nama,
			"email_member"		=> $_address->email,
			"phone_member"		=> $_address->phone_member,
			"payment"			=> $payment,
			"payment_name"		=> $payment_name,			
			"kode_unik"			=> $kode_unik,
			"kode_pos"			=> $_address->kode_pos,							
			"alamat_penerima"	=> $_address->alamat,														
			"phone_penerima"	=> $_address->phone,							
			"nama_penerima"		=> $_address->nama_penerima,											
			"provinsi"			=> $_address->nama_provinsi,			
			"nama_city"			=> $_address->nama_city,			
			"nama_alamat"		=> $_address->nama_alamat,			
			"pengiriman"		=> $pengiriman,
			"nama_pengiriman"	=> $nama_pengiriman,
			"status"			=> $_status,			
			"id_tier"			=> $id_tier,			
			"diskon_tier"		=> $diskon,			
			"id_bank"			=> $id_bank,			
			"nama_bank"			=> $nama_bank,			
			"no_rek"			=> $no_rek,			
			"holder_name"		=> $holder_name,
			"kurir_code"		=> $kurir_code,			
			"kurir_service"		=> $service,			
			"ongkir"			=> $ongkir,			
			"ttl_weight"		=> $ttl_weight,	
			"id_myvoucher"		=> $id_myvoucher,
			"id_voucher"		=> $id_voucher,
			"kode_voucher"		=> $kode_voucher,
			"pot_voucher"		=> $diskon_voucher,
			"expired_payment"	=> $expired_payment,
			"created_at"		=> $tgl_now
			
		);
		$save = 0;		
		$save = $this->access->inserttable('transaksi',$simpan_dt);
		
		$id_product = '';	
		$products = '';	
		$path = '';
		
		$jml = 0;	
		$ttl = 0;	
		$ttl_all = 0;	
		$dt_stock = array();	
		$dt = array();
		$simpan_products = array();
		$dt_upd = array();
		$disc_tambahan = 0;
		$name_stok = array();
		for($i = 0; $i < count($list_item); $i++){
			$id_product = '';	
			$note = '';	
			$jml = 0 ;	
			$id_product = $list_item[$i]->id_product;	
			$id_variant = $list_item[$i]->id_varian;	
			$jml = $list_item[$i]->jml;
			
			$products = '';
			if((int)$id_product > 0 && (int)$jml > 0){
				$select = array('product.*','kategori.nama_kategori','sub_kategori.nama_sub','sub_sub_kategori.nama_sub_s');
				$products = $this->access->readtable('product',$select,array('product.deleted_at'=>null,'product.id_product'=> $id_product),array('kategori'=> 'kategori.id_kategori = product.id_kategori','sub_kategori'=> 'sub_kategori.id_sub = product.id_sub','sub_sub_kategori'=> 'sub_sub_kategori.id_sub_s = product.id_sub_sub'),'','','LEFT')->row();	
				
				$path = '';				
				if(!empty($products)){					
					$path = '';
					$variant = '';
					$variant = $this->access->readtable('product_variant','',array('deleted_at'=>null, 'id_variant'=>$id_variant))->row();
					
					$total = 0;
					$harga = 0;
					$hrg_stlh_diskon_t = 0;
					$pot_diskon_p = 0;
					$pot_diskon_t = 0;
					$diskon_product = 0;
					$diskon_product = (int)$products->diskon > 0 ? $products->diskon / 100 : 0;
					$harga = $variant->hrg;
					if($diskon_product > 0){
						$pot_diskon_p = (int)$products->diskon / 100 * $harga;
						$harga = $harga - $pot_diskon_p;
					}
					$hrg_stlh_diskon_t = $harga;
					if((int)$diskon > 0){
						$pot_diskon_t = $harga * $_diskon;
						$hrg_stlh_diskon_t = $harga - $pot_diskon_t;
					}
					$hrg_final = $hrg_stlh_diskon_t;
					$total = (int)$jml * $hrg_final;
					$ttl_all += $total;
					$path = !empty($products->img) ? base_url('uploads/products/'.$products->img) : base_url('uploads/no_photo.jpg');				
					
					
					$dt[] = array(
						'id_trans'			=> $save,						
						'id_product'		=> $products->id_product,						
						'id_variant'		=> $variant->id_variant,						
						'id_kategori'		=> $products->id_kategori,						
						'id_sub'			=> $products->id_sub,						
						'id_sub_sub'		=> $products->id_sub_sub,						
						'nama_barang'		=> $products->nama_barang,
						'nama_variant'		=> $variant->nama_variant,
						'kondisi'			=> $products->kondisi,
						'harga_asli'		=> $variant->hrg,						
						'diskon_product'	=> $products->diskon,
						'pot_diskon_p'		=> $pot_diskon_p,
						'hrg_stlh_diskon_p'	=> $harga,
						'diskon_tier'		=> $diskon,
						'pot_diskon_t'		=> $pot_diskon_t,
						'hrg_stlh_diskon_t'	=> $hrg_stlh_diskon_t,
						'hrg_final'			=> $hrg_final,
						'stok'				=> $products->qty,						
						'jml_beli'			=> $jml,
						'sisa_stok'			=> (int)$products->qty - (int)$jml,
						'total'				=> $total,
						'deskripsi'			=> $products->deskripsi,
						'img'				=> $path,						
						'nama_kategori'		=> $products->nama_kategori,						
						'nama_sub'			=> $products->nama_sub,						
						'nama_sub_s'		=> $products->nama_sub_s,						
						
					);
					// $this->access->inserttable('transaksi_detail',$dt);					
					$dt_upd[] = array(
						'id_product'	=> $products->id_product,						
						'qty'			=> (int)$products->qty - (int)$jml
					);
					
					if((int)$jml > (int)$products->qty){
						array_push($name_stok, $products->nama_barang);
						$dt_stock[] = array(							
							'id_product'		=> $products->id_product,						
							'id_variant'		=> $products->id_variant,						
							'id_kategori'		=> $products->id_kategori,						
							'id_sub'			=> $products->id_sub,						
							'id_sub_sub'		=> $products->id_sub_sub,						
							'nama_barang'		=> $products->nama_barang,
							'nama_variant'		=> $variant->nama_variant,
							'harga_asli'		=> $variant->harga,						
							'diskon_product'	=> $products->diskon,
							'pot_diskon_p'		=> $pot_diskon_p,
							'hrg_stlh_diskon_p'	=> $harga,
							'diskon_tier'		=> $diskon,
							'pot_diskon_t'		=> $pot_diskon_t,
							'hrg_stlh_diskon_t'	=> $hrg_stlh_diskon_t,
							'hrg_final'			=> $hrg_final,
							'stok'				=> $products->qty,						
							'jml_beli'			=> $jml,
							'sisa_stok'			=> (int)$products->qty - (int)$jml,
							'total'				=> $total,
							'deskripsi'			=> $products->deskripsi,
							'img'				=> $path,						
							'nama_kategori'		=> $products->nama_kategori,						
							'nama_sub'			=> $products->nama_sub,						
							'nama_sub_s'		=> $products->nama_sub_s,								
							
						);
					}
				}				
			}			
		}
		$ttl_all += $ongkir + $kode_unik;
		$ttl_all = ceil($ttl_all) - $diskon_voucher;
		$simpan_dt += array("status_name"=> $status_name[$_status],'total' => $ttl_all,'id_trans'=>$save, 'list_item'=>$dt);
		$redirect_url = '';
		$url_payment = '';
		if(count($dt_stock) > 0){
			$this->db->trans_rollback();
			$this->set_response([
				'err_code'	=> '06',
				'err_msg' 	=> 'Stok habis untuk produk '.implode(", ",$name_stok),
				'err_stok' 	=> $dt_stock,
				'data'		=> $simpan_dt
			], REST_Controller::HTTP_OK);
			return false;
		}
		if(count($dt_stock) == 0){			
			if($manual_transfer == 0){
				$billing_address = array(
					'first_name'	=> $_address->nama,
					'last_name'		=> $_address->surname,
					'email'			=> $_address->email,
					'phone' 		=> $_address->phone_member,	
					'address' 		=> $_address->alamat,	
					'city' 			=> $_address->nama_city,	
					'postal_code' 	=> $_address->kode_pos,	
					'country_code' 	=> 'IDN',	
				);
				$customer_details = array(
					'first_name'		=> $_address->nama,
					'last_name'			=> $_address->surname,
					'email'				=> $_address->email,
					'phone' 			=> $_address->phone_member,	
					'billing_address'	=> $billing_address,
					'shipping_address'	=> $billing_address,				
				);
				if($pengiriman == 1){
					unset($customer_details['billing_address']);
					unset($customer_details['shipping_address']);
				}
				$transaction_details = array(
					'order_id' 		=> $save,
					'gross_amount' 	=> $ttl_all
				);
				$credit_card = array('secure'=>true);
				$items = array();				
				for($i=0; $i<(int)count($dt); $i++){
					$items[] = array(
						'id' 		=> $dt[$i]['id_product'],
						'price' 	=> $dt[$i]['hrg_final'],
						'quantity'	=> (int)$dt[$i]['jml_beli'],
						'name'		=> $dt[$i]['nama_barang'],
					);
				}
				$items[] = array(
					'id' 		=> 'ONGKIR',
					'price' 	=>  $ongkir,
					'quantity'	=> 1,
					'name'		=> 'Ongkir',
				);
				if($id_myvoucher > 0){
					$items[] = array(
						'id' 		=> 'Voucher',
						'price' 	=> -$diskon_voucher,
						'quantity'	=> 1,
						'name'		=> $kode_voucher,
					);
				}
				$post_data = array();
				$post_data = array(
					'transaction_details'	=> $transaction_details,
					'credit_card'			=> $credit_card,
					'item_details'			=> $items,
					'customer_details'		=> $customer_details,
				);
				error_log(serialize($post_data));
				$_url_payment = $this->send_api->send_data('https://app.sandbox.midtrans.com/snap/v1/transactions', $post_data, 'Basic U0ItTWlkLXNlcnZlci1PMU9SMmQ2WFVlV0IyQjFzeWlTd1NXd1k6','', $method='POST');
				$url_payment = json_decode($_url_payment);
				$redirect_url = $url_payment->redirect_url;
			}
			$simpan_dt += array('url_payment'=>$redirect_url);
			$this->access->updatetable('transaksi', array('ttl_all' => $ttl_all,'ttl_cart'=>count($dt),'url_payment'=>$redirect_url), array('id_transaksi' => $save));
			if($id_myvoucher > 0) $this->access->updatetable('my_voucher',array('is_used'=>$tgl_now,'id_trans'=>$save),array('id'=>$id_myvoucher,'id_member'=>$id_member));
			$this->db->update_batch('product', $dt_upd, 'id_product');
			$this->db->insert_batch('transaksi_detail', $dt);						
		}
		
		$this->db->trans_commit();
		
		$this->set_response([
			'err_code' 		=> '00',
			'err_msg' 		=> 'Terima Kasih, Pesanan Anda Akan Kami Proses',
			'data'			=> $simpan_dt,
			
			'response_payment'	=> $url_payment,
			
			
		], REST_Controller::HTTP_OK);
	}
	
	function confirm_payment_post(){
		$tgl = date('Y-m-d H:i:s');
		$param = $this->input->post();		
		$id_transaksi = isset($param['id_transaksi']) ? (int)$param['id_transaksi'] : 0;
		$bank = isset($param['bank']) ? $param['bank'] : '';
		$sender_name = isset($param['sender_name']) ? $param['sender_name'] : '';
		$no_rek = isset($param['no_rek']) ? $param['no_rek'] : '';
		$note = isset($param['note']) ? $param['note'] : '';
		$amount = isset($param['amount']) ? str_replace(',','',$param['amount']) : '';
		$date_transfer = isset($param['date_transfer']) ? date('Y-m-d', strtotime($param['date_transfer'])) : '';
		$config = array();
		$config['upload_path'] = "./uploads/payment/";
		$config['allowed_types'] = "jpg|png|jpeg|";
		$config['max_size']	= '4096';		
		$config['encrypt_name'] = TRUE;
		$this->load->library('upload',$config);
		$simpan = array();
		$simpan = array(
			'confirm_date'		=> $tgl,
			'confirm_bank'		=> $bank,
			'confirm_sender'	=> $sender_name,
			'confirm_rek'		=> $no_rek,
			'confirm_note'		=> $note,
			'confirm_amount'	=> $amount,
			'tgl_transfer'		=> $date_transfer
		);
		
		if(!empty($_FILES['img'])){
			$upl = '';
			if($this->upload->do_upload('img')){
				$upl = $this->upload->data();
				$simpan += array("confirm_img" => $upl['file_name']);			
			}
		}
		$this->access->updatetable('transaksi', $simpan, array('id_transaksi'=>$id_transaksi));
		$result = [
				'err_code'	=> '00',
				'err_msg'	=> 'ok',
				'data'		=> $simpan
			];
		$this->set_response($result, REST_Controller::HTTP_OK);	
	}
	
	
	function refund_post(){
		$tgl = date('Y-m-d H:i:s');
		$param = $this->input->post();		
		$id_transaksi = isset($param['id_transaksi']) ? (int)$param['id_transaksi'] : 0;
		$good_details = isset($param['good_details']) ? $param['good_details'] : '';
		$refund_reason = isset($param['refund_reason']) ? $param['refund_reason'] : '';
		$refund_amount = isset($param['refund_amount']) ? $param['refund_amount'] : '';
		$bank_name = isset($param['bank_name']) ? $param['bank_name'] : '';
		$account_holder = isset($param['account_holder']) ? $param['account_holder'] : '';
		$account_no = isset($param['account_no']) ? $param['account_no'] : '';
		$config = array();
		$config['upload_path'] = "./uploads/complain/";
		$config['allowed_types'] = "jpg|png|jpeg|";
		$config['max_size']	= '4096';		
		$config['encrypt_name'] = TRUE;
		$this->load->library('upload',$config);
		$simpan = array();
		$simpan = array(
			'complain_date'		=> $tgl,
			'complain_type'		=> 1,
			'good_details'		=> $good_details,
			'refund_reason'		=> $refund_reason,
			'address_bn'		=> $bank_name,
			'acc_holder_cp'		=> $account_holder,
			'acc_no_hp'			=> $account_no,
			'refund_amount'		=> $refund_amount,
			'status_complain'	=> 1,
			'status'			=> 8,
		);
		if(!empty($_FILES['signature'])){
			$upl = '';
			if($this->upload->do_upload('signature')){
				$upl = $this->upload->data();
				$simpan += array("signature" => $upl['file_name']);			
			}
		}
		$this->access->updatetable('transaksi', $simpan, array('id_transaksi'=>$id_transaksi,'status'=>5));
		
		$result = [
				'err_code'	=> '00',
				'err_msg'	=> 'ok',
				'data'		=> $simpan
			];
		$this->set_response($result, REST_Controller::HTTP_OK);	
	}
	
	function return_post(){
		$tgl = date('Y-m-d H:i:s');
		$param = $this->input->post();		
		$id_transaksi = isset($param['id_transaksi']) ? (int)$param['id_transaksi'] : 0;
		$good_details = isset($param['good_details']) ? $param['good_details'] : '';
		$refund_reason = isset($param['refund_reason']) ? $param['refund_reason'] : '';
		$address = isset($param['address']) ? $param['address'] : '';		
		$contact_person = isset($param['contact_person']) ? $param['contact_person'] : '';		
		$phone_number = isset($param['phone_number']) ? $param['phone_number'] : '';
		$config = array();
		$config['upload_path'] = "./uploads/complain/";
		$config['allowed_types'] = "jpg|png|jpeg|";
		$config['max_size']	= '4096';		
		$config['encrypt_name'] = TRUE;
		$this->load->library('upload',$config);
		$simpan = array();
		$simpan = array(
			'complain_date'		=> $tgl,
			'complain_type'		=> 2,
			'good_details'		=> $good_details,
			'refund_reason'		=> $refund_reason,
			'address_bn'		=> $address,
			'acc_holder_cp'		=> $contact_person,
			'acc_no_hp'			=> $phone_number,			
			'status_complain'	=> 1,
			'status'			=> 8,
		);
		if(!empty($_FILES['signature'])){
			$upl = '';
			if($this->upload->do_upload('signature')){
				$upl = $this->upload->data();
				$simpan += array("signature" => $upl['file_name']);			
			}
		}
		$this->access->updatetable('transaksi', $simpan, array('id_transaksi'=>$id_transaksi,'status'=>5));
		$result = [
				'err_code'	=> '00',
				'err_msg'	=> 'ok',
				'data'		=> $simpan
			];
		$this->set_response($result, REST_Controller::HTTP_OK);	
	}
	
	function cancel_transaksi_post(){
	    $param = $this->input->post();
		$id_transaksi = isset($param['id_transaksi']) ? (int)$param['id_transaksi'] : '';
		$transaksi = $this->access->readtable('transaksi','', array('id_transaksi' => $id_transaksi))->row();
		
		$status = (int)$transaksi->status;
	
		if($status > 0){
			$this->db->trans_rollback();
				$this->set_response([
					'err_code'	=> '06',
					'err_msg' 	=> 'transaksi tidak bisa dicancel',
					'data'		=> null
				], REST_Controller::HTTP_OK);
				return false;
		}
		$tgl_now = date('Y-m-d H:i:s');
		$transaksi_detail = $this->access->readtable('transaksi_detail',array('id_product','jml_beli'),array('id_trans'=>$id_transaksi))->result_array();
		$id_ta = array();
		$td = array();
		$dt_upd = array();
		$id_product = '';
		if(!empty($transaksi_detail)){
			foreach($transaksi_detail as $td){
				$id_ta[] = '"'.$td['id_product'].'"';
				$id_product = implode(',',$id_ta);		
				$td['id_product'] = $td['jml_beli'];
			}
			$sql_p = 'SELECT id_product, qty FROM product WHERE id_product IN ('.$id_product.')';
			$dt_p = $this->db->query($sql_p)->result_array();
			if(!empty($dt_p)){
				foreach($dt_p as $dp){
					$dt_upd[$dp['id_product']] = array(
						'id_product'	=> $dp['id_product'],
						'qty'			=> (int)$dp['qty'] + (int)$td[$dp['id_product']],
					);
				}
			}
			$this->db->update_batch('product', $dt_upd, 'id_product');
		}
		
		$where = array('status'=>0,'id_transaksi'=>$id_transaksi,'date_format(expired_payment, "%Y-%m-%d") <= '=>$tgl_now);
		$this->access->updatetable('transaksi', array('status'=>9), $where);	
		error_log($this->db->last_query());
		$result = [
				'err_code'	=> '00',
				'err_msg'	=> 'ok',
				'data'		=> $id_transaksi
			];
		$this->set_response($result, REST_Controller::HTTP_OK);	
	}
	
}