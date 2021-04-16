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
$id_product = !empty($product) ? (int)$product->id_product : 0;
?>
<div class="modal fade" role="dialog" id="confirm_del">
          <div class="modal-dialog" style="width:400px">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><strong>Confirmation</strong></h4>
              </div>
			 
              <div class="modal-body">
				<h4 class="text-center">Apakah anda yakin untuk menghapusnya ? </h4>
				<input type="hidden" id="del_id" value="">
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>               
                <button type="button" class="btn btn-success yes_del">Delete</button>               
              </div>
            </div>
            <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
</div>
<div class="modal fade" role="dialog" id="frm_category">
          <div class="modal-dialog" style="width:400px">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">Add/Edit Variant</h4>
              </div>
			 
              <div class="modal-body" style="padding-bottom:2px;">
				
				<form role="form" id="frm_variant" method="post" accept-charset="utf-8" autocomplete="off">
                <!-- text input -->
				<div class="row">
				<div class="form-group">
                  <label>Variant</label><span class="label label-danger pull-right nama_variant_error"></span>
                  <input type="text" class="form-control" name="nama_variant" id="nama_variant" value="" placeholder="Variant" autocomplete="off" />
                  <input type="hidden" value="" name="id_variant" id="id_variant">
                </div>
				
				<div class="form-group">
                  <label>Harga</label><span class="label label-danger pull-right"></span>
                  <input type="text" class="form-control" name="hrg_pack" id="hrg_pack" value="" placeholder="Harga" autocomplete="off" />
                </div>
				
				</div>
                
              </form>

              </div>
              <div class="modal-footer" style="margin-top:1px;">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>               
                <button type="button" class="btn btn-success yes_save">Save</button>               
              </div>
            </div>
            <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
