
<?php 

    $title="Financial | Dashboard";
    $path="Dashboard";
    session_start();
    //UI
    include('layouts/header.php');
    include('layouts/nav-bar.php');
    include('layouts/left-side-bar.php');

    include('classes/connect.php');
    include('classes/payment.php');
    include('classes/login.php');

    $login= new Login();
	$login->check_login($_SESSION['calamus_financial']);

    
?>
<!-- This is git ignore version -->
<!-- Test For git ignore -->
<!-- Body Start -->
<div class="wrapper">

    <div class="loading-container" id="main_pb1">
        <div class="spin"></div>
    </div>

    <div>
         <?php
 
         ?>
    </div>
    <div id="main_view1" style="display:none;">
        <div class="sa4d25">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <h2 class="st_title"><i class="uil uil-apps"></i> Earning</h2>
                    </div>
                      <div  class="col-xl-12 col-md-12"> 
                        <div class="card_dash">
                            <div class="card_dash_left">
                                <h5>Total Earning</h5>
                                <h2 id="current_total"> MMK</h2>
                                <div class="last-earning" id="last_month_total"><h6> MMK</h6></div>
                            </div>
                            <div class="card_dash_right">
                                <img src="images/dashboard/achievement.svg" alt="">
                            </div>
                        </div>
                    </div>

            

                    <div  class="col-xl-12 col-md-12"> 
                        <div class="row" id="project_container">
                            
                        </div>
                    </div>
                
                

                    <div class="col-xl-12 col-md-12">
                        <!-- Sales Graph -->
                        <div class="card card-default analysis_card p-0" data-scroll-height="450">
                            <div class="card-header">
                                <h2>Sales Of The Year</h2>
                            </div>
                            <div class="card-body p-5" style="height: 450px;">
                                <canvas id="saleOfYear" class="chartjs"></canvas>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
        <?php include('layouts/footer.php');?>

    <script src="vendor/charts/Chart.min.js"></script>
    <script> const req=JSON.parse(`<?php echo json_encode($_GET);?>`); </script>

    <script  type="module" src="./app/dashboard.js"> </script>