<?php
    include('../../classes/connect.php');
    include('../../classes/payment.php');
    include('../../classes/cost.php');

 

    $Payment =new Payment();
	$earnings=$Payment->getEarning($_GET);
	$payments=$earnings['payments'];
    $saleOfYear=$Payment->getSaleOfYear($_GET);
    $saleOfMonth=$Payment->getSaleOfMonth($_GET);
    $totalSaleOfDay=$Payment->getTotalSaleOfDay($_GET);

    $req['major']=$_GET['major'];
    $totalSaleAllTime=$Payment->getTotalSaleAmount($req);

	$Cost=new Cost();
	$projectCost=$Cost->getCosts($_GET);

	
    $response['earnings']=$earnings;
    $response['projectCosts']=$projectCost;
    $response['saleOfYear']=$saleOfYear;
    $response['saleOfMonth']=$saleOfMonth;
    $response['saleOfDay']=$totalSaleOfDay;
    $response['saleOfAllTime']=$totalSaleAllTime;

    echo json_encode($response);

?>