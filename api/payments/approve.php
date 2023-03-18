<?php
    include('../../classes/connect.php');
    include('../../classes/payment.php');

    if($_SERVER['REQUEST_METHOD']=='POST'){
       
        $Payment=new Payment();
        $result=$Payment->approve($_POST);
        if($result){
            echo "Successfully approved";
        }else{
            echo "Approvement Fail";
        }

    }else{
        echo "method not allow";
    }

?>