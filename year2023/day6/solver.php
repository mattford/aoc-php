<?php

$lines = array_map(function ($line) {
    return array_slice(preg_split('/\s+/', $line), 1);
}, file('input.txt', FILE_SKIP_EMPTY_LINES|FILE_IGNORE_NEW_LINES));
$races = [];
for ($i = 0; $i < count($lines[0]); $i++) {
    $races[] = [(int)$lines[0][$i], (int)$lines[1][$i]];
}

$part1 = array_product(array_map(fn($r) => getWays(...$r), $races));
echo "Part 1: $part1\n";

$t = (int)implode('', array_column($races, 0));
$d = (int)implode('', array_column($races, 1));

$part2 = getWays($t, $d);
echo "Part 2: $part2\n";

function getWays(int $t, int $d): int
{
    $min = ceil(($d+1)/$t);
    $max = $t - 1;
    $ways = 0;
    for ($i = $min; $i <= $max; $i++) {
        $nd = ($t - $i) * $i;
        if ($nd > $d) {
            $ways++;
        }
    }
    return $ways;
}