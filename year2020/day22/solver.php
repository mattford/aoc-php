<?php
$lines = file('input.txt', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);

$players = $player = [];
foreach ($lines as $line) {
    if (str_contains($line, 'Player')) {
        if (!empty($player)) {
            $players[] = $player;
        }
        $player = [];
        continue;
    }
    $player[] = (int)$line;
}
$players[] = $player;

$game = new Combat($players[0], $players[1]);

$winner = $game->play();

echo "Player " . $winner . " wins!\n";
echo "Score: " . $game->getScore($winner) . PHP_EOL;


$game = new Combat($players[0], $players[1], true);

$winner = $game->play();

echo "Player " . $winner . " wins!\n";
echo "Score: " . $game->getScore($winner) . PHP_EOL;

class Combat
{
    private array $seen = [];
    private int $rounds = 0;
    public function __construct(private array $player1, private array $player2, private bool $recurse = false)
    {

    }

    public function getScore(int $player): int
    {
        $deck = $this->{"player$player"};
        $count = count($deck);
        $sum = 0;
        foreach ($deck as $card) {
            $sum += ($card * $count);
            $count--;
        }
        return $sum;
    }

    public function play(): int
    {
        while (!$this->hasWinner()) {
            $this->doRound();
        }
        if (empty($this->player1)) {
            return 2;
        }
        return 1;
    }

    private function doRound(): void
    {
        $this->rounds++;
//        echo "Round {$this->rounds}\n";
        $hash = $this->hashGame();
//        echo "Player 1: " . implode(', ', $this->player1) . PHP_EOL;
//        echo "Player 2: " . implode(', ', $this->player2) . PHP_EOL;
        $player1 = array_shift($this->player1);
        $player2 = array_shift($this->player2);
//        echo "Player 1 plays $player1\n";
//        echo "Player 2 plays $player2\n";
        if ($this->recurse) {
            if (in_array($hash, $this->seen)) {
                // hack to make player 1 win
                $this->player2 = [];
                return;
            }
            $this->seen[] = $hash;
        }
        if ($this->canRecurse($player1, $player2)) {
            // Start a new game to determine the winner
            $game = new Combat(
                array_slice($this->player1, 0, $player1),
                array_slice($this->player2, 0, $player2),
                true
            );
            $winner = $game->play();
        } elseif ($player1 > $player2) {
            $winner = 1;
        } else {
            $winner = 2;
        }
        if ($winner === 1) {
            $this->player1[] = $player1;
            $this->player1[] = $player2;
//            echo "Player 1 wins the round!\n";
        } else {
            $this->player2[] = $player2;
            $this->player2[] = $player1;
//            echo "Player 2 wins the round!\n";
        }
//        echo PHP_EOL;
    }

    public function hasWinner(): bool
    {
        return empty($this->player1) || empty($this->player2);
    }

    private function canRecurse(int $player1Card, int $player2Card): bool
    {
        return $this->recurse &&
            count($this->player1) >= $player1Card &&
            count($this->player2) >= $player2Card;
    }

    private function hashGame(): string
    {
        return md5(sprintf('%s/%s', implode(',',$this->player1), implode(',', $this->player2)));
    }
}