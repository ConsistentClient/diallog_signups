<?php

session_start();
$message="";
if(count($_POST)>0) {

   include 'connect.php';
   $db = connect_db();

   $un = $_POST["userName"];
   $pwd =  $_POST["password"];

   $un = CleanUpDesc($un);
   $pwd = CleanUpDesc($pwd);

   //echo $pwd ;
   $result = $db->query("SELECT * FROM users WHERE username = '$un' AND password = '$pwd'") or die (pg_last_error());
   if( $result->num_rows > 0 ) {
	   $obj = $result->fetch_object();

	   $_SESSION['user'] = $un;
	   $_SESSION['PER'] = $obj->perm;

	   header("Location: phpinfo.php");
   } else {
	   echo "<html><head><script type='text/javascript'>alert('Invalid Login Attempt!');</script></head></html>";
	   header("Location: login.php");
	   exit();
   }
}


?>

