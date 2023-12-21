<?php

//Game 1: 3 blue, 4 red; 1 red, 2 green, 6 blue; 2 green

$max = [
    'red' => 12,
    'green' => 13,
    'blue' => 14,
];

$games = array_map(function (string $line): array {
    preg_match('/Game (\d+): (.*)/', $line, $matches);
    return [
        'id' => (int)$matches[1],
        'rounds' => array_map(function (string $round): array {
            preg_match_all('/(\d+) ([a-z]+)/', $round, $matches);
            return array_combine($matches[2], array_map(fn($v) => (int)$v, $matches[1]));
        }, explode('; ', $matches[2]))
    ];
}, file('input.txt', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES));

$valid = [];

foreach ($games as $game) {
    foreach ($game['rounds'] as $round) {
        foreach ($round as $color => $count) {
            if ($count > $max[$color]) {
                continue 3;
            }
        }
    }
    $valid[] = $game['id'];
}

$part1Result = array_sum($valid);

echo "Part 1: $part1Result\n";

$part2Result = array_sum(
    array_map(function (array $game): int {
        $mins = [
            'red' => 0,
            'green' => 0,
            'blue' => 0,
        ];
        foreach ($game['rounds'] as $round) {
            foreach ($round as $color => $count) {
                $mins[$color] = max($mins[$color], $count);
            }
        }
        return array_product(array_values($mins));
    }, $games)
);

echo "Part 2: $part2Result\n";
