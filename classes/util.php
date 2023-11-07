<?php
class Util{
    function getLastMonth($data){
        if(isset($data['year'])){
            $year=$data['year'];
        }else{
            $year=date('Y');
        }

        if(isset($data['month'])){
            $month=$data['month'];
        }else{
            $month=date('m');
        }
        $month--;
        if($month==0){
            $month=12;
            $year--;
        }

        $result['year']=$year;
        $result['month']=$month;

        return $result;
        
        }
    }
?>