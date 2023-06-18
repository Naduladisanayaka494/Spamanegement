<style>
  .info-tooltip, .info-tooltip:focus, .info-tooltip:hover {
    background: unset;
    border: unset;
    padding: unset;
  }
</style>
<h1>Welcome to <?php echo $_settings->info('name') ?></h1>
<hr>
<div class="row">
  <div class="col-12 col-sm-6 col-md-3">
    <div class="info-box">
      <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-money-bill-alt"></i></span>

      <div class="info-box-content">
        <span class="info-box-text">Current Overall Budget</span>
        <span class="info-box-number text-right">
          <?php 
            $cur_bul = $conn->query("SELECT sum(balance) as total FROM `categories` where status = 1 ")->fetch_assoc()['total'];
            echo number_format($cur_bul);
          ?>
          <?php ?>
        </span>
      </div>
      <!-- /.info-box-content -->
    </div>
    <!-- /.info-box -->
  </div>
  <!-- /.col -->
  <div class="col-12 col-sm-6 col-md-3">
    <div class="info-box mb-3">
      <span class="info-box-icon bg-info elevation-1"><i class="fas fa-calendar-day"></i></span>

      <div class="info-box-content">
        <span class="info-box-text">Today's Budget Entries</span>
        <span class="info-box-number text-right">
          <?php 
            $today_budget = $conn->query("SELECT sum(amount) as total FROM `running_balance` where category_id in (SELECT id FROM categories where status =1) and date(date_created) = '".(date("Y-m-d"))."' and balance_type = 1 ")->fetch_assoc()['total'];
            echo number_format($today_budget);
          ?>
        </span>
      </div>
      <!-- /.info-box-content -->
    </div>
    <!-- /.info-box -->
  </div>
  <!-- /.col -->

  <!-- fix for small devices only -->
  <div class="clearfix hidden-md-up"></div>

  <div class="col-12 col-sm-6 col-md-3">
    <div class="info-box mb-3">
      <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-calendar-day"></i></span>

      <div class="info-box-content">
        <span class="info-box-text">Today's Budget Expenses</span>
        <span class="info-box-number text-right">
          <?php 
            $today_expense = $conn->query("SELECT sum(amount) as total FROM `running_balance` where category_id in (SELECT id FROM categories where status =1) and date(date_created) = '".(date("Y-m-d"))."' and balance_type = 2 ")->fetch_assoc()['total'];
            echo number_format($today_expense);
          ?>
        </span>
      </div>
      <!-- /.info-box-content -->
    </div>
    <!-- /.info-box -->
  </div>
</div>
<div class="row">
  <div class="col-lg-12">
    <h4>Current Budget in each Categories</h4>
    <hr>
  </div>
</div>
<div class="col-md-12 d-flex justify-content-center">
  <div class="input-group mb-3 col-md-5">
    <input type="text" class="form-control" id="search" placeholder="Search Category">
    <div class="input-group-append">
      <span class="input-group-text"><i class="fa fa-search"></i></span>
    </div>
  </div>
</div>
<div class="row row-cols-4 row-cols-sm-1 row-cols-md-4 row-cols-lg-4">
  <?php 
    // Fetch data for each branch in the current month
    $branches = $conn->query("SELECT branch, SUM(amount) as total_amount FROM spa_data WHERE MONTH(date) = MONTH(CURRENT_DATE()) GROUP BY branch");

    // Iterate over the branches and display the total amount
    while ($row = $branches->fetch_assoc()) {
      $branch = $row['branch'];
      $totalAmount = number_format($row['total_amount']);

      echo "<div class='col p-2 cat-items'>";
      echo "<div class='callout callout-info'>";
      echo "<h5 class='mr-4'><b>$branch</b></h5>";
      echo "<div class='d-flex justify-content-end'>";
      echo "<b>$totalAmount</b>";
      echo "</div>";
      echo "</div>";
      echo "</div>";
    }
  ?>
</div>
<div class="col-md-12">
  <h3 class="text-center" id="noData" style="display:none">No Data to display.</h3>
</div>
<script>
  function check_cats(){
    if($('.cat-items:visible').length > 0){
      $('#noData').hide('slow');
    } else {
      $('#noData').show('slow');
    }
  }

  $(function(){
    $('[data-toggle="tooltip"]').tooltip({
      html: true
    });
    check_cats();

    $('#search').on('input', function(){
      var _f = $(this).val().toLowerCase();
      $('.cat-items').each(function(){
        var _c = $(this).text().toLowerCase();
        if(_c.includes(_f) == true)
          $(this).toggle(true);
        else
          $(this).toggle(false);
      });
      check_cats();
    });
  });
</script>
