<style type="text/css">
	.row * {
		box-sizing: border-box;
	}
	.kotak_judul {
		 border-bottom: 1px solid #fff; 
		 padding-bottom: 2px;
		 margin: 0;
	}
	.box-header {
		color: #444;
		display: block;
		padding: 10px;
		position: relative;
	}
	.toggle.ios, .toggle-on.ios, .toggle-off.ios { border-radius: 20px; }
	.toggle.ios .toggle-handle { border-radius: 20px; }
</style>
<?php
$tanggal = date('Y-m');
$txt_periode_arr = explode('-', $tanggal);
	if(is_array($txt_periode_arr)) {
		$txt_periode = $txt_periode_arr[1] . ' ' . $txt_periode_arr[0];
	}
$_disabled = '';
if($_LEVEL == 1){
	// $_disabled = ' hide';
}
?>
<div class="modal fade" role="dialog" id="import_dialog">
          <div class="modal-dialog" style="width:800px">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><strong>Verifikasi Payment</strong></h4>
              </div>
			 
              <div class="modal-body">
				<form role="form" id="frm_import" method="post" enctype="multipart/form-data" accept-charset="utf-8" autocomplete="off">
				<div class="row">
				<div class="col-md-6">
				<div class="form-group">
                  <label for="pemeriksa">Confirmation date</label>
				  <input type="text" class="form-control" name="confirm_date" id="confirm_date" placeholder="Name" readonly>
                  <input type="hidden" class="form-control" name="id_trans" id="id_trans" value="">
                </div>
				<div class="form-group">
					<label for="pemeriksa">Bank</label>
					<input type="text" class="form-control" name="confirm_bank" id="confirm_bank" placeholder="Bank" readonly>		
                </div>
				<div class="form-group">
					<label for="pemeriksa">Sender</label>
					<input type="text" class="form-control" name="confirm_sender" id="confirm_sender" placeholder="Sender dan No.Rekening" readonly>		
                </div> 
				<div class="form-group">
					<label for="pemeriksa">Amount</label>
					<input type="text" class="form-control" name="confirm_rek" id="confirm_rek" placeholder="No.Rekening" readonly>		
                </div> 
                </div>
				<div class="col-md-6">
				
				
				<div class="form-group">
                  <label for="pemeriksa">Photo bukti pembayaran</label><span class="label label-danger pull-right deskripsi_error"></span>
					<div class="fileupload-new thumbnail" style="width: 350px; height: 255px;">
						<img id="blah_selfie" style="width: 350px; height: 240px;" src="" alt="">
					</div>
                </div>
				
                </div>
				<div class="col-sm-12">
				<div class="form-group">
					<label for="pemeriksa">Note</label>
					<input type="text" class="form-control" name="note" id="note" placeholder="Note" readonly>		
                </div> 
                </div>
                </div>
				
              </div>
              <div class="modal-footer verify_acc" style="margin-top:0px;">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>               
                <button type="button" class="btn btn-warning btn_rej">Reject</button>               
                <button type="button" class="btn btn-success btn_appr">Approve</button>               
              </div>
            </div>
			</form>
            <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
</div>

<div class="modal fade" role="dialog" id="confirm_del">
          <div class="modal-dialog" style="width:370px">
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
 
<div class="box-body">
<div class='alert alert-info alert-dismissable' id="success-alert">

    <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>
    <div id="id_text"><b>Welcome</b></div>
