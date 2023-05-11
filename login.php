
<?php

 
    session_start();

    include ("classes/connect.php");
    include ("classes/login.php");
    
    $email="";
 
    $login=new Login();
    if($_SERVER['REQUEST_METHOD']=='POST'){
        
        
        $result=$login->evaluate($_POST);
        
        if($result!=""){
            
            echo "<div style='text-align:center;font-size:12px; color:white;background-color:grey;'>";
            echo "<br>The following errors occured<br><br>";
            echo $result;
            echo "</div>";
            
        }else {
            
            header("Location: index.php");
            die;
            //echo "login access";
        }
        
    }

    if(isset($_COOKIE['calamus_financial'])){
       $data['password']= $_COOKIE['calamus_financial'];
       $result=$login->evaluate($data);
       if($result==""){
            header("Location: index.php");
            die;
       }
    }

?>


<html>

    <head>
        <title> Calamus | Financial</title>
    </head>

    <style>
    
        #bar{
            height:100px;
            background-color:#3c5a99; 
            color: #d9dfeb;
            padding:4px;
             
        }
    
        #signup_button{
            background-color: #42b72a;
            width:70px;
            text-align: center;
            padding:4px;
            border-radius: 4px;
            float:right;
            
        }
        
        #bar2{
            background-color:white; 
            width:800px; 
            margin:auto;
            color:red;
            margin-top:50px;
            font-weight:bold;
            padding:10px;
            padding-top:50px;
            text-align:center;
        }
        
        #text{
            height:40px;
            width:300px;
            border-radius:4px;
            border:solid 1px #ccc;
            padding:4px;
            font-size: 14px;
             
        }
        
        #button{
            width:300px;
            height:40px;
            border-radius:4px;
            font-weight:bold;
            border:none;
            background-color:red; 
            color:white;
        }
        
    </style>

    <body style="font-family:tahoma;background-color: #e9ebee;">
    


        <div id="bar2">
            Log in to Calamus | Financial <br><br>
            <form method="post" action="">
               
                <input name="password" type="password" id="text" placeholder="Password"><br><br>
                <input type="submit" id="button" value="Log in">
            <br><br><br>
            </form>
        </div>

    </body>


</html>