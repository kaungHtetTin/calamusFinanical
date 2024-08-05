
<?php
    include('../../classes/connect.php');
    include('../../classes/fund.php');

    if($_SERVER['REQUEST_METHOD']=='POST'){

        $staffs[1]="Kaung Htet Tin";
        $staffs[2]="Min Htet Kyaw";
        $staffs[3]="Visa";
      
        $to=$_POST['to'];
        $from=$_POST['from'];
        $amount=$_POST['amount'];

        if($to==""){
            $result['status']="fail";
            $result['msg']="Please select TO input";
            echo json_encode($result);
            return;
        }

        if($from==""){
            $result['status']="fail";
            $result['msg']="Please select FROM input";
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

        if($to==2){
            
        }else{
            $title="Transfer to Kaung Htet Tin";
            $title_receive="Received from Min Htet Kyaw";
        }

        $title="Transfer to ".$staffs[$to];
        $title_receive="Received from ".$staffs[$from];

        $transfer_id=microtime(true);

        $req_transfer['title']=$title;
        $req_transfer['amount']=$amount;
        $req_transfer['type']=1;
        $req_transfer['staff_id']=$from;
        $req_transfer['transferring_id']=$transfer_id;

        $result=$Fund->add($req_transfer);

        $req_receive['title']=$title_receive;
        $req_receive['amount']=$amount;
        $req_receive['type']=0;
        $req_receive['staff_id']=$to;
        $req_receive['transferring_id']=$transfer_id;
        $result=$Fund->add($req_receive);
        echo json_encode($result);
        return;

    }else{
        $result['status']="fail";
        $result['msg']="Method not allow";
        echo json_encode($result);
    }

?>