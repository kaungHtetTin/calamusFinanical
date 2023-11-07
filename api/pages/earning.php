<?php
    include('../../classes/connect.php');
    include('../../classes/payment.php');
    include('../../classes/cost.php');
    include('../../classes/util.php');

 

    $Payment =new Payment();
	$earnings=$Payment->getEarning($_GET);
	$payments=$earnings['payments'];
    $saleOfYear=$Payment->getSaleOfYear($_GET);
    $saleOfMonth=$Payment->getSaleOfMonth($_GET);

    $Util=new Util();
    $reqData=$Util->getLastMonth($_GET);
    $reqData['major']=$_GET['major'];
    $saleOfPreviousMonth=$Payment->getSaleOfMonth($reqData);

    $totalSaleOfDay=$Payment->getTotalSaleOfDay($_GET);

    $req['major']=$_GET['major'];
    $totalSaleAllTime=$Payment->getTotalSaleAmount($req);

    $req['year']= $year=date('Y');
    $totalSaleOfCurrentYear=$Payment->getTotalSaleAmount($req);

	$Cost=new Cost();
	$projectCost=$Cost->getCosts($_GET);

	
    $response['earnings']=$earnings;
    $response['projectCosts']=$projectCost;
    $response['saleOfYear']=$saleOfYear;
    $response['saleOfMonth']=$saleOfMonth;
    $response['saleOfLastMonth']=$saleOfPreviousMonth;
    $response['saleOfDay']=$totalSaleOfDay;
    $response['totalSaleOfCurrentYear']=$totalSaleOfCurrentYear;
    $response['saleOfAllTime']=$totalSaleAllTime;

    echo json_encode($response);

?>