<?php

/**
 *
 * @author Jan Moritz Lindemann
 */

namespace BitcoinPHP\BitcoinECDSA;
require_once(__DIR__ . '/../BitcoinPHP/BitcoinECDSA/BitcoinECDSA.php');


$bitcoinECDSA = new BitcoinECDSA();

$nonce = 'addSomeRandomness';
$bitcoinECDSA->generateRandomPrivateKey($nonce);

$wallet_private_key = $bitcoinECDSA->getPrivateKey();
$wallet_pubic_key = $bitcoinECDSA->getPubKey();;
$wallet_address = $bitcoinECDSA->getAddress();

echo $wallet_private_key

?>
