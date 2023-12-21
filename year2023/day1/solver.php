<?php

function getSum(array $lines, array $replaceMap = []): int
{
    return array_sum(array_map(function ($line) use ($replaceMap) {
        $pattern = '/\d/';
        if (!empty($replaceMap)) {
            $searches = implode('|', array_keys($replaceMap));
            $pattern = "/(?=(\d|$searches))/";
        }

        preg_match_all($pattern, $line, $matches);

        $matches = array_map(function ($match) use ($replaceMap) {
            return $replaceMap[$match] ?? $match;
        }, $matches[1] ?? $matches[0]);
        return $matches[0] . end($matches);
    }, $lines));
}

$lines = file('input.txt', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);

echo "Part 1: " . getSum($lines) . PHP_EOL;

$replaceMap = [
    'one' => 1,
    'two' => 2,
    'three' => 3,
    'four' => 4,
    'five' => 5,
    'six' => 6,
    'seven' => 7,
    'eight' => 8,
    'nine' => 9,
];

echo "Part 2: " . getSum($lines, $replaceMap) . PHP_EOL;