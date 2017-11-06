<!DOCTYPE html>
<html>
<head>
	<title><?php echo $page_title; ?></title>
  <?= link_tag('assets/css/bootstrap.min.css') ?>
  <?= link_tag('assets/css/main.css') ?>
  <?= link_tag('assets/css/jquery.dataTables.min.css') ?>
  <?= link_tag('assets/css/bootstrap-datepicker3.min.css') ?>
  <?= link_tag('assets/css/jquery-ui.css') ?>
  <!-- export data needs - start -->
  <?= link_tag('assets/css/exportdata/buttons.dataTables.min.css') ?>
  <!-- export data needs - end -->

  <script type="text/javascript" src="<?= base_url('assets/js/jquery-3.2.0.min.js') ?>"></script>
  <script type="text/javascript" src="<?= base_url('assets/js/bootstrap.min.js')?>"></script>
  <script type="text/javascript" src="<?= base_url('assets/js/jquery.dataTables.min.js')?>"></script>
  <script type="text/javascript" src="<?= base_url('assets/js/dataTables.fixedColumns.min.js')?>"></script>
  <script type="text/javascript" src="<?= base_url('assets/js/bootstrap-datepicker.min.js')?>"></script>
  <script type="text/javascript" src="<?= base_url('assets/js/jquery-ui.autocomplete.min.js')?>"></script>
  <script type="text/javascript" src="<?= base_url('assets/js/jquery-mask.min.js')?>"></script>

  <!-- export data needs - start -->
  <script type="text/javascript" src="<?= base_url('assets/js/exportdata/buttons.html5.min.js')?>"></script>
  <script type="text/javascript" src="<?= base_url('assets/js/exportdata/buttons.print.min.js')?>"></script>
  <script type="text/javascript" src="<?= base_url('assets/js/exportdata/dataTables.buttons.min.js')?>"></script>
  <script type="text/javascript" src="<?= base_url('assets/js/exportdata/jszip.min.js')?>"></script>
  <script type="text/javascript" src="<?= base_url('assets/js/exportdata/pdfmake.min.js')?>"></script>
  <script type="text/javascript" src="<?= base_url('assets/js/exportdata/vfs_fonts.js')?>"></script>
  <script type="text/javascript" src="<?= base_url('assets/js/exportdata/buttons.flash.min.js')?>"></script>
  <!-- export data needs - end -->

  <script type="text/javascript">
    //my-custom-global-script

    $(document).ready(function() {
      $('input.currency').keyup(function(event) {
        if(event.which >= 37 && event.which <= 40) return;
        $(this).val(function(index, value) {
          return value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        });
      });

      $('input.percentage').mask('##0.00', {reverse: true});
    });

    function getDateNow(format) {
      var today = new Date();
      var dd = today.getDate();
      var mm = today.getMonth()+1; //January is 0!
      var yyyy = today.getFullYear();

      if(dd<10) {
          dd='0'+dd
      } 

      if(mm<10) {
          mm='0'+mm
      } 
      if (format == 'yyyy-mm')
        return yyyy+'-'+mm;
      else
        return yyyy+'-'+mm+'-'+dd;
    }

    function getNextMonth(format) {
      var today = new Date();
      var dd = <?php echo $this->config->item('bill_open_day'); ?>;
      var mm = today.getMonth()+2; //January is 0!
      var yyyy = today.getFullYear();

      if(dd<10) {
          dd='0'+dd
      } 

      if(mm<10) {
          mm='0'+mm
      } 
      if (format == 'yyyy-mm')
        return yyyy+'-'+mm;
      else
        return yyyy+'-'+mm+'-'+dd;
    }

    function toCurrencyFormat(val) {
      return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",").replace(".00", "");
    }

    function toNumberFormat(val) {
      return Number(val.toString().replace(/,/g , ""));
    }

    function getParam(name, url) {
      if (!url) url = window.location.href;
      name = name.replace(/[\[\]]/g, "\\$&");
      var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
      results = regex.exec(url);
      if (!results) return null;
      if (!results[2]) return '';
      return decodeURIComponent(results[2].replace(/\+/g, " "));
    }

    function isValidBillPeriod(date) {
      var today = new Date();
      var n = date.toString().split('-');
      var set_date = new Date(n[0], toNumberFormat(n[1]) - 1, today.getDate());
      if (set_date > today) {
        return false;
      }
      return true;
    }
  </script>
