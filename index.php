<?php
//	session_start();
	require_once 'login/login_query.php';
?>
<!DOCTYPE html>
<html xml:lang="en" lang="en">
<head>
    <meta name="Keywords" content="keywords" /><meta name="Description" content="qtCoin" />

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <!--<meta http-equiv="refresh" content="50">-->
    <!--should be set higher for less traffic use DEVELOPMENT MODE-->

    <style type="text/css" media="screen, projection">
        @import "style.css";
    </style>
    
    <title>xChange</title>
    
</head>
<!-- Remember to chmod 0755 uploads directory -->



<body style="background:#968D87">
 <div id="wrapper">
	 <!--logo-->
    <div align="center" style="color:black;background-color:#4A5568;border-radius: 5px 5px 5px 5px;border:2px black solid;height:100%;width:100%;">
        <div style="margin:1px; border-radius: 15px 15px 15px 15px; border:black solid 1px; background:#515151">
        	<img src="xchange.png" width="800px" />
        </div>
<?php include("header.php"); ?>
    
	<div>
		<?php

			//echo "session val:: Password: ".$_SESSION['password']."</br>";
		if ( $_SESSION['username'] == "" ){
		echo '<a href="index.php?page=login">LOGIN</a>';
		} else {			echo "welcome:: ".$_SESSION['username'].".</br>"; echo '<a href="index.php?page=login">LOGOUT</a>';}
		?>

	</div>
      
<?php
// ini_set('display_errors', 1);
//~ ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
    $sitename="qtCoin.ca";
         //   $pwd = getcwd();
            //chdir('test');
        
    include_once("render.php");
        //start of green div content box
    echo '<div id="vbody" style="border-radius: 15px 15px 15px 15px;clear:both;text-align:center;background-color:green; margin:20px;border:2px black solid;" >';
  
    include ("pageHandler.php");
 //include ("wallet_create.php");
		//	  include ("./PHPChart/examples/pie.php");
   //include ("./phpm/examples/download_chart_as_buffer.php");
    echo '</div>'; //end of green content box
      ?>

    
<progress max="100" value="80"></progress>
<?php include ("footer.php"); ?>

  </div> <!--wrapper-->
  

</body>
</html>