</div>
<form action="" method="post" autocomplete="off" class="pull-right" id="search_report">
        <label>Search From</label>
        <input type="text" name="froms" value="<?php echo $froms;?>" class="datepicker" required id="froms">
        <label>To</label>
        <input type="text" name="to" value="<?php echo $to;?>" class="datepicker" required id="to">
        <input type="hidden" name="status" value="<?php echo $status;?>">
        <input type="hidden" name="payment" value="<?php echo $payment;?>">
        <button type="submit" class="btn btn-xs btn-success" style="height:27px;"><i class="glyphicon glyphicon-search"></i> Search</button>
		<button type="button" class="btn btn-xs btn-danger res" style="height:27px;"><i class="glyphicon glyphicon-refresh"></i> Reset</button>
        <button type="button" id="print" class="btn btn-xs btn-primary hide" style="height:27px;"><i class="glyphicon glyphicon-print"></i> Export</button>               
    </form>
	<table id="example88" class="table table-bordered table-striped">
		<thead><tr>
			<th style="text-align:center; width:4%">No.</th>
			<th style="text-align:center; width:9%">Date</th>  
			<th style="text-align:center; width:23%">Transaction</th>
            <th style="text-align:center; width:20%">Member</th>           		
			<th style="text-align:center; width:8%">Action</th> 
           
		</tr>
		</thead>
		<tbody>
			<?php 
				$i =1;				
				$info = '';			
				$payment = '';			
				$_status = '-';			
				$_action = '-';			
				if(!empty($transaksi)){		
					foreach($transaksi as $t){						
						$payment = '';
						$_action = '';
						$_status = '';
						$payment = '<strong>'.$t['payment_name'].' '.$t['nama_bank'].'</strong>';
												
						$total = '';
						$total = number_format($t['total'],2,'.',',');						
						$path_payment = !empty($t['confirm_img']) ? base_url('uploads/payment/'.$t['confirm_img']) : base_url('uploads/no_photo.jpg');
						$_info = '';
						$_info = $t['id_transaksi'].'Þ'.$t['bank_name'].'Þ'.$t['confirm_bank'].'Þ'.$t['confirm_sender'].'Þ'.$t['confirm_rek'].'Þ'.date('d-m-Y',strtotime($t['tgl_transfer'])).'Þ'.$path_payment.'Þ'.$t['confirm_note'].'Þ'.number_format($t['confirm_amount'],2,'.',',');
						
						$_disabled = '';
						$info = $this->converter->encode($t['id_transaksi']);						
						
						
						if($t['status_complain'] == 2){
							$_status = '<small class="label label-default"><strong>Approved</strong></small>';
							
						}
						if($t['status_complain'] == 3){
							$_status = '<small class="label label-danger"><strong>Rejected</strong></small>';
						}
						$_action .= '<a href="'.site_url('complain/detail/'.$info).'"><button style="margin-top : 5px; width:69px;" title="View" class="btn btn-xs btn-info"><i class="fa fa-eye"></i> View</button></a>';
						echo '<tr>';
						echo '<td align="center">'.$i++.'.</td>';						
						echo '<td>'.date("d M Y H:i", strtotime($t['complain_date'])).'<br/>'.$_status.'</td>';
												
						
						if($t['payment'] == 3 && $t['status'] > 0){
							echo '<td>Id Trans : '.$t['id_transaksi'].'<br/>Total : <a href="#import_dialog" title="View data payment" id="'.$_info.'">'.number_format($t['ttl_all'],2,',','.').'</a></td>';
						}else{
							echo '<td>Id Trans : '.$t['id_transaksi'].'<br/>Total : '.number_format($t['ttl_all'],2,',','.').'</td>';
						}
						echo '<td>'.$t['nama_member'].' - '.$t['phone_member'].'<br/> Email : '.$t['email_member'].'</td>';	
						echo '<td align="center">'.$_action.'</td>';						
						echo '</tr>';
					}
					
				}				
			?>
		</tbody>
	
	</table>
</div>

</div>
<link href="<?php echo base_url(); ?>assets/datetimepicker/jquery.datetimepicker.css" rel="stylesheet" type="text/css" />	
<script src="<?php echo base_url(); ?>assets/datetimepicker/jquery.datetimepicker.js"></script>

<script src="<?php echo base_url(); ?>assets/theme_admin/js/plugins/datatables/jquery.dataTables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/theme_admin/js/plugins/datatables/dataTables.bootstrap.js" type="text/javascript"></script>


