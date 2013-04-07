<?php



namespace ptlis\CSOImport\Command;

use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Yaml\Yaml;

class ImportCommand extends \Cilex\Command\Command
{
    private $importMap;

    public function __construct(array $importMap, $name = null)
    {
        parent::__construct($name);
        $this->importMap = $importMap;
    }

    protected function configure()
    {
        // TODO: Add update
        $this
            ->setName('import')
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


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configFile = $input->getOption('from-config');

        $dbConfig = array(
            'driver'    => null,
            'host'      => null,
            'port'      => null,
            'database'  => null,
            'username'  => null,
            'password'  => null
        );

        if (strlen($configFile)) {
            // Read config from file
            $configPath = $input->getOption('from-config');
            $parsed = Yaml::parse($configPath);

            if ($parsed != $configPath) {
                // store parsed config
                if (array_key_exists('db', $parsed)) {
                    foreach ($dbConfig as $optionKey => $value) {
                        if (array_key_exists($optionKey, $parsed['db']) && strlen($parsed['db'][$optionKey])) {
                            $dbConfig[$optionKey] = $parsed['db'][$optionKey];
                        }
                    }
                } else {
                    throw new \RuntimeException('Config file missing required field "db"');
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
                throw new \InvalidArgumentException('Driver must be one of "Mysqli", "Pgsql", "Sqlsrv", "Pdo_Mysql", "Pdo_Sqlite" or "Pdo_Pgsql".');
                break;
        }

        $db = new \Zend\Db\Adapter\Adapter($dbConfig);



        $CSOImporter = new \ptlis\CSOImport\CSOImport($input->getArgument('file_name'), $this->importMap);
        $CSOImporter->extract();

        /*
            if ($input->getOption('option_name')) {
                // Do Something
            }
         */

        return;
    }
}