</div>
<div class="box box-success">

    <div class="box-body">
        <table class="table table-bordered table-reponsive">
            <form name="frm_cat" id="frm_cat" method="post" enctype="multipart/form-data" accept-charset="utf-8"
                autocomplete="off">
                <tr class="header_kolom">

                    <th style="vertical-align: middle; text-align:center"> Informasi Product </th>
                </tr>
                <tr>

                    <td>
                        <table class="table table-responsive">
							<tr style="vertical-align:middle;">
                                <td width="15%"><b>Product Name </b> </td>
                                <td width="2%">:</td>
                                <td width="33%" colspan=4>
                                    <input type="hidden" name="id_product" id="id_product"
                                        value="<?php echo !empty($product) ? $product->id_product : '';?>" />
                                    <span class="label label-danger pull-right nama_produk_error"></span>
                                    <input class="form-control" name="nama_produk" id="nama_produk"
                                        placeholder="Product Name" style="width:97%; height:18px;" type="text"
                                        value="<?php echo !empty($product->nama_barang) ? ucwords($product->nama_barang) : '';?>">
                                </td>
                                

                            </tr>
                            <tr style="vertical-align:middle;">
                                <td width="15%"><b>Stok </b> </td>
                                <td width="2%">:</td>
                                <td width="33%">
                                   
                                    <span class="label label-danger pull-right stok_error"></span>
                                    <input class="form-control" name="stok" id="stok"
                                        placeholder="Stok" style="width:92%; height:18px;" type="text"
                                        value="<?php echo !empty($product->qty) && (int)$product->qty > 0 ? number_format($product->qty,0,',','.') : '';?>">
                                </td>
                                <td width="15%" align="right"><b>Category </b></td>
                                <td width="2%">:</td>
                                <td>
                                    <select class="form-control" name="kategori" id="kategori" onchange="return get_sub(this.value,0);">
                                        <option value="">- Category -</option>
                                        <?php 
											if(!empty($kat)){
												foreach($kat as $k){
													if($k['id_kategori'] == $product->id_kategori){
														echo '<option selected="selected" value="'.$k['id_kategori'].'">'.$k['nama_kategori'].'</option>';
													}else{
														echo '<option value="'.$k['id_kategori'].'">'.$k['nama_kategori'].'</option>';
													}
												}
											}
										?>
                                    </select>
                                </td>


                            </tr>

                            <tr style="vertical-align:middle;">
                                <td><b>Berat (Gram) </b></td>
                                <td>:</td>
                                <td>
                                    <input class="form-control" name="weight" id="weight" placeholder="Berat (Gram)"
                                        style="width:93%; height:18px;" type="text"
                                        value="<?php echo $product->weight > 0 ? number_format($product->weight,0,',','.') : '';?>">
                                </td>
                                <td width="12%" align="right"><b>Sub Category </b></td>
                                <td width="2%">:</td>
                                <td>
                                    <select class="form-control" name="sub_kategori" id="sub_kategori" onchange="return get_sub2(this.value,0);">
                                        <option value="">- Sub Category -</option>
                                        
                                    </select>
                                </td>
                            </tr>

                            <tr style="vertical-align:middle;">
                                <td align="right"><b>Discount Product(%) </b></td>
                                <td>:</td>
                                <td>
                                    <input class="form-control" name="diskon" id="diskon"
                                        placeholder="Discount Product(%)" style="width:93%; height:18px;" type="text"
                                        value="<?php echo $product->diskon > 0 ? $product->diskon : '';?>">
                                </td>
                                <td width="15%" align="right"><b>Sub Sub Category </b></td>
                                <td width="2%">:</td>
                                <td>
                                    <select class="form-control" name="subs_kategori" id="subs_kategori">
                                        <option value="">- Sub Sub Category -</option>
                                        
                                    </select>
                                </td>
                            </tr>


                            <tr style="vertical-align:middle;">
                                <td><b>Minimum Order </b></td>
                                <td>:</td>
                                <td>
                                    <input class="form-control" name="min_order" id="min_order" placeholder="Minimum Order"
                                        style="width:93%; height:18px;" type="text"
                                        value="<?php echo !empty($product->minimum_order) ? number_format($product->minimum_order,0,',','.') : '';?>">  
                                </td>
                                <td align="right"><b>Kondisi </b></td>
                                <td>:</td>
                                <td>
                                    <input class="form-control" name="kondisi" id="kondisi" placeholder="Kondisi"
                                        style="width:93%; height:18px;" type="text"
                                        value="<?php echo !empty($product->kondisi)? $product->kondisi : '';?>">
                                </td>

                            </tr>
                            
                            <tr style="vertical-align:middle;">
                                <td><b>Color </b></td>
                                <td>:</td>
                                <td>
                                <select class="form-control" name="color" id="color">
                                        <option value="">- Color -</option>
                                        <?php if(!empty($colors)){
                                            foreach($colors as $m){
                                                if($product->id_color == $m['id']){
                                                    echo '<option value='.$m['id'].' selected>'.$m['color'].'</option>';
                                                }else{
                                                    echo '<option value='.$m['id'].'>'.$m['color'].'</option>';
                                                }
                                                
                                            }
                                        }?>
                                    </select>
                                </td>
                                <td width="15%" align="right"><b>Material </b></td>
                                <td width="2%">:</td>
                                <td>
                                    <select class="form-control" name="material" id="material">
                                        <option value="">- Material -</option>
                                        <?php if(!empty($materials)){
                                            foreach($materials as $m){
                                                if($product->id_material == $m['id']){
                                                    echo '<option value='.$m['id'].' selected>'.$m['material'].'</option>';
                                                }else{
                                                    echo '<option value='.$m['id'].'>'.$m['material'].'</option>';
                                                }
                                                
                                            }
                                        }?>
                                    </select>
                                </td>
                            </tr>
							<tr>
                                <td><b>Special Promo</b></td>
                                <td width="2%">:</td>
                                <td colspan=4>
                                    <input type="checkbox" value=1 name="special_promo" <?php echo (int)$product->special_promo > 0 ? 'checked' : '';?>>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Description</b></td>
                                <td width="2%">:</td>
                                <td colspan=4>
                                    <textarea name="deskripsi" id="deskripsi" class="form-control" style="width:97%;"
                                        rows="5"><?php echo !empty($product->deskripsi) ? $product->deskripsi : '';?></textarea>
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
		<?php if($id_product > 0){ ?>
	<br/>
	<table  class="table table-bordered table-reponsive">
	
		<div class='alert alert-info alert-dismissable' id="success-alert">
   
			<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>
			<div id="id_text"><b>Welcome</b></div>
		</div>
		<tr class="header_kolom">
			
			<th style="vertical-align: middle; text-align:center"> Data Variant </th>
		</tr>
		<tr>
			<td><button type="button" class="btn btn-success add_variant"><i class="fa fa-plus"></i> Add Variant</button></td>
			
		</tr>
		<tr>
			
			<td> 
				<table id="example88" class="table table-bordered table-striped">
					<thead><tr>
						<th style="text-align:center; width:4%">No.</th>
						<th style="text-align:center; width:32%">Variant</th>			
						<th style="text-align:center; width:15%">Harga</th>			
						
						<th style="text-align:center; width:16%">Action</th>
					</tr>
					</thead>
					<tbody>
						<?php 				
							$i =1;							
							$info = '';			
							if(!empty($variant)){		
								foreach($variant as $v){									
									$info = $v['id_variant'].'Þ'.$v['nama_variant'].'Þ'.number_format($v['hrg'],0,',','.');
									echo '<tr>';
									echo '<td align="center">'.$i++.'.</td>';
									echo '<td>'.$v['nama_variant'].'</td>';
									echo '<td>'.number_format($v['hrg'],0,',','.').'</td>';
									// echo '<td>'.number_format($v['hrg_dus'],0,',','.').'</td>';
									
									echo '<td align="center" style="vertical-align: middle;">		
			
			<a href="javascript:void(0)" id="'.$info.'" title="Edit" class="edit_variant"><button class="btn btn-xs btn-success"><i class="fa fa-edit"></i> Edit</button></a>
			<button title="Delete" id="'.$v['id_variant'].'" class="btn btn-xs btn-danger del_variant"><i class="fa fa-trash-o"></i> Delete</button>		
						</td>';
									
									echo '</tr>';
								}
							}
						?>
					</tbody>
				
				</table>
			</td>
		</tr>
	</table>	
		<?php } ?>
        </form>


    </div>
    <div class="box-footer" style="height:35px;">
        <div class="clearfix"></div>
        <div class="pull-right">
            <a href="<?php echo site_url('product');?>"> <button type="button" class="btn btn-danger"><i
                        class="glyphicon glyphicon-remove"></i> Cancel</button></a>

            <button type="button" class="btn btn-success btn_save"><i class="glyphicon glyphicon-ok"></i> Save</button>
        </div>
    </div>
