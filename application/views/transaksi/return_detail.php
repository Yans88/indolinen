<style type="text/css">
	.row * {
		box-sizing: border-box;
	}
	.kotak_judul {
		 border-bottom: 1px solid #fff; 
		 padding-bottom: 2px;
		 margin: 0;
	}
	.table > tbody > tr > td{
		vertical-align : middle;
	}
	
	.direct-chat-msg:before, .direct-chat-msg:after {
		content: " ";
		display: table;
	}
	:after, :before {
		-webkit-box-sizing: border-box;
		-moz-box-sizing: border-box;
		box-sizing: border-box;
	}
	
	.direct-chat-msg:before, .direct-chat-msg:after {
		content: " ";
		display: table;
	}
	:after, :before {
		-webkit-box-sizing: border-box;
		-moz-box-sizing: border-box;
		box-sizing: border-box;
	}
	
	.direct-chat-messages, .direct-chat-contacts {
		-webkit-transition: -webkit-transform .5s ease-in-out;
		-moz-transition: -moz-transform .5s ease-in-out;
		-o-transition: -o-transform .5s ease-in-out;
		transition: transform .5s ease-in-out;
	}
	.direct-chat-messages {
		-webkit-transform: translate(0, 0);
		-ms-transform: translate(0, 0);
		-o-transform: translate(0, 0);
		transform: translate(0, 0);
		padding: 10px;
		height: 300px;
		overflow: auto;
	}
	
	.direct-chat-msg, .direct-chat-text {
		display: block;
	}	
	.direct-chat-info {
		display: block;
		margin-bottom: 2px;
		font-size: 12px;
	}
	
	.direct-chat-text:before {
		border-width: 8px !important;
		margin-top: 3px;
	}
	.direct-chat-text:before {
		position: absolute;
		right: 100%;		
		
		border-right-color: #d2d6de;
		content: ' ';
		height: 0;
		width: 0;
		pointer-events: none;
	}
	
	.direct-chat-text {
		display: block;
	}
	.direct-chat-text {
		border-radius: 5px;
		position: relative;
		padding: 5px 10px;
		background: #d2d6de;
		border: 1px solid #d2d6de;
		margin: 5px 0 0 5px;
		color: #444;
	}
	.direct-chat-warning .right>.direct-chat-text{
		background: #ddd;
		border-color: #ddd;
		color: #000;
	}
	.table-bordered {
		border: none;
	}
	.msg_history {
	  height: 415px;
	  overflow-y: auto;
	}
	.tbl_history {
	  height: 340px;
	  overflow-y: auto;
	}
	.products-list {
    list-style: none;
		margin: 5px;
		padding: 0;
	}
	.products-list>.item {
		
		padding: 10px 0;
		background: #fff;
	}
	.product-list-in-box>.item {
		-webkit-box-shadow: none;
		box-shadow: none;
		border-radius: 0;
		border-bottom: 1px solid #ddd;
	}
</style>

<?php 
$disabled = '';

if($_id_merchant != $transaksi->id_principle){
	// $disabled = ' disabled';
}
$status_name = array(
		'0'=> '<small class="label label-warning"><strong>Waiting approval</strong></small>', 
		'1'=> '', 
		'3'=> '<small class="label label-danger"><strong>Rejected</strong></small>',		
		'2'=> '<small class="label label-default"><strong>Approved</strong></small>',
		
	);

