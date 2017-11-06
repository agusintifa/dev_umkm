<form id="form-filter" class="panel_filter">
    <table class="form_filter">
        <tr class="filter_first_row">
            <td class="filter_label">
                <label for="cluster_filter">Cluster</label>
            </td>
            <td class="filter_input">
                <?php echo $cluster_filter; ?>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>
                <button type="button" id="btn-filter" class="btn btn-primary btn-sm">Filter</button>
                <button type="button" id="btn-reset" class="btn btn-default btn-sm">Reset</button>
            </td>
        </tr>
    </table>
</form>

<div class="action_group">
  <input class="input-custom-date" id="month_bill" type="text" placeholder="yyyy-mm">
  <span></span>
  <button class="btn btn-success btn-sm" onclick="generate_billing()"></i> Generate Bill</button>
  <button class="btn btn-default btn-sm" onclick="reload_table()"><i></i> Reload</button>
</div>
    
<table id="indexTable" class="table table-striped table-hover ">
  <thead>
    <tr>
        <th class="no-sort">No.</th>
        <th>CustomerName</th>
        <th>Unit</th>
        <th class="no-sort">Blok</th>
        <th class="no-sort">Cluster</th>
        <th class="no-sort">Tariff</th>
        <th class="no-sort">Module</th>
        <th class="no-sort">UnitStatus</th>
        <th class="no-sort">CodeValue</th>
        <th>Period</th>
        <th class="no-sort">DueDate</th>
        <th class="no-sort">PaymentMethod</th>
        <th class="no-sort">Amount</th>
        <th class="no-sort">Tax</th>
        <th class="no-sort">Discount</th>
        <th class="no-sort">TotalAmount</th>
        <th class="no-sort">Notes</th>
        <th class="no-sort">IsSettle</th>
        <th class="no-sort">TotalPenaltyAmount</th>
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
    $('#month_bill').val(getDateNow('yyyy-mm'));
    //dataTable
    table = $('#indexTable').DataTable({
        "processing": true,
        "serverSide": true,
        "scrollX": true,
        "order": [],
        "dom": 'lBfrtip',
        "buttons": [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        "ajax": {
          "url": "<?php echo site_url('ipl/list_tagihan_ipl') ?>",
          "type": "POST",
          "data": function (data) {
                data.cluster_filter = $('#cluster_filter').val();
                // data.FirstName = $('#FirstName').val();
            }
        },
        "columnDefs": [
        { "targets": "no-sort", "orderable": false }
        ],
        "language": {
        searchPlaceholder: "Customer / Unit / Period"
        },
        "fixedColumns":   {
            leftColumns: 0,
            rightColumns: 1
        },
        "fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
                    if ( aData[17] == 0 ) {
                        $(nRow).addClass('info');
                    } else {
                        $(nRow).addClass('success');
                    }
        }
    });

    //datepicker
    $('.datepicker').datepicker({
        autoclose: true,
        format: "yyyy-mm-dd",
        todayHighlight: true,
        orientation: "top auto",
        todayBtn: true,
        todayHighlight: true
    });

    $('#month_bill').mask('0000-00');

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
    
    $('#Discount').keyup(function() {
        var n = $(this).val();
        if (n > 100) {
            $(this).val('100.00');
        }

        var bill = toNumberFormat($('#total_billing').text());
        var grand = toCurrencyFormat(bill * (100 - $(this).val()) / 100);
        $('#GrandTotal').val(grand);
        $('#Paid').val(grand);
        $('#ChangeDue').val(0);
    });

    $('#Paid').keyup(function() {
        var grand = toNumberFormat($('#GrandTotal').val());
        var paid = toNumberFormat($(this).val());
        $('#ChangeDue').val(toCurrencyFormat(paid - grand));
    });

    $('#btn-filter').click(function() {
        reload_table();
    });
    $('#cluster_filter').change(function() {
        reload_table();
    });
    $('#btn-reset').click(function() {
        $('#form-filter')[0].reset();
        reload_table();
    });
  }); //end document ready

function add_record() {
    save_method = 'add';
    $('#form')[0].reset(); // reset form on modals
    $('.form-group').removeClass('has-error'); // clear error class
    $('.help-block').empty(); // clear error string
    $('#modal_form_add').modal('show'); // show bootstrap modal
    $('.modal-title').text('Add ' + '<?php echo $page_title; ?>'); // Set Title to Bootstrap modal title
}

