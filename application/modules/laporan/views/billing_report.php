<form id="form-report" class="panel_filter">
    <table class="form_report">
        <tr class="filter_first_row">
            <td class="filter_label">
                <label for="Module">Modul</label>
            </td>
            <td>
                <select id="Module" name="Module" class="form-control">
                    <option value="All">-All-</option>
                    <option value="IPL">IPL</option>
                    <option value="PAM">PAM</option>
                </select>
            </td>
        </tr>
        <tr class="filter_first_row">
            <td class="filter_label">
                <label for="PaymentMethodId">Metode Pembayaran</label>
            </td>
            <td>
                <?php echo $PaymentMethodId; ?>
            </td>
        </tr>
        <tr class="filter_first_row">
            <td class="filter_label">
                <label for="StartDate">Start Date</label>
            </td>
            <td>
                <input class="form-control datepicker" id="StartDate" name="StartDate" placeholder="yyyy-mm-dd" type="text">
            </td>
        </tr>
        <tr class="filter_first_row">
            <td class="filter_label">
                <label for="EndDate">End Date</label>
            </td>
            <td>
                <input class="form-control datepicker" id="EndDate" name="EndDate" placeholder="yyyy-mm-dd" type="text">
            </td>
        </tr>
        <tr>
            <td></td>
            <td>
                <button type="button" id="btn_generate" onclick="generate_billing_report()" class="btn btn-primary btn-sm">Generate Report</button>
                <button type="button" id="btn_reset" class="btn btn-default btn-sm">Reset</button>
            </td>
        </tr>
    </table>
</form>

<script type="text/javascript">
    $(document).ready(function() {
        //start document ready
        $('.datepicker').val(getDateNow('yyyy-mm-dd'));

        //datepicker
        $('.datepicker').datepicker({
            autoclose: true,
            format: "yyyy-mm-dd",
            todayHighlight: true,
            orientation: "top auto",
            todayBtn: true,
            todayHighlight: true
        });

        $('#btn-filter').click(function() {
            reload_table();
        });
        $('#btn_reset').click(function() {
            $('#form-report')[0].reset();
        });
    }); //end document ready

    function generate_billing_report() {
        if ($('#StartDate').val() == '') {
            alert('Start Date harus diisi');
            $('#StartDate').focus();
        } else if ($('#EndDate').val() == '') {
            alert('End Date harus diisi');
            $('#EndDate').focus();
        } else {
            var start = new Date($('#StartDate').val());
            var end = new Date($('#EndDate').val());

            if (end < start) {
                alert('End Date harus sama dengan atau lebih dari Start Date');
                $('#EndDate').focus();
            } else {
                var module = $('#Module').val() == 'All' ? get_all_module() : "'" + $('#Module').val() + "'";
                var paymethod = $('#PaymentMethodId').val() == 'All' ? get_all_paymethod() : $('#PaymentMethodId').val();
                window.location = "<?php echo site_url('laporan/generate_billing_report'); ?>" + "?Module="+module +"&PaymentMethodId="+paymethod +"&StartDate="+$('#StartDate').val() +"&EndDate="+$('#EndDate').val();
                /*$.ajax({
                    url : "<?php echo site_url('laporan/generate_billing_report')?>",
                    type: "POST",
                    data: {"Module": module, "PaymentMethodId": paymethod, "StartDate": $('#StartDate').val(), "EndDate": $('#EndDate').val()},
                    dataType: "JSON",
                    success: function(data) {

                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        alert('Error get data from ajax');
                    }
                });*/
            }
        }
    }

    function get_all_module() {
        var list = '';
        $('#Module option').each(function() {
            list = list + "'" + $(this).val() + "'" + ',';
        });
        return list.substring(6, list.length - 1);
    }

    function get_all_paymethod() {
        var list = '';
        $('#PaymentMethodId option').each(function() {
            list = list + $(this).val() + ',';
        });
        return list.substring(4, list.length - 1);
    }
</script>