<?php
$lines = file('input.txt', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);

$instr = str_split($lines[0]);
$nodes = [];
foreach (array_slice($lines, 1) as $line) {
    preg_match('/([A-Z0-9]+) = \(([A-Z0-9]+), ([A-Z0-9]+)\)/', $line, $matches);
    $nodes[$matches[1]] = [$matches[2], $matches[3]];
}

$part1 = processStates([['AAA', 0]], $nodes, $instr);

echo "Part 1: $part1\n";

$states = [];
foreach (array_keys($nodes) as $node) {
    if (str_ends_with($node, 'A')) {
        $states[] = [$node, 0];
    }
}

$part2 = processStates($states, $nodes, $instr, true);

echo "Part 2: $part2\n";


function processStates($states, $nodes, $instr, $part2 = false) {
    $idx = 0;
    $steps = 0;
    while (1) {
        $allIntervals = true;
        $dir = $instr[$idx];
        $steps++;
        foreach ($states as $sIdx => [$current]) {
            if (empty($nodes[$current])) {
                die();
            }
            $current = $nodes[$current][$dir === 'L' ? 0 : 1];
            $states[$sIdx][0] = $current;

            if (
                (
                    ($part2 && str_ends_with($current, 'Z')) ||
                    (!$part2 && $current === 'ZZZ')
                ) && empty($states[$sIdx][1])
            ) {
                $interval = $steps;
                $states[$sIdx][1] = $interval;
            }
            if (empty($states[$sIdx][1])) {
                $allIntervals = false;
            }
        }

        if ($allIntervals) {
            $intervals = array_column($states, 1);
            $running = 1;
            while (!empty($intervals)) {
                $running = lcm($running, array_shift($intervals));
            }
            return $running;
        }

        $idx = ($idx + 1) % count($instr);
    }
    return 0;
}
// 10,668,805,688,490 too high
function lcm($p, $q) {
    $gcf = gcf($p, $q);

    return ($p * $q) / $gcf;
}

function gcf ($num1, $num2) {
    $gcd = 1;
    for ($i=2; $i<=$num1 && $i<=$num2; $i++) {
        // Checks if i is factor of both integers
        if (($num1 % $i == 0) && ($num2 % $i == 0)) {
            $gcd = $i;
        }
    }
    return $gcd;
}