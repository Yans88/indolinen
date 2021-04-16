<style>
.form-control {
    height: 20px;
}
</style>
<div class="row">
    <div class="col-md-12">
        <div class="box box-solid box-primary">
            <div class="box-header">
                <h3 class="box-title">Setting</h3>
                <div class="box-tools pull-right">
                    <button class="btn btn-primary btn-sm" data-widget="collapse"><i class="fa fa-minus"></i></button>
                </div>
            </div>
            <div class="box-body">
                <?php if($tersimpan == 'Y') { ?>
                <div class="box-body">
                    <div class="alert alert-success alert-dismissable">
                        <i class="fa fa-check"></i>
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        Data berhasil disimpan.
                    </div>
                </div>
                <?php } ?>

                <?php if($tersimpan == 'N') { ?>
                <div class="box-body">
                    <div class="alert alert-danger alert-dismissable">
                        <i class="fa fa-warning"></i>
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        Data tidak berhasil disimpan, silahkan ulangi beberapa saat lagi.
                    </div>
                </div>
                <?php } 
				
				?>

                <div class="form-group">
                    <?php 
					echo form_open('');
					//nama sekolah
					$data = array(
		              'name'        => 'email',
		              'id'			=> 'email',
		              'class'		=> 'form-control',
		              'value'       => $email,		   
		              'style'       => 'width: 98%'
	            	);
					echo form_label('Email', 'email');
					echo form_input($data);
					echo '<br>';
					
					//nama ketua
					$data = array(
		              'name'        => 'pass',
		              'id'			=> 'pass',
		              'class'		=> 'form-control',
		              'value'       => $pass,		           
		              'style'       => 'width: 98%'
	            	);
					echo form_label('Mail Password', 'pass');
					echo form_input($data);
					echo '<br>';
					
					
					
					//hp ketua
					$data = array(
		              'name'        => 'call_center',
		              'id'			=> 'call_center',
		              'class'		=> 'form-control',
		              'value'       => $call_center,		              
		              'style'       => 'width: 98%'
	            	);
					echo form_label('Call Center', 'call_center');
					echo form_input($data);
					echo '<br>';
					
					$data = array(
		              'name'        => 'message',
		              'id'			=> 'message',
		              'class'		=> 'form-control',
		              'value'       => $message,		              
		              'style'       => 'width: 98%'
	            	);
					echo form_label('Message', 'message');
					echo form_input($data);
					echo '<br>';
					
					$data = array(
		              'name'        => 'wa',
		              'id'			=> 'wa',
		              'class'		=> 'form-control',
		              'value'       => $wa,		              
		              'style'       => 'width: 98%'
	            	);
					echo form_label('Whatsapp', 'wa');
					echo form_input($data);
					echo '<br>';
					
					$data = array(
		              'name'        => 'email_cc',
		              'id'			=> 'email_cc',
		              'class'		=> 'form-control',
		              'value'       => $email_cc,		              
		              'style'       => 'width: 98%'
	            	);
					echo form_label('Email Customer Care', 'email_cc');
					echo form_input($data);
					echo '<br>';

					// alamat
					$data = array(
		              'name'        => 'subj_email_forgot',
		              'id'			=> 'subj_email_forgot',
		              'class'		=> 'form-control',
		              'value'       => $subj_email_forgot,		              
		              'style'       => 'width: 98%'
	            	);
					echo form_label('Subject Email for Forgot Password', 'subj_email_forgot');
					echo form_input($data);
					echo '<br>';

					
					
					$data = array(
		              'name'        => 'content_forgotPass',
		              'id'			=> 'content_forgotPass',
		              'class'		=> 'form-control',
		              'value'       => $content_forgotPass,		              
		              'style'       => 'width: 97%'
	            	);
					echo form_label('Content Email forgot password', 'content_forgotPass');
					echo form_textarea($data);
					echo '<br>';				
					
					
					$data = array(
		              'name'        => 'term_condition',
		              'id'			=> 'term_condition',
		              'class'		=> 'form-control',
		              'value'       => $term_condition,		              
		              'style'       => 'width: 97%'
	            	);
					echo form_label('Term & Condition', 'term_condition');
					echo form_textarea($data);
					echo '<br/>';
					
					$data = array(
		              'name'        => 'policy',
		              'id'			=> 'policy',
		              'class'		=> 'form-control',
		              'value'       => $policy,		              
		              'style'       => 'width: 97%'
	            	);
					echo form_label('Policy', 'policy');
					echo form_textarea($data);
					echo '<br/>';

					$data = array(
						'name'        => 'about_us',
						'id'			=> 'about_us',
						'class'		=> 'form-control',
						'value'       => $about_us,		              
						'style'       => 'width: 97%'
					  );
					  echo form_label('About Us', 'about_us');
					  echo form_textarea($data);
					  echo '<br/>';
					
					$data = array(
		              'name'        => 'incoming_order',
		              'id'			=> 'incoming_order',
		              'class'		=> 'form-control',
		              'value'       => $incoming_order,		              
		              'style'       => 'width: 97%'
	            	);
					echo form_label('Content incoming order', 'incoming_order');
					echo form_textarea($data);
					echo '<br>';
					
					// submit
					$data = array(
				    'name' 		=> 'submit',
				    'id' 		=> 'submit',
				    'class' 	=> 'btn btn-primary',
				    'value'		=> 'true',
				    'type'	 	=> 'submit',
				    'content' 	=> 'Update'
					);
					// echo '<br>';
					echo form_button($data);


					echo form_close();

					?>
                </div>
            </div><!-- /.box-body -->
        </div>
    </div>
</div>
<script src="<?php echo base_url(); ?>assets/theme_admin/js/plugins/ckeditor/ckeditor.js" type="text/javascript">
</script>
<script src="<?php echo base_url(); ?>assets/theme_admin/js/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"
    type="text/javascript"></script>
<script>
$("input").attr("autocomplete", "off");
$(function(config) {
    CKEDITOR.config.allowedContent = true;
    // CKEDITOR.replace('content_verifyReg');
    CKEDITOR.replace('content_forgotPass');
    CKEDITOR.replace('term_condition');
	CKEDITOR.replace('policy');
	CKEDITOR.replace('about_us');
    CKEDITOR.replace('incoming_order');

});

</script>