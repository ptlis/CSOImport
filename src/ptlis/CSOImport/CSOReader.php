<?php

/** Class to read Code Point data line-by-line from zip file.
 *
 * @version     CSOReader.php v0.1-dev 2013-04-06
 * @copyright   (c) 2013 ptlis
 * @license     GNU Lesser General Public License v2.1
 * @package     ptlis\conneg
 * @author      Brian Ridley <ptlis@ptlis.net>
 */


namespace ptlis\CSOImport;

/** Provides an interface to read an Open Code Point CSV line-by-line.
 */
class CSOReader implements \Iterator
{
    /** @var int The CSO import zip filename. */
    private $fileName;

    /** Map of column numbers to human-friendly. */
    private $importMap;

    /** @var int The current file number. */
    private $fileNo;

    /** @var int The line no of the currently read line. */
    private $lineNo;

    /** @var ZipArchive object containing Open Code Point data. */
    private $archive;

    /** @var \SplFileObject Object representing a single file of data to read. */
    private $currentCSV;

    /** @var string The filename of the current CSV being processed. */
    private $currentFileName;


    /** Constructor
     *
     * @param   array   $importMap  Map of column numbers to human-friendly
     *      names.
     */
    public function __construct(array $importMap)
    {
        $this->importMap = $importMap;
        $this->archive = new \ZipArchive();
    }


    /** Open a zip file for processing.
     *
     * @param   string  $fileName   The file to open.
     *
     * @throws  RuntimeException    When there is an error opening the file.
     */
    public function open($fileName)
    {
        $this->fileName = $fileName;

        if (($errorCode = $this->archive->open($this->fileName) !== true)) {
            switch ($errorCode) {
                case ZipArchive::ER_EXISTS: // File already exists.
                    $errMsg = 'Failed to open "' . $this->fileName . '": File already exists.';
                    break;
                case ZipArchive::ER_INCONS: // Zip archive inconsistent.
                    $errMsg = 'Failed to open "' . $this->fileName . '": Zip archive inconsistent.';
                    break;
                case ZipArchive::ER_INVAL:  // Invalid argument.
                    $errMsg = 'Failed to open "' . $this->fileName . '": Invalid argument.';
                    break;
                case ZipArchive::ER_MEMORY: // Malloc failure.
                    $errMsg = 'Failed to open "' . $this->fileName . '": Malloc failure.';
                    break;
                case ZipArchive::ER_NOENT:  // No such file.
                    $errMsg = 'Failed to open "' . $this->fileName . '": No such file.';
                    break;
                case ZipArchive::ER_NOZIP:  // Not a zip archive.
                    $errMsg = 'Failed to open "' . $this->fileName . '": Not a zip archive.';
                    break;
                case ZipArchive::ER_OPEN:   // Can't open file.
                    $errMsg = 'Failed to open "' . $this->fileName . '": Can\'t open file.';
                    break;
                case ZipArchive::ER_READ:   // Read error.
                    $errMsg = 'Failed to open "' . $this->fileName . '": Read error.';
                    break;
                case ZipArchive::ER_SEEK:   // Seek error.
                    $errMsg = 'Failed to open "' . $this->fileName . '": Seek error.';
                    break;
                default:                    // Should never occur
                    $errMsg = 'Failed to open "' . $this->fileName . '": Should never occur';
                    break;
            }

            throw new RuntimeException($errMsg, $errorCode);
        } else {
            // Find first data file & open for reading
            $this->fileNo = 0;
            $this->getNextCSV();
        }
    }


    /** Get the next CSV from the zip.
     *
     * @return  boolean True if there are CSVs remaining to parse, false
     *      otherwise.
     */
    private function getNextCSV()
    {
        // Scan for the next data file
        for (; $this->fileNo < $this->archive->numFiles; $this->fileNo++) {
            $stat = $this->archive->statIndex($this->fileNo);
            $nameParts = array_filter(explode('/', $stat['name']));

            // Found the first matching file, open for reading & mark as valid
            if (count($nameParts) == 2 && $nameParts[0] == 'Data' && $stat['name'] != $this->currentFileName) {
                $this->currentFileName = $stat['name'];
                $this->currentCSV = new \SplFileObject('zip://' . $this->fileName . '#' . $stat['name']);
                return true;
            }
        }
        return false;
    }


    /** Return the current element.
     *
     * @internal
     *
     * @return  array   An array of type data and quality factors.
     */
    public function current()
    {
        $rawData = $this->currentCSV->fgetcsv();
        $data = array();
        foreach ($this->importMap as $field => $key) {
            $data[$field] = $rawData[$key];
        }
        return $data;
    }


    /** Return the current key.
     *
     * @internal
     *
     * @return  int Current key.
     */
    public function key()
    {
        return $this->lineNo;
    }


    /** Increments the current key by one.
     *
     * @internal
     */
    public function next()
    {
        ++$this->lineNo;
    }


    /** Resets the current key to the start of the array.
     *
     * @internal
     */
    public function rewind()
    {
        $this->lineNo = 0;
        $this->fileNo = 0;
    }


    /** Checks to see if the current key is valid.
     *
     * @internal
     *
     * @return  bool    Returns false when out of rows.
     */
    public function valid()
    {
        if ($this->currentCSV->eof()) {
            // Look for another file
            return $this->getNextCSV();

        } else {
            // Data still remains in current file
            return true;
        }
    }
}
