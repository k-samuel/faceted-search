<?php

$fileName = 'mobile-db.php';

$brands = ['MyPhone', 'Xanomi', 'PlusTwo', 'Wyomi', 'NamSun', 'Alcun', 'Lyf', 'Kiano', 'Popo', 'Manamonic', 'Lilips', 'PonyClarcson', 'Yopa', 'Poople', 'Konor'];

$letters = ["a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"];

$models = [];

foreach ($brands as $name) {
    $modelCount = rand(3, 8);
    for ($i = 0; $i < $modelCount; $i++) {
        $abbLen = rand(2, 4);
        $model = '';
        for ($k = 0; $k < $abbLen; $k++) {
            $model .= mb_strtoupper($letters[rand(0, count($letters) - 1)]);
        }
        $model .= '-' . rand(1, 9);
        $models[$name][] = $model;
    }
}

$hd = ['32', '64', '128', '256', '512'];
$ram = ['4Gb', '6Gb', '8Gb', '12Gb'];
$color = ['Red', 'Black', 'White', 'Blue', 'Yellow', 'Green', 'Gray', 'Gold', 'Silver'];
$camMp = ['8', '12', '40', '50', '100'];
$dia = ['4.0', '4.7', '5.8', '6.1', '6.5', '6.7'];
$akk = ['1200', '4000', '5000', '6000'];
$st = ['new', 'refurbished', 'used'];


$setSize = 5000;
$data = [];
for ($i = 0; $i < $setSize; $i++) {
    $b = $brands[rand(0, count($brands) - 1)];
    $m = $models[$b][rand(0, count($models[$b]) - 1)];
    $id = $i + 1;
    $data[$id] = [
        'id' => $id,
        'brand' => $b,
        'model' => $m,
        'color' => $color[rand(0, count($color) - 1)],
        'cam' => $camMp[rand(0, count($camMp) - 1)],
        'diagonal' => $dia[rand(0, count($dia) - 1)],
        'battery' => $akk[rand(0, count($akk) - 1)],
        'state' => $st[rand(0, count($st) - 1)],
        'price' => rand(100, 1000),
        'ram' => $ram[rand(0, count($ram) - 1)],
        'hd' =>  $hd[rand(0, count($hd) - 1)],
    ];
}

file_put_contents($fileName, "<?php \n return " . var_export($data, true) . ';');
