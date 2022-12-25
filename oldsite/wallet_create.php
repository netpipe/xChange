<?php
	
	require_once('easybitcoin.php');
require_once("BitcoinECDSA.php");

use BitcoinPHP\BitcoinECDSA\BitcoinECDSA;
$bitcoinECDSA = new BitcoinECDSA();

$bitcoinECDSA->generateRandomPrivateKey();
$privkey = $bitcoinECDSA->getPrivateKey();
$getadd = $bitcoinECDSA->getAddress();

function signMessa($bitcoinECDSA){

    $bitcoinECDSA->generateRandomPrivateKey(); //generate new random private key

$message = "Test message";
$signedMessage = $bitcoinECDSA->signMessage($message);

echo "signed message:" . PHP_EOL;
echo $signedMessage . PHP_EOL;

$signature = $bitcoinECDSA->signMessage($message, true);

echo "signature:" . PHP_EOL;
echo $signature . PHP_EOL;

echo base64_decode($signature);
echo bin2hex(base64_decode($signature));
}
if (isset($_GET['hello'])) {
    signMessa($bitcoinECDSA);
}


	//including the database connection
	require_once 'conn.php';
	
	if(ISSET($_POST['register'])){
		// Setting variables
		$id = $getadd;//""; //$_POST['id'];
		$password = "";//$_POST['password'];
		$datecreate = "";//$_POST['datecreate'];
		$encryption = $privkey;//""; //$_POST['encryption'];
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
                <p>New Wallet BTC <span><?php echo $getadd; ?></span></p>
                <p>Priv Keys = <?php echo $privkey; ?> </p>
    <!-- Option 1: jQuery and Bootstrap Bundle (includes Popper) 
    <script src="./assets/jquery.slim.min.js"></script>
    <script src="./assets/bootstrap.bundle.min.js"></script>
-->
    <!-- Option 2: Separate Popper and Bootstrap JS -->
    <!--
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js" integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+" crossorigin="anonymous"></script>
    -->
