<?php

namespace ClickLab\Inflector\Console\Command;

use ClickLab\Inflector\BaseInflector;
use ClickLab\Inflector\ClassInflector;
use ClickLab\Inflector\EntityInflector;
use ClickLab\Inflector\FileInflector;
use ClickLab\Inflector\ViewInflector;
use \Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * @author Marcelo Rodrigues <marcelo.mx@gmail.com>
 * @api
 */
class FileCommand extends Command
{
    /**
     * @return void
     */
    public function configure()
    {
        $this
            ->setName('inflect:file')
            ->setDescription('Inflect a file')
            ->addArgument('file', InputArgument::REQUIRED, 'File absolute pathname')
            ->addOption('var',  null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Variable list to inflect', array())
        ;

        $this->configureModes();
    }

    /**
     * @return void
     */
    protected function configureModes()
    {
        $this
            ->addOption('mode', null, InputOption::VALUE_REQUIRED, 'Allowed inflect modes: [1] Camelize (fooBar), [2] Tableize (foo-bar), [3] Classify (FooBar)', BaseInflector::MODE_CAMELIZE)
            ->addOption('save', null, InputOption::VALUE_OPTIONAL, 'Save mode. Allowed values: [0] Save a preview, [1] Save a backup, [2] Overwrite file', -1)
            ->addOption('restore', null, InputOption::VALUE_OPTIONAL, 'Restore file. Allowed values: [1] Manual restore, [2] Auto-restore, [3] Ignore restore) ', 1)
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force inflection without confirmations')
            ->addOption('preview', null, InputOption::VALUE_NONE, 'Show inflection preview')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = realpath($input->getArgument('file'));
        $inflector = new FileInflector($filename);
        $this->doInflect($input, $output, $inflector);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param FileInflector $inflector
     * @param bool $showPreview
     * @return array
     */
    protected function doInflect(InputInterface $input, OutputInterface $output, FileInflector $inflector, $showPreview = true)
    {
        $mode = $input->getOption('mode');
        $output->writeln(sprintf('<comment>File:</comment> %s', $inflector->getFile()));
        $this->restoreInflector($inflector, $input, $output);
        $confirmedInflectedVars = array();

        if ($inflector instanceof EntityInflector) {
            $inflectedProperties = $inflector->getInflectedFields($mode);
            $confirmedInflectedVars = $this->confirmInflectedVariables($inflectedProperties, $input, $output);

            if ($confirmedInflectedVars) {
                $inflector->inflect($confirmedInflectedVars, $mode);

                if ($showPreview) {
                    $this->showInflection($inflector, $output);
                    $this->showInflection($inflector->getClassInflector(), $output);
                    $showPreview = false; // disable the global preview
                }
            }
        }
        else if ($inflector instanceof ClassInflector) {
            $this->restoreInflector($inflector, $input, $output);
            $inflectedProperties = $inflector->getInflectedProperties($mode);
            $confirmedInflectedVars = $this->confirmInflectedVariables($inflectedProperties, $input, $output);

            if ($confirmedInflectedVars) {
                $inflector->inflect($confirmedInflectedVars, $mode);
            }
        }
        else if ($inflector instanceof ViewInflector) {
            $inflectedVariables = $inflector->getInflectedVariables($mode);
            $confirmedInflectedVars = $this->confirmInflectedVariables($inflectedVariables, $input, $output);

            if ($confirmedInflectedVars) {
                $inflector->inflect(array_keys($confirmedInflectedVars), $mode);
            }
        } else {
            if ($variables = $input->getOption('var')) {
                $confirmedInflectedVars = BaseInflector::inflectArray($variables, $mode);
                $inflector->inflect($variables, $mode);
            }
        }

        if ($confirmedInflectedVars) {
            foreach ($confirmedInflectedVars as $var => $changedVar) {
                $output->writeln(sprintf('<info>Renamed</info> %s to <comment>%s</comment>', $var, $changedVar));
            }
            if ($showPreview) {
                $this->showInflection($inflector, $output);
            }
            $this->saveInflector($inflector, $input, $output);
        } else {
            $output->writeln('<info>Nothing to change...</info>');
        }

        return $confirmedInflectedVars;
    }

    /**
     * @param FileInflector $inflector
     * @param OutputInterface $output
     */
    protected function showInflection(FileInflector $inflector, OutputInterface $output)
    {
        $output->writeln('<info>Preview:</info>');
        $output->writeln($inflector->getContent(), OutputInterface::OUTPUT_RAW);

    }

    /**
     * @param FileInflector $inflector
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function saveInflector(FileInflector $inflector, InputInterface $input, OutputInterface $output)
    {
        if ($this->askSaveInflector($inflector, $input, $output)) {
            $inflector->save($input->getOption('save'));
        }
    }

    /**
     * @param FileInflector $inflector
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void|bool
     */
    protected function askSaveInflector(FileInflector $inflector, InputInterface $input, OutputInterface $output)
    {
        static $modeLabels = array(
            0 => 'Preview',
            1 => 'Backup',
            2 => 'Overwrite'
        );

        if (($saveMode = $input->getOption('save')) === -1) {
            return false;
        }

        if (!isset($modeLabels[$saveMode])) {
            throw new \InvalidArgumentException('Invalid save mode!');
        }

        if ($input->hasOption('force')) {
            return true; // Force save!
        }

        $helper = $this->getHelper('question');
        $modeLabel = $modeLabels[$saveMode];
        $questionText = sprintf('<info>Save file (<comment>%s</comment>)</info>: %s [<comment>no</comment>]? ', $modeLabel, $inflector->getFile());
        $question = new ConfirmationQuestion($questionText, false);

        return $helper->ask($input, $output, $question);
    }

    /**
     * @param FileInflector $inflector
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function restoreInflector(FileInflector $inflector, InputInterface $input, OutputInterface $output)
    {
        if ($this->askRestoreInflector($inflector, $input, $output)) {
            $inflector->restore();
        }
    }

    /**
     * @param FileInflector $inflector
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     */
    protected function askRestoreInflector(FileInflector $inflector, InputInterface $input, OutputInterface $output)
    {
        $restore = false;

        if (3 != ($restoreMode = $input->getOption('restore')) && $inflector->hasBackup()) {
            $restore = $input->hasOption('force') ? true : ($restoreMode == 2);
            if (!$restore) {
                $helper = $this->getHelper('question');
                $question = new ConfirmationQuestion('<info>Restore original file from backup [<comment>no</comment>]</info>? ', false);
                $restore = $helper->ask($input, $output, $question);
            }
        }

        return $restore;
    }


    /**
     * @param array $inflectedVariables
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array
     */
    protected function confirmInflectedVariables($inflectedVariables, InputInterface $input, OutputInterface $output)
    {
        $confirmedVariables = array();
        $helper = $this->getHelper('question');

        foreach ($inflectedVariables as $prop => $inflectedProp) {
            $confirm = $input->getOption('force');
            if (!$confirm) {
                $question = new ConfirmationQuestion(sprintf('<info>Inflect property/variable:</info> %s <comment>[yes]</comment>? ', $prop), true);
                $helper->ask($input, $output, $question);
            }
            if ($confirm) {
                $confirmedVariables[$prop] = $inflectedProp;
            }
        }

        return $confirmedVariables;
    }
}