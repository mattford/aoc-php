<?php

$cards = array_map(function (string $line): array {
    preg_match('/Card +(\d+): (.*)/', $line, $matches);

    [,$id, $lists] = $matches;
    [$winning, $numbers] = array_map(
        fn($v) => array_map(
            fn($i) => (int)$i, preg_split('/\s+/', trim($v))
        ),
        explode(' | ', $lists)
    );
    $wins = count(array_intersect($winning, $numbers));
    $count = 1;
    return compact('id', 'wins', 'count');
}, file('input.txt', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES));

$part1 = $part2 = 0;
for ($i = 0; $i < count($cards); $i++) {
    ['wins' => $wins, 'count' => $count] = $cards[$i];
    $part2 += $count;
    if ($wins > 0) {
        $part1 += pow(2, $wins - 1);
        for ($j = $i + 1; $j <= $i + $wins; $j++) {
            $cards[$j]['count'] += $count;
        }
    }
}

echo "Part 1: $part1\n";
echo "Part 2: $part2\n";