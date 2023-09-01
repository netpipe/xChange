<?php
/// Script start

/// Creates variable $dice and sets the content to a random number between the 1 and 6
$dice = rand(1,6);
$high = rand(0,1);
$win = 0;

/// Display $dice 
#echo $dice;
#echo $high;
/// If $dice is a 6 then display echo message
if($dice >= 4) {
if($high == 1) {
$win = 1;
} 
}

/// If $dice is lower then 3 display echo message
if($dice <= 3) {
if($high == 0) {
$win = 1; 
}
}

if($win == 1) { echo "winner"; } else { echo "loss"; }
/// Script end
?>
