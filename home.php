<!DOCTYPE html>
<?php 
//starting the session
session_start();
?>
<html lang="en">
	<head>
		<meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1"/>
	</head>
<body>
	<div></div>
	<div>
		<h3>PHP - Login And Registration</h3>
		<hr/>
		<?php
			echo "session val:: Username: ".$_SESSION['username'].".</br>";
			echo "session val:: Password: ".$_SESSION['password']."</br>";
		?>
		<a href="login/login.php">Logout</a>
		<h1>Welcome User!</h1>
	</div>
</body>
</html>
