<?php
    include('../../classes/connect.php');
    include('../../classes/fund.php');

    if($_SERVER['REQUEST_METHOD']=='POST'){
      
        $id=$_POST['id'];
        $Fund=new Fund();
        $result=$Fund->delete($id);
        echo json_encode($result);
        return;
        
    }else{
        $result['status']="fail";
        $result['msg']="Method not allow";
        echo json_encode($result);
    }

?>