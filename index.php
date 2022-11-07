<?php
//	session_start();
	require_once 'login_query.php';
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
   <!-- <script src="https://unpkg.com/axios/dist/axios.min.js"></script>-->
    <script type="text/javascript" src="./assets/axios.min.js"></script>

    <!-- Ethereum library for interacting with the blockchain 
    <script src="https://cdn.jsdelivr.net/npm/web3@latest/dist/web3.min.js"></script>
    <script type="text/javascript" src="https://unpkg.com/web3modal@1.9.8/dist/index.js"></script>
    <script type="text/javascript" src="https://unpkg.com/@walletconnect/web3-provider@1.7.8/dist/umd/index.min.js"></script>
-->
    <script type="text/javascript" src="./assets/web3.min.js"></script>
    <script type="text/javascript" src="./assets/index.js"></script>
    <script type="text/javascript" src="./assets/index.min.js"></script>
<!--body {
  background: lightblue url("img_tree.gif") no-repeat fixed center;
  background-image: url('')
} -->


<body style="background:#968D87">
 <div id="wrapper">
	 <!--logo-->
    <div align="center" style="color:black;background-color:#4A5568;border-radius: 5px 5px 5px 5px;border:2px black solid;height:100%;width:100%;">
        <div style="margin:1px; border-radius: 15px 15px 15px 15px; border:black solid 1px; background:#515151">
        	<img src="xchange.png" width="800px" />
        </div>
<?php include("header.php"); ?>
    
	<div>
		<h3>PHP - Login And Registration</h3>
		<hr/>
		<?php
			echo "session val:: Username: ".$_SESSION['username'].".</br>";
			echo "session val:: Password: ".$_SESSION['password']."</br>";
		?>
		<a href="login.php">Logout</a>
		<h1>Welcome <?php $username=""; echo $username; ?> !</h1>
	</div>
      
<?php
//~ ini_set('display_errors', 1);
//~ ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
$sitename="http://www.qtCoin.ca";
         //   $pwd = getcwd();
            //chdir('test');
        
        
include_once("render.php");
	   
	
        //start of green div content box
        echo '<div id="vbody" style="border-radius: 15px 15px 15px 15px;clear:both;text-align:center;background-color:green; margin:20px;border:2px black solid;" >';
        
        
        //GET PAGE CALLBACK
        $page="";
        $page=$_GET["page"];

        // CONTACT
        if  ( $page == "contact"){
            echo '<a href="' . 'mailto:admin@' . $sitename.'"' .'> Email </a>';
          // rendervideo($test);
          
		// VIDEOS
        }  
                elseif ( $page == "coins" ) {
			echo '<p align="middle"><B>' . $sitename . ' Books</B>';
			$bookpath = "images";
			$dirs = glob($bookpath . '/*' , GLOB_ONLYDIR);
			foreach($dirs as $dirs2) {
				//render($dirs2.".php");
					render($dirs2);
				//get first file from dir to display as picture
			}
            echo "</p>";
        }  
                elseif (  $page == "pot" ) {
			echo '<p align="middle"><B>Cannabis Pictures</B>';
			$picpath = "uploads/mov/authd";
			$dirs = glob($picpath . '/*' , GLOB_ONLYDIR);
			foreach($dirs as $dirs2) {
				render($dirs2);
				//get first file from dir to display as picture
			}
            echo "</p>";
        }  

				else {
			//$expandedpath=site_root($page);
			$expandedpath = explode("/", $page);
					$expandedpath2 = $expandedpath[count($expandedpath) - 1];
			$expandedpath = $expandedpath[count($expandedpath) - 3];
	
			echo $expandedpath;
			if ( $expandedpath == "images"){
			//uploads/mov/authd/
                $files = glob($page.'/*.{JPG,GIF,PNG,jpg,png,gif,php}', GLOB_BRACE);

			} elseif ( $expandedpath == "mov"){
				
				$files = glob($page.'/*.{webm}', GLOB_BRACE);

			}
			    foreach($files as $file) {
					render($file);
					
				}
				//		echo "PIC";
					//	echo "./." . $page . "/" . $expandedpath2;
			include('./' . $page . '/' . $expandedpath2 . '.php');
		//	include ("./images/cg/cg.php");
		//	echo "unknown issue or age not verified.";
         //   header("location: http://www.grandgallery.net") ;
			}		
									
    //    elseif  ( $page == "" ){ // index.php
	//		echo "main page";
		//echo '<div class="wrapper">';
    //echo			'<div class="progress-bar">';
	//echo			'<span class="progress-bar-fill" style="width: 80%;"></span>';
			//echo '</div>';
		//echo '</div>';



		//	render("books");
			//render("stories");

		//	}

		//	  include ("./PHPChart/examples/pie.php");
   //include ("./phpm/examples/download_chart_as_buffer.php");
        echo '</div>'; //end of green content box

include ("faucet2.php");


      ?>

    
<progress max="100" value="80"></progress>
<?php include ("footer.php"); ?>

  </div> <!--wrapper-->
  

</body>
</html>
