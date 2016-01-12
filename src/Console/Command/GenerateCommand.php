<?php
/**
 * prooph (http://getprooph.org/)
 *
 * @see       https://github.com/proophsoftware/prooph-cli for the canonical source repository
 * @copyright Copyright (c) 2016 prooph software GmbH (http://prooph-software.com/)
 * @license   https://github.com/proophsoftware/prooph-cli/blob/master/LICENSE.md New BSD License
 */

namespace Prooph\Cli\Console\Command;

use Prooph\Cli\Code\Generator\Command as CommandGenerator;
use Prooph\Cli\Code\Generator\CommandHandler as CommandHandlerGenerator;
use Prooph\Cli\Code\Generator\CommandHandlerFactory as CommandHandlerFactoryGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends AbstractCommand
{
    /**
     * @var CommandGenerator
     */
    private $commandGenerator;

    /**
     * @var CommandHandlerGenerator
     */
    private $commandHandlerGenerator;

    /**
     * @var CommandHandlerFactoryGenerator
     */
    private $commandHandlerFactoryGenerator;

    /**
     * GenerateCommand constructor.
     * @param CommandGenerator $generator
     * @param CommandHandlerGenerator $handlerGenerator
     * @param CommandHandlerFactoryGenerator $factoryGenerator
     */
    public function __construct(
        CommandGenerator $generator,
        CommandHandlerGenerator $handlerGenerator,
        CommandHandlerFactoryGenerator $factoryGenerator)
    {
        $this->commandGenerator = $generator;
        $this->commandHandlerGenerator = $handlerGenerator;
        $this->commandHandlerFactoryGenerator = $factoryGenerator;

        parent::__construct();
    }

    /**
     * @interitdoc
     */
    protected function configure()
    {
        $this
            ->setName('prooph:generate:command')
            ->setDescription('Generates a command, command handler and command handler factory class')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'What is the name of the command class?'
            )
            ->addArgument(
                'path',
                InputArgument::OPTIONAL,
                'Path to store the file. Starts from configured source folder path.',
                'Command'
            )
            ->addArgument(
                'class-to-extend',
                InputArgument::OPTIONAL,
                'FCQN of the base class , optional',
                '\Prooph\EventSourcing\AggregateRoot'
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
        ;
    }

    /**
     * @interitdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->generateClass($input, $output, $this->commandGenerator);

        $input->setArgument('class-to-extend', '');

        $this->generateClass($input, $output, $this->commandHandlerGenerator);
        $this->generateClass($input, $output, $this->commandHandlerFactoryGenerator);
    }
}
