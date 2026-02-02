<?php

class Database {

	// private $host="82.180.143.139";
	// private $username="u608908096_kht_navy";
	// private $password="kHt_5241";
	// private $db="u608908096_easyenglish";

	private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $db = "calamus_db";

	//this is git ignore file
	// I want to ignore this file

	function connect(){
		$connection=mysqli_connect($this->host,$this->username,$this->password,$this->db);
		return $connection;
	}

	function read($query){
		$conn=$this->connect();

		$result=mysqli_query($conn,$query);

		if(!$result){
			return false;
		}else{

			$data=false;
			 while ($row=mysqli_fetch_assoc($result)) {
					 
					$data[]=$row;
			}

			return $data;

		}
	}

	function save($query){
		$conn=$this->connect();
		$result=mysqli_query($conn,$query);

		if(!$result){
			return false;
		}
		// For INSERT, return the new row id so it can be used as transfer_id
		if (stripos($query, 'INSERT INTO') === 0) {
			$id = mysqli_insert_id($conn);
			return $id ? $id : true;
		}
		return true;
	}

}
