<?php
$lines = file('input.txt', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);

// Assumptions: sqrt(x) is an int
solve($lines);

function solve(array $lines): void
{
    $tiles = getTiles($lines);
    $sideLength = sqrt(count($tiles));
    $images = getImage($tiles);

    echo PartA($images, $sideLength) . PHP_EOL;
    partB($images);
}

function partA(array $grids, int $sideLength) {

    if (empty($grids)) {
        echo "didn't work\n";
        die();
    }
    $resultTiles = [];
    foreach ($grids[0]['tiles'] as $t) {
        $resultTiles = array_merge($resultTiles, explode(',', $t));
    }

    $end = $sideLength * $sideLength;
    $keys = [0, $sideLength - 1, $end-1, $end - $sideLength];
    $sum = 1;
    foreach ($keys as $k) {
        $sum *= $resultTiles[$k];
    }
    return $sum;
}

function partB(array $images)
{
    foreach ($images as $image) {
        $image = $image['image'];
        $seaMonsters = 0;
        for ($y = 1; $y < count($image) - 1; $y++) {
            for ($x = 0; $x < count($image[0]) - 18; $x++) {
                if (($image[$y][$x] ?? null) === '#') {
                    $points = getRelativePoints($y, $x);
                    foreach ($points as [$oY, $oX]) {
                        if (($image[$oY][$oX] ?? null) !== '#') {
                            continue 2;
                        }
                    }
                    $seaMonsters++;
                }
            }
        }
        if ($seaMonsters > 0) {
            $hashes = array_sum(array_map(fn ($row) => count(array_filter($row, fn($v) => $v === '#')), $image));
            $roughness =  $hashes - ($seaMonsters * 15);
            echo "Found $seaMonsters monsters, roughness: $roughness\n";
            return;
        }
    }
}

function getRelativePoints($y, $x)
{
    // Origin point is here
    // v                 #
    // #    ##    ##    ###
    //  #  #  #  #  #  #
    return [
        [$y + 1, $x + 1],
        [$y + 1, $x + 4],
        [$y + 1, $x + 7],
        [$y + 1, $x + 10],
        [$y + 1, $x + 13],
        [$y + 1, $x + 16],
        [$y, $x + 5],
        [$y, $x + 6],
        [$y, $x + 11],
        [$y, $x + 12],
        [$y, $x + 17],
        [$y, $x + 18],
        [$y, $x + 19],
        [$y - 1, $x + 18],
    ];
}

function getImage($tiles)
{
    $sideLength = sqrt(count($tiles));
    $rows = getGroups($tiles, ['r', 'l'], $sideLength);
    $mappedRows = [];
    foreach ($rows as $row) {
        $mappedRows[implode(',', $row['tiles'])] = getVariants($row['grid'], $row['image'], false);
    }
    return getGroups($mappedRows, ['b', 't'], $sideLength);
}

function getGroups(array $tiles, array $sides = ['r', 'l'], int $target = 3): array
{
    $groups = [];
    foreach ($tiles as $id => $variants) {
        foreach ($variants as ['tile' => $variant, 'image' => $image]) {
            $other = excludeTile($tiles, $id);

            $group = [
                'tiles' => [$id],
                'grid' => $variant,
                'image' => $image,
            ];
            if ($group = findGroup($other, $sides, $group, $target)) {
                $groups[] = $group;
            }
        }
    }
    return $groups;
}

function findGroup(array $tiles = [], array $sides = ['r', 'l'], array $group = [], int $target = 3): ?array
{
    [$firstEdge, $secondEdge] = $sides;
    $rightEdge = getEdge($group['grid'], $firstEdge);
    foreach ($tiles as $otherId => $otherVariants) {
        foreach ($otherVariants as $otherVariant) {
            $leftEdge = getEdge($otherVariant['tile'], $secondEdge);
            if ($leftEdge === $rightEdge) {
                $thisGroup = $group;
                $thisGroup['tiles'][] = $otherId;
                $thisGroup['grid'] = combineGrid($thisGroup['grid'], $otherVariant['tile'], $firstEdge === 'b');
                $thisGroup['image'] = combineGrid($thisGroup['image'], $otherVariant['image'], $firstEdge === 'b');
                if (count($thisGroup['tiles']) >= $target) {
                    return $thisGroup;
                }
                return findGroup(
                    excludeTile($tiles, $otherId),
                    $sides,
                    $thisGroup,
                    $target
                );
            }
        }
    }
    return null;
}

function excludeTile($tiles, $tileId): array
{
    $tileId = explode(',', $tileId);
    return array_filter($tiles, function ($k) use ($tileId) {
        $k = explode(',', $k);
        return empty(array_intersect($k, $tileId));
    }, ARRAY_FILTER_USE_KEY);
}

function combineGrid($a, $b, $vert = false) {

    if ($vert) {
        return array_merge($a, $b);
    }

    return array_map('array_merge', $a, $b);
}

function trimBorders($a): array
{
    $b = [];
    for ($y = 1; $y < count($a) - 1; $y++) {
        $b[] = array_slice($a[$y], 1, -1);
    }
    return $b;
}

function getVariants($tile, $image, $rotate = true): array
{
    $variants = [['tile' => $tile, 'image' => $image]];
    // rotate 90, 180, 270
    // flip horizontal, vertical
    $degs = [180];
    if ($rotate) {
        $degs[] = 90;
        $degs[] = 270;
    }
    foreach ($degs as $deg) {
        $var = rotate($tile, $deg);
        $img = rotate($image, $deg);
        $variants[] = ['tile' => $var, 'image' => $img];
    }
    foreach (['h', 'v'] as $dir) {
        $flipped = flip($tile, $dir);
        $img = flip($image, $dir);
        $variants[] = ['tile' => $flipped, 'image' => $img];
        foreach ($degs as $deg) {
            $var = rotate($flipped, $deg);
            $img2 = rotate($img, $deg);
            $variants[] = ['tile' => $var, 'image' => $img2];
        }
    }
    return $variants;
}

function flip($tile, $dir): ?array
{
    if ($dir === 'h') {
        $nextTile = [];
        foreach ($tile as $row) {
            $nextTile[] = array_reverse($row);
        }
        return $nextTile;
    } elseif ($dir === 'v') {
        return array_reverse($tile);
    }
    return null;
}

function rotate(array $tile, int $deg = 90) {
    $times = $deg / 90;
    while ($times > 0) {
        $nextTile = [];
        for ($i = 0; $i < count($tile[0]); $i++) {
            $col = array_column($tile, $i);
            $nextTile[] = array_reverse($col);
        }
        $tile = $nextTile;
        $times--;
    }
    return $tile;
}

function getEdge($tile, $edge): ?array
{
    if ($edge === 'l') {
        return array_column($tile, 0);
    } elseif ($edge === 'r') {
        return array_column($tile, count($tile[0]) - 1);
    } elseif ($edge === 't') {
        return $tile[0];
    } elseif ($edge === 'b') {
        return $tile[count($tile) - 1];
    }
    return null;
}

function getTiles(array $lines): array
{
    $tiles = $tile = [];
    $tileId = null;
    foreach ($lines as $line) {
        if (preg_match('/^Tile (\d{4}):$/', $line, $matches)) {
            if (!empty($tileId)) {
                $tiles[$tileId] = getVariants($tile, trimBorders($tile));
            }
            $tileId = (int)$matches[1];
            $tile = [];
            continue;
        }
        $tile[] = str_split($line);
    }
    $tiles[$tileId] = getVariants($tile, trimBorders($tile));
    return $tiles;
}
