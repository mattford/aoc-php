for ($y = $minY; $y <= $maxY; $y++) {
$crossed = 0;
for ($x = $minX; $x <= $maxX; $x++) {
if (isset($dugOut["$y,$x"])) {
$n = neighbours($dugOut, $y, $x);
if ($n === 'ud') {
$crossed++;
} elseif (!empty($expected) && $expected === $n) {
$crossed++;
$expected = null;
} else {
$expected = [
'dr' => 'ul',
'ur' => 'dl',
][$n] ?? $expected ?? null;
}
//            ESC[38;2;⟨r⟩;⟨g⟩;⟨b⟩m
$c = $dugOut["$y,$x"];
//            [$r, $g, $b] = array_map('hexdec', str_split(ltrim($c, '#'), 2));
//            print "\x1b[38;2;$r;$g;{$b}m";
//            switch ($n) {
//                case 'ud':
//                    echo '║';
//                    break;
//                case 'lr':
//                    echo '═';
//                    break;
//                case 'dr':
//                    echo '╔';
//                    break;
//                case 'ur':
//                    echo '╚';
//                    break;
//                case 'ul':
//                    echo '╝';
//                    break;
//                case 'dl':
//                    echo '╗';
//                    break;
//                default:
//                    echo 'X';
//            }
//            print "\x1b[38;2;255;255;255m";
} else {
if ($crossed % 2 !== 0) {
$filledIn[] = [$y,$x];
//                echo 'X';
} else {
//                echo ' ';
}
}
}
echo PHP_EOL;
}