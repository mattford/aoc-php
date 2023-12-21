<?php
$hands = array_map(function ($line) {
    [$hand, $bid] = explode(' ', $line);
    $cards = str_split($hand);
    $score = getHandScore($cards);
    return [
        'hand' => $hand,
        'cards' => $cards,
        'score' => $score,
        'optimalScore' => getOptimalScore($cards, $score),
        'bid' => $bid,
    ];
}, file('input.txt', FILE_SKIP_EMPTY_LINES|FILE_IGNORE_NEW_LINES));
$cardStrength = ['A', 'K', 'Q', 'J', 'T', '9', '8', '7', '6', '5', '4', '3', '2'];
$handStrengthLabel = [
    7 => 'Five of a kind',
    6 => 'Four of a kind',
    5 => 'Full house',
    4 => 'Three of a kind',
    3 => 'Two pair',
    2 => 'One pair',
    1 => 'High card',
];
usort($hands, function ($a, $b) use ($cardStrength) {
   $scoreOrder = $a['score'] <=> $b['score'];
   if ($scoreOrder === 0) {
       for ($i = 0; $i <= 4; $i++) {
           $diff = array_search($b['cards'][$i], $cardStrength) <=> array_search($a['cards'][$i], $cardStrength);
           if ($diff !== 0) {
               return $diff;
           }
       }
   }
   return $scoreOrder;
});

$winnings = 0;
foreach ($hands as $idx => $hand) {
    $rank = $idx + 1;
    echo "Rank $rank: {$hand['hand']} ({$handStrengthLabel[$hand['score']]})\n";
    $winnings += ($hand['bid'] * ($idx+1));
}

echo "Part 1: $winnings\n";

$cardStrength = ['A', 'K', 'Q', 'T', '9', '8', '7', '6', '5', '4', '3', '2', 'J'];
usort($hands, function ($a, $b) use ($cardStrength) {
    $scoreOrder = $a['optimalScore'] <=> $b['optimalScore'];
    if ($scoreOrder === 0) {
        for ($i = 0; $i <= 4; $i++) {
            $diff = array_search($b['cards'][$i], $cardStrength) <=> array_search($a['cards'][$i], $cardStrength);
            if ($diff !== 0) {
                return $diff;
            }
        }
    }
    return $scoreOrder;
});

$winnings = 0;
foreach ($hands as $idx => $hand) {
    $rank = $idx + 1;
    echo "Rank $rank: {$hand['hand']} ({$handStrengthLabel[$hand['optimalScore']]})\n";
    $winnings += ($hand['bid'] * ($idx+1));
}

echo "Part 2: $winnings\n";

function getOptimalScore(array $cards, int $score): int
{
    if ($score === 7 || !in_array('J', $cards)) {
        return $score;
    }
    $freeDigits = array_count_values($cards)['J'];
    if ($score === 1) {
        return 2;
    }
    if ($score === 3) {
        return $score + $freeDigits + 1;
    }
    return min(7, $score + 2);
}
function getHandScore(array $cards): int
{
    $counts = array_count_values($cards);
    sort($counts);
    if ($counts === [5]) {
        return 7;
    }
    if ($counts === [1, 4]) {
        return 6;
    }
    if ($counts === [2, 3]) {
        return 5;
    }
    if ($counts === [1, 1, 3]) {
        return 4;
    }
    if ($counts === [1, 2, 2]) {
        return 3;
    }
    if ($counts === [1, 1, 1, 2]) {
        return 2;
    }
    return 1;
}