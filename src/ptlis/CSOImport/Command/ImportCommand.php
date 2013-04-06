<?php



namespace ptlis\CSOImport\Command;

use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Output\OutputInterface;

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

        $this
            ->setName('file')
            ->setDescription('The path to the OS Open Code Point zip archive.')
            ->addArgument(
                'file_name',
                InputArgument::REQUIRED,
                'The path to the OS Open Code Point zip archive.'
            );

        /*
            ->addOption(
                'option_name',
                null,
                InputOption::VALUE_NONE,
                'Description'
            )
         */
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
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
