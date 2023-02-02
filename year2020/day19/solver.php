<?php
$lines = file('input.txt', FILE_IGNORE_NEW_LINES);

echo "Part 1: " . PartA($lines) . PHP_EOL;
echo "Part 2: " . PartB($lines) . PHP_EOL;

function PartA(array $lines) {
    [$rules, $messages] = buildRules($lines);
    $expr = "/^" . buildRegex(0, $rules) . "$/";
	$total = 0;
	foreach ($messages as $message) {
        if (preg_match($expr, $message)) {
            $total++;
		}
    }
	return $total;
}

function PartB(array $lines) {
    [$rules, $messages] = buildRules($lines);
    $rule42 = buildRegex(42, $rules);
    $rule31 = buildRegex(31, $rules);

    $rulesToMatch = ['/^'.$rule42.'{2}'.$rule31.'$/'];
    for ($i = 1; $i < 10; $i++) {
        $rulesToMatch[] = '/^'.$rule42.'+'.$rule42.'{'.$i.'}'.$rule31.'{'.$i.'}$/';
    }
    $x = 0;
    foreach ($messages as $m) {
        foreach ($rulesToMatch as $rule) {
            if (preg_match($rule, $m)) {
                $x++;
                continue 2;
            }
        }
    }

    return $x;
}

function buildRegex(int $idx, array $rules): string
{
    $rule = $rules[$idx];
	$groups = [];
	foreach ($rule as $id => $group) {
        foreach ($group as $ruleIdx) {
            if (!is_numeric($ruleIdx)) {
                return $ruleIdx;
            }
            if (empty($groups[$id])) {
                $groups[$id] = [];
            }
            if ($ruleIdx != $idx) {
                $groups[$id][] = buildRegex($ruleIdx, $rules);
            }
		}
	}
	$possibles = [];
	foreach ($groups as $group) {
        $thisOne = implode('', $group);
		$possibles[] = $thisOne;
	}
	return "(" . implode("|", $possibles) . ")";
}

function buildRules(array $lines): array {
    $rules = $messages = [];
	foreach ($lines as $i => $line) {
        if ($line == "") {
            $messages = array_slice($lines, $i+1);
			break;
		}
        $parts = explode(':', $line);
		$idx = (int)$parts[0];
		$subLists = explode('|', trim($parts[1]));
		$thisRule = [];
		foreach ($subLists as $subList) {
            $expr = "\"([a-z])\"";
			preg_match($expr, $subList, $matches);
			if (count($matches) > 0) {
                $thisRule[] = [$matches[1]];
			} else {
				$thisRule[] = explode(' ', trim($subList));
			}
		}
		$rules[$idx] = $thisRule;
	}
	return [$rules, $messages];
}