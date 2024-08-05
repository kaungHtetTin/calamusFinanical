<?php 
    $title="Financial | Balance";
    $path='remaining-balance';
    session_start();
	//UI
    
    include('classes/login.php');

    $login= new Login();
	$login->check_login($_SESSION['calamus_financial']);

    include('layouts/header.php');
    include('layouts/nav-bar.php');
    include('layouts/left-side-bar.php');
    
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
                        <h2 class="st_title"><i class="uil uil-dollar-sign"></i> Remaining Balance</h2>
                    
                    </div>					
                </div>

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
                            <p> Kaung Htet Tin </p>
                            <h2 id="kht_balance"> </h2>
                        </div>
                    </div>

                    <div class="col-md-4">						
                        <div class="earning_steps">						
                            <p> Min Htet Kyaw </p>
                            <h2 id="mhk_balance"> </h2>
                        </div>
                    </div>

                    <div class="col-md-4">						
                        <div class="earning_steps">						
                            <p>Visa</p>
                            <h2 id="visa_balance"> </h2>
                        </div>
                    </div>


                    <div class="col-lg-12 col-md-12">
                        

                        <div class="date_selector">
                            <h4>Transaction (Kaung Htet Tin)</h4>
                        </div>

                        <div class="table-responsive mt-30">
                            <table class="table ucp-table earning__table">
                                <thead class="thead-s">
                                    <tr>
                                        <th scope="col">Title</th>
                                        <th scope="col">Type</th>
                                        <th scope="col">Amount</th>
                                        <th scope="col">Current Balance</th>
                                        <th scope="col">Date</th>		
                                        <th colspan="2" scope="col" class="text-center" >Action</th>					
                                    </tr>
                                </thead>
                                <tbody id="transaction_contrainer_kht">
                                    
                                </tbody>
                            </table>

                        </div>

                        <div style="display:flex;">

                            <div class="text" style="padding: 5px 0px; margin-right: 5px;">Show rows:</div>
                            <div  class="ui dropdown table-offset-dropdown">
                                <input name="date" type="hidden" value="10" id="transaction_offset_kaung">
                                <div class="text" style="margin-left:7px;" >10</div>
                                <div class="menu" >
                                    <div class="item" data-value="10">10</div>
                                    <div class="item" data-value="20">20</div>
                                    <div class="item" data-value="50">50</div>
                                    <div class="item" data-value="100">100</div>
                                </div>
                                <i class="dropdown icon d-icon"></i>
                            </div>

                            <div id="row_counter_kaung" class="text" style="padding: 5px 0px; margin-right: 10px; margin-left:10px;"></div>
                
                            <div class="date_list152">
                                <span id="btnFirstKaungList"><i class="fa fa-angle-double-left fa-fw" aria-hidden="true"></i></span> 
                                <span id="btnPrevkaungList"><i class="fa fa-angle-left fa-fw" aria-hidden="true"></i></span>
                                <span id="btnNextKaungList"><i class="fa fa-angle-right fa-fw" aria-hidden="true"></i></span>
                                <span id="btnLastkaungList"><i class="fa fa-angle-double-right fa-fw" aria-hidden="true"></i></span> 
                                <a href="#"></a>
                            </div>
                        </div>

                        <div class="date_selector">
                           <h4>Transaction (Min Htet Kyaw)</h4>
                        </div>

                        <div class="table-responsive mt-30">
                            <table class="table ucp-table earning__table">
                                <thead class="thead-s">
                                    <tr>
                                        <th scope="col">Title</th>
                                        <th scope="col">Type</th>
                                        <th scope="col">Amount</th>
                                        <th scope="col">Current Balance</th>
                                        <th scope="col">Date</th>		
                                        <th colspan="2" scope="col" class="text-center" >Action</th>					
                                    </tr>
                                </thead>
                                <tbody id="transaction_contrainer_mhk">
                                    
                                </tbody>
                            </table>

                        </div>

                        <div style="display:flex;">

                            <div class="text" style="padding: 5px 0px; margin-right: 5px;">Show rows:</div>
                            <div  class="ui dropdown table-offset-dropdown">
                                <input name="date" type="hidden" value="10" id="transaction_offset_min">
                                <div class="text" style="margin-left:7px;" >10</div>
                                <div class="menu" >
                                    <div class="item" data-value="10">10</div>
                                    <div class="item" data-value="20">20</div>
                                    <div class="item" data-value="50">50</div>
                                    <div class="item" data-value="100">100</div>
                                </div>
                                <i class="dropdown icon d-icon"></i>
                            </div>

                            <div id="row_counter_min" class="text" style="padding: 5px 0px;  margin-right: 10px; margin-left:10px;"></div>
                
                            <div class="date_list152">
                                <span id="btnFirstMinList"><i class="fa fa-angle-double-left fa-fw" aria-hidden="true"></i></span> 
                                <span id="btnPrevMinList"><i class="fa fa-angle-left fa-fw" aria-hidden="true"></i></span>
                                <span id="btnNextMinList"><i class="fa fa-angle-right fa-fw" aria-hidden="true"></i></span>
                                <span id="btnLastMinList"><i class="fa fa-angle-double-right fa-fw" aria-hidden="true"></i></span> 
                                <a href="#"></a>
                            </div>
                        </div>

                        <div class="date_selector">
                           <h4>Transaction (Visa)</h4>
                        </div>

                        <div class="table-responsive mt-30">
                            <table class="table ucp-table earning__table">
                                <thead class="thead-s">
                                    <tr>
                                        <th scope="col">Title</th>
                                        <th scope="col">Type</th>
                                        <th scope="col">Amount</th>
                                        <th scope="col">Current Balance</th>
                                        <th scope="col">Date</th>		
                                        <th colspan="2" scope="col" class="text-center" >Action</th>					
                                    </tr>
                                </thead>
                                <tbody id="transaction_contrainer_visa">
                                    
                                </tbody>
                            </table>

                        </div>

                        <div style="display:flex;">

                            <div class="text" style="padding: 5px 0px; margin-right: 5px;">Show rows:</div>
                            <div  class="ui dropdown table-offset-dropdown">
                                <input name="date" type="hidden" value="10" id="transaction_offset_visa">
                                <div class="text" style="margin-left:7px;" >10</div>
                                <div class="menu" >
                                    <div class="item" data-value="10">10</div>
                                    <div class="item" data-value="20">20</div>
                                    <div class="item" data-value="50">50</div>
                                    <div class="item" data-value="100">100</div>
                                </div>
                                <i class="dropdown icon d-icon"></i>
                            </div>

                            <div id="row_counter_visa" class="text" style="padding: 5px 0px;  margin-right: 10px; margin-left:10px;"></div>
                
                            <div class="date_list152">
                                <span id="btnFirstVisaList"><i class="fa fa-angle-double-left fa-fw" aria-hidden="true"></i></span> 
                                <span id="btnPrevVisaList"><i class="fa fa-angle-left fa-fw" aria-hidden="true"></i></span>
                                <span id="btnNextVisaList"><i class="fa fa-angle-right fa-fw" aria-hidden="true"></i></span>
                                <span id="btnLastVisaList"><i class="fa fa-angle-double-right fa-fw" aria-hidden="true"></i></span> 
                                <a href="#"></a>
                            </div>
                        </div>

						<!-- add a transaction -->
                        <div class="date_selector">
                            <h4>Add a new transaction</h4>
                        </div>
                        <div class="card card-default analysis_card p-0" >
                            <div id="msg_box_success" style="display:none">
                                <div class="bg-success" style="padding: 7px;color:white;" id="success_msg">
                                    
                                </div>
                            </div>

                            <div id="msg_box_fail" style="display:none">
                                <div class="bg-danger" style="padding: 7px;color:white;" id="fail_msg">
                                    
                                </div>
                            </div>

                            <div class="modal-body" style="height: 500px;">
                                <div id="pb_trans_adding" style="display:none">
                                    <div style="padding: 150px 50%; text-align:center; position:absolute; background:hwb(0 100% 0% / 0.421)">
                                        <div class="spin"></div>
                                    </div>
                                </div>
                                <div class="tab-pane fade show active" id="nav-basic" role="tabpanel">
                                    <div class="new-section mt-30">
                                        <div class="form_group">
                                            <label class="label25">Transaction Title*</label>
                                            <input id="input_transc_title" class="form_input_1" type="text" placeholder="Title here">
                                        </div>
                                    </div>

                                    <div class="new-section mt-30">
                                        <div class="form_group">
                                            <label class="label25">Transaction Amount*</label>
                                            <input id="input_transc_amount" class="form_input_1" type="text" placeholder="Amount here">
                                        </div>
                                    </div>
                                     
                                    <div class="new-section mt-30">
                                        <label class="label25">Transaction Type*</label>
                                        <div class="ui fluid search selection dropdown focus cntry152">
                                            <input type="hidden" name="transaction_type" class="prompt srch_explore" id="input_transc_type">
                                            <i class="dropdown icon"></i>
                                                <div class="default text">Select Here</div>
                                                <div class="menu">
                                                <div class="item" data-value="1">Out</div>
                                                <div class="item" data-value="0">In</div>
                                                
                                            </div>
                                        </div>
                                    </div>

                                    <div class="new-section mt-30">
                                        <label class="label25">Owner*</label>
                                        <div class="ui fluid search selection dropdown focus cntry152">
                                            <input type="hidden" name="transaction_type" class="prompt srch_explore" id="input_staff_id">
                                            <i class="dropdown icon"></i>
                                                <div class="default text">Select Here</div>
                                                <div class="menu">
                                                <div class="item" data-value="1">Kaung Htet Tin</div>
                                                <div class="item" data-value="2">Min Htet Kyaw</div>
                                                 <div class="item" data-value="3">Visa</div>
                                            </div>
                                        </div>
                                    </div>
                                                            

                                </div>

                                <div class="new-section mt-30" width="150px">
                                    <button id="btn_add_transaction" type="button" class="main-btn">Submit</button> 
                                   
                                </div>
                                
                               

                            </div>

                            
                        </div>


                        <div class="date_selector">
                            <h4>Balance Transferring</h4>
                        </div>
                        <div class="card card-default analysis_card p-0" >
                            <div id="msg_box_success_tx" style="display:none">
                                <div class="bg-success" style="padding: 7px;color:white;" id="success_msg_tx">
                                    
                                </div>
                            </div>

                            <div id="msg_box_fail_tx" style="display:none">
                                <div class="bg-danger" style="padding: 7px;color:white;" id="fail_msg_tx">
                                    
                                </div>
                            </div>

                            <div class="modal-body" style="height: 400px;">
                                <div id="pb_balance_transfer" style="display:none">
                                    <div style="padding: 150px 50%; text-align:center; position:absolute; background:hwb(0 100% 0% / 0.421)">
                                        <div class="spin"></div>
                                    </div>
                                </div>
                                <div class="tab-pane fade show active" id="nav-basic" role="tabpanel">
                                    
                                     
                                    <div class="new-section mt-30">
                                        <label class="label25">From</label>
                                        <div class="ui fluid search selection dropdown focus cntry152">
                                            <input type="hidden" name="transaction_type" class="prompt srch_explore" id="input_trans_from">
                                            <i class="dropdown icon"></i>
                                                <div class="default text">Select Here</div>
                                                <div class="menu">
                                                <div class="item" data-value="1">Kaung Htet Tin</div>
                                                <div class="item" data-value="2">Min Htet Kyaw</div>
                                                <div class="item" data-value="3">Visa</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="new-section mt-30">
                                        <label class="label25">To</label>
                                        <div class="ui fluid search selection dropdown focus cntry152">
                                            <input type="hidden" name="transaction_type" class="prompt srch_explore" id="input_trans_to">
                                            <i class="dropdown icon"></i>
                                                <div class="default text">Select Here</div>
                                                <div class="menu">
                                                <div class="item" data-value="1">Kaung Htet Tin</div>
                                                <div class="item" data-value="2">Min Htet Kyaw</div>
                                                <div class="item" data-value="3">Visa</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="new-section mt-30">
                                        <div class="form_group">
                                            <label class="label25">Transferring Amount*</label>
                                            <input id="input_transfer_amount" class="form_input_1" type="text" placeholder="Amount here">
                                        </div>
                                    </div>
                                                            

                                </div>

                                <div class="new-section mt-30" width="150px">
                                    <button id="btn_balance_transfer" type="button" class="main-btn">Submit</button> 
                                   
                                </div>
                                
                               

                            </div>

                            
                        </div>

                    </div>

				</div>
                    
                </div>
            
            </div>
        </div>
        <?php include('layouts/footer.php');?>
   
	<!-- The Modal -->
    <div id="modal_container"></div>
  
    <script src="vendor/charts/Chart.min.js"></script>
    <script> const req=JSON.parse(`<?php echo json_encode($_GET);?>`); </script>

    <script  type="module">
        import * as Remaining from './app/remaining-balance.js';
        window.deleteTransaction=Remaining.deleteTransaction;
    </script>
