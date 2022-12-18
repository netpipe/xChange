<?php
	//starting the session
	session_start();

	//including the database connection
	require_once 'conn.php';
	
	if(ISSET($_POST['register'])){
		// Setting variables
		$username = ""; //$_POST['id'];
		$password = "";//$_POST['password'];
		$firstname = "";//$_POST['datecreate'];
		$lastname = ""; //$_POST['encryption'];
		//setcookie("name", $username, time() + (86400 * 30), "/");
		// Insertion Query
		$query = "INSERT INTO `wallets` (id, password,datecreate,encryption) VALUES(:username, :password, :datecreated, :encryption)";
		$stmt = $conn->prepare($query);
		$stmt->bindParam(':id', $username);
		$stmt->bindParam(':password', $password);
		$stmt->bindParam(':datecreated', $datecreated);
		$stmt->bindParam(':encryption', $encryption);
		
		// Check if the execution of query is success
		if($stmt->execute()){
			//setting a 'success' session to save our insertion success message.
			$_SESSION['success'] = "Successfully created an account";

			//redirecting to the index.php 
			header('location: index.php');
		}

	}
?>
