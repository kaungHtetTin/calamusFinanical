<?php
    include('../../classes/connect.php');
    include('../../classes/payment.php');
    include('../../classes/cost.php');

 

    $Payment =new Payment();
	$earnings=$Payment->getEarning($_GET);
	$payments=$earnings['payments'];
    $saleOfYear=$Payment->getSaleOfYear($_GET);
    $totalSaleOfDay=$Payment->getTotalSaleOfDay($_GET);

	$Cost=new Cost();
	$projectCost=$Cost->getCosts($_GET);

	
    $response['earnings']=$earnings;
    $response['projectCosts']=$projectCost;
    $response['saleOfYear']=$saleOfYear;
    $response['saleOfDay']=$totalSaleOfDay;

    echo json_encode($response);

?>