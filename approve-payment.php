<?php 
    $title="Financial | Balance";
    $path='Approve-payment';
    session_start();
	//UI
    include('layouts/header.php');
    include('layouts/nav-bar.php');
    include('layouts/left-side-bar.php');
    include('classes/login.php');

    $login= new Login();
	$login->check_login($_SESSION['calamus_financial']);
    
?>

<!-- Body Start -->
 
<div class="wrapper">

    <div class="loading-container" id="main_pb">
        <div class="spin"></div>
    </div>

    <div>
         <?php
			  
         ?>
    </div>

    <div id="main_view" style="display:none;">
        <div class="sa4d25">
            <div class="container-fluid">	
            
                <div class="row">
                    <div class="col-lg-12">	
                        <h2 class="st_title"><i class="uil uil-dollar-sign"></i> Approve Payments </h2>
                    
                    </div>					
                </div>

                <div class="row">
                   
                    <div class="col-lg-12 col-md-12">

                        <div class="date_selector">
                            <h4>Pending Payments</h4>
                        </div>

                        <div class="table-responsive mt-30">
                            <table class="table ucp-table earning__table">
                                <thead class="thead-s">
                                    <tr><th scope="col">Project</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Phone</th>
                                        <th scope="col">Amount</th>
                                        <th scope="col">Date</th>
                                        <th class="text-center" colspan="2" >Action</th>	
                                    </tr>
                                </thead>
                                <tbody id="table_container">
                                    
                                </tbody>
                            </table>

                        </div>

                        <div style="display:flex;">

                            <div class="text" style="padding: 5px 0px; margin-right: 5px;">Show rows:</div>
                            <div  class="ui dropdown table-offset-dropdown">
                                <input name="date" type="hidden" value="10" id="offset">
                                <div class="text" style="margin-left:7px;" >10</div>
                                <div class="menu" >
                                    <div class="item" data-value="10">10</div>
                                    <div class="item" data-value="20">20</div>
                                    <div class="item" data-value="50">50</div>
                                    <div class="item" data-value="100">100</div>
                                </div>
                                <i class="dropdown icon d-icon"></i>
                            </div>

                            <div id="row_counter" class="text" style="padding: 5px 0px; margin-right: 10px; margin-left:10px;"></div>
                
                            <div class="date_list152">
                                <span id="btnFirstList"><i class="fa fa-angle-double-left fa-fw" aria-hidden="true"></i></span> 
                                <span id="btnPrevList"><i class="fa fa-angle-left fa-fw" aria-hidden="true"></i></span>
                                <span id="btnNextList"><i class="fa fa-angle-right fa-fw" aria-hidden="true"></i></span>
                                <span id="btnLastList"><i class="fa fa-angle-double-right fa-fw" aria-hidden="true"></i></span> 
                                <a href="#"></a>
                            </div>
                        </div>

                        <br><br>
                    </div>

				</div>
                    
                </div>
            
            </div>
        </div>
        <?php include('layouts/footer.php');?>
   
	<!-- The Modal -->
    <div id="modal_container"></div>
    

    <script  type="module">
        import * as MyFile from './app/approve-payment.js';
        window.approvePayment=MyFile.approvePayment;
    </script>

    

