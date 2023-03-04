

<?php
class Salary{
    function get($req){

        $staff_id=$req['staff_id'];

        if(isset($req['year'])){
            $year=$req['year'];
        }else{
            $year=date('Y');
        }

        $DB=new Database();
        $query="SELECT * FROM salaries  WHERE staff_id=$staff_id AND YEAR(date)=$year";
        $result=$DB->read($query);
        return $result;
    }


    function add($req){
        $staff_id=$req['staff_id'];
        $amount=$req['amount'];
        $project=$req['project'];

        if(isset($req['transferring_id'])){
            $transferring_id=$req['transferring_id'];
        }else{
            $transferring_id=0;
        }

        $DB=new Database();
        $query="INSERT INTO salaries (staff_id,amount,project,transfer_id) VALUES ($staff_id,$amount,'$project',$transferring_id)";

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

        $query_del="DELETE FROM salaries where id=$id";

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
        $query="SELECT * FROM salaries where id=$id";
        $result=$DB->read($query);
        return $result[0];
    }

    function deleteByTransferID($transfer_id){
        $DB=new Database();
        $query="DELETE FROM salaries where transfer_id=$transfer_id";
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