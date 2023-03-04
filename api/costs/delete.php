<?php
    include('../../classes/connect.php');
    include('../../classes/cost.php');
    include('../../classes/fund.php');
    include('../../classes/salary.php');
    

    if($_SERVER['REQUEST_METHOD']=='POST'){
      
        $id=$_POST['id'];
        $Cost=new Cost();

        $cost=$Cost->detail($id);
        $transfer_id=$cost['transfer_id'];
        if($transfer_id==0){
            $result=$Cost->delete($id);
            echo json_encode($result);
            return;
        }else{
            $Cost->deleteByTransferID($transfer_id);
            $Fund=new Fund();
            $Fund->deleteByTransferID($transfer_id);
            $Salary=new Salary();
            $Salary->deleteByTransferID($transfer_id);
        }

       

    }else{
        $result['status']="fail";
        $result['msg']="Method not allow";
        echo json_encode($result);
    }

?>