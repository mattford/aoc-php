<?php
$lines = file('input.txt', FILE_SKIP_EMPTY_LINES|FILE_IGNORE_NEW_LINES);
$strings = explode(',', $lines[0]);

$part1 = 0;
$boxes = [];
foreach ($strings as $string) {
    $hash = reindeerHash($string);
    $part1 += $hash;
    preg_match('/([a-z]+)(-|=)([0-9]+)?/', $string, $matches);
    [,$label, $op] = $matches;
    $hash = reindeerHash($label);
    $currentLenses = $boxes[$hash] ?? [];
    if ($op === '-') {
        $currentLenses = array_values(array_filter($currentLenses, fn($lens) => $lens['label'] !== $label));
    } elseif ($op === '=') {
        $focalLength = $matches[3];
        $found = false;
        foreach ($currentLenses as $i => $lens) {
            if ($lens['label'] === $label) {
                $currentLenses[$i]['focalLength'] = $focalLength;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $currentLenses[] = ['label' => $label, 'focalLength' => $focalLength];
        }
    }
    $boxes[$hash] = $currentLenses;
}

$part2 = 0;
foreach ($boxes as $i => $box) {
    $boxNumber = $i + 1;
    foreach ($box as $j => $lens) {
        $lensNumber = $j + 1;
        $part2 += $boxNumber * $lensNumber * $lens['focalLength'];
    }
}

echo "Part 1: $part1\n";
echo "Part 2: $part2\n";

function reindeerHash(string $string): int
{
    $current = 0;
    foreach (str_split($string) as $char) {
        $code = ord($char);
        $current = (($current + $code) * 17) % 256;
    }
    return $current;
}