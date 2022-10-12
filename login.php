<!DOCTYPE html>
<?php 
//starting the session
//session_start();
//unset($_SESSION["username"]);
//unset($_SESSION["password"]);
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
		<!-- Link for redirecting page to Registration page -->
		<a href="register.php">Not a member yet? Register here...</a>
		<br/><br />
		<div></div>
		<div>
			<!-- Login Form Starts -->
			<form method="POST" action="index.php">	
				<div>Login</div>
				<div>
					<label>Username</label>
					<input type="text" name="username" required="required"/>
				</div>
				<div>
					<label>Password</label>
					<input type="password" name="password" required="required"/>
				</div>
				<?php
					//checking if the session 'error' is set. Erro session is the message if the 'Username' and 'Password' is not valid.
					if(ISSET($_SESSION['error'])){
				?>
				<!-- Display Login Error message -->
					<div><?php echo $_SESSION['error']?></div>
				<?php
					//Unsetting the 'error' session after displaying the message. 
					session_unset();
					}
				?>
				<button name="login"><span></span> Login</button>
			</form>	
			<!-- Login Form Ends -->
		</div>
	</div>
</body>
</html>
