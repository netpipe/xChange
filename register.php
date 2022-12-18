<?php 
//starting the session
session_start();
?>
	<div></div>
	<div>
		<h3>PHP - Login And Registration</h3>
		<hr/>
		<!-- Link for redirecting to Login Page -->
		<a href="login.php">Already a member? Log in here...</a>
		<br/><br />
		<div></div>
		<div>
			<!-- Registration Form start -->
			<form method="POST" action="save_member.php">	
				<div>Registration</div>
				<div>
					<label>Username</label>
					<input type="text" name="username" required="required"/>
				</div>
				<div>
					<label>Password</label>
					<input type="password" name="password" required="required"/>
				</div>
				<div>
					<label>Firstname</label>
					<input type="text" name="firstname" required="required"/>
				</div>
				<div>
					<label>Lastname</label>
					<input type="text" name="lastname" required="required"/>
				</div>
				<?php
					//checking if the session 'success' is set. Success session is the message that the credetials are successfully saved.
					
				    if(isset($_SESSION["username"])){
					echo $_SESSION["username"];
					}
					
					if(ISSET($_SESSION['success'])){
				?>
				<!-- Display registration success message -->
				<div><?php echo $_SESSION['success']?></div>
				<?php
					//Unsetting the 'success' session after displaying the message. 
					unset($_SESSION['success']);
					}
				?>
				<button name="register"><span></span> Register</button>
			</form>	
			<!-- Registration Form end -->
		</div>
	</div>
