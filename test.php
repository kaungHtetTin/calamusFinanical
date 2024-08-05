<?php
include('classes/connect.php');

$DB=new Database();
$query="SELECT SUM(amount) FROM payments";
$result=$DB->read($query);

echo json_encode($result);
?>