$tgl = '';
$month = '';
$tgl = date('Y-m-d');
$month = date('Y-m');
$payment = $transaksi->payment_name.' <b>'.$transaksi->nama_bank.'</b>';
if($transaksi->payment == 2){
	$payment = $transaksi->payment_name.' '.$transaksi->tempo.' x '.number_format($transaksi->angs,2,',','.');
	if($transaksi->status > 2){
		echo '<div class="row">
		<div class="col-md-8">';
	}
	
}
$nama_pengiriman = $transaksi->nama_pengiriman;
$ttl_all = $transaksi->ttl_all + $transaksi->ongkir;
if(!empty($transaksi->kurir_code)) $nama_pengiriman .= '('.ucwords($transaksi->kurir_code).' - '.$transaksi->kurir_service.')';
?>
<div class="modal fade" role="dialog" id="confirm_del">
          <div class="modal-dialog" style="width:300px">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><strong>Confirmation</strong></h4>
              </div>
			 
              <div class="modal-body">
				<h4 class="text-center text_warning">Apakah anda yakin ? </h4>
				<input type="hidden" id="del_id" nama="del_id" value="">
                <input type="hidden" id="nilai" nama="nilai" value="">
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Tidak</button>
                <button type="button" class="btn btn-success yes_app">Ya</button>               
              </div>
            </div>
            <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
</div>

<div class="box box-success">
<div class="box-header with-border hide">
              <h3 class="box-title">No.Transaksi #<?php echo $transaksi->id_transaksi;?></h3>

		<form action="" method="post" autocomplete="off" class="pull-right" id="search_report" style="margin-top:5px; margin-right:5px;">
       
        <input type="hidden" name="id_transaksi" value="<?php echo $transaksi->id_transaksi;?>">        
        
        <button type="button" id="print" class="btn btn-xs btn-primary" style="height:27px;"><i class="glyphicon glyphicon-print"></i> Export</button>               
    </form>             
            </div>
