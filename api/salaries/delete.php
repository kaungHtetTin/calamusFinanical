<?php
    include('../../classes/connect.php');
    include('../../classes/cost.php');
    include('../../classes/fund.php');
    include('../../classes/salary.php');

    if($_SERVER['REQUEST_METHOD']=='POST'){
      
        $id=$_POST['id'];
        $Salary=new Salary();

        $salary=$Salary->detail($id);

        $transfer_id=$salary['transfer_id'];
        if($transfer_id==0){
            $result=$Salary->delete($id);
            echo json_encode($result);
            return;
        }else{
            $Salary->deleteByTransferID($transfer_id);

            $Cost=new Cost();
            $Cost->deleteByTransferID($transfer_id);
            
            $Fund=new Fund();
            $Fund->deleteByTransferID($transfer_id);
        }

    }else{
        $result['status']="fail";
        $result['msg']="Method not allow";
        echo json_encode($result);
    }

?>