<?php
$grid = array_map('str_split', file('input.txt', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES));

$flips = [
    '/' => [
        'u' => 'r',
        'r' => 'u',
        'd' => 'l',
        'l' => 'd',
    ],
    '\\' => [
        'u' => 'l',
        'l' => 'u',
        'd' => 'r',
        'r' => 'd',
    ],
];
// if same state seen before, stop
// get unique cells (don't care about direction) visited

function followBeam($grid, $beam, &$seen = []): array
{
    global $flips;
    [[$y, $x], $dir] = $beam;

    while(isset($grid[$y][$x])) {
        $cellKey = "$y,$x";

        if (!array_key_exists($cellKey, $seen)) {
            $seen[$cellKey] = [];
        }
        if (in_array($dir, $seen[$cellKey])) {
            break;
        }
        $seen[$cellKey][] = $dir;
        $current = $grid[$y][$x];
        if (in_array($dir, ['l', 'r']) && $current === '|') {
            $seen = followBeam($grid, [[$y - 1, $x], 'u'], $seen);
            return followBeam($grid, [[$y + 1, $x], 'd'], $seen);
        } elseif (in_array($dir, ['u', 'd']) && $current === '-') {
            $seen = followBeam($grid, [[$y, $x-1], 'l'], $seen);
            return followBeam($grid, [[$y, $x+1], 'r'], $seen);
        } elseif (isset($flips[$current][$dir])) {
            $dir = $flips[$current][$dir];
        }
        switch ($dir) {
            case 'u':
                $y--;
                break;
            case 'd':
                $y++;
                break;
            case 'l':
                $x--;
                break;
            case 'r':
                $x++;
                break;
            default:
                die('Weird direction: ' . $dir);
        }
    }

    return $seen;
}


$part1 = count(followBeam($grid, [[0, 0], 'r']));

$max = $part1;
for ($y = 0; $y < count($grid); $y++) {
    for ($x = 1; $x < count($grid[$y]); $x++) {
        $possible = [];
        if ($y === 0) {
           $possible[] = 'd';
        }
        if ($x === 0) {
            $possible[] = 'r';
        }
        if ($y === count($grid)-1) {
            $possible[] = 'u';
        }
        if ($x === count($grid[$y])-1) {
            $possible[] = 'l';
        }
        foreach ($possible as $dir) {
            $result = count(followBeam($grid, [[$y, $x], $dir]));
            $max = max($max, $result);
        }
    }
}

echo "Part 1: $part1\n";
echo "Part 2: $max\n";