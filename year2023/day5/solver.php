<?php
$lines = file('input.txt', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);

$maps = [];
$seedList = [];

$map = [];
foreach ($lines as $line) {
    if (preg_match('/seeds: ([\d ]+)/', $line, $matches)) {
        [,$seedList] = $matches;
        $seedList = explode(' ', $seedList);
    } elseif (preg_match('/(.*) map:/', $line, $matches)) {
        if (!empty($map)) {
            $maps[] = $map;
            $map = [];
        }
    } else {
        [$destStart, $sourceStart, $length] = array_map('intval', explode(' ', $line));
        $map[] = [$destStart, $sourceStart, $length];
    }
}
if (!empty($map)) {
    $maps[] = $map;
}

$seeds = array_combine($seedList, array_map(fn($seed) => getSeedLocation($seed, $maps), $seedList));
$part1 = min(...$seeds);
echo "Part 1: $part1\n";

//$tests = [
//    [
//        [[1, 5]], [1, 2], [[3, 5]]
//    ],
//    [
//        [[1, 5]], [2, 6], [[1, 1]]
//    ]
//];
//foreach ($tests as [$in, $in2, $out]) {
//    $a = removeRange($in, $in2);
//    if ($a !== $out) {
//        echo "test failed\n";
//        var_dump($in, $in2, $a);
//    }
//}die;

$seedRanges = convertRanges(
    array_map(
        fn($v) => [(int)$v[0], $v[0] + $v[1]-1],
        array_chunk($seedList, 2)
    ),
    $maps
);

$part2 = PHP_INT_MAX;
foreach ($seedRanges as [$min, $max]) {
    $part2 = min($part2, $min, $max);
}

echo "Part 2: $part2\n";

function convertRange(array $range, array $map): array
{
    [$start, $end] = $range;
    $unmatchedRanges = [$range];
    $newRanges = [];
    foreach ($map as [$destStart, $sourceStart, $length]) {
        $sourceEnd = $sourceStart + $length;

        if ($start < $sourceEnd && $end >= $sourceStart) {
            // compute subrange
            $newStart = max($start, $sourceStart);
            $newEnd = min($end, $sourceEnd - 1);
            $unmatchedRanges = removeRange($unmatchedRanges, [$newStart, $newEnd]);
            $newRanges[] = [$destStart + ($newStart - $sourceStart), $destStart + ($newEnd - $sourceStart)];
        }
    }

    if (!empty($unmatchedRanges)) {
        // No mapping, so it maps to itself
        var_dump($unmatchedRanges);
        $newRanges = array_merge($newRanges, $unmatchedRanges);
    }

    if (empty($newRanges)) {
        $newRanges[] = $range;
    }

    return $newRanges;
}


// [[1, 5]], [2, 6]
function removeRange(array $ranges, array $range): array
{
    $new = [];
    [$rStart, $rEnd] = $range;
    foreach ($ranges as [$start, $end]) {
        $sDiff = $rStart - $start;
        if ($sDiff > 0) {
            $new[] = [max(1, $start), $rStart-1];
        }
        $eDiff = $end - $rEnd;
        if ($eDiff > 0) {
            $new[] = [max(1, $rEnd + 1), $end];
        }
        if ($sDiff < 0 && $eDiff < 0) {
            $new[] = [max(1, $start), $end];
        }
    }
    return $new;
}

function convertRanges(array $ranges, array $maps): array {

    foreach ($maps as $map) {
        $newRanges = [];
        foreach ($ranges as $range) {
            $convertedRanges = convertRange($range, $map);
            $newRanges = array_merge($newRanges, $convertedRanges);
        }
        $ranges = $newRanges;
    }

    return $ranges;
}

function getFromMap(array $map, int $input): int
{
    foreach ($map as [$destStart, $sourceStart, $length]) {
        if ($input >= $sourceStart && $input < $sourceStart + $length) {
            $offset = $input - $sourceStart;
            return $destStart + $offset;
        }
    }
    return $input;
}

function getSeedLocation(int $seed, array $maps): int {
    $value = $seed;
    foreach ($maps as $map) {
        $value = getFromMap($map, $value);
    }
    return $value;
}