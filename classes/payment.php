<?php
class Payment{
    
    function getTotalSale($req){

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
        $query_total="select sum(amount) as totalAmount from payments 
            where date>='$year-$month-01'
            and date<='$year-$month-31'
        ";

        $query_project="
            select sum(amount) as totalAmount, project_name,major,icon from payments 
            JOIN course_categories on major=keyword 
            where date>='$year-$month-01'
            and date<='$year-$month-31'
            GROUP BY major
        ";

        $result['total']=$DB->read($query_total)[0]['totalAmount'];


        $projects=$DB->read($query_project);

        if($month==1){
            $month=12;
            $year=$year-1;
        }else{
            $month=$month-1;
        }


        $query_total="select sum(amount) as totalAmount from payments 
            where date>='$year-$month-01'
            and date<='$year-$month-31'
        ";

        $query_project="
            select sum(amount) as totalAmount, project_name,major,icon from payments 
            JOIN course_categories on major=keyword 
            where date>='$year-$month-01'
            and date<='$year-$month-31'
            GROUP BY major
        ";

        $result['last_total']=$DB->read($query_total)[0]['totalAmount'];
        $last_month=$DB->read($query_project);
        
        for($i=0;$i<count($projects);$i++){
            $project=$projects[$i];
            for($j=0;$j<count($last_month);$j++){
                $last=$last_month[$j];
                if($last['major']==$project['major']){
                    $projects[$i]['last_month']=$last['totalAmount'];
                }

            }
        }

        $result['projects']=$projects;
        return $result;
    }


    function getEarning($req){

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

        $query_total="select sum(amount) as totalAmount from payments 
            where date>='$year-$month-01'
            and date<='$year-$month-31'
            and major='$major'
        ";

        $query_payments="
            SELECT payments.id,learner_name,learner_phone,amount,date
            from payments 
            JOIN learners on learners.learner_phone=payments.user_id
            where date>='$year-$month-01'
            and date<='$year-$month-31'
            and major='$major'
        ";

        $result['total']=$DB->read($query_total)[0]['totalAmount'];
        $result['payments']=$DB->read($query_payments);

        return $result;

    }

    function getTotalSaleAmount($req){
       
        $whereClase="";

        if(isset($req['month'])){
            $month=$req['month'];
            $whereClase.="and MONTH(date)=$month ";
        }

        if(isset($req['year'])){
            $year=$req['year'];
            $whereClase.="and YEAR(date)=$year ";
        }

        if(isset($req['major'])){
            $major=$req['major'];
            $whereClase.="and major='$major' ";
        }

        if($whereClase!=""){
            $whereClase=substr($whereClase,3);
            $whereClase="WHERE ".$whereClase;
        }


        $query="SELECT SUM(amount) as amount FROM payments $whereClase";
        $DB=new Database($query);

      
        return $DB->read($query)[0]['amount'];

    }


    function getSaleOfYear($req){
        if(isset($req['year'])){
            $year=$req['year'];
        }else{
            $year=date('Y');
        }

        $DB=new Database();
        
        if(isset($req['major'])){
            $major=$req['major'];
             $query="
                SELECT sum(amount) as amount,MONTH(date) as month from payments 
                where YEAR(date)=$year and major='$major'
                group by MONTH(date); 
            ";
        }else{
            $query="
                SELECT sum(amount) as amount,MONTH(date) as month from payments where YEAR(date)=$year 
                group by MONTH(date); 
            ";
        }
        $result=$DB->read($query);
        return $result;
    }

    function getTotalSaleOfDay($req){
        if(isset($req['date'])){
            $day=$req['date'];
        }else{
            $day=date('Y-m-d');
        }

        $major=$req['major'];

        $DB=new Database();
        $query="
        SELECT sum(amount) as total_amount, count(*) as total_subscriber from payments
        where date='$day'
        and major='$major'
        ";

        $result=$DB->read($query)[0];

        return $result;
    }

    function deletePayment($req){
        $id=$req['id'];
        
        $DB=new Database();
        $query="delete from payments where id=$id";
        $result=$DB->save($query);
        return $result;
    }
}

?>