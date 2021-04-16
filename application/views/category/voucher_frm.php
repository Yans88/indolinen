<style type="text/css">
.row * {
    box-sizing: border-box;
}

.kotak_judul {
    border-bottom: 1px solid #fff;
    padding-bottom: 2px;
    margin: 0;
}

.table>tbody>tr>td {
    vertical-align: middle;
}

.custom-file-input::-webkit-file-upload-button {
    visibility: hidden;
}

.custom-file-input::before {
    content: 'Select Photo';
    display: inline-block;
    background: -webkit-linear-gradient(top, #f9f9f9, #e3e3e3);
    border: 1px solid #999;
    border-radius: 3px;
    padding: 1px 4px;
    outline: none;
    white-space: nowrap;
    -webkit-user-select: none;
    cursor: pointer;
    text-shadow: 1px 1px #fff;
    font-weight: 700;
}

.custom-file-input:hover::before {
    color: #d3394c;
}

.custom-file-input:active::before {
    background: -webkit-linear-gradient(top, #e3e3e3, #f9f9f9);
    color: #d3394c;
}
</style>
<?php
$tanggal = date('Y-m');
$id = !empty($vouchers) ? (int)$vouchers->id : 0;
$is_publish = !empty($vouchers->is_publish) ? 1 : 0;
?>

<div class="box box-success">

    <div class="box-body">
        <table class="table table-bordered table-reponsive">
            <form name="frm_cat" id="frm_cat" method="post" enctype="multipart/form-data" accept-charset="utf-8"
                autocomplete="off">
                <tr class="header_kolom">

                    <th style="vertical-align: middle; text-align:center"> Informasi Voucher </th>
                </tr>
                <tr>

                    <td>
                        <table class="table table-responsive">
							<tr style="vertical-align:middle;">
                                <td width="15%"><b>Kode Voucher </b> </td>
                                <td width="2%">:</td>
                                <td width="33%">
                                   <input type="hidden" value="<?php echo $id;?>" name="id">
                                    <span class="label label-danger pull-right kd_vc_error"></span>
                                    <input class="form-control" name="kd_vc" id="kd_vc"
                                        placeholder="Kode Voucher" style="width:92%; height:18px;" type="text"
                                        value="<?php echo !empty($vouchers->kode_voucher) ? $vouchers->kode_voucher : '';?>">
                                </td>
                                <td width="16%" align="right"><b>Tipe Potongan </b></td>
                                <td width="2%">:</td>
                                <td>
									<span class="label label-danger pull-right tipe_pot_error"></span>
                                    <select class="form-control" name="tipe_pot" id="tipe_pot" onchange="return get_sub(this.value);">
                                        <option value="">- Tipe Potongan -</option>
                                        <option value="1" <?php echo $vouchers->tipe_voucher == 1 ? 'selected' : '';?> >IDR</option>
                                        <option value="2" <?php echo $vouchers->tipe_voucher == 2 ? 'selected' : '';?> >Persentase(%)</option>
                                        
                                    </select>
                                </td>


                            </tr>
							
                            <tr style="vertical-align:middle;">
                                <td width="15%"><b>Expired Date </b> </td>
                                <td width="2%">:</td>
                                <td width="33%">
                                   
                                    <span class="label label-danger pull-right exp_date_error"></span>
                                    <input class="form-control" name="exp_date" id="exp_date"
                                        placeholder="Expired Date" style="width:92%; height:18px;" type="text"
                                        value="<?php echo !empty($vouchers->expired_date) ? date('d-M-Y', strtotime($vouchers->expired_date)) : '';?>">
                                </td>
                                <td align="right"><b>Potongan </b></td>
                                <td>:</td>
                                <td>
									<span class="label label-danger pull-right potongan_error"></span>
                                    <input class="form-control" name="potongan" id="potongan"
                                        placeholder="Potongan" style="width:93%; height:18px;" type="text"
                                        value="<?php echo $vouchers->nilai_potongan > 0 ? number_format($vouchers->nilai_potongan,0,',','.') : '';?>" disabled>
                                </td>


                            </tr>

                            <tr style="vertical-align:middle;">
                                <td><b>Minimum Pembelian </b></td>
                                <td>:</td>
                                <td>
									<span class="label label-danger pull-right min_pembelian_error"></span>
                                    <input class="form-control" name="min_pembelian" id="min_pembelian" placeholder="Minimum Pembelian"
                                        style="width:93%; height:18px;" type="text"
                                        value="<?php echo $vouchers->min_pembelian > 0 ? number_format($vouchers->min_pembelian,0,',','.') : '';?>">
                                </td>
                                <td align="right"><b>Maksimum Potongan </b></td>
                                <td>:</td>
                                <td>
									<span class="label label-danger pull-right maks_potongan_error"></span>
                                    <input class="form-control" name="maks_potongan" id="maks_potongan"
                                        placeholder="Maksimum Potongan" style="width:93%; height:18px;" type="text"
                                        value="<?php echo $vouchers->maks_potongan > 0 ? number_format($vouchers->maks_potongan,0,',','.') : '';?>" disabled>
                                </td>
                            </tr>

                            <tr>
                                <td><b>Description</b></td>
                                <td width="2%">:</td>
                                <td colspan=4>
                                    <textarea name="deskripsi" id="deskripsi" class="form-control" style="width:97%;"
                                        rows="5"><?php echo !empty($vouchers->deskripsi) ? $vouchers->deskripsi : '';?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Image</b></td>
                                <td width="2%">:</td>
                                <td colspan=4>
                                    <input type="file" class="form-control custom-file-input"
                                        style="width:97%; height:24px;" name="userfile" id="userfile"
                                        accept="image/*" />
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td width="2%"></td>
                                <td colspan=4>
                                    <div class="fileupload-new thumbnail"
                                        style="width: 200px; height: 150px; margin-bottom:5px;">
                                        <img id="blah" style="width: 200px; height: 150px;" src="" alt="">
                                    </div>
                                </td>
                            </tr>

                        </table>
                    </td>

                </tr>
        </table>
		
       


    </div>
	
    <div class="box-footer" style="height:35px;">
        <div class="clearfix"></div>
        <div class="pull-right">
            <a href="<?php echo site_url('vouchers');?>"> <button type="button" class="btn btn-danger"><i
                        class="glyphicon glyphicon-remove"></i> Cancel</button></a>
			<?php if($is_publish <= 0){?>
            <button type="button" class="btn btn-success btn_save"><i class="glyphicon glyphicon-ok"></i> Save</button>
			<?php } ?>
        </div>
    </div>
	
</div>
<link href="<?php echo base_url(); ?>assets/datetimepicker/jquery.datetimepicker.css" rel="stylesheet" type="text/css" />	

<script src="<?php echo base_url(); ?>assets/datetimepicker/jquery.datetimepicker.js"></script>

<script type="text/javascript">
$("#success-alert").hide();
var img = '<?php echo !empty($vouchers->img) ? base_url('uploads/vouchers/'.$vouchers->img) : base_url('uploads/no_photo.jpg');?>';
$('#exp_date').datetimepicker({
	dayOfWeekStart : 1,
	changeYear: false,
	timepicker:false,
	scrollInput:false,
	format:'d-m-Y',
	lang:'en',
	minDate:'0'
});

var id = '<?php echo (int)$vouchers->id > 0 ? (int)$vouchers->id : 0;?>';
var tp_pot = '<?php echo (int)$vouchers->tipe_voucher > 0 ? (int)$vouchers->tipe_voucher : 0;?>';
if(id > 0) get_sub(tp_pot);

function get_sub(val){	
	$('#potongan').prop('disabled', true);
	$('#maks_potongan').prop('disabled', true);
	if(val > 0)	$('#potongan').prop('disabled', false);
	if(val == 1) $('#maks_potongan').val('');
	if(val == 2) {				
		$('#maks_potongan').prop('disabled', false);		
		var potongan = $('#potongan').val();
		var res = potongan.replace(".", "");
		if(res > 100) $('#potongan').val(100);
	}
}
$("#userfile").change(function() {
    $('#blah').attr('src', '');
    readURL(this);
});

function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#blah').attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    }
}
if (img != '') {
    $('#blah').attr('src', img);
}

$('.btn_save').click(function() {
    var kode_voucher = $('#kd_vc').val();
    var tipe_pot = $('#tipe_pot').val();
    var exp_date = $('#exp_date').val();
    var potongan = $('#potongan').val();
    var min_pembelian = $('#min_pembelian').val();
    
	if(tipe_pot == 1) $('#maks_potongan').val(potongan);
	var maks_potongan = $('#maks_potongan').val();
    $('.kd_vc_error').text('');
    $('.tipe_pot_error').text('');
    $('.exp_date_error').text('');
    $('.potongan_error').text('');
    $('.min_pembelian_error').text('');
    $('.maks_potongan_error').text('');
    if (kode_voucher <= 0 || kode_voucher == '') {
        $('.kd_vc_error').text('Kode voucher harus diisi');
        return false;
    }
    if (tipe_pot <= 0 || tipe_pot == '') {
        $('.tipe_pot_error').text('Tipe potongan harus diisi');
        return false;
    }	
	
	if (exp_date <= 0 || exp_date == '') {
        $('.exp_date_error').text('Expired date harus diisi');
        return false;
    }	
	if (potongan <= 0 || potongan == '') {
        $('.potongan_error').text('Potongan harus diisi');
        return false;
    }	
	if (min_pembelian <= 0 || min_pembelian == '') {
        $('.min_pembelian_error').text('Minimum Pembelian harus diisi');
        return false;
    }
	if (maks_potongan <= 0 || maks_potongan == '') {
        $('.maks_potongan_error').text('Maksimum Potongan harus diisi');
        return false;
    }

    var url = '<?php echo site_url('vouchers/chk_voucher');?>';
	$.ajax({
		data:{kode_voucher : kode_voucher,id:id},
		type:'POST',
		url : url,
		success:function(response){	
			
			if(response > 0){
				$('.kd_vc_error').text('Kode voucher sudah digunakan');
				return false;							
			}else{
				$('#maks_potongan').prop('disabled', false);
				var url2 = '<?php echo site_url('vouchers/simpan_voucher');?>';
				$('#frm_cat').attr('action', url2);
				$('#frm_cat').submit();
			}				
		}
	})
    
});

$('#min_pembelian').keyup(function(event) {
    // format number
    $(this).val(function(index, value) {
        return value
            .replace(/\D/g, "")
            .replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    });
});

$('#maks_potongan').keyup(function(event) {
    // format number
    $(this).val(function(index, value) {
        return value
            .replace(/\D/g, "")
            .replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    });
});

$('#potongan').keyup(function(event) {
	var tipe = $('#tipe_pot').val();
	if($(this).val() > 100 && tipe == 2) $(this).val(100);
    // format number
    $(this).val(function(index, value) {		
        return value
            .replace(/\D/g, "")
            .replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    });
});



</script>