<?php

 namespace ClickLab\Inflector\Console\Command;

 use ClickLab\Inflector\ViewInflector;
 use Symfony\Component\Console\Input\InputArgument;
 use Symfony\Component\Console\Input\InputInterface;
 use Symfony\Component\Console\Output\OutputInterface;

 /**
 * @author Marcelo Rodrigues <marcelo.mx@gmail.com>
 * @api
 */ 
class ViewCommand extends FileCommand
{
    /**
     * @return void
     */
    public function configure()
    {
        $this
            ->setName('inflect:view')
            ->setDescription('Inflect a Twig view')
            ->addArgument('file', InputArgument::REQUIRED, 'File absolute pathname')
        ;

        $this->configureModes();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $file = realpath($input->getArgument('file'));
        $inflector = new ViewInflector(null, $file);
        $this->doInflect($input, $output, $inflector);
    }
}