</head>
<body>
	
<nav class="navbar navbar-default">
  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="#">Real Estate Management</a>
    </div>

    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">

    <?php if (isset($_SESSION['user_session'])): ?>

      <!-- 1. Cashier -->
      <?php if ($user_data->Role == 'CASHIER'): ?>
      <ul class="nav navbar-nav">
        <li><?= anchor('ipl/index_price', 'Daftar Pelanggan'); ?></li>
        <li><?= anchor('ipl/index', 'Bayar IPL'); ?></li>
        <li><?= anchor('pam/index', 'Bayar PAM'); ?></li>
        <li><?= anchor('pam/index_reactivate_pam', 'Daftar PAM Non Aktif'); ?></li>
        <li><?= anchor('laporan/billing_report', 'Laporan Tagihan'); ?></li>
      </ul>

      <!-- 2. Admin -->
    <?php elseif ($user_data->Role == 'ADMIN'): ?>
      <ul class="nav navbar-nav">
        <li><?= anchor('ref/index_user', 'User'); ?></li>
        <li><?= anchor('ref/index_tariff_ipl', 'Tariff IPL'); ?></li>
        <li><?= anchor('ref/index_tariff_pam', 'Tariff PAM'); ?></li>
        <li><?= anchor('ref/index_tariff_denda', 'Tariff Denda'); ?></li>
      </ul>

      <!-- 3. Extended Dept. -->
      <?php elseif ($user_data->Role == 'EXTENDED DEPARTMENT'): ?>
      <ul class="nav navbar-nav">
        <!-- <li><?= anchor('ipl/index_extended', 'Daftar Tagihan IPL'); ?></li> -->
        <li><?= anchor('ipl/customer_ipl_bill_all', 'Tagihan IPL'); ?></li>
        <li><?= anchor('pam/customer_pam_bill_all', 'Tagihan PAM'); ?></li>
      </ul>

      <!-- 4. Pam Officer -->
      <?php elseif ($user_data->Role == 'PAM OFFICER'): ?>
      <ul class="nav navbar-nav">
        <li><?= anchor('pam/index', 'Screening PAM'); ?></li>
      </ul>

      <!-- 5. Customer -->
      <?php elseif ($user_data->Role == 'CUSTOMER'): ?>
      <ul class="nav navbar-nav">
        <li><?= anchor('ipl/customer_ipl_bill', 'Tagihan IPL'); ?></li>
        <li><?= anchor('pam/customer_pam_bill', 'Tagihan PAM'); ?></li>
      </ul>

      <?php else: ?>
      <ul class="nav navbar-nav"></ul>
      <?php endif; ?>

      <?php else: ?>
      <ul class="nav navbar-nav"></ul>
      <?php endif; ?>



      <!-- Sample - Start

      <ul class="nav navbar-nav">
        <li class="active"><a href="ref/add_module">Ref Module<span class="sr-only">(current)</span></a></li>
        <li><a href="#">Link</a></li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Dropdown <span class="caret"></span></a>
          <ul class="dropdown-menu" role="menu">
            <li><a href="#">Action</a></li>
            <li><a href="#">Another action</a></li>
            <li><a href="#">Something else here</a></li>
            <li class="divider"></li>
            <li><a href="#">Separated link</a></li>
            <li class="divider"></li>
            <li><a href="#">One more separated link</a></li>
          </ul>
        </li>
      </ul>

      Sample - End -->
     
    
        <?php if (isset($_SESSION['user_session'])): ?>
          <ul class="nav navbar-nav navbar-right">
          <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><?php echo $user_data->Username.' / '.strtolower($user_data->Role); ?> <span class="caret"></span></a>
          <ul class="dropdown-menu" role="menu">
            <li class="divider"></li>
            <li><?= anchor('login/logout', 'Logout'); ?></li>
          </ul>
          </li>



          </ul>
        <?php else: ?>
          <ul class="nav navbar-nav navbar-right">
          <li><?= anchor('login', 'Login'); ?></li>
          </ul>
        <?php endif; ?>

       
    </div>
  </div>
</nav>
<div class="container">
<div class="row header" style="text-align:left">
<h3><?php echo $page_title; ?></h3>
<br />
</div>
