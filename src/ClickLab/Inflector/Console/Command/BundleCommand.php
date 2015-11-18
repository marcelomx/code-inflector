<?php

 namespace ClickLab\Inflector\Console\Command;

 use ClickLab\Inflector\ClassInflector;
 use ClickLab\Inflector\EntityInflector;
 use ClickLab\Inflector\ViewInflector;
 use Symfony\Component\Console\Input\InputArgument;
 use Symfony\Component\Console\Input\InputInterface;
 use Symfony\Component\Console\Output\OutputInterface;
 use Symfony\Component\Console\Question\Question;
 use Symfony\Component\Finder\Finder;

 /**
 * @author Marcelo Rodrigues <marcelo.mx@gmail.com>
 * @api
 */ 
class BundleCommand extends FileCommand
{
    /**
     * @return void
     */
    public function configure()
    {
        $this
            ->setName('inflect:bundle')
            ->addArgument('path', InputArgument::REQUIRED, 'The bundle source path')
        ;

        $this->configureModes();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $bundlePath = realpath($input->getArgument('path'));
        $bundleNamespace = basename($bundlePath);
        $excludedFiles = [];
        $inflectedVariables = array();
        $showPreview = $input->getOption('preview');

        // Providing bundle paths!
        $suggestedMappingPath = $bundlePath . '/Resources/config/doctrine';
        $suggestsViewsPath = $bundlePath . '/Resources/views';

        $helper = $this->getHelper('question');
        $question = new Question(sprintf('<info>Provide the entityes mapping path</info> <comment>(%s)</comment>:', $suggestedMappingPath), $suggestedMappingPath);
        $mappingPath = $helper->ask($input, $output, $question);
        $question = new Question(sprintf('<info>Provide the entityes mapping path</info> <comment>(%s)</comment>:', $suggestsViewsPath), $suggestsViewsPath);
        $viewsPath = $helper->ask($input, $output, $question);

        if ($mappingPath && file_exists($mappingPath)) {
            // Inflect entities
            $output->writeln('--------------------------------------');
            $output->writeln('<comment>Inflecting ENTITIES</comment>');
            $output->writeln('--------------------------------------');

            $finder = new Finder();
            foreach ($finder->files()->name('/\.yml$/')->in($mappingPath) as $file) {

                $entityInflector = new EntityInflector((string) $file);
                $inflectedVariables = array_merge($inflectedVariables, $this->doInflect($input, $output, $entityInflector, $showPreview));
                $excludedFiles[] = (string) $file;
                $excludedFiles[] = $entityInflector->getClassInflector()->getFile();
            }
        }

        // Inflect all classes
        $output->writeln('--------------------------------------');
        $output->writeln('<comment>Inflecting CLASSES</comment>');
        $output->writeln('--------------------------------------');

        $finder = new Finder();
        foreach ($finder->files()->in($bundlePath)->name('/\.php$/') as $file) {
            $classFile = (string) $file;
            if (in_array($classFile, $excludedFiles)) continue;

            $className = str_replace($bundlePath, '', $classFile);
            $className = $this->normalizeClassName($className, $bundleNamespace);

            if (class_exists($className)) {
                $classInflector = new ClassInflector($className);
                $inflectedVariables = array_merge($inflectedVariables, $this->doInflect($input, $output, $classInflector, $showPreview));
            }
        }

        // Inflect all views
        if ($viewsPath && file_exists($viewsPath)) {
            $output->writeln('--------------------------------------');
            $output->writeln('<comment>Inflecting VIEWS</comment>');
            $output->writeln('--------------------------------------');

            $finder = new Finder();
            foreach ($finder->files()->in($viewsPath)->name('/\.twig$/') as $file) {
                $twigFile = (string) $file;
                echo $twigFile . PHP_EOL;
                $viewInflector = new ViewInflector(null, $twigFile);
                $this->doInflect($input, $output, $viewInflector, $showPreview);
            }
        }
    }

    /**
     * @param $className
     * @param string $baseNamespace
     * @return string
     */
    protected function normalizeClassName($className, $baseNamespace = '')
    {
        $className = ltrim($className, '/');
        $className = preg_replace('/\.php$/', '', $className);
        $className = preg_replace('/\//', '\\', $className);
        return $baseNamespace . '\\' . $className;
    }
}