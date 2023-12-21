<?php
$lines = file('input.txt', FILE_SKIP_EMPTY_LINES|FILE_IGNORE_NEW_LINES);
$grid = array_map('str_split', $lines);

$joints = findExpansionJoints($grid);

$galaxies = [];
for ($y = 0; $y < count($grid); $y++) {
    for ($x = 0; $x < count($grid[$y]); $x++) {
        if ($grid[$y][$x] === '#') {
            $galaxies[] = [$y, $x];
        }
    }
}

$part1 = calculateDistance($galaxies, $joints, 2);
$part2 = calculateDistance($galaxies, $joints, 1000000);

echo "Part 1: $part1\n";
echo "Part 2: $part2\n";

function calculateDistance($galaxies, $joints, $expansionFactor): int
{
    $distances = [];
    while (!empty($galaxies)) {
        [$y, $x] = array_pop($galaxies);
        foreach ($galaxies as [$yo, $xo]) {
            $minY = min($y, $yo);
            $maxY = max($y, $yo);
            $minX = min($x, $xo);
            $maxX = max($x, $xo);
            $dist = abs($y - $yo) + abs($x - $xo);
            foreach ($joints as [$jy, $jx]) {
                if (
                    ($jy > 0 && $jy > $minY && $jy < $maxY) ||
                    ($jx > 0 && $jx > $minX && $jx < $maxX)
                ) {
                    $dist += $expansionFactor - 1;
                }
            }
            $distances[] = $dist;
        }
    }

    return array_sum($distances);
}

function findExpansionJoints(array $grid): array
{
    $out = [];
    foreach ($grid as $y => $row) {
        $vals = array_count_values($row);
        if (($vals['.'] ?? 0) === count($row)) {
            $out[] = [$y, 0];
        }
    }
    foreach ($grid[0] as $x => $cell) {
        $col = array_column($grid, $x);
        $vals = array_count_values($col);
        if (($vals['.'] ?? 0) === count($col)) {
            $out[] = [0, $x];
        }
    }
    return $out;
}