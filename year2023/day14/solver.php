<?php
$grid = array_map('str_split', file('input.txt', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES));

$gridp1 = moveNorth($grid);

$part1 = 0;
foreach (array_reverse($gridp1) as $y => $row) {
    $vals = array_count_values($row);
    $part1 += ($vals['O'] ?? 0) * ($y+1);
}

echo "Part 1: $part1\n";
//printGrid($grid);

$cycles = 1000000000;
$seenStates = [];
for ($i = 0; $i < $cycles; $i++) {
    $grid = moveNorth($grid);
    $grid = rotate($grid);

// west
    $grid = moveNorth($grid);
    $grid = rotate($grid);
// south
    $grid = moveNorth($grid);
    $grid = rotate($grid);

// east
    $grid = moveNorth($grid);
    $grid = rotate($grid);

    $stateHash = implode('/', array_map(fn($r) => implode('', $r), $grid));
    if (isset($seenStates[$stateHash])) {
        $loopLength = $i - $seenStates[$stateHash];
        $cycleNumber = $i + 1;
        $cycles = $cycleNumber + (($cycles - $cycleNumber) % $loopLength);
    }
    $seenStates[$stateHash] = $i;
//    printGrid($grid);
}

$part2 = 0;
foreach (array_reverse($grid) as $y => $row) {
    $vals = array_count_values($row);
    $part2 += ($vals['O'] ?? 0) * ($y+1);
}

echo "Part 2: $part2\n";


function moveNorth($grid)
{
    $lastBlocker = [/* $col => $rowWithBlocker*/];
    $outGrid = [];
    for ($y = 0; $y < count($grid); $y++) {
        for ($x = 0; $x < count($grid[$y]); $x++) {
            $v = $grid[$y][$x];
            if ($v === '#') {
                $lastBlocker[$x] = $y;
                $outGrid[$y][$x] = $v;
            } elseif ($v === 'O') {
                if (!isset($lastBlocker[$x])) {
                    $lastBlocker[$x] = -1;
                }
                $outGrid[$y][$x] = '.';
                $outGrid[$lastBlocker[$x]+1][$x] = $v;
                $lastBlocker[$x]++;
            } else {
                $outGrid[$y][$x] = $v;
            }
        }
    }
    return $outGrid;
}

function rotate($grid) {
    return array_map(fn($i) => array_reverse(array_column($grid, $i)), range(0, count($grid[0])-1));
}

function printGrid($grid) {
    echo '╔' . implode('', array_fill(0, count($grid[0]), '═')) . '╗' . PHP_EOL;
    foreach ($grid as $row) {
        echo '║';
        foreach ($row as $col) {
            echo $col;
        }
        echo '║' . PHP_EOL;
    }
    echo '╚' . implode('', array_fill(0, count($grid[0]), '═')) . '╝' . PHP_EOL;
    echo PHP_EOL;
}