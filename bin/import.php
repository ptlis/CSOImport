<?php

/** Command-line interface for importing Open Code-Point data.
 *
 * @version     CSOReader.php v0.1-dev 2013-04-07
 * @copyright   (c) 2013 ptlis
 * @license     GNU Lesser General Public License v2.1
 * @package     ptlis\conneg
 * @author      Brian Ridley <ptlis@ptlis.net>
 */

require_once '../vendor/autoload.php';



$app = new \Cilex\Application('CSOImport', '0.1');

$app->register(
    new \Cilex\Provider\ConfigServiceProvider(),
    array(
        'config.path' => __DIR__.'/../src/ptlis/CSOImport/config.yml'
    )
);

$app->command(
    new ptlis\CSOImport\Command\ImportCommand(
        $app['config']['import_map'],
        $app['config']['db_fields'],
        $app['config']['import_cols']
    )
);

$app->run();
