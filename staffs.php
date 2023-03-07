<?php 
    $title="Financial | Balance";
    $path='staff-and-salary';
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
                        <h2 class="st_title"><i class="uil uil-dollar-sign"></i> Staffs and Salary </h2>
                    
                    </div>					
                </div>

                <div class="date_selector">

                    <div class="ui selection dropdown skills-search vchrt-dropdown">
                        <input name="date" type="hidden" value="default" id="project_selector">
                        <i class="dropdown icon d-icon"></i>
                        <div class="text">Project</div>
                        <div class="menu">
                            <div class="item" data-value="all">All</div>
                            <div class="item" data-value="english">Easy English</div>
                            <div class="item" data-value="korea">Easy Koean</div>
                        </div>
                    </div>

                    <div class="ui selection dropdown skills-search vchrt-dropdown">
                        <input name="date" type="hidden" value="default" id="status_selector">
                        <i class="dropdown icon d-icon"></i>
                        <div class="text">Status</div>
                        <div class="menu">
                            <div class="item" data-value="all">All</div>
                            <div class="item" data-value="1">In Service</div>
                            <div class="item" data-value="0">Out Of Service</div>
                        </div>
                    </div>
                </div>


                <div class="row">
                   
                    <div class="col-lg-12 col-md-12">

                        <div class="date_selector">
                            <h4>List of Staffs</h4>
                        </div>

                        <div class="table-responsive mt-30">
                            <table class="table ucp-table earning__table">
                                <thead class="thead-s">
                                    <tr>
                                        <th scope="col">Name</th>
                                        <th scope="col">Rank</th>
                                        <th scope="col">Project</th>
                                        <th scope="col">Status</th>
                                        <th colspan="3" scope="col" class="text-center" >Action</th>	
                                    </tr>
                                </thead>
                                <tbody id="staff_container">
                                    
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
                        <div>
                               <a href="add-new-staff.php" class="upload_btn" title="Add new staff">Add New Staff</a>
                        </div>
                    </div>

				</div>
                    
                </div>
            
            </div>
        </div>
        <?php include('layouts/footer.php');?>
   
	<!-- The Modal -->
    <div id="modal_container"></div>
    

    <script  type="module">
        import * as Remaining from './app/staff.js';
        
    </script>

    

