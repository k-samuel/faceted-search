<?php
$resultsCount = 100000;


$t = microtime(true);
$filePath = '../data/' . $resultsCount . '/data.json';
if (!is_dir(dirname($filePath))) {
    if (!mkdir(dirname($filePath), 0775, true)) {
        trigger_error('Cannot create dir ' . dirname($filePath));
    }
}

$colors = ['red', 'green', 'blue', 'yellow', 'black', 'white'];
$brands = [
    'Nike',
    'H&M',
    'Zara',
    'Adidas',
    'Louis Vuitton',
    'Cartier',
    'Hermes',
    'Gucci',
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
    'Puma',
    'Ray-Ban'
];

$warehouses = [1, 10, 23, 345, 43, 5476, 34, 675, 34, 24, 789, 45, 65, 34, 54, 511, 512, 520];
$type = ['normal', 'middle', 'good'];

$dataSet = [];

for ($i = 1; $i <= $resultsCount; $i++) {
    $countWh = rand(0, count($warehouses));
    $wh = [];
    for ($k = 0; $k < $countWh; $k++) {
        $wh[] = $warehouses[rand(0, count($warehouses) - 1)];
    }
    $rec = [
        'id' => $i,
        'color' => $colors[rand(0, 5)],
        'back_color' => $colors[rand(0, 5)],
        'size' => rand(34, 50),
        'brand' => $brands[rand(0, count($brands) - 1)],
        'price' => rand(1000, 10000),
        'discount' => rand(0, 10),
        'combined' => rand(0, 1),
        'quantity' => rand(0, 100),
        'warehouse' => array_unique($wh),
        'type' => $type[rand(0, count($type) - 1)]
    ];
    file_put_contents($filePath, json_encode($rec) . "\n", FILE_APPEND);
}

echo 'total time: ' . number_format(microtime(true) - $t, 3) . PHP_EOL;
