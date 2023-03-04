<?php
    include('../../classes/connect.php');
    include('../../classes/salary.php');
    include('../../classes/cost.php');
    include('../../classes/staff.php');
    include('../../classes/fund.php');


    if($_SERVER['REQUEST_METHOD']=='POST'){

        $staff_id=$_POST['staff_id'];
        $amount=$_POST['amount'];
        $project=$_POST['project'];
        $pay_from=$_POST['pay_from'];

        if($staff_id=="" || $amount =="" || !is_numeric($amount) || $project=="" || $pay_from==""){
            $result['status']="fail";
            $result['msg']="Please enter the form correctly";
            echo json_encode($result);
            return;
        }

        $transfer_id=microtime(true);
        $_POST['transferring_id']=$transfer_id;

        // add salary payment
        $Salary=new Salary();
        $Salary->add($_POST);

        // add cost to project
        $Staff=new Staff();
        $staff=$Staff->getAStaff($staff_id);

        $staff_name=$staff['name'];
        $cost['title']="Salary payment to $staff_name";
        $cost['amount']=$amount;
        $cost['major']=$project;
        $cost['transferring_id']=$transfer_id;

        $Cost =new Cost();
        $Cost->add($cost);

        // reduce balance from founder
        $req['title']="Salary payment to $staff_name";
        $req['amount']=$amount;
        $req['type']=1;
        $req['staff_id']=$pay_from;
        $req['transferring_id']=$transfer_id;
        $Fund=new Fund();
        $Fund->add($req);

        $result['status']="success";
        $result['msg']="Salary payment added successfully!";
        echo json_encode($result);



    }else{
        $result['status']="fail";
        $result['msg']="Method not allow";
        echo json_encode($result);
    }

?>