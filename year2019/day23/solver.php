<?php
require_once('../IntcodeComputer.php');
$input = file('input.txt', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES)[0];
$input = explode(',', $input);
$input = array_map('intval', $input);

$computers = [];
for ($i = 0; $i < 50; $i++) {
    $c = new IntcodeComputer($input);
    $c->run();
    $c->input($i);
    $computers[] = $c;
}

$queue = $nat = $natHistory = [];
while (true) {
    $allIdle = true;
    foreach ($computers as $i => $computer) {
        if (empty($queue[$i])) {
            $computer->input(-1);
        } else {
            $allIdle = false;
            while ($computer->wantsInput && !empty($queue[$i])) {
                [$x, $y] = array_shift($queue[$i]);
                $computer->input($x);
                $computer->input($y);
            }
        }

        $outs = [];
        while ($out = $computer->consumeOutput()) {
            $outs[] = $out;
        }
        foreach (array_chunk($outs, 3) as $p) {
            if ($p[0] === 255) {
                if (empty($nat)) {
                    echo "Part 1: " . $p[2] . PHP_EOL;
                }
                $nat = [$p[1], $p[2]];
                continue;
            }
            $queue[$p[0]][] = [$p[1], $p[2]];
        }
    }
    if ($allIdle && !empty($nat)) {
        $queue[0][] = $nat;
        if (in_array($nat[1], $natHistory)) {
            echo "Part 2: " . $nat[1] . PHP_EOL;
            break;
        }
        $natHistory[] = $nat[1];
    }
}