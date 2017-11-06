<form id="form" class="form-horizontal">
  <fieldset>
    <legend></legend>

    <div class="form-group">
      <label id="cluster_label" for="Cluster" class="col-lg-2 control-label">Cluster</label>
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
      <label for="Period" class="col-lg-2 control-label">Periode</label>
      <div class="col-lg-10">
        <input class="form-control datepicker" id="Period" name="Period" placeholder="yyyy-mm-dd" type="text">
        <span class="help-block"></span>
      </div>
    </div>

    <div class="form-group">
      <label for="PamLastMonth" class="col-lg-2 control-label">Pemakaian Sebelum (m&sup3;)</label>
      <div class="col-lg-10">
        <input class="form-control" id="PamLastMonth" name="PamLastMonth" placeholder="0" type="text" readonly="">
        <span class="help-block"></span>
      </div>
    </div>

    <div class="form-group">
      <label for="PamThisMonth" class="col-lg-2 control-label">Pemakaian Akhir (m&sup3;)</label>
      <div class="col-lg-10">
        <input class="form-control" id="PamThisMonth" name="PamThisMonth" placeholder="0" type="text">
        <span class="help-block"></span>
      </div>
    </div>

    <div class="form-group">
      <label for="PamCubic" class="col-lg-2 control-label">Jumlah Pemakaian (m&sup3;)</label>
      <div class="col-lg-10">
        <input class="form-control" id="PamCubic" name="PamCubic" placeholder="0" type="text" readonly="">
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
        <button type="button" class="btn btn-default" onclick="window.location='<?php echo site_url("pam/index");?>'">Cancel</button>
        <button type="button" id="btnSave" onclick="save()" class="btn btn-primary">Save</button>
      </div>
    </div>
  </fieldset>
</form>

<script type="text/javascript">
  //start_document_ready
	$(document).ready(function(){
    if (getParam('id') == null) {
      save_method = 'add';
    } else {
      save_method = 'update';
      fillDataScreening(getParam('id'));
    }

    //ddl cluster
    $.ajax({
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
    });

    $('.datepicker').datepicker({
        autoclose: true,
        format: "yyyy-mm-dd",
        todayHighlight: true,
        orientation: "top auto",
        todayBtn: true,
        todayHighlight: true
    }).on('changeDate', function() {
      getPamLastMonth($("#CustomerId").val(), $("#UnitId").val(), $(this).val());
    });

    $('#PamThisMonth').mask('0000');

    $('#Cluster').change(function() {
      resetCustomerInfor();
      fillDDLUnit($(this).val(), $('#Cluster option:selected').text());
    });

    $('#PamThisMonth').keyup(function() {
      var n = toNumberFormat($(this).val()) - toNumberFormat($('#PamLastMonth').val());
      $('#PamCubic').val(n);
    });

	});
  //end_document_ready

  function resetCustomerInfor() {
    $("#Unit").val('');
    $("#UnitId").val('');
    $("#CustomerName").val('');
    $("#CustomerId").val('');
    $("#Period").val('');
    $("#PamLastMonth").val('');
    $("#PamThisMonth").val('');
    $("#PamCubic").val('');
    $("#Notes").val('');
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
                      $('#Period').focus();
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

  function fillDataScreening(id) {
    //pam_screening_edit
    $.ajax({
      url : "<?php echo site_url('pam/pam_screening_edit')?>",
      type: "POST",
      data: {"Id": id},
      dataType: "JSON",
      success: function(data)
      {
        console.log(data);
        $('#Id').val(data.Id);
        $('#Period').val(data.Period);
        $('#PamLastMonth').val(data.PamLastMonth);
        $('#PamThisMonth').val(data.PamThisMonth);
        $('#PamCubic').val(data.PamCubic);
        $('#Notes').val(data.Notes);
        $('#Unit').val(data.Unit);
        $('#UnitId').val(data.UnitId);
        $('#CustomerName').val(data.CustomerName);
        $('#CustomerId').val(data.CustomerId);
        $('#Cluster').hide();
        $('#cluster_label').hide();
        $('#Unit').attr('readonly', true);
      },
      error: function (jqXHR, textStatus, errorThrown) {
          alert('Error deleting data');
      }
    });
  }

  function getPamLastMonth(custid, unitid, period) {
    $.ajax({
      url : "<?php echo site_url('pam/pam_get_last_cubic')?>",
      type: "POST",
      data: {"custid": custid, "unitid": unitid, "period": period},
      dataType: "JSON",
      success: function(data)
      {
        $('#PamLastMonth').val(data);
      },
      error: function (jqXHR, textStatus, errorThrown) {
          alert('Error deleting data');
      }
    });
  }

  function save() {
    $('#btnSave').text('saving...');
    $('#btnSave').attr('disabled',true);
    var url;

    if(save_method == 'add')
      url = "<?php echo site_url('pam/ajax_add')?>";
    else
      url = "<?php echo site_url('pam/ajax_update')?>";

    $.ajax({
        url : url,
        type: "POST",
        data: $('#form').serialize(),
        dataType: "JSON",
        success: function(data) {
            if(data.status) {
                alert(save_method + ' tagihan PAM success!');
                window.location='<?php echo site_url("pam/index");?>';
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