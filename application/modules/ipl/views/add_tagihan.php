<form id="form" class="form-horizontal">
  <fieldset>
    <legend></legend>

    <div class="form-group">
      <label for="Cluster" class="col-lg-2 control-label">Cluster</label>
      <div class="col-lg-10">
        <select class="form-control" id="Cluster" name="Cluster">
        </select>
      </div>
    </div>

    <div class="form-group">
      <input type="hidden" value="" id="Id" name="Id"/>
      <label for="Unit" class="col-lg-2 control-label">Unit</label>
      <div class="col-lg-10">
      <input type="hidden" value="" id="UnitId" name="UnitId"/>
        <input class="form-control" id="Unit" name="Unit" placeholder="Unit" type="text">
        <span class="help-block"></span>
      </div>
    </div>

    <div class="form-group">
      <label for="CustomerName" class="col-lg-2 control-label">Customer</label>
      <div class="col-lg-10">
      <input type="hidden" value="" id="CustomerId" name="CustomerId"/>
        <input class="form-control" id="CustomerName" name="CustomerName" placeholder="Customer" type="text" readonly="">
        <span class="help-block"></span>
      </div>
    </div>

    <div class="form-group">
      <label for="Tariff" class="col-lg-2 control-label">Tariff / bulan</label>
      <div class="col-lg-10">
        <input class="form-control" id="Tariff" name="Tariff" placeholder="0.00" type="text" readonly="">
        <span class="help-block"></span>
      </div>
    </div>

    <div class="form-group">
      <label for="totalMonth" class="col-lg-2 control-label">Jumlah Bulan</label>
      <div class="col-lg-10">
        <select class="form-control" id="totalMonth" name="totalMonth">
          <option value="1">1</option>
          <option value="2">2</option>
          <option value="3">3</option>
          <option value="4">4</option>
          <option value="5">5</option>
          <option value="6">6</option>
          <option value="7">7</option>
          <option value="8">8</option>
          <option value="9">9</option>
          <option value="10">10</option>
          <option value="11">11</option>
          <option value="12">12</option>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label for="StartPeriod" class="col-lg-2 control-label">Periode</label>
      <div class="col-lg-10">
        <input class="input-custom-date period_field" id="StartPeriod" name="StartPeriod" placeholder="yyyy-mm-dd" type="text" readonly="">
        <span>-</span>
        <input class="input-custom-date period_field" id="EndPeriod" name="EndPeriod" placeholder="yyyy-mm-dd" type="text" readonly="">
        <span class="help-block"></span>
      </div>
    </div>

    <div class="form-group">
      <label for="PaymentMethodId" class="col-lg-2 control-label">Tipe Pembayaran</label>
      <div class="col-lg-10">
          <?php echo $PaymentMethodId; ?>
          <span class="help-block"></span>
      </div>
    </div>

    <div class="form-group">
      <label for="Discount" class="col-lg-2 control-label">Diskon (%)</label>
      <div class="col-lg-10">
          <input id="Discount" name="Discount" placeholder="0" class="form-control percentage" type="text">
          <span class="help-block"></span>
      </div>
    </div>

    <div class="form-group">
      <label for="GrandTotal" class="col-lg-2 control-label" >Tagihan Bulan Berikutnya
        <span id="free_month_text" class="infor_bold"></span>
      </label>
      <div class="col-lg-10">
          <input id="GrandTotal" name="GrandTotal" placeholder="0" class="form-control currency" type="text" disabled="">
          <span class="help-block"></span>
      </div>
    </div>

    <div class="form-group">
      <label for="BillBefore" class="col-lg-2 control-label">Tagihan Bulan Sebelumnya</label>
      <div class="col-lg-10">
          <input id="BillBefore" name="BillBefore" placeholder="0" class="form-control currency" type="text" readonly="">
          <span class="help-block"></span>
      </div>
    </div>

    <div class="form-group">
      <label for="BillAfter" class="col-lg-2 control-label">Total Tagihan</label>
      <div class="col-lg-10">
          <input id="BillAfter" name="BillAfter" placeholder="0" class="form-control currency" type="text" readonly="">
          <span class="help-block"></span>
      </div>
    </div>

    <div class="form-group">
      <label for="Paid" class="col-lg-2 control-label">Total Bayar</label>
      <div class="col-lg-10">
          <input id="Paid" name="Paid" placeholder="0" class="form-control currency" type="text">
          <span class="help-block"></span>
      </div>
    </div>

    <div class="form-group">
      <label for="ChangeDue" class="col-lg-2 control-label">Total Kembali</label>
      <div class="col-lg-10">
          <input id="ChangeDue" name="ChangeDue" placeholder="0" class="form-control currency" type="text" readonly="">
          <span class="help-block"></span>
      </div>
    </div>

    <div class="form-group">
      <label for="Notes" class="col-lg-2 control-label">Catatan</label>
      <div class="col-lg-10">
        <textarea class="form-control" rows="3" id="Notes" name="Notes" placeholder="Catatan" maxlength="250"></textarea>
      </div>
    </div>
    
    <div class="form-group">
      <div class="col-lg-10 col-lg-offset-2">
        <button type="button" class="btn btn-default" onclick="window.location='<?php echo site_url("ipl/index");?>'">Cancel</button>
        <button type="button" id="btnSave" onclick="save()" class="btn btn-primary">Save</button>
      </div>
    </div>
  </fieldset>
</form>

