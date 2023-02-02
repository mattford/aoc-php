<?php
require_once('../IntcodeComputer.php');
$input = file('input.txt', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES)[0];
$input = explode(',', $input);
$input = array_map('intval', $input);

$computer = new IntcodeComputer($input, function ($out) {
    if ($out > 1000) {
        echo $out . PHP_EOL;
    }
    echo chr($out);
});
$computer->run();

$in = "NOT A J
NOT B T
OR T J
NOT C T
OR T J
AND D J
WALK
";
foreach (str_split($in) as $char) {
    $computer->input(ord($char));
}


// If D and (H or I)
$computer->reset();
$computer->run();
$in = "NOT A J
NOT B T
OR T J
NOT C T
OR T J
AND D J
NOT E T
NOT T T
OR H T
AND T J
RUN
";
foreach (str_split($in) as $char) {
    $computer->input(ord($char));
}