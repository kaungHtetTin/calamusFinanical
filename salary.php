<?php 
    $title="Financial | Balance";
    $path='staff-and-salary';
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
                        <h2 class="st_title"><i class="uil uil-dollar-sign"></i> Salary Payment</h2>
                    
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
                </div>


                <div class="row">
                   
                    <div class="col-lg-12 col-md-12">

                        <div class="date_selector">
                            <h4>Salary Payments For <span id="staff_name"></span></h4>
                        </div>

                        <div class="table-responsive mt-30">
                            <table class="table ucp-table earning__table">
                                <thead class="thead-s">
                                    <tr>
                                        <th scope="col">Project</th>
                                        <th scope="col">Amount</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">Action</th>	
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


						<!-- add a salary payment -->
                        <div class="date_selector">
                            <h4>Pay a salary</h4>
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

                            <div class="modal-body" style="height: 450px;" id="form_container">
                                <div id="pb_form_adding" style="display:none">
                                    <div style="padding: 150px 50%; text-align:center; position:absolute; background:hwb(0 100% 0% / 0.421)">
                                        <div class="spin"></div>
                                    </div>
                                </div>
                                <div class="tab-pane fade show active" id="nav-basic" role="tabpanel">
                                  
                                    <div class="new-section mt-30">
                                        <div class="form_group">
                                            <label class="label25">Amount*</label>
                                            <input id="input_amount" class="form_input_1" type="text" placeholder="Amount here">
                                        </div>
                                    </div>
                                     
                                    <div id="form_project_selector">
                                        <div class="new-section mt-30">
                                            <label class="label25">Project*</label>
                                            <div class="ui fluid search selection dropdown focus cntry152">
                                                <input type="hidden" name="project_type" class="prompt srch_explore" id="input_project">
                                                <i class="dropdown icon"></i>
                                                    <div class="default text">Select Here</div>
                                                    <div class="menu">
                                                    <div class="item" data-value="english">Easy English</div>
                                                    <div class="item" data-value="korea">Easy Korean</div>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="form_project_selector">
                                        <div class="new-section mt-30">
                                            <label class="label25">Pay From*</label>
                                            <div class="ui fluid search selection dropdown focus cntry152">
                                                <input type="hidden" name="project_type" class="prompt srch_explore" id="pay_from">
                                                <i class="dropdown icon"></i>
                                                    <div class="default text">Select Here</div>
                                                    <div class="menu">
                                                    <div class="item" data-value="1">Kaung Htet Tin</div>
                                                    <div class="item" data-value="2">Min Htet Kyaw</div>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="cogs-toggle mt-3">
                                        <label class="switch">
                                            <input type="checkbox" id="input_add_for_cost" value="">
                                            <span></span>
                                        </label>
                                        <label for="input_add_for_cost" class="lbl-quiz">Add amount to project cost</label>
                                    </div>

                                </div>

                                <div class="new-section mt-30" width="150px">
                                    <button id="btn_add" type="button" class="main-btn">Submit</button> 
                                   
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
    <script> const req=JSON.parse(`<?php echo json_encode($_GET);?>`); </script>

    <script  type="module">
        import * as Salary from './app/salary.js';
        window.deleteSalary=Salary.deleteSalary;
    </script>

    

