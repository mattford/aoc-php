<?php
$input = file('input.txt', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
$bugs = [];
$height = count($input);
$width = strlen(trim($input[0]));
foreach ($input as $y => $line) {
    $chars = str_split($line);
    foreach ($chars as $x => $char) {
        if ($char === '#') {
            $bugs[] = "$y,$x,0";
        }
    }
}
$initBugs = $bugs;

$seen = [];
while (true) {
    $bugs = doRound($bugs, $height, $width);
    $h = hashBugs($bugs);
    if (in_array($h, $seen)) {
        echo getBiodiversity($bugs, $width) . PHP_EOL;
        break;
    }
    $seen[] = $h;
}

$bugs = $initBugs;
printBugs($bugs);
for ($i = 0; $i < 200; $i++) {
    $bugs = doRound($bugs, $height, $width, true);
}
printBugs($bugs);
echo count($bugs) . PHP_EOL;

function getBiodiversity(array $bugs, int $width): int {
    $bd = 0;
    foreach ($bugs as $coord) {
        [$y, $x] = explode(',', $coord);
        $p = ((int)$y * $width) + ((int)$x);
        $bd += pow(2, $p);
    }
    return $bd;
}

function hashBugs(array $bugs): string
{
    sort($bugs);
    return implode(';', $bugs);
}

function printBugs(array $bugs): void
{
    $zStart = $zEnd = 0;
    foreach ($bugs as $b) {
        [,,$z] = explode(',', $b);
        $zStart = min($zStart, $z);
        $zEnd = max($zEnd, $z);
    }
    for ($z = $zStart; $z <= $zEnd; $z++) {
        echo "Level $z\n";
        for ($y = 0; $y < 5; $y++) {
            for ($x = 0; $x < 5; $x++) {
                echo in_array("$y,$x,$z", $bugs) ? "#" : ".";
            }
            echo PHP_EOL;
        }
        echo PHP_EOL;
    }
}

function doRound(array $bugs, int $height, int $width, bool $part2 = false): array {
    $newBugs = [];
    $zStart = $zEnd = 0;
    if ($part2) {
        foreach ($bugs as $b) {
            [,,$z] = explode(',', $b);
            $zStart = min($zStart, $z);
            $zEnd = max($zEnd, $z);
        }
        $zStart--;
        $zEnd++;
    }
    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            if ($y === 2 && $x === 2) {
                continue;
            }
            for ($z = $zStart; $z <= $zEnd; $z++) {
                $neighbours = getNeighbours($y, $x, $z, $part2);
                $otherBugs = 0;
                foreach ($neighbours as $neighbour) {
                    if (in_array(implode(',', $neighbour), $bugs)) {
                        $otherBugs++;
                    }
                }
                $isBug = in_array("$y,$x,$z", $bugs);
                if ($isBug && $otherBugs === 1) {
                    $newBugs[] = "$y,$x,$z";
                } elseif (!$isBug && in_array($otherBugs, [1, 2])) {
                    $newBugs[] = "$y,$x,$z";
                }
            }
        }
    }
    return $newBugs;
}

function getNeighbours(int $y, int $x, int $z, bool $threeDims = false): array
{
    $ns = [
        [$y-1, $x, $z], // up
        [$y+1, $x, $z], // down
        [$y, $x-1, $z], // left
        [$y, $x+1, $z], // right
    ];
    $nearEdge = in_array([$y, $x], [[1, 1], [1, 3], [3, 1], [3, 3]]);
    if (!$threeDims || $nearEdge) {
        return $ns;
    }
    // inner ones
    if ($y === 1 && $x === 2) {
        $ns = [
            [$y - 1, $x, $z],
            [$y, $x-1, $z],
            [$y, $x+1, $z],
        ];
        foreach (range(0, 4) as $x2) {
            $ns[] = [0, $x2, $z+1];
        }
        return $ns;
    }
    if ($y === 3 && $x === 2) {
        $ns = [
            [$y + 1, $x, $z],
            [$y, $x-1, $z],
            [$y, $x+1, $z],
        ];
        foreach (range(0, 4) as $x2) {
            $ns[] = [4, $x2, $z+1];
        }
        return $ns;
    }
    if ($y === 2 && $x === 1) {
        $ns = [
            [$y + 1, $x, $z],
            [$y - 1, $x, $z],
            [$y, $x-1, $z],
        ];
        foreach (range(0, 4) as $x2) {
            $ns[] = [$x2, 0, $z+1];
        }
        return $ns;
    }
    if ($y === 2 && $x === 3) {
        $ns = [
            [$y + 1, $x, $z],
            [$y - 1, $x, $z],
            [$y, $x+1, $z],
        ];
        foreach (range(0, 4) as $x2) {
            $ns[] = [$x2, 4, $z+1];
        }
        return $ns;
    }
    if ($y === 0) {
        $ns[0] = [1, 2, $z-1];
    }
    if ($y === 4) {
        $ns[1] = [3, 2, $z-1];
    }
    if ($x === 0) {
        $ns[2] = [2, 1, $z-1];
    }
    if ($x === 4) {
        $ns[3] = [2, 3, $z-1];
    }

    return $ns;
}
