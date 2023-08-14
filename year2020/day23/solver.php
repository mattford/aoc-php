<?php
$cups = array_map(fn($s) => (int)$s, str_split(trim(file_get_contents('input.txt'))));
$min = min(...$cups);
$max = max(...$cups);
$extraCups = range($max + 1, 1000000);
$cups = array_merge($cups, $extraCups);
$max = max(...$cups);

$states = [];

$map = [];
$last = $first = null;
foreach ($cups as $cup) {
    $map[$cup] = new Item($cup);
    if ($last) {
        $map[$cup]->previous = $last;
        $last->next = $map[$cup];
    }
    if (!$first) {
        $first = $map[$cup];
    }
    $last = $map[$cup];
}
$first->previous = $last;
$last->next = $first;

$current = $first;

$moves = 10000000;
//$moves = 100;
for ($i = 0; $i < $moves; $i++) {
    $removed = $removedIds = [];
    $n = $current;
    for ($j = 0; $j < 3; $j++) {
        $n = $n->next;
        $removed[] = $n;
        $removedIds[] = $n->data;
    }
    $current->append($n->next);
    $newLabel = $current->data;
    do  {
        $newLabel--;
        if ($newLabel < $min) {
            $newLabel = $max;
        }
    } while (in_array($newLabel, $removedIds));
    $new = $map[$newLabel];
    $newAfter = $new->next;
    $new->append($removed[0]);
    $newAfter->prepend($removed[2]);
    $current = $current->next;
}

$n = $map[1]->next;
$n2 = $n->next;

//while ($n->data !== 1) {
//    echo $n->data;
//    $n = $n->next;
//}
//echo PHP_EOL;
echo $n->data . ' * ' . $n2->data . PHP_EOL;
echo ($n->data * $n2->data) . PHP_EOL;



class Item
{
    public $previous;
    public $next;
    public function __construct(public $data)
    {

    }

    public function append(Item $other): void
    {
        $this->next = $other;
        $other->previous = $this;
    }

    public function prepend(Item $other): void
    {
        $this->previous = $other;
        $other->next = $this;
    }
}


