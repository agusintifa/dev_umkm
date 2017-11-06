<div class="action_group_master">
	<button class="btn btn-success btn-sm" onclick="add_record()"><i></i> Add <?php echo $page_title; ?> </button>
  	<button class="btn btn-default btn-sm" onclick="reload_table()"><i></i> Reload</button>
</div>

<table id="indexTable" class="table table-striped table-hover" style="width: 100%;">
  <thead>
    <tr>
        <th class="no-sort">No.</th>
        <th>Code</th>
        <th>Name</th>
        <th>Amount</th>
        <th>Tax</th>
        <th>Penalty</th>
        <th class="no-sort">Penalty Amount</th>
        <th class="no-sort">Module</th>
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
		      "url": "<?php echo site_url('ref/list_tariff_ipl') ?>",
		      "type": "POST"
		    },
		    "columnDefs": [
		    	{ "targets": "no-sort", "orderable": false }
		    ],
		    "language": {
		    	searchPlaceholder: "Code / Name / Penalty"
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
			url : "<?php echo site_url('ref/get_tariff_ipl_by_id/')?>",
			type: "POST",
			data: {"Id": id},
			dataType: "JSON",
			success: function(data) {
				$('#Id').val(data.Id);
				$('#CodeValue').val(data.CodeValue);
				$('#NameValue').val(data.NameValue);
				$('#Amount').val(toCurrencyFormat(data.Amount));
				$('#Tax').val(data.Tax);
				$('#PenaltyId').val(data.PenaltyId);
			},
			error: function (jqXHR, textStatus, errorThrown) {
			    alert('Error get data from ajax');
			}
		});

	    $('#modal_form').modal('show');
    	$('.modal-title').text('Edit ' + '<?php echo $page_title; ?>');
	}

	function delete_record(id, code) {
	    if(confirm('Are you sure delete tariff code : '+ code +'?')) {
	        $.ajax({
	            url : "<?php echo site_url('ref/delete_tariff_ipl')?>",
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
	        url = "<?php echo site_url('ref/add_tariff_ipl')?>";
	    } else {
	        url = "<?php echo site_url('ref/edit_tariff_ipl')?>";
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
                            <label class="control-label col-md-3">Code</label>
                            <div class="col-md-9">
                                <input id="CodeValue" name="CodeValue" placeholder="Code" class="input-custom-text" type="text" maxlength="20">
                                <span class="help-block"></span>
                            </div>

                            <label class="control-label col-md-3">Name</label>
                            <div class="col-md-9">
                                <input id="NameValue" name="NameValue" placeholder="Name" class="input-custom-text" type="text" maxlength="50">
                                <span class="help-block"></span>
                            </div>

                            <label class="control-label col-md-3">Amount</label>
                            <div class="col-md-9">
                                <input id="Amount" name="Amount" placeholder="0" class="form-control currency" type="text" maxlength="11">
                                <span class="help-block"></span>
                            </div>

                            <label class="control-label col-md-3">Tax</label>
                            <div class="col-md-9">
                                <input id="Tax" name="Tax" placeholder="0" class="form-control percentage" type="text" maxlength="5">
                                <span class="help-block"></span>
                            </div>

                            <label class="control-label col-md-3">Penalty</label>
                            <div class="col-md-9">
                                <?php echo $PenaltyId; ?>
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