</div>
<script src="<?php echo base_url(); ?>assets/theme_admin/js/plugins/datatables/jquery.dataTables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/theme_admin/js/plugins/datatables/dataTables.bootstrap.js" type="text/javascript"></script>
<script type="text/javascript">
$("#success-alert").hide();
var img = '<?php echo !empty($product->img) ? base_url('uploads/products/'.$product->img) : '';?>';
var id_product = '<?php echo (int)$id_tutorial > 0 ? (int)$id_tutorial : 0;?>';
var id_kategori = '<?php echo (int)$product->id_kategori > 0 ? (int)$product->id_kategori : 0;?>';
var id_sub = '<?php echo (int)$product->id_sub > 0 ? (int)$product->id_sub : 0;?>';
var id_sub_sub = '<?php echo (int)$product->id_sub_sub > 0 ? (int)$product->id_sub_sub : 0;?>';
if(id_kategori > 0) get_sub(id_kategori, id_sub);
$('.add_variant').click(function(){
	$('.nama_variant_error').text('');
	$('.stok_error').text('');
	$('#frm_variant').find("input[type=text], select, input[type=hidden]").val("");	
	$('#frm_category').modal({
		backdrop: 'static',
		keyboard: false
	});
	$(".yes_save").prop( "disabled", false );
	$('#frm_category').modal('show');
});
$('.edit_variant').click(function(){
	$('.nama_variant_error').text('');
	$('.stok_error').text('');
	$('#frm_variant').find("input[type=text], select, input[type=hidden]").val("");	
	var val = $(this).get(0).id;
	var dt = val.split('Þ');
	$('#id_variant').val(dt[0]);
	$('#nama_variant').val(dt[1]);
	
	$('#hrg_pack').val(dt[2]);	
	// $('#hrg_dus').val(dt[5]);	
	$('#frm_category').modal({
		backdrop: 'static',
		keyboard: false
	});
	$(".yes_save").prop( "disabled", false );
	$('#frm_category').modal('show');
});
$('.del_variant').click(function(){
	var val = $(this).get(0).id;
	$('#del_id').val(val);
	$('#confirm_del').modal({
		backdrop: 'static',
		keyboard: false
	});
	$("#confirm_del").modal('show');
});
$('.yes_del').click(function(){
	var id = $('#del_id').val();
	var url = '<?php echo site_url('product/del_variant');?>';
	$.ajax({
		data : {id : id},
		url : url,
		type : "POST",
		success:function(response){
			$('#confirm_del').modal('hide');
			$("#id_text").html('<b>Success,</b> Data telah dihapus');
			$("#success-alert").fadeTo(2000, 500).slideUp(500, function(){
				$("#success-alert").alert('close');
				location.reload();
			});			
		}
	});	
});
$('.yes_save').click(function(){
	$(".yes_save").prop( "disabled", true );
	var id_product = '<?php echo $id_product;?>';
	var nama_variant = $('#nama_variant').val();	
	var hrg_pack = $('#hrg_pack').val();	
	var id_variant = $('#id_variant').val();
	$('.nama_variant_error').text('');
	$('.stok_error').text('');
	if(nama_variant == '' && nama_variant == 0){
		$('.nama_variant_error').text('Nama Variant harus diisi');
		$(".yes_save").prop( "disabled", false );
		return false;
	}
	
	var url = '<?php echo site_url('product/save_variant');?>';
	$.ajax({
		data : {id_product : id_product,nama_variant:nama_variant,id_variant:id_variant,hrg_pack:hrg_pack},
		url : url,
		type : "POST",
		success:function(response){
			if(response > 0){
				location.reload();
			}				
		}
	});
	
});
function get_sub(id, id_sub){	
	var url = '<?php echo site_url('product/get_sub');?>';	
	var html = '';
	$.ajax({
		data:{id:id},
		type:'POST',
		url : url,
		success:function(response){	
			html += '<option ="0">- Sub Category -</option>';
			if(response.length > 0){
				html += response;				
			}	
			$('#sub_kategori').html(html);	
			$('#subs_kategori').html('<option ="0">- Sub Sub Category -</option>');	
			if(id_sub > 0){
				$('#sub_kategori').val(id_sub);
				get_sub2(id_sub, id_sub_sub);
			}
		}
	});	
}
function get_sub2(id, id_sub_sub){	
	var url = '<?php echo site_url('product/get_sub_sub');?>';	
	var html = '';
	$.ajax({
		data:{id:id},
		type:'POST',
		url : url,
		success:function(response){	
			html += '<option ="0">- Sub Sub Category -</option>';
			if(response.length > 0){
				html += response;				
			}	
			$('#subs_kategori').html(html);		
			if(id_sub > 0) $('#subs_kategori').val(id_sub_sub);
		}
	});	
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
    var nama_produk = $('#nama_produk').val();
    var harga = $('#harga').val();
    $('.nama_produk_error').text('');
    $('.harga_error').text('');
    if (nama_produk <= 0 || nama_produk == '') {
        $('.nama_produk_error').text('Nama produk harus diisi');
        return false;
    }

    if (harga <= 0 || harga == '') {
        $('.harga_error').text('Harga harus diisi');
        return false;
    }

    var url = '<?php echo site_url('product/simpan');?>';
    $('#frm_cat').attr('action', url);
    $('#frm_cat').submit();
});

$('#stok').keyup(function(event) {

    // format number
    $(this).val(function(index, value) {
        return value
            .replace(/\D/g, "")
            .replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    });
});

$('#weight').keyup(function(event) {

    // format number
    $(this).val(function(index, value) {
        return value
            .replace(/\D/g, "")
            .replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    });
});

$('#min_order').keyup(function(event) {

    // format number
    $(this).val(function(index, value) {
        return value
            .replace(/\D/g, "")
            .replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    });
});
$('#diskon').keyup(function(event) {

    // format number
    if ($(this).val() > 100) $(this).val(100);
    $(this).val(function(index, value) {
        return value
            .replace(/[^.\d]/g, "")
            .replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    });

});
$('#hrg_pack').keyup(function(event) {

    // format number
    $(this).val(function(index, value) {
        return value
            .replace(/\D/g, "")
            .replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    });
});

$(function() {               
    $('#example88').dataTable({});
});
</script>