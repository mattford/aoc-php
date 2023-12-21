<?php
$lines = file('input.txt', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
$modules = [];
foreach ($lines as $line) {
    preg_match('/(%|&)?([a-z]+) -> ([a-z, ]+)/', $line, $matches);
    [,$type, $name, $outputs] = $matches;
    $outputs = array_map('trim', explode(',', $outputs));
    $module = $modules[$name] ?? [
        'inputs' => [],
        'inputValues' => [],
        'currentState' => false,
        'last' => null,
    ];
    $module = array_merge($module, [
        'type' => $type,
        'outputs' => $outputs,
    ]);
    foreach ($outputs as $output) {
        $otherModule = $modules[$output] ?? [
            'type' => null,
            'inputs' => [],
            'outputs' => [],
            'currentState' => false,
            'last' => null,
        ];
        $otherModule['inputs'][] = $name;
        $modules[$output] = $otherModule;
    }
    $modules[$name] = $module;
//    broadcaster -> a, b, c
}

$cycles = array_combine($modules['lx']['inputs'], [0, 0, 0, 0]);
$found = [];
$low = $high = 0;
$part2 = 0;
for ($i = 0;;$i++) {
    $queue = [['broadcaster', null, false]];
    [$l, $h] = simulate($i);
    if ($i < 1000) {
        $low += $l;
        $high += $h;
    }
    if (count($cycles) === count($found)) {
        $found = array_filter($found, fn($v) => $v > 0);
        $part2 = array_shift($found);
        foreach ($found as $n) {
            $part2 = lcm($part2, $n);
        }
        break;
    }
}



echo "Low: $low, High: $high\n";

$part1 = $low * $high;
echo "Part 1: $part1\n";
echo "Part 2: $part2\n";

function simulate($i)
{
    global $modules, $queue, $cycles, $found;
    $pulses = [0, 0];

    while (!empty($queue)) {
        [$dest, $source, $pulse] = array_shift($queue);
        $pulses[$pulse ? 1 : 0]++;
        $module = &$modules[$dest];
        $module['last'] = $pulse;
        if (!empty($source)) {
            $module['inputValues'][$source] = $pulse;
        }
        if (!$pulse) {
            if (isset($cycles[$dest])) {
                if ($cycles[$dest] > 0 && !isset($found[$dest])) {
                    $found[$dest] = $i - $cycles[$dest];
                    echo "$dest cycle at $i of length {$found[$dest]}\n";
                } else {
                    $cycles[$dest] = $i;
                }
            }
        }
        $sendPulse = true;
        switch ($module['type']) {
            case '%':
                if (!$module['last']) {
                    $module['currentState'] = !$module['currentState'];
                } else {
                    $sendPulse = false;
                }
                break;
            case '&':
                $module['currentState'] = false;
                foreach ($module['inputs'] as $in) {
                    if (empty($module['inputValues'][$in])) {
                        $module['currentState'] = true;
                        break;
                    }
                }
                break;
            default:
                $module['currentState'] = $module['last'];
                break;
        }
        if ($sendPulse) {
            foreach ($module['outputs'] as $out) {
                $queue[] = [$out, $dest, $module['currentState']];
            }
        }
    }
    return $pulses;
}

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