<div class="box-body">
<?php 
if($transaksi->payment == 2 && $transaksi->status > 2){
	
	echo '<div class="tbl_history">';	
}
$signature = !empty($transaksi->signature) ? base_url('uploads/complain/'.$transaksi->signature) : base_url('uploads/no_photo.jpg');
?>	
	<table class="table table-bordered table-reponsive">
		<tbody>
		<tr class="header_kolom">
			<th style="vertical-align: middle; text-align:center">Signature</th>
			<th style="vertical-align: middle; text-align:center">Complain - Return</th>
			
		</tr>
		<tr>
        	<td class="h_tengah first" style="vertical-align:middle; width:15%">
			
			<a class="" href="<?php echo $signature;?>" title="Signature">	
			<img height="150" width="150" src="<?php echo $signature;?>"/></a> 	
			</td>
			<td class="h_tengah" style="vertical-align:middle;">
			<table class="table table-responsive">
				<tbody>
					<tr style="vertical-align:middle; text-align:left">
						<td style="width:15%;"><b>Tanggal</b></td>						
						<td style="width:1%;">:</td>						
						<td style="width:34%;"><?php echo date('d M Y', strtotime($transaksi->complain_date)).' '.$status_name[$transaksi->status_complain];?></td>
                        <td style="width:15%;"><b>Contact Person</b></td>
						<td style="width:1%;">:</td>
						<td><?php echo !empty($transaksi->acc_holder_cp) ? $transaksi->acc_holder_cp : '-';?></td>	
						
					</tr>
                    	
					<tr style="vertical-align:middle; text-align:left">
						<td><b>Inv</b></td>
						<td style="width:1%;">:</td>
						<td><?php echo $transaksi->id_transaksi?>
						
						<td><b>Phone Number</b></td>
						<td style="width:1%;">:</td>
						<td><?php echo !empty($transaksi->acc_no_hp) ? $transaksi->acc_no_hp : '-';?></td>	
						
						
						
					</tr>				
					
					
					<tr style="vertical-align:middle; text-align:left"> 
							
						<td><b>Address</b></td>
						<td style="width:1%;">:</td>
						<td colspan=4><?php echo !empty($transaksi->address_bn) ?  $transaksi->address_bn : '-';?></td>	 
						
					</tr>
					
					<tr style="vertical-align:middle; text-align:left"> 
						
						<td><b>Good Details</b></td>
						<td style="width:1%;">:</td>
						<td colspan=4><?php echo $transaksi->good_details;?></td>									
					</tr>
					<tr style="vertical-align:middle; text-align:left"> 
						
						<td><b>Return Reason</b></td>
						<td style="width:1%;">:</td>
						<td colspan=4><?php echo $transaksi->refund_reason;?></td>									
					</tr>
				</tbody>
			</table>
			</td>									
		</tr>
		
	</tbody></table>

	<table class="table table-bordered table-reponsive">
		<tbody>
		<tr class="header_kolom">
			<th style="vertical-align: middle; text-align:center; width:50%;" >Transaksi</th>
			
		</tr>
		<tr>
        	
			<td class="h_tengah" style="vertical-align:middle;">
			<table class="table table-responsive">
				<tbody>
					<tr style="vertical-align:middle; text-align:left">
						<td style="width:15%;"><b>Tanggal</b></td>						
						<td style="width:1%;">:</td>						
						<td style="width:34%;"><?php echo date('d M Y', strtotime($transaksi->created_at))?></td>
                        <td style="width:12%;"><b>Payment</b></td>		
						<td style="width:1%;">:</td>							
						<td><?php echo $payment;?></td>		
						</td>
						
					</tr>
                    	
					<tr style="vertical-align:middle; text-align:left">
						<td><b>Member</b></td>
						<td style="width:1%;">:</td>
						<td><?php echo $transaksi->nama_member.' - '.$transaksi->phone_member;?>
						
						<td><b>Email Member</b></td>
						<td style="width:1%;">:</td>
						<td><?php echo $transaksi->email_member;?></td>	
						
						
						
					</tr>				
					
					
					<tr style="vertical-align:middle; text-align:left"> 
							
						<td><b>Diskon Tier</b></td>
						<td style="width:1%;">:</td>
						<td><?php echo !empty($transaksi->diskon_tier) ? $transaksi->diskon_tier.'%' : '-';?></td>	
						<td><b>Pengiriman</b></td>
						<td style="width:1%;">:</td>
						<td><?php echo $nama_pengiriman;?></td>		
					</tr>
					<tr style="vertical-align:middle; text-align:left"> 
						
						<td><b>Alamat Pengiriman</b></td>
						<td style="width:1%;">:</td>
						<td colspan=4><?php echo $transaksi->nama_penerima.', '.$transaksi->alamat_penerima.'-'.$transaksi->nama_city.', '.$transaksi->nama_provinsi.', '.$transaksi->kode_pos.', '.$transaksi->phone_penerima;?></td>									
					</tr>
				</tbody>
			</table>
			</td>									
		</tr>
		
	</tbody></table>
    
	<table class="table table-bordered table-reponsive">
		<tbody><tr class="header_kolom">
			<th style="vertical-align: middle; text-align:center; width:50%;" >Daftar Produk</th>
			
		</tr>
		
		<tr style="vertical-align:middle; text-align:left">
			<table class="table table-bordered table-reponsive">
				<thead>
					<tr>
						<td style="text-align:center; width:4%"><b>No.</b></td>
						<td style="text-align:center; width:20%"><b>Nama Produk</b></td>
									
							
						<td style="text-align:center; width:10%"><b>Harga</b></td>		
						<td style="text-align:center; width:7%"><b>Qty</b></td>		
						<td style="text-align:center; width:23%"><b>Note</b></td>		
							
						<td style="text-align:center; width:13%"><b>Subtotal</b></td>
				   
					</tr>
				</thead>
				<tbody>
					<?php 
						$i = 1;
						$cnt_addon = 0;
						$ttl_disc = 0;
						$nama_option = '';
						$note_menu = '';
						$sub_ttl = '0.00';
						$addon = array();
						if(!empty($transaksi_detail)){
							foreach($transaksi_detail as $_td){								
								echo '<tr>';
								echo '<td align="center">'.$i.'.</td>';
								if((int)$_td['paket'] > 0){
									if(!empty($paket_detail)){
										echo '<td>'.$_td['nama_barang'];
										foreach($paket_detail as $pd){
											if($pd['id_paket'] == $_td['id_product']){
												echo '<br/>- '.$pd['jml'].' '.$pd['nama_barang'];
											}
										}
										echo '</td>';
									}else{
										echo '<td>'.$_td['nama_barang'].'</td>';
									}									
								}else{
									echo '<td>'.$_td['nama_barang'].'</td>';
								}				
															
								echo '<td align="right">'.number_format($_td['hrg_final'],2,',','.').'</td>';								
								echo '<td align="center">'.$_td['jml_beli'].'</td>';
								echo '<td align="center">'.$_td['note'].'</td>';
																
								echo '<td align="right">'.number_format($_td['total'],2,',','.').'</td>';
								echo '</tr>';
								
								$i++;
							}
							echo '<tr style="border:none;">';
							
							echo '<td style="border:none;"></td>';
							echo '<td style="border:none;"></td>';
							echo '<td style="border:none;"></td>';
							echo '<td style="border:none;"></td>';
							echo '<td align="right" style="border:none;"><b>Sub Total</b></td>';							
							echo '<td align="right"><b>'.number_format($transaksi->ttl_all,2,',','.').'</b></td>';
							echo '</tr>';
							echo '<tr style="border:none;">';
							
							echo '<td style="border:none;"></td>';
							echo '<td style="border:none;"></td>';
							echo '<td style="border:none;"></td>';
							echo '<td style="border:none;"></td>';
							echo '<td align="right" style="border:none;"><b>Biaya Kirim - '.$transaksi->courier_service.'('.$transaksi->ttl_weight.' Gram)</b></td>';			
							echo '<td align="right"><b>'.number_format($transaksi->ongkir,2,',','.').'</b></td>';
							echo '</tr>';
							
							
							
							if($transaksi->kode_unik > 0){						
								echo '<tr style="border:none;">';							
								echo '<td style="border:none;"></td>';
								echo '<td style="border:none;"></td>';
								echo '<td style="border:none;"></td>';
								echo '<td style="border:none;"></td>';
								echo '<td align="right" style="border:none;"><b>Kode Unik</b></td>';							
								echo '<td align="right"><b>'.number_format($transaksi->kode_unik,2,',','.').'</b></td>';
								echo '</tr>';
							}
							
							
							echo '<tr style="border:none;">';
							
							echo '<td style="border:none;"></td>';
							echo '<td style="border:none;"></td>';
							echo '<td style="border:none;"></td>';
							echo '<td style="border:none;"></td>';
							echo '<td align="right" style="border:none;"><b>Total</b></td>';							
							echo '<td align="right"><b>'.number_format($ttl_all,2,',','.').'</b></td>';
							echo '</tr>';
							
						}
					?>
				</tbody>
			</table>
		
	
			
		
		</tr>
	</tbody>
	</table>
