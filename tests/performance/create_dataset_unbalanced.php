<?php

$t = microtime(true);
$resultsCount = 1000000;

$colors = ['red', 'red',  'green', 'blue', 'yellow', 'black', 'black', 'black', 'black', 'white', 'white', 'white'];
$brands = [
    'Nike',
    'H&M', 'H&M', 'H&M', 'H&M',
    'Zara',
    'Adidas',
    'Louis Vuitton',
    'Cartier', 'Cartier', 'Cartier',
    'Hermes',
    'Gucci', 'Gucci',
    'Uniqlo',
    'Rolex',
    'Coach',
    'Victoria\'s Secret',
    'Chow Tai Fook',
    'Tiffany & Co.',
    'Burberry',
    'Christian Dior',
    'Polo Ralph Lauren',
    'Prada',
    'Under Armour',
    'Armani',
    'Puma', 'Puma', 'Puma', 'Puma',
    'Ray-Ban'
];

$warehouses = [1, 10, 23, 345, 43, 5476, 34, 675, 34, 24, 789, 45, 65, 34, 54, 511, 512, 520];
$type = ['normal', 'normal', 'normal', 'middle', 'good', 'good'];
$sizes = [34, 35, 36, 37, 38, 3, 40, 41, 42, 43, 44, 45];

$dataSet = [];

for ($i = 1; $i <= $resultsCount; $i++) {
    $countWh = rand(0, count($warehouses));
    $wh = [];
    for ($k = 0; $k < $countWh; $k++) {
        $wh[] = $warehouses[rand(0, count($warehouses) - 1)];
    }
    $rec = [
        'id' => $i,
        'color' => $colors[rand(0, count($colors) - 1)],
        'back_color' => $colors[rand(0, 5)],
        'size' => $sizes[rand(0, count($sizes) - 1)],
        'brand' => $brands[rand(0, count($brands) - 1)],
        'price' => rand(1000, 10000),
        'discount' => rand(0, 10),
        'combined' => rand(0, 1),
        'quantity' => rand(0, 100),
        'warehouse' => array_unique($wh),
        'type' => $type[rand(0, count($type) - 1)]
    ];
    file_put_contents('./ub_data.json', json_encode($rec) . "\n", FILE_APPEND);
}

echo 'total time: ' . number_format(microtime(true) - $t, 3) . PHP_EOL;
