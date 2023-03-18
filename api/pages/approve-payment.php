<?php
    include('../../classes/connect.php');
    include('../../classes/payment.php');

       
    $Payment=new Payment();
    $result = $Payment->getPendingPayment();

    $response['payments']=$result;
    echo json_encode($response);

?>