<?php

namespace App\Command;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class TaskCreateCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:task-create';

    /**
     * [$entityManager description]
     * @var EntityManager
     */
    private $entityManager;

    /**
     * [__construct description]
     * @param EntityManagerInterface $entityManager [description]
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');

        # task
        $taskTitle          = new Question('Please give your task a title:', '');
        $taskDescription    = new Question('Please give your task a brief description:', '');
        $tasResponsible     = new Question('Please assign task to responsible:', '');
        $taskClient         = new Question('Please assign task to client(customer):', '');
        $taskStart          = new Question('Please enter start datetime:', '');
        $taskEnd            = new Question('Please enter end datetime:', '');

        # questions
        $taskTitle          = $helper->ask($input, $output, $taskTitle);
        $taskDescription    = $helper->ask($input, $output, $taskDescription);
        $tasResponsible     = $helper->ask($input, $output, $tasResponsible);
        $taskClient         = $helper->ask($input, $output, $taskClient);
        $taskStart          = $helper->ask($input, $output, $taskStart);
        $taskEnd            = $helper->ask($input, $output, $taskEnd);

        # output
        $io->title('Task Summary');
        $io->table(
            ['Parameters', 'Values'],
            [
                ['Title', $taskTitle],
                ['Description', $taskDescription],
                ['Responsible', $tasResponsible],
                ['Client', $taskClient],
                ['Start', $taskStart],
                ['End', $taskEnd]
            ]
        );

        $question = new ConfirmationQuestion('Is this correct?', false);

        if (!$helper->ask($input, $output, $question)) {

            $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
            return 0;
        }

        return 0;
    }
}
