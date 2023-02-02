<?php
    include('../../classes/connect.php');
    include('../../classes/payment.php');
  
    $Payment=new Payment();
    $result=$Payment->getTotalSaleAmount($_GET);

    echo json_encode($result);
     
?>