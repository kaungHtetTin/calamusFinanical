<?php
    include('../../classes/connect.php');
    include('../../classes/fund.php');

    $Fund=new Fund();
    $kaung_balance=$Fund->getCurrentBalance(1);
    $min_balance=$Fund->getCurrentBalance(2);
    $visa_balance=$Fund->getCurrentBalance(3);

    $response['kaung_balance']=$kaung_balance['current_balance'];
    $response['min_balance']=$min_balance['current_balance'];
    $response['visa_balance']=$visa_balance['current_balance'];

    $response['kaung_transactions']=$Fund->getTransactions(1,$_GET);
    $response['min_transactions']=$Fund->getTransactions(2,$_GET);
    $response['visa_transactions']=$Fund->getTransactions(3,$_GET);

    echo json_encode($response);

?>