<?php
$lines = file('input.txt', FILE_IGNORE_NEW_LINES);
$lines[] = '';
$patterns = $pattern = [];
foreach ($lines as $line) {
    if (empty($line)) {
        $patterns[] = $pattern;
        $pattern = [];
        continue;
    }
    $pattern[] = str_split($line);
}

$part1 = $part2 = 0;
foreach ($patterns as $i => $pattern) {
    $p1Found = $p2Found = false;
    for ($y = 1; $y < count($pattern); $y++) {
        $maxLength = min($y, count($pattern) - $y);
        $top = array_slice($pattern, $y - $maxLength, $maxLength);
        $bottom = array_reverse(array_slice($pattern, $y, $maxLength));
        $diff = diff($top, $bottom);
        if ($diff === 0 && !$p1Found) {
            echo "Pattern $i reflects at horizontal $y\n";
            $part1 += 100 * $y;
            $p1Found = true;
        }
        if ($diff === 1 && !$p2Found) {
            $part2 += 100 * $y;
            $p2Found = true;
        }
        if ($p1Found && $p2Found) {
            continue 2;
        }
    }
    // rotate right
    $rotated = array_map(fn($i) => array_reverse(array_column($pattern, $i)), range(0, count($pattern[0]) - 1));

    for ($y = 1; $y < count($rotated); $y++) {
        $maxLength = min($y, count($rotated) - $y);
        $top = array_slice($rotated, $y - $maxLength, $maxLength);
        $bottom = array_reverse(array_slice($rotated, $y, $maxLength));
        $diff = diff($top, $bottom);
        if ($diff === 0 && !$p1Found) {
            echo "Pattern $i reflects at vertical $y\n";
            $part1 += $y;
        }
        if ($diff === 1 && !$p2Found) {
            $part2 += $y;
        }
        if ($p1Found && $p2Found) {
            continue 2;
        }
    }
}

echo "Part 1: $part1\n";
echo "Part 2: $part2\n";

function diff($a, $b): int
{
    $diff = 0;
    for($y = 0; $y < count($a); $y++) {
        for ($x = 0; $x < count($a[$y]); $x++) {
            if (($a[$y][$x] ?? null) !== ($b[$y][$x]?? null)) {
                $diff++;
            }
        }
    }
    return $diff;
}