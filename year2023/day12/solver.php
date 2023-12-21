<?php
$lines = array_map(function(string $line): array {
    [$pattern, $groups] = explode(' ', $line);
    $groups = explode(',', $groups);
    $pattern = preg_replace('/\.{2,}/', '.', $pattern);
    $pattern = str_split($pattern);
    return compact('pattern', 'groups');
}, file('input.txt', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES));
// .??..??...?##. 1,1,3

$cache = [];
$part1 = $part2 = 0;
foreach ($lines as $line) {
    $solutions = num_solutions(array_merge($line['pattern'], ['.']), $line['groups'], 0, $cache);

    $part2pattern = str_split(implode('?', array_fill(0, 5, implode('', $line['pattern']))) . '.');
    $part2groups = array_merge(...array_fill(0, 5, $line['groups']));
    $part2solutions = num_solutions($part2pattern, $part2groups, 0, $cache);
    $part1 += $solutions;
    $part2 += $part2solutions;
}

echo "Part 1: $part1\n";
echo "Part 2: $part2\n";

function num_solutions($s, $groups, $num_done_in_group = 0, &$cache = [])
{
    $stateHash = implode('', $s) . '/' . implode(',', $groups) . '/' . $num_done_in_group;
    if (isset($cache[$stateHash])) {
        return $cache[$stateHash];
    }
    if (empty($s)) {
        return (int)(empty($groups) && empty($num_done_in_group));
    }
    $num_sols = 0;
    # If next letter is a "?", we branch
    $possible = [$s[0]];
    if ($s[0] === '?') {
        $possible = ['.', '#'];
    }
    foreach ($possible as $c) {
        if ($c == "#") {
            # Extend current group
            $num_sols += num_solutions(array_slice($s, 1), $groups, $num_done_in_group + 1, $cache);
        } elseif ($num_done_in_group > 0) {
            # If we were in a group that can be closed, close it
            if (!empty($groups) && $groups[0] == $num_done_in_group) {
                $num_sols += num_solutions(array_slice($s, 1), array_slice($groups, 1), 0, $cache);
            }
        } else {
            # If we are not in a group, move on to next symbol
            $num_sols += num_solutions(array_slice($s, 1), $groups, 0, $cache);
        }
    }
    $cache[$stateHash] = $num_sols;
    return $num_sols;
}