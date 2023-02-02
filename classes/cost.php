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

        function add($req){

            $title=$req['title'];
            $amount=$req['amount'];
            $major=$req['major'];

            $DB=new Database();
            $query="INSERT INTO costs (title,amount,major) VALUE ('$title',$amount,'$major')";
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