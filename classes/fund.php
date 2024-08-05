<?php
class Fund{
    function getCurrentBalance($staff_id){
        $query="Select * from funds where staff_id=$staff_id order by id desc limit 1";
        $DB=new Database();
        $result=$DB->read($query);
        if($result){
            return $result[0];
        }else {
            return array('current_balance'=>'0');
        }
        
    }

    function getTransactions($staff_id,$req){

        if(isset($req['month'])){
            $month=$req['month'];
        }else{
            $month=date('m');
        }
        
        if(isset($req['year'])){
            $year=$req['year'];
        }else{
            $year=date('Y');
        }

        $DB=new Database();

        $cost_query="select * from funds
        where date>='$year-$month-01'
            and date<='$year-$month-31'
            and staff_id=$staff_id
        ";

        $result=$DB->read($cost_query);
        return $result;
    }

    function add($req){

        $title=$req['title'];
        $amount=$req['amount'];
        $type=$req['type'];
        $staff_id=$req['staff_id'];

        if(isset($req['transferring_id'])){
            $transferring_id=$req['transferring_id'];
        }else{
            $transferring_id=0;
        }

        $DB=new Database();
        $query="Select * from funds where staff_id=$staff_id order by id desc limit 1";
        $lastTrans=$DB->read($query)[0];
        $current_balance=$lastTrans['current_balance'];
        
        if($type==0){
            $current_balance=$current_balance+$amount;
        }else{
             $current_balance=$current_balance-$amount;
        }
        

        $query="INSERT INTO funds (title,amount,current_balance,type,staff_id,transfer_id) VALUE ('$title',$amount,'$current_balance','$type',$staff_id,$transferring_id)";
        $result=$DB->save($query);
        if($result){
            $response['status']="success";
            $response['msg']="Transaction added successfully";
           
            return $response;
        }else{
            $result['status']="fail";
            $result['msg']="An unexpected error occurred!";
            return $response;
        }
    }

    function delete($id){

        $DB=new Database();

        $query_del="DELETE FROM funds where id=$id";

        $result=$DB->save($query_del);
        if($result){
            $response['status']="success";
            $response['msg']="Cost deleted successfully";
            return $response;
        }else{
            $result['status']="fail";
            $result['msg']="An unexpected error occurred!";
            return $response;
        }
    }

    function detail($id){
        $DB=new Database();
        $query="SELECT * FROM funds where id=$id";
        $result=$DB->read($query);
        return $result[0];
    }

    function deleteByTransferID($transfer_id){
        $DB=new Database();
        $query="DELETE FROM funds where transfer_id=$transfer_id";
        $result=$DB->save($query);

        if($result){
            $response['status']="success";
            $response['msg']="Cost deleted successfully";
            return $response;
        }else{
            $result['status']="fail";
            $result['msg']="An unexpected error occurred!";
            return $response;
        }
    }
}
?>