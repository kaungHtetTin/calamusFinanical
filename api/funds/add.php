<?php
    include('../../classes/connect.php');
    include('../../classes/fund.php');

    if($_SERVER['REQUEST_METHOD']=='POST'){
      
        $title=$_POST['title'];
        $amount=$_POST['amount'];
        $type=$_POST['type'];

        if($title==""){
            $result['status']="fail";
            $result['msg']="Please enter title";
            echo json_encode($result);
            return;
        }

        if($type==""){
            $result['status']="fail";
            $result['msg']="Please select the transaction type";
            echo json_encode($result);
            return;
        }

        if($amount=="" || !is_numeric($amount)){
            $result['status']="fail";
            $result['msg']="Please enter correct amount";
            echo json_encode($result);
            return;
        }

        $Fund=new Fund();
        $result=$Fund->add($_POST);

        echo json_encode($result);
        return;

    }else{
        $result['status']="fail";
        $result['msg']="Method not allow";
        echo json_encode($result);
    }
?>