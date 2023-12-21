<?php
$grid = array_map('str_split', file('input.txt', FILE_SKIP_EMPTY_LINES|FILE_IGNORE_NEW_LINES));

const MOVES = [[0, -1], [1, 0], [0, 1], [-1, 0]];

$part1 = getMin($grid);
$part2 = getMin($grid, 4, 10);

echo "Part 1: $part1\n";
echo "Part 2: $part2\n";

function getMin($grid, $minSteps = 1, $maxSteps = 3): int
{
    $boundY = count($grid)-1;
    $boundX = count($grid[0])-1;
    $visited = [];
    $queue = new SplMinHeap();
    $queue->insert([0, [0, 0, -1, 0]]);
    $target = [count($grid)-1, count($grid[0])-1];
    $result = PHP_INT_MAX;
    while (!$queue->isEmpty()) {
        [$loss ,[$y, $x, $dir, $steps]] = $queue->extract();
        $k = "$y,$x,$dir,$steps";

        if (isset($visited[$k])) {
            continue;
        }
        $visited[$k] = $loss;

        if ([$y, $x] === $target) {
            return $loss;
        }

        foreach (MOVES as $newDirection => [$dy, $dx]) {
            [$ny, $nx] = [$y + $dy, $x + $dx];
            if ($ny < 0 || $nx < 0 || $ny > $boundY || $nx > $boundX || ($newDirection + 2) % 4 === $dir) {
                continue;
            }
            $newSteps = ($newDirection == $dir ? $steps + 1 : 1);
            if ($newSteps > $maxSteps || ($loss && $newDirection != $dir && $steps < $minSteps)) {
                continue;
            }

            $newLoss = $loss + (int)($grid[$ny][$nx]);
            $queue->insert([$newLoss, [$ny, $nx, $newDirection, $newSteps]]);
        }
    }
    return $result;
}

