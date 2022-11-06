<?php

require_once('easybitcoin.php');
require_once("BitcoinECDSA.php");
use BitcoinPHP\BitcoinECDSA\BitcoinECDSA;
$bitcoinECDSA = new BitcoinECDSA();

$bitcoinECDSA->generateRandomPrivateKey();
$privkey = $bitcoinECDSA->getPrivateKey();
$getadd = $bitcoinECDSA->getAddress();
$url = "https://dogechain.info/api/v1/pushtx";

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$headers = array(
   "Content-Type: application/json",
);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

$data = '{"tx":"H1kG82AYcsuQYLZYOaRCDEoQ8chh6AkiWtHT9Cu1PN7fB2gjg/M5rEyzl7NDlK3y5cEI4mfFMAuzQtFXiai9Hfw="}';

curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

//for debug only!
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$resp = curl_exec($curl);
curl_close($curl);
var_dump($resp);

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


//$url = "tunnel783824-pt.tunnel.tserv20.hkg1.ipv6.he.net";

//$curl = curl_init($url);
//curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
//curl_setopt($curl, CURLOPT_MAXREDIRS , 10);
//curl_setopt($curl, CURLOPT_URL, $url);
//curl_setopt($curl, CURLOPT_POST, true);
//curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));


//$headers = array(
//   "Content-Type: application/json",
//   "Content-Length: 0",
//);
//curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

//$raw_response = curl_exec($curl);
//$response     = json_decode($raw_response, true);

//var_dump($raw_response);



//$bitcoin = new Bitcoin('username','password','188.165.86.26','8333');

//$info = $bitcoin->getinfo();

//var_dump($bitcoin);
?>

<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">

    <title>Bitcoin RPC TX API</title>
</head>

<body>
    <div class="container">
        <div class="row">

            <div class="col-12">

                <h1>Bitcoin RPC TX API</h1>

                <?php ?>

                <p>New Wallet Generation <span><?php echo $getadd; ?></span></p>
                <p>Priv Keys = <?php echo $privkey; ?> </p>

                <h3>Sign MEssague</h3>
                <a href='index.php?hello=true'>Sign </a>
                <form>
                    <div class="form-group">
                        <label for="exampleInputEmail1">Host</label>
                        <input type="text" class="form-control" id="host" aria-describedby="emailHelp">
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">Port</label>
                        <input type="text" class="form-control" id="port" aria-describedby="emailHelp">
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">Username</label>
                        <input type="text" class="form-control" id="username" aria-describedby="emailHelp">
                    </div>
                    <div class="form-group">
                        <label for="exampleInputPassword1">Password</label>
                        <input type="password" class="form-control" id="pass">
                    </div>

                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>

            </div>

        </div>
    </div>

    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: jQuery and Bootstrap Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>

    <!-- Option 2: Separate Popper and Bootstrap JS -->
    <!--
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js" integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+" crossorigin="anonymous"></script>
    -->
</body>

</html>