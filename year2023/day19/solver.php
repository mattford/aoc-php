<?php
$lines = file('input.txt', FILE_IGNORE_NEW_LINES);

$parts = $workflows = [];
$isPart = false;
foreach ($lines as $line) {
    if ($line === '') {
        $isPart = true;
        continue;
    }
    if (!$isPart) {
        // px{a<2006:qkq,m>2090:A,rfg}
        preg_match('/([a-z]+){(.*)}/', $line, $matches);
        [,$name, $rules] = $matches;
        $rules = array_map(function ($r) {
            if (preg_match('/([a-z]+)(<|>)([0-9]+):([A-Za-z]+)/', $r, $matches)) {
                return array_slice($matches, 1);
            }
            return [$r];
        }, explode(',', $rules));
        $workflows[$name] = $rules;
    } else {
//        {x=787,m=2655,a=1222,s=2876}
        preg_match_all('/[0-9]+/', $line, $matches);
        $parts[] = $matches[0];
    }
}

$part1 = array_sum(array_map(fn($p) => doWorkflow('in', $p), $parts));

echo "Part 1: $part1\n";

$part2 = array_sum(array_map('reduceRoute', findRanges($workflows)));

echo "Part 2: $part2\n";

function reduceRoute($route)
{
    $mins = ['x' => 1, 'm' => 1, 'a' => 1, 's' => 1];
    $maxs = ['x' => 4000, 'm' => 4000, 'a' => 4000, 's' => 4000];

    foreach ($route as [$v, $o, $c]) {
        switch ($o) {
            case '>':
                $mins[$v] = max($mins[$v], $c+1);
                break;
            case '<':
                $maxs[$v] = min($maxs[$v], $c-1);
                break;
            case '<=':
                $maxs[$v] = min($maxs[$v], $c);
                break;
            case '>=':
                $mins[$v] = max($mins[$v], $c);
                break;
            default:
                die('Dodgy op');
        }
    }
    $combos = 1;
    foreach (['x', 'm','a','s'] as $v) {
        $combos *= $maxs[$v] - $mins[$v] + 1;
    }
    return $combos;
}

function findRanges($workflows, $target = 'A'): array
{
    global $cache;
    if (isset($cache[$target])) {
        return $cache[$target];
    }
    $routes = [];
    foreach ($workflows as $name => $rules) {
        $prereq = [];
        foreach ($rules as $rule) {
            if (
                (count($rule) === 1 && $rule[0] === $target) ||
                (count($rule) === 4 && $rule[3] === $target)
            ) {
                $myPrereq = $prereq;
                if ((count($rule) === 4 && $rule[3] === $target)) {
                    $myPrereq[] = array_slice($rule, 0, 3);
                }
                if ($name !== 'in') {
                    $myPrereq = array_merge($myPrereq, findRanges($workflows, $name)[0]);
                }
                $routes[] = $myPrereq;
            }
            if (count($rule) > 1) {
                $prereq[] = reverseRule($rule);
            }
        }
    }
    $cache[$target] = $routes;
    return $routes;
}

function reverseRule($rule) {
    [$v, $o, $c] = $rule;
    $op = [
        '>' => '<=',
        '<' => '>=',
    ][$o];
    return [$v, $op, $c];
}

function doWorkflow($workflowId, $part): int
{
    global $workflows;
    [$x, $m, $a, $s] = $part;
    $workflow = $workflows[$workflowId];
    $outcome = null;
    foreach ($workflow as $rule) {
        if (count($rule) === 1) {
            $outcome = $rule[0];
            break;
        } else {
            [$v, $o, $c, $r] = $rule;

            $res = eval("return $$v $o $c;");
            if ($res) {
                $outcome = $r;
                break;
            }
        }
    }
//    echo "{x=$x, m=$m, a=$a, s=$s} + $workflowId = $outcome\n";
    if ($outcome === 'A') {
        return array_sum([$x, $m, $a, $s]);
    }
    if ($outcome === 'R') {
        return 0;
    }
    return doWorkflow($outcome, $part);
}