<script type="text/javascript">
$("#success-alert").hide();
$("input").attr("autocomplete", "off"); 
var date_now = '<?php echo date('d/m/Y');?>';
$('.btn_confirm').click(function(){
	$('.verify_acc').show();
	$('#frm_cat').find("input[type=text], select").val("");	
	$('#blah_selfie').attr('src', '');
	var val = $(this).get(0).id;
	var dt = val.split('Þ');
	$('#id_trans').val(dt[0]);
	$('#confirm_date').val(dt[5]);
	$('#confirm_bank').val(dt[2]);	
	$('#confirm_sender').val(dt[3]);	
	$('#confirm_rek').val(dt[4]);	
	$('#blah_selfie').attr('src', dt[6]);
	
	$('#import_dialog').modal({
		backdrop: 'static',
		keyboard: false
	});
	$('#import_dialog').modal('show');
});
$('a[href$="#import_dialog"]').on( "click", function() {
	$('.verify_acc').hide();
	$('#blah_selfie').attr('src', '');
	var val = $(this).get(0).id;
	var dt = val.split('Þ');
	$('#id_trans').val(dt[0]);
	$('#confirm_date').val(dt[5]);
	$('#confirm_bank').val(dt[2]);	
	$('#confirm_sender').val(dt[3]+' - '+dt[4]);	
	$('#confirm_rek').val(dt[8]);	
	$('#blah_selfie').attr('src', dt[6]);
	$('#note').val(dt[7]);	
	$('#import_dialog').modal({
		backdrop: 'static',
		keyboard: false
	});
   $('#import_dialog').modal('show');
});
$('.res').click(function(){
	window.location.href = '<?php echo $url_report;?>';
});
$("#print").click(function(){	
	var url = '<?php echo site_url('transaksi/export_r');?>';
	$('#search_report').attr('action', url);
	$('#search_report').submit();
	setTimeout (submit2, 2000);
});
function submit2(){
	var url = '<?php echo site_url('transaksi/export_r2');?>';
	$('#search_report').attr('action', url);
	$('#search_report').submit();
	$('#search_report').attr('action', '');
}
$('.btn_send').click(function(){
	var val = $(this).get(0).id;
	$('#del_id').val(val);
	$('#nilai').val(4);
	$('.text_warning').html('Apakah anda yakin ?');	
	$('.text_warning').html('Apakah anda yakin untuk <br><strong> mengirim</strong> paket pada transaksi ini ? ');
	$('#confirm_del').modal({
		backdrop: 'static',
		keyboard: false
	});
	$("#confirm_del").modal('show');
});
$('.btn_sampai').click(function(){
	var val = $(this).get(0).id;
	$('#del_id').val(val);
	$('#nilai').val(5);
	$('.text_warning').html('Apakah anda yakin ?');	
	$('.text_warning').html('Apakah anda yakin paket pada transaksi ini<br><strong> sampai tujuan</strong> ? ');
	$('#confirm_del').modal({
		backdrop: 'static',
		keyboard: false
	});
	$("#confirm_del").modal('show');
});
$('.btn_rej').click(function(){
	var id_trans = $('#id_trans').val();
	var url = '<?php echo site_url('transaksi/upd_status');?>';
	$.ajax({
		data : {id_trans : id_trans,nilai:2},
		url : url,
		type : "POST",
		success:function(response){
			$('#import_dialog').modal('hide');
			$("#id_text").html('<b>Success,</b> Data telah di reject');
			$("#success-alert").fadeTo(2000, 500).slideUp(500, function(){
				$("#success-alert").alert('close');
				location.reload();
			});			
		}
	});
});
$('.btn_appr').click(function(){
	var id_trans = $('#id_trans').val();
	var url = '<?php echo site_url('transaksi/upd_status');?>';
	$.ajax({
		data : {id_trans : id_trans,nilai:3},
		url : url,
		type : "POST",
		success:function(response){
			$('#import_dialog').modal('hide');
			$("#id_text").html('<b>Success,</b> Data telah di approve');
			$("#success-alert").fadeTo(2000, 500).slideUp(500, function(){
				$("#success-alert").alert('close');
				location.reload();
			});			
		}
	});
});
$('.appr').click(function(){
	var val = $(this).get(0).id;
	$('#del_id').val(val);
	$('#nilai').val(3);
	$('.text_warning').html('Apakah anda yakin ?');	
	$('.text_warning').html('Apakah anda yakin untuk <br><strong> memproses</strong> transaksi ini ? ');
	$('#confirm_del').modal({
		backdrop: 'static',
		keyboard: false
	});
	$("#confirm_del").modal('show');
});

$('.reject').click(function(){
	var val = $(this).get(0).id;
	$('#del_id').val(val);
	$('#nilai').val(2);
	$('.text_warning').html('Apakah anda yakin ?');	
	$('.text_warning').html('Apakah anda yakin untuk <strong> menolak</strong> transaksi ini ? ');
	$('#confirm_del').modal({
		backdrop: 'static',
		keyboard: false
	});
	$("#confirm_del").modal('show');
});

$('.yes_app').click(function(){
	var id = $('#del_id').val();
	var nilai = $('#nilai').val();
	var url = '<?php echo site_url('transaksi/upd_status');?>';
	var status = '';
	$.ajax({
		data : {id : id, nilai : nilai},
		url : url,
		type : "POST",
		success:function(response){
			
			$('#confirm_del').modal('hide');
			if(nilai == 3){
				status = 'approve';
			}
			if(nilai == 2){
				status = 'reject';
			}
			if(nilai == 4){
				status = 'dikirim';
			}
			if(nilai == 5){
				status = 'sampai tujuan';
			}
			$("#id_text").html('<b>Success,</b> Data telah di '+status);
			$("#success-alert").fadeTo(2000, 500).slideUp(500, function(){
				$("#success-alert").alert('close');
				location.reload();
			});			
		}
	});
	
});
$('#froms').datetimepicker({
	dayOfWeekStart : 1,
	changeYear: false,
	timepicker:false,
	scrollInput:false,
	format:'d-m-Y',
	lang:'en',
		
	onChangeDateTime: function (fom) {
		$("#to").datetimepicker({
            minDate: fom
		});       
	}
});
$('#to').datetimepicker({
	dayOfWeekStart : 1,
	changeYear: false,
	timepicker:false,
	scrollInput:false,
	format:'d-m-Y',
	lang:'en',
	
	maxDate:date_now,	
	onChangeDateTime: function (to) {
		$("#forms").datetimepicker({
            maxDate: to
		});
       
	}
       
});

$(function() {               
    $('#example88').dataTable({
		bFilter : false
	});
});


</script>