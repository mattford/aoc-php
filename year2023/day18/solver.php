<?php
$moves = array_map(function ($line) {
    // R 6 (#70c710)
    preg_match('/(R|L|U|D) ([0-9]+) \(#([a-f0-9]+)\)/', $line, $matches);
    return array_slice($matches, 1);
}, file('input.txt', FILE_SKIP_EMPTY_LINES|FILE_IGNORE_NEW_LINES));

$p2Moves = array_map(function ($move) {
    [,,$c] = $move;
    $steps = hexdec(substr($c, 0, 5));
    $dir = hexdec(substr($c, -1, 1));
    $newDir = [
        'R',
        'D',
        'L',
        'U',
    ][$dir];
    return [$newDir, $steps];
}, $moves);

function getSize($moves)
{
    $points = [[0, 0]];
    $totalSteps = 0;
    $pos = [0, 0];
    foreach ($moves as [$dir, $steps]) {
        [$y, $x] = $pos;
        [$ey, $ex] = [
            'U' => [$y - $steps, $x],
            'D' => [$y + $steps, $x],
            'L' => [$y, $x - $steps],
            'R' => [$y, $x + $steps],
        ][$dir];
        $pos = [$ey, $ex];
        $points[] = $pos;
        $totalSteps += $steps;
    }

    $a = 0;
    // overlapping groups of two
    for ($i = 0; $i < count($points); $i++) {
        [$y1, $x1] = $points[$i];
        [$y2, $x2] = $points[($i+1)%count($points)];
        $a += ($y1 * $x2) - ($y2 * $x1);
    }

    $interior = abs($a / 2);

    $a = $interior + ($totalSteps / 2) - 1;

    return $a + 2;
}

$part1 = getSize($moves);
$part2 = getSize($p2Moves);

echo "Part 1: $part1\n";
echo "Part 2: $part2\n";

