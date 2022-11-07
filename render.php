<?php
	function render($file){
		$fileinfo=pathinfo($file);
	 							 $dirname = basename($file);
	 							 $filename = $fileinfo['filename'];
	 							 $extension = $fileinfo['extension'];
	// echo '<div style="background-color:red;width:auto;height:auto">'; 
	// echo "test";
				echo '<p>';
		echo '<div id="videobox" style="border-radius: 15px 15px 15px 15px;margin:20px;background-color:#c1c1c1;height:200px;float:left;border:2px black solid;width:330px;" >';
			//TITLEBAR for content box
		    echo '<div style="margin-left:  background-color:black;margin:2px">';
				//first bubble
				echo '<div style="border-radius: 15px 15px 15px 15px;width:10%; text-align:center; float:left;background-color:black;height:20px;">';
				echo "PIC";
				echo '</div>';
				//second bubble
				echo '<div style="border-radius: 15px 15px 15px 15px;width:80%; text-align:center; float:left;background-color:#5C6776;height:20px;">';
				if ( $extension == "") {
					echo '<a href="?page=' . $file . '">' . $dirname . '</a>';
				}
				if ( $extension == "php") {
					echo '<a href="./book/' . $filename . "/"  . $filename . '.php">' . $filename . '</a>';
					echo "PIC";
					//echo "include ./book/" . $filename . '/index.php">
				}
								if ( $extension == "jpg") {
					echo "PIC";
					//echo "include ./book/" . $filename . '/index.php">
				}
				else {
					echo '<a href="' . $file . "/" . $filename . '.php">' . $file . '</a>';
				//	echo 'include' . $file . "/" . $filename . ".php";
				echo "PIC";
				}
				echo '</div>';
				//3rd bubble
				echo '<div style="border-radius: 15px 15px 15px 15px;width:10%; text-align:center; float:left;background-color:#3A455B;height:20px;">';
				
				echo '</div>';
			    echo '</div>'; //titlebar

		   // echo '<div style="clear: both;vertical-align: middle;width:auto;height:auto" >';
		   
				//content box
				    echo '<div style="clear:both;width:auto;height:auto" >';
					 // echo '<video controls  style="clear:both;" poster="title_anouncement.jpg" width="250">';


					 if ( $extension == "webm" ) {
						echo '<video controls style="" preload="none" loop="1" poster="play.png" width="300">';
							echo '<source src="' . $file . '"' .'type="video/webm";codecs="vp8, vorbis" />'; 
						echo '</video>';
					 } elseif ( $extension == "" ) { 
						// echo "testing123";
						 echo '<div align="middle" style="height:300px;width:300px"/>';
						 echo '<p><a href="?page=' . $file . '">' ;
						 echo '<img src="./' . $file . '/' . $dirname . '.png' . '"' . ' alt="' . $file . '"' . ' width="' . '160px"' . ' height="' . '160px"' . '/></a></p>';
						 echo "</div>";		
						// echo "</p>";
					 }
					 elseif ( $extension == "php" ) { 
						// echo "testing123";
						 //echo $dirname;
						 echo '<div align="middle" style="height:300px;width:300px">';
						 //echo '<p><a href="./books/' . $filename . '/index.html">' ;
echo "PIC";
					//	 echo '<img src="./books/' . $filename . '/' . $filename . '.jpg' . '"' . ' alt="' . $filename . '"' . ' width="' . '160px"' . ' height="' . '160px"' . '/></a></p>';
						 echo "</div>";		
						// echo "</p>";
						// need jpg or png option
					 }
					 else { 
						 echo "hi";
						 //~ echo "<img src=" . '"http://grandgallery.net/' . $file . 'width="400px"' . "/>"; 
						 echo "<p><a href=" . './' . $file . ">";
						 echo '<img src="./' . $file . '"' . ' alt="'. $file .'"' .' width="' .'160px"' . ' height="'.'160px"' . '/>'; 
						 echo '</a></p>';	
							}
				echo '</div>';//content box
	   echo '</div>'; //videobox
	   echo "</p>";

	}  //contentbox generator
?>
