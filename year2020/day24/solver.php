<?php

$routes = array_map(function ($line) {
    preg_match_all('/ne|nw|se|sw|e|w/', $line, $matches);
    return $matches[0] ?? [];
}, file('input.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));

$blackSquares = [];

foreach ($routes as $route) {
    $y = $x = 0;
    foreach ($route as $move) {
        switch ($move) {
            case 'se':
                $y += 1;
                $x += 0.5;
                break;
            case 'sw':
                $y += 1;
                $x -= 0.5;
                break;
            case 'nw':
                $y -= 1;
                $x -= 0.5;
                break;
            case 'ne':
                $y -= 1;
                $x += 0.5;
                break;
            case 'e':
                $x += 1;
                break;
            case 'w':
                $x -= 1;
                break;
        }
    }
    $str = "$y,$x";
    if (in_array($str, $blackSquares)) {
        $blackSquares = array_values(array_filter($blackSquares, fn($v) => $v !== $str));
    } else {
        $blackSquares[] = $str;
    }
}

echo "Part 1: " . count($blackSquares) . PHP_EOL;

// Now simulate 100 days
$days = 100;
for ($i = 0; $i < $days; $i++) {
    $blackNeighbours = [];
    $newBlackSquares = [];
    foreach ($blackSquares as $coord) {
        [$y, $x] = explode(',', $coord);
        $neighbours = getNeighbours($y, $x);
        $adj = 0;
        foreach ($neighbours as [$oY, $oX]) {
            $pos = "$oY,$oX";
            $blackNeighbours[$pos] = ($blackNeighbours[$pos] ?? 0) + 1;
            if (in_array($pos, $blackSquares)) {
                $adj++;
            }
        }
        if ($adj !== 0 && $adj <= 2) {
            $newBlackSquares[] = $coord;
        }
    }
    foreach ($blackNeighbours as $coord => $count) {
        if ($count === 2) {
            $newBlackSquares[] = $coord;
        }
    }
    $blackSquares = array_values(array_unique($newBlackSquares));
}


echo "Part 2: " . count($blackSquares) . PHP_EOL;

function getNeighbours($y, $x): array {
    return [
        [$y - 1, $x - 0.5],
        [$y - 1, $x + 0.5],
        [$y, $x - 1],
        [$y, $x + 1],
        [$y + 1, $x - 0.5],
        [$y + 1, $x + 0.5],
    ];
}