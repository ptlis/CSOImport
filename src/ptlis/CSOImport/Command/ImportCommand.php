<?php

/** Console command for importing Open Code-Point data.
 *
 * @version     ImportCommand.php v0.1-dev 2013-04-07
 * @copyright   (c) 2013 ptlis
 * @license     GNU Lesser General Public License v2.1
 * @package     ptlis\conneg
 * @author      Brian Ridley <ptlis@ptlis.net>
 */


namespace ptlis\CSOImport\Command;

use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Yaml\Yaml;

/** Class representing the import console command for importing Open Code-Point
 *  data.
 */
class ImportCommand extends \Cilex\Command\Command
{
    /** Map of column numbers to human-friendly. */
    private $importMap;
    
    /** Database configuration items. */
    private $dbFields;

    /** Columns that are made available for import. */
    private $importCols;

    /** Constructor
     *
     * @param   array   $importMap  Map of column numbers to human-friendly
     * @param   array   $dbFields   Database configuration items.
     * @param   array   $importCols Columns that are made available for import.
     */
    public function __construct(array $importMap, array $dbFields, array $importCols)
    {
        parent::__construct('import');
        $this->importMap = $importMap;
        $this->dbFields = $dbFields;
        $this->importCols = $importCols;
    }


    /** Setup arguments for the import command.
     */
    protected function configure()
    {
        // TODO: Add update
        $this
            ->setDescription('The path to the OS Open Code Point zip archive.')
            ->addArgument(
                'file_name',
                InputArgument::REQUIRED,
                'The path to the OS Open Code Point zip archive.'
            );

        // Allow loading of import configuration from config file
        $this
            ->addOption(
                'from-config',
                null,
                InputOption::VALUE_REQUIRED,
                'Load the input options from a config file'
            );

        // Allow
        $this
            ->addOption(
                'driver',
                null,
                InputOption::VALUE_REQUIRED,
                'Database driver to use, one of "Mysqli", "Pgsql", "Sqlsrv", "Pdo_Mysql", "Pdo_Sqlite" or "Pdo_Pgsql".'
            )
            ->addOption(
                'host',
                null,
                InputOption::VALUE_REQUIRED,
                'The hostname of the database server.'
            )
            ->addOption(
                'port',
                null,
                InputOption::VALUE_REQUIRED,
                'The port to connect to.'
            )
            ->addOption(
                'database',
                null,
                InputOption::VALUE_REQUIRED,
                'The database name.'
            )
            ->addOption(
                'username',
                null,
                InputOption::VALUE_REQUIRED,
                'The Username'
            );
    }


    /** Execute the import command.
     *
     * @param   InputInterface  $input  Object representing arguments passed to
     *      the command.
     * @param   OutputInterface $output Object representing the console output.
     *
     * @throws  \RuntimeException           If the config file is missing a
     *      required field.
     * @throws \InvalidArgumentException    When an invalid value was provided
     *      to an argument.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configFile = $input->getOption('from-config');

        $dbConfig = $this->dbFields;
        
        $tableName = '';
        $tableMap = array();

        if (strlen($configFile)) {
            // Read config from file
            $configPath = $input->getOption('from-config');
            $parsed = Yaml::parse($configPath);

            if ($parsed != $configPath) {
                // store parsed db config
                if (array_key_exists('db', $parsed)) {
                    foreach ($dbConfig as $optionKey => $value) {
                        if (array_key_exists($optionKey, $parsed['db']) && strlen($parsed['db'][$optionKey])) {
                            $dbConfig[$optionKey] = $parsed['db'][$optionKey];
                        }
                    }
                } else {
                    throw new \RuntimeException('Config file missing required field "db"');
                }
                
                // Table name
                if (array_key_exists('table_name', $parsed)) {
                    $tableName = $parsed['table_name'];
                } else {
                    throw new \RuntimeException('Config file missing required field "table_name"');
                }
                
                // Map of import cols to table cols
                if (array_key_exists('table_map', $parsed)) {
                    foreach ($this->importCols as $columnName => $value) {
                        if (array_key_exists($columnName, $parsed['table_map']) && strlen($parsed['table_map'][$columnName])) {
                            $tableMap[$columnName] = $parsed['table_map'][$columnName];
                        }
                    }
                    
                } else {
                    throw new \RuntimeException('Config file missing required field "table_map"');
                }

            } else {
                // Error opening / parsing config file
                $output->writeln('<error>Unable to read config file "' . $configPath . '"</error>');
                die();
            }

        } else {
            // Read config from command line options
            foreach ($dbConfig as $optionKey => $value) {
                if ($optionKey == 'password') {
                    // Prompt for password
                    $dialog = $this->getHelperSet()->get('dialog');
                    $dbConfig[$optionKey] = $dialog->askHiddenResponse($output, 'Enter password: ', null);
                } else {
                    if (strlen($input->getOption($optionKey))) {
                        $dbConfig[$optionKey] = $input->getOption($optionKey);
                    }
                }
            }
        }


        // Ensure that a valid driver was provided
        switch ($dbConfig['driver']) {
            case 'Mysqli':
            case 'Pgsql':
            case 'Sqlsrv':
            case 'Pdo_Mysql':
            case 'Pdo_Sqlite':
            case 'Pdo_Pgsql':
                break;
            default:
                throw new \InvalidArgumentException(
                    'Driver must be one of "Mysqli", "Pgsql", "Sqlsrv", "Pdo_Mysql", "Pdo_Sqlite" or "Pdo_Pgsql".'
                );
                break;
        }

        $db = new \Zend\Db\Adapter\Adapter($dbConfig);



        $CSOImporter = new \ptlis\CSOImport\CSOImport($input->getArgument('file_name'), $this->importMap, $db);
        $CSOImporter->import();

        return;
    }
}
