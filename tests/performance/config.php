<?php
return [
    'result_size' => 100000,
    'data_dir' => '../data/',
    'data_file' => 'data.json',
    'facet_file' => 'facet.json',
    'ub_prefix' => 'ub_',
    'tests' => [
        'find' => true,
        // 'findAndSort' => true,
        // 'findWithExclude' => true,
        // 'findWithRange' => true,
        'aggregate' => true,
        'aggregateAndCount' => true,
        //'aggregateAndCountWithExclude' => true,
        //'sortTest' => true,
    ]
];
