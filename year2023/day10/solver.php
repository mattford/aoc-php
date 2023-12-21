<?php
$lines = file('input.txt', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);

$grid = array_map('str_split', $lines);

$startPos = null;
foreach ($grid as $y => $line) {
    foreach ($line as $x => $cell) {
        if ($cell === 'S') {
            $startPos = [$y, $x];
            break 2;
        }
    }
}

if (!$startPos) {
    die('Start pos not found');
}

$current = $startPos;
$prev = null;
$hops = 0;
$loopPoints = [$current];
$intersections = 1;
do {
    $adjacent = getAdjacent($current, $grid, $prev);
    $prev = $current;
    $hops++;
    $current = $adjacent[0];
    $loopPoints[] = $current;
} while ($current !== $startPos);

$part1 = $hops / 2;

echo "Part 1: $part1\n";

$translate = [
    'F' => '╔',
    '-' => '═',
    'J' => '╝',
    '7' => '╗',
    '|' => '║',
    'L' => '╚',
];

$pointsInside = getAllPointsWithinLoop($grid, $loopPoints);
$part2 = count($pointsInside);

for ($y = 0; $y < count($grid); $y++) {
    for ($x = 0; $x < count($grid[0]); $x++) {
        if (in_array([$y, $x], $pointsInside)) {
            echo 'X';
        } elseif (in_array([$y, $x], $loopPoints)) {
            echo $translate[$grid[$y][$x]] ?? $grid[$y][$x] ?? ' ';
        } else {
            echo ' ';
        }

    }
    echo PHP_EOL;
}

echo "Part 2: $part2\n";


function getAllPointsWithinLoop($grid, $points)
{
    $maxX = count($grid[0])-1;
    $maxY = count($grid)-1;
    $pointsInLoop = [];
    $lastJunc = null;
    // get points left/right above/below
    for ($y = 0; $y <= $maxY; $y++) {
        $intersections = 0;
        for ($x = 0; $x <= $maxX; $x++) {
            if (!in_array([$y, $x], $points)) {
                if ($intersections % 2 !== 0) {
                    $pointsInLoop[] = [$y, $x];
                }
                continue;
            }
            if ($grid[$y][$x] === '|') {
                $intersections++;
            } elseif (in_array($grid[$y][$x], ['L', 'J', '7', 'F', 'S'])) {
                if (empty($lastJunc)) {
                    $lastJunc = $grid[$y][$x];
                    continue;
                }
                if (
                    ($lastJunc === 'L' && $grid[$y][$x] === '7') ||
                    ($lastJunc === 'F' && $grid[$y][$x] === 'J')
                ) {
                    $intersections++;
                }
                $lastJunc = null;
            }

        }
    }
    return $pointsInLoop;
}
function isInLoop($point, $points, $grid)
{
    $maxX = count($grid[0])-1;
    [$y, $x] = $point;
    $intersections = 0;
    $lastJunc = null;
    // get points left/right above/below
    for ($x2 = $x+1; $x2 <= $maxX; $x2++) {
        if (in_array([$y, $x2], $points)) {
            if ($grid[$y][$x2] === '|') {
                $intersections++;
            } elseif (in_array($grid[$y][$x2], ['L', 'J', '7', 'F'])) {
                if (empty($lastJunc)) {
                    $lastJunc = $grid[$y][$x2];
                    continue;
                }
                if (($lastJunc === 'L' && $grid[$y][$x2] === '7') || ($lastJunc === 'F' && $grid[$y][$x2] === 'J')) {
                    $intersections++;
                }
                $lastJunc = null;
            }
        }
    }
    return $intersections % 2 !== 0;
}

function getAdjacent($current, $grid, $exclude = null) {

    [$y, $x] = $current;
    $thisPipe = $grid[$y][$x];
    $poses = [
        'u' => [[$y - 1, $x], ['|', '7', 'F', 'S']],
        'd' => [[$y + 1, $x], ['|', 'L', 'J', 'S']],
        'l' => [[$y, $x - 1], ['-', 'L', 'F', 'S']],
        'r' => [[$y, $x + 1], ['-', 'J', '7', 'S']]
    ];
    $directions = [
        'S' => ['u', 'd', 'l', 'r'],
        '|' => ['u', 'd'],
        '-' => ['l', 'r'],
        'L' => ['u', 'r'],
        'J' => ['u', 'l'],
        '7' => ['l', 'd'],
        'F' => ['r', 'd'],
    ][$thisPipe];
    $adjacent = [];
    foreach ($directions as $d) {
        [[$y2, $x2], $allowed] = $poses[$d];
        if (isset($grid[$y2][$x2]) && in_array($grid[$y2][$x2], $allowed) && [$y2, $x2] !== $exclude) {
            $adjacent[] = [$y2, $x2];
        }
    }
    return $adjacent;

}