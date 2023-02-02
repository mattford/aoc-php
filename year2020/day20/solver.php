<?php
$lines = file('input.txt', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);

function partA(array $lines) {
    $tiles = getTiles($lines);


}

function getTiles(array $lines): array
{
    $tiles = $tile = [];
    $tileId = null;
    foreach ($lines as $line) {
        if (preg_match('/^Tile ([0-9]{4})$/', $line, $matches)) {
            if (!empty($tileId)) {
                $tiles[$tileId] = $tile;
            }
            $tileId = (int)$matches[1];
            $tile = [];
        }
        $tile[] = str_split($line);
    }
    $tiles[$tileId] = $tile;
    return $tiles;
}