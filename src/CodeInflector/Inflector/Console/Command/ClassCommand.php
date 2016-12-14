<?php

namespace CodeInflector\Inflector\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use CodeInflector\Inflector\ClassInflector;

/**
 * @author Marcelo Rodrigues <marcelo.mx@gmail.com>
 * @api
 */ 
class ClassCommand extends FileCommand
{
    public function configure()
    {
        $this
            ->setName('inflect:class')
            ->setDescription('Inflect a class')
            ->addArgument('class', InputArgument::REQUIRED, 'The class name')
        ;

        $this->configureModes();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $className = $input->getArgument('class');
        $inflector = new ClassInflector($className);
        $this->doInflect($input, $output, $inflector);
    }
}