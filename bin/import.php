<?php

require_once '../vendor/autoload.php';



$app = new \Cilex\Application('CSOImport', '0.1');

$app->register(
    new \Cilex\Provider\ConfigServiceProvider(),
    array(
        'config.path' => __DIR__.'/../src/ptlis/CSOImport/config.yml'
    )
);

$app->command(new ptlis\CSOImport\Command\ImportCommand($app['config']['import_map']));

$app->run();
