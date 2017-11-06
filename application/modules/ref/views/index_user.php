<div class="action_group_master">
	<button class="btn btn-success btn-sm" onclick="add_record()"><i></i> Add <?php echo $page_title; ?> </button>
  	<button class="btn btn-default btn-sm" onclick="reload_table()"><i></i> Reload</button>
</div>

<table id="indexTable" class="table table-striped table-hover" style="width: 100%;">
  <thead>
    <tr>
        <th class="no-sort">No.</th>
        <th>User Name</th>
        <th>Role Name</th>
        <th class="no-sort">Password</th>
        <th class="no-sort"></th>
    </tr>
  </thead>

  <tbody>
  </tbody>
</table>

<script type="text/javascript">
	var table;
	$(document).ready(function() {
		//start document ready
		//dataTable
		table = $('#indexTable').DataTable({
		    "processing": true,
		    "serverSide": true,
		    "scrollX": true,
		    "order": [],
		    "ajax": {
		      "url": "<?php echo site_url('ref/list_user') ?>",
		      "type": "POST"
		    },
		    "columnDefs": [
		    	{ "targets": "no-sort", "orderable": false }
		    ],
		    "language": {
		    	searchPlaceholder: "User Name / Role Name"
		    }
		});
	}); //end document ready

	function reload_table() {
		table.ajax.reload(null,false);
	}

	function edit_record(id) {
	    save_method = 'update';
	    $('#form')[0].reset();
	    $('.form-group').removeClass('has-error');
	    $('.help-block').empty();

		$.ajax({
			url : "<?php echo site_url('ref/get_user_by_id/')?>",
			type: "POST",
			data: {"Id": id},
			dataType: "JSON",
			success: function(data) {
				$('#Id').val(data.Id);
				$('#UserName').val(data.UserName);
				$('#RoleId').val(data.RoleId);
				$('#Password').val(data.Password);
				$('#ConfirmPassword').val(data.Password);
			},
			error: function (jqXHR, textStatus, errorThrown) {
			    alert('Error get data from ajax');
			}
		});

	    $('#modal_form').modal('show');
    	$('.modal-title').text('Edit ' + '<?php echo $page_title; ?>');
	}

	function delete_record(id, name) {
	    if(confirm('Are you sure delete user name : '+ name +'?')) {
	        $.ajax({
	            url : "<?php echo site_url('ref/delete_user')?>",
	            type: "POST",
	            data: {"Id": id},
	            dataType: "JSON",
	            success: function(data) {
	                $('#modal_form').modal('hide');
	                reload_table();
	            },
	            error: function (jqXHR, textStatus, errorThrown) {
	                alert('Error deleting data');
	            }
	        });
	    }
	}

	function add_record() {
	    save_method = 'add';
	    $('#form')[0].reset();
	    $('.form-group').removeClass('has-error');
	    $('.help-block').empty();
	    $('#modal_form').modal('show');
	    $('.modal-title').text('Add ' + '<?php echo $page_title; ?>');
	}

	function save() {
	    $('#btnSave').text('saving...');
	    $('#btnSave').attr('disabled',true);
	    var url;

	    if(save_method == 'add') {
	        url = "<?php echo site_url('ref/add_user')?>";
	    } else {
	        url = "<?php echo site_url('ref/edit_user')?>";
	    }

		$.ajax({
			url : url,
			type: "POST",
			data: $('#form').serialize(),
			dataType: "JSON",
			success: function(data) {
			    if(data.status) {
			        $('#modal_form').modal('hide');
			        reload_table();
			    } else {
			        for (var i = 0; i < data.inputerror.length; i++) {
			            $('[name="'+data.inputerror[i]+'"]').parent().parent().addClass('has-error');
			            $('[name="'+data.inputerror[i]+'"]').next().text(data.error_string[i]);
			        }
			    }
			    $('#btnSave').text('Save');
			    $('#btnSave').attr('disabled',false);
			},
			error: function (jqXHR, textStatus, errorThrown) {
			    alert('Error adding / update data');
			    $('#btnSave').text('Save');
			    $('#btnSave').attr('disabled',false);

			}
		});
	}
</script>

<!-- Bootstrap modal -->
<div class="modal fade" id="modal_form" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h3 class="modal-title"></h3>
            </div>
            <div class="modal-body form">
                <form action="#" id="form" class="form-horizontal">
                    <input type="hidden" id="Id" value="" name="Id"/>
                    <div class="form-body">
                        <div class="form-group">
                            <label class="control-label col-md-3">User Name</label>
                            <div class="col-md-9">
                                <input id="UserName" name="UserName" placeholder="User Name" class="input-custom-text" type="text" maxlength="20">
                                <span class="help-block"></span>
                            </div>

                            <label class="control-label col-md-3">Role</label>
                            <div class="col-md-9">
                                <?php echo $RoleId; ?>
                                <span class="help-block"></span>
                            </div>

                            <label class="control-label col-md-3">Password</label>
                            <div class="col-md-9">
                                <input id="Password" name="Password" placeholder="Password" class="input-custom-text" type="password" maxlength="20">
                                <span class="help-block"></span>
                            </div>

                            <label class="control-label col-md-3">Confirm Password</label>
                            <div class="col-md-9">
                                <input id="ConfirmPassword" name="ConfirmPassword" placeholder="Confirm Password" class="input-custom-text" type="password" maxlength="20">
                                <span class="help-block"></span>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="btnSave" onclick="save()" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>