<?php 
if($transaksi->status_complain == 1){	
	echo '</div>';
?>
<div class="box-footer clearfix">
	<div class="clearfix"></div>
	<div class="pull-right">
	<button type="button" class="btn btn-small btn-danger btn_appr" disabled><i class="glyphicon glyphicon-remove"></i> Reject</button>			
	<button type="button" class="btn btn-small btn-info byr_reject" disabled><i class="glyphicon glyphicon-ok"></i> Approve</button>				
	</div>
</div>
<?php } ?>

</div>



<script>
$('.btn_back').click(function(){
	window.location = '<?php echo $url;?>';
});
var id_transaksi = '<?php echo $this->converter->encode($transaksi->id_transaksi);?>';
$('.first').magnificPopup({
		delegate: 'a',
		type: 'image',
		tLoading: 'Loading image #%curr%...',
		mainClass: 'mfp-img-mobile',
		closeOnContentClick: true,
		closeBtnInside: false,
		fixedContentPos: true,
		gallery: {
			enabled: true,
			navigateByImgClick: true,
			preload: [0,1] // Will preload 0 - before current, and 1 after the current image
		},
		image: {
			tError: '<a href="%url%">The image #%curr%</a> could not be loaded.',
			titleSrc: function(item) {
				return item.el.attr('title');
				// return item.el.attr('title') + '<small>by Marsel Van Oosten</small>';
			}
		}
	});
function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}
</script>