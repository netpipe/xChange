<?php 
//starting the session
//session_start();
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
			<form method="POST" action="save_address.php">	
				<div>Registration</div>
				<div>
					<label>Firstname</label>
					<input type="text" name="firstname" required="required"/>

					<label>last name</label>
					<input type="text" name="address" required="required"/>
				</div>
								<div>
					<label>postalcode</label>
					<input type="text" name="postalcode" required="required"/>

					<label>address</label>
					<input type="text" name="address" required="required"/>
				</div>
								<div>
					<label>country</label>
					
					
 <input type="text" id="theinput" name="country" />
 <select name="thelist" onChange="combo(this, 'theinput')">
   <option>Canada</option>
   <option>USA</option>
   <option>Europe</option>
 </select>
<script>  function combo(thelist, theinput) {
     theinput = document.getElementById(theinput);
     var idx = thelist.selectedIndex;
     var content = thelist.options[idx].innerHTML;
     theinput.value = content;
 }</script>
					<label>province/state</label>
					<input type="text" name="province" required="required"/>
				</div>
				<?php
					//checking if the session 'success' is set. Success session is the message that the credetials are successfully saved.
		$_SESSION["favcolor"] = "greengreengreengreengreengreengreen3greengreengreengreen6";
				//	echo $_SESSION["favcolor"];
					 


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
