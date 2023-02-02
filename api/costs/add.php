<?php
    include('../../classes/connect.php');
    include('../../classes/cost.php');

    if($_SERVER['REQUEST_METHOD']=='POST'){
      
        $title=$_POST['title'];
        $amount=$_POST['amount'];
        $major=$_POST['major'];

        if($title==""){
            $result['status']="fail";
            $result['msg']="Please enter title";

            echo json_encode($result);
            return;
        }

        if($amount=="" || !is_numeric($amount)){
            $result['status']="fail";
            $result['msg']="Please enter correct amount";
            echo json_encode($result);
            return;
        }

        $Cost=new Cost();
        $result=$Cost->add($_POST);

        echo json_encode($result);
        return;

    }else{
        $result['status']="fail";
        $result['msg']="Method not allow";
        echo json_encode($result);
    }

?>