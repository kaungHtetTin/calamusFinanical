<?php 
    $title="Financial | Earnings";
    $path=$_GET['path'];

    $major=$_GET['major'];

    if($major=='korea'){
        echo "<h3>No longer access this page</h3>";

        return;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
      <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, shrink-to-fit=9">
    <meta name="description" content="Gambolthemes">
    <meta name="author" content="Gambolthemes">
    <title><?php echo $title ?></title>

    <!-- Favicon Icon -->
    <link rel="icon" type="image/png" href="images/fav.png">

    <!-- Stylesheets -->
    <link href='http://fonts.googleapis.com/css?family=Roboto:400,700,500' rel='stylesheet'>
    <link href='vendor/unicons-2.0.1/css/unicons.css' rel='stylesheet'>
    <link href="css/vertical-responsive-menu1.min.css" rel="stylesheet">
    <link href="css/instructor-dashboard.css" rel="stylesheet">
    <link href="css/instructor-responsive.css" rel="stylesheet">
    <link href="css/night-mode.css" rel="stylesheet">
     <link href="css/mystyle.css" rel="stylesheet">

    <!-- Vendor Stylesheets -->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="vendor/OwlCarousel/assets/owl.carousel.css" rel="stylesheet">
    <link href="vendor/OwlCarousel/assets/owl.theme.default.min.css" rel="stylesheet">
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="vendor/semantic/semantic.min.css">
</head>
<body>

    <header class="header clearfix">
        <h3 style="padding-left: 10px;"> Financial | <?php echo $path ?></h3> 
    </header>

    <div class="">
        Hello world
        <div class="loading-container" id="main_pb">
            <div class="spin"></div>
        </div>

        <div id="main_view" style="display:none">

            <div class="sa4d25">
                <div class="container-fluid">	

                    <div class="date_selector">
                        <div class="ui selection dropdown skills-search vchrt-dropdown">
                            <input name="date" type="hidden" value="default" id="year_selector">
                            <i class="dropdown icon d-icon"></i>
                            <div class="text">Years</div>
                            <div class="menu" id="year_container">
                            
                                
                            </div>
                        </div>

                        <div class="ui selection dropdown skills-search vchrt-dropdown">
                            <input name="date" type="hidden" value="default" id="month_selector">
                            <i class="dropdown icon d-icon"></i>
                            <div class="text">Month</div>
                            <div class="menu" id="month_container">
                            
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">						
                            <div class="earning_steps">						
                                <p>Total Earning (MMK)</p>
                                <h2 id="total_earning"> </h2>
                            </div>
                        </div>
                        <div class="col-md-4">						
                            <div class="earning_steps">						
                                <p>Total Cost (MMK)</p>
                                <h2 id="total_cost"> </h2>
                            </div>
                        </div>
                        <div class="col-md-4">						
                            <div class="earning_steps">						
                                <p>Net Earning (MMK)</p>
                                <h2 id="net_earning"> </h2>
                            </div>
                        </div>

                        <div class="col-xl-12 col-md-12">
                            <!-- Sales Graph -->
                            <div class="card card-default analysis_card p-0" data-scroll-height="450">
                                <div class="card-header">
                                    <h2>Sales Of The Year</h2>
                                </div>
                                <div class="card-body p-5" style="height: 450px;" id="project_sale_of_year_container">
                                    <canvas id="project_sale_of_year" class="chartjs"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-12 col-md-12">
                            <!-- Sales Graph -->
                            <div class="card card-default analysis_card p-0" data-scroll-height="450">
                                <div class="card-header">
                                    <h2>Sales Of The Month</h2>
                                </div>
                                <div class="card-body p-5" style="height: 450px;" id="project_sale_of_month_container">
                                    <canvas id="project_sale_of_month" class="chartjs"></canvas>
                                </div>
                            </div>
                        </div>


                        <div class="col-lg-3 col-md-12">
                            <div class="top_countries mt-50">
                                <div class="top_countries_title">
                                    <h2>Today</h2>
                                </div>
                                <ul class="country_list">
                                    <li>
                                        <div class="country_item">
                                            <div class="country_item_left">
                                                <span>Subscriber</span>
                                            </div>
                                            <div class="country_item_right">
                                                <span id="subscriber_today"> </span>
                                            </div>
                                        </div>
                                    </li>
                                    
                                    <li>
                                        <div class="country_item">
                                            <div class="country_item_left">
                                                <span>Total Sale</span>
                                            </div>
                                            <div class="country_item_right">
                                                <span id="total_sale_today"> </span>
                                            </div>
                                        </div>
                                    </li>
                                    
                                </ul>
                            </div>

                            <div class="top_countries mt-50">
                                <div class="top_countries_title">
                                    <h2>Current Month</h2>
                                </div>
                                <ul class="country_list">
                                    <li>
                                        <div class="country_item">
                                            <div class="country_item_left">
                                                <span>Subscriber</span>
                                            </div>
                                            <div class="country_item_right">
                                                <span id="subscriber"> </span>
                                            </div>
                                        </div>
                                    </li>
                                    
                                    <li>
                                        <div class="country_item">
                                            <div class="country_item_left">
                                                <span>Total Sale</span>
                                            </div>
                                            <div class="country_item_right">
                                                <span id="total_sale_current_month"> </span>
                                            </div>
                                        </div>
                                    </li>
                                    
                                </ul>
                            </div>

                            <div class="top_countries mt-50">
                                <div class="top_countries_title">
                                    <h2>All Time</h2>
                                </div>
                                <ul class="country_list">
                                    <li>
                                        <div class="country_item">
                                            <div class="country_item_left">
                                                <span>Subscriber</span>
                                            </div>
                                            <div class="country_item_right">
                                                <span id="subscriber_all_time"> </span>
                                            </div>
                                        </div>
                                    </li>
                                    
                                    <li>
                                        <div class="country_item">
                                            <div class="country_item_left">
                                                <span>Total sale</span>
                                            </div>
                                            <div class="country_item_right">
                                                <span id="total_Sale_all_time"> </span>
                                            </div>
                                        </div>
                                    </li>
                                    
                                </ul>
                            </div>

                        </div>

                        <div class="col-lg-9 col-md-12">
                            

                            <div class="date_selector">
                                <h4>Payment</h4>
                            </div>

                            <div class="table-responsive mt-30">
                                <table class="table ucp-table earning__table">
                                    <thead class="thead-s">
                                        <tr>
                                            <th scope="col">Name</th>
                                            <th scope="col">Phone</th>
                                            <th scope="col">Amount</th>
                                            <th scope="col">Date</th>		
                                            <th colspan="2" scope="col" class="text-center" >Action</th>					
                                        </tr>
                                    </thead>
                                    <tbody id="payment_container">
                                        
                                        
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="2">Total</td>
                                            <td colspan="4" ><span id="total_payment" > </span> MMK </td>
                                        
                                        </tr>
                                    </tfoot>
                                </table>

                            </div>

                            <div style="display:flex;">

                                <div class="text" style="padding: 5px 0px; margin-right: 5px;">Show rows:</div>
                                <div  class="ui dropdown table-offset-dropdown">
                                    <input name="date" type="hidden" value="10" id="payment_offset">
                                    <div class="text" style="margin-left:7px;" >10</div>
                                    <div class="menu" >
                                        <div class="item" data-value="10">10</div>
                                        <div class="item" data-value="20">20</div>
                                        <div class="item" data-value="50">50</div>
                                        <div class="item" data-value="100">100</div>
                                    </div>
                                    <i class="dropdown icon d-icon"></i>
                                </div>

                                <div id="payment_row_counter" class="text" style="padding: 5px 0px; margin-right: 10px; margin-left:10px;"></div>
                    
                                <div class="date_list152">
                                    <span id="btnFirstPaymentList"><i class="fa fa-angle-double-left fa-fw" aria-hidden="true"></i></span> 
                                    <span id="btnPrevPaymentList"><i class="fa fa-angle-left fa-fw" aria-hidden="true"></i></span>
                                    <span id="btnNextPaymentList"><i class="fa fa-angle-right fa-fw" aria-hidden="true"></i></span>
                                    <span id="btnLastPaymentList"><i class="fa fa-angle-double-right fa-fw" aria-hidden="true"></i></span> 
                                    <a href="#"></a>
                                </div>
                            </div>

                            <div class="date_selector">
                                <h4>Costs</h4>
                            </div>

                            <div class="table-responsive mt-30">
                                <table class="table ucp-table earning__table">
                                    <thead class="thead-s">
                                        <tr>
                                            <th scope="col">Title</th>
                                            <th scope="col">Amount</th>
                                            <th scope="col">Date</th>		
                                            <th colspan="2" scope="col" class="text-center" >Action</th>						
                                        </tr>
                                    </thead>
                                    <tbody id="cost_container">
                                        
                                        
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="2">Total</td>
                                            <td colspan="3"><span id="total_cost_list" ></span>  MMK</td>
                                        
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div style="display:flex;">

                                <div class="text" style="padding: 5px 0px; margin-right: 5px;">Show rows:</div>
                                <div  class="ui dropdown table-offset-dropdown">
                                    <input name="date" type="hidden" value="10" id="cost_offset">
                                    <div class="text" style="margin-left:7px;" >10</div>
                                    <div class="menu" >
                                        <div class="item" data-value="10">10</div>
                                        <div class="item" data-value="20">20</div>
                                        <div class="item" data-value="50">50</div>
                                        <div class="item" data-value="100">100</div>
                                    </div>
                                    <i class="dropdown icon d-icon"></i>
                                </div>

                                <div id="cost_row_counter" class="text" style="padding: 5px 0px;  margin-right: 10px; margin-left:10px;"></div>
                    
                                <div class="date_list152">
                                    <span id="btnFirstCostList"><i class="fa fa-angle-double-left fa-fw" aria-hidden="true"></i></span> 
                                    <span id="btnPrevCostList"><i class="fa fa-angle-left fa-fw" aria-hidden="true"></i></span>
                                    <span id="btnNextCostList"><i class="fa fa-angle-right fa-fw" aria-hidden="true"></i></span>
                                    <span id="btnLastCostList"><i class="fa fa-angle-double-right fa-fw" aria-hidden="true"></i></span> 
                                    <a href="#"></a>
                                </div>
                            </div>

                        </div>

                    </div>
                        
                    </div>
                
                </div>
                <?php include('layouts/footer.php');?>
            </div>
        
        </div>
    </div>
    <script src="vendor/charts/Chart.min.js"></script>
    <script> const req=JSON.parse(`<?php echo json_encode($_GET);?>`); </script>

    <script  type="module">
        import * as Earning from './app/earning.js';
    </script>
    
</body>
</html>