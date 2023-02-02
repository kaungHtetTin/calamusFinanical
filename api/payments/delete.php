<?php
    include('../../classes/connect.php');
    include('../../classes/payment.php');

    if($_SERVER['REQUEST_METHOD']=='POST'){
       
        $Payment=new Payment();
        $result=$Payment->deletePayment($_POST);
        if($result){
            echo "Successfully Deleted";
        }else{
            echo "Delete Fail";
        }

    }else{
        echo "method not allow";
    }

?>