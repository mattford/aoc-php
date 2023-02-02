<?php

function part1(array $lines) {
    $nodes = parseInput($lines);
    $cache = [];
    $keys = array_keys(array_filter($nodes, fn($k) => !is_numeric($k) && strtolower($k) === $k, ARRAY_FILTER_USE_KEY));
    return distanceToCollectKeys(["P0"], $keys, $nodes, $cache);
}

function part2(array $lines) {
    $nodes = parseInput($lines);
    $cache = [];
    $positions = ["P0", "P1", "P2", "P3"];
    $keys = array_keys(array_filter($nodes, fn($k) => !is_numeric($k) && strtolower($k) === $k, ARRAY_FILTER_USE_KEY));
    return distanceToCollectKeys($positions, $keys, $nodes, $cache);
}

function distanceToCollectKeys(array $sources, array $keys, array $nodes, array &$cache)
{
    if (empty($keys)) {
        return 0;
    }
    $stateHash = implode(',', $sources) . "/" . implode(",", $keys);
    if (isset($cache[$stateHash])) {
        return $cache[$stateHash];
    }
    $result = PHP_INT_MAX;
    $nextMoves = [];
    foreach ($sources as $key) {
        $nextMoves[$key] = array_filter(
            $nodes[$key]['dists'],
            fn($nextNode, $k) => in_array($k, $keys) && empty(array_intersect($nextNode[1], $keys)),
            ARRAY_FILTER_USE_BOTH
        );
    }

    if (empty($nextMoves)) {
        return 0;
    }
    foreach ($nextMoves as $source => $dests) {
        $dests = array_keys($dests);
        foreach ($dests as $dest) {
            $newPositions = $sources;
            $newPositions[array_search($source, $sources)] = $dest;
            $dist = distanceToCollectKeys(
                $newPositions,
                array_values(array_filter($keys, fn($k) => $k !== $dest)),
                $nodes,
                $cache
            );
            $d = $nodes[$source]['dists'][$dest][0] + $dist;
            $result = min($result, $d);
        }
    }
    $cache[$stateHash] = $result;
    return $result;
}

function dikjstraTo(array $points, array $nodes, array $state): array {
    $visited = $costs = $needs = [];
    $costs[implode(',', $state['pos'])] = 0;
    $queue = [$state];
    while (!empty($queue)) {
        $s = array_shift($queue);
        $hashPos = implode(',', $s['pos']);
        if (in_array($hashPos, $visited)) {
            continue;
        }
        $visited[] = $hashPos;

        if (empty($costs[$hashPos]) || $costs[$hashPos] > $s['cost']) {
            $costs[$hashPos] = $s['cost'];
            $needs[$hashPos] = $s['needs'] ?? [];
        }

        $nextPositions = getNext($points, $s['pos']);

        foreach ($nextPositions as $nextPosition) {
            $hashOther = implode(',', $nextPosition);
            if (in_array($hashOther, $visited)) {
                continue;
            }
            $thisNeeds = $s['needs'] ?? [];
            foreach ($nodes as $nK => $n) {
                if (implode(',', $n['pos']) === $hashOther && strtoupper($nK) === $nK && $nK !== "@") {
                    $thisNeeds[] = strtolower($nK);
                }
            }
            $queue[] = [
                'pos' => $nextPosition,
                'cost' => $s['cost'] + 1,
                'needs' => $thisNeeds,
            ];
        }
        usort($queue, fn($a, $b) => $a['cost'] <=> $b['cost']);
    }
    return [$costs, $needs];
}

function getNext(array $points, array $pos): array
{
    [$y, $x] = $pos;
    $dirs = [
        [$y-1, $x], // Up
        [$y+1, $x], // Down
        [$y, $x-1], // Left
        [$y, $x+1], // Right
    ];
    $others = [];
    foreach ($dirs as $dir) {
        if (!empty($points[implode(',', $dir)])) {
            $others[] = $dir;
        }
    }
    return $others;
}

function parseInput(array $lines): array
{
    $p = 0;
    $grid = $nodes = [];
    foreach ($lines as $y => $line) {
        $chars = str_split($line);
        foreach ($chars as $x => $char) {
            if ($char === "#") {
                continue;
            }
            $grid["$y,$x"] = 1;
            if ($char === '@') {
                $char = "P$p";
                $p++;
            }
            if ($char !== ".") {
                $nodes[$char] = ['pos' => [$y,$x], 'dists' => []];
            }
        }
    }
    foreach ($nodes as $k => ['pos' => $keyPos]) {
        [$dist, $needs] = dikjstraTo($grid, $nodes, [
            'cost' => 0,
            'pos' => $keyPos,
            'needs' => [],
        ]);
        foreach ($nodes as $k2 => $otherDoor) {
            if ($k === $k2) {
                continue;
            }
            $otherDoorHash = implode(',', $otherDoor['pos']);
            if (!empty($dist[$otherDoorHash])) {
                $nodes[$k]['dists'][$k2] = [
                    $dist[$otherDoorHash],
                    $needs[$otherDoorHash]
                ];
            }
        }
    }
    return $nodes;
}

$lines = file('input.txt', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
echo "Part 1: " . part1($lines) . PHP_EOL;
$lines = file('input2.txt', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
echo "Part 2: " . part2($lines) . PHP_EOL;
