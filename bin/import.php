<?php

$zipFile = '/home/brian/Desktop/codepo_gb.zip';

$archive = new ZipArchive();

if (($errorCode = $archive->open($zipFile) !== true)) {
    switch ($errorCode) {
        case ZipArchive::ER_EXISTS: // File already exists.
            break;
        case ZipArchive::ER_INCONS: // Zip archive inconsistent.
            break;
        case ZipArchive::ER_INVAL:  // Invalid argument.
            break;
        case ZipArchive::ER_MEMORY: // Malloc failure.
            break;
        case ZipArchive::ER_NOENT:  // No such file.
            break;
        case ZipArchive::ER_NOZIP:  // Not a zip archive.
            break;
        case ZipArchive::ER_OPEN:   //Can't open file.
            break;
        case ZipArchive::ER_READ:   // Read error.
            break;
        case ZipArchive::ER_SEEK:   // Seek error.
            break;
    }
} else {

    // Get a list of files
    for ($i = 0; $i < $archive->numFiles; $i++) {
        $stat = $archive->statIndex($i);
        $nameParts = array_filter(explode('/', $stat['name']));

        if (count($nameParts) == 2 && $nameParts[0] == 'Data') {
            $fileObj = new SplFileObject('zip://' . $zipFile . '#' . $stat['name']);
            while (!$fileObj->eof()) {
                echo print_r($fileObj->fgetcsv(), true)."\n";
            }
        }
    }
}
