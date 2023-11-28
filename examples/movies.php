<?php
use KSamuel\FacetedSearch\Search;
use KSamuel\FacetedSearch\Filter\FilterInterface;
use KSamuel\FacetedSearch\Filter\ValueFilter;
use KSamuel\FacetedSearch\Filter\ExcludeValueFilter;
use KSamuel\FacetedSearch\Filter\RangeFilter;
use KSamuel\FacetedSearch\Filter\ValueIntersectionFilter;
use KSamuel\FacetedSearch\Index\Factory;
use KSamuel\FacetedSearch\Index\IndexInterface;
use KSamuel\FacetedSearch\Query\AggregationQuery;
use KSamuel\FacetedSearch\Query\AggregationSort;
use KSamuel\FacetedSearch\Query\Order;
use KSamuel\FacetedSearch\Query\SearchQuery;

include dirname(__FILE__) . "/../vendor/autoload.php";

$movies = [
    1 => [
        'title' => 'Inception',
        'director' => 'Christopher Nolan',
        'genre' => ['Sci-fi', 'Thriller', 'Adventure']
    ],
    2 => [
        'title' => 'The Godfather',
        'director' => 'Francis Ford Coppola',
        'genre' => ['Crime', 'Drama']
    ],
    3 => [
        'title' => 'Pulp Fiction',
        'director' => 'Quentin Tarantino',
        'genre' => ['Crime', 'Drama', 'Comedy']
    ],
    4 => [
        'title' => 'Schindler’s List',
        'director' => 'Steven Spielberg',
        'genre' => ['Drama', 'History']
    ],
    5 => [
        'title' => 'The Shawshank Redemption',
        'director' => 'Frank Darabont',
        'genre' => ['Drama']
    ],
    6 => [
        'title' => 'Forrest Gump',
        'director' => 'Robert Zemeckis',
        'genre' => ['Drama', 'Romance']
    ],
    7 => [
        'title' => 'Fight Club',
        'director' => 'David Fincher',
        'genre' => ['Drama', 'Thriller']
    ],
    8 => [
        'title' => 'The Dark Knight',
        'director' => 'Christopher Nolan',
        'genre' => ['Action', 'Crime', 'Drama']
    ],
    9 => [
        'title' => 'The Grand Budapest Hotel',
        'director' => 'Wes Anderson',
        'genre' => ['Comedy', 'Adventure']
    ],
    10 => [
        'title' => 'Mad Max: Fury Road',
        'director' => 'George Miller',
        'genre' => ['Action', 'Adventure', 'Sci-fi']
    ],
    11 => [
        'title' => 'Parasite',
        'director' => 'Bong Joon-ho',
        'genre' => ['Comedy', 'Drama', 'Thriller']
    ],
    12 => [
        'title' => 'Gladiator',
        'director' => 'Ridley Scott',
        'genre' => ['Action', 'Adventure', 'Drama']
    ],
    13 => [
        'title' => 'Titanic',
        'director' => 'James Cameron',
        'genre' => ['Drama', 'Romance']
    ],
    14 => [
        'title' => 'Amélie',
        'director' => 'Jean-Pierre Jeunet',
        'genre' => ['Comedy', 'Romance']
    ],
    15 => [
        'title' => 'The Matrix',
        'director' => 'Lana Wachowski, Lilly Wachowski',
        'genre' => ['Action', 'Sci-fi']
    ],
    16 => [
        'title' => 'Spirited Away',
        'director' => 'Hayao Miyazaki',
        'genre' => ['Animation', 'Adventure', 'Family']
    ],
    17 => [
        'title' => 'La La Land',
        'director' => 'Damien Chazelle',
        'genre' => ['Drama', 'Musical', 'Romance']
    ],
    18 => [
        'title' => 'The Silence of the Lambs',
        'director' => 'Jonathan Demme',
        'genre' => ['Crime', 'Drama', 'Thriller']
    ],
    19 => [
        'title' => 'Saving Private Ryan',
        'director' => 'Steven Spielberg',
        'genre' => ['Drama', 'War']
    ],
    20 => [
        'title' => 'Interstellar',
        'director' => 'Christopher Nolan',
        'genre' => ['Adventure', 'Drama', 'Sci-fi']
    ],
    21 => [
        'title' => 'Alien',
        'director' => 'Ridley Scott',
        'genre' => ['Sci-fi', 'Horror']
    ],
    22 => [
        'title' => 'Blade Runner',
        'director' => 'Ridley Scott',
        'genre' => ['Sci-fi', 'Drama']
    ],
    23 => [
        'title' => 'Thelma & Louise',
        'director' => 'Ridley Scott',
        'genre' => ['Adventure', 'Crime', 'Drama']
    ],
    24 => [
        'title' => 'Gladiator',
        'director' => 'Ridley Scott',
        'genre' => ['Action', 'Adventure', 'Drama']
    ],
    25 => [
        'title' => 'Black Hawk Down',
        'director' => 'Ridley Scott',
        'genre' => ['Drama', 'War']
    ],
    26 => [
        'title' => 'Reservoir Dogs',
        'director' => 'Quentin Tarantino',
        'genre' => ['Crime', 'Drama', 'Thriller']
    ],
    27 => [
        'title' => 'Kill Bill: Vol. 1',
        'director' => 'Quentin Tarantino',
        'genre' => ['Action', 'Crime', 'Drama']
    ],
    28 => [
        'title' => 'Django Unchained',
        'director' => 'Quentin Tarantino',
        'genre' => ['Drama', 'Western']
    ],
    29 => [
        'title' => 'Inglourious Basterds',
        'director' => 'Quentin Tarantino',
        'genre' => ['Adventure', 'Drama', 'War']
    ],
    30 => [
        'title' => 'Once Upon a Time in Hollywood',
        'director' => 'Quentin Tarantino',
        'genre' => ['Comedy', 'Drama']
    ]
];

$search = (new Factory)->create(Factory::ARRAY_STORAGE);
$storage = $search->getStorage();

foreach ($movies as $id => $item) { 
    $recordId = $id;

    $storage->addRecord($recordId, $item);
}

$storage->optimize();
$indexData = $storage->export();



$search = (new Factory)->create(Factory::ARRAY_STORAGE);
$search->setData($indexData);


$filters = [
    //new ValueFilter('genre', ['Comedy', 'Drama']), // ANY OF  (OR condition)
    new ValueIntersectionFilter('genre', ['Comedy', 'Drama', 'Sci-fi']) // AND condition
];

$query = (new SearchQuery())->filters($filters);

$records = $search->query($query);

if ($records) {
    foreach ($records as $record) {
        echo $movies[$record]['title'] . "\n";
        echo "- " . join(", ", $movies[$record]['genre']) . " (" . $movies[$record]['director'] . ")\n";
        echo "\n";
    }
} else {
    echo "Nothing found.\n\n";
}

$query = (new AggregationQuery())->filters($filters)->countItems()->sort();
$filterData = $search->aggregate($query);

print_r($filterData);
