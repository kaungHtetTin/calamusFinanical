<?php

class Database {

	private $host="localhost";
	private $username="u608908096_kht_navy";
	private $password="kHt_5241";
	private $db="u608908096_easyenglish";

	//this is git ignore file

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
		}else{
			return true;
		}
	}

}
