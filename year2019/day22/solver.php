<?php

$input = file('input.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

echo "Part 1: " . part1($input) . PHP_EOL;
echo "Part 2: " . part2($input, 119315717514047, 2020, 101741582076661) . PHP_EOL;
function part1(array $input)
{
    $cardCount = 10007;
    $deck = array_keys(array_fill(0, $cardCount, 0));
    foreach ($input as $instruction) {
        if ($instruction === 'deal into new stack') {
            $deck = array_reverse($deck);
//            echo "new stack " . implode(', ', $deck) . PHP_EOL;
        } elseif (preg_match('/cut ([-0-9]+)/', $instruction, $matches)) {
            if ($matches[1] > 0) {
                $cutCards = array_splice($deck, 0, $matches[1]);
                $deck = array_merge($deck, $cutCards);
            } else {
                $cutCards = array_splice($deck, $matches[1]);
                $deck = array_merge($cutCards, $deck);
            }
//            echo "cut " . implode(', ', $deck) . PHP_EOL;
        } elseif (preg_match('/deal with increment ([-0-9]+)/', $instruction, $matches)) {
            $newDeck = [];
            $offset = 0;
            foreach ($deck as $card) {
//                echo "offset: $offset = $card\n";

                $newDeck[$offset] = $card;
                $offset += (int)$matches[1];
                $offset %= count($deck);
                if ($offset < 0) {
                    $offset = count($deck) + $offset - 1;
                }
            }
            ksort($newDeck);
            $deck = $newDeck;
//            echo "increment "  . implode(', ', $deck) . PHP_EOL;
        }
    }
//    echo implode(", ", $deck) . PHP_EOL;
    return array_search(2019, $deck);
}

function part2(array $moves) {
  $times = 101741582076661;
  $deckSize = 119315717514047;
  $cardPosition = 2020;

  $incMultiplier = 1;
  $offsetDiff = 0;

  foreach ($moves as $instruction) {
      if ($instruction === 'deal into new stack') {
          $incMultiplier = -$incMultiplier % $deckSize;
          $offsetDiff = ($offsetDiff + $incMultiplier) % $deckSize;
      } elseif (preg_match('/cut ([-0-9]+)/', $instruction, $matches)) {
          $offsetDiff = ($offsetDiff + (int)$matches[1] * $incMultiplier) % $deckSize;
      } elseif (preg_match('/deal with increment ([-0-9]+)/', $instruction, $matches)) {
          $incMultiplier = ($incMultiplier * gmp_invert((int)$matches[1], $deckSize)) % $deckSize;
      }
  }

  $inc = gmp_powm($incMultiplier, $times, $deckSize);

  $offset =
        ($offsetDiff *
            (1 - $inc) *
            gmp_invert((1 - $incMultiplier) % $deckSize, $deckSize)) % $deckSize;

  return ($offset + $inc * $cardPosition) % $deckSize;
}

