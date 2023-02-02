<?php
require_once('../IntcodeComputer.php');
$input = file('input.txt', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES)[0];
$input = explode(',', $input);
$input = array_map('intval', $input);

echo "Part A: " . partA($input) . PHP_EOL;
echo "Part B: " . partB($input) . PHP_EOL;

function checkLocation(IntcodeComputer $computer, int $y, int $x): int {
    $computer->reset();
    $computer->run();
    $computer->input($x);
    $computer->input($y);
    return $computer->consumeOutput();
}
function partA($input) {
    $computer = new IntcodeComputer($input);

    $total = 0;
    for ($y = 0; $y < 50; $y++) {
        for ($x = 0; $x < 50; $x++) {
            $total += checkLocation($computer, $y, $x);
        }
    }
    return $total;
}

function partB($input) {
    $computer = new IntcodeComputer($input);

    $x = $y = 0;
    while (!checkLocation($computer, $y, $x+99)) {
        $y += 1;
        while (!checkLocation($computer, $y+99, $x)) {
            $x += 1;
        }
    }
    return $x*10000 + $y;
}



