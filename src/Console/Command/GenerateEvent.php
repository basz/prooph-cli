<?php
/**
 * prooph (http://getprooph.org/)
 *
 * @see       https://github.com/proophsoftware/prooph-cli for the canonical source repository
 * @copyright Copyright (c) 2016 prooph software GmbH (http://prooph-software.com/)
 * @license   https://github.com/proophsoftware/prooph-cli/blob/master/LICENSE.md New BSD License
 */

namespace Prooph\Cli\Console\Command;

use Prooph\Cli\Code\Generator\AddEventToAggregate;
use Prooph\Cli\Console\Helper\ClassInfo;
use Prooph\Cli\Exception\FileExistsException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Prooph\Cli\Code\Generator\Event as EventGenerator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateEvent extends AbstractCommand
{
    /**
     * @var EventGenerator
     */
    private $generator;

    /**
     * GenerateEvent constructor.
     * @param EventGenerator $generator
     */
    public function __construct(EventGenerator $generator)
    {
        $this->generator = $generator;

        parent::__construct();
    }

    /**
     * @interitdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('disable-type-prefix')) {
            $input->setArgument('path', $input->getArgument('path') . '/Event');
        }

        $this->generateClass($input, $output, $this->generator);

        if ($aggregateClass = $input->getOption('update-aggregate')) {
            $this->createAggregateClass($aggregateClass, $output);

            $this->updateExistingClass($aggregateClass, $input, $output, new AddEventToAggregate());
        }
    }

    /**
     * @param string $aggregateClass FCQN
     * @param OutputInterface $output
     */
    protected function createAggregateClass($aggregateClass, OutputInterface $output)
    {
        try {
            $command = $this->getApplication()->find('prooph:generate:aggregate');

            /* @var $classInfo ClassInfo */
            $classInfo = $this->getHelper(ClassInfo::class);

            $arguments = [
                'name' => substr($aggregateClass, strrpos($aggregateClass, '\\') + 1),
                'path' => $classInfo->getPath($aggregateClass),
                '--disable-type-prefix' => true,
            ];

            $command->run(new ArrayInput($arguments), $output);
        } catch (FileExistsException $e) {
            $output->writeln(sprintf('Skip generation of aggregate class "%s". Already exists.', $aggregateClass));
        }
    }

    /**
     * @interitdoc
     */
    protected function configure()
    {
        $this
            ->setName('prooph:generate:event')
            ->setDescription('Generates an event class')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'What is the name of the event?'
            )
            ->addArgument(
                'path',
                InputArgument::OPTIONAL,
                'Path to store the file. Starts from configured source folder path.'
            )
            ->addArgument(
                'class-to-extend',
                InputArgument::OPTIONAL,
                'FCQN of the base class , optional',
                '\Prooph\EventSourcing\AggregateChanged'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Overwrite file if exists, optional'
            )
            ->addOption(
                'not-final',
                null,
                InputOption::VALUE_NONE,
                'Mark class as NOT final, optional'
            )
            ->addOption(
                'update-aggregate',
                null,
                InputOption::VALUE_OPTIONAL,
                'FCQN of an aggregate to add event method, optional'
            )
            ->addOption(
                'disable-type-prefix',
                null,
                InputOption::VALUE_NONE,
                'Use this flag if you not want to put the classes under the "Event" namespace, optional'
            );
    }
}
