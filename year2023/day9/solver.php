<?php
$histories = array_map(fn($line) => explode(' ', $line), file('input.txt', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES));

$histories = array_map(fn($v) => extrapolateNext($v), $histories);

$part1 = array_sum(array_map('last', $histories));
$part2 = array_sum(array_column($histories, 0));

// 2038472403 too high
echo "Part 1: $part1\n";
echo "Part 2: $part2\n";

function extrapolateNext($history): ?array
{
    $diffs = [];
    $lastValue = $history[0];
    $allZeroes = true;
    foreach (array_slice($history, 1) as $value) {
        $x = $value - $lastValue;
        if ($x !== 0) {
            $allZeroes = false;
        }
        $diffs[] = $x;
        $lastValue = $value;
    }

    $incr = $incrFirst = 0;
    if (!$allZeroes) {
        $next = extrapolateNext($diffs);
        $incr = last($next);
        $incrFirst = $next[0];
    }

    $out = $history;
    $first = $history[0];
    $last = last($history);
    array_unshift($out, $first - $incrFirst);
    $out[] = $last + $incr;
    return $out;
}

function last($arr): int
{
    return $arr[count($arr)-1];
}