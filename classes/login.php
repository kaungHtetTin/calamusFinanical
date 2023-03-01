<?php

class Login
{
	private $error="";
	
	
	public function evaluate($data){
	 
		$password=addslashes($data['password']);
		//$password=hash("md5", $password);
		
		if($password=="52415241@@"){
            $_SESSION['calamus_financial']="access";
            return $error;
        }else{
            $error="Wrong password";
            return $error;
        }
		
	}
	
	public function check_login($access){    
        if($access!="access"){
                header("Location: login.php");
            die;
        }
	}
}
 