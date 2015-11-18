<?php

 namespace ClickLab\Inflector\Console\Command;

 use ClickLab\Inflector\EntityInflector;
 use Symfony\Component\Console\Input\InputArgument;
 use Symfony\Component\Console\Input\InputInterface;
 use Symfony\Component\Console\Output\OutputInterface;

 /**
 * @author Marcelo Rodrigues <marcelo.mx@gmail.com>
 * @api
 */ 
class EntityCommand extends FileCommand
{
    /**
     * @return void
     */
    public function configure()
    {
        $this
            ->setName('inflect:entity')
            ->addArgument('path', InputArgument::REQUIRED, 'The YAML mapping pathame or directory')
        ;

        $this->configureModes();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $path = realpath($input->getArgument('path'));
        $inflector = new EntityInflector($path);

        $this->doInflect($input, $output, $inflector);
    }
}