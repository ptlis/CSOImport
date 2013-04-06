<?php



namespace ptlis\CSOImport;

class CSOImport
{
    private $fileName;
    private $importMap;

    public function __construct($fileName, array $importMap)
    {
        $this->fileName = $fileName;
        $this->importMap = $importMap;
    }


    public function extract()
    {


        $reader = new CSOReader($this->importMap);

        $reader->open($this->fileName);

        foreach ($reader as $lineNo => $row) {
            echo 'line: ' . number_format($lineNo) . "\n";
            print_r($row);
        }
    }
}
