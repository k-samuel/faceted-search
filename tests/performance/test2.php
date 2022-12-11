<?php

$m = memory_get_usage();
$t = microtime(true);

/**
 * @var array<int,array{a:int,b:int}>
 */
$list = [];
for ($i=0;$i<10000;$i++){
    $list[] = ['a'=> (string)$i, 'b'=>(string)$i+1];
}
$result = 0;
foreach ($list as $o){
    $result+= $o['a'] . $o['b'];
    $result+= $o['a'] . $o['b'];
    $result+= $o['a'] . $o['b'];
    $result+= $o['a'] . $o['b'];
    $result+= $o['a'] . $o['b'];
}
echo (microtime(true) - $t) . 's ' . number_format((memory_get_usage() - $m)/1024/1024, 3) ." Mb";
echo PHP_EOL;