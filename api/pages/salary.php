<?php
    include('../../classes/connect.php');
    include('../../classes/salary.php');
    include('../../classes/staff.php');

    $Salary=new Salary();
    $salaries=$Salary->get($_GET);
    $response['salaries']=$salaries;

    $Staff=new Staff();
    $staff=$Staff->getAStaff($_GET['staff_id']);

    $response['staff']=$staff;

    echo json_encode($response);

?>