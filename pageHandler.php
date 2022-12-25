 <?php       
        //GET PAGE CALLBACK
        $page="";
        $page=$_GET["page"];
//echo $_GET['success'];
//unset($_SESSION["success"]);

        // DOWNLOADS
        if  ( $page == "downloads"){
          //  echo '<a href="' . 'mailto:admin@' . $sitename.'"' .'> Email </a>';
          // rendervideo($test);
          include("w2box/index.php"); 

        }   
                        // CHAT
        if  ( $page == "order"){        include("./coins/orderform.php");          }  
                // CHAT
        if  ( $page == "chat"){        include("chat.php");          }   
        // PRODUCT
        if  ( $page == "product"){     include($page + "/index.php");}   
        // REGISTER
        if  ( $page == "register"){    include("login/register.php");      }   
        // LOGIN
        if  ( $page == "login"){       include("login/login.php");         }   
        // CONTACT
        if  ( $page == "contact"){
            echo '<a href="' . 'mailto:admin@' . $sitename.'"' .'> Email </a>';
          // rendervideo($test);

        }   
        //GAMES            
           elseif ( $page == "games" ) {
			echo '<p align="middle"><B>' . $sitename . ' Books</B>';
			$bookpath = "wallets";
			$dirs = glob($bookpath . '/*' , GLOB_ONLYDIR);
			foreach($dirs as $dirs2) {
				//render($dirs2.".php");
					render($dirs2);
				//get first file from dir to display as picture
			}
			}
			//COINS
                elseif ( $page == "coins" ) {
			echo '<p align="middle"><B>' . $sitename . ' Books</B>';
			
		    echo '<form action="upload.php" method="post" enctype="multipart/form-data">';
            echo    '<p style="color:#F78989">use wallet generated file to upload transaction';
            echo   '<input type="file" name="fileToUpload" id="fileToUpload">';
            echo    '<input type="submit" value="Upload" name="submit">';
            echo   '</p>';
            echo '</form>';
    
    
			include ("coins/faucet2.php");
			    include ("coins/dogetx.php");
			

    
			$bookpath = "wallets";
			$dirs = glob($bookpath . '/*' , GLOB_ONLYDIR);
			foreach($dirs as $dirs2) {
				//render($dirs2.".php");
					render($dirs2);
				//get first file from dir to display as picture
			}
            echo "</p>";
        }  
        //CANNABIS
                elseif (  $page == "pot" ) {
			echo '<p align="middle"><B>Cannabis Pictures</B>';
			$picpath = "images";
			$dirs = glob($picpath . '/*' , GLOB_ONLYDIR);
			

			foreach($dirs as $dirs2) {
				$ep = explode("/", $dirs2);
			    $ep2 = $ep[count($ep) - 1];
			    $ep = $ep[count($ep) - 3];

			    if ( $ep2 != "dice" ){
				    render($dirs2);
				    //get first file from dir to display as picture
			    }
			}
            echo "</p>";
        }  else {
        

        
			//$expandedpath=site_root($page);
			$expandedpath = explode("/", $page);
			$expandedpath2 = $expandedpath[count($expandedpath) - 1];
			$expandedpath = $expandedpath[count($expandedpath) - 2];
			//echo $expandedpath;
			//echo $page;
			if ( $expandedpath == "images"){
			//uploads/mov/authd/
                $files = glob($page.'/*.{JPG,GIF,PNG,jpg,png,gif,wmv,webm}', GLOB_BRACE);
			    foreach($files as $file) {
					render($file);
				}
						echo '<a href="index.php?page=pot"> back </a>';
				 include('./' . $page . '/index.php');
			} elseif ( $expandedpath == "mov"){
				$files = glob($page.'/*.{webm}', GLOB_BRACE);
			    foreach($files as $file) {
					render($file);
				}
		
				 //include('./' . $page . '/index.php');
			} else {
			    if ( $expandedpath2 == "" ){
					    include("mainpage.php");				
			    }
			}


						
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
		
		?>