function edit_record(id, custid, unitid) {
    save_method = 'update';
    $('#form')[0].reset(); // reset form on modals
    $('.form-group').removeClass('has-error'); // clear error class
    $('.help-block').empty(); // clear error string
    get_total_billing(custid, unitid);

    /*$.ajax({
        url : "<?php echo site_url('ipl/list_tagihan_ipl_edit/')?>",
        type: "POST",
        data: {"Id": id},
        dataType: "JSON",
        success: function(data)
        {
            

        },
        error: function (jqXHR, textStatus, errorThrown)
        {
            alert('Error get data from ajax');
        }
    });*/
}

function print_invoice(id) {
    window.location = "<?php echo site_url('laporan/ipl_invoice'); ?>" + "?Id="+id;
}

function filling_form_tagihan(data) {
    $('[name="CustomerId"]').val(data.CustomerId);
    $('[name="CustomerName"]').val(data.CustomerName);
    $('[name="UnitId"]').val(data.UnitId);
    $('[name="Unit"]').val(data.Unit);
    $('[name="Block"]').val(data.Block);
    $('[name="Cluster"]').val(data.Cluster);
    $('[name="GrandTotal"]').val(toCurrencyFormat(data.TotalBilling));

    //generate_row_in_table
    console.log(data.DetailAmount);
    $("#tb_detail_billing").find("tr:gt(0)").remove();
    var table = document.getElementById("tb_detail_billing");
    for (i=0; i < data.DetailAmount.length; i++) {
        var row = table.insertRow(-1);
        row.insertCell(0).innerHTML = data.DetailAmount[i].BillId;
        row.insertCell(1).innerHTML = data.DetailAmount[i].Period;
        row.insertCell(2).innerHTML = data.DetailAmount[i].DueDate;
        row.insertCell(3).innerHTML = toCurrencyFormat(data.DetailAmount[i].BillAmount);
        row.insertCell(4).innerHTML = toCurrencyFormat(data.DetailAmount[i].Tax);
        row.insertCell(5).innerHTML = toCurrencyFormat(data.DetailAmount[i].PenaltyAmount);
        row.insertCell(6).innerHTML = toCurrencyFormat(data.DetailAmount[i].TotalAmount);
    }
    var row = table.insertRow(-1);
    row.insertCell(0).innerHTML = '';
    row.insertCell(0).colSpan = 5;
    row.insertCell(1).innerHTML = '<b>' + toCurrencyFormat(data.TotalPenalty) + '</b>';
    var cell_billing = row.insertCell(2);
    cell_billing.id = "total_billing";
    cell_billing.innerHTML = '<b>' + toCurrencyFormat(data.TotalBilling) + '</b>';
    
    $('#modal_form').modal('show');
    $('.modal-title').text('Proses ' + '<?php echo $page_title; ?>');
}

function reload_table() {
    table.ajax.reload(null,false); //reload datatable ajax 
}

function save() {
    $('#btnSave').text('saving...'); //change button text
    $('#btnSave').attr('disabled',true); //set button disable
    var url;

    if(save_method == 'add') {
        url = "<?php echo site_url('ipl/ajax_add')?>";
    } else {
        url = "<?php echo site_url('ipl/list_tagihan_ipl_update')?>";
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
            $('#btnSave').text('Settle'); //change button text
            $('#btnSave').attr('disabled',false); //set button enable 


        },
        error: function (jqXHR, textStatus, errorThrown)
        {
            alert('Error adding / update data');
            $('#btnSave').text('Settle'); //change button text
            $('#btnSave').attr('disabled',false); //set button enable 

        }
    });
}

function update() {
    // ajax adding data to database
    window.location = "<?php echo site_url('ipl/index_add'); ?>" + "?CustomerId="+ $('#CustomerId').val() +"&UnitId=" + $('#UnitId').val();
}

