<?php
    include('../../classes/connect.php');
    include('../../classes/cost.php');
    include('../../classes/fund.php');
    include('../../classes/salary.php');

    if($_SERVER['REQUEST_METHOD']=='POST'){
      
        $id=$_POST['id'];
        $Fund=new Fund();

        $fund=$Fund->detail($id);

  

        $transfer_id=$fund['transfer_id'];
        if($transfer_id==0){
            $result=$Fund->delete($id);
            echo json_encode($result);
            return;
        }else{
            $Fund->deleteByTransferID($transfer_id);

            $Cost=new Cost();
            $Cost->deleteByTransferID($transfer_id);
            
            $Salary=new Salary();
            $Salary->deleteByTransferID($transfer_id);
        }

    }else{
        $result['status']="fail";
        $result['msg']="Method not allow";
        echo json_encode($result);
    }

?>