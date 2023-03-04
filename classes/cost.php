<?php
    Class Cost{
        function getCosts($req){
            $major=$req['major'];

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

            $query_total="select sum(amount) as totalCost from costs
                where date>='$year-$month-01'
                and date<='$year-$month-31'
                and major='$major'
            ";

            $result['total_cost']=$DB->read($query_total)[0]['totalCost'];
            
            $cost_query="select * from costs
            where date>='$year-$month-01'
                and date<='$year-$month-31'
                and major='$major'
            ";

            $result['costs']=$DB->read($cost_query);

            return $result;

        }

        function detail($id){
            $DB=new Database();
            $query="SELECT * FROM costs where id=$id";
            $result=$DB->read($query);
            return $result[0];
        }

        function delete($id){
            $DB=new Database();
            $query="DELETE FROM costs where id=$id";
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

        function deleteByTransferID($transfer_id){
            $DB=new Database();
            $query="DELETE FROM costs where transfer_id=$transfer_id";
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

        function add($req){

            $title=$req['title'];
            $amount=$req['amount'];
            $major=$req['major'];

            if(isset($req['transferring_id'])){
                $transferring_id=$req['transferring_id'];
            }else{
                $transferring_id=0;
            }

            $DB=new Database();
            $query="INSERT INTO costs (title,amount,major,transfer_id) VALUE ('$title',$amount,'$major',$transferring_id)";
            $result=$DB->save($query);
            if($result){
                $response['status']="success";
                $response['msg']="Cost added successfully";
                return $response;
            }else{
                $result['status']="fail";
                $result['msg']="An unexpected error occurred!";
                return $response;
            }
        }
    }
?>