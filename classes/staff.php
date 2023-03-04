<?php
class Staff{

    function add($req){

        $name=$req['name'];
        $rank=$req['rank'];
        $ranking=$req['ranking'];
        $project=$req['project'];

        $DB=new Database();
        $query="INSERT INTO staffs (name,rank,ranking,project,present) VALUES ('$name',$rank','$ranking','$project',1)";
        $result=$DB->save($query);
        return $result;
    }

    function getAStaff($id){
        $DB=new Database();
        $query="SELECT * FROM staffs WHERE id=$id";
        $result=$DB->read($query);
        return $result[0];

    }

    function getStaffs(){

        $DB=new Database();
        $query="SELECT * FROM staffs";
        $result=$DB->read($query);
        return $result;

    }

    function updateStaff(){
         $DB=new Database();

    }

    function removeStaff($id){
        //remove is not removing. it only change the present status
        $DB=new Database();
        $query="UPDATE staffs SET present=0 WHERE id=$id";
        $result=$DB->save($query);
        return $result;
    }
}

?>