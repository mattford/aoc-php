<?php
$input = file('input.txt', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
$input = array_map(fn($line) => str_split($line), $input);

echo "Part 1: " . part1($input) . PHP_EOL;
echo "Part 2: " . part2($input) . PHP_EOL;

function part1($input) {
    $portals = parseInput($input);
    var_dump($portals);
    return dikjstra('AA/out', 'ZZ/out', $portals, true);
}

function part2($input) {
    $portals = parseInput($input);
    return dikjstra('AA/out', 'ZZ/out', $portals);
}

function dikjstra(string $pos, string $target, array $nodes, $part1 = false) {
    $visited = [];
    $queue = [['cost' => 0, 'pos' => $pos, 'depth' => 0]];
    while (!empty($queue)) {
        $s = array_shift($queue);
        $hashPos = implode(',', [$s['pos'], $s['depth']]);

        if (in_array($hashPos, $visited)) {
            continue;
        }
        $visited[] = $hashPos;
        foreach ($nodes[$s['pos']] as $nextNode => [$cost, $depMod]) {
            if ($part1) {
                $depMod = 0;
            }
            if ($s['depth'] !== 0 && preg_match('/(AA|ZZ)./', $nextNode)) {
                continue;
            }
            if ($s['depth'] + $depMod < 0) {
                continue;
            }
            $hashOther = implode(',', [$nextNode, $s['depth'] + $depMod]);
            if (in_array($hashOther, $visited)) {
                continue;
            }
            if ($hashOther === "$target,0") {
                return $s['cost'] + $cost;
            }
            $queue[] = [
                'pos' => $nextNode,
                'depth' => $s['depth'] + $depMod,
                'cost' => $s['cost'] + $cost,
            ];
        }
        usort($queue, fn($a, $b) => $a['cost'] <=> $b['cost']);
    }
    return 0;
}

function dikjstraTo(array $pos, array $grid): array {
    $visited = $costs = [];
    $costs[implode(',', $pos)] = 0;
    $queue = [['cost' => 0, 'pos' => $pos]];
    while (!empty($queue)) {
        $s = array_shift($queue);
        $hashPos = implode(',', $s['pos']);
        if (in_array($hashPos, $visited)) {
            continue;
        }
        $visited[] = $hashPos;

        if (empty($costs[$hashPos]) || $costs[$hashPos] > $s['cost']) {
            $costs[$hashPos] = $s['cost'];
        }

        $nextPositions = getNext($grid, $s['pos']);
        foreach ($nextPositions as $nextPosition) {
            $hashOther = implode(',', $nextPosition);
            if (in_array($hashOther, $visited)) {
                continue;
            }
            if (empty($costs[$hashOther]) || $costs[$hashOther] > $s['cost'] + 1) {
                $costs[$hashOther] = $s['cost'] + 1;
            }
            $queue[] = [
                'pos' => $nextPosition,
                'cost' => $s['cost'] + 1,
            ];
        }
        usort($queue, fn($a, $b) => $a['cost'] <=> $b['cost']);
    }
    return $costs;
}

function getNext(array $grid, array $pos): array
{
    [$y, $x, $z] = $pos;
    $dirs = [
        [$y-1, $x, 0], // Up
        [$y+1, $x, 0], // Down
        [$y, $x-1, 0], // Left
        [$y, $x+1, 0], // Right
    ];
    $others = [];
    foreach ($dirs as $dir) {
        if (!empty($grid[implode(',', $dir)])) {
            $dir[2] = $z;
            $others[] = $dir;
        }
    }
    return $others;
}
function parseInput(array $rows) {
    $portals = $grid = [];
    foreach ($rows as $y => $row) {
        foreach ($row as $x => $col) {
            if ($col !== '.') {
                continue;
            }
            $grid["$y,$x,0"] = 1;
            $neighbours = [
                [1, 0], // down
                [-1, 0], // up
                [0, 1], // right
                [0, -1], // left
            ];
            foreach ($neighbours as [$tY, $tX]) {
                $nY = $y + $tY;
                $nX = $x + $tX;
                if (preg_match('/[A-Z]/', ($rows[$nY][$nX] ?? ''))) {
                    $portalName = $rows[$nY][$nX] . $rows[$nY + $tY][$nX + $tX];
                    if ($tX === -1 || $tY === -1) {
                        $portalName = strrev($portalName);
                    }
                    $isInner = isset($rows[$nY + $tY + $tY][$nX + $tX + $tX]);
                    if (empty($portals[$portalName])) {
                        $portals[$portalName] = [null, null];
                    }
                    $portals[$portalName][$isInner ? 0 : 1] = [$y, $x];
                }
            }
        }
    }
    $dists = [];
    foreach ($portals as $name => [$inner, $outer]) {
        if (!in_array($name, ['AA', 'ZZ'])) {
            $dists[$name . '/in'] = [
                $name . '/out' => [1, 1],
            ];
            $dists[$name . '/out'] = [
                $name . '/in' => [1, -1],
            ];
        }
        foreach (['in' => $inner, 'out' => $outer] as $k => $p) {
            if ($p === null) {
                continue;
            }
            $n = $name . "/" . $k;
            $p[2] = 0;
            $costs = dikjstraTo($p, $grid);
            foreach ($portals as $otherName => [$oi, $oo]) {
                if ($name === $otherName) {
                    continue;
                }
                foreach (['in' => $oi, 'out' => $oo] as $k2 => $op) {
                    if ($op === null) {
                        continue;
                    }
                    $on = $otherName . "/" . $k2;
                    $op[2] = 0;
                    $oh = implode(',', $op);
                    if (isset($costs[$oh])) {
                        $dists[$n][$on] = [$costs[$oh], 0];
                    }
                }
            }
        }
    }
    return $dists;
}