<?php

$number1 = random_int(1, 6);
$number2 = random_int(1, 6);
$result = json_encode(array('status' => 'success', 'number1' => $number1, 'number2' => $number2));

echo $result;