<script type="text/javascript">
  //start_document_ready
  $(document).ready(function(){

    if (getParam('CustomerId') == null && getParam('UnitId') == null) {
      save_method = 'add';
    } else {
      save_method = 'update';
      fillDataBilling(getParam('CustomerId'), getParam('UnitId'));
    }

    //ddl cluster
    /*$.ajax({
        url: "<?php echo site_url('ipl/get_ddl_cluster') ?>",
        type: "GET",
        contentType: "application/json",
        dataType: "json",
        success: function(data) {
          //ddl unit
          fillDDLUnit(data[0].value, data[0].label);
          $.each(data, function() {
            $("#Cluster").append('<option value="'+ this.value +'">'+ this.label +'</option>');
          });
        }
    });*/

    var dateToday = new Date(getNextMonth('yyyy-mm-dd'));
    // dateToday.setMonth(dateToday.getMonth()+1);
    $('.datepicker').datepicker({
        defaultDate: dateToday,
        autoclose: true,
        format: "yyyy-mm-dd",
        todayHighlight: true,
        orientation: "top auto",
        todayBtn: true,
        todayHighlight: true,
        startDate: dateToday
    });

    $('.period_field').val(getNextMonth('yyyy-mm-dd'));

    $('#Discount').keyup(function() {
        var n = $(this).val();
        if (n > 100) {
            $(this).val('100.00');
        }
        setTotalBill();
    });

    $('#Paid').keyup(function() {
        var grand = toNumberFormat($('#BillAfter').val());
        var paid = toNumberFormat($(this).val());
        $('#ChangeDue').val(toCurrencyFormat(paid - grand));
    });

    $('#totalMonth').change(function() {
      var s_date = $('#StartPeriod').val().split('-');
      var start_period = new Date(s_date[0], s_date[1] - 1, s_date[2]);
      start_period.setMonth(start_period.getMonth()+parseInt($(this).val()));
      var mm = (start_period.getMonth()).toString();
      mm = mm.length == 1 ? '0' + mm : mm;
      var dd = start_period.getDate().toString();
      dd = dd.length == 1 ? '0' + dd : dd;
      $('#EndPeriod').val(start_period.getFullYear().toString() + '-' + mm + '-' + dd);
      setTotalBill();
    });

    $('#Cluster').change(function() {
      resetCustomerInfor();
      fillDDLUnit($(this).val(), $('#Cluster option:selected').text());
    });

  });
  //end_document_ready

  function setTotalBill() {
      var bill = toNumberFormat($('#Tariff').val());
      var bill_before = toNumberFormat($('#BillBefore').val());
      var grand = '';

      if ($('#totalMonth').val() == 12) {
        document.getElementById('free_month_text').innerHTML = '(gratis 1 bulan)';
        grand = toCurrencyFormat(bill * ($('#totalMonth').val() - 1));
      } else {
        document.getElementById('free_month_text').innerHTML = '';
        grand = toCurrencyFormat(bill * $('#totalMonth').val());
      }
      var bill_all_total = toCurrencyFormat((toNumberFormat(grand) + bill_before) * (100 - $('#Discount').val()) / 100);

      $('#GrandTotal').val(grand);
      $('#Paid').val(bill_all_total);
      $('#ChangeDue').val(0);
      $('#BillAfter').val(bill_all_total);
  }

  function fillDataBilling(custid, unitid) {
    //ipl_billing
    $.ajax({
      url : "<?php echo site_url('ipl/get_total_billing')?>",
      type: "POST",
      data: {"custid": custid, "unitid": unitid},
      dataType: "JSON",
      success: function(data)
      {
        $("#Cluster").append('<option value="'+ data.ClusterId +'">'+ data.Cluster +'</option>');
        $("#UnitId").val(data.UnitId);
        $("#Unit").val(data.Unit);
        $("#CustomerId").val(data.CustomerId);
        $("#CustomerName").val(data.CustomerName);
        $("#Tariff").val(toCurrencyFormat(data.BillPerMonth));
        $("#BillBefore").val(toCurrencyFormat(data.TotalBilling));

        $('#Cluster').attr('readonly', true);
        $('#Unit').attr('readonly', true);
        setTotalBill();
      },
      error: function (jqXHR, textStatus, errorThrown) {
          alert('Error deleting data');
      }
    });
  }

  function fillDDLUnit(id, name) {
    $.ajax({
      url : "<?php echo site_url('ipl/get_ddl_unit')?>",
      type: "POST",
      data: {"ClusterId": id},
      dataType: "JSON",
      success: function(data) {
          if (data.length < 1) {
            alert('Tidak ada customer yang terdaftar di Cluster ' + name);
          } else {
              $("#Unit").autocomplete({
              source: data,
              select: function (event, ui) {
                $("#Unit").val(ui.item.label);
                $("#UnitId").val(ui.item.value);
                $("#CustomerName").val(ui.item.CustomerName);
                $("#CustomerId").val(ui.item.CustomerId);
                $("#Tariff").val(toCurrencyFormat(ui.item.BillPerMonth));
                $('#totalMonth').focus();
                return false;
              },
              focus: function (event, ui) {
                $("#Unit").val(ui.item.label);
                return false;
              }
              });
          }
        }
    });
  }

  function resetCustomerInfor() {
    $("#Unit").val('');
    $("#UnitId").val('');
    $("#CustomerName").val('');
    $("#CustomerId").val('');
    $("#Tariff").val('');
  }

  function save() {
    $('#btnSave').text('saving...');
    $('#btnSave').attr('disabled',true);
    
    /*var url;
    if(save_method == 'add')
      url = "<?php echo site_url('ipl/ajax_add')?>";
    else
      url = "<?php echo site_url('ipl/ajax_update')?>";*/

    $.ajax({
        url : "<?php echo site_url('ipl/ajax_add')?>",
        type: "POST",
        data: $('#form').serialize(),
        dataType: "JSON",
        success: function(data) {
            if(data.status) {
                alert('add tagihan IPL success!');
                window.location='<?php echo site_url("ipl/index");?>';
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