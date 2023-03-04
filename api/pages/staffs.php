<?php
    include('../../classes/connect.php');
    include('../../classes/staff.php');

    $Staff=new Staff();

    $staffs=$Staff->getStaffs();

    $response['staffs']=$staffs;

    echo json_encode($response);

?>