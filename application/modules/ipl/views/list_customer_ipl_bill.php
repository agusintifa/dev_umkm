<div class="action_group">
  <button class="btn btn-success btn-sm" onclick="show_detail()"></i> Show Detail</button>
  <button class="btn btn-default btn-sm" onclick="reload_table()"><i></i> Reload</button>
</div>
    
<table id="indexTable" class="table table-striped table-hover" style="width: 100%;">
  <thead>
    <tr>
        <th class="no-sort">No.</th>
        <th class="no-sort">CustomerName</th>
        <th>Period</th>
        <th>Unit</th>
        <th class="no-sort">Blok</th>
        <th>Cluster</th>
        <th class="no-sort">BillPerMonth</th>
        <th class="no-sort">Module</th>
        <th class="no-sort">UnitStatus</th>
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
        "dom": 'lBfrtip',
        "buttons": [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        "ajax": {
          "url": "<?php echo site_url('ipl/customer_ipl_list') ?>",
          "type": "POST"
        },
        "columnDefs": [
        { "targets": "no-sort", "orderable": false }
        ],
        "language": {
        searchPlaceholder: "Period / Unit / Cluster"
        }
    });

    $('#btn-filter').click(function() {
        reload_table();
    });
    $('#btn-reset').click(function() {
        $('#form-filter')[0].reset();
        reload_table();
    });
  }); //end document ready


function reload_table() {
    table.ajax.reload(null,false); //reload datatable ajax 
}

function show_detail() {
    $('#form')[0].reset(); // reset form on modals
    $('.form-group').removeClass('has-error'); // clear error class
    $('.help-block').empty(); // clear error string
    $.ajax({
        url : "<?php echo site_url('ipl/get_customer_bill_details')?>",
        type: "POST",
        dataType: "JSON",
        success: function(data) {
            filling_form_tagihan(data);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            alert('Error deleting data');
        }
    });
}

function filling_form_tagihan(data) {
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

    //row_for_total
    var row = table.insertRow(-1);
    row.className = 'spaceUnder';
    var cell_nol = row.insertCell(0);
    cell_nol.innerHTML = '<span><b>Total</b></span>';
    cell_nol.colSpan = 5;
    var cel_satu = row.insertCell(1);
    cel_satu.innerHTML = '<b>' + toCurrencyFormat(data.TotalPenalty) + '</b>';
    var cell_billing = row.insertCell(2);
    cell_billing.id = "total_billing";
    cell_billing.innerHTML = '<b>' + toCurrencyFormat(data.TotalBilling) + '</b>';
    
    $('#modal_form').modal('show');
    $('.modal-title').text('Detail ' + '<?php echo $page_title; ?>');
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
                    <div class="form-body">
                        <div class="form-group">
                            <table id="tb_detail_billing" class="table_detail">
                                <th>Bill ID</th>
                                <th>Period</th>
                                <th>Due Date</th>
                                <th>Amount</th>
                                <th>Tax</th>
                                <th>Penalty</th>
                                <th>Total</th>
                            </table>

                        </div>

                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- End Bootstrap modal -->