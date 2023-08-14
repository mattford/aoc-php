<?php
$lines = array_map(function ($line) {
    preg_match('/([a-z ]+) \(contains ([a-zA-Z, ]*)\)/', $line, $matches);
    [,$ingredients, $allergens] = $matches;
    $ingredients = explode(' ', $ingredients);
    $allergens = array_map('trim', explode(', ', $allergens));
    return [$ingredients, $allergens];
}, file('input.txt', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES));

$allIngredients = [];

$allergenMap = [];

foreach ($lines as [$ingredients, $allergens]) {
    $counts = array_count_values($ingredients);
    foreach ($counts as $i => $c) {
        $allIngredients[$i] = ($allIngredients[$i] ?? 0) + $c;
    }
    foreach ($allergens as $allergen) {
        if (!isset($allergenMap[$allergen])) {
            $allergenMap[$allergen] = $ingredients;
        } else {
            $allergenMap[$allergen] = array_values(array_intersect($allergenMap[$allergen], $ingredients));
        }
    }
}

$allergicIngredients = [];
foreach ($allergenMap as $ingredients) {
    $allergicIngredients = array_merge($allergicIngredients, $ingredients);
}
$allergicIngredients = array_values(array_unique($allergicIngredients));

$safeIngredients = array_diff(array_keys($allIngredients), $allergicIngredients);

$part1 = 0;
foreach ($safeIngredients as $safeIngredient) {
    $part1 += $allIngredients[$safeIngredient];
}
echo $part1 . PHP_EOL;

$canonicalAllergenMap = [];
while (!empty($allergenMap)) {
    foreach ($allergenMap as $allergen => &$possibleIngredients) {
        $possibleIngredients = array_values(
            array_filter(
                $possibleIngredients,
                fn($ingredient) => empty($canonicalAllergenMap[$ingredient])
            )
        );
        if (count($possibleIngredients) === 1) {
            $canonicalAllergenMap[$possibleIngredients[0]] = $allergen;
            unset($allergenMap[$allergen]);
        }
    }
}

asort($canonicalAllergenMap);
$dangerous = array_keys($canonicalAllergenMap);
echo implode(',', $dangerous) . PHP_EOL;
