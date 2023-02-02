<?php
    Class CourseCategory{
        function get(){
            $DB=new Database();
            $query="select * from course_categories";
            $result=$DB->read($query);
            return $result;
        }
    }
?>