<div class="action_group">
  <button class="btn btn-default btn-sm" onclick="reload_table()"><i></i> Reload</button>
</div>
<table id="indexTable" class="table table-striped table-hover ">
  <thead>
    <tr>
      <th class="no-sort">No.</th>
      <th>Customer Name</th>
      <th>Unit</th>
      <th>Code</th>
      <th>Tariff Name</th>
      <th>Tariff Amount</th>
      <th>Tax</th>
      <th>Penalty</th>
      <th>Penalty Amount</th>
      <th class="no-sort">Module</th>
      <th class="no-sort">Status</th>
    </tr>
  </thead>

  <tbody>
  </tbody>
</table>



<script type="text/javascript">
  var table;
  var tablename = "IPLPriceLists";

  $(document).ready(function(){
  //start document ready

    //dataTable
    table = $('#indexTable').DataTable({
      "processing": true,
      "serverSide": true,
      "order": [],
      "dom": 'lBfrtip',
        "buttons": [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
      "ajax": {
          "url": "<?php echo site_url('ipl/ajax_list') ?>",
          "type": "POST",
          "data": {"TableName": tablename}
      },
      "columnDefs": [
        { "targets": "no-sort", "orderable": false }
      ],
      "language": {
        searchPlaceholder: "Customer / Unit / Tariff Name"
      }
    });

    //set input/textarea/select event when change value, remove class error and remove text help block 
    $("input").change(function(){
        $(this).parent().parent().removeClass('has-error');
        $(this).next().empty();
    });
    $("textarea").change(function(){
        $(this).parent().parent().removeClass('has-error');
        $(this).next().empty();
    });
    $("select").change(function(){
        $(this).parent().parent().removeClass('has-error');
        $(this).next().empty();
    });

  }); //end document ready



function add_record() {
    save_method = 'add';
    $('#form')[0].reset(); // reset form on modals
    $('.form-group').removeClass('has-error'); // clear error class
    $('.help-block').empty(); // clear error string
    $('#modal_form').modal('show'); // show bootstrap modal
    $('.modal-title').text('Add ' + '<?php echo $page_title; ?>'); // Set Title to Bootstrap modal title
}

function edit_record(id) {
    save_method = 'update';
    $('#form')[0].reset(); // reset form on modals
    $('.form-group').removeClass('has-error'); // clear error class
    $('.help-block').empty(); // clear error string

    //Ajax Load data from ajax
    $.ajax({
        url : "<?php echo site_url('ipl/ajax_edit/')?>",
        type: "POST",
        data: {"Id": id},
        dataType: "JSON",
        success: function(data)
        {
            $('[name="Id"]').val(data.Id);
            $('[name="NameValue"]').val(data.NameValue);
            $('#modal_form').modal('show'); // show bootstrap modal when complete loaded
            $('.modal-title').text('Edit ' + '<?php echo $page_title; ?>'); // Set title to Bootstrap modal title

        },
        error: function (jqXHR, textStatus, errorThrown)
        {
            alert('Error get data from ajax');
        }
    });
}

function reload_table() {
    table.ajax.reload(null,false); //reload datatable ajax 
}

function save() {
    $('#btnSave').text('saving...'); //change button text
    $('#btnSave').attr('disabled',true); //set button disable
    $('[name="TableName"]').val(tablename);
    var url;

    if(save_method == 'add') {
        url = "<?php echo site_url('ipl/ajax_add')?>";
    } else {
        url = "<?php echo site_url('ipl/ajax_update')?>";
    }

    // ajax adding data to database
    $.ajax({
        url : url,
        type: "POST",
        data: $('#form').serialize(),
        dataType: "JSON",
        success: function(data)
        {

            if(data.status) //if success close modal and reload ajax table
            {
                $('#modal_form').modal('hide');
                reload_table();
            }
            else
            {
                for (var i = 0; i < data.inputerror.length; i++) 
                {
                    $('[name="'+data.inputerror[i]+'"]').parent().parent().addClass('has-error'); //select parent twice to select div form-group class and add has-error class
                    $('[name="'+data.inputerror[i]+'"]').next().text(data.error_string[i]); //select span help-block class set text error string
                }
            }
            $('#btnSave').text('save'); //change button text
            $('#btnSave').attr('disabled',false); //set button enable 


        },
        error: function (jqXHR, textStatus, errorThrown)
        {
            alert('Error adding / update data');
            $('#btnSave').text('save'); //change button text
            $('#btnSave').attr('disabled',false); //set button enable 

        }
    });
}

function delete_record(id) {
    if(confirm('Are you sure delete this data?')) {
        // ajax delete data to database
        $.ajax({
            url : "<?php echo site_url('ipl/ajax_delete')?>",
            type: "POST",
            data: {"TableName": tablename, "Id": id},
            dataType: "JSON",
            success: function(data)
            {
                //if success reload ajax table
                $('#modal_form').modal('hide');
                reload_table();
            },
            error: function (jqXHR, textStatus, errorThrown)
            {
                alert('Error deleting data');
            }
        });

    }
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
                    <input type="hidden" value="" name="Id"/>
                    <input type="hidden" value="" name="TableName"/>
                    <div class="form-body">
                        <div class="form-group">
                            <label class="control-label col-md-3">Name</label>
                            <div class="col-md-9">
                                <input name="NameValue" placeholder="Name" class="form-control" type="text">
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
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<!-- End Bootstrap modal -->