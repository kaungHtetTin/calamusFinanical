<?php
    include('../../classes/connect.php');
    include('../../classes/cost.php');

    if($_SERVER['REQUEST_METHOD']=='POST'){
      
        $id=$_POST['id'];
        $Cost=new Cost();
        $result=$Cost->delete($id);
        echo json_encode($result);
        return;

    }else{
        $result['status']="fail";
        $result['msg']="Method not allow";
        echo json_encode($result);
    }

?>