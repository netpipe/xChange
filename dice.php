<!DOCTYPE html>
<html lang="en" dir="ltr">

<?php
  session_start();


?>

<head>
  <meta charset="utf-8">
  <title>🎲 Dice Game</title>
  
  <!-- bootstrap 5 -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js"></script>

  <link rel="stylesheet" href="assets/dice.css">
  <link href="https://fonts.googleapis.com/css?family=Lobster" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.gstatic.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;700&display=swap" rel="stylesheet">
  <script type="text/javascript" src="./assets/axios.min.js"></script>
</head>

<body>
  <nav class="navbar navbar-expand-sm bg-grey justify-content-center">

    <div class="container">
      <!-- Links -->
      <select class="form-select-lg mb-3" aria-label=".form-select-lg example">
        <option value="1">DOGE Chain</option>
      </select>
      <span class="form-select-lg mb-3 text-center" aria-label=".form-select-lg example">
        0x<?=$_SESSION['deposit_address']?><br>
        [Deposit Address (Note: Only NETC or DOGE coin in DOGECHAIN)]
      </span>

      <select class="form-select-lg mb-3" aria-label=".form-select-lg example">
        <option value="1">NETC: 0</option>
        <option value="2">DOGE: 0</option>
      </select>

      <!-- <button class="btn btn-primary form-control mb-3">Withdraw</button> -->
    </div>

  </nav>
  <div class="container container_body">
    <h1>Roll the Dice</h1>

    <div class="dice">
      <p>You</p>
      <img class="img1" src="images/dice/dice6.png">
    </div>

    <div class="dice">
      <p>Computer</p>
      <img class="img2" src="images/dice/dice6.png">
    </div>

    <div class="dice_roll">
      <div class="input-group input-group-lg mb-3 mt-5">
        <input type="text" class="form-control" placeholder="Amount">
        <button class="btn btn-primary startRoll">Roll the Dice</button>
      </div>
    </div>
      
  </div>

  <script src="assets/dice.js"></script>
</body>

<footer>
  
</footer>

</html>