function delete_record(id) {
    if(confirm('Are you sure delete this data?')) {
        // ajax delete data to database
        $.ajax({
            url : "<?php echo site_url('ipl/ajax_delete')?>",
            type: "POST",
            data: {"Id": id},
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

function generate_billing() {
    if (isValidBillPeriod($('#month_bill').val())) {
        $.ajax({
        url : "<?php echo site_url('ipl/generate_billing_customer')?>",
        type: "POST",
        data: {"period": $('#month_bill').val() },
        dataType: "JSON",
        success: function(data)
        {
            //if success reload ajax table
            if (data.status) {
                if (data.insertid > 0) {
                    alert('Success Generate Bill');
                } else {
                    alert('Billing untuk Periode ' + $('#month_bill').val() + ' sudah tergenerate');
                }
                reload_table();
            }
        },
        error: function (jqXHR, textStatus, errorThrown)
        {
            alert('Error deleting data');
        }
        });
    } else {
        alert('Periode Billing tidak boleh lebih dari bulan sekarang');
    }
}

function get_total_billing(custid, unitid) {
    $.ajax({
        url : "<?php echo site_url('ipl/get_total_billing')?>",
        type: "POST",
        data: {"custid": custid, 'unitid': unitid},
        dataType: "JSON",
        success: function(data)
        {
            console.log('aku ada disini');
            if (data.PamUnsettleBill == null) {
                //tagihan > 1 bulan
                if (data.DetailAmount.length > 1) {
                    alert('Customer ini memiliki total ' + data.DetailAmount.length + ' tagihan yang harus dibayar!');
                }
                filling_form_tagihan(data);
            } else {
                alert('Maaf, IPL tidak bisa diproses karena customer masih memiliki total ' + data.PamUnsettleBill + ' tagihan PAM yang belum terbayar');
            }
        },
        error: function (jqXHR, textStatus, errorThrown)
        {
            alert('Error deleting data');
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
                    <input type="hidden" id="CustomerId" value="" name="CustomerId"/>
                    <input type="hidden" id="UnitId" value="" name="UnitId"/>
                    <div class="form-body">
                        <div class="form-group">
                            <label class="control-label col-md-3">Customer</label>
                            <div class="col-md-9">
                                <input name="CustomerName" placeholder="Customer" class="input-custom-text" readonly type="text">
                                <span class="help-block"></span>
                            </div>

                            <label class="control-label col-md-3">Unit</label>
                            <div class="col-md-9">
                                <input name="Unit" placeholder="Unit" class="input-custom-text" readonly type="text">
                                <span class="help-block"></span>
                            </div>

                            <label class="control-label col-md-3">Block</label>
                            <div class="col-md-9">
                                <input name="Block" placeholder="Block" class="input-custom-text" readonly type="text">
                                <span class="help-block"></span>
                            </div>

                            <label class="control-label col-md-3">Cluster</label>
                            <div class="col-md-9">
                                <input name="Cluster" placeholder="Cluster" class="input-custom-text" readonly type="text">
                                <span class="help-block"></span>
                            </div>

                            <br>
                            <table id="tb_detail_billing" class="table_detail">
                                <caption>Detail Billing :</caption>
                                <th>Bill ID</th>
                                <th>Period</th>
                                <th>Due Date</th>
                                <th>Amount</th>
                                <th>Tax</th>
                                <th>Penalty</th>
                                <th>Total</th>
                            </table>

                            <label class="control-label col-md-3">Tipe Pembayaran</label>
                            <div class="col-md-9">
                                <?php echo $PaymentMethodId; ?>
                                <span class="help-block"></span>
                            </div>

                            <label class="control-label col-md-3">Diskon (%)</label>
                            <div class="col-md-9">
                                <input id="Discount" name="Discount" placeholder="0" class="form-control percentage" type="text">
                                <span class="help-block"></span>
                            </div>

                            <label class="control-label col-md-3">Total Tagihan</label>
                            <div class="col-md-9">
                                <input id="GrandTotal" name="GrandTotal" placeholder="0" class="form-control currency" type="text" disabled="">
                                <span class="help-block"></span>
                            </div>

                            <label class="control-label col-md-3">Total Bayar</label>
                            <div class="col-md-9">
                                <input id="Paid" name="Paid" placeholder="0" class="form-control currency" type="text">
                                <span class="help-block"></span>
                            </div>

                            <label class="control-label col-md-3">Total Kembali</label>
                            <div class="col-md-9">
                                <input id="ChangeDue" name="ChangeDue" placeholder="0" class="form-control currency" type="text" readonly="">
                                <span class="help-block"></span>
                            </div>

                            <label class="control-label col-md-3">Catatan</label>
                            <div class="col-md-9">
                                <textarea name="Notes" placeholder="Catatan" class="form-control"></textarea>
                                <span class="help-block"></span>
                            </div>

                        </div>

                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" style="float: left;" id="btnAddNextMonth" onclick="update()" class="btn btn-success">Tambah Bulan Berikutnya</button>
                <button type="button" id="btnSave" onclick="save()" class="btn btn-primary">Settle</button>
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<!-- End Bootstrap modal -->