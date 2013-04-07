<?php

/** Class to read Code Point data line-by-line from zip file.
 *
 * @version     CSOReader.php v0.1-dev 2013-04-07
 * @copyright   (c) 2013 ptlis
 * @license     GNU Lesser General Public License v2.1
 * @package     ptlis\conneg
 * @author      Brian Ridley <ptlis@ptlis.net>
 */


namespace ptlis\CSOImport;

/** Provides an interface through which Open Code-Point data can be imported
 *  from the zip file into a database. Also converts northings & eastings into
 *  latitude and longitude.
 */
class CSOImport
{
    /** @var int    The path to the CSO import zip file. */
    private $filePath;

    /** @var array  Map of column numbers to human-friendly. */
    private $importMap;


    /** Constructor
     *
     * @param   string  $filePath   The path to the CSO import zip file.
     * @param   array   $importMap  Map of column numbers to human-friendly
     *      names.
     */
    public function __construct($filePath, array $importMap)
    {
        $this->filePath = $filePath;
        $this->importMap = $importMap;
    }


    /** Extract the Open Code-Point data and propagate it into a database.
     */
    public function import()
    {
        $reader = new CSOReader($this->importMap);

        $reader->open($this->filePath);

        foreach ($reader as $lineNo => $row) {
            /*echo 'line: ' . number_format($lineNo) . "\n";
            print_r($row);*/
            if ($lineNo % 1000 == 0 && $lineNo > 0) {
                echo 'line: ' . number_format($lineNo) . "\n";
            }
        }

        echo 'total: ' . number_format($lineNo)."\n";
    }
}
