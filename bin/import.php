<?php

require_once '../vendor/autoload.php';

// Handle arguments

$fileName = null;

if (count($argv) > 1) {

    if ($argv[1] === '--help' || $argv[1] === 'help') {
        // Help page

        echo 'Usage:' . "\n";
        echo '  ./import' . "\n";

        echo "\n";
    } elseif (substr($argv[count($argv) - 1], 0, 1) === '/') {
        // The final element should be a path
        $fileName = $argv[count($argv) - 1];
    }

} else {
    echo 'bad call'."\n\n";
    die();
}

if ($fileName !== null) {
    $CSOImporter = new \ptlis\CSOImport\CSOImport($argv[count($argv) - 1]);
    $CSOImporter->extract();
}
