<?php
    include('../../classes/connect.php');
    include('../../classes/payment.php');

       
    $Payment=new Payment();
    $result = $Payment->getPendingPayment();

    echo json_encode($result);

?>