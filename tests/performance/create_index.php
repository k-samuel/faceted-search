<?php
require '../../vendor/autoload.php';

$t = microtime(true);

$colors = ['red','green','blue','yellow','black','white'];
$brands = ['Nike','H&M','Zara','Adidas','Louis Vuitton','Cartier','Hermes','Gucci','Uniqlo','Rolex','Coach','Victoria\'s Secret','Chow Tai Fook','Tiffany & Co.','Burberry','Christian Dior','Polo Ralph Lauren','Prada','Under Armour','Armani','Puma','Ray-Ban'];

$warehouses = [1,10,23,345,43,5476,34,675,34,24,789,45,65,34,54];
$type = ['normal','middle','good'];

$index = new \KSamuel\FacetedSearch\Index();

for($i=1; $i<100000;$i++){
    $countWh = rand(0, count($warehouses));
    $wh = [];
    for($k=0;$k<$countWh;$k++) {
        $wh[] = $warehouses[rand(0,count($warehouses)-1)];
    }
    $rec = [
        'color' => $colors[rand(0,5)],
        'back_color' => $colors[rand(0,5)],
        'size' => rand(34,50),
        'brand' =>  $brands[rand(0,count($brands)-1)],
        'price' => rand(1000,8000),
        'discount' => rand(0,10),
        'combined' => rand(0,1),
        'quantity' => rand(0,100),
        'warehouse' => array_unique($wh),
        'type' => $type[rand(0,count($type)-1)]
    ];
    $index->addRecord($i,$rec);
}

file_put_contents('./facet.json', json_encode($index->getData()));

echo microtime(true) - $t."\n";