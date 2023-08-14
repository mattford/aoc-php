<?php
$doorPublicKey = 17786549;
$cardPublicKey = 7573546;
//$doorPublicKey = 17807724;
//$cardPublicKey = 5764801;

$cardLoopSize = 0;
$value = 1;
do {
    $cardLoopSize++;
    $value = doLoop($value, 7);
} while ($value !== $cardPublicKey);

$doorLoopSize = 0;
$value = 1;
do {
    $doorLoopSize++;
    $value = doLoop($value, 7);
} while ($value !== $doorPublicKey);

$encryptionKey = 1;
for ($i = 0; $i < $doorLoopSize; $i++) {
    $encryptionKey = doLoop($encryptionKey, $cardPublicKey);
}

echo "Part 1: $encryptionKey\n";

function doLoop($value, $subjectNumber)
{
    $value *= $subjectNumber;
    $value %= 20201227;
    return $value;
}
