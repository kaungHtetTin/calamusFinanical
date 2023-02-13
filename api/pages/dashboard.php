<?php
    include('../../classes/connect.php');
    include('../../classes/payment.php');
    include('../../classes/cost.php');
    include('../../classes/course-category.php');

    if(isset($_GET['month'])){
        $month=$_GET['month'];
    }else{
        $month=date('m');
    }
    
    if(isset($_GET['year'])){
        $year=$_GET['year'];
    }else{
        $year=date('Y');
    }
 
    $Payment =new Payment();
    $DB=new Database();
    $CourseCategory=new CourseCategory();
    $projects=$CourseCategory->get();
    
    //Total sale
    $total_sale_req['month']=$month;
    $total_sale_req['year']=$year;


    if($month==1){
        $month=12;
        $year=$year-1;
    }else{
        $month=$month-1;
    }

    $last_sale_req['month']=$month;
    $last_sale_req['year']=$year;

    $total_sale=$Payment->getTotalSaleAmount($total_sale_req);
    $response['total_sale']['current']=$total_sale['amount'];

    $last_sale=$Payment->getTotalSaleAmount($last_sale_req);
    $response['total_sale']['last']=$last_sale['amount'];


    foreach($projects as $project){
        $total_sale_req['major']=$project['keyword'];
        $total_sale=$Payment->getTotalSaleAmount($total_sale_req);
        $project['total_sale']=$total_sale['amount'];

        $last_sale_req['major']=$project['keyword'];
        $last_sale=$Payment->getTotalSaleAmount($last_sale_req);
        $project['last_sale']=$last_sale['amount'];

        $response['projects'][]=$project;
    }

    $saleOfYear=$Payment-> getSaleOfYear($_GET);
    $response['saleOfYear']=$saleOfYear;
    echo json_encode($response);
    
?>