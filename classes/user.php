<?php
class User{


    function detail($phone){
        $DB=new Database();
        $query="SELECT * from learners where learner_phone=$phone";
        $result =$DB->read($query);
        return $result;
    }

    function getVipCourses(){

    }

    function getEnglishData(){

    }

    function getKoreaData(){

    }

}

?>