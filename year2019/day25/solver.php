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
while (true) {
    if ($computer->wantsInput) {
        $in = readline('Enter instruction: ');
        foreach (str_split($in) as $char) {
            $computer->input(ord($char));
        }
        $computer->input(10);
    }
}




